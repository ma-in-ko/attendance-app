<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockOutTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 退勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));
        $response->assertSee('退勤');

        $this->post(route('attendance.clock-out'));

        $response = $this->get(route('attendance.create'));

        $response->assertSee('退勤済');
        $response->assertSee('お疲れ様でした');
    }

    /** @test */
    public function 退勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $this->post(route('attendance.clock-in'));

        $this->post(route('attendance.clock-out'));

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $response = $this->get(route('attendance.index'));

        $response->assertSee(
            \Carbon\Carbon::parse($attendance->clock_out)->format('H:i')
        );
    }
}
