# 📝 Todo: Penyempurnaan Sidebar & UI/UX

Berdasarkan analisis UI/UX sebelumnya, berikut adalah daftar tugas untuk menyempurnakan sidebar agar lebih profesional, intuitif, dan aksesibel.

## 🎨 UI (User Interface) Polish
- [x] **Optimasi Kontras Teks Inaktif:** Tingkatkan kontras warna teks menu yang tidak aktif (`--text-secondary`) agar tetap terbaca jelas di atas latar belakang glassmorphism yang bervariasi.
- [x] **Indikator Aktif yang Lebih Tegas:** 
    - [x] Tambahkan garis vertikal (`border-left`) tipis atau *glow* halus pada sisi item menu yang sedang aktif.
    - [x] Pastikan transisi antara state aktif dan inaktif tetap mulus.
- [x] **Audit Konsistensi Ikon:** Pastikan semua ikon SVG memiliki ketebalan garis (*stroke-width*) yang seragam (saat ini sudah cukup baik, perlu pengecekan ulang jika ada penambahan menu).
- [x] **Pembaruan Logo:** Ganti `logo.png` dengan `Logo-RS-New.png` untuk memperbarui branding RS Ngoerah di seluruh sidebar, halaman auth, dan ekspor PDF.

## 🧠 UX (User Experience) Enhancement
- [x] **Fine-tuning Perilaku Hover:**
    - [x] Tambahkan sedikit *delay* (sekitar 150-200ms) pada fungsi `expandOnHover()` untuk mencegah sidebar melebar secara tidak sengaja saat mouse hanya lewat.
    - [x] Tambahkan *debounce* pada `collapseOnLeave()` agar tidak langsung menutup jika pengguna tidak sengaja keluar area sidebar sebentar.
- [x] **Optimasi Tap Target (Mobile):**
    - [x] Pastikan setiap item menu memiliki area klik/tap minimal 44x44px sesuai standar aksesibilitas mobile.
    - [x] Periksa kembali padding pada sidebar item di mode mobile.
- [x] **Penyempurnaan Tooltips:**
    - [x] Pastikan tooltip muncul secara instan saat sidebar dalam kondisi menciut (*collapsed*).
    - [ ] Gunakan library tooltip yang lebih estetik (seperti Tippy.js) jika ingin menggantikan atribut `title` bawaan browser.

## ♿ Aksesibilitas & Teknis
- [x] **Keyboard Navigation:** Pastikan pengguna bisa menavigasi menu sidebar menggunakan tombol `Tab` dan `Enter` dengan *focus ring* yang terlihat jelas.
- [x] **Screen Reader Support:** Verifikasi ulang label ARIA agar deskripsi menu terbaca dengan tepat oleh perangkat pembantu.
- [x] **Performance:** Pastikan animasi transisi lebar sidebar tidak menyebabkan *layout shift* yang mengganggu pada konten utama.

---
*Terakhir diperbarui: 23 Januari 2026*
