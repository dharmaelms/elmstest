<?php
namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\SiteSetting;
use App\Services\Testimonial\ITestimonialService;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\HomePage\HomePagePermission;
use Common;
use Input;
use Request;
use Redirect;
use Validator;

class TestimonialController extends AdminBaseController
{

    protected $layout = 'admin.theme.layout.master_layout';
    private $testimonial;


    public function __construct(Request $request, ITestimonialService $testimonial)
    {
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->testimonial = $testimonial;
        $this->theme_path = 'admin.theme';
        $this->label = SiteSetting::module('Homepage', 'Quotes')['label'];
    }

    /**
     * Display a listing of the testimonials.
     *
     * @return \Illuminate\Http\Response
     */
    public function getIndex()
    {

        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_TESTIMONIALS)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            $this->label => 'testimonials',
            trans('admin/testimonial.list') . ' ' . $this->label => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/testimonial.list_page_title') . ' ' . $this->label;
        $this->layout->pageicon = trans('admin/testimonial.list_page_icon');
        $this->layout->pagedescription = trans('admin/testimonial.list_page_description') . ' ' . $this->label;
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'testimonial');
        $this->layout->footer = view('admin.theme.common.footer');

        if (!is_null(Input::get('filter'))) {
            $filter = Input::get('filter');
        } else {
            $filter = 'ALL';
        }
        $testimonials = $this->testimonial->listTestimonials($filter);

        $last_order = $this->testimonial->getMaxSortOrder();
        $this->layout->content = view('admin.theme.testimonial.listtestimonial')
            ->with('testimonials', $testimonials)
            ->with('last_order', $last_order)
            ->with('label', $this->label);
    }

    /**
     * Show the form for creating a new testimonial.
     *
     * @return \Illuminate\Http\Response
     */
    public function getCreateTestimonial()
    {

        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_TESTIMONIALS)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            $this->label => 'testimonials',
            trans('admin/testimonial.add_page_title') . ' ' . $this->label => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/testimonial.add_page_title') . ' ' . $this->label;
        $this->layout->pageicon = trans('admin/testimonial.add_page_icon');
        $this->layout->pagedescription = trans('admin/testimonial.add_page_description') . ' ' . $this->label;
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'testimonial');
        $this->layout->footer = view('admin.theme.common.footer');
        $sort_order = $this->testimonial->getMaxSortOrder();
        $sort_order = $sort_order + 1;
        $this->layout->content = view('admin.theme.testimonial.addtestimonial')->with('sort_order', $sort_order);
    }

    public function postCreateTestimonial()
    {
        $input = Input::all();
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_TESTIMONIALS)) {
            return parent::getAdminError($this->theme_path);
        }


        $rules = [
            'name' => 'Required',
            'file' => 'Required|image|mimes:png,jpg,jpeg',
            'testimonial_description' => 'Min:3',
            'testimonial_short_description' => 'Min:3|Max:100',
            'status' => 'Required',
        ];

        $formattedNames = [
            'file' => 'Profile Picture',
        ];

        $messages = ['testimonial_description.min' => trans('admin/testimonial.testimonial_description_min'),
            'testimonial_short_description.min' => trans('admin/testimonial.testimonial_short_description_min'),
            'testimonial_short_description.max' => trans('admin/testimonial.testimonial_short_description_max')
        ];


        $validation = Validator::make(Input::all(), $rules, $messages);
        $validation->setAttributeNames($formattedNames);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $partner = $this->testimonial->createTestimonials($input, 'add', '');
            if ($partner) {
                Input::flush();
                return redirect('cp/testimonials')
                    ->with('success', trans('admin/testimonial.testimonial_add_success'));
            } else {
                return redirect('cp/testimonials/add-partner_logo')
                    ->with('error', trans('admin/testimonial.testimonial_error'));
            }
        }
    }

    public function getEditTestimonial($id)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_TESTIMONIALS)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            'Manage ' . $this->label => 'testimonials',
            trans('admin/testimonial.list_edit_testimonial') . ' ' . $this->label => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/testimonial.edit_page_title') . ' ' . $this->label;
        $this->layout->pageicon = trans('admin/testimonial.edit_page_icon');
        $this->layout->pagedescription = trans('admin/testimonial.edit_page_description') . ' ' . $this->label;
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'testimonial');
        $this->layout->footer = view('admin.theme.common.footer');

        $testimonial = $this->testimonial->getTestimonialDetails($id);
        $testimonial = $testimonial[0];
        $sort_order = $this->testimonial->getMaxSortOrder();
        $this->layout->content = view('admin.theme.testimonial.edittestimonial')
            ->with('testimonial', $testimonial)
            ->with('order', $sort_order);
    }


    /**
     * Update the specified resource in storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\RedirectResponse
     */
    public function postEditTestimonial($id)
    {
        $input = Input::all();
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_TESTIMONIALS)) {
            return parent::getAdminError($this->theme_path);
        }
        $rules = [
            'name' => 'Required',
            'file' => 'image|mimes:png,jpg,jpeg',
            'testimonial_description' => 'Min:3',
            'testimonial_short_description' => 'Min:3|Max:100',
            'status' => 'Required',
        ];
        $messages = ['testimonial_description.min' => trans('admin/testimonial.testimonial_description_min'),
            'testimonial_short_description.min' => trans('admin/testimonial.testimonial_short_description_min'),
            'testimonial_short_description.max' => trans('admin/testimonial.testimonial_short_description_max')
        ];

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return Redirect::back()->withInput()->withErrors($validation);
        } else {
            $partner = $this->testimonial->createTestimonials($input, 'edit', $id);
            if ($partner) {
                Input::flush();
                return redirect('cp/testimonials')
                    ->with('success', trans('admin/testimonial.testimonial_edit_success'));
            } else {
                return redirect('cp/testimonials/edit-testimonial/' . $id)
                    ->with('error', trans('admin/testimonial.testimonial_error'));
            }
        }
    }


    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function getDeleteTestimonial($id, $logo_name, $sort_order)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_TESTIMONIALS)) {
            return parent::getAdminError($this->theme_path);
        }

        $res = $this->testimonial->deleteTestimonials($id, $logo_name, $sort_order);


        if ($res) {
            return redirect('cp/testimonials')
                ->with('success', trans('admin/testimonial.delete_success'));
        } else {
            return redirect('cp/testimonials')
                ->with('error', trans('admin/testimonial.testimonial_error'));
        }
    }

    public function getSortOrder($id, $curval, $nextval)
    {

        $this->testimonial->sortLogos($id, $curval, $nextval);
        return redirect('cp/testimonials');
    }
}
