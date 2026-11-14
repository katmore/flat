<?php
namespace flat\test\Support {
    class NativeSessionBoundary { public static $disabled = false; }
}
namespace flat\data\orm\driver\session\handler {
    function session_status()
    {
        return \flat\test\Support\NativeSessionBoundary::$disabled ? PHP_SESSION_DISABLED : \session_status();
    }
}
