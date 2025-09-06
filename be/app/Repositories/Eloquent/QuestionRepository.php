<?php

namespace App\Repositories\Eloquent;

use App\Models\Question;
use App\Repositories\Contracts\QuestionRepositoryInterface;

class QuestionRepository extends BaseRepository implements QuestionRepositoryInterface
{
    public function __construct(Question $model)
    {
        parent::__construct($model);
    }

}
