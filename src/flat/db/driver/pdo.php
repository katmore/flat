<?php
/**
 * \flat\db\driver\mongo class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver;
use flat\core\uuid;
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
abstract class pdo extends \flat\db implements \flat\db\driver {
   
   private $_param;
   /**
    * provides a PDO object based on previously obtained connection parameters.
    *    uses a cached PDO object, or establishes a new one as needed.
    * 
    * @param bool $force_new Optional, default false. When this value is (bool) true, a new PDO object 
    *    is always established.
    * 
    * @return \flat\db\driver\pdo\pdo
    * 
    * @uses \flat\db\driver\pdo::connect()
    * @uses flat\db\driver\pdo::_load_pdo()
    * 
    * @throws \flat\db\driver\pdo\exception\missing_connection_params
    */
   public function get_pdo($force_new=false) {
      if (!$this->_param) throw new \flat\db\driver\pdo\exception\missing_connection_params();
      $param = $this->_param;
      if ($force_new) $param['force_new'] = true;
      return pdo::_load_pdo($param);
   }
   /**
    * @var \PDO[] $_pdo array of cached pdo objects, 
    *    cached by hash of connection parameters
    * @uses \flat\db\driver\pdo::load_pdo()
    */
   private static $_pdo;
   private static function _param_to_hash(array $param=NULL) {
      if (!$param) return crc32("");
      return crc32(json_encode(self::_param_to_pdo_param($param)));
   }
   private static function _param_to_pdo_param(array $param=NULL) {
      
      $pdo = array(
         'dsn'=>'',
         'username'=>NULL,
         'password'=>NULL,
         'options'=>NULL
      );
      
      if ($param) foreach ($pdo as $key=>&$val) {
         if (isset($param[$key])) {
            if (is_string($param[$key])) {
               $val  = trim($param[$key]);
            } else {
               $val=$param[$key];
            }
         }
      }
      
      return $pdo;
   }
   
   /**
    * prepares a \PDOstatement, optionally executing and or fetching results
    * 
    * @return \PDOStatement|array
    * 
    * method call signatures: (normal or assoc array)
    *    statement(array $criteria,$table_name,$table_alias,array $criteria_hash,array $join_hash,array $column_hash)
    *       OR
    *    statement(array(
         'criteria'=>NULL, //assoc array of criteria values with key correlating to $criteria_hash key
         'criteria_hash'=>NULL, //assoc array keys correlate to possible $criteria keys to accept, value correlates to table_alias.column
         'table_name'=>NULL, //table for SELECT clause
         'table_alias'=>NULL, //table alias for SELECT clause
         'join_clause'=>NULL, //join clause as needed
         'join_hash'=>NULL,
         'column_hash'=>NULL,
         'limit'=>NULL,
         'skip'=>NULL,
         'options'=>NULL,
         ))
    * 
    * @param scalar[] $criteria assoc array of criteria values with key correlating to $criteria_hash key
    * @param string[]|array[] $criteria_hash assoc array keys correlate to 
    *    possible $criteria keys to accept, value correlates to table_alias.column
    * @param string $table_name
    * @param string $table_alias
    * @param string $join_clause
    * @param string[] $join_hash assoc array of potential SQL JOIN claus relationships
    * @param string[] $column_hash assoc array of requied columns
    * @param int $limit SQL LIMIT
    * @param int $skip SQL limit
    * @param string[] $options include:
    *    'execute'=>if non-empty value, executes prepared statement before return,
    *    'fetch'=>if non-empty value, executes and fetches assoc array of full resultset,
    *    'fetch_callback'=>if (callable) value, executes callback for each row
    *       in resultset with signature callback(array $assoc_row).
    * 
    * @throws \flat\db\driver\pdo\exception\bad_criteria sanity check failure on criteria value
    * @throws \flat\db\driver\pdo\exception\bad_param other sanity check failure 
    * @throws \PDOException database issue
    * @throws \flat\db\driver\pdo\exception\not_found
    */
   //public function get_statement(array $criteria,array $criteria_hash,$table_name,$table_alias,array $column_hash,$join_clause,array $join_hash,$limit=NULL,$skip=0,array $options=NULL) {
   public function load_statement(array $param) {
      
      $arg = func_get_args();
      
      $param= new \flat\db\driver\pdo\statement\rules();
      
      /*
       * determine if it's 1 argument call signature
       *    or multiple argument signature
       */
      if (count($arg)==1) {
         /*
          * if 1 argument... determine if it's 
          *    rules object OR assoc array
          */ 
         if (is_a($arg[0],"\\flat\\db\\driver\\pdo\\statement\\rules")) {
            $param = new \flat\db\driver\pdo\statement\rules($arg[0]);
         } else {
            foreach ($param as $key=>&$val ) {
               if (isset($arg[0][$key])) $val = $arg[0][$key];
            }
         }
      } else {
         $i=0;
         foreach ($param as $key=>&$val) {
            if (isset($arg[$i][$key])) $val = $arg[$i][$key];
            $i++;
         }
      }
      
      return new \flat\db\driver\pdo\statement($this->get_pdo(),$param);
   }
   
   final protected static function _load_pdo(array $param=NULL) {
      /*
       * map parameters
       */ 
      $pdo = self::_param_to_pdo_param($param);
      
      /*
       * compute param hash
       */
      if (isset($param['force_new']) && $param['force_new']===true) {
         $hash = null;
         $pdo['options'][\PDO::ATTR_PERSISTENT]=false;
      } else {
         $hash = self::_param_to_hash($pdo);
      }
      
      /**
       * return already established connection
       * @todo perform test on connection to see if timed or errored out
       */
      if ($hash && isset(self::$_pdo[$hash])) return self::$_pdo[$hash];
      
      try {
         /*
          * create new pdo object
          */
         $pdo = new pdo\pdo(
            $pdo['dsn'], 
            $pdo['username'],
            $pdo['password'],
            $pdo['options']
         );
      } catch (\Exception $e) {
         throw new \flat\db\driver\pdo\exception\connect_error($e);
      }
      
      $pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
      if ($hash) self::$_pdo[$hash] = $pdo;
      return $pdo;
   }
   /**
    * establishes db connection, return PDO object
    * 
    * @param array $param assoc array of params:
    *    string $param['dsn'] dsn like 'mysql:host=localhost;dbname=testdb',
    *    string $param['username'] username
    *    string $param['password'] password
    *    array $param['options'] A key=>value array of PDO driver-specific connection options.
    *    bool $param['force_new'] If true, a new PDO object is instantiated even when connection params are 
    *       identical to previously instantiated PDO object.
    * 
    * @uses \flat\db\driver\pdo\connection_params
    * @return \flat\db\driver\pdo\pdo
    */
   final public function connect(array $param=NULL) {
      
      if ($this instanceof \flat\db\driver\pdo\connection_params) {
         $param = self::_param_to_pdo_param();
         foreach ($param as $k=>&$v) {
            $method = "get_pdo_".$k;
            $v = $this->$method();
         }
      }
      $force_new = false;
      if (!empty($param['force_new']) && $param['force_new']===true) {
         unset($param['force_new']);
         $force_new = true;
      }
      $this->_param = $param;
      return $this->get_pdo($force_new);
      
   }

   public function command(array $param=NULL) {

   }
}







