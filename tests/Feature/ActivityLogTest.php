<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;
    private User $manager;
    private User $staff;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create([
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->manager = User::factory()->create([
            'role' => 'manager',
            'is_active' => true,
        ]);

        $this->staff = User::factory()->create([
            'role' => 'staff',
            'is_active' => true,
        ]);
    }

    public function test_staff_and_manager_cannot_access_activity_logs(): void
    {
        $this->actingAs($this->staff)->get(route('activity-logs.index'))->assertForbidden();
        $this->actingAs($this->manager)->get(route('activity-logs.index'))->assertForbidden();
    }

    public function test_admin_can_view_activity_logs(): void
    {
        ActivityLog::create([
            'user_id' => $this->admin->id,
            'action' => 'login',
            'description' => 'User login ke sistem',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)->get(route('activity-logs.index'));
        $response->assertOk();
        $response->assertSee('Log Aktivitas (Audit Trail)');
        $response->assertSee('User login ke sistem');
    }
}
