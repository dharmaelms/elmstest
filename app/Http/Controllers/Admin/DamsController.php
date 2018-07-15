<?php


namespace App\Http\Controllers\Admin;

use Akamai;
use App\Enums\Assessment\AssessmentPermission;
use App\Enums\Assignment\AssignmentPermission;
use App\Enums\Package\PackagePermission;
use App\Enums\Program\ChannelPermission;
use App\Enums\Program\ElementType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\Course\CoursePermission;
use App\Helpers\DAMS\ScormHelper;
use App\Http\Controllers\AdminBaseController;
use App\Http\Validators\DAMS\ScormValidator;
use App\Model\Announcement;
use App\Model\Common;
use App\Model\Dam;
use App\Model\Dams\Repository\IDamsRepository;
use App\Model\ManageLmsProgram;
use App\Model\NotificationLog;
use App\Model\Packet;
use App\Model\Program;
use App\Model\User;
use App\Model\UserGroup;
use App\Services\Question\IQuestionService;
use App\Traits\AkamaiTokenTrait;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\DAMS\DAMSPermission;
use App\Services\Program\IProgramService;
use App\Exceptions\ApplicationException;
use Auth;
use Config;
use ErrorException;
use File;
use getID3;
use Imagick;
use Input;
use League\Csv\Reader as CsvReader;
use League\Csv\Writer as CsvWriter;
use PHPExcel;
use PHPExcel_IOFactory;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use Request;
use Session;
use SplFileObject;
use Symfony\Component\HttpFoundation\File\File as SymfonyFile;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Timezone;
use URL;
use Illuminate\Support\Facades\Validator;
use ZipArchive;
use App\Enums\DAMs\BoxDocumentStatus;
use stdClass;
use Event;
use App\Events\DAMs\DocumentAdded;
use App\Events\DAMs\DocumentUpdated;
use App\Events\DAMs\DocumentDeleted;
use Log;

class DamsController extends AdminBaseController
{
    use AkamaiTokenTrait;
    protected $layout = 'admin.theme.layout.master_layout';

    protected $dams_repository;
    /**
     * @var IProgramService
     */
    private $programService;

    /**
     * DamsController constructor.
     * @param Request $request
     * @param IDamsRepository $dams_repository
     * @param IProgramService $programService
     */
    public function __construct(
        Request $request,
        IDamsRepository $dams_repository,
        IProgramService $programService
    ) {
        parent::__construct();
        $this->dams_repository = $dams_repository;

        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->theme_path = 'admin.theme';
        $this->programService = $programService;
    }

    public function getIndex()
    {
        $this->getListMedia();
    }

    public function getListMedia()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/dams.manage_assets') => 'dams',
            trans('admin/dams.list_media_assets') => '',
        ];
        $viewmode = Input::get('view', 'desktop');
        $select = Input::get('select', 'checkbox');
        $idtype = Input::get('id', '_id');
        $from = Input::get('from', 'none');
        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = '';
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.dams.listmediaiframe')
                                    ->with('select', $select)
                                    ->with('idtype', $idtype)
                                    ->with('from', $from);
            $this->layout->footer = '';
        } else {
            if (!has_admin_permission(ModuleEnum::DAMS, DAMSPermission::LIST_MEDIA)) {
                return parent::getAdminError($this->theme_path);
            }
            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/dams.list_media_assets');
            $this->layout->pageicon = 'fa fa-video-camera';
            $this->layout->pagedescription = trans('admin/dams.list_assets');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'dams');
            $this->layout->content = view('admin.theme.dams.listmedia');
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function getMediaListAjax(IQuestionService $question)
    {
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $select = Input::get('select');
        $idtype = Input::get('id', '_id');
        $searchKey = '';
        $viewmode = Input::get('view', 'desktop');

        $has_list_media_permission = false;
        
        switch ($viewmode) {
            case "desktop":
                $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                    $this->request->user()->uid,
                    ModuleEnum::DAMS,
                    PermissionType::ADMIN,
                    DAMSPermission::LIST_MEDIA,
                    null,
                    null,
                    true
                );

                $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                break;
            case "iframe":
                $from = Input::get('from', 'none');
                switch($from) {
                    case "announcement":
                        $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::ANNOUNCEMENT,
                            PermissionType::ADMIN,
                            AnnouncementPermission::ASSIGN_MEDIA,
                            Contexts::PROGRAM,
                            null,
                            true
                        );
                        $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                        break;
                    case "add-program":
                        $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::CHANNEL,
                            PermissionType::ADMIN,
                            ChannelPermission::ADD_CHANNEL,
                            Contexts::PROGRAM,
                            null,
                            true
                        );

                        $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                        break;
                    case "program":
                    case "add-post":
                    case "post":
                        $program_type = $this->request->get("program_type", null);
                        $program_slug = $this->request->get("program_slug", null);
                        try {
                            $program = $this->programService->getProgramBySlug($program_type, $program_slug);
                            if ($from === "post") {
                                $post_slug = $this->request->get("post_slug", null);
                                $this->programService->getProgramPostBySlug(
                                    $program_type,
                                    $program_slug,
                                    $post_slug
                                );
                            }

                            if ($program_type == "course") {
                                $has_list_media_permission = has_admin_permission(
                                    ModuleEnum::COURSE,
                                    CoursePermission::MANAGE_COURSE_POST
                                );
                                $from = "course";
                            } else {
                                $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                                    $this->request->user()->uid,
                                    ModuleEnum::CHANNEL,
                                    PermissionType::ADMIN,
                                    ChannelPermission::MANAGE_CHANNEL_POST,
                                    Contexts::PROGRAM,
                                    $program->program_id,
                                    true
                                );
                                $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                            }
                        } catch (ApplicationException $e) {
                            Log::error($e->getTraceAsString());
                        }
                        break;
                    case "question":
                        $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::ASSESSMENT,
                            PermissionType::ADMIN,
                            AssessmentPermission::ADD_QUESTION,
                            null,
                            null,
                            true
                        );

                        $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                        break;
                    case 'course':
                        $has_list_media_permission =has_admin_permission(
                            ModuleEnum::COURSE,
                            CoursePermission::MANAGE_COURSE_POST
                        );
                        break;
                    case "add_package":
                        $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::PACKAGE,
                            PermissionType::ADMIN,
                            PackagePermission::ADD_PACKAGE,
                            null,
                            null,
                            true
                        );

                        $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                        break;
                    case "edit_package":
                        $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::PACKAGE,
                            PermissionType::ADMIN,
                            PackagePermission::ADD_PACKAGE,
                            null,
                            null,
                            true
                        );

                        $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                        break;
                    case "add-assignment":
                        $list_media_permission_data_with_flag = $this->roleService->hasPermission(
                            $this->request->user()->uid,
                            ModuleEnum::ASSIGNMENT,
                            PermissionType::ADMIN,
                            AssignmentPermission::ADD_ASSIGNMENT,
                            null,
                            null,
                            true
                        );

                        $has_list_media_permission = get_permission_flag($list_media_permission_data_with_flag);
                }
                break;
        }

        if (!$has_list_media_permission) {
            return response()->json(
                [
                    'recordsTotal' => 0,
                    'recordsFiltered' => 0,
                    'data' => [],
                ]
            );
        }

        $filter_params = [];
        
        if ($viewmode == "iframe" && $from == "course") {
            $filter_params = [];
        } else {
            $list_media_permission_data = get_permission_data($list_media_permission_data_with_flag);
            $filter_params = has_system_level_access($list_media_permission_data)? [] :
                ["in_ids" => get_user_accessible_elements($list_media_permission_data, ElementType::MEDIA)];
        }

        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '2') {
                $orderByArray = ['type' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['created_at' => $order_by[0]['dir']];
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
        if (is_array($filter)) {
            $filter = array_filter($filter, function ($value) {
                if (in_array($value, ["image", "video", "audio", "document"], true)) {
                    return $value;
                }
            });
            if (empty($filter)) {
                $filter = "all";
            }
        } else {
            $filter = strtolower($filter);
            if (!in_array($filter, ['image', 'video', 'document', 'audio', 'media', 'scorm'])) {
                $filter = 'all';
            }
        }
        $totalRecords = Dam::getDamsCount("all", null, $filter_params);
        $filteredRecords = Dam::getDamsCount($filter, $searchKey, $filter_params);
        $filtereddata = Dam::getDAMSAssetsWithTypeWithPagination(
            $filter,
            $start,
            $limit,
            $orderByArray,
            $searchKey,
            $filter_params
        );

        $dataArr = [];
        foreach ($filtereddata as $key => $value) {
            $type = '';
            $namedata = '';
            switch ($value['type']) {
                case 'image':
                    $type = '<a style="cursor:default" class="btn btn-circle show-tooltip" title="' . trans('admin/dams.image') . '" onclick="return false" ><i class="fa fa-picture-o"></i></a>';
                    break;
                case 'document':
                    $type = '<a style="cursor:default" class="btn btn-circle show-tooltip" title="' . trans('admin/dams.document') . '"  onclick="return false" ><i class="fa fa-file-word-o"></i></a>';
                    break;
                case 'video':
                    $type = '<a style="cursor:default" class="btn btn-circle show-tooltip" title="' . trans('admin/dams.video') . '"  onclick="return false" ><i class="fa fa-play-circle-o"></i></a>';
                    break;
                case 'audio':
                    $type = '<a style="cursor:default" class="btn btn-circle show-tooltip" title="' . trans('admin/dams.audio') . '"  onclick="return false" ><i class="fa fa-file-audio-o"></i></a>';
                    break;
                case 'scorm':
                    $type = '<a style="cursor:default" class="btn btn-circle show-tooltip" title="' . trans('admin/dams.scorm') . '"  onclick="return false" ><i class="fa fa-film"></i></a>';
                    break;
            }
            if (isset($value['video_status'])) {
                if (in_array($value['video_status'], ['INTEMP'])) {
                    $namedata = ' <span class="label">'.trans("admin/dams.pending").'</span>';
                } elseif (in_array($value['video_status'], ['UPLOADING', 'UPLOADED'])) {
                    $namedata = ' <span class="label label-info">'.trans("admin/dams.processing").'</span>';
                } elseif (in_array($value['video_status'], ['READY'])) {
                    $namedata = ' <span class="label label-success">'.trans("admin/dams.ready").'</span>';
                }
            }
            if (isset($value['srt_status']) && ($value['srt_status'] == 'READY' || $value['srt_status'] == 'ADDED')) {
                $namedata .= ' <span class="label label-success">SRT</span>';
            }
            $type .= '(' . ucfirst($value['asset_type']) . ')';
            $preview = 'No Preview';
            if ($value['type'] == 'image' && $value['asset_type'] == 'file') {
                $preview = '<img src="' . URL::to('/cp/dams/show-media/' . $value['_id']) . '?thumb=180x180" width="100px" />';
            } elseif ($value['type'] == 'video' && $value['asset_type'] == 'file' && isset($value['kaltura_details']['thumbnailUrl'])) {
                $preview = '<img src="' . $value['kaltura_details']['thumbnailUrl'] . '/width/100' . '" width="100px" />';
            } elseif ($value['type'] == 'video' && $value['asset_type'] == 'file' && file_exists(config('app.dams_video_thumb_path') . $value['unique_name'] . '.png')) {
                $preview = '<img src="' . URL::to('/cp/dams/show-media/' . $value['_id']) . '?thumb=180x180" width="100px" />';
            }
            $actions = '';
            if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::VIEW_MEDIA)) {
                $actions .= '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('/cp/dams/media-details/' . $value['_id']) . '" ><i class="fa fa-eye"></i></a>';
            }

            if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::EDIT_MEDIA)) {
                $extra_html = 'class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '"';
                if ($value['type'] == 'video' && $value['asset_type'] == 'file' && isset($value['video_status']) && $value['video_status'] != 'READY') {
                    $extra_html = 'class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit_restricted') . '" onclick="return false"';
                }
                $actions .= '<a ' . $extra_html . ' href="' . URL::to('/cp/dams/edit-media/' . $value['_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . (is_array($filter) ? http_build_query($filter) : $filter) . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            }

            if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::DELETE_MEDIA)) {
                $asset = Dam::getDAMSAssetsUsingID($value['_id']);
                $deleteflag = false;
                $asset = $asset[0];

                //datatable pagination
                $error = false;
                $extra_html = 'class="btn btn-circle show-tooltip deletemedia" title="' . trans('admin/manageweb.action_delete') . '"';
                $delete = '<a ' . $extra_html . ' href="' . URL::to('/cp/dams/delete-media/' . $value['_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . (is_array($filter) ? http_build_query($filter) : $filter) . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                //Have to check if the id is already related to modules. If yes then dont delete and return back with a message
                if (isset($asset['relations']) && is_array($asset['relations']) && !empty($asset['relations'])) {
                    foreach ($asset['relations'] as $k => $v) {
                        if (is_array($v) && count($v)) {
                            $error = true;
                        }
                    }
                } elseif (!$question->getQuestionsByMedia($value["_id"])->isEmpty()) {
                    $error = true;
                }

                if ($error == 1) {
                    $extra_html = 'class="btn btn-circle show-tooltip mediarelations" title="' . trans('admin/manageweb.action_delete') . '"';
                    $delete = '<a ' . $extra_html . ' href="' . URL::to('/cp/dams/media-relations/' . $value['_id']) . '?start=' . $start . '&limit=' . $limit . '&filter=' . (is_array($filter) ? http_build_query($filter) : $filter) . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                }
                $actions .= $delete;
            }
            $temparr = [
                '<input type="checkbox" value="' . $value['_id'] . '">',
                $value['name'] . $namedata,
                $type,
                Timezone::convertFromUTC($value['created_at'], Auth::user()->timezone, config('app.date_time_format')),
                $value['created_by_name'],
                $preview,
            ];

            $temparr[] = $actions;

            if ($idtype == 'id') {
                array_splice($temparr, 0, 1, ['<input type="checkbox" value="' . $value['id'] . '">']);
            }
            if ($select == 'radio' && $idtype == 'id') {
                array_splice($temparr, 0, 1, ['<input type="radio" value="' . $value['id'] . '" name="radio">']);
            } elseif ($select == 'radio' && $idtype == '_id') {
                array_splice($temparr, 0, 1, ['<input type="radio" value="' . $value['_id'] . '" name="radio">']);
            }

            if ($viewmode === 'iframe') {
                array_splice($temparr, 6, 0, [implode(',', $value['tags'])]);
                array_pop($temparr);
            }

            $dataArr[] = $temparr;
        }

        if ($viewmode == 'iframe') {
            $totalRecords = $filteredRecords;
        }

        $finaldata = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finaldata);
    }

    public function getAddMedia()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/dams.manage_assets') => 'dams',
            trans('admin/dams.add_media_assets') => '',
        ];
        $viewmode = Input::get('view', 'desktop');

        if ($viewmode == 'iframe') {
            $this->layout->breadcrumbs = '';
            $this->layout->pagetitle = trans('admin/dams.add_media_assets');
            $this->layout->pageicon = '';
            $this->layout->pagedescription = '';
            $this->layout->header = '';
            $this->layout->sidebar = '';
            $this->layout->content = view('admin.theme.dams.addmediaiframe');
            $this->layout->footer = '';
        } else {
            if (!has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA)) {
                return parent::getAdminError($this->theme_path);
            }

            $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
            $this->layout->pagetitle = trans('admin/dams.add_media_assets');
            $this->layout->pageicon = 'fa fa-video-camera';
            $this->layout->pagedescription = trans('admin/dams.add_new_assets');
            $this->layout->header = view('admin.theme.common.header');
            $this->layout->sidebar = view('admin.theme.common.sidebar')
                ->with('mainmenu', 'dams');
            $this->layout->content = view('admin.theme.dams.addmedia');
            $this->layout->footer = view('admin.theme.common.footer');
        }
    }

    public function postUploadHandler()
    {
        if (!has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA)) {
            return parent::getAdminError($this->theme_path);
        }

        $viewmode = Input::get('view', 'desktop');
        $media_type = Input::get('media_type');
        $filter  = http_build_query(["filter" => Input::get('filter', ['all'])]);
        $link_type = null;
        switch ($media_type) {
            case 'image':
                $input_type = Input::get('input_type');
                switch ($input_type) {
                    case 'uploadform':
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'Required|image|mimes:'.implode(',', config('app.dams_image_extensions')),
                        ];
                        $validation = Validator::make(Input::all(), $rules);
                        if ($validation->fails()) {
                            if ($viewmode == 'iframe') {
                                return redirect(
                                    'cp/dams/add-media?view=iframe' .
                                    ((Input::get('select') == 'radio') ? '&select=radio' : '') .
                                    '&' . $filter .
                                    ((Input::get('id') == 'id') ? '&id=id' : '') .
                                    '&from=' . Input::get('from') .
                                    '&program_type=' . Input::get('program_type') .
                                    '&program_slug=' . Input::get('program_slug') .
                                    '&post_slug=' . Input::get('post_slug')
                                )->withInput()
                                ->withErrors($validation);
                            } else {
                                return redirect('cp/dams/add-media')->withInput()
                                    ->withErrors($validation);
                            }
                        } else {
                            $keyword = Input::get('keyword');
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $image_sizes = Config::get('app.thumb_resolutions');
                            $private_dams_images_path = Config::get('app.private_dams_images_path');
                            $public_dams_images_path = Config::get('app.public_dams_images_path');
                            // $dams_documents_path = Config::get('app.dams_documents_path');
                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $file = Input::file('file');
                            $insertarr = [
                                'id' => Dam::uniqueDAMSId(),
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'type' => 'image',
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'unique_name_with_extension' => $random_filename . '.' . $file->getClientOriginalExtension(),
                                'visibility' => $visibility,
                                'file_client_name' => $file->getClientOriginalName(),
                                'id3_info' => '',
                                'file_size' => $file->getSize(),
                                'file_extension' => $file->getClientOriginalExtension(),
                                'mimetype' => $file->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            
                            if ($visibility == 'public') {
                                $insertarr['public_file_location'] = $public_dams_images_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                $file->move($public_dams_images_path, $insertarr['public_file_location']);
                                // $insertarr['id3_info'] = $getID3->analyze($insertarr['public_file_location']);
                            } else {
                                $insertarr['private_file_location'] = $private_dams_images_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                $file->move($private_dams_images_path, $insertarr['private_file_location']);
                                // $insertarr['id3_info'] = $getID3->analyze($insertarr['private_file_location']);
                            }
                            foreach ($image_sizes as $value) {
                                $res = explode('x', $value);
                                if (is_array($res)) {
                                    $loc = null;
                                    if ($visibility == 'public') {
                                        $image_obj = new Imagick($insertarr['public_file_location']);
                                        $loc = $public_dams_images_path . $random_filename . '_' . $value . '.' . $file->getClientOriginalExtension();
                                    } else {
                                        $image_obj = new Imagick($insertarr['private_file_location']);
                                        $loc = $private_dams_images_path . $random_filename . '_' . $value . '.' . $file->getClientOriginalExtension();
                                    }
                                    if (isset($res[0]) && isset($res[1])) {
                                        // $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                                        // skips resizing (and copy the original file) if the given res is less than the original image.
                                        if ($res[0] < $image_obj->getImageWidth() && $res[1] < $image_obj->getImageHeight()) {
                                            $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                                        }
                                        $image_obj->writeImage($loc);
                                        $insertarr['thumb_img'][$value] = $loc;
                                    }
                                }
                            }
                            Dam::insert($insertarr);
                            $insertedAsset = Dam::getDAMSAsset($random_filename);
                            $redirectKey = '';
                            if (isset($insertedAsset[0]->_id)) {
                                $redirectKey = $insertedAsset[0]->_id;
                            }
                            if ($viewmode == 'iframe') {
                                $key = $insertedAsset[0]->_id;
                                if (Input::get('id') == 'id') {
                                    $key = $insertedAsset[0]->id;
                                }
                                return redirect(
                                    'cp/dams/list-media?view=iframe&select=radio&' .
                                    $filter .
                                    ((Input::get('id') == 'id') ? '&id=id' : '') .
                                    '&mediaid=' . $key .
                                    '&from=' . Input::get('from') .
                                    '&program_type=' . Input::get('program_type') .
                                    '&program_slug=' . Input::get('program_slug') .
                                    '&post_slug=' . Input::get('post_slug')
                                )->with('success', trans('admin/dams.asset_added_success'));
                            } else {
                                return redirect('cp/dams/add-media-success/' . $redirectKey)
                                    ->with('success', trans('admin/dams.asset_added_success'));
                            }
                        }
                        break;
                    case 'links':
                        $link_type = 'image';
                        break;
                }
                break;
            case 'video':
                $input_type = Input::get('input_type');
                switch ($input_type) {
                    case 'uploadform':
                        // Upload the video to temp storage.
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'Required|checkextension|mimes:'.implode(',', config('app.dams_video_extensions')).'|max:' . config('app.dams_max_upload_size')*1024,
                            'srtfile' => 'checksrtextension',
                        ];
                        Validator::extend('checkextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            // echo $mimetype = $value->getMimeType();exit();
                            if (in_array($extension, config('app.dams_video_extensions'))) {
                                return true;
                            }

                            return false;
                        });
                        Validator::extend('checksrtextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            if (in_array(strtolower($extension), ['srt'])) {
                                return true;
                            }

                            return false;
                        });
                        $messages = [
                            'checkextension' => trans('admin/dams.check_video_extension'),
                            'checksrtextension' => trans('admin/dams.check_srt_extension'),
                        ];
                        $validation = Validator::make(Input::all(), $rules, $messages);
                        if ($validation->fails()) {
                            if ($viewmode == 'iframe') {
                                return redirect('cp/dams/add-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter . ((Input::get('id') == 'id') ? '&id=id' : ''))->withInput()
                                        ->withErrors($validation);
                            } else {
                                return redirect('cp/dams/add-media')->withInput()
                                    ->withErrors($validation);
                            }
                        } else {
                            $keyword = Input::get('keyword');
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $dams_temp_video_path = Config::get('app.dams_temp_video_path');
                            $dams_srt_path = Config::get('app.dams_srt_path');

                            $transcoding = Input::get('transcoding', 'no');
                            if (config('app.dams_media_library_transcoding') && $transcoding == 'yes') {
                                $transcoding = 'yes';
                            } else {
                                $transcoding = 'no';
                            }

                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $file = Input::file('file');
                            $srtfile = Input::file('srtfile');
                            $insertarr = [
                                'id' => Dam::uniqueDAMSId(),
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'type' => 'video',
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'unique_name_with_extension' => $random_filename . '.' . $file->getClientOriginalExtension(),
                                'visibility' => $visibility,
                                'file_client_name' => $file->getClientOriginalName(),
                                'id3_info' => '',
                                'file_size' => $file->getSize(),
                                'file_extension' => $file->getClientOriginalExtension(),
                                'mimetype' => $file->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'transcoding' => $transcoding,
                                'status' => 'ACTIVE',
                                'video_status' => 'INTEMP',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            if ($srtfile) {
                                $insertarr['srt_unique_name_with_extension'] = $random_filename . '.' . $srtfile->getClientOriginalExtension();
                                $insertarr['srt_location'] = $dams_srt_path . $random_filename . '.' . $srtfile->getClientOriginalExtension();
                                $insertarr['srt_status'] = 'ADDED';
                                $insertarr['srt_client_name'] = $srtfile->getClientOriginalName();
                                $srtfile->move($dams_srt_path, $random_filename . '.' . $srtfile->getClientOriginalExtension());
                            }
                            $file->move($dams_temp_video_path, $random_filename . '.' . $file->getClientOriginalExtension());
                            
                            $insertarr['temp_location'] = $dams_temp_video_path . $random_filename . '.' . $file->getClientOriginalExtension();
                            // $insertarr['id3_info'] = $getID3->analyze($insertarr['temp_location']);
                            Dam::insert($insertarr);
                            $insertedAsset = Dam::getDAMSAsset($random_filename);
                            $redirectKey = '';
                            if (isset($insertedAsset[0]->_id)) {
                                $redirectKey = $insertedAsset[0]->_id;
                            }
                            if ($viewmode == 'iframe') {
                                $key = $insertedAsset[0]->_id;
                                if (Input::get('id') == 'id') {
                                    $key = $insertedAsset[0]->id;
                                }
                                return redirect('cp/dams/list-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter . ((Input::get('id') == 'id') ? '&id=id' : '') . '&mediaid=' . $key)
                                    ->with('success', trans('admin/dams.asset_added_success'));
                            } else {
                                return redirect('cp/dams/add-media-success/' . $redirectKey)
                                    ->with('success', trans('admin/dams.asset_added_success'));
                            }
                        }
                        break;
                    case 'links':
                        $link_type = 'video';
                        break;
                }
                break;
            case 'document':
                $input_type = Input::get('input_type');
                switch ($input_type) {
                    case 'uploadform':
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'Required|uploadable|checkextension',
                        ];
                        Validator::extend('checkextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            if (in_array($extension, config('app.dams_document_extensions'))) {
                                return true;
                            }

                            return false;
                        });
                        Validator::extend('uploadable', function ($attribute, $value, $parameters) {
                            return $value instanceof UploadedFile
                            && in_array($value->getMimeType(), config('app.dams_document_mime_types'));
                        });
                        $messages = [
                            'checkextension' => trans('admin/dams.check_doc_extension'),
                            'uploadable' => trans('admin/dams.mimetype_error'),
                        ];
                        $validation = Validator::make(Input::all(), $rules, $messages);
                        if ($validation->fails()) {
                            if ($viewmode == 'iframe') {
                                return redirect('cp/dams/add-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') .'&'.$filter. ((Input::get('id') == 'id') ? '&id=id' : ''))->withInput()
                                    ->withErrors($validation);
                            } else {
                                return redirect('cp/dams/add-media')->withInput()
                                    ->withErrors($validation);
                            }
                        } else {
                            $keyword = Input::get('keyword');
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $private_dams_documents_path = Config::get('app.private_dams_documents_path');
                            $public_dams_documents_path = Config::get('app.public_dams_documents_path');
                            // $dams_documents_path = Config::get('app.dams_documents_path');
                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }

                            $box_details = new stdClass();
                            $box_details->document_id = null;
                            $box_details->status = BoxDocumentStatus::PENDING;
                            $box_details->uploaded_at = null;
                            
                            $file = Input::file('file');
                            $insertarr = [
                                'id' => Dam::uniqueDAMSId(),
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'type' => 'document',
                                'box_details' => $box_details,
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'unique_name_with_extension' => $random_filename . '.' . $file->getClientOriginalExtension(),
                                'visibility' => $visibility,
                                'file_client_name' => $file->getClientOriginalName(),
                                'id3_info' => '',
                                'file_size' => $file->getSize(),
                                'file_extension' => $file->getClientOriginalExtension(),
                                'mimetype' => $file->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            
                            if ($visibility == 'public') {
                                $insertarr['public_file_location'] = $public_dams_documents_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                $file->move($public_dams_documents_path, $insertarr['public_file_location']);
                                // $insertarr['id3_info'] = $getID3->analyze($insertarr['public_file_location']);
                            } else {
                                $insertarr['private_file_location'] = $private_dams_documents_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                $file->move($private_dams_documents_path, $insertarr['private_file_location']);
                                // $insertarr['id3_info'] = $getID3->analyze($insertarr['private_file_location']);
                            }
                            Dam::insert($insertarr);
                            $insertedAsset = Dam::getDAMSAsset($random_filename);
                            $redirectKey = '';
                            if (isset($insertedAsset[0]->_id)) {
                                $redirectKey = $insertedAsset[0]->_id;
                            }
                            if ($viewmode == 'iframe') {
                                $key = $insertedAsset[0]->_id;
                                if (Input::get('id') == 'id') {
                                    $key = $insertedAsset[0]->id;
                                }
                                return redirect('cp/dams/list-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter . ((Input::get('id') == 'id') ? '&id=id' : '') . '&mediaid=' . $key. '&from=' . Input::get('from'))
                                        ->with('success', trans('admin/dams.asset_added_success'));
                            } else {
                                $full_path = config::get('app.public_dams_documents_path');
                                $file_path = $full_path . head(head($insertedAsset))->unique_name_with_extension;

                                $data = new stdClass();
                                $data->id = $redirectKey;
                                $data->file_path = $file_path;
                                $data->new_file = true;
                               
                                Event::fire(new DocumentAdded($data));
                                return redirect('cp/dams/add-media-success/' . $redirectKey)
                                    ->with('success', trans('admin/dams.asset_added_success'));
                            }
                        }
                        break;
                    case 'links':
                        $link_type = 'document';
                        break;
                }
                break;
            case 'audio':
                $input_type = Input::get('input_type');
                switch ($input_type) {
                    case 'uploadform':
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'Required|checkmime|checkextension',
                        ];
                        Validator::extend('checkextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            if (in_array($extension, config('app.dams_audio_extensions'))) {
                                return true;
                            }

                            return false;
                        });
                        Validator::extend('checkmime', function ($attribute, $value, $parameters) {
                            $extension = $value->getMimeType();
                            if (in_array($extension, config('app.dams_audio_mime_types'))) {
                                return true;
                            }

                            return false;
                        });
                        $messages = [
                            'checkextension' => trans('admin/dams.check_audio_extension'),
                            'checkmime' => trans('admin/dams.check_audio_extension'),
                        ];
                        $validation = Validator::make(Input::all(), $rules, $messages);
                        if ($validation->fails()) {
                            if ($viewmode == 'iframe') {
                                return redirect('cp/dams/add-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') .'&'.$filter. ((Input::get('id') == 'id') ? '&id=id' : ''))->withInput()
                                    ->withErrors($validation);
                            } else {
                                return redirect('cp/dams/add-media')->withInput()
                                    ->withErrors($validation);
                            }
                        } else {
                            $keyword = Input::get('keyword');
                            $random_filename = strtolower(str_random(32));
                            while (true) {
                                $result = Dam::getDAMSAsset($random_filename);
                                if ($result->isEmpty()) {
                                    break;
                                } else {
                                    $random_filename = strtolower(str_random(32));
                                }
                            }
                            $private_dams_audio_path = Config::get('app.private_dams_audio_path');
                            $public_dams_audio_path = Config::get('app.public_dams_audio_path');
                            // $dams_documents_path = Config::get('app.dams_documents_path');
                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $file = Input::file('file');
                            $insertarr = [
                                'id' => Dam::uniqueDAMSId(),
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'type' => 'audio',
                                'asset_type' => 'file',
                                'unique_name' => $random_filename,
                                'unique_name_with_extension' => $random_filename . '.' . $file->getClientOriginalExtension(),
                                'visibility' => $visibility,
                                'file_client_name' => $file->getClientOriginalName(),
                                'id3_info' => '',
                                'file_size' => $file->getSize(),
                                'file_extension' => $file->getClientOriginalExtension(),
                                'mimetype' => $file->getMimeType(),
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'created_at' => time(),
                                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                                'created_by_username' => Auth::user()->username,
                            ];
                            
                            if ($visibility == 'public') {
                                $insertarr['public_file_location'] = $public_dams_audio_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                $file->move($public_dams_audio_path, $insertarr['public_file_location']);
                                // $insertarr['id3_info'] = $getID3->analyze($insertarr['public_file_location']);
                            } else {
                                $insertarr['private_file_location'] = $private_dams_audio_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                $file->move($private_dams_audio_path, $insertarr['private_file_location']);
                                // $insertarr['id3_info'] = $getID3->analyze($insertarr['private_file_location']);
                            }
                            Dam::insert($insertarr);
                            $insertedAsset = Dam::getDAMSAsset($random_filename);
                            $redirectKey = '';
                            if (isset($insertedAsset[0]->_id)) {
                                $redirectKey = $insertedAsset[0]->_id;
                            }
                            if ($viewmode == 'iframe') {
                                $key = $insertedAsset[0]->_id;
                                if (Input::get('id') == 'id') {
                                    $key = $insertedAsset[0]->id;
                                }
                                return redirect('cp/dams/list-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter .  ((Input::get('id') == 'id') ? '&id=id' : '') . '&mediaid=' . $key)
                                    ->with('success', trans('admin/dams.asset_added_success'));
                            } else {
                                return redirect('cp/dams/add-media-success/' . $redirectKey)
                                    ->with('success', trans('admin/dams.asset_added_success'));
                            }
                        }
                        break;
                    case 'links':
                        $link_type = 'audio';
                        break;
                }
                break;
            case 'scorm':
                $scorm_version = null;
                $launch_file = null;

                ScormValidator::extendValidatorToValidateScorm();

                $rules = [
                    'title' => 'Required',
                    'visibility' => 'Required',
                    'file' => 'Required|mimes:zip|max:1048576|unsupported_files_not_exist|imsmanifest_exists',
                ];

                $validation = Validator::make(Input::all(), $rules);
                if ($validation->fails()) {
                    if ($viewmode == 'iframe') {
                        return redirect('cp/dams/add-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter . ((Input::get('id') == 'id') ? '&id=id' : ''))->withInput()
                            ->withErrors($validation);
                    } else {
                        return redirect('cp/dams/add-media')->withInput()
                            ->withErrors($validation);
                    }
                } else {
                    $keyword = Input::get('keyword');
                    $random_filename = strtolower(str_random(32));
                    while (true) {
                        $result = Dam::getDAMSAsset($random_filename);
                        if ($result->isEmpty()) {
                            break;
                        } else {
                            $random_filename = strtolower(str_random(32));
                        }
                    }

                    $public_dams_scorm_path = Config::get('app.public_dams_scorm_path');
                    // $dams_documents_path = Config::get('app.dams_documents_path');
                    $file = Input::file('file');
                    $file_client_name = basename($file->getClientOriginalName(), ".zip");
                    $insertarr = [
                        'id' => Dam::uniqueDAMSId(),
                        'name' => Input::get('title'),
                        'name_lower' => strtolower(Input::get('title')),
                        'description' => Input::get('description'),
                        'type' => 'scorm',
                        'asset_type' => 'file',
                        'unique_name' => $random_filename,
                        //'unique_name_with_extension' => $random_filename.'.'.$file->getClientOriginalExtension(),
                        'visibility' => "public",
                        'file_client_name' => $file_client_name,
                        'id3_info' => '',
                        'file_size' => $file->getSize(),
                        'file_extension' => $file->getClientOriginalExtension(),
                        'mimetype' => $file->getMimeType(),
                        'tags' => explode(',', $keyword),
                        'status' => 'ACTIVE',
                        'created_at' => time(),
                        'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                        'created_by_username' => Auth::user()->username,
                    ];
                    //

                    $insertarr['public_file_location'] = $public_dams_scorm_path . $random_filename;

                    $zip = new ZipArchive;

                    if ($zip->open($file) === true) {
                        $zip->extractTo($public_dams_scorm_path . $random_filename);
                        $zip->close();
                    }

                    $scorm_config_data = ScormHelper::getScormConfigData($insertarr['public_file_location']);
                    if (array_has($scorm_config_data, "scorm_version") &&
                        array_has($scorm_config_data, "scorm_launch_file")) {
                        $insertarr["version"] = $scorm_config_data["scorm_version"];
                        $insertarr["launch_file"] = $scorm_config_data["scorm_launch_file"];

                        if (array_has($scorm_config_data, "scorm_mastery_score")) {
                            $insertarr["mastery_score"] = $scorm_config_data["scorm_mastery_score"];
                        }
                    }

                    Dam::insert($insertarr);
                    $insertedAsset = Dam::getDAMSAsset($random_filename);
                    $redirectKey = '';
                    if (isset($insertedAsset[0]->_id)) {
                        $redirectKey = $insertedAsset[0]->_id;
                    }
                    if ($viewmode == 'iframe') {
                        $key = $insertedAsset[0]->_id;
                        if (Input::get('id') == 'id') {
                            $key = $insertedAsset[0]->id;
                        }
                        return redirect('cp/dams/list-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter .  ((Input::get('id') == 'id') ? '&id=id' : '') . '&mediaid=' . $key)
                            ->with('success', trans('admin/dams.asset_added_success'));
                    } else {
                        return redirect('cp/dams/add-media-success/' . $redirectKey)
                            ->with('success', trans('admin/dams.asset_added_success'));
                    }
                }
                break;
        }
        if ($link_type && in_array($link_type, ['image', 'document', 'video', 'audio'])) {
            $rules = [
                'link_title' => 'Required',
                // 'link_description' => 'Required',
                // 'link_keyword' => 'Required',
                'link_visibility' => 'Required',
                'link_url' => 'Required|url',
            ];
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                if ($viewmode == 'iframe') {
                    return redirect('cp/dams/add-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter . ((Input::get('id') == 'id') ? '&id=id' : ''))->withInput()
                            ->withErrors($validation);
                } else {
                    return redirect('cp/dams/add-media')->withInput()
                        ->withErrors($validation);
                }
            } else {
                $visibility = Input::get('link_visibility');
                $keyword = Input::get('link_keyword');
                if ($visibility != 'public') {
                    $visibility = 'private';
                }
                $created_at = time();
                $insertarr = [
                    'id' => Dam::uniqueDAMSId(),
                    'name' => Input::get('link_title'),
                    'name_lower' => strtolower(Input::get('link_title')),
                    'description' => Input::get('link_description'),
                    'type' => $link_type,
                    'asset_type' => 'link',
                    'url' => Input::get('link_url'),
                    'visibility' => $visibility,
                    'tags' => explode(',', $keyword),
                    'status' => 'ACTIVE',
                    'created_at' => $created_at,
                    'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                    'created_by_username' => Auth::user()->username,
                ];
                Dam::insert($insertarr);
                $insertedAsset = Dam::getDAMSAssetByCreatedAt($created_at);
                $redirectKey = '';
                if (isset($insertedAsset[0]->_id)) {
                    $redirectKey = $insertedAsset[0]->_id;
                }
                if ($viewmode == 'iframe') {
                    $key = $insertedAsset[0]->_id;
                    if (Input::get('id') == 'id') {
                        $key = $insertedAsset[0]->id;
                    }
                    return redirect('cp/dams/list-media?view=iframe' . ((Input::get('select') == 'radio') ? '&select=radio' : '') . '&' . $filter . ((Input::get('id') == 'id') ? '&id=id' : '') . '&mediaid=' . $key)
                            ->with('success', trans('admin/dams.asset_added_success'));
                } else {
                    return redirect('cp/dams/add-media-success/' . $redirectKey)
                        ->with('success', trans('admin/dams.asset_added_success'));
                }
            }
        }

        return redirect('cp/dams/')
            ->with('error', trans('admin/dams.memory_limit', ['size' => config('app.dams_max_upload_size')]));
    }

    public function getAddMediaSuccess($key = null)
    {
        if (!has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA)) {
            return parent::getAdminError($this->theme_path);
        }

        $asset = Dam::getDAMSAssetsUsingID($key);
        if (empty($asset) || !$key) {
            $msg = trans('admin/dams.missing_asset');

            return redirect('/cp/dams/')
                ->with('error', $msg);
        }
        $asset = $asset[0]; // Can do this since we know that the array will not be empty and will not be associative array as well
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/dams.manage_assets') => 'dams',
            trans('admin/dams.add_media_assets') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/dams.add_media_assets');
        $this->layout->pageicon = 'fa fa-video-camera';
        $this->layout->pagedescription = trans('admin/dams.add_new_assets');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'dams');
        $this->layout->content = view('admin.theme.dams.addmediasuccess')
            ->with('asset', $asset)
            ->with('key', $key);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postAssignMedia($action = null, $key = null)
    {
        $asset = Dam::getDAMSAssetsUsingID($key);
        $ids = Input::get('ids');
        $empty = Input::get('empty');
        if ($ids) {
            $ids = explode(',', $ids);
        } else {
            $ids = [];
        }
        if (!$empty || !in_array($action, ['user', 'usergroup'])) {
            if (empty($asset) || !$key || !in_array($action, ['user', 'usergroup']) || !is_array($ids) || empty($ids)) {
                $msg = trans('admin/dams.missing_asset');

                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
        }
        if ($action == 'user') {
            $arrname = 'active_user_media_rel';
        }
        if ($action == 'usergroup') {
            $arrname = 'active_usergroup_media_rel';
        }
        $insertarr = [
            'media_id' => $key,
        ];
        $ids = array_map('intval', $ids);
        $ids = array_filter($ids);
        $asset = $asset[0];
        $deleted = [];
        if (isset($asset['relations'])) {
            if ($action == 'user' && isset($asset['relations']['active_user_media_rel'])) {
                // Code to remove relations from user collection
                $deleted = array_diff($asset['relations']['active_user_media_rel'], $ids);
                $ids = array_diff($ids, $asset['relations']['active_user_media_rel']);
                foreach ($deleted as $value1) {
                    User::removeUserRelation((int)$value1, ['user_media_rel'], $asset['id']);
                }
            }
            if ($action == 'usergroup' && isset($asset['relations']['active_usergroup_media_rel'])) {
                // Code to remove relations from usergroup collection
                $deleted = array_diff($asset['relations']['active_usergroup_media_rel'], $ids);
                $ids = array_diff($ids, $asset['relations']['active_usergroup_media_rel']);
                foreach ($deleted as $value2) {
                    UserGroup::removeUserGroupRelation((int)$value2, ['usergroup_media_rel'], $asset['id']);
                }
            }
        }
        $notify_ids = $ids = array_values($ids); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $notify_ids_d = $deleted = array_values($deleted); //  This is to reset the index. [Problem: when using array_diff, the keys gets altered which converts normal array to associative array.]
        $notify_flag = true;
        foreach ($ids as $value) {
            if ($action == 'user') {
                if (Config::get('app.notifications.dams.assign_user') && $notify_flag) {
                    $notify_flag = false;
                    $notif_msg = trans('admin/notifications.assigning_user', ['medianame' => $asset['name'], 'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname]);
                    NotificationLog::getInsertNotification($notify_ids, 'dams', $notif_msg);
                }
                User::addUserRelation($value, ['user_media_rel'], $asset['id']);
            }
            if ($action == 'usergroup') {
                UserGroup::addUserGroupRelation($value, ['usergroup_media_rel'], $asset['id']);
                if (Config::get('app.notifications.dams.assign_usergroup')) {
                    $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                    $notify_ids_ary = [];
                    foreach ($usergroup_data as $usergroup) {
                        if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                            $notify_ids_ary = array_merge($notify_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                            /*foreach ($usergroup['relations']['active_user_usergroup_rel'] as $user) {
                                $notif_msg = trans('admin/notifications.assigning_usergroup', ['medianame' => $asset['name'], 'groupname' => $usergroup['usergroup_name'], 'adminname' => Auth::user()->firstname.' '.Auth::user()->lastname]);
                                Notification::getInsertNotification($user, 'dams', $notif_msg);
                            }*/
                        }
                    }
                    if (!empty($notify_ids_ary)) {
                        $notif_msg = trans('admin/notifications.assigning_usergroup', ['medianame' => $asset['name'], 'groupname' => $usergroup['usergroup_name'], 'adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname]);
                        NotificationLog::getInsertNotification($notify_ids_ary, 'dams', $notif_msg);
                    }
                }
            }
        }
        if (!empty($ids)) {
            Dam::updateDAMSRelation($key, $arrname, $ids);
        }
        $notify_flag = true;
        foreach ($deleted as $value) {
            $value = (int)$value;
            if ($action == 'user') {
                if (Config::get('app.notifications.dams.mediarevoke') && $notify_flag) {
                    $notify_flag = false;
                    $notif_msg = trans('admin/notifications.mediarevoke', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'media' => $asset['name']]);
                    NotificationLog::getInsertNotification($notify_ids_d, 'dams', $notif_msg);
                }
            } elseif ($action == 'usergroup') {
                if (Config::get('app.notifications.dams.mediarevoke')) {
                    $usergroup_data = UserGroup::getUserGroupsUsingID((int)$value);
                    $notify_ids_ary = [];
                    foreach ($usergroup_data as $usergroup) {
                        if (isset($usergroup['relations']['active_user_usergroup_rel'])) {
                            $notify_ids_ary = array_merge($notify_ids_ary, $usergroup['relations']['active_user_usergroup_rel']);
                            /*foreach ($usergroup['relations']['active_user_usergroup_rel'] as $user) {
                                $notif_msg = trans('admin/notifications.mediarevoke', ['adminname' => Auth::user()->firstname.' '.Auth::user()->lastname, 'media' => $asset['name']]);
                                Notification::getInsertNotification($user, 'dams', $notif_msg);
                            }*/
                        }
                    }
                    if (!empty($notify_ids_ary)) {
                        $notif_msg = trans('admin/notifications.mediarevoke', ['adminname' => Auth::user()->firstname . ' ' . Auth::user()->lastname, 'media' => $asset['name']]);
                        NotificationLog::getInsertNotification($notify_ids_ary, 'dams', $notif_msg);
                    }
                }
            }
        }
        Dam::removeMediaRelation($key, [$arrname], $deleted);

        return response()->json(['flag' => 'success']);
    }

    public function getUploadHandler()
    {
        return redirect('cp/dams/add-media');
    }

    public function getEditMedia($key = null, $type = null)
    {
        $edit_media_permission_data_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::DAMS,
            PermissionType::ADMIN,
            DAMSPermission::EDIT_MEDIA,
            null,
            null,
            true
        );

        $packet = Input::get('post_slug');

        //$asset = Dam::getDAMSAssetsUsingID($key);
        if ($type == 1) {
            $key = Dam::getmongoid($key);
            $asset = Dam::getDAMSAssetsUsingID($key);
        } else {
            $asset = Dam::getDAMSAssetsUsingID($key);
        }
        if (empty($asset) || !$key) {
            //TODO: Satish: Re-validate the 'message' passed below
            return redirect('/cp/dams/')
                ->with('error', trans("admin/dams.missing_asset"));
        }

        // Can do this since we know that the array will not be empty and will not be associative array as well
        $asset = $asset[0];

        $edit_media_permission_data = get_permission_data($edit_media_permission_data_with_flag);
        if (!is_element_accessible($edit_media_permission_data, ElementType::MEDIA, $asset["id"])) {
            return parent::getAdminError($this->theme_path);
        }

        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/dams.manage_assets') => 'dams',
            trans('admin/dams.edit_media_asset') => '',
        ];

        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/dams.edit_media_asset');
        $this->layout->pageicon = 'fa fa-video-camera';
        $this->layout->pagedescription = trans('admin/dams.edit_asset');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'dams');
        $this->layout->content = view('admin.theme.dams.editmedia')
            ->with('asset', $asset)
            ->with('key', $key)
            ->with('packet', $packet);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postEditHandler($key = null, $type = null)
    {

        $edit_media_permission_data_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::DAMS,
            PermissionType::ADMIN,
            DAMSPermission::EDIT_MEDIA,
            null,
            null,
            true
        );

        //$asset = Dam::getDAMSAssetsUsingID($key);
        if ($type == 1) {
            $asset = Dam::getDAMSAssetsUsingID($key);
        } else {
            $asset = Dam::getDAMSAssetsUsingID($key);
        }
        //echo "<pre>"; print_r($asset); die;
        if (empty($asset) || !$key) {
            $msg = trans('admin/dams.missing_asset');

            return redirect('/cp/dams/')
                ->with('error', $msg);
        }

        // Can do this since we know that the array will not be empty and will not be associative array as well
        $asset = $asset[0];

        $edit_media_permission_data = get_permission_data($edit_media_permission_data_with_flag);
        if (!is_element_accessible($edit_media_permission_data, ElementType::MEDIA, $asset["id"])) {
            return parent::getAdminError($this->theme_path);
        }

        $media_type = $asset['type'];
        $input_type = $asset['asset_type'];
        $link_type = '';
        switch ($media_type) {
            case 'image':
                switch ($input_type) {
                    case 'file':
                        $errorflag = 0;
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'image|mimes:'.implode(',', config('app.dams_image_extensions')),
                        ];
                        $validation = Validator::make(Input::all(), $rules);
                        if ($validation->fails()) {
                            return redirect('cp/dams/edit-media/' . $key)->withInput()
                                ->withErrors($validation);
                        } else {
                            $keyword = Input::get('keyword');
                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $updatearr = [
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'type' => 'image',
                                'visibility' => $visibility,
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'updated_at' => time(),
                            ];

                            $file = Input::file('file');
                            // If file found, do the processing here
                            $private_dams_images_path = Config::get('app.private_dams_images_path');
                            $public_dams_images_path = Config::get('app.public_dams_images_path');
                            $unsetarr = ['dummy']; // This is a dummy array val since the unset wont take empty vals. This is a workaround
                            if ($file) {
                                $random_filename = strtolower(str_random(32));
                                while (true) {
                                    $result = Dam::getDAMSAsset($random_filename);
                                    if ($result->isEmpty()) {
                                        break;
                                    } else {
                                        $random_filename = strtolower(str_random(32));
                                    }
                                }
                                $image_sizes = Config::get('app.thumb_resolutions');
                                $private_dams_images_path = Config::get('app.private_dams_images_path');
                                $public_dams_images_path = Config::get('app.public_dams_images_path');
                                $updatearr['unique_name'] = $random_filename;
                                $updatearr['unique_name_with_extension'] = $random_filename . '.' . $file->getClientOriginalExtension();
                                $updatearr['file_client_name'] = $file->getClientOriginalName();
                                $updatearr['file_size'] = $file->getSize();
                                $updatearr['file_extension'] = $file->getClientOriginalExtension();
                                $updatearr['mimetype'] = $file->getMimeType();

                                // Delete the old files
                                if ($asset['visibility'] == 'public') {
                                    if (file_exists($asset['public_file_location'])) {
                                        unlink($asset['public_file_location']);
                                    }
                                } else {
                                    if (file_exists($asset['private_file_location'])) {
                                        unlink($asset['private_file_location']);
                                    }
                                }
                                foreach ($asset['thumb_img'] as $val) {
                                    if (file_exists($val)) {
                                        unlink($val);
                                    }
                                }

                                // Get Additional information
                                
                                if ($visibility == 'public') {
                                    $updatearr['public_file_location'] = $public_dams_images_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                    $file->move($public_dams_images_path, $updatearr['public_file_location']);
                                    // $updatearr['id3_info'] = $getID3->analyze($updatearr['public_file_location']);
                                    $unsetarr[] = 'private_file_location';
                                } else {
                                    $updatearr['private_file_location'] = $private_dams_images_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                    $file->move($private_dams_images_path, $updatearr['private_file_location']);
                                    // $updatearr['id3_info'] = $getID3->analyze($updatearr['private_file_location']);
                                    $unsetarr[] = 'public_file_location';
                                }
                                foreach ($image_sizes as $value) {
                                    $res = explode('x', $value);
                                    if (is_array($res)) {
                                        $loc = null;
                                        if ($visibility == 'public') {
                                            $image_obj = new Imagick($updatearr['public_file_location']);
                                            $loc = $public_dams_images_path . $random_filename . '_' . $value . '.' . $file->getClientOriginalExtension();
                                        } else {
                                            $image_obj = new Imagick($updatearr['private_file_location']);
                                            $loc = $private_dams_images_path . $random_filename . '_' . $value . '.' . $file->getClientOriginalExtension();
                                        }
                                        if (isset($res[0]) && isset($res[1])) {
                                            // $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                                            // skips resizing (and copy the original file) if the given res is less than the original image.
                                            if ($res[0] < $image_obj->getImageWidth() && $res[1] < $image_obj->getImageHeight()) {
                                                $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                                            }
                                            $image_obj->writeImage($loc);
                                            $updatearr['thumb_img'][$value] = $loc;
                                        }
                                    }
                                }
                            } elseif (!$file && $visibility != $asset['visibility']) {
                                if ($visibility == 'public') {
                                    File::move($asset['private_file_location'], $public_dams_images_path . $asset['unique_name_with_extension']);
                                    $updatearr['public_file_location'] = $public_dams_images_path . $asset['unique_name_with_extension'];
                                    foreach ($asset['thumb_img'] as $k => $value) {
                                        File::move($value, $public_dams_images_path . $asset['unique_name'] . '_' . $k . '.' . $asset['file_extension']);
                                        $updatearr['thumb_img'][$k] = $public_dams_images_path . $asset['unique_name'] . '_' . $k . '.' . $asset['file_extension'];
                                    }
                                    $unsetarr[] = 'private_file_location';
                                } else {
                                    File::move($asset['public_file_location'], $private_dams_images_path . $asset['unique_name_with_extension']);
                                    $updatearr['private_file_location'] = $private_dams_images_path . $asset['unique_name_with_extension'];
                                    foreach ($asset['thumb_img'] as $kk => $value) {
                                        File::move($value, $private_dams_images_path . $asset['unique_name'] . '_' . $kk . '.' . $asset['file_extension']);
                                        $updatearr['thumb_img'][$kk] = $private_dams_images_path . $asset['unique_name'] . '_' . $kk . '.' . $asset['file_extension'];
                                    }
                                    $unsetarr[] = 'public_file_location';
                                }
                            }
                            // Finally update the data to database and unset the unwanted variables

                            //item page redirection

                            if (Input::get('post_slug')) {
                                $post_slug = Input::get('post_slug');
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);
                                return redirect('cp/contentfeedmanagement/elements/' . $post_slug);
                            } else {
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);
                                return redirect('cp/dams/')
                                    ->with('success', trans('admin/dams.asset_edited_success'));
                            }
                        }
                        break;
                    case 'link':
                        $link_type = 'image';
                        break;
                }
                break;
            case 'video':
                switch ($input_type) {
                    case 'file':
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'checkextension|mimes:'.implode(',', config('app.dams_video_extensions')).'|max:' . config('app.dams_max_upload_size')*1024,
                            'srtfile' => 'checksrtextension',
                        ];
                        Validator::extend('checkextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            if (in_array($extension, config('app.dams_video_extensions'))) {
                                return true;
                            }

                            return false;
                        });
                        Validator::extend('checksrtextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            if (in_array(strtolower($extension), ['srt'])) {
                                return true;
                            }

                            return false;
                        });
                        $messages = [
                            'checkextension' => trans('admin/dams.check_video_extension'),
                            'checksrtextension' => trans('admin/dams.check_srt_extension'),
                        ];
                        $validation = Validator::make(Input::all(), $rules, $messages);
                        if ($validation->fails()) {
                            return redirect('cp/dams/edit-media/' . $key)->withInput()
                                ->withErrors($validation);
                        } else {
                            $keyword = Input::get('keyword');
                            $dams_temp_video_path = Config::get('app.dams_temp_video_path');
                            $dams_srt_path = Config::get('app.dams_srt_path');
                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $updatearr = [
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'visibility' => $visibility,
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'updated_at' => time(),
                            ];
                            $file = Input::file('file');
                            $srtfile = Input::file('srtfile');
                            $dams_temp_video_path = Config::get('app.dams_temp_video_path');
                            $dams_srt_path = Config::get('app.dams_srt_path');
                            $unsetarr = ['dummy']; // This is a dummy array val since the unset wont take empty vals. This is a workaround
                            if ($file) { // Check if new files is uploaded
                                $random_filename = strtolower(str_random(32));
                                while (true) {
                                    $result = Dam::getDAMSAsset($random_filename);
                                    if ($result->isEmpty()) {
                                        break;
                                    } else {
                                        $random_filename = strtolower(str_random(32));
                                    }
                                }

                                if (isset($asset['temp_location']) && file_exists($asset['temp_location'])) { // Check if the temp file exists, if yes then delete that.
                                    unlink($asset['temp_location']);
                                }

                                if (isset($asset['kaltura_details'])) { // Check if video already present in kaltura, if yes then delete that
                                    Dam::deleteKalturaVideo($asset['kaltura_details']['id']);
                                }

                                // Check if the files exists in Akamai. If yes then delete that
                                if (isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == '200') {
                                    Dam::deleteAkamaiVideo($asset);
                                }

                                // Copy the new file to new directory
                                $updatearr['unique_name'] = $random_filename;
                                $updatearr['unique_name_with_extension'] = $random_filename . '.' . $file->getClientOriginalExtension();
                                $updatearr['file_client_name'] = $file->getClientOriginalName();
                                $updatearr['id3_info'] = '';
                                $updatearr['file_size'] = $file->getSize();
                                $updatearr['file_extension'] = $file->getClientOriginalExtension();
                                $updatearr['mimetype'] = $file->getMimeType();
                                $updatearr['video_status'] = 'INTEMP';
                                $file->move($dams_temp_video_path, $random_filename . '.' . $file->getClientOriginalExtension());
                                $updatearr['temp_location'] = $dams_temp_video_path . $random_filename . '.' . $file->getClientOriginalExtension();

                                if ($srtfile) {
                                    if (isset($asset['srt_location'])) {
                                        if (file_exists($asset['srt_location'])) {
                                            unlink($asset['srt_location']);
                                        }
                                    }
                                    $updatearr['srt_unique_name_with_extension'] = $random_filename . '.' . $srtfile->getClientOriginalExtension();
                                    $updatearr['srt_location'] = $dams_srt_path . $random_filename . '.' . $srtfile->getClientOriginalExtension();
                                    $updatearr['srt_status'] = 'ADDED';
                                    $updatearr['srt_client_name'] = $srtfile->getClientOriginalName();
                                    $srtfile->move($dams_srt_path, $random_filename . '.' . $srtfile->getClientOriginalExtension());
                                } else {
                                    if (isset($asset['kaltura_details']['srt_data'])) {
                                        $unsetarr[] = 'kaltura_details.srt_data';
                                    }
                                    if (isset($asset['akamai_details'])) {
                                        $unsetarr[] = 'akamai_details';
                                    }
                                    $unsetarr[] = 'srt_unique_name_with_extension';
                                    $unsetarr[] = 'srt_location';
                                    $unsetarr[] = 'srt_status';
                                    $unsetarr[] = 'srt_client_name';
                                }

                                // If srt is available, update that entry in database so that cron can pick it up.

                                // If Srt is updated with out video change then check if old video has srt, if yes then remove that and attach this or just attach this
                            } elseif (!$file && $srtfile) {
                                // Remove old srt data from data base and kaltura. Then push the new data to database and
                                if (isset($asset['srt_location'])) {
                                    if (file_exists($asset['srt_location'])) {
                                        unlink($asset['srt_location']);
                                    }
                                }
                                $random_filename = $asset['unique_name'];
                                $updatearr['srt_unique_name_with_extension'] = $random_filename . '.' . $srtfile->getClientOriginalExtension();
                                $updatearr['srt_location'] = $dams_srt_path . $random_filename . '.' . $srtfile->getClientOriginalExtension();
                                $updatearr['srt_status'] = 'ADDED';
                                $updatearr['srt_client_name'] = $srtfile->getClientOriginalName();
                                $srtfile->move($dams_srt_path, $random_filename . '.' . $srtfile->getClientOriginalExtension());
                                if (isset($asset['kaltura_details']['srt_data']['id'])) {
                                    Dam::deleteCaptionAsset($asset['kaltura_details']['srt_data']['id']);
                                }
                                if (isset($asset['kaltura_details']['id'])) {
                                    $recorddata = Dam::addCaptionAsset($asset['kaltura_details']['id'], $updatearr['srt_location']);
                                    $updatearr['srt_status'] = 'READY';
                                    Dam::where('srt_location', '=', $asset['srt_location'])->update(['kaltura_details.srt_data' => $recorddata]);
                                }
                            }

                            //item page redirection code
                            if (Input::get('post_slug')) {
                                $post_slug = Input::get('post_slug');
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);
                                {
                                    return redirect('cp/contentfeedmanagement/elements/' . $post_slug);
                                }
                            } else {
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);

                                return redirect('cp/dams/')
                                    ->with('success', trans('admin/dams.asset_edited_success'));
                            }
                        }
                        break;
                    case 'link':
                        $link_type = 'document';
                        break;
                }
                break;
            case 'document':
                switch ($input_type) {
                    case 'file':
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'uploadable|checkextension',
                        ];
                        Validator::extend('uploadable', function ($attribute, $value, $parameters) {
                            return $value instanceof UploadedFile
                            && in_array($value->getMimeType(), config('app.dams_document_mime_types'));
                        });
                        Validator::extend('checkextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            if (in_array($extension, config('app.dams_document_extensions'))) {
                                return true;
                            }

                            return false;
                        });
                        $messages = [
                            'checkextension' => trans('admin/dams.check_doc_extension'),
                            'uploadable' => trans('admin/dams.mimetype_error'),
                        ];
                        $validation = Validator::make(Input::all(), $rules, $messages);
                        if ($validation->fails()) {
                            return redirect('cp/dams/edit-media/' . $key)->withInput()
                                ->withErrors($validation);
                        } else {
                            $keyword = Input::get('keyword');
                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $updatearr = [
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'visibility' => $visibility,
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'updated_at' => time(),
                            ];
                            $file = Input::file('file');

                            $private_dams_documents_path = Config::get('app.private_dams_documents_path');
                            $public_dams_documents_path = Config::get('app.public_dams_documents_path');
                            $unsetarr = ['dummy']; // This is a dummy array val since the unset wont take empty vals. This is a workaround

                            $data = new stdClass();
                            $data->id = $key;
                            $data->new_file = false;

                            if ($file) {
                                $random_filename = strtolower(str_random(32));
                                while (true) {
                                    $result = Dam::getDAMSAsset($random_filename);
                                    if ($result->isEmpty()) {
                                        break;
                                    } else {
                                        $random_filename = strtolower(str_random(32));
                                    }
                                }

                                $updatearr['unique_name'] = $random_filename;
                                $updatearr['unique_name_with_extension'] = $random_filename . '.' . $file->getClientOriginalExtension();
                                $updatearr['file_client_name'] = $file->getClientOriginalName();
                                $updatearr['file_size'] = $file->getSize();
                                $updatearr['file_extension'] = $file->getClientOriginalExtension();
                                $updatearr['mimetype'] = $file->getMimeType();

                                $full_path = config::get('app.public_dams_documents_path');
                                $file_path = $full_path . array_get($updatearr, 'unique_name_with_extension');

                                $data->file_path = $file_path;
                                $data->new_file = true;
                        
                                // Delete the old files
                                if ($asset['visibility'] == 'public') {
                                    if (file_exists($asset['public_file_location'])) {
                                        unlink($asset['public_file_location']);
                                    }
                                } else {
                                    if (file_exists($asset['private_file_location'])) {
                                        unlink($asset['private_file_location']);
                                    }
                                }

                                
                                if ($visibility == 'public') {
                                    $updatearr['public_file_location'] = $public_dams_documents_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                    $file->move($public_dams_documents_path, $updatearr['public_file_location']);
                                    // $updatearr['id3_info'] = $getID3->analyze($updatearr['public_file_location']);
                                    $unsetarr[] = 'private_file_location';
                                } else {
                                    $updatearr['private_file_location'] = $private_dams_documents_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                    $file->move($private_dams_documents_path, $updatearr['private_file_location']);
                                    // $updatearr['id3_info'] = $getID3->analyze($updatearr['private_file_location']);
                                    $unsetarr[] = 'public_file_location';
                                }
                            } elseif (!$file && $visibility != $asset['visibility']) {
                                if ($visibility == 'public') {
                                    File::move($asset['private_file_location'], $public_dams_documents_path . $asset['unique_name_with_extension']);
                                    $updatearr['public_file_location'] = $public_dams_documents_path . $asset['unique_name_with_extension'];
                                    $unsetarr[] = 'private_file_location';
                                } else {
                                    File::move($asset['public_file_location'], $private_dams_documents_path . $asset['unique_name_with_extension']);
                                    $updatearr['private_file_location'] = $private_dams_documents_path . $asset['unique_name_with_extension'];
                                    $unsetarr[] = 'public_file_location';
                                }
                            }
                           
                            //item page redirection
                            if (Input::get('post_slug')) {
                                $post_slug = Input::get('post_slug');
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);
                                {
                                    Event::fire(new DocumentUpdated($data));
                                    return redirect('cp/contentfeedmanagement/elements/' . $post_slug);
                                }
                            } else {
                                
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);
                                Event::fire(new DocumentUpdated($data));

                                return redirect('cp/dams/')
                                    ->with('success', trans('admin/dams.asset_edited_success'));
                            }
                        }
                        break;
                    case 'link':
                        $link_type = 'document';
                        break;
                }
                break;
            case 'audio':
                switch ($input_type) {
                    case 'file':
                        $rules = [
                            'title' => 'Required',
                            // 'description' => 'Required',
                            // 'keyword' => 'Required',
                            'visibility' => 'Required',
                            'file' => 'checkmime|checkextension',
                        ];
                        Validator::extend('checkextension', function ($attribute, $value, $parameters) {
                            $extension = $value->getClientOriginalExtension();
                            if (in_array($extension, config('app.dams_audio_extensions'))) {
                                return true;
                            }

                            return false;
                        });
                        Validator::extend('checkmime', function ($attribute, $value, $parameters) {
                            $extension = $value->getMimeType();
                            if (in_array($extension, config('app.dams_audio_mime_types'))) {
                                return true;
                            }

                            return false;
                        });
                        $messages = [
                            'checkextension' => trans('admin/dams.check_audio_extension'),
                            'checkmime' => trans('admin/dams.check_audio_extension'),
                        ];
                        $validation = Validator::make(Input::all(), $rules, $messages);
                        if ($validation->fails()) {
                            return redirect('cp/dams/edit-media/' . $key)->withInput()
                                ->withErrors($validation);
                        } else {
                            $keyword = Input::get('keyword');
                            $visibility = Input::get('visibility');
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $updatearr = [
                                'name' => Input::get('title'),
                                'name_lower' => strtolower(Input::get('title')),
                                'description' => Input::get('description'),
                                'visibility' => $visibility,
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'updated_at' => time(),
                            ];
                            $file = Input::file('file');
                            $private_dams_audio_path = Config::get('app.private_dams_audio_path');
                            $public_dams_audio_path = Config::get('app.public_dams_audio_path');
                            $unsetarr = ['dummy']; // This is a dummy array val since the unset wont take empty vals. This is a workaround
                            if ($file) {
                                $random_filename = strtolower(str_random(32));
                                while (true) {
                                    $result = Dam::getDAMSAsset($random_filename);
                                    if ($result->isEmpty()) {
                                        break;
                                    } else {
                                        $random_filename = strtolower(str_random(32));
                                    }
                                }
                                $updatearr['unique_name'] = $random_filename;
                                $updatearr['unique_name_with_extension'] = $random_filename . '.' . $file->getClientOriginalExtension();
                                $updatearr['file_client_name'] = $file->getClientOriginalName();
                                $updatearr['file_size'] = $file->getSize();
                                $updatearr['file_extension'] = $file->getClientOriginalExtension();
                                $updatearr['mimetype'] = $file->getMimeType();

                                // Delete the old files
                                if ($asset['visibility'] == 'public') {
                                    if (file_exists($asset['public_file_location'])) {
                                        unlink($asset['public_file_location']);
                                    }
                                } else {
                                    if (file_exists($asset['private_file_location'])) {
                                        unlink($asset['private_file_location']);
                                    }
                                }

                                
                                if ($visibility == 'public') {
                                    $updatearr['public_file_location'] = $public_dams_audio_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                    $file->move($public_dams_audio_path, $updatearr['public_file_location']);
                                    // $updatearr['id3_info'] = $getID3->analyze($updatearr['public_file_location']);
                                    $unsetarr[] = 'private_file_location';
                                } else {
                                    $updatearr['private_file_location'] = $private_dams_audio_path . $random_filename . '.' . $file->getClientOriginalExtension();
                                    $file->move($private_dams_audio_path, $updatearr['private_file_location']);
                                    // $updatearr['id3_info'] = $getID3->analyze($updatearr['private_file_location']);
                                    $unsetarr[] = 'public_file_location';
                                }
                            } elseif (!$file && $visibility != $asset['visibility']) {
                                if ($visibility == 'public') {
                                    File::move($asset['private_file_location'], $public_dams_audio_path . $asset['unique_name_with_extension']);
                                    $updatearr['public_file_location'] = $public_dams_audio_path . $asset['unique_name_with_extension'];
                                    $unsetarr[] = 'private_file_location';
                                } else {
                                    File::move($asset['public_file_location'], $private_dams_audio_path . $asset['unique_name_with_extension']);
                                    $updatearr['private_file_location'] = $private_dams_audio_path . $asset['unique_name_with_extension'];
                                    $unsetarr[] = 'public_file_location';
                                }
                            }
                            //item page redirection
                            if (Input::get('post_slug')) {
                                $post_slug = Input::get('post_slug');
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);
                                {
                                    return redirect('cp/contentfeedmanagement/elements/' . $post_slug);
                                }
                            } else {
                                Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);

                                return redirect('cp/dams/')
                                    ->with('success', trans('admin/dams.asset_edited_success'));
                            }
                        }
                        break;
                    case 'link':
                        $link_type = 'audio';
                        break;
                }
                break;
            case 'scorm':
                ScormValidator::extendValidatorToValidateScorm();
                $rules = [
                    'title' => 'Required',
                    'file' => 'sometimes|file|mimes:zip|max:1048576|unsupported_files_not_exist|imsmanifest_exists',
                ];

                $validation = Validator::make(Input::all(), $rules);

                if ($validation->fails()) {
                    return redirect('cp/dams/edit-media/' . $key)->withInput()
                        ->withErrors($validation);
                } else {
                    $keyword = Input::get('keyword');
                    $updatearr = [
                        'name' => Input::get('title'),
                        'name_lower' => strtolower(Input::get('title')),
                        'description' => Input::get('description'),
                        'visibility' => "public",
                        'tags' => explode(',', $keyword),
                        'status' => 'ACTIVE',
                        'updated_at' => time(),
                    ];
                    $file = Input::file('file');
                    $public_dams_scorm_path = Config::get('app.public_dams_scorm_path');
                    $unsetarr = ['dummy']; // This is a dummy array val since the unset wont take empty vals. This is a workaround
                    if ($file) {
                        $random_filename = strtolower(str_random(32));
                        while (true) {
                            $result = Dam::getDAMSAsset($random_filename);
                            if ($result->isEmpty()) {
                                break;
                            } else {
                                $random_filename = strtolower(str_random(32));
                            }
                        }
                        $file_client_name = basename($file->getClientOriginalName(), ".zip");

                        $updatearr['unique_name'] = $random_filename;
                        //$updatearr['unique_name_with_extension'] = $random_filename.'.'.$file->getClientOriginalExtension();
                        $updatearr['file_client_name'] = $file_client_name;
                        $updatearr['file_size'] = $file->getSize();
                        $updatearr['file_extension'] = $file->getClientOriginalExtension();
                        $updatearr['mimetype'] = $file->getMimeType();

                        // Delete the old files
                        $this->recursiveRemoveDirectory($asset['public_file_location']);

                        $updatearr['public_file_location'] = $public_dams_scorm_path . $random_filename;
                        // $updatearr['id3_info'] = $getID3->analyze($updatearr['public_file_location']);
                        $zip = new ZipArchive;
                        if ($zip->open($file) === true) {
                            $zip->extractTo($public_dams_scorm_path . $random_filename);
                            $zip->close();
                        }

                        $scorm_config_data =
                            ScormHelper::getScormConfigData($updatearr['public_file_location']);
                        if (array_has($scorm_config_data, "scorm_version")
                            && array_has($scorm_config_data, "scorm_launch_file")) {
                            $updatearr["version"] = $scorm_config_data["scorm_version"];
                            $updatearr["launch_file"] = $scorm_config_data["scorm_launch_file"];

                            if (array_has($scorm_config_data, "scorm_mastery_score")) {
                                $updatearr["mastery_score"] = $scorm_config_data["scorm_mastery_score"];
                            }
                        }
                    }

                    //item page redirection
                    if (Input::get('post_slug')) {
                        $post_slug = Input::get('post_slug');
                        Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);
                        {
                            return redirect('cp/contentfeedmanagement/elements/' . $post_slug);
                        }
                    } else {
                        Dam::where('_id', '=', $key)->unset($unsetarr)->update($updatearr);

                        return redirect('cp/dams/')
                            ->with('success', trans('admin/dams.asset_edited_success'));
                    }
                }
                break;
        }
        if ($link_type && in_array($link_type, ['image', 'document', 'video', 'audio'])) {
            $rules = [
                'link_title' => 'Required',
                // 'link_description' => 'Required',
                // 'link_keyword' => 'Required',
                'link_visibility' => 'Required',
                'link_url' => 'Required|url',
            ];
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return redirect('cp/dams/edit-media/' . $key)->withInput()
                    ->withErrors($validation);
            } else {
                $visibility = Input::get('link_visibility');
                $keyword = Input::get('link_keyword');
                if ($visibility != 'public') {
                    $visibility = 'private';
                }
                $updatearr = [
                    'name' => Input::get('link_title'),
                    'name_lower' => strtolower(Input::get('link_title')),
                    'description' => Input::get('link_description'),
                    'asset_type' => 'link',
                    'url' => Input::get('link_url'),
                    'visibility' => $visibility,
                    'tags' => explode(',', $keyword),
                    'status' => 'ACTIVE',
                    'updated_at' => time(),
                ];
                Dam::where('_id', '=', $key)->update($updatearr);

                return redirect('cp/dams/')
                    ->with('success', trans('admin/dams.asset_edited_success'));
            }
        }

        return redirect('cp/dams/')
        
            ->with('error', trans('admin/dams.memory_limit', ['size' => config('app.dams_max_upload_size')]));
    }


    public function getMediaDetails($key = null)
    {
        $asset = [];
        $token = null;
        $kaltura = null;
        $message = null;
        $s3_path = "";
        $view_media_permission_data_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::DAMS,
            PermissionType::ADMIN,
            DAMSPermission::VIEW_MEDIA,
            null,
            null,
            true
        );

        try {
            $asset = Dam::getMediaById($key);
            if(isset($asset->s3_path)) {

                $s3_path = Dam::getSignedurl($asset->s3_path);  

            }
            
            $view_media_permission_data = get_permission_data($view_media_permission_data_with_flag);
            if (!is_element_accessible($view_media_permission_data, ElementType::MEDIA, $asset->id)) {
                $message = trans("admin/dams.no_permission_to_view_media");
            }

            $token = null;
            $uniconf_id = Config::get('app.uniconf_id');
            $kaltura_url = Config::get('app.kaltura_url');
            $partnerId = Config::get('app.partnerId');
            // getToken method is in AkamaiTokenTrait
            $token = $this->getToken($asset->toArray());
            $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';
        } catch (\Exception $e) {
            $message = trans("admin/dams.missing_asset");
        }

        return view('admin.theme.dams.mediadetails')
            ->with('asset', $asset)
            ->with('kaltura', $kaltura)
            ->with('token', $token)
            ->with('s3_path', $s3_path)
            ->with('message', $message);
    }

    public function getShowMedia($key = null)
    {
        // Need to be changed
        /* Fix for audio play back. Jwplayer only accepts file URL with extension*/
        if (strpos($key, '.mp3')) {
            $key = rtrim($key, '.mp3');
        }

        $id = Input::get('id', '_id');
        if ($id == 'id') {
            $key = Dam::getmongoid($key);
            $asset = Dam::getDAMSAssetsUsingID($key);
        } else {
            $asset = Dam::getDAMSAssetsUsingID($key);
        }
        if (empty($asset) || !$key) {
            return response('');
        }
        $asset = $asset[0];
        $preview = Input::get('preview');
        switch ($asset['type']) {
            case 'image':
                $this->showImage($asset, $preview);
                break;
            case 'document':
                $this->showDocument($asset, $preview);
                break;
            case 'audio':
                $this->showAudio($asset, $preview);
                break;
            case 'video':
                return $this->showVideo($asset, $preview);
                break;
            default:
                break;
        }

        return '';
    }

    private function showImage($asset, $preview)
    {
        if ($asset['asset_type'] == 'file') {
            $file_extension = $asset['file_extension'];
            if (isset($asset['mimetype'])) {
                $ctype = $asset['mimetype'];
            }
            switch (!$ctype && $file_extension) {
                case 'gif':
                    $ctype = 'image/gif';
                    break;
                case 'png':
                    $ctype = 'image/png';
                    break;
                case 'jpeg':
                case 'jpg':
                    $ctype = 'image/jpg';
                    break;
                default:
            }
            $thumb = Input::get('thumb');
            if ($asset['visibility'] == 'private') {
                $fullpath = Config::get('app.private_dams_images_path');
            } else {
                $fullpath = Config::get('app.public_dams_images_path');
            }
            if ($thumb && isset($asset['thumb_img'][$thumb])) {
                $fullpath = $fullpath . $asset['unique_name'] . '_' . $thumb . '.' . $asset['file_extension'];
            } else {
                $fullpath = $fullpath . $asset['unique_name_with_extension'];
            }
            if (file_exists($fullpath)) {
                // Resizing the image
                $width = Input::get('width');
                $height = Input::get('height');
                if (!$height) {
                    $height = $width;
                }
                $image_obj = new Imagick($fullpath);
                if ($width) {
                    //$image_obj->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
                    // skips resizing (and copy the original file) if the given res is less than the original image.
                    if ($width < $image_obj->getImageWidth() && $height < $image_obj->getImageHeight()) {
                        $image_obj->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
                    }
                }
                header('Content-type: ' . $ctype);
                echo $image_obj->getImageBlob();
                exit;
            } else {
                return '';
            }
        } elseif ($asset['asset_type'] == 'link') {
            if ($preview) {
                $fullpath = Config::get('app.default_link_image');
                header('Content-type: image/png');
                readfile($fullpath);
            } else {
                header('Content-type: image/png');
                echo file_get_contents($asset['url']);
                exit;
            }
            // return $asset['url'];
        }
    }

    private function showDocument($asset, $preview)
    {
        if ($asset['asset_type'] == 'file') {
            $ctype = '';
            if (isset($asset['mimetype'])) {
                $ctype = $asset['mimetype'];
            }
            if ($preview) {
                $fullpath = Config::get('app.default_document_image');
                header('Content-type: image/png');
                readfile($fullpath);
            } else {
                if ($asset['visibility'] == 'private') {
                    $fullpath = Config::get('app.private_dams_documents_path');
                    $fullpath = $fullpath . $asset['unique_name_with_extension'];
                    if (file_exists($fullpath)) {
                        header('Content-Disposition: attachment; filename="' . $asset['file_client_name'] . '"');
                        header('Content-type: ' . $ctype);
                        readfile($fullpath);
                    } else {
                        return '';
                    }
                } else {
                    $fullpath = Config::get('app.public_dams_documents_path');
                    $fullpath = $fullpath . $asset['unique_name_with_extension'];
                    header('Content-Disposition: attachment; filename="' . $asset['file_client_name'] . '"');
                    header('Content-type: ' . $ctype);
                    readfile($fullpath);
                }
            }
        } elseif ($asset['asset_type'] == 'link') {
            if ($preview) {
                $fullpath = Config::get('app.default_link_image');
                header('Content-type: image/png');
                readfile($fullpath);
            } else {
                return redirect($asset['url']);
            }
        }
    }

    private function showAudio($asset, $preview)
    {
        if ($asset['asset_type'] == 'file') {
            $ctype = '';
            if (isset($asset['mimetype'])) {
                $ctype = $asset['mimetype'];
            }
            if ($preview) {
                $fullpath = Config::get('app.default_audio_image');
                header('Content-type: image/png');
                readfile($fullpath);
            } else {
                if ($asset['visibility'] == 'private') {
                    $fullpath = Config::get('app.private_dams_audio_path');
                } else {
                    $fullpath = Config::get('app.public_dams_audio_path');
                }
                $fullpath = $fullpath . $asset['unique_name_with_extension'];
                if (file_exists($fullpath)) {
                    $filesize = filesize($fullpath);
                    header('Content-Disposition: attachment; filename="' . $asset['file_client_name'] . '"');
                    header('Content-type: ' . $ctype);
                    header('Content-Length: ' . $filesize);
                    readfile($fullpath);
                } else {
                    return '';
                }
            }
        } elseif ($asset['asset_type'] == 'link') {
            if ($preview) {
                $fullpath = Config::get('app.default_link_image');
                header('Content-type: image/png');
                readfile($fullpath);
            } else {
                return redirect($asset['url']);
            }
        }
    }

    private function showVideo($asset, $preview)
    {
        if ($asset['asset_type'] == 'file') {
            $returntype = Input::get('return', 'thumbnail');
            if ($returntype == 'thumbnail') {
                $ctype = 'image/png';
                $filename = config('app.dams_video_thumb_path') . $asset['unique_name'] . '.png';
                if (file_exists($filename)) {
                    // To deliver the source image with alteration
                    if (Input::get('raw') == 'yes') {
                        header('Content-type: ' . $ctype);
                        echo file_get_contents($filename);
                        exit;
                    }
                    // To compress png image to PNG8
                    if ($ctype == 'image/png' && Input::get('compress', 0) == 1) {
                        $image_obj = new Imagick($filename);
                        $image_obj->setImageFormat('PNG8');
                        $colors = min(255, $image_obj->getImageColors());
                        $image_obj->quantizeImage($colors, Imagick::COLORSPACE_RGB, 0, false, false);
                        $image_obj->setImageDepth(8);
                        header('Content-type: ' . $ctype);
                        echo $image_obj->getImageBlob();
                        exit;
                    }
                    return $this->respondImage($filename, $ctype);
                }
                if (isset($asset['kaltura_details']['thumbnailUrl'])) {
                    $width = Input::get('width', '100');
                    $height = Input::get('height', '100');
                    if ($width) {
                        $asset['kaltura_details']['thumbnailUrl'] = $asset['kaltura_details']['thumbnailUrl'] . '/width/' . $width;
                    }
                    if ($height) {
                        $asset['kaltura_details']['thumbnailUrl'] = $asset['kaltura_details']['thumbnailUrl'] . '/height/' . $height;
                    }
                    header('Content-type: ' . $ctype);
                    echo file_get_contents($asset['kaltura_details']['thumbnailUrl']);
                    exit;
                }
                return $this->respondImage(config('app.solid_black'), 'image/png');
            } else {
                if (isset($asset['kaltura_details']['id'])) {
                    $uniconf_id = Config::get('app.uniconf_id');
                    $kaltura_url = Config::get('app.kaltura_url');
                    $partnerId = Config::get('app.partnerId');

                    $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';
                    echo '<object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" height="330" width="400" rel="media:audio" resource="' . $kaltura . $asset['kaltura_details']['id'] . '" data="' . $kaltura . $asset['kaltura_details']['id'] . '">
                        <param name="allowFullScreen" value="true" />
                        <param name="allowNetworking" value="all" />
                        <param name="allowScriptAccess" value="always" />
                        <param name="bgcolor" value="#000000" />
                        <param name="flashVars" value="" />
                        <param name="movie" value="' . $kaltura . $asset['kaltura_details']['id'] . '/>
                        <span property="dc:description" content=""></span>
                        <span property="media:title" content="Kaltura Video"></span>
                        <span property="media:type" content="application/x-shockwave-flash"></span>
                    </object>';
                    exit;
                }
            }
        } elseif ($asset['asset_type'] == 'link') {
            if ($preview) {
                 return $this->respondImage(Config::get('app.default_link_image'), 'image/png');
            } else {
                return redirect($asset['url']);
            }
        }
    }

    public function getVideoSrt($key = null)
    {
        $id = Input::get('id', '_id');
        if ($id == 'id') {
            $asset = Dam::getDAMSAssetsUsingAutoID((int)$key);
        } else {
            $asset = Dam::getDAMSAssetsUsingID($key);
        }
        if (empty($asset) || !$key) {
            return response('');
        }
        $asset = $asset[0];
        if ($asset['type'] == 'video' && $asset['asset_type'] == 'file') {
            if (isset($asset['srt_location']) && file_exists($asset['srt_location'])) {
                header('Content-type: text/srt');
                readfile($asset['srt_location']);
                exit;
            } else {
                return response('');
            }
        } else {
            return response('');
        }
    }

    public function getDeleteMedia($key = null)
    {
        $delete_media_permission_data_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::DAMS,
            PermissionType::ADMIN,
            DAMSPermission::DELETE_MEDIA,
            null,
            null,
            true
        );

        $asset = Dam::getDAMSAssetsUsingID($key);
        if (empty($asset) || !$key) {
            $msg = trans('admin/dams.missing_asset');

            return redirect('/cp/dams/')
                ->with('error', $msg);
        }
        $deleteflag = false;
        $asset = $asset[0];

        $delete_media_permission_data = get_permission_data($delete_media_permission_data_with_flag);
        if (!is_element_accessible($delete_media_permission_data, ElementType::MEDIA, $asset["id"])) {
            return parent::getAdminError($this->theme_path);
        }

        //datatable pagination
        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '1 desc');
        $filter = Input::get('filter', 'ACTIVE');

        // Deleting media relations from Users
        if (isset($asset['relations']) && is_array($asset['relations']) && !empty($asset['relations'])) {
            if (isset($asset['relations']['active_user_media_rel']) && !empty($asset['relations']['active_user_media_rel'])) {
                foreach ($asset['relations']['active_user_media_rel'] as $each_auid) {
                    User::removeUserRelation($each_auid, ['user_media_rel'], $asset['id']);
                }
            }
            if (isset($asset['relations']['inactive_user_media_rel']) && !empty($asset['relations']['inactive_user_media_rel'])) {
                foreach ($asset['relations']['inactive_user_media_rel'] as $each_auid) {
                    User::removeUserRelation($each_auid, ['user_media_rel'], $asset['id']);
                }
            }

            // Deleting media relations from User groups
            if (isset($asset['relations']['active_usergroup_media_rel']) && !empty($asset['relations']['active_usergroup_media_rel'])) {
                foreach ($asset['relations']['active_usergroup_media_rel'] as $each_augid) {
                    UserGroup::removeUserGroupRelation($each_augid, ['usergroup_media_rel'], $asset['id']);
                }
            }
            if (isset($asset['relations']['inactive_usergroup_media_rel']) && !empty($asset['relations']['inactive_usergroup_media_rel'])) {
                foreach ($asset['relations']['inactive_usergroup_media_rel'] as $each_iaugid) {
                    UserGroup::removeUserGroupRelation($each_iaugid, ['usergroup_media_rel'], $asset['id']);
                }
            }

            //Deleting media relations from announcements
            if (isset($asset['relations']['media_announcement_rel']) && !empty($asset['relations']['media_announcement_rel'])) {
                foreach ($asset['relations']['media_announcement_rel'] as $each_auid) {
                    Announcement::removeAnnouncementRelation($each_auid, ['active_media_announcement_rel'], $asset['id']);
                }
            }

            //Deleting media relations from Channel
            if (isset($asset['relations']['contentfeed_media_rel']) && !empty($asset['relations']['contentfeed_media_rel'])) {
                Program::removeChannelCoverMedia($asset['relations']['contentfeed_media_rel']);
            }
            //Deleting media relations from lmsprogram
            if (isset($asset['relations']['lmscourse_media_rel']) && !empty($asset['relations']['lmscourse_media_rel'])) {
                ManageLmsProgram::removeLmscoureseCoverMedia($asset['relations']['lmscourse_media_rel']);
            }
            //Delete cover media relations from post
            if (isset($asset['relations']['packet_banner_media_rel']) && !empty($asset['relations']['packet_banner_media_rel'])) {
                Packet::removePacketCoverMedia($asset['relations']['packet_banner_media_rel']);
            }

            //Delete item media relations from post
            if (isset($asset['relations']['dams_packet_rel']) && !empty($asset['relations']['dams_packet_rel'])) {
                Packet::removePacketItemMedia($asset['relations']['dams_packet_rel'], $asset['id']);
            }
        }
        // If its link then delete it and return
        if ($asset['asset_type'] == 'link') {
            Dam::deleteAsset($asset['_id']);
            $totalRecords = Dam::getDamsCount();
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }

            $msg = trans('admin/dams.asset_deleted');

            return redirect('/cp/dams?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                ->with('success', $msg);
        } else {
            // else detect the type and do the processing
            switch ($asset['type']) {
                case 'video':
                    // Check and delete temp file
                    if (isset($asset['temp_location']) && file_exists($asset['temp_location'])) {
                        unlink($asset['temp_location']);
                    }
                    // Check and delete srt file
                    if (isset($asset['srt_location']) && file_exists($asset['srt_location'])) {
                        unlink($asset['srt_location']);
                    }
                    // Check if the file exists in kaltura. if yes then delete that
                    if (isset($asset['kaltura_details']['id'])) {
                        Dam::deleteKalturaVideo($asset['kaltura_details']['id']);
                    }
                    // Check if the files exists in Akamai. If yes then delete that
                    if (isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == '200') {
                        Dam::deleteAkamaiVideo($asset);
                    }
                    $deleteflag = true;
                    // finally change the deleteflag = true
                    break;
                case 'image':
                    if ($asset['visibility'] == 'public') {
                        if (file_exists($asset['public_file_location'])) {
                            unlink($asset['public_file_location']);
                        }
                        if (isset($asset['thumb_img']) && is_array($asset['thumb_img'])) {
                            foreach ($asset['thumb_img'] as $key => $value) {
                                unlink($value);
                            }
                        }
                        $deleteflag = true;
                    } else {
                        if (file_exists($asset['private_file_location'])) {
                            unlink($asset['private_file_location']);
                        }
                        if (isset($asset['thumb_img']) && is_array($asset['thumb_img'])) {
                            foreach ($asset['thumb_img'] as $key => $value) {
                                unlink($value);
                            }
                        }
                        $deleteflag = true;
                    }
                    break;
                case 'document':
                    Event::fire(new DocumentDeleted($asset));
                case 'audio':
                    if ($asset['visibility'] == 'public') {
                        if (file_exists($asset['public_file_location'])) {
                            unlink($asset['public_file_location']);
                        }
                        $deleteflag = true;
                    } else {
                        if (file_exists($asset['private_file_location'])) {
                            unlink($asset['private_file_location']);
                        }
                        $deleteflag = true;
                    }
                    break;
                case 'scorm':
                    if ($asset['visibility'] == 'public') {
                        if (is_dir($asset['public_file_location'])) {
                            $this->recursiveRemoveDirectory($asset['public_file_location']);
                        }
                        $deleteflag = true;
                    } else {
                        if (is_dir($asset['private_file_location'])) {
                            $this->recursiveRemoveDirectory($asset['private_file_location']);
                        }
                        $deleteflag = true;
                    }
                    break;
            }
            if ($deleteflag) {
                Dam::deleteAsset($asset['_id']);
                $totalRecords = Dam::getDamsCount();
                if ($totalRecords <= $start) {
                    $start -= $limit;
                    if ($start < 0) {
                        $start = 0;
                    }
                }

                $msg = trans('admin/dams.asset_deleted');

                return redirect('/cp/dams?start=' . $start . '&limit=' . $limit . '&filter=' . $filter . '&search=' . $search . '&order_by=' . $order_by)
                    ->with('success', $msg);
            }
        }
    }

    private function recursiveRemoveDirectory($directory)
    {
        foreach (glob("{$directory}/*") as $file) {
            if (is_dir($file)) {
                $this->recursiveRemoveDirectory($file);
            } else {
                unlink($file);
            }
        }

        try {
            rmdir($directory);
        } catch (ErrorException $e) {
            Log::error($e->getMessage());
        }
    }

    public function postBulkDelete()
    {
        $delete_media_permission_data_with_flag = $this->roleService->hasPermission(
            $this->request->user()->uid,
            ModuleEnum::DAMS,
            PermissionType::ADMIN,
            DAMSPermission::DELETE_MEDIA,
            null,
            null,
            true
        );

        $keys = Input::get('ids');
        if (!$keys) {
            $msg = trans('admin/dams.missing_params');

            return redirect('/cp/dams/')
                ->with('error', $msg);
        }

        $warningMsg = '';
        $keys = explode(',', $keys);

        foreach ($keys as $value) {
            $asset = Dam::getDAMSAssetsUsingID($value);
            if (!empty($asset)) {
                $deleteflag = false;
                $asset = $asset[0];

                // Check if this media has any relation with other entities
                // $relation = Dam::getDAMSRelation($asset['_id']); // Needs rework
                if (isset($asset['relations']) && is_array($asset['relations']) && !empty($asset['relations'])) {
                    foreach ($asset['relations'] as $k => $v) {
                        if (is_array($v) && count($v)) {
                            $msg = trans('admin/dams.asset_delete_warning');
                            $warningMsg = $msg;
                            continue 2;
                        }
                    }
                }

                // If its link then delete it and return
                if ($asset['asset_type'] == 'link') {
                    Dam::deleteAsset($asset['_id']);
                } else {
                    // else detect the type and do the processing
                    switch ($asset['type']) {
                        case 'video':
                            // Check and delete temp file
                            if (isset($asset['temp_location']) && file_exists($asset['temp_location'])) {
                                unlink($asset['temp_location']);
                            }
                            // Check and delete srt file
                            if (isset($asset['srt_location']) && file_exists($asset['srt_location'])) {
                                unlink($asset['srt_location']);
                            }
                            // Check if the file exists in kaltura. if yes then delete that
                            if (isset($asset['kaltura_details']['id'])) {
                                Dam::deleteKalturaVideo($asset['kaltura_details']['id']);
                            }
                            // Check if the files exists in Akamai. If yes then delete that
                            if (isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == '200') {
                                Dam::deleteAkamaiVideo($asset);
                            }
                            $deleteflag = true;
                            // finally change the deleteflag = true
                            break;
                        case 'image':
                            if ($asset['visibility'] == 'public') {
                                if (file_exists($asset['public_file_location'])) {
                                    unlink($asset['public_file_location']);
                                }
                                if (isset($asset['thumb_img']) && is_array($asset['thumb_img'])) {
                                    foreach ($asset['thumb_img'] as $attribute) {
                                        if (file_exists($attribute)) {
                                            unlink($attribute);
                                        }
                                    }
                                }
                                $deleteflag = true;
                            } else {
                                if (file_exists($asset['private_file_location'])) {
                                    unlink($asset['private_file_location']);
                                }
                                if (isset($asset['thumb_img']) && is_array($asset['thumb_img'])) {
                                    foreach ($asset['thumb_img'] as $attribute) {
                                        if (file_exists($attribute)) {
                                            unlink($attribute);
                                        }
                                    }
                                }
                                $deleteflag = true;
                            }
                            break;
                        case 'document':
                        case 'audio':
                            if ($asset['visibility'] == 'public') {
                                if (file_exists($asset['public_file_location'])) {
                                    unlink($asset['public_file_location']);
                                }
                                $deleteflag = true;
                            } else {
                                if (file_exists($asset['private_file_location'])) {
                                    unlink($asset['private_file_location']);
                                }
                                $deleteflag = true;
                            }
                            break;
                        case 'scorm':
                            if ($asset['visibility'] == 'public') {
                                if (is_dir($asset['public_file_location'])) {
                                    $this->recursiveRemoveDirectory($asset['public_file_location']);
                                }
                                $deleteflag = true;
                            } else {
                                if (is_dir($asset['private_file_location'])) {
                                    $this->recursiveRemoveDirectory($asset['private_file_location']);
                                }
                                $deleteflag = true;
                            }
                            break;
                    }
                    if ($deleteflag) {
                        Dam::deleteAsset($asset['_id']);
                    }
                }
            }
        }
        if ($warningMsg) {
            return redirect('/cp/dams/')
                ->with('warning', $warningMsg);
        }
        $msg = trans('admin/dams.asset_deleted');

        return redirect('/cp/dams/')
            ->with('success', $msg);
    }

    public function getInitCron()
    {
        Log::info("dams cron called");
        ini_set('memory_limit', config('app.dams_max_upload_size')*1.2.'M');
        $filestoupload = Dam::getDAMSVideoAssetsUsingStatus('INTEMP');
        
        // ob_start();
        foreach ($filestoupload as $value) {
            echo '******************************************************<br/>';
            Dam::where('_id', '=', $value['_id'])->update(['video_status' => 'UPLOADING'], ['upsert' => true]);
            echo 'Upload initiated for ' . $value['_id'] . ' at ' . date('d-m-Y H:i:s') . '<br/>';
            $returndata = Dam::uploadToAkamai($value);
            Dam::where('_id', '=', $value['_id'])->update(['akamai_details' => $returndata], ['upsert' => true]);
            if (isset($returndata['code']) && $returndata['code'] == 200) {
                if (isset($value['transcoding']) && $value['transcoding'] == 'no') {
                    Dam::where('_id', '=', $value['_id'])->update(['video_status' => 'READY'], ['upsert' => true]);
                } else {
                    Dam::where('_id', '=', $value['_id'])->update(['video_status' => 'UPLOADED'], ['upsert' => true]);
                }
                echo 'Upload completed for ' . $value['_id'] . ' at ' . date('d-m-Y H:i:s') . '<br/>';
                echo 'Deleting temp file of ' . $value['_id'] . ' at ' . date('d-m-Y H:i:s') . '<br/>';
                unlink(base_path() . '/' . str_replace('../', '', $value['temp_location']));
                echo 'Temp file with id no ' . $value['_id'] . ' is deleted at ' . date('d-m-Y H:i:s') . '<br/>';
                Dam::where('_id', '=', $value['_id'])->update(['temp_deleted_status' => 'DELETED'], ['upsert' => true]);
                echo '******************************************************<br/></br/>';
            } else {
                echo 'Upload error for ' . $value['_id'] . ' at ' . date('d-m-Y H:i:s') . '<br/>';
                echo 'Reverting back the status to INTEMP for ' . $value['_id'] . ' at ' . date('d-m-Y H:i:s') . '<br/>';
                Dam::where('_id', '=', $value['_id'])->update(['video_status' => 'INTEMP'], ['upsert' => true]);
            }
            // flush(); //  Commented out coz laravel scheduler was not able to handle output buffers
            // ob_flush(); //  Commented out coz laravel scheduler was not able to handle output buffers
        }
        // ob_end_clean(); //  Commented out coz laravel scheduler was not able to handle output buffers
        if (empty($filestoupload)) {
            echo 'No files to upload';
        }
        exit;
    }

    public function anyResponse()
    {
        $input = Input::all();
        $data_to_write = json_encode($input);
        file_put_contents('./dams/temp.txt', $data_to_write); // TODO: Move to log
        if (!empty($input)) {
            if (isset($input['status']) && $input['status'] == 'completed' && isset($input['sourceFileName'])) {
                $akamai_config = config('app.akamai');
                Dam::where('unique_name_with_extension', '=', $input['sourceFileName'])->update(['akamai_details.response' => $input]);
                Dam::where('unique_name_with_extension', '=', $input['sourceFileName'])->update(['video_status' => 'READY']);

                if (isset($akamai_config['ftp_delivery_url']) && isset($input['deliveryBaseURL']) && $akamai_config['ftp_delivery_url']) {
                    if (isset($akamai_config['delivery_streaming_url_flash']) && $akamai_config['delivery_streaming_url_flash']) {
                        if (isset($input['derivatives']) && count($input['derivatives']) == 1) {
                            if (substr($input['deliveryBaseURL'], strrpos($input['deliveryBaseURL'], '/') + 1, 1) == ',') {
                                $flash_url = str_replace('<url>', $akamai_config['ftp_delivery_url'] . '/' . $input['deliveryBaseURL'] . '.csmil', $akamai_config['delivery_streaming_url_flash']);
                            } else {
                                $flash_url = str_replace('<url>', $akamai_config['ftp_delivery_url'] . '/' . $input['deliveryBaseURL'], $akamai_config['delivery_streaming_url_flash']);
                            }
                        } else {
                            $flash_url = str_replace('<url>', $akamai_config['ftp_delivery_url'] . '/' . $input['deliveryBaseURL'] . '.csmil', $akamai_config['delivery_streaming_url_flash']);
                        }
                        Dam::where('unique_name_with_extension', '=', $input['sourceFileName'])->update(['akamai_details.delivery_flash_url' => $flash_url]);
                    }
                    if (isset($akamai_config['delivery_streaming_url_html']) && $akamai_config['delivery_streaming_url_html']) {
                        if (isset($input['derivatives']) && count($input['derivatives']) == 1) {
                            if (substr($input['deliveryBaseURL'], strrpos($input['deliveryBaseURL'], '/') + 1, 1) == ',') {
                                $html_url = str_replace('<url>', $akamai_config['ftp_delivery_url'] . '/' . $input['deliveryBaseURL'] . '.csmil', $akamai_config['delivery_streaming_url_html']);
                            } else {
                                $html_url = str_replace('<url>', $akamai_config['ftp_delivery_url'] . '/' . $input['deliveryBaseURL'], $akamai_config['delivery_streaming_url_html']);
                            }
                        } else {
                            $html_url = str_replace('<url>', $akamai_config['ftp_delivery_url'] . '/' . $input['deliveryBaseURL'] . '.csmil', $akamai_config['delivery_streaming_url_html']);
                        }
                        Dam::where('unique_name_with_extension', '=', $input['sourceFileName'])->update(['akamai_details.delivery_html5_url' => $html_url]);
                    }
                }

                // Get the image for the video and save it
                if (isset($input['deliveryBaseURL']) && isset($akamai_config['ftp_image_loc']) && $akamai_config['ftp_image_loc'] && isset($akamai_config['video_thumbnail']) && $akamai_config['video_thumbnail'] == 'enabled') {
                    $akamai = new Akamai();
                    $url = $akamai_config['ftp_image_loc'] . '/' . substr($input['deliveryBaseURL'], 0, strrpos($input['deliveryBaseURL'], '/'));
                    $response = $akamai->dir($url);
                    $xml = simplexml_load_string($response); // TODO: Validate xml and then proceed
                    foreach ($xml->children() as $node) {
                        if ($node['type'] == 'file') {
                            $url = $url . '/' . $node['name'];
                            $filedata = $akamai->download($url);
                            Dam::where('unique_name_with_extension', '=', $input['sourceFileName'])->update(['akamai_details.delivery_image_url' => $url]);
                            file_put_contents(config('app.dams_video_thumb_path') . $input['foreignId'] . '.png', $filedata);
                        }
                    }
                }
            }
        }
        exit;
    }

    public function getRegenerateImage($force = false)
    {
        $videosstoprocess = Dam::where('type', '=', 'video')->where('asset_type', '=', 'file')->where('akamai_details.response.deliveryBaseURL', 'exists', true);
        if ($force) {
            $videosstoprocess = $videosstoprocess->where('akamai_details.delivery_image_url', 'exists', false);
        }
        $videosstoprocess = $videosstoprocess->get()->toArray();
        $akamai = new Akamai();
        $akamai_config = config('app.akamai');
        foreach ($videosstoprocess as $video) {
            if (isset($video['akamai_details']['response']['deliveryBaseURL']) && isset($akamai_config['ftp_image_loc']) && $akamai_config['ftp_image_loc'] && isset($akamai_config['video_thumbnail']) && $akamai_config['video_thumbnail'] == 'enabled') {
                if ($force || !file_exists(config('app.dams_video_thumb_path') . $video['akamai_details']['response']['foreignId'] . '.png')) {
                    $url = $akamai_config['ftp_image_loc'] . '/' . substr($video['akamai_details']['response']['deliveryBaseURL'], 0, strrpos($video['akamai_details']['response']['deliveryBaseURL'], '/'));
                    $response = $akamai->dir($url);
                    $xml = simplexml_load_string($response); // TODO: Validate xml and then proceed
                    foreach ($xml->children() as $node) {
                        if ($node['type'] == 'file') {
                            $url = $url . '/' . $node['name'];
                            $filedata = $akamai->download($url);
                            Dam::where('unique_name_with_extension', '=', $video['akamai_details']['response']['sourceFileName'])->update(['akamai_details.delivery_image_url' => $url]);
                            file_put_contents(config('app.dams_video_thumb_path') . $video['akamai_details']['response']['foreignId'] . '.png', $filedata);
                        }
                    }
                }
            }
        }
        //TODO: Satish: Log information here if needed.
    }

    public function anyCreateFolders()
    {

        // Private folders
        $oldmask = umask(0);
        if (!file_exists('../dams')) {
            if (is_writable('../')) {
                mkdir('../dams', 0777);
            } else {
                umask($oldmask);
                die('Parent Directory is not writable<br />');
            }
        }

        if (file_exists('../dams')) {
            if (is_writable('../dams')) {
                if (!file_exists('../dams/audio')) {
                    mkdir('../dams/audio', 0777);
                }
                if (!file_exists('../dams/bulkimport')) {
                    mkdir('../dams/bulkimport', 0777);
                }
                if (!file_exists('../dams/documents')) {
                    mkdir('../dams/documents', 0777);
                }
                if (!file_exists('../dams/images')) {
                    mkdir('../dams/images', 0777);
                }
                if (!file_exists('../dams/videosrt')) {
                    mkdir('../dams/videosrt', 0777);
                }
                if (!file_exists('../dams/videotemp')) {
                    mkdir('../dams/videotemp', 0777);
                }
                if (!file_exists('../dams/videothumb')) {
                    mkdir('../dams/videothumb', 0777);
                }
            } else {
                umask($oldmask);
                die('Private DAMS directory is not writable<br />');
            }
        }

        // Public folders

        if (!file_exists('./dams')) {
            if (is_writable('./')) {
                mkdir('./dams', 0777);
            } else {
                umask($oldmask);
                die('Public folder is not writable<br />');
            }
        }
        if (file_exists('./dams')) {
            if (is_writable('./dams')) {
                if (!file_exists('./dams/audio')) {
                    mkdir('./dams/audio', 0777);
                }
                if (!file_exists('./dams/documents')) {
                    mkdir('./dams/documents', 0777);
                }
                if (!file_exists('./dams/images')) {
                    mkdir('./dams/images', 0777);
                }
            } else {
                umask($oldmask);
                die('Public DAMS directory is not writable<br />');
            }
        }

        echo 'Folders created successfully';

        exit;
    }

    public function getBulkImport()
    {
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/dams.manage_assets') => 'dams',
            trans('admin/dams.import_media_assets') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pagetitle = trans('admin/dams.import_media_assets');
        $this->layout->pageicon = 'fa fa-video-camera';
        $this->layout->pagedescription = trans('admin/dams.add_new_assets');
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'dams');
        $this->layout->content = view('admin.theme.dams.bulkimportmedia');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postBulkImport()
    {
        $rules = [
            'xlsfile' => 'Required|allowexcel',
            'zipfile' => 'Required|mimes:zip|max:1048576',
        ];
        $niceNames = [
            'xlsfile' => 'Import File',
            'zipfile' => 'Zip File',
        ];
        Validator::extend('allowexcel', function ($attribute, $value, $parameters) {
            $mime = $value->getMimeType();
            if (in_array($mime, ['application/vnd.oasis.opendocument.text', 'application/vnd.ms-excel', 'application/zip', 'application/vnd.ms-office'])) {
                return true;
            }

            return false;
        });
        $validator = Validator::make(Input::all(), $rules);
        $validator->setAttributeNames($niceNames);
        if ($validator->fails()) {
            return redirect('cp/dams/bulk-import')->withInput()
                ->withErrors($validator);
        } else {
            $random_filename = strtolower(str_random(32));
            while (true) {
                $result = Dam::getDAMSBulkImport($random_filename);
                if (empty($result) || $result->isEmpty()) {
                    break;
                } else {
                    $random_filename = strtolower(str_random(32));
                }
            }
            $xlsfile = Input::file('xlsfile');
            $zipfile = Input::file('zipfile');
            $dams_bulkimport_path = Config::get('app.dams_bulkimport_path');
            $insertarr = [
                'unique_name' => $random_filename,
                'excel_unique_name_with_extension' => $random_filename . '.' . $xlsfile->getClientOriginalExtension(),
                'zip_unique_name_with_extension' => $random_filename . '.' . $zipfile->getClientOriginalExtension(),
                'excel_file_client_name' => $xlsfile->getClientOriginalName(),
                'zip_file_client_name' => $zipfile->getClientOriginalName(),
                'excel_file_size' => $xlsfile->getSize(),
                'zip_file_size' => $zipfile->getSize(),
                'excel_file_extension' => $xlsfile->getClientOriginalExtension(),
                'zip_file_extension' => $zipfile->getClientOriginalExtension(),
                'excel_mimetype' => $xlsfile->getMimeType(),
                'zip_mimetype' => $zipfile->getMimeType(),
                'excel_file_location' => $dams_bulkimport_path . $random_filename . '.' . $xlsfile->getClientOriginalExtension(),
                'zip_file_location' => $dams_bulkimport_path . $random_filename . '.' . $zipfile->getClientOriginalExtension(),
                'status' => 'ACTIVE',
                'created_at' => time(),
                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
                'created_by_username' => Auth::user()->username,
            ];
            $xlsfile->move($dams_bulkimport_path, $random_filename . '.' . $xlsfile->getClientOriginalExtension());
            $returndata = $this->checkImportErrors($dams_bulkimport_path . $random_filename . '.' . $xlsfile->getClientOriginalExtension());
            if ($returndata != false) {
                unlink($dams_bulkimport_path . $random_filename . '.' . $xlsfile->getClientOriginalExtension());
                Session::put('xlsreport', $returndata);
                Session::flash('errorflag', 'dummytext');

                return redirect('cp/dams/bulk-import');
            } else {
                $zipfile->move($dams_bulkimport_path, $random_filename . '.' . $zipfile->getClientOriginalExtension());
                Dam::getDAMSBulkImportInsert($insertarr);

                return redirect('cp/dams/')
                    ->with('success', trans('admin/dams.bulk_import_success'));
            }
        }
    }

    public function getBulkImportCron()
    {
        $bulkimport = Dam::getDAMSBulkImportRecords('ACTIVE');
        foreach ($bulkimport as $key => $record) {
            $objPHPExcel = PHPExcel_IOFactory::load($record['excel_file_location']);
            // Code to unzip the file to a temp directory
            $zip = new ZipArchive();
            $res = $zip->open($record['zip_file_location']);
            $dams_bulkimport_path = Config::get('app.dams_bulkimport_path');
            File::makeDirectory($dams_bulkimport_path . $record['unique_name'], $mode = 0777, true, true);
            if ($res === true) {
                $zip->extractTo($dams_bulkimport_path . $record['unique_name']);
                $zip->close();
            }

            $sheet = $objPHPExcel->getActiveSheet();
            $rows = $sheet->getHighestRow();
            $allRowData = [];
            for ($i = 1; $i <= $rows; ++$i) {
                $rowData = $sheet->rangeToArray('A' . $i . ':J' . $i, null, true, false);
                $allRowData = array_merge($allRowData, $rowData);
                if (!empty($rowData) && $i > 1) {
                    $rowData = $rowData[0];
                    $keys = ['filename', 'mediatitle', 'mediatype', 'assettype', 'description', 'shortdescription', 'visibility', 'keywords', 'linkdata', 'srtfilename'];
                    $rules = [
                        'mediatitle' => 'Required',
                        'mediatype' => 'Required|in:audio,video,document,image',
                        'assettype' => 'Required|in:file,link',
                        'description' => 'Required',
                        'visibility' => 'Required|in:public,private',
                        'shortdescription' => 'Required',
                    ];
                    $rowData = array_combine($keys, $rowData);
                    $links = '';
                    if ($rowData['mediatype'] && $rowData['assettype']) {
                        switch ($rowData['mediatype']) {
                            case 'image':
                                switch ($rowData['assettype']) {
                                    case 'file':
                                        $rules['filename'] = 'Required';
                                        if ($this->customValidate($rowData, $rules) && file_exists($dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'])) {
                                            $fileLocation = $dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'];
                                            $fileObj = new SymfonyFile($fileLocation);
                                            $extn = File::extension($fileLocation);
                                            if (in_array($extn, config('app.dams_image_extensions'))) {
                                                $keyword = $rowData['keywords'];
                                                $random_filename = strtolower(str_random(32));
                                                while (true) {
                                                    $result = Dam::getDAMSAsset($random_filename);
                                                    if ($result->isEmpty()) {
                                                        break;
                                                    } else {
                                                        $random_filename = strtolower(str_random(32));
                                                    }
                                                }
                                                $image_sizes = Config::get('app.thumb_resolutions');
                                                $private_dams_images_path = Config::get('app.private_dams_images_path');
                                                $public_dams_images_path = Config::get('app.public_dams_images_path');
                                                // $dams_documents_path = Config::get('app.dams_documents_path');
                                                $visibility = $rowData['visibility'];
                                                if ($visibility != 'public') {
                                                    $visibility = 'private';
                                                }
                                                // $filesize = filesize($dams_bulkimport_path.$record['unique_name'].'/'.$rowData['filename']);
                                                // $filesize = $filesize / 1024;
                                                // $filesize = round($filesize);
                                                $filesize = File::size($fileLocation);
                                                $insertarr = [
                                                    'id' => Dam::uniqueDAMSId(),
                                                    'name' => $rowData['mediatitle'],
                                                    'description' => $rowData['description'],
                                                    'short_description' => $rowData['shortdescription'],
                                                    'type' => 'image',
                                                    'asset_type' => 'file',
                                                    'unique_name' => $random_filename,
                                                    'unique_name_with_extension' => $random_filename . '.' . $extn,
                                                    'visibility' => $visibility,
                                                    'file_client_name' => $rowData['filename'],
                                                    'id3_info' => '',
                                                    'file_size' => $filesize,
                                                    'file_extension' => $extn,
                                                    'mimetype' => $fileObj->getMimeType(),
                                                    'tags' => explode(',', $keyword),
                                                    'status' => 'ACTIVE',
                                                    'created_at' => time(),
                                                    'created_by_name' => $record['created_by_name'],
                                                    'created_by_username' => $record['created_by_username'],
                                                ];
                                                
                                                if ($visibility == 'public') {
                                                    $insertarr['public_file_location'] = $public_dams_images_path . $random_filename . '.' . $extn;
                                                    // $file->move($public_dams_images_path,$insertarr['public_file_location']);
                                                    File::move($fileLocation, $insertarr['public_file_location']);
                                                    // $insertarr['id3_info'] = $getID3->analyze($insertarr['public_file_location']);
                                                } else {
                                                    $insertarr['private_file_location'] = $private_dams_images_path . $random_filename . '.' . $extn;
                                                    File::move($fileLocation, $insertarr['private_file_location']);
                                                    // $insertarr['id3_info'] = $getID3->analyze($insertarr['private_file_location']);
                                                }
                                                foreach ($image_sizes as $value) {
                                                    $res = explode('x', $value);
                                                    if (is_array($res)) {
                                                        // Have to skip resizing (and copy the original file) if the given res is less than the original image.
                                                        $loc = null;
                                                        if ($visibility == 'public') {
                                                            $image_obj = new Imagick($insertarr['public_file_location']);
                                                            $loc = $public_dams_images_path . $random_filename . '_' . $value . '.' . $extn;
                                                        } else {
                                                            $image_obj = new Imagick($insertarr['private_file_location']);
                                                            $loc = $private_dams_images_path . $random_filename . '_' . $value . '.' . $extn;
                                                        }
                                                        if (isset($res[0]) && isset($res[1])) {
                                                            $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                                                            $image_obj->writeImage($loc);
                                                            $insertarr['thumb_img'][$value] = $loc;
                                                        }
                                                    }
                                                }
                                                Dam::insert($insertarr);
                                            } else {
                                                $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                            }
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }
                                        break;
                                    case 'link':
                                        $rules['linkdata'] = 'Required|url';
                                        if ($this->customValidate($rowData, $rules)) {
                                            $links = 'image';
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }
                                        break;
                                }
                                break;
                            case 'audio':
                                switch ($rowData['assettype']) {
                                    case 'file':
                                        $rules['filename'] = 'Required';
                                        if ($this->customValidate($rowData, $rules) && file_exists($dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'])) {
                                            $fileLocation = $dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'];
                                            $fileObj = new SymfonyFile($fileLocation);
                                            $extn = File::extension($fileLocation);
                                            if (in_array($extn, config('app.dams_audio_extensions'))) {
                                                $keyword = $rowData['keywords'];
                                                $random_filename = strtolower(str_random(32));
                                                while (true) {
                                                    $result = Dam::getDAMSAsset($random_filename);
                                                    if ($result->isEmpty()) {
                                                        break;
                                                    } else {
                                                        $random_filename = strtolower(str_random(32));
                                                    }
                                                }
                                                $private_dams_audio_path = Config::get('app.private_dams_audio_path');
                                                $public_dams_audio_path = Config::get('app.public_dams_audio_path');
                                                // $dams_documents_path = Config::get('app.dams_documents_path');
                                                $visibility = $rowData['visibility'];
                                                if ($visibility != 'public') {
                                                    $visibility = 'private';
                                                }
                                                $filesize = File::size($fileLocation);
                                                $insertarr = [
                                                    'id' => Dam::uniqueDAMSId(),
                                                    'name' => $rowData['mediatitle'],
                                                    'description' => $rowData['description'],
                                                    'short_description' => $rowData['shortdescription'],
                                                    'type' => 'audio',
                                                    'asset_type' => 'file',
                                                    'unique_name' => $random_filename,
                                                    'unique_name_with_extension' => $random_filename . '.' . File::extension($fileLocation),
                                                    'visibility' => $visibility,
                                                    'file_client_name' => $rowData['filename'],
                                                    'id3_info' => '',
                                                    'file_size' => $filesize,
                                                    'file_extension' => File::extension($fileLocation),
                                                    'mimetype' => $fileObj->getMimeType(),
                                                    'tags' => explode(',', $keyword),
                                                    'status' => 'ACTIVE',
                                                    'created_at' => time(),
                                                    'created_by_name' => $record['created_by_name'],
                                                    'created_by_username' => $record['created_by_username'],
                                                ];
                                                
                                                if ($visibility == 'public') {
                                                    $insertarr['public_file_location'] = $public_dams_audio_path . $random_filename . '.' . $extn;
                                                    File::move($fileLocation, $insertarr['public_file_location']);
                                                    // $insertarr['id3_info'] = $getID3->analyze($insertarr['public_file_location']);
                                                } else {
                                                    $insertarr['private_file_location'] = $private_dams_audio_path . $random_filename . '.' . $extn;
                                                    File::move($fileLocation, $insertarr['private_file_location']);
                                                    // $insertarr['id3_info'] = $getID3->analyze($insertarr['private_file_location']);
                                                }
                                                Dam::insert($insertarr);
                                            } else {
                                                $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                            }
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }
                                        break;
                                    case 'link':
                                        $rules['linkdata'] = 'Required|url';
                                        if ($this->customValidate($rowData, $rules)) {
                                            $links = 'audio';
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }
                                        break;
                                }
                                break;
                            case 'document':
                                switch ($rowData['assettype']) {
                                    case 'file':
                                        $rules['filename'] = 'Required';
                                        if ($this->customValidate($rowData, $rules) && file_exists($dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'])) {
                                            $fileLocation = $dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'];
                                            $fileObj = new SymfonyFile($fileLocation);
                                            $extn = File::extension($fileLocation);
                                            if (in_array($extn, ['doc', 'docx', 'xls', 'xlsx', 'pdf', 'ppt', 'pptx', 'txt', 'rtf'])) {
                                                $keyword = $rowData['keywords'];
                                                $random_filename = strtolower(str_random(32));
                                                while (true) {
                                                    $result = Dam::getDAMSAsset($random_filename);
                                                    if ($result->isEmpty()) {
                                                        break;
                                                    } else {
                                                        $random_filename = strtolower(str_random(32));
                                                    }
                                                }
                                                $private_dams_document_path = Config::get('app.private_dams_documents_path');
                                                $public_dams_document_path = Config::get('app.public_dams_documents_path');
                                                // $dams_documents_path = Config::get('app.dams_documents_path');
                                                $visibility = $rowData['visibility'];
                                                if ($visibility != 'public') {
                                                    $visibility = 'private';
                                                }
                                                $filesize = File::size($fileLocation);
                                                $insertarr = [
                                                    'id' => Dam::uniqueDAMSId(),
                                                    'name' => $rowData['mediatitle'],
                                                    'description' => $rowData['description'],
                                                    'short_description' => $rowData['shortdescription'],
                                                    'type' => 'document',
                                                    'asset_type' => 'file',
                                                    'unique_name' => $random_filename,
                                                    'unique_name_with_extension' => $random_filename . '.' . File::extension($fileLocation),
                                                    'visibility' => $visibility,
                                                    'file_client_name' => $rowData['filename'],
                                                    'id3_info' => '',
                                                    'file_size' => $filesize,
                                                    'file_extension' => File::extension($fileLocation),
                                                    'mimetype' => $fileObj->getMimeType(),
                                                    'tags' => explode(',', $keyword),
                                                    'status' => 'ACTIVE',
                                                    'created_at' => time(),
                                                    'created_by_name' => $record['created_by_name'],
                                                    'created_by_username' => $record['created_by_username'],
                                                ];
                                                
                                                if ($visibility == 'public') {
                                                    $insertarr['public_file_location'] = $public_dams_document_path . $random_filename . '.' . File::extension($fileLocation);
                                                    File::move($fileLocation, $insertarr['public_file_location']);
                                                    // $insertarr['id3_info'] = $getID3->analyze($insertarr['public_file_location']);
                                                } else {
                                                    $insertarr['private_file_location'] = $private_dams_document_path . $random_filename . '.' . File::extension($fileLocation);
                                                    File::move($fileLocation, $insertarr['private_file_location']);
                                                    // $insertarr['id3_info'] = $getID3->analyze($insertarr['private_file_location']);
                                                }
                                                Dam::insert($insertarr);
                                            } else {
                                                $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                            }
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }
                                        break;
                                    case 'link':
                                        $rules['linkdata'] = 'Required|url';
                                        if ($this->customValidate($rowData, $rules)) {
                                            $links = 'document';
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }
                                        break;
                                }
                                break;
                            case 'video':
                                switch ($rowData['assettype']) {
                                    case 'file':
                                        $rules['filename'] = 'Required';
                                        if ($this->customValidate($rowData, $rules) && file_exists($dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'])) {
                                            $fileLocation = $dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['filename'];
                                            $fileObj = new SymfonyFile($fileLocation);
                                            $extn = File::extension($fileLocation);
                                            if (in_array($extn, config('app.dams_video_extensions'))) {
                                                $keyword = $rowData['keywords'];
                                                $random_filename = strtolower(str_random(32));
                                                while (true) {
                                                    $result = Dam::getDAMSAsset($random_filename);
                                                    if ($result->isEmpty()) {
                                                        break;
                                                    } else {
                                                        $random_filename = strtolower(str_random(32));
                                                    }
                                                }
                                                $dams_temp_video_path = Config::get('app.dams_temp_video_path');
                                                $dams_srt_path = Config::get('app.dams_srt_path');
                                                $visibility = $rowData['visibility'];

                                                if ($visibility != 'public') {
                                                    $visibility = 'private';
                                                }
                                                $filesize = File::size($fileLocation);
                                                $transcoding = 'no';
                                                if (config('app.dams_media_library_transcoding')) {
                                                    $transcoding = 'yes';
                                                }
                                                $insertarr = [
                                                    'id' => Dam::uniqueDAMSId(),
                                                    'name' => $rowData['mediatitle'],
                                                    'description' => $rowData['description'],
                                                    'short_description' => $rowData['shortdescription'],
                                                    'type' => 'video',
                                                    'asset_type' => 'file',
                                                    'unique_name' => $random_filename,
                                                    'unique_name_with_extension' => $random_filename . '.' . File::extension($fileLocation),
                                                    'visibility' => $visibility,
                                                    'file_client_name' => $rowData['filename'],
                                                    'id3_info' => '',
                                                    'file_size' => $filesize,
                                                    'file_extension' => File::extension($fileLocation),
                                                    'mimetype' => $fileObj->getMimeType(),
                                                    'tags' => explode(',', $keyword),
                                                    'transcoding' => $transcoding,
                                                    'status' => 'ACTIVE',
                                                    'video_status' => 'INTEMP',
                                                    'created_at' => time(),
                                                    'created_by_name' => $record['created_by_name'],
                                                    'created_by_username' => $record['created_by_username'],
                                                ];
                                                $srtExtn = File::extension($fileLocation);
                                                if ($rowData['srtfilename'] && file_exists($dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['srtfilename']) && in_array($srtExtn, ['srt'])) {
                                                    $srtLocation = $dams_bulkimport_path . $record['unique_name'] . '/' . $rowData['srtfilename'];
                                                    $insertarr['srt_unique_name_with_extension'] = $random_filename . '.' . File::extension($srtLocation);
                                                    $insertarr['srt_location'] = $dams_srt_path . $random_filename . '.' . File::extension($srtLocation);
                                                    $insertarr['srt_status'] = 'ADDED';
                                                    $insertarr['srt_client_name'] = $rowData['srtfilename'];
                                                    File::move($srtLocation, $insertarr['srt_location']);
                                                }
                                                $insertarr['temp_location'] = $dams_temp_video_path . $random_filename . '.' . $fileObj->getClientOriginalExtension();
                                                
                                                File::move($fileLocation, $insertarr['temp_location']);
                                                Dam::insert($insertarr);
                                            } else {
                                                $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                            }
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }

                                        break;
                                    case 'link':
                                        $rules['linkdata'] = 'Required|url';
                                        if ($this->customValidate($rowData, $rules)) {
                                            $links = 'video';
                                        } else {
                                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                                        }
                                        break;
                                }
                                break;
                        }
                    } else {
                        $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                    }
                    if ($links && in_array($links, ['image', 'document', 'video', 'audio'])) {
                        if ($this->customValidate($rowData, $rules)) {
                            $visibility = $rowData['visibility'];
                            $keyword = $rowData['keywords'];
                            if ($visibility != 'public') {
                                $visibility = 'private';
                            }
                            $insertarr = [
                                'id' => Dam::uniqueDAMSId(),
                                'name' => $rowData['mediatitle'],
                                'description' => $rowData['description'],
                                'short_description' => $rowData['shortdescription'],
                                'type' => $links,
                                'asset_type' => 'link',
                                'url' => $record['created_by_username'],
                                'visibility' => $visibility,
                                'tags' => explode(',', $keyword),
                                'status' => 'ACTIVE',
                                'created_at' => time(),
                                'created_by_name' => $record['created_by_name'],
                                'created_by_username' => $record['created_by_username'],
                            ];
                            Dam::insert($insertarr);
                        }
                    }
                }
            }
            Dam::updateDAMSBulkImportExcelData($record['unique_name'], $allRowData);
            Dam::getDAMSBulkImportStatusUpdate($record['unique_name']);
            // Delete the zip, excel and finally delete the extracted folder
            if (file_exists($record['excel_file_location'])) {
                unlink($record['excel_file_location']);
            }
            if (file_exists($record['zip_file_location'])) {
                unlink($record['zip_file_location']);
            }

            if (is_dir($dams_bulkimport_path . $record['unique_name'])) {
                $dir = $dams_bulkimport_path . $record['unique_name'];
                $it = new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS);
                $files = new RecursiveIteratorIterator($it, RecursiveIteratorIterator::CHILD_FIRST);
                foreach ($files as $file) {
                    if ($file->isDir()) {
                        rmdir($file->getRealPath());
                    } else {
                        unlink($file->getRealPath());
                    }
                }
                rmdir($dir);
            }
        }
        exit;
    }

    public function customValidate($input, $rules, $messages = [])
    {
        $validation = Validator::make($input, $rules, $messages);
        if ($validation->fails()) {
            return false;
        }

        return true;
    }

    /*
        This function returns true or and array of data if there is an error
    */
    public function checkImportErrors($file = null)
    {
        if ($file) {
            $objPHPExcel = PHPExcel_IOFactory::load($file);
            $dams_bulkimport_path = Config::get('app.dams_bulkimport_path');
            $sheet = $objPHPExcel->getActiveSheet();
            $rows = $sheet->getHighestRow();
            $errorFlag = 0;
            $allRowData = [];
            for ($i = 1; $i <= $rows; ++$i) {
                $rowData = $sheet->rangeToArray('A' . $i . ':J' . $i, null, true, false);
                $allRowData = array_merge($allRowData, $rowData);
                if (!empty($rowData) && $i > 1) {
                    $rowData = $rowData[0];
                    $keys = ['filename', 'mediatitle', 'mediatype', 'assettype', 'description', 'shortdescription', 'visibility', 'keywords', 'linkdata', 'srtfilename'];
                    $rules = [
                        'mediatitle' => 'Required',
                        'mediatype' => 'Required|in:audio,video,document,image',
                        'assettype' => 'Required|in:file,link',
                        'description' => 'Required',
                        'visibility' => 'Required|in:public,private',
                        'shortdescription' => 'Required',
                    ];
                    $rowData = array_combine($keys, $rowData);
                    if ($this->customValidate($rowData, $rules)) {
                        if ($rowData['assettype'] == 'link' && !$rowData['linkdata']) {
                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                            $errorFlag = 1;
                        }
                        if ($rowData['assettype'] == 'file' && !$rowData['filename']) {
                            $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                            $errorFlag = 1;
                        }
                    } else {
                        $allRowData[$i - 1]['validation'] = 'Invalid Data Detected';
                        $errorFlag = 1;
                    }
                }
            }
            if ($errorFlag) {
                return $allRowData;
            } else {
                return false;
            } //  No errors
        }

        return true;  // Default Error
    }

    public function getBulkImportErrorReport()
    {
        $xlsreport = Session::get('xlsreport');
        if ($xlsreport) {
            $excelObj = new PHPExcel();
            $excelObj->setActiveSheetIndex(0);
            $excelObj->getActiveSheet()->setTitle('Excel upload report');
            $excelObj->getActiveSheet()->fromArray($xlsreport, null, 'A1');
            $filename = 'Error Report.xlsx'; //save our workbook as this file name
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet'); //mime type
            header('Content-Disposition: attachment;filename="' . $filename . '"'); //tell browser what's the file name
            header('Cache-Control: max-age=0'); //no cache
            //save it to Excel5 format (excel 2003 .XLS file), change this to 'Excel2007' (and adjust the filename extension, also the header mime type)
            //if you want to save it as .XLSX Excel 2007 format
            $objWriter = PHPExcel_IOFactory::createWriter($excelObj, 'Excel2007');
            //force user to download the Excel file without writing it to server's HD
            $objWriter->save('php://output');
        }
        exit;
    }

    /*
        Bulk Import Cron : This cron reads through the csv files and imports media files.
    */
    public function getCron()
    {
        ini_set('max_execution_time', 1800);
        $this->processBulkImport();
    }

    private function processBulkImport()
    {
        $csv_path = config('app.bulk_upload.csv_path');
        $files_path = config('app.bulk_upload.files_path');
        $files = scandir($csv_path);
        $files = array_diff($files, ['.', '..']); // Remove current and parent directories from the list
        $successflag = 0;
        foreach ($files as $file) {
            if (is_file($csv_path . $file) && strlen($file) > 0 && $file[0] != '.') {
                $successflag = 1;
                $reader = CsvReader::createFromPath($csv_path . $file);
                $data = $reader->fetchAll();
                if (count($data) <= 1) {
                    continue;
                }
                $header = $data[0]; // Taking a backup of headers
                $processed_data = [];
                unset($data[0]); // Unsetting the header data before processing
                $data = array_values($data);
                foreach ($data as $row) {
                    if (trim(implode($row)) != '') {  // To make sure that a row has atleast some values.
                        $processed_data[] = $this->processMedia($row);
                    }
                }
                $csv_processed_path = config('app.bulk_upload.csv_processed_path');
                $writer = CsvWriter::createFromPath(new SplFileObject($csv_processed_path . $file, 'a+'), 'w');
                $keys = $this->getMediaKeys();
                $header[count($keys)] = 'Messages will come here (If any)';
                $writer->insertOne($header);
                $writer->insertAll($processed_data);
                $this->saveToDatabase($processed_data, $file, $csv_path . $file);
                // Delete the csv file after processing
                unlink($csv_path . $file);
                //TODO: Satish: Please log information here.
            }
        }
        if (empty($files) || !$successflag) {
            echo 'No Files to Process';
        }
        exit;
    }

    private function processMedia($rowData = [])
    {
        if (!empty($rowData)) {
            $rowData = $this->prepareRowData($rowData);
            $validator = $this->validateMaster($rowData);
            if ($validator['flag'] == 'success') {
                $rowData['message'] = 'Success';
                if ($rowData['filevariant'] == 'link') {
                    $this->processLink($rowData);
                } else {
                    $type = 'process' . $rowData['filetype'];
                    $this->$type($rowData);
                }
            } else {
                $rowData['message'] = $validator['message'];
            }
        }

        return $rowData;
    }

    private function prepareRowData($rowData = [])
    {
        if (!empty($rowData)) {
            $keys = $this->getMediaKeys();
            $rowData = array_slice($rowData, 0, count($keys), true);
            $rowData = array_combine($keys, $rowData);
        }

        return $rowData;
    }

    private function getMediaKeys()
    {
        $keys = [
            'filename',
            'filetitle',
            'filetype',
            'filevariant',
            'description',
            'visibility',
            'keywords',
            'link',
            'transcoding',
            'srtfile',
        ];

        return $keys;
    }

    private function validateMaster($rowData)
    {
        $validator = ['flag' => 'error', 'message' => 'Empty row data'];
        if (!empty($rowData)) {
            $rules = $this->getRules($rowData['filetype'], $rowData['filevariant']);
            $fileLocation = config('app.bulk_upload.files_path') . $rowData['filename'];
            if ($rowData['filevariant'] == 'file' && !is_file($fileLocation)) {
                return ['flag' => 'error', 'message' => '404 - File not found in location : ' . $fileLocation];
            }
            if ($rowData['filevariant'] == 'file') {
                $rowData['file'] = new SymfonyFile($fileLocation);
            }
            $validator = $this->bulkValidate($rowData, $rules);
            if ($validator === true) {
                $validator = ['flag' => 'success'];
            } else {
                $validator = ['flag' => 'error', 'message' => implode(' , ', $validator)];
            }
        }

        return $validator;
    }

    private function getRules($condition, $variant = 'file')
    {
        $rules = [
            'filename' => 'Required',
            'filetitle' => 'Required',
            'filetype' => 'Required|in:image,video,document,audio',
            'filevariant' => 'Required|in:file',
            // "description" => "Required",
            // "shortdescription" => "Required",
            'visibility' => 'Required|in:private,public',
        ];
        if ($variant == 'link') {
            $rules['filevariant'] = 'Required|in:link';
            $rules['link'] = 'Required|URL';
            unset($rules['filename']);
        }
        switch ($condition) {
            case 'video':
                $rules['filetype'] = 'Required|in:video';
                if ($variant == 'file') {
                    $rules['file'] = 'Required|mimes:'.implode(',', config('app.dams_video_extensions')).'|max:' . config('app.dams_max_upload_size')*1024;
                }
                break;
            case 'image':
                $rules['filetype'] = 'Required|in:image';
                if ($variant == 'file') {
                    $rules['file'] = 'Required|image|mimes:'.implode(',', config('app.dams_image_extensions')).'|max:' . config('app.dams_max_upload_size')*1024;
                }
                break;
            case 'audio':
                $rules['filetype'] = 'Required|in:audio';
                if ($variant == 'file') {
                    $rules['file'] = 'Required|max:'. config('app.dams_max_upload_size')*1024 . '|checkaudioextension|checkaudiomime';
                }
                break;
            case 'document':
                $rules['filetype'] = 'Required|in:document';
                if ($variant == 'file') {
                    $rules['file'] = 'Required|mimes:'.implode(',', config('app.dams_document_extensions')).'|max:' . config('app.dams_max_upload_size')*1024;
                }
                break;
        }

        return $rules;
    }

    private function bulkValidate($input, $rules, $messages = [])
    {
        // Custom Validation for Audio
        Validator::extend('checkaudioextension', function ($attribute, $value, $parameters) {
            $extension = $value->getExtension();
            if (in_array($extension, config('app.dams_audio_extensions'))) {
                return true;
            }

            return false;
        });
        Validator::extend('checkaudiomime', function ($attribute, $value, $parameters) {
            $extension = $value->getMimeType();
            if (in_array($extension, config('app.dams_audio_mime_types'))) {
                return true;
            }

            return false;
        });
        $messages['checkaudioextension'] = trans('admin/dams.check_audio_extension');
        $messages['checkaudiomime'] = trans('admin/dams.check_audio_extension');

        $validation = Validator::make($input, $rules, $messages);
        if ($validation->fails()) {
            return $validation->messages()->all();
        }

        return true;
    }

    private function processLink($rowData)
    {
        if (in_array($rowData['filetype'], ['audio', 'document', 'video', 'image'])) {
            $visibility = $rowData['visibility'];
            $keyword = $rowData['keywords'];
            if ($visibility != 'public') {
                $visibility = 'private';
            }
            $insertarr = [
                'id' => Dam::uniqueDAMSId(),
                'name' => $rowData['filetitle'],
                'name_lower' => strtolower($rowData['filetitle']),
                'description' => $rowData['description'],
                'type' => $rowData['filetype'],
                'asset_type' => 'link',
                'url' => $rowData['link'],
                'visibility' => $visibility,
                'tags' => explode(',', $keyword),
                'status' => 'ACTIVE',
                'created_at' => time(),
                'created_by_name' => 'Admin',
                'created_by_username' => 'admin',
            ];
            Dam::insert($insertarr);
        }
    }

    /**
     * @SuppressWarnings("unused")
     * @param array $rowData
     */
    private function processImage($rowData)
    {
        $keyword = $rowData['keywords'];
        $random_filename = $this->getRandomMediaName();
        $image_sizes = Config::get('app.thumb_resolutions');
        $private_dams_images_path = Config::get('app.private_dams_images_path');
        $public_dams_images_path = Config::get('app.public_dams_images_path');
        // $dams_documents_path = Config::get('app.dams_documents_path');
        $visibility = $rowData['visibility'];
        if ($visibility != 'public') {
            $visibility = 'private';
        }
        $fileLocation = config('app.bulk_upload.files_path') . $rowData['filename'];
        $file = new SymfonyFile($fileLocation);
        $filesize = File::size($fileLocation);
        $insertarr = [
            'id' => Dam::uniqueDAMSId(),
            'name' => $rowData['filetitle'],
            'name_lower' => strtolower($rowData['filetitle']),
            'description' => $rowData['description'],
            'type' => 'image',
            'asset_type' => 'file',
            'unique_name' => $random_filename,
            'unique_name_with_extension' => $random_filename . '.' . $file->getExtension(),
            'visibility' => $visibility,
            'file_client_name' => $rowData['filename'],
            'id3_info' => '',
            'file_size' => $filesize,
            'file_extension' => $file->getExtension(),
            'mimetype' => $file->getMimeType(),
            'tags' => explode(',', $keyword),
            'status' => 'ACTIVE',
            'created_at' => time(),
            'created_by_name' => 'Admin',
            'created_by_username' => 'admin',
        ];
        if ($visibility == 'public') {
            $insertarr['public_file_location'] = $public_dams_images_path . $random_filename . '.' . $file->getExtension();
            $file->move($public_dams_images_path, $insertarr['public_file_location']);
        } else {
            $insertarr['private_file_location'] = $private_dams_images_path . $random_filename . '.' . $file->getExtension();
            $file->move($private_dams_images_path, $insertarr['private_file_location']);
        }
        foreach ($image_sizes as $value) {
            $res = explode('x', $value);
            if (is_array($res)) {
                // Have to skip resizing (and copy the original file) if the given res is less than the original image.
                $loc = null;
                if ($visibility == 'public') {
                    $image_obj = new Imagick($insertarr['public_file_location']);
                    $loc = $public_dams_images_path . $random_filename . '_' . $value . '.' . $file->getExtension();
                } else {
                    $image_obj = new Imagick($insertarr['private_file_location']);
                    $loc = $private_dams_images_path . $random_filename . '_' . $value . '.' . $file->getExtension();
                }
                if (isset($res[0]) && isset($res[1])) {
                    // $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                    // skips resizing (and copy the original file) if the given res is less than the original image.
                    if ($res[0] < $image_obj->getImageWidth() && $res[1] < $image_obj->getImageHeight()) {
                        $image_obj->resizeImage($res[0], $res[1], Imagick::FILTER_LANCZOS, 1, true);
                    }
                    $image_obj->writeImage($loc);
                    $insertarr['thumb_img'][$value] = $loc;
                }
            }
        }
        Dam::insert($insertarr);
        $insertedAsset = Dam::getDAMSAsset($random_filename);
    }

    /**
     * @SuppressWarnings("unused")
     * @param array $rowData
     */
    private function processVideo($rowData)
    {
        $keyword = $rowData['keywords'];
        $random_filename = $this->getRandomMediaName();
        $dams_temp_video_path = Config::get('app.dams_temp_video_path');

        $transcoding = $rowData['transcoding'];
        if (config('app.dams_media_library_transcoding') && $transcoding == 'yes') {
            $transcoding = 'yes';
        } else{
            $transcoding = 'no';
        }

        $visibility = $rowData['visibility'];
        if ($visibility != 'public') {
            $visibility = 'private';
        }
        $fileLocation = config('app.bulk_upload.files_path') . $rowData['filename'];
        $file = new SymfonyFile($fileLocation);
        $filesize = File::size($fileLocation);
        $insertarr = [
            'id' => Dam::uniqueDAMSId(),
            'name' => $rowData['filetitle'],
            'name_lower' => strtolower($rowData['filetitle']),
            'description' => $rowData['description'],
            'type' => 'video',
            'asset_type' => 'file',
            'unique_name' => $random_filename,
            'unique_name_with_extension' => $random_filename . '.' . $file->getExtension(),
            'visibility' => $visibility,
            'file_client_name' => $rowData['filename'],
            'id3_info' => '',
            'file_size' => $filesize,
            'file_extension' => $file->getExtension(),
            'mimetype' => $file->getMimeType(),
            'tags' => explode(',', $keyword),
            'transcoding' => $transcoding,
            'status' => 'ACTIVE',
            'video_status' => 'INTEMP',
            'created_at' => time(),
            'created_by_name' => 'Admin',
            'created_by_username' => 'admin',
        ];
        $file->move($dams_temp_video_path, $random_filename . '.' . $file->getExtension());
        $insertarr['temp_location'] = $dams_temp_video_path . $random_filename . '.' . $file->getExtension();
        $srtdata = $this->processSRT($rowData, $random_filename);
        $insertarr = array_merge($insertarr, $srtdata);
        Dam::insert($insertarr);
    }

    /**
     * @SuppressWarnings("unused")
     * @param array $rowData
     */
    private function processAudio($rowData)
    {
        $keyword = $rowData['keywords'];
        $random_filename = $this->getRandomMediaName();
        $private_dams_audio_path = Config::get('app.private_dams_audio_path');
        $public_dams_audio_path = Config::get('app.public_dams_audio_path');
        // $dams_documents_path = Config::get('app.dams_documents_path');
        $visibility = $rowData['visibility'];
        if ($visibility != 'public') {
            $visibility = 'private';
        }
        $fileLocation = config('app.bulk_upload.files_path') . $rowData['filename'];
        $file = new SymfonyFile($fileLocation);
        $filesize = File::size($fileLocation);
        $insertarr = [
            'id' => Dam::uniqueDAMSId(),
            'name' => $rowData['filetitle'],
            'name_lower' => strtolower($rowData['filetitle']),
            'description' => $rowData['description'],
            'type' => 'audio',
            'asset_type' => 'file',
            'unique_name' => $random_filename,
            'unique_name_with_extension' => $random_filename . '.' . $file->getExtension(),
            'visibility' => $visibility,
            'file_client_name' => $rowData['filename'],
            'id3_info' => '',
            'file_size' => $filesize,
            'file_extension' => $file->getExtension(),
            'mimetype' => $file->getMimeType(),
            'tags' => explode(',', $keyword),
            'status' => 'ACTIVE',
            'created_at' => time(),
            'created_by_name' => 'Admin',
            'created_by_username' => 'admin',
        ];
        if ($visibility == 'public') {
            $insertarr['public_file_location'] = $public_dams_audio_path . $random_filename . '.' . $file->getExtension();
            $file->move($public_dams_audio_path, $insertarr['public_file_location']);
        } else {
            $insertarr['private_file_location'] = $private_dams_audio_path . $random_filename . '.' . $file->getExtension();
            $file->move($private_dams_audio_path, $insertarr['private_file_location']);
        }
        Dam::insert($insertarr);
    }

    /**
     * @SuppressWarnings("unused")
     * @param array $rowData
     */
    private function processDocument($rowData)
    {
        $keyword = $rowData['keywords'];
        $random_filename = $this->getRandomMediaName();
        $private_dams_document_path = Config::get('app.private_dams_documents_path');
        $public_dams_document_path = Config::get('app.public_dams_documents_path');
        // $dams_documents_path = Config::get('app.dams_documents_path');
        $visibility = $rowData['visibility'];
        if ($visibility != 'public') {
            $visibility = 'private';
        }
        $fileLocation = config('app.bulk_upload.files_path') . $rowData['filename'];
        $file = new SymfonyFile($fileLocation);
        $filesize = File::size($fileLocation);
        $insertarr = [
            'id' => Dam::uniqueDAMSId(),
            'name' => $rowData['filetitle'],
            'name_lower' => strtolower($rowData['filetitle']),
            'description' => $rowData['description'],
            'type' => 'document',
            'asset_type' => 'file',
            'unique_name' => $random_filename,
            'unique_name_with_extension' => $random_filename . '.' . $file->getExtension(),
            'visibility' => $visibility,
            'file_client_name' => $rowData['filename'],
            'id3_info' => '',
            'file_size' => $filesize,
            'file_extension' => $file->getExtension(),
            'mimetype' => $file->getMimeType(),
            'tags' => explode(',', $keyword),
            'status' => 'ACTIVE',
            'created_at' => time(),
            'created_by_name' => 'Admin',
            'created_by_username' => 'admin',
        ];
        if ($visibility == 'public') {
            $insertarr['public_file_location'] = $public_dams_document_path . $random_filename . '.' . $file->getExtension();
            $file->move($public_dams_document_path, $insertarr['public_file_location']);
        } else {
            $insertarr['private_file_location'] = $private_dams_document_path . $random_filename . '.' . $file->getExtension();
            $file->move($private_dams_document_path, $insertarr['private_file_location']);
        }
        Dam::insert($insertarr);
    }

    private function processSRT($rowData, $random_filename)
    {
        $srtdata = [];
        $fileLocation = config('app.bulk_upload.files_path') . $rowData['srtfile'];
        if (isset($rowData['srtfile']) && is_file($fileLocation)) {
            if (strtolower(pathinfo($fileLocation, PATHINFO_EXTENSION)) == 'srt') {
                $dams_srt_path = Config::get('app.dams_srt_path');
                $srtfile = new SymfonyFile($fileLocation);
                $srtdata['srt_unique_name_with_extension'] = $random_filename . '.' . $srtfile->getExtension();
                $srtdata['srt_location'] = $dams_srt_path . $random_filename . '.' . $srtfile->getExtension();
                $srtdata['srt_status'] = 'ADDED';
                $srtdata['srt_client_name'] = $rowData['srtfile'];
                $srtfile->move($dams_srt_path, $random_filename . '.' . $srtfile->getExtension());
            }
        }

        return $srtdata;
    }

    private function getRandomMediaName()
    {
        $random_filename = strtolower(str_random(32));
        while (true) {
            $result = Dam::getDAMSAsset($random_filename);
            if ($result->isEmpty()) {
                break;
            } else {
                $random_filename = strtolower(str_random(32));
            }
        }

        return $random_filename;
    }

    private function saveToDatabase($info_to_log, $filename, $path)
    {
        $insertdata = [
            'filename' => $filename,
            'path' => $path,
            'records' => $info_to_log,
            'created_at' => time(),
            'updated_at' => time(),
        ];
        Dam::logInfo($insertdata);
    }

    /*Sandeep-to get media relation details, while deleting*/
    public function getMediaRelations(IQuestionService $question, $key)
    {
        $asset = Dam::getDAMSAssetsUsingID($key);

        if (empty($asset) || !$key) {
            return $msg = trans('admin/dams.missing_asset');
        }
        $deleteflag = false;
        $asset = $asset[0];

        $data = [];
        $start = (int)Input::get('start', 0);
        $limit = (int)Input::get('limit', 10);
        $search = Input::get('search', '');
        $order_by = Input::get('order_by', '1 desc');
        $filter = Input::get('filter', 'ACTIVE');
        $user_ids = $announcement_ids = $user_group_ids = $channel_media_ids = $post_media_item = $post_media_cover = $channel_ids = [];
        $rel_details = '';
        $rel_details .= '<b>' . $asset['name'] . '</b> is assigned to the following: ';
        // Have to check if the id is already related to modules. If yes then dont delete and return back with a message
        if (isset($asset['relations']) && is_array($asset['relations']) && !empty($asset['relations'])) {
            /*Gets directly assigned userIDs*/
            if (isset($asset['relations']['active_user_media_rel']) && is_array($asset['relations']['active_user_media_rel']) && !empty($asset['relations']['active_user_media_rel'])) {
                foreach ($asset['relations']['active_user_media_rel'] as $each) {
                    $user_ids[] = $each;
                }
            }

            /*Gets directly assigned inactive userIDs*/
            if (isset($asset['relations']['inactive_user_media_rel']) && is_array($asset['relations']['inactive_user_media_rel']) && !empty($asset['relations']['inactive_user_media_rel'])) {
                foreach ($asset['relations']['inactive_user_media_rel'] as $each) {
                    $user_ids[] = $each;
                }
            }
            /*Gets related user group Ids*/
            if (isset($asset['relations']['active_usergroup_media_rel']) && is_array($asset['relations']['active_usergroup_media_rel']) && !empty($asset['relations']['active_usergroup_media_rel'])) {
                foreach ($asset['relations']['active_usergroup_media_rel'] as $each_aug) {
                    $usergroup_ids[] = $each_aug;
                }
            }

            if (isset($asset['relations']['inactive_usergroup_media_rel']) && is_array($asset['relations']['inactive_usergroup_media_rel']) && !empty($asset['relations']['inactive_usergroup_media_rel'])) {
                foreach ($asset['relations']['inactive_usergroup_media_rel'] as $each_iaug) {
                    $usergroup_ids[] = $each_iaug;
                }
            }

            /*Users,Announcements,Channels related to media thro User groups */
            if (isset($usergroup_ids) && !empty($usergroup_ids)) {
                $user_details = UserGroup::getUserIDsUsingGroupIDs($usergroup_ids);
            }

            /*Gets related announcement Ids*/
            if (isset($asset['relations']['media_announcement_rel']) && is_array($asset['relations']['media_announcement_rel']) && !empty($asset['relations']['media_announcement_rel'])) {
                foreach ($asset['relations']['media_announcement_rel'] as $each) {
                    $announcement_ids[] = $each;
                }
            }

            if (isset($user_details['announcement_ids']) && !empty($user_details['announcement_ids']) && is_array($user_details['announcement_ids'])) {
                $announcement_ids = array_unique(array_merge($user_details['announcement_ids'], $announcement_ids));
            }

            if (isset($announcement_ids) && !empty($announcement_ids)) {
                $announcement_info = Announcement::getAnnouncementDetails($announcement_ids);
            }
            // echo "<pre>"; print_r($announcement_info); die;
            if (isset($announcement_info) && !empty($announcement_info)) {
                foreach ($announcement_info as $each_announce) {
                    if (isset($each_announce['relations']['active_user_announcement_rel']) && !empty($each_announce['relations']['active_user_announcement_rel']) && is_array($each_announce['relations']['active_user_announcement_rel'])) {
                        foreach ($each_announce['relations']['active_user_announcement_rel'] as $each_auid) {
                            $user_ids[] = $each_auid;
                        }
                    }

                    if (isset($each_announce['relations']['inactive_user_announcement_rel']) && !empty($each_announce['relations']['inactive_user_announcement_rel'])) {
                        foreach ($each_announce['relations']['inactive_user_announcement_rel'] as $each_iauid) {
                            $user_ids[] = $each_iauid;
                        }
                    }

                    if (isset($each_announce['relations']['active_contentfeed_announcement_rel']) && !empty($each_announce['relations']['active_contentfeed_announcement_rel'])) {
                        foreach ($each_announce['relations']['active_contentfeed_announcement_rel'] as $each_cid) {
                            $channel_ids[] = $each_cid;
                        }
                    }
                }
            }

            /*Gets related post Ids*/
            if (isset($asset['relations']['packet_banner_media_rel']) && is_array($asset['relations']['packet_banner_media_rel']) && !empty($asset['relations']['packet_banner_media_rel'])) {
                foreach ($asset['relations']['packet_banner_media_rel'] as $each) {
                    $packet_ids[] = $each;
                    $post_media_cover[] = $each;
                }
            }

            if (isset($asset['relations']['dams_packet_rel']) && is_array($asset['relations']['dams_packet_rel']) && !empty($asset['relations']['dams_packet_rel'])) {
                foreach ($asset['relations']['dams_packet_rel'] as $each) {
                    $packet_ids[] = $each;
                    $post_media_item[] = $each;
                }
            }
            if (isset($packet_ids) && !empty($packet_ids)) {
                $post_info = Packet::getPostDetailsUsingIds($packet_ids);
                $post_details = [];
                $post_ids = [];
                foreach ($post_info as $each_post) {
                    $post_details[$each_post['feed_slug']][] = $each_post['packet_title'];
                    $post_ids[$each_post['feed_slug']][] = $each_post['packet_id'];
                }
            }
            // echo "<pre>"; print_r($packet_ids); die;
            if (isset($post_info) && !empty($post_info)) {
                foreach ($post_info as $each_post) {
                    $channel_slugs[] = $each_post['feed_slug'];
                }
            }

            if (isset($channel_slugs) && !empty($channel_slugs) && is_array($channel_slugs)) {
                $chan_info = Program::getPrograms($channel_slugs);
            }

            if (isset($chan_info) && !empty($chan_info)) {
                foreach ($chan_info as $each_chan) {
                    $channel_ids[] = $each_chan['program_id'];
                }
            }

            /*Gets related channel Ids*/
            if (isset($asset['relations']['contentfeed_media_rel']) && is_array($asset['relations']['contentfeed_media_rel']) && !empty($asset['relations']['contentfeed_media_rel'])) {
                foreach ($asset['relations']['contentfeed_media_rel'] as $each) {
                    $channel_ids[] = $each;
                    $channel_media_ids[] = $each;
                }
            }

            if (isset($user_details['channel_ids']) && !empty($user_details['channel_ids']) && is_array($user_details['channel_ids'])) {
                $channel_ids = array_unique(array_merge($user_details['channel_ids'], $channel_ids));
            }

            if (isset($channel_ids) && !empty($channel_ids)) {
                $channel_ids = array_unique($channel_ids);
                $channel_info = Program::getSubscribedFeedInfo($channel_ids);
            }

            /* Gets users related to the channel */
            if (!empty($channel_info)) {
                foreach ($channel_info as $each) {
                    if (isset($each['relations']['active_user_feed_rel']) && !empty($each['relations']['active_user_feed_rel'])) {
                        foreach ($each['relations']['active_user_feed_rel'] as $each_auid) {
                            $user_ids[] = $each_auid;
                        }
                    }

                    if (isset($each['relations']['inactive_user_feed_rel']) && !empty($each['relations']['inactive_user_feed_rel'])) {
                        foreach ($each['relations']['inactive_user_feed_rel'] as $each_iauid) {
                            $user_ids[] = $each_iauid;
                        }
                    }
                }
            }

            if (isset($user_details['users_ids']) && !empty($user_details['users_ids']) && is_array($user_details['users_ids'])) {
                $user_ids = array_unique(array_merge($user_details['users_ids'], $user_ids));
            }
             $user_ids = array_unique($user_ids);
            $user_info = User::getUserDetailsUsingUserIDs($user_ids);
        }
        $questions = $question->getQuestionsByMedia($asset["_id"]);

        $rel_details .= "<div class='tabbable'>
                            <ul class='nav nav-tabs active-green'>";
        if (isset($user_ids) && count($user_ids)) {
            $rel_details .= "<li class='active'><a href='#media_users' data-toggle='tab'><i class='fa fa-user'></i> Users (" . count($user_ids) . ")</a></li>";
        }
        if (isset($usergroup_ids) && count($usergroup_ids)) {
            $rel_details .= "<li><a href='#media_usergroups' data-toggle='tab'><i class='fa fa-group'></i> User Groups (" . count($usergroup_ids) . ")</a></li>";
        }
        if (isset($channel_ids) && count($channel_ids)) {
            $rel_details .= "<li><a href='#media_channels' data-toggle='tab'><i class='fa fa-rss'></i> " . trans('admin/dams.channel') . " (" . count($channel_ids) . ")</a></li>";
        }
        if (isset($announcement_ids) && count($announcement_ids)) {
            $rel_details .= "<li><a href='#media_announcements' data-toggle='tab'><i class='fa fa-flag'></i> Announcements (" . count($announcement_ids) . ")</a></li>";
        }
        if (!$questions->isEmpty()) {
            $rel_details .= "<li><a href='#media_questions' data-toggle='tab'><i class='fa fa-flag'></i> Questions (" . $questions->count() . ")</a></li>";
        }
        $rel_details .= "</ul>
                            <div class='tab-content'>
                                <div class='tab-pane fade in active' id='media_users'>
                                 <div style='height: 150px; overflow-y: auto;'  >
                                   <table  border='1' style='width:100%'>";
        if (isset($user_info) && !empty($user_info)) {
            $i = 0;
            foreach ($user_info as $each) {
                if ($i % 3 == 0) {
                    $rel_details .= '<tr>';
                }
                $rel_details .= '<td>' . $each['firstname'] . ' ';
                if ($each['lastname'] != '') {
                    $rel_details .= $each['lastname'];
                    if ($each['status'] != 'ACTIVE') {
                        $rel_details .= ' <b>(' . $each['status'] . ')</b></td>';
                    }
                } else {
                    if ($each['status'] != 'ACTIVE') {
                        $rel_details .= ' <b>(' . $each['status'] . ')</b>';
                    }
                    $rel_details .= '</td>';
                }

                $i++;
                if ($i % 3 == 0) {
                    $rel_details .= '</tr>';
                }
            }
        }
        $rel_details .= "</table>
                                    </div>
                                </div>
                                <div class='tab-pane fade' id='media_usergroups'>";
        if (isset($user_details['user_group']) && !empty($user_details['user_group'])) {
            $rel_details .= '<div style="height: 150px; overflow-y: auto;"><table  border="1" style="width:100%">';
            $i = 0;
            if ($i % 3 == 0) {
                $rel_details .= '<tr>';
            }
            foreach ($user_details['user_group'] as $each) {
                $rel_details .= '<td>' . $each . '</td>';
                $i++;
                if ($i % 3 == 0) {
                    $rel_details .= '</tr>';
                }
            }

            $rel_details .= "</table></div>";
        }

        $rel_details .= " </div>
                                <div class='tab-pane fade' id='media_channels'>";
        if (isset($channel_info) && !empty($channel_info) && is_array($channel_info)) {
            $rel_details .= "<div style='height: 300px; overflow-y: auto;'><table  border='1' style='width:100%'>
                                        <tr>
                                            <th>" . trans('admin/dams.channel') . "</th>
                                            <th>Post</th>
                                            <th>Usage</th>
                                        </tr>";

            foreach ($channel_info as $each) {
                $message = '';
                if (in_array($each['program_id'], $channel_media_ids)) {
                    $message = ' ' . trans('admin/dams.channel') . ' cover';
                }
                $rel_details .= "<tr><td>" . $each['program_title'] . "</td>";
                if (isset($post_details[$each['program_slug']]) && isset($post_ids[$each['program_slug']])) {
                    $rel_details .= "<td>";
                    $rel_details .= "<ul class='cs-tb-ul'><li>-<li>";

                    foreach ($post_details[$each['program_slug']] as $each_post) {
                        $rel_details .= "<li>" . $each_post . "</li>";
                    }
                    $rel_details .= "</ul></td><td><ul class='cs-tb-ul'><li>";
                    if ($message != '') {
                        $rel_details .= $message . "</li>";
                    } else {
                        $rel_details .= "-</li>";
                    }
                    foreach ($post_ids[$each['program_slug']] as $each_pid) {
                        if (in_array($each_pid, $post_media_cover) && in_array($each_pid, $post_media_item)) {
                            $rel_details .= "<li>Post cover,Post item</li>";
                        } elseif (in_array($each_pid, $post_media_cover)) {
                            $rel_details .= "<li>Post cover</li>";
                        } elseif (in_array($each_pid, $post_media_item)) {
                            $rel_details .= "<li>Post item</li>";
                        } else {
                            $rel_details .= "<li>-</li>";
                        }
                    }


                    $rel_details .= "</ul></td>";
                } else {
                    $rel_details .= "<td></td></tr>";
                }
            }


            $rel_details .= "</table></div>";
        }
        $rel_details .= "</div>
                                <div class='tab-pane fade' id='media_announcements'>";
        if (isset($announcement_info) && !empty($announcement_info) && is_array($announcement_info)) {
            $rel_details .= '<div style="height: 150px; overflow-y: auto;"><table  border="1" style="width:100%">';
            $i = 0;
            if ($i % 3 == 0) {
                $rel_details .= '<tr>';
            }
            foreach ($announcement_info as $each) {
                $rel_details .= '<td>' . $each['announcement_title'] . '</td>';
                $i++;
                if ($i % 3 == 0) {
                    $rel_details .= '</tr>';
                }
            }

            $rel_details .= "</table></div>";
        }

        $rel_details .= "</div>
                                <div class='tab-pane fade' id='media_questions'>";
        if (!$questions->isEmpty()) {
            $questionCounter = 1;
            $totalQuestionCount = $questions->count();
            $questionNamesString = "";
            foreach ($questions as $tmpQuestion) {
                if ($questionCounter !== $totalQuestionCount) {
                    $questionNamesString .= "{$tmpQuestion->question_name}, ";
                } else {
                    $questionNamesString .= "{$tmpQuestion->question_name}";
                }
                ++$questionCounter;
            }
        }
        if (isset($questionNamesString)) {
            $rel_details .= "<strong>{$questionNamesString}</strong>";
        }
        $rel_details .= "</div>
                            </div>
                        </div>
                    </div>";
        $data['rel_details'] = $rel_details;
        $data['del_url'] = URL::to('/') . '/cp/dams/delete-media/' . $asset['_id'] . '?start=' . $start . '&limit=' . $limit . '&search=' . $search . '&order_by=' . $order_by;
        return $data;
    }

    public function getEmbedCode()
    {
        if (Request::ajax()) {
            $mediaIDArray = Request::input("media", []);
            $mediaCollection = Dam::getMediaCollection($mediaIDArray);
            if (!$mediaCollection->isEmpty()) {
                $embedCode = [];
                $mediaCollection->each(function ($media) use (&$embedCode) {
                    $embedCode[] = $this->dams_repository->getMediaEmbedCode($media);
                });
            }
        }
        return response()->json($embedCode);
    }

    /**
     * respondImage sending image response
     * @param  string $filename
     * @param  image type $ctype
     * @return image
     */
    public function respondImage($filename, $ctype)
    {
        $width = Input::get('width', '100');
        $height = Input::get('height');
        if (!$height) {
            $height = $width;
        }
        $image_obj = new Imagick($filename);
        $image_obj->resizeImage($width, $height, Imagick::FILTER_LANCZOS, 1, true);
        return ( response(
            $image_obj->getImagesBlob(),
            200,
            ['Content-Type' => $ctype]
        ));
    }
}
