<?php

namespace App\Enums\Assignment;

use App\Enums\BaseEnum;

/**
 * Define permissions for Assignment module
 * Class Permission
 * @package App\Enums\Assignment
 */
abstract class AssignmentPermission extends BaseEnum
{
    const LIST_ASSIGNMENT = "list-assignment";

    const ADD_ASSIGNMENT = "add-assignment";

    const EDIT_ASSIGNMENT = "edit-assignment";

    const DELETE_ASSIGNMENT = "delete-assignment";

    const EXPORT_ASSIGNMENT = "export-assignment";

    const REPORT_ASSIGNMENT = "report-assignment";

    const ASSIGNMENT_ASSIGN_USER = "assignment-assign-user";

    const ASSIGNMENT_ASSIGN_USER_GROUP = "assignment-assign-user-group";
}
