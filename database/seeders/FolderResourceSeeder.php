<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class FolderResourceSeeder extends Seeder
{
    public function run(): void
    {
        $this->call(DemoSeeder::class);
    }
}
