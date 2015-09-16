<?php
/**
 * \flat\cloud\aws\s3 class 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 

 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\cloud\aws;
/**
 * AWS namespace aliases
 */
use Aws\Common\Exception\MultipartUploadException;
use Aws\S3\Model\MultipartUpload\UploadBuilder;
use Aws\S3\S3Client;
/**
 * s3 object controller
 * 
 * @package    flat\cloud\aws
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 * @abstract
 */
class s3 extends \flat\cloud\aws {
   const multipart_min_size = 10737418240; //10MB
   /**
    * form a consistent s3 object key from given parameters
    * 
    * @return string
    * @param string $key s3 object key
    * @param string $prefix (optional) object name prefix (similar to a subfolder)
    * @static
    */
   private static function _canonicalize_key($key,$prefix=null) {
      $pre = "";
      if (!empty($prefix) && is_string($prefix)) {
         /*
          * always have trailing slash on prefix
          */
         if (substr($prefix,-1)!="/") $prefix .= "/";
         if (strlen($prefix)>1) { //make sure prefix is not just "/"
            //if prefix starts with slash, remove it
            if (substr($prefix,0,1)=="/") $prefix = substr($prefix,1);
            $pre = $prefix;
         }
      }
      /*
       * if key starts with slash, remove it
       */
      if (substr($key,0,1)=="/") $key = substr($key,1);
      /*
       * concatonate any prefix with key
       */
      return $pre.$key; 
   }
   /**
    * create s3 object from given data
    * 
    * @param \Aws\S3\S3Client $client s3 client object
    * @param string $bucket bucket name
    * @param string $data data of new object
    * @param string $key s3 object key
    * @param string $prefix (optional) object name prefix (similar to a subfolder)
    * @return void
    * @static
    */
   public static function body_upload($client,$bucket,$data,$key,$prefix=null) {

      $result = $client->putObject(array(
          'Bucket' => $bucket,
          'Key'    => self::_canonicalize_key($key,$prefix),
          'Body'   => $data
      ));
      // We can poll the object until it is accessible
      $client->waitUntil('ObjectExists', array(
          'Bucket' => $bucket,
          'Key'    => self::_canonicalize_key($key,$prefix),
      ));
   }
   /**
    * upload file to s3
    * @param \Aws\S3\S3Client $client s3 client object
    * @param string $bucket bucket name
    * @param string $file filename
    * @param string $key s3 object key
    * @param string $prefix (optional) object name prefix (in front of key)
    * @return void
    * @static
    * @see http://docs.aws.amazon.com/AmazonS3/latest/dev/usingHLmpuPHP.html
    *    AWS SDK multipart upload example 
    */
   public static function file_upload($client,$bucket,$file,$key,$prefix=null,$contentType=null) {
      
      /*
       * param sanity check
       */      
      if (empty($bucket)) {
         throw new s3\bad_param(
            "bucket",
            "cannot determine bucket name"
         );
      }
      
      if (!is_file($file)) {
         throw new s3\bad_param(
            "file",
            "is not regular file"
         );
      }
      if (!is_readable($file)) {
         throw new s3\bad_param(
            "file",
            "is not readable file"
         );
      }
      $size = filesize($file);
      $rname =  self::_canonicalize_key($key,$prefix);
//       echo "\nkey: ".$key . "\n ";
//       echo "\nrname: ".$rname . "\n ";
//       echo "\\nprefix: ".$prefix . "\n ";
//       die("cloud aws s3 (die)");      
      $objConfig = [
          'Bucket'     => $bucket,
          'Key'        => self::_canonicalize_key($key,$prefix),
          'SourceFile' => $file,
      ];
      if (!empty($contentType)) $objConfig['ContentType'] = $contentType;
      //var_dump($objConfig);die('cloud aws s3');
      if ($size < self::multipart_min_size) {
         $result = $client->putObject($objConfig);
         // We can poll the object until it is accessible
         $client->waitUntil('ObjectExists', array(
             'Bucket' => $bucket,
             'Key'    => self::_canonicalize_key($key,$prefix),
         ));
      } else {
         // Prepare the upload parameters.
         $uploader = UploadBuilder::newInstance()
             ->setClient($client)
             ->setSource($file)
             ->setBucket($bucket)
             ->setKey(self::_canonicalize_key($key,$prefix))
             ->setMinPartSize(25 * 1024 * 1024)
             ->setConcurrency(3)
             ->build();
         // Perform the upload. Abort the upload if something goes wrong.
         try {
             $uploader->upload();
             return;
         } catch (MultipartUploadException $e) {
             $uploader->abort();
             throw new s3\upload_failure($e);
         } 
         // We can poll the object until it is accessible
         $client->waitUntil('ObjectExists', array(
             'Bucket' => $bucket,
             'Key'    => self::_canonicalize_key($key,$prefix)
         ));
      }
   }
   
   public function get_baseurl() {
      if ($this instanceof \flat\cloud\aws\s3\baseurl_provider) {
         return $this->get_s3_baseurl($this->bucket,$this->prefix);
      }
      return "https://".$this->bucket.".s3.amazonaws.com";
   }
   
   public function get_url() {
      $key = self::_canonicalize_key($this->key,$this->prefix);

      return $this->get_baseurl()."/$key";
   }
   
   /** 
    * uploads contents of file at $this->file, or value of $data as s3 object value
    * 
    * @return void
    * @param string $data (optional) if given, s3 object body will be set to this value
    * 
    */
   public function set($data=null) {
      $client = $this->_get_client();
      if ($data !==null) {
         self::body_upload(
            $client,
            $this->bucket,
            $data,
            $this->key,
            $this->prefix,
            $this->contentType
         );
      } else {
         self::file_upload(
            $client,
            $this->bucket,
            $this->file,
            $this->key,
            $this->prefix,
            $this->contentType
         );
      }
   }
   
   /**
    * read an s3 object to data property, write to file if file property is set
    * @todo write s3 read routine
    */
   public function get() {
      
   }
   
   /**
    * function for \flat\cloud\aws parent client controller
    * 
    * @return string
    */
   protected function _get_client_class() {
      return "\\AWS\\S3\\S3Client";
   }   
   protected $file;
   protected $key;
   protected $bucket;
   protected $contentType;
   protected $prefix;
   
   /**
    * 
    * @return void
    * 
    * @param string $key s3 object key
    * @param string $file (optional) system filename (for upload or saving into)
    * @param strsing $prefix (optional) s3 object name prefix
    * @param string $operation (optional) set to "upload" to upload given file
    *    to s3 as object with key $key, "read" to download s3 object contents
    *    into given file. @todo implement "read"
    * @param string $contentType (optional) explicitly set s3 ContentType
    *    property for given s3 $key to this value.
    * @param string $bucket (optional) s3 bucket name; if empty, attempts to
    *    dervive bucket name contextually.
    * @param \AWS\S3\S3Client $client (optional) if empty, attempts to derive
    *    client contextually.
    * 
    * @see \flat\cloud\aws::_get_client() for potential exceptions
    */
   public function __construct($key=null,$file=null,$prefix=null,$operation=null,$contentType=null,$bucket=null,$client=null) {
      $param = new \flat\core\util\map(
         func_get_args(),
         array(
            'key'=>null,
            'file'=>null,
            'prefix'=>null,
            'operation'=>null,
            'contentType'=>null,
            'bucket'=>null,
            'client'=>null,
         )
      );
      if ($param->client !==null) $this->_set_client($param->client);
      unset($param->client);
      $operation = $param->operation;
      unset($param->operation);
      if ($param->bucket===null) {
         /**
          * check interface for bucket
          */
         if ($this instanceof s3\bucket_provider) {
            $param->bucket = $this->get_s3_bucket();
         }
      } 
      
      /**
       * @todo sanity check bucket and client
       */

      /*
       * map other argument params to properties
       */
      $this->object_to_properties($param);
      //var_dump($this);die('cloud aws s3');
      
//       echo "\nkey: ".$this->key."\n";
//       die("die: cloud aws s3");
      
      if ($operation=="upload") {
         $this->set();
      }
   }

}


















