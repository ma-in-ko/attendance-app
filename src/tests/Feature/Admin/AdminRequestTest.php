<?php

namespace Tests\Feature\Admin;

use App\Models\User;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectionRequest;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use Carbon\Carbon;

class AdminRequestTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function 承認待ちの修正申請がすべて表示されている()
    {
        $user = User::factory()->create();

        $admin = User::factory()->create([
            'admin_status' => true,
            'email_verified_at' => now(),
        ]);

        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => Carbon::today(),
            'clock_in' => Carbon::today()->setTIme(9, 0),
            'clock_out' => Carbon::today()->setTIme(18, 0),
        ]);

        AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'requested_clock_in' => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(10, 0),
            'reason' => 'テスト',
            'is_approved' => false,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('request.index', [
            'tab' => 'pending',
        ]));

        $response->assertStatus(200);

        $response->assertSee([
            '承認待ち',
            $user->name,
            'テスト'
            ]);
    }

    /** @test */
    public function 承認済の修正申請がすべて表示されている()
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

        AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'requested_clock_in' => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(10, 0),
            'reason' => 'テスト',
            'is_approved' => true,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('request.index', [
            'tab' => 'approved',
            ],
        ));

        $response->assertStatus(200);

        $response->assertSee([
            '承認済み',
            $user->name,
            'テスト',
        ]);
    }

    /** @test */
    public function 修正申請の詳細内容が正しく表示されている()
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

        $request = AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'requested_clock_in' => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(10, 0),
            'reason' => 'テスト',
            'is_approved' => false,
        ]);

        $this->actingAs($admin);

        $response = $this->get(route('admin.request.show', $request->id));

        $response->assertStatus(200);

        $response->assertSee([
                $user->name,
                '09:00',
                '10:00',
                'テスト',
            ]);
    }

    /** @test */
    public function 修正申請の承認処理が正しく行われる()
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

        $request = AttendanceCorrectionRequest::create([
            'attendance_record_id' => $attendance->id,
            'requested_clock_in' => Carbon::today()->setTime(9, 0),
            'requested_clock_out' => Carbon::today()->setTime(10, 0),
            'reason' => 'テスト',
            'is_approved' => false,
        ]);

        $this->actingAs($admin);

        $response = $this->post(route('admin.request.approve', $request->id));

        $request->refresh();
        $attendance->refresh();

        $this->assertEquals(1, $request->is_approved);
        $this->assertNotNull($request->approved_at);

        $this->assertEquals(
            Carbon::today()->setTime(9, 0),
            $attendance->clock_in
        );

        $this->assertEquals(
            Carbon::today()->setTime(10, 0),
            $attendance->clock_out
        );
    }
}