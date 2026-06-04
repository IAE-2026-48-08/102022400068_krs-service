langkah langkah yang seharusnya saya lakukan apa saja, mulai dari menginstall laravel framework terus apa?

Oke, mari kita buat peta jalannya! Biar tidak bingung dan proses coding-nya terstruktur, ini adalah urutan langkah logis yang harus kamu lewati dari nol sampai proyeknya siap disetorkan.

1. Inisiasi Proyek & Instalasi Library
Langkah pertama tentu menyiapkan "rumah" dan perlengkapan wajibnya.


Install Laravel: Buka terminal dan buat proyek dengan format penamaan dari dosen.  
PDF

Bash
composer create-project laravel/laravel 102022400068_krs-service
cd 102022400068_krs-service

Install Library Tugas: Tambahkan paket untuk Swagger (REST API Docs) dan Lighthouse (GraphQL).  
PDF
+ 1

Bash
composer require darkaonline/l5-swagger
composer require nuwave/lighthouse mll-lab/laravel-graphiql
Publish Konfigurasi: Tarik file konfigurasi bawaan library agar bisa diedit.

Bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
php artisan vendor:publish --tag=lighthouse-schema
2. Konfigurasi Lingkungan (.env)
Buka file .env di code editor kamu. Ubah koneksi database menjadi SQLite agar praktis, dan tambahkan kunci rahasia untuk autentikasinya.  
PDF

Cuplikan kode
DB_CONNECTION=sqlite
# (Hapus baris DB_HOST, DB_PORT, dll di bawahnya agar tidak bentrok)

IAE_KEY=102022400068
Buat file database kosong dengan menjalankan touch database/database.sqlite di terminal.

3. Pembuatan Fondasi Database (Migration & Model)
Buat struktur tabel untuk Course (Mata Kuliah) dan KrsItem (Transaksi KRS).

Bash
php artisan make:model Course -m
php artisan make:model KrsItem -m
Tugas Kita Nanti: Kita akan mengisi file migration yang baru terbuat dengan kolom-kolom seperti code, name, credits, quota, sesuai dengan draf README kamu.

4. Implementasi Keamanan (Middleware)
Buat sistem penjaga pintu gerbang API yang akan mengecek header X-IAE-KEY.  
PDF

Bash
php artisan make:middleware CheckApiKey
Tugas Kita Nanti: Menambahkan logika if-else sederhana di file middleware ini untuk memastikan request ditolak jika header-nya salah.

5. Pembangunan REST API & Swagger (Fokus Utama)
Buat controller untuk mengatur arus datanya.

Bash
php artisan make:controller Api/V1/KrsController

Tugas Kita Nanti: Menulis logika untuk 3 endpoint wajib (GET courses, GET draft, POST submit). Setelah kodenya jalan, kita pasang "mantra" anotasi panjang di atas fungsinya agar dokumentasi Swagger UI-nya otomatis tercipta.  
PDF
+ 1

6. Pembangunan GraphQL

Tugas Kita Nanti: Membuka file graphql/schema.graphql dan merancang skema query sederhana. Ini agar klien punya keleluasaan mengambil data mata kuliah secara dinamis sesuai kebutuhan mereka.  
PDF

7. Finalisasi & Upload
Simpan file README.md dan REKAP-PROMPT.md (berisi percakapan kita ini) di folder paling depan proyekmu.  
PDF


Commit dan Push seluruh kodenya ke repositori GitHub organisasi dosen. 
