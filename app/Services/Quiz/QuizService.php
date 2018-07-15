<?php

namespace App\Services\Quiz;

use App\Enums\Program\ElementType;
use App\Exceptions\Program\NoProgramAssignedException;
use App\Exceptions\Quiz\QuizNotFoundException;
use App\Model\Common;
use App\Model\Email;
use App\Model\Post\IPostRepository;
use App\Model\Quiz;
use App\Model\Program;
use App\Model\Quiz\IQuizRepository;
use App\Model\UserGroup\Repository\IUserGroupRepository;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use Auth;
use Exception;
use Helpers;
use Log;
use Timezone;

/**
 * Class QuizService
 *
 * @package App\Services\Quiz
 */
class QuizService implements IQuizService
{
    /**
     * @var \App\Model\Post\IPostRepository
     */
    private $post_repository;

    /**
     * @var \App\Model\Quiz\IQuizRepository
     */
    private $quiz_repository;

    /**
     * @var \App\Model\UserGroup\Repository\IUserGroupRepository
     */
    private $usergroup_repository;

    /**
     * @var \App\Services\Post\IPostService
     */
    private $post_service;

    /**
     * @var \App\Services\Program\IProgramService
     */
    private $program_service;

    /**
     * QuizService constructor.
     * @param IQuizRepository $quiz_repository
     * @param IPostService $post_service
     */
    public function __construct(
        IPostRepository $post_repository,
        IQuizRepository $quiz_repository,
        IUserGroupRepository $usergroup_repository,
        IProgramService $program_service,
        IPostService $post_service
    ) {
        $this->post_repository = $post_repository;
        $this->quiz_repository = $quiz_repository;
        $this->usergroup_repository = $usergroup_repository;
        $this->program_service = $program_service;
        $this->post_service = $post_service;
    }

    /**
     * {@inheritdoc}
     * @throws \App\Exceptions\Program\ProgramNotFoundException
     */
    public function getQuizzes($page, $limit)
    {
        $data = [];
        $quizzes = $this->getAllQuizzesAssigned();
        $nonSequenceQuizzes = array_diff($quizzes['quiz_list'], $quizzes['seq_quizzes']);
        $result = $this->quiz_repository->getUserQuizzes($page - 1, $limit, $nonSequenceQuizzes);
        $qids = array_keys($result->groupBy('quiz_id')->toArray());
        $qids = array_map('intval', array_unique($qids));
        $feedQuizList = $quizzes['feed_quiz_list'];
        $allQuizzes = Quiz::whereIn('quiz_id', $qids)
            ->where('status', '=', 'ACTIVE')
            ->get();
        $replaceQDate = $this->replaceDates($feedQuizList, $allQuizzes);
        $replaceQDateCol = collect($replaceQDate);
        $replaceQDateCol = $replaceQDateCol->groupBy('quiz_id');
        foreach ($result->items() as $quiz) {
            $row = new \stdClass;
            $row->quiz_id = $quiz->quiz_id;
            $row->quiz_name = $quiz->quiz_name;
            $row->questions = count($quiz->questions);
            if (is_null($quiz->duration) || $quiz->duration == 0) {
                $row->duration = 0;
            } else {
                $row->duration = Helpers::secondsToTimeString($quiz->duration * 60);
            }
            $replaceSpecificQDate = isset($replaceQDateCol->get($quiz->quiz_id)[0]) ?
                $replaceQDateCol->get($quiz->quiz_id)[0] :
                [];
            if (!$quiz->start_time) {
                $row->start_time = 0;
            } elseif (isset($replaceSpecificQDate['start_time'])
                && $replaceSpecificQDate['start_time'] != 0
            ) {
                $row->start_time = (int)Timezone::convertToUTC(Timezone::convertFromUTC('@' . $replaceSpecificQDate['start_time'], Auth::user()->timezone, 'd-m-Y h:m:s'), Auth::user()->timezone, 'U');
            } else {
                $row->start_time = $quiz->start_time->timezone(Auth::user()->timezone)->timestamp;
            }

            if (isset($replaceSpecificQDate['end_time'])
                && $replaceSpecificQDate['end_time'] != 0
            ) {
                $row->end_time = (int)Timezone::convertToUTC(Timezone::convertFromUTC('@' . $replaceSpecificQDate['end_time'], Auth::user()->timezone, 'd-m-Y h:m:s'), Auth::user()->timezone, 'U');
            } elseif ($quiz->end_time != 0) {
                $row->end_time = $quiz->end_time->timezone(Auth::user()->timezone)->timestamp;
            } else {
                $row->end_time = 0;
            }
            $data[] = $row;
        }
        if (empty($data)) {
            throw new QuizNotFoundException();
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizzesByProgram($page, $limit, $slug)
    {
        $posts = $this->post_service->postDetailsBySlug([$slug]);
        $quiz_ids = [];
        foreach ($posts as $post) {
            if (isset($post->elements)) {
                foreach ($post->elements as $element) {
                    if ($element['type'] == 'assessment') {
                        $quiz_ids[] = $element['id'];
                    }
                }
            }
        }
        $active_quizzes = $this->quiz_repository->getActiveQuizzes(array_unique($quiz_ids));
        $result = $this->quiz_repository->paginateData($active_quizzes, $page, $limit);
        $qids = array_keys($result->groupBy('quiz_id')->toArray());
        $user_quiz_rel =  $this->getAllQuizzesAssigned();
        $qids = array_map('intval', array_unique($qids));
        $feedQuizList = $user_quiz_rel['feed_quiz_list'];
        $allQuizzes = Quiz::whereIn('quiz_id', $qids)
            ->where('status', '=', 'ACTIVE')
            ->get();
        $replaceQDate = $this->replaceDates($feedQuizList, $allQuizzes);
        $replaceQDateCol = collect($replaceQDate);
        $replaceQDateCol = $replaceQDateCol->groupBy('quiz_id');
        foreach ($result->items() as $quiz) {
            $row = new \stdClass;
            $row->quiz_id = $quiz->quiz_id;
            $row->quiz_name = $quiz->quiz_name;
            $row->questions = count($quiz->questions);
            if (is_null($quiz->duration) || $quiz->duration == 0) {
                $row->duration = 0;
            } else {
                $row->duration = Helpers::secondsToTimeString($quiz->duration * 60);
            }
            //replace dates with subscribed and channel date
            $replaceSpecificQDate = isset($replaceQDateCol->get($quiz->quiz_id)[0]) ?
                $replaceQDateCol->get($quiz->quiz_id)[0] :
                [];
            if (!$quiz->start_time) {
                $row->start_time = 0;
            } elseif (isset($replaceSpecificQDate['start_time'])
                && $replaceSpecificQDate['start_time'] != 0
            ) {
                $row->start_time = (int)Timezone::convertToUTC(Timezone::convertFromUTC('@' . $replaceSpecificQDate['start_time'], Auth::user()->timezone, 'd-m-Y h:m:s'), Auth::user()->timezone, 'U');
            } else {
                $row->start_time = $quiz->start_time->timezone(Auth::user()->timezone)->timestamp;
            }

            if (isset($replaceSpecificQDate['end_time'])
                && $replaceSpecificQDate['end_time'] != 0
            ) {
                $row->end_time = (int)Timezone::convertToUTC(Timezone::convertFromUTC('@' . $replaceSpecificQDate['end_time'], Auth::user()->timezone, 'd-m-Y h:m:s'), Auth::user()->timezone, 'U');
            } elseif ($quiz->end_time != 0) {
                $row->end_time = $quiz->end_time->timezone(Auth::user()->timezone)->timestamp;
            } else {
                $row->end_time = 0;
            }
            $data[] = $row;
        }
        if (empty($data)) {
            throw new QuizNotFoundException();
        }
        return $data;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizzesByUsername($usernames = [])
    {
        $results = $this->quiz_repository->getQuizzesByUsername($usernames);
        return $results->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getAllQuizzesAssigned()
    {
        if (!is_admin_role(Auth::user()->role)) {
            $assigned_quizzes = array_get(Auth::user(), 'attributes.relations.user_quiz_rel', []);
            $assigned_ug = array_get(Auth::user(), 'attributes.relations.active_usergroup_user_rel', []);
            if (!empty($assigned_ug)) {
                $usergroup_quizzes = $this->usergroup_repository->get(['ugid' => $assigned_ug])->map(function ($group) {
                    return array_get($group, 'attributes.relations.usergroup_quiz_rel', []);
                });
                $assigned_quizzes = array_merge($assigned_quizzes, array_flatten(array_filter($usergroup_quizzes->toArray())));
            }
        } else {
            $assigned_quizzes = $this->quiz_repository->activeQuizzes()->pluck('quiz_id')->all();
        }
        $program_quizzes = $this->getProgramQuizzes();
        $quizzes = array_unique(array_merge($assigned_quizzes, $program_quizzes['feed_quiz_list']), SORT_REGULAR);
        $quiz['direct_quizzes'] = array_unique($assigned_quizzes);
        $quiz['quiz_list'] = array_unique(array_merge($assigned_quizzes, array_flatten($program_quizzes['feed_quiz_list'])), SORT_REGULAR);
        return array_merge($quiz, $program_quizzes);
    }

    /**
     * {@inheritdoc}
     */
    public function getProgramQuizzes()
    {
        $quiz = ['feed_list' => [], 'feed_quiz_list' => [], 'seq_quizzes' => []];
        try {
            $program_slugs = $this->program_service->getUserProgramSlugs();
            if (!empty($program_slugs)) {
                $quiz['feed_list'] = $program_slugs;
                $packet_data = $this->post_repository->getQuizIdByProgramSlugs($program_slugs)->toArray();
                $array_quiz_ids = $sequential_access_quiz_ids = [];
                foreach ($packet_data as $value) {
                    $feed_slug = array_get($value, 'feed_slug');
                    $quiz_ids = iterator_to_array(array_get($value, 'quiz_ids', []));
                    $array_quiz_ids[$feed_slug][] = $quiz_ids;
                    if (array_get($value, 'sequential_access') == 'yes') {
                        $sequential_access_quiz_ids[] = iterator_to_array(array_get($value, 'quiz_ids', []));
                    }
                }
                $feed_quizzes = [];
                foreach ($array_quiz_ids as $key => $post_quizzes) {
                    $feed_quizzes[$key] = array_flatten($post_quizzes);
                }
                $quiz['feed_quiz_list'] = $feed_quizzes;
                $quiz['seq_quizzes'] = array_flatten($sequential_access_quiz_ids);
            }
        } catch (PostNotFoundException $e) {
            $quiz = ['feed_list' => [], 'feed_quiz_list' => [], 'seq_quizzes' => []];
        } catch (NoProgramAssignedException $e) {
            $quiz = ['feed_list' => [], 'feed_quiz_list' => [], 'seq_quizzes' => []];
        }
        return $quiz;
    }
    /**
     * @inheritdoc
     */
    public function getQuizzesByIds($quiz_ids, $reminder_filter)
    {
        return $this->quiz_repository->getQuizzesByIds($quiz_ids, $reminder_filter)->keyBy('quiz_id');
    }

    /**
     * @inheritdoc
     */
    public function getAboutExpireQuizzes($date, $reminder_filter)
    {
        return $this->quiz_repository->getAboutExpireQuizzes(
            [array_get($date, 'start', 0), array_get($date, 'end', 0)],
            $reminder_filter
        )->keyBy('quiz_id');
    }

    public function sendReminderEmailNotification($email_template_slug, $quiz_details)
    {
        try {
            // sending email to user
            $site_name = config('app.site_name');
            $to = $quiz_details['email_id'];
            $headers = "MIME-Version: 1.0\r\n";
            $headers .= "Content-type: text/html; charset=iso-8859-1\r\n";
            $headers .= 'From:' . $site_name . "\r\n";
            $base_url = config('app.url');
            $email_details = Email::getEmail($email_template_slug);
            $subject = $email_details[0]['subject'];
            $body = $email_details[0]['body'];
           
           /* General quizzes */
            if (empty($quiz_details['program_name']) && $quiz_details['quiz_type'] == "general") {
                $url = $base_url . '/assessment/detail/' . $quiz_details['quiz_id'];
            
                $quiz_name = '<a href="' .$url. '">'.$quiz_details['quiz_name'].'</a>';
                $support_email = config('mail.from');

                $subject_find = ['<REMINDER NAME>', '<QUIZ NAME>', '<SITE NAME>'];
                $subject_replace = [$quiz_details['reminder_type'], $quiz_details['quiz_name'], $site_name];
                $subject = str_replace($subject_find, $subject_replace, $subject);

                $find = [
                    '<FIRSTNAME>',
                    '<LASTNAME>',
                    '<SITE NAME>',
                    '<QUIZ NAME>',
                    '<QUIZ END DATE>'
                ];
                $replace = [
                    ucwords($quiz_details['firstname']),
                    ucwords($quiz_details['lastname']),
                    $site_name,
                    $quiz_name,
                    $quiz_details['quiz_end_time']
                ];
                $body = str_replace($find, $replace, $body);
            }


            /*Question generator quizzes*/
            if (empty($quiz_details['program_name']) && $quiz_details['quiz_type'] == "QUESTION_GENERATOR") {
                $url = $base_url . '/assessment/detail/' . $quiz_details['quiz_id'];

                $quiz_name = '<a href="' .$url. '">'.$quiz_details['quiz_name'].'</a>';
                $support_email = config('mail.from');

                $subject_find = ['<REMINDER NAME>', '<QUIZ NAME>', '<SITE NAME>'];
                $subject_replace = [$quiz_details['reminder_type'], $quiz_details['quiz_name'], $site_name];
                $subject = str_replace($subject_find, $subject_replace, $subject);

                $find = [
                    '<FIRSTNAME>',
                    '<LASTNAME>',
                    '<SITE NAME>',
                    '<QUIZ NAME>',
                    '<QUIZ END DATE>'
                ];
                $replace = [
                    ucwords($quiz_details['firstname']),
                    ucwords($quiz_details['lastname']),
                    $site_name,
                    $quiz_name,
                    $quiz_details['quiz_end_time']
                ];
                $body = str_replace($find, $replace, $body);
            }

            /*program quizzes */
            if (!empty($quiz_details['program_name'])) {
                $support_email = config('mail.from');

                $subject_find = ['<REMINDER NAME>', '<PROGRAM NAME>','<SITE NAME>'];
                $subject_replace = [$quiz_details['reminder_type'], $quiz_details['program_name'], $site_name];
                $subject = str_replace($subject_find, $subject_replace, $subject);

                $find = [
                    '<FIRSTNAME>',
                    '<LASTNAME>',
                    '<PROGRAM NAME>',
                    '<PROGRAM END DATE>',
                    '<SITE NAME>',
                    '<QUIZ ASSIGNED TO POST LIST>'
                ];
                $replace = [
                    ucwords($quiz_details['firstname']),
                    ucwords($quiz_details['lastname']),
                    $quiz_details['program_name'],
                    $quiz_details['program_end_time'],
                    $site_name,
                    implode("\n", $quiz_details['quiz_and_post_list'])
                    ];
                $body = str_replace($find, $replace, $body);
            }

            Common::sendMailHtml($body, $subject, $to);
        } catch (Exception $e) {
            Log::info('Quiz reminder email exception- file name '.$e->getFile().' Line no '.$e->getLine().' message '.$e->getMessage());
        }
    }

    /**
     * @inheritdoc
     */
    public function getQuizDataUsingIDS($ids)
    {
        return $this->quiz_repository->getQuizDataUsingIDS($ids);
    }

    public function replaceDates($feedQuizRel, $allQuizzes)
    {
        $uid = (int)Auth::user()->uid;
        $subscriped_channels = collect(array_get(Auth::user(), 'subscription', []));
        $feedSlugs = array_keys($feedQuizRel);
        $transDetails = $programDetails = Program::whereIn('program_slug', $feedSlugs)
            ->where('status', '=', 'ACTIVE')
            ->orderby('created_at', 'desc')
            ->get();
        $transDetailsGrouped = $transDetails->keyBy('program_slug');
        $programDetailsGrouped = $programDetails->keyBy('program_slug');
        $allQuizzesGrouped = $allQuizzes->keyBy('quiz_id');
        $replace_dates = [];
        foreach ($transDetailsGrouped as $progSlug => $transDetail) {
            $startDate = 0;
            $endDate = 0;
            if ($subscriped_channels->has($transDetail->program_id)) {
                $subscriped_channel = $subscriped_channels->get($transDetail->program_id);
                $startDate = array_get($subscriped_channel, 'start_time', 0);
                $endDate = array_get($subscriped_channel, 'end_time', 0);
            } else {
                $specProg = $programDetailsGrouped->get($progSlug);
                if (isset($specProg->program_startdate)
                    && $specProg->program_startdate != ""
                ) {
                    $startDate = $specProg->program_startdate->timestamp;
                }
                if (isset($specProg->program_enddate)
                    && $specProg->program_enddate != ""
                ) {
                    $endDate = $specProg->program_enddate->timestamp;
                }
            }
            if (($startDate != 0 || $endDate != 0) && !empty(array_get($feedQuizRel, $progSlug, []))
            ) {
                foreach ($feedQuizRel[$progSlug] as $quizId) {
                    $quizId = (int) $quizId;
                    if ($startDate != 0
                        &&  (
                                empty(array_get($replace_dates, $quizId, []))
                                || array_get($replace_dates, $quizId.'.start_time', $startDate) >= $startDate
                            )
                        ) {
                        $replace_dates[$quizId]['start_time'] = $startDate;
                    }
                    if ($endDate != 0
                        &&  (
                                empty(array_get($replace_dates, $quizId, []))
                                || array_get($replace_dates, $quizId.'.end_time', $endDate) <= $endDate
                            )
                        ) {
                        $replace_dates[$quizId]['end_time'] = $endDate;
                    }
                }
            }
        }
        $bulkAry = [];
        foreach ($allQuizzesGrouped as $eachQuiz) {
            $tempQuiz = [];
            if (!isset($eachQuiz['quiz_id'])) {
                continue;
            }
            $replace_date_quiz = array_get($replace_dates, $eachQuiz['quiz_id'], []);
            if (!empty($replace_date_quiz)) {
                $tempQuiz['end_time'] = array_get(
                    $replace_date_quiz,
                    'end_time',
                    is_object($eachQuiz['end_time']) ?
                        $eachQuiz['end_time']->timestamp : $eachQuiz['end_time']
                );
                $tempQuiz['start_time'] = array_get(
                    $replace_date_quiz,
                    'start_time',
                    is_object($eachQuiz['start_time']) ?
                        $eachQuiz['start_time']->timestamp : $eachQuiz['start_time']
                );
            } else {
                $tempQuiz['end_time'] = is_object($eachQuiz['end_time']) ?
                                        $eachQuiz['end_time']->timestamp : $eachQuiz['end_time'];
                $tempQuiz['start_time'] = is_object($eachQuiz['start_time']) ?
                                        $eachQuiz['start_time']->timestamp : $eachQuiz['start_time'];
            }
            $tempQuiz['quiz_name'] = $eachQuiz['quiz_name'];
            $tempQuiz['quiz_id'] = $eachQuiz['quiz_id'];
            $tempQuiz['is_score_display'] = array_get($eachQuiz, 'is_score_display', true);
            if (isset($eachQuiz['duration'])) {
                $tempQuiz['duration'] = $eachQuiz['duration'];
            }
            if (isset($eachQuiz['attempts'])) {
                $tempQuiz['attempts'] = $eachQuiz['attempts'];
            }
            if (isset($eachQuiz['practice_quiz'])) {
                $tempQuiz['practice_quiz'] = $eachQuiz['practice_quiz'];
            }
            if (isset($eachQuiz['type'])) {
                $tempQuiz['type'] = $eachQuiz['type'];
            }
            if (isset($eachQuiz['beta'])) {
                $tempQuiz['beta'] = $eachQuiz['beta'];
            }
            $bulkAry[$eachQuiz['quiz_id']] = $tempQuiz;
        }
        return array_values($bulkAry);
    }

    /**
     * @param  array $quiz_ids
     * @inheritdoc
     */
    public function countActiveQuizzes($quiz_ids)
    {
        return $this->quiz_repository->countActiveQuizzes($quiz_ids);
    }
}
