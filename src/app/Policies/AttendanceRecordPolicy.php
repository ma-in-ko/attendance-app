<?php

namespace App\Policies;

use App\Models\AttendanceRecord;
use App\Models\User;

class AttendanceRecordPolicy
{
    /**
     * 管理者はすべての操作を許可する
     */
    public function before(User $user): ?bool
    {
        if($user->admin_status) {
            return true;
        }
        return null;
    }

    /**
     * 勤怠を更新できるか
     */
    public function update(User $user, AttendanceRecord $attendanceRecord): bool {
        return $user->id === $attendanceRecord->user_id;
    }

    /**
     * 勤怠を削除できるか
     */
    public function delete(User $user, AttendanceRecord $attendanceRecord): bool {
        return $user->id === $attendanceRecord->user_id;
    }
}
