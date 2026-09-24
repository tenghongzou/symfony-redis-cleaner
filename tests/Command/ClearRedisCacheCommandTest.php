<?php

namespace App\Tests\Command;

use App\Command\ClearRedisCacheCommand;
use App\Service\RedisService;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
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
        $redisConnectionMock->expects($this->once())->method('flushdb')->willReturn(true);

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['--force' => true]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::SUCCESS, $exitCode);
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
        $redisConnectionMock->method('flushdb')->willThrowException(new \Exception('Redis error'));

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['--force' => true]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('清理 Redis 快取時發生錯誤：Redis error', $output);
    }

    public function testExecuteConfirmed()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);
        $redisConnectionMock = $this->createMock(\Redis::class);

        // 配置 RedisService 行為
        $redisServiceMock->method('getConnection')->willReturn($redisConnectionMock);

        // 使用者確認後才清空
        $redisConnectionMock->expects($this->once())->method('flushdb')->willReturn(true);

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['yes']);

        $exitCode = $commandTester->execute([], ['interactive' => true]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('Redis 快取已成功清理。', $output);
    }

    public function testExecuteCancelled()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);

        // 取消時不應建立連線
        $redisServiceMock->expects($this->never())->method('getConnection');

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);
        $commandTester->setInputs(['no']);

        $exitCode = $commandTester->execute([], ['interactive' => true]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::SUCCESS, $exitCode);
        $this->assertStringContainsString('已取消，未清除任何資料。', $output);
    }

    public function testExecuteNonInteractiveWithoutForce()
    {
        // 模擬 RedisService
        $redisServiceMock = $this->createMock(RedisService::class);

        // 未指定 --force 時不應建立連線
        $redisServiceMock->expects($this->never())->method('getConnection');

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute([], ['interactive' => false]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::INVALID, $exitCode);
        $this->assertStringContainsString('--force', $output);
    }

    public function testExecuteConnectionFailure()
    {
        // 模擬 RedisService：連線延後到 getConnection() 才建立，失敗時會在這裡拋出
        $redisServiceMock = $this->createMock(RedisService::class);
        $redisServiceMock->method('getConnection')->willThrowException(new \Exception('Connection refused'));

        // 創建並測試命令
        $command = new ClearRedisCacheCommand($redisServiceMock);
        $commandTester = new CommandTester($command);

        $exitCode = $commandTester->execute(['--force' => true]);

        // 檢查輸出
        $output = $commandTester->getDisplay();
        $this->assertSame(Command::FAILURE, $exitCode);
        $this->assertStringContainsString('清理 Redis 快取時發生錯誤：Connection refused', $output);
    }
}
