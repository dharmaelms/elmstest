<?php

namespace App\Enums\Course;

use App\Enums\BaseEnum;

abstract class CoursePermission extends BaseEnum
{
    const LIST_COURSE = "list-course";

    const VIEW_COURSE = "view-course";

    const ADD_COURSE = "add-course";

    const EDIT_COURSE = "edit-course";

    const DELETE_COURSE = "delete-course";

    const ASSIGN_CATEGORY = "assign-category";

    const COURSE_ASSIGN_USER = "course-assign-user";

    const MANAGE_COURSE_POST = "manage-course-post";

    const LIST_BATCH = "list-batch";

    const VIEW_BATCH = "view-batch";

    const ADD_BATCH = "add-batch";

    const EDIT_BATCH = "edit-batch";

    const DELETE_BATCH = "delete-batch";

    const MANAGE_BATCH_POST = "manage-batch-post";

    const BATCH_ASSIGN_USER = "batch-assign-user";

    const BATCH_ASSIGN_USER_GROUP = "batch-assign-user-group";

    const EXPORT_COURSE = "export-course";
}
