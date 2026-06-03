<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

use App\Models\Student;

class StudentSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $students = [
            [
                'id' => '102022400068',
                'name' => 'Galih Pratama',
            ],
            [
                'id' => '102022400001',
                'name' => 'Budi Santoso',
            ],
            [
                'id' => '102022400002',
                'name' => 'Siti Aminah',
            ],
        ];

        foreach ($students as $student) {
            Student::create($student);
        }
    }
}
