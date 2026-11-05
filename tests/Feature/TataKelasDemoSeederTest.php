<?php

namespace Tests\Feature;

use App\Models\Classroom;
use App\Models\Folder;
use App\Models\Photo;
use App\Models\User;
use Database\Seeders\TataKelasDemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class TataKelasDemoSeederTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        config(['seeding.demo_password' => 'local-demo-password']);
        Storage::fake('local');
    }

    public function test_demo_seeder_creates_accounts_and_sample_data_idempotently(): void
    {
        $this->seed(TataKelasDemoSeeder::class);
        $this->seed(TataKelasDemoSeeder::class);

        $this->assertDatabaseCount('users', 4);
        $this->assertDatabaseCount('classrooms', 1);
        $this->assertDatabaseCount('classroom_user', 2);
        $this->assertDatabaseCount('folders', 5);
        $this->assertDatabaseCount('resources', 7);
        $this->assertDatabaseCount('notes', 2);
        $this->assertDatabaseCount('photos', 1);
        $this->assertDatabaseCount('submissions', 2);

        $this->assertSame('admin', User::where('email', 'admin@tatakelas.test')->value('role'));
        $this->assertSame('teacher', User::where('email', 'guru@tatakelas.test')->value('role'));
        $this->assertSame('student', User::where('email', 'murid1@tatakelas.test')->value('role'));
        $this->assertTrue(Hash::check('local-demo-password', User::where('email', 'admin@tatakelas.test')->value('password')));
        $this->assertDatabaseMissing('folders', [
            'user_id' => User::where('email', 'admin@tatakelas.test')->value('id'),
        ]);

        $photo = Photo::firstOrFail();
        Storage::disk('local')->assertExists($photo->filepath);
        $this->assertSame('image/gif', getimagesizefromstring(Storage::disk('local')->get($photo->filepath))['mime']);
    }

    public function test_demo_students_are_enrolled_and_private_folders_remain_inaccessible_to_other_roles(): void
    {
        $this->seed(TataKelasDemoSeeder::class);

        $classroom = Classroom::firstOrFail();
        $this->assertCount(2, $classroom->students);

        $privateFolder = Folder::withoutGlobalScopes()
            ->where('name', 'Workspace Pribadi')
            ->firstOrFail();

        foreach (['guru@tatakelas.test', 'admin@tatakelas.test', 'murid2@tatakelas.test'] as $email) {
            $this->actingAs(User::where('email', $email)->firstOrFail());
            $this->assertFalse(Folder::query()->whereKey($privateFolder->id)->exists());
        }
    }

    public function test_demo_seeder_requires_a_locally_configured_password(): void
    {
        config(['seeding.demo_password' => null]);

        $this->expectException(\RuntimeException::class);
        $this->expectExceptionMessage('Set TATAKELAS_DEMO_PASSWORD');

        $this->seed(TataKelasDemoSeeder::class);
    }
}
