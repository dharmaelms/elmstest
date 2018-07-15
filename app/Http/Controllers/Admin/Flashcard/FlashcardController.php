<?php namespace App\Http\Controllers\Admin\Flashcard;

use Common;

class FlashcardController extends FlashcardBaseController
{
    public function __construct()
    {
        parent::__construct();
    }

    public function getList()
    {
        $crumbs = $this->flashcardBaseCrumbs;
        $data = [
            "pagetitle" => $this->flashcardPageDetails["flashcard-set-list"]["title"],
            "pagedescription" => $this->flashcardPageDetails["flashcard-set-list"]["description"],
            "pageicon" => $this->flashcardPageDetails["flashcard-set-list"]["icon"],
            "breadcrumbs" => Common::getBreadCrumbs($crumbs)
        ];

        return view("admin.theme.flashcard.flashcard_set_list", $data);
    }
}
