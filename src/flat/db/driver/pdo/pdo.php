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
namespace flat\db\driver\pdo;



class pdo extends \PDO {
   const path_root_none = 2;
   const path_root_from_config = 1;
   const path_root_from_config_ns = 'app/script/pdo/path_root';
   
   /**
    * prepares and optionally executes a statement, optionally mapping the results
    *    to a \flat\data object, and optionally invoking a callback for each result.
    *    returns a \PDOStatement object unless 'record' param is set; in that case
    *    an  array of \flat\data objects is returned.
    * 
    * @param array $param A key=>val array of parameter values:
    *    <ul>
    *       <li><strong>string</strong> $param['script'] pdo script path to prepare from. required unless $param['statement'] 
    *       has non-empty value.</li>
    *    <li><strong>string $param['statement']</strong> statement. required unless $param['script'] has 
    *       non-empty value.</li>
    *    <li><strong>array $param['bindValue']</strong> map of values to bind.</li>
    *    <li><strong>array $param['bindParam']</strong> map of values to bind.    </li>
    *    <li><strong>callable $param['callback']</strong> function to invoke for each result after 
    *       successful execution. callback signature: function(array $fetch_assoc_row) {};</li>
    *    <li>
    *       <strong>\flat\data | string | bool $param['record']</strong> Optional. 
    *          The statement will be executed and an array of \flat\data objects will be returned as specified:
    *       <ul>
    *          <li><strong>\flat\data $param['record']</strong> object cloned for each row, and each column mapped to public and protected properties of original \flat\data object.</li>
    *          <li<strong>string \flat\data $param['record']</strong> Object instantiated for each row, and each column mapped to public and protected properties of original \flat\data object.</li>
    *          <li><strong>(bool) $param['record']</strong> If value is (bool) true, a \flat\data\generic object is instantiated for each row, with a public property created for column value. Ignored if value is (bool) false.</li>
    *       </ul>
    *    </li>
    *    <li><strong>\flat\data | string | bool $param['result']</strong> alias of $param['record'].</li>
    *    <li><strong>string $param['stmt']</strong> OPTIONAL alias of $param['statement'].</li>
    *    <li><strong>string $param['path_root']</strong> OPTIONAL root directory to find the statement file.
    *       Defaults to pdo::path_root_from_config, which means the config value obtained
    *       using configuration namespace contained in the constant 
    *       pdo::path_root_from_config_ns is used as the path_root value. pdo::path_root_from_config
    *       is probably the most convenient behavior.</li>
    *    <li><strong>array $param['driver_options']</strong> OPTIONAL see \PDO::prepare $driver_options argument documentation
    *       for details.</li>
    *    <li><strong>bool $param['execute']</strong> OPTIONAL attempt to execute</li>
    *    </ul>
    *       
    * @return \PDOStatement | \flat\data[] 
    */
   public function prepare_map(array $param) {
      $record = null;
      $path = null;
      $stmt = null;
      $driver_options=null;
      $callback = null;
      if (!empty($param['path'])) {
         $path = $param['path'];
      }else if (!empty($param['script'])) {
         $path = $param['script'];
      }
      if (!empty($param['stmt'])) {
         $stmt = $param['stmt'];
      } elseif (!empty($param['statement'])) {
         $stmt = $param['statement'];
      }
      if (empty($path) && empty($stmt)) {
         throw new \flat\lib\exception\bad_param('param',"must have 'file' or 'statement' or 'stmt'");
      }
      if (!empty($param['paramValue']) && !is_array($param['paramValue'])) {
         throw new \flat\lib\exception\bad_param('paramValue', 'must be assoc array');
      }
      if (!empty($param['callback'])) $callback = $param['callback'];
      if (!empty($param['record'])) {
         $record = $param['record'];
      } elseif (!empty($param['result'])) {
         $record = $param['result'];
      }
      if (!empty($record)) {
         if (is_string($record)) {
            if (($record !='array') && (!is_a($record,'\flat\data',true))) {
               throw new \flat\lib\exception\bad_param(
                  "record",
                  'must be name of \flat\data child class if string'
               );
            }
         }elseif(is_object($record)) {
            if (!$record instanceof \flat\data) {
               throw new \flat\lib\exception\bad_param(
                     "record",
                     'must be instance of \flat\data object'
               );               
            }
         } elseif($record===true) {
            $record = '\flat\data\generic';
         } else {
            throw new \flat\lib\exception\bad_param(
               "record",
               'must be (\flat\data) object, (\flat\data) child classname, or (bool) true'
            );            
         }
      } else {
         if (in_array('record',$param,true) || in_array('result',$param,true)) {
            $record = '\flat\data\generic';
         }
      }
      
      
      //if (!empty($param['driver_options'])) $driver_options = $param['driver_options'];
      $arg = [];
      $cbobj = $this;
      if (!empty($path)) {
         $arg[]=$path;
         if (!empty($param['path_root']) && ($param['path_root']!=pdo::path_root_from_config)) {
            $arg[]=$param['path_root'];
         }
         if (!empty($param['driver_options'])) {
            $arg[]=$param['driver_options'];
         }
         $stmt = call_user_func_array([$cbobj,'prepare_from_file'], $arg);
      } else {
         $arg[]=$stmt;
         if (!empty($param['driver_options'])) {
            $arg[]=$param['driver_options'];
         }
         $stmt = call_user_func_array([$cbobj,'prepare'], $arg);      
      }
      
      
      
      if (!empty($param['bindValue']) || !empty($param['bindParam'])) {
         if (!empty($param['bindValue'])) {
            foreach($param['bindValue'] as $k=>$v) {
               $type = null;
               if (is_int($v)) {
                  $type = \PDO::PARAM_INT;
               }elseif(is_string($v)) {
                  $type= \PDO::PARAM_STR;
               }
               $stmt->bindValue($k,$v,$type);
            }
         }
         if (!empty($param['bindParam'])) {
            foreach($param['bindParam'] as $k=>$v) {
               $type = null;
               if (is_int($v)) {
                  $type = \PDO::PARAM_INT;
               } elseif(is_string($v)) {
                  $type= \PDO::PARAM_STR;
               }     
               $stmt->bindParam($k,$v,$type);
            }
         }   
      }    
      $executed = false;
      
      
      if ($callback || $record) {
         $executed = true;
         
         $stmt->execute();
         if ($stmt->rowCount()) {
            $class = null;
            $clone = null;
            $assoc = null;
            $result=[];
            if (is_string($record)) {
               if ($record=='array') {
                  $assoc = true;
               } else {
                  $class = $record;
               }
            } else
            if (is_object($record)) {
               $clone = $record;
            }
            while($row = $stmt->fetch(\PDO::FETCH_ASSOC)) {
               //var_dump($row);
               if ($callback) $callback($row);
               if ($assoc) {
                  $obj = $row;
               } elseif ($class) {
                  $obj = new $class($row);
               } elseif ($clone) {
                  $obj = clone $clone;
                  $obj->map(new \flat\data\rules(['data'=>$row]));
               }
               if ($assoc || $class || $clone) {
                  if (!$callback) $result[]=$obj;
               }
            }
            if (!$callback && ($assoc || $class || $clone)) {
               return $result;
            }
         }
         if (!$callback) return [];
         return;
      }
      
      if ((!empty($param['execute']) || in_array('execute',$param) || !empty($param['exec']) || in_array('exec',$param) ) && !$executed && ($stmt instanceof \PDOStatement)) {
         $stmt->execute();
      }
      
      return $stmt;
   }
   /**
    * Reads an SQL file and prepares as statement to be executed by the 
    *    PDOStatement::execute() method. Returns \PDOStatement object if successful.
    *    Throws an exception or (bool) false on encountering an error.
    * 
    * @return \PDOStatement | bool
    * 
    * @param string $path sql file path
    * @param int | string $path_root (optional) defaults to pdo::path_root_from_config.
    *    acceptable values are pdo::path_root_from_config pdo::path_root_none, or 
    *    a path to a system directory that will contain the file specified by $path param.
    * @param array $driver_options (optional) driver options. see \PDO::prepare().
    * 
    * @throws \flat\db\driver\pdo\exception\bad_prepare_path
    * @throws \flat\db\driver\pdo\exception\bad_prepare_path_root
    * 
    * @see \PDO::prepare()
    */
   public function prepare_from_file($path,$path_root=pdo::path_root_from_config, array $driver_options=[]) {
      $basedir = "";
      if (is_array($path_root) && empty($driver_options)) {
         
         $driver_options = $path_root;
         $path_root = pdo::path_root_from_config;
      }
      if (is_int($path_root) && $path_root==pdo::path_root_from_config) {
         $basedir = \flat\core\config::get(self::path_root_from_config_ns);
      } else
      if (is_string($path_root)) {
         $basedir = $path_root;
      }
      if (!empty($basedir)) {
         if (!file_exists($basedir)) {
            throw new exception\bad_prepare_path_root($basedir, "does not exist");
         }
         if (!is_dir($basedir)) {
            throw new exception\bad_prepare_path_root($basedir, "not a directory");
         }
         $filename = $basedir . "/" . $path;
      } else {
         $filename = $path;
      }
      if (!file_exists($filename)) {
         throw new exception\bad_prepare_path($filename,"does not exist");
      }
      if (!is_file($filename)) {
         throw new exception\bad_prepare_path($filename,"is not a file");
      }
      if (!is_readable($filename)) {
         throw new exception\bad_prepare_path($filename,"is not readable");
      }
      $statement = file_get_contents($filename);
      return parent::prepare($statement,$driver_options);
   }
}




