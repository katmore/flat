<?php
namespace flat\core {
    // Controlled primitive for the wrapper test; ordinary calls use native getopt.
    function getopt($short = '', $long = [], &$index = null) {
        if (isset($GLOBALS['flat_unit_getopt_result'])) {
            $index = $GLOBALS['flat_unit_getopt_index'];
            return $GLOBALS['flat_unit_getopt_result'];
        }
        return \getopt($short, $long, $index);
    }
}
namespace flat\test\Unit\Core {
use flat\core\cli;
use PHPUnit\Framework\TestCase;
class CliTest extends TestCase
{
    private $saved = [];
    protected function setUp(): void
    {
        $class = new \ReflectionClass(cli::class); $defaults = $class->getDefaultProperties();
        foreach ($class->getProperties(\ReflectionProperty::IS_STATIC) as $property) {
            $property->setAccessible(true); $name = $property->getName();
            $this->saved[$name] = $property->getValue(); $property->setValue(null, $defaults[$name]);
        }
    }
    protected function tearDown(): void
    {
        foreach ($this->saved as $name => $value) { $p = new \ReflectionProperty(cli::class, $name); $p->setAccessible(true); $p->setValue(null, $value); }
    }
    public function testUninitializedAndEmptyArguments()
    {
        $this->assertFalse(cli::is_switch_on('quiet'));
        $this->assertNull(cli::get_command()); $this->assertNull(cli::get_resource());
        $calls = []; cli::each_arg(function ($arg) use (&$calls) { $calls[] = $arg; });
        cli::set_argv([], -1, 'bad');
        $this->assertSame(cli::command, cli::get_command()); $this->assertSame('', cli::get_resource());
        $this->assertNull(cli::get_param());
        cli::each_arg(function ($arg) use (&$calls) { $calls[] = $arg; });
        $this->assertSame([], $calls);
    }
    public function testArgumentsSwitchesAndCommandOverride()
    {
        $args = ['/path/tool.php', 'task', 'operand', '--verbose', '-q'];
        cli::set_argv($args);
        $this->assertSame('tool.php', cli::get_command()); $this->assertSame('task', cli::get_resource());
        $this->assertSame('operand', cli::get_param()); $this->assertNull(cli::get_param(1));
        $this->assertTrue(cli::is_switch_on('verbose')); $this->assertTrue(cli::is_switch_on('quiet', 'q'));
        $this->assertTrue(cli::is_quiet()); $this->assertFalse(cli::is_switch_on('missing', 'z'));
        $seen = []; cli::each_arg(function ($arg) use (&$seen) { $seen[] = $arg; });
        $this->assertSame($args, $seen);
        cli::set_command('override'); $this->assertSame('override', cli::get_command());
        cli::set_argv(['tool', 'prefix', 'command', 'argument'], 3, 2);
        $this->assertSame('command', cli::get_resource()); $this->assertSame('argument', cli::get_param());
    }
    public function testOptionFormsAndAliases()
    {
        cli::set_argv(['tool', 'task', '--name=value', '-p', '123', '--flag', '--port-other=wrong']);
        $this->assertSame('value', cli::get_option('name'));
        $this->assertSame('123', cli::get_option('port', 'p'));
        $this->assertSame('', cli::get_option('flag'));
        $this->assertNull(cli::get_option('missing'));
        cli::set_argv(['tool', '-p', '--flag']); $this->assertSame('', cli::get_option('port', 'p'));
        cli::set_argv(['tool', '--port-other=wrong']); $this->assertNull(cli::get_option('port'));
    }
    public function testEnumerationsMatchDocumentedExample()
    {
        cli::set_argv(['cli.php','some-command','some-arg','--some-flag','--foo','bar1','--foo=bar2','--','--another-option','anotherValue','-f=bar1','-f=bar2','-x','-y','-z','-invalid-flag','-invalid-optval=foobar']);
        $this->assertSame([['foo'=>'bar1'], ['foo'=>'bar2'], ['-'=>''], ['another-option'=>'anotherValue'], ['f'=>'bar1'], ['f'=>'bar2']], cli::enum_optval());
        $this->assertSame(['some-flag','-','x','y','z'], cli::enum_flag());
        cli::set_argv(['tool', '-']); $this->assertSame([''], cli::enum_flag());
        cli::set_argv(['tool', '--last']); $this->assertSame(['last'], cli::enum_flag());
    }
    public function testInputLineCallbacks()
    {
        $seen = []; cli::each_input_line(function ($line) use (&$seen) { $seen[] = $line; });
        $lines = ['first', 'second', null];
        cli::set_input_line_handler(function () use (&$lines) { return array_shift($lines); });
        cli::each_input_line(function ($line) use (&$seen) { $seen[] = $line; });
        $this->assertSame(['first', 'second'], $seen);
    }
    public function testWrappedLinesAndWidthHandlers()
    {
        $this->assertSame("text\n", cli::get_wrapped_line('text'));
        $this->assertSame("  text\n", cli::get_wrapped_line("\ttext", ['tabs'=>1]));
        cli::set_width_handler(function () { return 10; });
        $this->assertSame("text\n", cli::get_wrapped_line('text'));
        cli::set_width_handler(function () { return 20; });
        $this->assertSame(wordwrap('abcdefghijklmnopqrstuvw', 20, "\n", true)."\n", cli::get_wrapped_line('abcdefghijklmnopqrstuvw'));
        $this->assertSame("  text\n", cli::get_wrapped_line('text', ['tabs'=>1]));
    }
    public function testPrintAndDumpHandlers()
    {
        $seen = []; cli::set_print_handler(function ($text, $options) use (&$seen) { $seen[] = [$text, $options]; });
        cli::print_string('text', ['x'=>1]); cli::printf('100%'); cli::printf('%s-%d', 'x', 2);
        $this->assertSame([['text',['x'=>1]],['100%',null],['x-2',null]], $seen);
        $dump = []; cli::set_dump_handler(function (...$values) use (&$dump) { $dump = $values; });
        cli::var_dump(1, 'two'); $this->assertSame([1,'two'], $dump);
    }
    public function testDefaultPrintAndDumpOutput()
    {
        ob_start();
        try { cli::print_string('text'); cli::var_dump(42); $output = ob_get_contents(); }
        finally { ob_end_clean(); }
        $this->assertStringStartsWith('text', $output);
        $this->assertSame(1, substr_count($output, 'int(42)'));
        $this->assertRegExp('/int\\(42\\)\\n$/', $output);
    }
    public function testLineAndErrorHandlers()
    {
        $lines = []; cli::set_line_handler(function ($text, $options) use (&$lines) { $lines[] = [$text, $options]; });
        cli::line('normal', 2); cli::line('error', ['error'=>7]); cli::line('error marker', ['error']);
        $this->assertSame([['normal',['tabs'=>2,'error'=>false]],['error',['tabs'=>0,'error'=>7]],['error marker',['tabs'=>0,'error'=>true]]], $lines);
        cli::set_error_line_handler(function ($text, $options) use (&$lines) { $lines[] = ['dedicated', $text, $options]; });
        cli::line('failure', ['error'=>true,'tabs'=>1]);
        $this->assertSame(['dedicated','failure',['tabs'=>1]], end($lines));
    }
    public function testNormalQuietAndErrorOutput()
    {
        $this->expectOutputString("normal\n\nERROR MSG:\nfailure\n\n");
        cli::set_argv(['tool']); cli::line('normal');
        cli::set_argv(['tool','--quiet']); cli::line('hidden'); cli::line('failure', ['error'=>true]);
    }
    public function testMissingArgvPreservesCurrentExceptionNamespaceFailure()
    {
        $old = $GLOBALS['argv']; unset($GLOBALS['argv']);
        try {
            $this->expectException(\Error::class); $this->expectExceptionMessage('flat\\core\\RuntimeException');
            cli::getopt_and_operand();
        } finally { $GLOBALS['argv'] = $old; }
    }
    public function testGetoptWrapperUsesTheReturnedOperandIndex()
    {
        $argv = $GLOBALS['argv'];
        $GLOBALS['argv'] = ['tool', '--flag', 'operand', 'second'];
        $GLOBALS['flat_unit_getopt_result'] = ['flag' => false];
        $GLOBALS['flat_unit_getopt_index'] = 2;
        try {
            $this->assertSame(['opt' => ['flag' => false], 'operand' => ['operand', 'second']], cli::getopt_and_operand('', ['flag']));
        } finally {
            $GLOBALS['argv'] = $argv;
            unset($GLOBALS['flat_unit_getopt_result'], $GLOBALS['flat_unit_getopt_index']);
        }
    }
}
}
