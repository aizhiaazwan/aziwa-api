<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'university' => $this->university,
            'program' => $this->program,
            'semester' => $this->semester,
            'entry_year' => $this->entry_year,
            'avatar' => $this->avatar,
        ];
    }
}