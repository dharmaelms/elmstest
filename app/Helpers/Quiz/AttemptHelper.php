<?php
namespace App\Helpers\Quiz;

/**
 * class AttemptHelper
 * @package App\Model\Quiz
 */
class AttemptHelper
{
    public static function getQuestionStatus($details, $question_id)
    {
        $class = 'not-viewed';
        if (in_array($question_id, $details['viewed'])) {
            if (in_array($question_id, $details['answered'])) {
                if (in_array($question_id, $details['reviewed'])) {
                    $class = 'review-answered';
                } else {
                    $class = 'answered';
                }
            } else {
                if (in_array($question_id, $details['reviewed'])) {
                    $class = 'review-not-answered';
                } else {
                    $class = 'not-answered';
                }
            }
        }
        return $class;
    }
}
