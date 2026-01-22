# 🚀 Panduan Deployment - Sistem Manajemen Dokumen Hukum Terpusat

> **Versi:** 1.0  
> **Terakhir Diperbarui:** Januari 2025  
> **Target:** Production Server RS Ngoerah

---

## 📋 Daftar Isi

1. [Persyaratan Server](#persyaratan-server)
2. [Persiapan Server](#persiapan-server)
3. [Instalasi Software](#instalasi-software)
4. [Konfigurasi Nginx](#konfigurasi-nginx)
5. [Konfigurasi PHP-FPM](#konfigurasi-php-fpm)
6. [Konfigurasi MySQL](#konfigurasi-mysql)
7. [Deploy Aplikasi](#deploy-aplikasi)
8. [Konfigurasi SSL](#konfigurasi-ssl)
9. [Konfigurasi Queue & Scheduler](#konfigurasi-queue-scheduler)
10. [Backup & Monitoring](#backup-monitoring)
11. [Checklist Deployment](#checklist-deployment)

---

## 🖥️ Persyaratan Server

### Minimum Specifications

| Komponen | Minimum | Rekomendasi |
|----------|---------|-------------|
| CPU | 4 Core | 8 Core |
| RAM | 8 GB | 16 GB |
| Storage | 100 GB SSD | 250 GB NVMe SSD |
| Bandwidth | 100 Mbps | 1 Gbps |
| OS | Ubuntu 22.04 LTS | Ubuntu 22.04 LTS |

### Untuk 3000+ Concurrent Users

| Komponen | Spesifikasi |
|----------|-------------|
| Web Server | 2 x Load Balanced (8 Core, 16GB RAM) |
| Database | Dedicated MySQL Server (8 Core, 32GB RAM) |
| Redis | Dedicated Redis Server (4 Core, 8GB RAM) |
| Storage | Shared NFS atau Object Storage |

---

## 🔧 Persiapan Server

### Update Sistem

```bash
sudo apt update && sudo apt upgrade -y
```

### Konfigurasi Firewall

```bash
sudo ufw allow OpenSSH
sudo ufw allow 'Nginx Full'
sudo ufw allow 3306/tcp  # MySQL (hanya dari internal)
sudo ufw allow 6379/tcp  # Redis (hanya dari internal)
sudo ufw enable
```

### Buat User Aplikasi

```bash
sudo adduser hukum
sudo usermod -aG www-data hukum
```

---

## 📦 Instalasi Software

### Install Nginx

```bash
sudo apt install nginx -y
sudo systemctl enable nginx
sudo systemctl start nginx
```

### Install PHP 8.3 dan Extensions

```bash
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.3-fpm php8.3-cli php8.3-common php8.3-mysql \
    php8.3-zip php8.3-gd php8.3-mbstring php8.3-curl php8.3-xml \
    php8.3-bcmath php8.3-tokenizer php8.3-redis php8.3-imagick -y
```

### Install MySQL 8

```bash
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

### Install Redis

```bash
sudo apt install redis-server -y
sudo systemctl enable redis-server
```

### Install Composer

```bash
curl -sS https://getcomposer.org/installer | php
sudo mv composer.phar /usr/local/bin/composer
```

### Install Node.js

```bash
curl -fsSL https://deb.nodesource.com/setup_20.x | sudo -E bash -
sudo apt install nodejs -y
```

### Install Supervisor

```bash
sudo apt install supervisor -y
sudo systemctl enable supervisor
```

---

## 🌐 Konfigurasi Nginx

### Buat File Konfigurasi

```bash
sudo nano /etc/nginx/sites-available/hukum-ngoerah
```

### Isi Konfigurasi

```nginx
server {
    listen 80;
    listen [::]:80;
    server_name hukum.rsngoerah.com;
    
    # Redirect to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    listen [::]:443 ssl http2;
    server_name hukum.rsngoerah.com;
    
    root /var/www/hukum-ngoerah/public;
    index index.php index.html;
    
    # SSL Configuration
    ssl_certificate /etc/letsencrypt/live/hukum.rsngoerah.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/hukum.rsngoerah.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256;
    ssl_prefer_server_ciphers off;
    
    # Security Headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline' 'unsafe-eval'" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Gzip Compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_proxied expired no-cache no-store private auth;
    gzip_types text/plain text/css text/xml text/javascript application/x-javascript application/xml application/javascript application/json;
    gzip_disable "MSIE [1-6]\.";
    
    # Client Max Body Size (untuk upload file)
    client_max_body_size 100M;
    
    # Logging
    access_log /var/log/nginx/hukum-ngoerah.access.log;
    error_log /var/log/nginx/hukum-ngoerah.error.log;
    
    # Static files caching
    location ~* \.(jpg|jpeg|png|gif|ico|css|js|pdf|woff|woff2|ttf|svg)$ {
        expires 1M;
        add_header Cache-Control "public, immutable";
    }
    
    # Laravel
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.3-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # Deny hidden files
    location ~ /\.(?!well-known).* {
        deny all;
    }
}
```

### Enable Site

```bash
sudo ln -s /etc/nginx/sites-available/hukum-ngoerah /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

---

## ⚙️ Konfigurasi PHP-FPM

### Edit Pool Configuration

```bash
sudo nano /etc/php/8.3/fpm/pool.d/www.conf
```

### Optimasi Settings

```ini
[www]
user = www-data
group = www-data
listen = /run/php/php8.3-fpm.sock
listen.owner = www-data
listen.group = www-data

; Process Manager
pm = dynamic
pm.max_children = 50
pm.start_servers = 10
pm.min_spare_servers = 5
pm.max_spare_servers = 20
pm.max_requests = 500

; Timeouts
request_terminate_timeout = 300
```

### Edit php.ini

```bash
sudo nano /etc/php/8.3/fpm/php.ini
```

```ini
memory_limit = 512M
upload_max_filesize = 100M
post_max_size = 100M
max_execution_time = 300
max_input_time = 300
date.timezone = Asia/Makassar
```

### Restart PHP-FPM

```bash
sudo systemctl restart php8.3-fpm
```

---

## 🗄️ Konfigurasi MySQL

### Login ke MySQL

```bash
sudo mysql
```

### Buat Database dan User

```sql
CREATE DATABASE hukum_ngoerah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'hukum_user'@'localhost' IDENTIFIED BY 'StrongPassword123!';
GRANT ALL PRIVILEGES ON hukum_ngoerah.* TO 'hukum_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

### Optimasi MySQL (my.cnf)

```bash
sudo nano /etc/mysql/mysql.conf.d/mysqld.cnf
```

```ini
[mysqld]
# InnoDB Settings
innodb_buffer_pool_size = 4G
innodb_log_file_size = 1G
innodb_flush_log_at_trx_commit = 2
innodb_flush_method = O_DIRECT

# Connection Settings
max_connections = 500
wait_timeout = 600
interactive_timeout = 600

# Query Cache (MySQL 8 tidak lagi menggunakan query cache)
# Gunakan ProxySQL jika perlu caching

# Logging
slow_query_log = 1
slow_query_log_file = /var/log/mysql/slow.log
long_query_time = 2
```

```bash
sudo systemctl restart mysql
```

---

## 📁 Deploy Aplikasi

### Clone Repository

```bash
cd /var/www
sudo git clone https://github.com/rsngoerah/hukum-ngoerah.git
cd hukum-ngoerah
```

### Set Permissions

```bash
sudo chown -R hukum:www-data .
sudo chmod -R 755 .
sudo chmod -R 775 storage bootstrap/cache
```

### Install Dependencies

```bash
composer install --no-dev --optimize-autoloader
npm ci --production
npm run build
```

### Configure Environment

```bash
cp .env.example .env
nano .env
```

```env
APP_NAME="Sistem Manajemen Dokumen Hukum"
APP_ENV=production
APP_KEY=
APP_DEBUG=false
APP_URL=https://hukum.rsngoerah.com
APP_TIMEZONE=Asia/Makassar
APP_LOCALE=id

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hukum_ngoerah
DB_USERNAME=hukum_user
DB_PASSWORD=StrongPassword123!

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

MAIL_MAILER=smtp
MAIL_HOST=smtp.rsngoerah.com
MAIL_PORT=587
MAIL_USERNAME=noreply@rsngoerah.com
MAIL_PASSWORD=mailpassword
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@rsngoerah.com
MAIL_FROM_NAME="${APP_NAME}"

LOG_CHANNEL=daily
LOG_LEVEL=error
```

### Generate Key & Optimize

```bash
php artisan key:generate
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
```

### Run Migrations

```bash
php artisan migrate --force
php artisan db:seed --force
```

---

## 🔒 Konfigurasi SSL

### Install Certbot

```bash
sudo apt install certbot python3-certbot-nginx -y
```

### Generate Certificate

```bash
sudo certbot --nginx -d hukum.rsngoerah.com
```

### Auto Renewal

```bash
sudo crontab -e
```

```
0 3 * * * certbot renew --quiet --post-hook "systemctl reload nginx"
```

---

## 📋 Konfigurasi Queue & Scheduler

### Supervisor untuk Queue Worker

```bash
sudo nano /etc/supervisor/conf.d/hukum-worker.conf
```

```ini
[program:hukum-worker]
process_name=%(program_name)s_%(process_num)02d
command=php /var/www/hukum-ngoerah/artisan queue:work redis --sleep=3 --tries=3 --max-time=3600
autostart=true
autorestart=true
stopasgroup=true
killasgroup=true
user=hukum
numprocs=4
redirect_stderr=true
stdout_logfile=/var/log/hukum-worker.log
stopwaitsecs=3600
```

```bash
sudo supervisorctl reread
sudo supervisorctl update
sudo supervisorctl start hukum-worker:*
```

### Cron untuk Scheduler

```bash
sudo crontab -e -u hukum
```

```
* * * * * cd /var/www/hukum-ngoerah && php artisan schedule:run >> /dev/null 2>&1
```

---

## 💾 Backup & Monitoring

### Backup Script

```bash
sudo nano /opt/scripts/backup-hukum.sh
```

```bash
#!/bin/bash
DATE=$(date +%Y%m%d_%H%M%S)
BACKUP_DIR="/backup/hukum"

# Database backup
mysqldump -u hukum_user -p'StrongPassword123!' hukum_ngoerah | gzip > $BACKUP_DIR/db_$DATE.sql.gz

# Files backup
tar -czf $BACKUP_DIR/files_$DATE.tar.gz /var/www/hukum-ngoerah/storage/app

# Cleanup old backups (keep 30 days)
find $BACKUP_DIR -type f -mtime +30 -delete
```

```bash
chmod +x /opt/scripts/backup-hukum.sh
```

### Cron Backup

```bash
sudo crontab -e
```

```
0 2 * * * /opt/scripts/backup-hukum.sh >> /var/log/hukum-backup.log 2>&1
```

### Log Rotation

```bash
sudo nano /etc/logrotate.d/hukum
```

```
/var/www/hukum-ngoerah/storage/logs/*.log {
    daily
    missingok
    rotate 14
    compress
    delaycompress
    notifempty
    create 0640 hukum www-data
    sharedscripts
}
```

---

## ✅ Checklist Deployment

### Pre-Deployment

- [ ] Backup database production (jika update)
- [ ] Review environment variables
- [ ] Test di staging environment
- [ ] Notify tim tentang maintenance window

### Deployment

- [ ] Pull latest code
- [ ] Install dependencies: `composer install --no-dev`
- [ ] Build assets: `npm run build`
- [ ] Run migrations: `php artisan migrate --force`
- [ ] Clear & rebuild cache:
  ```bash
  php artisan config:cache
  php artisan route:cache
  php artisan view:cache
  ```
- [ ] Restart queue workers: `supervisorctl restart hukum-worker:*`
- [ ] Restart PHP-FPM: `systemctl restart php8.3-fpm`

### Post-Deployment

- [ ] Test login functionality
- [ ] Test document upload/download
- [ ] Check error logs
- [ ] Monitor performance (15 menit pertama)
- [ ] Notify tim deployment selesai

---

## 🆘 Rollback Procedure

Jika terjadi masalah:

```bash
cd /var/www/hukum-ngoerah
git checkout <previous-commit-hash>
composer install --no-dev
php artisan migrate:rollback --step=1
php artisan config:cache
php artisan route:cache
supervisorctl restart hukum-worker:*
systemctl restart php8.3-fpm
```

---

## 📞 Kontak Darurat

| Nama | Role | Kontak |
|------|------|--------|
| IT Admin | Server Administrator | 08xx-xxxx-xxxx |
| Legal Head | Application Owner | 08xx-xxxx-xxxx |
| Developer | Technical Support | 08xx-xxxx-xxxx |

---

*Dokumen ini adalah bagian dari Sistem Manajemen Dokumen Hukum Terpusat RS Ngoerah*
