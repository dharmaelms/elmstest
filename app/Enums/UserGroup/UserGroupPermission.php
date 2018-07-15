<?php

namespace App\Enums\UserGroup;

use App\Enums\BaseEnum;

abstract class UserGroupPermission extends BaseEnum
{
    const LIST_USER_GROUP = "list-user-group";

    const VIEW_USER_GROUP = "view-user-group";

    const ADD_USER_GROUP = "add-user-group";

    const EDIT_USER_GROUP = "edit-user-group";

    const DELETE_USER_GROUP = "delete-user-group";

    const USER_GROUP_ASSIGN_USER = "user-group-assign-user";

}
