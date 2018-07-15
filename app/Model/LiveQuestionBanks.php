<?php
namespace App\Model;

use Moloquent;

class LiveQuestionBanks extends Moloquent
{
    protected $table = 'livequestionbanks';

    /**
     * function to get questionbank details in live database
     * @param  [type] $id [question id]
     * @return [type]     [description]
     */
    public static function findQuestionBankByColumn($name, $value)
    {
        $data = QuestionBank::where($name, '=', $value)
            ->where('status', '=', 'ACTIVE')->get();
        return $data;
    }

    /**
     * function used to insert question id to questionbank
     */
    public static function addQuestionsToQuestionBank($qbId, $qids)
    {
        return QuestionBank::where('question_bank_id', '=', $qbId)->push('questions', $qids, true);
    }
}
