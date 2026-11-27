<?php
namespace flat\test\Unit\Tools;
use PHPUnit\Framework\TestCase;

/**
 * tests/tools/services.php: which test services do the tests a run selects need? (docker/bin/test starts only those.)
 */
class ServicesToolTest extends TestCase
{
    private $list;

    protected function setUp(): void
    {
        $this->list = tempnam(sys_get_temp_dir(), 'flat-services-');
        file_put_contents($this->list, <<<'XML'
<?xml version="1.0" encoding="UTF-8"?>
<tests>
 <testCaseClass name="flat\test\Unit\Plain">
  <testCaseMethod name="testNothingNeeded" groups="default"/>
 </testCaseClass>
 <testCaseClass name="flat\test\Unit\Db\MongoThing">
  <testCaseMethod name="testReads" groups="default,mongo,services"/>
  <testCaseMethod name="testWritesData" groups="default,mongo,services" dataSet="#1"/>
 </testCaseClass>
 <testCaseClass name="flat\test\Unit\Db\QueueThing">
  <testCaseMethod name="testPublishes" groups="default,rabbitmq,services"/>
 </testCaseClass>
 <testCaseClass name="flat\test\Sandbox\ChargeThing">
  <testCaseMethod name="testCharges" groups="default,sandbox,anet,services"/>
 </testCaseClass>
 <testCaseClass name="flat\test\Unit\Core\Curl\HttpThing">
  <testCaseMethod name="testFetches" groups="default,http,services"/>
 </testCaseClass>
 <testCaseClass name="flat\test\Unit\Vague">
  <testCaseMethod name="testNeedsSomething" groups="default,services"/>
 </testCaseClass>
</tests>
XML
        );
    }

    protected function tearDown(): void
    {
        unlink($this->list);
    }

    private function needed(array $args = [])
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__,2).'/tools/services.php').' '.escapeshellarg($this->list);
        foreach ($args as $arg) $command .= ' '.escapeshellarg($arg);
        $process = proc_open($command, [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
        $this->assertIsResource($process); fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $this->assertSame(0, proc_close($process), $out.$err);
        $this->assertSame('', $err);
        return trim($out);
    }

    public function selections()
    {
        return [
            'every test, so every service' => [[], 'mongo rabbitmq anet-mock'],
            'the http group needs the mock server, named once' => [['--group', 'http'], 'anet-mock'],
            'http and anet together name the mock once' => [['--group', 'http,anet'], 'anet-mock'],
            'a filter on the http test' => [['--filter', 'HttpThing'], 'anet-mock'],
            'a plain word filter on one class' => [['--filter', 'MongoThing'], 'mongo'],
            'a filter on one method' => [['--filter', 'testPublishes'], 'rabbitmq'],
            'a filter with a data set' => [['--filter', 'testWritesData with data set #1'], 'mongo'],
            'a regular expression' => [['--filter', '/Thing::test(Reads|Charges)$/'], 'mongo anet-mock'],
            'the filter equals form' => [['--filter=ChargeThing'], 'anet-mock'],
            'a filter on a test that needs nothing' => [['--filter', 'Plain'], ''],
            'a filter that matches nothing' => [['--filter', 'NoSuchTest'], ''],
            'a group' => [['--group', 'rabbitmq'], 'rabbitmq'],
            'two groups' => [['--group', 'mongo,anet'], 'mongo anet-mock'],
            'services only' => [['--group', 'services'], 'mongo rabbitmq anet-mock'],
            'unit, which excludes services' => [['--exclude-group', 'services'], ''],
            'a group and a filter together' => [['--group', 'services', '--filter', 'Charge'], 'anet-mock'],
            'a test in services that names no service asks for all' => [['--filter', 'Vague'], 'mongo rabbitmq anet-mock'],
            'an argument it does not understand keeps every test' => [['some/path/Test.php'], 'mongo rabbitmq anet-mock'],
        ];
    }

    /** @dataProvider selections */
    public function testItNamesTheServicesTheSelectedTestsNeed($args, $expected)
    {
        $this->assertSame($expected, $this->needed($args));
    }

    public function testAMissingListIsAUsageError()
    {
        $command = escapeshellarg(PHP_BINARY).' '.escapeshellarg(dirname(__DIR__,2).'/tools/services.php').' /no/such/list.xml';
        $process = proc_open($command, [0=>['pipe','r'],1=>['pipe','w'],2=>['pipe','w']], $pipes);
        $this->assertIsResource($process); fclose($pipes[0]);
        $out = stream_get_contents($pipes[1]); fclose($pipes[1]);
        $err = stream_get_contents($pipes[2]); fclose($pipes[2]);
        $this->assertSame(2, proc_close($process));
        $this->assertStringContainsString('usage:', $err);
        $this->assertSame('', $out);
    }
}
