<?php
/**
 * class definition 
 *
 * PHP version >=5.6
 * 
 * Copyright (c) 2012-2015 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * COPYRIGHT NOTICE:
 * The flat framework. https://github.com/katmore/flat
 * Copyright (C) 2012-2015  Doug Bird.
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
 * @copyright  Copyright (c) 2012-2015 Doug Bird. All Rights Reserved.
 */
namespace flat\db\driver\pdo;



class pdo extends \PDO {
   const path_root_none = 2;
   const path_root_from_config = 1;
   const path_root_from_config_ns = 'app/script/pdo/path_root';
   /**
    * Reads an SQL file and prepares as statement to be executed by the 
    *    PDOStatement::execute() method. Returns \PDOStatement object if successful.
    *    Throws an exception or (bool) false on encountering an error.
    * 
    * @return \PDOStatement | bool
    * 
    * @param string $path sql file path
    * @param int | string $path_root (optional) defaults to pdo::path_root_from_config.
    *    acceptable values are pdo::path_root_from_config pdo::path_root_none, or 
    *    a path to a system directory that will contain the file specified by $path param.
    * @param array $driver_options (optional) driver options. see \PDO::prepare().
    * 
    * @throws \flat\db\driver\pdo\exception\bad_prepare_path
    * @throws \flat\db\driver\pdo\exception\bad_prepare_path_root
    * 
    * @see \PDO::prepare()
    */
   public function prepare_from_file($path,$path_root=pdo::path_root_from_config, array $driver_options=[]) {
      $basedir = "";
      if (is_array($path_root) && empty($driver_options)) {
         
         $driver_options = $path_root;
         $path_root = pdo::path_root_from_config;
      }
      if (is_int($path_root) && $path_root==pdo::path_root_from_config) {
         $basedir = \flat\core\config::get(self::path_root_from_config_ns);
      } else
      if (is_string($path_root)) {
         $basedir = $path_root;
      }
      if (!empty($basedir)) {
         if (!file_exists($basedir)) {
            throw new exception\bad_prepare_path_root($basedir, "does not exist");
         }
         if (!is_dir($basedir)) {
            throw new exception\bad_prepare_path_root($basedir, "not a directory");
         }
         $filename = $basedir . "/" . $path;
      } else {
         $filename = $path;
      }
      if (!file_exists($filename)) {
         throw new exception\bad_prepare_path($filename,"does not exist");
      }
      if (!is_file($filename)) {
         throw new exception\bad_prepare_path($filename,"is not a file");
      }
      if (!is_readable($filename)) {
         throw new exception\bad_prepare_path($filename,"is not readable");
      }
      $statement = file_get_contents($filename);
      return parent::prepare($statement,$driver_options);
   }
}




