# KRS Service API (Layanan Registrasi Kartu Rencana Studi)

Project ini adalah Tugas 2 Integrasi Aplikasi Enterprise (IAE) berupa backend service berbasis **framework Laravel modern** yang menyediakan RESTful API untuk mengelola katalog mata kuliah, sisa kuota, serta pengajuan Kartu Rencana Studi (KRS) mahasiswa.

## Fitur Utama

1. **Autentikasi API Key**: Semua endpoint dilindungi oleh middleware kustom `ApiKeyMiddleware` yang mencocokkan header `X-IAE-KEY` dengan nilai yang terkonfigurasi di file `.env`.
2. **Katalog Mata Kuliah**: Endpoint untuk melihat daftar mata kuliah beserta jumlah SKS dan sisa kuota yang dinamis.
3. **Pendaftaran KRS**: Memungkinkan mahasiswa mendaftar kelas dengan validasi otomatis:
   - Mahasiswa harus terdaftar di database.
   - Kelas harus tersedia di database.
   - Mahasiswa tidak dapat mengambil mata kuliah yang sama lebih dari satu kali (duplikasi data dicegah melalui unique constraint database & validasi aplikasi).
4. **Pencegahan Race Condition**: Menggunakan fitur **Pessimistic Locking (`lockForUpdate()`)** dalam **Database Transaction** saat proses registrasi KRS dilakukan untuk menjamin sisa kuota kelas berkurang secara akurat meskipun diakses secara bersamaan (concurrency).
5. **Dokumentasi API Terintegrasi (Swagger/OpenAPI)**: Dokumentasi API langsung ditulis menggunakan PHP Attributes di level controller dan dapat diakses secara visual menggunakan Swagger UI.

---

## Struktur Database & Model

Database menggunakan **SQLite** (`database/database.sqlite`) dengan tabel-tabel utama sebagai berikut:

### 1. Students (`students`)
Menyimpan informasi mahasiswa.
*   `id` (String / NIM) - **Primary Key**
*   `name` (String) - Nama Mahasiswa
*   `created_at` / `updated_at` (Timestamp)

### 2. Courses (`courses`)
Menyimpan katalog mata kuliah yang ditawarkan.
*   `id` (BigInt) - **Primary Key**
*   `code` (String, Unique) - Kode Mata Kuliah (contoh: `IF-101`)
*   `name` (String) - Nama Mata Kuliah
*   `credits` (Integer) - Jumlah SKS
*   `quota` (Integer) - Kapasitas Kelas
*   `remaining_quota` (Integer) - Sisa Kapasitas Kelas
*   `created_at` / `updated_at` (Timestamp)

### 3. KRS Items (`krs_items`)
Menyimpan transaksi pengambilan mata kuliah oleh mahasiswa.
*   `id` (BigInt) - **Primary Key**
*   `student_id` (String, Foreign Key -> `students.id`)
*   `course_id` (BigInt, Foreign Key -> `courses.id`)
*   `status` (String, default: `draft`) - Status KRS (contoh: `submitted`)
*   `created_at` / `updated_at` (Timestamp)
*   *Constraint*: Unique key gabungan `[student_id, course_id]` untuk menghindari duplikasi kelas yang sama oleh mahasiswa yang sama.

---

## Struktur Direktori Utama & File Penting

Berikut adalah beberapa file penting dalam project ini:

*   **`routes/api.php`**: Berisi definisi rute API dengan prefix `/v1` dan diproteksi oleh middleware `api.key`.
*   **`app/Http/Middleware/ApiKeyMiddleware.php`**: Logika validasi header `X-IAE-KEY`.
*   **`app/Http/Controllers/Api/V1/KrsController.php`**: Controller utama yang menangani logic API dan berisi anotasi Swagger/OpenAPI.
*   **`app/Models/`**:
    *   `Student.php` - Model Mahasiswa.
    *   `Course.php` - Model Mata Kuliah.
    *   `KrsItem.php` - Model Transaksi KRS.
*   **`database/migrations/`**: Berisi berkas migrasi pembuatan tabel database.
*   **`database/seeders/`**: Berisi seeder awal untuk memasukkan data mahasiswa dan mata kuliah bawaan (default seed data).

---

## Daftar API Endpoints

Semua endpoint memerlukan header autentikasi berikut:
*   `X-IAE-KEY`: `<nilai_kunci_dari_env>` (default: `102022400068`)

### 1. Mendapatkan Daftar Mata Kuliah
*   **HTTP Method**: `GET`
*   **Path**: `/api/v1/courses`
*   **Respons Sukses (200 OK)**:
    ```json
    {
      "status": "success",
      "message": "Courses retrieved successfully",
      "data": [
        {
          "id": 1,
          "code": "IF-101",
          "name": "Pemrograman Dasar",
          "credits": 3,
          "quota": 30,
          "remaining_quota": 30,
          "created_at": "2026-06-02T07:50:50.000000Z",
          "updated_at": "2026-06-02T07:50:50.000000Z"
        }
      ],
      "meta": {
        "count": 1
      }
    }
    ```

### 2. Melihat Draf KRS Mahasiswa
*   **HTTP Method**: `GET`
*   **Path**: `/api/v1/krs/{student_id}`
*   **Respons Sukses (200 OK)**:
    ```json
    {
      "status": "success",
      "message": "KRS draft retrieved successfully",
      "data": {
        "student": {
          "id": "102022400068",
          "name": "Galih Hirpana"
        },
        "items": [
          {
            "id": 1,
            "course": {
              "id": 1,
              "code": "IF-101",
              "name": "Pemrograman Dasar",
              "credits": 3
            },
            "status": "submitted",
            "created_at": "2026-06-02T07:51:00.000000Z"
          }
        ]
      },
      "meta": {
        "total_courses": 1,
        "total_credits": 3
      }
    }
    ```

### 3. Mengajukan/Mendaftarkan KRS (Submit KRS)
*   **HTTP Method**: `POST`
*   **Path**: `/api/v1/krs/submit`
*   **Request Body (JSON)**:
    ```json
    {
      "student_id": "102022400068",
      "course_id": 1
    }
    ```
*   **Respons Sukses (201 Created)**:
    ```json
    {
      "status": "success",
      "message": "KRS submitted successfully",
      "data": {
        "id": 1,
        "student_id": "102022400068",
        "course": {
          "id": 1,
          "code": "IF-101",
          "name": "Pemrograman Dasar",
          "credits": 3,
          "remaining_quota": 29
        },
        "status": "submitted"
      },
      "meta": {
        "timestamp": "2026-06-02T07:51:00.000000Z"
      }
    }
    ```

---

## Dokumentasi & Penggunaan GraphQL

Selain RESTful API, service ini juga mendukung GraphQL untuk fleksibilitas query dari sisi klien. GraphQL diimplementasikan menggunakan package **Lighthouse GraphQL** untuk Laravel.

### Endpoint Utama
*   **GraphQL Endpoint**: `/graphql`
*   **GraphiQL Playground**: `/graphiql` (dapat diakses pada *environment* lokal/development untuk melakukan testing query secara interaktif)

### Contoh Query GraphQL
Klien dapat meminta field spesifik sesuai dengan kebutuhan mereka secara dinamis. Berikut adalah contoh query untuk mengambil daftar mata kuliah (*courses*) beserta sisa kuotanya:

#### Query
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

#### Respons (JSON)
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

## Cara Instalasi & Menjalankan Proyek

### Prerequisites
Pastikan Anda sudah menginstal **PHP (>= 8.3)** dan **Composer** pada sistem Anda.

### 1. Clone & Instalasi Dependensi
Jalankan perintah berikut untuk menginstal package yang diperlukan:
```bash
composer install
```

### 2. Konfigurasi Environment File
Salin file `.env.example` menjadi `.env`:
```bash
copy .env.example .env
```
Pastikan variabel `IAE_KEY` sudah terdefinisi di dalam file `.env`:
```env
IAE_KEY=102022400068
```

### 3. Generate Application Key
```bash
php artisan key:generate
```

### 4. Setup Database SQLite & Seed Data
Secara default, Laravel dikonfigurasi untuk menggunakan SQLite. Pastikan file database telah dibuat, lalu jalankan migrasi dan seeder:
```bash
# Membuat file database sqlite jika belum ada
touch database/database.sqlite

# Menjalankan migrasi database beserta pengisian data awal (seeder)
php artisan migrate --seed
```

### 5. Generate Dokumentasi Swagger/OpenAPI
Untuk mengompilasi anotasi OpenAPI menjadi dokumen JSON Swagger, jalankan perintah:
```bash
php artisan l5-swagger:generate
```

### 6. Menjalankan Server Development
Jalankan web server lokal menggunakan perintah Artisan:
```bash
php artisan serve
```
Setelah server berjalan, Anda dapat mengakses:
*   **API Base URL**: `http://localhost:8000/api`
*   **Swagger UI (Dokumentasi API)**: [http://localhost:8000/api/documentation](http://localhost:8000/api/documentation)
