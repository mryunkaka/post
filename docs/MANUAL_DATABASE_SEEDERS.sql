-- TodakSiring
-- Manual seed SQL for phpMyAdmin import
-- Source of truth implementation: database/seeders
-- Keep this file synchronized with every seeder and relevant model change.

INSERT INTO users (
    name, email, password, role, is_active, created_at, updated_at
) VALUES
(
    'Admin Default',
    'admin@local.test',
    '$2y$12$j77KFipaLwMK..yYf.VpbOKMybEh1tjyzVSe.AKHrhfqlEeLHcxnW',
    'admin',
    1,
    NOW(),
    NOW()
),
(
    'Editor Default',
    'editor@local.test',
    '$2y$12$j77KFipaLwMK..yYf.VpbOKMybEh1tjyzVSe.AKHrhfqlEeLHcxnW',
    'editor',
    1,
    NOW(),
    NOW()
),
(
    'Wartawan Sample',
    'wartawan@local.test',
    '$2y$12$j77KFipaLwMK..yYf.VpbOKMybEh1tjyzVSe.AKHrhfqlEeLHcxnW',
    'wartawan',
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    name = VALUES(name),
    password = VALUES(password),
    role = VALUES(role),
    is_active = VALUES(is_active),
    updated_at = NOW();

INSERT INTO categories (
    parent_id, name, slug, description, sort_order, is_active, seo_title, seo_description, created_at, updated_at
) VALUES
(
    NULL,
    'Berita',
    'berita',
    'Kanal berita utama.',
    1,
    1,
    'Berita',
    'Kanal berita utama.',
    NOW(),
    NOW()
),
(
    NULL,
    'Lokal',
    'lokal',
    'Berita lokal dan daerah.',
    2,
    1,
    'Lokal',
    'Berita lokal dan daerah.',
    NOW(),
    NOW()
),
(
    NULL,
    'Nasional',
    'nasional',
    'Berita nasional.',
    3,
    1,
    'Nasional',
    'Berita nasional.',
    NOW(),
    NOW()
),
(
    NULL,
    'Ekonomi',
    'ekonomi',
    'Berita ekonomi dan bisnis.',
    4,
    1,
    'Ekonomi',
    'Berita ekonomi dan bisnis.',
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    parent_id = VALUES(parent_id),
    name = VALUES(name),
    description = VALUES(description),
    sort_order = VALUES(sort_order),
    is_active = VALUES(is_active),
    seo_title = VALUES(seo_title),
    seo_description = VALUES(seo_description),
    updated_at = NOW();

INSERT INTO settings (
    `group`, `key`, `value`, autoload, created_at, updated_at
) VALUES
(
    'general',
    'site_name',
    'TodakSiring',
    1,
    NOW(),
    NOW()
),
(
    'general',
    'site_description',
    'Portal berita modern berbasis Laravel untuk workflow editorial, SEO, dan monetisasi.',
    1,
    NOW(),
    NOW()
)
ON DUPLICATE KEY UPDATE
    `group` = VALUES(`group`),
    `value` = VALUES(`value`),
    autoload = VALUES(autoload),
    updated_at = NOW();
