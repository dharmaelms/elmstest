<?php

namespace App\Http\Controllers\Admin;

use App;
use App\Events\Elastic\Packages\PackageAdded;
use App\Events\Elastic\Packages\PackageEdited;
use App\Events\Elastic\Packages\PackageRemoved;
use App\Events\Elastic\Programs\ProgramAssigned;
use App\Events\Elastic\Users\PackageAssigned;
use App\Exceptions\ApplicationException;
use App\Http\Controllers\AdminBaseController;
use App\Services\Package\IPackageService;
use App\Services\Catalog\Pricing\IPricingService;
use App\Services\Country\ICountryService;
use App\Services\CustomFields\ICustomService;
use App\Services\Tabs\ITabService;
use App\Services\UserGroup\IUserGroupService;
use App\Services\User\IUserService;
use App\Helpers\User\UserListHelper;
use App\Model\Common;
use App\Model\Dam;
use App\Model\Program;
use App\Model\Transaction;
use App\Model\TransactionDetail;
use App\Model\User;
use App\Model\UserGroup;
use App\Model\NotificationLog;
use App\Model\Category;
use App\Model\Email;
use App\Exceptions\Package\PackageNotFoundException;
use App\Transformers\Package\PackageProgramTransformer;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Validator;
use App\Enums\User\UserStatus;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\SystemRoles;
use App\Enums\User\UserEntity;
use App\Events\User\EntityEnrollmentByAdminUser;
use App\Events\User\EntityEnrollmentThroughUserGroup;
use App\Events\User\EntityUnenrollmentByAdminUser;
use App\Events\User\EntityUnenrollmentThroughUserGroup;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Package\PackagePermission;
use App\Enums\Category\CategoryStatus;
use Carbon\Carbon;
use App\Libraries\Timezone;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Redirect;

/**
*
*/
class PackageController extends AdminBaseController
{
    protected $layout = 'admin.theme.layout.master_layout';


    /**
     * @var IPackageService
     */
    private $packageService;

    /**
     * @var IPricingService
     */
    private $priceService;

    /**
     * @var ICustomService
     */
    private $customService;

    /**
     * @var IUserGroupService
     */
    private $userGroupService;

    /**
     * @var ITabService
     */
    private $tabService;

    /**
     * @var ICountryService
     */
    private $countryService;

    /**
     * @var IUserService
     */
    private $userService;

    /**
     * @var array
     */
    protected $countryList;

    public function __construct(
        Request $request,
        IPackageService $packageService,
        IPricingService $priceService,
        ICustomService $customService,
        ITabService $tabService,
        ICountryService $countryService,
        IUserGroupService $userGroupService,
        IUserService $userService
    ) {
        parent::__construct();
        // Stripping all html tags from the request body
        $input = $request::input();
        array_walk(
            $input,
            function (&$i) {
                (is_string($i)) ? $i = htmlentities($i) : '';
            }
        );
        $request::merge($input);

        $this->packageService = $packageService;
        $this->customService = $customService;
        //Pricing Service
        $this->priceService = $priceService;
        $this->customSer = $customService;
        //Tab Service
        $this->tabService = $tabService;
        $this->countryService = $countryService;
        $this->countryList = $this->countryService->supportedCurrencies();
        $this->userGroupService = $userGroupService;
        $this->userService = $userService;
        $this->theme_path = 'admin.theme';
    }

    /**
     * get add package method used to get add package
     */
    public function getAddPackage()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::ADD_PACKAGE) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/package.package_list') . 's' => 'package/list-template',
            trans('admin/package.add_package') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/package.add_package');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/package.add_package');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'package');
        $this->layout->content = view('admin.theme.package.addpackage')
            ->with('url', 'list-template');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getListTemplate()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::LIST_PACKAGES) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/package.package_list') . 's' => 'package/list-template'
        ];

        $breadcrumbs = Common::getBreadCrumbs($crumbs);
        $data = [
            "pagetitle" => trans("admin/package.package_list"),
            "pagedescription" => trans("admin/package.package_list_page_description"),
            "pageicon" => "fa fa-archive",
            "breadcrumbs" => $breadcrumbs,
            "mainmenu" => "package"
        ];

        return view("admin.theme.package.list_template", $data);
    }

    public function getListData()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::LIST_PACKAGES) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => []
                ]
            );
        }

        $filter_params = [];

        $filter_params["search_key"] = $this->request->input("search.value", null);
        $order_by_columns = [
            "created_at",
            "package_title",
            "package_shortname",
            "package_startdate",
            "package_enddate"
        ];

        $order_by_data = $this->request->get("order");
        $order_by_column_index = $order_by_data[0]["column"];
        $filter_params["order_by"] = array_get($order_by_columns, $order_by_column_index, "created_at");
        $filter_params["order_by_dir"] = $order_by_data[0]["dir"];

        $total_count = $this->packageService->getPackagesCount();
        $filtered_count = $this->packageService->getPackagesCount($filter_params);

        $filter_params["start"] = $this->request->input("start", 0);
        $filter_params["limit"] = $this->request->input("length", 10);
        $package_data = $this->packageService->getPackages(
            $filter_params,
            [
                "package_id", "package_title", "package_shortname", "package_slug", "package_startdate",
                "package_enddate", "program_ids", "user_ids", "user_group_ids", "status", "category_ids"
            ]
        )->toArray();

        return response()->json(
            [
                'recordsTotal' => $total_count,
                'recordsFiltered' => $filtered_count,
                'data' => $package_data
            ]
        );
    }

    /**
     * Validating and inserting package details
     */
    public function postAddPackage()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::ADD_PACKAGE) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $packageShortnameSlug = Input::get('package_shortname_slug', '');

        Validator::extend(
            'checkslug',
            function ($attribute, $value, $parameters) use ($packageShortnameSlug) {
                
                $slug = 'package-' . $value;
                
                if (!empty($packageShortnameSlug)) {
                    $slug .= '-' . $packageShortnameSlug;
                }
                
                try {
                    $packages_info = $this->packageService->getPackageBySlug('package_slug', $slug);
                    return false;
                } catch (PackageNotFoundException $e) {
                    return true;
                }
            }
        );

        Validator::extend(
            'checkslugregex',
            function ($attribute, $value, $parameters) {
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                    return true;
                }
                return false;
            }
        );

        Validator::extend(
            'datecheck',
            function ($attribute, $value, $parameters) {
                $package_start_date = Input::get('package_start_date');
                $package_end_date = Input::get('package_end_date');
                // if((strtotime($pub_date) + 432000) ) // 432000 is for 5 days. Skipped this calculation for now
                if ((strtotime($package_start_date) < strtotime($package_end_date))) {
                    return true;
                }

                return false;
            }
        );

        Validator::extend(
            'displaydatecheck',
            function ($attribute, $value, $parameters) {
                $package_display_start_date = Input::get('package_display_start_date');
                $package_display_end_date = Input::get('package_display_end_date');
                if ((strtotime($package_display_start_date) < strtotime($package_display_end_date))) {
                    return true;
                }

                return false;
            }
        );

        Validator::extend(
            'displaystartdatecheck',
            function ($attribute, $value, $parameters) {
                $package_start_date = Input::get('package_start_date');
                $package_display_start_date = Input::get('package_display_start_date');
                if ((strtotime($package_display_start_date) >= strtotime($package_start_date))) {
                    return true;
                }

                return false;
            }
        );
        Validator::extend(
            'displayenddatecheck',
            function ($attribute, $value, $parameters) {
                $package_end_date = Input::get('package_end_date');
                $package_display_end_date = Input::get('package_display_end_date');
                if ((strtotime($package_display_end_date) <= strtotime($package_end_date))) {
                    return true;
                }

                return false;
            }
        );

        
        $messages = [
                'displaystartdatecheck' => trans('admin/package.pack_disp_start_date_great_than_start_date'),
                'displayenddatecheck' => trans('admin/package.pack_disp_end_date_less_than_end_date'),
                'displaydatecheck' => trans('admin/package.pack_disp_end_date_greater_than_disp_start_date'),
                'datecheck' => trans('admin/package.pack_date_check'),
                'checkslug' => trans('admin/package.package_check_slug'),
                'checkslugregex' => trans('admin/package.package_check_slug_regex'),
                'package_title.required' => trans('admin/package.package_field_required'),
                'min' => trans('admin/package.shortname'),
        ];
        

        $rules = [
            'package_title' => 'Required',
            'package_slug' => 'Required|checkslugregex|checkslug',
            'propackage_shortname' => 'min:3',
            'package_start_date' => 'Required',
            'package_end_date' => 'Required|datecheck',
            'package_display_start_date' => 'Required|displaystartdatecheck',
            'package_display_end_date' => 'Required|displaydatecheck|displayenddatecheck',
            'visibility' => 'Required|in:yes,no',
            'status' => 'Required|in:active,inactive',
        ];

        if (config('app.ecommerce')) {
            $rules += ['sellability' => 'Required|in:yes,no'];
        } else {
            $rules += ['package_access' => 'Required|in:restricted_access,general_access'];
        }

        $validation = Validator::make(Input::all(), $rules, $messages);
        
        if ($validation->fails()) {
            return redirect('cp/package/add-package/')->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            $status = 'IN-ACTIVE';
            if (Input::get('status') == 'active') {
                $status = 'ACTIVE';
            }

            $mediaid = Input::get('banner', '');
            $package_keywords = explode(',', Input::get('package_tags'));

            if (empty($package_keywords)) {
                $package_keywords = [];
            }
            
            if (!empty(Input::get('package_shortname_slug', ''))) {
                $package_slug =
                    'package-' . Input::get('package_slug') . '-' . Input::get('package_shortname_slug', '');
            } else {
                $package_slug = 'package-' . Input::get('package_slug');
            }

            $packageData = [
                'package_title' => trim(Input::get('package_title')),
                'title_lower' => trim(strtolower(Input::get('package_title'))),
                'package_shortname' => Input::get('package_shortname'),
                'package_slug' => $package_slug,
                'package_description' => Input::get('package_description'),
                'package_startdate' => (int)Timezone::convertToUTC(
                    Input::get('package_start_date'),
                    Auth::user()->timezone,
                    'U'
                ),
                'package_enddate' => (int)Timezone::convertToUTC(
                    Carbon::createFromFormat('d-m-Y', Input::get('package_end_date'))->endOfDay(),
                    Auth::user()->timezone,
                    'U'
                ),
                'package_display_startdate' => (int)Timezone::convertToUTC(
                    Input::get('package_display_start_date'),
                    Auth::user()->timezone,
                    'U'
                ),
                'package_display_enddate' => (int)Timezone::convertToUTC(
                    Carbon::createFromFormat('d-m-Y', Input::get('package_display_end_date'))->endOfDay(),
                    Auth::user()->timezone,
                    'U'
                ),
                'package_duration' => '',
                'package_review' => 'no',
                'package_rating' => 'no',
                'package_visibility' => Input::get('visibility'),
                'package_keywords' => $package_keywords,
                'package_cover_media' => $mediaid,
                'duration' => [ // Duration may have more than one object like different subscription plans
                    [
                        'label' => 'Forever',
                        'days' => 'forever',
                    ],
                ],
                'package_categories' => [],
                'last_activity' => time(),
                'status' => $status,
                'created_by' => Auth::user()->username
            ];

            if (config('app.ecommerce')) {
                $packageData += [
                    'package_sellability' => Input::get('sellability'),
                    'package_access' => 'restricted_access'
                ];
            } else {
                $packageData += [
                    'package_access' => Input::get('package_access'),
                    'package_sellability' => 'yes'
                ];
            }

            $package_obj = $this->packageService->createPackage($packageData);
            $this->customService->insertNewProgramCustomFields($package_obj->package_id, 'packagefields');
            if (config('elastic.service')) {
                event(new PackageAdded($package_obj->package_id));
            }
            Dam::removeMediaRelation($mediaid, ['package_media_rel'], (int)$package_obj->package_id);
            
            if ($mediaid) {
                Dam::updateDAMSRelation($mediaid, 'package_media_rel', (int)$package_obj->package_id);
            }

            $msg = trans('admin/package.pack_add_success');
            $url = 'list-template';

            if (Input::get('sellability') === 'yes' && config('app.ecommerce')  === true) {
                return redirect(URL::to('cp/package/add-price/' . $package_slug))->with('success', $msg);
            }

            return redirect('cp/package/' . $url . '/' . $package_slug)->with('success', $msg);
        }
    }

    public function getPackagePrograms(PackageProgramTransformer $packageProgramTransformer, $package_id)
    {
        $response_data = [];
        $total_count = 0;
        $filtered_count = 0;
        $program_data = [];

        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_CHANNELS) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'recordsTotal' => $total_count,
                    'recordsFiltered' => $filtered_count,
                    'data' => $program_data
                ]
            );
        }

        $filter_params = [];

        $enrollment_status = $this->request->input("enrollment_status", "ASSIGNED");
        $filter_params["search_key"] = $this->request->input("search.value");

        $order_by_columns = [
            null,
            "program_title",
            null,
            "created_at",
            "created_by",
            "status"
        ];

        $order_by_column_index = $this->request->input("order.0.column", 3);

        if (array_has($order_by_columns, $order_by_column_index)) {
            $filter_params["order_by"] = $order_by_columns[$order_by_column_index];
            $filter_params["order_by_dir"] = $this->request->input("order.0.dir", "desc");
        }

        $filter_params["start"] = $this->request->input("start", 0);
        $filter_params["limit"] = $this->request->input("length", 10);

        $program_columns = ["program_id", "program_title", "program_slug", "status", "created_at", "created_by"];

        try {
            switch ($enrollment_status) {
                case "ASSIGNED":
                    $package_data =
                        $this->packageService->getPackageWithAssignedPrograms(
                            $package_id,
                            $filter_params,
                            ["*"],
                            $program_columns
                        );

                    $total_count = $package_data["programs_total_count"];
                    $filtered_count = $package_data["programs_filtered_count"];
                    $program_data = $packageProgramTransformer->transformForPackageProgramList(
                        $package_data["package"]->programs
                    );
                    break;
                case "NON_ASSIGNED":
                    $package_data =
                        $this->packageService->getPackageWithNoNAssignedPrograms(
                            $package_id,
                            $filter_params,
                            ["*"],
                            $program_columns
                        );

                    $total_count = $package_data["programs_total_count"];
                    $filtered_count = $package_data["programs_filtered_count"];
                    $program_data = $packageProgramTransformer->transformForPackageProgramList(
                        $package_data["programs"]
                    );
                    break;
            }
        } catch (PackageNotFoundException $e) {
            Log::error($e->getTraceAsString());
        }

        $response_data = array_merge(
            $response_data,
            [
                'recordsTotal' => $total_count,
                'recordsFiltered' => $filtered_count,
                'data' => $program_data
            ]
        );

        return response()->json($response_data);
    }

    /**
     * @param int $package_id
     * @return int
     */
    public function getPackageUsersCount($package_id)
    {
        $users_count = $this->packageService->getPackageUsersCount($package_id);

        return response()->json(["users_count" => $users_count]);
    }

    /**
     * @param $package_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function assignPrograms($package_id)
    {
        $flag = false;
        $message = null;

        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_CHANNELS) || config("app.ecommerce") === false) {
            $message = trans("admin/package.no_permission_to_manage_programs");
            return response()->json(["flag" => $flag, "message" => $message]);
        }

        $program_ids = array_map("intval", $this->request->input("program_ids", []));

        try {
            $this->packageService->assignProgramsToPackage($package_id, $program_ids);
            if (config('elastic.service')) {
                event(new ProgramAssigned($program_ids));
            }
            $flag = true;
            $message = trans("admin/package.programs_assigned");
        } catch (ApplicationException $e) {
            $message = $e->getMessage();
        }

        return response()->json(
            ["flag" => $flag, "message" => $message]
        );
    }

    /**
     * @param $package_id
     * @return \Illuminate\Http\JsonResponse
     */
    public function unAssignPrograms($package_id)
    {
        $flag = false;
        $message = null;

        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_CHANNELS) || config("app.ecommerce") === false) {
            $message = trans("admin/package.no_permission_to_manage_programs");
            return response()->json(["flag" => $flag, "message" => $message]);
        }

        $program_ids = array_map("intval", $this->request->input("program_ids", []));

        try {
            $this->packageService->unAssignProgramsFromPackage($package_id, $program_ids);
            if (config('elastic.service')) {
                event(new ProgramAssigned($program_ids));
            }
            $flag = true;
            $message = trans("admin/package.programs_un_assigned");
        } catch (ApplicationException $e) {
            $message = $e->getMessage();
        }

        return response()->json(
            ["flag" => $flag, "message" => $message]
        );
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function getEditPackage($slug = null)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EDIT_PACKAGE) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/package.package_list') . 's' => 'package/list-template',
            trans('admin/package.edit_package') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/package.edit_package');
        $this->layout->pageicon = 'fa fa-rss';
        $this->layout->pagedescription = trans('admin/package.edit_package');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'package');
        $pri_ser_data = $this->setPricingService($package);
        $tabs = [
            'p_id' => $package->package_id,
            'tabs' => isset($package->tabs) ? $package->tabs : null ,
        ];
        $pricing_info = array_get($pri_ser_data, 'pri_ser_data', null);
        $subscription_array = array_get($pricing_info, 'subscription', []);
        $packageCF = $this->customService->getFormCustomFields('packagefields');

        $package = $package->toArray();
        $this->layout->content = view('admin.theme.package.editpackage')
            ->with('pri_ser_info', $pri_ser_data)
            ->with('package', $package)
            ->with('tabs', $tabs)
            ->with('url', 'list-template')
            ->with('packageCF', $packageCF)
            ->with('package_users_count', $this->packageService->getPackageUsersCount($package["package_id"]))
            ->with('subscription_array', $subscription_array);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    /**
     * using this method update package details
     * @param string $slug
     * @return mixed
     */
    public function postEditPackage($slug = null)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EDIT_PACKAGE) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }

        Input::flash();
        $package_shortname = strtolower(Input::get('package_shortname'));
        $old_slug = $package->package_slug;
        $old_shortname = isset($package->package_shortname) ? strtolower($package->package_shortname) : [];
        $package_title = trim(strtolower(Input::get('package_title')));
        $packageShortnameSlug = Input::get('package_shortname_slug', '');
        Validator::extend(
            'checkslug',
            function (
                $attribute,
                $value,
                $parameters
            ) use (
                $packageShortnameSlug,
                $old_slug,
                $package_shortname,
                $old_shortname
            ) {
                $slug = 'package-' . $value;
                if (!empty($packageShortnameSlug)) {
                    $slug .= '-' . $packageShortnameSlug;
                }
                if ($old_slug == $slug && $old_shortname == $package_shortname) {
                    return true;
                }

                try {
                    $packages_info = $this->packageService->getPackageBySlug('package_slug', $slug);
                    return false;
                } catch (PackageNotFoundException $e) {
                    return true;
                }
            }
        );

        Validator::extend(
            'checkstatus',
            function ($attribute, $value, $parameters) {
                $parameters = array_filter($parameters);
                
                if ($value == 'inactive') {
                    if (is_array($parameters) && !empty($parameters)) {
                        return false;
                    }

                    return true;
                }

                return true;
            }
        );
        
        Validator::extend(
            'checkslugregex',
            function ($attribute, $value, $parameters) {
                
                if (preg_match('/^[a-zA-Z0-9-_]+$/', $value)) {
                    return true;
                }

                return false;
            }
        );

        Validator::extend(
            'datecheck',
            function ($attribute, $value, $parameters) {
                $package_start_date = Input::get('package_start_date');
                $package_end_date = Input::get('package_end_date');
                
                if ((strtotime($package_start_date) < strtotime($package_end_date))) {
                    return true;
                }

                return false;
            }
        );
        
        Validator::extend(
            'displaydatecheck',
            function ($attribute, $value, $parameters) {
                $package_display_start_date = Input::get('package_display_start_date');
                $package_display_end_date = Input::get('package_display_end_date');
                
                if ((strtotime($package_display_start_date) < strtotime($package_display_end_date))) {
                    return true;
                }

                return false;
            }
        );

        Validator::extend(
            'displaystartdatecheck',
            function ($attribute, $value, $parameters) {
                $package_start_date = Input::get('package_start_date');
                $package_display_start_date = Input::get('package_display_start_date');
                
                if ((strtotime($package_display_start_date) >= strtotime($package_start_date))) {
                    return true;
                }

                return false;
            }
        );

        Validator::extend(
            'displayenddatecheck',
            function ($attribute, $value, $parameters) {
                $package_end_date = Input::get('package_end_date');
                $package_display_end_date = Input::get('package_display_end_date');
                
                if ((strtotime($package_display_end_date) <= strtotime($package_end_date))) {
                    return true;
                }

                return false;
            }
        );

        $messages = [
            'displaystartdatecheck' => trans('admin/package.pack_disp_start_date_great_than_start_date'),
            'displayenddatecheck' => trans('admin/package.pack_disp_end_date_less_than_end_date'),
            'displaydatecheck' => trans('admin/package.pack_disp_end_date_greater_than_disp_start_date'),
            'datecheck' => trans('admin/package.pack_date_check'),
            'checkslug' => trans('admin/package.package_check_slug'),
            'checkslugregex' => trans('admin/package.package_check_slug_regex'),
            'feed_title.required' => trans('admin/package.package_field_required'),
            'min' => trans('admin/package.shortname'),
        ];

        $relations = '';

        if (isset($package->user_ids) && !empty($package->user_ids)) {
            $rel = $package->user_ids;
            $relations = implode(',', $rel);
            $messages['status.checkstatus'] = trans('admin/package.cannot_deactivate_program');
        }

        if (isset($package->user_group_ids) && !empty($package->user_group_ids)) {
            $rel = $package->user_group_ids;
            $relations = implode(',', $rel);
            $messages['status.checkstatus'] = trans('admin/package.usergroup_deactivate_program');
        }

        if (isset($package->program_ids) && !empty($package->program_ids)) {
            $rel = $package->program_ids;
            $relations = implode(',', $rel);
            $messages['status.checkstatus'] = trans('admin/package.child_deactivate_program');
        }

        $rules = [
            'package_title' => 'Required',
            'package_shortname' => 'min:3',
            'package_slug' => 'Required|checkslugregex|checkslug:' . $package->_id,
            'package_start_date' => 'Required',
            'package_end_date' => 'Required|datecheck',
            'package_display_start_date' => 'Required|displaystartdatecheck',
            'package_display_end_date' => 'Required|displaydatecheck|displayenddatecheck',
            'visibility' => 'Required|in:yes,no',
            'status' => 'Required|in:active,inactive|checkstatus:' . $relations,
        ];
        
        if (config('app.ecommerce')) {
            $rules += ['sellability' => 'Required|in:yes,no'];
        } else {
            $rules += ['package_access' => 'Required|in:restricted_access,general_access'];
        }

        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/package/edit-package/' . $slug)->withInput()
                ->withErrors($validation);
        } elseif ($validation->passes()) {
            if (Config::get('app.notifications.contentfeed.metadatachange')) {
                if (isset($package->relations['user_ids'])) {
                    $notify_user_ids_ary = [];
                    $notify_user_ids_ary = $package->relations['user_ids'];
                    $notif_msg = trans(
                        'admin/notifications.feedmetachange',
                        [
                            'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                            'feed' => $package->package_title
                        ]
                    );

                    NotificationLog::getInsertNotification(
                        $notify_user_ids_ary,
                        trans('admin/package.program'),
                        $notif_msg
                    );
                }

                if (isset($package->user_group_ids)) {
                    $notify_user_ids_ary = [];
                    foreach ($package->user_group_ids as $usergroupid) {
                        $usergroup_data = UserGroup::getUserGroupsUsingID((int)$usergroupid);
                        foreach ($usergroup_data as $usergroup) {
                            if (isset($usergroup->user_group_ids)) {
                                $notify_user_ids_ary = array_merge(
                                    $notify_user_ids_ary,
                                    $usergroup->relations['active_user_usergroup_rel']
                                );
                            }
                        }
                    }
                    if (!empty($notify_user_ids_ary)) {
                        $notif_msg = trans(
                            'admin/notifications.feedmetachange',
                            [
                                'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'feed' => $package->package_title
                            ]
                        );

                        NotificationLog::getInsertNotification(
                            $notify_user_ids_ary,
                            trans('admin/package.program'),
                            $notif_msg
                        );
                    }
                }
            }

            $status = 'IN-ACTIVE';
            $package_media_rel = 'package_media_rel';

            if (Input::get('status') == 'active') {
                $status = 'ACTIVE';
            }

            $mediaid = Input::get('banner', '');

            $package_keywords = explode(',', Input::get('package_tags'));
            
            if (empty($package_keywords)) {
                $package_keywords = [];
            }

            $package_slug = Input::get('package_slug');

            if ($old_slug != $package_slug || $old_shortname != $package_shortname) {
                $new_slug = 'package-' . $package_slug;
                if (!empty($packageShortnameSlug)) {
                    $new_slug .= '-' . $packageShortnameSlug;
                }
            } else {
                $new_slug = $old_slug;
            }
            
            $packageData = [
                'package_title' => trim(Input::get('package_title')),
                'title_lower' => trim(strtolower(Input::get('package_title'))),
                'package_shortname' => Input::get('package_shortname'),
                'package_slug' => $new_slug,
                'package_description' => Input::get('package_description'),
                'package_startdate' =>
                    (int)Timezone::convertToUTC(Input::get('package_start_date'), Auth::user()->timezone, 'U'),
                'package_enddate' => (int)Timezone::convertToUTC(
                    Carbon::createFromFormat('d-m-Y', Input::get('package_end_date'))->endOfDay(),
                    Auth::user()->timezone,
                    'U'
                ),
                'package_display_startdate' => (int)Timezone::convertToUTC(
                    Input::get('package_display_start_date'),
                    Auth::user()->timezone,
                    'U'
                ),
                'package_display_enddate' => (int)Timezone::convertToUTC(
                    Carbon::createFromFormat('d-m-Y', Input::get('package_display_end_date'))->endOfDay(),
                    Auth::user()->timezone,
                    'U'
                ),
                'package_duration' => '',
                'package_visibility' => Input::get('visibility'),
                'package_keywords' => $package_keywords,
                'package_cover_media' => $mediaid,
                'last_activity' => time(),
                'status' => $status,
                'updated_at' => time(),
                'updated_by' => Auth::user()->username,
                'updated_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            ];


            if (config('app.ecommerce')) {
                $packageData += [
                    'package_sellability' => Input::get('sellability'),
                    'package_access' => $package->package_access
                ];
            } else {
                $packageData += [
                    'package_access' => Input::get('package_access'),
                    'package_sellability' => $package->package_sellability
                ];
            }

            Dam::removeMediaRelation($package->package_cover_media, [$package_media_rel], (int)$package->package_id);
            
            if ($mediaid) {
                Dam::updateDAMSRelation($mediaid, $package_media_rel, (int)$package->package_id);
            }
            $this->packageService->updatePackage((int)$package->package_id, $packageData);
            if (config('elastic.service')) {
                event(new PackageEdited($package->package_id, $old_slug != $new_slug));
            }
            TransactionDetail::where('package_slug', '=', $slug)->update(['package_slug' => $new_slug]);

            return redirect('cp/package/list-template')
                ->with('success', trans('admin/package.pack_edit_success'));
        }
    }


    /**
     * @param $package
     * @return array
     */
    private function setPricingService($package)
    {
        $returnData = [];
        if (!collect($package)->isEmpty()) {
            $returnData['currency_support_list'] = $this->countryService->supportedCurrencies();
            $returnData['pri_service'] = 'enabled';
            $returnData['package_slug'] = $package->package_slug;
            $returnData['package_sellability'] = $package->package_sellability;

            $filter_params = [
                'sellable_id' => [$package->package_id],
                'sellable_type' => ['package'],
            ];

            $returnData += [
                'sellable_id' => $package->package_id,
                'sellable_type' => 'package'
            ];
            
            $pricing_info = $this->priceService->getPricingDetails($filter_params);
            if (!$pricing_info->isEmpty()) {
                $pricing_info = $pricing_info->first();
                $returnData['pri_ser_data'] = $pricing_info->toArray();
            } else {
                $returnData['pri_ser_data'] = [];
            }
        } else {
            $returnData['pri_service'] = 'disabled';
        }

        return $returnData;
    }

    /**
     * Using this method storing subscription data
     * @return mixed
     */
    public function postSaveSellability()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $data = Input::all();
        $rules = [
            'title' => 'Required|subdublicate|regex:/^[a-zA-Z0-9- ]+$/',
            'duration' => 'Required'
        ];

        Validator::extend(
            'subdublicate',
            function ($attribute, $value, $parameters) {
                $data = Input::all();
                return $this->priceService->checkDubSubscription(
                    $data['sellable_id'],
                    $data['sellable_type'],
                    $data['title']
                );
            }
        );

        Validator::extend(
            'mark_comapre',
            function ($attribute, $value, $parameters) {
                $data = Input::all();
                $str = explode("mark_", $attribute);
                if ($data[$str[1]] >= $data[$attribute]) {
                    return true;
                }
                return false;
            }
        );

        if ($data['subscription_type'] === "paid") {
            if (!empty($this->countryList)) {
                foreach ($this->countryList as $value) {
                    $rules = array_merge($rules, [strtolower($value['currency_code']) => "required"]);
                    $rules = array_merge($rules, ["mark_" . strtolower($value['currency_code']) => "mark_comapre"]);
                }
            }
        }
        $slug = Input::get('slug');
        $messages = [
            'subdublicate' => trans('admin/package.title_duplicated'),
            'mark_comapre' => trans('admin/package.mark_comapre'),
            'regex' => trans('admin/package.regex_error')
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        if ($validation->fails()) {
            return redirect('cp/package/edit-package/' . $slug)->withInput()
                ->withErrors($validation)->with('pricing', 'enabled')->with('pricing_action', 'add');
        } elseif ($validation->passes()) {
            $msg = trans('admin/program.package_add_success');
            $type = ['type' => Input::get('subscription_type')];
            $duration = ['duration_count' => $data['duration']];
            $data = array_merge($data, $type, $duration);
            $pv_data = $this->priceService->addPrice($data);
            $pv_data = $this->priceService->priceFirst($data);
            $this->priceService->addSubscriptions($pv_data, $data);

            return redirect('cp/package/edit-package/' . Input::get('slug'))
                ->with('success_price', trans('admin/package.subscription_add_msg'))
                ->with('pricing', 'enabled');
        }
    }

    /**
     * Using this method can edit package subscription
     */
    public function postEditSubscription()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $this->layout = null;
        $data = Input::all();
        $editItem['sellable_type'] = $data['sellable_type'];
        $editItem['sellable_id'] = $data['sellable_id'];
        $editItem['package_slug'] = $data['package_slug'];
        $sellableEntity = $this->priceService->getPricing($data);
        if (!empty($sellableEntity)) {
            foreach ($sellableEntity['subscription'] as $eachval) {
                if ($eachval['title'] === $data['slug']) {
                    $editItem['subdata'] = $eachval;
                }
            }
        }
        $countryList = $this->countryService->supportedCurrencies();
        echo view(
            'admin/theme/package/pricing/edit_subscription',
            ['subscription' => $editItem, 'currency_support_list' => $countryList]
        );
    }

    /**
     * Save edit sellability method to store package subscription details
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSaveEditSellability()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $data = Input::all();
        $rules = [
            'title' => 'Required|subdublicate|regex:/^[a-zA-Z0-9- ]+$/',
            'duration' => 'Required'
        ];

        Validator::extend(
            'subdublicate',
            function ($attribute, $value, $parameters) {
                $data = Input::all();
                return $this->priceService->checkDubSubscription($data['sellable_id'], $data['sellable_type'], $data['title'], $data['ctitle']);
            }
        );

        Validator::extend(
            'mark_comapre',
            function ($attribute, $value, $parameters) {
                $data = Input::all();
                $str = explode("mark_", $attribute);
                if ($data[$str[1]] >= $data[$attribute]) {
                    return true;
                }
                return false;
            }
        );

        if ($data['subscription_type'] === "paid") {
            if (!empty($this->countryList)) {
                foreach ($this->countryList as $value) {
                    $rules = array_merge($rules, [strtolower($value['currency_code']) => "required"]);
                    $rules = array_merge($rules, ["mark_" . strtolower($value['currency_code']) => "mark_comapre"]);
                }
            }
        }

        $slug = Input::get('package_slug');
        $messages = [
            'subdublicate' => trans('admin/package.title_duplicated'),
            'mark_comapre' => trans('admin/package.mark_comapre'),
            'regex' => trans('admin/package.regex_error')
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        $json = [];
        foreach ($data as $key => $value) {
            $json[$key] = '';
        }
        foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
            $json = array_merge($json, [$key => $eachError[0]]);
        }
        if ($validation->fails()) {
            $json = array_merge($json, ['success' => 'error']);
            return response()->json($json);
        } elseif ($validation->passes()) {
            $msg = trans('admin/package.package_add_success');
            $type = ['type' => Input::get('subscription_type')];
            $duration = ['duration_count' => $data['duration']];
            $data = array_merge($data, $type, $duration);
            $pv_data = $this->priceService->priceFirst($data);
            $this->priceService->updateSubscription($pv_data, $data);
            $json = ['success' => URL::to("cp/package/route-to-package/$slug")];
            return response()->json($json);
        }
    }

    /**
     * @param $slug
     * @return mixed
     */
    public function getRouteToPackage($slug)
    {
        return redirect('cp/package/edit-package/' . $slug)
            ->with('success_price', trans('admin/package.subscription_edit_msg'))
            ->with('pricing', 'enabled');
    }

    /**
     * @param $subTitle
     * @param $sal_id
     * @param $sal_type
     * @param $package_slug
     * @return mixed
     */
    public function getDeleteSubscription($subTitle, $sal_id, $sal_type, $package_slug)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $data = [
            'sellable_id' => $sal_id,
            'sellable_type' => $sal_type,
            'title' => $subTitle
        ];
        $pv_data = $this->priceService->priceFirst($data);
        $this->priceService->deleteSubscriptions($pv_data, $data);
        return redirect('cp/package/edit-package/' . $package_slug)
            ->with('pricing', 'enabled')
            ->with('success_price', trans('admin/package.subscription_delete_msg'));
    }

    /**
     * @param $slug
     * @param null $p_type
     * @return mixed
     */
    public function getAddPrice($slug, $p_type = null)
    {
        return redirect('cp/package/edit-package/' . $slug)
                ->with('pricing', 'enabled')
                ->with('ap_success', trans('admin/package.pack_add_success'));
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function postSaveTab()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_TABS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $data = Input::all();
        $rules = [
            'title' => 'Required|regex:/^[a-zA-Z0-9-_ ]+$/|subdublicate',
            'description' => 'Required'
        ];

        Validator::extend(
            'subdublicate',
            function ($attribute, $value, $parameters) {
                $data = Input::all();
                return $this->tabService->checkDuplicateTab($data['p_id'], $data['p_type'], $data['title']);
            }
        );


        $slug = Input::get('package_slug');
        $messages = [
            'subdublicate' => trans('admin/package.title_duplicated'),
            'title.regex' => trans('admin/package.regex_error')
        ];


        $validation = Validator::make(Input::all(), $rules, $messages);
        $json = [];
        foreach ($data as $key => $value) {
            $json[$key] = '';
        }
        foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
            $json = array_merge($json, [$key => $eachError[0]]);
        }
        if ($validation->fails()) {
            $json = array_merge($json, ['success' => 'error']);
            return response()->json($json);
        } elseif ($validation->passes()) {
            $this->tabService->savePackageTab($data);
            $json = ['success' => URL::to("cp/package/route-to/{$slug}/".array_get($data, 'p_type', ''))];
            return response()->json($json);
        }
    }


    /**
     * @param $slug
     * @param $p_type
     * @return mixed
     */
    public function getRouteTo($slug, $p_type = null)
    {
        return redirect('cp/package/edit-package/'.$slug)
            ->with('success_tab', trans('admin/package.tab_add_success'))
            ->with('tab', 'enabled');
    }

    /**
     * @param $pid
     * @param $slug
     */
    public function postEditTab($pid, $slug)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_TABS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $this->layout = null;
        $data = $this->tabService->getPackageTabBySlug($pid, $slug);
        $data = array_merge($data, ['pid' => $pid]);
        echo view(
            'admin/theme/package/tabs/edit',
            ['tabs' => $data]
        );
    }

    /**
     * @return \Illuminate\Http\JsonResponse
     */
    public function postEditSave()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_TABS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $data = Input::all();
        $rules = [
            'title' => 'Required|regex:/^[a-zA-Z0-9-_ ]+$/|subdublicate',
            'description' => 'Required'
        ];

        Validator::extend(
            'subdublicate',
            function ($attribute, $value, $parameters) {
                $data = Input::all();
                return $this->tabService->checkDuplicateTab($data['p_id'], $data['p_type'], $data['title'], $data['ctitle']);
            }
        );

        $slug = Input::get('package_slug');
        $messages = [
            'subdublicate' => trans('admin/package.title_duplicated'),
            'title.regex' => trans('admin/package.regex_error')
        ];
        $validation = Validator::make(Input::all(), $rules, $messages);
        $json = [];
        foreach ($data as $key => $value) {
            $json[$key] = '';
        }
        foreach ($validation->getMessageBag()->toArray() as $key => $eachError) {
            $json = array_merge($json, [$key => $eachError[0]]);
        }
        if ($validation->fails()) {
            $json = array_merge($json, ['success' => 'error']);
            return response()->json($json);
        } elseif ($validation->passes()) {
            $this->tabService->saveEditPackageTab($data);
            $json = ['success' => URL::to("cp/package/route-to-edit/{$slug}/".array_get($data, 'p_type', ''))];
            return response()->json($json);
        }
    }

    /**
     * @param $slug
     * @param $p_type
     * @return mixed
     */
    public function getRouteToEdit($slug, $p_type = null)
    {
        return redirect('cp/package/edit-package/' . $slug)
            ->with('success_tab', trans('admin/package.tab_edit_success'))
            ->with('tab', 'enabled');
    }

    /**
     * @param $p_id
     * @param $slug
     * @param $p_type
     * @return mixed
     */
    public function getDeletePackageTab($p_id, $slug, $p_type = null)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_TABS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }
        $p_slug = $this->tabService->deletePackageTab($p_id, $slug);
        return redirect('cp/package/edit-package/' . $p_slug)
                ->with('success_tab', trans('admin/package.tab_delete_success'))
                ->with('tab', 'enabled');
    }

    /**
     * Get package assigned user list
     */
    public function getUserList(UserListHelper $user_list_helper)
    {

        $totalCount = 0;
        $filteredCount = 0;
        $data = [];

        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USERS) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'recordsTotal' => $totalCount,
                    'recordsFiltered' => $filteredCount,
                    'data' => $data,
                ]
            );
        }

        $filter_params = [];
        $filter_params["start"] = Input::get("start", 0);
        $filter_params["limit"] = Input::get("length", 10);
        $order_by_data = Input::get("order", []);
        $search_data = Input::get('search');

        $order_by_column_index = null;
        if (!empty($order_by_data[0]["column"])) {
            $order_by_column_index = $order_by_data[0]["column"];
        }

        if (!empty($order_by_data[0]["dir"])) {
            $filter_params["order_by_dir"] = $order_by_data[0]["dir"];
        } else {
            $filter_params["order_by_dir"] = "desc";
        }

        if (!empty($search_data['value'])) {
            $filter_params["search_key"] = $search_data['value'];
        } else {
            $filter_params["search_key"] = null;
        }

        $filter_params["status"] = [UserStatus::ACTIVE];

        $package_id = Input::get("package_id");
        $filter_params["enrollment_status"] = Input::get("enrollment_status", "ASSIGNED");
        $columns = ["", "username", "firstname", "email", "created_at", "status"];
        $filter_params["order_by"] = !is_null($order_by_column_index)? $columns[$order_by_column_index] :
                                        "created_at";
        $users_data = $this->packageService->getPackageUsers($package_id, $filter_params);
        foreach ($users_data["data"] as $user) {
            $tmpArray = [
                ($this->request->user()->uid !== $user["id"])?
                    $user_list_helper->generateUserListCheckbox($user["id"]): "",
                $user["username"],
                $user["firstname"]." ". $user["lastname"],
                $user["email"],
                Timezone::convertFromUTC(
                    "@" . $user["created_at"],
                    Auth::user()->timezone,
                    config("app.date_format")
                ),
                $user["status"],
            ];

            $data[] = $tmpArray;
        }

        $totalCount = $users_data["total_users_count"];
        $filteredCount = $users_data["filtered_users_count"];
        return response()->json(
            [
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $data,
            ]
        );
    }

    /**
     * Get package assigned user groups list
     */
    public function getUserGroupList()
    {
        $totalCount = 0;
        $filteredCount = 0;
        $data = [];

        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USER_GROUPS) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'recordsTotal' => $totalCount,
                    'recordsFiltered' => $filteredCount,
                    'data' => $data,
                ]
            );
        }

        $filter_params = [];
        $filter_params["start"] = Input::get("start", 0);
        $filter_params["limit"] = Input::get("length", 10);
        $order_by_data = Input::get("order", []);
        $search_data = Input::get('search');

        $order_by_column_index = null;
        if (!empty($order_by_data[0]["column"])) {
            $order_by_column_index = $order_by_data[0]["column"];
        }

        if (!empty($order_by_data[0]["dir"])) {
            $filter_params["order_by_dir"] = $order_by_data[0]["dir"];
        } else {
            $filter_params["order_by_dir"] = "desc";
        }

        if (!empty($search_data['value'])) {
            $filter_params["search_key"] = $search_data['value'];
        } else {
            $filter_params["search_key"] = null;
        }

        $filter_params["status"] = [UserStatus::ACTIVE];

        $package_id = Input::get("package_id");
        $filter_params["enrollment_status"] = Input::get("enrollment_status", "ASSIGNED");
        $columns = ["", "usergroup_name", "usergroup_email", "created_at", "status"];
        $filter_params["order_by"] = !is_null($order_by_column_index)? $columns[$order_by_column_index] :
                                        "created_at";
        $user_groups_data = $this->packageService->getPackageUserGroups($package_id, $filter_params);
        foreach ($user_groups_data["data"] as $user_group) {
            $tmpArray = [
                "<input type='checkbox' value='{$user_group["ugid"]}'>",
                $user_group["usergroup_name"],
                $user_group["usergroup_email"],
                Timezone::convertFromUTC(
                    "@" . $user_group["created_at"],
                    Auth::user()->timezone,
                    config("app.date_format")
                ),
                $user_group["status"],
            ];

            $data[] = $tmpArray;
        }

        $totalCount = $user_groups_data["total_user_groups_count"];
        $filteredCount = $user_groups_data["filtered_user_groups_count"];

        return response()->json(
            [
                'recordsTotal' => $totalCount,
                'recordsFiltered' => $filteredCount,
                'data' => $data,
            ]
        );
    }

    /**
     * Assign user to package
     * @param string $slug
     */
    public function postEnrollUser($slug = null)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USERS) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'flag' => false,
                    'message' => trans('admin/package.no_permission_to_manage_users')
                ]
            );
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
            
            if (empty($package->program_ids)) {
                return response()->json(
                    [
                        'flag' => false,
                        'message' => trans('admin/package.user_assign_channel_error')
                    ]
                );
            } else {
                $user_ids = $this->request->input('user_ids', []);
                $user_ids = $user_ids ? array_map("intval", $user_ids) : [];
                if (!empty($user_ids)) {
                    foreach ($user_ids as $value) {
                        $this->packageService->mapUserAndPackage($package->package_id, $user_ids);
                        User::addUserRelation($value, ['user_parent_feed_rel'], $package->package_id);
                        $this->updateUserRelation($package, $user_ids);

                        if (config('elastic.service')) {
                            event(new PackageAssigned($package->package_id));
                        }
                        return response()->json(
                            [
                                'flag' => true,
                                'message' => trans('admin/package.user_assigned')
                            ]
                        );
                    }
                } else {
                    return response()->json(
                        [
                            'flag' => false,
                            'message' =>  trans('admin/package.user_assign_error')
                        ]
                    );
                }
            }
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
    }

    /**
     * UnAssign user to package
     * @param string $slug
     */
    public function postUnEnrollUser($slug = null)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USERS) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'flag' => false,
                    'message' => trans('admin/package.no_permission_to_manage_users')
                ]
            );
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
            $user_ids = $this->request->input('user_ids', []);
            $user_ids = $user_ids ? array_map("intval", $user_ids) : [];

            if (!empty($user_ids)) {
                foreach ($user_ids as $value) {
                    $this->packageService->unMapUserAndPackage($package->package_id, $user_ids);
                    User::removeUserRelation($value, ['user_parent_feed_rel'], $package->package_id);
                    $this->removeUserRelation($package, $user_ids);
                    if (config('elastic.service')) {
                        event(new PackageAssigned($package->package_id));
                    }
                    return response()->json(
                        [
                            'flag' => true,
                            'message' => trans('admin/package.user_unassigned')
                        ]
                    );
                }
            } else {
                return response()->json(
                    [
                        'flag' => false,
                        'message' => trans('admin/package.user_assign_error')
                    ]
                );
            }
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
    }

    /**
     * To update assigned user transaction details
     * @param array $user_ids
     * @param collection $package
     */
    private function updateUserRelation($package, $user_ids)
    {
        $notify_user_ids = $user_ids;
        $notify_log_flg = true;

        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
        $role_id = array_get($role_info, 'id', '');
        $context_id = array_get($context_info, 'id', '');

        foreach ($user_ids as $value) {
            event(
                new EntityEnrollmentByAdminUser(
                    $value,
                    UserEntity::PACKAGE,
                    $package->package_id
                )
            );

            $userdetails = User::getUserDetailsByID($value)->toArray();
            $email = isset($userdetails['email']) ? $userdetails['email'] : '' ;
            $program_ids = $package->program_ids;
            $now = time();
            if (!empty($program_ids)) {
                foreach ($program_ids as $channel_id) {
                    $this->roleService->mapUserAndRole(
                        $value,
                        $context_id,
                        $role_id,
                        $channel_id
                    );
                    $channel = Program::getProgramDetailsByID($channel_id);
                    $trans_id = Transaction::uniqueTransactionId();

                    $transaction = [
                        'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                        'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                        'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                        'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                        'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                        'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                        'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                        'trans_level' => 'user',
                        'id' => $value,
                        'created_date' => time(),
                        'email' => $email,
                        'trans_id' => (int)$trans_id,
                        'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                        'access_mode' => 'assigned_by_admin',
                        'added_by' => Auth::user()->username,
                        'added_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                        'created_at' => time(),
                        'updated_at' => time(),
                        'type' => 'subscription',
                        'status' => 'COMPLETE', // This is transaction status
                    ];

                    $transaction_details = [
                        'trans_level' => 'user',
                        'id' => $value,
                        'trans_id' => (int)$trans_id,
                        'program_id' => $channel['program_id'],
                        'package_id' => $package->package_id,
                        'program_slug' => $channel['program_slug'],
                        'type' => 'content_feed',
                        'program_sub_type' => 'collection',
                        'program_title' => $channel['program_title'],
                        'duration' => [ // Using the same structure from duration master
                            'label' => 'Forever',
                            'days' => 'forever',
                        ],
                        'start_date' => '', // Empty since the duration is forever
                        'end_date' => '', // Empty since the duration is forever
                        'created_at' => time(),
                        'updated_at' => time(),
                        'status' => 'COMPLETE',
                    ];
                    // Add record to user transaction table
                    Transaction::insert($transaction);
                    TransactionDetail::insert($transaction_details);
                }
            }

            $trans_id = Transaction::uniqueTransactionId();

            $transaction = [
                'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                'trans_level' => 'user',
                'id' => $value,
                'created_date' => time(),
                'email' => $email,
                'trans_id' => (int)$trans_id,
                'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                'access_mode' => 'assigned_by_admin',
                'added_by' => Auth::user()->username,
                'added_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'created_at' => time(),
                'updated_at' => time(),
                'type' => 'subscription',
                'status' => 'COMPLETE', // This is transaction status
            ];

            $transaction_details = [
                'trans_level' => 'user',
                'id' => $value,
                'trans_id' => (int)$trans_id,
                'program_id' => (int)$package->package_id,
                'package_id' => (int)$package->package_id,
                'program_slug' => $package->package_slug,
                'type' => 'content_feed',
                'program_sub_type' => 'collection',
                'program_title' => $package->package_title,
                'duration' => [ // Using the same structure from duration master
                    'label' => 'Forever',
                    'days' => 'forever',
                ],
                'start_date' => '', // Empty since the duration is forever
                'end_date' => '', // Empty since the duration is forever
                'created_at' => time(),
                'updated_at' => time(),
                'status' => 'COMPLETE',
            ];
            // Add record to user transaction table
            Transaction::insert($transaction);
            TransactionDetail::insert($transaction_details);
            // Send Notifications to the user
            if (Config::get('app.notifications.contentfeed.feedadd') && $notify_log_flg) {
                $notify_log_flg = false;
                $notif_msg = trans('admin/notifications.feedadd', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $package->package_title]);
                // Notification::getInsertNotification($value, trans('admin/program.program'), $notif_msg);
                NotificationLog::getInsertNotification($notify_user_ids, trans('admin/program.program'), $notif_msg);
            }
            // Send Mail Notifications to the user
            if (Config::get('email.notifications.contentfeed.feedadd')) {
                $email = Email::getEmail('feed_assignment')->first()->toArray();
                if (isset($email['body']) && isset($email['subject'])) {
                    $body = str_replace('[:adminname:]', Auth::user()->firstname . ' ' . Auth::user()->lastname, $email['body']);
                    $body = str_replace('[:feed:]', $package->package_title, $body);
                    Common::sendMailHtml($body, $email['subject'], $email);
                }
            }
        }
    }

    /**
     * To update unassigned user transaction details
     * TODO ACCESS REQUEST NEED TO CONFIRM WITH TEAM MEMBER
     * @param array $user_ids
     * @param collection $package
     */
    private function removeUserRelation($package, $user_ids)
    {
        $program_context = $this->roleService->getContextDetails(Contexts::PROGRAM);
        foreach ($user_ids as $value) {
            $program_ids = $package->program_ids;
            
            event(
                new EntityUnenrollmentByAdminUser(
                    $value,
                    UserEntity::PACKAGE,
                    (int)$package->package_id
                )
            );

            if (!empty($program_ids)) {
                foreach ($program_ids as $channel_id) {
                    $this->roleService->unmapUserAndRole($value, $program_context["id"], $channel_id);
                    TransactionDetail::updateStatusByLevel(
                        'user',
                        $value,
                        (int)$channel_id,
                        ['status' => 'IN-ACTIVE'],
                        $type = 'collection',
                        (int)$package->package_id
                    );
                }

                TransactionDetail::updateStatusByLevel(
                    'user',
                    $value,
                    (int)$package->package_id,
                    ['status' => 'IN-ACTIVE'],
                    $type = 'collection',
                    (int)$package->package_id
                );
            }
        }
    }

    /**
     * Un enroll user groups in packages
     * @param string $slug
     */
    public function postUnEnrollUserGroup($slug = null)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USER_GROUPS) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'flag' => false,
                    'message' => trans('admin/package.no_permission_to_manage_user_groups')
                ]
            );
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
            $user_group_ids = $this->request->input('user_group_ids', []);
            $user_group_ids = $user_group_ids ? array_map("intval", $user_group_ids) : [];
            if (!empty($user_group_ids)) {
                $user_ids = $this->userGroupService->getUsersByUserGroupIds($user_group_ids);
                $user_ids = array_unique(array_flatten($user_ids));
                foreach ($user_ids as $value) {
                    $this->userService->removeUserRelation($value, ['user_parent_feed_rel'], $package->package_id);
                }
                $this->packageService->unMapUserGroupAndPackage($package->package_id, $user_group_ids);
                $this->removeUserGroupRelation($package, $user_group_ids);
                if (config('elastic.service')) {
                    event(new PackageAssigned($package->package_id));
                }
                return response()->json(
                    [
                        'flag' => true,
                        'message' => trans('admin/package.user_group_unassigned')
                    ]
                );
            } else {
                return response()->json(
                    [
                        'flag' => false,
                        'message' => trans('admin/package.user_group_assign_error')
                    ]
                );
            }
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
    }

    /**
     * Enroll user groups in packages
     * @param string $slug
     */
    public function postEnrollUserGroup($slug = null)
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USER_GROUPS) || config("app.ecommerce") === false) {
            return response()->json(
                [
                    'flag' => false,
                    'message' => trans('admin/package.no_permission_to_manage_user_groups')
                ]
            );
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
            
            if (empty($package->program_ids)) {
                return response()->json(
                    [
                        'flag' => false,
                        'message' => trans('admin/package.user_group_assign_channel_error')
                    ]
                );
            } else {
                $user_group_ids = $this->request->input('user_group_ids', []);
                $user_group_ids = $user_group_ids ? array_map("intval", $user_group_ids) : [];
                
                if (!empty($user_group_ids)) {
                    $user_ids = $this->userGroupService->getUsersByUserGroupIds($user_group_ids);
                    $user_ids = array_unique(array_flatten($user_ids));
                    foreach ($user_ids as $value) {
                        $this->userService->addUserRelation($value, ['user_parent_feed_rel'], $package->package_id);
                    }
                    $this->packageService->mapUserGroupAndPackage($package->package_id, $user_group_ids);
                    $this->updateUserGroupRelation($package, $user_group_ids);
                    if (config('elastic.service')) {
                        event(new PackageAssigned($package->package_id));
                    }
                    return response()->json(
                        [
                            'flag' => true,
                            'message' => trans('admin/package.user_group_assigned')
                        ]
                    );
                } else {
                    return response()->json(
                        [
                            'flag' => false,
                            'message' =>  trans('admin/package.user_group_assign_error')
                        ]
                    );
                }
            }
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
    }

    /**
     * @param array $user_group_ids
     * @param collection $package
     */
    private function removeUserGroupRelation($package, $user_group_ids)
    {
        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
        $context_id = array_get($context_info, 'id', '');

        foreach ($user_group_ids as $value) {
            try {
                $usergroup_info = $this->userGroupService->getUserGroupDetails($value);
                $usergroup_rel = $usergroup_info->relations;
                $user_usergroup_rel_ids = array_get($usergroup_rel, 'active_user_usergroup_rel', '');
            } catch (UserGroupNotFoundException $e) {
                Log::info(trans('admin/user.usergroup_not_found', ['id' => $value]));
            }

            if (!empty($user_usergroup_rel_ids)) {
                foreach ($user_usergroup_rel_ids as $user_id) {
                    event(
                        new EntityUnenrollmentThroughUserGroup(
                            $user_id,
                            UserEntity::PACKAGE,
                            $package->package_id,
                            $value
                        )
                    );

                    $program_ids = $package->program_ids;
                    if (!empty($program_ids)) {
                        foreach ($program_ids as $channel_id) {
                            $this->roleService->unmapUserAndRole((int)$user_id, $context_id, $channel_id);
                        }
                    }
                }
            }
                
            TransactionDetail::updateStatusByLevel(
                'usergroup',
                $value,
                (int)$package->package_id,
                ['status' => 'IN-ACTIVE'],
                'collection',
                (int)$package->package_id
            );
        }
    }

    /**
     * To update user group and package relation
     * @param array $user_group_ids
     * @param collection $package
     */
    private function updateUserGroupRelation($package, $user_group_ids)
    {
        $role_info = $this->roleService->getRoleDetails(SystemRoles::LEARNER, ['context']);
        $context_info = $this->roleService->getContextDetails(Contexts::PROGRAM, false);
        $role_id = array_get($role_info, 'id', '');
        $context_id = array_get($context_info, 'id', '');
        $notify_user_ids_ary = [];
        $now = time();
        foreach ($user_group_ids as $value) {
            try {
                $usergroup_info = $this->userGroupService->getUserGroupDetails($value);
                $usergroup_rel = $usergroup_info->relations;
                $user_usergroup_rel_ids = array_get($usergroup_rel, 'active_user_usergroup_rel', '');
            } catch (UserGroupNotFoundException $e) {
                Log::info(trans('admin/user.usergroup_not_found', ['id' =>  $value]));
            }

            if (!empty($user_usergroup_rel_ids)) {
                foreach ($user_usergroup_rel_ids as $user_id) {
                    event(
                        new EntityEnrollmentThroughUserGroup(
                            $user_id,
                            UserEntity::PACKAGE,
                            $package->package_id,
                            $value
                        )
                    );

                    $program_ids = $package->program_ids;
                    if (!empty($program_ids)) {
                        foreach ($program_ids as $channel_id) {
                            $this->roleService->mapUserAndRole((int)$user_id, $context_id, $role_id, $channel_id);
                        }
                    }
                }
            }

            $trans_id = Transaction::uniqueTransactionId();
            $transaction = [
                'DAYOW' => Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'l'),
                'DOM' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'j'),
                'DOW' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'w'),
                'DOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'z'),
                'MOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'n'),
                'WOY' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'W'),
                'YEAR' => (int)Timezone::convertToUTC('@' . $now, Auth::user()->timezone, 'Y'),
                'trans_level' => 'usergroup',
                'id' => $value,
                'created_date' => time(),
                'trans_id' => (int)$trans_id,
                'full_trans_id' => 'ORD' . sprintf('%07d', (string)$trans_id),
                'access_mode' => 'assigned_by_admin',
                'added_by' => Auth::user()->username,
                'added_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'created_at' => time(),
                'updated_at' => time(),
                'type' => 'subscription',
                'status' => 'COMPLETE', // This is transaction status
            ];

            $transaction_details = [
                'trans_level' => 'usergroup',
                'id' => $value,
                'trans_id' => (int)$trans_id,
                'program_id' => $package->package_id,
                'package_id' => $package->package_id,
                'program_slug' => $package->package_slug,
                'type' => 'content_feed',
                'program_sub_type' => 'collection',
                'program_title' => $package->package_title,
                'duration' => [ // Using the same structure from duration master
                    'label' => 'Forever',
                    'days' => 'forever',
                ],
                'start_date' => '', // Empty since the duration is forever
                'end_date' => '', // Empty since the duration is forever
                'created_at' => time(),
                'updated_at' => time(),
                'status' => 'COMPLETE',
            ];

            Transaction::insert($transaction);
            TransactionDetail::insert($transaction_details);

            if (Config::get('app.notifications.contentfeed.feedadd')) {
                $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                foreach ($usergroup_data as $usergroup) {
                    if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                        $notify_user_ids_ary = array_merge($notify_user_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                    }
                }
            }

            // Send Mail Notifications to the user
            if (Config::get('email.notifications.contentfeed.feedadd')) {
                $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                $emailtemplate = Email::getEmail('feed_assignment')->first()->toArray();
                foreach ($usergroup_data as $usergroup) {
                    if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                        foreach ($usergroup['relations']['active_user_usergroup_rel'] as $user) {
                            $userdetails = User::getUserDetailsByID($user)->toArray();
                            if (isset($emailtemplate['body']) && isset($emailtemplate['subject'])) {
                                $body = str_replace('[:adminname:]', Auth::user()->firstname . ' ' . Auth::user()->lastname, $emailtemplate['body']);
                                $body = str_replace('[:feed:]', $package->package_title, $body);
                                Common::sendMailHtml($body, $emailtemplate['subject'], $userdetails['email']);
                            }
                        }
                    }
                }
            }
        }

        if (!empty($notify_user_ids_ary)) {
            $notif_msg = trans('admin/notifications.feedadd', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'feed' => $package->package_title]);
            NotificationLog::getInsertNotification($notify_user_ids_ary, trans('admin/program.program'), $notif_msg);
            $notify_user_ids_ary = [];
        }
    }

    /**
     * view package details using package slug
     * @param string $slug
     */
    public function getPackageDetails($slug = '')
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::VIEW_PACKAGE_DETAILS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
            $package_program_rel = $package->programs()->get(['program_title'])->toArray();
            $package = $package->toArray();
            $package['package_startdate'] = Timezone::convertFromUTC('@' . $package['package_startdate'], Auth::user()->timezone, config('app.date_format'));
            $package['package_enddate'] = Timezone::convertFromUTC('@' . $package['package_enddate'], Auth::user()->timezone, config('app.date_format'));
            $package['package_display_startdate'] = Timezone::convertFromUTC('@' . $package['package_display_startdate'], Auth::user()->timezone, config('app.date_format'));
            $package['package_display_enddate'] = Timezone::convertFromUTC('@' . $package['package_display_enddate'], Auth::user()->timezone, config('app.date_format'));
            $media = '';
            
            if (isset($package['package_cover_media'])) {
                $media = Dam::getDAMSAssetsUsingID($package['package_cover_media']);
                if (!empty($media)) {
                    $media = $media[0];
                }
            }
            
            return view('admin.theme.package.packagedetail')
                ->with('program_rel', $package_program_rel)
                ->with('package', $package)
                ->with('media', $media);
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
    }

    /**
     * Delete package using slug
     * Remove category relations needs to implement
     * @param  string $slug
     */
    public function getDeletePackage($slug = '')
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::DELETE_PACKAGE) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);

            
            if (isset($package->user_ids) && !empty($package->user_ids)) {
                return redirect('cp/package/list-template')
                    ->with('error', trans('admin/package.pack_feed_delete_rel_error'));
            }


            if (isset($package->user_group_ids) && !empty($package->user_group_ids)) {
                return redirect('cp/package/list-template')
                    ->with('error', trans('admin/package.pack_feed_delete_rel_error'));
            }

            if (isset($package->category_ids) && !empty($package->category_ids)) {
                foreach ($package->category_ids as $category) {
                    Category::removeCategoryRelation($category, ['assigned_packages'], (int)$package->package_id);
                }
            }
            if (config('elastic.service')) {
                event(new PackageRemoved($package->package_id));
            }
            $this->packageService->deletePackage((int)$package->package_id);

            return redirect('cp/package/list-template')
                ->with('success', trans('admin/package.pack_delete_success'));
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
    }

    /**
     * To get category iframe template
     * @param string $slug
     */
    public function getCategoryTemplate($slug = '')
    {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.package.category.list_category');
            $this->layout->footer = '';
    }

    /**
     * To get category list iframe
     * @param string $slug
     */
    public function getListCategory()
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = '';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];
        
        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['category_name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['status' => $order_by[0]['dir']];
            }
        }

        if (isset($search['value'])) {
            $searchKey = $search['value'];
        }

        if (preg_match('/^[0-9]+$/', Input::get('start'))) {
            $start = Input::get('start');
        }
        if (preg_match('/^[0-9]+$/', Input::get('length'))) {
            $limit = Input::get('length');
        }

        $filter = Input::get('filter');
        
        if (!in_array($filter, ['ACTIVE', 'IN-ACTIVE', 'EMPTY'])) {
            $filter = 'all';
        }

        $total_num_category = Category::getCategoryCount();
        $num_category_with_filter = Category::getCategoryCount($filter, $searchKey);
        $categories = Category::getAllFilteredCategoryWithPagination($filter, $start, $limit, $orderByArray, $searchKey);

        $dataArr = [];
        $parentCatArr = null;
        $dataArr = [];
        /* Pick all parent categories */
        if (is_array($categories) && !empty($categories)) {
            foreach ($categories as $catkey => $value) {
                $parentCatArr = [
                    "<input type='checkbox' value=" . $value['category_id'] . " name='parentCategory' id=" . $value['category_id'] . ">",
                    $value['category_name'],
                    Timezone::convertFromUTC('@' . $value['created_at'], Auth::user()->timezone, config('app.date_format')),
                    $value['status'],
                ];
                $dataArr[] = $parentCatArr;
             
                if (array_key_exists("children", $value) && !empty($value['children'])) {
                    $subCatArr = null;
                    $subCat = array_pull($value, "children");
                    if (is_array($subCat) && !empty($subCat)) {
                        foreach ($subCat as $key => $val) {
                            $subCatName = Category::getCategoryName($val['category_id']);
                            $subCatName = $subCatName[0];
                            $subCatArr = [
                                "<input type='checkbox' value=" . $val['category_id'] . " style='margin-left:30px;' name ='sub_category' data-parentid=" . $value['category_id'] . ">",
                                $subCatName['category_name'],
                                Timezone::convertFromUTC('@' . $subCatName['created_at'], Auth::user()->timezone, config('app.date_format')),
                                $subCatName['status'],
                            ];
                            $dataArr[] = $subCatArr;
                        }
                    }
                }
            }
        }
        $finaldata = [
            'recordsTotal' => $total_num_category,
            'recordsFiltered' => $num_category_with_filter,
            'data' => $dataArr,
        ];
        return response()->json($finaldata);
    }

    /**
     * Using this method enrolling packages to category
     */
    public function postAssignCategory($slug = '')
    {
        try {
            $package = $this->packageService->getPackageBySlug('package_slug', $slug);
            $ids = Input::get('ids');
            $category_ids = !empty($package->category_ids) ? $package->category_ids: [];

            if ($ids) {
                $ids = array_map("intval", explode(',', $ids));
            } else {
                $ids = [];
            }
            
            $package->category()->sync($ids);
            
            return response()->json(['flag' => 'success', 'message' => trans('admin/package.category_assign_success')]);
        } catch (PackageNotFoundException $e) {
            return redirect('/cp/package/list-template')
                ->with('error', trans('admin/package.slug_missing_error'));
        }
    }

    /**
     * Using this method updating cutomfield
     */
    public function postSaveCustomfield($slug)
    {
        Input::flash();
        $filter = Input::get('filter');
        $feedCF = $this->customService->getFormCustomFields($filter);
        $niceNames = [];
        $rules = [];

        foreach ($feedCF as $feedfield) {
            if ($feedfield['mark_as_mandatory'] == 'yes') {
                $rules[$feedfield['fieldname']] = 'required|max:256';
            } else {
                $rules[$feedfield['fieldname']] = 'max:256';
            }
            $niceNames[$feedfield['fieldname']] = $feedfield['fieldlabel'];
        }

        $validation = Validator::make(Input::all(), $rules, [], $niceNames);

        if ($validation->fails()) {
            return Redirect::back()->withInput()
                ->withErrors($validation)->with('packagecustomfield', 'packagecustomfield');
        } elseif ($validation->passes()) {
            $input = Input::except('filter');
            $result = $this->customService->insertPackageModuleCustomField($input, $slug);
            Input::flush();
            if ($result) {
                return redirect('cp/package/list-template')
                    ->with('success', trans('admin/customfields.success_msg'));
            } else {
                return redirect('/cp/contentfeedmanagement/list-packs');
            }
        }
        
        $msg = trans('admin/program.slug_missing_error');

        return redirect('cp/package/list-template')
            ->with('error', $msg);
    }

    /**
     * Package export with user
     */
    public function getPackageUserExport()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EXPORT_PACKAGE_WITH_USERS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $packages = $this->packageService->getPackages();
        if ($packages->isEmpty()) {
            $fp = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename = PackageExportUsers.csv');
            fputcsv($fp, ["No records found, Please try after some time"]);
            fclose($fp);
            exit();
        } else {
            $packages = $packages->toArray();
        }
        $export_user_list = [];
        $userAttributes = config('app.ChannelExportUserFields');
        $user_time = Auth::user()->timezone;

        array_walk_recursive(
            $packages,
            function (&$item, $key) {
                (is_string($item)) ? $item = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($item))), ENT_QUOTES) : '';
            }
        );
        foreach ($packages as $key => &$val) {
            unset($val['_id']);
            if (isset($packages[$key]['user_ids']) && !empty($packages[$key]['user_ids']) && array_key_exists('user_ids', $packages[$key])) {
                $userList = $packages[$key]['user_ids'];
                $user_details = User::whereIn('uid', $userList)->get($userAttributes)->toArray();
                $usersFullName = null;
                foreach ($user_details as $each_user) {
                    $user['fullname'] = $each_user['firstname'] . ' ' . $each_user['lastname'];
                    $user['username'] = (isset($each_user['username'])) ? $each_user['username'] : 'NA';
                    $user['email'] = (isset($each_user['email'])) ? $each_user['email'] : 'NA';
                    $export_user_list[$key]['users'][] = $user;
                };
                $export_user_list[$key]['package_title'] = $packages[$key]['package_title'];
                $export_user_list[$key]['shortname'] = (isset($packages[$key]['package_shortname'])) ? $packages[$key]['package_shortname'] : " ";
                $export_user_list[$key]['status'] = $packages[$key]['status'];
                $export_user_list[$key]['updated_by'] = (isset($val['updated_by_name'])) ? $val['updated_by_name'] : "";
                $export_user_list[$key]['updated_at'] = (isset($val['updated_at'])) ? Timezone::convertFromUTC($val['updated_at'], $user_time, config('app.date_format')) : ' ';
            } else {
                $export_user_list[$key]['package_title'] = array_get(array_get($packages, $key), 'package_title');
                $export_user_list[$key]['shortname'] = (isset($packages[$key]['package_shortname'])) ? $packages[$key]['package_shortname'] : " ";
                $export_user_list[$key]['status'] = array_get(array_get($packages, $key), 'status');
                $export_user_list[$key]['users'] = " ";
                $export_user_list[$key]['updated_by'] = (isset($val['updated_by_name'])) ? $val['updated_by_name'] : "";
                $export_user_list[$key]['updated_at'] = (isset($val['updated_at'])) ? Timezone::convertFromUTC($val['updated_at'], $user_time, config('app.date_format')) : ' ';
            }
            if (array_key_exists("user_ids", $packages[$key])) {
                array_pull($export_user_list[$key], 'user_ids');
            }
        }

        $fp = fopen('php://output', 'w');
        header('Content-type: application/csv');
        header('Content-Disposition: attachment; filename = PackageExportUsers.csv');
        $headers = ['Package Name', 'Short Name', 'Status', 'Updated Date', 'Updated By', 'Assigned_User_FullName', 'Assigned_UserName', 'Assigned_User_Email'];
        fputcsv($fp, $headers);
        $export_user_new_list = null;
        foreach ($export_user_list as $each_packages) {
            $row['package_name'] = $each_packages['package_title'];
            $row['short_name'] = $each_packages['shortname'];
            $row['status'] = $each_packages['status'];
            $row['updated_at'] = $each_packages['updated_at'];
            $row['updated_by'] = $each_packages['updated_by'];
            if (isset($each_packages['users']) && !empty($each_packages['users'])) {
                if ($each_packages['users'] != " ") {
                    foreach ($each_packages['users'] as $each_user) {
                        $row['Fullname'] = $each_user['fullname'];
                        $row['username'] = $each_user['username'];
                        $row['email'] = $each_user['email'];
                        $export_user_new_list[] = $row;
                    }
                } else {
                    $row = [];
                    $row['package_name'] = $each_packages['package_title'];
                    $row['short_name'] = $each_packages['shortname'];
                    $row['status'] = $each_packages['status'];
                    $row['updated_at'] = $each_packages['updated_at'];
                    $row['updated_by'] = $each_packages['updated_by'];
                    $export_user_new_list[] = $row;
                }
            }
        }
        foreach ($export_user_new_list as $val) {
            fputcsv($fp, $val);
        }
        exit();
    }

    /**
     * Package export with user group
     */
    public function getPackageUsergroupExport()
    {
        if (!has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EXPORT_PACKAGE_WITH_USER_GROUPS) || config("app.ecommerce") === false) {
            return parent::getAdminError();
        }

        $packages = $this->packageService->getPackages();
        
        if ($packages->isEmpty()) {
            $packages = [];
        } else {
            $packages = $packages->toArray();
        }

        $export_user_list = [];
        $user_time = Auth::user()->timezone;

        array_walk_recursive(
            $packages,
            function (&$item, $key) {
                (is_string($item)) ? $item = htmlspecialchars_decode(trim(preg_replace('/[\x00-\x1F\x80-\xFF]/', '', strip_tags($item))), ENT_QUOTES) : '';
            }
        );

        foreach ($packages as $key => &$val) {
            unset($val['_id']);
            if (isset($packages[$key]['user_group_ids']) && !empty($packages[$key]['user_group_ids']) && array_key_exists('user_group_ids', $packages[$key])) {
                $userGroupList = $packages[$key]['user_group_ids'];
                $userGroupNames = UserGroup::getUserGroupNames($userGroupList);
                $export_user_list[$key]['package_title'] = $packages[$key]['package_title'];
                $export_user_list[$key]['package_shortname'] = (isset($packages[$key]['package_shortname'])) ? $packages[$key]['package_shortname'] : " ";
                $export_user_list[$key]['status'] = $packages[$key]['status'];
                $export_user_list[$key]['updated_by'] = (isset($packages[$key]['updated_by_name'])) ? $packages[$key]['updated_by_name'] : "";
                $export_user_list[$key]['updated_at'] = (isset($packages[$key]['updated_at'])) ?
                    Timezone::convertFromUTC($packages[$key]['updated_at'], $user_time, config('app.date_format')) :
                    Timezone::convertFromUTC($packages[$key]['created_at'], $user_time, config('app.date_format'));
                $usergroup = implode(",", $userGroupNames);
                $export_user_list[$key]['usersgroup'] = $usergroup;
            } else {
                $export_user_list[$key]['package_title'] = array_get(array_get($packages, $key), 'package_title');
                $export_user_list[$key]['package_shortname'] = (isset($packages[$key]['package_shortname'])) ? $packages[$key]['package_shortname'] : " ";
                $export_user_list[$key]['status'] = array_get(array_get($packages, $key), 'status');
                $export_user_list[$key]['updated_by'] = (isset($packages[$key]['updated_by_name'])) ? $packages[$key]['updated_by_name'] : "";
                $export_user_list[$key]['updated_at'] = (isset($packages[$key]['updated_at'])) ?
                    Timezone::convertFromUTC(array_get(array_get($packages, $key), 'updated_at'), $user_time, config('app.date_format')) :
                    Timezone::convertFromUTC(array_get(array_get($packages, $key), 'created_at'), $user_time, config('app.date_format'));
            }

            if (array_key_exists("user_group_ids", $packages[$key])) {
                array_pull($export_user_list[$key], 'user_group_ids');
            }
        }

        $fp = fopen('php://output', 'w');
        header('Content-Encoding: UTF-8');
        header("Content-type: application/csv; charset=UTF-8");
        header('Content-Disposition: attachment; filename = PackageExportUserGroup.csv');
        $headers = ['Package Name', 'Short Name', 'Status', 'Updated By', 'Updated Date', 'Assigned_UserGroups'];
        fputcsv($fp, $headers);

        foreach ($export_user_list as $line) {
            fputcsv($fp, $line);
        }
        unset($export_user_list);
        exit();
    }
}
