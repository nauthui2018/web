<?php

namespace App\Repositories\Eloquent;

use App\Models\Certificate;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use Carbon\Carbon;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

class CertificateRepository extends BaseRepository implements CertificateRepositoryInterface
{
    public function __construct(Certificate $model)
    {
        parent::__construct($model);
    }

    /**
     * Find certificate by certificate number
     */
    public function findByCertificateNumber(string $certificateNumber): ?Certificate
    {
        return $this->model->where('certificate_number', $certificateNumber)
                          ->with(['user', 'test', 'testAttempt'])
                          ->first();
    }

    /**
     * Get certificates for a specific user
     */
    public function getUserCertificates(int $userId, bool $activeOnly = false): Collection
    {
        $query = $this->model->where('user_id', $userId)
                            ->with(['test', 'testAttempt'])
                            ->latest('issued_at');

        if ($activeOnly) {
            $query->where('is_valid', true)
                  ->where(function ($q) {
                      $q->whereNull('expires_at')
                        ->orWhere('expires_at', '>', now());
                  })
                  ->where('revoked_at', null);
        }

        return $query->get();
    }

    /**
     * Get certificate by test attempt
     */
    public function findByTestAttempt(int $testAttemptId): ?Certificate
    {
        return $this->model->where('test_attempt_id', $testAttemptId)->first();
    }

    /**
     * Get certificates for a specific test
     */
    public function getTestCertificates(int $testId): Collection
    {
        return $this->model->where('test_id', $testId)
                          ->with(['user', 'testAttempt'])
                          ->latest('issued_at')
                          ->get();
    }

    /**
     * Get certificates expiring within specified days
     */
    public function getCertificatesExpiringIn(int $days): Collection
    {
        $endDate = now()->addDays($days);

        return $this->model->where('is_valid', true)
                          ->whereNotNull('expires_at')
                          ->whereBetween('expires_at', [now(), $endDate])
                          ->with(['user', 'test'])
                          ->get();
    }

    /**
     * Get expired certificates
     */
    public function getExpiredCertificates(): Collection
    {
        return $this->model->whereNotNull('expires_at')
                          ->where('expires_at', '<', now())
                          ->where('is_valid', true)
                          ->with(['user', 'test'])
                          ->get();
    }

    /**
     * Get revoked certificates
     */
    public function getRevokedCertificates(): Collection
    {
        return $this->model->whereNotNull('revoked_at')
                          ->with(['user', 'test'])
                          ->latest('revoked_at')
                          ->get();
    }

    /**
     * Get active certificates
     */
    public function getActiveCertificates(): Collection
    {
        return $this->model->where('is_valid', true)
                          ->where(function ($query) {
                              $query->whereNull('expires_at')
                                    ->orWhere('expires_at', '>', now());
                          })
                          ->whereNull('revoked_at')
                          ->with(['user', 'test'])
                          ->latest('issued_at')
                          ->get();
    }

    /**
     * Search certificates by user name or certificate number
     */
    public function searchCertificates(string $search): Collection
    {
        return $this->model->where(function ($query) use ($search) {
                              $query->where('certificate_number', 'like', "%{$search}%")
                                    ->orWhere('user_name', 'like', "%{$search}%")
                                    ->orWhere('test_title', 'like', "%{$search}%");
                          })
                          ->with(['user', 'test'])
                          ->latest('issued_at')
                          ->get();
    }

    /**
     * Get certificates with filters and pagination
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        $query = $this->model->with(['user', 'test']);

        // Apply filters
        if (isset($filters['user_id'])) {
            $query->where('user_id', $filters['user_id']);
        }

        if (isset($filters['test_id'])) {
            $query->where('test_id', $filters['test_id']);
        }

        if (isset($filters['is_valid'])) {
            $query->where('is_valid', $filters['is_valid']);
        }

        if (isset($filters['template'])) {
            $query->where('certificate_template', $filters['template']);
        }

        if (isset($filters['issued_from'])) {
            $query->where('issued_at', '>=', $filters['issued_from']);
        }

        if (isset($filters['issued_to'])) {
            $query->where('issued_at', '<=', $filters['issued_to']);
        }

        if (isset($filters['search'])) {
            $search = $filters['search'];
            $query->where(function ($q) use ($search) {
                $q->where('certificate_number', 'like', "%{$search}%")
                  ->orWhere('user_name', 'like', "%{$search}%")
                  ->orWhere('test_title', 'like', "%{$search}%");
            });
        }

        return $query->latest('issued_at')->paginate($perPage);
    }

    /**
     * Bulk revoke certificates for a test
     */
    public function bulkRevokeTestCertificates(int $testId, ?string $reason = null): int
    {
        return $this->model->where('test_id', $testId)
                          ->where('is_valid', true)
                          ->whereNull('revoked_at')
                          ->update([
                              'is_valid' => false,
                              'revoked_at' => now(),
                              'revoked_reason' => $reason ?? 'Bulk revocation'
                          ]);
    }

    /**
     * Get certificate statistics
     */
    public function getCertificateStats(): array
    {
        $total = $this->model->count();
        $active = $this->model->where('is_valid', true)
                             ->where(function ($query) {
                                 $query->whereNull('expires_at')
                                       ->orWhere('expires_at', '>', now());
                             })
                             ->whereNull('revoked_at')
                             ->count();

        $expired = $this->model->whereNotNull('expires_at')
                              ->where('expires_at', '<', now())
                              ->count();

        $revoked = $this->model->whereNotNull('revoked_at')->count();

        $thisMonth = $this->model->whereBetween('issued_at', [
            now()->startOfMonth(),
            now()->endOfMonth()
        ])->count();

        $thisYear = $this->model->whereBetween('issued_at', [
            now()->startOfYear(),
            now()->endOfYear()
        ])->count();

        return [
            'total' => $total,
            'active' => $active,
            'expired' => $expired,
            'revoked' => $revoked,
            'issued_this_month' => $thisMonth,
            'issued_this_year' => $thisYear,
            'templates' => $this->getTemplateStats()
        ];
    }

    /**
     * Get certificates issued between dates
     */
    public function getCertificatesIssuedBetween(Carbon $startDate, Carbon $endDate): Collection
    {
        return $this->model->whereBetween('issued_at', [$startDate, $endDate])
                          ->with(['user', 'test'])
                          ->latest('issued_at')
                          ->get();
    }

    /**
     * Get certificates by template type
     */
    public function getCertificatesByTemplate(string $template): Collection
    {
        return $this->model->where('certificate_template', $template)
                          ->with(['user', 'test'])
                          ->latest('issued_at')
                          ->get();
    }

    /**
     * Check if certificate number exists
     */
    public function certificateNumberExists(string $certificateNumber): bool
    {
        return $this->model->where('certificate_number', $certificateNumber)->exists();
    }

    /**
     * Get the latest certificate for a user and test
     */
    public function getLatestUserTestCertificate(int $userId, int $testId): ?Certificate
    {
        return $this->model->where('user_id', $userId)
                          ->where('test_id', $testId)
                          ->latest('issued_at')
                          ->first();
    }

    /**
     * Get template statistics
     */
    private function getTemplateStats(): array
    {
        return $this->model->selectRaw('certificate_template, COUNT(*) as count')
                          ->groupBy('certificate_template')
                          ->pluck('count', 'certificate_template')
                          ->toArray();
    }
}
