<?php
/**
 * \flat\core\util\image\dim class
*
* PHP version >=7.1
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
namespace flat\core\util\image;
/**
 * image dimension data
 *
 * @package    flat\core\util\image
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2017 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 *
 * @todo impelment alpha, dpi properties
 */
class dim extends \flat\data
implements \flat\data\ready
{
   public $height;
   public $width;
   public $x;
   public $y;
   public $h;
   public $w;
   public $alpha;
   public $dpi;
    
   /**
    * calculates dimension for scaling up an image given the minumum dimension constraint
    *
    * @return \flat\core\util\image\dim
    *
    * @param \flat\core\util\image\dim $image original image dimensions
    * @param \flat\core\util\image\dim $min minimum dimension constraint
    */
   public static function scale_up(dim $image, dim $min) {
       
      if (
            ($image->w < $min->w) || //width undersized
            ($image->h < $min->h) //height undersized
            ) {
               return new dim(
                     array(
                        'width' => round( max($min->w, $image->ratio() * $min->h) ),
                        'height' => round( max($min->h, $min->w / $image->ratio()) )
                     )
                     );
            }
            return $image;
   }
   /**
    * calculates dimension for scaling down an image given the minumum dimension constraint
    *
    * @return \flat\core\util\image\dim | bool
    *
    * @param \flat\core\util\image\dim $image original image dimensions
    * @param \flat\core\util\image\dim $max maximum dimension constraint
    */
   public static function scale_down(dim $image, dim $max){
       
      if (
            ($image->w > $max->w) || //width oversized
            ($image->h > $max->h) //height oversized
            ) {
               return new dim (array(
                  'width' => round( min($max->w, $image->ratio() * $max->h) ),
                  'height' => round( min($max->h, $max->w / $image->ratio()) )
               ));
            }
            return $image;
   }
    
   public static function scale_dim(dim $image, dim $min, dim $max) {
      /*
       * scale to max
       */
      $image = self::scale_down($image, $max);
      /*
       * scale to min
       */
      $image = self::scale_up($image, $min);
      return $image;
   }
   /**
    * calculates dimension for scaling an image given the minimum and maximum dimension constraints
    *
    * @return void
    *
    * @param dim $min minimum dimension constraint
    * @param dim $max maximum dimension constraint
    */
   public function scale(dim $min=NULL, dim $max=NULL) {
      if ($min && $max) {
         parent::__construct(
               (array) self::scale_dim($this, $min, $max)
               );
      } else
         if ($min) {
            parent::__construct(
                  (array) self::scale_up($this,$min)
                  );
         } else
            if ($max) {
               parent::__construct(
                     (array) self::scale_down($this, $max)
                     );
            }
   }
    
   /**
    * calculates ratio of width to height
    *
    * @return float
    */
   public function ratio() {
         if($this->w > 0 && $this->h > 0){

            //    var_dump('w', $this->w);
            //    var_dump($this->h);
            //    var_dump($this->w * 1.0 / $this->h);
            //    die();
            return (float) $this->w * 1.0 / $this->h;
         }
         else{
               return (float) 1;
         }
      
      // return (float) ($this->w * 1.0 / $this->h ? $this->w * 1.0 / $this->h : 1);
      // return 1;
   }
    
   /**
    * magic method __toString()
    *
    */
   public function __toString() {
      return (string) $this->width . "x". $this->height;
   }
    
   protected $imagick;
   /**
    * invoked once all data has been mapped
    *
    * @see \flat\data\transform part of the data transform interface
    *
    * @return void
    *
    * @param array $data data (if any) that was already given and mapped
    *    by /flat/data::__construct().
    */
   public function data_ready() {
      $imagic = null;
      if (!empty($this->imagick) && class_exists("\\Imagick") && ($this->imagick instanceof \Imagick)) {
         $imagic = clone $this->imagick;
         $imagic->setImagePage(0,0,0,0);
         $this->height = $imagic->getImageHeight();
         $this->width = $imagic->getImageWidth();
      }
      /*
       * if $width, $heigh properties empty try and derive
       * from shortcut properties:
       *       $x,$w (for $width)
       *       $y,$h (for $height)
       */
      if (empty($this->width)) {

         if (!empty($this->x)) {
            $this->width = $this->x;
         } else
            if (!empty($this->w)) {
               $this->width = $this->w;
            }
      }
      if (empty($this->height)) {
         if (!empty($this->y)) {
            $this->height = $this->y;
         } else
            if (!empty($this->h)) {
               $this->height = $this->h;
            }
      }

      /*
       * sanity enforcement:
       *    height and width must be values that can be evaluated to integer
       */
      foreach (array('height','width') as $prop) {
         if (is_string($this->$prop) && !is_numeric($this->$prop)) throw new exception\bad_dim(
               "$prop must be numeric"
               );
         if (empty($this->$prop)) $this->$prop = 0;
         if (!is_int($this->$prop)) $this->$prop = (int) sprintf("%d",$this->$prop);
         if ($this->$prop<0) throw new exception\bad_dim(
               "$prop must evaluate to an integer 0 or greater"
               );
      }

      /*
       * make y/h properties synonymous with height
       */
      $this->y = $this->height;
      $this->h = $this->height;
      /*
       * make x/w properties synonymous with width
       */
      $this->x = $this->width;
      $this->w = $this->width;
   }
}

















