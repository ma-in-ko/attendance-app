<?php

namespace Tests\Feature\Attendance;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\AttendanceCorrectionRequest;
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

    /** @test */
    public function 備考欄が未入力の場合、エラーメッセージが表示される()
        {
            $user = User::factory()->create();

            $attendance = AttendanceRecord::create([
                'user_id' => $user->id,
                'work_date' => Carbon::create(2026, 7, 21),
                'clock_in' => Carbon::create(2026, 7, 21, 9, 0),
                'clock_out' => Carbon::create(2026, 7, 21, 18),
            ]);

            $this->actingAs($user);

            $response = $this->followingRedirects()
                ->from(route('attendance.show', $attendance))
                ->post(route('attendance.request', $attendance), [
                    'requested_clock_in' => '09:00',
                    'requested_clock_out' => '17:00',
                    'reason' => '',
                ]);

            $response->assertSee('備考を記入してください');
        }

    /** @test */
    public function 修正申請処理が実行される()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $adminUser = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7, 21),
            'clock_in' => Carbon::create(2026, 7, 21, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 21, 18, 0),
        ]);

        $this->actingAs($user);

        $response = $this
            ->from(route('attendance.show', $attendance))
            ->post(route('attendance.request', $attendance), [
                'requested_clock_in' => '09:30',
                'requested_clock_out' =>'18:00',
                'reason' => '電車遅延のため',
            ]);

        $this->assertDatabaseHas('attendance_correction_requests', [
            'attendance_record_id' => $attendance->id,
            'reason' => '電車遅延のため',
        ]);

        $this->actingAs($adminUser);

        $response = $this->get(route('request.index'));
        $response->assertSee('電車遅延のため');

    }

    /** @test */
    public function 「承認待ち」にログインユーザーが行った申請がすべて表示されていること()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date'=> Carbon::create(2026, 7, 21),
            'clock_in' => Carbon::create(2026, 7, 21, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 21, 18, 0),
        ]);

        $this->actingAs($user);

        $response = $this
            ->from(route('attendance.show', $attendance))
            ->post(route('attendance.request', $attendance), [
                'requested_clock_in' => '09:30',
                'requested_clock_out' =>'18:00',
                'reason' => '電車遅延のため'
            ]);

        $response = $this->get(route('request.index'));

        $response->assertSee('電車遅延のため');
        $response->assertSee('承認待ち');
    }

    /** @test */
    public function 「承認済み」に管理者が承認した修正申請がすべて表示されている()
    {
        $user = User::factory()->create([
            'email_verified_at' => now(),
        ]);

        $adminUser = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::create(2026, 7,  21),
            'clock_in' => Carbon::create(2026, 7, 21, 9, 0),
            'clock_out' => Carbon::create(2026, 7, 21, 18, 0),
        ]);

        $this->actingAs($user);

        $response = $this
            ->from(route('attendance.show', $attendance))
            ->post(route('attendance.request', $attendance), [
                'requested_clock_in' => '09:30',
                'requested_clock_out' => '18:00',
                'reason' => '電車遅延のため',
            ]);

        $correctionRequest = AttendanceCorrectionRequest::where(
            'attendance_record_id',
            $attendance->id,
        )->first();

        $this->actingAs($adminUser);

        $this->post(route('admin.request.approve', $correctionRequest->id));

        $this->assertDatabaseHas('attendance_correction_requests', [
            'id' => $correctionRequest->id,
            'is_approved' => true,
        ]);

        $this->actingAs($user);

        $response = $this->get(route('request.index', [
            'status' => 'approved',
        ]));

        $response->assertStatus(200);

        $response->assertSee('電車遅延のため');
        $response->assertSee('承認済み');
    }

    /** @test */
    public function 各申請の「詳細」を押下すると勤怠詳細画面に遷移する()
        {
            $user = User::factory()->create([
                'email_verified_at' => now(),
            ]);

            $attendance = AttendanceRecord::create([
                'user_id' => $user->id,
                'work_date' => Carbon::create(2026, 7, 21),
                'clock_in' => Carbon::create(2026, 7, 21, 9, 0),
                'clock_out'=> Carbon::create(2026, 7, 21, 18, 0),
            ]);

            $this->actingAs($user);

            $this->post(route('attendance.request', $attendance), [
                    'requested_clock_in' => '09:30',
                    'requested_clock_out' => '18:00',
                    'reason' => '電車遅延のため',
                ]);

            $response = $this->get(route('attendance.show', $attendance));

            $response->assertStatus(200);
            $response->assertSee($user->name);
        }
}
