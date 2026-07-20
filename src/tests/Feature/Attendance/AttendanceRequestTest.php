<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AttendanceRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user =User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7, 20),
            'clock_in' => Carbon::create(2026, 7, 20, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 20, 14, 0),
        ]);

        $this->actingAs($user);

        $response = $this->followingRedirects()
            ->from(route('attendance.show', $attendance))
            ->post(route('attendance.request', $attendance),[
                'requested_clock_in' => '15:00',
                'requested_clock_out' => '14:00',
            'reason' => 'テスト',
            ]);

        $response->assertSee('出勤時間もしくは退勤時間が不適切な値です');
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7, 20),
            'clock_in' => Carbon::create(2026, 7, 20, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 20, 18, 0),
        ]);


        $this->actingAs($user);

        $response = $this->followingRedirects()
            ->from(route('attendance.show', $attendance))
            ->post(route('attendance.request', $attendance), [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',

                'breaks' => [
                    [
                        'break_start' => '19:00',
                        'break_end' => '20:00',
                    ],

                ],

                'reason' => 'テスト',
            ]);

            $response->assertSee('休憩時間が不適切な値です');
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026,7,20),
            'clock_in' => Carbon::create(2026, 7, 20, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 20, 18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->followingRedirects()
            ->from(route('attendance.show', $attendance))
            ->post(route('attendance.request', $attendance), [
                'requested_clock_in' => '09:00',
                'requested_clock_out' => '18:00',

                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '20:00',
                    ],
                ],

                'reason' => 'テスト',
            ]);

            $response->assertSee('休憩時間もしくは退勤時間が不適切な値です');
    }

}