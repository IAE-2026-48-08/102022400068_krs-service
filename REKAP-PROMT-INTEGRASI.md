Judul: VALIDASI DAN PERSETUJUAN PENGAMBILAN MATA KULIAH (KRS)
Service Data Mahasiswa, Service Mata Kuliah & KRS, dan Service Nilai & Kurikulum

1. Aktivitas Verifikasi Status (Service Data Mahasiswa):

*  Service ini menerima kiriman data NIM dari Service Mata Kuliah & KRS ketika mahasiswa melakukan submit.

* Berdasarkan NIM tersebut, service ini memeriksa daftar global mahasiswa aktif di dalam databasenya sendiri, kemudian memfilter data profil spesifik mahasiswa tersebut untuk memastikan statusnya mutlak "Aktif" serta mengembalikan data jatah maksimum SKS-nya kembali ke Service Mata Kuliah & KRS. 

2. Aktivitas Validasi Kapasitas Kelas (Service Mata Kuliah & KRS): 

* Service ini mengambil data draf pilihan mata kuliah yang dikirimkan oleh Aplikasi Portal Mahasiswa (Frontend). 

* Sistem kemudian mencocokkan kode mata kuliah yang diambil tersebut dengan data daftar kelas yang dibuka pada databasenya sendiri untuk mengecek apakah sisa kuota bangku masih tersedia dan jam kuliahnya tidak saling bentrok.

3. Aktivitas Pemeriksaan Prasyarat Akademis (Service Nilai & Kurikulum): 

* Service ini menerima request pengecekan dari Service Mata Kuliah & KRS yang membawa data NIM dan Kode Mata Kuliah pilihan. 

* Berdasarkan data kiriman tersebut, service ini memeriksa struktur aturan kurikulum program studi dan transkrip historis nilai mahasiswa yang ada di databasenya sendiri untuk memastikan mahasiswa tersebut sudah lulus mata kuliah prasyarat dengan nilai aman, lalu mengirimkan status "Lolos/Tidak" kembali ke Service Mata Kuliah & KRS. 

4. Aktivitas Finalisasi Kontrak Mata Kuliah (Service Mata Kuliah & KRS ): 

* Setelah menerima konfirmasi sukses dari hasil validasi status mahasiswa dan prasyarat nilai, service ini mengunci dan menyimpan data transaksi KRS mahasiswa ke database KRS miliknya.

*  lalu mengirimkan perintah HTTP POST berisi data NIM dan Kode MK ke Service Nilai & Kurikulum agar service tersebut membuatkan baris data (record) nilai kosong baru sebagai penutup siklus. 

 
1. SERVICE DATA MAHASISWA (D Hans Dhika Slamet)
Resource Name: students

*  Collection:  GET /api/v1/students  (Mengambil seluruh daftar mahasiswa aktif untuk sinkronisasi data kuota awal semester).

* Resource:  GET /api/v1/students/{id}  (Mengambil detail profil statis mahasiswa seperti Nama, NIM, dan Status Aktif/Tidak untuk verifikasi identitas dasar).

* Action:  POST /api/v1/students/validate-quota  (Memproses validasi logika bisnis dengan menerima input jumlah SKS yang diajukan via request body, lalu menghitung kecukupan sisa kuota mahasiswa secara real-time untuk menentukan eligibility pengambilan KRS).

2. SERVICE MATA KULIAH & KRS (Galih Hirpana)
Resource Name: krs

* Collection: GET /api/v1/courses (Menampilkan daftar mata kuliah beserta sisa kuota kapasitas kelas yang dibuka pada semester berjalan).

* Resource: GET /api/v1/krs/{student_id} (Menampilkan detail isi draf pilihan kelas milik mahasiswa tertentu untuk pengecekan bentrok jadwal).

* Action: POST /api/v1/krs/submit (Membuat transaksi pengajuan kontrak mata kuliah dan mengubah status draf KRS menjadi "Terkunci/Disetujui").

3. SERVICE NILAI & KURIKULUM (Muhammad Manhal Syarifudin)
Resource Name: grades

* Collection: GET /api/v1/curriculums (Menampilkan daftar aturan prasyarat kurikulum program studi untuk mendeteksi keterikatan antar mata kuliah).

* Resource: GET /api/v1/grades/{student_id} (Menampilkan detail riwayat transkrip nilai mahasiswa untuk pembuktian kelulusan mata kuliah prasyarat).

* Action: POST /api/v1/grades/initialize (Membuat baris data (record) nilai baru yang masih kosong di database nilai setelah menerima perintah finalisasi dari Service Mata Kuliah & KRS).

  setellah membuat 3 aplikasi ini saya diminta untuk menyatukannya. nah sekarang kita akan menulis api endpoint untuk probis ini.



loh bukannya jika berbeda port masih belum terintegrasi? soalnya kan kita menggunakan docker 

apakah disini masih menggunakan rabbit mq ?

saya belum ada rabbitmq sih soalnya kemarin pun dibebankan ke server dosen

apakah bisa jika rabbit mq implan di service data mahasiswa

baiklah kita akan tembak  langsung ke endpoint http



Rekap Sesi Prompting: Finalisasi & Audit Keamanan Arsitektur Microservices (TEAM-09)
Dokumen ini mencatat interaksi dan penggunaan AI Assistant dalam tahap finalisasi Tugas Besar Integrasi Aplikasi Enterprise (IAE). Fokus utama pada sesi ini adalah pemenuhan rubrik penilaian, migrasi gateway, penambalan celah keamanan, dan penyusunan dokumentasi.

1. Penyelesaian Dokumen Administratif
Masalah: Mengidentifikasi kekurangan dokumen wajib untuk penilaian individu (33,33%) pada direktori krs-service.

Prompt/Tindakan: Meminta AI membuat draf analisis_tugas_3.md yang berisi justifikasi state-changing pada proses pendaftaran KRS (menggunakan Pessimistic Locking) dan Sequence Diagram interaksi sistem dengan Tritunggal Pusat (SSO, SOAP, RabbitMQ).

Hasil: Dokumen analisis_tugas_3.md berhasil ditambahkan. Selanjutnya, AI membantu menyusun draf RESUME_KONTRIBUSI.md dengan bahasa teknis yang kemudian disederhanakan agar lebih membumi dan aman saat sesi tanya-jawab dengan dosen penguji.

2. Migrasi API Gateway (Sesuai Rubrik Institusi)
Masalah: Terdeteksi adanya dua arsitektur gateway (Laravel dan Nginx). Penggunaan Laravel berisiko melanggar spesifikasi rubrik yang mewajibkan Nginx/Kong.

Prompt/Tindakan: Menginstruksikan AI untuk bertindak sebagai Lead Systems Architect dan men- translate fitur keamanan dari Laravel ke Nginx (nginx.conf). Instruksi mencakup Anti-Header Stripping (meneruskan X-IAE-KEY, Authorization, dll), Timeout Prevention, dan CORS Handling.

Hasil: Direktori 102022400068_api-gateway (Laravel) dihapus secara permanen. File docker-compose.yml diperbarui untuk mem-build Nginx dari folder api-gateway/ sebagai Single Point of Entry.

3. Audit Keamanan Port (Anti-Bypass Gateway)
Masalah: Pemeriksaan menemukan celah keamanan di mana layanan internal (krs-service di port 8001) masih terekspos ke mesin host, memungkinkan asisten lab melakukan bypass pada Nginx.

Prompt/Tindakan: Meminta AI melakukan audit DevSecOps dengan aturan ketat: "Zero External Ports for Backend Services".

Hasil: Blok ports: pada seluruh service internal dihapus dari docker-compose.yml. Kini hanya api-gateway yang terbuka di port eksternal (8000:80). Komunikasi antar-service murni terjadi di dalam team09_network.

4. Audit Ketahanan Sistem & Pembuatan E2E Guide
Masalah: Memastikan sistem tidak crash total (Cascading Failure) jika server pihak ketiga (SOAP/RabbitMQ dosen) down atau lambat merespons.

Prompt/Tindakan: Menggunakan prompt audit "Principal Enterprise Architect" untuk memvalidasi penanganan exception dan penambahan batas waktu (timeout). Selain itu, meminta AI untuk men- generate urutan pengujian cURL untuk skenario End-to-End (Profil -> KRS -> Grades).

Hasil: Sistem divalidasi dengan status Ready for Production. Panduan pengujian cURL lengkap dengan Sequence Diagram berhasil dirangkum dan diintegrasikan ke dalam halaman utama README.md