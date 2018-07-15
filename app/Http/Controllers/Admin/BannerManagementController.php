<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Banners;
use App\Model\Common;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\HomePage\HomePagePermission;
use Config;
use File;
use Imagick;
use Input;
use Redirect;
use Request;
use Validator;

class BannerManagementController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';

    public function __construct(Request $request)
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);

        $this->theme_path = 'admin.theme';
    }

    public function getIndex()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_BANNERS)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/banner.manage_banners') => 'banners',
            trans('admin/banner.list_banners') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/banner.manage_banners');
        $this->layout->pageicon = 'fa fa-image';
        $this->layout->pagedescription = trans('admin/banner.list_of_banner');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'banners');
        $this->layout->footer = view('admin.theme.common.footer');

        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ALL';
        }
        $banners = Banners::getBanners($filter);
        $last_order = Banners::where('status', '!=', 'DELETED')->max('sort_order');
        $this->layout->content = view('admin.theme.banners.listbanners')
            ->with('banners', $banners)->with('last_order', $last_order);
    }

    public function getAddBanner()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_BANNERS)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/banner.manage_banners') => 'banners',
            trans('admin/banner.add_banner') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/banner.add_banner');
        $this->layout->pageicon = 'fa fa-image';
        $this->layout->pagedescription = trans('admin/banner.add_banner');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'banners');
        $this->layout->footer = view('admin.theme.common.footer');
        $sort_order = Banners::where('status', '!=', 'DELETED')->max('sort_order');
        $sort_order = $sort_order + 1;
        $this->layout->content = view('admin.theme.banners.addbanners')->with('sort_order', $sort_order);
    }

    public function postAddBanner()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_BANNERS)) {
            return parent::getAdminError($this->theme_path);
        }

        Input::flash();
        $rules = [
            'banner_name' => 'Required',
            'banner_type' => 'Required',
            'banner_url' => 'url',
            'file' => 'required_without_all:mobile_file,mobile_landscape_file|image|mimes:png,jpg,jpeg',
            'mobile_file' => 'required_without_all:file,mobile_landscape_file|image|mimes:png,jpg,jpeg',
            'mobile_landscape_file' => 'required_without_all:file,mobile_file|image|mimes:png,jpg,jpeg',
            'description' => 'Min:3|Max:250',
            'mobile_description' => 'Min:3|Max:250',
            'status' => 'Required',
        ];

        $messages = [];
        $messages += [
            'file.required_without_all' => 'Upload atleast 1 image at a time',
            'mobile_file.required_without_all' => 'Upload atleast 1 image at a time',
            'mobile_landscape_file.required_without_all' => 'Upload atleast 1 image at a time'
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $file_original_name = ' ';
            $file_original_mobile_name = ' ';
            $file_original_mobile_name2 = ' ';

            $web = "web";
            $mob_portrait = "portrait";
            $mob_landscape = "landscape";
            $file = Input::file('file');
            $mobile_banner_resolution = Config::get('app.mobile_banner_resolution');
            if ($file) {
                $site_banners_path = config('app.site_banners_path');
                $file_client_name = $file->getClientOriginalName();
                $file_name = pathinfo($file_client_name, PATHINFO_FILENAME);
                $file_extension = pathinfo($file_client_name, PATHINFO_EXTENSION);
                $id = Banners::getUniqueId();
                $file_original_name = $file_name . $id . $web . '_' . $mobile_banner_resolution . '.' . $file_extension;
                $file_location = $site_banners_path . $file_original_name;
                $file->move($site_banners_path, $file_location);

                $image_obj = new Imagick($file_location);
                $required_sizes = explode('x', $mobile_banner_resolution);

                $mobile_banner = $site_banners_path . $file_name . $id . $web . '_' . $mobile_banner_resolution . $file_extension;
                if ($required_sizes[0] < $image_obj->getImageWidth() && $required_sizes[1] < $image_obj->getImageHeight()) {
                    $image_obj->resizeImage($required_sizes[0], $required_sizes[1], Imagick::FILTER_LANCZOS, 1, true);
                }
                $image_obj->writeImage($file_location);
            } else {
                $file_original_name = '';
            }

            // mobile image code
            $mobile_file = Input::file('mobile_file');
            $mobile_banner_portrait_resolution = Config::get('app.mobile_banner_portrait_resolution');
            if ($mobile_file) {
                $site_banners_mobile_path = config('app.site_banners_path');
                $file_client_mobile_name = $mobile_file->getClientOriginalName();
                $file_mobile_name = pathinfo($file_client_mobile_name, PATHINFO_FILENAME);
                $file_mobile_extension = pathinfo($file_client_mobile_name, PATHINFO_EXTENSION);
                $id = Banners::getUniqueId();
                $file_original_mobile_name = $file_mobile_name . $id . $mob_portrait . '_' . $mobile_banner_portrait_resolution . '.' . $file_mobile_extension;
                $file_mobile_location = $site_banners_mobile_path . $file_original_mobile_name;
                $mobile_file->move($site_banners_mobile_path, $file_mobile_location);
                $image_obj = new Imagick($file_mobile_location);
                $required_sizes_mobile = explode('x', $mobile_banner_portrait_resolution);
                $mobile_banner = $site_banners_mobile_path . $file_mobile_name . $id . $mob_portrait . '_' . $mobile_banner_portrait_resolution . $file_mobile_extension;
                if ($required_sizes_mobile[0] < $image_obj->getImageWidth() && $required_sizes_mobile[1] < $image_obj->getImageHeight()) {
                    $image_obj->resizeImage($required_sizes_mobile[0], $required_sizes_mobile[1], Imagick::FILTER_LANCZOS, 1, true);
                }
                $image_obj->writeImage($file_mobile_location);
            } else {
                $file_original_mobile_name = '';
            }

            $mobile_file2 = Input::file('mobile_landscape_file');
            $mobile_banner_landscape_resolution = Config::get('app.mobile_banner_landscape_resolution');
            if ($mobile_file2) {
                $site_banners_mobile_path2 = config('app.site_banners_path');
                $file_client_mobile_name2 = $mobile_file2->getClientOriginalName();
                $file_mobile_name2 = pathinfo($file_client_mobile_name2, PATHINFO_FILENAME);
                $file_mobile_extension2 = pathinfo($file_client_mobile_name2, PATHINFO_EXTENSION);
                $id = Banners::getUniqueId();
                $file_original_mobile_name2 = $file_mobile_name2 . $id . $mob_landscape . '_' . $mobile_banner_landscape_resolution . '.' . $file_mobile_extension2;
                $file_mobile_location2 = $site_banners_mobile_path2 . $file_original_mobile_name2;
                $mobile_file2->move($site_banners_mobile_path2, $file_mobile_location2);
                $image_obj = new Imagick($file_mobile_location2);
                $required_sizes_mobile2 = explode('x', $mobile_banner_landscape_resolution);
                $mobile_banner2 = $site_banners_mobile_path2 . $file_mobile_name2 . $id . $mob_landscape . '_' . $mobile_banner_landscape_resolution . $file_mobile_extension2;

                if ($required_sizes_mobile2[0] < $image_obj->getImageWidth() && $required_sizes_mobile2[1] < $image_obj->getImageHeight()) {
                    $image_obj->resizeImage($required_sizes_mobile2[0], $required_sizes_mobile2[1], Imagick::FILTER_LANCZOS, 1, true);
                }

                $image_obj->writeImage($file_mobile_location2);
            } else {
                $file_original_mobile_name2 = '';
            }

            $res = Banners::addBanner($id, $file_original_name, $file_original_mobile_name, $file_original_mobile_name2, Input::all());


            $currentValue = Input::get('curval');
            $nextValue = Input::get('sort_order');
            if ($currentValue == $nextValue) {
                Banners::where('id', '=', (int)$id)->update(['sort_order' => (int)$nextValue]);
            } else {
                Banners::sortBanners($id, $currentValue, $nextValue);
            }

            if ($res) {
                Input::flush();
                return redirect('cp/banners')
                    ->with('success', trans('admin/banner.add_success'));
            } else {
                return redirect('cp/banners/add-banner')
                    ->with('error', trans('admin/banner.banner_error'));
            }
        }
    }

    public function getEditBanner($id)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_BANNERS)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/banner.manage_banners') => 'banners',
            trans('admin/banner.edit_banner') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);

        $this->layout->pageicon = 'fa fa-image';
        $this->layout->pagedescription = trans('admin/banner.edit_banner');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->pagetitle = trans('admin/banner.edit_banner');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'banners');
        $banner = Banners::getBannerUsingId($id);
        $banner = $banner[0];

        $sort_order = Banners::where('status', '!=', 'DELETED')->max('sort_order');
        $this->layout->footer = view('admin.theme.common.footer');
        $this->layout->content = view('admin.theme.banners.editbanners')->with('banner', $banner)->with('sort_order', $sort_order);
    }

    public function postEditBanner($id)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_BANNERS)) {
            return parent::getAdminError($this->theme_path);
        }
        Input::flash();
        $input_all = Input::all();
        $rules = [
            'banner_name' => 'Required',
            'banner_type' => 'Required',
            'banner_url' => 'url',
            'file' => 'image|mimes:png,jpg,jpeg',
            'mobile_file' => 'image|mimes:png,jpg,jpeg',
            'mobile_landscape_file' => 'image|mimes:png,jpg,jpeg',
            'description' => 'Min:3|Max:250',
            'mobile_description' => 'Min:3|Max:250',
            'status' => 'Required',
        ];

        if (empty($input_all['old_file']) && (empty($input_all['old_file1'])) && (empty($input_all['old_file2']))) {
            $rules = [];
            if (!empty($input_all['old_web'])) {
                $rules += [
                    'file' => 'required_without_all:mobile_file,mobile_landscape_file|image|mimes:png,jpg,jpeg',
                ];
            }
            if (!empty($input_all['old_portrait'])) {
                $rules += [
                    'mobile_file' => 'required_without_all:file,mobile_landscape_file|image|mimes:png,jpg,jpeg',
                ];
            }
            if (!empty($input_all['old_landscape'])) {
                $rules += [
                    'mobile_landscape_file' => 'required_without_all:file,mobile_file|image|mimes:png,jpg,jpeg',
                ];
            }
        }
        $messages = [];
        $messages += [
            'file.required_without_all' => trans('admin/banner.atleast_upload_1_banner'),
            'mobile_file.required_without_all' => trans('admin/banner.atleast_upload_1_banner'),
            'mobile_landscape_file.required_without_all' => trans('admin/banner.atleast_upload_1_banner'),
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);


        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $file_original_name = '';
            $file_original_mobile_name = '';
            $file_original_mobile_name2 = '';
            $web = "web";
            $mob_portrait = "portrait";
            $mob_landscape = "landscape";

            // individual images delete code
            if ($input_all['deleteweb'] == 1) {
                $path = config('app.site_banners_path');
                $delete_file = $path . $input_all['old_web'];
                if (File::exists($path)) {
                    $file_deleted = File::delete($delete_file);
                }
                $db_delete_banner = Banners::where('file_client_name', '=', $input_all['old_web'])->update(['file_client_name' => ""]);
            } else {
                $file = Input::file('file');
                $mobile_banner_resolution = Config::get('app.mobile_banner_resolution');
                if ($file) {
                    $site_banners_path = config('app.site_banners_path');
                    $file_client_name = $file->getClientOriginalName();
                    $file_name = pathinfo($file_client_name, PATHINFO_FILENAME);
                    $file_extension = pathinfo($file_client_name, PATHINFO_EXTENSION);
                    $file_original_name = $file_name . $id . $web . '_' . $mobile_banner_resolution . '.' . $file_extension;
                    $file_location = $site_banners_path . $file_original_name;
                    $file->move($site_banners_path, $file_location);

                    $image_obj = new Imagick($file_location);
                    $required_sizes = explode('x', $mobile_banner_resolution);

                    $mobile_banner = $site_banners_path . $file_name . $id . $web . '_' . $mobile_banner_resolution . $file_extension;
                    if ($required_sizes[0] < $image_obj->getImageWidth() && $required_sizes[1] < $image_obj->getImageHeight()) {
                        $image_obj->resizeImage($required_sizes[0], $required_sizes[1], Imagick::FILTER_LANCZOS, 1, true);
                    }
                    $image_obj->writeImage($file_location);
                } elseif (Input::get('old_file')) {
                    $file_original_name = Input::get('old_file');
                } else {
                    $file_original_name = '';
                }
            }

            // mobile portrait
            if ($input_all['deleteportrait'] == 1) {
                $path = config('app.site_banners_path');
                $delete_file = $path . $input_all['old_portrait'];
                if (File::exists($path)) {
                    $file_deleted = File::delete($delete_file);
                }
                $db_delete_banner = Banners::where('mobile_portrait', '=', $input_all['old_portrait'])->update(['mobile_portrait' => ""]);
            } else {
                $mobile_file = Input::file('mobile_file');
                $mobile_banner_portrait_resolution = Config::get('app.mobile_banner_portrait_resolution');
                if (isset($mobile_file) && !empty($mobile_file)) {
                    $site_banners_mobile_path = config('app.site_banners_path');
                    $file_client_mobile_name = $mobile_file->getClientOriginalName();
                    $file_mobile_name = pathinfo($file_client_mobile_name, PATHINFO_FILENAME);
                    $file_mobile_extension = pathinfo($file_client_mobile_name, PATHINFO_EXTENSION);
                    $file_original_mobile_name = $file_mobile_name . $id . $mob_portrait . '_' . $mobile_banner_portrait_resolution . '.' . $file_mobile_extension;
                    $file_mobile_location = $site_banners_mobile_path . $file_original_mobile_name;
                    $mobile_file->move($site_banners_mobile_path, $file_mobile_location);
                    $image_obj = new Imagick($file_mobile_location);
                    $required_sizes_mobile = explode('x', $mobile_banner_portrait_resolution);
                    $mobile_banner = $site_banners_mobile_path . $file_mobile_name . $id . $mob_portrait . '_' . $mobile_banner_portrait_resolution . $file_mobile_extension;
                    if ($required_sizes_mobile[0] < $image_obj->getImageWidth() && $required_sizes_mobile[1] < $image_obj->getImageHeight()) {
                        $image_obj->resizeImage($required_sizes_mobile[0], $required_sizes_mobile[1], Imagick::FILTER_LANCZOS, 1, true);
                    }
                    $image_obj->writeImage($file_mobile_location);
                } elseif (Input::get('old_file1')) {
                    $file_original_mobile_name = Input::get('old_file1');
                } else {
                    $file_original_mobile_name = '';
                }
            }

            // mobile landscape
            if ($input_all['deletelandscape'] == 1) {
                $path = config('app.site_banners_path');
                $delete_file = $path . $input_all['old_landscape'];
                if (File::exists($path)) {
                    $file_deleted = File::delete($delete_file);
                }
                $db_delete_banner = Banners::where('mobile_landscape', '=', $input_all['old_landscape'])->update(['mobile_landscape' => ""]);
            } else {
                $mobile_file2 = Input::file('mobile_landscape_file');
                $mobile_banner_landscape_resolution = Config::get('app.mobile_banner_landscape_resolution');
                if (isset($mobile_file2) && !empty($mobile_file2)) {
                    $site_banners_mobile_path2 = config('app.site_banners_path');
                    $file_client_mobile_name2 = $mobile_file2->getClientOriginalName();
                    $file_mobile_name2 = pathinfo($file_client_mobile_name2, PATHINFO_FILENAME);
                    $file_mobile_extension2 = pathinfo($file_client_mobile_name2, PATHINFO_EXTENSION);
                    $file_original_mobile_name2 = $file_mobile_name2 . $id . $mob_landscape . '_' . $mobile_banner_landscape_resolution . '.' . $file_mobile_extension2;
                    $file_mobile_location2 = $site_banners_mobile_path2 . $file_original_mobile_name2;
                    $mobile_file2->move($site_banners_mobile_path2, $file_mobile_location2);
                    $image_obj = new Imagick($file_mobile_location2);
                    $required_sizes_mobile2 = explode('x', $mobile_banner_landscape_resolution);
                    $mobile_banner2 = $site_banners_mobile_path2 . $file_mobile_name2 . $id . $mob_landscape . '_' . $mobile_banner_landscape_resolution . $file_mobile_extension2;
                    if ($required_sizes_mobile2[0] < $image_obj->getImageWidth() && $required_sizes_mobile2[1] < $image_obj->getImageHeight()) {
                        $image_obj->resizeImage($required_sizes_mobile2[0], $required_sizes_mobile2[1], Imagick::FILTER_LANCZOS, 1, true);
                    }

                    $image_obj->writeImage($file_mobile_location2);
                } elseif (Input::get('old_file2')) {
                    $file_original_mobile_name2 = Input::get('old_file2');
                } else {
                    $file_original_mobile_name2 = '';
                }
            }

            $res = Banners::updateBanner($id, $file_original_name, $file_original_mobile_name, $file_original_mobile_name2, Input::all());

            $currentValue = Input::get('curval');
            $nextValue = Input::get('sort_order');
            if ($currentValue != $nextValue) {
                Banners::sortBanners($id, $currentValue, $nextValue);
            }

            if ($res) {
                Input::flush();
                return redirect('cp/banners')
                    ->with('success', trans('admin/banner.edit_success'));
            } else {
                return redirect('cp/banners/edit-banner/' . $id)
                    ->with('error', trans('admin/banner.banner_error'));
            }
        }
    }

    public function getDeleteBanner($id, $sort_order)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_BANNERS)) {
            return parent::getAdminError($this->theme_path);
        }

        $max_order = Banners::where('status', '!=', 'DELETED')->max('sort_order');

        $res = Banners::deleteBanner($id, $sort_order, $max_order);

        if ($res) {
            return redirect('cp/banners')
                ->with('success', trans('admin/banner.delete_success'));
        } else {
            return redirect('cp/banners')
                ->with('error', trans('admin/banner.banner_error'));
        }
    }

    public function getSortOrder($id, $currentValue, $nextValue)
    {
        Banners::sortBanners($id, $currentValue, $nextValue);
        return redirect('cp/banners');
    }
}
