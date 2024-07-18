<?php

namespace App\Service;

use Predis\ClientInterface;
use Redis;
use RedisArray;
use RedisCluster;
use Relay\Relay;
use Symfony\Component\Cache\Adapter\RedisAdapter;
class RedisService
{
    private RedisArray|Redis|RedisCluster|Relay|ClientInterface $redisConnection;

    public function __construct(string $redisUrl)
    {
        $this->redisConnection = RedisAdapter::createConnection($redisUrl);
    }

    public function getConnection(): ClientInterface|Relay|RedisCluster|Redis|RedisArray
    {
        return $this->redisConnection;
    }
}
