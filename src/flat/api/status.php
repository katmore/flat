<?php
/**
 * \flat\api\status definition 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *  All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (c) 2012-2017 Doug Bird.
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
 * @author   D. Bird <retran@gmail.com>
 * @copyright Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 */
namespace flat\api;

abstract class status {

   /**
    * The "type" of status-code class `1xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_TYPE_1XX = 'Informational';

   /**
    * The status-code "class" when the first digit of the status code is `1` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_INFORMATIONAL = '1xx';
   
   /**
    * Alias of \flat\api\status::CODE_CLASS_INFORMATIONAL
    * @see \flat\api\status::CODE_CLASS_INFORMATIONAL
    */
   const INFORMATIONAL_CODE_CLASS = self::CODE_CLASS_INFORMATIONAL;

   /**
    * The "description" of status-code class `1xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_DESCRIPTION_1XX = 'The request was received, continuing process';

   /**
    * The "type" of status-code class `2xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_TYPE_2XX = 'Successful';

   /**
    * The status-code "class" when the first digit of the status code is `2` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_SUCCESSFUL = '2xx';
   
   /**
    * Alias of \flat\api\status::CODE_CLASS_SUCCESSFUL
    * @see \flat\api\status::CODE_CLASS_SUCCESSFUL
    */
   const SUCCESSFUL_CODE_CLASS = self::CODE_CLASS_SUCCESSFUL;

   /**
    * The "description" of status-code class `2xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_DESCRIPTION_2XX = 'The request was successfully received, understood, and accepted';

   /**
    * The "type" of status-code class `3xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_TYPE_3XX = 'Redirection';

   /**
    * The status-code "class" when the first digit of the status code is `3` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_REDIRECTION = '3xx';
   
   /**
    * Alias of \flat\api\status::CODE_CLASS_REDIRECTION
    * @see \flat\api\status::CODE_CLASS_REDIRECTION
    */
   const REDIRECTION_CODE_CLASS = self::CODE_CLASS_REDIRECTION;

   /**
    * The "description" of status-code class `3xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_DESCRIPTION_3XX = 'Further action needs to be taken in order to complete the request';

   /**
    * The "type" of status-code class `4xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_TYPE_4XX = 'Client Error';

   /**
    * The status-code "class" when the first digit of the status code is `4` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_CLIENT_ERROR = '4xx';
   
   /**
    * Alias of \flat\api\status::CODE_CLASS_CLIENT_ERROR
    * @see \flat\api\status::CODE_CLASS_CLIENT_ERROR
    */
   const CLIENT_ERROR_CODE_CLASS = self::CODE_CLASS_CLIENT_ERROR;

   /**
    * The "description" of status-code class `4xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_DESCRIPTION_4XX = 'The request contains bad syntax or cannot be fulfilled';

   /**
    * The "type" of status-code class `5xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_TYPE_5XX = 'Server Error';

   /**
    * The status-code "class" when the first digit of the status code is `5` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_SERVER_ERROR = '5xx';
   
   /**
    * Alias of \flat\api\status::CODE_CLASS_SERVER_ERROR
    * @see \flat\api\status::CODE_CLASS_SERVER_ERROR
    */
   const SERVER_ERROR_CODE_CLASS = self::CODE_CLASS_SERVER_ERROR;

   /**
    * The "description" of status-code class `5xx` as outlined in section 6 of RFC 7231.
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASS_DESCRIPTION_5XX = 'The server failed to fulfill an apparently valid request';

   /**
    * Hashmap of status-code class information as outlined in section 6 of RFC 7231.
    * 
    * <ul>
    *  <li>
    *    if the first digit of the <b>status code</b> is `<b>1</b>`:
    *    <ul>
    *     <li>the status-code <b>class</b> is: "<i>1xx</i>"</li>
    *     <li><i>the status-code class <b>type</b></i> is: "<i>Informational</i>"</li>
    *     <li><i>the status-code class <b>description</b></i> is: "<i>The request was received, continuing process</i>"</li>
    *    </ul>
    *  </li>
    *  <li>
    *    if the first digit of the <b>status code</b> is `<b>2</b>`:
    *    <ul>
    *     <li>the status-code <b>class</b> is: "<i>2xx</i>"</li>
    *     <li><i>the status-code class <b>type</b></i> is: "<i>Successful</i>"</li>
    *     <li><i>the status-code class <b>description</b></i> is: "<i>The request was successfully received, understood, and accepted</i>"</li>
    *    </ul>
    *  </li>
    *  <li>
    *    if the first digit of the <b>status code</b> is `<b>3</b>`:
    *    <ul>
    *     <li>the status-code <b>class</b> is: "<i>3xx</i>"</li>
    *     <li><i>the status-code class <b>type</b></i> is: "<i>Redirection</i>"</li>
    *     <li><i>the status-code class <b>description</b></i> is: "<i>Further action needs to be taken in order to complete the request</i>"</li>
    *    </ul>
    *  </li>
    *  <li>
    *    if the first digit of the <b>status code</b> is `<b>4</b>`:
    *    <ul>
    *     <li>the status-code <b>class</b> is: "<i>4xx</i>"</li>
    *     <li><i>the status-code class <b>type</b></i> is: "<i>Client Error</i>"</li>
    *     <li><i>the status-code class <b>description</b></i> is: "<i>The request contains bad syntax or cannot be fulfilled</i>"</li>
    *    </ul>
    *  </li>
    *  <li>
    *    if the first digit of the <b>status code</b> is `<b>5</b>`:
    *    <ul>
    *     <li>the status-code <b>class</b> is: "<i>5xx</i>"</li>
    *     <li><i>the status-code class <b>type</b></i> is: "<i>Server Error</i>"</li>
    *     <li><i>the status-code class <b>description</b></i> is: "<i>The server failed to fulfill an apparently valid request</i>"</li>
    *    </ul>
    *  </li>
    * </ul>
    * 
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   const CODE_CLASSES = [
      self::CODE_CLASS_INFORMATIONAL => [
         'type' => self::CODE_CLASS_TYPE_1XX,
         'description' => self::CODE_CLASS_DESCRIPTION_1XX
      ],
      self::CODE_CLASS_SUCCESSFUL => [
         'type' => self::CODE_CLASS_TYPE_2XX,
         'description' => self::CODE_CLASS_DESCRIPTION_2XX
      ],
      self::CODE_CLASS_REDIRECTION => [
         'type' => self::CODE_CLASS_TYPE_3XX,
         'description' => self::CODE_CLASS_DESCRIPTION_3XX
      ],
      self::CODE_CLASS_CLIENT_ERROR => [
         'type' => self::CODE_CLASS_TYPE_4XX,
         'description' => self::CODE_CLASS_DESCRIPTION_4XX
      ],
      self::CODE_CLASS_SERVER_ERROR => [
         'type' => self::CODE_CLASS_TYPE_5XX,
         'description' => self::CODE_CLASS_DESCRIPTION_5XX
      ]
   ];
   
   /**
    * Provides the status message the as derived from the corresponding <i>Description</i> field of the  
    *    IANA maintained <a href="http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml">Hypertext Transfer Protocol (HTTP) Status Code Registry</a>.
    *
    * @return string the status code reason phrase
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    */
   abstract public function get_message();
   
   /**
    * Provides the status code.
    *
    * @return integer status code
    */
   abstract public function get_code();
   
   /**
    * Provides a status-code class according to the first digit of the <b>$status_code</b>.
    *
    * @param int $status_code the HTTP status code
    * 
    * @return string the status-code class or an empty string if the <b>$status_code</b> is invalid:
    * <ul>
    *  <li><i>When the first digit of the <b>$status_code</b> is</i> `<b>1</b>` <i>the status-code class returned is</i> "<b>1xx</b>".</li>
    *  <li><i>When the first digit of the <b>$status_code</b> is</i> `<b>2</b>` <i>the status-code class returned is</i> "<b>2xx</b>".</li>
    *  <li><i>When the first digit of the <b>$status_code</b> is</i> `<b>3</b>` <i>the status-code class returned is</i> "<b>3xx</b>".</li>
    *  <li><i>When the first digit of the <b>$status_code</b> is</i> `<b>4</b>` <i>the status-code class returned is</i> "<b>4xx</b>".</li>
    *  <li><i>When the first digit of the <b>$status_code</b> is</i> `<b>5</b>` <i>the status-code class returned is</i> "<b>5xx</b>".</li>
    * </ul>
    * 
    * @static
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    * @see https://tools.ietf.org/html/rfc7231#section-6
    */
   final public static function status_code2status_code_class(int $status_code): string {
      if ($status_code >= 100 && $status_code <= 599) {
         $first_digit = (int) substr($status_code,0,1);
         if (isset(static::CODE_CLASSES[$first_digit . 'xx'])) {
            return $first_digit . 'xx';
         }
      }
      return '';
   }

   /**
    * Formats a string with properties of the status-code class as outlined in section 6 of RFC 7231.
    *
    * @param int $status_code_class status-code class <ul>
    *  <li><i>If the first digit of the status code is</i> `<b>1</b>` <i>the status-code class should be</i> "<b>1xx</b>".</li>
    *  <li><i>If the first digit of the status code is</i> `<b>2</b>` <i>the status-code class should be</i> "<b>2xx</b>".</li>
    *  <li><i>If the first digit of the status code is</i> `<b>3</b>` <i>the status-code class should be</i> "<b>3xx</b>".</li>
    *  <li><i>If the first digit of the status code is</i> `<b>4</b>` <i>the status-code class should be</i> "<b>4xx</b>".</li>
    *  <li><i>If the first digit of the status code is</i> `<b>5</b>` <i>the status-code class should be</i> "<b>5xx</b>".</li>
    * </ul>
    * 
    * @param string $format value to be returned after replacing the following substring tokens:
    * <ul>
    *  <li>The "<b>%class%</b>" substring token will be replaced with the same value as the <b>$status_code_class</b> parameter</li>
    *  <li>
    *    The "<b>%class-type%</b>" substring token will be replaced with status-code class "type":
    *    <ul>
    *     <li><i>If the <b>$status_code_class</b> is "<b>1xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Informational</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>2xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Successful</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>3xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Redirection</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>4xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Client Error</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>5xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Server Error</b>".</li>
    *    </ul>
    *  </li>
    *  <li>
    *    The "<b>%class-description%</b>" substring token will be replaced with status-code class "description":
    *    <ul>
    *     <li><i>If the <b>$status_code_class</b> is "<b>1xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The request was received, continuing process</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>2xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The request was successfully received, understood, and accepted</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>3xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>Further action needs to be taken in order to complete the request</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>4xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The request contains bad syntax or cannot be fulfilled</b>".</li>
    *     <li><i>If the <b>$status_code_class</b> is "<b>5xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The server failed to fulfill an apparently valid request</b>".</li>
    *    </ul>
    *  </li>
    * </ul>
    *
    * @return string the formatted string
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see \flat\api\status::status_code2status_code_class()
    * @static
    */
   final public static function status_code_class_format(string $status_code_class, string $format): string {
      if (!isset(static::CODE_CLASSES[$status_code_class]))
         return '';

      $format = str_replace("%class%",$status_code_class,$format);

      $format = str_replace("%class-type%",$status_code_class['type'],$format);

      $format = str_replace("%class-description%",$status_code_class['description'],$format);

      return $format;
   }
   
   /**
    * Provides the status code reason phrase as derived from the corresponding <i>Description</i> field of the
    *    IANA maintained <a href="http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml">Hypertext Transfer Protocol (HTTP) Status Code Registry</a>.
    *
    * @return string the status code reason phrase or empty string if the status message is invalid
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see \flat\api\status::get_message()
    */
   final public function get_reason_phrase(): string {
      if (!is_string($message = $this->get_message())) {
         return '';
      }
      return $message;
   }

   /**
    * Formats a string with properties of the status-code as outlined in section 6 of RFC 7231.
    *  
    * @param string $format value to be returned after replacing following substring tokens:
    * <ul>
    *  <li>
    *    The "<b>%code%</b>" substring token will be replaced with the status code.
    *  </li>
    *  <li>
    *    The "<b>%reason-phrase%</b>" substring token will be replaced with the status code reason phrase as derived from the corresponding <i>Description</i> field of the  
    *    IANA maintained <a href="http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml">Hypertext Transfer Protocol (HTTP) Status Code Registry</a>.
    *  </li>
    *  <li>
    *    The "<b>%class%</b>" substring token will be replaced with status-code class:
    *    <ul>
    *     <li><i>If the first digit of the status code is `<b>1</b>`</i>, the substring <i>"<b>%class%</b>" will be replaced with</i> "<b>1xx</b>".</li>
    *     <li><i>If the first digit of the status code is `<b>2</b>`</i>, the substring <i>"<b>%class%</b>" will be replaced with</i> "<b>2xx</b>".</li>
    *     <li><i>If the first digit of the status code is `<b>3</b>`</i>, the substring <i>"<b>%class%</b>" will be replaced with</i> "<b>3xx</b>".</li>
    *     <li><i>If the first digit of the status code is `<b>4</b>`</i>, the substring <i>"<b>%class%</b>" will be replaced with</i> "<b>4xx</b>".</li>
    *     <li><i>If the first digit of the status code is `<b>5</b>`</i>, the substring <i>"<b>%class%</b>" will be replaced with</i> "<b>5xx</b>".</li>
    *    </ul>
    *  </li>
    *  <li>
    *    The "<b>%class-type%</b>" substring token will be replaced with status-code class "type":
    *    <ul>
    *     <li><i>If the status-code class is "<b>1xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Informational</b>".</li>
    *     <li><i>If the status-code class is "<b>2xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Successful</b>".</li>
    *     <li><i>If the status-code class is "<b>3xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Redirection</b>".</li>
    *     <li><i>If the status-code class is "<b>4xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Client Error</b>".</li>
    *     <li><i>If the status-code class is "<b>5xx</b>"</i>, the substring <i>"<b>%class-type%</b>" will be replaced with</i> "<b>Server Error</b>".</li>
    *    </ul>
    *  </li>
    *  <li>
    *    The "<b>%class-description%</b>" substring token will be replaced with status-code class "description":
    *    <ul>
    *     <li><i>If the status-code class is "<b>1xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The request was received, continuing process</b>".</li>
    *     <li><i>If the status-code class is "<b>2xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The request was successfully received, understood, and accepted</b>".</li>
    *     <li><i>If the status-code class is "<b>3xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>Further action needs to be taken in order to complete the request</b>".</li>
    *     <li><i>If the status-code class is "<b>4xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The request contains bad syntax or cannot be fulfilled</b>".</li>
    *     <li><i>If the status-code class is "<b>5xx</b>"</i>, the substring <i>"<b>%class-description%</b>" will be replaced with</i> "<b>The server failed to fulfill an apparently valid request</b>".</li>
    *    </ul>
    *  </li>
    * </ul>
    * 
    * @return string the formatted string or an empty string if the status code is invalid
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    * @see \flat\api\status::status_code_class_format()
    * @see \flat\api\status::get_code_class()
    * @see \flat\api\status::get_reason_phrase()
    * @see \flat\api\status::get_code_number()
    */
   final public function format_code(string $format): string {
      $format = static::status_code_class_format($this->get_code_class(),$format);
      $format = str_replace("%reason-phrase%",$this->get_reason_phrase(),$format);
      if (0 === ($code = $this->get_code_number())) {
         $code = '';
      }
      $format = str_replace("%code%",$code,$format);
      return $format;
   }
   
   /**
    * Provides the status code number.
    * 
    * @return integer the status code or <b>integer</b> 0 if the status code is invalid 
    * @see \flat\api\status::get_code()
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    */
   final public function get_code_number(): int {
      if (is_int($status_code = $this->get_code()) || (is_string($status_code) && ctype_digit($status_code))) {
         return (int) $status_code;
      }
      return 0;
   }

   /**
    * Provides a status-code class according to the first digit of the status code.
    *
    * @return string the status-code class or an empty string if the status code was invalid:
    * <ul>
    *  <li><i>When the first digit of the status code is</i> `<b>1</b>` <i>the status-code class is</i> "<b>1xx</b>".</li>
    *  <li><i>When the first digit of the status code is</i> `<b>2</b>` <i>the status-code class is</i> "<b>2xx</b>".</li>
    *  <li><i>When the first digit of the status code is</i> `<b>3</b>` <i>the status-code class is</i> "<b>3xx</b>".</li>
    *  <li><i>When the first digit of the status code is</i> `<b>4</b>` <i>the status-code class is</i> "<b>4xx</b>".</li>
    *  <li><i>When the first digit of the status code is</i> `<b>5</b>` <i>the status-code class is</i> "<b>5xx</b>".</li>
    * </ul>
    * 
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    * @see \flat\api\status::status_code2status_code_class()
    * @see \flat\api\status::get_code_number()
    */
   final public function get_code_class(): string {
      if (0!==($status_code = $this->get_code_number())) {
         return static::status_code2status_code_class($status_code);
      }
      return '';
   }

   /**
    * Provides the status-code class "type" as outlined in section 6 of RFC 7231.
    *
    * @return string the status code "type" or an empty string if the status code was invalid:
    * <ul>
    *  <li><i>When the status-code class is</i> "<b>1xx</b>" <i>the return value will be</i> "<b>Informational</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>2xx</b>" <i>the return value will be</i> "<b>Successful</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>3xx</b>" <i>the return value will be</i> "<b>Redirection</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>4xx</b>" <i>the return value will be</i> "<b>Client Error</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>5xx</b>" <i>the return value will be</i> "<b>Server Error</b>".</li>
    * </ul>
    * 
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see \flat\api\status::format_code()
    */
   final public function get_code_class_type(): string {
      return $this->format_code("%type%");
   }

   /**
    * Provides the status-code class "description" as outlined in section 6 of RFC 7231.
    *
    * @return string the status code "description" or an empty string if the status code was invalid:
    * <ul>
    *  <li><i>When the status-code class is</i> "<b>1xx</b>" <i>the return value will be</i> "<b>The request was received, continuing process</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>2xx</b>" <i>the return value will be</i> "<b>The request was successfully received, understood, and accepted</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>3xx</b>" <i>the return value will be</i> "<b>Further action needs to be taken in order to complete the request</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>4xx</b>" <i>the return value will be</i> "<b>The request contains bad syntax or cannot be fulfilled</b>".</li>
    *  <li><i>When the status-code class is</i> "<b>5xx</b>" <i>the return value will be</i> "<b>The server failed to fulfill an apparently valid request</b>".</li>
    * </ul>
    * 
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see \flat\api\status::format_code()
    */
   final public function get_code_class_description(): string {
      return $this->format_code("%description%");
   }
   
   /**
    * Provides the status code and reason phrase separated by a space.
    * 
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    * @see \flat\api\status::get_code()
    * @see \flat\api\status::get_message()
    * @return string status code and reason phrase separated by a space
    */
   final public function get_str() {
      return $this->get_code() . " " . $this->get_message();
   }
   
   /**
    * Provides the status code and reason phrase separated by a space.
    *
    * @see https://tools.ietf.org/html/rfc7231#section-6
    * @see http://www.iana.org/assignments/http-status-codes/http-status-codes.xhtml
    *
    * @return string status code and reason phrase separated by a space
    */
   final public function __toString() {
      return $this->get_str();
   }
}
