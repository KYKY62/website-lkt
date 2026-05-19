# Portal Kabupaten Langkat

Website Pemerintah Kabupaten Langkat berbasis Laravel 13 dan Vue 3.

## Kebutuhan environment

- PHP `^8.3` sesuai `composer.json`.
- Composer 2.x.
- Node.js `^20.19.0` atau `>=22.12.0` sesuai kebutuhan Vite 8 dan plugin Vite.
- npm 10+.
- Ekstensi PHP yang dicek oleh Composer: `ctype`, `dom`, `fileinfo`, `filter`, `hash`, `iconv`, `json`, `libxml`, `openssl`, `pcre`, `phar`, `session`, `tokenizer`, `xml`, dan `xmlwriter`.

## Menjalankan proyek

1. Install dependency PHP dan JavaScript:

   ```powershell
   composer install
   npm install
   ```

2. Siapkan `.env`, application key, database, dan storage link:

   ```powershell
   Copy-Item .env.example .env
   php artisan key:generate
   php artisan migrate --seed --force
   php artisan storage:link
   ```

3. Jalankan server Laravel:

   ```powershell
   php artisan serve --host=127.0.0.1 --port=8000
   ```

4. Di terminal lain, jalankan Vite:

   ```powershell
   npm run dev
   ```

5. Buka `http://localhost:8000`.

## Migrasi dan seed data

```powershell
php artisan migrate --seed --force
```

Seeder tidak memasukkan konten publik awal. Jika ingin membuat akun awal melalui seeder, isi variabel berikut di `.env` sebelum menjalankan perintah migrasi:

```env
ADMIN_SEED_NAME="Super Admin"
ADMIN_SEED_EMAIL="admin@domain-resmi.go.id"
ADMIN_SEED_PASSWORD="ganti-dengan-password-kuat"
```

## Panel admin

- Modul manajemen berita tersedia di `http://localhost:8000/admin/news`.
- Modul pengumuman tersedia di `http://localhost:8000/admin/announcements`.
- Modul download/dokumen tersedia di `http://localhost:8000/admin/downloads`.
- Modul layanan tersedia di `http://localhost:8000/admin/services`.
- Modul Kabar Perangkat Daerah tersedia di `http://localhost:8000/admin/department-news`.
- Login admin tersedia di `http://localhost:8000/admin/login`.
- Halaman akun admin tersedia di `http://localhost:8000/admin/account`.
- Role yang tersedia saat ini hanya `super_admin` dan `news_editor`.
- Hanya `super_admin` yang bisa membuka modul `http://localhost:8000/admin/users` untuk menambah atau mengubah akun editor berita.
- Modul halaman statis tersedia di `http://localhost:8000/admin/pages`.
- Modul manajemen menu tersedia di `http://localhost:8000/admin/menus`.
- Modul widget halaman tersedia di `http://localhost:8000/admin/widgets`.
- Widget halaman mengatur area dua kolom sebelum footer per target halaman, mendukung `static_image`, `link_banner`, `html`, `embed`, dan `text_cta`.
- `super_admin` dan `news_editor` dapat mengelola widget halaman.
- Layanan berisi shortcut aplikasi atau halaman layanan perangkat daerah dengan logo/icon, judul, penyelenggara, deskripsi, link, status, dan urutan.
- Kabar Perangkat Daerah mengambil berita dari API multisite secara server-side, memakai cache, dan tampil sebagai section di beranda.
- Menu website mendukung posisi `master` dan `submenu`.
- Jenis menu yang tersedia saat ini: `page`, `link`, dan `module`.
- Modul berita sekarang memakai editor WYSIWYG untuk isi artikel.
- Gambar berita sekarang diupload sebagai file gambar langsung dari panel admin, dengan dukungan multiple image, thumbnail admin, dan gambar pertama otomatis menjadi sampul.
- Urutan gambar berita bisa diatur ulang dari panel admin, baik untuk gambar yang sudah tersimpan maupun file baru sebelum disimpan.
- Gambar lama di galeri berita juga bisa dihapus satu-per-satu tanpa harus upload ulang seluruh gambar.
- Halaman detail berita menampilkan nama editor yang mempublikasikan berita.

## File storage

Jika gambar berita atau widget belum tampil di browser, buat symbolic link storage terlebih dahulu:

```powershell
php artisan storage:link
```

## Build produksi

```powershell
npm run build
```

## Logo dan favicon

- Logo utama disimpan di `resources/img/logo_langkat.png` dan dipakai oleh navbar publik, footer, header admin, serta login admin.
- Favicon juga memakai asset logo yang sama melalui Vite, sehingga penggantian logo cukup dilakukan dari `resources/img/logo_langkat.png`.

## Menjalankan test

```powershell
php artisan test
```

## Catatan

- Form kontak menyimpan pesan ke `storage/app/private/contact-messages.jsonl` pada disk lokal Laravel.
- Berita publik sekarang dapat dibaca dari database dan dikelola lewat panel admin.
- Widget pre-footer hanya tampil pada halaman yang memiliki widget berstatus `published`.
- Konten publik awal tidak disediakan di konfigurasi atau seeder; halaman tanpa data menampilkan empty state produksi.

## Migrasi konten website lama

Migrasi konten legacy memakai command idempotent, sehingga aman dijalankan ulang karena data dicocokkan berdasarkan `legacy_id`.

Tambahkan konfigurasi berikut di `.env` lokal/server produksi. Jangan commit password database lama ke repository:

```env
LEGACY_DB_CONNECTION=legacy
LEGACY_DB_HOST=192.168.4.22
LEGACY_DB_PORT=3306
LEGACY_DB_DATABASE=langkat_baru
LEGACY_DB_USERNAME=langkat_baru
LEGACY_DB_PASSWORD=
LEGACY_BASE_URL=https://www.langkatkab.go.id
```

## Kabar Perangkat Daerah

Widget beranda ini mengambil berita perangkat daerah dari API multisite dan menyimpan hasil sukses di cache agar halaman tetap stabil bila API sedang lambat atau gagal.

```env
DEPARTMENT_NEWS_API_URL=https://multisite.langkatkab.go.id/api/v1/all-berita
DEPARTMENT_NEWS_TIMEOUT=8
DEPARTMENT_NEWS_RETRY_TIMES=1
DEPARTMENT_NEWS_RETRY_SLEEP_MS=200
```

Pengaturan tampilan, jumlah item, durasi cache, refresh cache, dan clear cache tersedia di panel admin.

Jalankan dry-run untuk memastikan jumlah target:

```powershell
php artisan legacy:migrate-content --only=news,announcements,downloads --dry-run
```

Jalankan import aktual setelah database baru sudah dimigrasikan dan `php artisan storage:link` sudah dibuat:

```powershell
php artisan legacy:migrate-content --only=news,announcements,downloads
```

Konten yang dimigrasikan hanya data published/non-trash: berita, pengumuman, dan download. File lama disalin ke `storage/app/public/legacy/...`, bukan hotlink, dan URL legacy berikut disiapkan sebagai redirect/handler:

```text
/berita/{legacy_id}/{legacy_slug?}
/pengumuman/detil/{legacy_id}/{legacy_slug?}
/pengumuman/get/{legacy_id}/{anything?}
/download/get/{legacy_id}/{anything?}
```
