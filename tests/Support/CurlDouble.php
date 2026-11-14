<?php
namespace flat\test\Unit\Core\Curl {
    class CurlDouble
    {
        public static $active = false;
        public static $handles = [];
        public static $urls = [];
        public static $options = [];
        public static $responses = [];
        public static function response($handle) { return self::$responses[self::$urls[(int) $handle]] ?? ['body'=>'<html><body><p>text</p></body></html>', 'code'=>200]; }
        public static function errno($handle) { return self::$active ? (self::response($handle)['errno'] ?? 0) : \curl_errno($handle); }
        public static function error($handle) { return self::$active ? 'controlled transfer error' : \curl_error($handle); }
    }
}
namespace flat\core\curl {
    use flat\test\Unit\Core\Curl\CurlDouble as Double;
    function curl_init($url = null) {
        $handle = \curl_init($url);
        if (Double::$active) { Double::$handles[] = $handle; Double::$urls[(int) $handle] = $url; }
        return $handle;
    }
    function curl_setopt($handle, $option, $value) {
        if (!Double::$active) return \curl_setopt($handle, $option, $value);
        Double::$options[(int) $handle][$option] = $value; return true;
    }
    function curl_exec($handle) {
        if (!Double::$active) return \curl_exec($handle);
        $response = Double::response($handle);
        if (!empty($response['fail'])) return false;
        $body = $response['body'] ?? '';
        if (isset(Double::$options[(int) $handle][CURLOPT_FILE])) { fwrite(Double::$options[(int) $handle][CURLOPT_FILE], $body); return true; }
        return $body;
    }
    function curl_getinfo($handle, $option = null) {
        if (!Double::$active) return $option === null ? \curl_getinfo($handle) : \curl_getinfo($handle, $option);
        $response = Double::response($handle);
        if ($option === CURLINFO_HTTP_CODE) return $response['code'] ?? 200;
        if ($option === CURLINFO_FILETIME) return 123;
        if (!empty($response['missing_info'])) return false;
        return ['http_code'=>$response['code'] ?? 200];
    }
    function curl_errno($handle) { return Double::errno($handle); }
    function curl_close($handle) { if (is_resource($handle)) \curl_close($handle); }
}
namespace flat\core\curl\exception {
    function curl_errno($handle) { return \flat\test\Unit\Core\Curl\CurlDouble::errno($handle); }
    function curl_error($handle) { return \flat\test\Unit\Core\Curl\CurlDouble::error($handle); }
}
