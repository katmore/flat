<?php
namespace flat\core\exception;

trait controller {
   public function _value_to_code($value) {
      /*
       * generate checksum by serializing our crc array
       */
      $code_suffix = sprintf("%u",crc32(json_encode($value)));
   
      /*
       * shorten the base of the code
       *    (knowingly ruin the checksum by knocking all but 3 places)
       */
      $code_suffix = substr($code_suffix,0,3);
      //900000000
      $code = (int) substr($this->_derive_code(),0,6).$code_suffix;
      return $code;
   }
   public function _derive_code($code_offset=900000000) {
      /*
       * prepare an array to serialize into a string
       *    start with caller trace array
       */
      $crc=$this->getTrace();
       
      /*
       * use only 0th caller from trace
       */
      if (isset($crc[0])) {
         $crc = $crc[0];
      }
       
      /*
       * to keep code same in more conditions...
       *    remove trace's args data
       */
      if (isset($crc['args'])) unset($crc['args']);
       
      /*
       * add class name
       */
      $crc['name'] = get_class($this);
       
      /*
       * add caller file
       */
      $flatfile = basename( $this->getFile());
   
      $crc['file'] = basename( $this->getFile());
       
      /*
       * generate checksum by serializing our crc array
       */
      $code = sprintf("%u",crc32(json_encode($crc)));
       
      /*
       * shorten the base of the code
       *    (knowingly ruin the checksum by knocking all but 5 places)
       */
      $code = (int) substr($code,0,5);
       
      /*
       * add offset to the code to indicate it was derived
       */
      $code += $code_offset;
       
      /*
       * to indicate namespace prefix of caller
       *    add 10 million indicates any namespace prefix
       *    other than found in $flatind array below
       *       each member within $flatind offsets indication an extra +10 mil
       */
      $code += 10000000;
      $flatind = array(
         "flat\\api",
         "flat\\cli",
         "flat\\core",
         "flat\\data",
         "flat\\fsm",
         "flat\\listener",
         "flat\\meta",
         "flat\\route",
         "flat\\theme",
         "flat\\view",
      );
      /*
       * calculate a 10s of millions place
       */
      $ind=0;
      foreach ($flatind as $pre) {
         $ind+=10000000;
         $len = strlen($pre);
         if(substr($crc['name'],0,$len) == $pre) {
            $code += ($ind);
            break 1;
         }
      }
      return $code;
   }

}
