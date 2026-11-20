<?php
namespace flat\test\Unit\Db\Pdo;
use PHPUnit\Framework\TestCase;
use flat\db\driver\pdo\pdo;
class RowFixture extends \flat\data { public $id; public $name; }
class PdoTest extends TestCase
{
    public function testBareRecordFlagsMapRowsToGenericData()
    {
        foreach (['record','result'] as $flag) {
            $rows = $this->pdo->prepare_map(['stmt'=>'SELECT id,name FROM items ORDER BY id',$flag]);
            $this->assertInstanceOf(\flat\data\generic::class,$rows[0]);
            $this->assertSame('first',$rows[0]->name);
        }
    }
    private $pdo;
    private $directory;
    protected function setUp(): void
    {
        $this->pdo = new pdo('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE items (id INTEGER, name TEXT)');
        $this->pdo->exec("INSERT INTO items VALUES (1, 'first'), (2, 'second')");
        $this->directory = sys_get_temp_dir().'/flat-pdo-'.bin2hex(random_bytes(8));
        mkdir($this->directory);
    }
    protected function tearDown(): void
    {
        foreach (glob($this->directory.'/*') as $file) { chmod($file, 0600); unlink($file); }
        rmdir($this->directory); $this->pdo = null;
    }
    public function testPreparedStatementsRemainUnexecutedUnlessRequested()
    {
        $statement = $this->pdo->prepare_map(['statement'=>'SELECT name FROM items WHERE id = :id', 'bindValue'=>[':id'=>1]]);
        $this->assertInstanceOf(\PDOStatement::class, $statement);
        $this->assertFalse($statement->fetch());
        $this->assertTrue($statement->execute());
        $this->assertSame('first', $statement->fetchColumn());
    }
    public function testAliasesBindingAndExecuteFlags()
    {
        foreach ([['execute'=>true], ['exec'=>true], ['execute'], ['exec']] as $flag) {
            $statement = $this->pdo->prepare_map($flag + ['stmt'=>'SELECT name FROM items WHERE id = :id', 'bindParam'=>[':id'=>2], 'driver_options'=>[\PDO::ATTR_CURSOR=>\PDO::CURSOR_FWDONLY]]);
            $this->assertSame('second', $statement->fetchColumn());
        }
        $statement = $this->pdo->prepare_map(['stmt'=>'SELECT :text', 'bindValue'=>[':text'=>'bound'], 'execute'=>true]);
        $this->assertSame('bound', $statement->fetchColumn());
        $statement = $this->pdo->prepare_map(['stmt'=>'SELECT :text', 'bindParam'=>[':text'=>'parameter'], 'execute'=>true]);
        $this->assertSame('parameter', $statement->fetchColumn());
        $statement = $this->pdo->prepare_map(['stmt'=>'SELECT :empty', 'bindValue'=>[':empty'=>null], 'execute'=>true]);
        $this->assertNull($statement->fetchColumn());
    }
    /** @dataProvider invalidParameters */
    public function testInvalidMapParameters($parameters)
    {
        $this->expectException(\flat\lib\exception\bad_param::class);
        $this->pdo->prepare_map($parameters);
    }
    public function invalidParameters()
    {
        return [[[]], [['stmt'=>'SELECT 1','paramValue'=>'not array']],
            [['stmt'=>'SELECT 1','record'=>\stdClass::class]], [['stmt'=>'SELECT 1','record'=>new \stdClass]],
            [['stmt'=>'SELECT 1','record'=>42]]];
    }
    public function testSqlFilesWithExplicitRootOrNoRoot()
    {
        $path = $this->directory.'/select.sql'; file_put_contents($path, 'SELECT name FROM items ORDER BY id');
        foreach ([[$path,pdo::path_root_none], ['select.sql',$this->directory]] as $args) {
            $statement = $this->pdo->prepare_from_file(...$args); $statement->execute();
            $this->assertSame(['first','second'], $statement->fetchAll(\PDO::FETCH_COLUMN));
        }
        foreach (['path','script'] as $name) {
            $statement = $this->pdo->prepare_map([$name=>'select.sql','path_root'=>$this->directory,'execute'=>true]);
            $this->assertSame('first', $statement->fetchColumn());
        }
    }
    /** @dataProvider invalidPaths */
    public function testBadSqlPathsReportPathAndReason($kind, $exception, $reason)
    {
        $path = $this->directory.'/missing'; $root = pdo::path_root_none;
        if ($kind === 'directory') $path = $this->directory;
        if ($kind === 'root-missing') $root = $this->directory.'/absent';
        if ($kind === 'root-file') { $root = $this->directory.'/root'; file_put_contents($root, 'file'); }
        if ($kind === 'unreadable') { file_put_contents($path, 'SELECT 1'); chmod($path, 0000); }
        try { $this->pdo->prepare_from_file($path, $root); }
        catch (\flat\db\driver\pdo\exception $e) {
            $this->assertInstanceOf($exception, $e);
            $this->assertSame($reason, $e->get_reason());
            $this->assertSame(is_string($root) ? $root : $path, (is_string($root) ? $e->get_path_root() : $e->get_path()));
            return;
        }
        $this->fail('Invalid path should fail');
    }
    public function invalidPaths()
    {
        return [['missing',\flat\db\driver\pdo\exception\bad_prepare_path::class,'does not exist'],
            ['directory',\flat\db\driver\pdo\exception\bad_prepare_path::class,'is not a file'],
            ['unreadable',\flat\db\driver\pdo\exception\bad_prepare_path::class,'is not readable'],
            ['root-missing',\flat\db\driver\pdo\exception\bad_prepare_path_root::class,'does not exist'],
            ['root-file',\flat\db\driver\pdo\exception\bad_prepare_path_root::class,'not a directory']];
    }
    public function testRecordsAndCallbacksUseNativeSqliteResults()
    {
        $rows = $this->pdo->prepare_map(['stmt'=>'SELECT id, name FROM items ORDER BY id', 'record'=>'array']);
        $this->assertSame([['id'=>'1','name'=>'first'], ['id'=>'2','name'=>'second']], $rows);
        foreach ([RowFixture::class, new RowFixture, true] as $record) {
            $rows = $this->pdo->prepare_map(['stmt'=>'SELECT id, name FROM items ORDER BY id', 'record'=>$record]);
            $this->assertCount(2, $rows);
            $this->assertSame('first', $rows[0]->name);
            $this->assertSame('2', $rows[1]->id);
        }
        $seen = [];
        $result = $this->pdo->prepare_map(['stmt'=>'SELECT name FROM items ORDER BY id', 'callback'=>function ($row) use (&$seen) { $seen[] = $row['name']; }]);
        $this->assertNull($result);
        $this->assertSame(['first','second'], $seen);
        $this->assertSame([], $this->pdo->prepare_map(['stmt'=>'SELECT name FROM items WHERE id=999', 'result'=>'array']));
    }
}
