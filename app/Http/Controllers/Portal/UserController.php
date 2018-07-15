<?php
namespace App\Http\Controllers\Portal;

use App\Http\Controllers\PortalBaseController;
use App\Model\Common;
use App\Model\Country;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\DimensionChannel;
use App\Model\DimensionUser;
use App\Model\MyActivity;
use App\Model\SiteSetting;
use App\Model\States;
use App\Model\User;
use App\Services\Playlyfe\IPlaylyfeService;
use Auth;
use Carbon;
use Hash;
use Imagick;
use Input;
use Redirect;
use Request;
use Session;
use Timezone;
use Validator;

class UserController extends PortalBaseController
{
    public function __construct(Request $request)
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        $this->middleware('auth');
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getIndex(IPlaylyfeService $playlyfe, ReportController $reportController)
    {
        if (SiteSetting::module('LHSMenuSettings', 'my_activity') != 'on') {
            return parent::getError($this->theme, $this->theme_path, 401);
        }
        $general = SiteSetting::module('General');
        $range = Input::get('range');
        $start_date = Carbon::today()->subDays(31)->timestamp;
        $end_date = Carbon::yesterday()->timestamp;
        if ($range) {
            $range = explode(' to ', $range);
            if ($range && is_array($range) && !empty($range) && count($range) > 1) {
                if (trim($range[0])) {
                    $start_date = (int)Timezone::convertToUTC($range[0], Auth::user()->timezone, "U");
                }
                if (trim($range[1])) {
                    $end_date = (int)Timezone::convertToUTC($range[1], Auth::user()->timezone, "U");
                }
            }
        }
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->header = view($this->theme_path . '.common.header');

        $activities = [];
        $user_id = Auth::user()->uid;
        $user_res = DimensionUser::isExist($user_id);
        $channel_names = [];
        $channel_ids = [];
        $cids = [];

        /********
         * Added by: Muniraju N.
         * Purpose : Passes the required playlyfe data.
         ********/
        $isPlaylyfeEnabled = $playlyfe->isPlaylyfeEnabled();
        $channnelIdName = [];
        if (isset($user_res) && !empty($user_res)) {
            $channel_ids = $user_res->channel_ids;
            if (isset($channel_ids) && !empty($channel_ids)) {
                $channel_details = DimensionChannel::getChannelsDetails($channel_ids);
                foreach ($channel_details as $key => $channel_detail) {
                    $channel_names[] = $channel_detail['channel_name'];
                    $cids[] = $channel_detail['channel_id'];
                    $channnelIdName[$channel_detail['channel_id']] = $channel_detail['channel_name'];
                }
            }
        }

        $cahnnelPerformance = $reportController->getSpecificChannelPerformanceTillDate();
        $this->layout->content = view($this->theme_path . '.user.myactivity')
            ->with('channel_names', $channel_names)
            ->with('channel_ids', $cids)
            ->with('cahnnelPerformance', $cahnnelPerformance)
            ->with('activities', $activities)
            ->with('start_date', $start_date)
            ->with('end_date', $end_date)
            ->with('channnelIdName', $channnelIdName)
            ->with('isPlaylyfeEnabled', $isPlaylyfeEnabled)
            ->with('general', $general);
    }

    public function getNextRecords($currenttab, $pageno)
    {
        $pageno = (int)$pageno;
        $record_perpage = SiteSetting::module('General', 'my_activities', 10);
        $activities = MyActivity::getActivitieswithPaginition(Auth::user()->uid, $record_perpage, $pageno, $currenttab);
        if (empty($activities)) {
            return 0;
        }
        $no = (int)$record_perpage * $pageno;
        //add the output...
        $output = '';
        foreach ($activities as $activity) {
            $no = $no + 1;
            $i = $activity['module'];
            if ($activity['module'] == 'general') {
                $output .= '
                    <p><div class="left-no">' . $no . ". " . '</div><div class="right-data">&nbsp;' . $activity['action'] . ' on ' . Timezone::convertFromUTC('@' . $activity['date'], Auth::user()->timezone, 'd-m-Y H:i:s') . '.</div>
                    </p>
                    ';
            } elseif ($activity['module'] == 'contentfeed') {
                $output .= '
                    <p><div class="left-no">' . $no . ". " . '</div><div class="right-data">&nbsp;<a href="' . $activity['url'] . '/myactivity">' . ucfirst(str_replace('_', ' ', $activity['action'])) . ' "<i>' . $activity['module_name'] . ' </i>" on ' . Timezone::convertFromUTC('@' . $activity['date'], Auth::user()->timezone, 'd-m-Y H:i:s') . '.</a></div></p>';
            } elseif ($activity['module'] == 'element') {
                $feedName = isset($activity['feed_name']) ? $activity['feed_name'] : '';
                $output .= '
                    <p><div class="left-no">' . $no . ". " . '</div><div class="right-data">&nbsp;<a href="' . $activity['url'] . '/myactivity">' . ucfirst(str_replace('_', ' ', $activity['action'])) . ' "<i>' . $activity['module_name'] . ' </i>" of "<i>' . $activity['packet_name'] . ' </i>" of "<i>' . $feedName . '</i>" on ' . Timezone::convertFromUTC('@' . $activity['date'], Auth::user()->timezone, 'd-m-Y H:i:s') . '.</a></div></p>';
            } elseif ($activity['module'] == 'packet') {
                $feedName = isset($activity['feed_name']) ? $activity['feed_name'] : '';

                $output .= '
                   <p><div class="left-no">' . $no . ". " . '</div><div class="right-data">&nbsp;<a href="' . $activity['url'] . '/myactivity">' . ucfirst(str_replace('_', ' ', $activity['action'])) . ' "<i>' . $activity['module_name'] . '</i> " of "<i>' . $feedName . ' </i>" on ' . Timezone::convertFromUTC('@' . $activity['date'], Auth::user()->timezone, 'd-m-Y H:i:s') . '.</a></div></p>';
            } elseif ($activity['module'] == 'assessment') {
                $output .= '
                    <p><div class="left-no">' . $no . ". " . '</div><div class="right-data">&nbsp;<a href="' . $activity['url'] . '/myactivity">' . ucfirst(str_replace('_', ' ', $activity['action'])) . ' ' . $activity['module'] . ' ' . $activity['module_name'] . ' on ' . Timezone::convertFromUTC('@' . $activity['date'], Auth::user()->timezone, 'd-m-Y H:i:s') . '.</a></div></p>';
            } elseif ($activity['module'] == 'event') {
                $output .= '
                   <p><div class="left-no">' . $no . ". " . '</div><div class="right-data">&nbsp;<a href="' . $activity['url'] . '/myactivity">' . ucfirst(str_replace('_', ' ', $activity['action'])) . ' ' . $activity['module'] . ' ' . $activity['module_name'] . ' on ' . Timezone::convertFromUTC('@' . $activity['date'], Auth::user()->timezone, 'd-m-Y H:i:s') . '.</a></div></p>';
            } elseif ($activity['module'] == 'QAs') {
                $output .= '
                    <p><div class="left-no">' . $no . ". " . '</div><div class="right-data">&nbsp;<a href="' . $activity['url'] . '/myactivity">' . ucfirst(str_replace('_', ' ', $activity['action'])) . ' ' . $activity['module_name'] . ' in "<i>' . $activity['feed_name'] . '</i> &nbsp; > <i>' . $activity['packet_name'] . '</i> " on ' . Timezone::convertFromUTC('@' . $activity['date'], Auth::user()->timezone, 'd-m-Y H:i:s') . '.</a></div></p>';
            }
        }

        return $output;
    }

    public function getMyProfile()
    {
        $session_arr = (is_array(Session::get('session_arr'))) ? Session::get('session_arr') : [];

        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $user = User::getUsersUsingID(Auth::user()->uid);
        $user = $user[0];
        /* $CustomField gives us the record from the custom table */
        $CustomField = CustomFields::getUserActiveCustomField($program_type = 'user', $program_sub_type = '', $status = 'ACTIVE');
        $list = [];

        foreach ($CustomField as $key => $val) {
            $list[$val['fieldname']] = null;
        }

        $customFieldList = array_intersect_key($user, $list);
        $newcustomfield = array_diff_key($list, $customFieldList);

        /* for new user records where we dont have a custom fields in user table
        assign back customFieldList = newcustomfield
        */
        if (empty($customFieldList)) {
            $customFieldList = $newcustomfield;
        }

        /* $customFieldList variable contains the custom fields of and individual user ie. present in the $user   */
        $this->layout->content = view($this->theme_path . '.user.myprofile')->with('user', $user)
            ->with('timezones', Timezone::get())->with('frequent_tz', Timezone::frequent())->
            with('CustomField', $CustomField)->with('newcustomfield', $newcustomfield)->
            with('customFieldList', $customFieldList)->with('session_arr', $session_arr);
    }

    public function postMyProfile($uid = null)
    {
        Input::flash();
        $rules = [
            'firstname' => 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'lastname' => 'Min:2|Max:30|Regex:/^([A-Za-z\'. ])+$/',
            'email' => 'Required|email',
            'mobile' => 'required|max:15|regex:/[0-9+-]{10,15}$/',
            // 'old_password' => 'Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|required_with:password',
            // 'password' => 'Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|Confirmed|required_with:old_password',
            // 'password_confirmation' => 'required_with:password',
            'dob' => 'date',
        ];
        $messages = [
            'firstname.required' => trans('user.first_name_required'),
            'firstname.min' => trans('user.first_name_min'),
            'firstname.max' => trans('user.first_name_max'),
            'firstname.regex' => trans('user.first_name_special_characters'),
            'lastname.regex' => trans('user.last_name_special_characters'),
            'lastname.min' => trans('user.last_name_min'),
            'lastname.max' => trans('user.last_name_max'),
        ];
        $session_arr = [];
        $userCustomField = CustomFields::getUserActiveCustomField($program_type = 'user', $program_sub_type = '');
        foreach ($userCustomField as $values) {
            /*
                    As discussed , allow validation to pass only if edited_by_user is yes
                */
            if ($values['mark_as_mandatory'] == 'yes' && $values['edited_by_user'] == 'yes') {
                $rules[$values['fieldname']] = 'Required';
            }

            if ($values['mark_as_mandatory'] == 'yes') {
                if (empty(Input::get($values['fieldname']))) {
                    array_push($session_arr, $values['fieldname']);
                }
            }
        }
        Session::put('session_arr', $session_arr);
        $validation = Validator::make(Input::all(), $rules, $messages);

        // if (!empty(Input::get('old_password'))) {
        //     $validation->after(function ($validation) {
        //         $uid = Auth::user()->uid;
        //         $pass_cur = User::where('uid', '=', (int) $uid)->value('password');

        //         if (!Hash::check(Input::get('old_password'), $pass_cur)) {
        //             $validation->errors()->add('old_password', 'Old password is incorrect!');
        //         }
        //     });
        // }
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            //checking uniqueness of username and email except for the current id
            //$user_name=User::pluckUserName($uid, Input::get('username'));
            $user_email = User::pluckUserEmail($uid, Input::get('email'));
            if (!empty($user_email)) {
                $error = str_replace(':attribute', 'email', trans('user.user_error'));

                return Redirect::back()->with('email_exist', $error);
            }
            /*elseif(!empty($user_name))
            {
                $error=str_replace(':attribute', 'name', trans('user.user_error'));
                return Redirect::back()->with( 'name_exist', $error );
            }*/
            $List = $this->customPortalFieldData($program_type = 'user', $program_sub_type = '');
            User::getUpdateProfile($uid, Input::all(), $List);
            Input::flush();
            $success = trans('user.myprofile_success');

            return redirect('user/my-profile')->with('success', $success);
        }
    }

    public function customPortalFieldData($program_type, $program_sub_type)
    {
        $userCustomField = CustomFields::getUserActiveCustomField($program_type, $program_sub_type);
        $inputList = Input::all();
        $customField = [];
        foreach ($userCustomField as $key => $user) {
            $customField[$user['fieldname']] = null;
        }
        $userlist = array_intersect_key($inputList, $customField);
        return $userlist;
    }

    public function getMyAddress()
    {
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $uid = Auth::user()->uid;
        $addresses = User::getUserAddress($uid);
        if (count($addresses) > 0 && !Input::get('new_address') && !Input::get('edit_address')) {
            $this->layout->content = view($this->theme_path . '.user.myaddress')->with('addresses', $addresses)->with('uid', $uid);
        } else {
            if (Input::get('edit_address')) {
                $address_id = Input::get('address_id');
                $key = array_search($address_id, array_column($addresses, 'address_id'));
                $address = $addresses[$key];
            } else {
                $address_id = '';
                $address = [];
            }
            $countries = Country::getCountries();
            $states = States::getStates('IN');
            $this->layout->content = view($this->theme_path . '.user.addressform')->with('uid', $uid)->with('address', $address)->with('address_id', $address_id)->with('countries', $countries)->with('states', $states);
        }
    }

    public function postMyAddress($uid, $address_id = null)
    {
        Input::flash();
        $rules = [
            'fullname' => 'required|min:4|max:30|regex:/[a-zA-Z ]+$/',
            'street' => 'required|max:255|regex:/[a-zA-Z#.,0-9- ]+$/',
            'landmark' => 'Min:3|Max:255|Regex:/^([a-zA-Z0-9:.,\-@#&()\/+\n\' ])+$/',
            'city' => 'required|regex:/^[a-zA-Z ]+$/|max:75',
            'state' => 'required|max:75|regex:/[a-zA-Z ]+$/',
            'pincode' => 'required|max:11|regex:/^[0-9]{6}$/',
            'phone' => 'required|max:20|regex:/[0-9+-]{10,15}$/',
            'country' => 'not_in:select',
        ];

        $messages = [
            'state.required_if' => trans('user.address_state_error')
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);

        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            User::updateMyAddress($uid, Input::all(), $address_id);
            Input::flush();
            $success = trans('user.myaddress_success');

            return redirect('user/my-address')->with('success', $success);
        }
    }

    public function getStates()
    {
        $country_code = Input::get('country_code');
        $states = States::getStates($country_code);
        return $states;
    }

    public function getDeleteAddress($uid, $address_id)
    {
        $res = User::deleteAddress($uid, $address_id);

        if ($res) {
            return redirect('user/my-address')
                ->with('success', trans('user.myaddress_delete'));
        } else {
            return redirect('user/my-address')
                ->with('error', trans('user.myaddress_error'));
        }
    }


    public function postProfilePicture($uid)
    {
        $rules = [
            'file' => 'Required|image|mimes:png,jpg,jpeg',
        ];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $file = Input::file('file');
            $image_max_width = config('app.user_pic_width');
            $image_max_height = config('app.user_pic_height');
            $profilePicPath = config('app.user_profile_pic');
            if ($file) {
                $fileName = $file->getClientOriginalName();
                $file_name = pathinfo($fileName, PATHINFO_FILENAME);
                $file_extenstion = pathinfo($fileName, PATHINFO_EXTENSION);
                $fullName = $file_name . '.' . $file_extenstion;
                $file_location = $profilePicPath . $fullName;
                $file->move($profilePicPath, $file_location);
                $image = new Imagick($file_location);
                $imageprops = $image->getImageGeometry();
                if ($imageprops['width'] < $image_max_width && $imageprops['height'] < $image_max_height) {
                    $image->resizeImage($imageprops['width'], $imageprops['height'], Imagick::FILTER_LANCZOS, 1, true);
                }
                $image->writeImage($file_location);
                User::where('uid', '=', (int)$uid)->update(['profile_pic' => $fullName]);
            }
            $success = trans('user.profile_pic_added');
            return redirect('user/my-profile')->with('success', $success);
        }
    }

    public function getDeleteProfilePicture($uid, $from = null)
    {
        User::where('uid', '=', (int)$uid)->update(['profile_pic' => ""]);
        $success = trans('user.remove_profile_picture');
        return redirect('user/my-profile')->with('success', $success);
    }

    public function getChangePassword()
    {
        $user = User::getUsersUsingID(Auth::user()->uid);
        $user = $user[0];
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->content = view($this->theme_path . '.user.changepassword')->with('user', $user);
    }

    public function postChangePassword($uid = null)
    {
        $rules = [
            'old_password' => 'Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|required',
            'password' => 'Required|Min:6|Max:24|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|Confirmed',
            'password_confirmation' => 'required_with:password',
        ];

        $messages = [
            'password.regex' => trans('admin/user.password_regex_msg'),
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);

        if (!empty(Input::get('old_password'))) {
            $validation->after(function ($validation) {
                $uid = Auth::user()->uid;
                $pass_cur = User::where('uid', '=', (int)$uid)->value('password');

                if (!Hash::check(Input::get('old_password'), $pass_cur)) {
                    $validation->errors()->add('old_password', 'Old password is incorrect!');
                }
            });
        }

        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } elseif ($validation->passes()) {
            User::getPasswordUpdate($uid, Input::all());

            Input::flush();
            $success = trans('user.password_change_success');
            return redirect('user/change-password')->with('success', $success);
        }
    }
}
