<?php

namespace App\Repositories\Eloquent;

use App\Models\TestAttempt;
use App\Repositories\Contracts\TestAttemptRepositoryInterface;

class TestAttemptRepository extends BaseRepository implements TestAttemptRepositoryInterface
{
    public function __construct(TestAttempt $model)
    {
        parent::__construct($model);
    }

}
