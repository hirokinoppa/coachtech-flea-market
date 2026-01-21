# 模擬案件　coachtechフリマ

## 環境構築

## Dockerビルド
- git clone git@github.com:hirokinoppa/coachtech-flea-market.git
- docker-compose up -d --build

## Laravel環境構築
- docker compose exec php bash
- composer install
- cp .env.example .env
- php artisan key:generate
- php artisan migrate
- php artisan db:seed

## 開発環境
- ：
- ユーザー登録：
-
- phpMyAdmin：http://localhost:8080/

## 使用技術（実行環境）
- PHP 8.2.11
- Laravel 8.83.8
- MySQL 8.0.34
- nginx 1.21.1
- fortify

## ER図
![ER図]()