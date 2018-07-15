<?php

namespace App\Enums\User;

use App\Enums\BaseEnum;

abstract class UserPermission extends BaseEnum
{
    const LIST_USER = "list-user";

    const VIEW_USER = "view-user";

    const ADD_USER = "add-user";

    const EDIT_USER = "edit-user";

    const DELETE_USER = "delete-user";

    const IMPORT_USERS = "import-users";

    const EXPORT_USERS = "export-users";
}
