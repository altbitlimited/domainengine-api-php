<?php

namespace DomainEngine;

use \GuzzleHttp;

/**
 * Class DomainEngine
 * @package DomainEngine
 */
class DomainEngine
{
    /** @var string BASE_URL Base API URL */
    const BASE_URL = 'https://api.domainengine.app';

    /** @var GuzzleHttp\Client $_client GuzzleHTTP client */
    private $_client;

    private $_clientId;

    private $_clientSecret;

    private $_cache;

    private $_cacheEnabled;

    /** @var array $_accessToken access token */
    private $_accessToken;

    /**
     * DomainEngine constructor.
     */
    public function __construct(String $clientId, String $clientSecret, DomainEngineCacheInterface $cache = null)
    {
        $this->_clientId = $clientId;
        $this->_clientSecret = $clientSecret;
        $this->_cache = $cache;

        $this->_cacheEnabled = !is_null($this->_cache);

        $this->_client = new GuzzleHttp\Client([
            'base_uri' => self::BASE_URL,
            'verify' => false,
        ]);
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->_accessToken;
    }

    /**
     * @param string $accessToken
     */
    public function setAccessToken(String $accessToken)
    {
        $this->_accessToken = $accessToken;
    }

    /**
     * @param $clientId
     * @param $clientSecret
     * @return mixed
     */
    public function getAccessTokenFromServer(){
        $accessToken = $this->_postRequest('/oauth/token', [
            'clientId' => $this->_clientId,
            'clientSecret' => $this->_clientSecret
        ], false);

        if(isset($accessToken['error'])){
            throw new \Exception(isset($accessToken['message']) ? $accessToken['message'] : $accessToken['error']);
        }

        if($this->_cacheEnabled){
            $this->_cache->set($accessToken['access_token'], $accessToken['expires_in']);
        }

        return $accessToken['access_token'];
    }

    public function getAndSetAccessToken(){
        //if cache is enabled then get from cache
        $cachedAccessToken = $this->_cacheEnabled ? $this->_cache->get() : null;

        if(!$cachedAccessToken){
            //cache is disabled or cache didn't return token so get new one
            $accessToken = $this->getAccessTokenFromServer();
        }else{
            //else use the cached token
            $accessToken = $cachedAccessToken;
        }

        //set to property so we can use in API call
        $this->setAccessToken($accessToken);
    }

    /**
     * @param $name string Method to call
     * @param $arguments mixed The domain(s) to check; string for single domain, array for batches
     * @return mixed
     * @throws \Exception
     */
    public function __call($name, $arguments)
    {
        if(!$this->getAccessToken()){
            //if no access token set then get from cache or server
            $this->getAndSetAccessToken();
        }

        switch ($name){
            case 'availability':
                switch(gettype($arguments[0])){
                    case 'string':
                        return $this->_getRequest('/availability/' . $arguments[0]);
                        break;
                    case 'array':
                        return $this->_postRequest('/availability/', ['domains' => $arguments[0]]);
                        break;
                    default:
                        throw new \Exception('Invalid argument, must be String or Array.');
                        break;
                }
                break;
            case 'whois':
                switch(gettype($arguments[0])){
                    case 'string':
                        return $this->_getRequest('/whois/' . $arguments[0]);
                        break;
                    case 'array':
                        return $this->_postRequest('/whois/', ['domains' => $arguments[0]]);
                        break;
                    default:
                        throw new \Exception('Invalid argument, must be String or Array.');
                        break;
                }
                break;
            default:
                throw new \Exception('Unknown method.');
                break;
        }
    }



        /**
     * @param $path
     * @param $data
     * @param bool $addHeaders
     * @return mixed
     * @throws \Exception
     */
    private function _postRequest($path, $data, $addHeaders = true){
        $response = $this->_client->request('POST', $path, [
            'headers' => $addHeaders ? $this->getAuthHeader() : [],
            'json' => $data,
        ]);

        try {
            $json = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception('Error decoding response.');
        }

        return $json;
    }

    /**
     * @param $path
     * @param bool $addHeaders
     * @return mixed
     * @throws \Exception
     */
    private function _getRequest($path, $addHeaders = true){
        $response = $this->_client->request('GET', $path, [
            'headers' => $addHeaders ? $this->getAuthHeader() : []
        ]);

        try {
            $json = json_decode($response->getBody(), true);
        } catch (\Exception $e) {
            throw new \Exception('Error decoding response.');
        }

        return $json;
    }

    /**
     * @return array Get Authorization header used by Guzzle for requests
     */
    private function getAuthHeader(){
        return ['Authorization' => 'Bearer ' . $this->getAccessToken()];
    }
}