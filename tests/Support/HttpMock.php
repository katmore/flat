<?php
namespace flat\test\Support;

/**
 * A plain HTTP server for the cURL tests: the WireMock server of the test stack (FLAT_TEST_HTTP_URL), driven through its admin API
 * with PHP streams (not cURL, which is what the tests exercise). Each instance owns the stubs it adds, all under one random
 * path prefix, and removes them in close(); the Authorize.Net stubs of the same server are never touched.
 */
class HttpMock
{
    private $base;
    private $prefix;
    private $ids = [];

    public function __construct()
    {
        $this->base = rtrim((string) getenv('FLAT_TEST_HTTP_URL'), '/');
        if ($this->base === '') {
            throw new \RuntimeException('FLAT_TEST_HTTP_URL is not set: run these tests with docker/bin/test (all, services or anet)');
        }
        $this->prefix = '/flat-curl-'.bin2hex(random_bytes(6));
    }

    /** The URL of a path under this instance's own prefix. */
    public function url($path = '/')
    {
        return $this->base.$this->prefix.$path;
    }

    /** A path under this instance's prefix, as the server sees it (without scheme and host). */
    public function path($path)
    {
        return $this->prefix.$path;
    }

    /** The URL of a path at the root of the server (robots.txt is always found there). */
    public function rootUrl($path)
    {
        return $this->base.$path;
    }

    /**
     * Answers $method requests for a path under the prefix.
     * @param array $response WireMock's response: status (default 200), body, headers (name => value) or fault
     */
    public function stub($path, array $response = [], $method = 'GET')
    {
        $this->add($this->prefix.$path, $response, $method);
    }

    /**
     * Like stub(), for a path at the root of the server. The path is shared by every test, so the requests the server remembers for it
     * are forgotten first: rootRequests() then shows only this test's.
     */
    public function stubRoot($path, array $response = [], $method = 'GET')
    {
        $this->admin('POST', '/__admin/requests/remove', ['urlPath' => $path]);
        $this->add($path, $response, $method);
    }

    /** The requests the server received for a path under the prefix: [['method', 'url' (path and query), 'headers' (lower-case names), 'body']]. */
    public function requests($path)
    {
        return $this->found($this->prefix.$path);
    }

    /** The requests received for a path at the root of the server. */
    public function rootRequests($path)
    {
        return $this->found($path);
    }

    /** Removes the stubs this instance added. */
    public function close()
    {
        foreach ($this->ids as $id) $this->admin('DELETE', '/__admin/mappings/'.$id);
        $this->ids = [];
    }

    private function add($urlPath, array $response, $method)
    {
        $created = $this->admin('POST', '/__admin/mappings', ['request' => ['method' => $method, 'urlPath' => $urlPath], 'response' => $response + ['status' => 200]]);
        $this->ids[] = $created['id'];
    }

    private function found($urlPath)
    {
        $result = $this->admin('POST', '/__admin/requests/find', ['urlPath' => $urlPath]);
        $requests = [];
        foreach ($result['requests'] as $entry) {
            $request = $entry['request'] ?? $entry;
            $headers = [];
            foreach ($request['headers'] ?? [] as $name => $value) $headers[strtolower($name)] = $value;
            $requests[] = ['method' => $request['method'], 'url' => $request['url'], 'headers' => $headers, 'body' => $request['body'] ?? ''];
        }
        return $requests;
    }

    private function admin($method, $path, array $body = null)
    {
        $context = stream_context_create(['http' => [
            'method' => $method, 'header' => "Content-Type: application/json\r\n", 'content' => $body === null ? '' : json_encode($body),
            'ignore_errors' => true, 'timeout' => 10,
        ]]);
        $answer = file_get_contents($this->base.$path, false, $context);
        $status = isset($http_response_header[0]) ? (int) explode(' ', $http_response_header[0])[1] : 0;
        if ($answer === false || $status < 200 || $status >= 300) {
            throw new \RuntimeException("WireMock $method $path answered $status: ".var_export($answer, true));
        }
        return $answer === '' ? null : json_decode($answer, true, 512, JSON_THROW_ON_ERROR);
    }
}
