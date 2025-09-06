<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AttemptResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isOwner = $user && $this->user_id === $user->id;
        $isTestOwnerOrAdmin = $user && ($user->isAdmin() || 
            ($this->relationLoaded('test') && $this->test->created_by === $user->id));

        return [
            'id' => $this->id,
            'test_id' => $this->test_id,
            'user_id' => $this->user_id,
            'status' => $this->status,
            'attempt_type' => $this->attempt_type,
            'started_at' => $this->started_at->toISOString(),
            'completed_at' => $this->completed_at?->toISOString(),
            'score' => $this->score,
            'total_questions' => $this->total_questions,
            'correct_answers' => $this->correct_answers,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Computed fields
            'duration_taken' => $this->when(
                $this->completed_at,
                fn() => $this->started_at->diffInMinutes($this->completed_at)
            ),
            
            'percentage' => $this->when(
                $this->score !== null,
                fn() => round(($this->correct_answers / $this->total_questions) * 100, 2)
            ),
            
            // Relationships
            'user' => new UserResource($this->whenLoaded('user')),
            'test' => new TestResource($this->whenLoaded('test')),
            
            // Show answers only to attempt owner or test owner/admin
            'answers' => $this->when(
                $isOwner || $isTestOwnerOrAdmin,
                $this->answers
            ),
            
            // Time remaining for in-progress attempts
            'time_remaining' => $this->when(
                $isOwner && $this->status === 'in_progress' && $this->relationLoaded('test'),
                function () {
                    $elapsed = $this->started_at->diffInMinutes(now());
                    $remaining = $this->test->duration_minutes - $elapsed;
                    return max(0, $remaining);
                }
            ),
        ];
    }
}