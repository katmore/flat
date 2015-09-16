<?php
/**
 * \flat\core\util\image class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * BY ACCESSING THE CONTENTS OF THIS SOURCE FILE IN ANY WAY YOU AGREE TO BE 
 * BOUND BY THE PROVISIONS OF THE "SOURCE FILE ACCESS AGREEMENT", A COPY OF 
 * WHICH CAN IS FOUND IN THE FILE 'LICENSE.txt'.
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\core\util;
/**
 * processes images
 * 
 * @package    flat\core\util\image
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class image {
   /**
    * @var \Imagick $_imagick imagick object in context
    */
   private $_imagick;
   
   /**
    * retrieves image as base64 encoded string
    * @return string
    */
   public function get_base64() {
      return base64_encode($this->_imagick->getImageBlob());
   }
   
   /**
    * saves image to file
    * @return void
    */
   public function save($file) {
      $this->_imagick->writeImage($file);
   }
   
   /**
    * retrieves image as blob
    * @return string
    */
   public function get_blob() {
      return $this->_imagick->getImageBlob();
   }
   
   /**
    * retrieves image as imagick object
    * 
    * @return \Imagick
    */
   public function get_imagick() {
      return $this->_imagick;
   }
   /**
    * @param string|\Imagick $source image's path, url, or imagick object
    * 
    * @param int $width (optional) minimum width to enforce
    * @param int $width (optional) minimum height to enforce
    */
   public function __construct($source,$width=0,$height=0) {
      /*
       * sanitize height & width
       */
      $width = max((int) sprintf("%d",$width),0);
      $height = max((int) sprintf("%d",$height),0);
      
      if (is_string($source)) {
         $this->_imagick = new \Imagick($source);
         $this->_imagick->setImagePage(0,0,0,0);
      } else
      if (is_a($source,'\Imagick')) {
         $this->_imagick = $source;
      }
      if (!$width && !$height) return;
      
      if (!$width || !$height) {
         if ($width) {
            self::resize_width_proportional($this->_imagick, $width);
            return;
         }
         if ($height) {
            self::resize_height_proportional($this->_imagick, $height);
            return;
         }
      }
   }
   /**
    * retrieves dimension object for given image
    * 
    * @return image\dim
    * @param \Imagic $image 
    */
   private static function _imagick_to_dim(\Imagick $image) {
      return new image\dim(
         $image->getImageGeometry()
      );  
   }
   /**
    * resizes an image to minimum dimension, scaling proportionally
    * 
    * @return void
    * 
    * @param \Imagick $image imagick object to resize
    * @param int $width minimum width
    * @param int $height minimum height
    * 
    */
   public static function resize_min_proportional(\Imagick $image, $width, $height) {
      $width = max((int) sprintf("%d",$width),0);
      $height = max((int) sprintf("%d",$height),0);
      /*
       * get current image dimensions
       */
      $dim=self::_imagick_to_dim( $image);
      
      /*
       * leave if we aren't needed
       */
      if (($dim->h==$height) && ($dim->w=$width)) return;
      
      $dim->scale(
         new image\dim(array(
            'h'=>$height,
            'w'=>$width,
         ))
      );
      $image->resizeImage(
         $dim->width,
         $dim->height,
         \Imagick::FILTER_LANCZOS,
         0.7,
         false
      );
   }
   
   /**
    * resizes an image to fixed height, scaling width proportionally
    * 
    * @return void
    * 
    * @param \Imagick $image imagick object to resize
    * @param int $height width to resize to
    * 
    * @throws image\exception
    */
   public static function resize_height_proportional(\Imagick $image,$height) {
      //$width = max((int) sprintf("%d",$width),0);
      $height = max((int) sprintf("%d",$height),0);
      /*
       * get current image dimensions
       */
      $dim=self::_imagick_to_dim( $image);
      
      /*
       * leave if we aren't needed
       */
      if ($dim->h==$height) return;
      
      $dim->scale(
         new image\dim(array(
            'h'=>$height
         )),
         new image\dim(array(
            'h'=>$height
         ))
      );
      $image->resizeImage(
         $dim->width,
         $dim->height,
         \Imagick::FILTER_LANCZOS,
         0.7,
         false
      );
   }
   
   /**
    * resizes an image to fixed width, scaling height proportionally
    * 
    * @return void
    * 
    * @param \Imagick $image imagick object to resize
    * @param int $width width to resize to
    * 
    * @throws image\exception
    */
   public static function resize_width_proportional(\Imagick $image,$width) {
      $width = max((int) sprintf("%d",$width),0);
      //$height = max((int) sprintf("%d",$height),0);
      /*
       * get current image dimensions
       */
      $dim=self::_imagick_to_dim( $image);
      
      /*
       * leave if we aren't needed
       */
      if ($dim->w==$width) return;
      
      $dim->scale(
         new image\dim(array(
            'w'=>$width
         )),
         new image\dim(array(
            'w'=>$width
         ))
      );
      $image->resizeImage(
         $dim->width,
         $dim->height,
         \Imagick::FILTER_LANCZOS,
         0.7,
         false
      );
   }
}

























