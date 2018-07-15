<?php
namespace App\Services\Report;

use App\Model\SiteSetting;
use App\Model\ChannelAnalytic\Repository\IOverAllChannalAnalyticRepository;
use App\Model\User;
use App\Services\DimensionChannel\IDimensionChannelService;
use App\Services\Package\IPackageService;
use App\Services\Post\IPostService;
use App\Services\Program\IProgramService;
use App\Services\TransactionDetail\ITransactionDetailService;
use App\Services\User\IUserService;
use App\Services\UserCertificate\IUserCertificateService;
use App\Services\UserGroup\IUserGroupService;
use Carbon;
use Exception;
use Log;
use Timezone;
use Auth;

/**
 * Class ExportReportService
 * @package App\Services\Report
 */
class ExportReportService implements IExportReportService
{
    private $user_cert_service;

    private $program_service;

    private $user_service;

    private $ug_service;

    private $oa_ca_repository;

    private $trans_d_service;

    private $dim_channel_service;

    private $post_service;

    private $package_service;

    const ADMIN_REPORTS_REPORT_NAME = 'admin/reports.report_name';

    const ADMIN_REPORTS_USER_ACT_COURSE = 'admin/reports.user_act_course';

    const COURSE = 'course';

    const ALL_TIME = 'all_time';

    const ALL_TIME_LABLE = 'admin/reports.all_time';

    /**
     * ExportReportService constructor.
     * @param IUserCertificateService $user_cert_service
     * @param IProgramService $program_service
     * @param IUserService $user_service
     * @param IUserGroupService $ug_service
     * @param IOverAllChannalAnalyticRepository $oa_ca_repository
     * @param IDimensionChannelService $dim_channel_service
     * @param IPostService $post_service
     * @param ITransactionDetailService $trans_d_service
     */
    public function __construct(
        IUserCertificateService $user_cert_service,
        IProgramService $program_service,
        IUserService $user_service,
        IUserGroupService $ug_service,
        IOverAllChannalAnalyticRepository $oa_ca_repository,
        IDimensionChannelService $dim_channel_service,
        IPostService $post_service,
        ITransactionDetailService $trans_d_service,
        IPackageService $package_service
    ) {
        $this->user_cert_service = $user_cert_service;
        $this->program_service = $program_service;
        $this->user_service = $user_service;
        $this->ug_service = $ug_service;
        $this->oa_ca_repository = $oa_ca_repository;
        $this->trans_d_service = $trans_d_service;
        $this->dim_channel_service = $dim_channel_service;
        $this->post_service = $post_service;
        $this->package_service = $package_service;
    }

    /**
     * {@inheritdoc}
     */
    public function prepareUserActivityByCourse($channel_id, $date_range, $package_id, $filter_by, $date_string)
    {
        try {
            $package_name = '';
            $package_short_name = '';
            if ($filter_by == 'package') {
                $package_details = $this->package_service->getPackages(
                    ["in_ids" => [(int)$package_id]],
                    ['package_title', 'package_shortname']
                )->first();
                if (is_null($package_details)) {
                    return false;
                }
                $package_name = $package_details->package_title;
                $package_short_name = isset($package_details->package_shortname) ?
                    $package_details->package_shortname : '';
            } else if ($filter_by == 'course') {
                $package_details = $this->program_service->getProgramById((int)$package_id);
                if (is_null($package_details)) {
                    return false;
                }
                $package_name = $package_details->program_title;
                $package_short_name = isset($package_details->program_shortname) ?
                    $package_details->program_shortname : '';
            }
            $program_details = $this->program_service->getProgramById((int)$channel_id);
            if (is_null($program_details)) {
                return false;
            }
            $trans_details  = $this->user_service->getUsersByChannelEnrolleddate(
                (int)$channel_id,
                $date_range
            )->keyBy('user_id');
            if ($trans_details->count() <= 0) {
                return false;
            }
            $ug_ids = array_get($program_details->relations, 'active_usergroup_feed_rel', []);
            $ug_details = $this->ug_service->getUsergroupIdName($ug_ids);
            $ug_users = [];
            foreach ($ug_details as $ug_detail) {
                $ug_users[$ug_detail->ugid] = array_get($ug_detail->relations, 'active_user_usergroup_rel', []);
            }
            $ug_users = collect($ug_users);
            $user_ids = $trans_details->keys()->all();
            $user_count = $this->user_service->getListOfUsersCount($user_ids);
            $user_comp_details = $this->oa_ca_repository->getSpecificChannelUserCompletion((int)$channel_id, $user_ids);
            $user_comp_details = $user_comp_details->keyBy('user_id');
            $is_certificate_req = SiteSetting::module('certificates', 'visibility', 'true');
            if ($is_certificate_req == 'true') {
                $certified_users = $this->user_cert_service->getSpecifiedChannelCertifiedUsers((int)$channel_id, $user_ids);
            } else {
                $certified_users = collect([]);
            }
            $file_pointer = $this->writeReportsTitle(
                [
                    "report_name" => trans(static::ADMIN_REPORTS_USER_ACT_COURSE),
                    "filter_by" => $filter_by,
                    "package_name" => $package_name,
                    "package_short_name" => $package_short_name,
                    "program_title" => $program_details->program_title,
                    "program_shortname" => isset($program_details->program_shortname) ?
                    $program_details->program_shortname : ''
                ]
            );
            fputcsv($file_pointer, [
                trans('admin/reports.selected_date_ranges'),
                ($date_string == static::ALL_TIME) ? trans(static::ALL_TIME_LABLE) : $date_string
            ]);
            $column_heads = [
                trans('admin/reports.user_name'),
                trans('admin/reports.first_name'),
                trans('admin/reports.last_name'),
                trans('admin/reports.email_id'),
                trans('admin/reports.group_name'),
                trans('admin/reports.last_access_date'),
                trans('admin/reports.enrollment_date'),
                trans('admin/reports.completion_date'),
                trans('admin/reports.completion_status'),
                trans('admin/reports.last_score'),
                trans('admin/reports.actual_duration')
            ];
            if (SiteSetting::module('certificates', 'visibility', 'true') == 'true') {
                $column_heads[] = trans('admin/reports.certificate_status');
            }
            fputcsv($file_pointer, $column_heads);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($user_count / $batch_limit);
            $record_set = 0 ;
            do {
                $start = $record_set * $batch_limit;
                $user_details = $this->user_service->getListOfUsersDetails($user_ids, $start, $batch_limit);

                foreach ($user_details as $user_detail) {
                    $user_id = $user_detail->uid;
                    $user_trans = $trans_details->get($user_id)->toArray();
                    if (is_null($user_trans)) {
                        continue;
                    }
                    $enroll_at = Timezone::getTimeStamp(array_get($user_trans, 'enrolled_on', 0));
                    if (is_numeric($enroll_at)) {
                        $start_time_obj = Carbon::createFromTimestamp($enroll_at);
                    } else {
                        $start_time_obj = Carbon::createFromTimestamp($enroll_at->timestamp);
                    }
                    $spec_user_comp = $user_comp_details->get($user_id);
                    if (!is_null($spec_user_comp)) {
                        if (isset($spec_user_comp->completed_at) && !empty($spec_user_comp->completed_at)) {
                            $completed_at = min($spec_user_comp->completed_at);
                            $completion_status = trans('admin/reports.completed');
                        } elseif (isset($spec_user_comp->updated_at) && $spec_user_comp->completion >= 100) {
                            $completed_at = $spec_user_comp->updated_at;
                            $completion_status = trans('admin/reports.completed');
                        } elseif ($spec_user_comp->completion >= 100) {
                            $completed_at = $spec_user_comp->created_at;
                            $completion_status = trans('admin/reports.completed');
                        } else {
                            $completed_at = '';
                            $actual_duration = 0;
                            $completion_status = trans('admin/reports.incomplete');
                        }
                        $last_access_at = isset($spec_user_comp->updated_at) ?
                            $spec_user_comp->updated_at : $spec_user_comp->created_at;
                        if (is_numeric($last_access_at)) {
                            $last_access_at = Carbon::createFromTimestamp($last_access_at);
                            $actual_duration = $last_access_at->diffInDays($start_time_obj);
                            $last_access_at = $last_access_at->toDateTimeString();
                            $actual_duration = $actual_duration == 0 ? 1 : $actual_duration;
                        } else {
                            $actual_duration = 0;
                        }
                        if (is_numeric($completed_at)) {
                            $completed_at = Carbon::createFromTimestamp($completed_at);
                            $actual_duration = $completed_at->diffInDays($start_time_obj);
                            $completed_at = $completed_at->toDateTimeString();
                        } elseif ($completed_at != '') {
                            $actual_duration = $spec_user_comp->completed_at->diffInDays($start_time_obj);
                        }
                        $last_score = $spec_user_comp->score;
                    } else {
                        $completed_at = '';
                        $last_access_at = '';
                        $completion_status = trans('admin/reports.yet_to_start');
                        $last_score = 0;
                        $actual_duration = 0;
                    }
                    $ug_names = [];
                    $filtered_ug = $ug_users->filter(function ($item) use ($user_id) {
                        if (in_array($user_id, $item)) {
                            return $item;
                        }
                        return null;
                    });
                    foreach ($filtered_ug->keys()->toArray() as $ugid) {
                        $ug_names[] = $ug_details->where('ugid', $ugid)->lists('usergroup_name')->toArray();
                    }
                    $each_row = [
                        $user_detail->username,
                        $user_detail->firstname,
                        $user_detail->lastname,
                        $user_detail->email,
                        implode(':;', array_collapse($ug_names)),
                        ($last_access_at) ? Timezone::convertFromUTC('@' . $last_access_at, Auth::user()->timezone, config('app.reports_date_format')) : "",
                        ($start_time_obj) ? Timezone::convertFromUTC('@' . $start_time_obj, Auth::user()->timezone, config('app.reports_date_format')) : "",
                        ($completed_at) ? Timezone::convertFromUTC('@' . $completed_at, Auth::user()->timezone, config('app.reports_date_format')) : "",
                        $completion_status,
                        isset($user_comp_details->get($user_detail->uid)->score) ?
                            $user_comp_details->get($user_detail->uid)->score : 0,
                        $actual_duration
                    ];
                    if ($is_certificate_req == 'true') {
                        $each_row[] = $certified_users->contains($user_detail->uid) ?
                            trans('admin/reports.issued') :
                            trans('admin/reports.not_issued');
                    }
                    fputcsv($file_pointer, $each_row);
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            return true;
        } catch (Exception $e) {
            Log::error('ExportReportService::prepareUserActivityByCourse()  ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareCourseActivityByUser($user_id, $date_range, $date_string)
    {
        try {
            $user_details = $this->user_service->getSpecificUser((int)$user_id);
            if (is_null($user_details)) {
                return false;
            }
            $ug_ids = array_get($user_details->relations, 'active_usergroup_user_rel', []);
            $trans_details  = $this->user_service->getUserEnrolledChannelsByUser(
                $user_id,
                $date_range
            )->keyBy('entity_id');
            $enrolled_programs = $trans_details->lists('entity_id');
            $trans_details = $trans_details->keyBy('entity_id');
            if ($enrolled_programs->count() <= 0) {
                return false;
            }
            $enrolled_programs = array_map('intval', $enrolled_programs->toArray());
            $channel_count = count($enrolled_programs);
            $program_details = $this->program_service->getCFDetailsById($enrolled_programs)
                ->keyBy('program_id');
            $feed_ids = [];
            $packages_ids = array_get($user_details->relations, 'user_package_feed_rel', []);
            $packages_count = count(array_intersect($enrolled_programs, $packages_ids));
            $ug_details = $this->ug_service->getUsergroupIdName($ug_ids);
            $feed_ids = array_get($user_details->relations, 'user_feed_rel', []);
            $ug_feeds = [];
            foreach ($ug_details as $ug_detail) {
                $ug_feeds[$ug_detail->ugid] = array_get($ug_detail->relations, 'usergroup_feed_rel', []);
            }
            if (!empty($ug_feeds)) {
                $feed_ids = array_merge($feed_ids, call_user_func_array('array_merge', $ug_feeds));
            }
            $ug_feeds = collect($ug_feeds);
            $comp_details = $this->oa_ca_repository->getUserChannelCompletionDetails($enrolled_programs, $user_id)->keyBy('channel_id');
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename='
                 . str_replace(" ", "", trans('admin/reports.course_act_user')) . '.csv');
            fputcsv($file_pointer, [trans(static::ADMIN_REPORTS_REPORT_NAME), trans('admin/reports.course_act_user')]);
            fputcsv($file_pointer, [trans('admin/reports.user_name'), $user_details->username.' (' .$user_details->fullname.')']);
            fputcsv($file_pointer, [
                trans('admin/reports.selected_date_ranges'),
                ($date_string == static::ALL_TIME) ? trans(static::ALL_TIME_LABLE) : $date_string
            ]);
            fputcsv($file_pointer, [trans('admin/reports.course_assigned'), $channel_count]);
            fputcsv($file_pointer, [trans('admin/reports.package_assigned'), $packages_count]);
            fputcsv(
                $file_pointer,
                [
                    trans('admin/reports.group_name'),
                    trans('admin/reports.channel_name'),
                    trans('admin/reports.short_name_channel'),
                    trans('admin/reports.last_access_date'),
                    trans('admin/reports.enrollment_date'),
                    trans('admin/reports.completion_date'),
                    trans('admin/reports.completion_status'),
                    trans('admin/reports.last_score'),
                    trans('admin/reports.actual_duration')
                ]
            );
            foreach ($enrolled_programs as $enrolled_program) {
                $user_trans = $trans_details->get($enrolled_program);
                if (is_null($user_trans) || is_null($program_details->get($enrolled_program))) {
                    continue;
                }
                $enroll_at = Timezone::getTimeStamp($user_trans->enrolled_on);
                if (is_numeric($enroll_at)) {
                    $start_time_obj = Carbon::createFromTimestamp($enroll_at);
                } else {
                    $start_time_obj = Carbon::createFromTimestamp($enroll_at->timestamp);
                }
                $ug_names = [];
                $filtered_ug = $ug_feeds->filter(function ($item) use ($enrolled_program) {
                    if (in_array($enrolled_program, $item)) {
                        return $item;
                    }
                    return null;
                });
                foreach ($filtered_ug->keys()->toArray() as $ug_id) {
                    $ug_names[] = $ug_details->where('ugid', $ug_id)->lists('usergroup_name')->toArray();
                }
                $spec_chan_comp = $comp_details->get($enrolled_program);
                if (!is_null($spec_chan_comp)) {
                    if (isset($spec_chan_comp->completed_at) && !empty($spec_chan_comp->completed_at)) {
                        $completed_at = min($spec_chan_comp->completed_at);
                        $completion_status = trans('admin/reports.completed');
                    } elseif (isset($spec_chan_comp->updated_at) && $spec_chan_comp->completion >= 100) {
                        $completed_at = $spec_chan_comp->updated_at;
                        $completion_status = trans('admin/reports.completed');
                    } elseif ($spec_chan_comp->completion >= 100) {
                        $completed_at = $spec_chan_comp->created_at;
                        $completion_status = trans('admin/reports.completed');
                    } else {
                        $completed_at = '';
                        $actual_duration = 0;
                        $completion_status = trans('admin/reports.incomplete');
                    }
                    $last_access_at = isset($spec_chan_comp->updated_at) ?
                        $spec_chan_comp->updated_at : $spec_chan_comp->created_at;
                    if (is_numeric($last_access_at)) {
                        $last_access_at = Carbon::createFromTimestamp($last_access_at);
                        $actual_duration = $last_access_at->diffInDays($start_time_obj);
                        $last_access_at = $last_access_at->toDateTimeString();
                        $actual_duration = $actual_duration == 0 ? 1 : $actual_duration;
                    } else {
                        $actual_duration = 0;
                    }
                    if (is_numeric($completed_at)) {
                        $completed_at = Carbon::createFromTimestamp($completed_at);
                        $actual_duration = $completed_at->diffInDays($start_time_obj);
                        $completed_at = $completed_at->toDateTimeString();
                    } elseif ($completed_at != '') {
                        $actual_duration = $spec_chan_comp->completed_at->diffInDays($start_time_obj);
                    }
                    $last_score = $spec_chan_comp->score;
                } else {
                    $completed_at = '';
                    $last_access_at = '';
                    $completion_status = trans('admin/reports.yet_to_start');
                    $last_score = 0;
                    $actual_duration = 0;
                }
                fputcsv(
                    $file_pointer,
                    [
                        html_entity_decode(implode(':;', array_collapse($ug_names))),
                        html_entity_decode(array_get($program_details->get($enrolled_program), 'program_title', '')),
                        html_entity_decode(array_get($program_details->get($enrolled_program), 'program_shortname', '')),
                        ($last_access_at) ? Timezone::convertFromUTC('@' . $last_access_at, Auth::user()->timezone, config('app.reports_date_format')) : "",
                        ($start_time_obj) ? Timezone::convertFromUTC('@' . $start_time_obj, Auth::user()->timezone, config('app.reports_date_format')) : "",
                        ($completed_at)? Timezone::convertFromUTC('@' . $completed_at, Auth::user()->timezone, config('app.reports_date_format')) : "",
                        $completion_status,
                        $last_score,
                        $actual_duration
                    ]
                );
            }
            fclose($file_pointer);
            return true;
        } catch (Exception $e) {
            Log::error('ExportReportService::prepareCourseActivityByUser()  ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareCourseActivityByGroup($group_id, $channel_id, $package_id, $filter_by)
    {
        try {
            $data = [];
            $ug_detail = $this->ug_service->getUserGroupsUsingID((int)$group_id);
            $ug_detail = isset($ug_detail[0]) ? $ug_detail[0] : [];
            if (empty($ug_detail)) {
                return false;
            }
            $package_name = '';
            $package_short_name = '';
            if ($filter_by == 'package') {
                $package_details = $this->package_service->getPackages(
                    ["in_ids" => [(int)$package_id]],
                    ['package_title', 'package_shortname']
                )->first();
                if (is_null($package_details)) {
                    return false;
                }
                $package_name = $package_details->package_title;
                $package_short_name = isset($package_details->package_shortname) ?
                    $package_details->package_shortname : '';
            } else if ($filter_by == 'course') {
                $package_details = $this->program_service->getProgramById((int)$package_id);
                if (is_null($package_details)) {
                    return false;
                }
                $package_name = $package_details->program_title;
                $package_short_name = isset($package_details->program_shortname) ?
                    $package_details->program_shortname : '';
            }
            $ug_name = array_get($ug_detail, 'usergroup_name', '');
            $dir_feed_ids = array_get($ug_detail, 'relations.usergroup_feed_rel', []);
            $child_feed_ids = array_get($ug_detail, 'relations.usergroup_child_feed_rel', []);
            $total_feeds = array_merge($dir_feed_ids, $child_feed_ids);
            if (!in_array((int)$channel_id, $total_feeds)) {
                return false;
            }
            $act_user_ids = array_get($ug_detail, 'relations.active_user_usergroup_rel', []);
            $inactive_user_ids = array_get($ug_detail, 'relations.inactive_user_usergroup_rel', []);
            $total_users = array_merge($act_user_ids, $inactive_user_ids);
            $program_details = $this->program_service->getProgramById((int)$channel_id);
            $user_count = $this->user_service->getListOfUsersCount($total_users);
            $users_cha_com = $this->oa_ca_repository->getSpecificChannelUserCompletion($channel_id, $total_users);
            $user_comp = $users_cha_com->keyBy('user_id');
            // $trans_detail_uid  = $this->user_service->getUserEnrolledByChannelUG($channel_id, $group_id)->keyBy('user_id');
            $data = [];
            $file_pointer = $this->writeReportsTitle(
                [
                    "report_name" => trans('admin/reports.course_act_ug'),
                    "filter_by" => $filter_by,
                    "package_name" => $package_name,
                    "package_short_name" => $package_short_name,
                    "program_title" => $program_details->program_title,
                    "program_shortname" => isset($program_details->program_shortname) ?
                    $program_details->program_shortname : ''
                ]
            );
            fputcsv($file_pointer, [trans('admin/reports.group_name'), html_entity_decode($ug_name)]);
            fputcsv($file_pointer, [trans('admin/reports.no_of_user'), $user_count]);
            fputcsv(
                $file_pointer,
                [
                    trans('admin/reports.user_name'),
                    trans('admin/reports.first_name'),
                    trans('admin/reports.last_name'),
                    trans('admin/reports.last_access_date'),
                    // trans('admin/reports.enrollment_date'),
                    trans('admin/reports.completion_date'),
                    trans('admin/reports.completion_status'),
                    trans('admin/reports.last_score')
                ]
            );
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($user_count / $batch_limit);
            $record_set = 0 ;
            do {
                $start = $record_set * $batch_limit;
                $user_details = $this->user_service->getListOfUsersDetails($total_users, $start, $batch_limit);
                foreach ($user_details as $user_detail) {
                    // $user_trans = $trans_detail_uid->get($user_detail->uid);
                    // if (is_null($user_trans)) {
                    //     continue;
                    // }
                    // $enroll_at = Carbon::createFromTimestamp($user_trans->enrolled_on)->toDateTimeString();
                    $spec_user_comp = $user_comp->get($user_detail->uid);
                    if (!is_null($spec_user_comp)) {
                        if (isset($spec_user_comp->completed_at) && !empty($spec_user_comp->completed_at)) {
                            $completed_at = min($spec_user_comp->completed_at);
                            $completion_status = trans('admin/reports.completed');
                        } elseif (isset($spec_user_comp->updated_at) && $spec_user_comp->completion >= 100) {
                            $completed_at = $spec_user_comp->updated_at;
                            $completion_status = trans('admin/reports.completed');
                        } elseif ($spec_user_comp->completion >= 100) {
                            $completed_at = $spec_user_comp->created_at;
                            $completion_status = trans('admin/reports.completed');
                        } else {
                            $completed_at = '';
                            $completion_status = trans('admin/reports.incomplete');
                        }
                        $last_access_at = isset($spec_user_comp->updated_at) ?
                            $spec_user_comp->updated_at : $spec_user_comp->created_at;
                        if (is_numeric($last_access_at)) {
                            $last_access_at = Carbon::createFromTimestamp($last_access_at)->toDateTimeString();
                        }
                        if (is_numeric($completed_at)) {
                            $completed_at = Carbon::createFromTimestamp($completed_at)->toDateTimeString();
                        }
                        $last_score = $spec_user_comp->score;
                    } else {
                        $completed_at = '';
                        $last_access_at = '';
                        $completion_status = trans('admin/reports.yet_to_start');
                        $last_score = 0;
                    }
                    fputcsv(
                        $file_pointer,
                        [
                            $user_detail->username,
                            $user_detail->firstname,
                            $user_detail->lastname,
                            ($last_access_at) ? Timezone::convertFromUTC('@' . $last_access_at, Auth::user()->timezone, config('app.reports_date_format')) : "",
                            // ($enroll_at) ? Timezone::convertFromUTC('@' . $enroll_at, Auth::user()->timezone) : "",
                            ($completed_at) ? Timezone::convertFromUTC('@' . $completed_at, Auth::user()->timezone, config('app.reports_date_format')) : "",
                            $completion_status,
                            $last_score
                        ]
                    );
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            return true;
        } catch (Exception $e) {
            Log::error('ExportReportService::prepareCourseActivityByGroup()  ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareGroupSummary($create_date_range, $date_string)
    {
        try {
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", trans('admin/reports.ug_summary')) . '.csv');
            fputcsv($file_pointer, [trans(static::ADMIN_REPORTS_REPORT_NAME), trans('admin/reports.ug_summary')]);
            fputcsv(
                $file_pointer,
                [
                    trans('admin/reports.selected_date_ranges'),
                    ($date_string == static::ALL_TIME) ? trans(static::ALL_TIME_LABLE) : $date_string
                ]
            );
            $ug_details = $this->ug_service->getUsergroupDetailsByDate($create_date_range);
            fputcsv($file_pointer, [trans('admin/reports.no_of_group'), $ug_details->count()]);
            fputcsv(
                $file_pointer,
                [
                    trans('admin/reports.group_name'),
                    trans('admin/reports.group_create_date'),
                    trans('admin/reports.total_users'),
                    trans('admin/reports.active_users'),
                    trans('admin/reports.inactive_users'),
                    trans('admin/reports.total_no_of_courses_assigned_to_this_group')
                ]
            );
            foreach ($ug_details as $ug_detail) {
                $active_user_count = count(array_get($ug_detail->relations, 'active_user_usergroup_rel', []));
                $inactive_user_count = count(array_get($ug_detail->relations, 'inactive_user_usergroup_rel', []));
                $feed_rel = array_get($ug_detail->relations, 'usergroup_feed_rel', []);
                $child_feed_rel = array_get($ug_detail->relations, 'usergroup_child_feed_rel', []);
                $total_feeds = array_unique(array_merge($feed_rel, $child_feed_rel));
                fputcsv(
                    $file_pointer,
                    [
                        html_entity_decode($ug_detail->usergroup_name),
                        $ug_detail->created_at,
                        $active_user_count + $inactive_user_count,
                        $active_user_count,
                        $inactive_user_count,
                        count($total_feeds)
                    ]
                );
            }
            fclose($file_pointer);
            return true;
        } catch (Exception $e) {
            Log::error('ExportReportService::prepareGroupSummary()  ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareDetailedByGroup($group_id)
    {
        try {
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", trans('admin/reports.ug_details')) . '.csv');
            fputcsv($file_pointer, [trans(static::ADMIN_REPORTS_REPORT_NAME), trans('admin/reports.ug_details')]);
            $ug_detail = $this->ug_service->getUserGroupsUsingID((int)$group_id);
            $ug_detail = isset($ug_detail[0]) ? $ug_detail[0] : [];
            if (empty($ug_detail)) {
                return false;
            }
            $ug_name = array_get($ug_detail, 'usergroup_name', '');
            fputcsv($file_pointer, [trans('admin/reports.group_name'), html_entity_decode($ug_name)]);
            $act_user_ids = array_get($ug_detail, 'relations.active_user_usergroup_rel', []);
            $inactive_user_ids = array_get($ug_detail, 'relations.inactive_user_usergroup_rel', []);
            $total_users = array_merge($act_user_ids, $inactive_user_ids);
            $user_count = $this->user_service->getListOfUsersCount($total_users);
            fputcsv($file_pointer, [trans('admin/reports.no_of_user'), $user_count]);
            fputcsv(
                $file_pointer,
                [
                    trans('admin/reports.user_name'),
                    trans('admin/reports.first_name'),
                    trans('admin/reports.last_name'),
                    trans('admin/reports.status')
                ]
            );
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($user_count / $batch_limit);
            $record_set = 0 ;
            do {
                $start = $record_set * $batch_limit;
                $user_details = $this->user_service->getListOfUsersDetails($total_users, $start, $batch_limit);
                foreach ($user_details as $user_detail) {
                    fputcsv(
                        $file_pointer,
                        [
                            $user_detail->username,
                            $user_detail->firstname,
                            $user_detail->lastname,
                            in_array($user_detail->uid, $act_user_ids) ?
                                trans('admin/reports.c_active') : trans('admin/reports.c_inactive'),
                        ]
                    );
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            return true;
        } catch (Exception $e) {
            Log::error('ExportReportService::prepareDetailedByGroup()  ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function preparePostLevelCompletion($channel_id, $date_range, $package_id, $filter_by, $date_string)
    {
        try {
            $package_name = '';
            $package_short_name = '';
            if ($filter_by == 'package') {
                $package_details = $this->package_service->getPackages(
                    ["in_ids" => [(int)$package_id]],
                    ['package_title', 'package_shortname']
                )->first();
                if (is_null($package_details)) {
                    return false;
                }
                $package_name = $package_details->package_title;
                $package_short_name = isset($package_details->package_shortname) ?
                    $package_details->package_shortname : '';
            } else if ($filter_by == 'course') {
                $package_details = $this->program_service->getProgramById((int)$package_id);
                if (is_null($package_details)) {
                    return false;
                }
                $package_name = $package_details->program_title;
                $package_short_name = isset($package_details->program_shortname) ?
                    $package_details->program_shortname : '';
            }
            $u_ids = $this->user_service->getUsersByChannelEnrolleddate(
                (int)$channel_id,
                $date_range
            )->lists('user_id')->unique();
            if ($u_ids->isEmpty()) {
                return false;
            }
            $user_comp_details = $this->oa_ca_repository->getSpecificChannelUserCompletion($channel_id, $u_ids)->toArray();
            $program_details = $this->program_service->getProgramById((int)$channel_id);
            if (is_null($program_details)) {
                return false;
            }
            $file_pointer = $this->writeReportsTitle(
                [
                    "report_name" => trans('admin/reports.course_com_item_lev'),
                    "filter_by" => $filter_by,
                    "package_name" => $package_name,
                    "package_short_name" => $package_short_name,
                    "program_title" => $program_details->program_title,
                    "program_shortname" => isset($program_details->program_shortname) ?
                    $program_details->program_shortname : ''
                ]
            );
            fputcsv($file_pointer, [
                trans('admin/reports.selected_date_ranges'),
                ($date_string == static::ALL_TIME) ? trans(static::ALL_TIME_LABLE) : $date_string
            ]);
            $user_details = $this->user_service->getListOfUsersDetails($u_ids);
            $channel_details_ary = $this->dim_channel_service->getChannelsDetails([(int)$channel_id]);
            $channel_details = array_get($channel_details_ary, 0, []);
            $post_ids = array_get($channel_details, 'post_ids', []);
            $post_details = $this->post_service->getPacketsUsingIds($post_ids);
            $user_details = $user_details->keyBy('uid');
            $post_names = [];
            foreach ($post_details as $post_detail) {
                $post_names[] = html_entity_decode(array_get($post_detail, 'packet_title', ''));
            }
            fputcsv(
                $file_pointer,
                array_merge(
                    [
                        trans('admin/reports.full_name'),
                        trans('admin/reports.email_id'),
                        trans('admin/reports.phone_number'),
                        trans('admin/reports.over_all')
                    ],
                    $post_names
                )
            );
            foreach ($user_comp_details as $user_comp_detail) {
                $temp = [];
                $uid = (int)array_get($user_comp_detail, 'user_id', 0);
                if (!$user_details->has($uid) || $uid <= 0) {
                    continue;
                }
                $temp[] = $user_details->get($uid)->fullname;
                $temp[] = $user_details->get($uid)->email;
                $temp[] = $user_details->get($uid)->mobile;
                $temp[] = array_get($user_comp_detail, 'completion', 0);
                $post_completions = [];
                $post_completion = array_get($user_comp_detail, 'post_completion', false);
                foreach ($post_details as $post_detail) {
                    if ($date_range == 'all_time' && $post_completion) {
                        $post_completions[] = array_get($post_completion, 'p_' . $post_detail['packet_id'], 0);
                    } elseif ($post_completion) {
                        $post_completions[] = array_get($post_completion, '_' . $post_detail['packet_id'], 0);
                    }
                }
                $temp = array_merge($temp, $post_completions);
                fputcsv(
                    $file_pointer,
                    $temp
                );
            }
            fclose($file_pointer);
            return true;
        } catch (Exception $e) {
            Log::error('ExportReportService::preparePostLevelCompletion()  ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function prepareProgramsCompletion($date_timestamp, $date_range, $is_completed)
    {
        try {
            set_time_limit(300);
            $count = $this->oa_ca_repository->findCompletedChannels($date_timestamp, $is_completed, true, null, null);
            if ($count <= 0) {
                return false;
            }
            $header[] = trans('admin/reports.user_name');
            $header[] = trans('admin/reports.first_name');
            $header[] = trans('admin/reports.last_name');
            $header[] = trans('admin/reports.email_id');
            $header[] = trans('admin/reports.group_name');
            $header[] = trans('admin/reports.channel_name');
            if ($is_completed) {
                $header[] = trans('admin/reports.completion_date');
                $filename = trans('admin/reports.channel_compl_report');
            } else {
                $filename = trans('admin/reports.channel_inprogress_report');
            }
            $header[] = trans('admin/reports.completion_status');
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' . str_replace(" ", "", $filename) . '.csv');
            fputcsv($file_pointer, [$filename]);
            fputcsv($file_pointer, [
                trans('admin/reports.selected_date_ranges'),
                ($date_range == static::ALL_TIME) ? trans(static::ALL_TIME_LABLE) : $date_range
            ]);
            fputcsv($file_pointer, $header);
            $batch_limit = config('app.bulk_insert_limit');
            $total_batchs = intval($count / $batch_limit);
            $record_set = 0;
            do {
                $start = $record_set * $batch_limit;
                if ($start >= 1) {
                    $start--;
                }
                $report = $this->oa_ca_repository->findCompletedChannels(
                    $date_timestamp,
                    $is_completed,
                    false,
                    $start,
                    $batch_limit
                );
                $user_ids = $report->lists('user_id')->unique()->all();
                $program_ids = $report->lists('channel_id')->unique()->all();
                $user_details = $this->user_service->getUsersDetails(['user_ids' => $user_ids])->keyBy('uid');
                $program_details = $this->program_service->getCFDetailsById($program_ids)->keyBy('program_id');
                foreach ($report as $rep) {
                    $user = $user_details->get($rep->user_id);
                    $program = $program_details->get($rep->channel_id);
                    if (is_null($user) || is_null($program)) {
                        continue;
                    }
                    $tempRow = [];
                    $tempRow[] = $user->username;
                    $tempRow[] = $user->firstname;
                    $tempRow[] = $user->lastname;
                    $tempRow[] = $user->email;
                    $ug_ids = array_get($user->relations, 'active_usergroup_user_rel', []);
                    $groupName = [];
                    foreach ($ug_ids as $ugid) {
                        $ug_detail = $this->ug_service->getUserGroupsUsingID($ugid);
                        if (!empty($ug_detail) && !empty(array_first($ug_detail))) {
                            $groupName[] = array_get(array_first($ug_detail), 'usergroup_name', '');
                        }
                    }
                    if (!empty($groupName)) {
                        $tempRow[] = implode(':; ', $groupName);
                    } else {
                        $tempRow[] = '';
                    }
                    $tempRow[] = $program->program_title;
                    if ($is_completed) {
                        if (isset($rep['completed_at'][0])) {
                            $date = $rep['completed_at'][0];
                        } elseif (isset($rep['updated_at'])) {
                            $date = $rep['updated_at'];
                        } else {
                            $date = $rep['created_at'];
                        }
                        $tempRow[] = (string)Timezone::convertFromUTC('@' . $date, Auth::user()->timezone, config('app.reports_date_format'));
                        $tempRow[] = trans('admin/reports.completed');
                    } else {
                        $tempRow[] = trans('admin/reports.inprogress');
                    }

                    fputcsv($file_pointer, $tempRow);
                }
                $record_set++;
            } while ($record_set <= $total_batchs);
            fclose($file_pointer);
            return true;
        } catch (Exception $e) {
            Log::error('ExportReportService::prepareCompletedPrograms()  ' . $e->getMessage());
            return false;
        }
    }

    /**
     * {@inheritdoc}
     */
    public function writeReportsTitle($title_details)
    {
        try {
            $report_name = array_get($title_details, 'report_name', 'reports');
            $filter_by = array_get($title_details, 'filter_by', '');
            $package_name = array_get($title_details, 'package_name', '');
            $package_short_name = array_get($title_details, 'package_short_name', '');
            $program_title = array_get($title_details, 'program_title', '');
            $program_shortname = array_get($title_details, 'program_shortname', '');
            $file_pointer = fopen('php://output', 'w');
            header('Content-type: application/csv');
            header('Content-Disposition: attachment; filename=' .str_replace(" ", "", $report_name) . '.csv');
            fputcsv($file_pointer, [trans(static::ADMIN_REPORTS_REPORT_NAME), $report_name]);
            if ($filter_by == static::COURSE) {
                fputcsv($file_pointer, [trans('admin/reports.course_name'), html_entity_decode($package_name)]);
                fputcsv($file_pointer, [trans('admin/reports.short_name_course'), html_entity_decode($package_short_name)]);
                fputcsv($file_pointer, [trans('admin/reports.batch_name'), html_entity_decode($program_title)]);
            } elseif ($filter_by == 'package') {
                fputcsv($file_pointer, [trans('admin/reports.package_name'), html_entity_decode($package_name)]);
                fputcsv($file_pointer, [trans('admin/reports.short_name_package'), html_entity_decode($package_short_name)]);
                fputcsv($file_pointer, [trans('admin/reports.channel_name'), html_entity_decode($program_title)]);
                fputcsv($file_pointer, [trans('admin/reports.short_name_channel'), html_entity_decode($program_shortname)]);
            } else {
                fputcsv($file_pointer, [trans('admin/reports.channel_name'), html_entity_decode($program_title)]);
                fputcsv($file_pointer, [trans('admin/reports.short_name_channel'), html_entity_decode($program_shortname)]);
            }
            return $file_pointer;
        } catch (Exception $e) {
            return null;
        }
    }
}
