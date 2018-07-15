<?php

namespace App\Http\Validators\Quiz;

use App\Http\Validators\BaseValidator;
use App\Model\Quiz;
use App\Model\Section;
use Validator;

class QuizValidator extends BaseValidator
{
    private static function extendQuizValidator()
    {
        Validator::extend("check_is_section_editable", function ($attribute, $isSectionsEnabled, $parameters) {
            $quiz = Quiz::getQuizByCustomId($parameters[0]);
            $isSectionsEnabledEditable = true;
            if ($isSectionsEnabled === "TRUE") {
                $isSectionsEnabledEditable = !(isset($quiz->questions) && !empty($quiz->questions));
            } else {
                $sections = Section::getSectionInQuiz($quiz->quiz_id);
                if (isset($sections) && is_array($sections)) {
                    foreach ($sections as $section) {
                        if (isset($section->questions) && !empty($section->questions)) {
                            $isSectionsEnabledEditable = false;
                            break;
                        }
                    }
                }
            }

            return $isSectionsEnabledEditable;
        });

        Validator::extend("check_slug", function ($attribute, $value, $parameters) {
            $exists = Quiz::where('type', '=', 'QUESTION_GENERATOR')->where('status', '!=', 'DELETED')->where('slug', '=', $parameters[0])->count();

            if ($exists) {
                return false;
            }
            return true;
        });
        Validator::extend("check_slug_edit", function ($attribute, $value, $parameters) {
            $qg_id[] = $parameters[1];
            $exists = Quiz::where('type', '=', 'QUESTION_GENERATOR')->where('slug', '=', $parameters[0])->whereNotIn('_id', $qg_id)->where('status', '!=', 'DELETED')->count();

            if ($exists) {
                return false;
            }
            return true;
        });
    }

    public static function getQuizValidator($quizType, $context, $input, $validatorOptions = [], $quizData = [])
    {
        $rules = [];
        $customAttributes = [];
        $options = [];
        self::extendQuizValidator();
        switch ($quizType) {
            case "QUIZ":
                break;
            case "QUESTION_GENERATOR":
                $qg_slug = Quiz::getQuizSlug($input['r-q-g-name']);
                $rules["r-q-g-name"] = "required|min:3|max:512|check_slug:" . $qg_slug;
                $rules["r-q-g-total-question-limit"] = "required|numeric|min:1";
                $rules["r-q-g-display-end-date"] = "valid_end_date:{$input["r-q-g-display-start-date"]}";

                if ($context === "edit") {
                    $tempData = $qg_slug . ',' . $input['q-g-uid'];
                    $rules["r-q-g-enable-sections"] = "check_is_section_editable:{$quizData["quiz_id"]}";
                    $rules["r-q-g-name"] = "required|check_slug_edit:" . $tempData;
                }

                $customAttributes["r-q-g-name"] = "name";
                $customAttributes["r-q-g-total-question-limit"] = "total question limit";
                $customAttributes["r-q-g-display-start-date"] = "Display start date";
                $customAttributes["r-q-g-display-end-date"] = "Display end date";

                $options["custom_attributes"] = $customAttributes;
                break;
        }

        return parent::getValidator($input, $rules, $options);
    }
}
