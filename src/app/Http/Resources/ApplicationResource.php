<?php

namespace App\Http\Resources;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ApplicationResource extends JsonResource
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
            'reason' => $this->reason,
            'is_approved' => $this->is_approved,
            'approved_at' => $this->approved_at
                ? Carbon::parse($this->approved_at)->format('Y-m-d H:i:s')
                : null,
        ];
    }
}
