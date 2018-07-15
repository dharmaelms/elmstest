<?php

namespace App\Services\Survey;

use App\Exceptions\Program\NoProgramAssignedException;
use App\Model\Survey\Repository\ISurveyAttemptRepository;
use App\Model\Survey\Repository\ISurveyRepository;
use App\Services\Survey\ISurveyAttemptDataService;
use App\Services\Survey\ISurveyService;
use Auth;
use Exception;
use Log;
use Timezone;

/**
 * Class SurveyAttemptService
 *
 * @package App\Services\Survey
 */
class SurveyAttemptService implements ISurveyAttemptService
{
	/**
     * @var App\Model\Survey\Repository\ISurveyAttemptRepository
     */
    private $survey_attempt_repository;

    /**
     * @var App\Model\Survey\Repository\ISurveyRepository
     */
    private $survey_repository;

    /**
     * @var App\Services\Survey\ISurveyService
     */
    private $survey_service;

    /**
     * @var App\Model\Survey\Survey\ISurveyAttemptDataService
     */
    private $survey_attempt_data_service;

    /**
     * SurveyAttemptService constructor.
     * @param ISurveyAttemptRepository $survey_attempt_repository
     */
    public function __construct(
        ISurveyAttemptRepository $survey_attempt_repository,
        ISurveyRepository $survey_repository,
        ISurveyService $survey_service,
        ISurveyAttemptDataService $survey_attempt_data_service
    ) {
        $this->survey_attempt_repository = $survey_attempt_repository;
        $this->survey_repository = $survey_repository;
        $this->survey_service = $survey_service;
        $this->survey_attempt_data_service = $survey_attempt_data_service;
    }

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
    public function insertData($data)
    {
        return $this->survey_attempt_repository->insertData($data);
    }

    /**
     * {@inheritdoc}
     */
    public function getNextSequence()
    {
        return $this->survey_attempt_repository->getNextSequence();
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptBySurveyIdAndUserId($params)
    {
        return $this->survey_attempt_repository->getSurveyAttemptBySurveyIdAndUserId($params);
    }

    /**
     * {@inheritdoc}
     */
    public function updateData($survey_id, $user_id, $status, $completed_on)
    {
        return $this->survey_attempt_repository->updateData($survey_id, $user_id, $status, $completed_on);
    }

    /**
     * {@inheritdoc}
     */
    public function getSurveyAttemptIdsByUserAndSurveyIds($user_id, $survey_ids)
    {
        return $this->survey_attempt_repository->getSurveyAttemptByUserIdAndSurveyIds($user_id, $survey_ids)->pluck('survey_id')->unique();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllIncompleteSurveys($page, $limit)
    {
        try {
            $user_survey_rel = collect($this->survey_service->getAllSurveysAssigned());
        } catch (Exception $e) {
            Log::info($e);
            $user_survey_rel = (['seq_surveys' => [], 'survey_list' => [], 'feed_survey_list' => []]);
        }
        $seqSurvey = $user_survey_rel->get('seq_surveys', []);
        $survey_list = array_unique($user_survey_rel->get('survey_list', []));

        // code for completed survey
        $attempted = $this->survey_attempt_data_service->getSurveyAttemptByUserIdAndSurveyIds(
                (int)Auth::user()->uid,
                $survey_list
        );
        $attempt = $data = $unattempted_survey_ids = [];
        foreach ($attempted as $value) {
            $attempt[] = $value;
        }
        $myUASurveyIds = [];
        $myUASurveyIds = array_diff($survey_list, $attempt);
        //unattempted survey id's with non sequential posts survey items
        $nonSquSurveys = array_diff($myUASurveyIds, $seqSurvey);

        $survey = $this->survey_repository->getIncompleteSurveysBySurveyId($page-1, $limit, $nonSquSurveys);
        foreach ($survey as $value) {
            $row = new \stdClass;
            $row->id = $value->id;
            $row->survey_title = $value->survey_title;
            $row->start_time = $value->start_time->timezone(Auth::user()->timezone)->format('d M Y');
            $row->end_time = $value->end_time->timezone(Auth::user()->timezone)->format('d M Y');
            if (empty($value->start_time)) {
                $row->start_time = 0;
            }
            if (empty($value->end_time)) {
                $row->end_time = 0;
            }
            $row->questions = count($value->survey_question);
            $data[] = $row;
        }
        if (empty($data)) {
            throw new NoProgramAssignedException();
        }
        return $data;
    }
}
