<?php

namespace App\Tests\Service;

use App\Service\RedisService;
use PHPUnit\Framework\TestCase;

class RedisServiceTest extends TestCase
{
    public function testConstructorDoesNotConnect()
    {
        // 指向不存在的 Redis，建構時不應嘗試連線
        $service = new RedisService('redis://127.0.0.1:1');

        $this->assertInstanceOf(RedisService::class, $service);
    }

    public function testGetConnectionReusesConnection()
    {
        // lazy=1 讓連線物件建立時不立即連線，方便在沒有 Redis 的環境驗證重複使用
        $service = new RedisService('redis://127.0.0.1:1?lazy=1');

        $this->assertSame($service->getConnection(), $service->getConnection());
    }
}
