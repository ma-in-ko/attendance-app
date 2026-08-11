<?php

namespace Database\Seeders;

use App\Models\AttendanceRecord;
use App\Models\BreakTime;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Database\Seeder;

class AttendanceSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $user1 = User::where('email', 'user1@example.com')->first();
        $user2 = User::where('email', 'user2@example.com')->first();

        $user3 = User::where('email', 'user3@example.com')->first();

        if (! $user1 || ! $user2 || ! $user3) {
            return;
        }

        $this->createPastFiveMonths($user1);
        $this->createCurrentMonth($user1);

        $this->createNormalAttendance($user2);
        $this->createNormalAttendance($user3);

    }

    private function createAttendance(User $user, Carbon $date, string $clockIn, string $clockOut): void
    {
        $attendance = AttendanceRecord::create([
            'user_id' => $user->id,
            'work_date' => $date->toDateString(),
            'clock_in' => Carbon::parse($date->format('Y-m-d').' '.$clockIn),
            'clock_out' => Carbon::parse($date->format('Y-m-d').' '.$clockOut),
            'note' => null,
        ]);

        BreakTime::create([
            'attendance_record_id' => $attendance->id,
            'break_start' => Carbon::parse($date->format('Y-m-d').' 12:00'),
            'break_end' => Carbon::parse($date->format('Y-m-d').' 13:00'),
        ]);
    }

    private function createPastFiveMonths(User $user): void
    {
        for ($month = 5; $month >= 1; $month--) {

            $targetMonth = now()->subMonths($month);

            // その月の平日を取得
            $workDays = collect();

            $date = $targetMonth->copy()->startOfMonth();

            while ($date->month === $targetMonth->month) {

                if (! $date->isWeekend()) {
                    $workDays->push($date->copy());
                }

                $date->addDay();
            }

            // 平日数
            $count = $workDays->count();

            // 等間隔で15日選択
            $selectedDays = collect();

            for ($i = 0; $i < 15; $i++) {
                $index = (int) floor($i * ($count - 1) / 14);
                $selectedDays->push($workDays[$index]);
            }

            foreach ($selectedDays as $day) {

                $this->createAttendance(
                    $user,
                    $day,
                    '09:00',
                    '18:00'
                );
            }
        }
    }

    private function createCurrentMonth(User $user): void
    {
        $date = now()->startOfMonth();

        $patterns = [
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],
            ['09:00', '18:00'],

            ['09:00', '20:00'],
            ['09:00', '20:00'],
            ['09:00', '20:00'],

            ['09:30', '18:00'],
            ['09:30', '18:00'],

            ['09:00', '17:00'],

            ['08:00', '21:00'],
        ];

        foreach ($patterns as $pattern) {

            while ($date->isWeekend()) {
                $date->addDay();
            }

            $this->createAttendance(
                $user,
                $date->copy(),
                $pattern[0],
                $pattern[1]
            );

            $date->addDay();
        }
    }

    private function createNormalAttendance(User $user): void
    {
        $this->createPastFiveMonths($user);

        $date = now()->startOfMonth();
        $count = 0;

        while ($count < 17) {

            if (! $date->isWeekend()) {
                $this->createAttendance(
                    $user,
                    $date->copy(),
                    '09:00',
                    '18:00'
                );

                $count++;
            }

            $date->addDay();
        }
    }
}
