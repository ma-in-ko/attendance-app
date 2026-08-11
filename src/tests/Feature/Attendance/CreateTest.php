<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CreateTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 現在の日時情報が_u_iと同じ形式で出力されている()
    {
        Carbon::setTestNow(Carbon::parse('2026-07-20 09:30:00'));

        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertStatus(200);
        $response->assertSee('2026年7月20日');
        $response->assertSee('09:30');

    }

    /** @test */
    public function 勤務外の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertSee('勤務外');

    }

    public function 出勤中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertSee('出勤中');
    }

    /** @test */
    public function 休憩中の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
            'clock_out' => null,
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => now(),
            'break_end' => null,
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 退勤済の場合、勤怠ステータスが正しく表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertSee('退勤済');
    }
}
