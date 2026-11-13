<?php
namespace flat\vendor\azuyalabs\yasumi;

use \Yasumi\Yasumi;
use \Yasumi\Provider\AbstractProvider;

/**
 * convenience wrapper for Yasumi's holiday library
 * @see https://github.com/azuyalabs/yasumi
 */
class dateQuery {
   
   const DEFAULT_PROVIDER = 'USA';
   
   /**
    * @var \Yasumi\Provider\AbstractProvider[] assoc array of yasumi provider objects
    *    indexed by provider id, which is provider name and year ($provider_name."_".$year)
    * @static
    */
   private static $_provider_map = [];
   
   /**
    * @var int date used for this query (Unix timestamp)
    */
   private $_date;
   /**
    * @return int date used for this query (Unix timestamp)
    */
   public function get_date() {
      return $this->_date;
   }
   /**
    * @return string year used for this query
    */   
   private $_year;
   /**
    * @return string year used for this query
    */   
   public function get_year() {
      return $this->_year;
   }

   /**
    * @return string provider id ($provider_name."_".$year) used for this query
    * @see \flat\vendor\azuyalabs\yasumi\datequery::$provider_map
    */   
   private $_provider_id;
   
   /**
    * @var \Yasumi\Provider\AbstractProvider provider used
    */
   private $_provider;
   /**
    * @return \Yasumi\Provider\AbstractProvider
    */
   public function getProvider() {
      return $this->_provider;
   }
   
   /**
    * @return \DateTime the query date, as Yasumi's providers expect it
    */
   private function _date_time() {
      return (new \DateTime())->setTimestamp($this->_date);
   }
   public function isWorkingDay() {
      return $this->_provider->isWorkingDay($this->_date_time());
   }
   
   public function isHoliday() {
      return $this->_provider->isHoliday($this->_date_time());
   }
   const DEFAULT_SCHEDULE = [
    'Sun' => [],
    'Mon' => ['9:00 AM' => '05:00 PM'],
    'Tue' => ['9:00 AM' => '05:00 PM'],
    'Wed' => ['9:00 AM' => '05:00 PM'],
    'Thu' => ['9:00 AM' => '05:00 PM'],
    'Fri' => ['9:00 AM' => '05:00 PM'],
    'Sat' => []
   ];
   public function isWithinSchedule($schedule=self::DEFAULT_SCHEDULE) {
      $currentTime = (new \DateTime())->setTimestamp($this->_date);
      foreach ($schedule[date('D', $this->_date)] as $startTime => $endTime) {
         // create time objects from start/end times
         $startTime = \DateTime::createFromFormat('h:i A', $startTime);
         $endTime   = \DateTime::createFromFormat('h:i A', $endTime);
         // check if current time is within a range
         if (($startTime < $currentTime) && ($currentTime < $endTime)) {
            return true;
         }
      }
      return false;
   }
   
   /**
    * @return void
    * @uses self::__construct()
    */
   public function set_date($date=null,$provider=self::DEFAULT_PROVIDER, $year = null, $locale = null) {
      $this->__construct($date,$provider,$year,$locale);
   }
   
   /**
    * Sets a date and provider to perform date queries upon.
    *  
    * @param string Optional $date (default is current date) date string; must be in format parsable by strtotime() 
    * @param string Optional provider name (default 'USA')
    * @param string Optional $year (default is year of provided date)
    * @param string Optional $locale locale name (default 'en_US') 
    */
   public function __construct($date=null,$provider=self::DEFAULT_PROVIDER, $year = null, $locale = null) {
      // A Unix timestamp (owner answer, known-defects Q7). strtotime() returns
      // 0 for the epoch, so only false means the date could not be parsed.
      $timestamp = is_string($date) ? strtotime($date) : false;
      $this->_date = ($timestamp === false) ? time() : $timestamp;
      if (!$year || (!is_scalar($year) || is_bool($year))) {
         $year = date('Y',$this->_date);
      }
      $this->_year = (string) $year;
      $this->_provider_id = $provider."_".$year.($locale ? "_".$locale : "");
      if (empty(static::$_provider_map[$this->_provider_id])) {
         static::$_provider_map[$this->_provider_id] = $locale
            ? Yasumi::create($provider, (int) $year, $locale)
            : Yasumi::create($provider, (int) $year);
      }
      $this->_provider = static::$_provider_map[$this->_provider_id];
   }
   
}
