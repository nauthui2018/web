<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CategoryResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_active' => $this->is_active,
            'created_at' => $this->created_at ? $this->created_at->toISOString() : null,
            'updated_at' => $this->updated_at ? $this->updated_at->toISOString() : null,

            // Relationships
            'creator' => new UserResource($this->whenLoaded('creator')),
            'tests' => TestResource::collection($this->whenLoaded('tests')),

            // Counts
            'tests_count' => $this->whenCounted('tests'),
            'active_tests_count' => $this->when(
                $this->relationLoaded('tests'),
                fn() => $this->tests->where('is_active', true)->count()
            ),

            // Admin-only fields
            'created_by' => $this->when(
                $request->user() && $request->user()->isAdmin(),
                $this->created_by
            ),
            'deleted_at' => $this->when(
                $request->user() && $request->user()->isAdmin(),
                $this->deleted_at?->toISOString()
            ),
        ];
    }
}
