<?php

namespace App\Http\Validators\Question;

use Validator;

class DescriptiveAttributeRules extends CoreQuestionAttributeRules
{
    /**
     * Gets validation rules and messages to add descriptive type of question
     * @return [array] [rules and messages]
     */
    protected static function getAddQuestionRules()
    {
        return parent::getAddQuestionRules();
    }

    /**
     * Gets validation rules and messages to edit descriptive type of question
     * @return [array] [rules and messages]
     */
    protected static function getEditQuestionRules()
    {
        return parent::getEditQuestionRules();
    }

    public static function getValidator($for, $input)
    {
        $validator = null;
        if ($for === "add_question") {
            $addQuestionRules = self::getAddQuestionRules();
            $validator = Validator::make($input, $addQuestionRules["rules"], $addQuestionRules["messages"]);
        } elseif ($for === "edit_question") {
            $editQuestionRules = self::getEditQuestionRules();
            $validator = Validator::make($input, $editQuestionRules["rules"], $editQuestionRules["messages"]);
        }

        return $validator;
    }
}
