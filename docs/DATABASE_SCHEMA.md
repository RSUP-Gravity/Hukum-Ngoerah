# 📊 Dokumentasi Skema Database - Sistem Manajemen Dokumen Hukum Terpusat

> **Versi:** 1.0  
> **Terakhir Diperbarui:** Januari 2025  
> **Database:** MySQL 8.0  
> **Character Set:** utf8mb4_unicode_ci

---

## 📋 Daftar Isi

1. [Gambaran Umum](#gambaran-umum)
2. [Entity Relationship Diagram](#entity-relationship-diagram)
3. [Tabel Inti](#tabel-inti)
4. [Tabel Master Data](#tabel-master-data)
5. [Tabel Dokumen](#tabel-dokumen)
6. [Tabel Audit & Logging](#tabel-audit-logging)
7. [Tabel Sistem](#tabel-sistem)
8. [Indexes & Performance](#indexes-performance)

---

## 📌 Gambaran Umum

Database `hukum_ngoerah` terdiri dari **20+ tabel** yang dikelompokkan sebagai berikut:

| Kategori | Jumlah Tabel | Keterangan |
|----------|--------------|------------|
| Core | 4 | Users, Roles, Permissions, Sessions |
| Master Data | 4 | Directorates, Units, Positions, Document Types |
| Documents | 6 | Documents, Versions, Categories, Templates, Access, Approvals |
| Audit & Logs | 3 | Audit Logs, Document History, Notifications |
| System | 4 | System Settings, Cache, Jobs, Password Resets |

---

## 🔗 Entity Relationship Diagram

```
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│   roles     │────<│    users    │>────│    units    │
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │                   │
       │                   │                   │
       ▼                   ▼                   ▼
┌─────────────┐     ┌─────────────┐     ┌─────────────┐
│ permissions │     │  documents  │     │ directorates│
└─────────────┘     └─────────────┘     └─────────────┘
       │                   │
       │            ┌──────┴──────┐
       ▼            ▼             ▼
┌─────────────┐  ┌──────────┐  ┌──────────┐
│ role_perms  │  │ versions │  │ approvals│
└─────────────┘  └──────────┘  └──────────┘
                      │
                      ▼
              ┌─────────────┐
              │   history   │
              └─────────────┘
```

---

## 👤 Tabel Inti

### users

Menyimpan data pengguna sistem.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `username` | VARCHAR(50) | NO | - | Username unik untuk login |
| `employee_id` | VARCHAR(30) | YES | NULL | NIP/ID Pegawai |
| `name` | VARCHAR(255) | NO | - | Nama lengkap |
| `email` | VARCHAR(255) | YES | NULL | Email (opsional) |
| `email_verified_at` | TIMESTAMP | YES | NULL | Waktu verifikasi email |
| `password` | VARCHAR(255) | NO | - | Password terenkripsi (bcrypt) |
| `role_id` | BIGINT UNSIGNED | YES | NULL | FK ke roles |
| `unit_id` | BIGINT UNSIGNED | YES | NULL | FK ke units |
| `position_id` | BIGINT UNSIGNED | YES | NULL | FK ke positions |
| `phone` | VARCHAR(20) | YES | NULL | Nomor telepon |
| `avatar` | VARCHAR(255) | YES | NULL | Path foto profil |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `must_change_password` | BOOLEAN | NO | FALSE | Wajib ganti password |
| `last_login_at` | TIMESTAMP | YES | NULL | Waktu login terakhir |
| `last_login_ip` | VARCHAR(45) | YES | NULL | IP login terakhir |
| `password_changed_at` | TIMESTAMP | YES | NULL | Waktu ganti password |
| `remember_token` | VARCHAR(100) | YES | NULL | Token remember me |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Indexes:**
- `PRIMARY KEY (id)`
- `UNIQUE (username)`
- `UNIQUE (employee_id)`
- `UNIQUE (email)`
- `INDEX (is_active)`
- `INDEX (role_id)`
- `INDEX (unit_id)`

**Foreign Keys:**
- `role_id` → `roles(id)` ON DELETE SET NULL
- `unit_id` → `units(id)` ON DELETE SET NULL
- `position_id` → `positions(id)` ON DELETE SET NULL

---

### roles

Menyimpan data role/peran pengguna.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `name` | VARCHAR(50) | NO | - | Nama role (slug) |
| `display_name` | VARCHAR(100) | NO | - | Nama tampilan |
| `description` | TEXT | YES | NULL | Deskripsi role |
| `level` | INTEGER | NO | 0 | Level hierarki (0=terendah) |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Default Roles:**
| ID | Name | Display Name | Level |
|----|------|--------------|-------|
| 1 | super_admin | Super Administrator | 100 |
| 2 | admin | Administrator | 80 |
| 3 | hukum_staff | Staff Hukum | 60 |
| 4 | unit_head | Kepala Unit | 40 |
| 5 | executive | Eksekutif | 30 |
| 6 | general_user | Pengguna Umum | 10 |

---

### permissions

Menyimpan daftar hak akses sistem.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `name` | VARCHAR(100) | NO | - | Nama permission (slug) |
| `display_name` | VARCHAR(150) | NO | - | Nama tampilan |
| `module` | VARCHAR(50) | NO | - | Modul: documents, users, master_data, admin, reports |
| `description` | TEXT | YES | NULL | Deskripsi permission |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |

**Contoh Permissions:**
| Module | Name | Display Name |
|--------|------|--------------|
| documents | documents.view | Lihat Dokumen |
| documents | documents.create | Buat Dokumen |
| documents | documents.edit | Edit Dokumen |
| documents | documents.delete | Hapus Dokumen |
| documents | documents.download | Unduh Dokumen |
| documents | documents.approve | Approve Dokumen |
| users | users.view | Lihat User |
| users | users.manage | Kelola User |
| reports | reports.view | Lihat Laporan |
| reports | reports.export | Export Laporan |

---

### role_permissions

Tabel pivot untuk relasi role dan permissions (Many-to-Many).

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `role_id` | BIGINT UNSIGNED | NO | - | FK ke roles |
| `permission_id` | BIGINT UNSIGNED | NO | - | FK ke permissions |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |

**Indexes:**
- `UNIQUE (role_id, permission_id)`

---

## 🏢 Tabel Master Data

### directorates

Menyimpan data direktorat/bidang.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `code` | VARCHAR(20) | NO | - | Kode direktorat (unik) |
| `name` | VARCHAR(150) | NO | - | Nama direktorat |
| `description` | TEXT | YES | NULL | Deskripsi |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `sort_order` | INTEGER | NO | 0 | Urutan tampilan |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

---

### units

Menyimpan data unit kerja (bagian dari direktorat).

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `directorate_id` | BIGINT UNSIGNED | NO | - | FK ke directorates |
| `code` | VARCHAR(20) | NO | - | Kode unit (unik) |
| `name` | VARCHAR(150) | NO | - | Nama unit |
| `description` | TEXT | YES | NULL | Deskripsi |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `sort_order` | INTEGER | NO | 0 | Urutan tampilan |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Foreign Keys:**
- `directorate_id` → `directorates(id)` ON DELETE RESTRICT

---

### positions

Menyimpan data jabatan.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `name` | VARCHAR(150) | NO | - | Nama jabatan |
| `description` | TEXT | YES | NULL | Deskripsi |
| `level` | INTEGER | NO | 0 | Level jabatan |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

---

### document_types

Menyimpan jenis/tipe dokumen.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `code` | VARCHAR(20) | NO | - | Kode tipe (unik) |
| `name` | VARCHAR(150) | NO | - | Nama tipe dokumen |
| `description` | TEXT | YES | NULL | Deskripsi |
| `prefix` | VARCHAR(10) | NO | - | Prefix nomor dokumen |
| `requires_approval` | BOOLEAN | NO | TRUE | Memerlukan approval |
| `has_expiry` | BOOLEAN | NO | FALSE | Memiliki tanggal kadaluarsa |
| `default_retention_days` | INTEGER | YES | NULL | Masa retensi default (hari) |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `sort_order` | INTEGER | NO | 0 | Urutan tampilan |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Contoh Document Types:**
| Code | Name | Prefix |
|------|------|--------|
| PERDIR | Peraturan Direktur | PD |
| SK | Surat Keputusan | SK |
| SOP | Standard Operating Procedure | SOP |
| KONTRAK | Kontrak/Perjanjian | KTR |
| SURAT | Surat Keluar | SR |

---

### document_categories

Menyimpan kategori/sub-kategori dokumen (bagian dari document_type).

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `document_type_id` | BIGINT UNSIGNED | NO | - | FK ke document_types |
| `code` | VARCHAR(20) | NO | - | Kode kategori (unik) |
| `name` | VARCHAR(150) | NO | - | Nama kategori |
| `description` | TEXT | YES | NULL | Deskripsi |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `sort_order` | INTEGER | NO | 0 | Urutan tampilan |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

---

## 📄 Tabel Dokumen

### documents

Tabel utama untuk menyimpan data dokumen.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `document_number` | VARCHAR(100) | NO | - | Nomor dokumen (unik) |
| `title` | VARCHAR(255) | NO | - | Judul dokumen |
| `description` | TEXT | YES | NULL | Deskripsi/ringkasan |
| `document_type_id` | BIGINT UNSIGNED | NO | - | FK ke document_types |
| `document_category_id` | BIGINT UNSIGNED | YES | NULL | FK ke document_categories |
| `directorate_id` | BIGINT UNSIGNED | YES | NULL | FK ke directorates |
| `unit_id` | BIGINT UNSIGNED | YES | NULL | FK ke units |
| `effective_date` | DATE | YES | NULL | Tanggal berlaku |
| `expiry_date` | DATE | YES | NULL | Tanggal kadaluarsa |
| `review_date` | DATE | YES | NULL | Tanggal review berikutnya |
| `retention_days` | INTEGER | YES | NULL | Masa retensi (hari) |
| `status` | ENUM | NO | 'draft' | Status dokumen |
| `rejection_reason` | TEXT | YES | NULL | Alasan penolakan |
| `current_version` | INTEGER | NO | 1 | Versi saat ini |
| `is_locked` | BOOLEAN | NO | FALSE | Dikunci (dalam proses approval) |
| `confidentiality` | ENUM | NO | 'internal' | Tingkat kerahasiaan |
| `keywords` | TEXT | YES | NULL | Kata kunci untuk pencarian |
| `created_by` | BIGINT UNSIGNED | NO | - | FK ke users (pembuat) |
| `updated_by` | BIGINT UNSIGNED | YES | NULL | FK ke users (pengupdate) |
| `approved_by` | BIGINT UNSIGNED | YES | NULL | FK ke users (approver) |
| `approved_at` | TIMESTAMP | YES | NULL | Waktu diapprove |
| `published_by` | BIGINT UNSIGNED | YES | NULL | FK ke users (publisher) |
| `published_at` | TIMESTAMP | YES | NULL | Waktu dipublish |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Status Values:**
| Status | Keterangan |
|--------|------------|
| `draft` | Draf, belum disubmit |
| `pending_review` | Menunggu review |
| `pending_approval` | Menunggu approval |
| `approved` | Sudah diapprove |
| `published` | Sudah dipublish |
| `expired` | Sudah kadaluarsa |
| `archived` | Diarsipkan |
| `rejected` | Ditolak |

**Confidentiality Values:**
| Level | Keterangan |
|-------|------------|
| `public` | Dapat diakses semua orang |
| `internal` | Hanya internal RS |
| `confidential` | Rahasia, akses terbatas |
| `restricted` | Sangat rahasia |

**Indexes:**
- `INDEX (document_type_id)`
- `INDEX (document_category_id)`
- `INDEX (directorate_id)`
- `INDEX (unit_id)`
- `INDEX (status)`
- `INDEX (expiry_date)`
- `INDEX (created_by)`
- `FULLTEXT (title, description, keywords)`

---

### document_versions

Menyimpan versi-versi file dokumen.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `document_id` | BIGINT UNSIGNED | NO | - | FK ke documents |
| `version_number` | INTEGER | NO | - | Nomor versi |
| `file_path` | VARCHAR(500) | NO | - | Path file di storage |
| `file_name` | VARCHAR(255) | NO | - | Nama file asli |
| `file_type` | VARCHAR(50) | NO | - | Tipe file (pdf, docx, dll) |
| `file_size` | BIGINT | NO | - | Ukuran file (bytes) |
| `file_hash` | VARCHAR(64) | NO | - | SHA-256 hash untuk integritas |
| `change_summary` | TEXT | YES | NULL | Ringkasan perubahan |
| `change_type` | ENUM | NO | 'initial' | Jenis perubahan |
| `is_current` | BOOLEAN | NO | FALSE | Apakah versi aktif |
| `uploaded_by` | BIGINT UNSIGNED | NO | - | FK ke users |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

**Change Type Values:**
| Type | Keterangan |
|------|------------|
| `initial` | Upload pertama |
| `minor` | Perubahan kecil (typo, format) |
| `major` | Perubahan besar (konten) |
| `correction` | Koreksi/perbaikan |

---

### document_approvals

Menyimpan workflow approval dokumen.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `document_id` | BIGINT UNSIGNED | NO | - | FK ke documents |
| `document_version_id` | BIGINT UNSIGNED | YES | NULL | FK ke document_versions |
| `sequence` | INTEGER | NO | 1 | Urutan dalam approval chain |
| `approver_id` | BIGINT UNSIGNED | NO | - | FK ke users (approver) |
| `delegated_to` | BIGINT UNSIGNED | YES | NULL | FK ke users (delegasi) |
| `status` | ENUM | NO | 'pending' | Status approval |
| `comments` | TEXT | YES | NULL | Komentar/catatan |
| `responded_at` | TIMESTAMP | YES | NULL | Waktu response |
| `due_date` | TIMESTAMP | YES | NULL | Batas waktu |
| `is_overdue` | BOOLEAN | NO | FALSE | Sudah melewati deadline |
| `reminder_count` | INTEGER | NO | 0 | Jumlah reminder terkirim |
| `last_reminder_at` | TIMESTAMP | YES | NULL | Waktu reminder terakhir |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |

**Status Values:**
| Status | Keterangan |
|--------|------------|
| `pending` | Menunggu response |
| `approved` | Disetujui |
| `rejected` | Ditolak |
| `skipped` | Dilewati |
| `delegated` | Didelegasikan |

---

### document_access

Menyimpan hak akses spesifik per dokumen.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `document_id` | BIGINT UNSIGNED | NO | - | FK ke documents |
| `user_id` | BIGINT UNSIGNED | YES | NULL | FK ke users |
| `role_id` | BIGINT UNSIGNED | YES | NULL | FK ke roles |
| `unit_id` | BIGINT UNSIGNED | YES | NULL | FK ke units |
| `directorate_id` | BIGINT UNSIGNED | YES | NULL | FK ke directorates |
| `permission` | ENUM | NO | 'view' | Jenis akses |
| `valid_from` | TIMESTAMP | YES | NULL | Berlaku mulai |
| `valid_until` | TIMESTAMP | YES | NULL | Berlaku sampai |
| `granted_by` | BIGINT UNSIGNED | NO | - | FK ke users (pemberi akses) |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |

**Permission Values:**
| Permission | Keterangan |
|------------|------------|
| `view` | Hanya lihat |
| `download` | Boleh download |
| `edit` | Boleh edit |
| `delete` | Boleh hapus |
| `approve` | Boleh approve |
| `full` | Akses penuh |

---

### document_templates

Menyimpan template dokumen.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `name` | VARCHAR(150) | NO | - | Nama template |
| `description` | TEXT | YES | NULL | Deskripsi |
| `document_type_id` | BIGINT UNSIGNED | NO | - | FK ke document_types |
| `file_path` | VARCHAR(500) | NO | - | Path file template |
| `file_name` | VARCHAR(255) | NO | - | Nama file |
| `file_type` | VARCHAR(50) | NO | - | Tipe file |
| `is_active` | BOOLEAN | NO | TRUE | Status aktif |
| `sort_order` | INTEGER | NO | 0 | Urutan tampilan |
| `created_by` | BIGINT UNSIGNED | NO | - | FK ke users |
| `updated_by` | BIGINT UNSIGNED | YES | NULL | FK ke users |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |
| `deleted_at` | TIMESTAMP | YES | NULL | Soft delete |

---

## 📝 Tabel Audit & Logging

### document_history

Menyimpan riwayat perubahan dokumen.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `document_id` | BIGINT UNSIGNED | NO | - | FK ke documents |
| `document_version_id` | BIGINT UNSIGNED | YES | NULL | FK ke document_versions |
| `action` | ENUM | NO | - | Jenis aksi |
| `old_status` | VARCHAR(50) | YES | NULL | Status sebelum |
| `new_status` | VARCHAR(50) | YES | NULL | Status sesudah |
| `notes` | TEXT | YES | NULL | Catatan |
| `changes` | JSON | YES | NULL | Detail perubahan (JSON) |
| `performed_by` | BIGINT UNSIGNED | NO | - | FK ke users |
| `ip_address` | VARCHAR(45) | YES | NULL | IP address |
| `user_agent` | VARCHAR(500) | YES | NULL | Browser/device info |
| `created_at` | TIMESTAMP | YES | NULL | Waktu aksi |

**Action Values:**
| Action | Keterangan |
|--------|------------|
| `created` | Dokumen dibuat |
| `updated` | Dokumen diupdate |
| `version_uploaded` | Versi baru diupload |
| `submitted_for_review` | Dikirim untuk review |
| `reviewed` | Direview |
| `submitted_for_approval` | Dikirim untuk approval |
| `approved` | Diapprove |
| `rejected` | Ditolak |
| `published` | Dipublish |
| `unpublished` | Unpublish |
| `archived` | Diarsipkan |
| `restored` | Dikembalikan |
| `deleted` | Dihapus |
| `viewed` | Dilihat |
| `downloaded` | Didownload |
| `printed` | Dicetak |
| `shared` | Dibagikan |
| `locked` | Dikunci |
| `unlocked` | Dibuka kuncinya |
| `comment_added` | Komentar ditambah |
| `status_changed` | Status berubah |

---

### audit_logs

Menyimpan log audit seluruh sistem.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `user_id` | BIGINT UNSIGNED | YES | NULL | FK ke users |
| `username` | VARCHAR(50) | YES | NULL | Username (backup jika user dihapus) |
| `action` | VARCHAR(50) | NO | - | Jenis aksi |
| `module` | VARCHAR(50) | NO | - | Modul: documents, users, master_data, auth, admin |
| `entity_type` | VARCHAR(50) | YES | NULL | Nama model/class |
| `entity_id` | BIGINT UNSIGNED | YES | NULL | ID entity |
| `entity_name` | VARCHAR(255) | YES | NULL | Nama entity (human readable) |
| `old_values` | JSON | YES | NULL | Nilai lama |
| `new_values` | JSON | YES | NULL | Nilai baru |
| `description` | TEXT | YES | NULL | Deskripsi aksi |
| `ip_address` | VARCHAR(45) | YES | NULL | IP address |
| `user_agent` | VARCHAR(500) | YES | NULL | Browser/device info |
| `url` | VARCHAR(500) | YES | NULL | URL yang diakses |
| `method` | VARCHAR(10) | YES | NULL | HTTP method |
| `session_id` | VARCHAR(100) | YES | NULL | Session ID |
| `created_at` | TIMESTAMP | YES | NULL | Waktu aksi |

**Indexes:**
- `INDEX (user_id)`
- `INDEX (action)`
- `INDEX (module)`
- `INDEX (entity_type, entity_id)`
- `INDEX (created_at)`
- `INDEX (ip_address)`

---

### notifications

Menyimpan notifikasi untuk pengguna.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `user_id` | BIGINT UNSIGNED | NO | - | FK ke users |
| `type` | VARCHAR(50) | NO | - | Jenis notifikasi |
| `title` | VARCHAR(255) | NO | - | Judul notifikasi |
| `message` | TEXT | NO | - | Isi pesan |
| `entity_type` | VARCHAR(50) | YES | NULL | Tipe entity terkait |
| `entity_id` | BIGINT UNSIGNED | YES | NULL | ID entity terkait |
| `action_url` | VARCHAR(500) | YES | NULL | URL untuk action |
| `priority` | ENUM | NO | 'normal' | Prioritas |
| `is_read` | BOOLEAN | NO | FALSE | Sudah dibaca |
| `read_at` | TIMESTAMP | YES | NULL | Waktu dibaca |
| `email_sent` | BOOLEAN | NO | FALSE | Email terkirim |
| `email_sent_at` | TIMESTAMP | YES | NULL | Waktu email terkirim |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |

**Type Values:**
| Type | Keterangan |
|------|------------|
| `document_approval` | Request approval dokumen |
| `document_approved` | Dokumen diapprove |
| `document_rejected` | Dokumen ditolak |
| `document_expired` | Dokumen kadaluarsa |
| `document_expiring` | Dokumen akan kadaluarsa |
| `reminder` | Pengingat umum |
| `system` | Notifikasi sistem |

**Priority Values:**
| Priority | Keterangan |
|----------|------------|
| `low` | Prioritas rendah |
| `normal` | Prioritas normal |
| `high` | Prioritas tinggi |
| `urgent` | Sangat penting |

---

## ⚙️ Tabel Sistem

### system_settings

Menyimpan konfigurasi sistem.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | BIGINT UNSIGNED | NO | AUTO_INCREMENT | Primary Key |
| `key` | VARCHAR(100) | NO | - | Kunci setting (unik) |
| `value` | TEXT | YES | NULL | Nilai setting |
| `type` | VARCHAR(20) | NO | 'string' | Tipe data |
| `group` | VARCHAR(50) | NO | 'general' | Grup setting |
| `label` | VARCHAR(150) | YES | NULL | Label untuk UI |
| `description` | TEXT | YES | NULL | Deskripsi |
| `is_public` | BOOLEAN | NO | FALSE | Dapat diakses tanpa login |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |
| `updated_at` | TIMESTAMP | YES | NULL | Waktu diupdate |

**Type Values:**
- `string` - Teks biasa
- `integer` - Angka
- `boolean` - True/False
- `json` - JSON object
- `array` - Array

**Group Values:**
- `general` - Pengaturan umum
- `security` - Keamanan
- `documents` - Dokumen
- `email` - Email
- `appearance` - Tampilan

---

### sessions

Menyimpan session pengguna (Laravel default).

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `id` | VARCHAR(255) | NO | - | Session ID (Primary Key) |
| `user_id` | BIGINT UNSIGNED | YES | NULL | FK ke users |
| `ip_address` | VARCHAR(45) | YES | NULL | IP address |
| `user_agent` | TEXT | YES | NULL | Browser/device info |
| `payload` | LONGTEXT | NO | - | Session data (serialized) |
| `last_activity` | INTEGER | NO | - | Unix timestamp aktivitas terakhir |

---

### password_reset_tokens

Menyimpan token reset password.

| Kolom | Tipe | Null | Default | Keterangan |
|-------|------|------|---------|------------|
| `email` | VARCHAR(255) | NO | - | Email (Primary Key) |
| `token` | VARCHAR(255) | NO | - | Token hash |
| `created_at` | TIMESTAMP | YES | NULL | Waktu dibuat |

---

### cache / cache_locks

Tabel untuk Laravel cache (jika menggunakan database driver).

**cache:**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `key` | VARCHAR(255) | Cache key (Primary) |
| `value` | MEDIUMTEXT | Cache value |
| `expiration` | INTEGER | Expiration timestamp |

**cache_locks:**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `key` | VARCHAR(255) | Lock key (Primary) |
| `owner` | VARCHAR(255) | Lock owner |
| `expiration` | INTEGER | Expiration timestamp |

---

### jobs / job_batches / failed_jobs

Tabel untuk Laravel Queue.

**jobs:**
| Kolom | Tipe | Keterangan |
|-------|------|------------|
| `id` | BIGINT | Job ID |
| `queue` | VARCHAR(255) | Queue name |
| `payload` | LONGTEXT | Job payload |
| `attempts` | TINYINT | Jumlah percobaan |
| `reserved_at` | INTEGER | Waktu reserved |
| `available_at` | INTEGER | Waktu available |
| `created_at` | INTEGER | Waktu dibuat |

---

## 📈 Indexes & Performance

### Tips Optimasi

1. **Full-Text Search**
   ```sql
   ALTER TABLE documents ADD FULLTEXT INDEX ft_search (title, description, keywords);
   ```

2. **Composite Indexes untuk Query Umum**
   ```sql
   -- Untuk filter dokumen
   CREATE INDEX idx_doc_filter ON documents(status, document_type_id, created_at);
   
   -- Untuk approval queue
   CREATE INDEX idx_approval_queue ON document_approvals(approver_id, status, due_date);
   ```

3. **Partitioning untuk Audit Logs**
   ```sql
   -- Partisi per bulan untuk audit_logs
   ALTER TABLE audit_logs PARTITION BY RANGE (YEAR(created_at) * 100 + MONTH(created_at)) (
       PARTITION p202401 VALUES LESS THAN (202402),
       PARTITION p202402 VALUES LESS THAN (202403),
       ...
   );
   ```

### Query Examples

**Dokumen yang akan kadaluarsa dalam 30 hari:**
```sql
SELECT * FROM documents 
WHERE status = 'published' 
AND expiry_date BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 30 DAY);
```

**Pending approvals per user:**
```sql
SELECT u.name, COUNT(*) as pending 
FROM document_approvals da
JOIN users u ON da.approver_id = u.id
WHERE da.status = 'pending'
GROUP BY u.id;
```

**Download statistics per dokumen:**
```sql
SELECT d.title, COUNT(*) as download_count
FROM document_history dh
JOIN documents d ON dh.document_id = d.id
WHERE dh.action = 'downloaded'
GROUP BY d.id
ORDER BY download_count DESC
LIMIT 10;
```

---

*Dokumen ini adalah bagian dari Sistem Manajemen Dokumen Hukum Terpusat RS Ngoerah*
