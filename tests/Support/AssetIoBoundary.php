<?php
namespace flat\test\Support {
    class AssetIoBoundary { public static $failedPath; }
}
namespace flat {
    function file_get_contents($path, ...$arguments) {
        if ($path === \flat\test\Support\AssetIoBoundary::$failedPath) return false;
        return \file_get_contents($path,...$arguments);
    }
}
