<?php namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;

class LibraryManagementController extends PortalBaseController
{

    public function __construct()
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.two_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getIndex()
    {
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.library.listlibraries');
    }
}
