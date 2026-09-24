<?php

namespace App\Tests\Command;

use App\Command\DeleteRedisKeyCommand;
use App\Service\RedisService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;

class DeleteRedisKeyCommandTest extends TestCase
{
    public function testExecuteWithExistingKey()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);
        $redisConnectionMock = $this->createMock(\Redis::class);

        // 配置 RedisService 行為
        $redisServiceMock->method('getConnection')->willReturn($redisConnectionMock);

        // 配置 Redis 連接行為
        $redisConnectionMock->expects($this->once())->method('del')->with(['existing_key'])->willReturn(1);

        // 創建並測試命令
        $command = new DeleteRedisKeyCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['key' => 'existing_key']);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString("Redis 鍵值 'existing_key' 已成功刪除。", $output);
    }

    public function testExecuteWithNonExistingKey()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);
        $redisConnectionMock = $this->createMock(\Redis::class);

        // 配置 RedisService 行為
        $redisServiceMock->method('getConnection')->willReturn($redisConnectionMock);

        // 配置 Redis 連接行為
        $redisConnectionMock->expects($this->once())->method('del')->with(['non_existing_key'])->willReturn(0);

        // 創建並測試命令
        $command = new DeleteRedisKeyCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['key' => 'non_existing_key']);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString("Redis 鍵值 'non_existing_key' 不存在。", $output);
    }

    public function testExecuteFailure()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);
        $redisConnectionMock = $this->createMock(\Redis::class);

        // 配置 RedisService 行為
        $redisServiceMock->method('getConnection')->willReturn($redisConnectionMock);

        // 配置 Redis 連接行為
        $redisConnectionMock->method('del')->willThrowException(new \Exception('Redis error'));

        // 創建並測試命令
        $command = new DeleteRedisKeyCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['key' => 'some_key']);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('刪除 Redis 鍵值時發生錯誤：Redis error', $output);
    }
}
