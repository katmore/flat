<?php

/**
 *
 * PHP version >=7.3
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
 * @copyright  Copyright (c) 2012-2019 Doug Bird. All Rights Reserved.
 */
namespace flat\core\html;

class encode implements \flat\core\serializer
{

    public static function unserialize($input, array $options = null)
    {
        throw new \flat\core\status\exception\feature_not_ready();
    }

    public static function serialize($input, array $options = null)
    {
        $html = "";
        ob_start();
        ?>
<style>
[data-item-description] {
	font-size: 0.70em;
	font-family: Gill Sans Extrabold, sans-serif;
}
</style>
<div class="flat-encoded-html" data-flat-encoded-html>
   <button style="display: none;" data-toggle-types-button></button>
        <?php
        $html .= ob_get_clean();
        $html .= self::_data_to_html($input, static::_option_to_param($options));
        ob_start();
        ?>
</div>
<script>
(function() {
//    var toggleButton = document.querySelector('[data-toggle-types-button]');
   
//    toggleButton.addEventListener('click',function(e) {
      
//       toggleButton.setAttribute('disabled','');

//       console.log('(before) toggleButton.dataset.hideTypes', toggleButton.dataset.hideTypes);
      
//       var itemDesc = document.querySelectorAll('[data-item-description]');
      
//       itemDesc.forEach(function(d) {
//          d.style.display = toggleButton.dataset.hideTypes ? 'none' : 'inline';
//       });

//       toggleButton.textContent = ( toggleButton.dataset.hideTypes ? "Show" : "Hide" ) + " Types";
//       if (toggleButton.dataset.hideTypes) {
//          delete toggleButton.dataset.hideTypes;
//       } else {
//          toggleButton.dataset.hideTypes = true;
//       }

//       toggleButton.removeAttribute('disabled');

//       console.log('(after) toggleButton.dataset.hideTypes', toggleButton.dataset.hideTypes);
//    });
   
//    var item = document.querySelectorAll('[data-item-type]');
//    item.forEach(function(i) {
//       if (!i.dataset.itemType) return;
//       var span = document.createElement('span');
//       span.setAttribute('data-item-type-description','');
//       span.appendChild(document.createTextNode(i.dataset.itemType));
      
//       var w = document.createElement('span');
//       w.setAttribute('data-item-description','');
//       w.appendChild(span);
      
//       w.insertBefore(document.createTextNode('('), w.childNodes[0] || null);
//       w.append(document.createTextNode(') '));
//       i.parentNode.insertBefore(w, i || null);
//    });
//    toggleButton.textContent="Hide Types";
//    toggleButton.dataset.hideTypes = true;
//    toggleButton.style.display = 'inline';
})();
</script>
<?php
        //var_dump($data);
        $html .= ob_get_clean();
        return $html;
    }

    private function _option_to_param(?array $options): array
    {
        !is_array($options) && $options = [];

        $param = [
            'top_element' => 'div',
            'parent_element' => 'ul',
            'child_element' => 'li',
            'ordinal_parent_element' => 'ol',
            'indent_level' => 0,
            'indent_size' => 3,
        ];

        array_walk($param, function (string &$v, string $k) use ($options) {
            isset($options[$k]) && $v = $options[$k];
        });

        return $param;
    }

    protected static function _data_to_type($data): string
    {
        if ($data === null) return 'null';

        if (is_scalar($data)) return gettype($data);

        if (is_object($data)) {
            if ("stdClass" == ($className = get_class($data))) {
                return "object";
            }
            return htmlspecialchars($className, ENT_QUOTES);
        }
        
        if (is_array($data)) {

            if (static::_is_assoc($data)) return 'hashmap';

            $lastType = null;
            $sameType = true;
            foreach ($data as $k => $v) {
                if ($lastType !== null) {
                    if (static::_data_to_type($v) !== $lastType) {
                        $sameType = false;
                        break 1;
                    }
                }
                $lastType = static::_data_to_type($v);
            }
            unset($v);

            return $sameType ? $lastType . '[]' : 'array';
        }

        return 'unknown';
    }

    private static function _ident($level = 1, $size = 3)
    {
        $ident = "";
        for ($i = 0; $i < $size * $level; $i++)
            $ident .= " ";
        return $ident;
    }

    /**
     * 
     * @see https://stackoverflow.com/questions/173400/how-to-check-if-php-array-is-associative-or-sequential
     */
    private static function _is_assoc(array $arr): bool
    {
        if (array() === $arr) return false;
        return array_keys($arr) !== range(0, count($arr) - 1);
    }

    //private static function _data_to_html($data, $parent_element, $child_element, $indent_level = 1, $indent_size = 3)
    private static function _data_to_html($data, array $options = null)
    {
        $param = static::_option_to_param($options);

        if (is_array($data) || is_object($data)) {
            $index = !is_object($data);
            $assoc = $index && static::_is_assoc($data);
            
            $i = 0;
            $type = self::_data_to_type($data);

            if (is_object($data)) {
                $type_attr = 'data-item-object="' . $type . '"';
            } else if ($type === 'hashmap' || $type === 'array') {
                $type_attr = "data-item-$type";
            } else {
                $type_attr = 'data-item-array="' . $type . '"';
            }
            

            $html = self::_ident($param['indent_level'], $param['indent_size']) . '<';
            
            $html .= $index && !$assoc ? $param['ordinal_parent_element'] : $param['parent_element'];
            $html .= " $type_attr";
            $html .= ">\n";
            foreach ($data as $key => $value) {
                $param['indent_level']++;

                $html .= self::_ident($param['indent_level'], $param['indent_size']) . "<{$param['child_element']} data-item ";

                if ($index && !$assoc) {
                    $html .= "data-index=\"$i\"";
                } else {
                    $html .= 'data-key="' . htmlspecialchars($key, ENT_QUOTES) . '"';
                }

                $html .= '>';

                false === filter_var($key, FILTER_VALIDATE_INT) && $html .= "<span data-item-key>" . htmlspecialchars($key, ENT_QUOTES) . "</span>" . ": ";

                //$html .= '<span data-item-value ' . (is_bool($value) ? 'data-item-boolean-' . ($value ? 'true' : 'false') : ($value === null ? 'data-item-null' : 'data-item-type="' . static::_data_to_type($value) . '"')) . '>';
                $html .= '<span data-item-value';
                (is_array($value) || is_object($value)) && $html .= ' data-item-type="'.static::_data_to_type($value).'"';
                $html .= '>';
                $value !== null && $html .= self::_data_to_html($value, $param);
                $html .= "</span></{$param['child_element']}><!--/data-item: (" . htmlspecialchars($key, ENT_QUOTES) . ")-->\n";
                $param['indent_level']--;
                $i++;
            }
            unset($key);
            unset($value);
            $html .= self::_ident($param['indent_level'], $param['indent_size']) . "</";
            $html .= $index && !$assoc ? $param['ordinal_parent_element'] : $param['parent_element'];
            $html .= ">\n";
            return $html;
        }

        $html = '<span data-item-type="' . static::_data_to_type($data) . '"';
        $data === '' && $html .= ' data-empty-string';
        $data === null && $html .= ' data-null-value';
        is_bool($data) && $html .= ' data-boolean-value data-boolean-' . ($data ? 'true' : 'false');
        $html .= '>';

        if (is_string($data) && ctype_print(str_replace([
            "\n",
            "\r"
        ], "", $data))) {
            $html .= htmlspecialchars($data, ENT_QUOTES);
        } else if (is_bool($data)) {
            $html .= $data ? 'true' : 'false';
        } else if (is_scalar($data)) {
            $html .= $data;
        } else {
            ob_start();
            var_dump($data);
            $dump = ob_get_clean();
            $html .= "(dump) <br><pre data-dump>" . nl2br(htmlspecialchars($dump, ENT_QUOTES)) . "</pre>";
        }

        $html .= '</span>';
        return $html;
    }
}
