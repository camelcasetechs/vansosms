<?php
/*
 * File: ApiBase.php
 * Project: camelcasetechs/vansosms
 * File Created: Thursday, 19th September 2018 1:21:pm am
 * Author: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Last Modified: Thursday, 19th September 2019 1:15:56 pm
 * Modified By: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Copyright 2019, CamelCase Technologies Ltd
 */

namespace CamelCase\VansoSMS\Classes;

use GuzzleHttp\Client;
use Illuminate\Support\Facades\Log;

/**
 * This is an API class that use GuzzleHttp under the hood.
 *
 * it can be used to create rest and soap (yea I know, it's 2019+ and some people stil lives in stone age) clients.
 * child classes must set the $baseUrl then call Parent constructor.
 * Then call the send method to perform the request.
 *
 * A very important note is that query string are not sent with the url,
 * please call addQueryString() method before calling send.
 */
abstract class ApiBase implements IApiTemplate
{

    protected $client;
    protected $baseUrl;
    protected $endpoint;
    protected $verifyClient = 'unset';
    protected $curlOptions = [];
    protected $timeout = 300;
    protected $data;
    protected $apiType = 'rest';
    protected $headers = [];
    protected $sendMethod = 'json'; //json, form_params, body
    protected $soapRootScaffold = null; // Initial scaffolding
    protected $soapRoot = null;
    protected $soapBody;
    protected $soapBodyAvoidNamespace = true;
    protected $soapNamespace = ''; //Ignore if not using soap
    protected $soapNamespaceStrip; //The namespaces to strip from response
    protected $soapAttributeStrip; //The Attributes to strip from response
    protected $queryString = [];
    protected $setSSLV3 = false;

    public $response;
    public $rawResponse;

    public function __construct()
    {
        if ($this->apiType === 'rest') {
            $this->headers['Content-Type'] = 'application/json';
            $this->headers['Accept'] = 'application/json';
        } else if ($this->apiType === 'soap') {
            $this->headers['Content-Type'] = 'application/soap+xml; charset=utf-8';
            $this->sendMethod = 'body';

            // Self::log(is_null($this->soapRoot));

            if (is_null($this->soapRootScaffold)) {
                $this->soapRootScaffold = '<?xml version="1.0" encoding="utf-8"?>
                    <soap12:Envelope xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:soap12="http://www.w3.org/2003/05/soap-envelope"></soap12:Envelope>';
            }

            $this->resetSoapBody();

            $this->soapAttributeStrip = [
                'xmlns:soap="http://www.w3.org/2003/05/soap-envelope"',
                'xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance"',
                'xmlns:xsd="http://www.w3.org/2001/XMLSchema"',
            ];
            $this->soapNamespaceStrip = [
                'soap', 'soap12',
            ];
        }

        $this->createClient();

        return $this;
    }

    /**
     * Create Guzzle client
     *
     * @param null
     *
     * @return Self
     */

    protected function createClient(): Self
    {
        if ($this->client) {
            unset($this->client);
        }

        $options = [
            'base_uri' => $this->baseUrl,
            'timeout' => $this->timeout,
            'headers' => $this->headers,
        ];

        if ($this->verifyClient !== 'unset') {
            $options['verify'] = $this->verifyClient;
        }

        if ($this->setSSLV3) {
            $options['curl'][CURLOPT_SSLVERSION] = CURL_SSLVERSION_SSLv3;
        }

        foreach ($this->curlOptions as $key => $value) {
            $options['curl'][$key] = $value;
        }
        $this->client = new Client($options);

        return $this;
    }

    /**
     * set header for request
     *
     * @param string $header     The header key
     * @param string $value      The header value
     */

    protected function setHeader(string $key, string $value): Self
    {
        $this->removeHeader($key);
        $this->headers[$key] = $value;
        return $this->createClient();
    }

    /**
     * remove header for request
     *
     * @param string $header     The header key
     * @param string $value      The header value
     */

    protected function removeHeader(string $key): Self
    {
        if (isset($this->headers[$key])) {
            unset($this->headers[$key]);
        }
        return $this->createClient();
    }

    /**
     * Reset the soap body to be able to call another soap requst
     * in a single request
     *
     * @param null
     *
     * @return void
     */
    protected function resetSoapBody()
    {
        $this->soapRoot = new \SimpleXMLElement($this->soapRootScaffold);
        $this->soapBody = $this->soapRoot->addChild('Body');
    }

    /**
     * Add Query Strings
     *
     * @param string $key
     * @param string $value
     *
     * @return void
     */
    protected function addQueryString(string $key, string $value): void
    {
        $this->queryString[$key] = $value;
    }

    /**
     * Set the query string, deleting the one there before
     *
     * @param array $qs
     *
     * @return void
     */
    protected function setQueryString(array $qs): void
    {
        $this->queryString = $qs;
    }

    /**
     * Reset the response variables
     *
     * @return Static
     */

    protected function resetResponse(): Self
    {
        $this->response = null;
        $this->rawResponse = null;

        return $this;
    }

    /**
     * Log message with the Log class
     */
    protected static function log($message)
    {
        Log::debug($message);
    }

    /**
     * The method that actually sends the message
     * Dispatches the message response to process function
     *
     * @return $this->data
     */
    protected function go()
    {

        if (!$this->endpoint) {
            abort(401, "Endpoint is required");
        }

        if ($this->apiType == 'soap') {
            $body = $this->soapBody;
            array_walk($this->data, function ($value, $key) use ($body) {
                if ($this->soapBodyAvoidNamespace) {
                    return $body->addChild($key, $value, $this->soapNamespace);
                } else {
                    return $body->addChild($key, $value, $this->soapNamespace);
                }

            });

            $this->soapBody = $body;

            // self::log($this->soapRoot->asXML());
        }

        try {
            if (is_null($this->data)) {
                $result = $this->client->get($this->baseUrl . $this->endpoint);
            } else {
                if ($this->apiType == 'rest') {
                    $result = $this->client->request('POST', $this->baseUrl . $this->endpoint, [
                        $this->sendMethod => $this->data,
                        'query' => $this->queryString,
                    ]);
                }
                if ($this->apiType == 'soap') {
                    $result = $this->client->request('POST', $this->baseUrl . $this->endpoint, [
                        $this->sendMethod => $this->soapRoot->asXML(),
                        'query' => $this->queryString,
                    ]);
                }

            }

            $this->response = $this->rawResponse = $result->getBody(true);
        } catch (\GuzzleHttp\Exception\BadResponseException $e) {
            // Log::debug($e);
            if ($e->hasResponse()) {
                $this->response = $this->rawResponse = $e->getResponse()->getBody(true);
            }
        }

        return $this->process();
    }

    /**
     * Overridable process function that process the data before sending the response;
     * In the case of soap, the data can be converted to Array or StdClass;
     */
    protected function process()
    {
        if ($this->apiType == 'soap') {

            $cleanXML = $this->removeNamespaceFromXML($this->response);

            $this->response = json_decode(json_encode(simplexml_load_string($cleanXML)));
        }

        return $this->response;
    }

    /**
     * Clean the xml and make remove the namespace bloatware
     * https://laracasts.com/discuss/channels/general-discussion/converting-xml-to-jsonarray
     */
    public function removeNamespaceFromXML($xml)
    {
        $toRemove = $this->soapNamespaceStrip;
        $nameSpaceDefRegEx = '(\S+)=["\']?((?:.(?!["\']?\s+(?:\S+)=|[>"\']))+.)["\']?';
        foreach ($toRemove as $remove) {
            $xml = str_replace('<' . $remove . ':', '<', $xml);
            $xml = str_replace('</' . $remove . ':', '</', $xml);
            $xml = str_replace($remove . ':commentText', 'commentText', $xml);
        }

        foreach ($this->soapAttributeStrip as $rm) {
            $xml = str_replace($rm, '', $xml);
        }

        // Return sanitized and cleaned up XML with no namespaces
        return $xml;
    }

    /**
     * Overridable method that send the request to pass through the go function
     * !!! Dont forget to call and return the go method
     */
    public function send($endpoint, $data = null)
    {
        $this->endpoint = $endpoint;
        $this->data = $data;
        return $this->go();
    }

}
