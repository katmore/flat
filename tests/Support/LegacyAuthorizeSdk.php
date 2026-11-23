<?php
namespace flat\test\Support;
trait LegacyAuthorizeSdk
{
    /*
     * APPROVED EXCEPTION to prime directive 2 (AGENTS.md, "Approved exceptions"; approved
     * 2026-11-21). Only the E_USER_DEPRECATED notice that the Authorize.Net SDK raises from its own
     * lib/deprecated/ files while a legacy class loads is discarded; every other error goes on to
     * the previous handler. flat's Authorize.Net wrappers are built on those deprecated classes; they are
     * deprecated and will be removed in v2.X (docs/modernization/DEFERRED-V2.md, item 2), and this helper
     * goes with them. Do not copy this pattern.
     */
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
