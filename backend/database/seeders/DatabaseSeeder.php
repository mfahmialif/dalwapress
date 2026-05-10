<?php

namespace Database\Seeders;

use App\Models\Author;
use App\Models\Book;
use App\Models\BookCategory;
use App\Models\News;
use App\Models\NewsCategory;
use App\Models\Role;
use App\Models\Submission;
use App\Models\SubmissionEditorAssignment;
use App\Models\SubmissionReview;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = Role::updateOrCreate(['name' => 'Admin'], [
            'description' => 'Full access untuk mengelola UII Dalwa Press.',
            'status' => 'Active',
        ]);

        $operator = Role::updateOrCreate(['name' => 'Operator'], [
            'description' => 'Dapat mengelola dan menerbitkan konten news.',
            'status' => 'Active',
        ]);

        $legacyUserRole = Role::where('name', 'User')->first();
        if ($legacyUserRole && !Role::where('name', 'Author')->exists()) {
            $legacyUserRole->update(['name' => 'Author']);
        }

        $authorRole = Role::updateOrCreate(['name' => 'Author'], [
            'description' => 'Akses dashboard author untuk submission dan buku terbit.',
            'status' => 'Active',
        ]);

        $editorRole = Role::updateOrCreate(['name' => 'Editor'], [
            'description' => 'Akses dashboard editor untuk review naskah yang ditugaskan.',
            'status' => 'Active',
        ]);

        if ($legacyUserRole && $legacyUserRole->id !== $authorRole->id) {
            User::where('role_id', $legacyUserRole->id)->update(['role_id' => $authorRole->id]);
            $legacyUserRole->delete();
        }

        $adminUser = User::updateOrCreate(['username' => 'admin'], [
            'name' => 'Administrator',
            'email' => 'admin@uiidalwapress.com',
            'password' => bcrypt('password'),
            'role_id' => $admin->id,
            'status' => 'Active',
            'last_active_at' => now(),
        ]);

        User::updateOrCreate(['username' => 'operator'], [
            'name' => 'Operator Konten',
            'email' => 'operator@uiidalwapress.com',
            'password' => bcrypt('password'),
            'role_id' => $operator->id,
            'status' => 'Active',
            'last_active_at' => now()->subHours(2),
        ]);

        $authorUser = User::updateOrCreate(['username' => 'author'], [
            'name' => 'Muhammad Farhan',
            'email' => 'farhan@example.com',
            'password' => bcrypt('password'),
            'role_id' => $authorRole->id,
            'status' => 'Active',
            'last_active_at' => now()->subMinutes(45),
        ]);

        $editorUser = User::updateOrCreate(['username' => 'editor'], [
            'name' => 'Editor Utama',
            'email' => 'editor@uiidalwapress.com',
            'password' => bcrypt('password'),
            'role_id' => $editorRole->id,
            'status' => 'Active',
            'last_active_at' => now()->subMinutes(30),
        ]);

        $editorUser2 = User::updateOrCreate(['username' => 'editor2'], [
            'name' => 'Editor Pendamping',
            'email' => 'editor2@uiidalwapress.com',
            'password' => bcrypt('password'),
            'role_id' => $editorRole->id,
            'status' => 'Active',
            'last_active_at' => now()->subMinutes(50),
        ]);

        User::updateOrCreate(['username' => 'editor3'], [
            'name' => 'Editor Bahasa',
            'email' => 'editor3@uiidalwapress.com',
            'password' => bcrypt('password'),
            'role_id' => $editorRole->id,
            'status' => 'Active',
            'last_active_at' => now()->subMinutes(80),
        ]);

        $categories = collect([
            [
                'name' => 'Artikel Editorial',
                'slug' => 'artikel-editorial',
                'type' => 'Artikel',
                'description' => 'Artikel, opini, dan tulisan editorial UII Dalwa Press.',
            ],
            [
                'name' => 'Kabar Penerbitan',
                'slug' => 'kabar-penerbitan',
                'type' => 'Artikel',
                'description' => 'Informasi penerbitan buku, katalog, dan layanan press.',
            ],
            [
                'name' => 'Galeri Kegiatan',
                'slug' => 'galeri-kegiatan',
                'type' => 'Gambar',
                'description' => 'Dokumentasi visual kegiatan redaksi dan literasi kampus.',
            ],
            [
                'name' => 'Video Redaksi',
                'slug' => 'video-redaksi',
                'type' => 'Video',
                'description' => 'Konten video wawancara, dokumentasi, dan publikasi digital.',
            ],
        ])->mapWithKeys(fn ($category) => [
            $category['slug'] => NewsCategory::updateOrCreate(['slug' => $category['slug']], $category),
        ]);

        $newsItems = [
            [
                'title' => 'UII Dalwa Press Resmi Diluncurkan',
                'category_slug' => 'kabar-penerbitan',
                'body' => '<p>UII Dalwa Press hadir sebagai pusat publikasi, berita, dan literasi kampus yang dikelola secara profesional.</p>',
                'status' => 'Published',
            ],
            [
                'title' => 'Program Literasi Akademik untuk Civitas UII Dalwa',
                'category_slug' => 'artikel-editorial',
                'body' => '<p>Program ini mendorong budaya menulis, membaca, dan menerbitkan karya akademik secara berkelanjutan.</p>',
                'status' => 'Published',
            ],
            [
                'title' => 'Dokumentasi Kegiatan Editorial UII Dalwa Press',
                'category_slug' => 'galeri-kegiatan',
                'body' => null,
                'status' => 'Published',
            ],
            [
                'title' => 'Wawancara Redaksi: Arah Baru Publikasi Kampus',
                'category_slug' => 'video-redaksi',
                'body' => '<p>Redaksi membahas arah pengembangan publikasi digital UII Dalwa Press.</p>',
                'speaker' => 'Tim Redaksi UII Dalwa Press',
                'duration' => 1800,
                'status' => 'Draft',
            ],
        ];

        foreach ($newsItems as $index => $item) {
            $categorySlug = $item['category_slug'];
            unset($item['category_slug']);

            News::updateOrCreate(['title' => $item['title']], [
                ...$item,
                'news_category_id' => $categories[$categorySlug]->id,
                'created_by' => $adminUser->id,
                'created_at' => now()->subHours($index * 3),
                'updated_at' => now()->subHours($index * 3),
            ]);
        }

        $bookCategories = collect([
            [
                'name' => 'Buku Akademik',
                'slug' => 'buku-akademik',
                'description' => 'Karya ilmiah, buku ajar, dan referensi akademik UII Dalwa.',
            ],
            [
                'name' => 'Studi Islam',
                'slug' => 'studi-islam',
                'description' => 'Publikasi keislaman, pesantren, dakwah, dan turats.',
            ],
            [
                'name' => 'Riset & Prosiding',
                'slug' => 'riset-prosiding',
                'description' => 'Luaran penelitian, prosiding, dan kajian multidisipliner.',
            ],
            [
                'name' => 'Literasi Kampus',
                'slug' => 'literasi-kampus',
                'description' => 'Karya populer, esai, dan literasi civitas akademika.',
            ],
        ])->mapWithKeys(fn ($category) => [
            $category['slug'] => BookCategory::updateOrCreate(['slug' => $category['slug']], $category),
        ]);

        $authors = collect([
            [
                'name' => 'Dr. Ahmad Fathoni, M.Pd.',
                'slug' => 'dr-ahmad-fathoni-mpd',
                'email' => 'ahmad.fathoni@uiidalwa.ac.id',
                'phone' => '081234567890',
                'institution' => 'UII Dalwa',
                'bio' => 'Dosen dan peneliti bidang pendidikan Islam, literasi akademik, dan pengembangan kurikulum pesantren.',
            ],
            [
                'name' => 'Nabila Zahra, M.Hum.',
                'slug' => 'nabila-zahra-mhum',
                'email' => 'nabila.zahra@uiidalwa.ac.id',
                'phone' => '081298765432',
                'institution' => 'UII Dalwa Press',
                'bio' => 'Editor akademik dengan fokus kajian bahasa, penerbitan ilmiah, dan pengelolaan naskah kampus.',
            ],
            [
                'name' => 'Tim Redaksi UII Dalwa Press',
                'slug' => 'tim-redaksi-uii-dalwa-press',
                'email' => 'redaksi@uiidalwapress.com',
                'institution' => 'UII Dalwa Press',
                'bio' => 'Tim editorial yang mengelola katalog, publikasi, dan distribusi karya akademik UII Dalwa.',
            ],
            [
                'user_id' => $authorUser->id,
                'name' => 'Muhammad Farhan',
                'slug' => 'muhammad-farhan',
                'email' => 'farhan@example.com',
                'phone' => '081300001111',
                'institution' => 'UII Dalwa',
                'bio' => 'Author aktif di bidang literasi santri dan transformasi digital pesantren.',
                'social_media' => [
                    'website' => 'https://uiidalwa.ac.id',
                    'instagram' => '@farhan_dalwa',
                ],
            ],
        ])->mapWithKeys(fn ($author) => [
            $author['slug'] => Author::updateOrCreate(['slug' => $author['slug']], $author),
        ]);

        $books = [
            [
                'category_slug' => 'buku-akademik',
                'author_slug' => 'dr-ahmad-fathoni-mpd',
                'title' => 'Metodologi Penulisan Akademik Pesantren',
                'slug' => 'metodologi-penulisan-akademik-pesantren',
                'isbn' => '978-623-90000-1-1',
                'year' => 2026,
                'publisher' => 'UII Dalwa Press',
                'pages' => 214,
                'language' => 'Indonesia',
                'description' => 'Panduan praktis menyusun karya ilmiah berbasis tradisi akademik pesantren dan standar publikasi modern.',
                'table_of_contents' => "Bab 1: Tradisi Keilmuan\nBab 2: Riset dan Kerangka Tulisan\nBab 3: Sitasi dan Publikasi",
                'tags' => 'akademik,pesantren,penulisan',
                'views' => 128,
                'downloads' => 36,
                'featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(12),
            ],
            [
                'category_slug' => 'studi-islam',
                'author_slug' => 'nabila-zahra-mhum',
                'title' => 'Kajian Islam Kontemporer di Lingkungan Kampus',
                'slug' => 'kajian-islam-kontemporer-di-lingkungan-kampus',
                'isbn' => '978-623-90000-2-8',
                'year' => 2025,
                'publisher' => 'UII Dalwa Press',
                'pages' => 186,
                'language' => 'Indonesia',
                'description' => 'Kumpulan kajian tematik mengenai dinamika pemikiran Islam, kampus, dan masyarakat.',
                'table_of_contents' => "Bab 1: Kampus dan Dakwah\nBab 2: Etika Digital\nBab 3: Literasi Keislaman",
                'tags' => 'islam,kampus,dakwah',
                'views' => 94,
                'downloads' => 21,
                'featured' => true,
                'status' => 'published',
                'published_at' => now()->subDays(24),
            ],
            [
                'category_slug' => 'riset-prosiding',
                'author_slug' => 'tim-redaksi-uii-dalwa-press',
                'title' => 'Prosiding Seminar Literasi Akademik 2026',
                'slug' => 'prosiding-seminar-literasi-akademik-2026',
                'isbn' => '978-623-90000-3-5',
                'year' => 2026,
                'publisher' => 'UII Dalwa Press',
                'pages' => 320,
                'language' => 'Indonesia',
                'description' => 'Kumpulan makalah seminar literasi akademik yang membahas penerbitan, riset, dan transformasi digital.',
                'table_of_contents' => "Panel 1: Penerbitan Kampus\nPanel 2: Riset Mahasiswa\nPanel 3: Teknologi Publikasi",
                'tags' => 'prosiding,riset,literasi',
                'views' => 75,
                'downloads' => 18,
                'featured' => false,
                'status' => 'review',
                'published_at' => null,
            ],
        ];

        foreach ($books as $book) {
            $categorySlug = $book['category_slug'];
            $authorSlug = $book['author_slug'];
            unset($book['category_slug'], $book['author_slug']);

            Book::updateOrCreate(['slug' => $book['slug']], [
                ...$book,
                'category_id' => $bookCategories[$categorySlug]->id,
                'author_id' => $authors[$authorSlug]->id,
            ]);
        }

        $submission = Submission::updateOrCreate(['slug' => 'manuskrip-literasi-santri-era-digital'], [
            'user_id' => $authorUser->id,
            'category_id' => $bookCategories['literasi-kampus']->id,
            'title' => 'Manuskrip Literasi Santri Era Digital',
            'author_name' => 'Muhammad Farhan',
            'email' => 'farhan@example.com',
            'phone' => '081300001111',
            'description' => 'Naskah membahas strategi penguatan budaya baca dan tulis santri di ruang digital.',
            'note' => 'Mohon direview untuk kemungkinan diterbitkan sebagai buku populer kampus.',
            'status' => 'revision',
            'submitted_at' => now()->subDays(3),
            'reviewed_at' => now()->subDay(),
        ]);

        SubmissionReview::updateOrCreate([
            'submission_id' => $submission->id,
            'reviewer_email' => 'nabila.zahra@uiidalwa.ac.id',
            'status' => 'revision',
        ], [
            'editor_id' => $editorUser->id,
            'reviewer_name' => 'Nabila Zahra, M.Hum.',
            'note' => 'Tema menarik. Perlu perapian struktur bab dan penambahan referensi pada bagian metodologi.',
            'created_at' => now()->subDay(),
        ]);

        SubmissionEditorAssignment::updateOrCreate([
            'submission_id' => $submission->id,
            'editor_id' => $editorUser->id,
        ], [
            'assigned_by' => $adminUser->id,
            'role' => 'primary',
            'note' => 'Assignment awal dari seeder.',
        ]);

        SubmissionEditorAssignment::updateOrCreate([
            'submission_id' => $submission->id,
            'editor_id' => $editorUser2->id,
        ], [
            'assigned_by' => $adminUser->id,
            'role' => 'co_editor',
            'note' => 'Co-editor awal dari seeder.',
        ]);

        $this->command?->newLine();
        $this->command?->info('Login seed accounts:');
        $this->command?->line('Admin     : username=admin    password=password');
        $this->command?->line('Operator  : username=operator password=password');
        $this->command?->line('Author    : username=author   password=password');
        $this->command?->line('Editor    : username=editor   password=password');
        $this->command?->line('Editor 2  : username=editor2  password=password');
        $this->command?->line('Editor 3  : username=editor3  password=password');
        $this->command?->newLine();
    }
}
