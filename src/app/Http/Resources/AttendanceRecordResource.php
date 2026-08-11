<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Carbon\Carbon;

class AttendanceRecordResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $workMinutes = $this->getActualWorkMinutes();
        $breakMinutes = $this->getBreakMinutes();

        $totalTime = sprintf(
            '%02d:%02d',
            floor($workMinutes / 60),
            $workMinutes %60
        );

        $totalBreakTime = sprintf(
            '%02d:%02d',
            floor($breakMinutes / 60),
            $breakMinutes % 60
        );

        return [
            'id' => $this->id,
            'user_id' => $this->user_id,
            'user_name' => optional($this->user)->name,
            'user' => new UserResource($this->whenLoaded('user')),
            'date' => Carbon::parse($this->work_date)->format('Y-m-d'),
            'comment' => $this->note,
            'clock_in' => $this->clock_in
                ? Carbon::parse($this->clock_in)->format('H:i:s'):null,
            'clock_out' => $this->clock_out
                ? Carbon::parse($this->clock_out)->format('H:i:s'):null,
            'breaks' => AttendanceBreakResource::collection(
                $this->whenLoaded('breakTimes')
            ),
            'total_time' => $totalTime,
            'total_break_time' => $totalBreakTime,
            'applications' => ApplicationResource::collection(
                $this->whenLoaded('attendanceCorrectionRequests')
            ),
        ];
    }
}
