<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use App\Models\AttendanceRecord;
use App\Models\AttendanceCorrectionBreak;

class AttendanceCorrectionRequest extends Model
{
    use HasFactory;

    protected $fillable =[
        'attendance_record_id',
        'requested_clock_in',
        'requested_clock_out',
        'reason',
        'is_approved',
        'approved_at',
    ];

    public function attendanceRecord()
    {
    return $this->belongsTo(AttendanceRecord::class);
    }

    public function attendanceCorrectionBreaks()
    {
        return $this->hasMany(AttendanceCorrectionBreak::class);
    }

}
