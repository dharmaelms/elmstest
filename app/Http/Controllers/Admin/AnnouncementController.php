<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\AdminBaseController;
use App\Model\Announcement;
use App\Model\Common;
use App\Model\Dam;
use App\Model\Email;
use App\Model\Event;
use App\Model\Notification;
use App\Model\Program;
use App\Model\SiteSetting;
use App\Model\User;
use App\Model\UserGroup;
use App\Enums\Module\Module as ModuleEnum;
use App\Enums\Announcement\AnnouncementPermission;
use App\Enums\RolesAndPermissions\PermissionType;
use App\Enums\RolesAndPermissions\Contexts;
use App\Services\Announcement\IAnnouncementService;
use App\Services\UserGroup\IUserGroupService;
use App\Services\User\IUserService;
use App\Traits\AkamaiTokenTrait;
use Auth;
use Carbon\Carbon;
use Config;
use Input;
use Request;
use Timezone;
use URL;
use Validator;

class AnnouncementController extends AdminBaseController
{

    use AkamaiTokenTrait;

    protected $layout = 'admin.theme.layout.master_layout';
    private $announcement_service;
    private $user_group_service;
    private $user_service;
    
    public function __construct(Request $request, IAnnouncementService $announcement_service, IUserGroupService $user_group_service, IUserService $user_service)
    {
        parent::__construct();
        $input = $request::input();
        array_walk($input, function (&$i) {
            (is_string($i)) ? $i = htmlentities($i) : '';
        });
        $request::merge($input);
        $this->theme_path = 'admin.theme';
        $this->announcement_service = $announcement_service;
        $this->user_service = $user_service;
        $this->user_group_service = $user_group_service;
    }

    public function getIndex()
    {
        if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::LIST_ANNOUNCEMENT)) {
            return parent::getAdminError($this->theme_path);
        }
        
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/announcement.manage_announcements') => 'announce',
            trans('admin/announcement.list_announcement') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'announcement');
        $this->layout->pagetitle = trans('admin/announcement.manage_announcement');
        $this->layout->pagedescription = trans('admin/announcement.list_manage_announcement');
        $this->layout->content = view('admin.theme.announcement.manageannouncement');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function getAdd()
    {
        if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ADD_ANNOUNCEMENT)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/announcement.manage_announcements') => 'announce',
            trans('admin/announcement.add_announcement') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'announcement');
        $this->layout->pagetitle = trans('admin/announcement.add_announcement');
        $this->layout->pagedescription = trans('admin/announcement.add_new_announcement');
        $this->layout->content = view('admin.theme.announcement.addannouncement');
        $this->layout->footer = view('admin.theme.common.footer');
    }

    //Upload announcement data get from addannouncement view and store into announcetbl
    public function postUploadAnnouncement($slug = null)
    {
        if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ADD_ANNOUNCEMENT)) {
            return parent::getAdminError($this->theme_path);
        }
        if (is_null($slug)) {
            return parent::getAdminError($this->theme_path);
        }
        if (Input::get('status_mode') == 'ACTIVE') {
            $rules = [
                'announcement_title' => 'Required|Min:5',
                'announcement_content' => 'Required|Min:10',
                'schedule_date' => 'Required',
                'checkbox' => 'Required',
            ];
            $validation = Validator::make(Input::all(), $rules);
            if ($validation->fails()) {
                return redirect('/cp/announce/add')
                    ->withInput()
                    ->withErrors($validation);
            }
        }
        $expire_date = (int)Carbon::createFromFormat('d-m-Y',
            Input::get('expire_date', (time() + (15 * 24 * 60 * 60))),
            Auth::user()->timezone
        )->endOfDay()->timestamp;;
        $schedule_date = (int)Carbon::createFromFormat('d-m-Y',
            Input::get('schedule_date', time()),
            Auth::user()->timezone
        )->startOfDay()->timestamp;;
        if ($expire_date < $schedule_date) {
            $expire_date = $schedule_date + (15 * 24 * 60 * 60);
        }
        $tar_people = '';
        $tar_for = Input::get('checkbox', ['public']);
        $announcement_type = 'General';
        foreach ($tar_for as $key => $value) {
            if ($value == 'cfusers') {
                $announcement_type = 'Content Feed';
            }
            if ($key >= 1) {
                $tar_people .= ' & ' . $value;
            } else {
                $tar_people .= $value;
            }
        }
        $addAnnounceArray = [
            'announcement_title' => trim(strip_tags(Input::get('announcement_title'))),
            'announcement_type' => $announcement_type,
            'announcement_content' => Input::get('announcement_content', ''),
            'status' => Input::get('status_mode'),
            'editor_images' => Input::get('editor_images', []),
            'announcement_for' => $tar_people,
            'media_assigned' => Input::get('banner', ''),
            'notify_mail' => Input::get('mail_notify', 'off'),
            'cron_flag' => 0,
            'schedule' => (int)$schedule_date,
            'expire_date' => (int)$expire_date,
            'created_by' => Auth::user()->username,
            'updated_at' => time(),
            'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
        ];
        if (!isset($slug)) {
            return redirect('cp/announce/add')
                ->with('error', trans('admin/announcement.error_add_announcement_db'))
                ->with('announce_id', $slug);
        } else {
            $update = Announcement::updateAnnouncement($slug, $addAnnounceArray);
            if (is_null($update) && $update < 0) {
                return redirect('cp/announce/add')
                    ->with('error', trans('admin/announcement.error_add_announcement_db'));
            } else {
                $announce = Announcement::getAnnouncement($slug)[0];
                if (isset($announce['media_assigned']) && !empty($announce['media_assigned'])) {
                    $for_dam_id = Dam::getDAMSAssetsUsingID($announce['media_assigned']);
                    Announcement::updateAnnouncementsRelation(
                        $slug,
                        'active_media_announcement_rel',
                        [$for_dam_id[0]['id']],
                        true
                    );
                    Dam::addMediaRelation(
                        $for_dam_id[0]['id'],
                        ['media_announcement_rel'],
                        (int)$announce['announcement_id']
                    );
                }
                Input::merge(['ids' => Input::get('user', ''), 'empty' => true]);
                $this->postAssignAnnouncement('user', $slug, false);
                Input::merge(['ids' => Input::get('usergroup', ''), 'empty' => true]);
                $this->postAssignAnnouncement('usergroup', $slug, false);
                Input::merge(['ids' => Input::get('contentfeed', ''), 'empty' => true]);
                $this->postAssignAnnouncement('contentfeed', $slug, false);
                $this->allAssignAnnouncementProcess($announce);
            }
            // $this->getInitCron(); //for testing mail sent
            return redirect('cp/announce');
        }
    }

    public function getEdit($slug = null)
    {
         $permission_data_with_flag = $this->roleService->hasPermission(
             Auth::user()->uid,
             ModuleEnum::ANNOUNCEMENT,
             PermissionType::ADMIN,
             AnnouncementPermission::EDIT_ANNOUNCEMENT,
             null,
             null,
             true
         );
        
        $edit_permission_data = get_permission_data($permission_data_with_flag);
        if (!is_announcement_accessible($edit_permission_data, $slug)) {
            return parent::getAdminError();
        }
        
        if (is_null($slug)) {
            return parent::getAdminError($this->theme_path);
        }
        $crumbs = [
            trans('admin/dashboard.dashboard') => 'cp',
            trans('admin/announcement.manage_announcements') => 'announce',
            trans('admin/announcement.edit_announcement') => '',
        ];
        $this->layout->breadcrumbs = Common::getBreadCrumbs($crumbs);
        $content_feed_list = null;
        $event_list = null;
        $this->layout->pageicon = 'fa fa-flag';
        $this->layout->header = view('admin.theme.common.header');
        $this->layout->sidebar = view('admin.theme.common.sidebar')
            ->with('mainmenu', 'announcement');
        $this->layout->pagetitle = trans('admin/announcement.edit_announcement');
        $this->layout->pagedescription = trans('admin/announcement.edit_announcement');
        $announcement = Announcement::getAnnouncement($slug);

        $announcement[0]['schedule'] = Timezone::convertFromUTC(
            '@' . $announcement[0]['schedule'],
            Auth::user()->timezone,
            config('app.date_format')
        );
        if (isset($announcement[0]['relations'])) {
            $rel = $announcement[0]['relations'];
        } else {
            $rel = null;
        }

        $start_serv = 0;
        $length_page_serv = 10;
        $filter = 'General';
        $status_filter = 'ACTIVE';
        if (!is_null(Input::get('filter')) && !is_null(Input::get('status_filter'))) {
            $filter = Input::get('filter');
            $status_filter = Input::get('status_filter');
        }
        if (!is_null(Input::get('start')) && !is_null(Input::get('limit'))) {
            $start_serv = (int)Input::get('start');
            $length_page_serv = (int)Input::get('limit');
        }
        $this->layout->content = view('admin.theme.announcement.editannouncement')
            ->with('start_serv', $start_serv)
            ->with('length_page_serv', $length_page_serv)
            ->with('filter', $filter)
            ->with('status_filter', $status_filter)
            ->with('announcement', $announcement[0])
            ->with('rel', $rel);
        $this->layout->footer = view('admin.theme.common.footer');
    }

    public function postEditLoad($slug)
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ANNOUNCEMENT,
            PermissionType::ADMIN,
            AnnouncementPermission::EDIT_ANNOUNCEMENT,
            null,
            null,
            true
        );
        
        $edit_permission_data = get_permission_data($permission_data_with_flag);
        if (!is_announcement_accessible($edit_permission_data, $slug)) {
            return parent::getAdminError();
        }
        
        $rules = [
            'announcement_title' => 'Required|Min:5',
            'announcement_content' => 'Required|Min:10',
            'checkbox' => 'Required',
            'schedule_date' => 'Required',
        ];
        $validation = Validator::make(Input::all(), $rules);
        if ($validation->fails()) {
            return redirect('/cp/announce/edit/' . $slug)
                ->withInput()
                ->withErrors($validation);
        } else {
            $announcement = Announcement::getAnnouncement($slug);
            // TODO: Sateesh: This editor_images variable is unused. Please check.
            if (isset($announcement[0]['editor_images'])) {
                $editor_images = $announcement[0]['editor_images'];
                if (is_array($editor_images)) {
                    $editor_images = array_merge($editor_images, Input::get('editor_images', []));
                } else {
                    $editor_images = Input::get('editor_images', []);
                }
            }
            $expire_date = (int)Carbon::createFromFormat('d-m-Y',
                Input::get('expire_date', (time() + (15 * 24 * 60 * 60))),
                Auth::user()->timezone
            )->endOfDay()->timestamp;
            $schedule_date = (int)Carbon::createFromFormat('d-m-Y',
                Input::get('schedule_date', time()),
                Auth::user()->timezone
            )->startOfDay()->timestamp;
            if ($expire_date < $schedule_date) {
                $expire_date = $schedule_date + (15 * 24 * 60 * 60);
            }
            $tar_people = '';
            $tar_for = Input::get('checkbox');
            $announcement_type = 'General';

            foreach ($tar_for as $key => $value) {
                if ($value == 'cfusers') {
                    $announcement_type = 'Content Feed';
                }
                if ($key >= 1) {
                    $tar_people .= ' & ' . $value;
                } else {
                    $tar_people .= $value;
                }
            }
            $readers['user'] = [];
            $addAnnounceArray = [
                'announcement_title' => trim(strip_tags(Input::get('announcement_title'))),
                'announcement_type' => $announcement_type,
                'announcement_content' => Input::get('announcement_content'),
                'status' => Input::get('status_mode'),
                'editor_images' => Input::get('editor_images', []),
                'announcement_for' => $tar_people,
                'media_assigned' => Input::get('banner', ''),
                'notify_mail' => Input::get('mail_notify', 'off'),
                'cron_flag' => 0,
                'schedule' => (int)$schedule_date,
                'expire_date' => (int)$expire_date,
                'readers' => $readers,
                // 'created_at' => time(),
                'updated_at' => time(),
                'created_by' => Auth::user()->username,
                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            ];
            $update = Announcement::updateAnnouncement($slug, $addAnnounceArray);
            if (is_null($update)) {
                return redirect('cp/announce/edit/' . $slug)
                    ->with('error', trans('admin/announcement.error_add_announcement_db'));
            } else {
                $announce = Announcement::getAnnouncement($slug)[0];
                if (isset($announce['relations']['active_media_announcement_rel'])) {
                    foreach ($announce['relations']['active_media_announcement_rel'] as $key => $m_id) {
                        Dam::removeMediaRelationId(
                            $m_id,
                            ['media_announcement_rel'],
                            (int)$announce['announcement_id']
                        );
                    }
                    Announcement::updateAnnouncementsRelation($slug, 'active_media_announcement_rel', [], true);
                }
                if (isset($announce['media_assigned']) && !empty($announce['media_assigned'])) {
                    $for_dam_id = Dam::getDAMSAssetsUsingID($announce['media_assigned']);
                    Announcement::updateAnnouncementsRelation(
                        $slug,
                        'active_media_announcement_rel',
                        [$for_dam_id[0]['id']],
                        true
                    );
                    Dam::addMediaRelation(
                        $for_dam_id[0]['id'],
                        ['media_announcement_rel'],
                        (int)$announce['announcement_id']
                    );
                }
                Input::merge(['ids' => Input::get('user', ''), 'empty' => true]);
                $this->postAssignAnnouncement('user', $slug, false);
                Input::merge(['ids' => Input::get('usergroup', ''), 'empty' => true]);
                $this->postAssignAnnouncement('usergroup', $slug, false);
                Input::merge(['ids' => Input::get('contentfeed', ''), 'empty' => true]);
                $this->postAssignAnnouncement('contentfeed', $slug, false);

                $this->allAssignAnnouncementProcess($announce);
                // $this->getInitCron(); //for tet mail sent
                return redirect('cp/announce');
            }
        }
    }

    public function getDelete($key)
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ANNOUNCEMENT,
            PermissionType::ADMIN,
            AnnouncementPermission::DELETE_ANNOUNCEMENT,
            null,
            null,
            true
        );
        $delete_permission_data = get_permission_data($permission_data_with_flag);
        if (!is_announcement_accessible($delete_permission_data, $key)) {
            return parent::getAdminError();
        }
        if ($key) {
            $addAnnounceArray = [
                'status' => 'DELETE', //"status"   => "INACTIVE",
            ];
            $deleted = Announcement::updateAnnouncement($key, $addAnnounceArray);
            $start = Input::get('start', 0);
            $limit = Input::get('limit', 10);
            $search = Input::get('search', '');
            $order_by = Input::get('order_by', '3 desc');
            $status = Input::get('status', 'ACTIVE');
            $totalRecords = (int)Announcement::getAnnouncementSearchCount($search, $status);
            if ($totalRecords <= $start) {
                $start -= $limit;
                if ($start < 0) {
                    $start = 0;
                }
            }
            if ($deleted <= 0) {
                return redirect('cp/announce/' . $key)
                    ->with('error', trans('admin/announcement.error_delete'));
            } else {
                return redirect('cp/announce?start=' . $start . '&limit=' . $limit . '&status=' . $status . '&search=' . $search . '&order_by=' . $order_by)
                    ->with('success', trans('admin/announcement.announcement_delete_success'));
            }
        }
    }

    public function postBulkDelete()
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ANNOUNCEMENT,
            PermissionType::ADMIN,
            AnnouncementPermission::DELETE_ANNOUNCEMENT,
            null,
            null,
            true
        );
        $ary = json_decode(Input::get('announcement_delete'));
        $ary = explode(',', Input::get('ids'));
        $str = '';
        
        $delete_permission_data = get_permission_data($permission_data_with_flag);
        if (!are_announcements_accessible($delete_permission_data, array_filter($ary))) {
            return parent::getAdminError();
        }
        if (is_null($ary)) {
            return 'yes we got 0 delete object';
        } else {
            foreach ($ary as $value) {
                $addAnnounceArray = [
                    'status' => 'DELETE',
                ];
                $update = Announcement::updateAnnouncement($value, $addAnnounceArray);
            }
            $msg = trans('admin/announcement.announcement_delete_success');

            return redirect('/cp/announce')
                ->with('success', $msg);
        }
    }

    public function getViewAnnouncement($key = null)
    {
        if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::VIEW_ANNOUNCEMENT)) {
            return parent::getAdminError($this->theme_path);
        }
        if ($key) {
            $announcement = Announcement::getAnnouncement($key);
            if (empty($announcement)) {
                return parent::getAdminError($this->theme_path);
            }
            $this->layout->pageicon = 'fa fa-flag';
            $this->layout->pagetitle = trans('admin/announcement.view_announcement');
            $this->layout->pagedescription = trans('admin/announcement.view_announcement');
            $withMedia = null;
            if (isset($announcement[0]['media_assigned']) &&
                !empty(trim($announcement[0]['media_assigned'])) &&
                $announcement[0]['media_assigned'] != 'yet to fix'
            ) {
                $withMedia = $this->getMediaDetails($announcement[0]['media_assigned'], '_id');
            } elseif (isset($announcement[0]['relations']['active_media_announcement_rel']) &&
                !empty($announcement[0]['relations']['active_media_announcement_rel'])
            ) {
                $withMedia = $this->getMediaDetails($announcement[0]['relations']['active_media_announcement_rel'][0]);
            }
            return view('admin.theme.announcement.viewannouncement')
                ->with('media', $withMedia)
                ->with('announcement', $announcement[0]);
        }
    }

    public function getAnnouncementListAjax()
    {
        $permission_data_with_flag = $this->roleService->hasPermission(
            Auth::user()->uid,
            ModuleEnum::ANNOUNCEMENT,
            PermissionType::ADMIN,
            AnnouncementPermission::LIST_ANNOUNCEMENT,
            null,
            null,
            true
        );
     
        $start = 0;
        $limit = 10;
        $search = Input::get('search');
        $searchKey = 'active';
        $order_by = Input::get('order');
        $orderByArray = ['created_at' => 'desc'];

        if (isset($order_by[0]['column']) && isset($order_by[0]['dir'])) {
            if ($order_by[0]['column'] == '1') {
                $orderByArray = ['announcement_title' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '6') {
                $orderByArray = ['announcement_for' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '5') {
                $orderByArray = ['created_by_name' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '3') {
                $orderByArray = ['schedule' => $order_by[0]['dir']];
            }
            if ($order_by[0]['column'] == '4') {
                $orderByArray = ['expire_date' => $order_by[0]['dir']];
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

        $status_filter = Input::get('status_filter');
    
        if (!in_array($status_filter, ['ACTIVE', 'INACTIVE', 'PENDING', 'DRAFT', 'DELETE'])) {
            $status_filter = 'ACTIVE';
        }

        $list_permission_data = get_permission_data($permission_data_with_flag);
        if (!has_system_level_access($list_permission_data)) {
            $program_announcement_id = get_user_accessible_announcements($list_permission_data);
            $totalRecords = [];
            $filteredRecords = [];
            $filteredData = [];
            $status_filter = strtoupper($status_filter);
            if (!empty($program_announcement_id)) {
                $filter_params = ['in_ids' => $program_announcement_id,
                                  'search_key' => $searchKey,
                                  'status_filter' => $status_filter,
                                  'start' => $start,
                                  'limit' => $limit,
                                  'order_by' => $orderByArray
                                  ];
                $all_announcement = $this->announcement_service->getAllAnnouncements($filter_params);
                $totalRecords = $all_announcement->count();
                $filteredRecords = $totalRecords;
                $filteredData = $all_announcement->toArray();
            }
        } else {
            $status_filter = strtoupper($status_filter);
            $totalRecords = Announcement::getAnnouncementSearchCount($searchKey, $status_filter);
            $filteredRecords = Announcement::getAnnouncementSearchCount($searchKey, $status_filter);
            $filteredData = Announcement::getAnnouncementwithPagenation(
                $start,
                $limit,
                $orderByArray,
                $searchKey,
                $status_filter
            );
        }
    
        $dataArr = [];
        foreach ($filteredData as $key => $value) {
            $for_view_ancor = '';
            $for_edit_ancor = '';
            $for_delete_ancor = '';
            if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::VIEW_ANNOUNCEMENT)) {
                $for_view_ancor = '<a class="btn btn-circle show-tooltip ajax" title="' . trans('admin/manageweb.action_view') . '" href="' . URL::to('/cp/announce/view-announcement/' . $value->announcement_id) . '?start=' . $start . '&limit=' . $limit . '&status_filter=' . $status_filter . '" ><i class="fa fa-eye"></i></a>';
            }
            if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::EDIT_ANNOUNCEMENT)) {
                $for_edit_ancor = '<a class="btn btn-circle show-tooltip" title="' . trans('admin/manageweb.action_edit') . '" href="' . URL::to('/cp/announce/edit/' . $value->announcement_id) . '?start=' . $start . '&limit=' . $limit . '&status=' . $status_filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-edit"></i></a>';
            }
            if ($status_filter != 'DELETE') {
                if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::DELETE_ANNOUNCEMENT)) {
                    $for_delete_ancor = '<a class="btn btn-circle show-tooltip deletemedia" title="' . trans('admin/manageweb.action_delete') . '" href="' . URL::to('/cp/announce/delete/' . $value->announcement_id) . '?start=' . $start . '&limit=' . $limit . '&status=' . $status_filter . '&search=' . $searchKey . '&order_by=' . $order_by[0]['column'] . ' ' . $order_by[0]['dir'] . '" ><i class="fa fa-trash-o"></i></a>';
                }
            }
            $type = '';
            $namedata = '';
            if ($value->status == 'DRAFT') {
                $p_status = 'DRAFT';
            } elseif ($value->status == 'PENDING') {
                $p_status = 'PENDING';
            } else {
                $schedule_utc = Timezone::getTimeStamp($value->schedule);
                if ($schedule_utc > time()) {
                    $p_status = 'Yet to Publish';
                } else {
                    $p_status = 'Published';
                }
            }
            $title_trim = $value->announcement_title;
            if (strlen($title_trim) > 20) {
                $title_trim = str_split($title_trim, 20)[0];
                $title_trim .= '...';
            }
            if (isset($value->expire_date)) {
                $expire_date = $value->expire_date;
            } else {
                $expire_date = time();
            }
            $created_by_name = '';
            if (isset($value->created_by_name)) {
                $created_by_name = $value->created_by_name;
            }
            $tempArray = [
                '<input type="checkbox" value="' . $value->announcement_id . '">',
                $title_trim,
                $p_status,
                Timezone::convertFromUTC('@' . $value->schedule, Auth::user()->timezone, config('app.date_format')),
                Timezone::convertFromUTC('@' . $expire_date, Auth::user()->timezone, config('app.date_format')),
                $created_by_name,
                ($value->announcement_for == 'cfusers' ? 'channel users' : $value->announcement_for),
                $for_view_ancor . $for_edit_ancor . $for_delete_ancor,
            ];
            $viewMode = 'normal';
            if ($viewMode != 'iframe') {
                $assign_content_feed_for = false;
                $assign_event_for = false;
                if ($value->announcement_for == 'public' ||
                    $value->announcement_for == 'registerusers' ||
                    $value->announcement_for == 'All'
                ) {
                    $assign_user_for = false;
                    $assign_usergroup_for = false;
                } elseif ($value->announcement_for == 'users') {
                    $assign_user_for = true;
                    $assign_usergroup_for = false;
                } elseif ($value->announcement_for == 'usergroup') {
                    $assign_user_for = false;
                    $assign_usergroup_for = true;
                } elseif ($value->announcement_for == 'users & usergroup') {
                    $assign_user_for = true;
                    $assign_usergroup_for = true;
                } elseif ($value->announcement_for == 'cfusers') {
                    $assign_user_for = false;
                    $assign_usergroup_for = false;
                } elseif ($value->announcement_for == 'users & cfusers') {
                    $assign_content_feed_for = true;
                    $assign_user_for = true;
                    $assign_usergroup_for = false;
                } elseif ($value->announcement_for == 'usergroup & cfusers') {
                    $assign_content_feed_for = true;
                    $assign_user_for = false;
                    $assign_usergroup_for = true;
                } elseif ($value->announcement_for == 'users & usergroup & cfusers') {
                    $assign_content_feed_for = true;
                    $assign_user_for = true;
                    $assign_usergroup_for = true;
                }

                if (isset($value->relations)) {
                    $rel = $value->relations;
                } else {
                    $rel = [];
                }

                if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USER) && $assign_user_for == true) {
                    $userCount = "<a href='" . URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=' . $value->announcement_id) . "' class='damsrel badge badge-grey' data-key='" . $value->announcement_id . "' data-info='user' data-text='Assign user(s) to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                } else {
                    $userCount = "<a title='" . trans('admin/announcement.blocked') . "' href='#' class=' badge badge-grey' data-key='" . $value->announcement_id . "' data-info='user' data-text='Assign user(s) : <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                }
                if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USERGROUP) && $assign_usergroup_for == true) {
                    $userGroupCount = "<a href='" . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=' . $value->announcement_id) . "' class='damsrel badge badge-grey' data-key='" . $value->announcement_id . "'data-info='usergroup' data-text='Assign usergroup(s) to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                } else {
                    $userGroupCount = "<a title='" . trans('admin/announcement.blocked') . "'  href='#' class=' badge badge-grey' data-key='" . $value->announcement_id . "'data-info='usergroup' data-text='Assign usergroup(s) to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                }
                if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_MEDIA)) {
                    $media = "<a href='" . URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio') . "' class='damsrel badge badge-grey' data-key='" . $value->announcement_id . "' data-info='media' data-text='Assign media to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                } else {
                    $media = "<a title='" . trans('admin/announcement.blocked') . "'  href='#' class=' badge badge-grey' data-key='" . $value->announcement_id . "' data-info='media' data-text='Assign media to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                }
                if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_CHANNEL) && $assign_content_feed_for == true) {
                    $contentfeed = "<a href='" . URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&subtype=single&from=announcement&relid=' . $value->announcement_id) . "' class='damsrel badge badge-grey' data-key='" . $value->announcement_id . "' data-info='contentfeed' data-text='Assign " . trans('admin/program.programs') . ' to <b>' . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                } else {
                    $contentfeed = "<a title='" . trans('admin/announcement.blocked') . "'   href='#' class=' badge badge-grey' data-key='" . $value->announcement_id . "' data-info='contentfeed' data-text='Assign " . trans('admin/program.programs') . ' to <b>' . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json=''>" . 0 . '</a>';
                }

                if (!empty($rel)) {
                    if (isset($rel['active_user_announcement_rel']) && !empty($rel['active_user_announcement_rel'])) {
                        if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USER)) {
                            $userCount = "<a href='" . URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=' . $value->announcement_id) . "' class='damsrel badge badge-success' data-key='" . $value->announcement_id . "' data-info='user' data-text='Assign user(s) to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_user_announcement_rel']) . "'>" . count($rel['active_user_announcement_rel']) . '</a>';
                        } else {
                            $userCount = "<a href='#' title='" . trans('admin/announcement.no_permi_to_assign_user') . "' class=' badge badge-success' data-key='" . $value->announcement_id . "' data-info='user' data-text='Assign user(s) to <b>" . htmlentities('"' . $value->announcement_title. '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_user_announcement_rel']) . "'>" . count($rel['active_user_announcement_rel']) . '</a>';
                        }
                    }
                    if (isset($rel['active_usergroup_announcement_rel']) && !empty($rel['active_usergroup_announcement_rel'])) {
                        if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USERGROUP)) {
                            $userGroupCount = "<a href='" . URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=' . $value->announcement_id) . "' class='damsrel badge badge-success' data-key='" . $value->announcement_id . "' data-info='usergroup' data-text='Assign usergroup(s) to <b>" . htmlentities('"' . $value->announcement_title. '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_usergroup_announcement_rel']) . "'>" . count($rel['active_usergroup_announcement_rel']) . '</a>';
                        } else {
                            $userGroupCount = "<a title='" . trans('admin/announcement.no_permi_to_assign_usergroup') . "' href='#' class=' badge badge-success' data-key='" . $value->announcement_id . "' data-info='usergroup' data-text='Assign usergroup(s) to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_usergroup_announcement_rel']) . "'>" . count($rel['active_usergroup_announcement_rel']) . '</a>';
                        }
                    }
                    if (isset($rel['active_media_announcement_rel']) && !empty($rel['active_media_announcement_rel'])) {
                        if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_MEDIA)) {
                            $media = "<a href='" . URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio') . "' class='damsrel badge badge-success' data-key='" . $value->announcement_id . "' data-info='media' data-text='Assign media to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_media_announcement_rel']) . "'>" . count($rel['active_media_announcement_rel']) . '</a>';
                        } else {
                            $media = "<a title='" . trans('admin/announcement.blocked') . "'  href='#' class=' badge badge-success' data-key='" . $value->announcement_id . "' data-info='media' data-text='Assign media to <b>" . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_media_announcement_rel']) . "'>" . count($rel['active_media_announcement_rel']) . '</a>';
                        }
                    }
                    if (isset($rel['active_contentfeed_announcement_rel']) && !empty($rel['active_contentfeed_announcement_rel'])) {
                        if (has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_CHANNEL)) {
                            $contentfeed = "<a href='" . URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&subtype=single&field=relations.contentfeed_announcement_rel&from=announcement&relid=' . $value->announcement_id) . "' class='damsrel badge badge-success' data-key='" . $value->announcement_id;

                            $contentfeed .= "' data-info='contentfeed' data-text='Assign " . trans('admin/program.programs') . ' to <b>' . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_contentfeed_announcement_rel']) . "'>" . count($rel['active_contentfeed_announcement_rel']) . '</a>';
                        } else {
                            $contentfeed = "<a title='" . trans('admin/announcement.blocked') . "'  href='#' class=' badge badge-success' data-key='" . $value->announcement_id . "' data-info='contentfeed' data-text='Assign " . trans('admin/program.programs') . ' to <b>' . htmlentities('"' . $value->announcement_title . '"', ENT_QUOTES) . "</b>' data-json='" . json_encode($rel['active_contentfeed_announcement_rel']) . "'>" . count($rel['active_contentfeed_announcement_rel']) . '</a>';
                        }
                    }
                }
                array_splice($tempArray, 7, 0, [$userCount, $userGroupCount, $contentfeed]);
            } else {
                array_splice($tempArray, 7, 0, [implode(',', $value->tags)]);
                array_pop($tempArray);
            }
            $dataArr[] = $tempArray;
        }
        $finalData = [
            'recordsTotal' => $totalRecords,
            'recordsFiltered' => $filteredRecords,
            'data' => $dataArr,
        ];

        return response()->json($finalData);
    }

    public function postAssignAnnouncement($action = null, $key = null, $fcont = false)
    {
       
        $msg = null;
        $announce_id = $key;
        if ($action == 'user') {
            if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USER)) {
                return parent::getAdminError($this->theme_path);
            }
        } elseif ($action == 'usergroup') {
            if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USERGROUP)) {
                return parent::getAdminError($this->theme_path);
            }
        } elseif ($action == 'contentfeed') {
            if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_CHANNEL)) {
                return parent::getAdminError($this->theme_path);
            }
        }
        $announce = Announcement::getAnnouncementsRelation($key);
        $announce = $announce[0];
        $ids = Input::get('ids');
        $empty = Input::get('empty');
        if ($ids) {
            $ids = explode(',', $ids);
        } else {
            $ids = [];
        }
        if (!$empty && $fcont) {
            if (empty($announce) || !$key || !in_array($action, ['user', 'usergroup', 'media', 'contentfeed']) || !is_array($ids) || empty($ids)) {
                $msg = trans('admin/dams.missing_asset');

                return response()->json(['flag' => 'error', 'message' => $msg]);
            }
        }
        if ($action == 'user') {
            $arrname = 'active_user_announcement_rel';
            $msg = trans('admin/user.user_assigned');
        }
        if ($action == 'usergroup') {
            $arrname = 'active_usergroup_announcement_rel';
            $msg = trans('admin/user.usergroup_assigned');
        }
        if ($action == 'media') {
            $arrname = 'active_media_announcement_rel';
        }
        if ($action == 'contentfeed') {
            $arrname = 'active_contentfeed_announcement_rel';
            $msg = trans('admin/program.channel_assigned_success');
        }
        if ($action == 'event') {
            $arrname = 'active_event_announcement_rel';
        }

        $insertarr = [
            'announcement_id' => $key,
        ];
        if (isset($announce->relations)) {
            if ($action == 'user' && isset($announce->relations['active_user_announcement_rel'])) {
                // Code to remove relations from user collection
                foreach ($announce->relations['active_user_announcement_rel'] as $value1) {
                    User::removeUserRelation($value1, ['user_announcement_rel'], $announce->announcement_id);
                }
            }
            if ($action == 'usergroup' && isset($announce->relations['active_usergroup_announcement_rel'])) {
                // Code to remove relations from usergroup collection
                foreach ($announce->relations['active_usergroup_announcement_rel'] as $value2) {
                    UserGroup::removeUserGroupRelation(
                        $value2,
                        ['usergroup_announcement_rel'],
                        $announce->announcement_id
                    );
                }
            }
            if ($action == 'media' && isset($announce->relations['active_media_announcement_rel'])) {
                // Code to remove relations from Dam collection
                foreach ($announce->relations['active_media_announcement_rel'] as $value3) {
                    Dam::removeMediaRelationId(
                        $value3,
                        ['media_announcement_rel'],
                        (int)$announce->announcement_id
                    );
                }
            }
            if ($action == 'contentfeed' && isset($announce->relations['active_contentfeed_announcement_rel'])) {
                // Code to remove relations from Program collection
                foreach ($announce->relations['active_contentfeed_announcement_rel'] as $value4) {
                    Program::removeFeedRelation(
                        $value4,
                        ['contentfeed_announcement_rel'],
                        $announce->announcement_id
                    );
                }
            }
            if ($action == 'event' && isset($announce->relations['active_event_announcement_rel'])) {
                // Code to remove relations from Program collection
                foreach ($announce->relations['active_event_announcement_rel'] as $value4) {
                    Event::removeEventRelation(
                        $value4,
                        ['active_event_announcement_rel'],
                        $announce->announcement_id
                    );
                }
            }
        }
        foreach ($ids as &$value) {
            $value = (int)$value;
            if ($action == 'user') {
                User::addUserRelation($value, ['user_announcement_rel'], $announce->announcement_id);
            }
            if ($action == 'usergroup') {
                UserGroup::addUserGroupRelation($value, ['usergroup_announcement_rel'], $announce->announcement_id);
            }
            if ($action == 'media') {
                Dam::addMediaRelation($value, ['media_announcement_rel'], $announce->announcement_id);
                break;
            }
            if ($action == 'contentfeed') {
                Program::addFeedRelation($value, ['contentfeed_announcement_rel'], $announce->announcement_id);
            }
            if ($action == 'event') {
                Event::addEventRelation($value, ['event_announcement_rel'], $announce->announcement_id);
            }
        }
        if ($arrname == 'active_media_announcement_rel') {
            if (count($ids) > 1) {
                // True is for over writing the array with new data
                Announcement::updateAnnouncementsRelation($key, $arrname, [$ids[0]], true);
            } else {
                // True is for over writing the array with new data
                Announcement::updateAnnouncementsRelation($key, $arrname, $ids, true);
            }
        } else {
            // True is for over writing the array with new data
            Announcement::updateAnnouncementsRelation($key, $arrname, $ids, true);
        }
        //need to do with good mail template
        /* if(!$fcont){
              $this->getSendAnnouncementMail($announce_id);
         }*/

        $addAnnounceArray = [
            'cron_flag' => 0, //"status"   => "INACTIVE",
            'updated_at' => time(),
        ];
        Announcement::updateAnnouncement($key, $addAnnounceArray);
        // $this->getInitCron();
        return response()->json(['flag' => 'success', 'message' => $msg]);
    }

    public function getCreateNotification($user_id = 9)
    {
        // $user_id=$key;
        $from_module = 'announcement';
        $nid = Notification::getMaxID();
        $message = 'New Notification from Announcement ' . $nid;
        $res = Notification::getInsertNotification((int)$user_id, $from_module, $message);
        if ($res == 1) {
            echo 'Successfully Notification Created';
        } else {
            echo 'Unable to crete Notification';
        }
        die;
    }

    public function getCreateAnnouncement()
    {
        if (!has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::LIST_ANNOUNCEMENT)) {
            return parent::getAdminError($this->theme_path);
        }

        if (!is_null(Input::get('announcement_title'))) {
            $announcement_id = Announcement::getAnounceMaxID();
            $time = time();
            $df_xpire = SiteSetting::module('Notifications and Announcements', 'ann_expire_date');
            $expire_date = strtotime($df_xpire . ' day', time());
            $addAnnounceArray = [
                'announcement_id' => $announcement_id,
                'announcement_title' => trim(strip_tags(Input::get('announcement_title'))),
                'announcement_type' => 'General',
                'announcement_content' => 'Pending announcement',
                'status' => 'PENDING',
                'announcement_for' => 'public',
                'media_assigned' => Input::get('banner', ''),
                'notify_mail' => Input::get('mail_notify', 'off'),
                'cron_flag' => 0,
                'created_at' => $time,
                'schedule' => $time,
                'expire_date' => $expire_date,
                'created_by' => Auth::user()->username,
                'created_by_name' => Auth::user()->firstname . ' ' . Auth::user()->lastname,
            ];

            $upload = Announcement::addAnnouncement($addAnnounceArray);
            if (is_null($upload) && $upload > 0) {
                $finalData = [
                    'flag' => 'error',
                ];

                return response()->json($finalData);
            } else {
                $finalData = [
                    'announcement_id' => $announcement_id,
                    'flag' => 'success',
                ];

                return response()->json($finalData);
            }
        }

        return Input::get('announcement_title');
    }

    public function getMediaDetails($key = null, $_id = null)
    {
        if (!is_null($key) && !is_null($_id) && $_id == '_id') {
            $asset = Dam::getDAMSAssetsUsingID($key);
        } elseif (!is_null($key)) {
            $asset = Dam::getDAMSMediaUsingID($key);
        } else {
            return '';
        }
        if (empty($asset) || !$key) {
            return '';
        }
        $asset = $asset[0];
        $uniconf_id = Config::get('app.uniconf_id');
        $kaltura_url = Config::get('app.kaltura_url');
        $partnerId = Config::get('app.partnerId');

        //getToken method is in AkamaiTokenTrait
        $token = null;
        $token = $this->getToken($asset);

        $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';

        return view('admin.theme.announcement.viewmedia')->with('asset', $asset)->with('kaltura', $kaltura)->with('token', $token);
    }

    public function getInitCron()
    {
        $list_announcements = Announcement::getAnnouncementListForCron();
        $addAnnounceArray = [
            'cron_flag' => 1, //"status"   => "INACTIVE",
            'updated_at' => time(),
        ];
        foreach ($list_announcements as $key => $value) {
            Announcement::updateAnnouncement($value['announcement_id'], $addAnnounceArray);
            $this->getSendAnnouncementMailToUser($value['announcement_id']);
        }
    }

    public function getSendAnnouncementMailToUser($announce_id = null)
    {
        if (is_null($announce_id)) {
            return false;
        }
        $userList = [];
        $user_ids = [];
        $ugids = [];
        $cf_ids = [];

        $announcement = Announcement::getOneAnnouncement($announce_id)->toArray();

        if (!empty($announcement) && $announcement[0]['announcement_for'] != 'public') {
            switch ($announcement[0]['announcement_for']) {
                case 'registerusers':
                    $userList = User::getAllUsers('ACTIVE');
                    foreach ($userList as $key => $specific_user) {
                        if (isset($specific_user['uid']) && $specific_user['uid'] > 0) {
                            array_push($user_ids, $specific_user['uid']);
                        }
                    }
                    break;
                case 'users':
                    if (isset($announcement[0]['relations']['active_user_announcement_rel']) && !empty($announcement[0]['relations']['active_user_announcement_rel'])) {
                        $user_ids = $announcement[0]['relations']['active_user_announcement_rel'];
                    }
                    break;
                case 'usergroup':
                    if (isset($announcement[0]['relations']['active_usergroup_announcement_rel']) && !empty($announcement[0]['relations']['active_usergroup_announcement_rel'])) {
                        $ugids = $announcement[0]['relations']['active_usergroup_announcement_rel'];
                    }
                    break;
                case 'cfusers':
                    if (isset($announcement[0]['relations']['active_contentfeed_announcement_rel']) && !empty($announcement[0]['relations']['active_contentfeed_announcement_rel'])) {
                        $cf_ids = $announcement[0]['relations']['active_contentfeed_announcement_rel'];
                    }
                    break;
                case 'users & usergroup':
                    if (isset($announcement[0]['relations']['active_user_announcement_rel']) && !empty($announcement[0]['relations']['active_user_announcement_rel'])) {
                        $user_ids = $announcement[0]['relations']['active_user_announcement_rel'];
                    }
                    if (isset($announcement[0]['relations']['active_usergroup_announcement_rel']) && !empty($announcement[0]['relations']['active_usergroup_announcement_rel'])) {
                        $ugids = $announcement[0]['relations']['active_usergroup_announcement_rel'];
                    }
                    break;
                case 'usergroup & cfusers':
                    if (isset($announcement[0]['relations']['active_usergroup_announcement_rel']) && !empty($announcement[0]['relations']['active_usergroup_announcement_rel'])) {
                        $ugids = $announcement[0]['relations']['active_usergroup_announcement_rel'];
                    }
                    if (isset($announcement[0]['relations']['active_contentfeed_announcement_rel']) && !empty($announcement[0]['relations']['active_contentfeed_announcement_rel'])) {
                        $cf_ids = $announcement[0]['relations']['active_contentfeed_announcement_rel'];
                    }
                    break;
                case 'users & cfusers':
                    if (isset($announcement[0]['relations']['active_user_announcement_rel']) && !empty($announcement[0]['relations']['active_user_announcement_rel'])) {
                        $user_ids = $announcement[0]['relations']['active_user_announcement_rel'];
                    }
                    if (isset($announcement[0]['relations']['active_contentfeed_announcement_rel']) && !empty($announcement[0]['relations']['active_contentfeed_announcement_rel'])) {
                        $cf_ids = $announcement[0]['relations']['active_contentfeed_announcement_rel'];
                    }
                    break;
                case 'users & usergroup & cfusers':
                    if (isset($announcement[0]['relations']['active_user_announcement_rel']) && !empty($announcement[0]['relations']['active_user_announcement_rel'])) {
                        $user_ids = $announcement[0]['relations']['active_user_announcement_rel'];
                    }
                    if (isset($announcement[0]['relations']['active_usergroup_announcement_rel']) && !empty($announcement[0]['relations']['active_usergroup_announcement_rel'])) {
                        $ugids = $announcement[0]['relations']['active_usergroup_announcement_rel'];
                    }
                    if (isset($announcement[0]['relations']['active_contentfeed_announcement_rel']) && !empty($announcement[0]['relations']['active_contentfeed_announcement_rel'])) {
                        $cf_ids = $announcement[0]['relations']['active_contentfeed_announcement_rel'];
                    }
                    break;
                default:
                    $user_ids = [];
                    $ugids = [];
                    $cf_ids = [];
                    break;
            }
            if (!empty($cf_ids)) {
                $cf_ids = array_unique($cf_ids);
                foreach ($cf_ids as $key => $cf_id) {
                    $program = Program::getProgramDetailsByID($cf_id)->toArray();
                    if (isset($program['relations']['active_usergroup_feed_rel']) && !empty($program['relations']['active_usergroup_feed_rel'])) {
                        $ugids = array_merge($ugids, $program['relations']['active_usergroup_feed_rel']);
                    }
                    if (isset($program['relations']['active_user_feed_rel']) && !empty($program['relations']['active_user_feed_rel'])) {
                        $user_ids = array_merge($user_ids, $program['relations']['active_user_feed_rel']);
                    }
                }
            }
            if (!empty($ugids)) {
                $ugids = array_unique($ugids);
                foreach ($ugids as $key => $ugid) {
                    $usergorup = UserGroup::getActiveUserGroupsUsingID($ugid);
                    if (isset($usergorup[0]['relations']['active_user_usergroup_rel']) &&
                        !empty($usergorup[0]['relations']['active_user_usergroup_rel'])
                    ) {
                        $user_ids = array_merge($user_ids, $usergorup[0]['relations']['active_user_usergroup_rel']);
                    }
                }
            }
            if (!empty($user_ids)) {
                $user_ids = array_unique($user_ids);
                if (isset($announcement[0]['sentmail']['users']) && !empty($announcement[0]['sentmail']['users'])) {
                    $sendmailids = $announcement[0]['sentmail']['users'];
                } else {
                    $sendmailids = [];
                }
                /* echo "<pre>";
                 print_r($user_ids);
                 die;*/
                foreach ($user_ids as $key => $u_id) {
                    if (!in_array($u_id, $sendmailids)) {
                        $u_email = User::getUserDetailsByID($u_id)->toArray();
                        if (!empty($u_email)) {
                            $mail_id = $u_email['email'];
                            $u_name = $u_email['username'];
                            $site_name = config('app.site_name');
                            $to = [$mail_id];//['karthikeyan@linkstreet.in'];
                            $from = [config('mail.from.address')];
                            $slug = 'announcement';
                            $email_details = Email::getEmail($slug);

                            $sender_name = $announcement[0]['created_by_name'];
                            $announce_title = $announcement[0]['announcement_title'];
                            $date_and_time = Timezone::convertFromUTC(
                                '@' . $announcement[0]['schedule'],
                                $u_email['timezone'],
                                config('app.date_format')
                            );
                            $link = URL::to('/announcements/index/' . $announcement[0]['announcement_id'] . '');
                            $support_email = config('mail.from.address');
                            $phone_number = '';
                            $user_full_name = $u_email['firstname'] . ' ' . $u_email['lastname'];

                            $subject = $email_details[0]['subject'];
                            $body = $email_details[0]['body'];
                            $subject_find = ['<ANNOUNCEMENT TITLE>'];
                            $subject_replace = [$announce_title];
                            $subject = str_replace($subject_find, $subject_replace, $subject);
                            $find = [
                                '<NAME>',
                                '<SITE NAME>',
                                '<SENDER NAME>',
                                '<ANNOUNCEMENT TITLE>',
                                '<DATE AND TIME>',
                                '<SITE URL>',
                                '<SUPPORT EMAIL>'
                            ];
                            $replace = [
                                $user_full_name,
                                $site_name,
                                $sender_name,
                                $announce_title,
                                $date_and_time,
                                $link,
                                $support_email
                            ];
                            $body = str_replace($find, $replace, $body);
                            $res = Common::sendMailHtml($body, $subject, $to, $from);
                            Announcement::updateAnnouncementsSentMail($announce_id, $u_id);
                        }
                    }
                }
            }
        }
    }

    public function allAssignAnnouncementProcess($announce = null)
    {
        if (is_null($announce)) {
            return false;
        }
        if (trim($announce['announcement_for']) == 'public') {
            if (isset($announce['relations']['active_user_announcement_rel'])) {
                foreach ($announce['relations']['active_user_announcement_rel'] as $value1) {
                    User::removeUserRelation($value1, ['user_announcement_rel'], $announce['announcement_id']);
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_user_announcement_rel',
                    [],
                    true
                );
            }
            if (isset($announce['relations']['active_usergroup_announcement_rel'])) {
                foreach ($announce['relations']['active_usergroup_announcement_rel'] as $value2) {
                    UserGroup::removeUserGroupRelation(
                        $value2,
                        ['usergroup_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_usergroup_announcement_rel',
                    [],
                    true
                );
            }
            if (isset($announce['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($announce['relations']['active_contentfeed_announcement_rel'] as $value4) {
                    Program::removeFeedRelation($value4, ['contentfeed_announcement_rel'], $announce['announcement_id']);
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_contentfeed_announcement_rel',
                    [],
                    true
                );
            }
        } elseif (trim($announce['announcement_for']) == 'registerusers') {
            if (isset($announce['relations']['active_user_announcement_rel'])) {
                foreach ($announce['relations']['active_user_announcement_rel'] as $value1) {
                    User::removeUserRelation($value1, ['user_announcement_rel'], $announce['announcement_id']);
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_user_announcement_rel',
                    [],
                    true
                );
            }
            if (isset($announce['relations']['active_usergroup_announcement_rel'])) {
                foreach ($announce['relations']['active_usergroup_announcement_rel'] as $value2) {
                    UserGroup::removeUserGroupRelation(
                        $value2,
                        ['usergroup_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_usergroup_announcement_rel',
                    [],
                    true
                );
            }
            if (isset($announce['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($announce['relations']['active_contentfeed_announcement_rel'] as $value4) {
                    Program::removeFeedRelation(
                        $value4,
                        ['contentfeed_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_contentfeed_announcement_rel',
                    [],
                    true
                );
            }
        } elseif (trim($announce['announcement_for']) == 'users') {
            if (isset($announce['relations']['active_usergroup_announcement_rel'])) {
                foreach ($announce['relations']['active_usergroup_announcement_rel'] as $value2) {
                    UserGroup::removeUserGroupRelation(
                        $value2,
                        ['usergroup_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_usergroup_announcement_rel',
                    [],
                    true
                );
            }
            if (isset($announce['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($announce['relations']['active_contentfeed_announcement_rel'] as $value4) {
                    Program::removeFeedRelation(
                        $value4,
                        ['contentfeed_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_contentfeed_announcement_rel',
                    [],
                    true
                );
            }
        } elseif (trim($announce['announcement_for']) == 'usergroup') {
            if (isset($announce['relations']['active_user_announcement_rel'])) {
                foreach ($announce['relations']['active_user_announcement_rel'] as $value1) {
                    User::removeUserRelation($value1, ['user_announcement_rel'], $announce['announcement_id']);
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_user_announcement_rel',
                    [],
                    true
                );
            }
            if (isset($announce['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($announce['relations']['active_contentfeed_announcement_rel'] as $value4) {
                    Program::removeFeedRelation(
                        $value4,
                        ['contentfeed_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_contentfeed_announcement_rel',
                    [],
                    true
                );
            }
        } elseif (trim($announce['announcement_for']) == 'cfusers') {
            if (isset($announce['relations']['active_user_announcement_rel'])) {
                foreach ($announce['relations']['active_user_announcement_rel'] as $value1) {
                    User::removeUserRelation($value1, ['user_announcement_rel'], $announce['announcement_id']);
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_user_announcement_rel',
                    [],
                    true
                );
            }
            if (isset($announce['relations']['active_usergroup_announcement_rel'])) {
                foreach ($announce['relations']['active_usergroup_announcement_rel'] as $value2) {
                    UserGroup::removeUserGroupRelation(
                        $value2,
                        ['usergroup_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_usergroup_announcement_rel',
                    [],
                    true
                );
            }
        } elseif (trim($announce['announcement_for']) == 'users & usergroup') {
            if (isset($announce['relations']['active_contentfeed_announcement_rel'])) {
                foreach ($announce['relations']['active_contentfeed_announcement_rel'] as $value4) {
                    Program::removeFeedRelation(
                        $value4,
                        ['contentfeed_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_contentfeed_announcement_rel',
                    [],
                    true
                );
            }
        } elseif (trim($announce['announcement_for']) == 'usergroup & cfusers') {
            if (isset($announce['relations']['active_user_announcement_rel'])) {
                foreach ($announce['relations']['active_user_announcement_rel'] as $value1) {
                    User::removeUserRelation($value1, ['user_announcement_rel'], $announce['announcement_id']);
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_user_announcement_rel',
                    [],
                    true
                );
            }
        } elseif (trim($announce['announcement_for']) == 'users & cfusers') {
            if (isset($announce['relations']['active_usergroup_announcement_rel'])) {
                foreach ($announce['relations']['active_usergroup_announcement_rel'] as $value2) {
                    UserGroup::removeUserGroupRelation(
                        $value2,
                        ['usergroup_announcement_rel'],
                        $announce['announcement_id']
                    );
                }
                Announcement::updateAnnouncementsRelation(
                    $announce['announcement_id'],
                    'active_usergroup_announcement_rel',
                    [],
                    true
                );
            }
        }
    }
}
