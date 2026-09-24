<?php

namespace App\Command;

use App\Service\RedisService;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:clear-redis-cache',
    description: '清理 Redis 快取',
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
            ->addOption('force', 'f', InputOption::VALUE_NONE, '不經確認直接清空（非互動模式下必須指定）');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        // FLUSHDB 會清除整個 DB，未指定 --force 時需要確認
        if (!$input->getOption('force')) {
            if (!$input->isInteractive()) {
                $io->error('非互動模式下請加上 --force 才會清空 Redis DB。');
                return Command::INVALID;
            }

            if (!$io->confirm('將清除目前 Redis DB 的所有鍵值，確定要繼續嗎？', false)) {
                $io->note('已取消，未清除任何資料。');
                return Command::SUCCESS;
            }
        }

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
