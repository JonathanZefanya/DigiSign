# 🚀 Panduan Deploy DigiSign ke Server (Subfolder)

Panduan ini untuk deploy aplikasi DigiSign sebagai subfolder di domain,
contoh: `https://domain.com/digisign`

---

## 📋 Prasyarat Server

- PHP >= 8.2
- MySQL / MariaDB
- Composer (untuk install dependensi)
- Ekstensi PHP: `openssl`, `pdo`, `mbstring`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `gd`
- `mod_rewrite` Apache harus aktif

---

## 📦 Langkah-Langkah Deploy

### 1️⃣ Upload File ke Server

Upload **seluruh isi folder `signer`** ke subfolder di server.

Contoh lokasi di server:
```
/home/username/public_html/digisign/
```

Struktur di server harus seperti ini:
```
public_html/
└── digisign/          ← seluruh isi folder signer
    ├── .htaccess      ← sudah ada (redirect ke public/)
    ├── app/
    ├── bootstrap/
    ├── config/
    ├── database/
    ├── public/
    │   └── .htaccess  ← sudah ada (RewriteBase /digisign/)
    ├── resources/
    ├── routes/
    ├── storage/
    ├── vendor/
    ├── .env.production
    ├── artisan
    └── composer.json
```

### 2️⃣ Konfigurasi `.env`

Di server, **rename** `.env.production` menjadi `.env`, lalu edit:

```env
APP_NAME=DigiSign
APP_ENV=production
APP_DEBUG=false

# ── Ubah sesuai domain dan subfolder Anda ──
APP_URL=https://domain-anda.com/digisign
ASSET_URL=/digisign

# ── Database (ubah sesuai server) ──
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=nama_database_anda
DB_USERNAME=user_database_anda
DB_PASSWORD=password_database_anda

# ── Session ──
SESSION_PATH=/digisign
```

> ⚠️ **PENTING:** Jangan lupa generate APP_KEY baru di server!

### 3️⃣ Install Dependensi (via SSH / Terminal)

```bash
cd /home/username/public_html/digisign
composer install --optimize-autoloader --no-dev
```

### 4️⃣ Generate App Key

```bash
php artisan key:generate
```

### 5️⃣ Setup Database

```bash
# Buat semua tabel
php artisan migrate --force

# (Opsional) Jalankan seeder untuk membuat user admin default
php artisan db:seed --force
```

Setelah seeder, akun default:
- **Admin:** admin@digisign.local / password
- **User:** user@digisign.local / password

> ⚠️ Segera ganti password setelah login pertama kali!

### 6️⃣ Buat Storage Link

```bash
php artisan storage:link
```

Ini membuat symlink `public/storage` → `storage/app/public`

### 7️⃣ Set Permission (Linux)

```bash
chmod -R 775 storage bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache
```

> Ganti `www-data` dengan user web server Anda (bisa `apache`, `nginx`, dll)

### 8️⃣ Optimasi Cache (Production)

```bash
php artisan config:cache
php artisan route:cache
php artisan view:cache
```

---

## ✅ Selesai!

Buka browser dan akses: `https://domain-anda.com/digisign`

---

## 🔧 Troubleshooting

### Halaman Blank / Error 500
```bash
# Cek log error:
tail -f storage/logs/laravel.log

# Pastikan permission benar:
chmod -R 775 storage bootstrap/cache
```

### Asset (CSS/JS) Tidak Muncul
- Pastikan `ASSET_URL=/digisign` sudah ada di `.env`
- Jalankan: `php artisan config:cache`

### Login Tidak Tersimpan (Session Hilang)
- Pastikan `SESSION_PATH=/digisign` sudah ada di `.env`
- Pastikan tabel `sessions` sudah ter-migrate

### URL Error / Route Tidak Ditemukan
- Pastikan `RewriteBase /digisign/` ada di `public/.htaccess`
- Pastikan `mod_rewrite` aktif di Apache
- Jalankan: `php artisan route:cache`

### Nama Subfolder Berbeda?
Jika subfolder bukan `digisign` (misal `esign`), ubah di 3 tempat:
1. `public/.htaccess` → `RewriteBase /esign/`
2. `.env` → `APP_URL`, `ASSET_URL=/esign`, `SESSION_PATH=/esign`

---

## 📝 Ringkasan Perintah (Semua Sekaligus)

```bash
cd /home/username/public_html/digisign
composer install --optimize-autoloader --no-dev
cp .env.production .env
# edit .env sesuai kebutuhan
php artisan key:generate
php artisan migrate --force
php artisan db:seed --force
php artisan storage:link
chmod -R 775 storage bootstrap/cache
php artisan config:cache
php artisan route:cache
php artisan view:cache
```
