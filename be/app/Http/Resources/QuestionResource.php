<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class QuestionResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     */
    public function toArray(Request $request): array
    {
        $user = $request->user();
        $isOwnerOrAdmin = $user && ($user->isAdmin() || 
            ($this->relationLoaded('test') && $this->test->created_by === $user->id));

        return [
            'id' => $this->id,
            'question_text' => $this->question_text,
            'question_type' => $this->question_type,
            'options' => $this->options,
            'points' => $this->points,
            'order' => $this->order,
            'created_at' => $this->created_at->toISOString(),
            'updated_at' => $this->updated_at->toISOString(),
            
            // Only show correct answer to test owner/admin
            'correct_answer' => $this->when(
                $isOwnerOrAdmin,
                $this->correct_answer
            ),
            
            // Relationships
            'test' => new TestResource($this->whenLoaded('test')),
            
            // Admin/Teacher only fields
            'deleted_at' => $this->when(
                $isOwnerOrAdmin,
                $this->deleted_at?->toISOString()
            ),
        ];
    }
}