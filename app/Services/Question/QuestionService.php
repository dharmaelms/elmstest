<?php

namespace App\Services\Question;

use App\Exceptions\Question\QuestionBankNotFoundException;
use App\Exceptions\Question\QuestionBaseException;
use App\Exceptions\Question\QuestionNotFoundException;
use App\Model\Question\Repository\IQuestionBankRepository;
use App\Model\Question\Repository\IQuestionRepository;
use App\Model\Quiz;

/**
 * Class QuestionService
 * @package App\Services\Question
 */
class QuestionService implements IQuestionService
{
    /**
     * @var IQuestionBankRepository
     */
    private $question_bank_repository;

    /**
     * @var IQuestionRepository
     */
    private $question_repository;

    /**
     * QuestionService constructor.
     * @param IQuestionBankRepository $question_bank_repository
     * @param IQuestionRepository $question_repository
     */
    public function __construct(
        IQuestionBankRepository $question_bank_repository,
        IQuestionRepository $question_repository
    ) {
        $this->question_bank_repository = $question_bank_repository;
        $this->question_repository = $question_repository;
    }

    /**
     * @param $questionBank
     * @param $data
     * @return mixed
     */
    public function addQuestion($questionBank, $data)
    {
        $question = $this->question_repository->add($data);
        $this->question_bank_repository->assignQuestion($questionBank, $question);
        return $question;
    }

    /**
     * @param $id
     * @return array
     */
    public function getQuestion($id)
    {
        $tmpData = [];
        try {
            $question = $this->question_repository->find($id);
            $tmpData["data_flag"] = true;
            $tmpData["details"] = $question;
        } catch (QuestionNotFoundException $e) {
            $tmpData["data_flag"] = false;
            $tmpData["error_info"] = trans("admin/exception.{$e->getCode()}");
        } finally {
            return $tmpData;
        }
    }

    /**
     * @param $mediaId
     * @return mixed
     */
    public function getQuestionsByMedia($mediaId)
    {
        return $this->question_repository->getByMedia($mediaId);
    }

    /**
     * @param $customId
     * @return mixed
     */
    public function getQuestionByCustomId($customId)
    {
        $questionCollection = $this->question_repository->getByAttribute("question_id", $customId);
        return $questionCollection->first();
    }

    /**
     * @param $questionBankId
     * @param $questionId
     * @param $data
     * @return array
     */
    public function updateQuestion($questionBankId, $questionId, $data)
    {
        $tmpData = [];
        try {
            $question = $this->question_repository->update($questionId, $data);
            $tmpData["status_flag"] = true;
            $tmpData["details"] = $question;
        } catch (QuestionBaseException $e) {
            $tmpData["status_flag"] = false;
            $tmpData["error_info"] = trans("admin/exception.{$e->getCode()}");
        } finally {
            return $tmpData;
        }
    }

    /**
     * @param $questionBankId
     * @param $questionId
     * @return array
     */
    public function deleteQuestion($questionBankId, $questionId)
    {
        $status = [];
        try {
            $questionBank = $this->question_bank_repository->find($questionBankId);
            $question = $this->question_repository->find($questionId);
            if (Quiz::isQuestionAssignedToQuiz($question->question_id)) {
                //TODO: Vignesh: What is the error code that needs to be passed here? This is mandatory.
                throw new QuestionBaseException();
            }
            $this->question_repository->delete($questionId);
            $this->question_bank_repository->unassignQuestion($questionBank, $question);
            $status["flag"] = true;
        } catch (QuestionBaseException $e) {
            $status["flag"] = false;
            $status["error_info"] = trans("admin/exception.{$e->getCode()}");
        } finally {
            return $status;
        }
    }

    /**
     * @param $data
     */
    public function addQuestionBank($data)
    {
    }

    /**
     * @param $id
     * @return array
     */
    public function getQuestionBank($id)
    {
        $tmpData = [];
        try {
            $questionBank = $this->question_bank_repository->find($id);
            $tmpData["data_flag"] = true;
            $tmpData["details"] = $questionBank;
        } catch (QuestionBankNotFoundException $e) {
            $tmpData["data_flag"] = false;
            $tmpData["error_info"] = trans("admin/exception.{$e->getCode()}");
        } finally {
            return $tmpData;
        }
    }

    /**
     * @param int $start
     * @param string $orderBy
     * @param string $orderByDir
     * @param null $limit
     * @return mixed
     */
    public function getActiveQuestionBanks($start = 0, $orderBy = "created_at", $orderByDir = "desc", $limit = null)
    {
        return $this->question_bank_repository->getActiveQuestionBanks($start, $orderBy, $orderByDir, $limit);
    }

    public function getQuestionsText($question_ids)
    {
        return $this->question_repository->getQuestionsText($question_ids);
    }
}
