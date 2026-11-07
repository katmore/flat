<?php
namespace flat\core {
    function openssl_random_pseudo_bytes($length, &$strong = null) {
        if (!empty($GLOBALS['flat_unit_weak_nonce'])) { $strong = false; return str_repeat('x', $length); }
        return \openssl_random_pseudo_bytes($length, $strong);
    }
}
namespace flat\test\Unit\Core\Session {
    use flat\core\session;
    use PHPUnit\Framework\TestCase;
    /**
     * @runTestsInSeparateProcesses
     * @preserveGlobalState disabled
     */
    class SessionTest extends TestCase
    {
        private $directory;
        protected function setUp(): void
        {
            $this->directory = sys_get_temp_dir().'/flat-session-'.bin2hex(random_bytes(8));
            mkdir($this->directory);
            ini_set('session.save_path', $this->directory);
            ini_set('session.use_cookies', '0');
            ini_set('session.use_only_cookies', '0');
            ini_set('session.cache_limiter', '');
        }
        protected function tearDown(): void
        {
            if (session_status() === PHP_SESSION_ACTIVE) session_destroy();
            foreach (glob($this->directory.'/*') as $file) unlink($file);
            rmdir($this->directory);
        }
        public function testStartRefreshIdAndSnapshot()
        {
            $this->assertSame('flat-unit-session', session::start(['no-cookies', 'refresh'], 'flat-unit-session'));
            $this->assertSame(PHP_SESSION_ACTIVE, session_status());
            $this->assertSame('flat-unit-session', session::get_id());
            $this->assertSame('flat-unit-session', session::load()->id);
            $this->assertArrayHasKey('time_float', $_SESSION[session::root_ns.'/refresh']);
            session::refresh('custom');
            $this->assertArrayHasKey('date', $_SESSION['custom']);
            $_SESSION['zero'] = 0; $_SESSION['empty'] = [];
            $snapshot = session::load(true);
            $this->assertSame(0, $snapshot->data->zero);
            $this->assertSame([], $snapshot->data->empty);
            $_SESSION['zero'] = 1;
            $this->assertSame(0, $snapshot->data->zero);
        }
        public function testStoredValuesCanBeReadAndRemoved()
        {
            foreach (['text', 0, false, []] as $value) {
                session::set('key', $value); $this->assertSame($value, session::get('key'));
            }
            session::set(42, 'numeric'); $this->assertSame('numeric', session::get(42));
            session::set('key', null); $this->assertNull(session::get('key'));
            $this->assertNull(session::get('missing'));
        }
        public function testNonceCanBeConsumedOnlyOnce()
        {
            $nonce = session::generate_nonce('form', 60, ['value' => 0]);
            $this->assertSame(16, strlen(base64_decode($nonce, true)));
            $this->assertEquals((object) ['value' => 0], session::consume_nonce('form', $nonce));
            $this->expectException(\flat\core\session\exception\nonce_consumed::class);
            session::consume_nonce('form', $nonce);
        }
        public function testNonceWithoutDataOrExpiryAndFalsyData()
        {
            $nonce = session::generate_nonce('plain');
            $this->assertNull(session::consume_nonce('plain', $nonce));
            foreach ([0, false, ''] as $value) {
                $nonce = session::generate_nonce('value', null, $value);
                $this->assertSame($value, session::consume_nonce('value', $nonce));
            }
        }
        public function testMissingNonceIsRejected()
        {
            $this->expectException(\flat\core\session\exception\nonce_not_found::class);
            session::consume_nonce('unknown', 'missing');
        }
        public function testExpiredNonceIsRejectedWithoutSleeping()
        {
            $nonce = session::generate_nonce('expired', 1);
            $_SESSION[session::root_ns.'/nonce']['expired'][$nonce]->created = date('c', time()-60);
            $this->expectException(\flat\core\session\exception\nonce_expired::class);
            session::consume_nonce('expired', $nonce);
        }
        /**
         * The TTL is "number of seconds from creation time": a nonce whose age
         * equals its TTL is still accepted; one second older is expired.
         */
        public function testNonceIsAcceptedAtExactlyItsTtl()
        {
            $ttl = 30;
            // Age is measured in whole seconds. Retry only if the clock ticks
            // between backdating and consuming, which would make the age ttl+1.
            for ($attempt = 0; $attempt < 5; $attempt++) {
                $nonce = session::generate_nonce('boundary', $ttl, 'accepted');
                $now = time();
                $_SESSION[session::root_ns.'/nonce']['boundary'][$nonce]->created = date('c', $now - $ttl);
                try {
                    $data = session::consume_nonce('boundary', $nonce);
                } catch (\flat\core\session\exception\nonce_expired $e) {
                    if (time() !== $now) continue;
                    throw $e;
                }
                $this->assertSame('accepted', $data);
                return;
            }
            $this->fail('clock advanced during every attempt');
        }
        public function testNonceIsExpiredOneSecondAfterItsTtl()
        {
            $ttl = 30;
            $nonce = session::generate_nonce('boundary', $ttl);
            $_SESSION[session::root_ns.'/nonce']['boundary'][$nonce]->created = date('c', time() - $ttl - 1);
            $this->expectException(\flat\core\session\exception\nonce_expired::class);
            session::consume_nonce('boundary', $nonce);
        }
        public function testCorruptNonceTtlIsRejected()
        {
            $nonce = session::generate_nonce('corrupt', 60);
            $_SESSION[session::root_ns.'/nonce']['corrupt'][$nonce]->ttl = 'not an integer';
            $this->expectException(\flat\lib\exception\app_error::class);
            session::consume_nonce('corrupt', $nonce);
        }
        /** @dataProvider invalidNonceParameters */
        public function testInvalidNonceParameters($key, $ttl, $exception)
        {
            $this->expectException($exception); session::generate_nonce($key, $ttl);
        }
        public function invalidNonceParameters()
        {
            return [['', null, \flat\core\session\exception\bad_key::class],
                [123, null, \flat\core\session\exception\bad_key::class],
                ['key', '60', \flat\lib\exception\bad_param::class]];
        }
        public function testWeakRandomnessIsRejected()
        {
            $GLOBALS['flat_unit_weak_nonce'] = true;
            $this->expectException(\flat\lib\exception\app_error::class);
            session::generate_nonce('weak');
        }
        public function testInvalidSessionKey()
        {
            $this->expectException(\flat\core\session\exception\bad_key::class);
            session::set([], 'value');
        }
        /** @dataProvider invalidRefreshKeys */
        public function testInvalidRefreshKey($key)
        {
            $this->expectException(\flat\core\session\exception\bad_key::class); session::refresh($key);
        }
        public function invalidRefreshKeys() { return [[12], [session::root_ns.'/collision']]; }
        public function testNoCookieStartRequiresExplicitId()
        {
            $this->expectException(\flat\core\session\exception\bad_config::class);
            session::start(['no-cookies']);
        }
        public function testDestroyNotifiesListenersAndCanRestart()
        {
            session::start(); session::set('key', 'old'); $calls = [];
            session::add_destroy_listener(function ($flags) use (&$calls) { $calls[] = $flags; });
            session::destroy();
            $this->assertSame([[]], $calls); $this->assertSame(PHP_SESSION_NONE, session_status());
            session::start(['destroy']);
            $this->assertSame(PHP_SESSION_ACTIVE, session_status());
            $this->assertNull(session::get('key'));
        }
        public function testCookieSessionDestroyReportsCookieCleanup()
        {
            ini_set('session.use_cookies', '1'); ini_set('session.use_only_cookies', '1');
            session::start(); $calls = [];
            session::add_destroy_listener(function ($flags) use (&$calls) { $calls[] = $flags; });
            session::destroy();
            $this->assertSame([['no-cookies']], $calls);
            $this->assertSame(PHP_SESSION_NONE, session_status());
        }

        public function testMissingSessionGlobalIsRejectedEvenWhenActive()
        {
            session::start(); $saved = $_SESSION; unset($_SESSION);
            try {
                $this->expectException(\flat\core\session\exception\no_session_var::class);
                session::start();
            } finally { $_SESSION = $saved; }
        }
        public function testFailedStorageRetriesAndReturnsNoId()
        {
            ini_set('session.save_path', $this->directory.'/missing');
            $this->assertSame('', session::start([]));
            $this->assertSame(PHP_SESSION_NONE, session_status());
            $this->assertTrue(isset($_SESSION));
        }
    }
}
