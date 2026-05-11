# Admin Product Specification for Testing

Tanggal dokumen: 2026-05-11  
Target pengujian: Portal Admin UII Dalwa Press  
Base route: `/administrator`  
Akun testing utama: `admin / password`

## 1. Tujuan

Dokumen ini mendefinisikan spesifikasi produk khusus untuk pengujian portal admin. Fokus testing adalah memastikan admin dapat login, mengakses dashboard, mengelola master data, mengelola konten, mengelola buku, mengelola submission, menugaskan editor, mengelola royalti, dan mengelola user/role.

Pengujian harus memvalidasi UI, API behavior, otorisasi, validasi form, perubahan data, state loading/error, dan konsistensi data setelah operasi create, update, delete, filter, dan search.

## 2. Scope Testing

Termasuk dalam scope:

- Login admin.
- Guard route admin.
- Admin dashboard.
- News management.
- Book category management.
- Author management.
- Book management.
- Submission management.
- Editor assignment.
- Royalty management.
- User management.
- Role management.
- Admin profile.
- Pengaturan page smoke test.
- Logout.

Tidak termasuk dalam scope utama:

- Portal author.
- Portal editor kecuali dampak assignment dari admin.
- Portal publik kecuali verifikasi data admin tampil di publik bila relevan.
- Pengujian performa beban tinggi.

## 3. Environment dan Data Prasyarat

### Aplikasi

- Backend Laravel API berjalan.
- Frontend Vue/Vite berjalan.
- Database sudah dimigrasi dan diseed.
- Storage link tersedia bila pengujian upload/download file dilakukan.

### Akun

Gunakan akun admin:

- Username: `admin`
- Password: `password`
- Role expected: `Admin`
- Status expected: `Active`

Data pendukung yang disarankan tersedia:

- Minimal satu role `Admin`, `Operator`, `Author`, dan `Editor`.
- Minimal satu user role `Editor` aktif.
- Minimal satu kategori buku.
- Minimal satu author.
- Minimal satu buku `published` untuk pengujian royalti.
- Minimal satu submission untuk pengujian assignment dan review admin.

## 4. Navigation dan Route Admin

Admin harus dapat mengakses route berikut setelah login:

- `/administrator/dashboard`
- `/administrator/news`
- `/administrator/news/create`
- `/administrator/news/:id/edit`
- `/administrator/book-categories`
- `/administrator/authors`
- `/administrator/books`
- `/administrator/books/create`
- `/administrator/books/:id/edit`
- `/administrator/submissions`
- `/administrator/submissions/:id`
- `/administrator/royalties`
- `/administrator/royalties/create`
- `/administrator/royalties/:id/edit`
- `/administrator/manajemen-user`
- `/administrator/manajemen-role`
- `/administrator/profile`
- `/administrator/pengaturan`

Expected behavior:

- Jika belum login dan membuka `/administrator/*`, user diarahkan ke `/login`.
- Jika login sebagai admin, semua route admin dapat dibuka.
- Jika login sebagai role selain `Admin` atau `Operator`, route admin ditolak oleh frontend guard dan user diarahkan ke login.
- Document title berubah sesuai route.
- Sidebar/navbar tetap tampil konsisten pada seluruh halaman admin.

## 5. Authentication Test

### TC-ADM-AUTH-001 Login Admin Berhasil

Precondition:

- User berada di `/login`.

Steps:

1. Masukkan username `admin`.
2. Masukkan password `password`.
3. Submit form login.

Expected:

- API `POST /api/login` berhasil.
- `auth_token` tersimpan di `localStorage`.
- `auth_user` tersimpan dan berisi role `Admin`.
- User diarahkan ke `/administrator/dashboard`.
- Dashboard admin tampil tanpa error.

### TC-ADM-AUTH-002 Login Invalid Ditolak

Steps:

1. Login dengan username `admin`.
2. Gunakan password salah.
3. Submit form login.

Expected:

- Login gagal.
- Token tidak tersimpan.
- User tetap di halaman login.
- Error login terlihat oleh user.

### TC-ADM-AUTH-003 Logout

Steps:

1. Login sebagai admin.
2. Klik logout.

Expected:

- API `POST /api/logout` dipanggil.
- Token dan auth user dihapus dari `localStorage`.
- User diarahkan ke `/login`.
- Membuka `/administrator/dashboard` kembali mengarahkan ke login.

## 6. Dashboard Admin

### TC-ADM-DASH-001 Smoke Test Dashboard

Steps:

1. Login sebagai admin.
2. Buka `/administrator/dashboard`.

Expected:

- Dashboard tampil dalam layout admin.
- Tidak ada error JavaScript fatal.
- Tidak ada API unauthorized.
- Komponen utama dashboard terlihat.
- Sidebar, navbar, horizontal nav, dan footer tetap tampil.

## 7. News Management

API terkait:

- `GET /api/news`
- `POST /api/news`
- `PUT /api/news/{news}`
- `DELETE /api/news/{news}`
- `GET /api/news-categories`
- `POST /api/upload-editor`
- `POST /api/delete-editor-file`

Field utama:

- `title` wajib.
- `news_category_id` wajib.
- `body` opsional.
- `speaker` opsional.
- `duration` opsional.
- `status` harus `Published` atau `Draft`.
- `image` opsional, image maksimal 5 MB.
- `video` opsional, `mp4`, `webm`, atau `ogg`, maksimal 50 MB.

### TC-ADM-NEWS-001 List News

Steps:

1. Buka `/administrator/news`.

Expected:

- Daftar news tampil.
- Pagination berjalan bila data melebihi per page.
- Search berdasarkan title/body mengubah hasil.
- Filter kategori/status bila tersedia mengirim query yang benar.

### TC-ADM-NEWS-002 Create News Draft

Steps:

1. Buka `/administrator/news/create`.
2. Isi title unik.
3. Pilih kategori news.
4. Isi body.
5. Set status `Draft`.
6. Submit.

Expected:

- API `POST /api/news` berhasil dengan status 201.
- News baru tersimpan.
- User mendapat feedback sukses.
- Data muncul di list news.

### TC-ADM-NEWS-003 Create News Published dengan Media

Steps:

1. Buka form create news.
2. Isi field wajib.
3. Upload image valid.
4. Upload video valid kecil.
5. Set status `Published`.
6. Submit.

Expected:

- File tersimpan.
- Response memuat data news dan category.
- News muncul di list dengan status `Published`.

### TC-ADM-NEWS-004 Validasi News

Steps:

1. Submit form news tanpa title.
2. Submit tanpa kategori.
3. Upload file image non-image.
4. Upload video dengan format tidak didukung.

Expected:

- Server mengembalikan error validasi.
- UI menampilkan pesan error.
- Data tidak tersimpan.

### TC-ADM-NEWS-005 Edit dan Delete News

Steps:

1. Buka edit news.
2. Ubah title/body/status.
3. Simpan.
4. Hapus news tersebut dari list/detail.

Expected:

- API update memakai `PUT /api/news/{id}`.
- Perubahan tampil setelah refresh/list reload.
- Delete menghapus konten dan file terkait.
- News tidak muncul lagi di list.

## 8. Book Categories

API terkait:

- `GET /api/book-categories`
- `POST /api/book-categories`
- `GET /api/book-categories/{id}`
- `PUT/PATCH /api/book-categories/{id}`
- `DELETE /api/book-categories/{id}`

Field:

- `name` wajib.
- `description` opsional.
- `slug` dibuat otomatis dan unik.

### TC-ADM-CAT-001 CRUD Kategori Buku

Steps:

1. Buka `/administrator/book-categories`.
2. Buat kategori baru dengan nama unik.
3. Cari kategori tersebut.
4. Edit nama/deskripsi.
5. Hapus kategori.

Expected:

- Kategori tersimpan dengan slug otomatis.
- Search berdasarkan name/description berhasil.
- Update mengubah data.
- Delete berhasil jika kategori belum dipakai.

### TC-ADM-CAT-002 Kategori yang Dipakai Tidak Bisa Dihapus

Steps:

1. Pilih kategori yang sudah dipakai buku atau submission.
2. Coba hapus.

Expected:

- API mengembalikan 422.
- Pesan: kategori masih digunakan.
- Data tetap ada.

## 9. Authors

API terkait:

- `GET /api/authors`
- `POST /api/authors`
- `GET /api/authors/{id}`
- `PUT/PATCH /api/authors/{id}`
- `DELETE /api/authors/{id}`

Field:

- `name` wajib.
- `email` opsional dan harus format email.
- `phone` opsional.
- `photo` opsional, image maksimal 5 MB.
- `bio` opsional.
- `institution` opsional.

### TC-ADM-AUTHOR-001 CRUD Author

Steps:

1. Buka `/administrator/authors`.
2. Buat author baru dengan nama unik.
3. Isi email, phone, bio, institution.
4. Upload photo valid.
5. Simpan.
6. Search author.
7. Edit data dan ganti/hapus photo.
8. Hapus author.

Expected:

- Author tersimpan dengan slug otomatis.
- Photo tersimpan di storage publik.
- Search berdasarkan name/email/institution berhasil.
- Update mengganti data dan file lama dibersihkan.
- Delete berhasil jika author belum memiliki buku.

### TC-ADM-AUTHOR-002 Author dengan Buku Tidak Bisa Dihapus

Steps:

1. Pilih author yang sudah memiliki buku.
2. Coba hapus.

Expected:

- API mengembalikan 422.
- Pesan: author masih memiliki buku.
- Author tetap ada.

## 10. Books

API terkait:

- `GET /api/books`
- `POST /api/books`
- `GET /api/books/{id}`
- `PUT/PATCH /api/books/{id}`
- `DELETE /api/books/{id}`
- `GET /api/books/{id}/download`

Field wajib:

- `category_id`
- `author_id`
- `title`
- `status`: `draft`, `review`, `published`, atau `archived`

Field opsional:

- `isbn`
- `year` 1000 sampai 9999.
- `publisher`
- `pages` minimal 1.
- `language`
- `cover` image maksimal 5 MB.
- `preview_file` PDF maksimal 50 MB.
- `full_file` PDF maksimal 100 MB.
- `description`
- `table_of_contents`
- `tags`
- `featured`
- `published_at`
- `galleries[]` image maksimal 5 MB per file.

### TC-ADM-BOOK-001 List dan Filter Buku

Steps:

1. Buka `/administrator/books`.
2. Jalankan search berdasarkan title/isbn/description/author.
3. Filter category, author, status, dan featured.
4. Ubah sorting bila UI mendukung.

Expected:

- Data buku tampil dengan category, author, dan gallery.
- Filter mengirim query yang sesuai.
- Pagination dan sorting bekerja.

### TC-ADM-BOOK-002 Create Draft Book

Steps:

1. Buka `/administrator/books/create`.
2. Pilih kategori.
3. Pilih author.
4. Isi title unik.
5. Set status `draft`.
6. Submit.

Expected:

- API `POST /api/books` berhasil.
- Slug dibuat otomatis.
- `published_at` null.
- Buku tampil di list.

### TC-ADM-BOOK-003 Create Published Book dengan File

Steps:

1. Buat buku baru.
2. Set status `published`.
3. Isi metadata ISBN/year/pages/language.
4. Upload cover image valid.
5. Upload preview PDF.
6. Upload full PDF.
7. Upload gallery image jika tersedia.
8. Submit.

Expected:

- Buku tersimpan.
- `published_at` otomatis terisi jika kosong.
- Cover, preview, full file, dan gallery tersimpan.
- Buku published dapat muncul di katalog publik.

### TC-ADM-BOOK-004 Edit Book dan Remove File

Steps:

1. Buka edit buku.
2. Ubah title, status, dan metadata.
3. Ganti cover.
4. Remove preview/full file bila kontrol tersedia.
5. Simpan.

Expected:

- Update berhasil.
- Jika title berubah, slug diperbarui unik.
- File lama dihapus saat diganti/remove.
- Jika status bukan `published`, `published_at` menjadi null.

### TC-ADM-BOOK-005 Validasi Book

Steps:

1. Submit tanpa kategori.
2. Submit tanpa author.
3. Submit tanpa title.
4. Isi year di luar rentang.
5. Isi pages kurang dari 1.
6. Upload preview non-PDF.

Expected:

- API mengembalikan error validasi.
- UI menampilkan error.
- Data tidak tersimpan.

## 11. Submissions

API terkait:

- `GET /api/submissions`
- `GET /api/submissions/{id}`
- `PUT/PATCH /api/submissions/{id}`
- `DELETE /api/submissions/{id}`
- `POST /api/submissions/{id}/reviews`
- `GET /api/editors`
- `POST /api/submissions/{id}/assign-editors`

Status submission:

- `submitted`
- `under_review`
- `revision`
- `accepted`
- `rejected`
- `published`

### TC-ADM-SUB-001 List dan Filter Submission

Steps:

1. Buka `/administrator/submissions`.
2. Search berdasarkan title, author_name, atau email.
3. Filter category.
4. Filter status.
5. Buka pagination.

Expected:

- Semua submission lintas author tampil.
- Filter/search mengubah hasil sesuai query.
- Data memuat category, reviews, dan editor assignments.

### TC-ADM-SUB-002 Detail Submission

Steps:

1. Buka salah satu submission dari list.

Expected:

- Halaman detail tampil.
- Detail memuat category, reviews editor, revisions, editor assignments, dan assigner.
- File naskah/cover dapat diakses bila tersedia.

### TC-ADM-SUB-003 Update Metadata dan Status

Steps:

1. Buka detail submission.
2. Ubah title, author_name, email, phone, description, note.
3. Ubah status ke `accepted`.
4. Simpan.

Expected:

- Update berhasil.
- Jika title berubah, slug diperbarui.
- Jika status berubah ke `revision`, `accepted`, `rejected`, atau `published` dan `reviewed_at` kosong, `reviewed_at` terisi.

### TC-ADM-SUB-004 Assign Editor

Precondition:

- Ada minimal satu user aktif role `Editor`.

Steps:

1. Buka detail submission dengan status `submitted`.
2. Buka kontrol assignment editor.
3. Pilih primary editor.
4. Pilih satu atau lebih co-editor jika tersedia.
5. Isi note assignment.
6. Simpan.

Expected:

- API `GET /api/editors` hanya mengembalikan user aktif role `Editor`.
- API `POST /api/submissions/{id}/assign-editors` berhasil.
- Assignment lama diganti dengan assignment baru.
- Primary editor tercatat dengan role `primary`.
- Co-editor tercatat dengan role `co_editor`.
- Jika status awal `submitted`, status berubah menjadi `under_review`.
- UI detail diperbarui dari response tanpa reload penuh bila implementasi mendukung.

### TC-ADM-SUB-005 Assign Editor Invalid

Steps:

1. Kirim assignment tanpa primary editor.
2. Coba gunakan user non-editor sebagai primary editor.
3. Coba gunakan user non-editor sebagai co-editor.

Expected:

- API mengembalikan 422.
- Pesan validasi sesuai masalah.
- Assignment existing tidak rusak.

### TC-ADM-SUB-006 Admin Manual Review

Steps:

1. Buka detail submission.
2. Tambahkan review manual dengan status `revision`.
3. Isi reviewer_name, reviewer_email, dan note.
4. Submit.

Expected:

- API `POST /api/submissions/{id}/reviews` berhasil.
- Review baru muncul di detail.
- Status submission berubah sesuai review.
- `reviewed_at` diperbarui.

### TC-ADM-SUB-007 Delete Submission

Steps:

1. Pilih submission test yang aman dihapus.
2. Hapus submission.

Expected:

- API delete berhasil.
- File `manuscript_file` dan `cover_file` terkait dihapus dari storage publik.
- Submission tidak muncul lagi di list.

## 12. Royalties

API terkait:

- `GET /api/royalties`
- `GET /api/royalties/summary`
- `POST /api/royalties`
- `GET /api/royalties/{id}`
- `PUT/PATCH /api/royalties/{id}`
- `DELETE /api/royalties/{id}`

Field:

- `book_id` wajib dan buku harus `published`.
- `period_month` wajib, 1 sampai 12.
- `period_year` wajib, 2000 sampai 2100.
- `sold_qty` wajib, minimal 0.
- `sale_price_per_unit` wajib, minimal 0.
- `royalty_per_unit` wajib, minimal 0.
- `status`: `draft`, `pending`, atau `paid`.
- `paid_at` opsional.
- `notes` opsional.

### TC-ADM-ROY-001 List, Filter, dan Summary Royalti

Steps:

1. Buka `/administrator/royalties`.
2. Search berdasarkan title/isbn/author.
3. Filter book, author, status, period_month, period_year.
4. Periksa summary.

Expected:

- List royalti tampil.
- Summary memuat total entries, total books, total sold qty, gross amount, royalty amount, draft amount, pending amount, dan paid amount.
- Filter list dan summary konsisten.

### TC-ADM-ROY-002 Create Royalti Draft

Steps:

1. Buka `/administrator/royalties/create`.
2. Pilih buku published.
3. Isi period month/year.
4. Isi sold qty, sale price per unit, royalty per unit.
5. Set status `draft`.
6. Submit.

Expected:

- API berhasil dengan status 201.
- `author_id` otomatis mengikuti author buku.
- `gross_amount = sold_qty * sale_price_per_unit`.
- `royalty_amount = sold_qty * royalty_per_unit`.
- `created_by` dan `updated_by` adalah admin login.
- `paid_at` null.

### TC-ADM-ROY-003 Create Royalti Paid

Steps:

1. Buat royalti dengan status `paid`.
2. Kosongkan `paid_at`.
3. Submit.

Expected:

- Data tersimpan.
- `paid_at` otomatis diisi waktu saat ini.

### TC-ADM-ROY-004 Royalti untuk Buku Non-published Ditolak

Steps:

1. Pilih buku status `draft`, `review`, atau `archived`.
2. Submit royalti.

Expected:

- API mengembalikan 422.
- Pesan: royalti hanya dapat dibuat untuk buku published.
- Data tidak tersimpan.

### TC-ADM-ROY-005 Delete Royalti

Steps:

1. Hapus royalti status `draft`.
2. Coba hapus royalti status `pending` atau `paid`.

Expected:

- Draft dapat dihapus.
- Pending/paid ditolak dengan 422.

## 13. User Management

API terkait:

- `GET /api/users`
- `POST /api/users`
- `GET /api/users/{id}`
- `PUT/PATCH /api/users/{id}`
- `DELETE /api/users/{id}`

Field:

- `username` wajib dan unik.
- `name` wajib.
- `email` wajib, format email, unik.
- `password` wajib saat create, minimal 6 karakter.
- `password` opsional saat update, minimal 6 karakter bila diisi.
- `role_id` wajib.
- `status`: `Active` atau `Inactive`.

### TC-ADM-USER-001 CRUD User

Steps:

1. Buka `/administrator/manajemen-user`.
2. Buat user baru dengan username/email unik.
3. Pilih role.
4. Set status `Active`.
5. Simpan.
6. Search user berdasarkan name/email/username.
7. Filter role dan status.
8. Edit name, email, role, status.
9. Update password.
10. Hapus user test.

Expected:

- User tersimpan dan password di-hash.
- Search dan filter bekerja.
- Update tanpa password tidak mengubah password.
- Update dengan password valid berhasil.
- Delete user test berhasil.

### TC-ADM-USER-002 Admin Tidak Bisa Menghapus Akun Sendiri

Steps:

1. Login sebagai `admin`.
2. Coba hapus user admin yang sedang login.

Expected:

- API mengembalikan 422.
- Pesan: tidak dapat menghapus akun sendiri.
- User admin tetap ada.

### TC-ADM-USER-003 Validasi User

Steps:

1. Buat user dengan username yang sudah ada.
2. Buat user dengan email yang sudah ada.
3. Buat user tanpa role.
4. Buat user dengan password kurang dari 6 karakter.

Expected:

- API mengembalikan error validasi.
- UI menampilkan error.
- Data tidak tersimpan.

## 14. Role Management

API terkait:

- `GET /api/roles`
- `POST /api/roles`
- `GET /api/roles/{id}`
- `PUT/PATCH /api/roles/{id}`
- `DELETE /api/roles/{id}`

Field:

- `name` wajib dan unik.
- `description` opsional, maksimal 500 karakter.
- `status`: `Active` atau `Inactive`.

### TC-ADM-ROLE-001 CRUD Role

Steps:

1. Buka `/administrator/manajemen-role`.
2. Buat role test dengan nama unik.
3. Search role.
4. Filter status.
5. Edit description/status.
6. Hapus role test.

Expected:

- Role tersimpan.
- List memuat `users_count`.
- Search/filter bekerja.
- Update berhasil.
- Delete berhasil jika role tidak dipakai user.

### TC-ADM-ROLE-002 Role yang Dipakai Tidak Bisa Dihapus

Steps:

1. Pilih role yang memiliki user.
2. Coba hapus.

Expected:

- API mengembalikan 422.
- Pesan: tidak dapat menghapus role yang masih memiliki user.
- Role tetap ada.

## 15. Profile Admin

Route:

- `/administrator/profile`

### TC-ADM-PROFILE-001 Smoke Test Profile

Steps:

1. Login sebagai admin.
2. Buka `/administrator/profile`.

Expected:

- Halaman profile tampil.
- Data user admin tampil.
- Tidak ada error unauthorized.

Jika halaman mendukung update profile:

- Perubahan field valid tersimpan.
- Email tetap unik.
- Password hanya berubah bila field password diisi dan valid.

## 16. Pengaturan

Route:

- `/administrator/pengaturan`

### TC-ADM-SETTINGS-001 Smoke Test Pengaturan

Steps:

1. Login sebagai admin.
2. Buka `/administrator/pengaturan`.

Expected:

- Halaman pengaturan tampil.
- Layout admin konsisten.
- Tidak ada error fatal di console.

## 17. Cross-cutting UI Requirements

Untuk setiap halaman admin:

- Loading state tampil saat data dimuat.
- Empty state tampil saat data kosong.
- Error state tampil saat request gagal.
- Tombol submit disabled saat request berjalan.
- Setelah create/update/delete, list/detail mencerminkan data terbaru.
- Modal/form dapat ditutup tanpa menyimpan perubahan.
- Search tidak menyebabkan error saat query kosong.
- Pagination tetap mempertahankan filter yang aktif.
- Upload file menolak tipe dan ukuran yang tidak valid.
- Konfirmasi delete tampil sebelum aksi hapus destruktif.
- Teks dan tombol tidak overlap pada viewport desktop dan mobile.

## 18. Security dan Authorization Requirements

- Semua endpoint admin berada di middleware `auth:sanctum`.
- Request tanpa token ke endpoint admin harus ditolak.
- User role non-admin/non-operator tidak boleh mengakses route admin dari frontend.
- Endpoint yang eksplisit membatasi admin/operator, seperti royalty dan editor assignment, harus mengembalikan 403 untuk role lain.
- Token logout tidak dapat digunakan kembali.
- Admin tidak dapat menghapus akun dirinya sendiri.

## 19. Test Data Naming Convention

Gunakan prefix agar data test mudah dicari dan dibersihkan:

- Kategori: `QA Category <timestamp>`
- Author: `QA Author <timestamp>`
- Book: `QA Book <timestamp>`
- News: `QA News <timestamp>`
- User: `qa_user_<timestamp>`
- Role: `QA Role <timestamp>`
- Submission note: `QA submission test <timestamp>`
- Royalty note: `QA royalty test <timestamp>`

## 20. End-to-End Admin Critical Path

### TC-ADM-E2E-001 Publishing Operations Flow

Steps:

1. Login sebagai `admin / password`.
2. Buat kategori buku baru.
3. Buat author baru.
4. Buat buku draft.
5. Edit buku menjadi `published`.
6. Buat royalti untuk buku published.
7. Buat user editor aktif bila belum tersedia.
8. Buka submission existing.
9. Assign primary editor.
10. Tambahkan review manual atau ubah status submission.
11. Buat news published.
12. Logout.

Expected:

- Seluruh operasi berhasil tanpa error fatal.
- Data antar modul konsisten.
- Buku published dapat dipilih untuk royalti.
- Editor aktif dapat dipilih untuk assignment.
- Submission yang diassign berubah menjadi `under_review` jika sebelumnya `submitted`.
- News published muncul di list news.
- Logout membersihkan sesi.

## 21. Regression Checklist

Jalankan checklist ini setiap ada perubahan di portal admin:

- Login admin berhasil.
- Semua route admin utama dapat dibuka.
- List table utama tidak blank saat data ada.
- CRUD kategori buku masih berjalan.
- CRUD author masih berjalan.
- CRUD buku masih berjalan.
- CRUD news masih berjalan.
- Submission detail masih memuat assignment dan review.
- Assignment editor masih berjalan.
- Royalti hanya bisa dibuat untuk buku published.
- User management masih menghormati validasi unique.
- Role yang sedang dipakai user tidak bisa dihapus.
- Build frontend berhasil.

## 22. Verification Commands

Backend route check:

```powershell
php artisan route:list
```

Migration dry run:

```powershell
php artisan migrate --pretend
```

Frontend build:

```powershell
node "C:\Program Files\nodejs\node_modules\npm\bin\npm-cli.js" run build
```

Controller lint contoh:

```powershell
php -l backend\app\Http\Controllers\SubmissionController.php
php -l backend\app\Http\Controllers\BookController.php
php -l backend\app\Http\Controllers\RoyaltyController.php
```
