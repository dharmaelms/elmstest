<?php
namespace App\Http\Controllers\Admin\Courses;

use App\Http\Controllers\AdminBaseController;
use App\Model\Common;
use App\Model\Program;
use App\Services\Courses\Popular\PopularService;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\HomePage\HomePagePermission;
use Auth;
use Input;
use Request;
use Timezone;
use URL;

class PopularCoursesController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';
    protected $popularSer;

    public function __construct(Request $request, PopularService $Popular)
    {
        $this->theme_path = 'admin.theme';
        $this->popularSer = $Popular;
    }

    public function getIndex()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_POPULAR_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/courses.manage_popular_courses') => 'popularcourses',
            trans('admin/courses.list_popular_courses') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/courses.manage_popular_courses');
        $this->layout->pageicon = 'fa fa-book';
        $this->layout->pagedescription = trans('admin/courses.manage_popular_courses');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'homepage')
            ->with('submenu', 'popularcourses');
        $this->layout->content = view('admin.theme.courses.listpopularcourses');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getPopularCoursesAjax()
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_POPULAR_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $start = 0;
        $limit = 10;

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $totalRecords = $this->popularSer->getPopularCount();
        $filteredRecords = $this->popularSer->getPopularCount();
        $filtereddata = $this->popularSer->getPopularCourses($start, $limit);

        $dataArr = [];

        foreach ($filtereddata as $key => $value) {
            $program_title = Program::getCFTitle($value['program_slug']);

            if ($value['program_type'] == 'content_feed') {
                if ($value['program_sub_type'] == 'collection') {
                    $program_type = 'Package';
                    $delete_tooltip = trans('admin/courses.delete_package');
                } else {
                    $program_type = 'Channel';
                    $delete_tooltip = trans('admin/courses.delete_channel');
                }
            } elseif ($value['program_type'] == 'course') {
                $program_type = 'Course';
                $delete_tooltip = trans('admin/courses.delete_course');
            } else {
                $program_type = 'Product';
                $delete_tooltip = trans('admin/courses.delete_product');
            }

            if (has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_POPULAR_COURSES)) {
                $delete = '<a class="btn btn-circle show-tooltip deletecourse" title="' . $delete_tooltip . '" href="' . URL::to('cp/popularcourses/delete-course/' . $value['program_id']) . '" ><i class="fa fa-trash-o"></i></a>';
            } else {
                $delete = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/courses.permission_delete') . '" ><i class="fa fa-trash-o"></i></a>';
            }

            $temparr = [
                "<div>" . $program_title . "</div>",
                $program_type,
                Timezone::convertFromUTC('@' . $value['program_display_startdate'], Auth::user()->timezone, config('app.date_format')),
                Timezone::convertFromUTC('@' . $value['program_display_enddate'], Auth::user()->timezone, config('app.date_format')),
                $delete,
            ];

            $dataArr[] = $temparr;
        }
        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getDeleteCourse($program_id)
    {
        if (!has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_POPULAR_COURSES)) {
            return parent::getAdminError($this->theme_path);
        }

        $result = $this->popularSer->getDeleteCourse($program_id);
        if ($result) {
            $success = trans('admin/courses.delete_success');
            return redirect('cp/popularcourses')->with('success', $success);
        } else {
            $error = trans('admin/courses.delete_error');
            return redirect('cp/popularcourses')->with('error', $success);
        }
    }
}
