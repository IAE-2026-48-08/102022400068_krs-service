Judul: VALIDASI DAN PERSETUJUAN PENGAMBILAN MATA KULIAH (KRS)
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
 
1. SERVICE DATA MAHASISWA (D Hans Dhika Slamet)
Resource Name: students
Collection:  GET /api/v1/students (Mengambil seluruh daftar mahasiswa aktif untuk sinkronisasi data kuota awal semester).
Resource:  GET /api/v1/students/{id} (Mengambil detail profil statis mahasiswa seperti Nama, NIM, dan Status Aktif/Tidak untuk verifikasi identitas dasar).
Action:  POST /api/v1/students/validate-quota (Memproses validasi logika bisnis dengan menerima input jumlah SKS yang diajukan via request body, lalu menghitung kecukupan sisa kuota mahasiswa secara real-time untuk menentukan eligibility pengambilan KRS).

2. SERVICE MATA KULIAH & KRS (Galih Hirpana)
Resource Name: krs
Collection: GET /api/v1/courses (Menampilkan daftar mata kuliah beserta sisa kuota kapasitas kelas yang dibuka pada semester berjalan).
Resource: GET /api/v1/krs/{student_id} (Menampilkan detail isi draf pilihan kelas milik mahasiswa tertentu untuk pengecekan bentrok jadwal).
Action: POST /api/v1/krs/submit (Membuat transaksi pengajuan kontrak mata kuliah dan mengubah status draf KRS menjadi "Terkunci/Disetujui").
3. SERVICE NILAI & KURIKULUM (Muhammad Manhal Syarifudin)
Resource Name: grades
Collection: GET /api/v1/curriculums (Menampilkan daftar aturan prasyarat kurikulum program studi untuk mendeteksi keterikatan antar mata kuliah).
Resource: GET /api/v1/grades/{student_id} (Menampilkan detail riwayat transkrip nilai mahasiswa untuk pembuktian kelulusan mata kuliah prasyarat).
Action: POST /api/v1/grades/initialize (Membuat baris data (record) nilai baru yang masih kosong di database nilai setelah menerima perintah finalisasi dari Service Mata Kuliah & KRS). ini adalah punya kelompok kita
Kamu adalah Bertindaklah sebagai Senior Backend Engineer dan Software Architect.
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
