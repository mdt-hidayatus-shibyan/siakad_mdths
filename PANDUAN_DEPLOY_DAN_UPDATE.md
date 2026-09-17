# 📘 Panduan Alur Update Fitur, Git Push ke GitHub, dan Deploy ke VPS

Panduan praktis alur kerja sinkronisasi kode dan deploy untuk **MDT Hidayatus Shibyan**.

---

## 📌 ATURAN DASAR ALUR DEPLOYMENT

| Jenis Perbaikan / Pembaruan | Alur Kerja yang Dilakukan |
| :--- | :--- |
| 🔧 **Perbaikan / Fitur Backend (Laravel, API, Web Admin, Database)** | 1. `git push` dari komputer lokal ke GitHub.<br>2. `git pull` di server VPS agar perubahan langsung aktif di web/API. |
| 📱 **Perbaikan / Fitur Frontend (Flutter `app_ustadz` & `app_murid`)** | 1. Cukup `git push` dari komputer lokal ke GitHub.<br>2. *(Tidak perlu pull ke VPS)*. Cukup build ulang APK jika ingin mendistribusikan aplikasi baru ke pengguna. |

---

## 🖥️ 1. JIKA ADA PERBAIKAN DI BACKEND (LARAVEL / API / WEB)

### A. Di Komputer Lokal (Laptop / PC):
```bash
# 1. Jika ada perubahan aset CSS/JS Blade:
cd backend && npm run build && cd ..

# 2. (Opsional) Jika ada perubahan struktur database yang ingin dicadangkan:
mysqldump -u root database_v2_mdt_hidayatus_shibyan > backend/backup_database.sql

# 3. Commit dan push ke GitHub:
git add .
git commit -m "fix(backend): deskripsi perbaikan backend"
git push origin main
```

### B. Di Terminal Server VPS:
Tarik update ke server production:
```bash
cd /var/www/siakad && git pull origin main && cd backend && php artisan optimize:clear && php artisan config:cache && php artisan route:cache && php artisan view:cache
```
*(Jika ada file migrasi tabel baru, tambahkan: `php artisan migrate --force`)*.

---

## 📱 2. JIKA ADA PERBAIKAN DI FRONTEND (FLUTTER MOBILE)

### A. Di Komputer Lokal (Laptop / PC):
Cukup simpan kode dan push ke GitHub:
```bash
git add .
git commit -m "fix(frontend): deskripsi perbaikan mobile app"
git push origin main
```
*(Selesai! Tidak perlu membuka terminal VPS).*

### B. (Opsional) Jika Ingin Menghasilkan File APK Baru:
- **Build APK Ustadz:**
  ```bash
  cd frontend/app_ustadz
  flutter build apk --release
  ```
- **Build APK Wali Murid:**
  ```bash
  cd frontend/app_murid
  flutter build apk --release
  ```
- File APK release yang dibagikan ke pengguna: `build/app/outputs/flutter-apk/app-release.apk`.
