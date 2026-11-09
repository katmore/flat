<?php
namespace flat\test\Unit\Cloud;
use PHPUnit\Framework\TestCase;
use flat\cloud\authorize\customer\transaction;
class TransactionClassProbe extends transaction\authcapture
{
    public static function legacyClassName($type) { return parent::_typed_classname($type); }
}
class ControlledTransactionAdapter extends transaction\AuthorizeNetTransaction
{
    public static $validation;
    protected function _validate_transaction_type(transaction $transaction) { return self::$validation; }
}
class AuthorizeTransactionTest extends TestCase
{
    /** @dataProvider kinds */
    public function testTransactionKindsAreLoadableTypedData($kind)
    {
        $class = transaction::class.'\\'.$kind; $data = new $class;
        $this->assertInstanceOf(\flat\data\typed\loader::class,$data);
        $this->assertSame($kind,$class::get_data_type());
        $this->assertSame($kind,$data->get_customer_transaction_type());
        $this->assertSame($kind,$class::typed_name([]));
        $this->assertSame('\\'.$class,transaction::typed_class($kind));
    }
    public function kinds() { return [['authonly'],['authcapture'],['captureonly']]; }
    public function testLegacyClassNameHookStillUsesItsOriginalCustomerPrefix()
    {
        $this->assertSame('\\flat\\cloud\\authorize\\customer\\authcapture',TransactionClassProbe::legacyClassName('authcapture'));
    }
    /** @dataProvider kinds */
    public function testSdkAdaptersAcceptOnlyTheirMatchingType($kind)
    {
        $class = transaction::class.'\\'.$kind; $adapter = $class.'\\AuthorizeNetTransaction';
        $this->assertInstanceOf(\AuthorizeNetTransaction::class,new $adapter(new $class));
        $other = $kind === 'authonly' ? transaction\authcapture::class : transaction\authonly::class;
        try { new $adapter(new $other); }
        catch (\flat\cloud\authorize\bad_param $error) {
            $this->assertSame('transaction',$error->get_param_key());
            $this->assertSame('must be '.$kind,$error->get_reason());
            return;
        }
        $this->fail('Expected mismatched transaction rejection');
    }
    /** @group known-defect */
    public function testAdapterRetainsMappedInitializedFieldsAndCurrentNullFieldLimitation()
    {
        $tax = (object)['amount'=>'1.00','name'=>'tax','description'=>'local'];
        $data = new transaction\authcapture(['amount'=>'12.34','tax'=>$tax]);
        $adapter = new transaction\authcapture\AuthorizeNetTransaction($data);
        $this->assertSame($tax,$adapter->tax);
        // Current isset guard drops SDK fields initialized to null.
        $this->assertNull($adapter->amount);
    }
    /** @group known-defect */
    public function testExplicitFalseValidationIsRejectedAndOtherReturnTypesCurrentlyPass()
    {
        try {
            ControlledTransactionAdapter::$validation = false;
            try { new ControlledTransactionAdapter(new transaction\authcapture); $this->fail('Expected explicit false rejection'); }
            catch (\flat\cloud\authorize\bad_param $error) { $this->assertSame('invalid transaction type',$error->get_reason()); }
            ControlledTransactionAdapter::$validation = null;
            $this->assertInstanceOf(\AuthorizeNetTransaction::class,new ControlledTransactionAdapter(new transaction\authcapture));
        } finally { ControlledTransactionAdapter::$validation = null; }
    }
    public function testLegacyPaymentTypeNamesAutoloadWithoutRenamingTheirImplementations()
    {
        $card = new \flat\cloud\authorize\customer\payment_profile\creditCard(['cardNumber'=>'local-card']);
        $bank = new \flat\cloud\authorize\customer\payment_profile\bankAccount(['accountNumber'=>'local-account']);
        $this->assertSame('creditCard',$card::get_data_type()); $this->assertSame('local-card',$card->cardNumber);
        $this->assertSame('bankAccount',$bank::get_data_type()); $this->assertSame('local-account',$bank->accountNumber);
    }
}
