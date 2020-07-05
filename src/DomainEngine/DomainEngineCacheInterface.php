<?php
/**
 * Created by PhpStorm.
 * User: kylec
 * Date: 05/07/2020
 * Time: 00:38
 */

namespace DomainEngine;


interface DomainEngineCacheInterface
{
    public function set($accessToken, $expiresInSeconds);

    public function get();
}