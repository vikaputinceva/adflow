<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignMaterial;
use App\Models\Platform;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class PlatformAndFileTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role): User
    {
        return User::create([
            'name' => 'Тест Тестов',
            'email' => $role . '@test.ru',
            'password' => 'secret123',
            'role' => $role,
        ]);
    }

    private function makeCampaign(string $platform = 'ВКонтакте'): Campaign
    {
        return Campaign::create([
            'name' => 'Тестовая кампания',
            'platform_name' => $platform,
            'status' => 'active',
            'budget_total' => 10000,
            'budget_spent' => 0,
            'revenue_attributed' => 0,
            'end_date' => '2026-12-31',
        ]);
    }

    public function test_only_admin_can_open_platforms(): void
    {
        $this->actingAs($this->makeUser('manager'))->get('/platforms')->assertStatus(403);
        $this->actingAs($this->makeUser('admin'))->get('/platforms')->assertOk();
    }

    public function test_admin_can_add_platform_and_duplicates_are_rejected(): void
    {
        $admin = $this->makeUser('admin');

        $this->actingAs($admin)->post('/platforms', ['name' => 'Одноклассники'])->assertRedirect('/platforms');
        $this->assertDatabaseHas('platforms', ['name' => 'Одноклассники']);

        $this->actingAs($admin)->post('/platforms', ['name' => 'Одноклассники'])->assertSessionHasErrors('name');
    }

    public function test_used_platform_cannot_be_deleted(): void
    {
        $this->makeCampaign('ВКонтакте');
        $platform = Platform::where('name', 'ВКонтакте')->first();

        $this->actingAs($this->makeUser('admin'))->delete('/platforms/' . $platform->id)
            ->assertSessionHasErrors('platform');
        $this->assertDatabaseHas('platforms', ['name' => 'ВКонтакте']);
    }

    public function test_unused_platform_can_be_deleted(): void
    {
        $platform = Platform::create(['name' => 'Новая площадка']);

        $this->actingAs($this->makeUser('admin'))->delete('/platforms/' . $platform->id);
        $this->assertDatabaseMissing('platforms', ['name' => 'Новая площадка']);
    }

    public function test_campaign_requires_platform_from_directory(): void
    {
        $manager = $this->makeUser('manager');
        $data = ['name' => 'Новая', 'budget_total' => 5000, 'end_date' => '2026-12-31'];

        $this->actingAs($manager)->post('/campaigns', $data + ['platform_name' => 'Выдуманная'])
            ->assertSessionHasErrors('platform_name');

        $this->actingAs($manager)->post('/campaigns', $data + ['platform_name' => 'Telegram'])
            ->assertRedirect('/campaigns');
        $this->assertDatabaseHas('campaigns', ['name' => 'Новая', 'platform_name' => 'Telegram']);
    }

    public function test_material_file_is_uploaded_and_removed(): void
    {
        Storage::fake('public');
        $manager = $this->makeUser('manager');
        $campaign = $this->makeCampaign();

        $this->actingAs($manager)->post('/campaigns/' . $campaign->id . '/materials', [
            'title' => 'Баннер',
            'format_label' => '1080x1080',
            'file' => UploadedFile::fake()->create('banner.pdf', 100, 'application/pdf'),
        ])->assertRedirect();

        $material = CampaignMaterial::first();
        $this->assertEquals('banner.pdf', $material->file_name);
        Storage::disk('public')->assertExists($material->file_path);

        $path = $material->file_path;
        $this->actingAs($manager)->delete('/materials/' . $material->id);
        Storage::disk('public')->assertMissing($path);
        $this->assertDatabaseCount('campaign_materials', 0);
    }

    public function test_material_without_file_is_allowed(): void
    {
        $campaign = $this->makeCampaign();

        $this->actingAs($this->makeUser('manager'))->post('/campaigns/' . $campaign->id . '/materials', [
            'title' => 'Текст объявления',
            'format_label' => 'Текст',
        ])->assertRedirect();

        $this->assertDatabaseHas('campaign_materials', ['title' => 'Текст объявления', 'file_path' => null]);
    }

    public function test_bad_file_type_and_big_file_are_rejected(): void
    {
        Storage::fake('public');
        $manager = $this->makeUser('manager');
        $campaign = $this->makeCampaign();
        $url = '/campaigns/' . $campaign->id . '/materials';

        $this->actingAs($manager)->post($url, [
            'title' => 'Скрипт',
            'format_label' => 'php',
            'file' => UploadedFile::fake()->create('hack.php', 10),
        ])->assertSessionHasErrors('file');

        $this->actingAs($manager)->post($url, [
            'title' => 'Огромный',
            'format_label' => 'pdf',
            'file' => UploadedFile::fake()->create('big.pdf', 6000, 'application/pdf'),
        ])->assertSessionHasErrors('file');

        $this->assertDatabaseCount('campaign_materials', 0);
    }

    public function test_deleting_campaign_removes_its_files(): void
    {
        Storage::fake('public');
        $manager = $this->makeUser('manager');
        $campaign = $this->makeCampaign();

        $this->actingAs($manager)->post('/campaigns/' . $campaign->id . '/materials', [
            'title' => 'Баннер',
            'format_label' => 'png',
            'file' => UploadedFile::fake()->image('banner.png'),
        ]);
        $path = CampaignMaterial::first()->file_path;

        $this->actingAs($manager)->delete('/campaigns/' . $campaign->id);
        Storage::disk('public')->assertMissing($path);
    }
}
