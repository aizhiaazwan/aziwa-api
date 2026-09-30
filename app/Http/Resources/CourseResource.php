<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CourseResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'code' => $this->code,
            'sks' => $this->sks,
            'lecturer' => $this->lecturer,
            'day' => $this->day,
            'start_time' => substr($this->start_time, 0, 5), // 08:00
            'end_time' => substr($this->end_time, 0, 5),
            'room' => $this->room,
            'semester' => $this->semester,
            'description' => $this->description,
            'icon' => $this->icon,
            'tone' => $this->tone,
            'stripe' => $this->stripe,
            'tasks_count' => $this->when(isset($this->tasks_count), fn () => $this->tasks_count),
            'active_tasks_count' => $this->when(isset($this->active_tasks_count), fn () => $this->active_tasks_count),
        ];
    }
}