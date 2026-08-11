<?php

namespace Tests\Feature\Attendance;

use App\Models\AttendanceRecord;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AttendanceIndexTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 自分が行った勤怠情報がすべて表示されている()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->startOfMonth()->addDay(2),
            'clock_in' => Carbon::today()->startOfMonth()->addDay(2)->setTime(10, 0),
            'clock_out' => Carbon::today()->startOfMonth()->addDay(2)->setTime(19, 0),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->startOfMonth()->addDay(),
            'clock_in' => Carbon::today()->startOfMonth()->addDay()->setTime(8, 30),
            'clock_out' => Carbon::today()->startOfMonth()->addDay()->setTime(17, 30),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->startOfMonth(),
            'clock_in' => Carbon::today()->startOfMonth()->setTime(9, 0),
            'clock_out' => Carbon::today()->startOfMonth()->setTime(18, 0),
        ]);

        $otheruser = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $otheruser->id,
            'work_date' => Carbon::today()->startOfMonth(),
            'clock_in' => Carbon::today()->startOfMonth()->setTime(9, 30),
            'clock_out' => Carbon::today()->startOfMonth()->setTime(16, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));

        $response->assertSee('09:00');
        $response->assertSee('18:00');

        $response->assertSee('10:00');
        $response->assertSee('19:00');

        $response->assertSee('08:30');
        $response->assertSee('17:30');

        $response->assertDontSee('09:30');
        $response->assertDontSee('16:00');
    }

    /** @test */
    public function 勤怠一覧画面に遷移した際に現在の月が表示される()
    {
        $user = User::factory()->create();

        $this->actingAs($user);

        $response = $this->get(route('attendance.index'));

        $response->assertSee(
            Carbon::now()->format('Y/m')
        );
    }

    /** @test */
    public function 前月を押下した時に表示月の前月の情報が表示される()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => Carbon::today()->setTime(9, 15),
            'clock_out' => Carbon::today()->setTime(18, 45),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today()->startOfMonth()->subMonth(),
            'clock_in' => Carbon::today()->startOfMonth()->subMonth()->setTime(10, 0),
            'clock_out' => Carbon::today()->startOfMonth()->subMonth()->setTime(19, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index', [
            'month' => Carbon::now()->subMonth()->format('Y-m'),
        ]));

        $response->assertSee(
            Carbon::now()->copy()->subMonth()->format('Y/m')
        );

        $response->assertSee('10:00');
        $response->assertSee('19:00');

    }

    /** @test */
    public function 翌月を押下した時に表示月の翌月の情報が表示される()
    {
        $user = User::factory()->create();

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
            'clock_in' => Carbon::today()->setTime(9, 15),
            'clock_out' => Carbon::today()->setTime(18, 45),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today()->startOfMonth()->addMonth(),
            'clock_in' => Carbon::today()->startOfMonth()->addMonth()->setTime(10, 0),
            'clock_out' => Carbon::today()->startOfMonth()->addMonth()->setTime(18, 0),
        ]);

        $this->actingAs($user);

        $response = $this->get(route('attendance.index', [
            'month' => Carbon::now()->addMonth()->format('Y-m'),
        ]));

        $response->assertSee(
            Carbon::now()->copy()->addMonth()->format('Y/m')
        );

        $response->assertSee('10:00');
        $response->assertSee('18:00');
    }

    /** @test */
    public function 「詳細」を押下すると、その日の勤怠詳細画面に遷移する()
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

        $response->assertSee('勤怠詳細');

        $response->assertSee('2026年');
        $response->assertSee('7月20日');

        $response->assertSee('09:00');
        $response->assertSee('18:00');

    }
}
