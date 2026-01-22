# 📘 Panduan Instalasi - Sistem Manajemen Dokumen Hukum Terpusat

> **Versi:** 1.0  
> **Terakhir Diperbarui:** Januari 2025  
> **Platform:** RS Ngoerah Legal Document Management System

---

## 📋 Daftar Isi

1. [Persyaratan Sistem](#persyaratan-sistem)
2. [Instalasi Pengembangan (Development)](#instalasi-pengembangan)
3. [Konfigurasi Environment](#konfigurasi-environment)
4. [Setup Database](#setup-database)
5. [Build Frontend Assets](#build-frontend-assets)
6. [Menjalankan Aplikasi](#menjalankan-aplikasi)
7. [Troubleshooting](#troubleshooting)

---

## 🖥️ Persyaratan Sistem

### Minimum Requirements

| Komponen | Versi Minimum | Rekomendasi |
|----------|---------------|-------------|
| PHP | 8.2+ | 8.3 |
| MySQL | 8.0+ | 8.0.35+ |
| Node.js | 18+ | 20 LTS |
| NPM | 9+ | 10+ |
| Composer | 2.5+ | 2.7+ |

### PHP Extensions yang Diperlukan

```
- BCMath
- Ctype
- Fileinfo
- GD atau Imagick
- JSON
- Mbstring
- OpenSSL
- PDO (pdo_mysql)
- Tokenizer
- XML
- Zip
- Redis (opsional, untuk caching)
```

### Rekomendasi Server Production

| Komponen | Spesifikasi |
|----------|-------------|
| CPU | 4 Core+ |
| RAM | 8 GB+ |
| Storage | SSD 100 GB+ |
| Web Server | Nginx 1.24+ atau Apache 2.4+ |
| OS | Ubuntu 22.04 LTS atau CentOS 8+ |

---

## 🚀 Instalasi Pengembangan

### 1. Clone Repository

```bash
git clone https://github.com/rsngoerah/hukum-ngoerah.git
cd hukum-ngoerah
```

### 2. Install Dependencies PHP

```bash
composer install
```

### 3. Install Dependencies Node.js

```bash
npm install
```

### 4. Copy File Environment

```bash
cp .env.example .env
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

---

## ⚙️ Konfigurasi Environment

### Konfigurasi Dasar (.env)

Edit file `.env` dan sesuaikan nilai berikut:

```env
# Aplikasi
APP_NAME="Sistem Manajemen Dokumen Hukum"
APP_ENV=local
APP_KEY=base64:...
APP_DEBUG=true
APP_TIMEZONE=Asia/Makassar
APP_URL=http://localhost:8000
APP_LOCALE=id

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=hukum_ngoerah
DB_USERNAME=root
DB_PASSWORD=your_password

# Redis (opsional)
CACHE_STORE=file
SESSION_DRIVER=database
QUEUE_CONNECTION=database

# Mail (opsional)
MAIL_MAILER=log
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=null
MAIL_PASSWORD=null
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@rsngoerah.com"
MAIL_FROM_NAME="${APP_NAME}"

# Filesystem
FILESYSTEM_DISK=local
```

### Konfigurasi untuk Production

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=https://hukum.rsngoerah.com

CACHE_STORE=redis
SESSION_DRIVER=redis
QUEUE_CONNECTION=redis

# Logging
LOG_CHANNEL=daily
LOG_LEVEL=error
```

---

## 🗄️ Setup Database

### 1. Buat Database

Masuk ke MySQL dan buat database:

```sql
CREATE DATABASE hukum_ngoerah CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2. Jalankan Migrations

```bash
php artisan migrate
```

### 3. Jalankan Seeders (Data Awal)

```bash
php artisan db:seed
```

### 4. Atau Jalankan Fresh Migration dengan Seeder

```bash
php artisan migrate:fresh --seed
```

### Akun Default Setelah Seeding

| Username | Password | Role |
|----------|----------|------|
| superadmin | password123 | Super Admin |

> ⚠️ **Penting:** Segera ganti password setelah login pertama!

---

## 🎨 Build Frontend Assets

### Development (dengan Hot Reload)

```bash
npm run dev
```

### Production Build

```bash
npm run build
```

---

## ▶️ Menjalankan Aplikasi

### Development Server

```bash
# Terminal 1: PHP Development Server
php artisan serve

# Terminal 2: Vite Development Server (untuk hot reload)
npm run dev
```

Akses aplikasi di: `http://localhost:8000`

### Dengan Queue Worker (Opsional)

```bash
# Terminal 3: Queue Worker
php artisan queue:work
```

### Dengan Scheduler (Opsional)

```bash
# Untuk menjalankan scheduler manually
php artisan schedule:run

# Untuk production, tambahkan ke crontab:
# * * * * * cd /path-to-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔗 Storage Link

Untuk menghubungkan storage dengan public folder:

```bash
php artisan storage:link
```

---

## 🧪 Menjalankan Tests

```bash
# Semua tests
php artisan test

# Feature tests saja
php artisan test --testsuite=Feature

# Unit tests saja
php artisan test --testsuite=Unit

# Dengan coverage
php artisan test --coverage
```

---

## 🔧 Troubleshooting

### Error: SQLSTATE[HY000] [2002] Connection refused

**Solusi:** Pastikan MySQL server berjalan dan konfigurasi database di `.env` sudah benar.

```bash
# Ubuntu/Debian
sudo systemctl start mysql

# macOS dengan Homebrew
brew services start mysql
```

### Error: The stream or file could not be opened

**Solusi:** Berikan permission pada folder storage dan bootstrap/cache:

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache  # untuk production
```

### Error: Class not found

**Solusi:** Jalankan autoload ulang:

```bash
composer dump-autoload
php artisan clear-compiled
php artisan optimize
```

### Error: Vite manifest not found

**Solusi:** Build assets terlebih dahulu:

```bash
npm run build
```

### Error: Maximum execution time exceeded

**Solusi:** Tingkatkan nilai `max_execution_time` di `php.ini`:

```ini
max_execution_time = 300
```

### Error: Allowed memory size exhausted

**Solusi:** Tingkatkan nilai `memory_limit` di `php.ini`:

```ini
memory_limit = 512M
```

---

## 📞 Bantuan

Jika mengalami masalah saat instalasi, silakan:

1. Cek dokumentasi Laravel: https://laravel.com/docs
2. Buat issue di repository GitHub
3. Hubungi tim teknis RS Ngoerah

---

## 📝 Catatan Versi

| Versi | Tanggal | Catatan |
|-------|---------|---------|
| 1.0.0 | Jan 2025 | Rilis awal |

---

*Dokumen ini adalah bagian dari Sistem Manajemen Dokumen Hukum Terpusat RS Ngoerah*
