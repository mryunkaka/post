# RECOVERY

## Tujuan

Dokumen ini menjelaskan prosedur backup dan restore baseline untuk deployment shared hosting atau server kecil proyek `TodakSiring`.

## Cakupan Backup

- Backup database dibuat harian melalui command `php artisan backup:database`
- Backup media dibuat terpisah melalui command `php artisan backup:media`
- Retensi lokal minimum `7 hari`
- Backup lokal disimpan di `storage/app/private/backups`
- Remote copy bersifat opsional melalui `BACKUP_REMOTE_DISK`

## Scheduler

Pastikan scheduler server menjalankan:

```bash
php artisan schedule:run
```

Task yang dijadwalkan:

- `backup:database` pada `BACKUP_DATABASE_SCHEDULE` default `02:00`
- `backup:media` pada `BACKUP_MEDIA_SCHEDULE` default `02:30`
- `backup:prune` pada `BACKUP_PRUNE_SCHEDULE` default `03:00`

## Variabel Environment

Variabel yang relevan:

- `BACKUP_DISK=local`
- `BACKUP_MEDIA_DISK=public`
- `BACKUP_REMOTE_DISK=` bila ingin copy ke disk lain seperti `s3`
- `BACKUP_RETENTION_DAYS=7`
- `BACKUP_DATABASE_BINARY=mysqldump`
- `BACKUP_DATABASE_TIMEOUT=300`
- `BACKUP_DATABASE_PATH=backups/database`
- `BACKUP_MEDIA_PATH=backups/media`

## Prosedur Backup Manual

### Backup Database

```bash
php artisan backup:database
```

Hasil file:

- `storage/app/private/backups/database/YYYY-MM-DD_HHMMSS.sql`

### Backup Media

```bash
php artisan backup:media
```

Hasil file:

- `storage/app/private/backups/media/YYYY-MM-DD_HHMMSS.zip`

### Prune Retensi

```bash
php artisan backup:prune
```

Command ini menghapus backup lokal dan remote yang lebih tua dari retensi yang dikonfigurasi.

## Restore Database

1. Pilih file backup SQL yang sesuai dari folder `backups/database`
2. Pastikan aplikasi masuk maintenance mode bila restore dilakukan ke environment produksi
3. Siapkan database target kosong atau backup database lama sebelum overwrite
4. Import file SQL dengan salah satu metode:

```bash
mysql -h DB_HOST -P DB_PORT -u DB_USERNAME -p DB_DATABASE < backup.sql
```

Atau melalui phpMyAdmin import bila akses CLI terbatas.

5. Jalankan verifikasi login admin dan cek halaman publik utama setelah import selesai

## Restore Media

1. Pilih file zip dari folder `backups/media`
2. Extract arsip ke isi disk `public`, yaitu target `storage/app/public`
3. Pastikan struktur folder artikel tetap sama seperti sebelum backup
4. Verifikasi beberapa URL media publik setelah restore

## Urutan Recovery Minimum

Jika harus recovery penuh:

1. Aktifkan maintenance mode
2. Restore database
3. Restore media
4. Bersihkan cache aplikasi bila perlu
5. Verifikasi login admin, homepage, detail artikel, dan media publik
6. Matikan maintenance mode

## Catatan Operasional

- Backup database mengandalkan binary `mysqldump` yang harus tersedia di PATH server atau dikonfigurasi via `BACKUP_DATABASE_BINARY`
- Jika remote disk dipakai, pastikan kredensial disk tersebut valid sebelum mengandalkan backup remote
- Simpan setidaknya satu salinan backup di luar server utama untuk mitigasi kegagalan disk lokal
