# PROJECT MASTER DOC

## 0. Document Control

- Document name: `PROJECT_MASTER_DOC.md`
- Function: single source of truth untuk seluruh keputusan sistem, arsitektur, workflow, dan arah implementasi portal berita modern.
- Project working title: `LAUTPULO MEDIA`
- Branding rule: nama media harus fleksibel dan tidak di-hardcode. Untuk fase awal gunakan env:
  - `APP_NAME="LAUTPULO MEDIA"`
  - `APP_BRAND_NAME="LAUTPULO MEDIA"`
- Current repository state at document creation:
  - Workspace masih kosong
  - Belum ada instalasi Laravel
  - Belum ada database migration
  - Belum ada repository Git aktif
- Document status: `ACTIVE`
- Last updated: `2026-04-01`
- Owner role: `Tech Lead / Senior Software Engineer / AI Executor`

## 1. Project Overview

### 1.1 Nama Project

`LAUTPULO MEDIA` adalah nama kerja awal untuk portal berita modern berbasis web yang berorientasi mobile, SEO, monetisasi, dan skalabilitas editorial.

### 1.2 Filosofi Nama

Nama mengacu pada nuansa geografis dan budaya Kalimantan Selatan dan wilayah pesisir sungai:

- `Laut` merepresentasikan keterbukaan, jangkauan luas, arus informasi, dan karakter daerah pesisir.
- `Pulo` atau pulau merepresentasikan identitas lokal, simpul komunitas, dan titik temu informasi.
- Asosiasi budaya yang relevan:
  - laut dan sungai sebagai jalur kehidupan
  - gunung sebagai simbol kekuatan dan keteguhan
  - kelotok sebagai simbol mobilitas, distribusi, dan koneksi antarwilayah
- Secara brand, nama harus fleksibel. Jika nama media berubah di masa depan, penggantian dilakukan melalui `.env`, bukan hardcode di view, config, atau database seed.

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
  - memberi komentar
  - berlangganan notifikasi atau newsletter

#### B. Admin Media

- Mengelola operasi editorial
- Mengatur kategori, artikel, iklan, user, dan setting situs
- Membutuhkan panel admin yang stabil, jelas, dan aman
- Membutuhkan workflow approval agar kualitas konten terjaga

### 1.4 Tujuan Bisnis

- Membangun traffic organik tinggi melalui SEO, kecepatan, dan struktur konten yang rapi
- Mendukung monetisasi multi-channel:
  - Google Adsense
  - iklan langsung
  - affiliate
  - sponsored post
  - newsletter
  - push notification
- Menjaga biaya operasional efisien agar tetap layak di shared hosting dan bisa naik kelas ke VPS/cloud
- Menyediakan fondasi untuk multi bahasa dan ekspansi brand di masa depan

### 1.5 Prinsip Produk

- Mobile first
- Clean reading experience
- Fokus pada performa, SEO, dan kemudahan editorial
- Semua keputusan teknis harus mengacu ke dokumen ini
- Kode mengikuti docs, bukan sebaliknya

## 2. User Role System

Sistem role harus sederhana, tegas, dan mudah diaudit. Role disimpan pada tabel `users.role`.

### 2.1 Daftar Role

#### 1. Super Admin

- Full akses sistem
- Kelola setting global
- Kelola user dan role
- Kelola menu, iklan, integrasi, cache, dan fitur eksperimental
- Bisa override approval bila diperlukan
- Bisa melihat semua log administratif

#### 2. Admin

- Kelola seluruh konten
- Buat, edit, review, publish, unpublish artikel
- Kelola kategori, tag, komentar, ads
- Bisa mengelola penjadwalan publikasi
- Tidak boleh mengubah setting sistem level infrastruktur tanpa izin Super Admin

#### 3. Editor

- Edit artikel
- Review kualitas konten
- Approve artikel penulis untuk naik ke tahap publish atau jadwal publish
- Bisa mengembalikan artikel ke penulis dengan catatan revisi
- Tidak boleh mengubah global setting sensitif

#### 4. Wartawan / Penulis

- Membuat artikel
- Menyimpan draft
- Mengajukan artikel ke review
- Tidak bisa publish langsung
- Hanya boleh mengedit artikel milik sendiri selama status belum `published`

#### 5. Guest / Pembaca

- Mengakses halaman publik
- Membaca artikel
- Melakukan pencarian
- Melihat kategori
- Memberi komentar jika fitur komentar aktif
- Berlangganan newsletter atau push notification jika tersedia

### 2.2 Permission Matrix Ringkas

| Fitur | Super Admin | Admin | Editor | Penulis | Guest |
|---|---|---|---|---|---|
| Kelola setting global | Ya | Terbatas | Tidak | Tidak | Tidak |
| Kelola user | Ya | Terbatas | Tidak | Tidak | Tidak |
| Buat artikel | Ya | Ya | Ya | Ya | Tidak |
| Edit semua artikel | Ya | Ya | Ya | Tidak | Tidak |
| Edit artikel sendiri | Ya | Ya | Ya | Ya | Tidak |
| Submit review | Ya | Ya | Ya | Ya | Tidak |
| Approve artikel | Ya | Ya | Ya | Tidak | Tidak |
| Publish artikel | Ya | Ya | Ya | Tidak | Tidak |
| Hapus artikel | Ya | Ya | Terbatas | Tidak | Tidak |
| Kelola komentar | Ya | Ya | Ya | Tidak | Tidak |
| Baca artikel | Ya | Ya | Ya | Ya | Ya |
| Komentar | Ya | Ya | Ya | Ya | Ya/Tergantung mode |

### 2.3 Aturan Workflow Artikel

- `draft`: artikel masih dikerjakan penulis/editor
- `review`: artikel menunggu review editorial
- `published`: artikel tampil publik
- `rejected`: artikel ditolak dan perlu revisi
- `archived`: artikel tidak aktif tetapi masih tersimpan

Catatan:

- Requirement minimum user menyebut `draft`, `review`, `publish`; sistem tetap boleh menambahkan `rejected` dan `archived` demi operasi editorial yang lebih aman.
- Jika ingin disiplin penuh terhadap requirement awal, enum database utama tetap memakai `draft`, `review`, `published`. Status `rejected` dan `archived` dapat diwujudkan melalui kolom tambahan `is_rejected` atau `archived_at`. Pada fase implementasi awal, opsi paling aman adalah menambahkan status penuh di docs terlebih dahulu lalu memvalidasi saat migration final.

Keputusan arsitektur saat ini:

- Untuk konsistensi requirement user, SQL utama pada dokumen ini memakai status inti:
  - `draft`
  - `review`
  - `published`
- Status non-inti seperti penolakan atau arsip akan di-handle oleh kolom tambahan:
  - `review_notes`
  - `published_at`
  - `archived_at`

## 3. Tech Stack

Dokumen ini mengunci target stack implementasi awal. Stack ini adalah target yang harus dipakai saat bootstrap proyek.

### 3.1 Core Stack

- Framework: `Laravel 12.x`
- Language: `PHP 8.3+`
- Recommended runtime: `PHP 8.4.x` untuk environment baru
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

- Laravel 12 sebagai baseline resmi proyek
- PHP minimum kompatibilitas: `8.3`
- PHP yang direkomendasikan untuk produksi baru: `8.4`
- MySQL yang direkomendasikan:
  - minimum `8.0`
  - direkomendasikan `8.4 LTS` bila hosting mendukung
- Redis untuk:
  - cache
  - session bila memungkinkan
  - queue di environment yang mendukung worker
- Jika shared hosting belum mendukung Redis, sistem harus tetap berjalan dengan fallback:
  - cache driver: `file`
  - session driver: `file` atau `database`
  - queue driver: `database`

### 3.4 Catatan Verifikasi Versi

Versi target ini mengikuti referensi resmi yang diverifikasi saat penulisan dokumen:

- Laravel release docs menunjukkan `Laravel 12` telah rilis pada `2025-02-24`
- Tailwind official blog menunjukkan `Tailwind CSS v4.0` telah rilis pada `2025-01-22`
- Alpine official docs masih mengacu pada `Alpine.js v3`
- PHP official supported versions menunjukkan `PHP 8.3` dan `PHP 8.4` masih dalam masa support

### 3.5 Prinsip Pemilihan Stack

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

`User -> Request -> Route -> Controller -> Service -> Model/Repository -> Database -> Response`

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
- Hindari frontend SPA penuh pada fase awal karena tidak efisien untuk portal berita shared hosting

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
- pengaturan bahasa dan translasi

Prinsip:

- route admin dipisah jelas
- akses berbasis role dan policy
- semua aksi sensitif harus bisa diaudit

### 4.5 API

API tidak boleh dibuka liar. API dipakai untuk:

- mobile app di masa depan
- feed internal
- endpoint AJAX terbatas
- integrasi PWA
- integrasi OneSignal

Aturan:

- API publik hanya untuk data yang memang aman
- API admin harus pakai auth
- ratelimit wajib aktif

### 4.6 Service Layer

Service layer wajib digunakan untuk logika bisnis non-trivial, misalnya:

- publish article
- generate slug
- article approval
- comment moderation
- ad placement resolver
- SEO metadata builder
- AI content generation
- legal scraping pipeline
- translation orchestration

Tujuan service layer:

- controller tetap tipis
- logika bisnis mudah diuji
- memudahkan AI lain memahami alur sistem

### 4.7 Modul Sistem yang Direncanakan

- Auth Module
- User & Role Module
- Article Module
- Category Module
- Comment Module
- Ads Module
- Setting Module
- Search Module
- SEO Module
- RSS Module
- AMP Module
- PWA Module
- Notification Module
- AI Module
- Scraping Module
- Localization Module

## 5. Struktur Folder

Struktur ini adalah target Laravel project structure yang harus diikuti.

### 5.1 `app/`

Fungsi utama:

- berisi inti aplikasi
- business logic
- model
- services
- policies
- jobs

Subfolder yang direkomendasikan:

- `app/Models`
- `app/Http/Controllers`
- `app/Http/Requests`
- `app/Services`
- `app/Policies`
- `app/Enums`
- `app/Actions`
- `app/Jobs`
- `app/Notifications`
- `app/Support`

Prinsip:

- logika bisnis jangan ditumpuk di controller
- buat service per domain, bukan per method acak

### 5.2 `resources/`

Fungsi:

- view Blade
- asset CSS/JS sumber Vite
- localization file bila memakai file-based translation

Subfolder yang direkomendasikan:

- `resources/views/frontend`
- `resources/views/admin`
- `resources/views/components`
- `resources/css`
- `resources/js`
- `resources/lang`

### 5.3 `routes/`

Pemisahan route harus tegas:

- `routes/web.php` untuk frontend publik
- `routes/admin.php` untuk panel admin
- `routes/api.php` untuk API
- `routes/console.php` untuk command internal

Jika diperlukan:

- `routes/rss.php`
- `routes/amp.php`

Namun fase awal lebih aman memakai grouping route di file standar dulu untuk menghindari fragmentasi berlebihan.

### 5.4 `public/`

Fungsi:

- dokumen web root
- asset hasil build
- file publik seperti favicon, manifest, robots.txt, sitemap statis bila ada

Catatan:

- jangan menaruh logika bisnis di `public/`
- file upload user sebaiknya melalui storage yang di-link, bukan file acak di `public/`

### 5.5 `docs/`

Folder wajib dokumentasi proyek.

Minimal isi yang direncanakan:

- `docs/PROJECT_MASTER_DOC.md`
- `docs/CHANGELOG.md` bila kelak dipisah
- `docs/DECISIONS/` untuk ADR bila skala proyek membesar

Aturan:

- setiap perubahan signifikan harus memperbarui docs
- AI baru wajib membaca folder ini dulu sebelum coding

## 6. Database Design

Bagian ini adalah desain database target. SQL di bawah adalah baseline arsitektur, belum berarti sudah diimplementasikan.

### 6.1 Prinsip Database

- gunakan InnoDB
- gunakan `utf8mb4`
- semua foreign key jelas
- beri index pada kolom pencarian dan relasi
- dukung audit minimal dengan kolom timestamps
- slug harus unik bila tampil di publik
- status dan role harus konsisten dengan docs

### 6.2 Relasi Utama

- `users` 1..n `articles`
- `categories` 1..n `articles`
- `categories` self-reference untuk nested category
- `articles` 1..n `comments`
- `settings` key-value global
- `languages` 1..n `translations`
- `articles` dapat dikaitkan dengan translasi melalui entitas translation group pada fase lanjutan

### 6.3 SQL Schema Baseline

```sql
CREATE TABLE users (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    email VARCHAR(150) NOT NULL UNIQUE,
    email_verified_at TIMESTAMP NULL,
    password VARCHAR(255) NOT NULL,
    role ENUM('super_admin', 'admin', 'editor', 'penulis') NOT NULL DEFAULT 'penulis',
    avatar VARCHAR(255) NULL,
    phone VARCHAR(30) NULL,
    bio TEXT NULL,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    remember_token VARCHAR(100) NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_users_role (role),
    INDEX idx_users_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE categories (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id BIGINT UNSIGNED NULL,
    name VARCHAR(150) NOT NULL,
    slug VARCHAR(180) NOT NULL UNIQUE,
    description TEXT NULL,
    sort_order INT NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
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
    INDEX idx_ads_is_active (is_active)
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

CREATE TABLE languages (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    code VARCHAR(10) NOT NULL UNIQUE,
    name VARCHAR(100) NOT NULL,
    locale VARCHAR(20) NOT NULL UNIQUE,
    is_default TINYINT(1) NOT NULL DEFAULT 0,
    is_active TINYINT(1) NOT NULL DEFAULT 1,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    INDEX idx_languages_is_default (is_default),
    INDEX idx_languages_is_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

CREATE TABLE translations (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    language_id BIGINT UNSIGNED NOT NULL,
    translatable_type VARCHAR(150) NOT NULL,
    translatable_id BIGINT UNSIGNED NOT NULL,
    field_name VARCHAR(100) NOT NULL,
    translated_value LONGTEXT NOT NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_translations_language
        FOREIGN KEY (language_id) REFERENCES languages(id)
        ON DELETE CASCADE ON UPDATE CASCADE,
    INDEX idx_translations_language_id (language_id),
    INDEX idx_translations_lookup (translatable_type, translatable_id, field_name)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

### 6.4 Contoh Data

```sql
INSERT INTO users (name, email, password, role, is_active, created_at, updated_at)
VALUES
('Root Media', '[email protected]', 'hashed-password', 'super_admin', 1, NOW(), NOW()),
('Admin Redaksi', '[email protected]', 'hashed-password', 'admin', 1, NOW(), NOW()),
('Editor Utama', '[email protected]', 'hashed-password', 'editor', 1, NOW(), NOW()),
('Wartawan Banjar', '[email protected]', 'hashed-password', 'penulis', 1, NOW(), NOW());

INSERT INTO categories (parent_id, name, slug, description, sort_order, is_active, created_at, updated_at)
VALUES
(NULL, 'Berita', 'berita', 'Kanal berita utama', 1, 1, NOW(), NOW()),
(1, 'Kalimantan Selatan', 'kalimantan-selatan', 'Berita regional Kalimantan Selatan', 1, 1, NOW(), NOW()),
(NULL, 'Ekonomi', 'ekonomi', 'Kanal ekonomi dan bisnis', 2, 1, NOW(), NOW());

INSERT INTO articles (
    user_id, category_id, title, slug, excerpt, content, status,
    meta_title, meta_description, schema_type, published_at, created_at, updated_at
) VALUES (
    4, 2,
    'Harga Pangan di Pasar Terapung Stabil Jelang Pekan Baru',
    'harga-pangan-di-pasar-terapung-stabil-jelang-pekan-baru',
    'Ringkasan berita ekonomi lokal terkait harga pangan.',
    'Konten artikel lengkap diletakkan di sini.',
    'published',
    'Harga Pangan Stabil di Pasar Terapung',
    'Update harga pangan terbaru dari pasar terapung.',
    'NewsArticle',
    NOW(), NOW(), NOW()
);

INSERT INTO settings (`group`, `key`, `value`, autoload, created_at, updated_at)
VALUES
('general', 'site_name', 'LAUTPULO MEDIA', 1, NOW(), NOW()),
('general', 'tagline', 'Portal berita modern, cepat, dan fokus baca', 1, NOW(), NOW()),
('seo', 'default_meta_description', 'Portal berita modern untuk pembaca mobile dan SEO.', 1, NOW(), NOW());

INSERT INTO languages (code, name, locale, is_default, is_active, created_at, updated_at)
VALUES
('id', 'Bahasa Indonesia', 'id_ID', 1, 1, NOW(), NOW()),
('en', 'English', 'en_US', 0, 1, NOW(), NOW());
```

### 6.5 Catatan Implementasi Database

- `articles.slug` wajib unik
- `categories.slug` wajib unik
- `settings.key` wajib unik
- `translations` memakai pola polymorphic agar fleksibel
- fulltext index artikel bergantung dukungan engine dan versi MySQL
- bila shared hosting membatasi fitur tertentu, fallback pencarian dapat memakai `LIKE` bertahap pada fase awal

## 7. Fitur System

### 7.1 Admin Panel

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

### 7.2 Frontend

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

## 8. Fitur Advanced

### 8.1 AI Generate

Fungsi:

- generate draft artikel dari keyword
- generate judul alternatif
- generate excerpt
- generate meta description

Batasan:

- AI tidak boleh langsung publish otomatis
- output AI wajib masuk status `draft` atau `review`
- wajib ada human review
- prompt dan sumber data harus tercatat bila fitur berkembang

### 8.2 Scraping Legal Only

Tujuan:

- mengumpulkan ringkasan dari sumber legal untuk bahan editorial

Aturan keras:

- hanya ambil ringkasan, metadata, dan sumber
- jangan salin penuh artikel pihak lain
- selalu simpan URL sumber
- hasil scraping tidak boleh auto-publish
- semua hasil masuk ke review internal

### 8.3 AMP

Tujuan:

- menyajikan halaman ringan dan cepat untuk distribusi tertentu

Aturan:

- halaman AMP minimal untuk detail artikel
- script pihak ketiga dibatasi
- layout harus konsisten dengan identitas brand utama

### 8.4 PWA

Tujuan:

- website bisa di-install ke HP
- caching halaman penting
- mendukung pengalaman mobile yang lebih baik

Komponen:

- manifest
- service worker
- icon set
- offline fallback minimal

### 8.5 Multi Language

Target:

- Bahasa Indonesia sebagai default
- bahasa lain bisa ditambahkan

Aturan:

- konten inti bisa ditranslasi bertahap
- URL structure untuk multi-language harus diputuskan sebelum produksi penuh
- fase awal boleh fokus ke UI translation dulu, lalu article translation di fase berikutnya

## 9. UI/UX Guideline

- mobile first
- clean
- tidak banyak widget
- fokus baca

Pedoman operasional:

- prioritas ruang baca lebih besar daripada blok promosi
- iklan tidak boleh merusak kenyamanan membaca
- headline harus jelas
- tipografi mudah dibaca
- jarak antar elemen cukup
- hindari layout ramai
- komponen interaktif harus ringan
- dark mode opsional, bukan prioritas fase awal

## 10. SEO System

Wajib tersedia:

- slug SEO
- meta tag
- sitemap
- robots.txt
- schema JSON-LD

Detail implementasi:

- slug otomatis dari title, dapat diedit manual oleh admin/editor
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

## 11. Monetization

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

## 12. TODO System

Format wajib:

- nama task
- status
- file terkait
- catatan

### 12.1 DONE

- [x] Menetapkan `docs/PROJECT_MASTER_DOC.md` sebagai single source of truth
  - Status: `DONE`
  - File terkait: `docs/PROJECT_MASTER_DOC.md`
  - Catatan: Dokumen master awal selesai dibuat pada fase inisialisasi proyek

### 12.2 NEED VERIFICATION

- [ ] Verifikasi versi runtime hosting target
  - Status: `NEED VERIFICATION`
  - File terkait: `N/A`
  - Catatan: Pastikan shared hosting mendukung PHP 8.3 atau 8.4, MySQL memadai, dan akses cron/queue tersedia

- [ ] Verifikasi dukungan Redis di environment produksi
  - Status: `NEED VERIFICATION`
  - File terkait: `config/cache.php`, `config/queue.php`
  - Catatan: Jika tidak tersedia, siapkan fallback `file` dan `database`

- [ ] Verifikasi apakah AMP masih diperlukan untuk strategi distribusi target
  - Status: `NEED VERIFICATION`
  - File terkait: `routes`, `resources/views`
  - Catatan: Fitur tetap dipertahankan di docs, implementasi menunggu validasi bisnis

- [ ] Verifikasi kebutuhan legal untuk fitur scraping
  - Status: `NEED VERIFICATION`
  - File terkait: `app/Services/Scraping`
  - Catatan: Hanya legal summary, tidak boleh melanggar hak cipta atau terms sumber

### 12.3 TODO

- [ ] Inisialisasi repository Git
  - Status: `TODO`
  - File terkait: `.git`
  - Catatan: Diperlukan agar workflow commit tanpa push dapat dijalankan disiplin

- [ ] Install Laravel fresh versi 12.x
  - Status: `TODO`
  - File terkait: `composer.json`, `artisan`, `app/`, `bootstrap/`, `config/`, `database/`, `public/`, `resources/`, `routes/`
  - Catatan: Langkah pertama setelah docs untuk memulai proyek riil

- [ ] Setup `.env` awal dan database connection
  - Status: `TODO`
  - File terkait: `.env`
  - Catatan: Termasuk `APP_NAME`, `APP_BRAND_NAME`, DB, cache, queue, mail

- [ ] Implement auth dasar: login, register, logout
  - Status: `TODO`
  - File terkait: `routes`, `app/Http/Controllers`, `resources/views`
  - Catatan: Tujuan awal untuk uji shared hosting dan keamanan DB

- [ ] Implement CRUD sederhana artikel
  - Status: `TODO`
  - File terkait: `app/Models/Article.php`, `app/Http/Controllers`, `resources/views`, `database/migrations`
  - Catatan: Fokus ke create, read, update, delete, status draft/review/published

- [ ] Buat migration untuk tabel inti
  - Status: `TODO`
  - File terkait: `database/migrations`
  - Catatan: `users`, `categories`, `articles`, `comments`, `ads`, `settings`, `subscribers`, `languages`, `translations`

- [ ] Buat seed data admin awal dan category awal
  - Status: `TODO`
  - File terkait: `database/seeders`
  - Catatan: Minimal super admin, admin, editor, penulis, dan kategori dasar

- [ ] Siapkan admin panel minimal
  - Status: `TODO`
  - File terkait: `routes/admin.php`, `resources/views/admin`, `app/Http/Controllers/Admin`
  - Catatan: Fokus pada artikel, kategori, setting awal

## 13. Changelog

Semua perubahan proyek wajib dicatat. Jika belum ada file changelog terpisah, catat di bagian ini.

### 2026-04-01

- Membuat dokumen master `docs/PROJECT_MASTER_DOC.md`
- Menetapkan docs sebagai pusat keputusan proyek
- Mendefinisikan:
  - visi proyek
  - role system
  - target stack
  - arsitektur sistem
  - struktur folder
  - desain database
  - fitur inti dan lanjutan
  - guideline UI/UX
  - SEO
  - monetisasi
  - TODO system
  - protokol pengembangan aman
- Mencatat kondisi aktual bahwa workspace masih kosong dan belum ada Git repo aktif

## 14. Safe Development Protocol

Aturan ini wajib dipatuhi oleh semua developer dan AI executor.

### 14.1 Protokol Inti

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

### 14.2 Aturan Perubahan

- jika fitur belum dibuat, tulis sebagai `TODO`, bukan seolah sudah jadi
- jika ada asumsi, tandai sebagai asumsi
- jika ada area meragukan, masukkan ke `NEED VERIFICATION`
- setiap perubahan signifikan harus memperbarui dokumen ini

### 14.3 Aturan AI Baru

AI baru yang melanjutkan proyek wajib:

1. Membaca `docs/PROJECT_MASTER_DOC.md`
2. Memahami status aktual proyek
3. Memilih satu task TODO
4. Mengimplementasikan task
5. Memperbarui docs dan changelog
6. Commit tanpa push

### 14.4 Aturan Keamanan Sistem

- validasi input wajib
- auth dan authorization wajib
- jangan tampilkan data sensitif ke publik
- semua konfigurasi brand harus berbasis env/config
- gunakan migration, bukan edit database manual tanpa jejak

## 15. Workflow Wajib

Workflow operasional harian:

1. Baca docs
2. Cek TODO
3. Kerjakan 1 task kecil sampai selesai
4. Verifikasi hasil
5. Update docs
6. Update changelog
7. Commit
8. Jangan push

## 16. Step Awal Implementasi

Setelah dokumen ini, urutan kerja awal yang wajib:

1. Install Laravel fresh
2. Setup DB
3. Buat login
4. Buat register
5. Buat logout
6. Buat CRUD sederhana artikel

Tujuan:

- test shared hosting
- pastikan DB aman
- validasi flow auth dasar
- validasi bahwa panel admin dan frontend bisa berjalan dari fondasi minimal

## 17. Prinsip Utama Proyek

- Docs adalah pusat sistem
- Kode mengikuti docs
- AI baru wajib baca docs dulu
- Tidak boleh merusak sistem lama
- Semua perubahan harus bisa dijelaskan
- Semua perubahan harus bisa ditelusuri

## 18. Keputusan Teknis Awal

- Brand name fleksibel melalui `.env`
- Laravel Blade + Alpine diprioritaskan pada fase awal
- Admin panel dan frontend dipisah jelas
- Database dirancang untuk editorial workflow, SEO, monetisasi, dan ekspansi bahasa
- Shared hosting compatibility adalah constraint nyata, bukan asumsi opsional
- Implementasi awal harus sesederhana mungkin tetapi tidak boleh mengunci arsitektur menjadi buruk

## 19. Status Nyata Saat Ini

Status aktual per `2026-04-01`:

- Dokumen master: ada
- Laravel app: belum ada
- Git repository: belum ada
- Database schema: baru dalam bentuk desain dokumen
- Auth system: belum ada
- CRUD artikel: belum ada
- Admin panel: belum ada
- Frontend publik: belum ada

Kesimpulan:

Proyek saat ini berada pada fase `documentation and foundation planning`, bukan fase aplikasi berjalan.
