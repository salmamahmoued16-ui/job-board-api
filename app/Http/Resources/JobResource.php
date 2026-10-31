<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class JobResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'location' => $this->location,
            'type' => $this->type,
            'salary' => $this->salary,
            'is_active' => $this->is_active,
            'company' => [
                'id' => $this->company->id,
                'name' => $this->company->name,
            ],
            'created_at' => $this->created_at,
        ];
    }
}
