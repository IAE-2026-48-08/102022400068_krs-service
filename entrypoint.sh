#!/bin/sh
set -e

# Bersihkan cache bootstrap bawaan dari host agar tidak bentrok dengan dependensi container
rm -f /var/www/bootstrap/cache/*.php

# 1. Pastikan berkas .env tersedia
if [ ! -f /var/www/.env ]; then
    echo "=> .env file tidak ditemukan. Menyalin .env.example..."
    cp /var/www/.env.example /var/www/.env
fi

# 2. Pastikan direktori database dan berkas SQLite tersedia
echo "=> Menyiapkan file database SQLite..."
mkdir -p /var/www/database
if [ ! -f /var/www/database/database.sqlite ]; then
    touch /var/www/database/database.sqlite
    chmod 775 /var/www/database/database.sqlite || true
fi

# 3. Jalankan composer install jika folder vendor kosong (antisipasi volume override)
if [ ! -d "/var/www/vendor" ] || [ ! -f "/var/www/vendor/autoload.php" ]; then
    echo "=> Folder vendor kosong atau tidak lengkap. Menjalankan composer install..."
    composer install --no-interaction --prefer-dist --no-dev --optimize-autoloader
fi

# 4. Generate Application Key jika belum ada
if ! grep -q "APP_KEY=base" /var/www/.env || [ -z "$(grep APP_KEY /var/www/.env | cut -d '=' -f2)" ]; then
    echo "=> Menghasilkan application key..."
    php artisan key:generate --force
fi

# 5. Tunggu database siap jika menggunakan MySQL
if [ "$DB_CONNECTION" = "mysql" ]; then
    echo "=> Menunggu database MySQL siap..."
    until php -r "try { new PDO('mysql:host=' . getenv('DB_HOST') . ';port=' . getenv('DB_PORT') . ';dbname=' . getenv('DB_DATABASE'), getenv('DB_USERNAME'), getenv('DB_PASSWORD')); exit(0); } catch (Exception \$e) { exit(1); }"; do
        echo "MySQL belum siap, menunggu 2 detik..."
        sleep 2
    done
    echo "=> MySQL siap!"
fi

# 6. Jalankan migrasi database beserta data awal (seeding)
echo "=> Menjalankan migrasi database dan pengisian data awal (seeding)..."
php artisan migrate --seed --force

# 6. Generate Swagger/OpenAPI Documentation
echo "=> Membuat dokumentasi API Swagger..."
php artisan l5-swagger:generate

# 7. Mengatur kepemilikan dan permission agar runtime Laravel lancar
echo "=> Mengatur hak akses folder storage & bootstrap..."
chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache /var/www/database || true
chmod -R 775 /var/www/storage /var/www/bootstrap/cache /var/www/database || true

echo "=> Laravel siap digunakan. Menjalankan perintah utama..."
exec "$@"
