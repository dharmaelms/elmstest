<?php
namespace App\Http\Controllers\Portal;

use App\Enums\Survey\SurveyType;
use App\Http\Controllers\PortalBaseController;
use App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\Survey\ISurveyService;
use App\Services\Survey\ISurveyAttemptService;
use App\Services\Survey\ISurveyAttemptDataService;
use App\Services\Survey\ISurveyQuestionService;
use Auth;
use Exception;
use Input;
use Log;
use Request;
use Validator;

class SurveyController extends PortalBaseController
{

    /**
     * @var \App\Services\Survey\ISurveyService
     */
    private $survey_service;

    /**
     * @var \App\Services\Survey\ISurveyQuestionService
     */
    private $survey_question_service;

    /**
     * @var \App\Services\Survey\ISurveyAttemptService
     */
    private $survey_attempt_service;

    /**
     * @var \App\Services\Survey\ISurveyAttemptDataService
     */
    private $survey_attempt_data_service;

    /**
     * @var \App\Services\Program\IProgramService
     */
    private $program_service;

    /**
     * @var \App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository
     */
    private $overall_channel_analytic_repo;

    /**
     * @var \App\Services\Post\IPostService
     */
    private $post_service;

    public function __construct(
        Request $request,
        ISurveyService $survey_service,
        ISurveyQuestionService $survey_question_service,
        ISurveyAttemptService $survey_attempt_service,
        ISurveyAttemptDataService $survey_attempt_data_service,
        IProgramService $program_service,
        IOverAllChannalAnalyticRepository $overall_channel_analytic_repo,
        IPostService $post_service
    )
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->survey_service = $survey_service;
        $this->survey_question_service = $survey_question_service;
        $this->survey_attempt_service = $survey_attempt_service;
        $this->survey_attempt_data_service = $survey_attempt_data_service;
        $this->program_service = $program_service;
        $this->overall_channel_analytic_repo = $overall_channel_analytic_repo;
        $this->post_service = $post_service;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getIndex()
    {
        $filter = Input::get('filter', 'unattempted');
        $start = Input::get('start', 0);
        $limit = 9;
        try {
            $user_survey_rel = collect($this->survey_service->getAllSurveysAssigned());
        } catch (\Exception $e) {
            Log::info($e);
            $user_survey_rel = collect(['seq_surveys' => [], 'survey_list' => [], 'feed_survey_list' => []]);
        }
        $seqSurvey = $user_survey_rel->get('seq_surveys', []);
        $survey_list = array_unique($user_survey_rel->get('survey_list', []));

        // code for completed survey
        $attempted = $this->survey_attempt_data_service->getSurveyAttemptByUserIdAndSurveyIds(
                (int)Auth::user()->uid,
                $survey_list
        );
        $attempt = [];
        foreach ($attempted as $value) {
            $attempt[] = $value;
        }
        $count['attempted'] = $attempted->count();
        $myUASurveyIds = [];
        $myUASurveyIds = array_diff($survey_list, $attempt);
        $nonSquSurveys = array_diff($myUASurveyIds, $seqSurvey);

        // code for unattempted survey
        $unattempted = $this->survey_service->getSurveyByIds($nonSquSurveys);
        $attempted = $this->survey_service->getSurveyByIds($attempted->all());
        $count['unattempted'] = $unattempted->count();

        switch ($filter) {
            case 'unattempted':
                $surveys = $unattempted->slice($start, $limit);
                break;
            case 'attempted':
                $surveys = $attempted->slice($start, $limit);;
                break;
            default:
                return parent::getError($this->theme, $this->theme_path);
                break;
        }

        if (Request::ajax()) {
            if (!$surveys->isEmpty()) {
                return response()->json([
                    'status' => true,
                    'data' => view(
                            $this->theme_path . '.survey.survey_ajax_load',
                            [
                                'surveys' => $surveys,
                                'completed_list' => $attempted,
                                'filter' => $filter
                            ])->render(),
                ]);
            } else {
                return response()->json([
                    'status' => false,
                    'data' => 'No more assessment to show',
                ]);
            }
        } else {
            $this->layout->pagetitle = "Surveys";
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.survey.list_surveys')
                ->with('surveys', $surveys)
                ->with('filter', $filter)
                ->with('count', $count)
                ->with('completed_list', $attempted);
        }
    }

    public function getSurveyDetails($survey_id)
    {
        $survey = $this->survey_service->getSurveyByIds($survey_id)->first();
        $survey_attempt = $this->survey_attempt_service->getSurveyAttemptBySurveyIdAndUserId(
                            ["survey_id" => $survey_id, "user_id" => Auth::user()->uid]
                        )->first();
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.survey.survey_detail')
                ->with('survey', $survey)
                ->with('survey_attempt', $survey_attempt);
    }

    public function getStartSurvey($packet_slug, $survey_id)
    {
        $survey_id = (int)$survey_id;
        $user_id = (int)Auth::user()->uid;
        $survey_attempt_details = $this->survey_attempt_service->getSurveyAttemptBySurveyIdAndUserId(
                                            ["survey_id" => $survey_id, "user_id" => $user_id]
                                        )->first();
        if (empty($survey_attempt_details)) {
            $data = [
                "id" => $this->survey_attempt_service->getNextSequence(),
                "survey_id" => $survey_id,
                "user_id" => (int)$user_id,
                "status" => "OPEN",
                "started_on" => time(),
                "completed_on" => "",
            ];
            //Insert survey attempt data
            $this->survey_attempt_service->insertData($data);
        }
        $data = $this->surveyQuestionDetails($survey_id);
        $survey = array_get($data, 'survey');
        $survey_question = array_get($data, 'survey_questions', []);
        $this->layout->pagetitle = trans('survey.survey'). ' | ' . ucwords($survey->survey_title);
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.survey.attempt_survey')
                                    ->with(
                                        [
                                            'survey' => $survey,
                                            'survey_questions' => $survey_question,
                                            'packet_slug' => $packet_slug
                                        ]
                                    );
    }

    public function surveyQuestionDetails($survey_id)
    {
        //Get survey details
        $survey = $this->survey_service->getSurveyByIds($survey_id)->first();
        //Get survey question details
        $survey_question_details = $this->survey_question_service->getQuestionBySurveyId($survey_id);
        return [
            'survey' => $survey,
            'survey_questions' => $survey_question_details,
        ];
    }

    public function postSubmitSurvey($packet_slug, $survey_id)
    {
        try {
            $inputs = Request::all();
            $survey_id = (int)$survey_id;
            $user_id = (int)Auth::user()->uid;
            $inputs_values = array_filter(array_values($inputs));
            $survey_attempt_details = $this->survey_attempt_service->getSurveyAttemptBySurveyIdAndUserId(
                                                ["survey_id" => $survey_id, "user_id" => $user_id]
                                            )->first();
            if ($survey_attempt_details->status == "CLOSED") {
                return redirect('survey/start-survey/'.$packet_slug.'/'.$survey_id)->with('error', trans('survey.submitted_survey'));
            } elseif (empty($inputs_values)) {
                return redirect('survey/start-survey/'.$packet_slug.'/'.$survey_id)->with('error', trans('survey.unanswered'));
            } else {
                $validation_results = $this->surveyQuestionValidation($survey_id, $inputs);
                $validation = Validator::make(
                        Input::all(),
                        array_get($validation_results, 'rules'),
                        array_get($validation_results, 'messages')
                    );
                if ($validation->fails()) {
                    return redirect('survey/start-survey/'.$packet_slug.'/'.$survey_id)->withInput()->withErrors($validation);
                } else {
                    $attempt_id = $this->survey_attempt_data_service->getNextSequence();
                    //Get survey question details
                    $survey_question_details = $this->survey_question_service->getQuestionBySurveyId($survey_id)->keyBy('id');
                    foreach ($survey_question_details as $key => $value) {
                        $survey_question_id = $value->id;
                        $survey_question_choices = array_map('strtolower', $value->choices);
                        $survey_question_type = $value->type;
                        switch ($survey_question_type) {
                            case SurveyType::SINGLE_ANSWER:
                                $answer = array_get($inputs, 'radio_'.$key, []);
                                $answer = strtolower(implode('', $answer));
                                $others = array_get($inputs, 'radio_textarea_'.$key);
                                $answer_index = [];
                                if ((!empty($answer)) || (!empty($others))) {
                                    if (!empty($answer)) {
                                        $answer_index[] = array_search($answer, $survey_question_choices);
                                    }
                                    $this->insertSurveyAttemptedData($attempt_id, $survey_id, $user_id, $survey_question_id, $others, $answer_index);
                                }
                                break;

                            case SurveyType::MULTIPLE_ANSWER:
                                $answer = array_get($inputs, 'checkbox_'.$key, []);
                                $answers = array_map('strtolower', $answer);
                                $others = array_get($inputs, 'checkbox_textarea_'.$key);
                                if ((!empty($answer)) || (!empty($others))) {
                                    $answer_index = [];
                                    foreach ($answers as $key => $val) {
                                        $answer_index[$key] = array_search($val, $survey_question_choices);
                                    }
                                    $this->insertSurveyAttemptedData($attempt_id, $survey_id, $user_id, $survey_question_id, $others, $answer_index);
                                }
                                break;

                            case SurveyType::RANGE:
                                $answer = array_get($inputs, 'rate_'.$key, []);
                                $answer = strtolower(implode('', $answer));
                                $others = array_get($inputs, 'rate_textarea_'.$key);
                                if ((!empty($answer)) || (!empty($others))) {
                                    $answer_index = array_search($answer, $survey_question_choices);
                                    $this->insertSurveyAttemptedData($attempt_id, $survey_id, $user_id, $survey_question_id, $others, [$answer_index]);
                                }
                                break;

                            case SurveyType::DESCRIPTIVE:
                                $answer = array_get($inputs, 'textarea_'.$key, []);
                                if (!empty($answer)) {
                                    $this->insertSurveyAttemptedData($attempt_id, $survey_id, $user_id, $survey_question_id, $answer, []);
                                }
                                break;

                            default:
                                Log::error(trans('survey.error_question_type'));
                                break;
                        }
                    }
                    //Update survey attempt data
                    $survey_close = $this->survey_attempt_service->updateData($survey_id, $user_id, 'CLOSED', time());
                    $survey = $this->survey_service->getSurveyByIds($survey_id)->first();
                    if (($survey_close) && (!empty($survey->post_id))) {
                        $this->putEntryInToOca((int)$survey_id);
                    }
                    if ($packet_slug == "unattempted") {
                        $this->surveyViewReports($survey_id, $inputs, $packet_slug, 1);
                    } else {
                        return redirect('program/packet/'.$packet_slug.'/element/'.$survey_id.'/survey');
                    }
                }
            }
        } catch(Exception $e) {
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
            return redirect('survey/start-survey/'.$packet_slug.'/'.$survey_id)->with('error', trans('survey.error_message'));
        }
    }

    public function insertSurveyAttemptedData($attempt_id, $survey_id, $user_id, $survey_question_id, $others, $answer)
    {
        $this->survey_attempt_data_service->insertData([
            'attempt_id' => $attempt_id,
            'survey_id' => $survey_id,
            'user_id' => $user_id,
            'question_id' => $survey_question_id,
            'other_text' => $others,
            'user_answer' => $answer,
        ]);
    }

    public function surveyQuestionValidation($survey_id, $inputs, $rules = [], $messages = [])
    {
        //Get survey question details
        $survey_question_details = $this->survey_question_service->getQuestionBySurveyId($survey_id)->keyBy('id');
        foreach ($survey_question_details as $key => $value) {
            $is_mandatory = $value->is_mandatory;
            $survey_question_type = $value->type;
            //Question Radio buttons validation
            $radio_values = array_get($inputs, 'radio_'.$key, []);
            $radio_textarea_value = array_get($inputs, 'radio_textarea_'.$key);

            //Question Checkbox validation
            $checkbox_values = array_get($inputs, 'checkbox_'.$key, []);
            $checkbox_textarea_value = array_get($inputs, 'checkbox_textarea_'.$key);

            //Question Range validation
            $rate_values = array_get($inputs, 'rate_'.$key, []);
            $rate_textarea_value = array_get($inputs, 'rate_textarea_'.$key);

            //Question Textarea validation
            $textarea_value = array_get($inputs, 'textarea_'.$key);


            if ($is_mandatory) {
                //Question Radio buttons validation
                if ($survey_question_type == SurveyType::SINGLE_ANSWER) {
                    if ((empty($radio_values)) && (empty($radio_textarea_value))) {
                        $radio_question = 'radio_'.$key;
                        $rules[$key][$radio_question] = 'Required';
                        $messages[$key] = [
                            $radio_question.'.required' => trans('survey.required'),
                        ];
                    }
                }

                //Question Checkbox validation
                if ($survey_question_type == SurveyType::MULTIPLE_ANSWER) {
                    if ((empty($checkbox_values)) && (empty($checkbox_textarea_value))) {
                        $checkbox_question = 'checkbox_'.$key;
                        $rules[$key][$checkbox_question] = 'Required';
                        $messages[$key] = [
                            $checkbox_question.'.required' => trans('survey.required'),
                        ];
                    }
                }

                //Question Range validation
                if ($survey_question_type == SurveyType::RANGE) {
                    if ((empty($rate_values)) && (empty($rate_textarea_value))) {
                        $rate_question = 'rate_'.$key;
                        $rules[$key][$rate_question] = 'Required';
                        $messages[$key] = [
                            $rate_question.'.required' => trans('survey.required'),
                        ];
                    }
                }

                //Question Textarea validation
                if (($survey_question_type == SurveyType::DESCRIPTIVE) && (empty($textarea_value))) {
                    $textarea_question = 'textarea_'.$key;
                    $rules[$key][$textarea_question] = 'Required';
                    $messages[$key] = [
                        $textarea_question.'.required' => trans('survey.required'),
                    ];
                }
            }
        }
        if (!empty($rules)) {
            $rules = call_user_func_array('array_merge', $rules);
        }
        if (!empty($messages)) {
            $messages = call_user_func_array('array_merge', $messages);
        }
        return ['rules' => $rules, 'messages' => $messages];
    }

    public function getViewReports($packet_slug, $survey_id)
    {
        try {
            $survey_id = (int)$survey_id;
            $user_id = (int)Auth::user()->uid;
            $survey_attempt_data = $this->survey_attempt_data_service->getSurveyAttemptDataByUserIdAndSurveyId($survey_id, $user_id)->keyBy('question_id');
            $question_ids = $survey_attempt_data->keys()->toArray();
            $question_details = $this->survey_question_service->getSurveyQuestionsById($question_ids)->keyBy('id');
            $answers = [];
            foreach ($question_details as $question_id => $value) {
                $question_type = $value->type;
                $question_choices = $value->choices;
                switch ($question_type) {
                    case SurveyType::SINGLE_ANSWER:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $other_text = $attempted_data->other_text;
                        $user_answer = array_values($attempted_data->user_answer);
                        $user_answer = implode('', $user_answer);
                        $answer = array_get($question_choices, $user_answer);
                        $answers['radio_'.$question_id] = [$answer];
                        $answers['radio_textarea_'.$question_id] = $other_text;
                        break;
                    case SurveyType::MULTIPLE_ANSWER:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $other_text = $attempted_data->other_text;
                        $user_answer = array_values($attempted_data->user_answer);
                        $multiple_answers = [];
                        foreach ($user_answer as $value) {
                            $multiple_answers[] = array_get($question_choices, $value);
                        }
                        $answers['checkbox_'.$question_id] = $multiple_answers;
                        $answers['checkbox_textarea_'.$question_id] = $other_text;
                        break;
                    case SurveyType::RANGE:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $other_text = $attempted_data->other_text;
                        $user_answer = array_values($attempted_data->user_answer);
                        $user_answer = implode('', $user_answer);
                        $answer = array_get($question_choices, $user_answer);
                        $answers['rate_'.$question_id] = [$answer];
                        $answers['rate_textarea_'.$question_id] = $other_text;
                        break;
                    case SurveyType::DESCRIPTIVE:
                        $attempted_data = $survey_attempt_data[$question_id];
                        $answers['textarea_'.$question_id] = $attempted_data->other_text;
                        break;

                    default:
                        Log::error(trans('survey.error_question_type'));
                        break;
                }
            }
            $this->surveyViewReports($survey_id, $answers, $packet_slug, 0);
        } catch(Exception $e) {
            Log::error($e->getMessage() . ' at line '. $e->getLine(). ' in file '. $e->getFile());
            return redirect('survey/view-reports/'.$packet_slug.'/'.$survey_id)->with('error', trans('survey.error_message'));
        }
    }

    public function surveyViewReports($survey_id, $answers, $packet_slug, $success = 0) {
        //Get survey question details.
        $data = $this->surveyQuestionDetails($survey_id);
        $survey = array_get($data, 'survey');
        $survey_question_data = array_get($data, 'survey_questions', []);
        $this->layout->pagetitle = trans('survey.survey'). ' | ' . ucwords($survey->survey_title);
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.survey.survey_reports')
                                    ->with(
                                        [
                                            'survey' => $survey,
                                            'survey_questions' => $survey_question_data,
                                            'survey_answer' => $answers,
                                            'packet_slug' => $packet_slug,
                                            'success' => $success
                                        ]
                                    );
    }

    public function putEntryInToOca($survey_id)
    {
        $user_id = (int)Auth::user()->uid;
        $survey = $this->survey_service->getSurveyByIds($survey_id)->first();
        $post_id = $survey->post_id;
        $packet = $this->post_service->getPacketByID((int)$post_id);
        $feed_slug = array_get($packet, 'feed_slug');
        $program = $this->program_service->getProgramBySlug('content_feed', $feed_slug);
        $channelId = $program->program_id;
        $isExists = $this->overall_channel_analytic_repo->isExists($channelId, $user_id);
        $postCountChannel = $this->post_service->getAllPacketsByFeedSlug($feed_slug, 'ACTIVE')->count();
        $returnFlag = true;
        $isViewedEle = false;
        $completion = 0;
        $postCompletion = [];
        $itemDetails = [];
        $postKey = 'p_' . $post_id;
        $viewedElement = 'survey_' . $survey_id;
        $countEle = 1;
        $postElement = [];
        if (isset($packet['elements']) && !empty($packet['elements'])) {
            foreach ($packet['elements'] as $element) {
                $postElement[] = $element['type'] . '_' . $element['id'];
            }
            $countEle = count($postElement);
        }
        $data = [];
        $data['updated_at'] = time();
        $data['user_id'] = $user_id;
        $data['channel_id'] = $channelId;
        $data['post_count'] = $postCountChannel;

        //Record exists in overall channel analytic
        if (!is_null($isExists) || !empty($isExists)) {
            $existsPostCompletion = $isExists->post_completion;
            $existsItemDetails = $isExists->item_details;
            if (isset($existsItemDetails[$postKey])) {
                $tempPostEleRaw = $existsItemDetails[$postKey];
                $tempPostEle = array_unique($tempPostEleRaw);
                if (in_array($viewedElement, $tempPostEle)) {
                    $isViewedEle = true;
                }
                $tempPostEle[] = $viewedElement;
                $tempPostEle = array_unique($tempPostEle);
                $viewedCount = count(array_intersect($tempPostEle, $postElement));
                $existsPostCompletion[$postKey] = round(
                    ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                    2
                );
                $existsItemDetails[$postKey] = $tempPostEle;
            } else {
                $tempPostEle = [];
                $tempPostEle[] = $viewedElement;
                $viewedCount = count(array_intersect($tempPostEle, $postElement));

                $existsPostCompletion[$postKey] = $postCompletion[$postKey] = round(
                    (($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100),
                    2
                );
                $existsItemDetails[$postKey] = $itemDetails[$postKey] = $tempPostEle;
            }
            $completion = round(
                (array_sum(array_values($existsPostCompletion))) /
                (($postCountChannel > 1) ? $postCountChannel : 1),
                2
            );

            $data['item_details'] = $existsItemDetails;
            $data['post_completion'] = $existsPostCompletion;
            $data['completion'] = $completion;
            if ($data['completion'] >= 100) {
                if (isset($isExists->completed_at) && !empty($isExists->completed_at) && !$isViewedEle) {
                    $data['completed_at'] = $isExists->completed_at;
                    $data['completed_at'][] = time();
                } else {
                    $data['completed_at'] = [time()];
                }
            }
            $res = $this->overall_channel_analytic_repo->updateData(
                $data,
                $data['channel_id'],
                $data['user_id']
            );
            if (!$res) {
                $returnFlag = false;
            }
        } else {
            //No record in overall channel analytic
            $tempPostEle = [];
            $tempPostEle[] = $viewedElement;
            $viewedCount = count(array_intersect($tempPostEle, $postElement));
            //post_completion
            $postCompletion[$postKey] = round(
                ($viewedCount / (($countEle > 1) ? $countEle : 1)) * 100,
                2
            );
            //item_details
            $itemDetails[$postKey] = $tempPostEle;
            //completion
            $completion = round(
                (array_sum(array_values($postCompletion))) /
                (($postCountChannel > 1) ? $postCountChannel : 1),
                2
            );


            $data['item_details'] = $itemDetails;
            $data['post_completion'] = $postCompletion;
            $data['completion'] = $completion;
            if ($data['completion'] >= 100) {
                $data['completed_at'] = [time()];
            }
            $data['created_at'] = time();
            $res = $this->overall_channel_analytic_repo->insertData($data);
            if (!$res) {
                $returnFlag = false;
            }
        }
        return $returnFlag;
    }
}
