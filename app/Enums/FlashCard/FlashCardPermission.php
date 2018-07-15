<?php

namespace App\Enums\FlashCard;

use App\Enums\BaseEnum;

abstract class FlashCardPermission extends BaseEnum
{
    const LIST_FLASHCARD = "list-flashcard";

    const ADD_FLASHCARD = "add-flashcard";

    const VIEW_FLASHCARD = "view-flashcard";

    const EDIT_FLASHCARD = "edit-flashcard";

    const DELETE_FLASHCARD = "delete-flashcard";

    const IMPORT_FLASHCARD = "import-flashcard";
}
