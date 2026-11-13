<?php
namespace flat\test\Unit\Core\Template;
require_once __DIR__.'/../../../Support/ConfigSandbox.php';
use PHPUnit\Framework\TestCase;
class ViewFixture extends \flat\core\controller\view
{
    protected function _get_design() { return '\\flat\\design\\sample'; }
}
class ViewTest extends TestCase
{
    use \flat\test\Support\ConfigSandbox;
    protected function setUp(): void { $this->openConfigSandbox([]); }
    protected function tearDown(): void
    {
        $root = $this->configSandboxDirectory();
        unlink($root.'/design.php'); unlink($root.'/sample/page.php'); unlink($root.'/sample.php'); rmdir($root.'/sample');
        $this->closeConfigSandbox();
    }
    public function testResourceSearchTriggersEventsForMatchingAncestorFiles()
    {
        $root = $this->configSandboxDirectory(); mkdir($root.'/sample');
        file_put_contents($root.'/sample/page.php','page'); file_put_contents($root.'/sample.php','ancestor');
        file_put_contents($root.'/design.php','<?php return ["basedir"=>'.var_export($root,true).'];');
        $event = new \flat\core\event\factory; $seen = [];
        $event->add_listener('view_resolved',function ($data) use (&$seen) { $seen[] = $data; });
        $view = new ViewFixture; $view->set_event_factory($event);
        $view->set_resource('page');
        $this->assertCount(2,$seen);
        $this->assertSame($root.'/sample/page.php',$seen[0]->path);
        $this->assertSame($root.'/sample.php',$seen[1]->path);
        $this->assertSame('page',$seen[0]->resource);
        // Each event names the design class path that matched.
        $this->assertSame('\\flat\\design\\sample\\page',$seen[0]->resolved);
        $this->assertSame('\\flat\\design\\sample',$seen[1]->resolved);
    }
}
