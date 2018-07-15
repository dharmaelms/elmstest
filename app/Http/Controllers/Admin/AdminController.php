<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\CustomFields\Entity\CustomFields;
use App\Model\User;
use Auth;
use Hash;
use Imagick;
use Input;
use Redirect;
use Session;
use Timezone;
use Validator;

class AdminController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    public function getIndex()
    {
        return redirect('cp/dashboard');
    }

    public function getMyProfile()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/user.my_profile') => 'my-profile',
        ];
        $session_arr = (is_array(Session::get('session_arr'))) ? Session::get('session_arr') : [];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/user.my_profile');
        $this->layout->pageicon = 'fa fa-group';
        $this->layout->pagedescription = trans('admin/user.my_profile');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'my-profile');
        $this->layout->footer = view('admin.theme.common.footer');
        $user = User::getUsersUsingID(Auth::user()->uid);
        /* $CustomField gives us the record from the custom table */
        $CustomField = CustomFields::getUserActiveCustomField('user', '', 'ACTIVE');
        $list = [];
        foreach ($CustomField as $key => $val) {
            $list[$val['fieldname']] = null;
        }
        $customFieldList = array_intersect_key($user[0], $list);
        $newcustomfield = array_diff_key($list, $customFieldList);
        /* $customFieldList variable contains the custom fields of and individual user ie. present in the $user   */

        $this->layout->content = view('admin.theme.dashboard.my_profile')
            ->with('user', $user[0])
            ->with('timezones', Timezone::get())
            ->with('frequent_tz', Timezone::frequent())
            ->with('CustomField', $CustomField)
            ->with('newcustomfield', $newcustomfield)
            ->with('customFieldList', $customFieldList)
            ->with('session_arr', $session_arr);
    }

    public function postMyProfile($uid = null)
    {
        $session_arr = [];
        $rules = [
            'firstname' => 'Required|Min:3|Max:30|Regex:/^[[:alpha:]]+(?:[-_ ]?[[:alpha:]]+)*$/',
            'lastname' => 'Min:2|Max:30|Regex:/^([A-Za-z\'. ])+$/',
            'email' => 'Required|email',
            'mobile' => 'numeric|Regex:/^([0-9]{10})/',
            'old_password' => '|Min:6|Max:24|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|required_with:password',
            'password' => '|Min:6|Max:24|Regex:/^[0-9A-Za-z!@#$%_.-]{6,24}$/|Confirmed|required_with:old_password',
            'password_confirmation' => 'required_with:password',
            'dob' => 'date',
        ];
        $messages = [];
        $messages += [
            'username.regex' => 'Symbolic characters not allowed',
            'password.regex' => trans('admin/user.password_regex_msg'),
            'firstname.regex' => 'Numbers & symbols are not allowed except _ & -',
            'lastname.regex' => 'Numbers & symbols are not allowed except _ & -',
            'firstname.required' => trans('admin/user.first_name_required'),
            'firstname.min' => trans('admin/user.first_name_min'),
            'firstname.max' => trans('admin/user.first_name_max'),
            'lastname.min' => trans('admin/user.last_name_min'),
            'lastname.max' => trans('admin/user.last_name_max'),
        ];
        $userCustomField = CustomFields::getUserActiveCustomField($program_type = 'user', $program_sub_type = '');
        foreach ($userCustomField as $values) {
            if ($values['mark_as_mandatory'] == 'yes') {
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
            return Redirect::back()->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $user_email = User::pluckUserEmail($uid, Input::get('email'));
            if (!empty($user_email)) {
                $error = str_replace(':attribute', 'email', trans('admin/user.user_error'));

                return Redirect::back()->with('email_exist', $error);
            }
            $List = $this->customAdminFieldData($program_type = 'user', $program_sub_type = '');
            User::getUpdateProfile($uid, Input::all(), $List);
            Input::flush();
            $success = trans('admin/user.my_profile_success');

            return redirect('cp/my-profile')->with('success', $success);
        }
    }

    public function customAdminFieldData($program_type, $program_sub_type)
    {
        $userCustomField = CustomFields::getUserActiveCustomField($program_type, $program_sub_type);
        $inputList = Input::all();
        $customField = [];
        foreach ($userCustomField as $key => $user) {
            $customField[$user['fieldname']] = null;
        }
        $userList = array_intersect_key($inputList, $customField);
        return $userList;
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
                $file_extension = pathinfo($fileName, PATHINFO_EXTENSION);
                $fullName = $file_name . '.' . $file_extension;
                $file_location = $profilePicPath . $fullName;
                $file->move($profilePicPath, $file_location);
                $image = new Imagick($file_location);
                $imageProperty = $image->getImageGeometry();
                if ($imageProperty['width'] < $image_max_width && $imageProperty['height'] < $image_max_height) {
                    $image->resizeImage(
                        $imageProperty['width'],
                        $imageProperty['height'],
                        Imagick::FILTER_LANCZOS,
                        1,
                        true
                    );
                }
                $image->writeImage($file_location);
                User::where('uid', '=', (int)$uid)->update(['profile_pic' => $fullName]);
            }
            $success = trans('admin/user.profile_pic_added');
            return redirect('cp/my-profile')->with('success', $success);
        }
    }

    public function getDeleteProfilePicture($uid)
    {
        User::where('uid', '=', (int)$uid)->update(['profile_pic' => ""]);
        $success = trans('admin/user.remove_profile_picture');
        return redirect('cp/my-profile')->with('success', $success);
    }
}
