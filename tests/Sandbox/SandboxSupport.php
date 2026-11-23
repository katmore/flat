<?php
namespace flat\test\Sandbox;

/**
 * Credentials for the Authorize.Net sandbox tests come from the environment. A missing one FAILS the test
 * (it never skips): run `docker/bin/test sandbox` with a sandbox account's keys, or do not run this suite.
 */
trait SandboxSupport
{
    protected function sandboxLoginId()
    {
        return $this->sandboxEnv('FLAT_TEST_ANET_LOGIN_ID');
    }
    protected function sandboxTransactionKey()
    {
        return $this->sandboxEnv('FLAT_TEST_ANET_TRANSACTION_KEY');
    }
    private function sandboxEnv($name)
    {
        $value = getenv($name);
        if ($value === false || $value === '') {
            $this->fail("Set $name to an Authorize.Net SANDBOX account's value (docker/bin/test sandbox), or run them against the mock (docker/bin/test anet). These tests do not skip.");
        }
        return $value;
    }
    /**
     * A value that is unique per run, so tests never collide and leftovers are recognisable. The mock
     * (docker/bin/test anet) fixes it, because its answers are fixed.
     */
    protected function sandboxRunId()
    {
        $fixed = getenv('FLAT_TEST_ANET_RUN_ID');
        return ($fixed !== false && $fixed !== '') ? $fixed : 'flat-' . bin2hex(random_bytes(5));
    }
}

/** Where the tests send their requests: the real sandbox, or a stand-in named by FLAT_TEST_ANET_URL (no path). */
function sandboxBaseUrl()
{
    $url = getenv('FLAT_TEST_ANET_URL');
    if ($url !== false && $url !== '') return rtrim($url, '/');
    // The real sandbox is contacted only when asked for: `docker/bin/test sandbox` (or `bin/test sandbox`) sets this.
    if (getenv('FLAT_TEST_ANET_SANDBOX') !== '1') {
        throw new \RuntimeException('These tests contact the real Authorize.Net sandbox only when you run `docker/bin/test sandbox` explicitly; otherwise they need FLAT_TEST_ANET_URL (docker/bin/test anet).');
    }
    return \net\authorize\api\constants\ANetEnvironment::SANDBOX;
}

/**
 * The SDK's HTTP client trusts only its own bundled CA file, whatever the environment says. Where an extra CA
 * is needed (a proxy that re-signs TLS), FLAT_TEST_CA_BUNDLE names it and this client trusts that file instead.
 * Verification stays on. Without that variable the SDK's own client is used untouched.
 */
class SandboxHttpClient extends \net\authorize\util\HttpClient
{
    private $ca;
    public function __construct($ca) { parent::__construct(); $this->ca = $ca; }
    public function _sendRequest($xmlRequest)
    {
        $curl = curl_init($this->_getPostUrl());
        curl_setopt_array($curl, [CURLOPT_POSTFIELDS=>$xmlRequest, CURLOPT_HEADER=>0, CURLOPT_TIMEOUT=>45, CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_CAINFO=>$this->ca, CURLOPT_HTTPHEADER=>['Content-Type: text/xml']]);
        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl); curl_close($curl);
            throw new \RuntimeException('Authorize.Net sandbox request failed: ' . $error);
        }
        curl_close($curl);
        return $response;
    }
}

/** A handler on the current API (flat\cloud\authorize), pointed at the sandbox. */
class SandboxApi extends \flat\cloud\authorize
{
    private $login;
    private $key;
    public function __construct($login, $key) { $this->login = $login; $this->key = $key; }
    protected function _get_login_id() { return $this->login; }
    protected function _get_transaction_key() { return $this->key; }
    protected function _get_end_point() { return sandboxBaseUrl(); }

    private function run($controller)
    {
        // The SDK's client is used untouched unless a proxy CA is named; it needs none for the mock (plain HTTP).
        if (($ca = getenv('FLAT_TEST_CA_BUNDLE')) !== false && $ca !== '') $controller->httpClient = new SandboxHttpClient($ca);
        return $this->_executeWithApiResponse($controller);
    }
    public function authenticate()
    {
        $request = new \net\authorize\api\contract\v1\AuthenticateTestRequest();
        $request->setMerchantAuthentication($this->_load_MerchantAuthentication());
        return $this->run(new \net\authorize\api\controller\AuthenticateTestController($request));
    }
    public function deleteCustomerProfile($customerProfileId)
    {
        $request = new \net\authorize\api\contract\v1\DeleteCustomerProfileRequest();
        $request->setMerchantAuthentication($this->_load_MerchantAuthentication());
        $request->setCustomerProfileId($customerProfileId);
        return $this->run(new \net\authorize\api\controller\DeleteCustomerProfileController($request));
    }
}

/**
 * flat's legacy CIM client with the same CA arrangement: the old SDK also trusts only its bundled CA file.
 * It also throws when the request itself fails: the SDK would return an empty response that looks like no error.
 */
class SandboxCim extends \flat\cloud\authorize\AuthorizeNetCIM
{
    private $ca;
    public function __construct($login, $key, $ca) { $this->ca = $ca; parent::__construct($login, $key); }
    protected function _getPostUrl() { return sandboxBaseUrl() . '/xml/v1/request.api'; }
    protected function _sendRequest()
    {
        $this->_setPostString();
        $curl = curl_init($this->_getPostUrl());
        curl_setopt_array($curl, [CURLOPT_POSTFIELDS=>$this->_post_string, CURLOPT_HEADER=>0, CURLOPT_TIMEOUT=>45, CURLOPT_RETURNTRANSFER=>1,
            CURLOPT_SSL_VERIFYPEER=>true, CURLOPT_SSL_VERIFYHOST=>2, CURLOPT_HTTPHEADER=>['Content-Type: text/xml']]);
        if ($this->ca) curl_setopt($curl, CURLOPT_CAINFO, $this->ca);
        $response = curl_exec($curl);
        if ($response === false) {
            $error = curl_error($curl); curl_close($curl);
            throw new \RuntimeException('Authorize.Net sandbox request failed: ' . $error);
        }
        curl_close($curl);
        return $this->_handleResponse($response);
    }
}

/** flat's legacy customer wrapper, pointed at the sandbox. */
class SandboxCustomer extends \flat\cloud\authorize\customer
{
    private $login;
    private $key;
    public function __construct($login, $key) { $this->login = $login; $this->key = $key; }
    private $cim;
    protected function _prepare_AuthorizeNetRequest($client) { return $client; }
    protected function _get_login_id() { return $this->login; }
    protected function _get_transaction_key() { return $this->key; }
    protected function _get_end_point() { return sandboxBaseUrl(); }
    protected function _get_CIM()
    {
        if (!$this->cim) {
            $ca = getenv('FLAT_TEST_CA_BUNDLE');
            $ca = ($ca !== false && $ca !== '') ? $ca : null;
            $mock = getenv('FLAT_TEST_ANET_URL');
            // The old SDK's own client is used untouched, unless a proxy CA or a stand-in server needs the subclass.
            $cim = ($ca || ($mock !== false && $mock !== '')) ? new SandboxCim($this->login, $this->key, $ca) : new \flat\cloud\authorize\AuthorizeNetCIM($this->login, $this->key);
            $this->cim = $this->_prepare_AuthorizeNetRequest($cim);
        }
        return $this->cim;
    }
    public function client() { return $this->_get_CIM(); }
}
