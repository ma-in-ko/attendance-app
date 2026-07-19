<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\AttendanceRecord;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ClockInTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤ボタンが正しく機能する()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->post(route('attendance.clock-in'));

        $response->assertRedirect(route('attendance.create'));

        $this->assertDatabaseHas('attendance_records', [
            'user_id' => $user->id,
            'work_date' =>today(),
        ]);

        $response = $this->get(route('attendance.create'));

        $response->assertSee('出勤中');

    }

    /** @test */
    public function 出勤は一日一回のみできる()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => now()->subHours(8),
            'clock_out' => now(),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.create'));

        $response->assertDontSee('出勤');
    }

    public function 出勤時刻が勤怠一覧画面で確認できる()
    {
        $user = User::factory()->create();


        $this->actingAs($user);

        $this->post(route('attendance.clock-in'));

        $attendance = AttendanceRecord::where('user_id', $user->id)
            ->whereDate('work_date', today())
            ->first();

        $response = $this->get(route('attendance.index'));

        $response->assertSee(
            \Carbon\Carbon::parse($attendance->clock_in)->format('H:i')
        );
    }

}