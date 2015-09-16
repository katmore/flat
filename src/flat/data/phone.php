<?php
namespace flat\data;
class phone extends \flat\data 
   implements \flat\data\ready 
{
   /**
    * @var string
    *    extension derived from $value, if any.
    */
   public $extension;
   /**
    * @var string
    *    zone derived from $value, if any.
    */
   public $zone_code;
   /**
    * @var string
    *    country code derived from $value, if any.
    */
   public $country_code;
   /**
    * @var string
    *    area code derived from $value, if any.
    */
   public $area_code;
   /**
    * @var string
    *    notation style used in formatting value.
    *    possible values are:
    *       'all-dash-namp','braket-dash-nanp','E.123'
    *    examples:
    *       'all-dash-namp' : 800-555-1234
    *       'braket-dash-nanp' : (800) 555-1234
    *       'intl' : +1 800 555 1234
    *       'arpa' : 
    *       
    */
   public $notation;
   /**
    * @var string
    *    phone number formatted as specified by 'format' field
    */
   public $string;
   /**
    * @var string
    *    same as 'string' field but stripped of all non-numeric chars
    */
   public $numeric;
   /**
    * @var string
    *    phone number to format
    */
   protected $value;
   /**
    * @var string
    *    original phone string before any formatting / structuring
    */
   public $original;
   /**
    * formats phone number to standard format.
    * 
    * @uses \flat\data\phone::$value value is used to determine the phone number to format
    * 
    * 
    */
   public function data_ready() {
      $phoneNumber = $this->value;
      $ext=null;
      if (false !==($extp = strpos($phoneNumber,'ext'))) {
         $phoneNumber = substr($phoneNumber,0,$extp);
         $ext = preg_replace('/[^0-9]/','',substr($phoneNumber,-1*$extp));
      } elseif (false !==($xp = strpos($phoneNumber,'x'))) {
         $phoneNumber = substr($phoneNumber,0,$xp);
         $ext = preg_replace('/[^0-9]/','',substr($phoneNumber,-1*$xp));
      }
      if (substr($phoneNumber,0,3)=="011") {
         $phoneNumber = substr($phoneNumber,3);
      }
       $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);
       $countryPrefix = null;
       
       if(strlen($phoneNumber) > 10) {
          $zone = $this->zone_code = substr($phoneNumber,0,1);
          $zoneP1 = substr($phoneNumber,1,1);
          $zoneP2 = substr($phoneNumber,2,1);
          if ($this->zone_code==1) {
             $this->country_code = 1;
          } else {
             //$countryPrefix = substr($phoneNumber,0,1);
             /*
              * @see https://en.wikipedia.org/wiki/List_of_country_calling_codes
              */
             if ($zone==2) {
                //20 is country code
                if ($zoneP1==0) {
                   $this->country_code == substr($phoneNumber,0,2);
                }else {//rest of codes in zone 2 are three-digit country codes
                   $this->country_code == substr($phoneNumber,0,3);
                }
             }elseif($zone==3) {
                //30,31,32,33,34,36,39 are two-digit country codes
                if (($zoneP1==0)||($zoneP1==1)||($zoneP1==2)||($zoneP1==3)||($zoneP1==4)||($zoneP1==6)||($zoneP1==9)) {
                   $this->country_code == substr($phoneNumber,0,2);
                }else {//rest of codes in zone 3 are three-digit country codes
                   $this->country_code == substr($phoneNumber,0,3);
                }
             }elseif($zone==4) {
                //40,41,43,44,45,46,47,48,49 are two-digit country codes
                if (($zoneP1==0)||($zoneP1==1)||($zoneP1==3)||($zoneP1==4)||($zoneP1==6)||($zoneP1==7)||($zoneP1==8)||($zoneP1==9)) {
                   $this->country_code == substr($phoneNumber,0,2);
                }else {//rest of codes in zone 4 are three-digit country codes
                   $this->country_code == substr($phoneNumber,0,3);
                }
             }elseif($zone==5) {
                //51,52,53,54,55,56,57,58 are two-digit country codes
                if (($zoneP1==1)||($zoneP1==2)||($zoneP1==3)||($zoneP1==4)||($zoneP1==5)||($zoneP1==6)||($zoneP1==7)||($zoneP1==8)) {
                   $this->country_code == substr($phoneNumber,0,2);
                }else {//rest of codes in zone 5 are three-digit country codes
                   $this->country_code == substr($phoneNumber,0,3);
                }
             }elseif($zone==6) {
                //60,61,62,63,64,65,66 are two-digit country codes
                if (($zoneP1==0)||($zoneP1==1)||($zoneP1==2)||($zoneP1==3)||($zoneP1==4)||($zoneP1==5)||($zoneP1==6)) {
                   $this->country_code == substr($phoneNumber,0,2);
                }else {//rest of codes in zone 6 are three-digit country codes
                   $this->country_code == substr($phoneNumber,0,3);
                }
             }elseif($zone==7) {
                //73,74,76,78 can indicate a zone 7 country codes longer than one-digit
                $over1 = null;
                if (($zoneP1==3)||($zoneP1==4)||($zoneP1==6)||($zoneP1==8)) {
                   if (substr($phoneNumber,0,4)==7365) {
                      //crimea
                      $over1 = substr($phoneNumber,0,4);
                   } else
                   if (substr($phoneNumber,0,4)==7426) {
                      //jewish oblast
                      $over1 = 7426;
                   } else
                   if (($zoneP1==6) || ($zoneP1==7)) {
                      //kazakhstan
                      $over1 = substr($phoneNumber,0,4);
                   } else
                   if ((substr($phoneNumber,0,4)==7840)||(substr($phoneNumber,0,4)==7940)) {
                      //abkhazia
                      $over1 = substr($phoneNumber,0,4);
                   } else
                   if (substr($phoneNumber,0,4)==7869) {
                      //sevastopol
                      $over1 = substr($phoneNumber,0,4);
                   }
                }
                if (!empty($over1)) {
                   $this->country_code = $over1;
                } else {
                   $this->country_code = 7;
                }
             }elseif($zone==8) {
                //81,82,84,86 are always two-digit country codes
                if (($zoneP1==1)||($zoneP1==2)||($zoneP1==4)||($zoneP1==6)) {
                   $this->country_code == substr($phoneNumber,0,2);
                } else {//rest of codes in zone 8 are three-digit country codes
                   $this->country_code == substr($phoneNumber,0,3);
                }
             }elseif($zone==9) {
                //turkey
                if ($zoneP1==0) {
                   
                } else 
                if (($zoneP1==1)||($zoneP1==2)||($zoneP1==4)||($zoneP1==6)) {
                   //91,92,93,94,95,98 are always two-digit country codes
                   $this->country_code == substr($phoneNumber,0,2);
                } else
                if ($zoneP1==9) { //99x
                   if (substr($phoneNumber,0,3)==995) {
                      if (substr($phoneNumber,0,5)==99534) {
                         //south ossetia
                         $this->country_code == substr($phoneNumber,0,5);
                      } else
                      if (substr($phoneNumber,0,5)==99534) {
                         //abkhazia
                         $this->country_code == substr($phoneNumber,0,5);
                      } else {
                         //georgia
                         $this->country_code == substr($phoneNumber,0,3);
                      }
                   } else { //rest of 99x are three-digit country codes
                      $this->country_code == substr($phoneNumber,0,2);
                   }
                }else {//rest of codes in zone 9 are three-digit country codes
                   $this->country_code == substr($phoneNumber,0,3);
                }
             }
          }
          
       } else
       if(strlen($phoneNumber) == 10) {
          $us=true;
           $areaCode = substr($phoneNumber, 0, 3);
           $nextThree = substr($phoneNumber, 3, 3);
           $lastFour = substr($phoneNumber, 6, 4);
   
           $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
       }
       else if(strlen($phoneNumber) == 7) {
          $us=true;
           $nextThree = substr($phoneNumber, 0, 3);
           $lastFour = substr($phoneNumber, 3, 4);
   
           $phoneNumber = $nextThree.'-'.$lastFour;
       }
       if ($us) {
          $this->country_code = 1;
       } else {
          if (!empty($countryCode)) {
             $this->country_code = $countryCode;
          }
       }
       
   
       return $phoneNumber;
   }
}