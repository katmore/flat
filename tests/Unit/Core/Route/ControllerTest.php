<?php
namespace flat\test\Unit\RouteApps {
    class App implements \flat\core\routable, \flat\core\resolver, \flat\core\input\consumer,
        \flat\core\app\event\factory\consumer, \flat\core\resolver\prepared,
        \flat\core\resolver\unresolved_consumer, \flat\core\controller\route\restful_status
    {
        public static $created = [];
        public $resource; public $input; public $event; public $prepared = false; public $unresolved;
        public function __construct() { self::$created[] = $this; }
        public function set_resource($resource) { $this->resource = $resource; }
        public function set_input(\flat\input $input) { $this->input = $input; }
        public function set_event_factory(\flat\core\event\factory &$factory) { $this->event = $factory; }
        public function set_prepared_on() { $this->prepared = true; }
        public function set_unresolved($value) { $this->unresolved = $value; }
        public function get_status_code() { return 200; }
        public function get_status_string() { return 'OK'; }
    }
    class Leaf extends App {}
    class ParentApp extends App {}
    class _list extends App {}
    class Multi extends App implements \flat\route\cycle\multiple_ok {}
    class Demand extends App implements \flat\route\cycle\demander { public static function get_cycle_demand() { return 1; } }
    class ArrayDemand extends App implements \flat\route\cycle\demander { public static function get_cycle_demand() { return [0, 2]; } }
    class Ignored extends App implements \flat\route\ignore {}
    class RuntimeIgnored extends App implements \flat\route\ignorable { public function is_route_ignored() { return true; } }
    class RuntimeAccepted extends App implements \flat\route\ignorable { public function is_route_ignored() { return false; } }
    class Unresolved extends App implements \flat\route\resolution\unresolved {}
    class Provider extends App implements \flat\route\resolution\provider { public function is_resolved() { return false; } }
    class Suppressed extends App implements \flat\core\debug\suppress {}
    class Vanilla implements \flat\core\routable {}
    abstract class AbstractApp extends App {}
}
namespace flat\test\Unit\RouteApps\ParentApp { class Leaf extends \flat\test\Unit\RouteApps\App {} }
namespace flat\test\Unit\Core\Route {
    use flat\core\controller\route;
    use flat\test\Unit\RouteApps\App;
    use PHPUnit\Framework\TestCase;
    class RouteFactory extends \flat\route\factory
    {
        public function __construct(array $rules = null)
        {
            if ($rules === null) $rules = [['ns' => 'flat\\test\\Unit\\RouteApps', 'weight' => 0]];
            foreach ($rules as $rule) $this->add_route(new \flat\route\rule($rule));
        }
        public function get_base() { return '/'; }
    }
    class InterfaceBaseFactory extends RouteFactory implements \flat\route\base {}
    class NodeProbe extends route
    {
        public function __construct() {}
        public function normalize(array $nodes) { return $this->_transformSpecialNodes($nodes); }
    }
    class CustomNodeProbe extends NodeProbe { const SPECIAL_NODES = ['custom']; }
    class ControllerTest extends TestCase
    {
        private function controller($resource, array $rules, array $extra = [])
        {
            return new route(array_merge(['resource' => $resource, 'route_factory' => new RouteFactory($rules), 'input' => ['value' => 42]], $extra));
        }
        private function rule($name, array $extra = []) { return array_merge(['ns' => 'flat\\test\\Unit\\RouteApps\\' . $name, 'ignore_resource' => true, 'weight' => 0], $extra); }
        protected function setUp(): void { App::$created = []; \flat\core\debug::set_suppress_off(); }
        protected function tearDown(): void { \flat\core\debug::set_suppress_off(); }
        public function testResolutionInjectsInputsLifecycleAndStatus()
        {
            $statuses = [];
            $controller = $this->controller('Leaf', [['ns' => 'flat\\test\\Unit\\RouteApps', 'weight' => 0]], ['restful_status_callback' => function ($status) use (&$statuses) { $statuses[] = $status; }]);
            $this->assertSame(1, $controller->get_resolve_count());
            $app = App::$created[0];
            $this->assertSame('Leaf', $app->resource); $this->assertSame(42, $app->input->value);
            $this->assertTrue($app->prepared); $this->assertSame('', $app->unresolved);
            $this->assertInstanceOf(\flat\core\app\event\factory::class, $app->event);
            $this->assertSame(200, $statuses[0]->code); $this->assertSame('OK', $statuses[0]->string);
            $controller->get_collection()->rewind();
            $this->assertSame($app, $controller->get_collection()->current());
        }
        public function testSameWeightIsSkippedAndLaterWeightsResolve()
        {
            $controller = $this->controller('Leaf', [$this->rule('Leaf'), $this->rule('RuntimeAccepted'), $this->rule('RuntimeAccepted', ['weight' => 1])]);
            $this->assertSame(2, $controller->get_resolve_count());
            $this->assertCount(2, App::$created);
        }
        public function testTraversalCanResolveAParentAndItsChild()
        {
            $controller = $this->controller('ParentApp/Leaf', [['ns' => 'flat\\test\\Unit\\RouteApps', 'iterate' => true, 'weight' => 0]]);
            $this->assertSame(2, $controller->get_resolve_count());
            $this->assertSame('ParentApp\\Leaf', App::$created[0]->resource);
            $single = $this->controller('ParentApp/Leaf', [['ns' => 'flat\\test\\Unit\\RouteApps', 'iterate' => false, 'weight' => 0]]);
            $this->assertSame(1, $single->get_resolve_count());
        }
        public function testResolverCanConsumeUnmatchedSuffix()
        {
            $controller = $this->controller('ParentApp/Missing', [['ns' => 'flat\\test\\Unit\\RouteApps', 'iterate' => false, 'traverse' => false, 'weight' => 0]]);
            $this->assertSame(1, $controller->get_resolve_count());
            $this->assertSame('Missing', App::$created[0]->unresolved);
            $unmatched = $this->controller('Vanilla/Missing', [['ns' => 'flat\\test\\Unit\\RouteApps', 'iterate' => false, 'traverse' => false, 'weight' => 0]]);
            $this->assertSame(0, $unmatched->get_resolve_count());
        }
        public function testResourceTransformsAndNullTransformFallback()
        {
            $controller = $this->controller('unmatched', [['ns' => 'flat\\test\\Unit\\RouteApps', 'weight' => 0, 'transform' => function ($resource) { $this->assertSame('unmatched', $resource); return 'Leaf'; }]]);
            $this->assertSame(1, $controller->get_resolve_count());
            $this->assertSame('unmatched', App::$created[0]->resource);
            $this->assertSame(1, $this->controller('Leaf', [['ns' => 'flat\\test\\Unit\\RouteApps', 'transform' => function () { return null; }]])->get_resolve_count());
        }
        /** @dataProvider cycleRules */
        public function testCyclesAndDemands($name, $expected)
        {
            $this->assertSame($expected, $this->controller('unused', [$this->rule($name, ['cycles' => 3])])->get_resolve_count());
        }
        public function cycleRules() { return [['Leaf', 1], ['Multi', 3], ['Demand', 1], ['ArrayDemand', 2]]; }
        /** @dataProvider ignoredClasses */
        public function testIgnoredOrAbstractClassesDoNotResolve($name)
        {
            $this->assertSame(0, $this->controller('unused', [$this->rule($name)])->get_resolve_count());
        }
        public function ignoredClasses() { return [['Ignored'], ['RuntimeIgnored'], ['AbstractApp'], ['Missing']]; }
        public function testUnresolvedAndProviderClassesDoNotStopIteration()
        {
            foreach (['Unresolved', 'Provider'] as $name) {
                $this->assertSame(1, $this->controller('unused', [$this->rule($name, ['iterate' => false])])->get_resolve_count());
            }
        }
        public function testDebugSuppressInterface()
        {
            $this->assertSame(1, $this->controller('unused', [$this->rule('Suppressed')])->get_resolve_count());
            $this->assertTrue(\flat\core\debug::is_suppress_active());
        }
        public function testStringFactoryAndBaseInterface()
        {
            $this->assertSame(1, (new route(['resource' => 'Leaf', 'route_factory' => RouteFactory::class, 'input' => []]))->get_resolve_count());
            $this->assertSame(1, (new route(['resource' => 'Leaf', 'route_factory' => new InterfaceBaseFactory, 'input' => []]))->get_resolve_count());
        }
        public function testReservedNodesAndCustomSetsAreCachedSeparately()
        {
            $probe = new NodeProbe;
            $this->assertSame(['_list', 'normal'], $probe->normalize(['list', 'normal']));
            $this->assertSame(['_list'], $probe->normalize(['list']));
            $this->assertSame(['_custom', 'list'], (new CustomNodeProbe)->normalize(['custom', 'list']));
            $this->assertSame(1, $this->controller('list', [['ns' => 'flat\\test\\Unit\\RouteApps']])->get_resolve_count());
        }
        /** @dataProvider invalidParams */
        public function testInvalidControllerParameters(array $params, $exception)
        {
            $this->expectException($exception); new route($params);
        }
        public function invalidParams()
        {
            return [[[], \flat\core\controller\route\exception\bad_resource::class],
                [['resource' => 'Leaf'], \flat\core\controller\route\exception\missing::class],
                [['resource' => 'Leaf', 'route_factory' => new \stdClass], \flat\core\controller\route\exception\bad_resource::class],
                [['resource' => 'Leaf', 'route_factory' => 'NoSuchRouteFactory'], \flat\core\controller\route\exception\no_route::class]];
        }
    }
}
