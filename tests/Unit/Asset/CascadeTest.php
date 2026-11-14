<?php
namespace flat\test\Unit\Asset\LocalAsset\sub {
    class leaf extends \flat\test\Unit\Asset\LocalAsset {}
}
namespace flat\test\Unit\Asset\LocalAsset {
    class sub extends \flat\test\Unit\Asset\LocalAsset {}
    abstract class unavailable extends \flat\test\Unit\Asset\LocalAsset {}
}
namespace flat\test\Unit\Asset {
    use PHPUnit\Framework\TestCase;
    use flat\asset;
    class BaseControllerAsset extends \flat\core\controller\asset
    {
        protected function on_resolve_param(string $asset, string $key, $value): string { return $key === 'target' ? $value : parent::on_resolve_param($asset,$key,$value); }
    }
    class ReplacementControllerAsset extends BaseControllerAsset {}
    abstract class AbstractControllerAsset extends BaseControllerAsset {}
    class CascadeTest extends TestCase
    {
        private $saved = [];
        protected function setUp(): void
        {
            foreach ([asset::class, \flat\core\controller\asset::class] as $class) foreach ((new \ReflectionClass($class))->getProperties(\ReflectionProperty::IS_STATIC) as $p) {
                $p->setAccessible(true); $this->saved[$class][$p->getName()] = $p->getValue(); $p->setValue(null, $class === asset::class ? [] : null);
            }
        }
        protected function tearDown(): void
        {
            foreach ($this->saved as $class=>$values) foreach ($values as $name=>$value) { $p = new \ReflectionProperty($class,$name); $p->setAccessible(true); $p->setValue(null,$value); }
        }
        public function testFullAndAncestorCascadeClassesResolve()
        {
            $direct = LocalAsset::asset('sub/leaf.js', ['cascade'=>true]);
            $this->assertInstanceOf(LocalAsset\sub\leaf::class, $direct);
            $this->assertSame('leaf.js', $direct->get_resource_uri());
            $ancestor = LocalAsset::asset('sub/other.js', ['cascade'=>true]);
            $this->assertInstanceOf(LocalAsset\sub::class, $ancestor);
            $this->assertSame('other.js', $ancestor->get_resource_uri());
            $explicit = LocalAsset::asset('other.js', ['cascade'=>'\\'.LocalAsset\sub::class]);
            $this->assertInstanceOf(LocalAsset\sub::class, $explicit);
            $relative = LocalAsset::asset('other.js', ['cascade'=>'sub']);
            $this->assertInstanceOf(LocalAsset\sub::class, $relative);
            $this->assertInstanceOf(LocalAsset::class, LocalAsset::asset('other.js', ['cascade'=>'unavailable','unknown'=>'ignored']));
            $this->assertInstanceOf(LocalAsset::class, LocalAsset::asset('unavailable/other.js', ['cascade'=>true]));
        }
        public function testControllerResolutionSubstitutesOnlyInstantiableClasses()
        {
            $base = new BaseControllerAsset('/path\\file.js');
            $this->assertSame('path/file.js', $base->get_resource_uri());
            $this->assertSame($base, $base->resolve());
            $resolved = $base->resolve(['target'=>'\\'.ReplacementControllerAsset::class]);
            $this->assertInstanceOf(ReplacementControllerAsset::class, $resolved);
            $this->assertSame('path/file.js', $resolved->get_resource_uri());
            $this->assertSame($base, $base->resolve(['target'=>'\\'.AbstractControllerAsset::class]));
            $this->assertSame($base, $base->resolve(['target'=>'']));
        }
        public function testArrayDependencyGroupsPreserveTheirOrder()
        {
            asset::register_asset('parent.js',['depends'=>[['one.js','two.js']]]);
            $seen = []; asset::each_registered_asset(function ($asset) use (&$seen) { $seen[]=$asset->get_resource_uri(); });
            $this->assertSame(['one.js','two.js','parent.js'],$seen);
        }
        public function testNestedDependencyInputsAndParameterForms()
        {
            $dependency = new asset('dep.js');
            $parent = asset::register_asset('parent.js', [$dependency, 'depends'=>[['asset'=>'nested.css','depends'=>['base.css']]]]);
            $parent->depends(new asset('extra.js'));
            $parent->depends(null, [['asset'=>new asset('array.js')]]);
            $seen = [];
            asset::each_registered_asset(function ($asset) use (&$seen) { $seen[] = $asset->get_resource_uri(); });
            $this->assertSame(['dep.js','base.css','nested.css','extra.js','array.js','parent.js'], $seen);
            $this->assertSame($parent, $parent->register(['depends'=>[$dependency]]));
        }
    }
}
