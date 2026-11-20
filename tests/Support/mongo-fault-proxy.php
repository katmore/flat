<?php
// Test-only TCP transport: forward actual MongoDB bytes, or close connections
// when the control file says "down". No MongoDB responses are synthesized.
$target = $argv[1];
$control = $argv[2];
$listener = stream_socket_server('tcp://127.0.0.1:0', $errno, $error);
if (!$listener) throw new RuntimeException($error, $errno);
stream_set_blocking($listener, false);
echo stream_socket_get_name($listener, false)."\n";
flush();
$streams = $peers = $buffers = [];
$closePair = function ($key) use (&$streams, &$peers, &$buffers) {
    $keys = [$key];
    if (isset($peers[$key])) $keys[] = $peers[$key];
    foreach ($keys as $id) {
        if (isset($streams[$id]) && is_resource($streams[$id])) fclose($streams[$id]);
        unset($streams[$id], $peers[$id], $buffers[$id]);
    }
};
// A read or write on a connection that the other side has closed can raise a notice. It is
// not hidden: the message goes to this process's stderr and the connection is treated as closed.
$stream_io = function (callable $operation) {
    $notice = null;
    set_error_handler(function ($level, $message) use (&$notice) { $notice = $message; return true; });
    try { $result = $operation(); } finally { restore_error_handler(); }
    if ($notice !== null) fwrite(STDERR, "mongo-fault-proxy: $notice\n");
    return $result;
};
try {
    while (($state = file_get_contents($control)) !== 'stop') {
        if ($state === 'down') foreach (array_keys($streams) as $key) $closePair($key);
        $read = array_merge([$listener], array_values($streams));
        $write = [];
        foreach ($buffers as $key => $buffer) if ($buffer !== '') $write[] = $streams[$key];
        $except = null;
        if (!stream_select($read, $write, $except, 0, 10000)) continue;
        foreach ($read as $stream) {
            if ($stream === $listener) {
                $incoming = stream_socket_accept($listener, 0);
                if ($state === 'down') { fclose($incoming); continue; }
                $outgoing = stream_socket_client('tcp://'.$target, $errno, $error, 1);
                stream_set_blocking($incoming, false); stream_set_blocking($outgoing, false);
                $left = (int) $incoming; $right = (int) $outgoing;
                $streams[$left] = $incoming; $streams[$right] = $outgoing;
                $peers[$left] = $right; $peers[$right] = $left;
                continue;
            }
            if (!is_resource($stream)) continue;
            $key = (int) $stream;
            $data = $stream_io(function () use ($stream) { return fread($stream, 65536); });
            if ($data === false || ($data === '' && feof($stream))) { $closePair($key); continue; }
            $peer = $peers[$key];
            $buffers[$peer] = ($buffers[$peer] ?? '').$data;
        }
        foreach ($write as $stream) {
            if (!is_resource($stream)) continue;
            $key = (int) $stream;
            $written = $stream_io(function () use ($stream, $buffers, $key) { return fwrite($stream, $buffers[$key]); });
            if ($written === false) { $closePair($key); continue; }
            $buffers[$key] = substr($buffers[$key], $written);
        }
    }
} finally {
    foreach (array_keys($streams) as $key) $closePair($key);
    fclose($listener);
}
