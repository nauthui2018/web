<?php

namespace App\Providers;

use App\Repositories\Contracts\CategoryRepositoryInterface;
use App\Repositories\Contracts\CertificateRepositoryInterface;
use App\Repositories\Contracts\QuestionRepositoryInterface;
use App\Repositories\Contracts\TestAttemptRepositoryInterface;
use App\Repositories\Contracts\TestRepositoryInterface;
use App\Repositories\Contracts\UserRepositoryInterface;
use App\Repositories\Eloquent\CategoryRepository;
use App\Repositories\Eloquent\CertificateRepository;
use App\Repositories\Eloquent\QuestionRepository;
use App\Repositories\Eloquent\TestAttemptRepository;
use App\Repositories\Eloquent\TestRepository;
use App\Repositories\Eloquent\UserRepository;
use Illuminate\Support\ServiceProvider;

class RepositoryServiceProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->bind(UserRepositoryInterface::class, UserRepository::class);
        $this->app->bind(CategoryRepositoryInterface::class, CategoryRepository::class);
        $this->app->bind(TestRepositoryInterface::class, TestRepository::class);
        $this->app->bind(QuestionRepositoryInterface::class, QuestionRepository::class);
        $this->app->bind(TestAttemptRepositoryInterface::class, TestAttemptRepository::class);
        $this->app->bind(CertificateRepositoryInterface::class, CertificateRepository::class);
    }

    public function boot()
    {
        //
    }
}
