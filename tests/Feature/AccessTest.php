<?php

namespace Tests\Feature;

use App\Models\Campaign;
use App\Models\CampaignTask;
use App\Models\LeadRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AccessTest extends TestCase
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

    private function makeCampaign(): Campaign
    {
        return Campaign::create([
            'name' => 'Тестовая кампания',
            'platform_name' => 'ВКонтакте',
            'status' => 'active',
            'budget_total' => 10000,
            'budget_spent' => 1000,
            'revenue_attributed' => 2500,
            'end_date' => '2026-12-31',
        ]);
    }

    public function test_guest_is_redirected_to_login(): void
    {
        $this->get('/campaigns')->assertRedirect('/login');
        $this->get('/tasks')->assertRedirect('/login');
    }

    public function test_login_with_wrong_password_fails(): void
    {
        $this->makeUser('manager');

        $this->post('/login', ['email' => 'manager@test.ru', 'password' => 'wrong'])
            ->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_manager_can_open_campaigns(): void
    {
        $this->actingAs($this->makeUser('manager'))->get('/campaigns')->assertOk();
    }

    public function test_executor_cannot_open_campaigns_and_finance(): void
    {
        $executor = $this->makeUser('executor');

        $this->actingAs($executor)->get('/campaigns')->assertStatus(403);
        $this->actingAs($executor)->get('/finance')->assertStatus(403);
    }

    public function test_executor_can_open_tasks(): void
    {
        $this->actingAs($this->makeUser('executor'))->get('/tasks')->assertOk();
    }

    public function test_manager_cannot_open_users_page(): void
    {
        $this->actingAs($this->makeUser('manager'))->get('/users')->assertStatus(403);
    }

    public function test_admin_can_create_user(): void
    {
        $this->actingAs($this->makeUser('admin'))
            ->post('/users', [
                'name' => 'Новый Сотрудник',
                'email' => 'new@test.ru',
                'phone' => '+79990001122',
                'role' => 'executor',
                'password' => 'secret123',
            ])
            ->assertRedirect('/users');

        $this->assertDatabaseHas('users', ['email' => 'new@test.ru', 'role' => 'executor']);
    }

    public function test_executor_sees_only_own_tasks(): void
    {
        $campaign = $this->makeCampaign();
        $me = $this->makeUser('executor');
        $other = User::create([
            'name' => 'Другой Исполнитель',
            'email' => 'other@test.ru',
            'password' => 'secret123',
            'role' => 'executor',
        ]);

        CampaignTask::create([
            'campaign_id' => $campaign->id,
            'user_id' => $me->id,
            'title' => 'Моя задача для проверки',
            'assignee_role' => 'designer',
        ]);
        CampaignTask::create([
            'campaign_id' => $campaign->id,
            'user_id' => $other->id,
            'title' => 'Чужая задача для проверки',
            'assignee_role' => 'designer',
        ]);

        $this->actingAs($me)->get('/tasks')
            ->assertSee('Моя задача для проверки')
            ->assertDontSee('Чужая задача для проверки');
    }

    public function test_roi_is_calculated(): void
    {
        $campaign = $this->makeCampaign();

        // (2500 - 1000) / 1000 * 100 = 150
        $this->assertEquals(150, $campaign->roi);
        $this->assertEquals(10, $campaign->spent_percent);
    }

    public function test_lead_can_be_converted_to_campaign(): void
    {
        $lead = LeadRequest::create([
            'client_name' => 'Кофейня Утро',
            'contact' => '+79990000000',
            'platform_name' => 'ВКонтакте',
            'budget_estimate' => 40000,
        ]);

        $this->actingAs($this->makeUser('manager'))->post('/leads/' . $lead->id . '/convert');

        $this->assertDatabaseHas('campaigns', ['name' => 'Кофейня Утро']);
        $this->assertDatabaseHas('lead_requests', ['id' => $lead->id, 'status' => 'in_work']);
    }
}
