<?php
namespace flat\test\Unit\Core;
use PHPUnit\Framework\TestCase;
// Preserve coverage of this file's actual historical declaration; its basename
// uses bad_param while the declared class is badParam.
require_once __DIR__.'/../../../src/flat/core/ssh/exception/input_error/bad_param.php';
class UnlabeledDuplicateError extends \flat\db\op\exception\duplicate_key
{
    public function get_namespace_label() { return null; }
}
class LegacyErrorMessagesTest extends TestCase
{
    /** @dataProvider messages */
    public function testErrorsRetainOperationContext($class, $arguments, $expected)
    {
        $error = new $class(...$arguments);
        $this->assertInstanceOf(\Exception::class, $error);
        $this->assertSame($expected, $error->getMessage());
    }
    public function messages()
    {
        $ssh = 'flat\\core\\ssh\\exception\\';
        $rows = [
            [$ssh.'input_error',['invalid'], 'input error: invalid'],
            [$ssh.'syserror',['unavailable'], 'system error: unavailable'],
            [$ssh.'input_error\\missing_param',['host'], "input error: missing parameter 'host'"],
            [$ssh.'sftp\\local',['write failed'], 'local error: write failed'],
            [$ssh.'sftp\\remote',['read failed'], 'remote error: read failed'],
            [$ssh.'exec\\pid\\cannot_kill',[42,10], 'running process (pid 42) could not be killed after 10 seconds'],
            ['flat\\db\\driver\\pdo\\exception\\bad_param',['dsn'], "bad param 'dsn'"],
            ['flat\\db\\driver\\pdo\\exception\\bad_param',['dsn','missing'], "bad param 'dsn': missing"],
            ['flat\\db\\driver\\mongo\\collection\\illegal',['rename'], 'illegal action: rename'],
            ['flat\\core\\mappable\\exception\\invalid_object',[], 'object argument must be valid object'],
            ['flat\\core\\resolver\\exception\\not_ready',[], 'class resolution not ready'],
            ['flat\\core\\shutdown\\exception\\not_callable',[], 'callback arg must be callable'],
            ['flat\\core\\shutdown\\exception\\bad_priority',[], "priority must be NULL, (string) 'first', (string) 'last', or (int) 0 or greater."],
            ['flat\\core\\controller\\asset\\exception\\bad_resource',['LocalAsset'], "asset 'LocalAsset' must be given (string) for resource"],
            ['flat\\core\\controller\\asset\\exception\\missing_system_base',['LocalAsset'], "to get system path, 'LocalAsset' must have \\flat\\asset\\system_base interface"],
            ['flat\\core\\controller\\asset\\exception\\not_resolvable',['LocalAsset','path.png'], "asset 'LocalAsset' could not resolve 'path.png'"],
            ['flat\\core\\util\\image\\exception\\system_err',['native failure'], 'system error: native failure.'],
            ['flat\\core\\controller\\api\\exception\\interface_not_allowed',['GET','LocalApi'], 'GET interface is not allowed for API resource LocalApi'],
            ['flat\\core\\controller\\api\\exception\\method_mismatch',['PATCH'], "requested method 'PATCH' does not match api interface"],
            ['flat\\core\\session\\exception\\invalid_nonce_data',[], 'could not find a valid meta object associated with specified nonce'],
            ['flat\\data\\orm\\driver\\exception\\bad_param',['missing handler'], 'bad param: missing handler'],
            ['flat\\db\\driver\\mongo\\db\\exception\\connect_failure',[], 'connection failure'],
            ['flat\\db\\driver\\mongo\\db\\exception\\connect_failure',['offline'], 'connection failure, details: offline'],
        ];
        foreach ([['auth\\not_ready','ssh2 authorization has not occured'],
            ['auth\\failure\\password','password auth failure'],
            ['auth\\failure\\pubkey','pubkey auth failure'],
            ['connection\\failure','connection failure'],
            ['connection\\missing','not connected'],
            ['exec\\pid\\missing','server did not provide pid as expected'],
            ['exec\\pid\\not_running','failed to find running process']] as $spec) {
            list($name,$base) = $spec;
            $rows[] = [$ssh.$name, [], $base];
            $rows[] = [$ssh.$name, ['local detail'], $base.': local detail'];
        }
        $rows[] = [$ssh.'exec\\pid\\kill',[42], 'kill command failed'];
        $rows[] = [$ssh.'exec\\pid\\kill',[42,'local detail'], 'kill command failed: local detail'];
        foreach ([['flat\\db\\op\\exception\\failure','database operation failure'],
            ['flat\\lib\\exception\\db_error','db error'],
            ['flat\\db\\driver\\mongo\\collection\\not_found','collection not found']] as $spec) {
            $rows[] = [$spec[0], [''], $spec[1]];
            $rows[] = [$spec[0], ['local detail'], $spec[1].': local detail'];
        }
        return $rows;
    }
    public function testDuplicateKeyRetainsNonScalarValueDescription()
    {
        $error = new UnlabeledDuplicateError('collection','index',['nested']);
        $this->assertStringContainsString('(non-scalar value)',$error->getMessage());
    }
    public function testConfigReferenceErrorRetainsAllLookupKeys()
    {
        $error = new \flat\core\config\exception\non_scalar_inline_ref_value('app/name','secrets','token');
        $this->assertSame('app/name', $error->get_key());
        $this->assertSame('secrets', $error->get_basename());
        $this->assertSame('token', $error->get_ns());
    }
    public function testScalarDefaultErrorAndThreadFailureRetainDetails()
    {
        $error = new \flat\data\scalar\exception\bad_default('not scalar');
        $this->assertSame('not scalar', $error->get_reason());
        $this->assertSame('bad default given: not scalar', $error->getMessage());
        $this->assertNull((new \flat\data\scalar\exception\bad_default)->get_reason());
        $this->assertSame('bad default given', (new \flat\data\scalar\exception\bad_default(false))->getMessage());
        $error = new \flat\event\alert\thread_fail('local listener');
        $this->assertSame('local listener', $error->get_context());
        $this->assertSame('could not create thread for local listener', $error->getMessage());
    }
    public function testIgnoredCollectionMemberRemainsAnEmptyString()
    {
        $ignore = new \flat\core\collection\member\ignore;
        $this->assertSame('', (string)$ignore);
        $this->assertSame('', $ignore->missing);
        $this->assertSame('', $ignore->missingMethod('unused'));
    }
    public function testUploadAndBrokerErrorsRetainTheOriginalExceptionContext()
    {
        $cause = new \RuntimeException('offline',17);
        $error = new \flat\cloud\aws\s3\upload_failure($cause);
        $this->assertSame($cause,$error->get_exception());
        $this->assertSame('upload failure: RuntimeException',$error->getMessage());
        $error = new \flat\db\driver\rabbitmq\exception\connect_error($cause);
        $this->assertSame('connection error 17 (RuntimeException): offline',$error->getMessage());
    }
    /** @dataProvider validationMessages */
    public function testValidationFailureDescribesTheSuppliedValueAndRule($params,$message)
    {
        $this->assertSame($message,(new \flat\core\util\validate\exception\fail($params))->getMessage());
    }
    public function validationMessages()
    {
        return [[null,'value did not pass validation'], [['value'=>'invalid','rule'=>'hostname'], "value 'invalid' did not pass validation rule hostname"],
            [['value'=>['nested']],'value did not pass validation'], [['value'=>false],'value did not pass validation']];
    }
    public function testRouteMissingErrorExposesBothCoordinates()
    {
        foreach (['','unknown resource'] as $details) {
            $error = new \flat\core\controller\route\exception\missing('controller',$details);
            $this->assertSame('controller',$error->get_missing());
            $this->assertSame($details,$error->get_details());
            $this->assertSame('missing controller'.($details ? ': '.$details : ''),$error->getMessage());
        }
    }
    public function testCrudErrorAcceptsUnspecifiedOperation()
    {
        $error = new \flat\orm\crud_error(null);
        $this->assertSame('orm error',$error->getMessage());
    }
    public function testDuplicateKeyErrorRetainsIndexAndNonScalarValue()
    {
        $error = new UnlabeledDuplicateError('records',null,['nested']);
        $this->assertNull($error->get_index());
        $this->assertSame(['nested'],$error->get_value());
        $this->assertSame('duplicate key in: records (non-scalar value)',$error->getMessage());
    }
    public function testDuplicateKeyDescribesANonemptyScalarValue()
    {
        $error=new UnlabeledDuplicateError('records','id',42);
        $this->assertSame(42,$error->get_value());
        $this->assertSame('duplicate key in: records, index: id (value: 42)',$error->getMessage());
    }
    public function testDuplicateKeyDescribesIndexesWithoutWarnings()
    {
        $warnings=[]; set_error_handler(function ($level,$message) use (&$warnings) { $warnings[]=$message; return true; });
        try {
            $empty=new UnlabeledDuplicateError('records',[]);
            $nested=new UnlabeledDuplicateError('records',['a','b']);
            $default=new UnlabeledDuplicateError('records');
        } finally { restore_error_handler(); }
        $this->assertSame([],$warnings);
        $this->assertSame([],$empty->get_index()); $this->assertSame('duplicate key in: records',$empty->getMessage());
        $this->assertSame('duplicate key in: records, index: (non-scalar index)',$nested->getMessage());
        // The default empty index adds nothing (it used to leave a trailing ", ").
        $this->assertSame('duplicate key in: records',$default->getMessage());
    }
    public function testBadApiResponseRetainsDocumentedClassAndMethod()
    {
        $error = new \flat\core\controller\api\exception\bad_response('LocalApi','PATCH');
        $this->assertSame('LocalApi',$error->get_api_class());
        $this->assertSame('PATCH',$error->get_method());
    }
    public function testSessionErrorsRetainReasonAndExpirationMetadata()
    {
        foreach ([\flat\core\session\exception\bad_key::class,\flat\core\session\exception\bad_config::class] as $class) {
            $this->assertSame('local reason',(new $class('local reason'))->get_reason());
        }
        $this->assertSame(120,(new \flat\core\session\exception\nonce_expired(120))->get_ttl());
        $this->assertSame('missing',(new \flat\data\exception\not_field('missing'))->get_field_name());
    }
    public function testCurlSystemErrorRetainsDataAndDetails()
    {
        $error = new \flat\core\curl\exception\system_error('local error',['path'=>'fixture']);
        $this->assertSame('local error',$error->get_details());
        $this->assertSame(['path'=>'fixture'],$error->get_data());
    }
    public function testNotFoundStatusReportsCode404()
    {
        $status = new \flat\core\controller\api\not_found;
        $this->assertSame(404,$status->get_status_code());
        $this->assertSame('Not Found',$status->get_status_string());
    }
    public function testXpathErrorRetainsActualLibxmlDetailsWithoutDuplicatingMessage()
    {
        $previous = libxml_use_internal_errors(true); libxml_clear_errors();
        try {
            (new \DOMDocument)->loadXML('<root>'); $native = libxml_get_last_error();
            $this->assertInstanceOf(\LibXMLError::class,$native);
            $error = new \flat\core\curl\exception\xpath_error('//bad','explicit',$native);
            $this->assertSame('//bad',$error->get_query());
            $this->assertSame('explicit',$error->get_details());
            $this->assertSame($native,$error->get_libXMLError());
            $this->assertStringContainsString($native->message,$error->getMessage());
            $error = new \flat\core\curl\exception\xpath_error('//bad',$native->message,$native);
            $this->assertSame(1,substr_count($error->getMessage(),$native->message));
            $this->assertSame('xpath error',(new \flat\core\curl\exception\xpath_error(null))->getMessage());
        } finally { libxml_clear_errors(); libxml_use_internal_errors($previous); }
    }
    public function testScalarTypeErrorUsesItsSuppliedTypeWithoutAnUndefinedLocal()
    {
        foreach (['array','object',12] as $type) {
            $error = new \flat\data\scalar\exception\bad_value($type);
            $this->assertStringContainsString("given type $type instead",$error->getMessage());
        }
    }
    public function testLegacySshParameterErrorRetainsItsDeclaredNameAndMessage()
    {
        $error = new \flat\core\ssh\exception\input_error\badParam('pid','invalid');
        $this->assertSame('input error: bad pid: invalid',$error->getMessage());
    }
}
