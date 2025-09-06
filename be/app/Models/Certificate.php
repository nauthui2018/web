<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Certificate extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'test_id',
        'test_attempt_id',
        'certificate_number',
        'user_name',
        'test_title',
        'score',
        'passing_score',
        'completed_at',
        'issued_at',
        'expires_at',
        'certificate_template',
        'metadata',
        'is_valid',
        'revoked_at',
        'revoked_reason',
    ];

    protected $casts = [
        'completed_at' => 'datetime',
        'issued_at' => 'datetime',
        'expires_at' => 'datetime',
        'revoked_at' => 'datetime',
        'metadata' => 'array',
        'score' => 'decimal:2',
        'passing_score' => 'decimal:2',
        'is_valid' => 'boolean',
    ];

    /**
     * Relationships
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function test(): BelongsTo
    {
        return $this->belongsTo(Test::class);
    }

    public function testAttempt(): BelongsTo
    {
        return $this->belongsTo(TestAttempt::class);
    }

    /**
     * Scopes
     */
    public function scopeValid($query)
    {
        return $query->where('is_valid', true)->whereNull('revoked_at');
    }

    public function scopeRevoked($query)
    {
        return $query->where('is_valid', false)->whereNotNull('revoked_at');
    }

    public function scopeExpired($query)
    {
        return $query->where('expires_at', '<', now());
    }

    public function scopeActive($query)
    {
        return $query->valid()
                    ->where(function ($q) {
                        $q->whereNull('expires_at')
                          ->orWhere('expires_at', '>', now());
                    });
    }

    /**
     * Accessors & Mutators
     */
    public function getIsExpiredAttribute(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public function getIsActiveAttribute(): bool
    {
        return $this->is_valid && !$this->is_expired && !$this->revoked_at;
    }

    public function getFormattedScoreAttribute(): string
    {
        return number_format($this->score, 1) . '%';
    }

    /**
     * Instance Methods
     */
    public function revoke(?string $reason = null): bool
    {
        $this->update([
            'is_valid' => false,
            'revoked_at' => now(),
            'revoked_reason' => $reason,
        ]);

        return true;
    }

    public function restore(): bool
    {
        $this->update([
            'is_valid' => true,
            'revoked_at' => null,
            'revoked_reason' => null,
        ]);

        return true;
    }

    public function getDownloadUrl(): string
    {
        return route('certificates.download', $this->certificate_number);
    }

    public function getVerificationUrl(): string
    {
        return route('certificates.verify', $this->certificate_number);
    }
}
