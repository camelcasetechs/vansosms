<?php
/*
 * File: VansoSMS.php
 * Project: camelcasetechs/vansosms
 * File Created: Thursday, 19th September 2019 11:04:24 am
 * Author: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Last Modified: Wednesday, 18th December 2019 1:51:22 pm
 * Modified By: Temitayo Bodunrin (temitayo@brandnaware.com)
 * -----
 * Copyright 2019, CamelCase Technologies Ltd
 */

namespace CamelCase\VansoSMS;

use CamelCase\VansoSMS\Classes\ApiBase;

class VansoSMSClient extends ApiBase
{

    private $username;
    private $password;
    private $encoding;
    private $source_type = "alphanumeric";
    private $dlr;
    private $from;
    private $phone;
    private $message;

    public function __construct()
    {

        $this->configure();

        Parent::__construct();

        $this->removeHeader('Accept', 'text/xml')
            ->setHeader('Content-Type', 'text/xml')
            ->setHeader('charset', $this->encoding);

        return $this;
    }

    /**
     * Use this method to pass the credentials and the from field.
     * The config/config.php explains what each does.
     *
     * You will have to adapt this method to your need by replacing the config function
     * and update with your own config code.
     */
    private function configure(): Self
    {

        // All these are consumer specific
        $this->username = config('services.vanso.username');
        $this->password = config('services.vanso.password');
        $this->from = config('services.vanso.from');

        // All these are valid from the config file
        // But you have to pass it in your own way.
        // You can even hardcode them from the values in the config file
        $this->baseUrl = config('services.vanso.endpoint');
        $this->dlr = config('services.vanso.dlr');
        $this->encoding = config('services.vanso.encoding');

        // Dont touch this except you know what you are doing
        $this->verifyClient = false;
        $this->sendMethod = 'body';

        return $this;

    }

    /** * Converts string to hex-string
     * @param string $string
     * @return string
     */
    private function strToHex(string $string): string
    {
        $hex = '';
        for ($i = 0; $i < strlen($string); $i++) {
            $ord = ord($string[$i]);
            $hexCode = dechex($ord);
            $hex .= substr('0' . $hexCode, -2);
        }
        return strToUpper($hex);
    }

    /** Build the xml string
     * @return Self
     */
    public function createSubmitRequestXML(): Self
    {
        $xmldoc = new \DOMDocument('1.0');
        $xmldoc->formatOutput = true;
        $root = $xmldoc->createElement('operation');
        $root = $xmldoc->appendChild($root);
        $root->setAttribute('type', 'submit');
        $account = $xmldoc->createElement('account');
        $account = $root->appendChild($account);
        $account->setAttribute('username', $this->username);
        $account->setAttribute('password', $this->password);
        $submitRequest = $xmldoc->createElement('submitRequest');
        $submitRequest = $root->appendChild($submitRequest);
        $deliveryReport = $xmldoc->createElement('deliveryReport', $this->dlr);
        $deliveryReport = $submitRequest->appendChild($deliveryReport);
        $sourceAddress = $xmldoc->createElement('sourceAddress', $this->from);
        $sourceAddress = $submitRequest->appendChild($sourceAddress);
        $sourceAddress->setAttribute('type', $this->source_type);
        $destinationAddress = $xmldoc->createElement('destinationAddress', $this->phone);
        $destinationAddress = $submitRequest->appendChild($destinationAddress); // destination address type international is mandatory
        $destinationAddress->setAttribute('type', 'international');
        // $msg = $xmldoc->createElement('text', $this->strToHex($message));
        $msg = $xmldoc->createElement('text', bin2hex($this->message));
        $msg = $submitRequest->appendChild($msg);
        $msg->setAttribute('encoding', $this->encoding);

        $this->data = $xmldoc->saveXML();

        return $this;
    }

    /**
     * @override the send method which sends the SMS;
     *
     * @param string $endpoint      The Vanso endpoint
     * @param array $data           The data to send along
     */
    public function send($endpoint, $data = null)
    {
        if (!is_array($data) || !$data['phone'] || !$data['message']) {
            throw new \Exception('Please supply message and or phone number');
        }
        extract($data);
        $this->phone = '+234' . $phone;
        $this->message = $message;
        $this->endpoint = $endpoint;
        return $this->createSubmitRequestXML()->go();
    }

    /**
     * Overridable process function that process the data
     * before sending the response;
     */
    protected function process()
    {
        try {
            $this->response = new \SimpleXMLElement($this->response);
        } catch (\Throwable $th) {
            //throw $th;
        }

        return $this->response;
    }

    /**
     * A facade to send the SMS
     * This could returns a \SimpleXMLElement object
     * so a json_encode cast might be required in some cases
     * Provided you get an object response with ticketId,
     * you have done your own part
     *
     * @param string $phone        The phone number to send sms to
     * @param string $message      The message to send
     */
    public static function sendSMS(string $phone, string $message): Object
    {
        return (new Self)->send('/api/sxmp/1.0',
            [
                'phone' => $phone,
                'message' => $message,
            ]
        );
    }

}
