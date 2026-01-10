# 模擬案件2
coachtech 勤怠管理アプリ

# 環境構築手順
クローンの作成
git clone <リンク名>

プロジェクト直下に.envを作成
touch .env

.envに以下を記述（UID/GIDはホストOSのユーザーIDに合わせて設定）
UID=1000
GID=1000

# コンテナ操作
PHPコンテナに入る 

docker-compose exec php bash

# Composer インストール
composer install

# Laravel初期設定
.env 作成 cp .env.example .env

アプリキー生成 php artisan key:generate

マイグレーション php artisan migrate

ダミーデータ作成 php artisan db:seed

# 各キャッシュのクリアコマンド(動作が不安定な場合に使用してください)
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

PHPコンテナから出る　Ctrl+D

# ログインについての重要な注意
管理者側・一般ユーザーでセッションを分けていますが
複数の一般ユーザーが同一ブラウザでログインした場合、挙動が不安定になります
その際は別のブラウザ(chrome・edgeなど)でログインしてください

# ダミーデータユーザー情報（管理者1名、一般ユーザー6名）

## 1
name  管理者
email  admin@example.com
password  password123
メール認証済み

## 2
name  西　伶奈
email  reina.n@coachtech.com
password  password123
メール認証済み

## 3
name  山田　太郎
email  taro.y@coachtech.com
password  password123
メール認証済み

## 4
name  増田　一世
email  issei.m@coachtech.com
password  password123
メール認証済み
## 5
name  山本　敬吉
email  keikichi.y@coachtech.com
password  password123
メール認証済み

## 6
name  秋田　朋美
email  tomomi.a@coachtech.com
password  password123
メール認証済み

## 7
name  中西　教夫
email  norio.n@coachtech.com
password  password123
メール認証済み

# MailHogのメール認証手順
1. 新規ユーザー登録を行う
2. メール認証誘導画面に遷移、「認証はこちらから」のボタンをクリック
3. 以下のURLから MailHog にアクセスするので、メール内容を確認してください  
   👉 [http://localhost:8025]
4. 自身が登録したメール本文内の「メールアドレスを確認する」または「Verify Email Address」をクリックすると、認証が完了し、初回はプロフィール設定画面に遷移します
※MailHog画面内で認証リンクをクリックした際、1回目は反応しない場合があります。
その際はメール一覧画面に戻り、再度メール本文内の「メールアドレスを確認する」または「Verify Email Address」をクリックしてください。

# 動作確認URL一覧
会員登録画面（一般ユーザー）：	http://localhost/register

ログイン画面（一般ユーザー）	http://localhost/login

ログイン画面（管理者）：	http://localhost/admin/login

MySQL画面：	http://localhost:8080

mailhog認証画面：	http://localhost:8025/

# 仕様環境
PHP: 8.1.33 (CLI/FPM)
Laravel Framework: 8.83.8 (LTS)
MySQL: 8.0.26
nginx: 1.21.1
MailHog

# テストケース確認コマンド


# 補足（環境関連）
- MailHogはローカル開発用のSMTPキャプチャツールです。メールは実際には送信されません。
- UID/GIDはLinux環境で `id` コマンドにより確認可能です。
- ER図は設計の参考用です。実装と完全一致しない場合があります。
