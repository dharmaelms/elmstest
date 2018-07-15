<?php

namespace App\Enums\Announcement;

use App\Enums\BaseEnum;

abstract class AnnouncementPermission extends BaseEnum
{
    const LIST_ANNOUNCEMENT = "list-announcement";

    const ADD_ANNOUNCEMENT = "add-announcement";

    const VIEW_ANNOUNCEMENT = "view-announcement";

    const EDIT_ANNOUNCEMENT = "edit-announcement";

    const DELETE_ANNOUNCEMENT = "delete-announcement";

    const ASSIGN_CHANNEL = "assign-channel";

    const ASSIGN_USER = "assign-user";

    const ASSIGN_USERGROUP = "assign-usergroup";

    const ASSIGN_MEDIA = "assign-media";
}
