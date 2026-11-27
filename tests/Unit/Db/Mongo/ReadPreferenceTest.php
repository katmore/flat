<?php
namespace flat\test\Unit\Db\Mongo;
use Alcaeus\MongoDbAdapter\Helper\ReadPreference;
use PHPUnit\Framework\TestCase;

/** Stands in for the adapter classes that use the trait (MongoClient, MongoDB, MongoCollection). */
class ReadPreferenceHarness
{
    use ReadPreference;
    public function __construct() { $this->getReadPreference(); } // the adapter classes always set one when they are built
    public function setReadPreference($readPreference, $tags = null) { return $this->setReadPreferenceFromParameters($readPreference, $tags); }
    public function fromArray($array) { return $this->setReadPreferenceFromArray($array); }
    public function slaveOkay($ok = true) { return $this->setReadPreferenceFromSlaveOkay($ok); }
    public function isSlaveOkay() { return $this->getSlaveOkayFromReadPreference(); }
    public function mode() { return $this->getReadPreferenceModeString(); }
    public function driverReadPreference() { return $this->readPreference; }
}

/**
 * flat carries a corrected copy of the adapter's read-preference trait (Q53): the adapter used what ext-mongodb 1.20
 * deprecates. These tests run the trait without a server and fail on any PHP message.
 */
class ReadPreferenceTest extends TestCase
{
    private $messages = [];

    protected function setUp(): void
    {
        $this->messages = [];
        set_error_handler(function ($level, $message) { $this->messages[] = $message; return true; });
    }

    protected function tearDown(): void
    {
        restore_error_handler();
    }

    public function testFlatsCopyOfTheTraitIsTheOneThatLoads()
    {
        $file = (new \ReflectionClass(ReadPreference::class))->getFileName();
        $this->assertSame(realpath(__DIR__.'/../../../../src/flat/vendor/alcaeus/mongo-php-adapter/Helper/ReadPreference.php'), realpath($file));
    }

    public function testTheDefaultIsPrimaryWithoutTags()
    {
        $harness = new ReadPreferenceHarness;
        $this->assertSame(['type' => \MongoClient::RP_PRIMARY], $harness->getReadPreference());
        $this->assertSame('primary', $harness->mode());
        $this->assertFalse($harness->isSlaveOkay());
        $this->assertSame([], $this->messages);
    }

    public function modes()
    {
        return [
            'primary' => [\MongoClient::RP_PRIMARY, false],
            'primary preferred' => [\MongoClient::RP_PRIMARY_PREFERRED, true],
            'secondary' => [\MongoClient::RP_SECONDARY, true],
            'secondary preferred' => [\MongoClient::RP_SECONDARY_PREFERRED, true],
            'nearest' => [\MongoClient::RP_NEAREST, true],
        ];
    }

    /** @dataProvider modes */
    public function testEveryLegacyModeIsSetAndReadBack($mode, $slaveOkay)
    {
        $harness = new ReadPreferenceHarness;
        $this->assertTrue($harness->setReadPreference($mode));
        $this->assertSame($mode, $harness->mode());
        $this->assertSame(['type' => $mode], $harness->getReadPreference());
        $this->assertSame($slaveOkay, $harness->isSlaveOkay());
        $this->assertInstanceOf(\MongoDB\Driver\ReadPreference::class, $harness->driverReadPreference());
        $this->assertSame([], $this->messages);
    }

    public function testTagSetsAreKept()
    {
        $harness = new ReadPreferenceHarness;
        $this->assertTrue($harness->setReadPreference(\MongoClient::RP_SECONDARY, [['dc' => 'east']]));
        $this->assertSame(['type' => \MongoClient::RP_SECONDARY, 'tagsets' => [['dc' => 'east']]], $harness->getReadPreference());
        $this->assertSame([], $this->messages);
    }

    public function testTheArrayFormIsAcceptedForInternalCopies()
    {
        $harness = new ReadPreferenceHarness;
        $this->assertTrue($harness->fromArray(['type' => \MongoClient::RP_NEAREST, 'tagsets' => [['dc' => 'west']]]));
        $this->assertSame(['type' => \MongoClient::RP_NEAREST, 'tagsets' => [['dc' => 'west']]], $harness->getReadPreference());
        $this->assertTrue($harness->fromArray(['type' => \MongoClient::RP_PRIMARY]));
        $this->assertSame(['type' => \MongoClient::RP_PRIMARY], $harness->getReadPreference());
        $this->assertSame([], $this->messages);
    }

    public function testAnUnknownModeWarnsAndChangesNothing()
    {
        $harness = new ReadPreferenceHarness;
        $this->assertFalse($harness->setReadPreference('whatever'));
        $this->assertSame(["The value 'whatever' is not valid as read preference type"], $this->messages);
        $this->assertSame(['type' => \MongoClient::RP_PRIMARY], $harness->getReadPreference());
    }

    public function testPrimaryRefusesTags()
    {
        $harness = new ReadPreferenceHarness;
        $this->assertFalse($harness->setReadPreference(\MongoClient::RP_PRIMARY, [['dc' => 'east']]));
        $this->assertSame(["You can't use read preference tags with a read preference of PRIMARY"], $this->messages);
    }

    public function testSlaveOkayTogglesSecondaryPreferredAndReportsTheEarlierState()
    {
        $harness = new ReadPreferenceHarness;
        $this->assertFalse($harness->slaveOkay(true));
        $this->assertSame('secondaryPreferred', $harness->mode());
        $this->assertTrue($harness->slaveOkay(false));
        $this->assertSame('primary', $harness->mode());
        $this->assertFalse($harness->isSlaveOkay());
        $this->assertSame([], $this->messages);
    }

    public function testSlaveOkayKeepsTheTagSets()
    {
        $harness = new ReadPreferenceHarness;
        $harness->setReadPreference(\MongoClient::RP_NEAREST, [['dc' => 'east']]);
        $harness->slaveOkay(true);
        $this->assertSame(['type' => \MongoClient::RP_SECONDARY_PREFERRED, 'tagsets' => [['dc' => 'east']]], $harness->getReadPreference());
        $this->assertSame([], $this->messages);
    }
}
