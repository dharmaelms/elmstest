<?php

namespace App\Model\Quiz;

use App\Enums\Quiz\QuizType;
use App\Exceptions\Quiz\NoQuizAssignedException;
use App\Exceptions\Quiz\QuizNotFoundException;
use App\Model\Quiz;
use App\Model\QuizAttempt;
use Auth;
use Carbon\Carbon;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Collection;
use MongoDate;

/**
 * Class QuizRepository
 *
 * @package App\Model\Quiz
 */
class QuizRepository implements IQuizRepository
{
    /**
     * {@inheritdoc}
     */
    public function getUserQuizzes($page, $limit, $quiz_ids)
    {
        $active_quizzes = $this->getActiveQuizzes($quiz_ids);
        
        //Get current page form url e.g. &page=6
        $currentPage = $page;

        //Create a new Laravel collection from the array data
        $collection = new Collection($active_quizzes);

        //Define how many items we want to be visible in each page
        $perPage = $limit;

        //Slice the collection to get the items to display in current page
        $searchResults = $collection->slice($currentPage * $perPage, $perPage)->all();

        //Create our paginator and pass it to the view
        return new LengthAwarePaginator($searchResults, count($collection), $perPage);
    }

    /**
     * {@inheritdoc}
     */
    public function getAllQuizzesAssignedToUser()
    {
        $all_quiz_ids = Quiz::userQuizRel(true);
        $nonSequenceQuizzes = array_diff($all_quiz_ids['quiz_list'], $all_quiz_ids['seq_quizzes']);
        if (empty($nonSequenceQuizzes)) {
            throw new NoQuizAssignedException();
        } else {
            return $nonSequenceQuizzes;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getAttemptedQuizzes($quiz_ids, $column = ['quiz_id'])
    {
        $attempted = QuizAttempt::where('user_id', '=', (int)Auth::user()->uid)
            ->whereIn('quiz_id', $quiz_ids)
            ->where('status', 'CLOSED')
            ->get($column);
        return $attempted;
    }

    /**
     * {@inheritdoc}
     */
    public function getActiveQuizzes($quiz_ids)
    {
        $column = ['quiz_id', 'quiz_name', 'questions', 'start_time', 'end_time', 'duration'];
        $quiz_ids = array_map('intval', array_unique($quiz_ids));
        $attempted = $this->getAttemptedQuizzes($quiz_ids);
        $active = array_diff($quiz_ids, array_unique($attempted->lists('quiz_id')->all()));
        $quizzes = $this->getQuizEndsToday($active, $column);
        $practice_quizzes = $this->getQuizzesWithNoEndTime($active, $column);
        $quizzes = $quizzes->merge($practice_quizzes);
        $upcoming_quizzes = $this->getQuizzesEndsFromTomorrow($active, $column);
        $quizzes = $quizzes->merge($upcoming_quizzes);
        if ($quizzes->isEmpty()) {
            throw new QuizNotFoundException;
        }
        return $quizzes;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizEndsToday($quiz_ids, $column = [])
    {
        $today_quizzes = Quiz::where('end_time', '>=', Carbon::now(Auth::user()->timezone)->timestamp)
            ->where('end_time', '<=', Carbon::tomorrow(Auth::user()->timezone)->timestamp - 1)
            ->where('status', 'ACTIVE')
            ->whereIn('quiz_id', $quiz_ids)
            ->orderBy('end_time', 'ASC')
            ->get($column);
        return $today_quizzes;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizzesWithNoEndTime($quiz_ids, $column = [])
    {
        $quizzes_no_time = Quiz::where('end_time', '=', 0)
            ->where('status', 'ACTIVE')
            ->whereIn('quiz_id', $quiz_ids)
            ->orderBy('created_at', 'DESC')
            ->get($column);
        return $quizzes_no_time;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizzesEndsFromTomorrow($quiz_ids, $column = [])
    {
        $upcoming_quizzes = Quiz::where('end_time', '>=', Carbon::tomorrow(Auth::user()->timezone)->timestamp)
            ->where('status', 'ACTIVE')
            ->whereIn('quiz_id', $quiz_ids)
            ->orderBy('end_time', 'ASC')
            ->get($column);
        return $upcoming_quizzes;
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizzes($quiz_ids, $start, $limit, $columns = [])
    {
        $quizzes = Quiz::whereIn('quiz_id', $quiz_ids)
            ->where('status', 'ACTIVE')
            ->skip((int)$start)
            ->limit((int)$limit)
            ->get($columns);
        if ($quizzes->isEmpty()) {
            throw new QuizNotFoundException;
        }
        return $quizzes;
    }

    /**
     * {@inheritdoc}
     */
    public function paginateData($data, $page, $limit)
    {
        if (!$data->isEmpty()) {
            // $start = ($page * $limit) - $limit;
            // $columns = ['quiz_id', 'quiz_name', 'questions', 'start_time', 'end_time', 'duration'];
            // $result = $this->quiz_repository->getQuizzes($active_quizzes, $start, $limit, $columns);
            // return $result;
            //Get current page form url e.g. &page=6
            $currentPage = $page - 1;

            //Create a new Laravel collection from the array data
            $collection = new Collection($data);

            //Define how many items we want to be visible in each page
            $perPage = $limit;

            //Slice the collection to get the items to display in current page
            $currentPageSearchResults = $collection->slice($currentPage * $perPage, $perPage)->all();

            //Create our paginator and pass it to the view
            $paginatedSearchResults = new LengthAwarePaginator($currentPageSearchResults, count($collection), $perPage);
            if (empty($paginatedSearchResults->items())) {
                throw new QuizNotFoundException;
            }
            return $paginatedSearchResults;
        } else {
            throw new QuizNotFoundException;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function getQuizzesByUsername($usernames = [])
    {
        return Quiz::whereIn('created_by', $usernames)
                ->where('status', '=', 'ACTIVE')
                ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function activeQuizzes()
    {
        return Quiz::active()->get();
    }

    /**
     * {@inheritdoc}
     */
    public function find($quiz_id)
    {
        //TODO: use scopeActive instead of where condition for status
        return Quiz::where('quiz_id', (int)$quiz_id)->where('status', 'ACTIVE')->first();
    }
    /**
     * @inheritdoc
     */
    public function getQuizzesByIds($quiz_ids, $reminder_filter)
    {
        $type_general = array_get($reminder_filter, 'general', 'off') == 'on' ? 'GENERAL_WITHOUT_PRATICE' : '';
        $type_practice = array_get($reminder_filter, 'general_practice', 'off') == 'on' ? 'GENERAL_WITH_PRACTICE' : '';
        $type_qg = array_get($reminder_filter, 'question_generator', 'off') == 'on' ? 'QUESTION_GENERATOR' : '';
        $query =  Quiz::whereIn('quiz_id', array_values($quiz_ids));
        $query->Where(function ($inqe) use ($type_general, $type_practice, $type_qg) {
            if ($type_general == 'GENERAL_WITHOUT_PRATICE') {
                $inqe->orWhere('practice_quiz', false);
            }
            if ($type_practice == 'GENERAL_WITH_PRACTICE') {
                $inqe->orWhere('practice_quiz', true);
            }
            if ($type_qg == 'QUESTION_GENERATOR') {
                $inqe->orWhere('type', 'QUESTION_GENERATOR');
            }
        });
        $query->where('is_production', 1);
        return $query->get(['quiz_id', 'quiz_name', 'type', 'end_time']);
    }


    /**
     * @inheritdoc
     */
    public function getAboutExpireQuizzes($date, $reminder_filter)
    {
        $start = new \MongoDB\BSON\UTCDateTime($date[0]);
        $end = new \MongoDB\BSON\UTCDateTime($date[1]);
        $type_general = array_get($reminder_filter, 'general', 'off') == 'on' ? 'GENERAL_WITHOUT_PRATICE' : '';
        $type_practice = array_get($reminder_filter, 'general_practice', 'off') == 'on' ? 'GENERAL_WITH_PRACTICE' : '';
        $type_qg = array_get($reminder_filter, 'question_generator', 'off') == 'on' ? 'QUESTION_GENERATOR' : '';
        $query = Quiz::where('status', '=', 'ACTIVE');
        $query->where(function ($q) use ($start, $end, $date) {
            $q->whereBetween('end_time', $date)
                ->orwhereBetween('end_time', [$start, $end]);
        });
        $query->Where(function ($inqe) use ($type_general, $type_practice, $type_qg) {
            if ($type_general == 'GENERAL_WITHOUT_PRATICE') {
                $inqe->orWhere('practice_quiz', false);
            }
            if ($type_practice == 'GENERAL_WITH_PRACTICE') {
                $inqe->orWhere('practice_quiz', true);
            }
            if ($type_qg == 'QUESTION_GENERATOR') {
                $inqe->orWhere('type', 'QUESTION_GENERATOR');
            }
        });
        $query->where('is_production', 1);
        return $query->get([
            'quiz_id',
            'relations.active_user_quiz_rel',
            'relations.active_usergroup_quiz_rel',
            'relations.feed_quiz_rel',
            'end_time',
            'quiz_name',
            'type'
        ]);
    }

    /**
     * @inheritdoc
     */
    public function getNotAttemptedQuizzes($quiz_id, $user_ids)
    {
        if (!empty($user_ids)) {
            return QuizAttempt::whereIn('user_id', $user_ids)
                ->where('quiz_id', '=', (int)$quiz_id)
                ->where('status', '!=', 'CLOSED')
                ->get(['user_id', 'quiz_id'])
                ->toArray();
        }
        return ;
    }

    /**
     * @inheritdoc
     */
    public function getQuizChannel($channelId)
    {
        return Quiz::where('relations.feed_quiz_rel.' . $channelId, 'exists', true)
                    ->where('type', '!=', 'QUESTION_GENERATOR')->get();
    }

        /**
     * {@inheritdoc}
     */
    public function getChannelRelation()
    {
        return Quiz::where('relations.feed_quiz_rel', 'exists', true)
            ->get(['quiz_id', 'relations.feed_quiz_rel'])
            ->toArray();
    }

    /**
     * {@inheritdoc}
     */
    public function getUserRelQuizzes()
    {
        return Quiz::where(function ($query) {
            $query->orWhere('relations.active_user_quiz_rel.0', 'exists', true)
                ->orWhere('relations.active_usergroup_quiz_rel.0', 'exists', true);
        })
            ->where('type', '!=', QuizType::QUESTION_GENERATOR)
            ->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findQuizzesByQuizids($quiz_ids, $order_by = [])
    {
        $query = Quiz::whereIn('quiz_id', $quiz_ids)
            ->where('type', '!=', QuizType::QUESTION_GENERATOR);
        if (!empty($order_by)) {
            $query->orderBy(array_keys($order_by)[0], $order_by[array_keys($order_by)[0]]);
        }
        return $query->get();
    }

    /**
     * {@inheritdoc}
     */
    public function findByAttribute($field, $value)
    {
        return Quiz::where($field, $value)->first();
    }

    /**
     * @inheritdoc
     */
    public function getQuizDataUsingIDS($ids)
    {
        return Quiz::whereIn('quiz_id', $ids)->get()->toArray();
    }

    /**
     * @inheritdoc
     */
    public function countActiveQuizzes($quiz_ids)
    {
        if (!empty($quiz_ids)) {
            return Quiz::whereIn('quiz_id', $quiz_ids)->where('status', 'ACTIVE')->count();
        } else {
            return Quiz::where('status', 'ACTIVE')->count();
        }
    }
}
