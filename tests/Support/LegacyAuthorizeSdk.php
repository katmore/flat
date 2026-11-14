<?php
namespace flat\test\Support;
trait LegacyAuthorizeSdk
{
    protected function loadLegacyAuthorizeClass($class)
    {
        $previous = null;
        $previous = set_error_handler(function ($severity,$message,$file,$line) use (&$previous) {
            if ($severity === E_USER_DEPRECATED && strpos($file,'/authorizenet/authorizenet/lib/deprecated/') !== false) return true;
            return $previous ? $previous($severity,$message,$file,$line) : false;
        });
        try { return class_exists($class); }
        finally { restore_error_handler(); }
    }
}
