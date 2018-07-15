<?php

namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\ContactUs\IContactUsRepository;
use App\Model\SiteSetting;
use Request;
use Validator;

/**
 * Class ContactUsController
 * @package App\Http\Controllers\Portal
 */
class ContactUsController extends PortalBaseController
{
    /**
     * @var IContactUsRepository
     */
    protected $contact_us;

    /**
     * ContactUsController constructor.
     * @param Request $request
     * @param IContactUsRepository $contact_us
     */
    public function __construct(Request $request, IContactUsRepository $contact_us)
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
        $this->contact_us = $contact_us;
    }

    /**
     * Get contact us page
     */
    public function getIndex()
    {
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.common.contactus')
            ->with('sitessets', SiteSetting::module('Contact Us')->setting);
    }

    /**
     * Submit contact us form
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postIndex()
    {
        $rules = [
            'name' => 'required',
            'email' => 'required|email',
            'message' => 'required',
            'phone' => 'min:10|max:20|regex:/^([0-9-+ ])+$/',
            'mobile' => 'numeric|regex:/^([0-9]{10})/',
            'g-recaptcha-response' => 'required'
        ];

        $validation = Validator::make(Request::all(), $rules);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        } else {
            $this->contact_us->save(Request::all(), 'general');
            return redirect()->back()->with('success', 'Your Query submitted successfully.');
        }
    }

    /**
     * Submit course enquiry form
     *
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEnquiry()
    {
        $messages = [
            'g-recaptcha-response.required' => 'Please select CAPTCHA',
        ];
        $rules = [
            'name' => 'required|min:3|max:50|regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'email' => 'required|email|max:96',
            'message' => 'required',
            'mobile' => 'regex:/^([0-9+]{10,13})+$/',
            'g-recaptcha-response' => 'required'
        ];

        $validation = Validator::make(Request::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect()->back()->withInput()->withErrors($validation);
        } else {
            $this->contact_us->save(Request::all(), 'coursepage');
            return redirect()->back()->with('success', 'Your query submitted successfully.');
        }
    }
}
