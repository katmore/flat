<?php
/**
 * \flat\db\driver\mongo class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver;
/**
 * operations for mongo client
 * 
 * @package    flat\db\mongo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * @abstract 
 * @link http://php.net/manual/en/mongo.tutorial.connecting.php
 * 
 * @todo mongo::command()
 * 
 */
abstract class mongo extends \flat\db implements \flat\db\driver {
   
   /**
    * retrieves a mongo collection object based on given namespace.
    * 
    * @param string $ns (optional) if given, appends a "." and string value of $ns to derived collection name
    *  
    * @return \MongoCollection
    * @see mongo::get_collection() for possible exception
    * @see mongo::get_ns_collection_name()
    */
   public function get_ns_collection($ns=null) {
      return $this->get_collection($this->get_ns_collection_name($ns));
   }
   /**
    * derives a namespaced mongo collection name
    * 
    * @param string $ns (optional) if given, appends a "." and string value of $ns to derived collection name
    * 
    * @see mongo::get_collection_name() for possible exceptions and how collection name is derived
    */
   public function get_ns_collection_name($ns=null) {
      $name = $this->get_collection_name();
      if (is_string($ns) && !empty($ns)) {
         $name = $name.".$ns";
      }
      return $name;
   }
   /**
    * retrieves a mongo collection object. if $name not specified, attempts to 
    *    use contextually determined name from interface.
    * 
    * @param string $name (optional) collection name
    * 
    * @return \MongoCollection
    * @throws \flat\db\driver\mongo\collection\not_found
    * @throws \flat\db\driver\mongo\collection\missing name not specified and 
    *    could not be determined from interface.
    * @throws \flat\db\driver\mongo\collection\bad_param specified name fails sanity check.
    * 
    * @see \flat\db\driver\mongo\collection\explicit interface to specify collection name explicitly.
    * @see \flat\db\driver\mongo\collection\shortname interface that indicates collection name is namespace shortname.
    */
   public function get_collection($name="") {
      if (empty($name)) {
         $name = $this->get_collection_name();
      } else {
         $this->_check_collection_name($name);         
      }
      
      $col = $this->get_db()->selectCollection($name);
      if (!is_a($col,"\\MongoCollection")) throw new mongo\collection\not_found(
         "could not get mongo collection object"
      );
      return $col;
   }
   private $_col_name;
   
   /**
    * retrieve current collection name in context
    * 
    * @return string
    * 
    * @see \flat\db\driver\mongo\collection\shortname
    *    if child class has this interface, it is implied the 
    *    namespace shortname is the collection name. This overrides 
    *    any other method of defining collection name in context.
    * 
    * @see \flat\db\driver\mongo\collection\explicit
    *    if child class has this interface, it will be used to define
    *    the collection name in context.
    * 
    * @see \flat\db\driver\mongo::set_collection_name() can be used to set 
    *    collection name into context when not provided by interface.
    * 
    * @throws \flat\db\driver\mongo\collection\missing name cannot be 
    *    determined from interface and set_collection_name() method 
    *    never called successfully.
    */
   public function get_collection_name() {
      if ($this instanceof mongo\collection\shortname ) {
         $r = new \ReflectionClass($this);
         return $r->getShortname();
      } else
      if ($this instanceof mongo\collection\explicit ) {
         return $this->get_mongo_collection();
      }
      if (empty($this->_col_name)) throw new mongo\collection\missing();
      return $this->_col_name;
   }
   /**
    * sanity check collection name
    * @return void
    * @param mixed $name collection name
    * @throws \flat\db\driver\mongo\collection\bad_param if name fails sanity check
    */
   private function _check_collection_name($name) {
      if (empty($name)) throw new mongo\collection\bad_param(
         "name","cannot be empty"
      );
      if (!is_string($name)) throw new mongo\collection\bad_param(
         "name","must be string"
      );
   }
   /**
    * set collection name into context when not provided by interface.
    * 
    * @return void
    * 
    * @param string $name collection name
    * 
    * @throws \flat\db\driver\mongo\collection\illegal if child is interface of
    *    \flat\db\driver\mongo\collection\shortname or 
    *    \flat\db\driver\mongo\collection\explicit
    * 
    * @throws \flat\db\driver\mongo\collection\bad_param if name fails sanity check
    * 
    */
   public function set_collection_name($name) {

      if ($this instanceof mongo\collection\shortname ) {
         return mongo\collection\illegal(
            "cannot set collection name while having interface ".
            "\\flat\\db\\driver\\mongo\\collection\\shortname"
         );
      } else
      if ($this instanceof mongo\collection\explicit ) {
         return mongo\collection\illegal(
            "cannot set collection name while having interface ".
            "\\flat\\db\\driver\\mongo\\collection\\explicit"
         );
      }
      $this->_check_collection_name($name);
      $this->_col_name = $name;
   }
   /**
    * retrieve number of affected records from previous mongo collection update
    * 
    * @return int
    * @throws \flat\db\driver\mongo\record\not_modified
    * @param array $options (optional) options as defined:
    *    bool $options['count_only'] if set true, will return 0 instead of 
    *       throwing exceptions
    */
   public function get_affected_records(array $options=NULL) {
      $doc = $this->command(array('data'=>array('getLastError'=>1)));

      if (!isset($doc['updatedExisting'])) {
         throw new \flat\db\driver\mongo\record\not_modified(
            "no update data found"
         );
      }
      if (!$doc['updatedExisting']) {
         throw new \flat\db\driver\mongo\record\not_modified(
            "not updatedExisting"
         );
      }
      if (!isset($doc['n'])) {
         throw new \flat\db\driver\mongo\record\not_modified(
            "missing 'n' record count"
         );
      }
      if (!($doc['n'])) {
         throw new \flat\db\driver\mongo\record\not_modified(
            "no records updated"
         );
      }
      return (int) $doc['n'];
   }
   /**
    * perform command on mongo client
    * part of the \flat\db\driver interface
    * 
    * @see \flat\db\driver
    * 
    * @param array $param command parameters
    *    array $param['data'] (required) command data
    *    array $param['options'] (optional) command options 
    *       (as of time of writing, only for index creation)
    * @return mixed
    * @throws \flat\db\driver\mongo\db\exception\bad_param
    * 
    * @see \MongoDB::command()
    */
   public function command(array $param=NULL) {
      if (empty($param['data'])) {
         throw new \flat\db\driver\mongo\db\exception\bad_param(
            "data","cannot be missing or empty"
         );
      }
      $data = $param['data'];
      if (!is_array($data)) {
         throw new \flat\db\driver\mongo\db\exception\bad_param(
            "data","must be array"
         );
      }
      if (empty($param['options'])) {
         return $this->get_db()->command($data);
      }
      $opt = $param['options'];
      if (!is_array($opt)) {
         throw new \flat\db\driver\mongo\db\exception\bad_param(
            "options","must be array, if given"
         );
      }
      return $this->get_db()->command($data,$opt);
   }
   /**
    * @uses \flat\db\driver\mongo::load_client()
    *    minimum interval between mongo ping commands
    */
   const client_ping_expire = 60;
   /**
    * @uses \flat\db\driver\mongo::load_client()
    *    default timezone offset in seconds between this system 
    *       and mongo db server.
    */
   const default_ping_offset = 0;
   
   /**
    * collection to run test commands on
    */
   const test_collection = "test";
   
   /**
    * @var \MongoClient[] $_client mongo clients. indexed by connection string.
    * @uses \flat\db\driver\mongo::load_client()
    * @static
    */   
   private static $_client;
   
   /**
    * @var int[] $_client_ping timestamp of last successful command 
    *    to mongo client. indexed by connection string.
    * @uses \flat\db\driver\mongo::load_client()
    * @uses \flat\db\driver\mongo::$_client
    * @static
    */   
   private static $_client_ping;
   
   /**
    * retrieve new or existing cached \MongoClient object. cache expires when a
    *    connection does not exist or last ping is greater than 
    *    self::client_expire seconds.
    * 
    * @param string $connect_string mongo connection string. client is 
    *    cached by this value.  
    *    
    * @param int $ping_offset (optional) default 0. timezone offset in seconds 
    *    to assume between system and mongo server.
    *    
    * @return \MongoClient
    * 
    * @link http://php.net/manual/en/mongoclient.construct.php 
    *    connection string format definition
    * 
    * @throws \MongoConnectionException
    * @static
    * @final
    */
   final public static function load_client($server,array $options=NULL) {

      $param = array(
         'ping_offset'=>0,
      );
      foreach ($options as $key=>$val) {
         if (isset($param[$key])) {
            $param[$key] = $val;
         }
      }
      /**
       * prepare client and server options for \MongoClient
       * 
       * @uses array $param['client_options'] (optional)
       *    $options arg for \MongoClient::__construct
       * 
       * @uses array $param['server_options'] (optional)
       *    $driver_options arg for \MongoClient::__construct
       */
      foreach (array(
         'client_options' => array(),
         'driver_options' => array(),      
      ) as $key=>$val) {
         $param[$key] = $val;
         if (!empty($options[$key])) {
            if (is_array($options[$key])) {
               $param[$key] = $options[$key];
            }
         }
      }
      
      /**
       * @var int $ping_offset determine / sanitize ping_offset value
       */
      $ping_offset=self::default_ping_offset;
      if (!empty($param['ping_offset'])) {
         if (is_numeric($param['ping_offset'])) {
            $ping_offset = (int) $param['ping_offset'];
         }
      }
      
      /**
       * @var string $hash client hash
       */
      $hash = array('server'=>$server);
      foreach (
         array('client_options','driver_options') 
         as $key
      ) if (!empty($param[$key])) $hash[$key] = $param[$key];
      $hash = md5(json_encode($hash));
//       var_dump($param);
//       die('mongo driver die');
      ////\flat\core\debug::dump($hash,"client hash");
      /**
       * @var bool $new determine if need to create a new \MongoClient
       *    (establish new connection(s))
       */
      $new = false;
      if (isset(self::$_client[$hash])) {
         ////\flat\core\debug::msg("client exists... checking if need reconnect");
         $client = self::$_client[$hash];
         
         $ping = null;
         if (!empty(self::$_client_ping[$hash])) {
            $ping = self::$_client_ping[$hash];
         } else {
            $conn = self::$_client[$hash]->getConnections();
            if (empty($conn)) {
               //\flat\core\debug::msg("no array returned from \\MongoClient->getConnections()");
               $new = true;
            } else {
               $most_recent_ping = 0;
               foreach ($conn as $c) {
                  if (isset($conn['connection'])) {
                     if (isset($conn['last_ping'])) {
                        if (
                           empty($most_recent_ping) ||
                           ($conn['last_ping']>$most_recent_ping)
                        ) {
                           $most_recent_ping = $conn['last_ping'];
                        }
                     } else {
                        //\flat\core\debug::dump($c,"no mongo last_ping");
                        
                     }
                  } else {
                     //\flat\core\debug::dump($c,"no 'connection' assoc key array returned from \\MongoClient->getConnections()");
                  }
               }
               if (!empty($most_recent_ping)) $ping = $most_recent_ping;
            }
         }
         $new_ping = false;
         if (empty($ping)) {
            $new_ping = true;
            //\flat\core\debug::msg("no last ping determined, must try ping command");
         } else
         if (((time()+$ping_offset) - ($ping)) > self::client_ping_expire) {
            $new_ping = true;
            ////\flat\core\debug::msg("ping expired, must try ping command");
         }
         if ($new_ping) {
            ////\flat\core\debug::msg("doing ping command...");
            try {
               $doc = $client->selectDB(self::test_collection)->command(array("ping"=>1));
               if (empty($doc)) {
                  ////\flat\core\debug::msg("empty response for mongo ping command");
                  $new = true;
               } else {
                  ////\flat\core\debug::dump($doc,"mongo ping response");
                  if (isset($doc['ok'])) {
                     if ($doc['ok']!=1) {
                        //\flat\core\debug::dump($doc['ok'],"'ok' field not '1' in response");
                        $new = true;
                     }
                  } else {
                     ////\flat\core\debug::msg("missing 'ok' field in response");
                     $new = true;
                  }
               }
            } catch (\Exception $e) {
               ////\flat\core\debug::dump($e,"ping failed");
               $new = true;
            }
         }         
      } else {
         $new = true;
      }

      /**
       * @var \MongoClient[] \flat\db\driver\mongo::$_client
       *    establish new connection(s) as determined above
       */
      if ($new) {
         /**
          * @uses int $param['connect_attempts'] (optional) number of reconnection attempts.
          * @uses \flat\db\driver\mongo::connect_attempts default
          */
         ////\flat\core\debug::msg("reconnecting");
         $attempts=self::connect_attempts;
         if (!empty($param['connect_attempts'])) {
            if (is_numeric($param['connect_attempts'])) {
               $attempts = (int) $param['connect_attempts'];
            }
         }         
         $success = false;
         $last_ex = null;
         $first_ex = null;
         for ($i=0;$i<$attempts;$i++) {
            try {
               self::$_client[$hash] = new \MongoClient(
                  $server,
                  $param['client_options'],
                  $param['driver_options']
               );
               $success = true;
               break 1;
            } catch (\MongoConnectionException $e) {
               ////\flat\core\debug::dump($e,"connection error: $i");
               //throw $e;
               if (!$first_ex) $first_ex = $e;
               $last_ex = $e;
            }
         }
         if (!$success) {
            $msg = "failed after $attempts attempts";
            if ($first_ex!==null) {
               $msg .= " (msg='".$first_ex->getMessage()."'), opt=".json_encode($param['client_options']);
            }
            throw new mongo\db\exception\connect_failure($msg);
         }
      } else {
         ////\flat\core\debug::dump($ping,"existing connection ok");
      }
      
      self::$_client_ping[$hash] = time()+$ping_offset;
      return self::$_client[$hash];
      
   } /*end function mongo::load_client()*/
   
   const connect_attempts = 5;
   /**
    * @return \MongoDB
    * @param string $name (optional) database name
    * @param \MongoClient $client (optional) specify MongoClient client. 
    *    by default use client is mongo::get_client() without any args.
    * 
    * @uses \flat\db\driver\mongo\db\explicit::get_client_db()
    *    if $name empty, uses this interface if child class has it 
    * 
    * @see \flat\db\driver\mongo::get_client() uses \MongoClient as cached
    * 
    * @throws \flat\db\driver\mongo\db\exception\bad_interface if 
    *    \flat\db\driver\mongo\db\explicit interface does not return
    *    non-empty string
    * 
    * @throws \flat\db\driver\mongo\db\exception\bad_param if given 
    *    non-empty string
    * 
    * 
    */
   public function get_db($name="",\MongoClient $client=NULL) {
      if (empty($name)) {
         if ($this instanceof \flat\db\driver\mongo\db\explicit) {
            $name = $this->get_client_db();
            if (empty($name) || (!is_string($name))) {
               throw new \flat\db\driver\mongo\db\exception\bad_interface(
                  '\flat\db\driver\mongo\db\explicit',
                  get_called_class(),                  
                  "get_client_db",
                  "must return non empty string"
               );
            }   
         } else {
            throw new \flat\db\driver\mongo\db\exception\bad_param(
               "name",
               "cannot be empty unless child class has ".
               '\flat\db\driver\mongo\db\explicit interface'   
            );
         }
      }
      if (empty($client)) $client =$this->get_client();
      return $client->selectDB($name);
   }

   /**
    * retrieve \MongoClient object
    * 
    * @param string $server (optional) mongo server connection string
    * 
    * @see \flat\db\driver\mongo::load_client()
    * 
    * @return \MongoClient
    * 
    */
   public function get_client($server="",array $options=NULL) {
      
      /**
       * @var string $server 
       *    sanitize and determine server connection string as appropriate
       * @uses \flat\db\driver\mongo\client\trivial_connection
       *    if $this class has trivial_connection interface, server string
       *    is just hostname:port 
       */
      if (!is_string($server)) $server = "";
      if (empty($server)) {
         if ($this instanceof \flat\db\driver\mongo\client\trivial_connection) {
            $server = "mongodb://".$this->get_client_host() .
               ":".$this->get_client_port();     
         } else {
            if ($this instanceof \flat\db\driver\mongo\client\server_string) {
               $server = $this->get_client_server_string();
            }
         }
      }
      
      $opt['client_options'] = ['connect'=>TRUE];
      
      if (isset($options['client_options'])) {
         $opt['client_options'] = $options['client_options'];
      } else {
         if ($this instanceof \flat\db\driver\mongo\client\options) {
            
            if (is_array($arr = $this->get_client_options())) {
               //$opt['client_options'] = $arr;
               foreach ($arr as $key=>$val) {
                  $opt['client_options'][$key] = $val;
               }
            }
         }
      }
      
      $opt['driver_options'] = NULL;
      
      if (isset($options['driver_options'])) {
         $opt['driver_options'] = $options['driver_options'];
      } else {
         if ($this instanceof \flat\db\driver\mongo\client\driver_options) {
            if (is_array($arr = $this->get_client_driver_options())) $opt['driver_options'] = $arr;
         }
      }
//       var_dump($opt);
//       die($server);
      return self::load_client($server,$opt);
      
   }

   /**
    * connect to database
    * 
    * @param array $param (optional) set connection paramters
    * 
    * @return \MongoClient
    * 
    * @throws \MongoConnectionException on connection failure
    * 
    * @see \flat\db\driver
    *    part of driver interface
    * 
    * @see \flat\db\driver\mongo::get_client()
    *    abstraction to get_client(). establishes connection
    */
   public function connect(array $param=NULL) {
      /**
       * prepare server string and options
       * @uses string $param['server'] server connection string
       * @uses array $param['client_options'] \MongoClient options 
       *    (same as 2nd argument to \MongoClient::__construct())
       * @uses array $param['driver_options'] \MongoClient driver options 
       *    (same as 3rd argument to \MongoClient::__construct())
       */
      $server = "";
      if (isset($param['server'])) $server = $param['server'];
      $options = array();
      foreach (array(
         'driver_options',
         'client_options',
      ) as $key) if (isset($param[$key])) $options[$key]=$param[$key];
      return $this->get_client($server,$options);
   }

}











