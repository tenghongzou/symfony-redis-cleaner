<?php

namespace App\Tests\Command;

use App\Command\ClearRedisCacheCommand;
use App\Service\RedisService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Application;
use Symfony\Component\Console\Tester\CommandTester;

class ClearRedisCacheCommandTest extends TestCase
{
    public function testExecuteSuccess()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);
        $redisConnectionMock = $this->createMock(\Redis::class);

        // 配置 RedisService 行為
        $redisServiceMock->method('getConnection')->willReturn($redisConnectionMock);

        // 配置 Redis 連接行為
        $redisConnectionMock->method('flushdb')->willReturn(true);

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('Redis 快取已成功清理。', $output);
    }

    public function testExecuteFailure()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);
        $redisConnectionMock = $this->createMock(\Redis::class);

        // 配置 RedisService 行為
        $redisServiceMock->method('getConnection')->willReturn($redisConnectionMock);

        // 配置 Redis 連接行為
        $redisConnectionMock->method('flushdb')->will($this->throwException(new \Exception('Redis error')));

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $commandTester->execute([]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertStringContainsString('清理 Redis 快取時發生錯誤：Redis error', $output);
    }
}
