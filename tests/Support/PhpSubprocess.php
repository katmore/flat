<?php
namespace flat\test\Support;
trait PhpSubprocess
{
    /**
     * Runs $body in a fresh PHP process. $ini: PHP settings for it (name => value), $env: its whole environment (null: this one's).
     * @return array [status, stdout, stderr]
     */
    protected function subprocess($body, array $ini = [], array $env = null)
    {
        $directory = sys_get_temp_dir().'/flat-php-child-'.bin2hex(random_bytes(8)); mkdir($directory);
        $bootstrap = dirname(dirname((new \ReflectionClass(\Composer\Autoload\ClassLoader::class))->getFileName())).'/autoload.php';
        $script = $directory.'/run.php'; $report = $directory.'/coverage.bin';
        $prefix = '<?php require '.var_export($bootstrap,true).';'."\n";
        $prefix .= 'if (function_exists("xdebug_start_code_coverage")) { xdebug_start_code_coverage(XDEBUG_CC_UNUSED | XDEBUG_CC_DEAD_CODE); register_shutdown_function(function () { register_shutdown_function(function () { file_put_contents('.var_export($report,true).', serialize(xdebug_get_code_coverage())); }); }); }'."\n";
        file_put_contents($script,$prefix.$body);
        $pipes = [];
        try {
            $process = proc_open(escapeshellarg(PHP_BINARY).$this->iniArguments($ini).' '.escapeshellarg($script), [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']],$pipes,null,$env);
            $this->assertIsResource($process); fclose($pipes[0]);
            $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
            $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
            $status = proc_close($process);
            $coverage = $this->getTestResultObject()->getCodeCoverage();
            if ($coverage !== null) {
                $this->assertFileExists($report);
                // Merge only genuine Xdebug execution data collected at child shutdown.
                $coverage->append(
                    \SebastianBergmann\CodeCoverage\RawCodeCoverageData::fromXdebugWithoutPathCoverage(unserialize(file_get_contents($report), ['allowed_classes'=>false])),
                    $this
                );
            }
            return [$status,$out,$err];
        } finally {
            foreach (glob($directory.'/*') as $file) unlink($file);
            rmdir($directory);
        }
    }
    private function iniArguments(array $ini)
    {
        $arguments = '';
        foreach ($ini as $name => $value) $arguments .= ' -d '.escapeshellarg($name.'='.$value);
        return $arguments;
    }
}
