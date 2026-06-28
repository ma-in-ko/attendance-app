# attendance-app
## 概要
企業向けの勤怠管理アプリです。
従業員は出勤・退勤・休憩時間の打刻や勤怠修正申請を行うことができ、
管理者は勤怠情報の確認や修正申請の承認を行うことができます。

## 目的
勤怠管理業務をWebアプリケーション上で効率的に管理できるシステムを
構築すること。

## 機能一覧

### 一般ユーザー
* 会員登録
* ログイン / ログアウト
* 出勤打刻
* 退勤打刻
* 休憩開始 / 休憩終了
* 勤怠一覧表示（月別）
* 勤怠詳細表示
* 勤怠修正申請
* 修正申請一覧表示

### 管理者
* 管理者ログイン
* 日次勤怠一覧表示
* スタッフ一覧表示
* 勤怠詳細表示
* 修正申請一覧表示
* 修正申請承認

## 画面一覧
 
### 一般ユーザー
* ログイン画面
* 会員登録画面
* 勤怠打刻画面
* 勤怠一覧画面
* 勤怠詳細画面
* 修正申請一覧画面

### 管理者
* ログイン画面
* 日次勤怠一覧画面
* スタッフ一覧画面
* 勤怠詳細画面
* 修正申請一覧画面

## 使用技術
* PHP 8.1.34
* Laravel 10.50.2
* MySQL 8.0
* Docker
* Blade
* Laravel Fortify(認証)

## 環境構築

### Dockerビルド
```
git clone https://github.com/ma-in-ko/attendance-app.git
cd attendance-app
docker compose up -d --build
```

### Laravelセットアップ
```
docker compose exec php bash
composer install
cp .env.example .env
php artisan key:generate
```

.envファイルに以下を設定してください
```
DB_CONNECTIONmy=sql
DB_HOST=mysql
DB_PORT=3306
DB_DATABASE=laravel_db
DB_USERNAME=laravel_user
DB_PASSWORD=laravel_pass
```

### マイグレーション
```
php artisan migrate --seed
```

## 認証機能
本アプリではLaravel Fortifyを使用して認証機能を実装しています。

### 実装内容
* 会員登録
* ログイン
* ログアウト
* 管理者ログイン

### バリデーション
FormRequestを使用してバリデーションを実装しています。

## 開発環境URL
* アプリ： http://localhost
* phpMyAdmin： http://localhost:8080

## ER図
![ER図](docs/ER-drawio.png)

## 工夫した点
* Laravel Fortifyを利用して認証機能を実装
* 出勤・退勤・休憩状態を画面上でわかりやすく表示
* 勤怠修正申請と承認機能を実装し、実際の業務を意識した設計
* リレーションを活用し、勤怠・休憩・修正申請を適切に管理
* Bladeテンプレートを利用し、画面ごとにレイアウトを共通化




 
