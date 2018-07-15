<?php


namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Announcement;
use App\Model\Common;
use App\Model\Faq;
use App\Model\StaticPage;
use Auth;
use Input;

class PagesController extends PortalBaseController
{
    public function __construct()
    {
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.two_columnlayout_frontend';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getIndex($slug = null)
    {
        if (is_null($slug)) {
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            // $this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.common.home');
        } else {
            if ($slug == 'faq') {
                $crumbs = [
                    'Home' => '',
                    'Faq' => '',
                ];
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $faqs = Faq::getActiveFaq();
                $this->layout->pagetitle = 'Faq';
                $this->layout->metakeys = 'Faq';
                $this->layout->metadescription = 'Faq';
                $this->layout->content = view($this->theme_path . '.managesite.staticpage')->with('faqs', $faqs);
            } elseif ($slug == 'viewannuncement' && !is_null(Input::get('id'))) {
                $crumbs = [
                    'Home' => '',
                    'View Announcement' => '',
                ];

                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

                if (isset(Auth::user()->relations)) {
                    /*echo "<pre>";
                    print_r($specific_user_announce_id);
                    die;*/
                }
                $announcement = Announcement::getOneAnnouncement(Input::get('id'));
                $this->layout->content = view($this->theme_path . '.announcement.viewannuncement')->with('announcement', $announcement[0]);
                // $this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
                $this->layout->footer = view($this->theme_path . '.common.footer');
            } else {
                $page = StaticPage::getOneStaticPageforSlug($slug);
                $this->layout->pagetitle = $page[0]->title;
                $crumbs = [
                    'Home' => '',
                    $page[0]->title => '',
                ];
                $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
                $this->layout->metakeys = $page[0]->metakey;
                $this->layout->metadescription = $page[0]->meta_description;
                $this->layout->content = view($this->theme_path . '.managesite.staticpage')->with('staticpage', $page[0]);
            }
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            // $this->layout->leftsidebar = view($this->theme_path.'.common.leftsidebar');
            $this->layout->footer = view($this->theme_path . '.common.footer');
        }
    }
}
