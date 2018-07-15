<?php

namespace App\Enums\Program;

use App\Enums\BaseEnum;

abstract class ChannelPermission extends BaseEnum
{
    const ADD_CHANNEL = "add-channel";

    const VIEW_CHANNEL = "view-channel";

    const EDIT_CHANNEL = "edit-channel";

    const DELETE_CHANNEL = "delete-channel";

    const LIST_CHANNEL = "list-channel";

    const MANAGE_CHANNEL_POST = "manage-channel-post";

    const MANAGE_CHANNEL_QUESTION = "manage-channel-question";

    const MANAGE_CHANNEL_ACCESS_REQUEST = "manage-channel-access-request";

    const CHANNEL_ASSIGN_CATEGORY = "channel-assign-category";

    const CHANNEL_ASSIGN_USER = "channel-assign-user";

    const CHANNEL_ASSIGN_USER_GROUP = "channel-assign-user-group";

    const EXPORT_CHANNEL = "export-channel";
}
