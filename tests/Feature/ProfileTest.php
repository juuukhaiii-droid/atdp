<?php
namespace Tests\Feature;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
class ProfileTest extends TestCase
{
    use RefreshDatabase;
    public function test_guest_profile_requires_login(): void
    {
        $this->get('/profile')->assertRedirect(route('login'));
    }
    public function test_employee_profile_shortcut_opens_employee_profile(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $department = \App\Models\Department::create(['name' => 'Kitchen']);
        $shift = \App\Models\Shift::create(['name' => 'Day', 'start_time' => '08:00', 'end_time' => '17:00', 'late_after' => '08:15']);
        \App\Models\Employee::create([
            'user_id' => $user->id, 'employee_code' => 'PROFILE-001', 'full_name' => $user->name,
            'pin' => \Illuminate\Support\Facades\Hash::make('2468'), 'department_id' => $department->id,
            'shift_id' => $shift->id, 'status' => 'active',
        ]);
        $this->actingAs($user)->get('/profile')->assertRedirect(route('employee.profile'));
        $this->get(route('employee.profile'))->assertOk();
    }
    public function test_admin_profile_shortcut_opens_admin_dashboard(): void
    {
        $user = User::factory()->create(['role' => 'admin']);
        $this->actingAs($user)->get('/profile')->assertRedirect(route('admin.dashboard'));
    }
    public function test_employee_cannot_modify_or_delete_admin_managed_account(): void
    {
        $user = User::factory()->create(['role' => 'employee']);
        $this->actingAs($user)->patch('/profile', ['name' => 'Changed'])->assertStatus(405);
        $this->delete('/profile', ['password' => 'password'])->assertStatus(405);
        $this->assertNotNull($user->fresh());
        $this->assertNotSame('Changed', $user->fresh()->name);
    }
}
