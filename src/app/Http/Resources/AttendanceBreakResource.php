<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttendanceBreakResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'break_in' => $this->break_start
            ? Carbon::parse($this->break_start)->format('H:i:s')
            : null,
            'break_out' => $this->break_end
                ? Carbon::parse($this->break_end)->format('H:i:s')
                : null,
        ];
    }
}
