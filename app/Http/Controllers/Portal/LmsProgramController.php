<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\SiteSetting;
use Auth;
use Request;

class LmsProgramController extends PortalBaseController
{
    public function __construct(Request $request)
    {
        // Stripping all html tags from the request body
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getMyCourses()
    {
        if (SiteSetting::module('General', 'moodle_courses') == "on") {
            $userid = Auth::user()->uid;
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.lmsprogram.mycourses')->with('userid', $userid);
        } else {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        
    }

    public function getMoreCourses()
    {
        if (SiteSetting::module('General', 'moodle_courses') == "on") {
            $userid = Auth::user()->uid;
            $username = Auth::user()->username;
            $setting = SiteSetting::module('Lmsprogram')->setting;
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->content = view($this->theme_path . '.lmsprogram.morecourses')
                                        ->with('userid', $userid)
                                        ->with('setting', $setting)
                                        ->with('username', $username);
        } else {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
    }
}
