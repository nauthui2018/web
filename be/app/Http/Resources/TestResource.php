<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class TestResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'duration_minutes' => $this->duration_minutes,
            'is_active' => $this->is_active,
            'is_public' => $this->is_public,
            'created_by' => $this->created_by,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            'passing_score' => $this->passing_score,
            'show_correct_answer' => $this->show_correct_answer,
            'difficulty_level' => $this->difficulty_level,

            // Relationships
            'category' => new CategoryResource($this->whenLoaded('category')),
            'creator' => new UserResource($this->whenLoaded('creator')),
            'questions' => QuestionResource::collection($this->whenLoaded('questions')),

            // Counts
            'questions_count' => $this->whenCounted('questions'),
            'attempts_count' => $this->whenCounted('attempts'),

            // Computed fields
            'total_points' => $this->when(
                $this->relationLoaded('questions'),
                fn() => $this->questions->sum('points')
            ),

            // Permissions
            'can_edit' => $this->when(
                $request->user(),
                function () use ($request) {
                    $user = $request->user();
                    return $user->isAdmin() ||
                           ($user->isTeacher() && $this->created_by === $user->id);
                }
            ),

            'can_delete' => $this->when(
                $request->user(),
                function () use ($request) {
                    $user = $request->user();
                    return $user->isAdmin() ||
                           ($user->isTeacher() && $this->created_by === $user->id && !$this->attempts()->exists());
                }
            ),

            // Admin/Teacher only fields
            'deleted_at' => $this->when(
                $request->user() && ($request->user()->isAdmin() || $request->user()->isTeacher()),
                $this->deleted_at?->toISOString()
            ),

            'can_attempt' => $this->when(
                $request->user(),
                function () use ($request) {
                    return $this->is_active && $this->is_public;
                }
            ),
        ];
    }
}
