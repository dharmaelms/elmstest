<?php

namespace App\Enums\ManageSite;

use App\Enums\BaseEnum;

/**
 * Define permissions for manage site module
 * Class Permission
 * @package App\Enums\ManageSite
 */
abstract class ManageSitePermission extends BaseEnum
{
    const LIST_FAQ = "list-faq";

    const ADD_FAQ = "add-faq";
    
    const EDIT_FAQ = "edit-faq";

    const DELETE_FAQ = "delete-faq";
    
    const VIEW_FAQ = "view-faq";

    const LIST_STATICPAGE = "list-staticpage";

    const ADD_STATICPAGE = "add-staticpage";

    const EDIT_STATICPAGE = "edit-staticpage";

    const DELETE_STATICPAGE = "delete-staticpage";

    const VIEW_STATICPAGE = "view-staticpage";

    const SITE_CONFIGURATION = "site-configuration";

    const CUSTOM_FIELDS = "custom-fields";

    const MANAGE_ATTRIBUTE = "manage-attribute";

    const LIST_NEWSLETTER = "list-newsletter";

    const DELETE_NEWSLETTER = "delete-newsletter";

    const EXPORT_NEWSLETTER = "export-newsletter";

    const CONFIGURATION = "configuration";

    const MANAGE_CACHE = "manage-cache";

    const LIST_SITESETTING = "list-sitesetting";

    const EDIT_SITESETTING = "edit-sitesetting";
}
