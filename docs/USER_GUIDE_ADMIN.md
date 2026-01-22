# 📘 Panduan Pengguna Admin - Sistem Manajemen Dokumen Hukum

> **Versi:** 1.0  
> **Terakhir Diperbarui:** Januari 2025  
> **Untuk:** Administrator dan Legal Head

---

## 📋 Daftar Isi

1. [Pendahuluan](#pendahuluan)
2. [Login dan Dashboard](#login-dan-dashboard)
3. [Manajemen Dokumen](#manajemen-dokumen)
4. [Manajemen Pengguna](#manajemen-pengguna)
5. [Master Data](#master-data)
6. [Laporan dan Export](#laporan-dan-export)
7. [Pengaturan Sistem](#pengaturan-sistem)

---

## 📖 Pendahuluan

Selamat datang di Sistem Manajemen Dokumen Hukum Terpusat RS Ngoerah. Panduan ini ditujukan untuk Administrator sistem yang memiliki akses penuh untuk mengelola dokumen, pengguna, dan konfigurasi sistem.

### Peran Administrator

Sebagai Admin, Anda memiliki akses untuk:
- Mengelola seluruh dokumen hukum
- Membuat, mengedit, dan menghapus pengguna
- Mengelola data master (jenis dokumen, kategori, unit, dll)
- Menyetujui/menolak dokumen
- Melihat audit log dan laporan
- Mengkonfigurasi pengaturan sistem

---

## 🔐 Login dan Dashboard

### Cara Login

1. Buka aplikasi di browser
2. Masukkan **Username** dan **Password**
3. Centang "Ingat Saya" jika menggunakan perangkat pribadi
4. Klik tombol **Login**

### Dashboard Overview

Dashboard menampilkan:

| Widget | Deskripsi |
|--------|-----------|
| **Total Dokumen** | Jumlah keseluruhan dokumen dalam sistem |
| **Dokumen Aktif** | Dokumen yang masih berlaku |
| **Akan Kadaluarsa** | Dokumen yang akan expired dalam 30 hari |
| **Kadaluarsa** | Dokumen yang sudah melewati tanggal kadaluarsa |

### Widget Quick Actions

- **Tambah Dokumen** - Buat dokumen baru
- **Semua Dokumen** - Lihat daftar semua dokumen
- **Export Laporan** - Buat laporan dalam Excel/PDF
- **Akan Kadaluarsa** - Filter dokumen yang segera kadaluarsa
- **Kadaluarsa** - Filter dokumen yang sudah expired
- **Cari Dokumen** - Buka pencarian cepat (Ctrl+K)

### Notifikasi Login

Saat login, jika ada dokumen kritis (kadaluarsa atau akan segera kadaluarsa), sistem akan menampilkan modal notifikasi dengan daftar dokumen yang memerlukan perhatian.

---

## 📄 Manajemen Dokumen

### Melihat Daftar Dokumen

1. Klik menu **Dokumen** di sidebar
2. Gunakan filter untuk menyaring dokumen:
   - **Search** - Cari berdasarkan nomor/judul
   - **Jenis** - Filter berdasarkan jenis dokumen
   - **Kategori** - Filter berdasarkan kategori
   - **Status** - Draft, Pending, Published, dll
   - **Tanggal** - Rentang tanggal dokumen

### Membuat Dokumen Baru

1. Klik tombol **+ Tambah Dokumen**
2. Isi form dengan informasi berikut:

| Field | Keterangan | Wajib |
|-------|------------|-------|
| Nomor Dokumen | Nomor unik dokumen | ✓ |
| Judul | Judul dokumen | ✓ |
| Jenis Dokumen | Pilih jenis (PKS, MoU, Kontrak, dll) | ✓ |
| Kategori | Pilih kategori | - |
| Tanggal Berlaku | Tanggal mulai berlaku | ✓ |
| Tanggal Kadaluarsa | Tanggal berakhir | - |
| Tingkat Kerahasiaan | Public/Internal/Confidential | ✓ |
| Deskripsi | Keterangan dokumen | - |
| File Dokumen | Upload file PDF (maks 50MB) | ✓ |

3. Klik **Simpan** untuk menyimpan sebagai draft
4. Klik **Simpan & Ajukan** untuk langsung mengajukan review

### Status Dokumen

| Status | Warna | Keterangan |
|--------|-------|------------|
| Draft | Abu-abu | Dokumen belum diajukan |
| Menunggu Review | Biru | Dalam proses review |
| Menunggu Persetujuan | Kuning | Menunggu approval |
| Disetujui | Hijau | Sudah approved |
| Dipublikasikan | Hijau | Dapat diakses publik |
| Ditolak | Merah | Perlu diperbaiki |
| Kadaluarsa | Merah | Sudah expired |
| Diarsipkan | Abu-abu | Tidak aktif |

### Menyetujui/Menolak Dokumen

1. Buka halaman detail dokumen
2. Klik tab **Persetujuan**
3. Pilih **Setujui** atau **Tolak**
4. Jika menolak, masukkan alasan penolakan
5. Klik **Konfirmasi**

### Upload Versi Baru

1. Buka halaman detail dokumen
2. Klik **Upload Versi Baru**
3. Pilih file baru
4. Masukkan catatan perubahan
5. Klik **Upload**

### Bulk Actions

1. Di daftar dokumen, centang beberapa dokumen
2. Pilih aksi dari dropdown:
   - **Arsipkan** - Arsipkan dokumen terpilih
   - **Hapus** - Hapus dokumen terpilih

---

## 👥 Manajemen Pengguna

### Melihat Daftar Pengguna

1. Klik menu **Pengguna** > **Daftar Pengguna**
2. Gunakan filter dan pencarian untuk mencari pengguna

### Membuat Pengguna Baru

1. Klik **+ Tambah Pengguna**
2. Isi form:

| Field | Keterangan |
|-------|------------|
| Username | Username untuk login |
| Nama Lengkap | Nama tampilan |
| Email | Email pengguna |
| NIP/NIK | Nomor identitas pegawai |
| Role | Pilih role pengguna |
| Unit | Unit kerja |
| Jabatan | Jabatan pengguna |
| Telepon | Nomor telepon |
| Password | Password awal |

3. Centang **Harus Ganti Password** jika perlu
4. Klik **Simpan**

### Role dan Permission

| Role | Akses |
|------|-------|
| Super Admin | Akses penuh ke seluruh sistem |
| Admin | Kelola dokumen, user, master data |
| Legal Head | Approve dokumen, lihat laporan |
| Reviewer | Review dokumen |
| Executive | Lihat dan download dokumen |
| General | Lihat dokumen sesuai akses |

### Menonaktifkan Pengguna

1. Buka detail pengguna
2. Klik toggle **Aktif**
3. Konfirmasi penonaktifan

### Reset Password Pengguna

1. Buka detail pengguna
2. Klik **Reset Password**
3. Masukkan password baru
4. Centang "Harus ganti password saat login"
5. Klik **Reset**

---

## 📊 Master Data

### Jenis Dokumen

Kelola jenis dokumen seperti:
- Perjanjian Kerjasama (PKS)
- Memorandum of Understanding (MoU)
- Kontrak
- Surat Keputusan
- Dan lainnya

**Cara Menambah:**
1. Buka **Master Data** > **Jenis Dokumen**
2. Klik **+ Tambah**
3. Isi Kode, Nama, Prefix, dan pengaturan lainnya
4. Klik **Simpan**

### Kategori Dokumen

Kategori merupakan sub-klasifikasi dari jenis dokumen.

### Direktorat

Kelola daftar direktorat/divisi dalam organisasi.

### Unit Kerja

Kelola daftar unit kerja di bawah direktorat.

### Jabatan

Kelola daftar jabatan pegawai.

---

## 📈 Laporan dan Export

### Jenis Laporan

| Laporan | Deskripsi |
|---------|-----------|
| Laporan Dokumen | Daftar dokumen dengan filter |
| Laporan Kadaluarsa | Dokumen yang akan/sudah expired |
| Laporan per Jenis | Statistik per jenis dokumen |
| Laporan per Unit | Statistik per unit kerja |

### Export Data

1. Buka halaman laporan atau daftar dokumen
2. Klik tombol **Export**
3. Pilih format:
   - **Excel** - File .xlsx untuk analisis lanjut
   - **PDF** - File .pdf untuk cetak
4. File akan terunduh otomatis

### Audit Log

Melihat riwayat aktivitas sistem:
1. Buka **Pengaturan** > **Audit Log**
2. Filter berdasarkan:
   - Pengguna
   - Aksi (Create, Update, Delete, Download, dll)
   - Tanggal
3. Klik entri untuk melihat detail

---

## ⚙️ Pengaturan Sistem

### Pengaturan Umum

- Nama aplikasi
- Logo aplikasi
- Timezone
- Format tanggal

### Pengaturan Email

- SMTP server
- Port
- Username/Password
- Email pengirim

### Pengaturan Dokumen

- Ukuran file maksimum
- Format file yang diizinkan
- Watermark otomatis
- Retensi dokumen default

### Backup Database

1. Buka **Pengaturan** > **Backup**
2. Klik **Backup Sekarang**
3. Download file backup

---

## ❓ FAQ

### Bagaimana mengubah password saya?

1. Klik foto profil di pojok kanan atas
2. Pilih **Profil**
3. Klik tab **Keamanan**
4. Masukkan password lama dan password baru
5. Klik **Simpan**

### Bagaimana melihat dokumen yang saya upload?

1. Buka **Dokumen**
2. Gunakan filter **Creator** dan pilih nama Anda
3. Atau klik **Profil** > **Dokumen Saya**

### Mengapa saya tidak bisa menghapus dokumen?

Dokumen yang sudah dipublikasikan tidak dapat dihapus secara permanen. Anda hanya bisa mengarsipkannya. Hubungi Super Admin jika perlu penghapusan permanen.

### Bagaimana menambah akses pengguna ke dokumen confidential?

1. Buka detail dokumen
2. Klik tab **Akses**
3. Klik **Tambah Akses**
4. Pilih pengguna/unit
5. Tentukan level akses (View/Download/Edit)
6. Klik **Simpan**

---

## 📞 Bantuan

Jika mengalami kendala, silakan hubungi:

- **Email:** admin@rsngoerah.com
- **Telepon:** (021) xxx-xxxx
- **WhatsApp:** 08xx-xxxx-xxxx

---

*Dokumen ini adalah bagian dari Sistem Manajemen Dokumen Hukum Terpusat RS Ngoerah*
