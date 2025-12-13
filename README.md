# 模擬案件2
coachtech 勤怠管理アプリ

## 環境構築手順
クローンの作成
git clone <リンク名>

プロジェクト直下に.envを作成
touch .env

.envに以下を記述（UID/GIDはホストOSのユーザーIDに合わせて設定）
UID=1000
GID=1000

## Laravel初期設定
make build
make init

## 各キャッシュのクリアコマンド(動作が不安定な場合に使用してください)
php artisan view:clear
php artisan route:clear
php artisan config:clear
php artisan cache:clear

PHPコンテナから出る　Ctrl+D

### MailHogのメール認証手順
1. 新規ユーザー登録を行う
2. メール認証誘導画面に遷移、「認証はこちらから」のボタンをクリック
3. 以下のURLから MailHog にアクセスするので、メール内容を確認してください  
   👉 [http://localhost:8025]
4. 自身が登録したメール本文内の「メールアドレスを確認する」または「Verify Email Address」をクリックすると、認証が完了し、初回はプロフィール設定画面に遷移します
※MailHog画面内で認証リンクをクリックした際、1回目は反応しない場合があります。
その際はメール一覧画面に戻り、再度メール本文内の「メールアドレスを確認する」または「Verify Email Address」をクリックしてください。

## 動作確認URL一覧
ログイン画面表示:http://localhost/login
商品一覧画面：http://localhost
MySQL画面：http://localhost:8080
mailhog認証画面：http://localhost:8025/

仕様環境
PHP: 8.1.33 (CLI/FPM)
Laravel Framework: 8.83.8 (LTS)
MySQL: 8.0.26
nginx: 1.21.1
MailHog

## 補足（環境関連）
- MailHogはローカル開発用のSMTPキャプチャツールです。メールは実際には送信されません。
- UID/GIDはLinux環境で `id` コマンドにより確認可能です。
- ER図は設計の参考用です。実装と完全一致しない場合があります。

###　仮画面確認用　URL
# ヘッダー部分	http://localhost
# ヘッダー部分	http://localhost/admin
# ヘッダー部分	http://localhost/user

# 会員登録画面（一般ユーザー）	http://localhost/register
# ログイン画面（一般ユーザー）	http://localhost/login
# メール認証誘導画面（一般ユーザー）http://localhost/verify-email
# 勤怠登録画面（一般ユーザー）	http://localhost/attendance
勤怠一覧画面（一般ユーザー）	http://localhost/attendance/list
勤怠詳細画面（一般ユーザー）	http://localhost/attendance/detail/{id}
申請一覧画面（一般ユーザー）	http://localhost/stamp_correction_request/list
# ログイン画面（管理者）	http://localhost/admin/login
勤怠一覧画面（管理者）	http://localhost/admin/attendance/list
勤怠詳細画面（管理者）	http://localhost/admin/attendance/{id}
スタッフ一覧画面（管理者）	http://localhost/admin/staff/list
スタッフ別勤怠一覧画面（管理者）	http://localhost/admin/attendance/staff/{id}
申請一覧画面（管理者）	http://localhost/stamp_correction_request/list
修正申請承認画面（管理者）	http://localhost/stamp_correction_request/approve/{attendance_correct_request_id}