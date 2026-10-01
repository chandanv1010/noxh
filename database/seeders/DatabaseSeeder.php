<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * `php artisan db:seed` goi thang NoxhSeeder - danh sach day du cac
     * seeder cua NOXH.vn theo dung thu tu phu thuoc. Dung sua danh sach o
     * day, sua trong NoxhSeeder.
     */
    public function run(): void
    {
        $this->call([
            NoxhSeeder::class,
        ]);
    }
}
