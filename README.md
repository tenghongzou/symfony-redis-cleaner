# symfony-redis-cleaner

以 Symfony Console 實作的 Redis 清理工具，提供清空整個 Redis DB 與刪除單一鍵值兩個指令。

## 需求

- PHP >= 8.2
- Composer
- Redis 伺服器
- Redis 用戶端（擇一即可）：
  - [`ext-redis`](https://github.com/phpredis/phpredis)（建議，效能較好；執行測試時必須安裝）
  - `ext-relay`
  - 兩者皆未安裝時，自動改用 [`predis/predis`](https://github.com/predis/predis)（已包含在相依套件中）

## 安裝

```bash
composer install
```

## 設定

在 `.env.local` 設定 Redis 連線位址（預設值見 `.env`）：

```dotenv
REDIS_URL=redis://localhost:6379
```

支援 Symfony `RedisAdapter` 的 DSN 格式，例如：

```dotenv
# 指定 DB 編號
REDIS_URL=redis://localhost:6379/2
# 帶密碼
REDIS_URL=redis://password@localhost:6379
```

## 使用方式

### 清空 Redis DB

```bash
php bin/console app:clear-redis-cache
```

> ⚠️ 會對 `REDIS_URL` 指定的 DB 執行 `FLUSHDB`，清除該 DB 內**所有**鍵值，不只是應用程式的快取，請確認連線目標後再執行。

### 刪除指定鍵值

```bash
php bin/console app:delete-redis-key <key>
```

鍵值存在時刪除並顯示成功訊息；不存在時顯示警告。兩種情況的結束碼皆為 `0`，連線或執行錯誤時為 `1`。

## 專案結構

```
src/
├── Command/
│   ├── ClearRedisCacheCommand.php   # app:clear-redis-cache
│   └── DeleteRedisKeyCommand.php    # app:delete-redis-key
└── Service/
    └── RedisService.php             # 依 REDIS_URL 建立 Redis 連線
config/services/redis.yaml           # RedisService 服務設定
tests/Command/                       # 指令的單元測試
```

## 測試

測試以 mock 取代 Redis 連線，不需要實際的 Redis 伺服器，但需要安裝 `ext-redis`：

```bash
vendor/bin/phpunit
```

本機未安裝 `ext-redis` 時，可在 Docker 中執行：

```bash
docker run --rm -v "$PWD":/app -w /app php:8.2-cli sh -c \
  'pecl install redis && docker-php-ext-enable redis && vendor/bin/phpunit'
```

## 技術棧

- Symfony 7.4 LTS（Console、FrameworkBundle、Cache）
- predis/predis 3.x
- PHPUnit 11.5
