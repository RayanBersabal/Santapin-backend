<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Member;
use Illuminate\Support\Facades\Schema;

class MemberSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Nonaktifkan foreign key checks untuk sementara
        Schema::disableForeignKeyConstraints();
        // Kosongkan tabel members
        Member::truncate();
        // Aktifkan kembali foreign key checks
        Schema::enableForeignKeyConstraints();

        $members = [
            [
                'name' => 'Egidius Dicky Narendra Baas',
                'role' => ['Team Leader', 'UI/UX Designer'],
                'task' => [
                    'Mengkoordinasikan tim dan arsitektur proyek',
                    'Merancang pengalaman interaktif',
                    'Implementasi desain responsif',
                ],
                'image' => 'https://avatars.githubusercontent.com/u/162414603?v=4',
                'github' => 'https://github.com/egidiusdicky',
                'nim' => '23.11.5490',
            ],
            [
                'name' => 'Rayan',
                'role' => ['Frontend Developer', 'Backend Developer'],
                'task' => [
                    'Mengembangkan antarmuka pengguna',
                    'Mengembangkan API backend',
                    'Manajemen database'
                ],
                'image' => 'https://avatars.githubusercontent.com/u/87006289?v=4',
                'github' => 'https://github.com/rayanbersabal',
                'nim' => '23.11.5486',
            ],
            [
                'name' => 'Garda Fitrananda',
                'role' => ['Frontend Developer'],
                'task' => [
                    'Implementasi desain visual',
                    'Memastikan responsivitas layout',
                    'Optimasi performa frontend'
                ],
                'image' => 'https://avatars.githubusercontent.com/u/202229964?v=4',
                'github' => 'https://github.com/gardafitrananda',
                'nim' => '23.11.5440',
            ],
            [
                'name' => 'Sauzana',
                'role' => ['Frontend Developer'],
                'task' => [
                    'Manajemen state aplikasi',
                    'Integrasi dengan komponen backend',
                    'Debugging UI'
                ],
                'image' => 'https://avatars.githubusercontent.com/u/202231744?v=4',
                'github' => 'https://github.com/Sauzana1919',
                'nim' => '23.11.5422',
            ],
            [
                'name' => 'Sandi Setiawan',
                'role' => ['Frontend Developer'],
                'task' => [
                    'Optimasi performa antarmuka pengguna',
                    'Mengembangkan fungsionalitas UI',
                    'Testing cross-browser compatibility'
                ],
                'image' => 'https://avatars.githubusercontent.com/u/193219383?v=4',
                'github' => 'https://github.com/SandiSetiawann',
                'nim' => '23.11.5443',
            ],
            [
                'name' => 'Fahrudiansyah',
                'role' => ['Frontend Developer'],
                'task' => [
                    'Membangun komponen UI yang reusable',
                    'Menerapkan logika bisnis di frontend',
                    'Melakukan code review'
                ],
                'image' => 'https://avatars.githubusercontent.com/u/202230345?v=4',
                'github' => 'https://github.com/Fahrudiyansah',
                'nim' => '23.11.5459',
            ],

        ];

        foreach ($members as $member) {
            Member::create($member);
        }

        $this->command->info('Members seeded successfully!');
    }
}
