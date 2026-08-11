<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceShowTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 勤怠詳細画面の「名前」がログインユーザーのに氏名なっている()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7, 20),
            'clock_in' => Carbon::create(2026, 7, 20, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 20, 18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertSee($user->name);
    }

    /** @test */
    public function 勤怠詳細画面の「日付」が選択した日付になっている()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7, 20),
            'clock_in' => Carbon::create(2026, 7, 20, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 20, 18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertSee('2026年');
        $response->assertSee('7月20日');
    }

    /** @test */
    public function 「出勤・退勤」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7, 20),
            'clock_in' => Carbon::create(2026, 7, 20, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 20, 18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertSee('09:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「休憩」にて記されている時間がログインユーザーの打刻と一致している()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7, 20),
            'clock_in' => Carbon::create(2026, 7, 20, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 20, 18, 0),
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => Carbon::create(2026, 7, 20, 12, 0),
            'break_end' => Carbon::create(2026, 7, 20, 13, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.show', $attendance));

        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }
}
