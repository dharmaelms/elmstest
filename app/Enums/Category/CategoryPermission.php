<?php

namespace App\Enums\Category;

use App\Enums\BaseEnum;

abstract class CategoryPermission extends BaseEnum
{
    const LIST_CATEGORY = "list-category";

    const VIEW_CATEGORY = "view-category";

    const ADD_CATEGORY = "add-category";

    const EDIT_CATEGORY = "edit-category";

    const DELETE_CATEGORY = "delete-category";

    const ASSIGN_CHANNEL = "assign-channel";
}
