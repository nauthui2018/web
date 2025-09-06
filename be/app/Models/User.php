<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\SoftDeletes;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Tymon\JWTAuth\Contracts\JWTSubject;

class User extends Authenticatable implements JWTSubject
{
    use HasFactory, Notifiable, SoftDeletes;

    protected $fillable = [
        'name', 'email', 'password', 'role', 'is_teacher', 'is_active', 'phone'
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected $casts = [
        'is_teacher' => 'boolean',
        'is_active' => 'boolean',
        'email_verified_at' => 'datetime',
    ];

    protected $dates = ['deleted_at'];

    // JWT Methods
    public function getJWTIdentifier()
    {
        return $this->getKey();
    }

    public function getJWTCustomClaims()
    {
        return [];
    }

    // Helper methods for role checking
    public function isAdmin(): bool
    {
        return $this->role === 'admin' && $this->is_active;
    }

    public function hasRole(string $role): bool
    {
        return $this->role === $role && $this->is_active;
    }

    public function isTeacher(): bool
    {
        return $this->role === 'user' && $this->is_teacher && $this->is_active;
    }

    public function isUser(): bool
    {
        return $this->role === 'user' && !$this->is_teacher && $this->is_active;
    }

    public function canManageTests(): bool
    {
        return $this->is_active && ($this->role === 'admin' || ($this->role === 'user' && $this->is_teacher));
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeTeachers($query)
    {
        return $query->where('role', 'user')->where('is_teacher', true);
    }

    public function scopeAdmins($query)
    {
        return $query->where('role', 'admin');
    }

    public function scopeRegularUsers($query)
    {
        return $query->where('role', 'user')->where('is_teacher', false);
    }

    // Relationships
    public function createdTests()
    {
        return $this->hasMany(Test::class, 'created_by');
    }

    public function createdCategories()
    {
        return $this->hasMany(Category::class, 'created_by');
    }

    public function testAttempts()
    {
        return $this->hasMany(TestAttempt::class);
    }

    public function refreshTokens()
    {
        return $this->hasMany(RefreshToken::class);
    }

    public function certificates()
    {
        return $this->hasMany(Certificate::class);
    }
}