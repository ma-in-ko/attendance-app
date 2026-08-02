<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class AttendanceRecord extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'work_date',
        'clock_in',
        'clock_out',
        'note',
    ];

    protected $casts = [
        'work_date' => 'date',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function breakTimes()
    {
        return $this->hasMany(BreakTime::class);
    }

    public function attendanceCorrectionRequests()
    {
        return $this->hasMany(AttendanceCorrectionRequest::class);
    }

    /**
     * 休憩時間（分）を取得
     */
    public function getBreakMinutes()
    {
        return $this->breakTimes->sum(function ($break) {

            if (!$break->break_start || !$break->break_end) {
                return 0;
            }

            $breakStart = Carbon::parse($break->break_start);
            $breakEnd = Carbon::parse($break->break_end);

            return $breakStart->diffInMinutes($breakEnd);
        });
    }

    /**
     * 実働時間（分）を取得
     */
    public function getActualWorkMinutes()
    {
        if (!$this->clock_in || !$this->clock_out) {
            return 0;
        }

        $clockIn = Carbon::parse($this->clock_in);
        $clockOut = Carbon::parse($this->clock_out);

        $workMinutes = $clockIn->diffInMinutes($clockOut);

        return $workMinutes - $this->getBreakMinutes();
    }
}
