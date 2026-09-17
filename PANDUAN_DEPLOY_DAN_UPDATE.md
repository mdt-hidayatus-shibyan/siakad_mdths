# 📘 Panduan Alur Update Fitur, Git Push ke GitHub, dan Deploy ke VPS

Panduan praktis langkah demi langkah jika di kemudian hari Anda melakukan penambahan fitur, perbaikan bug, atau perubahan data di komputer lokal (Laragon) dan ingin menerapkannya ke server VPS (**`https://mdt-hidayatus-shibyan.sch.id`**).

---

## 🖥️ BAGIAN 1: DI KOMPUTER LOKAL (LAPTOP / PC)

Setelah Anda selesai mengedit kode di laptop/PC, lakukan langkah berikut:

### 1. (Jika Ada Perubahan Aset Web Blade/CSS/JS)
Jika Anda mengubah tampilan web admin, Tailwind CSS, atau JavaScript, lakukan kompilasi aset Vite terlebih dahulu:
```bash
cd backend
npm run build
cd ..
```

### 2. (Jika Ada Perubahan Struktur / Data Database)
Jika ada tabel baru atau perubahan data database yang ingin ikut diperbarui ke backup:
```bash
mysqldump -u root database_v2_mdt_hidayatus_shibyan > backend/backup_database.sql
```

### 3. Simpan dan Push ke GitHub
Jalankan perintah Git dari root proyek (`d:\laragon\www\mdt_hidayatus_shibyan`):
```bash
# 1. Cek file apa saja yang berubah
git status

# 2. Masukkan semua perubahan ke staging
git add .

# 3. Buat catatan commit (ganti pesan sesuai fitur yang diubah)
git commit -m "feat: deskripsi perubahan fitur atau perbaikan yang dilakukan"

# 4. Upload / Push ke GitHub
git push origin main
```

---

## 🌐 BAGIAN 2: DI SERVER VPS (PRODUCTION)

Buka terminal SSH VPS Anda (via PuTTY, Bitvise, atau Terminal):

### 1. Masuk ke Folder Proyek & Tarik Update dari GitHub
```bash
cd /var/www/siakad
git pull origin main
```

### 2. Masuk ke Folder Backend & Sinkronkan Laravel
```bash
cd /var/www/siakad/backend

# Jalankan migrasi database (jika ada file migrasi tabel baru)
php artisan migrate --force

# (Opsional) Jika Anda ingin me-restore/import ulang database dump terbaru:
# mysql -u user_database -p nama_database < backup_database.sql

# Bersihkan dan optimasi cache Laravel untuk production
php artisan optimize:clear
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Pastikan izin akses folder storage tetap aman
chown -R www-data:www-data storage bootstrap/cache
chmod -R 775 storage bootstrap/cache
```

---

## 📱 BAGIAN 3: JIKA ADA UPDATE APLIKASI MOBILE (FLUTTER)

Jika Anda melakukan perubahan pada aplikasi mobile (`app_ustadz` atau `app_murid`):

### 1. Build APK Baru
Pastikan `baseUrl` sudah mengarah ke domain: `https://mdt-hidayatus-shibyan.sch.id/api`.

- **Aplikasi Ustadz:**
  ```bash
  cd frontend/app_ustadz
  flutter clean
  flutter pub get
  flutter build apk --release
  ```
  *File APK hasil build:* `frontend/app_ustadz/build/app/outputs/flutter-apk/app-release.apk`

- **Aplikasi Wali Murid:**
  ```bash
  cd frontend/app_murid
  flutter clean
  flutter pub get
  flutter build apk --release
  ```
  *File APK hasil build:* `frontend/app_murid/build/app/outputs/flutter-apk/app-release.apk`

---

## ⚡ CHEAT SHEET RINGKAS (COPY-PASTE CEPAT)

### 🔹 Di Komputer Lokal (Setiap Kali Selesai Coding):
```bash
cd backend && npm run build && cd ..
git add .
git commit -m "update: perbaikan dan pembaruan sistem"
git push origin main
```

### 🔹 Di Terminal VPS (Terapkan Update ke Website):
```bash
cd /var/www/siakad && git pull origin main && cd backend && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```
