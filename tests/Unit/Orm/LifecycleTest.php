<?php
namespace flat\test\Unit\Orm;
use PHPUnit\Framework\TestCase;
class Model extends \flat\orm
{
    public $id;
    public $name;
    public static $keys = ['id'];
    public $calls = [];
    public $result;
    protected function _get_key_list() { return self::$keys; }
    protected function _exists() { $this->calls[] = 'exists'; return $this->result; }
    protected function _before_create_record() { parent::_before_create_record(); $this->calls[] = 'before-create'; }
    protected function _before_read_record() { parent::_before_read_record(); $this->calls[] = 'before-read'; }
    protected function _before_update_record() { parent::_before_update_record(); $this->calls[] = 'before-update'; }
    protected function _before_delete_record() { parent::_before_delete_record(); $this->calls[] = 'before-delete'; }
    protected function _create() { parent::_create(); $this->calls[] = 'create'; return $this->result; }
    protected function _read() { parent::_read(); $this->calls[] = 'read'; return $this->result; }
    protected function _update() { parent::_update(); $this->calls[] = 'update'; return $this->result; }
    protected function _delete() { parent::_delete(); $this->calls[] = 'delete'; return $this->result; }
    public static function keyMap($keys,$value) { return parent::_keyval2map($keys,$value); }
    public static function dataMap(array $data,array $fields=null) { return parent::_data2map($data,$fields); }
}
class CrudModel extends Model implements \flat\orm\crud\create, \flat\orm\crud\read, \flat\orm\crud\update, \flat\orm\crud\delete {}
class LifecycleTest extends TestCase
{
    protected function setUp(): void { Model::$keys = ['id']; }
    protected function tearDown(): void { Model::$keys = ['id']; }
    public function testUnsupportedOperationsDoNothing()
    {
        $model = new Model(1);
        $this->assertNull($model->create()); $this->assertNull($model->read());
        $this->assertNull($model->update()); $this->assertNull($model->delete());
        $this->assertSame([],$model->calls);
    }
    public function testCreateReadAndUpdateTrackTheirLifecycle()
    {
        $created = new CrudModel(1); $created->create(); $created->create();
        $this->assertTrue($created->exists());
        $this->assertSame(['before-create','create'],$created->calls);
        $read = new CrudModel(1); $this->assertSame($read,$read->read()); $read->read();
        $this->assertTrue($read->exists());
        $this->assertSame(['before-read','read'],$read->calls);
        $read->create(); $this->assertCount(2,$read->calls);
        $read->update(); $read->update();
        $this->assertSame(['before-read','read','before-update','update','before-update','update'],$read->calls);
    }
    public function testDeleteWorksAfterReadAndOnlyOnce()
    {
        $deleted = new CrudModel(1); $deleted->delete();
        $this->assertFalse($deleted->exists());
        $this->assertSame(['before-delete','delete'],$deleted->calls);
        $read = new CrudModel(1); $read->read(); $read->delete();
        $this->assertFalse($read->exists()); // A record that was read can be deleted.
        $this->assertSame(['before-read','read','before-delete','delete'],$read->calls);
        $read->delete(); // A second delete does nothing.
        $this->assertSame(['before-read','read','before-delete','delete'],$read->calls);
    }
    /** @dataProvider operationFailures */
    public function testStorageHookFailuresExplainTheOperation($operation,$result)
    {
        $model = new CrudModel(1); $model->result = $result;
        try { $model->$operation(); }
        catch (\flat\orm\crud_error $e) {
            $this->assertStringContainsString('orm '.$operation.' error',$e->getMessage());
            $this->assertSame(is_string($result) ? $result : null,$e->get_details());
            $this->assertSame($operation,$e->get_op()); return;
        }
        $this->fail('Failed hook must throw');
    }
    public function operationFailures()
    {
        $rows=[]; foreach (['create','read','update','delete'] as $operation) foreach ([false,'failure details'] as $result) $rows[]=[$operation,$result]; return $rows;
    }
    public function testExistenceChecksAreCachedOnlyAfterAConcreteAnswer()
    {
        $model = new Model(1);
        $this->assertNull($model->exists()); $this->assertNull($model->exists());
        $this->assertSame(['exists','exists'],$model->calls);
        $model->result = false; $this->assertFalse($model->exists());
        $model->result = true; $this->assertFalse($model->exists());
        $other = new Model(2); $other->result = true; $this->assertTrue($other->exists());
    }
    public function testKeyAndFieldMappingRetainNamedAndPositionalValues()
    {
        $this->assertSame(['id'=>1],Model::keyMap('id',1));
        $this->assertSame([],Model::keyMap('missing',1));
        $this->assertSame(['id'=>1,'name'=>1],Model::keyMap(['id','name','missing'],1));
        $this->assertSame(['id'=>2],Model::keyMap(['id'],(object) ['id'=>2,'ignored'=>3]));
        $this->assertSame([],Model::keyMap([],null));
        $this->assertSame(['name'=>'value'],Model::dataMap(['name'=>'value','ignored'=>1]));
        $this->assertSame(['id'=>'first','name'=>'second'],Model::dataMap(['first','second','ignored'],['id','name']));
        $this->assertSame(['id'=>'first'],Model::dataMap(['first'],['id','name']));
        $this->assertSame([],Model::dataMap([]));
        $model = new Model(['id'=>2]); $model->set_data(['name'=>'value']);
        $this->assertSame(2,$model->id); $this->assertSame('value',$model->name);
        Model::$keys = 'id'; $other = new Model(3); $this->assertSame(3,$other->id);
    }
}
