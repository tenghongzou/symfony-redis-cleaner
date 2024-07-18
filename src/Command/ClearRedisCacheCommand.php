<?php

namespace App\Command;

use App\Service\RedisService;
use Predis\Client;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clear-redis-cache',
    description: 'Add a short description for your command',
)]
class ClearRedisCacheCommand extends Command
{
    private RedisService $redisService;

    public function __construct(RedisService $redisService)
    {
        parent::__construct();
        $this->redisService = $redisService;
    }

    protected function configure(): void
    {
        $this
            ->setDescription('清理 Redis 快取');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            // 連接到 Redis 伺服器
            $redisConnection = $this->redisService->getConnection();

            // 清空 Redis DB
            $redisConnection->flushdb();
            $io->success('Redis 快取已成功清理。');
        } catch (\Exception $e) {
            $io->error('清理 Redis 快取時發生錯誤：' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
