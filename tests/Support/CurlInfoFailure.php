<?php
namespace flat\test\Support {
    /**
     * The one thing real HTTP cannot do: make curl_getinfo() return false for a finished transfer (the branch of response.php that
     * throws missing_curlinfo). Referencing this class loads the file, which defines the function below; it passes every call through
     * to PHP's own unless $active is set. Used by ResponseTransferInfoTest only.
     */
    class CurlInfoFailure
    {
        public static $active = false;
    }
}
namespace flat\core\curl {
    function curl_getinfo($handle, $option = null)
    {
        if (\flat\test\Support\CurlInfoFailure::$active && $option === null) return false;
        return $option === null ? \curl_getinfo($handle) : \curl_getinfo($handle, $option);
    }
}
