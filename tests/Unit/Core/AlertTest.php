<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
use flat\event\alert;
class AlertCrudDouble
{
    public $calls = [];
    public function create($data, $options) { $this->calls[] = ['create', $data, $options]; }
    public function update($find, $update) { $this->calls[] = ['update', $find, $update]; }
}
class AlertChannelDouble
{
    public $callbacks = [];
    public $calls = [];
    public $timeout = false;
    public function queue_declare(...$args) { $this->calls[] = ['queue', $args]; }
    public function basic_consume(...$args) { $this->calls[] = ['consume', $args]; $this->callbacks[] = $args[6]; }
    public function wait(...$args)
    {
        if ($this->timeout) {
            $this->timeout = false;
            throw new \PhpAmqpLib\Exception\AMQPTimeoutException('local timeout');
        }
        $callback = array_shift($this->callbacks);
        $callback(new \PhpAmqpLib\Message\AMQPMessage('{"answer":42}'));
    }
}
class AlertDriverDouble
{
    public $channel;
    public function get_channel() { return $this->channel; }
}
class AlertFixture extends alert implements alert\mongo_crud_provider, alert\driver_provider, alert\queue_provider
{
    public static $crud;
    public static $driver;
    public static $queue;
    public static function get_alert_mongo_crud() { return self::$crud; }
    public static function get_alert_driver() { return self::$driver; }
    public static function get_alert_queue() { return self::$queue; }
}
class DefaultAlert extends alert {}
class AlertTest extends TestCase
{
    protected function setUp(): void
    {
        AlertFixture::$crud = new AlertCrudDouble;
        AlertFixture::$driver = new AlertDriverDouble;
        AlertFixture::$driver->channel = new AlertChannelDouble;
        AlertFixture::$queue = 'local-alert';
    }
    public function testQueueProviderSuffixAndFallback()
    {
        $this->assertSame('local-alert', AlertFixture::get_queue());
        $this->assertSame('local-alert.listener:42', AlertFixture::get_queue('listener:42'));
        $this->assertSame(DefaultAlert::class, DefaultAlert::get_queue());
        AlertFixture::$queue = '';
        $this->assertSame(AlertFixture::class, AlertFixture::get_queue());
    }
    /** @dataProvider ttls */
    public function testSubscriptionsStoreExpirationAndUniqueId($ttl, $finite)
    {
        $before = time(); $data = AlertFixture::subscribe($ttl);
        $this->assertInstanceOf(alert\subscription_data::class, $data);
        $this->assertSame(AlertFixture::class, $data->alert_type);
        $this->assertSame(['sub_collection'=>'subscription'], AlertFixture::$crud->calls[0][2]);
        $this->assertSame($data, AlertFixture::$crud->calls[0][1]);
        $this->assertRegExp('/^[a-f0-9-]{36}$/', $data->uuid);
        if ($finite) {
            $this->assertGreaterThanOrEqual($before + $ttl, $data->expires);
            $this->assertLessThanOrEqual(time() + $ttl, $data->expires);
        } else { $this->assertFalse($data->expires); }
    }
    public function ttls() { return [[false,false],[0,false],[-1,false],['30',false],[true,false],[30,true]]; }
    /** @dataProvider timeouts */
    public function testListenerDecodesMessagesAndRefreshesExpiryOnTimeout($timeout)
    {
        $channel = AlertFixture::$driver->channel; $channel->timeout = $timeout;
        $seen = []; $before = time();
        AlertFixture::add_listener(function ($data) use (&$seen) { $seen[] = $data; });
        $data = AlertFixture::$crud->calls[0][1];
        // A new listener is registered to expire a time to live from now.
        $this->assertGreaterThanOrEqual($before + AlertFixture::listener_ttl, $data->expires);
        $this->assertLessThanOrEqual(time() + AlertFixture::listener_ttl, $data->expires);
        $this->assertSame(['sub_collection'=>'listener'], AlertFixture::$crud->calls[0][2]);
        $this->assertSame('local-alert.listener:'.$data->uuid, $channel->calls[0][1][0]);
        $this->assertSame(42, $seen[0]->answer);
        $this->assertSame([], $channel->callbacks);
        if ($timeout) {
            $this->assertSame(['uuid'=>$data->uuid], AlertFixture::$crud->calls[1][1]);
            $this->assertGreaterThanOrEqual(time(), AlertFixture::$crud->calls[1][2]['$set']['expires']);
        } else { $this->assertCount(1, AlertFixture::$crud->calls); }
    }
    public function timeouts() { return [[false],[true]]; }
    public function testRetiredPublisherFailsBeforeInvokingCallbacksOrChangingSubscriptions()
    {
        try {
            AlertFixture::trigger('payload',['listener'=>function () { $this->fail('Retired publisher must not invoke a listener'); },
                'success'=>function () { $this->fail('Retired publisher must not claim success'); }]);
            $this->fail('Expected retired publisher exception');
        } catch (\BadMethodCallException $error) { $this->assertStringContainsString('retired',$error->getMessage()); }
        $this->assertSame([],AlertFixture::$crud->calls); $this->assertSame([],AlertFixture::$driver->channel->calls);
    }
    public function testRetiredPthreadScaffoldingNoLongerDeclaresAThreadClass()
    {
        $this->assertFalse(class_exists(\flat\event\alert\listener::class));
    }
    public function testUnimplementedLogAndSubscriptionListenerMethodsAreNoOps()
    {
        $this->assertNull(AlertFixture::get_log());
        $this->assertNull(AlertFixture::get_subscription_log('42'));
        $this->assertNull(AlertFixture::add_subscription_listener('42', function () { $this->fail('Not implemented'); }));
        $this->assertSame([], AlertFixture::$crud->calls);
    }
}
