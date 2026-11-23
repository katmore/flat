<?php
namespace flat\test\Sandbox;

/*
 * Loaded by SandboxSupport.php only when a test first needs it, after the test has loaded the legacy SDK class
 * this one extends through LegacyAuthorizeSdk::loadLegacyAuthorizeClass() (the approved exception). Declared at
 * the top of SandboxSupport.php instead, the SDK's deprecation notice would be raised while PHPUnit loads the
 * test files, outside any handler.
 */

/** flat's legacy ARB (subscription) client with the same CA and server arrangement as SandboxCim. */
class SandboxArb extends \flat\cloud\authorize\AuthorizeNetARB
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
        curl_close($curl);
        try { return $this->_handleResponse($response); }
        finally { $this->setRefId(null); } // as flat's own client does (KD-101)
    }
}
