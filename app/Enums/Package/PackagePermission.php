<?php

namespace App\Enums\Package;

use App\Enums\BaseEnum;

abstract class PackagePermission extends BaseEnum
{
    const ADD_PACKAGE = "add-package";

    const LIST_PACKAGES = "list-packages";

    const EDIT_PACKAGE = "edit-package-details";

    const VIEW_PACKAGE_DETAILS = "view-package-details";

    const DELETE_PACKAGE = "delete-package";

    const MANAGE_PACKAGE_CHANNELS = "manage-package-channels";

    const MANAGE_PACKAGE_USERS = "manage-package-users";

    const MANAGE_PACKAGE_USER_GROUPS = "manage-package-user-groups";

    const MANAGE_PACKAGE_CATEGORIES = "manage-package-categories";

    const MANAGE_PACKAGE_SUBSCRIPTIONS = "manage-package-subscriptions";

    const MANAGE_PACKAGE_TABS = "manage-package-tabs";

    const EXPORT_PACKAGE_WITH_USERS = "export-package-with-users";

    const EXPORT_PACKAGE_WITH_USER_GROUPS = "export-package-with-user-groups";
}
