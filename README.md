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