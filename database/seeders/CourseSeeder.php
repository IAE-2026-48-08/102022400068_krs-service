<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Course;

class CourseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $courses = [
            [
                'code' => 'IF-101',
                'name' => 'Pemrograman Dasar',
                'credits' => 3,
                'quota' => 30,
                'remaining_quota' => 30,
            ],
            [
                'code' => 'IF-202',
                'name' => 'Struktur Data & Algoritma',
                'credits' => 4,
                'quota' => 25,
                'remaining_quota' => 25,
            ],
            [
                'code' => 'IF-303',
                'name' => 'Rekayasa Perangkat Lunak',
                'credits' => 3,
                'quota' => 35,
                'remaining_quota' => 35,
            ],
            [
                'code' => 'IF-404',
                'name' => 'Pemrograman Web Lanjut',
                'credits' => 3,
                'quota' => 20,
                'remaining_quota' => 20,
            ],
            [
                'code' => 'IF-505',
                'name' => 'Kecerdasan Buatan',
                'credits' => 4,
                'quota' => 15,
                'remaining_quota' => 15,
            ],
        ];

        foreach ($courses as $course) {
            Course::create($course);
        }
    }
}
