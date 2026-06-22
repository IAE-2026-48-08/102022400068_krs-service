ini gimna ya (memberikan konteks tugas)

KRS Service API (Layanan Registrasi Kartu Rencana Studi)
Project ini adalah Tugas 2 Integrasi Aplikasi Enterprise (IAE) berupa backend service berbasis framework Laravel modern yang menyediakan RESTful API untuk mengelola katalog mata kuliah, sisa kuota, serta pengajuan Kartu Rencana Studi (KRS) mahasiswa.
Fitur Utama
Autentikasi API Key: Semua endpoint dilindungi oleh middleware kustom `ApiKeyMiddleware` yang mencocokkan header `X-IAE-KEY` dengan nilai yang terkonfigurasi di file `.env`.
Katalog Mata Kuliah: Endpoint untuk melihat daftar mata kuliah beserta jumlah SKS dan sisa kuota yang dinamis.
Pendaftaran KRS: Memungkinkan mahasiswa mendaftar kelas dengan validasi otomatis:
   - Mahasiswa harus terdaftar di database.
   - Kelas harus tersedia di database.
   - Mahasiswa tidak dapat mengambil mata kuliah yang sama lebih dari satu kali (duplikasi data dicegah melalui unique constraint database & validasi aplikasi).
Pencegahan Race Condition: Menggunakan fitur Pessimistic Locking (`lockForUpdate()`) dalam Database Transaction saat proses registrasi KRS dilakukan untuk menjamin sisa kuota kelas berkurang secara akurat meskipun diakses secara bersamaan (concurrency).
Dokumentasi API Terintegrasi (Swagger/OpenAPI): Dokumentasi API langsung ditulis menggunakan PHP Attributes di level controller dan dapat diakses secara visual menggunakan Swagger UI.
---
Struktur Database & Model
Database menggunakan SQLite (`database/database.sqlite`) dengan tabel-tabel utama sebagai berikut:
1. Students (`students`)
Menyimpan informasi mahasiswa.
  `id` (String / NIM) - Primary Key
  `name` (String) - Nama Mahasiswa
  `created_at` / `updated_at` (Timestamp)
2. Courses (`courses`)
Menyimpan katalog mata kuliah yang ditawarkan.
  `id` (BigInt) - Primary Key
  `code` (String, Unique) - Kode Mata Kuliah (contoh: `IF-101`)
  `name` (String) - Nama Mata Kuliah
  `credits` (Integer) - Jumlah SKS
  `quota` (Integer) - Kapasitas Kelas
  `remaining_quota` (Integer) - Sisa Kapasitas Kelas
  `created_at` / `updated_at` (Timestamp)
3. KRS Items (`krs_items`)
Menyimpan transaksi pengambilan mata kuliah oleh mahasiswa.
  `id` (BigInt) - Primary Key
  `student_id` (String, Foreign Key -> `students.id`)
  `course_id` (BigInt, Foreign Key -> `courses.id`)
  `status` (String, default: `draft`) - Status KRS (contoh: `submitted`)
  `created_at` / `updated_at` (Timestamp)
  Constraint: Unique key gabungan `[student_id, course_id]` untuk menghindari duplikasi kelas yang sama oleh mahasiswa yang sama.
---
Struktur Direktori Utama & File Penting
Berikut adalah beberapa file penting dalam project ini:
  `routes/api.php`: Berisi definisi rute API dengan prefix `/v1` dan diproteksi oleh middleware `api.key`.
  `app/Http/Middleware/ApiKeyMiddleware.php`: Logika validasi header `X-IAE-KEY`.
  `app/Http/Controllers/Api/V1/KrsController.php`: Controller utama yang menangani logic API dan berisi anotasi Swagger/OpenAPI.
  `app/Models/`:
    *   `Student.php` - Model Mahasiswa.
    *   `Course.php` - Model Mata Kuliah.
    *   `KrsItem.php` - Model Transaksi KRS.
  `database/migrations/`: Berisi berkas migrasi pembuatan tabel database.
  `database/seeders/`: Berisi seeder awal untuk memasukkan data mahasiswa dan mata kuliah bawaan (default seed data).
---
Daftar API Endpoints
Semua endpoint memerlukan header autentikasi berikut:
  `X-IAE-KEY`: `<nilai_kunci_dari_env>` (default: `102022400068`)
1. Mendapatkan Daftar Mata Kuliah
  HTTP Method: `GET`
  Path: `/api/v1/courses`
  Respons Sukses (200 OK):
    `json     {       "status": "success",       "message": "Courses retrieved successfully",       "data": [         {           "id": 1,           "code": "IF-101",           "name": "Pemrograman Dasar",           "credits": 3,           "quota": 30,           "remaining_quota": 30,           "created_at": "2026-06-02T07:50:50.000000Z",           "updated_at": "2026-06-02T07:50:50.000000Z"         }       ],       "meta": {         "count": 1       }     }     `
2. Melihat Draf KRS Mahasiswa
  HTTP Method: `GET`
  Path: `/api/v1/krs/{student_id}`
  Respons Sukses (200 OK):
    `json     {       "status": "success",       "message": "KRS draft retrieved successfully",       "data": {         "student": {           "id": "102022400068",           "name": "Galih Hirpana"         },         "items": [           {             "id": 1,             "course": {               "id": 1,               "code": "IF-101",               "name": "Pemrograman Dasar",               "credits": 3             },             "status": "submitted",             "created_at": "2026-06-02T07:51:00.000000Z"           }         ]       },       "meta": {         "total_courses": 1,         "total_credits": 3       }     }     `
3. Mengajukan/Mendaftarkan KRS (Submit KRS)
  HTTP Method: `POST`
  Path: `/api/v1/krs/submit`
  Request Body (JSON):
    `json     {       "student_id": "102022400068",       "course_id": 1     }     `
  Respons Sukses (201 Created):
    `json     {       "status": "success",       "message": "KRS submitted successfully",       "data": {         "id": 1,         "student_id": "102022400068",         "course": {           "id": 1,           "code": "IF-101",           "name": "Pemrograman Dasar",           "credits": 3,           "remaining_quota": 29         },         "status": "submitted"       },       "meta": {         "timestamp": "2026-06-02T07:51:00.000000Z"       }     }     `
---
Dokumentasi & Penggunaan GraphQL
Selain RESTful API, service ini juga mendukung GraphQL untuk fleksibilitas query dari sisi klien. GraphQL diimplementasikan menggunakan package Lighthouse GraphQL untuk Laravel.
Endpoint Utama
  GraphQL Endpoint: `/graphql`
  GraphiQL Playground: `/graphiql` (dapat diakses pada environment lokal/development untuk melakukan testing query secara interaktif)
Contoh Query GraphQL
Klien dapat meminta field spesifik sesuai dengan kebutuhan mereka secara dinamis. Berikut adalah contoh query untuk mengambil daftar mata kuliah (courses) beserta sisa kuotanya:
Query
```graphql
query GetCourses {
  courses {
    id
    code
    name
    credits
    remaining_quota
  }
}
```
Respons (JSON)
```json
{
  "data": {
    "courses": [
      {
        "id": "1",
        "code": "IF-101",
        "name": "Pemrograman Dasar",
        "credits": 3,
        "remaining_quota": 30
      },
      {
        "id": "2",
        "code": "IF-202",
        "name": "Struktur Data & Algoritma",
        "credits": 4,
        "remaining_quota": 25
      },
      {
        "id": "3",
        "code": "IF-303",
        "name": "Rekayasa Perangkat Lunak",
        "credits": 3,
        "remaining_quota": 35
      }
    ]
  }
}
```
---
Cara Instalasi & Menjalankan Proyek
Prerequisites
Pastikan Anda sudah menginstal PHP (>= 8.3) dan Composer pada sistem Anda.
1. Clone & Instalasi Dependensi
Jalankan perintah berikut untuk menginstal package yang diperlukan:
```bash
composer install
```
2. Konfigurasi Environment File
Salin file `.env.example` menjadi `.env`:
```bash
copy .env.example .env
```
Pastikan variabel `IAE_KEY` sudah terdefinisi di dalam file `.env`:
```env
IAE_KEY=102022400068
```
3. Generate Application Key
```bash
php artisan key:generate
```
4. Setup Database SQLite & Seed Data
Secara default, Laravel dikonfigurasi untuk menggunakan SQLite. Pastikan file database telah dibuat, lalu jalankan migrasi dan seeder:
```bash
# Membuat file database sqlite jika belum ada
touch database/database.sqlite

# Menjalankan migrasi database beserta pengisian data awal (seeder)
php artisan migrate --seed
```
5. Generate Dokumentasi Swagger/OpenAPI
Untuk mengompilasi anotasi OpenAPI menjadi dokumen JSON Swagger, jalankan perintah:
```bash
php artisan l5-swagger:generate
```
6. Menjalankan Server Development
Jalankan web server lokal menggunakan perintah Artisan:
```bash
php artisan serve
```
Setelah server berjalan, Anda dapat mengakses:
  API Base URL: `http://localhost:8000/api`
  Swagger UI (Dokumentasi API): http://localhost:8000/api/documentation diatas milik saya dan ini milik kelompok kelompok saya Judul: VALIDASI DAN PERSETUJUAN PENGAMBILAN MATA KULIAH (KRS)
Service Data Mahasiswa, Service Mata Kuliah & KRS, dan Service Nilai & Kurikulum
Aktivitas Verifikasi Status (Service Data Mahasiswa):
 Service ini menerima kiriman data NIM dari Service Mata Kuliah & KRS ketika mahasiswa melakukan submit.
Berdasarkan NIM tersebut, service ini memeriksa daftar global mahasiswa aktif di dalam databasenya sendiri, kemudian memfilter data profil spesifik mahasiswa tersebut untuk memastikan statusnya mutlak "Aktif" serta mengembalikan data jatah maksimum SKS-nya kembali ke Service Mata Kuliah & KRS. 
Aktivitas Validasi Kapasitas Kelas (Service Mata Kuliah & KRS): 
Service ini mengambil data draf pilihan mata kuliah yang dikirimkan oleh Aplikasi Portal Mahasiswa (Frontend). 
Sistem kemudian mencocokkan kode mata kuliah yang diambil tersebut dengan data daftar kelas yang dibuka pada databasenya sendiri untuk mengecek apakah sisa kuota bangku masih tersedia dan jam kuliahnya tidak saling bentrok.
Aktivitas Pemeriksaan Prasyarat Akademis (Service Nilai & Kurikulum): 
Service ini menerima request pengecekan dari Service Mata Kuliah & KRS yang membawa data NIM dan Kode Mata Kuliah pilihan. 
Berdasarkan data kiriman tersebut, service ini memeriksa struktur aturan kurikulum program studi dan transkrip historis nilai mahasiswa yang ada di databasenya sendiri untuk memastikan mahasiswa tersebut sudah lulus mata kuliah prasyarat dengan nilai aman, lalu mengirimkan status "Lolos/Tidak" kembali ke Service Mata Kuliah & KRS. 
Aktivitas Finalisasi Kontrak Mata Kuliah (Service Mata Kuliah & KRS ): 
Setelah menerima konfirmasi sukses dari hasil validasi status mahasiswa dan prasyarat nilai, service ini mengunci dan menyimpan data transaksi KRS mahasiswa ke database KRS miliknya.
 lalu mengirimkan perintah HTTP POST berisi data NIM dan Kode MK ke Service Nilai & Kurikulum agar service tersebut membuatkan baris data (record) nilai kosong baru sebagai penutup siklus. 
 
SERVICE DATA MAHASISWA (D Hans Dhika Slamet)
Resource Name: students

Collection:  GET /api/v1/students 
(Mengambil seluruh daftar mahasiswa aktif untuk sinkronisasi data kuota awal semester).
Resource:  GET /api/v1/students/{id} 
(Mengambil detail profil statis mahasiswa seperti Nama, NIM, dan Status Aktif/Tidak untuk verifikasi identitas dasar).
Action:  POST /api/v1/students/validate-quota 
(Memproses validasi logika bisnis dengan menerima input jumlah SKS yang diajukan via request body, lalu menghitung kecukupan sisa kuota mahasiswa secara real-time untuk menentukan eligibility pengambilan KRS).
SERVICE MATA KULIAH & KRS (Galih Hirpana)
Resource Name: krs
Collection: GET /api/v1/courses (Menampilkan daftar mata kuliah beserta sisa kuota kapasitas kelas yang dibuka pada semester berjalan).
Resource: GET /api/v1/krs/{student_id} (Menampilkan detail isi draf pilihan kelas milik mahasiswa tertentu untuk pengecekan bentrok jadwal).
Action: POST /api/v1/krs/submit (Membuat transaksi pengajuan kontrak mata kuliah dan mengubah status draf KRS menjadi "Terkunci/Disetujui").
SERVICE NILAI & KURIKULUM (Muhammad Manhal Syarifudin)
Resource Name: grades
Collection: GET /api/v1/curriculums (Menampilkan daftar aturan prasyarat kurikulum program studi untuk mendeteksi keterikatan antar mata kuliah).
Resource: GET /api/v1/grades/{student_id} (Menampilkan detail riwayat transkrip nilai mahasiswa untuk pembuktian kelulusan mata kuliah prasyarat).
Action: POST /api/v1/grades/initialize (Membuat baris data (record) nilai baru yang masih kosong di database nilai setelah menerima perintah finalisasi dari Service Mata Kuliah & KRS).


KRS Service API (Layanan Registrasi Kartu Rencana Studi)
Project ini adalah Tugas 2 Integrasi Aplikasi Enterprise (IAE) berupa backend service berbasis framework Laravel modern yang menyediakan RESTful API untuk mengelola katalog mata kuliah, sisa kuota, serta pengajuan Kartu Rencana Studi (KRS) mahasiswa.
Fitur Utama
Autentikasi API Key: Semua endpoint dilindungi oleh middleware kustom `ApiKeyMiddleware` yang mencocokkan header `X-IAE-KEY` dengan nilai yang terkonfigurasi di file `.env`.
Katalog Mata Kuliah: Endpoint untuk melihat daftar mata kuliah beserta jumlah SKS dan sisa kuota yang dinamis.
Pendaftaran KRS: Memungkinkan mahasiswa mendaftar kelas dengan validasi otomatis:
   - Mahasiswa harus terdaftar di database.
   - Kelas harus tersedia di database.
   - Mahasiswa tidak dapat mengambil mata kuliah yang sama lebih dari satu kali (duplikasi data dicegah melalui unique constraint database & validasi aplikasi).
Pencegahan Race Condition: Menggunakan fitur Pessimistic Locking (`lockForUpdate()`) dalam Database Transaction saat proses registrasi KRS dilakukan untuk menjamin sisa kuota kelas berkurang secara akurat meskipun diakses secara bersamaan (concurrency).
Dokumentasi API Terintegrasi (Swagger/OpenAPI): Dokumentasi API langsung ditulis menggunakan PHP Attributes di level controller dan dapat diakses secara visual menggunakan Swagger UI.
---
Struktur Database & Model
Database menggunakan SQLite (`database/database.sqlite`) dengan tabel-tabel utama sebagai berikut:
1. Students (`students`)
Menyimpan informasi mahasiswa.
  `id` (String / NIM) - Primary Key
  `name` (String) - Nama Mahasiswa
  `created_at` / `updated_at` (Timestamp)
2. Courses (`courses`)
Menyimpan katalog mata kuliah yang ditawarkan.
  `id` (BigInt) - Primary Key
  `code` (String, Unique) - Kode Mata Kuliah (contoh: `IF-101`)
  `name` (String) - Nama Mata Kuliah
  `credits` (Integer) - Jumlah SKS
  `quota` (Integer) - Kapasitas Kelas
  `remaining_quota` (Integer) - Sisa Kapasitas Kelas
  `created_at` / `updated_at` (Timestamp)
3. KRS Items (`krs_items`)
Menyimpan transaksi pengambilan mata kuliah oleh mahasiswa.
  `id` (BigInt) - Primary Key
  `student_id` (String, Foreign Key -> `students.id`)
  `course_id` (BigInt, Foreign Key -> `courses.id`)
  `status` (String, default: `draft`) - Status KRS (contoh: `submitted`)
  `created_at` / `updated_at` (Timestamp)
  Constraint: Unique key gabungan `[student_id, course_id]` untuk menghindari duplikasi kelas yang sama oleh mahasiswa yang sama.
---
Struktur Direktori Utama & File Penting
Berikut adalah beberapa file penting dalam project ini:
  `routes/api.php`: Berisi definisi rute API dengan prefix `/v1` dan diproteksi oleh middleware `api.key`.
  `app/Http/Middleware/ApiKeyMiddleware.php`: Logika validasi header `X-IAE-KEY`.
  `app/Http/Controllers/Api/V1/KrsController.php`: Controller utama yang menangani logic API dan berisi anotasi Swagger/OpenAPI.
  `app/Models/`:
    *   `Student.php` - Model Mahasiswa.
    *   `Course.php` - Model Mata Kuliah.
    *   `KrsItem.php` - Model Transaksi KRS.
  `database/migrations/`: Berisi berkas migrasi pembuatan tabel database.
  `database/seeders/`: Berisi seeder awal untuk memasukkan data mahasiswa dan mata kuliah bawaan (default seed data).
---
Daftar API Endpoints
Semua endpoint memerlukan header autentikasi berikut:
  `X-IAE-KEY`: `<nilai_kunci_dari_env>` (default: `102022400068`)
1. Mendapatkan Daftar Mata Kuliah
  HTTP Method: `GET`
  Path: `/api/v1/courses`
  Respons Sukses (200 OK):
    `json     {       "status": "success",       "message": "Courses retrieved successfully",       "data": [         {           "id": 1,           "code": "IF-101",           "name": "Pemrograman Dasar",           "credits": 3,           "quota": 30,           "remaining_quota": 30,           "created_at": "2026-06-02T07:50:50.000000Z",           "updated_at": "2026-06-02T07:50:50.000000Z"         }       ],       "meta": {         "count": 1       }     }     `
2. Melihat Draf KRS Mahasiswa
  HTTP Method: `GET`
  Path: `/api/v1/krs/{student_id}`
  Respons Sukses (200 OK):
    `json     {       "status": "success",       "message": "KRS draft retrieved successfully",       "data": {         "student": {           "id": "102022400068",           "name": "Galih Hirpana"         },         "items": [           {             "id": 1,             "course": {               "id": 1,               "code": "IF-101",               "name": "Pemrograman Dasar",               "credits": 3             },             "status": "submitted",             "created_at": "2026-06-02T07:51:00.000000Z"           }         ]       },       "meta": {         "total_courses": 1,         "total_credits": 3       }     }     `
3. Mengajukan/Mendaftarkan KRS (Submit KRS)
  HTTP Method: `POST`
  Path: `/api/v1/krs/submit`
  Request Body (JSON):
    `json     {       "student_id": "102022400068",       "course_id": 1     }     `
  Respons Sukses (201 Created):
    `json     {       "status": "success",       "message": "KRS submitted successfully",       "data": {         "id": 1,         "student_id": "102022400068",         "course": {           "id": 1,           "code": "IF-101",           "name": "Pemrograman Dasar",           "credits": 3,           "remaining_quota": 29         },         "status": "submitted"       },       "meta": {         "timestamp": "2026-06-02T07:51:00.000000Z"       }     }     `
---
Dokumentasi & Penggunaan GraphQL
Selain RESTful API, service ini juga mendukung GraphQL untuk fleksibilitas query dari sisi klien. GraphQL diimplementasikan menggunakan package Lighthouse GraphQL untuk Laravel.
Endpoint Utama
  GraphQL Endpoint: `/graphql`
  GraphiQL Playground: `/graphiql` (dapat diakses pada environment lokal/development untuk melakukan testing query secara interaktif)
Contoh Query GraphQL
Klien dapat meminta field spesifik sesuai dengan kebutuhan mereka secara dinamis. Berikut adalah contoh query untuk mengambil daftar mata kuliah (courses) beserta sisa kuotanya:
Query
```graphql
query GetCourses {
  courses {
    id
    code
    name
    credits
    remaining_quota
  }
}
```
Respons (JSON)
```json
{
  "data": {
    "courses": [
      {
        "id": "1",
        "code": "IF-101",
        "name": "Pemrograman Dasar",
        "credits": 3,
        "remaining_quota": 30
      },
      {
        "id": "2",
        "code": "IF-202",
        "name": "Struktur Data & Algoritma",
        "credits": 4,
        "remaining_quota": 25
      },
      {
        "id": "3",
        "code": "IF-303",
        "name": "Rekayasa Perangkat Lunak",
        "credits": 3,
        "remaining_quota": 35
      }
    ]
  }
}
```
---
Cara Instalasi & Menjalankan Proyek
Prerequisites
Pastikan Anda sudah menginstal PHP (>= 8.3) dan Composer pada sistem Anda.
1. Clone & Instalasi Dependensi
Jalankan perintah berikut untuk menginstal package yang diperlukan:
```bash
composer install
```
2. Konfigurasi Environment File
Salin file `.env.example` menjadi `.env`:
```bash
copy .env.example .env
```
Pastikan variabel `IAE_KEY` sudah terdefinisi di dalam file `.env`:
```env
IAE_KEY=102022400068
```
3. Generate Application Key
```bash
php artisan key:generate
```
4. Setup Database SQLite & Seed Data
Secara default, Laravel dikonfigurasi untuk menggunakan SQLite. Pastikan file database telah dibuat, lalu jalankan migrasi dan seeder:
```bash
# Membuat file database sqlite jika belum ada
touch database/database.sqlite

# Menjalankan migrasi database beserta pengisian data awal (seeder)
php artisan migrate --seed
```
5. Generate Dokumentasi Swagger/OpenAPI
Untuk mengompilasi anotasi OpenAPI menjadi dokumen JSON Swagger, jalankan perintah:
```bash
php artisan l5-swagger:generate
```
6. Menjalankan Server Development
Jalankan web server lokal menggunakan perintah Artisan:
```bash
php artisan serve
```
Setelah server berjalan, Anda dapat mengakses:
  API Base URL: `http://localhost:8000/api`
  Swagger UI (Dokumentasi API): http://localhost:8000/api/documentation diatas milik saya dan ini milik kelompok kelompok saya Judul: VALIDASI DAN PERSETUJUAN PENGAMBILAN MATA KULIAH (KRS)
Service Data Mahasiswa, Service Mata Kuliah & KRS, dan Service Nilai & Kurikulum
Aktivitas Verifikasi Status (Service Data Mahasiswa):
 Service ini menerima kiriman data NIM dari Service Mata Kuliah & KRS ketika mahasiswa melakukan submit.
Berdasarkan NIM tersebut, service ini memeriksa daftar global mahasiswa aktif di dalam databasenya sendiri, kemudian memfilter data profil spesifik mahasiswa tersebut untuk memastikan statusnya mutlak "Aktif" serta mengembalikan data jatah maksimum SKS-nya kembali ke Service Mata Kuliah & KRS. 
Aktivitas Validasi Kapasitas Kelas (Service Mata Kuliah & KRS): 
Service ini mengambil data draf pilihan mata kuliah yang dikirimkan oleh Aplikasi Portal Mahasiswa (Frontend). 
Sistem kemudian mencocokkan kode mata kuliah yang diambil tersebut dengan data daftar kelas yang dibuka pada databasenya sendiri untuk mengecek apakah sisa kuota bangku masih tersedia dan jam kuliahnya tidak saling bentrok.
Aktivitas Pemeriksaan Prasyarat Akademis (Service Nilai & Kurikulum): 
Service ini menerima request pengecekan dari Service Mata Kuliah & KRS yang membawa data NIM dan Kode Mata Kuliah pilihan. 
Berdasarkan data kiriman tersebut, service ini memeriksa struktur aturan kurikulum program studi dan transkrip historis nilai mahasiswa yang ada di databasenya sendiri untuk memastikan mahasiswa tersebut sudah lulus mata kuliah prasyarat dengan nilai aman, lalu mengirimkan status "Lolos/Tidak" kembali ke Service Mata Kuliah & KRS. 
Aktivitas Finalisasi Kontrak Mata Kuliah (Service Mata Kuliah & KRS ): 
Setelah menerima konfirmasi sukses dari hasil validasi status mahasiswa dan prasyarat nilai, service ini mengunci dan menyimpan data transaksi KRS mahasiswa ke database KRS miliknya.
 lalu mengirimkan perintah HTTP POST berisi data NIM dan Kode MK ke Service Nilai & Kurikulum agar service tersebut membuatkan baris data (record) nilai kosong baru sebagai penutup siklus. 
 
SERVICE DATA MAHASISWA (D Hans Dhika Slamet)
Resource Name: students

Collection:  GET /api/v1/students 
(Mengambil seluruh daftar mahasiswa aktif untuk sinkronisasi data kuota awal semester).
Resource:  GET /api/v1/students/{id} 
(Mengambil detail profil statis mahasiswa seperti Nama, NIM, dan Status Aktif/Tidak untuk verifikasi identitas dasar).
Action:  POST /api/v1/students/validate-quota 
(Memproses validasi logika bisnis dengan menerima input jumlah SKS yang diajukan via request body, lalu menghitung kecukupan sisa kuota mahasiswa secara real-time untuk menentukan eligibility pengambilan KRS).
SERVICE MATA KULIAH & KRS (Galih Hirpana)
Resource Name: krs
Collection: GET /api/v1/courses (Menampilkan daftar mata kuliah beserta sisa kuota kapasitas kelas yang dibuka pada semester berjalan).
Resource: GET /api/v1/krs/{student_id} (Menampilkan detail isi draf pilihan kelas milik mahasiswa tertentu untuk pengecekan bentrok jadwal).
Action: POST /api/v1/krs/submit (Membuat transaksi pengajuan kontrak mata kuliah dan mengubah status draf KRS menjadi "Terkunci/Disetujui").
SERVICE NILAI & KURIKULUM (Muhammad Manhal Syarifudin)
Resource Name: grades
Collection: GET /api/v1/curriculums (Menampilkan daftar aturan prasyarat kurikulum program studi untuk mendeteksi keterikatan antar mata kuliah).
Resource: GET /api/v1/grades/{student_id} (Menampilkan detail riwayat transkrip nilai mahasiswa untuk pembuktian kelulusan mata kuliah prasyarat).
Action: POST /api/v1/grades/initialize (Membuat baris data (record) nilai baru yang masih kosong di database nilai setelah menerima perintah finalisasi dari Service Mata Kuliah & KRS).

caranya gimana ya step by stepnya. dimulai darimana dulu

sebenernya disuruh membuat pakevsual paradigma tapi coba kamu buat dulu biar nanti aku ikutin. dan juga [21:45, 08/06/2026] +62 813-1282-5605: dear all sorry memberikan info di malam hari. saya hanya mau menginfokan URL dan akun untuk join ke SSO Dosen sdh available di LMS week 14. nuhun
[09:58, 10/06/2026] +62 813-1282-5605: dear all untuk contoh layanan yang terhubung ke cloud dan akun warga + API-KEY nya silahkan diakses via LMS ya. barusan saya upload katanya dia bisa tau siapa yang mengakses (menghint apimana dan sebagaimna emang benar bisa seperti itu ?

Step 2: Testing Endpoint Dosen via Postman
Sebelum menulis kode di Laravel, wajib tes endpoint dosen secara manual pakai aplikasi seperti Postman atau Insomnia.
Dapatkan Token: Hit POST https://iae-sso.virtualfri.id/api/v1/auth/token pakai email dan password warga (misal: warga01@ktp.iae.id) yang dikasih dosen untuk mendapatkan Token JWT.
Tes SOAP: Buka tab baru di Postman, set Method ke POST, masukkan URL SOAP dosen. Masukkan Token JWT di tab Authorization (tipe Bearer). Di bagian Body (pilih raw -> XML), masukkan format tag SOAP yang ada di spesifikasi dosen. Pastikan dapat balasan SUCCESS.
Tes RabbitMQ: Lakukan hal yang sama untuk endpoint publish message, tapi Body-nya format JSON.
Jika di Postman sudah berhasil dan dapat respons hijau (200 OK), berarti kamu sudah paham cara kerja API dosen. tadi kita loncat ke step tiga ya. nah soal ini berikan step by step yang lebih detail dong saya kurang paham
Tes RabbitMQ (Publish Message)
Terakhir, kita tes jalur pengiriman pesan ke layanan kurikulum milik rekan satu timmu.
Buka Tab Baru di Postman.
Ubah Method menjadi POST.
Masukkan URL: https://iae-sso.virtualfri.id/api/v1/messages/publish
Klik tab Authorization, pilih Bearer Token, dan masukkan token panjang yang sama dari langkah 1.
Klik tab Body, pilih raw, dan pastikan formatnya disetel ke JSON.
Masukkan kode ini ke kotak teks:
JSON
{
"exchange": "iae.central.exchange",
"routing_key": "krs.submitted.event",
"payload": {
"student_id": "102022400068",
"course_id": "IF-101",
"message": "KRS berhasil didaftarkan"
}
}
Klik Send. Jika sukses, Postman akan menampilkan respons hijau 200 OK dengan pesan bahwa event berhasil disebarkan (published).
Silakan dicoba di Postman kamu. Bagian mana dari ketiga tes di atas yang ingin langsung dipraktikkan sekarang, atau apakah ada peringatan error merah yang muncul saat menekan tombol Send di langkah pertama? nah ini gimana tokennya lupa

semuanya sudah ni habis itu apa
apakah seperti ini ?

Task php artisan test started. Let's look at the result when it finishes. <task_notification:2e8d7e62-b30d-4c7f-9aea-e99b7215af7c/task-71> Exit Code: 0 Stdout: {"tool":"phpunit","result":"passed","tests":2,"passed":2,"assertions":2,"duration_ms":319} Stderr:
</task_notification>
Semua tes berhasil lolos!
Rangkuman Penyesuaian (Langkah 3.2 & 3.3)
Pembaruan Service Class: File 
IaeIntegrationService.php telah disesuaikan agar:
Menggunakan $this->baseUrl yang bernilai default https://iae-sso.virtualfri.id (bisa di-override lewat .env melalui IAE_SSO_URL).
Menggunakan XML payload baru dengan tag <TeamID>, <ActivityName> (KrsSubmitted), dan <LogContent> (yang melampirkan JSON-encoded payload dalam blok CDATA).
sendSoapAudit mengembalikan true saat sukses dan melempar Exception jika terjadi kegagalan.
publishEvent mengembalikan status sukses pengiriman RabbitMQ.
Penyesuaian di KrsController: File 
KrsController.php pada method submit telah disesuaikan agar:
Memeriksa kehadiran Bearer Token terlebih dahulu (if (!$token)), mengembalikan respons 401 dengan pesan "Token JWT tidak ditemukan" jika tidak ada.
Mengirim data transaksi dengan key status => 'submitted' ke sistem eksternal.
Mengembalikan respons sukses sesuai standar format wrapper dengan pesan "KRS berhasil diajukan dan dicatat di sistem terpusat.".
Semua perubahan telah selesai diintegrasikan dan diverifikasi dengan sukses!
Running artisan tests finished
Semua integrasi telah diuji dan tes unit berjalan dengan sukses (passed). Seluruh kode telah sinkron dengan spesifikasi terbaru. Silakan beri tahu jika ada langkah selanjutnya yang perlu dibantu!
lalu sekarang apa.
ini sudah semua berarti ya ? cara ngecek sudah masuk ke dosennya gimana
ini ngeceknya juga harus pake docker kah ?
apakah dosen akan tahu bahwa yang ngehit tuh saya ?  dan kalo iya tau darimana dan apakah hit dengan penggunaan docker dipuerlukan ? dan cara hitnya gimana
GroupGroup descriptionCountMembersGroup 9
3/3HD HANS DHIKA SLAMET,  GALIH HIRPANA,  MUHAMMAD MANHAL SYARIFUDIN ini group saya dan apakah tadi saya sudah hit menggunakan nim ?
apakah harus ada yang diganti atau gimana
Akun Login SSO (Untuk Mengambil Token)
Saat kamu meminta Token JWT untuk mengakses cloud dosen (baik melalui Postman maupun untuk dikirim via Swagger), gunakan kredensial milik kelompok 9 ini:
Email: warga09@ktp.iae.id
Password: KtpDigital2026!
2. Identitas SOAP XML (Modul 2)
Pastikan kode di dalam file app/Services/IaeIntegrationService.php milikmu sudah mengirimkan request ke sistem legacy audit dengan tag identitas tim yang benar:
XML
<TeamID>TEAM-09</TeamID>
3. Header API Key Lokal
Untuk layanan krs-service milikmu, pastikan Header X-IAE-KEY yang kamu kirimkan saat melakukan pengujian di Swagger diisi dengan NIM-mu yang sebenarnya, yaitu 102022400068.
Eksekusi Final:
Ambil token baru menggunakan akun warga09.
Pastikan kode Laravel sudah diubah menggunakan TEAM-09.
Lakukan satu kali hit sukses ke endpoint POST /api/v1/krs/submit menggunakan token baru tersebut (jangan lupa ganti parameter course_id dengan ID kelas yang belum pernah kamu input agar tidak terkena error duplikasi SQLite).
Setelah kamu mendapatkan respons 201 Created dari hit ini, sistem dosen akan langsung memperbarui Activity Dashboard dan mencetak skor riwayat integrasi yang sah atas nama kelompokmu. Jangan lupa lakukan git push setelah semua perbaikan angka kelompok ini selesai. ini ngehitnya dimana apakah postman atau apa dan bagaimana stepnya
tapi warga xx disinikan tidak mengindikasikan bahwa ini adalah nomor kelompok

akun warga: warga27@ktp.iae.id & API-KEY: KEY-MHS-156 oh ternyata ini. dan tolong buatkan promt ataukah saya harus hit dulu lewat postmab

rabit mq soap dan satunya bagaimana ?
saya ga paham deh ini bagaimana data berjalan secara sequentialnya
berrti dosen tuh membuat banyak api legacy ya untuk menguji ini doang??
Di industri—terutama jika nanti kamu masuk ke lingkungan perusahaan dengan ekosistem teknologi yang kompleks seperti Finfra, Agate, atau Ecoxyztem—sebuah aplikasi tidak akan pernah berdiri sendiri. Pasti ada kebutuhan untuk berkomunikasi dengan sistem otentikasi pusat (SSO), bertukar data antar divisi secara asinkron (RabbitMQ), dan melapor ke sistem pencatatan tua yang belum bisa dipensiunkan (SOAP).  nah di point ini saya sungguh tidak bisa menangkap apa yang terjadi. tidak seperti ketika kita membuat web dan menyentuh navbar maka route akan melanjutkan sesuai rute. tapi disini saya tidak kebayang seperti ini. tolong buat saya paham. jelaskan dulu apa itu sso rabbit mq dan soap

nah analoginya sudah. sekarang saya ingin kamu menunjukan kodingannya, file di daerah mana (saya menggunakan laravel) dan teknis lainnya

nah jika sejauh ini rabbit mq kan bertugas untuk memberikan suara tanpa dia menunggu atau memverifikasi apakah penerima itu menerima pesannya dia. bagaimana kalau aplikasi yang dituju crash?
loh ini bergantung banget dengan rabbit mq ya?  mitigasi untuk rabbit mq  yang crash gimana? atau jika rabbit mq kewalahan karena menerima pesan banyak sehingga queue nya kacau bagaimana?

kalau seperti ini data krs dan data data lainnya bersebaran dong ya?
buatkan rekap untuk semua percakapan kita disini
