<?php

namespace App\Enums\Survey;

use App\Enums\BaseEnum;

/**
 * Define permissions for Survey module
 * Class Permission
 * @package App\Enums\Survey
 */
abstract class SurveyPermission extends BaseEnum
{
    const LIST_SURVEY = "list-survey";

    const ADD_SURVEY = "add-survey";

    const EDIT_SURVEY = "edit-survey";

    const DELETE_SURVEY = "delete-survey";

    const EXPORT_SURVEY = "export-survey";

    const REPORT_SURVEY = "report-survey";

    const SURVEY_ASSIGN_USER = "survey-assign-user";

    const SURVEY_ASSIGN_USER_GROUP = "survey-assign-user-group";

    const LIST_SURVEY_QUESTION = "list-survey-question";

    const ADD_SURVEY_QUESTION = "add-survey-question";

    const EDIT_SURVEY_QUESTION = "edit-survey-question";

    const DELETE_SURVEY_QUESTION = "delete-survey-question";
}
