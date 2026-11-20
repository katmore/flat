<?php
/**
 * class definition 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2017  Doug Bird.
 * ALL RIGHTS RESERVED. THIS COPYRIGHT APPLIES TO THE ENTIRE CONTENTS OF THE WORKS HEREIN
 * UNLESS A DIFFERENT COPYRIGHT NOTICE IS EXPLICITLY PROVIDED WITH AN EXPLANATION OF WHERE
 * THAT DIFFERENT COPYRIGHT APPLIES. WHERE SUCH A DIFFERENT COPYRIGHT NOTICE IS PROVIDED
 * IT SHALL APPLY EXCLUSIVELY TO THE MATERIAL AS DETAILED WITHIN THE NOTICE.
 * 
 * The flat framework is copyrighted free software.
 * You can redistribute it and/or modify it under either the terms and conditions of the
 * "The MIT License (MIT)" (see the file MIT-LICENSE.txt); or the terms and conditions
 * of the "GPL v3 License" (see the file GPL-LICENSE.txt).
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * 
 * @license The MIT License (MIT) http://opensource.org/licenses/MIT
 * @license GNU General Public License, version 3 (GPL-3.0) http://opensource.org/licenses/GPL-3.0
 * @link https://github.com/katmore/flat
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat\core;

use \flat\core\ssh\exception;

use \flat\core\ssh\auth;
use \flat\core\ssh\disconnect as disconnect;
use \flat\core\ssh\profile as profile;

class ssh {
   
   /*-stream handles-*/
   private $connection;
   private $sftp;
   
   /*-connection data-*/
   private $profile; //sf\module\ssh2\data\profile
   private $auth; //sf\module\ssh2\data\auth\(password|pubkey)
   private $disconnect; //sf\module\ssh2\data\disconnect
   
   private $pubkey_file;
   private $privkey_file;
   
   public function get_auth() {
      if (!$this->auth instanceof auth) throw new exception\auth\not_ready("not auth object");
      return $this->auth;
   }
   
   /*
    * CONSERVATIVELY DISABLED unused private callback. connect() registers its
    * separate no-op closure and this method has no caller. Original:
    * private function disconnect_callback($reason, $message, $language) {
    *
    * $dc = new disconnect();
    * $dc->time = date("c");
    * $dc->reason = $reason;
    * $dc->message = $message;
    * $dc->language = $language;
    *
    * $this->connection = null;
    * $this->disconnect = $dc;
    * return true;
    * }
    */

   /*
    * config:
    *    host,
    *    port,
    *    connect,
    *    username,
    *    password,
    *    pubkey,
    *    privkey,
    *    secret
    */
   public function __construct($config) {
      $this->config = $config;
      if (!function_exists("ssh2_connect")) {
         throw new exception\syserror(
            "ssh2_* functions not available"
         );
      }
      $required = array("host","port","username");
      foreach ($required as $key) {
         if (empty($config[$key])) {
            throw new exception\input_error\missing_param(
               $key
            );
         }
      }
      $profile = new profile();
      $profile->host = $config['host'];
      $profile->port = $config['port'];
      
      if (!empty($config['password'])) {
         
         $auth = new auth\password();
         $auth->username = $config['username'];
         $auth->password = $config['password'];
         
      } else
      if (!empty($config['pubkey'])) {
         if (empty($config['privkey'])) {
            throw new exception\input_error\missing_param(
               'privkey'
            );
         }
         $auth = new auth\pubkey();    
         $auth->username = $config['username'];
         $auth->pubkey = $config['pubkey'];
         $auth->privkey = $config['privkey'];
         $auth->passphrase = $config['passphrase'] ?? null;
      } else {
         throw new exception\input_error(
            'config must have either password or pubkey'
         );
      }
      
      $this->auth = $auth;
      $this->profile = $profile;
      
      if (!empty($config['connect'])) {
         $this->connect();
      }
   }

   public function __destruct() {
      $this->delete_keyfiles();
      if ($this->connection) {
         $this->disconnect(true);
      }
   }

   public function is_connected() {
      if (!$this->connection) {
         return false;
      }
      return true;
   }

   private function sftp() {
      if (!$this->connection) {
         throw new exception\connection\missing();
      }
      
      if (!$this->sftp) {      
         $this->sftp = ssh2_sftp($this->connection);
      }
   }

   /**
    * Download a file with an idle timeout from default_socket_timeout.
    * Every successful read restarts the timer. Callback arguments and normal
    * return behavior are unchanged; a timeout leaves the bytes already read.
    */
   public function sftp_get($remote,$local,$xfer_callback=null) {
      
      $this->sftp();
      
      $sftp_path = "ssh2.sftp://".$this->sftp.$remote;
      if (!$remote_h = fopen($sftp_path, 'r')) {
         throw new exception\sftp\remote(
            "could not open $remote"
         );
      }
      try {
         if (!$local_h = fopen($local, 'w')) {
            throw new exception\sftp\local("could not open $local for write");
         }
         try {
            $last_read = microtime(true);
            $timeout = (float) ini_get('default_socket_timeout');
            $read_bytes = 0;
            $filesize = filesize($sftp_path);
            $cv = 0;
            while (!feof($remote_h) && microtime(true) - $last_read < $timeout) {
               $buffer = fread($remote_h, 2048);
               if ($buffer === '' || $buffer === false) {
                  if (is_callable($xfer_callback)) call_user_func($xfer_callback);
                  break;
               }
               // Owner contract: every successful read restarts the idle timer.
               $last_read = microtime(true);
               fwrite($local_h, $buffer);
               if (is_callable($xfer_callback)) call_user_func($xfer_callback);
               $read_bytes += strlen($buffer);
               if (($cv % 100) === 0) {
                  $progress = $filesize > 0 ? round(min(100, 100 * $read_bytes / $filesize), 2) : 0;
                  if (is_callable($xfer_callback)) call_user_func($xfer_callback);
                  $cv = 0;
               }
               $cv++;
            }
         } finally {
            fclose($local_h);
         }
      } finally {
         fclose($remote_h);
      }
   }

   public function connect() {
      
      if ($this->connection) {
         throw new exception\syserror(
            "already connected"
         );
      }
         
      if (!$connection = ssh2_connect(
         $this->profile->host,
         $this->profile->port,
         array(),
         array(
            'disconnect'=>function($reason, $message, $language) {}
         )
      )) {
         throw new exception\connection\failure();
      }
   
      //if (is_a($this->auth,"\\sf\module\\ssh2\\data\\auth\\password")) {
      if ($this->auth instanceof auth\password) {
         if (!ssh2_auth_password($connection, $this->auth->username, $this->auth->password)) {
            throw new exception\auth\failure\password();
         }
      } else
      //if (is_a($this->auth,"\\sf\module\\ssh2\\data\\auth\\pubkey")) {
      if ($this->auth instanceof auth\pubkey) {
         /*
          * 
          * temporary files to give to ssh2 lib
          *    the function this->delete_keyfiles() deletes these files
          *    ideally, these files should exist for as little time as possible
          *       therefore...
          *    delete_keyfiles() is called in the destructor
          *    in addition to being called explicitly after ssh2 authentication is complete 
          *    
          */
         // Any failure below removes the temporary key files before it propagates.
         try {
         $this->pubkey_file = tempnam(sys_get_temp_dir(), "sfnpub");
         file_put_contents($this->pubkey_file, $this->auth->pubkey);
         
         $this->privkey_file = tempnam(sys_get_temp_dir(), "sfnpriv");
         $this->privkey_out = tempnam(sys_get_temp_dir(), "sfnout");
         
         /*
          * re-encrypt private key with a randomly generated passphrase
          *    an unencrypted private key will never be written to the file system
          */
         $passphrase = exec('< /dev/urandom tr -dc A-Za-z0-9 | head -c${1:-128};echo;');
         if (strlen($passphrase)!=128) {
            throw new exception\syserror("could not generate temp passphrase for plain private key");
         }

         if (empty($this->auth->passphrase)) {
            //escapeshellarg($this->privkey_file)
            $openssl_cmd = "/usr/bin/openssl rsa -in /dev/stdin -aes256 -out ".escapeshellarg($this->privkey_file)." -passout pass:".escapeshellarg($passphrase)."";
         } else {
            $openssl_cmd = "/usr/bin/openssl rsa -in /dev/stdin -passin pass:".escapeshellarg($this->auth->passphrase)." -aes256 -out ".escapeshellarg($this->privkey_file)." -passout pass:".escapeshellarg($passphrase)."";
         }
         //$openssl_cmd .= " <<< '".$this->auth->privkey."\n'";
         //$out = shell_exec($openssl_cmd ." 2>&1");
         $descriptorspec = array(
            0 => array("pipe", "r"),
            1 => array("pipe", "w"), 
            2 => array("pipe", "w") 
         );
         $process = proc_open($openssl_cmd, $descriptorspec, $pipes, null, null);
         if (!is_resource($process)) throw new exception\syserror("could not use openssl");
         stream_set_blocking($pipes[2], 0);
         if ($err = stream_get_contents($pipes[2])) throw new exception\syserror("could not encrypt plain private key with openssl: $err");
         fwrite($pipes[0], $this->auth->privkey."\n");
         fclose($pipes[0]);
         $out = stream_get_contents($pipes[1]);
         fclose($pipes[1]);
         
         if (!file_get_contents($this->privkey_file)) {
            $this->delete_keyfiles();
            
            throw new exception\syserror("could not encrypt plain private key with openssl: $out");
         }
         
         /*
          * verify fingerprint
          */
         $ssh2_fingerprint = ssh2_fingerprint($connection);
         if (empty($this->auth->fingerprint)) {
            $this->auth->fingerprint = $ssh2_fingerprint;
         } else {
            if ($this->auth->fingerprint != $ssh2_fingerprint) {
               throw new exception\syserror("fingerprint mismatch");
            }
         }
         
         if (!ssh2_auth_pubkey_file ( $connection , $this->auth->username , $this->pubkey_file, $this->privkey_file, $passphrase )) {
            throw new exception\auth\failure\pubkey();
         }
         } catch (\Throwable $e) {
            $this->delete_keyfiles();
            throw $e;
         }
      }
      $this->delete_keyfiles();
      $this->connection = $connection;
      
   }
   private function delete_keyfiles() {
      $filevars = array('pubkey_file','privkey_file','privkey_out');
      foreach ($filevars as $var) {
         if (isset($this->$var) && file_exists($this->$var)) {
            if (is_file($this->$var)) unlink($this->$var);
         }
      }
   }
      
   public function exec($cmd) {
      if (!$this->connection) {
         throw new exception\connection\missing();
      }
      
      if (!($stream = ssh2_exec($this->connection,$cmd))) {
         throw new exception\syserror(
            "could not get remote stream"
         );
      }
      stream_set_blocking($stream, true);
      $output = stream_get_contents($stream);
      fclose($stream);
      return $output;
   }
   
   /*
    * returns boolean true if pid exists
    *    --otherwise--
    *    throws exception unless $ignore_missing === true
    *       (then it returns boolean false)  
    *    
    */
   private function command_output($command) {
      // Keep application-provided legacy command hooks working, and use the
      // existing executor when a subclass does not supply that old helper.
      if (method_exists($this,'exec_output')) return $this->exec_output($command);
      return $this->exec($command);
   }

   public function checkpid($pid,$ignore_missing=false) {
      
      /*
       * check pid is sane
       */
      if (false ===($pid = filter_var(
         $pid, 
         FILTER_VALIDATE_INT, 
         array('options'=>array('min_range'=>1))))
      ) {         
         throw new exception\input_error\bad_param(
            "pid",
            "must be int 1 or greater"
         );
      }
            
      if (empty(trim($this->command_output('kill -0 '.$pid.' ; echo $? 2>&1')))) {
         return true;
      }
      
      if ($ignore_missing) return false;
      
      throw new exception\exec\pid\not_running();
      
   }
   
   /*
    * returns pid if successful
    *    throws exception otherwise
    */
   public function exec_backround($cmd,$logfile,$min_runtime=0) {
      
      if (!$this->connection) {
         throw new exception\connection\missing();
      }         
      
      if (false ===($min_runtime = filter_var(
         $min_runtime, 
         FILTER_VALIDATE_INT, 
         array('options'=>array('min_range'=>0,'max_range'=>60))))
      ) {
         $min_runtime = 3;
      }   
      
      
      $pid = $this->command_output($cmd.' > '.$logfile.' 2>&1 & echo $!');
      
      if (empty($pid)) {
         throw new exception\exec\pid\missing();
      }
      
      if (empty($min_runtime)) {
         return $pid;
      }
      
      /*
       * wait min_runtime seconds
       */
      sleep($min_runtime);
      
      /*
       * check again that pid is still running
       */
      $this->checkpid(
         $pid
      );
      
      return $pid;
   }

   public function disconnect($ignore_missing=false) {
      
      if (!$this->connection) {
         if ($ignore_missing) return false;
         throw new exception\connection\missing();
      }   
      
      ssh2_exec($this->connection,'echo "EXITING" && exit;');
      
      return true;
   }
   
   public function kill_process($pid,$sig_1=1,$wait_1=10,$sig_2=9,$wait_2=10) {
      if (!$this->connection) {
         throw new exception\connection\missing();
      }
      
      /*
       * check pid is sane
       */
      if (false ===($pid = filter_var(
         $pid, 
         FILTER_VALIDATE_INT, 
         array('options'=>array('min_range'=>1))))
      ) {         
         throw new exception\input_error\bad_param(
            "pid",
            "must be int 1 or greater"
         );
      }
      foreach (['sig_1'=>&$sig_1,'sig_2'=>&$sig_2] as $name=>&$signal) {
         if (false === ($signal = filter_var($signal,FILTER_VALIDATE_INT,
               ['options'=>['min_range'=>1,'max_range'=>15]]))) {
            throw new exception\input_error\bad_param($name,"must be int between 1 and 15");
         }
      }
      unset($signal);
      foreach (['wait_1'=>&$wait_1,'wait_2'=>&$wait_2] as $name=>&$wait) {
         if (false === ($wait = filter_var($wait,FILTER_VALIDATE_INT,
               ['options'=>['min_range'=>0,'max_range'=>60]]))) {
            throw new exception\input_error\bad_param($name,"must be int between 0 and 60");
         }
      }
      unset($wait);
      $start = time();
      
      /*
       * invoke a kill with sig $sig1
       */
      if (!empty(trim($this->command_output('kill -'.$sig_1.' '.$pid.' ; echo $? 2>&1')))) {
         throw new exception\exec\pid\kill($pid);
      }
      
      
      for (;;) {
         /*
          * if not running then we're done
          */
         if (!$this->checkpid(
            $pid,
            true
         )) {
            return true;
         }
         if (($elapsed = (time()-$start))>=$wait_1) break 1;
      }
      
      /*
       * invoke a kill with sig $sig1
       */
      if (!empty(trim($this->command_output('kill -'.$sig_2.' '.$pid.' ; echo $? 2>&1')))) {
         throw new exception\exec\pid\kill($pid);
      }
      
      for (;;) {
         /*
          * if not running then we're done
          */
         if (!$this->checkpid(
            $pid,
            true
         )) {
            return true;
         }
         if (($elapsed = (time()-$start))>=$wait_2) break 1;
      }
      
      throw new exception\exec\pid\cannot_kill(
         $pid,
         round($elapsed)
      );
   }   
}





















