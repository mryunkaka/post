-- TodakSiring
-- Manual incremental SQL updates for phpMyAdmin import
-- Source of truth implementation: database/migrations
-- Use this file for EXISTING databases on shared hosting without SSH.
-- Run section by section, not all at once, so errors are easier to isolate.

-- =========================================================
-- A. CREATE BARU
-- =========================================================
-- Tambahan tabel baru untuk pipeline AI newsroom.
-- Jalankan hanya jika tabel `news_candidates` BELUM ADA.

CREATE TABLE IF NOT EXISTS news_candidates (
    id BIGINT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    source_code VARCHAR(120) NOT NULL,
    source_name VARCHAR(180) NOT NULL,
    source_url VARCHAR(1000) NOT NULL,
    source_url_hash CHAR(64) NOT NULL UNIQUE,
    source_published_at TIMESTAMP NULL,
    region VARCHAR(120) NULL,
    title VARCHAR(255) NOT NULL,
    excerpt TEXT NULL,
    image_url VARCHAR(1000) NULL,
    facts_summary TEXT NULL,
    raw_payload JSON NULL,
    status ENUM('pending', 'validated', 'rejected', 'drafted') NOT NULL DEFAULT 'pending',
    rejection_reason TEXT NULL,
    article_id BIGINT UNSIGNED NULL,
    created_at TIMESTAMP NULL,
    updated_at TIMESTAMP NULL,
    CONSTRAINT fk_news_candidates_article
        FOREIGN KEY (article_id) REFERENCES articles(id)
        ON DELETE SET NULL ON UPDATE CASCADE,
    INDEX idx_news_candidates_status_source_published_at (status, source_published_at),
    INDEX idx_news_candidates_source_code (source_code),
    INDEX idx_news_candidates_region (region)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- B. UPDATE STRUKTUR TABEL EXISTING
-- =========================================================
-- Tambahan kolom sumber untuk atribusi artikel AI / legal sourcing.
-- Jalankan satu per satu. Jika phpMyAdmin bilang kolom sudah ada, lewati saja.

ALTER TABLE articles
    ADD COLUMN source_name VARCHAR(180) NULL AFTER featured_image;

ALTER TABLE articles
    ADD COLUMN source_url VARCHAR(1000) NULL AFTER source_name;

ALTER TABLE articles
    ADD COLUMN source_published_at TIMESTAMP NULL AFTER source_url;

-- =========================================================
-- C. YANG DIHAPUS
-- =========================================================
-- Tidak ada tabel/kolom yang dihapus pada update ini.
-- Jadi tidak ada perintah DROP untuk dijalankan.

-- =========================================================
-- D. RINGKASAN PERUBAHAN
-- =========================================================
-- CREATE:
-- - table `news_candidates`
--
-- UPDATE:
-- - add `articles.source_name`
-- - add `articles.source_url`
-- - add `articles.source_published_at`
--
-- DELETE:
-- - tidak ada

