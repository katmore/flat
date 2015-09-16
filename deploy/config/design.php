<?php
/**
 * design configuration for flat deployments 
 */
/**
 * @var bool full canonical path to root of flat design heirarchy.
 *    For example, it is used by /flat/tmpl controller to 
 * @uses \flat\core\controller\tmpl  
 */
$config['basedir'] = __DIR__."/../../design";
/**
 * @return array
 */
return $config;