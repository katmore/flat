<?php
/**
 * \flat\db\driver\mongo\crud class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver\mongo;

use \flat\data\generic;
use \flat\data\flat\data;
use \flat\db\driver\mongo\db\exception;

/**
 * CRUD ops on mongo
 *    (CREATE, READ, UPDATE, DELETE)
 * 
 * @package    flat\__SUB_PACKAGE__
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class crud extends \flat\db\driver\mongo 
   implements 
   \flat\db\driver\mongo\op\read
   ,\flat\db\driver\mongo\op\update   
   ,\flat\db\crud
{
   
   /**
    * removes documents from collection according to given critera
    *    and returns count of deleted records
    * @return void | int
    *
    * @param array $param assoc array of parameters:
    *    array $param['key'] (required) field/value criteria for update.
    *    bool $param['count'] (optional) set to true to return number of modified records.
    *    string $param['collection_ns'] (optional) specify a collection namespace suffix
    *
    * @see \flat\db\driver\mongo\op\delete part of the mongo read interface
    * @throws \flat\db\driver\mongo\record\bad_param
    * @throws \flat\db\driver\mongo\record\not_modified
    *
    * @see \flat\db\driver\mongo::get_collection() for other possible exceptions
    * @see \MongoCollection::update() for other possible exceptions
    */
   public function delete(array $param) {
      if (!empty($param['collection_ns'])) {
         $col = $this->get_ns_collection($param['collection_ns']);
      } else {
         $col = $this->get_collection();
      }
      
      if (!empty($param['key'])) {
         if (is_array($param['key'])) {
            $find = $param['key'];
            $res = $col->remove($find,['w'=>1]);
            if (!empty($param['count']) && isset($res['n'])) {
               return $res['n'];
            }
            if (!empty($res['n'])) return;
            throw new \flat\db\driver\mongo\record\not_modified("no documents matched given key");
         } else {
            throw new \flat\db\driver\mongo\record\bad_param(
                  "key","must be array"
            );
         }
      }
   }
   
   /**
    * creates or upserts record in collection with given data.
    *    returns the _id field.
    * 
    * @return mixed
    * 
    * @param array|object $data document to be inserted
    * @param array $option (optional) assoc array of options:
    *    string $option['collection_ns'] specify a collection namespace suffix.
    *    string $option['upsert'] upsert criteria: inserts document if this criteria
    *       does not yet exist in collection, otherwise, updates the fields contained in $data
    *       on document with matched criteria.
    * 
    * @see \flat\db\driver\mongo\op\create part of the mongo create interface
    * 
    * @see \flat\db\driver\mongo::get_collection() for possible exceptions
    * @see \MongoCollection::insert() for possible exceptions
    * 
    */
   public function create( $data,array $option=NULL) {
      
      $upsert = NULL;
      if (isset($option['upsert'])) {
         if (is_array($option['upsert'])) {
            $upsert = $option['upsert'];
         }
      }

      $col_ns = null;
      
      if (isset($option['sub_collection']) && is_string($option['sub_collection'])) {
         $col_ns = $option['sub_collection'];
      } else
      if (!empty($param['collection_ns'])) {
         $col_ns = $param['collection_ns'];
      } 
      
      if (!empty($col_ns)) {
         $col = $this->get_ns_collection($col_ns);
      } else {
         $col = $this->get_collection();
      }
      try {
         if (!is_object($data) && !is_array($data)) {
            throw new \flat\db\driver\mongo\record\bad_param(
               "data","must be array or object"
            );
         }
         if ($data instanceof \flat\data) {
            $data_create = $data->get_as_assoc();
         } else {
            $data_create = (new \flat\data\generic($data))->get_as_assoc();
         }
         if ($upsert!==NULL) {
            $doc = $col->findAndModify(
               $upsert,
               $data_create,
               [ '_id' => 1 ],
               [
                  'upsert'=>true,
                  'update'=>$data_create,
                  'new'=>true
               ]
            );
            return $doc['_id'];
         } else {
            
            $w = $col->insert($data_create,array('w'=>1));
            return $data_create['_id'];
         }
         
      } catch (\MongoCursorException $e) {
         if ($e->getCode()==11000) {
            $index = "{unknown}";
            $msg = $e->getMessage();
            
            /*
             * parse the index name from the error message.
             * 
             * solution using preg_match as suggested from 
             *    http://stackoverflow.com/questions/13557894/php-preg-match-get-in-between-string
             */
            $srch1 = 'index:';
            $srch2 = 'dup key:';
            preg_match("/(?<=$srch1).*?(?=$srch2)/", $msg, $match);
            if (count($match)) $index = trim(implode(", ",$match));
            throw new \flat\db\driver\mongo\collection\duplicate_key(
               $col->getName(),
               $index
            );
         } else {
            throw $e;
         }
      }
      
   }
   /**
    * updates collection according to given critera
    *    optionally returns count of modified records (see $param['record'])
    * 
    * @return void|int
    * 
    * @param array $param assoc array of parameters:
    *    array $param['key'] (required) field/value criteria for update.
    *    mixed $param['record'] (optional) replace record with this value.
    *    object|array $param['data'] (optional) only update fields as given in object or assoc array.
    *    bool $param['count'] (optional) set to true to return number of modified records.
    *    bool $param['not_modified_exception'] (optional) set to true to throw exceptions when
    *       no records are affected by operation.
    *    string $param['collection_ns'] (optional) specify a collection namespace suffix
    *    
    * @see \flat\db\driver\mongo\op\update part of the mongo read interface
    * @throws \flat\db\driver\mongo\record\bad_param
    * @throws \flat\db\driver\mongo\record\not_modified
    * 
    * @see \flat\db\driver\mongo::get_collection() for other possible exceptions
    * @see \MongoCollection::update() for other possible exceptions
    */
   public function update(array $param=NULL) {
      
      if (!empty($param['collection_ns'])) {
         $col = $this->get_ns_collection($param['collection_ns']);
      } else {
         $col = $this->get_collection();
      }

      if (!empty($param['key'])) {
         if (is_array($param['key'])) {
            $find = $param['key'];
            if (!empty($param['record'])) {
               $record = $param['record'];
               $col->update($find,$record);
               if (!empty($param['count']) || !empty($param['not_modified_exception'])) {
                  $count_only = true;
                  if (!empty($param['not_modified_exception'])) $count_only = false;
                  return $this->get_affected_records(array('count_only'=>$count_only));
               }
            } else
            if (!empty($param['data'])) {
               $data = $param['data'];
               if (!is_object($data) && !is_array($data)) {
                  throw new \flat\db\driver\mongo\record\bad_param(
                     "data","must be array or object"
                  );
               }
               $col->update($find,array('$set'=>$data));
               if (!empty($param['count']) || !empty($param['not_modified_exception'])) {
                  $count_only = true;
                  if (!empty($param['not_modified_exception'])) $count_only = false;
                  return $this->get_affected_records(array('count_only'=>$count_only));
               }           
            } else
            if (!empty($param['op'])) {
               $op = $param['op'];
               if (!is_object($op) && !is_array($op)) {
                  throw new \flat\db\driver\mongo\record\bad_param(
                     "op","must be array or object"
                  );
               }
               $col->update($find,$op);
               if (!empty($param['count']) || !empty($param['not_modified_exception'])) {
                  $count_only = true;
                  if (!empty($param['not_modified_exception'])) $count_only = false;
                  return $this->get_affected_records(array('count_only'=>$count_only));
               }           
            } else {
               throw new \flat\db\driver\mongo\record\not_modified(
                  "if given 'key' param, must have either a 'record' or 'data' param"
               );
            }
         } else {
            throw new \flat\db\driver\mongo\record\bad_param(
               "key","must be array"
            );
         }
      }   
   }
   const max_read_limit = 10000;
   const default_read_limit = 5;
   const default_read_limit_with_key = 1;
   /**
    * retrieve records from collection
    * 
    * @param array $param assoc array of parameters:
    * <ul>
    *    <li><b>array $param['key']</b> (optional) assoc field=>value find critera.</li>
    *    <li><b>int $param['limit']</b> (optional) max number of records to retrieve from resultset,
    *       ignored if less than 1.</li>
    *    <li><b>int $param['skip']</b> (optional) how many records to skip in resultset,
    *       ignored if less than 1.</li>
    *    <li><b>string[] $param['starts_with']</b> (optional) array( (string) field => (string) pattern )
    *       find records where given 'field' starts with 'pattern',
    *       ignored if field and pattern are not strings.</li>
    *    <li><b>string | \flat\data $param['record']</b> (optional) type of record to created for result
    *       members. "stdClass" puts each match is a \stdClass object. "assoc" puts each match
    *       into an associative array. if value is a (string) "\full\class\name" where it is the 
    *       full namespaced class name of a \flat\data child, puts each match into an instance of 
    *       given class. if value is a \flat\data object, each match is mapped into a clone of
    *       given object.</li>
    *    <li><b>string $param['collection_ns']</b> (optional) specify a collection namespace suffix.</li>
    *    <li><b>array $param['sort']</b> assoc array of document field and directions to sort.</li>
    *    <li><b>callback $param['callback']</b> optional callback for each result. if specified,
    *       return value is void. </li>
    * </ul>
    * @return array|array[]|void
    * @see \flat\db\driver\mongo\op\read part of the mongo read interface
    * 
    * @throws \flat\db\driver\mongo\record\not_found no record retrieved with given criteria
    * @throws \flat\db\driver\mongo\record\bad_param on parameter sanity check failure
    * 
    * @see \flat\db\driver\mongo::get_collection() for other possible exceptions
    * @see \MongoCollection::find() for other possible exceptions
    */
   public function read(array $param=NULL) {
      
      /*
       * if key param provided,
       *    return single record
       */
      $limit = self::default_read_limit;
      $find = array();
      $skip = 0;
      $result = "assoc";
      $sort = null;
      $col_ns = null;
      $callback = null;
      if (!empty($param['collection_ns'])) {
         $col_ns = $param['collection_ns'];
      }
      if (!empty($param['sort']) && is_array($param['sort'])) {
         $sort = $param['sort'];
      }
      if (isset($param['record'])) {
         $param['result'] = $param['record'];
      }
      if (!empty($param['result'])) {
         if (in_array($param['result'],['stdClass','assoc'])) {
            $result = $param['result'];
         } else {
            if (is_string($param['result']) && class_exists($param['result']) && is_a($param['result'],'\flat\data',true)) {
               $result = $param['result'];
            } else
            if (is_object($param['result']) && ($param['result'] instanceof \flat\data)) {
               $result = $param['result'];
            }
         }
      }
      //var_dump($result);die('db driver mongo crud');
      if (!empty($param['key'])) {
         if (is_array($param['key'])) {
            $limit = self::default_read_limit_with_key;
            $find = $param['key'];
         }
      }
      if (!empty($param['find'])) {
         if (is_array($param['find'])) {
            $limit = self::default_read_limit_with_key;
            $find = $param['find'];
         }
      }
      if (!empty($param['starts_with'])) {
         if (is_array($param['starts_with'])) {
            foreach ($param['starts_with'] as $f=>$s) {
               if (!empty($f) && !empty($s) && is_string($f) && is_string($s)) {
                  $search = preg_quote($s);
                  $find = array($f => array('$regex' => new \MongoRegex("/^$search/")));
                  $limit = self::default_read_limit;
               } else {
                  throw new \flat\db\driver\mongo\record\bad_param(
                     "starts_with",
                     "if given, must be assoc array( (string) field => (string) pattern ) ".
                     "having non-empty values"
                  );
               }
               break 1;
            }
         } else {
            throw new \flat\db\driver\mongo\record\bad_param(
               "starts_with","if given, must be assoc array"
            );
         }
      }
      if (!empty($param['limit'])) {
         if (is_int($param['limit'])) {
            if ($param['limit']<1) throw new \flat\db\driver\mongo\record\bad_param(
               "limit","if given, must be 1 or greater"
            );
            if ($param['limit']>self::max_read_limit) {
               throw new \flat\db\driver\mongo\record\bad_param(
                  "limit","if given, cannot be greater than ".self::max_read_limit
               ); 
            }           
            $limit = $param['limit'];
         }
      }
      if (!empty($param['skip'])) {
         if (is_int($param['skip'])) {
            if ($param['skip']<0) throw new \flat\db\driver\mongo\record\bad_param(
               "skip","if given, must be 0 or greater"
            );
            $skip = $param['skip'];
         } else {
            throw new \flat\db\driver\mongo\record\bad_param(
               "skip","if given, must be integer"
            );
         }
            
      }
      if (!empty($param['callback']) && is_callable($param['callback'])) {
         $callback = $param['callback'];
      }
      if (!$col_ns) {
         $col = $this->get_collection();
      } else {
         $col = $this->get_ns_collection($col_ns);
      }
      $cur = $col->find($find)->limit($limit);
      if ($sort) $cur->sort($sort);
      if (!$cur->count()) {
         if (!empty($param['count_only'])) {
            return 0;
         }
         throw new \flat\db\driver\mongo\record\not_found(
            "record not found"
         );
      }
      
      if ($skip>0) $cur->skip($skip);
      
      
      if (!empty($param['count_only'])) {
         if (!empty($param['limit']) || !empty($param['skip'])) {
            $foundOnly = true;
         } else {
            $foundOnly = false;
         }
         return (int) $cur->count($foundOnly);
      }
      /*
       * determine mapping type
       */
      if (is_string($result) && ($result=='stdClass' || $result == 'assoc')) {
         $type = $result;
      } else
      if (is_string($result)) {
         $type = "dataclass";
      } else {
         $type = "dataclone";
      }
      
      /*
       * map resultset to return value
       */
      if ($limit>1) {
         $arr = array();
         
         foreach ($cur as $doc) {
            if (isset($doc['_id'])) $doc['_id'] = (string) $doc['_id'];
            if ($type == 'stdClass') {
               $res = (object) $doc;
            } else
            if ($type == "assoc") {
               $res = (array) $doc;
            } else
            if ($type == "dataclass") {
               $res = new $result($doc);
            } else {
               $res = clone $result;
               $res->map($doc);
            }
            if ($callback) {
               $callback($res);
            } else {
               $arr[] = $res;
            }
         }
         if (!$callback) {
            return $arr;
         }
      } else {
         $doc = $cur->getNext();
         if (isset($doc['_id'])) $doc['_id'] = (string) $doc['_id'];
         if ($type == 'stdClass') {
            $res = (object) $doc;
         } else
         if ($type == "assoc") {
            $res = $doc;
         } else
         if ($type == "dataclass") {
            $res = new $result($doc);
         } else {
            $res = clone $result;
            $res->map($doc);
         }
         if ($callback) {
            $callback($res);
         } else {
            return $res;
         }
      }
   }      
   
   const load_default_limit = 10;
   const load_limit_ciel = 100;
   const load_start_tics = 1000;
   //const load_default_sort = ['created'=>-1];
   const load_default_sort_key = 'created';
   const load_default_sort_val = -1;
   
   /**
    * retrieves records with convenient defaults and options. options param provided
    *    for convenience as potential passthru param with another lib, api, etc. 
    * 
    * @return \flat\data[]
    * 
    * @param array $find criteria for record retrieval
    * @param array $opt (optional) assoc array of options:
    *    int $opt['skip'] default 0. number of records to skip in resultset.
    *       ignored if $opt['start'] is given.
    *    array $opt['start'] ignored by default. Specifies a key/value match for where starting point of 
    *       resultset begins. IT IS NOT THE NUMBER OF RECORDS TO SKIP. Note, that unlike 'skip', this option
    *       is ultimately processed on the client side, within this method.
    *    this is perfomed "client side" (within this method). 
    *    int $opt['limit'] default 10. maximum records to retrieve.
    *    string | \flat\data $opt['result'] specify result item data class or object to clone for
    *       result items. 
    * @param \flat\data $default_result_object object to use for recordset items when not specified in $opt['record'] param.
    * @param int $default_limit limit to use when not specified in $opt['limit'] param.
    * @param int $limit_ciel max allowed limit to specify in $opt['limit'] param.
    * @param array $default_sort default sorting criteria when not specified in $opt['sort'].
    * @param int $start_tics maximum iterations to match criteria in $opt['start'] param.

    */
   public function load(
      array $find,
      array $opt=null,
      \flat\data $default_result_object=null,
      $default_limit=self::load_default_limit,
      $limit_ciel=self::load_limit_ciel,
      $default_sort=null,
      $start_tics=self::load_start_tics
   ) {
      //use \actvp\db\metric\link\click;
      if (empty($default_sort)) {
         $default_sort = [self::load_default_sort_key=>self::load_default_sort_val];
      }
      $skip = 0;
      $start=null;
      $limit = $default_limit;
      $sort=null;
      if (empty($default_result_object)) $default_result_object = new generic();
      $result_object = $default_result_object;
      if (isset($opt['result'])) {
         $result_object = $opt['result'];
      } else if(isset($opt['record'])) {
         $result_object = $opt['record'];
      }
      if (!empty($opt['limit'])) {
         if (!is_int($opt['limit'])) {
            throw new exception\bad_param("limit","must be int");
         }
         if ($opt['limit'] < 1 || ($opt['limit'] > $limit_ciel)) {
            throw new exception\bad_param("limit","value must be between 1 and ".$limit_ciel);
         }
         $limit = $opt['limit'];
      }
      
      if (!empty($opt['skip'])) {
         if (!is_int($opt['skip'])) {
            throw new exception\bad_param("skip","must be int");
         }
         if ($opt['skip'] < 0) {
            throw new exception\bad_param("skip","value must be greater than 1");
         }
         $skip = $opt['skip'];
      }
      if (!empty($opt['start'])) {
         if (
            (!is_array($opt['start'])) ||
            (count($opt['start'])!=1)
         ) {
            throw new exception\bad_param("start","must be single element assoc array");
         }
         $field = array_keys($opt['start'])[0];
         if (is_int($field)) {
            throw new exception\bad_param("start","must be single element assoc array with non-numeric key");
         }
         $start = $opt['start'][$field];
      }
      if (isset($opt['sort'])) {
         if (!is_array($opt['sort'])) {
            throw new exception\bad_param("sort","must be assoc array");
         }
         $sort = $opt['sort'];
      }
      if (empty($sort)) {
         $sort=$default_sort;
      }
      if (empty($start)) {
         return $this->read([
            'result'=>$result_object,
            'find'=>$find,
            'sort'=>$sort,
            'skip'=>$skip,
            'limit'=>$limit,
         ]);
      } else {
         $result = [];
         $found=false;
         for($i=0;$i<$start_tics;$i++) {
            if (count($result)>=$limit) break 1;
            $record = $this->read([
               'result'=>$result_object,
               'find'=>$find,
               'sort'=>$sort,
               'skip'=>$i*$limit_ciel,
               'limit'=>$limit_ciel,
            ]);
            foreach ($record as $doc) {
               if (!$found && isset($doc[$field]) && ($doc[$field]==$start)) {
                  $found = true;
               }
               if ($found) $result[] = $doc;
            }
         }
         if (($count = count($result))>$limit) {
            for($i=0;$i<($count-$limit);$i++) array_pop($result);
         }
         return $result;
      }
   }
}















