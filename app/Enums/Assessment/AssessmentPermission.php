<?php

namespace App\Enums\Assessment;

use App\Enums\BaseEnum;

/**
 * Define permissions for assessment module
 * Class Permission
 * @package App\Enums\Assessment
 */
abstract class AssessmentPermission extends BaseEnum
{
    const LIST_QUIZ = "list-quiz";

    const ADD_QUIZ = "add-quiz";

    const VIEW_QUIZ = "view-quiz";

    const EDIT_QUIZ = "edit-quiz";

    const DELETE_QUIZ = "delete-quiz";

    const IMPORT_QUIZ = "import-quiz";

    const EXPORT_QUIZ = "export-quiz";

    const QUIZ_ASSIGN_CHANNEL = "quiz-assign-channel";

    const QUIZ_ASSIGN_USER = "quiz-assign-user";

    const QUIZ_ASSIGN_USER_GROUP = "quiz-assign-user-group";

    const LIST_QUESTION_BANK = "list-question-bank";

    const ADD_QUESTION_BANK = "add-question-bank";

    const EDIT_QUESTION_BANK = "edit-question-bank";

    const DELETE_QUESTION_BANK = "delete-question-bank";

    const IMPORT_QUESTION_BANK = "import-question-bank";

    const EXPORT_QUESTION_BANK = "export-question-bank";

    const ADD_QUESTION = "add-question";

    const EDIT_QUESTION = "edit-question";

    const DELETE_QUESTION = "delete-question";
}
