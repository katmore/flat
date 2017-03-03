<?php
namespace flat\data;
class phone extends \flat\data
implements \flat\data\ready
{
    
   /**
    * @var string
    *    original phone string before any formatting / structuring
    */
   public $original;
    
   /**
    * @var string
    *    extension derived from $value, if any.
    */
   public $extension;
    
   /**
    * @var string
    *    NANP local number
    */
   public $local_number;
    
   /**
    * @var string
    *    NANP Station Code
    *    (last 4 digits) if phone number
    *    is from NANP country, (null) otherwise
    */
   public $station_code;
    
   /**
    * @var string
    *    E.164 subscriber number (same as NANP station code)
    */
   public $subscriber_number;
   /**
    * @var string
    *    NANP zone derived from $value, if any.
    */
   public $exchange_code;
    
   /**
    * @var string
    *    country code derived from $value, if any.
    */
   public $country_code;
    
   /**
    * @var string
    *    ITU zone
    */
   public $zone;
    
   /**
    * @var string
    *    area code derived from $value, if any.
    */
   public $area_code;
    
   /**
    * @var string e164 notation
    */
   public $e164;
    
   /**
    * @var string
    */
   public $e164_intl;
    
   /**
    * @var string
    */
   public $e164_natl;
    
   /**
    * @var string
    */
   public $e164_local;
    
   /**
    * @var E.164 Subscriber Number
    */
   public $e164_sn;
    
   /**
    * @var E.164 Country Code
    */
   public $e164_cc;
    
   /**
    * @var E.164 National Destination Code
    */
   public $e164_ndc;
    
   /**
    * @var string
    *    number expressed in standard NANP notation
    *       ex: (800) 555-1234
    *    if number is from NANP zone
    */
   public $nanp;
    
   /**
    * @var string
    *    notation style used in formatting value.
    *    possible values are:
    *       'NANP-natl', 'NANP-local', 'E.123'
    *    examples:
    *       'NANP-natl' : (800) 555-1234
    *       'NANP-local' : 555-1234
    *       'E.123-intl' : +1 800 555 1234
    */
   public $notation;

   /**
    * @var string
    *    same as 'string' field but stripped of all non-numeric chars
    */
   public $numeric;

   /**
    * @var string
    *    phone number formatted as specified by 'format' field
    */
   public $string='';

   /**
    * @var string
    *    phone number to format
    */
   protected $value;
    
   public function __toString() {
      return $this->string;
   }
    
   /**
    * formats phone number to standard format.
    *
    * @uses \flat\data\phone::$value value is used to determine the phone number to format
    *
    *
    */
   public function data_ready() {

      if (is_string($this->original) && !empty($this->original)) {
         $phoneNumber = $this->value =$this->original;
      } elseif (is_string($this->value) && !empty($this->value) ) {
         $this->original = $phoneNumber = $this->value;
      } else {
         return;
      }

      $ext=null;
      if (false !==($extp = strpos(strtolower($phoneNumber),'ext'))) {
         $phoneNumber = substr($phoneNumber,0,$extp);
         $this->extension = $ext = preg_replace('/[^0-9]/','',substr($phoneNumber,-1*$extp));
      } elseif (false !==($xp = strpos($phoneNumber,'x'))) {
         $phoneNumber = substr($phoneNumber,0,$xp);
         $this->extension = $ext = preg_replace('/[^0-9]/','',substr($phoneNumber,-1*$xp));
      }
      if (substr($phoneNumber,0,3)=="011") {
         $phoneNumber = substr($phoneNumber,3);
      }
      $this->numeric = $phoneNumber = preg_replace('/[^0-9]/','',$phoneNumber);
       
      if(strlen($phoneNumber) > 10) {
         $this->zone = $zone = substr($phoneNumber,0,1);
         $zoneP1 = substr($phoneNumber,1,1);
         $zoneP2 = substr($phoneNumber,2,1);
         if ($zone==1) {
             
            $this->country_code = 1;
            $phoneNumber = substr($phoneNumber,1);
            $this->extension = substr($phoneNumber,(strlen($phoneNumber)-10)*-1);
            if (empty($this->extension)) $this->extension = null;
            $phoneNumber = substr($phoneNumber,0,10);
             
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
            } /*end zone/country code determination*/
             
            $this->e164_ndc = substr($phoneNumber,strlen($this->country_code));
            $this->e164_cc = $this->country_code;
            $this->e164 = "+{$this->country_code}{$this->e164_ndc}";
             
            if (empty($this->e164_intl)) {
               $this->e164_intl = "+{$this->country_code} {$this->e164_ndc}";
            }
             
            if (empty($this->string)) {
               $this->string = $this->e164_intl;
            }
             
         } /*endif $phoneNumber is NOT zone 1 (NANP)*/

      }/*endif $phoneNumber longer than 10*/
       
      if(strlen($phoneNumber) == 10) {

         $areaCode = substr($phoneNumber, 0, 3);
         $nextThree = substr($phoneNumber, 3, 3);
         $lastFour = substr($phoneNumber, 6, 4);
          
         $this->nanp = $phoneNumber = '('.$areaCode.') '.$nextThree.'-'.$lastFour;
         //$this->nanp_dash = "$areaCode-$nextThree-$lastFour";
         $this->exchange_code = $nextThree;
         $this->zone = '1';
          
         $this->e164_cc = $this->country_code = '1';
         $this->notation = 'NANP-natl';
         $this->area_code = $areaCode;
         $this->e164 = "+1$areaCode$nextThree$lastFour";
         $this->e164_sn = $this->station_code = $this->subscriber_number = $lastFour;
         $this->e164_intl = "+1 $areaCode $nextThree $lastFour";
         $this->e164_natl = "{$this->area_code} {$this->exchange_code} $lastFour";
         $this->e164_local = "($areaCode) $nextThree $lastFour";
         $this->local_number = "$nextThree-$lastFour";;
         $this->e164_ndc = "$nextThree$lastFour";
      }
      else if(strlen($phoneNumber) == 7) {
         $this->zone = '1';
          
         $this->e164_cc = $this->country_code = '1';
         $nextThree = substr($phoneNumber, 0, 3);
         $lastFour = substr($phoneNumber, 3, 4);
          
         $this->local_number = $this->nanp = $phoneNumber = $nextThree.'-'.$lastFour;
         $this->exchange_code = $nextThree;
         $this->notation = 'NANP-local';
         $this->e164_sn = $this->station_code = $this->subscriber_number = $lastFour;
         $this->e164_ndc = "$nextThree$lastFour";
      }
       
      if (!empty($phoneNumber)) {
         $this->string = $phoneNumber;
      }
       
      if (!empty($this->extension)) {
         if (!empty($this->nanp)) {
            $this->string .= " x{$this->extension}";
         } else {
            $this->string .= " ext. {$this->extension}";
         }
      }
       
   }
}