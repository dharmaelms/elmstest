<?php

namespace App\Enums\Module;

use App\Enums\BaseEnum;

/**
 * Defines modules available in the system
 * Class Module
 * @package App\Enums\RolesAndPermissions
 */
abstract class Module extends BaseEnum
{
    const USER = "user";

    const USER_GROUP = "user-group";

    const ROLE = "role";

    const CHANNEL = "channel";

    const PACKAGE = "package";

    const CATEGORY = "category";

    const COURSE = "course";

    const DAMS = "dams";

    const EVENT = "event";

    const ASSESSMENT = "assessment";

    const ANNOUNCEMENT = "announcement";

    const E_COMMERCE = "e-commerce";

    const REPORT = 'report';

    const FLASHCARD = 'flashcard';

    const MANAGE_SITE = 'manage-site';

    const HOME_PAGE = 'home-page';

    const COUNTRY = 'country';

    const ERP = 'erp';

    const SURVEY = 'survey';
    
    const ASSIGNMENT = 'assignment';
}
