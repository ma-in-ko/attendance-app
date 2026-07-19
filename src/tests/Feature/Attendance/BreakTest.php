<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class BreakTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 休憩ボタンが正しく機能する ()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertSee('休憩入');

        $this->post(route('attendance.break-start'));

        $response = $this->get(route('attendance.create'));

        $response->assertSee('休憩中');
    }

    /** @test */
    public function 休憩は一日に何回でもできる()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.break-start'));

        $this->post(route('attendance.break-end'));

        $response = $this->get(route('attendance.create'));

        $response->assertSee('休憩入');
    }

    /** @test */
    public function 休憩戻ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.break-start'));

        $response = $this->get(route('attendance.create'));

        $response->assertSee('休憩戻');

        $this->post(route('attendance.break-end'));

        $response = $this->get(route('attendance.create'));

        $response->assertSee('出勤中');

    }

    /** @test */
    public function 休憩戻は一日に何回でもできる()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.break-start'));

        $this->post(route('attendance.break-end'));

        $this->post(route('attendance.break-start'));

        $response = $this->get(route('attendance.create'));

        $response->assertSee('休憩戻');
    }

    /** @test */
    public function 休憩時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now(),
        ]);

        $this->actingAs($user);

        $this->post(route('attendance.break-start'));

        $this->post(route('attendance.break-end'));

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $break = BreakTime::where('attendance_record_id', $attendance->id)
            ->first();

        $response = $this->get(route('attendance.index'));

        $response->assertSee(
            \Carbon\Carbon::parse($break->break_start)->format('H:i')
        );

        $response->assertSee(
            \Carbon\Carbon::parse($break->break_end)->format('H:i')
        );
    }

}