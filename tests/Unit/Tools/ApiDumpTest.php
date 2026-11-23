<?php
namespace flat\test\Unit\Tools;
use PHPUnit\Framework\TestCase;
class ApiDumpTest extends TestCase
{
    private $directory;
    protected function setUp(): void
    {
        $this->directory = sys_get_temp_dir().'/flat-api-'.bin2hex(random_bytes(8));
        mkdir($this->directory);
    }
    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') as $file) unlink($file);
        rmdir($this->directory);
    }
    private function tool(array $args, $expectedStatus = 0)
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__,2).'/tools/apidump.php');
        foreach ($args as $arg) $command .= ' '.escapeshellarg($arg);
        $process = proc_open($command, [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
        $this->assertIsResource($process); fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $this->assertSame($expectedStatus, proc_close($process), $out.$err); $this->assertSame('', $err);
        return $out;
    }
    private function dump($reference, $type = 'int')
    {
        $class = $this->directory.'/Probe.php';
        file_put_contents($class, '<?php namespace flat; class Probe { public function '.($reference?'&':'').'value('.$type.' $x) { return $x; } }');
        $data = $this->tool(['one', dirname($this->directory), 'flat\\Probe', $class]);
        return ['flat\\Probe'=>json_decode($data, true, 512, JSON_THROW_ON_ERROR)];
    }
    private function compare($old, $new, $expectedStatus = 0)
    {
        file_put_contents($this->directory.'/old.json', json_encode($old));
        file_put_contents($this->directory.'/new.json', json_encode($new));
        return $this->tool(['compare', $this->directory.'/old.json', $this->directory.'/new.json'], $expectedStatus);
    }
    public function testReferenceReturnChangesAreDetectedInBothDirections()
    {
        $value = $this->dump(false); $reference = $this->dump(true);
        $this->assertStringContainsString('returns_reference', $this->compare($value, $reference));
        $this->assertStringContainsString('returns_reference', $this->compare($reference, $value));
    }
    public function testOlderBaselinesRequireFreshReferenceMetadata()
    {
        $new = $this->dump(false); $old = $new;
        unset($old['flat\\Probe']['methods']['value']['returns_reference']);
        $this->assertStringContainsString('metadata is missing', $this->compare($old, $new, 2));
        $this->assertStringContainsString('metadata is missing', $this->compare($old, $this->dump(true), 2));
        $this->assertStringContainsString('no differences', $this->compare($new, $new));
    }
    public function testParameterTypeControlStillFindsAChange()
    {
        $this->assertStringContainsString('parameter type changed', $this->compare($this->dump(false), $this->dump(false, 'string')));
    }
}
