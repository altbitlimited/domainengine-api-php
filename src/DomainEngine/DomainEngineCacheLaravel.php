<?php
/**
 * Created by PhpStorm.
 * User: kylec
 * Date: 05/07/2020
 * Time: 15:33
 */

namespace DomainEngine;

use Illuminate\Support\Facades\Cache;


class DomainEngineCacheLaravel implements DomainEngineCacheInterface
{
    const CACHE_KEY = 'domainengine:access_token';

    public function set($accessToken, $expiresInSeconds)
    {
        Cache::put(self::CACHE_KEY, $accessToken, $expiresInSeconds);
    }

    public function get()
    {
        return Cache::get(self::CACHE_KEY, null);
    }
}