<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Backendservice;

/**
 * make this make actual sense, add auth service, make it look less like shit
 */
class backendServiceClient extends \Grpc\BaseStub
{

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null)
    {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function getTest($metadata = [], $options = [])
    {
        return $this->_bidiRequest('/backendservice.backendService/getTest',
            ['\Backendservice\SpeechResponse', 'decode'],
            $metadata, $options);
    }

}
