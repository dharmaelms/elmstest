<?php

/**
 * @author  sathishkumar@linkstreet.in
 */

namespace App\Model;

use App\Exceptions\Quiz\QuestionTagMappingNotFoundException;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Moloquent;

class QuestionTagMapping extends Moloquent
{

    protected $table = 'question_tag_mapping';

    public $timestamps = true;

    /**
     * The attributes that should be mutated to dates.
     *
     * @var array
     */
    protected $dates = ['created_at', 'updated_at'];

    public static function getKeywordQuestionsMappingByQuiz($customQuizId)
    {
        try {
            return self::where("quiz_id", (int)$customQuizId)
                ->firstOrFail();
        } catch (ModelNotFoundException $e) {
            throw new QuestionTagMappingNotFoundException();
        }
    }
}
