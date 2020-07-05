<?php

namespace DomainEngine;


/**
 * Interface DomainEngineCacheInterface
 * @package DomainEngine
 */
interface DomainEngineCacheInterface
{

    /**
     * @param $accessToken string The access token to store in cache
     * @param $expiresInSeconds integer Token expires in this many seconds, make sure to set your cached token to expire at or before this
     * @return mixed Returns the cached access token or null if not found
     */
    public function set($accessToken, $expiresInSeconds);

    /**
     * @return mixed Returns string access token if found in cache or null if not found
     */
    public function get();
}