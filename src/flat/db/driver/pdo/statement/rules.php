<?php
/**
 * \flat\db\driver\pdo\statement\rules class 
 *
 * PHP version >=7.1
 * 
 * Copyright (c) 2012-2017 Doug Bird. 
 *    All Rights Reserved. 
 * 
 * NO LICENSE, EXPRESS OR IMPLIED, BY THE COPYRIGHT OWNERS
 * OR OTHERWISE, IS GRANTED TO ANY INTELLECTUAL PROPERTY IN THIS SOURCE FILE.
 *
 * ALL WORKS HEREIN ARE CONSIDERED TO BE TRADE SECRETS, AND AS SUCH ARE AFFORDED 
 * ALL CRIMINAL AND CIVIL PROTECTIONS AS APPLICABLE.
 * 
 * @license see /flat/LICENSE.txt
 */
namespace flat\db\driver\pdo\statement;
/**
 * rules for flat's pdo statement class
 * 
 * @package    flat\db\pdo
 * @author     D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2014 Doug Bird. All Rights Reserved.
 * @version    0.1.0-alpha
 * 
 */
class rules extends \flat\data {
   /**
    * @var scalar[] $criteria assoc array of criteria values with key correlating to $criteria_hash key
    * @var string[]|array[] $criteria_hash assoc array keys correlate to 
    *    possible $criteria keys to accept, value correlates to table_alias.column
    * @var string $table_name
    * @var string $table_alias
    * @var string $join assoc array of join data
    * @var string[] $join_hash assoc array of potential SQL JOIN claus relationships
    * @var string[] $column_hash assoc array of requied columns
    * @var int $limit SQL LIMIT
    * @var int $skip SQL limit
    * @var string[] $options include:
    *    'execute'=>if non-empty value, executes prepared statement before return,
    *    'fetch'=>if non-empty value, executes and fetches assoc array of full resultset,
    *    'fetch_callback'=>if (callable) value, executes callback for each row
    *       in resultset with signature callback(array $assoc_row).
    */
   public $criteria;
   public $criteria_hash;
   public $table_name;
   public $table_alias;
   public $column_hash;
   //public $join_clause;
   public $join;
   public $join_hash;
   public $order;
   public $limit;
   public $skip;
   public $options;
}