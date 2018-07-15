<?php

namespace App\Enums\DAMS;

use App\Enums\BaseEnum;

abstract class DAMSPermission extends BaseEnum
{
    const LIST_MEDIA = "list-media";

    const ADD_MEDIA = "add-media";

    const VIEW_MEDIA = "view-media";

    const EDIT_MEDIA = "edit-media";

    const DELETE_MEDIA = "delete-media";
}
