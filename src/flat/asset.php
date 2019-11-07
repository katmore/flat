<?php

/*
 * \flat\asset definition 
 *
 * PHP version >=7.2
 * 
 * This file is part of the "The Flat Framework".
 * 
 * More information regarding "The Flat Framework" is available by viewing the 
 * url "https://github.com/katmore/flat" in any web browser.
 * 
 * The following copyright notice and license notice shall apply to this file 
 * and to any other part of The Flat Framework.
 * 
 * COPYRIGHT NOTICE:
 * Copyright (c) 2012-2019 Doug Bird. All Rights Reserved.
 * 
 * LICENSE NOTICE:
 * The Flat Framework is copyrighted free software.
 * 
 * You may use, modify, and distribute it under the terms and conditions of any
 * one of the following licenses: the "MIT License" (MIT); the "GNU General 
 * Public License v3.0 or later" (GPL-3.0-or-later); or the "GNU Lesser General 
 * Public License v3.0 or later" (LGPL-3.0-or-later).
 * 
 * A full copy of the MIT license is available by viewing the url
 * "https://raw.githubusercontent.com/katmore/flat/master/LICENSE" in any web 
 * browser.
 * 
 * A full copy of the GNU General Public License v3.0 is available by viewing 
 * the url "https://www.gnu.org/licenses/gpl-3.0-standalone.html" in any web 
 * browser.
 * 
 * A full copy of the GNU Lesser General Public License v3.0 is available by
 * viewing the url "https://www.gnu.org/licenses/lgpl-3.0-standalone.html" 
 * in any web browser.
 */
namespace flat;

use flat\lib\exception;
use ReflectionClass;

/**
 * provides routable and cascading resource locations
 * 
 * @abstract
 * 
 * @license MIT
 * @license GPL-3.0-or-later
 * @license LGPL-3.0-or-later
 * @author D. Bird <retran@gmail.com>
 * @copyright  Copyright (c) 2012-2019 Doug Bird. All Rights Reserved.
 */
class asset extends core\controller\asset implements core\app
{

    use asset\deprecated_methods_trait;

    /**
     * @var int "script" tag type
     */
    public const TAG_SCRIPT = 1;

    /**
     * @var int stylesheet "link" tag type
     */
    public const TAG_STYLESHEET_LINK = 2;

    /**
     * @var int asset position is in the "body" element; after the main HTML "content"
     */
    public const POSITION_BODY_AFTER_CONTENT = 4;

    /**
     * @var int asset position is inside the "head" element
     */
    public const POSITION_HEAD = 8;

    /**
     * @var int asset position is right before the end of the "body" element
     */
    public const POSITION_BODY_BEFORE_END = 16;

    /**
     * @var int asset resource type is unknown
     */
    public const RESOURCE_UNKNOWN = 32;

    /**
     * @var int javascript asset resource type
     */
    public const RESOURCE_JS = 64;

    /**
     * @var int css stylesheet asset resource type
     */
    public const RESOURCE_CSS = 128;

    /**
     * @var int[] enumeration of tag types
     */
    private const VALID_TAGS = [
        self::TAG_SCRIPT,
        self::TAG_STYLESHEET_LINK
    ];

    /**
     * @var int[] enumeration of positions that can be explicitly specified
     */
    private const VALID_POSITIONS = [
        self::POSITION_BODY_AFTER_CONTENT,
        self::POSITION_HEAD,
        self::POSITION_BODY_BEFORE_END
    ];

    /**
     * @var int[] enumeration of resource types that can be explicitly specified
     */
    private const VALID_RESOURCES = [
        self::RESOURCE_JS,
        self::RESOURCE_CSS,
    ];

    private const EXTENSION_JS = 'js';

    private const EXTENSION_CSS = 'css';

    private const VALID_CSS_EXTENSIONS = [
        'js'
    ];

    private const VALID_JS_EXTENSIONS = [
        'css'
    ];

    private const VALID_CSS_MEDIA_TYPES = [
        'text/css'
    ];

    private const VALID_JS_MEDIA_TYPES = [
        'application/javascript',
        'application/json',
    ];

    /**
     * @var int fallback asset position when registering a callback and the
     * position is unspecified and the resource type is unknown or does
     * not have a default position.
     */
    private const CATCHALL_CALLBACK_POSITION = self::POSITION_BODY_BEFORE_END;

    /**
     * @var int fallback asset position when registering an asset and the
     * position is unspecified and the resource type is unknown or does
     * not have a default position.
     */
    private const CATCHALL_RESOURCE_POSITION = self::POSITION_HEAD;

    /**
     * @var int[] default tags for valid extensions
     */
    private const IMPLICIT_EXTENSION_TAG = [
        self::EXTENSION_JS => self::TAG_SCRIPT,
        self::EXTENSION_CSS => self::TAG_STYLESHEET_LINK
    ];

    /**
     * @var int[] map of resource types to positions
     */
    private const IMPLICIT_RESOURCE_POSITION = [
        self::RESOURCE_JS => self::POSITION_BODY_AFTER_CONTENT,
        self::RESOURCE_CSS => self::POSITION_HEAD,
    ];

    /**
     * @var int[] map of path extentions to resource types
     */
    private const IMPLICIT_RESOURCE_TYPE = [
        self::EXTENSION_JS => self::RESOURCE_JS,
        self::EXTENSION_CSS => self::RESOURCE_CSS,
    ];

    private const DESC_TAG = [
        self::TAG_SCRIPT => 'TAG_SCRIPT',
        self::TAG_STYLESHEET_LINK => 'TAG_STYLESHEET_LINK',
    ];

    private const DESC_POSITION = [
        self::POSITION_BODY_AFTER_CONTENT => 'POSITION_BODY_AFTER_CONTENT',
        self::POSITION_HEAD => 'POSITION_HEAD',
        self::POSITION_BODY_BEFORE_END => 'POSITION_BODY_BEFORE_END',
    ];

    private const DESC_RESOURCE = [
        self::RESOURCE_JS => 'RESOURCE_JS',
        self::RESOURCE_CSS => 'RESOURCE_CSS',
    ];

    /**
     * @var \flat\asset[] assoc array with an element value containing each registered asset or callback object; 
     * the element keys correspond to the unique hash of the asset or callback
     * @static
     */
    private static $register_asset = [];

    /**
     * @var string[] sequential array containing each registered asset's unique hash in the
     *    order they were registered.
     * @static
     */
    private static $register_hash = [];

    /**
     * @var int[] assoc array with an element value containing the position of each registered asset or callback; 
     * the element keys correspond to the unique hash of the asset or callback
     * @static
     */
    private static $register_position = [];

    /**
     * @var \flat\asset[] assoc array with an element value containing a nested assoc array for each registered asset or callback; 
     * the element keys correspond to the unique hash of the asset or callback, this nested assoc array in turn contains an element
     * with the value of any asset or callback objects that a registered asset or callback "depends" upon, this nested array's element keys
     * correspond to the unique hash of the asset or callback that is being  dependened upon. 
     * @static
     */
    private static $reigster_depends = [];

    /**
     * @var array[] assoc array with an element value containing a nested assoc array for each registered asset or callback; 
     * the element keys correspond to the unique hash of the asset or callback, this nested assoc array in turn contains an element
     * with the value of any parameter arrays needed to configure the asset or callback objects that a registered asset or 
     * callback "depends" upon, this nested array's element keys correspond to the unique hash of the asset or callback that is 
     * being  dependened upon. 
     * @static
     */
    private static $reigster_depends_param = [];

    /**
     * @var callable[] assoc array with an element value of each callback object; 
     * the element keys correspond to the unique hash of the callback
     * @static
     */
    private static $register_callback_resource = [];

    /**
     * @var string[] bunlde assoc array containing an assoc array for each unique "bundle" that has been decalred, 
     * the elements values of the 1st dimension contain contain a nested sequenetial array with the array keys corresponding 
     * to the bundle name; this sequential array contains an element for 
     * each item in the corresponding bundle.
     * @static
     */
    private static $bundle = [];

    /**
     * @return int asset resource type
     */
    public function get_resource_type(): int
    {
        if (empty($ext = $this->get_pathinfo(PATHINFO_EXTENSION)) || !isset(self::IMPLICIT_RESOURCE_TYPE[$ext])) {
            return self::RESOURCE_UNKNOWN;
        }
        return self::IMPLICIT_RESOURCE_TYPE[$ext];
    }

    /**
     * retrieves asset's URL when object invoked as string
     *
     * @uses \flat\asset::get_url()
     * @return string
     */
    public function __toString()
    {
        try {
            return $this->get_url();
        } catch (\Exception $e) {
            trigger_error("magic method asset::__toString() failed because exception " . get_class($e) . " code: " . $e->getCode() . " message: \"" . $e->getMessage() . "\" thrown by " . static::class . "::get_url()", E_USER_NOTICE);
            return "";
        }
    }

    /**
     * sets a new resource and provides the URL
     *
     * @see get_url()
     * @return string
     */
    public function __invoke(string $resource = ""): string
    {
        if ($this->get_resource() !== $resource) {
            $this->set_resource($resource);
        }
        return $this->get_url();
    }

    /**
     * @return void
     */
    public function print_data_uri($mtype = null): void
    {
        !is_string($mtype) && $mtype = mime_content_type($this->get_system());
        echo "data:$mtype;base64,";
        echo base64_encode(file_get_contents($this->get_system()));
    }

    /**
     * @return bool
     */
    public function exists_on_system(): bool
    {
        if (file_exists($this->get_system()) && is_file($this->get_system())) {
            return true;
        }
        return false;
    }

    /**
     * provides an asset's system path.
     *
     * @return string|null
     */
    public function get_system()
    {
        if (!$this instanceof asset\system_base) {
            return null;
        }
        $path = $this->_get_system_base();
        if (empty($this->get_resource())) return $path;
        return "$path/" . $this->get_resource();
    }

    /**
     * retrieves asset's URL
     *
     * @return string
     */
    public function get_url()
    {
        $url = "";
        if ($this instanceof asset\base) {
            $url .= $this->_get_base();
        }
        if (empty($this->get_resource())) return $url;
        return "$url/" . $this->get_resource();
    }

    private function print_system_contents(array $valid_extensions, array $valid_media_types)
    {
        if ($this->get_system() === null) {
            throw new exception\app_error('cannot determine system path for resource: ' . $this->get_resource());
        }
        if (!is_file($this->get_system())) {
            throw new exception\app_error('system resource file not found: ' . $this->get_system());
        }
        if (!is_readable($this->get_system())) {
            throw new exception\app_error('system resource file not readable: ' . $this->get_system());
        }
        if (!in_array($ext = strtolower(pathinfo($this->get_system(), PATHINFO_EXTENSION), $valid_extensions)) && !in_array(mime_content_type($this->get_system()), $valid_media_types)) {
            throw new exception\app_error('system resource file does not have a valid extension or media type: ' . $this->get_system());
        }
        if (false === ($content = file_get_contents($this->get_system()))) {
            throw new exception\app_error('failed to read system resource file');
        }
        echo trim($content);
    }

    /**
     * prints a style tag with contents of resource's system path (file).
     *
     * @return void
     *
     * @throws \flat\lib\exception\app_error if resource does not resolve on system to a .css file
     * @uses \flat\asset\system_base
     */
    public function print_contents_style_tag(): void
    {
        echo "<!--START style asset contents: {$this->get_pathinfo(PATHINFO_BASENAME)} " . static::class . "-->\n";
        echo "<style>\n";
        $this->print_system_contents(self::VALID_CSS_EXTENSIONS, self::VALID_CSS_MEDIA_TYPES);
        echo "\n</style>\n";
        echo "<!--END style asset contents: {$this->get_pathinfo(PATHINFO_BASENAME)} " . static::class . "-->\n";
    }

    /**
     * prints a script tag with contents of resource's system path (file).
     *
     * @return void
     *
     * @throws \flat\lib\exception\app_error if resource does not resolve on system to a .js file
     * @uses \flat\asset\system_base
     */
    public function print_contents_script_tag(): void
    {
        echo "<!--START script asset contents: {$this->get_pathinfo(PATHINFO_BASENAME)} " . static::class . "-->\n";
        echo "<script>\n";
        $this->print_system_contents(self::VALID_JS_EXTENSIONS, self::VALID_JS_MEDIA_TYPES);
        echo "\n</script>\n";
        echo "<!--END script asset contents: {$this->get_pathinfo(PATHINFO_BASENAME)} " . static::class . "-->\n";
    }

    /**
     * Prints a &lt;script> element for a javascript resource.
     * Example: &lt;script src="http://example.com/my-javascript.js">&lt;/script>
     *
     * @return void
     */
    protected function print_js_script_tag(): void
    {
        echo "<!--js script asset tag: " . static::class . "-->";
        echo '<script src="' . trim(htmlentities($this->get_url())) . '"></script>' . "\n";
    }

    /**
     * Prints a &lt;link> element for a stylesheet resource.
     * Example: &lt;link href="http://example.com/my-stylesheet.css" rel="stylesheet">
     *
     * @return void
     */
    protected function print_css_link_tag(): void
    {
        echo "<!--css link asset tag: " . static::class . "-->";
        echo '<link href="' . trim(htmlentities($this->get_url())) . '" rel="stylesheet">' . "\n";
    }

    /**
     * Prints an HTML element using the resource's url.
     *
     * @param int $tag_type
     * 
     * @return void
     */
    public function print_tag(int $tag_type = null): void
    {
        if (!in_array($tag_type, self::VALID_TAGS, true)) {

            $ext = pathinfo($this->get_url(), PATHINFO_EXTENSION);

            if (!isset(self::IMPLICIT_EXTENSION_TAG[$ext])) return;

            $tag_type = self::IMPLICIT_EXTENSION_TAG[$ext];
        }

        switch ($tag_type) {
            case self::TAG_SCRIPT:
                $this->print_js_script_tag();
                return;
            case self::TAG_STYLESHEET_LINK:
                $this->print_css_link_tag();
                return;
        }
    }

    /**
     * @return void
     */
    private static function each_dependency(array &$called, callable $callback, string $asset_hash, int $position, asset $asset = null): void
    {
        if (isset(self::$reigster_depends[$asset_hash])) {
            foreach (self::$reigster_depends[$asset_hash] as $depends_asset_hash => $depends_asset) {
                if (isset(self::$reigster_depends[$depends_asset_hash])) {
                    static::each_dependency($called, $callback, $depends_asset_hash, $position, $asset);
                }
                if (!in_array($depends_asset_hash, $called)) {
                    if ($depends_asset instanceof asset || is_callable($depends_asset)) {
                        $callback($depends_asset, $position, $asset);
                    }
                    $called[] = $depends_asset_hash;
                }
            }
            unset($depends_asset_hash);
            unset($depends_asset);
        }
    }

    /**
     * Prints registered resource tags for a given position
     * 
     * @param int $position
     * <ul>
     *   <li>
     *     <b>\flat\asset::POSITION_BODY_AFTER_CONTENT</b>
     *     asset position is in the "body" element; after the main HTML "content"
     *   </li>
     *   <li>
     *     <b>\flat\asset::POSITION_HEAD</b>
     *     asset position is inside the "head" element
     *   </li>
     *   <li>
     *     <b>\flat\asset::POSITION_BODY_BEFORE_END</b>
     *     asset position is right before the end of the "body" element
     *   </li>
     * </ul>
     *  
     * @return void
     */
    final public static function print_registered_tags(int $position): void
    {
        static::each_registered_asset(function (asset $asset) {
            echo "   ";
            $asset->print_tag();
        }, $position);
    }

    /**
     * Executes a callback function for registered assets.
     * Things that go {@see \flat\asset::POSITION_BODY_AFTER_CONTENT}
     *
     * @param callable $callback Function to execute for each matching registered asset.
     *    Callback signature:
     *    <code> 
     *    function(\flat\asset|callable $asset, int $position, \flat\asset $parent_asset=null) {};
     *    </code>
     *
     * @param int $filter optional filter bitmask:
     * <ul>
     *    <li>
     *       <b>\flat\asset::POSITION_BODY_AFTER_CONTENT</b>
     *       asset position is the end of the &lt;body> element (before the closing &lt;/body> tag)
     *    </li>
     *    <li>
     *       <b>\flat\asset::POSITION_HEAD</b>
     *       asset position is inside the &lt;head> element
     *    </li>
     *    <li>
     *       <b>\flat\asset::POSITION_BODY_BEFORE_END</b>
     *       asset position is after a logical "ready" event has occurred
     *    </li>
     *    <li>
     *       <b>\flat\asset::RESOURCE_JS</b>
     *       javascript asset resource type
     *    </li>
     *    <li>
     *       <b>\flat\asset::RESOURCE_CSS</b>
     *       css stylesheet asset resource type
     *    </li>
     * </ul>
     *
     * @return void
     */
    final public static function each_registered_asset(callable $callback, int $filter = 0): void
    {
        $called = [];
        foreach (self::$register_hash as $asset_hash) {

            $asset = self::$register_asset[$asset_hash];

            if ($asset instanceof asset) {
                $resource_type = $asset->get_resource_type();
                if (isset(self::$register_position[$asset_hash])) {
                    $position = self::$register_position[$asset_hash];
                } else if (isset(self::IMPLICIT_RESOURCE_POSITION[$resource_type])) {
                    $position = self::IMPLICIT_RESOURCE_POSITION[$resource_type];
                } else {
                    $position = self::CATCHALL_RESOURCE_POSITION;
                }
            } elseif (is_callable($asset)) {
                $resource_type = self::$register_callback_resource[$asset_hash];
                $position = self::CATCHALL_CALLBACK_POSITION;
            }

//             $positionFilterMask = $position & $filter;
//             $resourceTypeFilterMask = $resource_type & $filter;
//             echo "resource_type: $resource_type, position: $position, filter: $filter, positionFilterMask: $positionFilterMask, resourceTypeFilterMask: $resourceTypeFilterMask\n";

            $filterByResource = false;
            $filterByPosition = false;

            if ($filter === 0 || ($position & $filter) || ($resource_type & $filter)) {
                static::each_dependency($called, $callback, $asset_hash, $position, $asset instanceof asset ? $asset : null);
                if (!in_array($asset_hash, $called)) {
                    $callback($asset, $position);
                    $called[] = $asset_hash;
                }
            }
        }
        unset($asset_hash);
//         echo "\n\n";
//         debug_print_backtrace();
//         die(__FILE__);
    }

    /**
     * Prints registered assets.
     *
     * @param int $filter optional filter bitmask:
     * <ul>
     *    <li>
     *       <b>\flat\asset::POSITION_BODY_AFTER_CONTENT</b>
     *       asset position is at the end of the &lt;body> element (before the closing &lt;/body> tag)
     *    </li>
     *    <li>
     *       <b>\flat\asset::POSITION_HEAD</b>
     *       asset position is inside the &lt;head> element
     *    </li>
     *    <li>
     *       <b>\flat\asset::POSITION_BODY_BEFORE_END</b>
     *       asset position is after a logical "ready" event has occurred
     *    </li>
     *    <li>
     *       <b>\flat\asset::RESOURCE_JS</b>
     *       the resource type of the registered asset is "js" (javascript)
     *    </li>
     *    <li>
     *       <b>\flat\asset::RESOURCE_CSS</b>
     *       the resource type of the registered asset is "css" (stylesheet)
     *    </li>
     * </ul>
     *
     * @return void
     */
    final public static function print_registered_assets(int $filter = 0): void
    {
        static::each_registered_asset(function ($asset, int $position, asset $parent_asset = null) {
            //(\flat\asset|callable $asset, int $position)
            if ($asset instanceof asset) {
                $asset->print_tag();
            } elseif (is_callable($asset)) {
                $asset($parent_asset);
            }
        }, $filter);
    }

    /**
     * Registers this asset for future processing.
     *
     * @param array $param assoc array of parameters:
     * <ul>
     *    <li>
     *       <b>int $param['position']</b> optionally specify a position:
     *       <ul>
     *          <li>
     *             <b>\flat\asset::POSITION_BODY_AFTER_CONTENT</b>
     *             asset position is in the "body" element; after the main HTML "content"
     *          </li>
     *          <li>
     *             <b>\flat\asset::POSITION_HEAD</b>
     *             asset position is inside the "head" element
     *          </li>
     *          <li>
     *             <b>\flat\asset::POSITION_BODY_BEFORE_END</b>
     *             asset position is right before the end of the "body" element
     *          </li>
     *       </ul>
     *    </li>
     * </ul>
     */
    final public function register(array $param = null): asset
    {
        static::register_asset($this, $param);
        return $this;
    }

    /**
     * Adds the asset to a bundle.
     * 
     * @return \flat\asset
     */
    final public function bundle(string $bundle_name): asset
    {
        !isset(self::$bundle[$bundle_name]) && self::$bundle[$bundle_name] = [];

        $asset_hash = $this->get_hash();

        !isset(self::$bundle[$bundle_name][$asset_hash]) && self::$bundle[$bundle_name][$asset_hash] = $this;

        return $this;
    }

    /**
     * @return void
     * @static
     */
    final public static function each_bundle_asset(string $bundle_name, callable $callback): void
    {
    }

    /**
     * Registers an asset dependency.
     * 
     * @param int|\flat\asset $asset_position asset position or asset dependency
     * @param \flat\asset[] $asset_dependency,... one or more asset dependencies
     * 
     * @return \flat\asset
     */
    final public function depends($asset_position = null, ...$asset_dependency): asset
    {
        $depends_param = [];
        if (in_array($asset_position, self::VALID_POSITIONS, true)) {
            $depends_param['position'] = $asset_position;
        }

        if ($asset_position instanceof asset || is_callable($asset_position)) {
            array_unshift($asset_dependency, $asset_position);
        }

        count($asset_dependency) === 1 && $asset_dependency = [
            $asset_dependency[0]
        ];

        foreach ($asset_dependency as $depends) {

            !is_array($depends) && $depends = [
                $depends
            ];

            static::register_depends($this, $depends, $depends_param);
        }
        unset($depends);

        return $this;
    }

    private $hash;

    /**
     * @return string unique hash for this asset
     */
    public function get_hash(): string
    {
        if ($this->hash !== null) return $this->hash;
        return self::concat_hash($this->get_resource_type(), static::class, $this->get_resource());
    }

    /**
     * @return string
     * @static
     */
    private static function concat_hash(int $resource_type, string $asset_class, string $resource_uri): string
    {
        return "$resource_type.$asset_class.$resource_uri";
    }

    /**
     * @static
     * @return void
     */
    private static function register_depends(asset $asset, array $depends, array $param = null): void
    {
        $param === null && $param = [];

        $asset_hash = $asset->get_hash();

        $depends_param = $param;

        unset($depends_param['depends']);

        $depends_asset_param = $depends_param;

        unset($depends_asset_param['position']);

        foreach ($depends as $depends_asset) {

            $depends_asset_load_param = $depends_asset_param;
            $depends_param_asset = $depends_param;
            $depends_asset_depends = null;

            if (is_array($depends_asset) && isset($depends_asset['asset'])) {

                $depends_asset_load_param = array_merge($depends_asset_param, $depends_asset);
                unset($depends_asset_load_param['position']);
                unset($depends_asset_load_param['asset']);
                unset($depends_asset_load_param['depends']);

                $depends_param_asset = array_merge($depends_param_asset, $depends_asset);
                unset($depends_param_asset['asset']);

                if (isset($depends_asset['depends']) && is_array($depends_asset['depends'])) {
                    $depends_asset_depends = $depends_asset['depends'];
                }

                $depends_asset = $depends_asset['asset'];
            }

            if (is_array($depends_asset_depends)) {
                static::register_depends($asset, $depends_asset_depends, $depends_param_asset);
            }

            if (is_array($depends_asset)) {
                static::register_depends($asset, $depends_asset, $depends_param_asset);
                continue;
            }

            if (is_string($depends_asset)) {
                $depends_asset = static::asset($depends_asset, $depends_asset_load_param);
            }

            if ($depends_asset instanceof asset) {
                $depends_asset_hash = $depends_asset->get_hash();
            } else if (is_callable($depends_asset)) {
                $depends_asset_hash = uniqid("callback." . get_class($asset) . ".", true);
            }

            if (!isset(self::$reigster_depends[$asset_hash][$depends_asset_hash])) {
                self::$reigster_depends[$asset_hash][$depends_asset_hash] = $depends_asset;
                unset($depends_param_asset['position']);
                self::$reigster_depends_param["$asset_hash.$depends_asset_hash"] = $depends_param_asset;
            }
        }
        unset($depends_asset);
    }

    /**
     * @param callable|string $html if callable, it will be executed, when the corresponding registered asset is executed.
     * @param array $param optional parameters
     * 
     * @return \flat\asset
     * @static
     */
    final public static function register_html($html, array $param = null): asset
    {
        return static::register_output($html, $param);
    }

    final public static function register_html_special($html_position_type = null, ...$html): asset
    {
        $output_param = [];
        if (in_array($html_position_type, self::VALID_POSITIONS, true)) {
            $output_param['position'] = $html_position_type;
        }
        if (is_string($html_position_type) || is_callable($html_position_type)) {
            array_unshift($html, $html_position_type);
        }
        count($html) === 1 && $html = [
            $html[0]
        ];

        return static::register_output($html, $output_param);
    }

    /**
     * @param callable|string $output if callable, it will be executed when the corresponding registered asset is executed.
     * @param array $param optional parameters
     * 
     * @return \flat\asset
     * @static
     */
    private static function register_output($output, array $param = null): asset
    {
        $param === null && $param = [];

        if (isset($param['resolved'])) {
            $asset = $param['resolved'];
        } else {

            $asset = $resource = null;

            if (isset($param['asset'])) {
                $param['asset'] instanceof asset && $asset = $param['asset'];
                unset($param['asset']);
            }

            if (isset($param['resource'])) {
                is_string($param['resource']) && $resource = $param['resource'];
                unset($param['resource']);
            }

            if (!isset($param['hash']) && (!isset($param['hash_handle_prefix']) || !is_string($param['hash_handle_prefix']))) {
                $param['hash_handle_prefix'] = "output.";
            }

            $resolved = false;
            if (!$asset instanceof asset) {
                $asset = static::asset($resource !== null ? $resource : "", $param);
                $resource = null;
                $resolved = false;
            }
            if ($resource !== null || !$resolved) {
                $asset = $asset::asset($resource !== null ? $resource : "", $param);
                $resolved = false;
            }

            if (isset($param['hash'])) unset($param['hash']);
            if (isset($param['hash_handle_prefix'])) unset($param['hash_handle_prefix']);
        }
        if (is_array($output)) {

            $param['resolved'] = $asset;

            foreach ($output as $o) {
                static::register_output($o, $param);
            }
            unset($o);

            return $asset;
        }

        if (is_callable($output)) {
            $callback = $output;
        } else if (is_string($output)) {
            $callback = function () use (&$output) {
                echo $output;
            };
        } else {
            return $asset;
        }

        $asset_hash = $asset->get_hash();
        if (!isset(self::$register_asset[$asset_hash])) {

            if (isset($param['type']) && in_array($param['type'], self::VALID_RESOURCES)) {
                $resource_type = $param['type'];
            } else {
                $resource_type = self::RESOURCE_UNKNOWN;
            }

            if (isset($param['position']) && in_array($param['position'], self::VALID_POSITIONS, true)) {
                $position = $param['position'];
            } else {
                if (isset(self::IMPLICIT_RESOURCE_POSITION[$resource_type])) {
                    $position = self::IMPLICIT_RESOURCE_POSITION[$resource_type];
                } else {
                    $position = self::CATCHALL_CALLBACK_POSITION;
                }
            }

            self::$register_hash[] = $asset_hash;
            self::$register_asset[$asset_hash] = $callback;
            self::$register_position[$asset_hash] = $position;
            self::$register_callback_resource[$asset_hash] = $resource_type;
            self::$reigster_depends[$asset_hash] = [];
        }

        if (isset($param['type'])) unset($param['type']);

        return $asset->register($param);
    }

    /**
     * Registers an asset for future processing.
     * 
     * @return void
     * @static
     */
    public static function register_asset($asset = "", array $param = null): asset
    {
        $depends = [];
        if (is_array($param)) {
            $dk = [];
            foreach ($param as $k => $v) {
                if ($v instanceof asset) {
                    $depends[] = $v;
                    $dk[] = $k;
                }
            }
            unset($k);
            unset($v);

            foreach ($dk as $k) {
                unset($param[$k]);
            }
            unset($k);
            unset($dk);

            if (isset($param['depends'])) {
                if (is_array($param['depends'])) {
                    $depends = array_merge($depends, $param['depends']);
                }
                unset($param['depends']);
            }
        }

        if (!$asset instanceof asset) {
            $asset = static::asset($asset, $param);
        }



        $asset_hash = $asset->get_hash();

        if (!isset(self::$register_asset[$asset_hash])) {

            if (isset($param['position'])) {
                if (false === ($position = filter_var($param['position'], FILTER_VALIDATE_INT))) {
                    throw new exception\bad_param("position", 'must be an integer');
                }
                if (!in_array($position, self::VALID_POSITIONS, true)) {
                    throw new exception\bad_param("position", 'unknown position');
                }
            } else {

                $resource_type = $asset->get_resource_type();

                if (isset(self::IMPLICIT_RESOURCE_POSITION[$resource_type])) {
                    $position = self::IMPLICIT_RESOURCE_POSITION[$resource_type];
                } else {
                    $position = self::CATCHALL_RESOURCE_POSITION;
                }
            }

            self::$register_hash[] = $asset_hash;

            self::$register_asset[$asset_hash] = $asset;

            self::$register_position[$asset_hash] = $position;

            self::$reigster_depends[$asset_hash] = [];
        }

        if (count($depends)) {
            self::register_depends($asset, $depends, is_array($param) ? $param : []);
        }

        return $asset;
    }

    /**
     * @return \flat\core\controller\asset
     */
    protected function on_resolve_ready(core\controller\asset $resolved_asset): core\controller\asset
    {
        return $resolved_asset instanceof asset ? $resolved_asset : $this;
    }

    /**
     * Creates and resolves an asset object.
     *
     * @see \flat\core\controller\asset::__construct()
     * @see \flat\core\controller\asset::resolve()
     * 
     * @return \flat\asset
     */
    public static function asset($resource = "", $param = null)
    {
        !is_array($param) && $param = [];

        return (new static($resource))->resolve($param);
    }

    /**
     * @return string
     */
    protected function on_resolve_param(string $asset, string $key, $value): string
    {
        if ($key === 'hash_handle_prefix') {
            if (is_string($value) && $this->hash === null) {
                //$this->hash = $value;
                $this->hash = uniqid("$value." . static::class . '.', true);
            }
            return $asset;
        }
        if ($key === 'hash') {
            if (is_string($value) && $this->hash === null) {
                $this->hash = $value;
            }
            return $asset;
        }
        if ($key === 'cascade') {

            $base = "\\" . static::class;

            if (is_string($value)) {
                $base = str_replace("/", "\\", $value);
            }

            if (substr($base, 0, 1) == "\\" && class_exists($base) && is_a($base, '\flat\asset', true)) {
                $asset = $base;
            } else {
                if (class_exists("$asset\\$base") && is_a("$asset\\$base", '\flat\asset', true)) {
                    if ((new ReflectionClass("$asset\\$base"))->isInstantiable()) $asset = "$asset\\$base";
                }
            }

            /*
             * convert path to namespace to check if it's relative class reference
             */
            $ns = str_replace("/", "\\", $this->get_resource()); // convert slashes
            $base = $this->get_pathinfo(PATHINFO_BASENAME);
            $ns = preg_replace('/^([^\.]*).*$/', '$1', $ns); // remove file extension if has one
            $ns = "$asset\\$ns";

            /*
             * check relative resource without file extension is an asset
             */
            $ns_instance = false;
            if (class_exists($ns) && is_a($ns, '\flat\asset', true)) {
                if ((new ReflectionClass($ns))->isInstantiable()) {
                    $ns_instance = true;
                    $asset = $ns;
                    $ns = explode("\\", $ns);

                    $this->set_resource($base);
                }
            }

            /*
             * if relative resource wasnt asset yet...
             *    remove one level at a time from resource and check it
             */
            if (!$ns_instance) {
                $reslevel = [
                    $base
                ];
                $ns = str_replace("/", "\\", $this->get_resource()); // convert slashes
                $base = $this->get_pathinfo(PATHINFO_BASENAME);
                $ns = preg_replace('/^([^\.]*).*$/', '$1', $ns); // remove file extension if has one
                $ns = "$asset\\$ns";
                $nslevel = explode("\\", $ns);
                if (count($nslevel) > 1) {
                    $level_count = count($nslevel);
                    for ($i = 0; $i < $level_count; $i++) {
                        $ns_resource = array_pop($nslevel);
                        if ($i != 0) $reslevel[] = $ns_resource;
                        $ns = implode("\\", $nslevel);
                        if (class_exists($ns) && is_a($ns, '\flat\asset', true)) {
                            if ((new ReflectionClass($ns))->isInstantiable()) {
                                $ns_instance = true;
                                $asset = $ns;
                                $this->set_resource(implode("\\", array_reverse($reslevel)));
                                break 1;
                            }
                        }
                    }
                }
            }

            return $asset;
        }

        return parent::on_resolve_param($asset, $key, $value);
    }

    /**
     * @return void
     */
    protected function on_resource_ready(array $param = null): void
    {
        $param === null && $param = [];

        if ($this instanceof asset\resource\transform) {
            if (is_string($resource = $this->get_resource_transform($this->get_resource()))) {
                $this->set_resource($resource);
            }
        }

        if (isset($param['hash'])) {
            if (is_string($param['hash']) && $this->hash === null) {
                $this->hash = $param['hash'];
            }
        }

        parent::on_resource_ready($param);
    }
}
