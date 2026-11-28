<?php
// Online solver check: no lock, installed package or application state is reused.
if (error_reporting() !== E_ALL) throw new RuntimeException('The resolution check requires E_ALL');
$root = dirname(__DIR__, 2);
$runtime = json_decode(file_get_contents($root.'/tests/docker/runtime/composer.json'), true, 512, JSON_THROW_ON_ERROR);
$expected = ['PKSA-4t1p-xpk2-nsss', 'PKSA-dxyf-6n16-t87m', 'PKSA-y93m-jgy6-r9ns', 'PKSA-61k5-cqr9-b8b4'];
$policy = $runtime['config']['audit']['ignore'];
if (array_keys($policy) !== $expected) {
    throw new RuntimeException('Only the four owner-approved advisories may be excepted');
}
foreach ($policy as $exception) {
    if ($exception['apply'] !== 'block' || empty($exception['reason'])) {
        throw new RuntimeException('Exceptions must affect resolution only and explain the accepted risk');
    }
}
$consumer = [
    'name'=>'flat-test/fresh-consumer',
    'repositories'=>[['type'=>'path', 'url'=>$root, 'options'=>['versions'=>['katmore/flat'=>'dev-php-8.0']]]],
    'require'=>['katmore/flat'=>'dev-php-8.0'],
    'config'=>['platform'=>$runtime['config']['platform']],
];
$cases = ['default consumer'=>[$consumer, false]];
$consumer['config']['audit']['ignore'] = $policy;
$cases['approved consumer'] = [$consumer, true];
// Autoload paths do not affect resolution; avoid referencing the build-stage /app layout.
unset($runtime['autoload']);
$runtime['name'] = 'flat-test/fresh-runtime';
$cases['approved runtime'] = [$runtime, true];
$work = sys_get_temp_dir().'/flat-resolution-'.bin2hex(random_bytes(8));
mkdir($work);
try {
    foreach ($cases as $label=>[$manifest, $shouldPass]) {
        file_put_contents($work.'/composer.json', json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)."\n");
        // The Composer image lacks sockets; the PHP test image verifies the real extension.
        $command = [getenv('FLAT_TEST_COMPOSER') ?: 'composer', 'update', '--dry-run', '--no-install',
            '--no-interaction', '--no-progress', '--no-plugins', '--no-scripts', '--ignore-platform-req=ext-sockets'];
        $process = proc_open($command, [0=>['file','/dev/null','r'], 1=>['file',$work.'/output','w'], 2=>['redirect',1]], $pipes, $work);
        if (!is_resource($process)) throw new RuntimeException('Cannot run Composer');
        $status = proc_close($process);
        $output = file_get_contents($work.'/output');
        if (($status === 0) !== $shouldPass || (!$shouldPass && $status !== 2)) {
            throw new RuntimeException($label.' did not produce the expected solver result ('.$status."):\n".$output);
        }
        echo $label.': '.($shouldPass ? 'resolved from scratch' : 'blocked by security advisories as expected')."\n";
    }
    copy($root.'/tests/docker/runtime/composer.lock', $work.'/composer.lock');
    $process = proc_open([getenv('FLAT_TEST_COMPOSER') ?: 'composer', 'audit', '--locked', '--format=json'],
        [0=>['file','/dev/null','r'], 1=>['file',$work.'/output','w'], 2=>['file',$work.'/audit-error','w']], $pipes, $work);
    if (!is_resource($process)) throw new RuntimeException('Cannot audit Composer lock');
    $status = proc_close($process);
    $audit = json_decode(file_get_contents($work.'/output'), true, 512, JSON_THROW_ON_ERROR);
    $reported = [];
    foreach ($audit['advisories'] as $advisories) {
        foreach ($advisories as $advisory) $reported[] = $advisory['advisoryId'];
    }
    sort($reported);
    sort($expected);
    if ($status === 0 || $reported !== $expected || !empty($audit['ignored-advisories'])) {
        throw new RuntimeException('Audits must still report exactly the four accepted advisories');
    }
    echo "runtime audit: all four accepted advisories remain visible\n";
} finally {
    foreach (glob($work.'/*') as $file) unlink($file);
    rmdir($work);
}
