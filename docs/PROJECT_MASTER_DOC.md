# PROJECT MASTER DOC

## 0. Document Control

- Document name: `PROJECT_MASTER_DOC.md`
- Function: single source of truth untuk keputusan sistem, arsitektur, workflow, dan arah implementasi portal berita modern.
- Project working title: `TodakSiring`
- Branding rule: nama media harus fleksibel dan tidak di-hardcode. Untuk fase awal gunakan env:
  - `APP_NAME="TodakSiring"`
  - `APP_BRAND_NAME="TodakSiring"`
- Current repository state at document revision:
  - Workspace sudah menjadi aplikasi Laravel 13 baseline
  - Composer dependency utama sudah terpasang
  - Frontend baseline sudah terpasang dengan Vite, Tailwind CSS v4, Alpine.js, dan Trix
  - Belum ada database migration inti proyek yang diimplementasikan
- Document status: `ACTIVE`
- Last updated: `2026-04-02`
- Owner role: `Tech Lead / Senior Software Engineer / AI Executor`

## 1. Project Overview

### 1.1 Nama Project

`TodakSiring` adalah nama kerja awal untuk portal berita modern berbasis web yang berorientasi mobile, SEO, workflow editorial ketat, monetisasi, dan skalabilitas implementasi.

### 1.2 Prinsip Branding

- Nama brand harus fleksibel dan dikendalikan dari `.env`
- Tidak boleh ada hardcode nama media di view, config, seeder, atau service
- Semua elemen identitas publik harus mengambil nilai dari konfigurasi aplikasi
- Jika rebranding terjadi, perubahan utama cukup dilakukan melalui env dan setting administratif

### 1.3 Target User

#### A. Pembaca Umum

- Mayoritas akses dari perangkat mobile
- Menginginkan halaman cepat, ringan, mudah dibaca
- Fokus pada berita lokal, regional, dan nasional
- Perilaku utama:
  - membuka homepage
  - membaca headline
  - membuka detail artikel
  - mencari berita
  - membaca berita per kategori
  - memberi komentar bila fitur aktif
  - berlangganan newsletter atau notifikasi

#### B. Tim Redaksi

- Mengelola operasi editorial
- Mengatur artikel, kategori, komentar, user, dan setting situs
- Membutuhkan panel admin yang stabil, jelas, aman, dan mudah diaudit
- Membutuhkan workflow approval yang disiplin agar kualitas konten terjaga

### 1.4 Tujuan Bisnis

- Membangun traffic organik tinggi melalui SEO, performa, dan struktur konten rapi
- Mendukung monetisasi multi-channel:
  - Google Adsense
  - iklan langsung
  - affiliate
  - sponsored post
  - newsletter
  - push notification
- Menjaga biaya operasional efisien agar tetap layak di shared hosting dan dapat naik ke VPS/cloud
- Menyediakan fondasi untuk ekspansi AI-assisted editorial di masa depan

### 1.5 Prinsip Produk

- Mobile first
- Clean reading experience
- Fokus pada performa, SEO, dan kemudahan editorial
- Semua keputusan teknis harus mengacu ke dokumen ini
- Kode mengikuti docs, bukan sebaliknya

## 2. User Role System

Sistem role harus sederhana, tegas, dan mudah diaudit. Role disimpan pada tabel `users.role`.

### 2.1 Daftar Role

Sistem hanya memiliki 3 role internal:

#### 1. Admin

- Full akses panel admin
- Kelola user dan role
- Kelola artikel, kategori, komentar, ads, dan setting global
- Bisa approve, publish, unpublish, archive, dan override workflow bila diperlukan
- Bisa melihat log administratif dan log error tingkat aplikasi

#### 2. Editor

- Review kualitas artikel
- Edit artikel yang masuk ke redaksi
- Memberi catatan revisi
- Approve artikel untuk publish atau jadwal publish
- Tidak boleh mengubah setting global sensitif dan user role

#### 3. Wartawan

- Membuat artikel
- Menyimpan draft
- Mengubah artikel milik sendiri selama belum published
- Mengajukan artikel ke review
- Tidak bisa publish langsung

### 2.2 Permission Matrix Ringkas

| Fitur                 | Admin | Editor   | Wartawan |
| --------------------- | ----- | -------- | -------- |
| Kelola setting global | Ya    | Tidak    | Tidak    |
| Kelola user           | Ya    | Tidak    | Tidak    |
| Buat artikel          | Ya    | Ya       | Ya       |
| Edit semua artikel    | Ya    | Ya       | Tidak    |
| Edit artikel sendiri  | Ya    | Ya       | Ya       |
| Submit review         | Ya    | Ya       | Ya       |
| Approve artikel       | Ya    | Ya       | Tidak    |
| Publish artikel       | Ya    | Ya       | Tidak    |
| Hapus artikel         | Ya    | Terbatas | Tidak    |
| Kelola komentar       | Ya    | Ya       | Tidak    |

### 2.3 Aktor Publik Non-Role

Pembaca publik bukan role sistem. Pembaca adalah aktor non-auth yang dapat:

- mengakses halaman publik
- membaca artikel
- melakukan pencarian
- melihat kategori
- memberi komentar bila fitur komentar aktif
- berlangganan newsletter atau push notification bila tersedia

### 2.4 Status Artikel

Status inti artikel:

- `draft`
- `review`
- `published`

Status tambahan operasional:

- penolakan ditangani melalui `review_notes` dan pengembalian ke `draft`
- arsip ditangani melalui `archived_at`

## 3. Tech Stack

Dokumen ini mengunci target stack implementasi awal.

### 3.1 Core Stack

- Framework: `Laravel 13.x`
- Language: `PHP 8.3+`
- Recommended runtime: `PHP 8.4.x`
- Frontend utility CSS: `Tailwind CSS v4`
- Frontend interactivity: `Alpine.js v3`
- Bundler: `Vite`
- Database: `MySQL`
- Cache / queue support: `Redis`

### 3.2 Tambahan Wajib

- `PWA`
- `AMP`
- `RSS`
- `OneSignal`

### 3.3 Implementasi Stack yang Disarankan

- Laravel 13 sebagai baseline resmi proyek
- Gunakan fitur AI yang tersedia di ekosistem Laravel 13 untuk workflow editorial terkontrol
- PHP minimum kompatibilitas: `8.3`
- PHP direkomendasikan untuk produksi baru: `8.4`
- MySQL yang direkomendasikan:
  - minimum `8.0`
  - ideal `8.4 LTS` bila hosting mendukung
- Redis untuk:
  - cache
  - session bila memungkinkan
  - queue di environment yang mendukung worker
- Jika shared hosting belum mendukung Redis, sistem tetap harus berjalan dengan fallback:
  - cache driver: `file`
  - session driver: `file` atau `database`
  - queue driver: `database`

### 3.4 Strategi AI Laravel 13

Fitur AI harus digunakan sebagai alat bantu editorial, bukan autopublish engine.

Target penggunaan:

- generate draft artikel dari brief atau keyword
- generate alternatif judul
- generate excerpt
- generate meta description
- membantu klasifikasi kategori awal
- membantu menyusun ringkasan editorial internal

Aturan wajib:

- output AI masuk ke `draft` atau `review`, tidak pernah langsung `published`
- semua output AI harus melalui human review
- prompt, sumber konteks, dan hasil AI penting harus bisa dicatat bila fitur ini diimplementasikan
- implementasi AI harus dibungkus service layer agar mudah diganti model/provider

### 3.5 Mail System

- Mail driver fase awal: `SMTP`
- Harus kompatibel dengan shared hosting
- Untuk produksi direkomendasikan memakai layanan transaksional:
  - `Mailgun`
  - `Resend`
  - `Brevo`
- Antrian pengiriman email wajib memakai Laravel Queue
- Jika Redis tidak tersedia, queue driver memakai `database`
- Email tidak boleh dikirim synchronous di request cycle
- Tipe email yang dikirim sistem:
  - konfirmasi subscriber
  - notifikasi editorial internal sebagai opsi fase awal
- Konfigurasi mail wajib melalui `.env`, tidak boleh hardcode

### 3.6 Feature Flag System

Feature flag wajib tersedia untuk mengaktifkan atau menonaktifkan fitur tertentu tanpa mengubah kode inti.

Flag minimum fase awal:

- `AMP on/off`
- `AI on/off`
- `Comment on/off`

Prinsip:

- konfigurasi feature flag harus tersentralisasi
- keputusan fase awal:
  - feature flag disimpan di `settings` table
  - config hanya dipakai sebagai fallback default
- perubahan flag tidak boleh membutuhkan redeploy besar untuk kasus operasional sederhana
- perubahan flag operasional harus bisa dilakukan tanpa edit file aplikasi

### 3.7 Prinsip Pemilihan Stack

- harus stabil
- harus bisa hidup di shared hosting
- harus bisa di-upgrade ke VPS/cloud tanpa rewrite besar
- harus SEO-friendly
- harus mendukung workflow editorial dan monetisasi

## 4. Arsitektur Sistem

### 4.1 Gambaran Umum

Sistem dibagi menjadi empat lapisan utama:

1. Frontend publik
2. Admin panel
3. API layer
4. Service layer

### 4.2 Flow Utama

`User -> Route -> Controller -> Service -> Model/Repository -> Database -> Response`

### 4.3 Frontend

Tanggung jawab frontend publik:

- homepage
- detail artikel
- halaman kategori
- halaman pencarian
- halaman AMP
- halaman RSS
- landing PWA
- komponen langganan newsletter / push notif

Prinsip:

- SSR-first melalui Blade
- JavaScript ringan, hanya untuk interaksi yang benar-benar perlu
- Alpine.js dipakai untuk komponen kecil
- Hindari SPA penuh pada fase awal

### 4.3.1 Pagination Strategy

- Gunakan offset-based pagination melalui `Laravel paginate()` untuk fase awal
- Default per-page: `15` artikel untuk halaman kategori dan pencarian
- Homepage tidak memakai pagination
- Homepage hanya mengambil `N` artikel teratas per section
- URL pagination menggunakan query string `?page=N`
- Cursor-based pagination hanya dipertimbangkan jika performa offset pagination menjadi masalah nyata di data besar

### 4.4 Admin Panel

Tanggung jawab:

- dashboard ringkas
- manajemen artikel
- manajemen kategori
- manajemen role user
- moderasi komentar
- manajemen ads
- setting situs
- pengaturan SEO dasar
- pengaturan AI-assisted workflow

### 4.5 API

API dipakai untuk:

- integrasi PWA
- endpoint AJAX terbatas
- feed internal
- integrasi OneSignal
- kemungkinan mobile app di masa depan

Aturan:

- API publik hanya untuk data aman
- API admin harus pakai auth
- rate limit wajib aktif

### 4.5.1 Rate Limiting Strategy

- Gunakan Laravel built-in throttle middleware
- Rate limit endpoint publik: `60 request per menit per IP`
- Rate limit endpoint auth login: `10 request per menit per IP`
- Rate limit endpoint API: `30 request per menit per token atau IP`
- Konfigurasi throttle didefinisikan di `bootstrap/app.php` atau `RouteServiceProvider`
- Shared hosting fallback:
  - jika Redis tidak tersedia, throttle fallback ke cache driver `file`

### 4.6 Service Layer

Service layer wajib dipakai untuk logika bisnis non-trivial:

- publish article
- article approval
- generate slug
- SEO metadata builder
- comment moderation
- ad placement resolver
- AI content generation
- legal scraping pipeline
- translation orchestration
- error normalization untuk response terkontrol

## 5. Folder Structure Final

Struktur ini adalah target final yang harus diikuti saat aplikasi Laravel mulai dibangun.

### 5.1 Struktur Inti

```text
app/
|-- Models/
|-- Services/
|   |-- ArticleService.php
|   |-- SlugService.php
|   |-- TagService.php
|   |-- SEOService.php
|   |-- MediaService.php
|   `-- AIService.php
|-- Http/
|   |-- Controllers/
|   |   |-- Front/
|   |   `-- Admin/
|   |-- Requests/
|   `-- Middleware/
|-- Policies/
|-- Enums/
|-- Actions/
|-- Jobs/
|-- Notifications/
`-- Support/
```

### 5.2 Struktur Proyek yang Direkomendasikan

```text
app/
bootstrap/
config/
database/
|-- factories/
|-- migrations/
`-- seeders/
public/
resources/
|-- views/
|   |-- frontend/
|   |-- admin/
|   `-- components/
|-- css/
|-- js/
`-- lang/
routes/
|-- web.php
|-- admin.php
|-- api.php
|-- console.php
|-- rss.php
`-- amp.php
storage/
tests/
docs/
|-- PROJECT_MASTER_DOC.md
|-- CHANGELOG.md
|-- RECOVERY.md
`-- DECISIONS/
```

### 5.3 Aturan Struktur Folder

- controller frontend publik ditempatkan di `app/Http/Controllers/Front`
- controller admin ditempatkan di `app/Http/Controllers/Admin`
- seluruh logika bisnis non-trivial ditempatkan di `app/Services`
- `ArticleService.php` menangani create, update, submit review, publish, archive
- `SlugService.php` menangani auto-generate dan duplicate slug
- `TagService.php` menangani normalisasi tag, auto-generate slug tag, duplicate handling slug tag, dan sinkronisasi relasi artikel-tag
- `SEOService.php` menangani meta dan schema builder
- `MediaService.php` menangani upload, resize, hashing filename, dan path storage media
- `AIService.php` menangani integrasi AI terkontrol
- request validation dipisah ke `app/Http/Requests`
- authorization berbasis objek dipisah ke `app/Policies`

## 6. Database Design

Bagian ini adalah desain database target. Baseline ini belum berarti sudah diimplementasikan.

### 6.1 Prinsip Database

- gunakan InnoDB
- gunakan `utf8mb4`
- semua foreign key jelas
- beri index pada kolom pencarian dan relasi
- dukung audit minimal dengan timestamps
- slug harus unik bila tampil di publik
- status dan role harus konsisten dengan docs

### 6.2 Relasi Utama

- `users` 1..n `articles`
- `categories` 1..n `articles`
- `categories` self-reference untuk nested category
- `articles` 1..n `comments`
- `articles` many-to-many `tags`
- `settings` key-value global

### 6.3 Entitas Final Fase Awal

Entitas final minimum fase awal:

- `users`
- `categories`
- `articles`
- `comments`
- `ads`
- `settings`
- `subscribers`
- `tags`
- `article_tags`

Catatan role database:

- `users.role` hanya boleh berisi:
  - `admin`
  - `editor`
  - `wartawan`

Catatan artikel:

- `articles.slug` wajib unik
- `articles.status` minimal memakai:
  - `draft`
  - `review`
  - `published`
- butuh kolom berikut untuk mendukung workflow:
  - `review_notes`
  - `published_at`
  - `archived_at`
  - `created_by_ai` opsional bila fitur AI dilacak eksplisit

### 6.4 Database SQL Final

SQL ini adalah baseline final fase awal untuk desain tabel inti.

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('admin', 'editor', 'wartawan') NOT NULL DEFAULT 'wartawan',
    avatar VARCHAR(255) NULL,
    phone VARCHAR(30) NULL,
    bio TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at TIMESTAMP NULL,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_is_active (is_active),
    INDEX idx_users_last_login_at (last_login_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    seo_title VARCHAR(255) NULL,
    seo_description VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_categories_parent
        FOREIGN KEY (parent_id) REFERENCES categories(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_categories_parent_id (parent_id),
    INDEX idx_categories_is_active (is_active),
    INDEX idx_categories_sort_order (sort_order)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE articles (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id BIGINT UNSIGNED NOT NULL,
    category_id BIGINT UNSIGNED NOT NULL,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(280) NOT NULL UNIQUE,
    excerpt TEXT NULL,
    content LONGTEXT NOT NULL,
    featured_image VARCHAR(255) NULL,
    status ENUM('draft', 'review', 'published') NOT NULL DEFAULT 'draft',
    review_notes TEXT NULL,
    meta_title VARCHAR(255) NULL,
    meta_description VARCHAR(255) NULL,
    schema_type VARCHAR(100) NOT NULL DEFAULT 'NewsArticle',
    views_count BIGINT UNSIGNED NOT NULL DEFAULT 0,
    is_featured TINYINT(1) NOT NULL DEFAULT 0,
    created_by_ai TINYINT(1) NOT NULL DEFAULT 0,
    published_at TIMESTAMP NULL,
    archived_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_articles_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_articles_category
        FOREIGN KEY (category_id) REFERENCES categories(id)
        ON DELETE RESTRICT ON UPDATE CASCADE,
    INDEX idx_articles_user_id (user_id),
    INDEX idx_articles_category_id (category_id),
    INDEX idx_articles_status (status),
    INDEX idx_articles_published_at (published_at),
    INDEX idx_articles_featured (is_featured),
    INDEX idx_articles_archived_at (archived_at),
    FULLTEXT INDEX ftx_articles_title_excerpt_content (title, excerpt, content)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE comments (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    article_id BIGINT UNSIGNED NOT NULL,
    user_id BIGINT UNSIGNED NULL,
    parent_id BIGINT UNSIGNED NULL,
    guest_name VARCHAR(150) NULL,
    guest_email VARCHAR(150) NULL,
    content TEXT NOT NULL,
    status ENUM('pending', 'approved', 'rejected', 'spam') NOT NULL DEFAULT 'pending',
    ip_address VARCHAR(45) NULL,
    user_agent VARCHAR(255) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_comments_article
        FOREIGN KEY (article_id) REFERENCES articles(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_comments_user
        FOREIGN KEY (user_id) REFERENCES users(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    CONSTRAINT fk_comments_parent
        FOREIGN KEY (parent_id) REFERENCES comments(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_comments_article_id (article_id),
    INDEX idx_comments_user_id (user_id),
    INDEX idx_comments_parent_id (parent_id),
    INDEX idx_comments_status (status)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE ads (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    placement VARCHAR(100) NOT NULL,
    type ENUM('adsense', 'banner', 'html', 'affiliate', 'sponsored') NOT NULL DEFAULT 'banner',
    code LONGTEXT NULL,
    image_url VARCHAR(255) NULL,
    target_url VARCHAR(255) NULL,
    start_at TIMESTAMP NULL,
    end_at TIMESTAMP NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_ads_placement (placement),
    INDEX idx_ads_type (type),
    INDEX idx_ads_is_active (is_active),
    INDEX idx_ads_start_at (start_at),
    INDEX idx_ads_end_at (end_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE settings (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    `group` VARCHAR(100) NOT NULL DEFAULT 'general',
    `key` VARCHAR(150) NOT NULL UNIQUE,
    `value` LONGTEXT NULL,
    autoload TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_settings_group (`group`),
    INDEX idx_settings_autoload (autoload)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE subscribers (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(150) NOT NULL UNIQUE,
    source VARCHAR(100) NOT NULL DEFAULT 'website',
    status ENUM('pending', 'active', 'unsubscribed') NOT NULL DEFAULT 'pending',
    subscribed_at TIMESTAMP NULL,
    unsubscribed_at TIMESTAMP NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_subscribers_status (status),
    INDEX idx_subscribers_source (source)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE tags (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_tags_name (name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE article_tags (
    article_id BIGINT UNSIGNED NOT NULL,
    tag_id BIGINT UNSIGNED NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    PRIMARY KEY (article_id, tag_id),
    CONSTRAINT fk_article_tags_article
        FOREIGN KEY (article_id) REFERENCES articles(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    CONSTRAINT fk_article_tags_tag
        FOREIGN KEY (tag_id) REFERENCES tags(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_article_tags_tag_id (tag_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6.5 Migration Strategy

Urutan migration wajib mengikuti dependency foreign key agar aman dan deterministik.

Migration Order:

1. `users`
2. `categories`
3. `articles`
4. `tags`
5. `article_tags`
6. `comments`
7. `ads`
8. `settings`
9. `subscribers`

Aturan:

- migration harus dibuat sesuai urutan dependency
- foreign key tidak boleh mengandalkan urutan kebetulan
- semua index penting ditambahkan di migration awal, bukan ditunda
- perubahan schema setelah production harus additive semaksimal mungkin
- `tags` harus dibuat sebelum `article_tags`
- `article_tags` harus dibuat setelah `articles`

### 6.6 Seeder Strategy

Seeder wajib fase awal:

1. Admin default
   - email: `admin@local.test`
   - password: hashed default
2. Editor default
3. Wartawan sample
4. Kategori default:
   - `Berita`
   - `Lokal`
   - `Nasional`
   - `Ekonomi`
5. Setting default:
   - `site_name`
   - `site_description`

Tujuan:

- mempercepat testing
- memastikan sistem langsung usable setelah migrate

### 6.7 Catatan Implementasi Database

- `articles.slug` wajib unik global
- `categories.slug` wajib unik global
- `settings.key` wajib unik
- `articles.status` hanya memakai `draft`, `review`, `published`
- `archived_at` dipakai untuk soft archive operasional
- fulltext index artikel bergantung dukungan engine dan versi MySQL
- bila shared hosting membatasi fitur tertentu, fallback pencarian dapat memakai `LIKE` bertahap pada fase awal

### 6.8 Media & Image Storage Strategy

- Storage driver fase awal: local disk Laravel di `storage/app/public`
- Symlink public storage wajib dijalankan saat deployment:
  - `php artisan storage:link`
- Struktur folder upload:
  - `storage/app/public/articles/{year}/{month}/`
- Batasan upload:
  - maksimal `2MB` per gambar
  - format yang diizinkan: `jpg`, `jpeg`, `png`, `webp`
- Resize otomatis wajib dilakukan sebelum simpan dengan target maksimal `1200px` lebar
- Bila environment mendukung, migrasi ke S3-compatible storage harus dapat dilakukan tanpa perubahan besar karena Laravel filesystem abstraction
- Nama file harus di-hash atau di-randomize
- Nama asli file upload tidak boleh dipakai sebagai nama file final
- Resize image direkomendasikan memakai library image processing yang kompatibel dengan Laravel service layer
- Keputusan awal:
  - gunakan `intervention/image-laravel` untuk resize dan normalisasi dimensi
  - gunakan `spatie/laravel-image-optimizer` untuk optimasi ukuran file setelah resize
  - driver image bergantung dukungan `GD` atau `Imagick`
- Optimasi ukuran file bergantung pada binary optimizer yang tersedia di server seperti `jpegoptim`, `pngquant`, `optipng`, `gifsicle`, `svgo`, dan `cwebp`
- Pada shared hosting, paket Spatie tetap dapat dipasang lebih awal tetapi hasil optimasi maksimal baru tercapai bila binary tersebut tersedia atau dapat diarahkan melalui konfigurasi `binary_path`
- Target operasional adalah gambar tetap terlihat jelas dengan ukuran file sekecil mungkin; hasil seperti `1600x1200 -> 200KB` adalah target optimasi yang mungkin tercapai pada banyak gambar, tetapi bukan jaminan absolut untuk semua sumber file
- Dukungan ekstensi PHP untuk image processing wajib diverifikasi di hosting target
- Seluruh proses upload dan resize ditangani melalui `MediaService`

### 6.9 Tags System

- Tag bersifat opsional pada artikel
- Relasi artikel ke tag adalah many-to-many
- Tabel `tags` menyimpan:
  - `id`
  - `name`
  - `slug`
  - `created_at`
  - `updated_at`
- Tabel `article_tags` menyimpan:
  - `article_id`
  - `tag_id`
- `article_tags` memakai composite primary key pada `article_id` dan `tag_id`
- Pivot `article_tags` secara sengaja memakai `created_at` dan `updated_at`
- Implementasi Eloquent harus konsisten dengan keputusan ini melalui `withTimestamps()`
- `tags.slug` wajib unik global
- slug tag harus auto-generate dari `name`
- admin dan editor boleh membuat serta mengubah slug tag
- jika slug manual kosong, sistem wajib generate otomatis dari `name`
- jika terjadi duplicate slug tag, `TagService` wajib menambahkan suffix numerik secara deterministik
- wartawan tidak mengelola master tag langsung pada fase awal

### 6.10 Views Count Strategy

- Increment `views_count` tidak boleh dilakukan langsung di request cycle secara synchronous
- Strategi fase awal:
  - gunakan cache `file` atau `Redis` untuk buffer hit counter
  - flush ke database via scheduled job setiap `5-15 menit`
- Jika queue tersedia, gunakan Job untuk proses increment
- Unique view per session atau IP tidak diwajibkan di fase awal
- Yang dicatat cukup total hit
- Kolom `views_count` di database tetap menjadi nilai final yang di-sync dari buffer

## 7. Route Structure

Route publik dan admin harus tegas, konsisten, dan SEO-friendly.

### 7.1 Struktur URL Wajib

- `/` -> homepage
- `/berita/{articleSlug}` -> detail artikel
- `/kategori/{categorySlug}` -> halaman kategori
- `/admin/...` -> admin panel
- `/rss.xml` -> feed RSS
- `/amp/...` -> halaman AMP

### 7.2 Detail Route Publik

- `/`
  - menampilkan headline, artikel terbaru, artikel populer, dan kategori utama
- `/berita/{articleSlug}`
  - hanya untuk artikel `published`
  - slug artikel wajib unik global
  - query publik wajib menambahkan kondisi:
    - `published_at <= NOW()`
    - `archived_at IS NULL`
- `/kategori/{categorySlug}`
  - menampilkan daftar artikel published per kategori
  - artikel terarsip tidak boleh tampil
- `/cari`
  - endpoint pencarian frontend bila fitur pencarian diimplementasikan
  - artikel terarsip tidak boleh tampil
- `/rss.xml`
  - feed artikel published terbaru
  - artikel terarsip tidak boleh tampil
- `/amp/berita/{articleSlug}`
  - versi AMP detail artikel
  - artikel terarsip tidak boleh tampil
- route halaman tag publik `/tag/{tagSlug}` tidak diaktifkan pada fase awal
- halaman tag publik masuk fase lanjutan setelah validasi kebutuhan SEO dan UX

### 7.3 Detail Route Admin

Prefix admin wajib:

- `/admin/login`
- `/admin/dashboard`
- `/admin/artikel`
- `/admin/kategori`
- `/admin/komentar`
- `/admin/users`
- `/admin/settings`
- `/admin/logs` bila log viewer administratif diaktifkan

Aturan:

- semua route `/admin/*` wajib diproteksi auth kecuali login
- route admin harus memakai middleware auth dan role
- route sensitif harus dibatasi lebih ketat berdasarkan capability

### 7.4 Archived Article Behavior di Route Publik

- Artikel dengan `archived_at` terisi dianggap tidak aktif secara publik
- Query halaman publik wajib selalu menambahkan kondisi `archived_at IS NULL`
- Artikel yang diarsipkan tidak tampil di homepage, kategori, pencarian, atau RSS
- Akses langsung ke URL artikel yang diarsipkan harus mengembalikan `410 Gone`, bukan `404`

## 8. Auth System Design

Auth harus sederhana, aman, cocok untuk shared hosting, dan cukup fleksibel bila nanti ada API yang lebih formal.

### 8.1 Strategi Auth Utama

Pilihan implementasi awal:

- `Laravel Breeze`
- atau `manual auth` bila kebutuhan UI/admin flow lebih khusus

Keputusan arsitektur saat ini:

- fase bootstrap direkomendasikan memakai `Laravel Breeze` untuk mempercepat fondasi auth berbasis session
- manual auth hanya dipilih bila terdapat kebutuhan UI atau flow admin yang tidak cocok dengan struktur Breeze

### 8.2 Session vs Token

#### Session Auth

- menjadi default untuk web dan admin panel
- cocok untuk SSR Blade
- paling sederhana untuk shared hosting
- memakai middleware `auth`

#### Token Auth

- tidak menjadi kebutuhan utama fase awal
- disiapkan hanya bila API internal atau mobile app mulai aktif
- jika kelak dibutuhkan, token auth dipisahkan dari auth web agar boundary keamanan jelas

Keputusan:

- fase awal fokus ke `session-based authentication`
- token-based auth adalah opsi fase lanjutan, bukan baseline implementasi awal

### 8.3 Middleware Role

Middleware role wajib dipakai untuk membatasi akses:

- `role:admin`
- `role:editor`
- `role:wartawan`

Prinsip:

- middleware auth memverifikasi login
- middleware role memverifikasi hak akses
- policy tetap dipakai untuk pembatasan objek spesifik, misalnya wartawan hanya boleh mengedit artikel miliknya sendiri

### 8.4 Auth Flow Ringkas

- guest membuka admin login
- user login menggunakan email dan password
- session dibuat setelah kredensial valid
- middleware menentukan area yang boleh diakses
- logout menghancurkan session

## 9. Article Approval Flow Detail

Approval flow wajib jelas karena ini inti operasi redaksi.

### 9.1 Status Dasar

- `draft`
- `review`
- `published`

### 9.2 Flow Utama

#### A. Wartawan Menulis

- wartawan membuat artikel baru
- artikel tersimpan sebagai `draft`
- wartawan boleh edit artikel miliknya sendiri selama masih `draft`

#### B. Submit ke Review

- wartawan menekan aksi submit for review
- sistem mengubah status menjadi `review`
- timestamp dan pelaku submit idealnya dicatat

#### C. Review oleh Editor atau Admin

- editor atau admin membuka artikel status `review`
- reviewer dapat:
  - mengedit isi artikel
  - memberi `review_notes`
  - mengembalikan artikel ke `draft`
  - menyetujui artikel untuk publish

#### D. Revisi

- jika artikel perlu perbaikan, reviewer mengisi `review_notes`
- status dikembalikan ke `draft`
- wartawan memperbaiki artikel lalu submit ulang ke `review`

#### E. Publish

- editor atau admin dapat publish langsung atau menjadwalkan publish
- saat publish:
  - status menjadi `published`
  - `published_at` harus terisi
  - artikel hanya tersedia di route publik jika `published_at <= NOW()`

#### F. Pasca Publish

- artikel published hanya boleh diubah oleh editor atau admin
- perubahan besar pasca publish harus melalui audit trail bila fitur audit diaktifkan
- artikel dapat diarsipkan dengan mengisi `archived_at`

### 9.3 Scheduled Publishing & Laravel Scheduler

- Artikel dengan `published_at` di masa depan disimpan dengan status `published`
- Artikel tidak tampil di publik sampai `published_at` tercapai
- Query halaman publik wajib selalu menambahkan kondisi `published_at <= NOW()`
- Laravel Scheduler wajib dikonfigurasi via cron di server:

```bash
* * * * * php /path/to/artisan schedule:run >> /dev/null 2>&1
```

- Shared hosting yang tidak mendukung cron:
  - scheduled publishing tidak bisa digunakan
  - fitur ini harus didisable dari UI admin
- Scheduler juga dipakai untuk:
  - flush views counter
  - backup
  - queue worker fallback bila diperlukan

Queue worker fallback yang dimaksud:

- untuk shared hosting tanpa persistent worker, queue dijalankan melalui Scheduler atau cron
- command yang dipakai:

```bash
php artisan queue:work --stop-when-empty
```

- command tersebut dapat dipanggil setiap menit agar job yang menunggu tetap diproses
- pendekatan ini hanya fallback fase awal, bukan pengganti worker persisten pada environment yang lebih baik

### 9.4 Archived Article Behavior

- Artikel dengan `archived_at` terisi dianggap tidak aktif secara publik
- Status artikel tetap `published` saat diarsipkan
- `archived_at` adalah flag operasional terpisah
- Artikel arsip tetap bisa dilihat di admin panel
- Artikel arsip dapat di-unarchive oleh editor atau admin

### 9.5 Hak Akses per Tahap

- wartawan:
  - create draft
  - edit own draft
  - submit review
- editor:
  - review artikel
  - edit artikel redaksi
  - return to draft dengan catatan
  - publish
- admin:
  - semua capability editor
  - override workflow
  - unpublish atau archive bila diperlukan

### 9.6 Aturan Wajib

- AI output tidak boleh publish otomatis
- artikel `review` tidak tampil di publik
- artikel `published` harus lolos pemeriksaan slug, metadata minimum, dan validasi konten dasar
- setiap transisi status harus melalui service layer

### 9.7 Editor Strategy

- Admin panel penulisan artikel wajib memakai rich text editor berbasis HTML
- Format penyimpanan konten artikel fase awal adalah `HTML sanitized`, bukan Markdown
- Editor yang direkomendasikan untuk fase awal:
  - `Trix`
  - atau editor ringan setara yang ramah Laravel Blade
- Keputusan ini dipilih agar integrasi admin sederhana, output HTML mudah dirender di frontend, dan dependency tetap terkendali
- Sanitization HTML wajib dilakukan sebelum konten disimpan atau sebelum ditampilkan bila diperlukan

## 10. Slug Strategy Detail

Slug adalah bagian inti SEO dan route stability.

### 10.1 Tujuan

- slug harus unik
- slug harus auto-generate dari title
- slug harus stabil setelah publish kecuali ada alasan editorial kuat
- sistem harus mampu menangani duplicate title

### 10.2 Aturan Slug

- sumber slug utama adalah `title`
- gunakan format lowercase
- gunakan separator `-`
- hilangkan karakter yang tidak aman untuk URL
- batasi panjang slug agar tidak berlebihan
- simpan slug final di database

### 10.3 Auto Generate

Saat artikel dibuat:

- jika slug manual tidak diisi, sistem generate dari title
- jika slug manual diisi editor/admin, tetap normalisasi format slug sebelum simpan

Saat title berubah:

- untuk artikel `draft` dan `review`, slug boleh diregenerate bila belum dikunci manual
- untuk artikel `published`, slug tidak boleh berubah otomatis

### 10.4 Handle Duplicate

Strategi duplicate wajib deterministik:

1. generate base slug dari title
2. cek apakah slug sudah ada
3. jika belum ada, gunakan slug tersebut
4. jika sudah ada, tambahkan suffix numerik bertingkat:
   - `judul-berita`
   - `judul-berita-2`
   - `judul-berita-3`
5. ulangi sampai ditemukan slug unik

### 10.5 Validasi dan Edge Case

- slug unik global di tabel `articles`
- slug kategori unik global di tabel `categories`
- jika title kosong atau gagal menghasilkan slug valid, proses simpan harus ditolak
- reserved path seperti `admin`, `rss.xml`, `amp`, `kategori`, `berita` tidak boleh dipakai sebagai slug root yang menabrak route utama

### 10.6 Implementasi Teknis yang Direkomendasikan

- slug generation dibungkus dalam `SlugService`
- semua create/update artikel harus melewati service ini
- unique index database tetap wajib sebagai lapisan proteksi akhir

## 11. Error Handling Strategy

Error handling harus konsisten antara frontend publik, admin, dan API.

### 11.1 Tujuan

- mencegah error mentah tampil ke user
- memberi pesan yang jelas tanpa membocorkan detail sensitif
- menjaga pengalaman baca dan pengalaman admin tetap stabil

### 11.2 Prinsip

- tampilkan pesan ramah pengguna untuk error umum
- log detail teknis ke sistem logging
- jangan expose stack trace di production
- gunakan HTTP status code yang tepat

### 11.3 Strategi per Area

#### Frontend Publik

- `404` untuk artikel atau kategori yang tidak ditemukan
- `410` bisa dipertimbangkan untuk konten yang sengaja dihapus permanen
- `500` menampilkan halaman error generik dengan identitas brand

#### Admin Panel

- validasi gagal harus kembali ke form dengan pesan jelas
- authorization failure harus mengarah ke `403`
- aksi sensitif yang gagal harus menampilkan notifikasi error yang ringkas

#### API

- gunakan response JSON konsisten
- sertakan:
  - `message`
  - `errors` untuk validation error bila perlu
  - `code` internal opsional bila sistem membutuhkannya

### 11.4 Exception Handling

- sentralisasi exception handling di mekanisme exception Laravel
- normalisasi exception domain penting di service layer
- error dari integrasi AI, RSS, AMP, atau pihak ketiga harus ditangani tanpa menjatuhkan seluruh request bila memungkinkan

## 12. Logging Strategy

Logging harus membantu debugging, audit, dan monitoring tanpa menghasilkan noise berlebihan.

### 12.1 Tujuan

- melacak error aplikasi
- membantu audit aksi administratif penting
- membantu investigasi masalah editorial dan auth

### 12.2 Channel Logging

- default app log menggunakan stack Laravel
- file log cukup untuk fase awal shared hosting
- jika environment membaik, logging bisa diteruskan ke layanan terpusat

### 12.3 Event yang Wajib Dicatat

- login berhasil admin/editor/wartawan
- login gagal
- logout
- perubahan role user
- submit artikel ke review
- artikel dikembalikan ke draft
- publish artikel
- unpublish atau archive artikel
- error integrasi AI
- exception penting yang memengaruhi user flow

### 12.4 Aturan Logging

- jangan log password atau token mentah
- jangan log data sensitif berlebihan
- log harus cukup kontekstual:
  - user id
  - article id bila relevan
  - route atau action
  - ringkasan error
- gunakan level log dengan disiplin:
  - `info` untuk event penting normal
  - `warning` untuk anomali yang tidak fatal
  - `error` untuk kegagalan request atau proses
  - `critical` untuk kegagalan sistem berat

## 13. Backup Strategy

- Backup database wajib dijadwalkan minimal `1x sehari` via Laravel Scheduler
- Gunakan paket `spatie/laravel-backup` atau `mysqldump` manual via Artisan command
- Simpan backup di storage lokal dan idealnya satu lokasi remote
- Remote storage yang direkomendasikan:
  - `S3`
  - layanan sejenis
- Retensi backup: `7 hari` terakhir wajib tersedia
- Backup file media dijadwalkan terpisah
- Prosedur restore wajib didokumentasikan di `docs/RECOVERY.md`
- File `docs/RECOVERY.md` masuk TODO

## 14. Cache Strategy Detail

- Yang wajib di-cache:
  - daftar kategori aktif dengan TTL `60 menit`
  - settings global autoload dengan TTL `60 menit`
  - artikel populer dengan TTL `30 menit`
  - homepage article list dengan TTL `10 menit`
- Invalidation:
  - kategori aktif harus di-invalidate saat kategori diubah
  - settings global harus di-invalidate saat setting diubah
  - invalidation dilakukan secara eksplisit di service layer setelah mutasi data relevan
- Yang tidak boleh di-cache:
  - halaman admin
  - form dengan CSRF token
  - hasil pencarian real-time
- Cache key harus memakai prefix konsisten:
  - `{APP_NAME}:cache:{resource}`

## 15. Fitur System

### 15.1 Admin Panel

Fitur minimum:

- CRUD artikel
- CRUD kategori
- user role management
- ads management
- setting website

Fitur detail:

- dashboard statistik sederhana
- filter artikel per status
- editor artikel
- upload featured image
- preview artikel
- review notes dari editor/admin
- publish sekarang atau terjadwal
- moderasi komentar
- pengaturan identitas situs
- pengaturan SEO default
- pengaturan script pihak ketiga

### 15.2 Frontend

Fitur minimum:

- homepage
- detail artikel
- halaman kategori
- search

Fitur detail:

- headline section
- berita terbaru
- berita populer
- artikel terkait
- breadcrumbs
- lazy loading gambar
- daftar kategori
- pencarian cepat
- komentar pembaca
- langganan newsletter
- CTA push notification

### 15.3 Comment System Policy

- Fase awal: komentar diizinkan tanpa login
- Guest comment aktif
- Field wajib untuk komentar tamu:
  - `guest_name`
  - `guest_email`
- Semua komentar masuk dengan status `pending`
- Semua komentar wajib dimoderasi manual oleh editor atau admin sebelum tampil
- Anti-spam fase awal memakai honeypot field di form komentar
- CAPTCHA tidak diwajibkan di fase awal
- CAPTCHA dapat ditambahkan jika spam menjadi masalah nyata
- Komentar balasan dengan `parent_id` diizinkan maksimal `1 level` kedalaman
- Reply to comment diizinkan
- Reply to reply tidak diizinkan
- `ip_address` dan `user_agent` wajib dicatat untuk moderasi

## 16. Fitur Advanced

### 16.1 AI Generate

Fungsi:

- generate draft artikel dari keyword atau brief
- generate judul alternatif
- generate excerpt
- generate meta description

Batasan:

- AI tidak boleh publish otomatis
- output AI wajib masuk `draft` atau `review`
- wajib ada human review
- prompt dan sumber konteks penting sebaiknya tercatat

### 16.2 Scraping Legal Only

Tujuan:

- mengumpulkan ringkasan dari sumber legal untuk bahan editorial

Aturan keras:

- hanya ambil ringkasan, metadata, dan sumber
- jangan salin penuh artikel pihak lain
- selalu simpan URL sumber
- hasil scraping tidak boleh auto-publish
- semua hasil masuk ke review internal

### 16.3 AMP

Tujuan:

- menyajikan halaman ringan dan cepat untuk distribusi tertentu

Aturan:

- halaman AMP minimal untuk detail artikel
- script pihak ketiga dibatasi
- layout harus konsisten dengan identitas brand utama

### 16.4 PWA

Tujuan:

- website bisa di-install ke HP
- caching halaman penting
- mendukung pengalaman mobile lebih baik

Komponen:

- manifest
- service worker
- icon set
- offline fallback minimal

## 17. SEO System

Wajib tersedia:

- slug SEO
- meta tag
- sitemap
- robots.txt
- schema JSON-LD

Detail implementasi:

- meta title fallback ke title artikel
- meta description fallback ke excerpt
- sitemap minimal untuk:
  - homepage
  - articles
  - categories
- robots.txt harus eksplisit mengizinkan halaman publik dan membatasi area admin
- schema JSON-LD minimal:
  - `NewsArticle`
  - `BreadcrumbList`
  - `Organization`
  - `WebSite`

## 18. Monetization

Channel monetisasi target:

- Google Adsense
- Affiliate
- Sponsored post
- Newsletter
- Push notif ads

Aturan:

- monetisasi tidak boleh merusak performa ekstrem
- placement iklan harus bisa dikelola dari admin
- sponsored post harus jelas ditandai
- affiliate harus transparan

## 19. TODO System

Format wajib:

- nama task
- status
- file terkait
- catatan

### 19.1 DONE

- [x] Menetapkan `docs/PROJECT_MASTER_DOC.md` sebagai single source of truth
  - Status: `DONE`
  - File terkait: `docs/PROJECT_MASTER_DOC.md`
  - Catatan: Dokumen master telah direvisi agar selaras dengan keputusan teknis terbaru

- [x] Inisialisasi repository Git
  - Status: `DONE`
  - File terkait: `.git`
  - Catatan: Repository Git sudah aktif dan dipakai untuk workflow commit tanpa push

- [x] Install Laravel fresh versi 13.x
  - Status: `DONE`
  - File terkait: `composer.json`, `artisan`, `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`
  - Catatan: Baseline Laravel 13 sudah terpasang dan tervalidasi melalui `php artisan --version`

- [x] Install frontend baseline dan library pendukung awal
  - Status: `DONE`
  - File terkait: `package.json`, `package-lock.json`, `resources/js/app.js`, `resources/css/app.css`
  - Catatan: Tailwind CSS v4 bawaan Laravel aktif, Alpine.js terintegrasi, Trix terpasang, dan build Vite berhasil

- [x] Install library image processing awal
  - Status: `DONE`
  - File terkait: `composer.json`, `composer.lock`
  - Catatan: `intervention/image-laravel` sudah terpasang sebagai baseline media processing

- [x] Install library image optimization awal
  - Status: `DONE`
  - File terkait: `composer.json`, `composer.lock`, `config/image-optimizer.php`
  - Catatan: `spatie/laravel-image-optimizer` sudah terpasang untuk optimasi ukuran file pasca-resize dengan catatan efektivitas akhir bergantung pada binary optimizer di server

- [x] Setup `.env` awal dan database connection
  - Status: `DONE`
  - File terkait: `.env`
  - Catatan: Koneksi MySQL lokal ke database `media` sudah tervalidasi lewat proses migration dan seeding

- [x] Buat migration untuk tabel inti
  - Status: `DONE`
  - File terkait: `database/migrations`
  - Catatan: Tabel inti proyek dan tabel pendukung Laravel database driver sudah berhasil dimigrasikan

- [x] Buat seeder fase awal
  - Status: `DONE`
  - File terkait: `database/seeders`
  - Catatan: Seeder user default, kategori default, dan setting default sudah berhasil dijalankan

### 19.2 NEED VERIFICATION

- [ ] Verifikasi versi runtime hosting target untuk Laravel 13
  - Status: `NEED VERIFICATION`
  - File terkait: `N/A`
  - Catatan: Pastikan shared hosting mendukung PHP 8.3 atau 8.4, MySQL memadai, dan cron/queue tersedia

- [ ] Verifikasi dukungan Redis di environment produksi
  - Status: `NEED VERIFICATION`
  - File terkait: `config/cache.php`, `config/queue.php`
  - Catatan: Jika tidak tersedia, siapkan fallback `file` dan `database`

- [ ] Verifikasi apakah AMP masih diperlukan untuk strategi distribusi target
  - Status: `NEED VERIFICATION`
  - File terkait: `routes`, `resources/views`
  - Catatan: Tetap dipertahankan di docs, implementasi menunggu validasi bisnis

- [ ] Verifikasi pendekatan AI Laravel 13 yang paling sesuai untuk hosting target
  - Status: `NEED VERIFICATION`
  - File terkait: `app/Services/AI`
  - Catatan: Pastikan fitur AI yang dipilih kompatibel dengan arsitektur proyek dan biaya operasional

- [ ] Verifikasi mekanisme scheduler dan cron di hosting target
  - Status: `NEED VERIFICATION`
  - File terkait: `app/Console`, `routes/console.php`
  - Catatan: Scheduler wajib tersedia untuk backup, scheduled publishing, dan flush views counter

- [ ] Verifikasi dukungan GD atau Imagick di hosting target
  - Status: `NEED VERIFICATION`
  - File terkait: `app/Services/MediaService.php`
  - Catatan: Resize image wajib membutuhkan library dan ekstensi PHP yang kompatibel

- [ ] Verifikasi pemilihan rich text editor final untuk admin
  - Status: `NEED VERIFICATION`
  - File terkait: `resources/views/admin`, `resources/js`
  - Catatan: Fase awal direkomendasikan memakai Trix atau editor ringan setara

### 19.3 TODO

- [ ] Implement auth dasar berbasis session
  - Status: `TODO`
  - File terkait: `routes`, `app/Http/Controllers`, `resources/views`
  - Catatan: Gunakan Breeze atau manual auth sesuai keputusan implementasi final

- [ ] Implement role middleware untuk admin, editor, wartawan
  - Status: `TODO`
  - File terkait: `app/Http/Middleware`, `bootstrap/app.php` atau struktur Laravel terkait
  - Catatan: Harus konsisten dengan dokumen role system

- [ ] Implement CRUD sederhana artikel
  - Status: `TODO`
  - File terkait: `app/Models/Article.php`, `app/Http/Controllers`, `resources/views`, `database/migrations`
  - Catatan: Fokus ke create, read, update, submit review, publish

- [ ] Buat slug service dan rule unique slug
  - Status: `TODO`
  - File terkait: `app/Services/SlugService.php`, `app/Models`, `database/migrations`
  - Catatan: Harus mendukung auto generate dan duplicate handling

- [ ] Siapkan admin panel minimal
  - Status: `TODO`
  - File terkait: `routes/admin.php`, `resources/views/admin`, `app/Http/Controllers/Admin`
  - Catatan: Fokus pada artikel, kategori, setting awal

- [ ] Implement media upload dan image processing policy
  - Status: `TODO`
  - File terkait: `config/filesystems.php`, `app/Services/MediaService.php`, `app/Http/Requests`, `storage/app/public`
  - Catatan: Termasuk resize otomatis, hash filename, validasi format, ukuran maksimal 2MB, struktur folder upload artikel, dan integrasi image library

- [ ] Implement tags system
  - Status: `TODO`
  - File terkait: `app/Models/Tag.php`, `app/Models/Article.php`, `app/Services/TagService.php`, `database/migrations`, `app/Http/Controllers`
  - Catatan: Tambahkan relasi many-to-many `articles` ke `tags`, tabel pivot `article_tags`, dan `withTimestamps()`

- [ ] Implement rate limiting untuk publik, auth, dan API
  - Status: `TODO`
  - File terkait: `bootstrap/app.php`, `routes`, `app/Providers`
  - Catatan: Gunakan throttle middleware dengan fallback cache driver file bila Redis tidak tersedia

- [ ] Implement backup strategy dan dokumentasi recovery
  - Status: `TODO`
  - File terkait: `app/Console`, `routes/console.php`, `docs/RECOVERY.md`
  - Catatan: Backup DB harian, retensi 7 hari, backup media terpisah, dan prosedur restore wajib terdokumentasi

- [ ] Implement views counter buffering
  - Status: `TODO`
  - File terkait: `app/Jobs`, `app/Services`, `routes/console.php`, `app/Models/Article.php`
  - Catatan: Buffer hit di cache, flush periodik ke `articles.views_count`, tidak synchronous di request cycle

- [ ] Implement pagination strategy frontend
  - Status: `TODO`
  - File terkait: `app/Http/Controllers/Front`, `resources/views/frontend`
  - Catatan: Gunakan `paginate()` dengan default 15 item untuk kategori dan pencarian

- [ ] Implement cache strategy untuk kategori, settings, homepage, dan artikel populer
  - Status: `TODO`
  - File terkait: `app/Services`, `config/cache.php`, `app/Http/Controllers`
  - Catatan: Gunakan prefix key konsisten dan explicit invalidation setelah mutasi data

- [ ] Implement mail system asynchronous
  - Status: `TODO`
  - File terkait: `config/mail.php`, `.env`, `app/Mail`, `app/Jobs`
  - Catatan: SMTP fase awal, queue wajib, tanpa pengiriman synchronous di request cycle

- [ ] Implement feature flag system
  - Status: `TODO`
  - File terkait: `config`, `settings`, `app/Services`
  - Catatan: Minimal untuk AMP, AI, dan Comment on/off

- [ ] Implement rich text editor admin
  - Status: `TODO`
  - File terkait: `resources/views/admin`, `resources/js`, `app/Http/Controllers/Admin`
  - Catatan: Konten artikel disimpan sebagai HTML sanitized melalui editor ringan fase awal

- [ ] Implement scheduled publishing via Laravel Scheduler
  - Status: `TODO`
  - File terkait: `routes/console.php`, `app/Services/ArticleService.php`, `app/Http/Controllers/Admin`
  - Catatan: Artikel `published` dengan `published_at` masa depan tidak boleh tampil sebelum waktunya

- [ ] Implement archived article behavior
  - Status: `TODO`
  - File terkait: `app/Http/Controllers/Front`, `app/Services/ArticleService.php`, `routes`
  - Catatan: Query publik wajib filter `archived_at IS NULL`; akses direct ke artikel arsip harus `410 Gone`

- [ ] Implement guest comment policy dan moderation flow
  - Status: `TODO`
  - File terkait: `app/Models/Comment.php`, `app/Http/Controllers`, `app/Http/Requests`, `resources/views/frontend`
  - Catatan: Guest comment aktif, status default pending, honeypot aktif, depth reply maksimal 1 level

## 20. Changelog

Semua perubahan proyek wajib dicatat. Jika belum ada file changelog terpisah, catat di bagian ini.

### 2026-04-02

- Merevisi nama proyek kerja menjadi `TodakSiring`
- Mengganti baseline framework menjadi `Laravel 13.x`
- Menyederhanakan role sistem menjadi hanya `admin`, `editor`, dan `wartawan`
- Menambahkan bagian detail:
  - Route Structure
  - Auth System Design
  - Article Approval Flow Detail
  - Slug Strategy Detail
  - Error Handling Strategy
  - Logging Strategy
- Menambahkan keputusan teknis:
  - Media & Image Storage Strategy
  - Tags System
  - Rate Limiting Strategy
  - Backup Strategy
  - Views Count Strategy
  - Pagination Strategy
  - Cache Strategy Detail
  - Mail System
  - Deployment Strategy
  - Scheduled Publishing & Laravel Scheduler
  - Archived Article Behavior
  - Comment System Policy
- Merapikan struktur dokumen:
  - urutan sub-section Route Structure
  - normalisasi penomoran section tanpa suffix huruf
- Menambahkan keputusan teknis tambahan:
  - TagService dan MediaService pada folder structure
  - `docs/RECOVERY.md` pada struktur docs
  - rich text editor strategy untuk admin article editor
  - image resize library strategy dan verifikasi GD/Imagick
  - pivot timestamps policy untuk `article_tags`
  - Seeder Strategy
  - Feature Flag System
- Menginstall baseline aplikasi riil:
  - Laravel Framework `13.3.0`
  - Composer dependencies baseline
  - Tailwind CSS v4 via Vite
  - Alpine.js
  - Trix
  - `intervention/image-laravel`
- Mengaktifkan bootstrap Alpine.js di `resources/js/app.js`
- Menghasilkan build frontend awal di `public/build`
- Menghasilkan `APP_KEY` dan menetapkan `APP_NAME` serta `APP_BRAND_NAME` di `.env`
- Mengimplementasikan migration inti proyek:
  - `users`
  - `categories`
  - `articles`
  - `tags`
  - `article_tags`
  - `comments`
  - `ads`
  - `settings`
  - `subscribers`
- Mengimplementasikan seeder fase awal:
  - admin default
  - editor default
  - wartawan sample
  - kategori default
  - setting default
- Menjalankan `php artisan migrate:fresh --seed` ke database MySQL `media`
- Menyesuaikan TODO agar selaras dengan keputusan teknis terbaru
- Menginstall `spatie/laravel-image-optimizer` sebagai baseline optimasi ukuran file media
- Menambahkan konfigurasi awal `config/image-optimizer.php` untuk JPEG, PNG, GIF, SVG, dan WebP

### 2026-04-01

- Membuat dokumen master `docs/PROJECT_MASTER_DOC.md`
- Menetapkan docs sebagai pusat keputusan proyek

## 21. Safe Development Protocol

Aturan ini wajib dipatuhi oleh semua developer dan AI executor.

### 21.1 Protokol Inti

1. Baca docs sebelum coding
2. Cek TODO
3. Kerjakan 1 task
4. Update docs
5. Update changelog
6. Jalankan commit Git tanpa push

Perintah standar:

```bash
git add .
git commit -m "feat: deskripsi perubahan"
```

Larangan:

- jangan `git push` tanpa instruksi eksplisit
- jangan refactor besar tanpa alasan dan dokumentasi
- jangan hapus fitur tanpa analisa dampak
- jangan ubah struktur utama tanpa update docs
- jangan mengarang status implementasi

### 21.2 Aturan Perubahan

- jika fitur belum dibuat, tulis sebagai `TODO`, bukan seolah sudah jadi
- jika ada asumsi, tandai sebagai asumsi
- jika ada area meragukan, masukkan ke `NEED VERIFICATION`
- setiap perubahan signifikan harus memperbarui dokumen ini

### 21.3 Aturan AI Baru

AI baru yang melanjutkan proyek wajib:

1. Membaca `docs/PROJECT_MASTER_DOC.md`
2. Memahami status aktual proyek
3. Memilih satu task TODO
4. Mengimplementasikan task
5. Memperbarui docs dan changelog
6. Commit tanpa push

### 21.4 Aturan Keamanan Sistem

- validasi input wajib
- auth dan authorization wajib
- jangan tampilkan data sensitif ke publik
- semua konfigurasi brand harus berbasis env/config
- gunakan migration, bukan edit database manual tanpa jejak

## 22. Workflow Wajib

Workflow operasional harian:

1. Baca docs
2. Cek TODO
3. Kerjakan 1 task kecil sampai selesai
4. Verifikasi hasil
5. Update docs
6. Update changelog
7. Commit
8. Jangan push

## 23. Step Awal Implementasi

Setelah dokumen ini, urutan kerja awal yang wajib:

1. Install Laravel 13 fresh
2. Setup DB
3. Implement auth berbasis session
4. Implement middleware role
5. Buat CRUD sederhana artikel
6. Implement submit review dan publish flow

Tujuan:

- test shared hosting
- pastikan DB aman
- validasi flow auth dasar
- validasi editorial workflow dasar
- validasi bahwa panel admin dan frontend bisa berjalan dari fondasi minimal

## 24. Deployment Strategy

- Deployment ke shared hosting dilakukan manual via FTP/SFTP atau `git pull` di server
- Tidak ada CI/CD otomatis di fase awal
- Deployment manual adalah baseline
- Urutan langkah deployment wajib:
  1. Upload atau pull kode terbaru
  2. `composer install --no-dev --optimize-autoloader`
  3. `php artisan migrate --force`
  4. `php artisan config:cache`
  5. `php artisan route:cache`
  6. `php artisan view:cache`
  7. `php artisan storage:link`
  8. `php artisan queue:restart`
- Jangan jalankan `php artisan migrate` tanpa `--force` di production
- `php artisan queue:restart` wajib dijalankan bila queue worker aktif agar proses worker memuat kode terbaru
- Rollback:
  - siapkan prosedur rollback migration di `docs/RECOVERY.md`

## 25. Prinsip Utama Proyek

- Docs adalah pusat sistem
- Kode mengikuti docs
- AI baru wajib baca docs dulu
- Tidak boleh merusak sistem lama
- Semua perubahan harus bisa dijelaskan
- Semua perubahan harus bisa ditelusuri

## 26. Keputusan Teknis Awal

- Brand name fleksibel melalui `.env`
- Laravel Blade + Alpine diprioritaskan pada fase awal
- Laravel 13 menjadi baseline
- Auth awal berbasis session
- Route publik wajib mengikuti struktur SEO yang telah diputuskan
- Slug generation harus tersentralisasi di service layer
- Admin panel dan frontend dipisah jelas
- Shared hosting compatibility adalah constraint nyata, bukan asumsi opsional
- Implementasi awal harus sesederhana mungkin tetapi tidak boleh mengunci arsitektur menjadi buruk

## 27. Status Nyata Saat Ini

Status aktual per `2026-04-02`:

- Dokumen master: ada dan telah direvisi
- Laravel app: ada
- Git repository: aktif
- Database schema inti proyek: sudah diimplementasikan pada baseline awal
- Seeder fase awal: sudah diimplementasikan dan dijalankan
- Database connection lokal MySQL: tervalidasi
- Baseline frontend stack: terpasang
- Build frontend awal: berhasil
- Auth system: belum ada
- CRUD artikel: belum ada
- Admin panel: belum ada
- Frontend publik kustom: belum ada

Kesimpulan:

Proyek saat ini berada pada fase `foundation bootstrap`, yaitu dokumen sudah matang dan baseline aplikasi Laravel sudah berjalan, tetapi fitur inti proyek belum diimplementasikan.
