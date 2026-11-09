<?php
namespace flat\test\Unit\Db\Pdo;
use PHPUnit\Framework\TestCase;
use flat\db\driver\pdo\statement;
use flat\db\driver\pdo\statement\rules;
class RecordingPdo extends \PDO
{
    public $last;
    public function prepare($statement, $options = []) { return $this->last = parent::prepare($statement, $options); }
}
// Explicit buffered adapter: count actual native SQLite result rows and keep
// them available for fetch. This exercises drivers with meaningful SELECT
// row counts without pretending native SQLite rowCount has that guarantee.
class BufferedSqliteStatement extends \PDOStatement
{
    private $rows;
    protected function __construct() {}
    public function rowCount() { if ($this->rows===null) $this->rows=parent::fetchAll(\PDO::FETCH_ASSOC); return count($this->rows); }
    public function fetch($mode=null,$orientation=\PDO::FETCH_ORI_NEXT,$offset=0)
    {
        if ($this->rows===null) return parent::fetch($mode,$orientation,$offset);
        return array_shift($this->rows) ?? false;
    }
}
class JoinRules extends rules { public $join_clause; }
abstract class AbstractRow {}
class StatementTest extends TestCase
{
    private $pdo;
    protected function setUp(): void
    {
        $this->pdo = new RecordingPdo('sqlite::memory:');
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->exec('CREATE TABLE items (id INTEGER, name TEXT)');
        $this->pdo->exec("INSERT INTO items VALUES (1, 'one'), (2, 'two')");
    }
    private function rules(array $changes = [])
    {
        return new rules($changes + ['criteria'=>['id'=>1], 'criteria_hash'=>['id'=>'i.id'], 'table_name'=>'items', 'table_alias'=>'i', 'column_hash'=>['i'=>['id','name']]]);
    }
    public function testPreparedSqlBindsCriteriaAndMapsColumnsAndOrdering()
    {
        $collection = new statement($this->pdo, $this->rules(['column_hash'=>['i'=>['id'=>'identifier','name']], 'order'=>['i'=>['name'=>'DESC']],'limit'=>2,'skip'=>0]));
        $this->assertSame(0, $collection->count());
        $this->assertStringContainsString('i.id as i_identifier', $this->pdo->last->queryString);
        $this->assertStringContainsString('i.name DESC', $this->pdo->last->queryString);
        $this->assertStringContainsString('LIMIT :skip,:limit', $this->pdo->last->queryString);
        $this->pdo->last->execute();
        $this->assertSame([['i_identifier'=>'1','i_name'=>'one']], $this->pdo->last->fetchAll(\PDO::FETCH_ASSOC));
    }
    public function testLimitsDefaultSkipAndStringCriteria()
    {
        foreach ([['limit'=>1], ['skip'=>0], ['limit'=>1,'skip'=>0]] as $changes) {
            new statement($this->pdo, $this->rules($changes + ['criteria'=>['id'=>'1'], 'order'=>['i'=>['id'=>'anything']]]));
            $this->pdo->last->execute();
            $this->assertSame('1', $this->pdo->last->fetchColumn());
            $this->assertStringContainsString('i.id ASC', $this->pdo->last->queryString);
        }
    }
    public function testCriteriaJoinHashAndStructuredRelationsProduceExecutableJoins()
    {
        $this->pdo->exec('CREATE TABLE labels (item_id INTEGER, caption TEXT)');
        $this->pdo->exec("INSERT INTO labels VALUES (1, 'label')");
        $collection = new statement($this->pdo, $this->rules([
            'criteria'=>['caption'=>'label'], 'criteria_hash'=>['caption'=>['l'=>'l.caption']],
            'join_hash'=>['l'=>' LEFT JOIN labels l ON i.id=l.item_id '],
        ]));
        $this->pdo->last->execute();
        $this->assertSame('1', $this->pdo->last->fetchColumn());
        new statement($this->pdo, $this->rules(['join'=>['l'=>['LEFT'=>['labels'=>['item_id'=>['i'=>'id']]]]]]));
        $this->assertStringContainsString('l.item_id=i.id', $this->pdo->last->queryString);
        $this->pdo->last->execute();
        $this->assertSame('1', $this->pdo->last->fetchColumn());
    }
    /** @dataProvider invalidRules */
    public function testInvalidStructuralRules($changes, $exception)
    {
        $this->expectException($exception);
        new statement($this->pdo, $this->rules($changes));
    }
    public function invalidRules()
    {
        $bad = \flat\db\driver\pdo\exception\bad_rule::class;
        return [[['criteria'=>null],$bad], [['criteria_hash'=>'bad'],$bad], [['column_hash'=>null],$bad],
            [['join_hash'=>'bad'],$bad], [['order'=>'bad'],$bad], [['table_name'=>''],$bad], [['table_alias'=>'1invalid'],$bad],
            [['table_name'=>'bad;name'],$bad], [['skip'=>'not integer'],$bad], [['limit'=>'bad'],$bad],
            [['criteria'=>[]],$bad], [['criteria'=>['id'=>[]]],\flat\db\driver\pdo\exception\bad_criteria::class],
            [['column_hash'=>['bad-prefix'=>['id']]],$bad], [['column_hash'=>['i'=>['bad-column']]],$bad],
            [['column_hash'=>['i'=>['bad-alias'=>'id']]],$bad]];
    }
    /**
     * @dataProvider currentlyRejectedFetchOptions
     * @group known-defect
     */
    public function testEveryExplicitFetchOptionCurrentlyThrows($fetch)
    {
        $this->expectException(\flat\db\driver\pdo\exception\bad_option::class);
        new statement($this->pdo, $this->rules(['options'=>['fetch'=>$fetch]]));
    }
    public function currentlyRejectedFetchOptions()
    {
        return [[true], [false], [RowFixture::class], [\stdClass::class], ['absent_class'], [AbstractRow::class], [function () {}], [42]];
    }
    public function testFetchCallbackConsumesActualRowsThroughABufferedNativeStatement()
    {
        $this->pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS,[BufferedSqliteStatement::class]); $seen=[];
        $result=new statement($this->pdo,$this->rules(['options'=>['fetch_callback'=>function ($row) use (&$seen) { $seen[]=$row; }]]));
        $this->assertEquals([['i_id'=>1,'i_name'=>'one']],$seen); $this->assertSame(0,$result->count());
    }
    /** @group known-defect */
    public function testExecutedRowsWithoutACallbackStillReferenceUndefinedDataClass()
    {
        $this->pdo->setAttribute(\PDO::ATTR_STATEMENT_CLASS,[BufferedSqliteStatement::class]);
        $this->expectException(\Error::class); $this->expectExceptionMessage('Class name must be a valid object or a string');
        new statement($this->pdo,$this->rules(['options'=>['execute'=>true]]));
    }
    public function testDeclaredJoinClauseCannotContainAStatementSeparator()
    {
        $rules=new JoinRules((array)$this->rules()); $rules->join_clause=';';
        $this->expectException(\flat\db\driver\pdo\exception\bad_rule::class); $this->expectExceptionMessage('invalid char ;');
        new statement($this->pdo,$rules);
    }
    /** @group known-defect */
    public function testExecuteCurrentlyReportsNotFoundForSqliteSelectRowCount()
    {
        try { new statement($this->pdo, $this->rules(['options'=>['execute'=>true]])); }
        catch (\flat\db\driver\pdo\exception\not_found $e) {
            $this->assertSame(['id'=>1], $e->get_criteria_keys_used());
            // The query did return a row; the rowCount gate is a separate builder defect.
            $this->assertSame('1', $this->pdo->last->fetchColumn());
            return;
        }
        $this->fail('Current SQLite not_found failure should be exposed');
    }
}
