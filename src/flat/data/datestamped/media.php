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
namespace flat\data\datestamped;
class media extends \flat\data\datestamped 
   implements \flat\data\ready
{
   public $handle;
   /**
    * @var string|string[] url to media if string
    *    if array of strings, index should indicate url context. 
    *    ie:
    *       $this->url['webm'] = "https://example.com/videos/{handle}.webm
    *       $this->url['mp4'] = "https://example.com/videos/{handle}.mp4
    *          --or even--
    *       $this->url['webm']['1080p'] = {something}
    *       $this->url['webm']['480i'] = {something}
    *       $this->url['orig'] = {something}
    */   
   public $url;
   
   public $label;
   public $description;
   public $tags;
   
   /**
    * @var string[] $meta metadata key=>val assoc array. useful for things like:
    *    '' 
    */
   public $meta;

   /**
    * @var string $type brief description of type of media.
    *    ie: video, image, document, audio
    */
   public $type;
   
   /**
    * @var string $mtype internet media type.
    *    ie: video/webm, image/png, application/pdf, audio/ogg 
    * @link http://en.wikipedia.org/wiki/Internet_media_type
    * @see finfo_open() finfo_open(FILEINFO_MIME_TYPE);
    */
   //public $mtype;
   
   /**
    * @var \flat\data\history[] $history important things that have happened 
    *    to this media object. ideal for things like:
    *       'added as profile picture','
    */
   public $history;
   /**
    * @var string|string[] $assoc ideally URL associating a resource to this media object
    *    ie: 
    *       employee profile picture: https://example.com/employees/Jane_Doe/details/profile_pic
    *       part of employee image collection: https://example.com/employees/Jane_Doe/images
    *       power point lesson: https://example.com/lessons
    */
   public $assoc;
   
   /**
    * @var string $status status of media object.
    *    ie:
    *       processing: video is being transcoded, PDF is being generated, etc
    *       reserved: video or photo is awaiting user action, such as complete an upload
    */
   public $status='reserved'; //reserved, queued, processing, ready... something like that 
   
   public function data_ready() {
      if (is_null($this->meta)) {
         $this->meta = new \stdClass(); 
      }
      if (is_null($this->history)) {
         $this->history =  [];
      }
      if (is_null($this->url)) {
         $this->url =  new \stdClass();
      }
   }
}





