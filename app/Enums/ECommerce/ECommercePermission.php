<?php

namespace App\Enums\ECommerce;

use App\Enums\BaseEnum;

abstract class ECommercePermission extends BaseEnum
{
    const LIST_ORDER = "list-order";

    const LIST_PROMO_CODE = "list-promo-code";

    const ADD_PROMO_CODE = "add-promo-code";

    const EDIT_PROMO_CODE = "edit-promo-code";

    const DELETE_PROMO_CODE = "delete-promo-code";

    const EXPORT_PROMO_CODE = "export-promocode-code";

    const VIEW_ORDER = "view_order";

    const EDIT_ORDER = "edit_order";

    const EXPORT_ORDER = "export_order";
}
