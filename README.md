# 模擬案件　coachtechフリマ

## アプリ概要
ユーザーが商品を出品し、他のユーザーが商品を購入できるフリマサービスを想定しています。

ユーザー登録・ログイン・商品出品・商品購入・コメント・いいね機能など、
フリマアプリの基本機能をLaravelで実装しています。

また、実際のサービスに近づけるために以下の機能も実装しています。

- Stripeを利用した決済機能
- MailHogを利用したメール認証機能
- PHPUnitによる自動テスト
---

## 環境構築

### Dockerビルド

1. Docker clone
```sh
git clone git@github.com:hirokinoppa/coachtech-flea-market.git

```

2. Change Directory
```sh
cd coachtech-flea-market
```

3. Docker Build
```sh
docker-compose up -d --build
```

---

### Laravel環境構築

1. PHPコンテナ内にログイン
```sh
docker compose exec php bash
```

2. composerインストール
```sh
composer install
```

3. .envファイルの作成
```sh
cp .env.example .env
```

4.  storageフォルダの作成
```sh
mkdir -p storage/logs
```
```sh
chmod -R 775 storage bootstrap/cache
```
```sh
chown -R www-data:www-data storage bootstrap/cache
```

5. envファイルの編集(Part1)
ファイル内の一部を書き換えてください。
```sh

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

```
6. .envファイルの編集(Part2)
ファイル内の一部を書き換えてください。
```sh

MAIL_MAILER=smtp
MAIL_HOST=mailhog
MAIL_PORT=1025
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=null
MAIL_FROM_ADDRESS=no-reply@example.test
MAIL_FROM_NAME="${APP_NAME}"
MAIL_EHLO_DOMAIN=localhost

```

7. キーの作成
```sh
php artisan key:generate
```

8. マイグレーションの読み込み
```sh
php artisan migrate
```

9. シーダーファイルの読み込み
```sh
php artisan db:seed
```

10. ストレージファイルをリンクさせる
```sh
php artisan storage:link
```

---

## Stripe設定

StripeのAPIキーを取得して `.env` に設定してください。

.env
- STRIPE_KEY=your_stripe_public_key
- STRIPE_SECRET=your_stripe_secret_key

Stripeテストキーは以下から取得できます。
- https://dashboard.stripe.com/test/apikeys



## 開発環境
- トップページ：http://localhost/
- ユーザー登録：http://localhost/register
- ログイン：http://localhost/login
- phpMyAdmin：http://localhost:8080/
- MailHog(メール認証):http://localhost:8025/


---

## 主な機能
認証機能
- ユーザー登録
- ログイン / ログアウト
- メール認証（MailHog）
- 認証メール再送機能

新規登録後、メール認証を完了することでサービスを利用できます。

商品機能
- 商品一覧表示
- 商品詳細表示
- 商品検索
- 商品出品
- コメント機能
- いいね機能

マイページ機能
- 出品商品一覧表示
- 購入商品一覧表示
- プロフィール編集

購入機能

支払い方法選択

以下の支払い方法を選択できます。
- コンビニ支払い
- カード支払い

決済処理

Stripe Checkout を利用して決済を行います。

購入ボタンを押すとStripeの決済画面に遷移し、決済完了後に購入処理が実行されます。

---

## こだわりポイント(コーチの許可了承済み)
- 商品がSold outとなった場合にSold outと表示され画像が白黒になります
- 出品画面で画像を選択すると画像がセンターに表示され、画像を選択するのボタンが右に移動します

---

## 使用技術（実行環境）
- PHP 8.2.11
- Laravel 8.83.8
- MySQL 8.0.34
- nginx 1.21.1
- Docker
- Laravel Fortify（認証機能）
- Stripe（決済機能）
- MailHog（メール送信確認）

---

## テーブル設計
- users（ユーザー）
- profiles（プロフィール）
- items（商品）
- categories（カテゴリー）
- category_item（商品カテゴリー中間テーブル）
- comments（コメント）
- likes（いいね）
- orders（注文）

---

## テスト
 PHPUnitを使用して機能テストを実装しています。
 テストは本番データベースとは別の **テスト用データベース（laravel_test）** を使用して実行されます。

 これにより、実際のデータを破壊することなく安全にテストを行うことができます。
### テスト用データベースの作成

1. MySQLコンテナにログインする
```sh
docker compose exec mysql bash
```

2. MySQLにrootユーザーでログインする
```sh
mysql -u root -p
```

3. テスト用データベースを作成する
```sh
CREATE DATABASE laravel_test CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
GRANT ALL PRIVILEGES ON laravel_test.* TO 'laravel_user'@'%';
FLUSH PRIVILEGES;
```

4. .env.testingの設定
```sh
APP_ENV=testing

DB_CONNECTION=mysql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_test
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass

CACHE_DRIVER=array
QUEUE_CONNECTION=sync
SESSION_DRIVER=array
MAIL_MAILER=array
```

---
###　テスト実行

1. PHPコンテナにログインする
```sh
docker compose exec php bash
```

2. テスト用DBにマイグレーションを実行する
```sh
php artisan migrate --env=testing
```

3. テストを実行
```sh
php artisan test
```

---
## 主なテスト
- ユーザー登録
- ログイン
- 商品出品
- 商品検索
- 商品詳細表示
- コメント機能
- いいね機能
- マイページ表示
- プロフィール更新
- 商品購入
- 支払い方法選択
- 配送先変更

---

## ER図

本アプリケーションのテーブル構造です。
ユーザーを中心に商品・注文・コメント・いいねなどの関係を設計しています。

![ER図](docs/er-diagram.png)

---

