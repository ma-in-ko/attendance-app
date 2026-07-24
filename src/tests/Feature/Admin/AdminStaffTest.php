<?php

namespace Tests\Feature\Admin;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminStaffTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 管理者ユーザーが全一般ユーザーの「氏名」「メールアドレス」を確認できる()
    {
        $user1 = User::factory()->create();

        $user2 = User::factory()->create();

        $user3 = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff.list'));

        $response->assertStatus(200);

        $response->assertSee([
            $user1->name,
            $user2->name,
            $user3->name,
            $user1->email,
            $user2->email,
            $user3->email,
        ]);
    }

    /** @test */
    public function ユーザーの勤怠情報が正しく表示される()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff.attendance', $user));

        $response->assertStatus(200);
        $response->assertSee($user->name);
        $response->assertSee([
            Carbon::today()->format('m/d'),
            '09:00',
            '18:00',
            '01:00',
            '8:00',
            '詳細',
        ]);
    }

    /** @test */
    public function 「前月」を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subMonth(),
            'clock_in' => Carbon::today()->subMonth()->setTime(9, 0),
            'clock_out' => Carbon::today()->subMonth()->setTime(18, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff.attendance', [
            'user' => $user,
            'month' => Carbon::today()->subMonth()->format('Y-m'),
        ]));

        $response->assertStatus(200);

        $response->assertSee(
            Carbon::today()->subMonth()->format('Y/m')
        );

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「翌月」を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->addMonth(),
            'clock_in' => Carbon::today()->addMonth()->setTime(9, 0),
            'clock_out' => Carbon::today()->addMonth()->setTime(18, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.staff.attendance', [
            'user' => $user->id,
            'month' => Carbon::today()->addMonth()->format('Y-m'),
        ]));

        $response->assertStatus(200);

        $response->assertSee(Carbon::today()->addMonth()->format('Y/m'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.detail', $attendance));

        $response->assertStatus(200);

        $response->assertSee($user->name);

        $response->assertSee(Carbon::today()->format('Y年'));
        $response->assertSee(Carbon::today()->format('n月j日'));

        $response->assertSee([
            '09:00',
            '18:00',
            '12:00',
            '13:00',
        ]);
    }
}
