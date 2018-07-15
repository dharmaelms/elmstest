<?php

namespace App\Model\Survey\Repository;

use App\Model\Sequence;
use App\Model\Survey\Entity\Survey;
use App\Model\Survey\Entity\SurveyAttempt;

/**
 * Class SurveyAttemptRepository
 *
 * @package App\Model\Survey\Repository
 */
class SurveyAttemptRepository implements ISurveyAttemptRepository
{
    /**
     * {@inheritdoc}
     */
    public function getSurveyAttempt($attempt_id)
    {
        //TODO
    }

    /**
     * {@inheritdoc}
     */
    public function insertData($data = [])
    {
        return SurveyAttempt::insert($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return Sequence::getSequence('id');
    }
    
    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptBySurveyIdAndUserId($params)
    {
        return SurveyAttempt::filter($params)->get();
    }

    /**
     * {@inheritdoc}
     */
    public function updateData($survey_id, $user_id, $status, $completed_on)
    {
        return SurveyAttempt::where('survey_id', $survey_id)
                    ->where('user_id', $user_id)
                    ->update(['status' => $status, 'completed_on' => $completed_on]);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptByUserIdAndSurveyIds($user_id, $survey_ids)
    {
        return SurveyAttempt::where('user_id', '=', $user_id)
            ->whereIn('survey_id', $survey_ids)
            ->get();
    }
}
