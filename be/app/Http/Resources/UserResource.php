<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'email' => $this->email,
            'phone' => $this->phone,
            'role' => $this->role,
            'is_teacher' => $this->is_teacher,
            'is_active' => $this->is_active,
            'email_verified_at' => $this->email_verified_at?->toISOString(),
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Computed fields
            'role_display' => $this->when(true, function () {
                if ($this->role === 'admin') return 'admin';
                if ($this->is_teacher) return 'teacher';
                return 'user';
            }),
            
            // Admin-only fields
            'deleted_at' => $this->when(
                $request->user() && $request->user()->isAdmin(),
                $this->deleted_at?->toISOString()
            ),
            
            // Statistics (when loaded)
            'tests_created_count' => $this->whenCounted('createdTests'),
            'attempts_count' => $this->whenCounted('testAttempts'),
            
            // Relationships
            'created_tests' => TestResource::collection($this->whenLoaded('createdTests')),
            'test_attempts' => AttemptResource::collection($this->whenLoaded('testAttempts')),
        ];
    }
}