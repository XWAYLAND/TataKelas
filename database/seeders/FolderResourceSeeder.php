<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Folder;
use App\Models\Resource;

class FolderResourceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create Folders
        $laravelFolder = Folder::create([
            'name' => 'Laravel Development',
            'description' => 'Everything related to Laravel framework, best practices, and packages',
            'color_code' => '#EF4444',
        ]);

        $jsFolder = Folder::create([
            'name' => 'JavaScript & React',
            'description' => 'Modern JavaScript and React ecosystem resources',
            'color_code' => '#3B82F6',
        ]);

        $designFolder = Folder::create([
            'name' => 'UI/UX Design',
            'description' => 'Design systems, UI patterns, and UX principles',
            'color_code' => '#8B5CF6',
        ]);

        $devopsFolder = Folder::create([
            'name' => 'DevOps & Deployment',
            'description' => 'CI/CD, Docker, server management resources',
            'color_code' => '#10B981',
        ]);

        // Create Resources for Laravel Folder
        Resource::create([
            'folder_id' => $laravelFolder->id,
            'title' => 'Laravel 11 Official Documentation',
            'url' => 'https://laravel.com/docs/11.x',
            'resource_type' => 'documentation',
            'notes' => 'Complete guide for Laravel 11. Check routing, eloquent, and blade sections.',
            'read_status' => 'completed',
        ]);

        Resource::create([
            'folder_id' => $laravelFolder->id,
            'title' => 'Laravel Best Practices',
            'url' => 'https://github.com/alexeymezenin/laravel-best-practices',
            'resource_type' => 'repository',
            'notes' => 'Comprehensive guide on Laravel best practices and design patterns',
            'read_status' => 'reading',
        ]);

        Resource::create([
            'folder_id' => $laravelFolder->id,
            'title' => 'Laracasts - Laravel From Scratch',
            'url' => 'https://laracasts.com/series/laravel-from-scratch',
            'resource_type' => 'video',
            'notes' => 'Jeffrey Way\'s excellent video series for beginners',
            'read_status' => 'to_read',
        ]);

        // Create Resources for JavaScript Folder
        Resource::create([
            'folder_id' => $jsFolder->id,
            'title' => 'React Official Documentation',
            'url' => 'https://react.dev/',
            'resource_type' => 'documentation',
            'notes' => 'The new React docs with hooks and modern patterns',
            'read_status' => 'reading',
        ]);

        Resource::create([
            'folder_id' => $jsFolder->id,
            'title' => 'JavaScript.info - Modern JavaScript Tutorial',
            'url' => 'https://javascript.info/',
            'resource_type' => 'article',
            'notes' => 'In-depth JavaScript tutorial covering everything from basics to advanced',
            'read_status' => 'completed',
        ]);

        Resource::create([
            'folder_id' => $jsFolder->id,
            'title' => 'TypeScript Handbook',
            'url' => 'https://www.typescriptlang.org/docs/handbook/intro.html',
            'resource_type' => 'documentation',
            'notes' => 'Official TypeScript documentation and handbook',
            'read_status' => 'to_read',
        ]);

        // Create Resources for Design Folder
        Resource::create([
            'folder_id' => $designFolder->id,
            'title' => 'Tailwind CSS Documentation',
            'url' => 'https://tailwindcss.com/docs',
            'resource_type' => 'documentation',
            'notes' => 'Utility-first CSS framework documentation',
            'read_status' => 'completed',
        ]);

        Resource::create([
            'folder_id' => $designFolder->id,
            'title' => 'Refactoring UI',
            'url' => 'https://www.refactoringui.com/',
            'resource_type' => 'other',
            'notes' => 'Learn how to design beautiful UIs by yourself',
            'read_status' => 'reading',
        ]);

        // Create Resources for DevOps Folder
        Resource::create([
            'folder_id' => $devopsFolder->id,
            'title' => 'Docker Documentation',
            'url' => 'https://docs.docker.com/',
            'resource_type' => 'documentation',
            'notes' => 'Official Docker documentation and guides',
            'read_status' => 'to_read',
        ]);

        Resource::create([
            'folder_id' => $devopsFolder->id,
            'title' => 'Laravel Forge',
            'url' => 'https://forge.laravel.com/',
            'resource_type' => 'other',
            'notes' => 'Server management and deployment platform for Laravel',
            'read_status' => 'completed',
        ]);
    }
}
