<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;

class CategoryManagementController extends PortalBaseController
{
    public function __construct()
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.two_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }
}
