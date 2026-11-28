<?php
namespace flat\core\util;
class reserved  {
   const keywords = ['__halt_compiler', 'abstract', 'and', 'array', 'as', 'break', 'callable', 'case', 'catch', 'class', 'clone', 'const', 'continue', 'declare', 'default', 'die', 'do', 'echo', 'else', 'elseif', 'empty', 'enddeclare', 'endfor', 'endforeach', 'endif', 'endswitch', 'endwhile', 'eval', 'exit', 'extends', 'final', 'for', 'foreach', 'function', 'global', 'goto', 'if', 'implements', 'include', 'include_once', 'instanceof', 'insteadof', 'interface', 'isset', 'list', 'namespace', 'new', 'or', 'print', 'private', 'protected', 'public', 'require', 'require_once', 'return', 'static', 'switch', 'throw', 'trait', 'try', 'unset', 'use', 'var', 'while', 'xor',
      'fn', 'yield', 'match', 'mixed', 'self', 'parent', 'int', 'float',
      'bool', 'string', 'true', 'false', 'null', 'void', 'iterable', 'object',
      '__class__', '__trait__', '__namespace__', '__method__', '__function__',
      '__line__', '__file__', '__dir__'];

   public static function make_safe_classname($classname,$delimiter="\\") {
      $node = explode($delimiter,$classname);
      foreach ($node as &$n) {
         if (in_array(strtolower($n),self::keywords,true)) {
            $n=$n."_";
         } 
      }
      return implode($delimiter,$node);
   }
}