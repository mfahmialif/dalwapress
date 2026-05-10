<?php

namespace Database\Seeders;

use App\Models\News;
use App\Models\Role;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    public function run(): void
    {
        $admin = Role::create([
            'name' => 'Admin',
            'description' => 'Full access untuk mengelola UII Dalwa Press.',
            'status' => 'Active',
        ]);

        $operator = Role::create([
            'name' => 'Operator',
            'description' => 'Dapat mengelola dan menerbitkan konten news.',
            'status' => 'Active',
        ]);

        Role::create([
            'name' => 'User',
            'description' => 'Akses baca terbatas.',
            'status' => 'Active',
        ]);

        $adminUser = User::create([
            'username' => 'admin',
            'name' => 'Administrator',
            'email' => 'admin@uiidalwapress.com',
            'password' => bcrypt('password'),
            'role_id' => $admin->id,
            'status' => 'Active',
            'last_active_at' => now(),
        ]);

        User::create([
            'username' => 'operator',
            'name' => 'Operator Konten',
            'email' => 'operator@uiidalwapress.com',
            'password' => bcrypt('password'),
            'role_id' => $operator->id,
            'status' => 'Active',
            'last_active_at' => now()->subHours(2),
        ]);

        $newsItems = [
            [
                'title' => 'UII Dalwa Press Resmi Diluncurkan',
                'category' => 'Artikel',
                'body' => '<p>UII Dalwa Press hadir sebagai pusat publikasi, berita, dan literasi kampus yang dikelola secara profesional.</p>',
                'status' => 'Published',
            ],
            [
                'title' => 'Program Literasi Akademik untuk Civitas UII Dalwa',
                'category' => 'Artikel',
                'body' => '<p>Program ini mendorong budaya menulis, membaca, dan menerbitkan karya akademik secara berkelanjutan.</p>',
                'status' => 'Published',
            ],
            [
                'title' => 'Dokumentasi Kegiatan Editorial UII Dalwa Press',
                'category' => 'Gambar',
                'body' => null,
                'status' => 'Published',
            ],
            [
                'title' => 'Wawancara Redaksi: Arah Baru Publikasi Kampus',
                'category' => 'Video',
                'body' => '<p>Redaksi membahas arah pengembangan publikasi digital UII Dalwa Press.</p>',
                'speaker' => 'Tim Redaksi UII Dalwa Press',
                'duration' => 1800,
                'status' => 'Draft',
            ],
        ];

        foreach ($newsItems as $index => $item) {
            News::create([
                ...$item,
                'created_by' => $adminUser->id,
                'created_at' => now()->subHours($index * 3),
                'updated_at' => now()->subHours($index * 3),
            ]);
        }
    }
}
