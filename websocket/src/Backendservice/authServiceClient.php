<?php
// GENERATED CODE -- DO NOT EDIT!

namespace Backendservice;

/**
 */
class authServiceClient extends \Grpc\BaseStub {

    /**
     * @param string $hostname hostname
     * @param array $opts channel options
     * @param \Grpc\Channel $channel (optional) re-use channel object
     */
    public function __construct($hostname, $opts, $channel = null) {
        parent::__construct($hostname, $opts, $channel);
    }

    /**
     * @param \Backendservice\AuthRequestMessage $argument input argument
     * @param array $metadata metadata
     * @param array $options call options
     */
    public function auth(\Backendservice\AuthRequestMessage $argument,
      $metadata = [], $options = []) {
        return $this->_simpleRequest('/backendservice.authService/auth',
        $argument,
        ['\Backendservice\AuthResponseMessage', 'decode'],
        $metadata, $options);
    }

}
