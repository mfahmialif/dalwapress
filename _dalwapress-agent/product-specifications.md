# Product Specifications: UII Dalwa Press

Tanggal dokumen: 2026-05-11  
Status: baseline spesifikasi produk berdasarkan codebase saat ini

## 1. Ringkasan Produk

UII Dalwa Press adalah aplikasi web penerbitan kampus yang menggabungkan katalog publik, portal pengajuan naskah, proses review editorial, manajemen buku, berita, komentar, user/role, dan royalti author.

Produk terdiri dari:

- Frontend Vue 3/Vite untuk portal publik, admin/operator, author, dan editor.
- Backend Laravel API dengan autentikasi token Sanctum.
- Penyimpanan file untuk naskah, cover, revisi, cover buku, preview, dan file buku.

Tujuan utama produk adalah membuat proses penerbitan lebih terarah: pembaca dapat menemukan publikasi, author dapat mengirim dan memantau naskah, editor dapat meninjau naskah yang ditugaskan, dan admin/operator dapat mengelola data penerbitan.

## 2. Persona dan Role

### Pengunjung Publik

Pengunjung tidak perlu login. Pengunjung dapat melihat landing page, katalog buku, detail buku, berita, komentar berita, kontak, dan diarahkan ke proses pengajuan naskah melalui portal author.

### Author

Author adalah penulis yang memiliki akun login. Author dapat mengirim naskah, melihat status review, mengunggah revisi, melihat buku yang sudah terbit, memantau royalti, memperbarui profil, melihat notifikasi, dan melihat aktivitas submission.

### Editor

Editor adalah reviewer naskah. Editor hanya dapat melihat submission yang ditugaskan kepada dirinya, membuka detail naskah, melihat histori review/revisi, dan memberikan keputusan review berupa revisi, diterima, atau ditolak.

### Admin

Admin mengelola seluruh data operasional: user, role, kategori buku, author, buku, berita, submission, assignment editor, dan royalti.

### Operator

Operator memiliki portal yang sama dengan admin untuk kebutuhan operasional. Operasi sensitif tertentu, seperti assignment editor, dibatasi untuk role Admin atau Operator.

## 3. Struktur Portal dan Route

### Portal Publik

Base route: `/`

Fitur:

- Landing page di `/`
- Katalog buku di `/books`
- Detail buku di `/books/:id`
- Berita di `/news`
- Detail berita dan komentar di `/news/:id`
- Kontak di `/contact`
- Redirect pengajuan naskah publik dari `/submissions` ke `/author/submissions/create`
- Redirect legacy `info-terkini` ke modul news

### Portal Admin/Operator

Base route: `/administrator`

Fitur:

- Dashboard
- News
- Kategori buku
- Authors
- Books
- Submissions
- Royalti
- Manajemen user
- Manajemen role
- Profile
- Pengaturan

### Portal Author

Base route: `/author`

Fitur:

- Dashboard author
- My Submissions
- Create/Edit Submission
- Detail Submission
- Upload Revisi
- My Published Books
- Royalti Saya
- Profile Author
- Notifications
- Activity Log
- Bookmark Buku

### Portal Editor

Base route: `/editor`

Fitur:

- Dashboard editor
- Assigned Submissions
- Review Submission
- Profile Editor

## 4. Autentikasi dan Otorisasi

Autentikasi menggunakan endpoint `POST /api/login` dan token Sanctum. Frontend menyimpan token di `localStorage` sebagai `auth_token` dan data user sebagai `auth_user`.

Aturan redirect setelah login:

- `Author` diarahkan ke `AuthorDashboard`.
- `Editor` diarahkan ke `EditorDashboard`.
- `Admin` dan `Operator` diarahkan ke `AdminDashboard`.

Aturan akses:

- Route publik tidak membutuhkan token.
- Route `/administrator` membutuhkan login dan default role `Admin` atau `Operator`.
- Route `/author` membutuhkan role `Author`.
- Route `/editor` membutuhkan role `Editor`.
- Jika role tidak cocok, frontend menghapus token dan mengarahkan user ke halaman login.

## 5. Modul Publik

### Landing Page

Landing page harus memperkenalkan UII Dalwa Press sebagai layanan penerbitan kampus. Konten utama mencakup:

- Statistik ringkas: penulis terlayani, judul terbit, e-book terbit.
- Layanan: daftar terbitkan buku, cek progress naskah, konsultasi penerbitan, katalog buku.
- Keunggulan: ISBN resmi, layout profesional, proses terarah, pendampingan naskah, publikasi digital, kerja sama institusi.
- Flow penerbitan: kirim naskah, review editorial, editing, produksi, publikasi.
- News update.
- Contact CTA.

### Katalog Buku

Pengunjung dapat:

- Melihat daftar buku terbit.
- Membuka detail buku.
- Mengunduh file buku melalui endpoint download jika tersedia dan diizinkan.
- Melihat metadata buku seperti kategori, author, ISBN, tahun, penerbit, jumlah halaman, bahasa, deskripsi, daftar isi, tag, status, dan tanggal publish.

### Berita dan Komentar

Pengunjung dapat:

- Melihat daftar berita.
- Membuka detail berita.
- Melihat komentar.
- Mengirim komentar pada berita.

User yang login dapat menyukai komentar melalui endpoint like.

## 6. Modul Submission Author

### Membuat Submission

Author dapat membuat submission baru dengan data:

- `category_id` wajib.
- `title` wajib.
- `description` opsional.
- `note` opsional.
- `manuscript_file` wajib, format `pdf`, `doc`, atau `docx`, maksimal 50 MB.
- `cover_file` opsional, berupa gambar, maksimal 5 MB.

Sistem otomatis mengisi:

- `user_id` dari user login.
- `author_name` dari profil author atau nama user.
- `email` dari user.
- `phone` dari profil author.
- `slug` unik dari judul.
- `status = submitted`.
- `submitted_at = now`.

### Daftar dan Detail Submission

Author dapat melihat hanya submission miliknya. Daftar submission mendukung:

- Pencarian berdasarkan judul.
- Filter status.
- Pagination.

Detail submission menampilkan kategori, review, dan revisi.

### Mengedit Submission

Author dapat mengedit submission miliknya selama status belum `accepted` atau `published`. Jika file baru diunggah, file lama di storage publik dihapus.

### Menghapus Submission

Author hanya dapat menghapus submission dengan status `submitted`. File naskah dan cover terkait ikut dihapus dari storage publik.

### Upload Revisi

Author dapat mengunggah revisi hanya saat status submission adalah `revision`.

Data revisi:

- `revision_file` wajib, format `pdf`, `doc`, atau `docx`, maksimal 50 MB.
- `note` opsional.

Setelah revisi berhasil diunggah:

- Sistem membuat record `SubmissionRevision`.
- Status submission berubah menjadi `under_review`.

## 7. Modul Review Editor

Editor hanya melihat submission yang ditugaskan melalui `submission_editor_assignments`.

### Dashboard Editor

Dashboard menampilkan:

- Total assigned submissions.
- Jumlah per status: `submitted`, `under_review`, `revision`, `accepted`, `rejected`.
- Lima submission terbaru.

### Daftar Assigned Submissions

Daftar mendukung:

- Pencarian berdasarkan judul, nama author, atau email.
- Filter status.
- Pagination.

### Detail dan Review

Editor dapat membuka detail submission yang ditugaskan dan melihat:

- Kategori.
- Review terdahulu.
- Revisi.
- Assignment editor.

Editor dapat mengirim review dengan:

- `status`: `revision`, `accepted`, atau `rejected`.
- `note` opsional.

Sistem otomatis mengisi reviewer dari user editor yang sedang login:

- `editor_id`
- `reviewer_name`
- `reviewer_email`

Setelah review dibuat, status submission berubah sesuai status review dan `reviewed_at` diisi waktu saat ini.

## 8. Modul Admin/Operator Submission

Admin/Operator dapat melihat semua submission dengan:

- Pencarian berdasarkan judul, nama author, atau email.
- Filter kategori.
- Filter status.
- Pagination.

Admin/Operator dapat membuka detail submission, memperbarui metadata/status, menghapus submission, menambahkan review manual, dan menugaskan editor.

### Assignment Editor

Endpoint:

- `GET /api/editors`
- `POST /api/submissions/{submission}/assign-editors`

Aturan assignment:

- Hanya `Admin` dan `Operator` yang boleh mengambil daftar editor dan menyimpan assignment.
- `primary_editor_id` wajib dan harus user aktif dengan role `Editor`.
- `co_editor_ids` opsional, harus user role `Editor`.
- Primary editor tidak boleh diduplikasi sebagai co-editor.
- Assignment lama untuk submission tersebut dihapus saat assignment baru disimpan.
- Satu primary editor dibuat dengan role assignment `primary`.
- Co-editor dibuat dengan role assignment `co_editor`.
- Jika status submission masih `submitted`, sistem mengubah status menjadi `under_review`.

## 9. Modul Buku

Buku dikelola admin/operator dan ditampilkan pada katalog publik jika status publikasinya sesuai.

Data buku utama:

- Category
- Author
- Title
- Slug
- ISBN
- Year
- Publisher
- Pages
- Language
- Cover
- Preview file
- Full file
- Description
- Table of contents
- Tags
- Views
- Downloads
- Featured flag
- Status
- Published date

Relasi buku:

- Buku memiliki kategori.
- Buku memiliki author.
- Buku dapat memiliki gallery.
- Buku dapat memiliki royalty records.

Cover URL dihitung otomatis dari path storage, path absolut, atau URL eksternal.

## 10. Modul Author

Admin/operator dapat mengelola data author. Author juga dapat memperbarui profilnya sendiri melalui portal author.

Data profil author:

- Nama
- Email
- Phone
- Bio
- Institution
- Social media
- Photo
- Password opsional

Saat author memperbarui profil:

- Data user dan data author disinkronkan.
- Email harus unik.
- Password baru minimal 8 karakter dan membutuhkan konfirmasi.
- Jika nama berubah, slug author diperbarui agar tetap unik.
- Jika foto baru diunggah, foto lama dihapus dari storage publik.

## 11. Modul Royalti

Admin/operator mengelola data royalti. Author dapat melihat royalti miliknya dan ringkasannya.

Data royalti:

- Book
- Author
- Period month
- Period year
- Sold quantity
- Sale price per unit
- Royalty per unit
- Gross amount
- Royalty amount
- Status
- Paid date
- Notes
- Created by
- Updated by

Dashboard author menampilkan:

- Total nominal royalti.
- Royalti paid.
- Royalti pending.

## 12. Modul News

Admin/operator dapat mengelola news. Publik dapat membaca news dan kategori news.

Fitur pendukung:

- Upload file dari editor konten melalui `/api/upload-editor`.
- Delete file editor melalui `/api/delete-editor-file`.
- Komentar berita publik.
- Like komentar untuk user login.

## 13. Modul User dan Role

Admin/operator dapat mengelola:

- Roles melalui API resource `roles`.
- Users melalui API resource `users`.

Role utama sistem:

- Admin
- Operator
- Author
- Editor

Seeder development menyediakan akun:

- `admin / password`
- `operator / password`
- `author / password`
- `editor / password`
- `editor2 / password`
- `editor3 / password`

## 14. Status Submission

Status submission resmi:

- `submitted`: naskah baru dikirim author.
- `under_review`: naskah sedang ditangani editorial/editor.
- `revision`: editor meminta revisi.
- `accepted`: naskah diterima.
- `rejected`: naskah ditolak.
- `published`: naskah sudah menjadi buku terbit.

Transisi utama:

- Author submit: `submitted`.
- Admin/operator assign editor pada submission baru: `under_review`.
- Editor meminta revisi: `revision`.
- Author upload revisi: `under_review`.
- Editor menerima: `accepted`.
- Editor menolak: `rejected`.
- Admin/operator dapat memperbarui status sampai `published`.

## 15. Model Data Utama

Entitas utama:

- `User`
- `Role`
- `Author`
- `BookCategory`
- `Book`
- `BookGallery`
- `NewsCategory`
- `News`
- `NewsComment`
- `CommentLike`
- `Submission`
- `SubmissionReview`
- `SubmissionRevision`
- `SubmissionEditorAssignment`
- `Royalty`

Relasi penting:

- `Submission` belongs to `BookCategory`.
- `Submission` belongs to `User`.
- `Submission` has many `SubmissionReview`.
- `Submission` has many `SubmissionRevision`.
- `Submission` has many `SubmissionEditorAssignment`.
- `Submission` has one primary editor assignment.
- `SubmissionReview` belongs to editor user.
- `Book` belongs to `BookCategory`.
- `Book` belongs to `Author`.
- `Book` has many galleries.
- `Book` has many royalties.
- `Royalty` belongs to book and author.

## 16. API Surface

### Public API

- `POST /api/login`
- `GET /api/news`
- `GET /api/news-categories`
- `GET /api/news/{news}`
- `GET /api/news/{news}/comments`
- `POST /api/news/{news}/comments`
- `GET /api/book-categories`
- `GET /api/authors`
- `GET /api/authors/{author}`
- `GET /api/books`
- `GET /api/books/{book}`
- `GET /api/books/{book}/download`

### Authenticated Common API

- `GET /api/user`
- `POST /api/logout`
- `POST /api/comments/{comment}/like`

### Author API

- `GET /api/author/dashboard`
- `GET /api/author/submissions`
- `POST /api/author/submissions`
- `GET /api/author/submissions/{submission}`
- `POST /api/author/submissions/{submission}`
- `DELETE /api/author/submissions/{submission}`
- `POST /api/author/submissions/{submission}/revision`
- `GET /api/author/books`
- `GET /api/author/royalties`
- `GET /api/author/royalties/summary`
- `GET /api/author/profile`
- `POST /api/author/profile`
- `GET /api/author/notifications`
- `GET /api/author/activity`
- `GET /api/author/bookmarks`

### Editor API

- `GET /api/editor/dashboard`
- `GET /api/editor/submissions`
- `GET /api/editor/submissions/{submission}`
- `POST /api/editor/submissions/{submission}/reviews`
- `GET /api/editor/profile`

### Admin/Operator API

- API resource `roles`
- API resource `users`
- API resource `book-categories` except public index
- API resource `authors` except public index/show
- API resource `books` except public index/show
- `GET /api/royalties/summary`
- API resource `royalties`
- API resource `submissions` except store
- `POST /api/submissions/{submission}/reviews`
- `GET /api/editors`
- `POST /api/submissions/{submission}/assign-editors`
- `POST /api/news`
- `PUT /api/news/{news}`
- `DELETE /api/news/{news}`
- `POST /api/upload-editor`
- `POST /api/delete-editor-file`

## 17. File Upload dan Storage

Upload yang didukung:

- Submission manuscript: `submissions/manuscripts`
- Submission cover: `submissions/covers`
- Submission revision: `submissions/revisions`
- Author photo: `authors`
- Book cover, preview file, full file, dan gallery sesuai implementasi modul books
- News/editor assets sesuai implementasi upload editor

Batas validasi yang terlihat pada submission:

- Manuscript/revision: `pdf`, `doc`, `docx`, maksimal 50 MB.
- Cover/photo: image, maksimal 5 MB.

## 18. UX Requirements

### Umum

- Portal admin, author, dan editor menggunakan shell dashboard yang konsisten: sidebar, navbar, horizontal nav, footer, dan tema admin.
- Semua halaman dashboard harus menampilkan state loading, empty state, dan error state yang jelas.
- Tabel/list harus mendukung pencarian dan filter sesuai endpoint.
- Action yang menyimpan data harus memiliki loading state dan disable saat proses berjalan.

### Role-specific

- Author harus selalu melihat submission dan royalti miliknya saja.
- Editor harus selalu melihat submission yang ditugaskan kepadanya saja.
- Admin/operator dapat melihat data lintas user.
- Informasi assignment editor harus mudah dipindai pada detail submission.

## 19. Non-functional Requirements

- Backend harus mengembalikan JSON konsisten untuk API.
- Validasi server wajib menjadi sumber kebenaran.
- File lama harus dibersihkan saat diganti atau submission dihapus.
- Access control harus ditegakkan di backend, bukan hanya router frontend.
- Query daftar harus mendukung pagination untuk menjaga performa.
- Slug author dan submission harus unik.
- Editor review harus memakai identitas user login, bukan input bebas dari frontend.

## 20. Acceptance Criteria Utama

- Pengunjung publik dapat membuka landing page, katalog buku, detail buku, news, detail news, komentar, dan kontak tanpa login.
- User login diarahkan ke portal sesuai role.
- Author dapat membuat submission dengan file valid dan submission muncul di daftar miliknya.
- Author tidak dapat membuka, mengedit, atau menghapus submission milik author lain.
- Author tidak dapat mengedit submission yang sudah `accepted` atau `published`.
- Author hanya dapat upload revisi saat status `revision`, dan setelah upload status berubah ke `under_review`.
- Admin/operator dapat melihat seluruh submission dan menugaskan primary editor serta co-editor.
- Assignment editor mengubah submission baru dari `submitted` ke `under_review`.
- Editor hanya dapat melihat submission yang ditugaskan kepadanya.
- Editor dapat membuat review dan status submission berubah sesuai keputusan review.
- Author dapat melihat notifikasi ketika submission membutuhkan revisi, diterima, dipublikasikan, atau memiliki catatan editor.
- Royalti yang tampil di portal author hanya terkait author tersebut.
- Build frontend berhasil setelah perubahan fitur.

## 21. Known Gaps dan Catatan Produk

- Public route `/submissions` saat ini redirect ke `/author/submissions/create`, sehingga pengunjung harus login sebagai author sebelum mengajukan naskah.
- `AuthorPortalController::bookmarks` saat ini mengembalikan array kosong; fitur bookmark belum memiliki behavior produk lengkap.
- Admin/operator memiliki endpoint review manual yang masih menerima reviewer name/email bebas; untuk audit yang lebih kuat, perilaku ini sebaiknya diselaraskan dengan review editor yang mengambil identitas dari user login.
- Perubahan status `published` dari submission ke book perlu didefinisikan lebih eksplisit bila produk ingin otomatis membuat record buku dari submission yang diterima.
- Notifikasi author saat ini dihitung dari submission/review, belum menggunakan tabel notifikasi terpisah.

## 22. Verifikasi Teknis yang Disarankan

Untuk perubahan produk berikutnya, jalankan pemeriksaan terfokus:

```powershell
php artisan route:list
php artisan migrate --pretend
node "C:\Program Files\nodejs\node_modules\npm\bin\npm-cli.js" run build
```

Untuk perubahan controller tertentu, tambahkan lint PHP:

```powershell
php -l backend\app\Http\Controllers\NamaController.php
```
