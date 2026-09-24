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
    private RedisArray|Redis|RedisCluster|Relay|ClientInterface|null $redisConnection = null;

    public function __construct(private readonly string $redisUrl)
    {
    }

    /**
     * 第一次呼叫時才建立連線，之後重複使用同一個連線。
     */
    public function getConnection(): ClientInterface|Relay|RedisCluster|Redis|RedisArray
    {
        return $this->redisConnection ??= RedisAdapter::createConnection($this->redisUrl);
    }
}
