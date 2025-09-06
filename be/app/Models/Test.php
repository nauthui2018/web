<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Test extends Model
{
    use HasFactory, SoftDeletes;

    protected $fillable = [
        'title', 'description', 'duration_minutes',
        'category_id', 'is_active', 'is_public', 'created_by',
        'passing_score', 'show_correct_answer', 'difficulty_level',
        'has_certificate', 'certificate_passing_score',
        'certificate_template', 'certificate_validity_days'
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_public' => 'boolean',
        'show_correct_answer' => 'boolean',
        'has_certificate' => 'boolean',
        'certificate_passing_score' => 'decimal:2',
        'certificate_validity_days' => 'integer',
    ];

    protected $dates = ['deleted_at'];

    // Relationships
    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function questions()
    {
        return $this->hasMany(Question::class)->orderBy('order');
    }

    public function attempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopePublic($query)
    {
        return $query->where('is_public', true);
    }

    public function scopeAvailable($query)
    {
        return $query->active()->public();
    }

    public function scopeWithCertificate($query)
    {
        return $query->where('has_certificate', true);
    }

    // Certificate related methods
    public function canIssueCertificate(): bool
    {
        return $this->has_certificate && $this->certificate_passing_score !== null;
    }

    public function getCertificatePassingScore(): ?float
    {
        return $this->certificate_passing_score;
    }
}
