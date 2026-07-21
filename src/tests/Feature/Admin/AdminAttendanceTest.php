<?php
namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminAttendanceTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function その日になされた全ユーザーの勤怠情報が正確に確認できる()
    {
        $user1 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $user2 = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        AttendanceRecord::create([
            'user_id' => $user1->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clocl_out' => Carbon::today()->setTIme(18, 0),
        ]);

        AttendanceRecord::create([
            'user_id' => $user2->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(10, 0),
            'clock_out'=> Carbon::today()->setTime(19, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);

        $response->assertSee($user1->name);
        $response->assertSee($user2->name);
    }

    /** @test */
    public function 遷移した際に現在の日付が表示される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.list'));

        $response->assertStatus(200);
        $response->assertSee(now()->format('Y年m月d日'));
    }

    /** @test */
    public function 「前日」を押下した時に前の日の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->subDay(),
            'clock_in' => Carbon::today()->subDay()->setTime(9, 0),
            'clock_out'=> Carbon::today()->subDay()->setTime(18, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.list', [
            'day' => Carbon::today()->subDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);
        $response->assertSee(Carbon::today()->subDay()->format('Y年m月d日'));
        $response->assertSee($user->name);
    }

    /** @test */
    public function 「翌日」を押下した時に次の日の勤怠情報が表示される()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today()->addDay(),
            'clock_in' => Carbon::today()->addDay()->setTime(9, 0),
            'clock_out' => Carbon::today()->addDay()->setTime(18, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.attendance.list', [
            'day' => Carbon::today()->addDay()->format('Y-m-d'),
        ]));

        $response->assertStatus(200);

        $response->assertSee(Carbon::today()->addDay()->format('Y年m月d日'));
        $response->assertSee($user->name);

    }

    /** @test */
    public function 勤怠詳細画面に表示されるデータが選択したものになっている()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => today(),
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
        $response->assertSee('09:00');
        $response->assertSee('18:00');
        $response->assertSee('12:00');
        $response->assertSee('13:00');
    }

    /** @test */
    public function 出勤時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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
            'clock_out'=> Carbon::today()->setTime(18, 0),
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->from(route('admin.attendance.detail', $attendance))
            ->put(route('admin.attendance.update',$attendance),  [
                'clock_in' => '19:00',
                'clock_out' => '18:00',
                'note' => 'テスト'
            ]);

        $response->assertRedirect(route('admin.attendance.detail', $attendance));

        $response->assertSessionHasErrors([
            'clock_out' => '出勤時間もしくは退勤時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩開始時間が退勤時間より後になっている場合、エラーメッセージが表示される()
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

        $response = $this->from(route('admin.attendance.detail', $attendance))
            ->put(route('admin.attendance.update', $attendance), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'break_start' => '19:00',
                        'break_end' => '13:00',
                    ],
                ],

                'note' => 'テスト',
            ]);

        $response->assertRedirect(route('admin.attendance.detail', $attendance));

        $response->assertSessionHasErrors([
            'breaks.0.break_start' => '休憩時間が不適切な値です',
        ]);
    }

    /** @test */
    public function 休憩終了時間が退勤時間より後になっている場合、エラーメッセージが表示される()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clocl_in' => Carbon::today()->setTime(9, 0),
            'clock_out' => Carbon::today()->setTime(18, 0),
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->from(route('admin.attendance.detail', $attendance))
            ->put(route('admin.attendance.update' , $attendance), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'break_start' => '12:00',
                        'break_end' => '19:00',
                    ],
                ],
                'note' => 'テスト',
            ]);

        $response-> assertRedirect(route('admin.attendance.detail', $attendance));

        $response -> assertSessionHasErrors([
            'breaks.0.break_end' => '休憩時間もしくは退勤時間が不適切な値です'
        ]);
    }

    /** @test */
    public function 備考欄が未入力の場合エラーメッセージが表示される()
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
            'clock_out'=> Carbon::today()->setTime(18, 0),
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => Carbon::today()->setTime(12, 0),
            'break_end' => Carbon::today()->setTime(13, 0),
        ]);

        $this->actingAs($admin);

        $response = $this->from(route('admin.attendance.detail', $attendance))
            ->put(route('admin.attendance.update', $attendance), [
                'clock_in' => '09:00',
                'clock_out' => '18:00',
                'breaks' => [
                    [
                        'break_start' => '13:00',
                        'break_end' => '14:00',
                    ],
                ],
                'note' => '',
            ]);

        $response->assertRedirect(route('admin.attendance.detail', $attendance));

        $response->assertSessionHasErrors([
            'note' =>'備考を記入してください'
        ]);
    }
}