# 模擬案件　勤怠管理アプリ

## 環境構築
### Docker ビルド

1. git clone  git@github.com:keroyon-sgt/mock_attendance-management.git
2. docker-compose up -d --build

### Laravel 環境構築

1. docker-compose exec php bash
2. composer install
3. .env.example ファイルから.env を作成し、環境変数を変更  
   DB_DATABASE=laravel_db  
   DB_USERNAME=laravel_user  
   DB_PASSWORD=laravel_pass  
   MAIL_FROM_ADDRESS=from@example.com  
   MAIL_FROM_NAME="COACHTECH Attendance management system"

4. php artisan key:generate
5. php artisan migrate
6. php artisan db:seed  
   生成されるユーザーのパスワードはすべて”password”
   

## 使用技術  
・php 8.0  
・Laravel 8.x  
・MySQL 8.0  

## ER 図


<img width="591" height="421" alt="Image" src="https://github.com/user-attachments/assets/ca36852c-1077-4cc5-94d1-41c68802dfb4" />


## URL  
・開発環境：http://localhost  
・phpMyAdmin：http://localhost:8080  
・MailHog：http://localhost:8025  
