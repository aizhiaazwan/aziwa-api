<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TaskResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'course_id' => $this->course_id,
            'title' => $this->title,
            'description' => $this->description,
            'deadline' => $this->deadline->toIso8601String(), // 2026-09-30T23:59:00+07:00
            'priority' => $this->priority,
            'status' => $this->status,
            'subtasks_done' => $this->subtasks_done,
            'subtasks_total' => $this->subtasks_total,
            'completed_at' => $this->completed_at?->toIso8601String(),
            'created_at' => $this->created_at?->toIso8601String(),
            'updated_at' => $this->updated_at?->toIso8601String(),
        ];
    }
}