<?php

namespace App\Enums\Event;

use App\Enums\BaseEnum;

abstract class EventPermission extends BaseEnum
{
    const LIST_EVENT = "list-event";

    const ADD_EVENT = "add-event";

    const VIEW_EVENT = "view-event";

    const EDIT_EVENT = "edit-event";
    
    const DELETE_EVENT = "delete-event";

    const ASSIGN_CHANNEL = "assign-channel";

    const ASSIGN_USER = "assign-user";

    const ASSIGN_USER_GROUP = "assign-user-group";
}
