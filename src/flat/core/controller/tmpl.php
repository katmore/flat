<?php

/*
 * This file is part of the The Flat Framework
 *
 * PHP version >=7.2
 * 
 * Copyright (c) 2012-2020 Doug Bird - <retran@gmail.com>. All Rights Reserved.
 * This software is distributed under the terms of the MIT license or the GNU General Public License v3.0.
 */
namespace flat\core\controller;

use flat\tmpl\data;
use flat\core\md;
use flat\core\config;
use flat\tmpl\design_base;
use flat\tmpl\resolvable_design;
use flat\tmpl\output;
use flat\tmpl\data_provider;

/**
 * template controller
 */
abstract class tmpl implements \flat\core\controller, \flat\core\resolver\prepared
{

    /**
     * @var string design prefix
     */
    const DESIGN_PREFIX = 'tmpl';

    /**
     * threshold as expressed in bytes at which tmpl::display() will read
     *    non-php template file in chunks to avoid memory overruns
     */
    const MIN_CHUNKING_SIZE = 102400;

    /**
     * @var int
     * @static
     */
    protected static $logger_message_type = 0;

    /**
     * @var string
     * @static
     */
    protected static $logger_destination;

    /**
     * @var string
     * @static
     */
    protected static $logger_extra_headers;

    /**
     * @var bool
     * @static
     */
    protected static $logger_enabled = false;

    protected static $design_class_prefix = '';

    /**
     * @var callable
     */
    private static $display_handler;

    private static function format_design_prefix(): ?string
    {
        $design_prefix = trim(str_replace('/', '\\', static::DESIGN_PREFIX), '\\');

        return !empty($design_prefix) ? $design_prefix : null;
    }

    //min size is 10kB
    final public static function check_design($design)
    {
        try {
            static::display($design, NULL, true);
            return true;
        } catch (tmpl\exception\bad_design $e) {
            return false;
        }
    }

    /**
     * part of the \flat\core\resolver\prepared interface
     *
     * @see \flat\core\resolver\prepared
     */
    final public function set_prepared_on()
    {
        if ($this instanceof data_provider) {
            
            
            if ($this instanceof \flat\tmpl\ignore) return;
            if ($this instanceof \flat\tmpl\ignorable) {
                if ($this->is_ignored()) return;
            }
            return $this->resolve_display($this->get_data());
        }
        if ($this instanceof \flat\tmpl\ignorable) {
            if ($this->is_ignored()) return;
            return $this->resolve_display();
        }
    }

    /**
     * defaults to
     *    function($design,$data) {
     *       return static::display($design,$data);
     *    };
     * @param callable $handler signature:
     *    callback($design,$data)
     */
    final public static function set_display_handler(callable $handler)
    {
        self::$display_handler = $handler;
    }

    final public static function get_display_handler(): callable
    {
        if (self::$display_handler === null) return function ($design, $data) {
            return self::display($design, $data);
        };
        return self::$display_handler;
    }

    /**
     * @param mixed $data data to pass to template design
     *
     * @uses \flat\tmpl\design retrieves the template design explicitly
     *    if inherited class implements this interface
     *
     * @throws \flat\core\controller\tmpl\exception\bad_design when cannot resolve design
     */
    final public function __construct()
    {
        if ((!$this instanceof data_provider) && (!$this instanceof \flat\tmpl\ignorable)) {
            if ($this instanceof \flat\tmpl\ignore) return;

            return $this->resolve_display();
        }
    }

    /**
     * displays a design template as formatted by given parameters
     *
     * @return void|string
     * @static
     * @param array $param assoc array of parameters:
     *    mixed $param['data'] (optional) data to pass to template design.
     *
     *    string $flags[]='no_display' exists: formatted template
     *       will be returned as string, rather than being immediately
     *       displayed.
     *    string $flags[]='strip_tags' exists:  strips all HTML
     *       tags.
     *    string $flags[]='nl2br' exists: converts all newlines to
     *       'br' tags.
     *    string $flags[]='htmlescape' exists:  escapes all contents
     *       (even HTML tags) with HTML entities.
     *
     * @todo string $flags[]='textescape' exists: escapes all contents
     *       except HTML tags with HTML entities.
     *
     * @todo array[]string[] $param['replace'] array of assoc arrays:
     *    (string) 'search'=> (string) 'replace', search and replace within
     *    template.
     *
     * @see \flat\core\controller\tmpl::display() for possible exceptions.
     */
    final public static function format($design, array $flags = NULL, array $params = NULL)
    {
        $data = null;
        if (is_array($params) && isset($params['data'])) {
            $data = $params['data'];
        }


        if ($flags && (false !== ($idx = array_search('silent_fail', $flags)))) {
            try {
                $sflags = $flags;
                array_splice($sflags, $idx, 1);
                return self::format($design, $sflags, $params);
            } catch (\Exception $e) {
                return '';
            }
        } else {
            //          if (get_called_class()=='actvp\mail\tmpl\activepitch\artist') {
            //             throw new \Exception(print_r($data,true));
            //          }
            ob_start();
            static::display($design, $data);
            $ob = ob_get_clean();
        }

        /**
         * $var string strip_tags should be first
         * @todo replace
         */
        if ($flags && in_array('strip_tags', $flags)) $ob = strip_tags($ob);

        /*
         * nl2br should be after strip_tags, or else could have no effect
         */
        if ($flags && in_array('nl2br', $flags)) $ob = nl2br($ob);

        if ($flags && in_array('htmlescape', $flags)) $ob = htmlentities($ob);

        if ($params && isset($params['replace']) && is_array($params['replace'])) {
            foreach ($params['replace'] as $find => $replace) {
                $ob = str_replace($find, $replace, $ob);
            }
        }
        if ($flags && in_array('no_display', $flags)) return $ob;

        echo $ob;
    }

    public static function set_design_class_prefix(string $class_prefix): void
    {
        self::$design_class_prefix = $class_prefix;
    }

    /**
     * Enables logger of template design paths using error_log().
     *
     * @param int $message_type Optional. See: https://www.php.net/manual/en/function.error-log.php
     * @param string $destination Optional. See: https://www.php.net/manual/en/function.error-log.php
     * @param string $extra_headers Optional. See: https://www.php.net/manual/en/function.error-log.php
     *
     * @return void
     * @see error_log()
     * @static
     */
    public static function enable_design_logger(int $message_type = 0, string $destination = null, string $extra_headers = null): void
    {
        static::$logger_enabled = true;
        static::$logger_message_type = $message_type;
        static::$logger_destination = $destination;
        static::$logger_extra_headers = $extra_headers;
    }

    /**
     * Disables logger of template design paths.
     *
     * @return void
     * @see \flat\core\controller\tmpl::enable_design_logger()
     * @static
     */
    public static function disable_design_logger(): void
    {
        static::$logger_enabled = false;
    }

    /**
     * displays a design template
     *
     * @return void
     * @static
     * @param string $design filename or namespace that is resolved into a filename
     * @param mixed $data (optional) data to pass to template design
     *
     * @see \flat\core\controller\tmpl::get_design_root_dir() template designs are placed in flat\design path
     * @throws \flat\core\controller\tmpl\exception\bad_design when cannot resolve design definition
     */
    public static function display($design, $data = null, $check_only = false)
    {
        $orig_design = $design;

        /*
         * skip all resolving if design is a display class
         */
        if (is_string($design) && (substr($design, 0, 1) == '\\')) {
            if (class_exists($design) && is_a($design, tmpl\display::class, true)) {
                $design = new $design($design, $check_only);
            }
        }

        if (is_object($design)) {
            if ($design instanceof resolvable_design) {
                if ($check_only) return;
                static::design_log('resolvable_design object', get_class($design));
                
                if (!$data instanceof data) $data = new data((array) $data);
                $design->resolvable_design_output($data);
                return;
            }
            if (!$design instanceof output) {
                throw new tmpl\exception\bad_design('design object must be an instance-of any of the following: ' . implode(', ', [
                    resolvable_design::class,
                    output::class
                ]));
            }
            $output = $design->get_output($data, $check_only);

            static::design_log('object', get_class($design));

            if ($output !== null) {
                echo $output;
            }
            return;
        }

        /*
         * enforce sanity on $design arg
         */
        if (!is_string($design)) throw new tmpl\exception\bad_design('design must be a string or object instead got: ' . gettype($design));


        /*
         * transform all acceptable separators to backslash for consistency
         */
        $design = str_replace('/', '\\', $design);

        /*
         * transform relative to absolute design path
         *    as appropriate
         */
        if (substr($design, 0, 1) !== '\\') {

            if (is_a(get_called_class(), design_base::class, true)) {
                
                $design_base = trim(static::get_design_base(), '\\');
                if (substr($design, 0, strlen($design_base)) !== $design_base) {
                    $design = $design_base . '\\' . $design;
                }
            }
        }

        $design_class_prefix = trim(static::get_design_class_prefix(), '\\');

        $design_class_suffix = $design;

        if (null !== ($f_design_prefix = static::format_design_prefix())) {
            if (substr($design_class_suffix, 0, $f_design_prefix_len = strlen($f_design_prefix)) === $f_design_prefix) {
                $design_class_suffix = substr($design_class_suffix, $f_design_prefix_len);
            }
        }

        $design_class = str_replace('-','_',"$design_class_prefix\\$design_class_suffix");

        if (class_exists($design_class) && is_a($design_class, resolvable_design::class, true)) {
            
            if ($check_only) return;
            static::design_log('resolvable_design class', $design_class);

            $design_class = new $design_class();
            
            if (!$data instanceof data) $data = new data((array) $data);
            $design_class->resolvable_design_output($data);

            return;
        }

        $file = null;

        /*
         * skip remaining resolving logic if design is already full path to a php file
         */
        if (substr($design, 0, 1) == '\\') {
            $design_file = str_replace('\\', DIRECTORY_SEPARATOR, $file);
            if (is_file($design_file) && is_readable($design_file)) {
                if (pathinfo($design_file, PATHINFO_EXTENSION) == 'php') {
                    $file = $design_file;
                }
            }
        }

        /*
         * determine if resource resolves to a php filename
         *    if so...load template with a 'require' and return void.
         */
        if (empty($file)) {

            $file_base = rtrim(static::get_design_root_dir(), DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            if ($f_design_prefix !== null) {
                $file_base .= str_replace('\\', DIRECTORY_SEPARATOR, $f_design_prefix) . DIRECTORY_SEPARATOR;
            }

            $file_base .= str_replace('\\', DIRECTORY_SEPARATOR, $design);

            $file_base = rtrim($file_base, DIRECTORY_SEPARATOR);
            $file = $file_base . '.php';
        }

        if (is_file($file) && is_readable($file)) {
            if ($check_only) return;

            static::design_log('path', $design);

            /*
             * load in closure for clean scope
             */
            if (!$data instanceof data) $data = new data((array) $data);
            call_user_func(function () use ($file, $data) {
                require ($file);
            });
            return;
        }

        /**
         * determine if $design resolves to an existing html file
         *    if so...load template by echo'ing contents, and return void.
         * @todo consider loading into XML parser, and do 'cool things'
         */
        $tried = array(
            $file
        );
        
        foreach (array(
            'html',
            'htm'
        ) as $ext) {
            $file = $file_base . ".$ext";
            if (is_file($file) && is_readable($file)) {
                if ($filesize = filesize($file)) {
                    if ($check_only) return;
                    if ($filesize > self::MIN_CHUNKING_SIZE) {
                        if (!$h = fopen($file, "r")) throw new tmpl\exception\system_err("could not open file '$file' for read");

                        static::design_log('path', "$design.$ext");

                        while (!feof($h)) {
                            if (false === ($chunk = fread($h, self::MIN_CHUNKING_SIZE))) {
                                throw new tmpl\exception\system_err("could not read from file '$file'");
                            }
                            echo ($chunk);
                        }
                        if (!fclose($h)) {
                            throw new tmpl\exception\system_err("failed to close file '$file'");
                        }
                    } else {
                        if (false === ($str = file_get_contents($file))) {
                            throw new tmpl\exception\system_err("could not read string from file '$file'");
                        }

                        static::design_log('path', "$design.$ext");

                        echo ($str);
                    }
                    return;
                }
            }
            $tried[] = $file;
        }

        /*
         * determine if $design resolves to an existing txt or md files...
         *    if so...load template by giving file to MD to HTML converter,
         *       echo converted HTML contents, and return void.
         */
        foreach (array(
            'txt',
            'md'
        ) as $ext) {
            $file = $file_base . ".$ext";
            if (is_file($file) && is_readable($file)) {
                try {
                    $md = new md\convert\file($file);
                    if ($check_only) return;

                    static::design_log('path', "$design.$ext");

                    echo $md->get_html();
                    return;
                } catch (\Exception $e) {
                }
                return;
            }
            $tried[] = $file;
        }
        /**
         * determine if it's a php-md
         */
        foreach (array(
            "md.php",
            "txt.php"
        ) as $ext) {

            $file = $file_base . ".$ext";
            if (is_file($file) && is_readable($file)) {
                if (pathinfo($file, PATHINFO_EXTENSION) == 'php') {

                    try {

                        /*
                         * load in closure for clean scope
                         */
                        $loader = function ($filename, $data) {
                            require ($filename);
                        };
                        ob_start();
                        $loader($file, $data);
                        if ($check_only) {
                            ob_get_clean();
                            return;
                        }

                        static::design_log('path', $design);

                        $text = ob_get_clean();
                        echo md\convert::string_to_html($text);
                        return;
                    } catch (\Exception $e) {
                    }
                    return;
                }
            }
            $tried[] = $file;
        }
        throw new tmpl\exception\bad_design("'$design' did not resolve to regular non-zero length file. tried: '" . implode("', '", $tried) . "'");
    }

    public static function get_design_root_dir(): string
    {
        try {
            return \flat\core\config::get("design/root_dir");
        } catch (\Exception $e) {
        }
        return __DIR__ . str_replace('/', DIRECTORY_SEPARATOR, '/../../../../../../../app/Resources/design/tmpl');
    }

    /**
     * Log the design name if design logger is enabled using error_log
     *
     * @param string $type type of design (i.e "path" or "object").
     * @param string $name name of design (path or name of object).
     *
     * @static
     * @return void
     */
    protected static function design_log(string $type, string $name): void
    {
        if (!static::$logger_enabled) return;
        error_log("tmpl design ($type): " . trim(str_replace([
            '\\'
        ], [
            '/'
        ], $name), '/'), static::$logger_message_type, static::$logger_destination, static::$logger_extra_headers);
    }

    protected static function get_design_class_prefix(): string
    {
        return self::$design_class_prefix;
    }

    /**
     * resolves inherited full name of class
     *    into a design class name
     *
     * @return string
     *
     */
    private function get_design_from_root()
    {
        $designSubNs = explode('\\', get_called_class());

        foreach (array_reverse($designSubNs) as $subns) {
            if (in_array(strtolower($subns), [
                'tmpl',
                'template'
            ], true)) {
                break 1;
            }
            array_pop($designSubNs);
        }
        unset($subns);

        return implode('\\', $designSubNs);
    }

    private function resolve_display($data = null)
    {
        $display = self::get_display_handler();
        if ($this instanceof \flat\tmpl\design) {

            $design = $this->get_design();
        } else {
            $design = $this->get_design_from_root();
        }
        if ($data === null) $data = new data();

        $tmpl = $this;



        $display($design, $data, $tmpl);
    }
}










