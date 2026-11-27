<?php
/**
 * Prints the test services the tests of a run need, on one line, separated by spaces (nothing if none):
 *    php tests/tools/services.php build/tests/.../tests.xml [PHPUnit arguments]
 * The first argument is PHPUnit's --list-tests-xml of the whole suite (it ignores --filter and --group). The
 * arguments that choose tests are applied here the way PHPUnit applies them: --filter, --group, --exclude-group.
 * Anything else (a test file or directory, for example) is not understood, so every test is kept: more services
 * than needed is safe, fewer is not.
 *
 * A test needs the service of its group: mongo, rabbitmq, anet (the Authorize.Net mock; Compose service
 * anet-mock) http (the same WireMock server, used by the cURL tests as a plain HTTP server) ssh (the OpenSSH server, Compose service sshd) or s3 (the S3 emulator, Compose service s3mock). A test in `services` with none of those groups does not say what it needs, so it asks for all of them.
 */
$services = ['mongo' => 'mongo', 'rabbitmq' => 'rabbitmq', 'anet' => 'anet-mock', 'http' => 'anet-mock', 'ssh' => 'sshd', 's3' => 's3mock'];
$document = new DOMDocument();
if (!isset($argv[1]) || !is_file($argv[1]) || !$document->load($argv[1])) {
    fwrite(STDERR, "usage: php tests/tools/services.php tests.xml [--filter X] [--group a,b] [--exclude-group a,b]\n");
    exit(2);
}

$filter = null; $include = []; $exclude = [];
for ($i = 2; $i < count($argv); $i++) {
    $arg = $argv[$i];
    foreach (['--filter' => 'filter', '--group' => 'include', '--exclude-group' => 'exclude'] as $option => $target) {
        if ($arg === $option && isset($argv[$i + 1])) { $value = $argv[++$i]; }
        elseif (strpos($arg, $option . '=') === 0) { $value = substr($arg, strlen($option) + 1); }
        else { continue; }
        if ($target === 'filter') { $filter = $value; }
        else { $$target = array_merge($$target, explode(',', $value)); }
        break;
    }
}
// A filter written as a regular expression (delimiters, then optional flags) is used as it is; a plain word is
// wrapped in slashes, as PHPUnit does. An invalid expression raises PHP's own warning: nothing is hidden.
if ($filter !== null && !preg_match('/^([\/#~%!]).+\1[a-zA-Z]*$/', $filter)) {
    $filter = '/' . addcslashes($filter, '/') . '/';
}

$needed = [];
foreach ($document->getElementsByTagName('testCaseMethod') as $method) {
    $groups = explode(',', $method->getAttribute('groups'));
    if ($include && !array_intersect($include, $groups)) continue;
    if ($exclude && array_intersect($exclude, $groups)) continue;
    if ($filter !== null) {
        $name = $method->parentNode->getAttribute('name') . '::' . $method->getAttribute('name')
            . ($method->hasAttribute('dataSet') ? ' with data set ' . $method->getAttribute('dataSet') : '');
        if (!preg_match($filter, $name)) continue;
    }
    $specific = array_intersect(array_keys($services), $groups);
    foreach ($specific as $group) $needed[$services[$group]] = true;
    if (!$specific && in_array('services', $groups, true)) {
        foreach ($services as $name) $needed[$name] = true;
    }
}
// two groups (anet, http) share one service, so each service is named once
echo implode(' ', array_values(array_unique(array_intersect(array_values($services), array_keys($needed))))), "\n";
