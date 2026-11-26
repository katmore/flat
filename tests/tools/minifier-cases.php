<?php
// Emit the shared regression cases for the JavaScript execution check.
require '/opt/flat-test/bootstrap.php';
require __DIR__.'/../Unit/Vendor/JsMinifierTest.php';
$test = new \flat\test\Unit\Vendor\JsMinifierTest;
$cases = array_merge($test->commentPrograms(), $test->modernPrograms());
foreach ($cases as $case) {
    echo json_encode([
        'source' => $case[0],
        'minified' => \flat\vendor\tedious\JShrink\minifier::minify($case[0]),
        'expected' => $case[2],
    ], JSON_THROW_ON_ERROR), "\n";
}
