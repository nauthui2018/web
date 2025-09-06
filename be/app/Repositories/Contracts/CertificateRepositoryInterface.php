<?php

namespace App\Repositories\Contracts;

use App\Models\Certificate;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;

interface CertificateRepositoryInterface extends BaseRepositoryInterface
{
    /**
     * Find certificate by certificate number
     */
    public function findByCertificateNumber(string $certificateNumber): ?Certificate;

    /**
     * Get certificates for a specific user
     */
    public function getUserCertificates(int $userId, bool $activeOnly = false): Collection;

    /**
     * Get certificate by test attempt
     */
    public function findByTestAttempt(int $testAttemptId): ?Certificate;

    /**
     * Get certificates for a specific test
     */
    public function getTestCertificates(int $testId): Collection;

    /**
     * Get certificates expiring within specified days
     */
    public function getCertificatesExpiringIn(int $days): Collection;

    /**
     * Get expired certificates
     */
    public function getExpiredCertificates(): Collection;

    /**
     * Get revoked certificates
     */
    public function getRevokedCertificates(): Collection;

    /**
     * Get active certificates
     */
    public function getActiveCertificates(): Collection;

    /**
     * Search certificates by user name or certificate number
     */
    public function searchCertificates(string $search): Collection;

    /**
     * Get certificates with filters and pagination
     */
    public function getPaginatedWithFilters(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    /**
     * Bulk revoke certificates for a test
     */
    public function bulkRevokeTestCertificates(int $testId, ?string $reason = null): int;

    /**
     * Get certificate statistics
     */
    public function getCertificateStats(): array;

    /**
     * Get certificates issued between dates
     */
    public function getCertificatesIssuedBetween(\Carbon\Carbon $startDate, \Carbon\Carbon $endDate): Collection;

    /**
     * Get certificates by template type
     */
    public function getCertificatesByTemplate(string $template): Collection;

    /**
     * Check if certificate number exists
     */
    public function certificateNumberExists(string $certificateNumber): bool;

    /**
     * Get the latest certificate for a user and test
     */
    public function getLatestUserTestCertificate(int $userId, int $testId): ?Certificate;
}
