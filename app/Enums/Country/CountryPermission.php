<?php

namespace App\Enums\Country;

use App\Enums\BaseEnum;

abstract class CountryPermission extends BaseEnum
{
    const LIST_COUNTRY = "list-country";

    const ADD_COUNTRY = "add-country";

    const EDIT_COUNTRY = "edit-country";
}
