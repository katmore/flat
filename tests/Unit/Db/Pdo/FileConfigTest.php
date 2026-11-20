<?php
namespace flat\test\Unit\Db\Pdo;
require_once __DIR__.'/../../../Support/ConfigSandbox.php';
use PHPUnit\Framework\TestCase;
use flat\db\driver\pdo\pdo;
class FileConfigTest extends TestCase
{
    use \flat\test\Support\ConfigSandbox;
    protected function setUp(): void
    {
        $this->openConfigSandbox([]); $root = $this->configSandboxDirectory();
        mkdir($root.'/app/script',0700,true);
        file_put_contents($root.'/app/script/pdo.php','<?php return ["path_root"=>'.var_export($root,true).'];');
        file_put_contents($root.'/query.sql','SELECT 7 AS value');
    }
    protected function tearDown(): void
    {
        $root = $this->configSandboxDirectory(); unlink($root.'/query.sql'); unlink($root.'/app/script/pdo.php');
        rmdir($root.'/app/script'); rmdir($root.'/app'); $this->closeConfigSandbox();
    }
    public function testConfiguredSqlRootAndDriverOptionOverloadUseRealSqlite()
    {
        $pdo = new pdo('sqlite::memory:'); $options = [\PDO::ATTR_CURSOR=>\PDO::CURSOR_FWDONLY];
        $statement = $pdo->prepare_from_file('query.sql'); $this->assertTrue($statement->execute());
        $this->assertSame('7',$statement->fetchColumn());
        $statement = $pdo->prepare_from_file('query.sql',$options); $this->assertTrue($statement->execute());
        $this->assertSame('7',$statement->fetchColumn());
        $statement = $pdo->prepare_map(['path'=>'query.sql','driver_options'=>$options,'execute'=>true]);
        $this->assertSame('7',$statement->fetchColumn());
    }
}
