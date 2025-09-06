<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class TestAttempt extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'user_id',
        'test_id',
        'started_at',
        'completed_at',
        'score',
        'total_questions',
        'correct_answers',
        'answers',
        'status',
        'attempt_type',
    ];

    protected $casts = [
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
        'score' => 'float',
        'total_questions' => 'integer',
        'correct_answers' => 'integer',
        'answers' => 'array',
    ];

    // Scopes
    public function scopeCompleted($query)
    {
        return $query->where('status', 'completed');
    }

    public function scopeInProgress($query)
    {
        return $query->where('status', 'in_progress');
    }

    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function test()
    {
        return $this->belongsTo(Test::class);
    }

    public function certificate()
    {
        return $this->hasOne(Certificate::class);
    }

    // Certificate related methods
    public function canIssueCertificate(): bool
    {
        return $this->status === 'completed' && 
               $this->test->canIssueCertificate() && 
               $this->score >= $this->test->getCertificatePassingScore() &&
               !$this->certificate;
    }

    public function hasCertificate(): bool
    {
        return $this->certificate !== null;
    }
}