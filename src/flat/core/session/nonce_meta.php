<?php
namespace flat\core\session;
class nonce_meta extends \flat\data {
   /**
    * @var bool true if nonce is consumed
    *    and therefore un-usable 
    */
   public $consumed;
   
   /**
    * @var string ISO 8601 timestamp of creation
    */
   public $created;
   
   /**
    * @var mixed | null optional data associated with this nonce
    */
   public $data;
   
   /**
    * @var int | null optional number of seconds from creation time
    *    until the nonce is no longer valid
    */
   public $ttl;
}