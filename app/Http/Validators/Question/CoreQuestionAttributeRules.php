<?php

namespace App\Http\Validators\Question;

abstract class CoreQuestionAttributeRules
{
    // Defines global validation rules for question
    private static $coreRules = [
        "question_text" => "required",
        "default_mark" => "required|integer|min:0",
        "difficulty_level" => "required|in:easy,medium,difficult"
    ];

    // Defines custom error messages for question global validation rules.
    private static $coreRuleErrorMessages = [];

    /**
     * Gets global validation rules and error messages to add question
     * @return [array] [rules and messages]
     */
    protected static function getAddQuestionRules()
    {
        $rules = self::$coreRules;
        $rules["question_bank"] = "required|not_in:null|numeric";
        $messages = self::$coreRuleErrorMessages;
        return ["rules" => $rules, "messages" => $messages];
    }

    /**
     * Gets global validation rules and error messages to edit question
     * @return [array] [rules and messages]
     */
    protected static function getEditQuestionRules()
    {
        $rules = self::$coreRules;
        $messages = self::$coreRuleErrorMessages;
        return ["rules" => $rules, "messages" => $messages];
    }

    //public abstract static function getValidator($for, $input);
}
