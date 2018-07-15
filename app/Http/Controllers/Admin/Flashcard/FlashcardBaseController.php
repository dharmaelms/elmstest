<?php namespace App\Http\Controllers\Admin\Flashcard;

use App\Http\Controllers\AdminBaseController;
use URL;

class FlashcardBaseController extends AdminBaseController
{
    protected $flashcardBaseCrumbs;

    protected $flashcardPageDetails;

    public function __construct()
    {
        $this->flashcardBaseCrumbs = [
            trans('admin/dashboard.dashboard') => URL::to("cp"),
            trans('admin/flashcards.flashcard') => URL::route("flashcard-set-list")
        ];

        $this->flashcardPageDetails = trans("admin/page_details.admin.flashcard");
    }
}
