<?php

namespace App\Enums\RolesAndPermissions;

use App\Enums\BaseEnum;

abstract class RolePermission extends BaseEnum
{
    const LIST_ROLE = "list-role";

    const ADD_ROLE = "add-role";

    const EDIT_ROLE = "edit-role";

    const DELETE_ROLE = "delete-role";
}
