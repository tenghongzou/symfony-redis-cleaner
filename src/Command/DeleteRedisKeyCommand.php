<?php

namespace App\Command;

use App\Service\RedisService;
use Symfony\Component\Cache\Adapter\RedisAdapter;
use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

#[AsCommand(
    name: 'app:delete-redis-key',
    description: '刪除指定的 Redis 鍵值',
)]
class DeleteRedisKeyCommand extends Command
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
            ->addArgument('key', InputArgument::REQUIRED, '要刪除的 Redis 鍵值');
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $key = $input->getArgument('key');

        try {
            // 連接到 Redis 伺服器
            $redisConnection = $this->redisService->getConnection();

            // 直接刪除鍵值，依刪除數量判斷是否存在（單一指令，避免 exists/del 之間的競態）
            if ($redisConnection->del([$key]) > 0) {
                $io->success("Redis 鍵值 '$key' 已成功刪除。");
            } else {
                $io->warning("Redis 鍵值 '$key' 不存在。");
            }
        } catch (\Exception $e) {
            $io->error('刪除 Redis 鍵值時發生錯誤：' . $e->getMessage());
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
