<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Common;
use App\Model\StaticPage;

class DisplayStaticPageController extends PortalBaseController
{
    public function __construct()
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getIndex()
    {
        $pages = StaticPage::getOnlyActivePage();
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer')->with('pages', $pages);
        $this->layout->content = view($this->theme_path . '.common.home');
    }

    public function getDynamicPage($key = null)
    {
        if ($key) {
            $pages = StaticPage::getOnlyActivePage();
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer')->with('pages', $pages);
            $page = StaticPage::getOneStaticPage($key);
            $this->layout->pagetitle = $page[0]->title;
            $this->layout->metakeys = $page[0]->metakey;
            $this->layout->metadescription = $page[0]->meta_description;
            $this->layout->content = view($this->theme_path . '.managesite.staticpage')->with('staticpage', $page[0]);
        } else {
            return parent::getError($this->theme, $this->theme_path);
        }
    }
}
