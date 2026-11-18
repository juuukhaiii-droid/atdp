<?php

namespace Tests\Feature;

use App\Models\Attendance;
use App\Models\AttendancePoint;
use App\Models\Department;
use App\Models\Employee;
use App\Models\Shift;
use App\Models\User;
use App\Services\AttendanceLocationVerifier;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class AttendanceLocationTest extends TestCase
{
    use RefreshDatabase;

    private User $employeeUser;
    private AttendancePoint $point;

    protected function setUp(): void
    {
        parent::setUp();
        config(['services.telegram.bot_token' => 'test-bot-token', 'services.telegram.chat_id' => 'test-chat']);
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => true], 200)]);
        $this->travelTo(now()->setTime(10, 0));
        $department = Department::create(['name' => 'Kitchen']);
        $shift = Shift::create(['name' => 'Day', 'start_time' => '08:00', 'end_time' => '17:00', 'late_after' => '08:15']);
        $this->employeeUser = User::factory()->create(['role' => 'employee']);
        Employee::create([
            'employee_code' => 'GPS-001', 'full_name' => 'GPS Employee', 'pin' => Hash::make('2468'),
            'department_id' => $department->id, 'shift_id' => $shift->id,
            'user_id' => $this->employeeUser->id, 'status' => 'active',
        ]);
        $this->point = AttendancePoint::create([
            'name' => 'Shop', 'code' => 'GPS-TEST', 'qr_token' => 'gps-test-token', 'status' => 'active',
            'department_id' => $department->id, 'latitude' => 11.594184, 'longitude' => 104.810963, 'radius_meters' => 100,
        ]);
    }

    private function coordinates(array $overrides = []): array
    {
        return array_merge([
            'attendance_point_id' => $this->point->id, 'attendance_type' => 'in',
            'latitude' => 11.594184, 'longitude' => 104.810963,
            'location_accuracy' => 10, 'location_captured_at' => now()->toIso8601String(),
        ], $overrides);
    }

    public function test_malformed_scan_links_return_to_scanner_without_a_redirect_loop(): void
    {
        foreach (['broken', '', ['unexpected']] as $scan) {
            $this->actingAs($this->employeeUser)
                ->get(route('attendance.show', ['token' => $this->point->qr_token, 'scan' => $scan]))
                ->assertRedirect(route('attendance.show.qr'))
                ->assertSessionHasErrors('scan');
        }
        $this->assertDatabaseCount('attendances', 0);
        Http::assertNothingSent();
    }

    public function test_nearby_employee_can_check_in_and_gps_evidence_is_saved(): void
    {
        $this->actingAs($this->employeeUser)->withServerVariables(['REMOTE_ADDR' => '203.0.113.10'])
            ->post(route('employee.attendance.checkin'), $this->coordinates())
            ->assertRedirect(route('employee.attendance.success'));
        $event = Attendance::sole();
        $this->assertSame($this->point->id, $event->attendance_point_id);
        $this->assertEquals(11.594184, $event->latitude);
        $this->assertEquals(104.810963, $event->longitude);
        $this->assertEquals(10, $event->location_accuracy);
        $this->assertEquals(0, $event->distance_meters);
        $this->assertNotNull($event->location_captured_at);
    }

    public function test_employee_history_and_recent_activity_include_admin_corrections_without_raw_scans(): void
    {
        app(\App\Services\AttendanceSummaryService::class)->upsertOverride(
            $this->employeeUser->employee, today()->toDateString(), '08:00:00', '17:00:00', $this->point->id, 'Corrected attendance'
        );
        $this->actingAs($this->employeeUser)->get(route('employee.dashboard'))
            ->assertOk()->assertSee('Corrected attendance')->assertSee('08:00')->assertSee('17:00')
            ->assertDontSee('No attendance records yet.');
        $this->get(route('employee.attendance.history'))
            ->assertOk()->assertSee('Corrected attendance')->assertSee('08:00')->assertSee('17:00')
            ->assertDontSee('No attendance records yet.');
        $other = User::factory()->create(['role' => 'employee']);
        Employee::create(['employee_code' => 'OTHER-01', 'full_name' => 'Other Employee', 'pin' => Hash::make('2468'), 'user_id' => $other->id, 'status' => 'active', 'department_id' => $this->employeeUser->employee->department_id, 'shift_id' => $this->employeeUser->employee->shift_id]);
        $this->actingAs($other)->get(route('employee.attendance.history'))
            ->assertOk()->assertDontSee('Corrected attendance')->assertSee('No attendance records yet.');
    }

    public function test_employee_history_pairs_overnight_scans_on_one_workday(): void
    {
        $this->employeeUser->employee->shift->update(['start_time' => '18:00:00', 'end_time' => '06:00:00', 'late_after' => '18:15:00']);
        foreach (['in' => today()->setTime(18, 0), 'out' => today()->addDay()->setTime(6, 0)] as $type => $time) {
            Attendance::create(['user_id' => $this->employeeUser->id, 'attendance_point_id' => $this->point->id, 'type' => $type, 'scanned_at' => $time, 'ip_address' => '127.0.0.1']);
        }
        $this->actingAs($this->employeeUser)->get(route('employee.attendance.history'))
            ->assertOk()->assertViewHas('records', fn ($records) => $records->total() === 1)
            ->assertSee('18:00')->assertSee('06:00');
    }

    public function test_history_month_and_status_filters_keep_monthly_stats_and_duration(): void
    {
        $service = app(\App\Services\AttendanceSummaryService::class);
        $service->upsertOverride($this->employeeUser->employee, '2026-11-01', '08:00:00', '17:30:00', $this->point->id, 'November shift');
        $service->upsertOverride($this->employeeUser->employee, '2026-10-01', '08:00:00', '17:00:00', $this->point->id, 'October shift');
        $this->actingAs($this->employeeUser)->get(route('employee.attendance.history', ['month' => '2026-11']))
            ->assertOk()->assertSee('November shift')->assertDontSee('October shift')
            ->assertViewHas('stats', fn ($stats) => $stats['present'] === 1 && $stats['span_minutes'] === 570)
            ->assertViewHas('records', fn ($records) => $records->first()->span_minutes === 570);
        $this->get(route('employee.attendance.history', ['month' => '2026-11', 'status' => 'late']))
            ->assertOk()->assertViewHas('records', fn ($records) => $records->total() === 0)
            ->assertViewHas('stats', fn ($stats) => $stats['present'] === 1);
        $this->getJson(route('employee.attendance.history', ['month' => 'bad']))->assertUnprocessable();
    }

    public function test_check_out_requires_a_new_location_and_saves_work_summary(): void
    {
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'), $this->coordinates());
        $this->post(route('employee.attendance.checkout'), ['attendance_point_id' => $this->point->id])
            ->assertSessionHasErrors(['latitude', 'longitude', 'location_accuracy', 'location_captured_at']);
        $this->post(route('employee.attendance.checkout'), $this->coordinates(['work_summary' => 'Cleaned kitchen']))
            ->assertRedirect(route('employee.attendance.success'));
        $this->assertDatabaseCount('attendances', 2);
        $this->assertDatabaseHas('attendances', ['type' => 'out', 'work_summary' => 'Cleaned kitchen']);
    }

    public function test_employee_outside_radius_is_rejected_on_both_paths(): void
    {
        $this->actingAs($this->employeeUser);
        foreach ([route('employee.attendance.checkin'), route('attendance.submit', $this->point->qr_token)] as $url) {
            $this->post($url, $this->coordinates(['latitude' => 11.604184]))->assertSessionHasErrors('location');
        }
        $this->assertDatabaseCount('attendances', 0);
        Http::assertNothingSent();
    }

    public function test_location_is_required_for_direct_and_qr_submissions(): void
    {
        $this->actingAs($this->employeeUser);
        foreach ([route('employee.attendance.checkin'), route('attendance.submit', $this->point->qr_token)] as $url) {
            $this->post($url, ['attendance_point_id' => $this->point->id, 'attendance_type' => 'in'])
                ->assertSessionHasErrors(['latitude', 'longitude', 'location_accuracy', 'location_captured_at']);
        }
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_low_accuracy_stale_future_and_invalid_coordinates_are_rejected(): void
    {
        $this->actingAs($this->employeeUser);
        foreach ([
            ['location_accuracy' => 101], ['location_captured_at' => now()->subMinutes(3)->toIso8601String()],
            ['location_captured_at' => now()->addMinutes(2)->toIso8601String()],
            ['latitude' => 91], ['longitude' => -181], ['location_accuracy' => -1],
        ] as $badLocation) {
            $this->post(route('employee.attendance.checkin'), $this->coordinates($badLocation))->assertSessionHasErrors();
        }
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_large_accuracy_does_not_expand_the_allowed_radius(): void
    {
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'),
            $this->coordinates(['latitude' => 11.595534, 'location_accuracy' => 100]))
            ->assertSessionHasErrors('location');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_unconfigured_checkpoint_is_blocked(): void
    {
        $this->point->update(['latitude' => null, 'longitude' => null]);
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'), $this->coordinates())
            ->assertSessionHasErrors('location');
        $this->get(route('attendance.show', $this->point->qr_token))->assertOk()->assertSee('GPS is not configured');
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_inactive_checkpoint_is_blocked_for_direct_and_qr(): void
    {
        $this->point->update(['status' => 'inactive']);
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'), $this->coordinates())
            ->assertSessionHasErrors('location');
        $this->post(route('attendance.submit', $this->point->qr_token), $this->coordinates())->assertNotFound();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_inactive_employee_cannot_record_attendance(): void
    {
        $this->employeeUser->employee->update(['status' => 'inactive']);
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'), $this->coordinates())->assertForbidden();
        $this->post(route('attendance.submit', $this->point->qr_token), $this->coordinates())->assertForbidden();
        $this->assertDatabaseCount('attendances', 0);
    }

    public function test_automatic_qr_scans_alternate_but_refresh_and_replay_do_not_toggle(): void
    {
        $url = route('attendance.show', $this->point->qr_token);
        $post = route('attendance.submit', $this->point->qr_token);
        $page = $this->actingAs($this->employeeUser)->get($url)->assertOk()->assertSee('data-auto-start="true"', false);
        $scanId = $page->viewData('scanId');
        $this->assertDatabaseCount('attendances', 0);
        $data = $this->coordinates(['scan_id' => $scanId]);
        $this->post($post, $data)->assertRedirect(route('employee.attendance.success'));
        $this->post($post, $data)->assertRedirect(route('employee.attendance.success'));
        $this->get($url . '?scan=' . $scanId)->assertRedirect(route('employee.attendance.success'));
        $this->assertDatabaseCount('attendances', 1);
        Http::assertSentCount(1);

        $second = $this->get($url)->assertOk()->assertViewHas('nextAction', 'out');
        $secondId = $second->viewData('scanId');
        $this->assertNotSame($scanId, $secondId);
        $this->post($post, $this->coordinates(['scan_id' => $secondId, 'attendance_type' => 'out']))
            ->assertRedirect(route('employee.attendance.success'));
        $this->assertDatabaseCount('attendances', 2);
        $this->assertSame(['in', 'out'], Attendance::orderBy('id')->pluck('type')->all());
        Http::assertSentCount(2);
        $this->get($url . '?scan=' . $scanId)->assertRedirect(route('employee.attendance.success'));
        $this->assertDatabaseCount('attendances', 2);
    }

    public function test_failed_location_does_not_automatically_retry_or_consume_the_scan(): void
    {
        $url = route('attendance.show', $this->point->qr_token);
        $page = $this->actingAs($this->employeeUser)->get($url);
        $scanId = $page->viewData('scanId');
        $this->from($url . '?scan=' . $scanId)->post(route('attendance.submit', $this->point->qr_token),
            $this->coordinates(['scan_id' => $scanId, 'latitude' => 11.7]))->assertSessionHasErrors('location');
        $this->get($url . '?scan=' . $scanId)->assertOk()->assertSee('data-auto-start="false"', false);
        $this->assertDatabaseCount('attendances', 0);
        $this->post(route('attendance.submit', $this->point->qr_token), $this->coordinates(['scan_id' => $scanId]))
            ->assertRedirect(route('employee.attendance.success'));
        $this->assertDatabaseCount('attendances', 1);
    }

    public function test_expired_scan_requires_scanning_again_without_recording(): void
    {
        $url = route('attendance.show', $this->point->qr_token);
        $scanId = $this->actingAs($this->employeeUser)->get($url)->viewData('scanId');
        $this->travel(6)->minutes();
        $this->post(route('attendance.submit', $this->point->qr_token), $this->coordinates(['scan_id' => $scanId]))
            ->assertSessionHasErrors('scan');
        $this->assertDatabaseCount('attendances', 0);
        Http::assertNothingSent();
    }

    public function test_qr_double_submission_does_not_toggle_into_check_out(): void
    {
        $url = route('attendance.submit', $this->point->qr_token);
        $this->actingAs($this->employeeUser)->post($url, $this->coordinates())->assertSessionHasNoErrors();
        $this->post($url, $this->coordinates())->assertSessionHasErrors('attendance');
        $this->assertDatabaseCount('attendances', 1);
        $this->assertSame('in', Attendance::sole()->type);
    }

    public function test_employee_cannot_edit_geofence_or_read_other_employee_locations(): void
    {
        $this->actingAs($this->employeeUser)->get(route('admin.attendance-points.edit', $this->point))->assertForbidden();
        $this->put(route('admin.attendance-points.update', $this->point), [])->assertForbidden();
        $this->get(route('admin.attendances.index'))->assertForbidden();
    }

    public function test_admin_can_configure_gps_and_must_use_valid_values(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $data = $this->point->only('name', 'code', 'department_id', 'status', 'latitude', 'longitude', 'radius_meters');
        $this->actingAs($admin)->put(route('admin.attendance-points.update', $this->point), array_merge($data, ['radius_meters' => 150]))
            ->assertSessionHasNoErrors();
        $this->assertEquals(150, $this->point->fresh()->radius_meters);
        $this->put(route('admin.attendance-points.update', $this->point), array_merge($data, ['latitude' => 91, 'radius_meters' => 1]))
            ->assertSessionHasErrors(['latitude', 'radius_meters']);
    }

    public function test_haversine_distance_uses_meters(): void
    {
        $verifier = new AttendanceLocationVerifier;
        $this->assertEqualsWithDelta(0, $verifier->distance(11.594184, 104.810963, 11.594184, 104.810963), 0.001);
        $this->assertEqualsWithDelta(111.195, $verifier->distance(11.594184, 104.810963, 11.595184, 104.810963), 0.1);
    }

    public function test_admin_can_view_gps_evidence_without_fabricating_location_for_old_events(): void
    {
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'), $this->coordinates());
        Attendance::create([
            'user_id' => $this->employeeUser->id, 'type' => 'out', 'scanned_at' => now()->addHour(),
            'ip_address' => '192.0.2.10',
        ]);
        $admin = User::factory()->create(['role' => 'admin']);
        $employeeId = $this->employeeUser->employee->id;
        $this->actingAs($admin)->get(route('admin.attendances.show', ['employee' => $employeeId, 'date' => today()->toDateString()]))
            ->assertOk()->assertSee('GPS attendance locations')->assertSee('View map')
            ->assertSee('No GPS data recorded for this older event.');
    }

    public function test_qr_check_in_and_check_out_send_employee_and_gps_alerts(): void
    {
        $url = route('attendance.submit', $this->point->qr_token);
        $this->actingAs($this->employeeUser)->post($url, $this->coordinates())->assertSessionHasNoErrors();
        $this->post($url, $this->coordinates(['attendance_type' => 'out', 'work_summary' => 'Cleaned <kitchen> & packed orders']))
            ->assertSessionHasNoErrors();

        Http::assertSentCount(2);
        foreach (['ចូលធ្វើការ', 'ចេញពីធ្វើការ'] as $label) {
            Http::assertSent(fn ($request) => $request->url() === 'https://api.telegram.org/bottest-bot-token/sendMessage'
                && $request['chat_id'] === 'test-chat'
                && $request['parse_mode'] === 'HTML'
                && str_contains($request['text'], $label)
                && str_contains($request['text'], 'GPS Employee')
                && str_contains($request['text'], 'GPS-001')
                && str_contains($request['text'], 'Shop')
                && str_contains($request['text'], 'វត្តមានបុគ្គលិក')
                && str_contains($request['text'], 'ឈ្មោះ៖ GPS Employee')
                && str_contains($request['text'], 'ភាពត្រឹមត្រូវ GPS៖ ±10.0 ម៉ែត្រ')
                && str_contains($request['text'], now()->format('d/m/Y H:i'))
                && str_contains($request['text'], 'https://www.google.com/maps?q=11.594184,104.810963'));
        }
        Http::assertSent(fn ($request) => str_contains($request['text'], 'ចេញពីធ្វើការ')
            && str_contains($request['text'], 'សង្ខេបការងារ')
            && str_contains($request['text'], 'Cleaned &lt;kitchen&gt; &amp; packed orders'));
    }

    public function test_telegram_failure_does_not_undo_successful_attendance(): void
    {
        Http::fake(['api.telegram.org/*' => Http::response(['ok' => false, 'error_code' => 403], 403)]);
        $this->actingAs($this->employeeUser)->post(route('attendance.submit', $this->point->qr_token), $this->coordinates())
            ->assertRedirect(route('employee.attendance.success'));
        $this->assertDatabaseCount('attendances', 1);
        Http::assertSentCount(1);
    }

    public function test_completed_attendance_and_admin_corrections_render_consistently(): void
    {
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'), $this->coordinates());
        $this->post(route('employee.attendance.checkout'), $this->coordinates());
        $this->get(route('employee.dashboard'))->assertOk()->assertViewHas('presentDays', 1)->assertViewHas('totalAttendance', 1);
        app(\App\Services\AttendanceSummaryService::class)->upsertOverride(
            $this->employeeUser->employee, today()->toDateString(), null, null, $this->point->id, 'Absent correction',
        );
        $this->get(route('employee.dashboard'))->assertOk()->assertViewHas('presentDays', 0)->assertViewHas('totalAttendance', 0);
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard'))->assertOk()->assertViewHas('presentToday', 0)->assertViewHas('absentToday', 1);
    }

    public function test_history_and_assigned_configuration_cannot_be_deleted(): void
    {
        $this->actingAs($this->employeeUser)->post(route('employee.attendance.checkin'), $this->coordinates());
        $employee = $this->employeeUser->employee;
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->delete(route('admin.employees.destroy', $employee))->assertSessionHasErrors('employee');
        $this->delete(route('admin.shifts.destroy', $employee->shift_id))->assertSessionHasErrors('shift');
        $this->delete(route('admin.departments.destroy', $employee->department_id))->assertSessionHasErrors('department');
        $this->delete(route('admin.attendance-points.destroy', $this->point))->assertSessionHasErrors('checkpoint');
        $this->assertDatabaseCount('attendances', 1);
        $this->assertNotNull($employee->fresh());
    }

    public function test_inactive_employee_cannot_sign_in_and_malformed_admin_email_has_clear_error(): void
    {
        $this->employeeUser->employee->update(['status' => 'inactive']);
        $this->post(route('login'), ['identifier' => 'GPS Employee', 'password' => '2468'])->assertSessionHasErrors();
        $this->assertGuest();
        $this->post(route('login'), ['identifier' => 'phf@dmin001', 'password' => 'longpassword'])
            ->assertSessionHasErrors('identifier');
        $this->assertGuest();
    }

    public function test_overnight_checkout_stays_on_the_previous_workday_and_month(): void
    {
        $this->employeeUser->employee->shift->update(['start_time' => '18:00:00', 'end_time' => '06:00:00', 'late_after' => '18:30:00']);
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-10-31 18:00:00'));
        $this->actingAs($this->employeeUser)->post(route('attendance.submit', $this->point->qr_token), $this->coordinates())->assertSessionHasNoErrors();
        $this->travelTo(\Illuminate\Support\Carbon::parse('2026-11-01 06:15:00'));
        $this->get(route('attendance.show', $this->point->qr_token))->assertOk()->assertViewHas('nextAction', 'out')->assertSee('Check Out');
        $this->post(route('attendance.submit', $this->point->qr_token), $this->coordinates(['attendance_type' => 'out']))->assertSessionHasNoErrors();
        $summary = app(\App\Services\AttendanceSummaryService::class);
        $october = $summary->summarize(['month' => '2026-10'])->sole();
        $this->assertSame('2026-10-31', $october->attendance_date);
        $this->assertSame('18:00:00', $october->check_in_time);
        $this->assertSame('06:15:00', $october->check_out_time);
        $this->assertSame('present', $october->status);
        $this->assertCount(0, $summary->summarize(['month' => '2026-11']));
        $this->get(route('employee.dashboard'))->assertOk()->assertViewHas('totalAttendance', 1);
    }

    public function test_report_filter_validation_and_date_to_only_filter(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.attendances.index', ['date_to' => today()->toDateString()]))->assertOk();
        $this->getJson(route('admin.attendance.history', ['date' => 'invalid']))->assertUnprocessable();
        $this->getJson(route('admin.attendances.index', ['date_from' => '2026-11-17', 'date_to' => '2026-11-01']))->assertUnprocessable();
    }

    public function test_shift_seed_migration_preserves_assigned_shifts(): void
    {
        $shift = $this->employeeUser->employee->shift;
        $before = Shift::count();
        $migration = require database_path('migrations/2026_06_19_000002_add_shifts_asia_phnom_penh.php');
        $migration->up();
        $this->assertEquals($before, Shift::count());
        $this->assertEquals($shift->id, $this->employeeUser->employee->fresh()->shift_id);
        $this->assertSame('Day', $shift->fresh()->name);
    }

    public function test_success_page_stays_in_employee_layout_and_shows_saved_checkpoint(): void
    {
        $this->actingAs($this->employeeUser)->post(route('attendance.submit', $this->point->qr_token), $this->coordinates());
        $this->get(route('employee.attendance.success'))->assertOk()
            ->assertSee("You're checked in!")->assertSee('Shop')->assertSee('Attendance saved')
            ->assertDontSee('admin-sidebar');
    }

    public function test_single_checkpoint_is_selected_without_an_extra_selection_step(): void
    {
        $this->actingAs($this->employeeUser)->get(route('employee.attendance.checkin.form'))->assertOk()
            ->assertSee('Your checkpoint')->assertSee('Shop')->assertDontSee('id="attendance-point"', false);
    }

    public function test_unified_login_accepts_employee_name_and_pin_without_role_selection(): void
    {
        $this->post(route('login'), ['identifier' => ' GPS Employee ', 'password' => '2468'])
            ->assertRedirect(route('employee.dashboard'));
        $this->assertAuthenticatedAs($this->employeeUser);
    }

    public function test_unified_login_accepts_admin_email_and_password_without_role_selection(): void
    {
        $admin = User::factory()->create(['role' => 'admin', 'password' => Hash::make('AdminTestPassword!')]);
        $this->post(route('login'), ['identifier' => $admin->email, 'password' => 'AdminTestPassword!'])
            ->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_unified_login_rejects_wrong_pin_and_has_no_role_tabs(): void
    {
        $this->get(route('login'))->assertOk()->assertSee('Full name or email')->assertDontSee('login-mode-toggle');
        $this->post(route('login'), ['identifier' => 'GPS Employee', 'password' => '0000'])
            ->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_employee_disabled_after_login_cannot_access_employee_pages(): void
    {
        $this->actingAs($this->employeeUser);
        $this->employeeUser->employee->update(['status' => 'inactive']);
        foreach (['employee.dashboard', 'employee.attendance.history', 'employee.profile', 'attendance.show.qr'] as $route) {
            $this->get(route($route))->assertForbidden();
        }
    }

    public function test_ambiguous_employee_name_and_pin_never_selects_an_arbitrary_account(): void
    {
        $other = User::factory()->create(['role' => 'employee']);
        Employee::create([
            'employee_code' => 'GPS-002', 'full_name' => 'GPS Employee', 'pin' => Hash::make('2468'),
            'department_id' => $this->employeeUser->employee->department_id,
            'shift_id' => $this->employeeUser->employee->shift_id, 'user_id' => $other->id, 'status' => 'active',
        ]);
        $this->post(route('login'), ['identifier' => 'GPS Employee', 'password' => '2468'])->assertSessionHasErrors();
        $this->assertGuest();
    }

    public function test_admin_login_ignores_employee_qr_intended_destination(): void
    {
        $this->get(route('attendance.show', $this->point->qr_token))->assertRedirect(route('login'));
        $admin = User::factory()->create(['role' => 'admin', 'password' => Hash::make('AdminTestPassword!')]);
        $this->post(route('login'), ['identifier' => $admin->email, 'password' => 'AdminTestPassword!'])
            ->assertRedirect(route('admin.dashboard'));
    }

    public function test_public_file_route_only_serves_expected_images_with_nosniff(): void
    {
        \Illuminate\Support\Facades\Storage::fake('public');
        \Illuminate\Support\Facades\Storage::disk('public')->put('employees/test.jpg', 'image');
        \Illuminate\Support\Facades\Storage::disk('public')->put('private.txt', 'secret');
        $this->get('/files/employees/test.jpg')->assertOk()->assertHeader('X-Content-Type-Options', 'nosniff');
        $this->get('/files/private.txt')->assertNotFound();
        $this->get('/files/employees/not-an-image.php')->assertNotFound();
    }

    public function test_admin_dashboard_handles_array_date_and_shift_times_are_validated(): void
    {
        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin)->get(route('admin.dashboard', ['date' => ['invalid']]))->assertOk();
        $this->post(route('admin.shifts.store'), [
            'name' => 'Invalid shift', 'start_time' => 'not-time', 'end_time' => '25:70', 'late_after' => ['invalid'],
        ])->assertSessionHasErrors(['start_time', 'end_time', 'late_after']);
        foreach (['08:00', '08:00:00'] as $start) {
            $this->post(route('admin.shifts.store'), [
                'name' => 'Valid shift', 'start_time' => $start, 'end_time' => '17:00', 'late_after' => '08:15',
            ])->assertSessionHasNoErrors();
        }
    }
}
