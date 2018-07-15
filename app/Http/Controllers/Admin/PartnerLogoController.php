<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\PartnerLogo;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\HomePage\HomePagePermission;
use Common;
use Config;
use File;
use Imagick;
use Input;
use Redirect;
use Request;
use Validator;

class PartnerLogoController extends AdminBaseController
{

    protected $layout = 'admin.theme.layout.master_layout';

    public function __construct(Request $request)
    {
        $input = $request::input();
        array_walk(
            $input,
            function (&$i) {
                (is_string($i)) ? $i = htmlentities($i) : '';
            }
        );
        $request::merge($input);

        $this->theme_path = 'admin.theme';
    }

    public function getIndex()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_PARTNER)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/partnerlogo.corporate_partner') => 'partnerlogo',
            trans('admin/partnerlogo.list_partner') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/partnerlogo.corporate_partner');
        $this->layout->pageicon = 'fa fa-users';
        $this->layout->pagedescription = trans('admin/partnerlogo.list_of_partner_logo');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'partnerlogo');
        $this->layout->footer = view('admin.theme.common.footer');

        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ALL';
        }
        $partners = PartnerLogo::getFilteredRecords($filter);

        $last_order = PartnerLogo::where('status', '!=', 'DELETED')->max('sort_order');
        $this->layout->content = view('admin.theme.partnerlogo.listpartnerlogo')
            ->with('partners', $partners)
            ->with('last_order', $last_order);
    }


    public function getAddPartnerLogo()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_PARTNER)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/partnerlogo.corporate_partner') => 'partnerlogo',
            trans('admin/partnerlogo.add_partner') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/partnerlogo.add_partner_logo');
        $this->layout->pageicon = 'fa fa-users';
        $this->layout->pagedescription = trans('admin/partnerlogo.add_partner_logo');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'partnerlogo');
        $this->layout->footer = view('admin.theme.common.footer');
        $sort_order = PartnerLogo::where('status', '!=', 'DELETED')->max('sort_order');
        $sort_order = $sort_order + 1;
        $this->layout->content = view('admin.theme.partnerlogo.addpartnerlogo')->with('sort_order', $sort_order);
    }

    public function postAddPartnerLogo()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_PARTNER)) {
            return parent::getAdminError($this->theme_path);
        }

        $rules = [
            'partner_name' => 'Required',
            'file' => 'Required|image|mimes:png,jpg,jpeg',
            'description' => 'Min:3|Max:250|Regex:/^([a-zA-Z0-9:.,\-@#&()\/+\n\' ])+$/',
            'status' => 'Required',
        ];

        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $file = Input::file('file');
            $partner_logo_resolution = Config::get('app.partner_logo_resolution');
            if ($file) {
                $partner_logo_path = config('app.partner_logo_path');
                $file_client_name = $file->getClientOriginalName();
                $file_name = pathinfo($file_client_name, PATHINFO_FILENAME);
                $file_extenstion = pathinfo($file_client_name, PATHINFO_EXTENSION);
                $id = PartnerLogo::getUniqueId();
                $file_original_name = $file_name . $id . '.' . $file_extenstion;
                $file_location = $partner_logo_path . $file_original_name;
                $file->move($partner_logo_path, $file_location);
                $image_obj = new Imagick($file_location);
                $required_sizes = explode('x', $partner_logo_resolution);
                $logo_size = $partner_logo_path . $file_name . $id . '_' . $partner_logo_resolution . $file_extenstion;
                if ($required_sizes[0] < $image_obj->getImageWidth() && $required_sizes[1] < $image_obj->getImageHeight()) {
                    $image_obj->resizeImage($required_sizes[0], $required_sizes[1], Imagick::FILTER_LANCZOS, 1, true);
                }
                $image_obj->writeImage($logo_size);
                $logo_dimension = $file_name . $id . '_' . $partner_logo_resolution . $file_extenstion;
            } else {
                $file_original_name = '';
            }
            $partner = PartnerLogo::addPartner($id, Input::all(), $file_location, $file_original_name, $logo_dimension);

            $curval = Input::get('curval');
            $nextval = Input::get('sort_order');
            if ($curval == $nextval) {
                $data = PartnerLogo::where('partner_id', '=', (int)$id)->update(['sort_order' => (int)$nextval]);
            } else {
                PartnerLogo::sortlogos($id, $curval, $nextval);
            }

            if ($partner) {
                Input::flush();
                return redirect('cp/partnerlogo')
                    ->with('success', trans('admin/partnerlogo.logo_add_success'));
            } else {
                return redirect('cp/partnerlogo/add-partner_logo')
                    ->with('error', trans('admin/partnerlogo.logo_error'));
            }
        }
    }

    public function getEditPartnerLogo($id)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_PARTNER)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/partnerlogo.manage_partner') => 'partnerlogo',
            trans('admin/partnerlogo.edit_partner') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/partnerlogo.edit_partner_logo');
        $this->layout->pageicon = 'fa fa-users';
        $this->layout->pagedescription = trans('admin/partnerlogo.edit_partner_logo');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'partnerlogo');
        $this->layout->footer = view('admin.theme.common.footer');

        $partner = PartnerLogo::getPartnerDetails($id);
        $partner = $partner[0];
        $sort_order = PartnerLogo::where('status', '!=', 'DELETED')->max('sort_order');
        $this->layout->content = view('admin.theme.partnerlogo.editpartnerlogo')
            ->with('partner', $partner)
            ->with('sort_order', $sort_order);
    }

    public function postEditPartnerLogo($id)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_PARTNER)) {
            return parent::getAdminError($this->theme_path);
        }

        $rules = [
            'partner_name' => 'Required',
            'file' => 'image|mimes:png,jpg,jpeg',
            'description' => 'Min:3|Max:250|Regex:/^([a-zA-Z0-9:.,\-@#&()\/+\n\' ])+$/',
            'status' => 'Required',
        ];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $file_location = ' ';
            $file_original_name = ' ';

            $file = Input::file('file');
            $partner_logo_resolution = Config::get('app.partner_logo_resolution');
            $partner_logo_path = config('app.partner_logo_path');
            if ($file) {
                $file_client_name = $file->getClientOriginalName();
                $file_name = pathinfo($file_client_name, PATHINFO_FILENAME);
                $file_extenstion = pathinfo($file_client_name, PATHINFO_EXTENSION);
                $file_original_name = $file_name . $id . '.' . $file_extenstion;
                $file_location = $partner_logo_path . $file_original_name;
                $file->move($partner_logo_path, $file_location);
                $image_obj = new Imagick($file_location);
                $required_sizes = explode('x', $partner_logo_resolution);
                $logo_size = $partner_logo_path . $file_name . $id . '_' . $partner_logo_resolution . $file_extenstion;
                if ($required_sizes[0] < $image_obj->getImageWidth() && $required_sizes[1] < $image_obj->getImageHeight()) {
                    $image_obj->resizeImage($required_sizes[0], $required_sizes[1], Imagick::FILTER_LANCZOS, 1, true);
                }
                $image_obj->writeImage($logo_size);
                $logo_dimension = $file_name . $id . '_' . $partner_logo_resolution . $file_extenstion;
            } elseif (Input::get('old_file')) {
                $file_original_name = Input::get('old_file');
                $file_location = $partner_logo_path . $file_original_name;
                $logo_dimension = $partner_logo_path . Input::get('old_diamension');
            } else {
                $file_original_name = '';
            }
            $partner_update = PartnerLogo::upadatePartner($id, Input::all(), $file_location, $file_original_name, $logo_dimension);

            $curval = Input::get('curval');
            $nextval = Input::get('sort_order');
            if ($curval != $nextval) {
                PartnerLogo::sortlogos($id, $curval, $nextval);
            }

            if ($partner_update) {
                Input::flush();
                return redirect('cp/partnerlogo')
                    ->with('success', trans('admin/partnerlogo.logo_edit_success'));
            } else {
                return redirect('cp/partnerlogo/edit-partner-logo/' . $id)
                    ->with('error', trans('admin/partnerlogo.logo_error'));
            }
        }
    }

    public function getSortOrder($id, $curval, $nextval)
    {

        PartnerLogo::sortlogos($id, $curval, $nextval);
        return redirect('cp/partnerlogo');
    }

    public function getDeleteLogo($id, $logo_name, $sort_order)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_PARTNER)) {
            return parent::getAdminError($this->theme_path);
        }

        $path = config('app.partner_logo_path');
        $delete_file = $path . $logo_name;

        if (File::exists($path)) {
            $a = File::delete($delete_file);
        }
        $max_order = PartnerLogo::where('status', '!=', 'DELETED')->max('sort_order');
        $res = PartnerLogo::deletelogo($id, $sort_order, $max_order);

        if ($res) {
            return redirect('cp/partnerlogo')
                ->with('success', trans('admin/partnerlogo.delete_success'));
        } else {
            return redirect('cp/partnerlogo')
                ->with('error', trans('admin/partnerlogo.logo_error'));
        }
    }
}
