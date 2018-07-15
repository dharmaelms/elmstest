<?php


namespace App\Http\Controllers\Portal;

use App\Model\Dams\Repository\IDamsRepository;
use App\Exceptions\Dams\MediaNotFoundException;
use App\Http\Controllers\PortalBaseController;
use App\Model\Announcement;
use App\Model\Common;
use App\Model\Dam;
use App\Model\Program;
use App\Model\SiteSetting;
use App\Model\UserGroup;
use App\Services\Announcement\IAnnouncementService;
use App\Traits\AkamaiTokenTrait;
use Auth;
use Config;
use Input;
use URL;

/**
 * Class AnnouncementPortalController
 * @package App\Http\Controllers\Portal
 */
class AnnouncementPortalController extends PortalBaseController
{
    use AkamaiTokenTrait;

    private $announcement_service;

    /**
     * AnnouncementPortalController constructor.
     * @param IAnnouncementService $announcement_service
     */
    public function __construct(
        IAnnouncementService $announcement_service,
        IDamsRepository $dams_repository
    ) {
        $this->announcement_service = $announcement_service;
        $this->dams_repository = $dams_repository;
        $this->theme = config('app.portal_theme_name');
        $this->layout = 'portal.theme.' . $this->theme . '.layout.one_columnlayout';
        $this->theme_path = 'portal.theme.' . $this->theme;
    }

    public function getIndex($announcementId = 0)
    {
        $input = Input::all();
        $path = array_get($input, 'announcement', 'dashboard_or_notification');
        $user_id = 0;
        if (Auth::check()) {
            $user_id = Auth::user()->uid;
            $this->layout->content = view($this->theme_path . '.announcement.listannouncement')
                ->with('user_id', $user_id)
                ->with('announcementId', $announcementId)
                ->with('path', $path);
        } else {
            $this->layout->content = view($this->theme_path . '.announcement.listannouncement')
                ->with('user_id', $user_id)
                ->with('announcementId', $announcementId)
                ->with('path', $path);
        }
        $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
        $this->layout->footer = view($this->theme_path . '.common.footer');
        $this->layout->theme = 'portal/theme/' . $this->theme;
        $this->layout->header = view($this->theme_path . '.common.header');
        $this->layout->footer = view($this->theme_path . '.common.footer');
    }

    public function getViewAnnouncement($id = 0)
    {
        if ($id > 0) {
            $viewrec = [];
            $announce_title = '';
            $announcement = Announcement::getOneAnnouncementforPortal($id);
            if (!is_null($announcement) && !empty($announcement)) {
                $for_media = null;
                $cf_tit_html = null;
                foreach ($announcement as $key => $value1) {
                    if (isset($value1['media_assigned']) && !empty($value1['media_assigned']) && $value1['media_assigned'] != 'yet to fix') {
                        $for_media = $this->getMediaDetails($value1['media_assigned'], '_id');
                    } elseif (isset($value1['relations']['active_media_announcement_rel']) && !empty($value1['relations']['active_media_announcement_rel'])) {
                        $for_media = $this->getMediaDetails($value1['relations']['active_media_announcement_rel'][0]);
                    }
                    if (isset($value['relations']['active_contentfeed_announcement_rel']) && !empty($value['relations']['active_contentfeed_announcement_rel'])) {
                        $temp_cf = [];
                        foreach ($value['relations']['active_contentfeed_announcement_rel'] as $key_cf => $value_cf) {
                            $temp_cf[] = $value_cf;
                        }
                        if (!empty($temp_cf)) {
                            $cf_tit_html = $this->getCFTitlesAry($temp_cf);
                        }
                    }
                    $announce_title = $value1['announcement_title'];
                    $created_by_name = '';
                    if (isset($value1['created_by_name'])) {
                        $created_by_name = $value1['created_by_name'];
                    }
                    $viewrec[] = '
                            <div class="custom-box">
                        <h3 class="page-title-small margin-top-0 pull-right">' . $value1['announcement_title'] . '&nbsp;<small><i class="fa fa-star yellow font-20"></i></small></h3>

                        <div class="row">
                            <div class="col-md-12 col-sm-12 col-xs-12 xs-margin"><br>
                              <div align="center">' . (($for_media instanceof \Illuminate\View\View) ? $for_media->render() : $for_media) . '</div><br>
                            </div>
                            <div class="col-md-12 col-sm-12 col-xs-12">
                                <div class="publish_on_id"><div class="publish_on_name">' . $created_by_name . '</div><div class="publish_on_date"><i>' . Common::getPublishOnDisplay($value1['schedule']) . '</i></div></div>
                                ' . $cf_tit_html . '
                                <p>' . html_entity_decode($value1['announcement_content']) . '</p>
                            </div>
                        </div>
                        <script>
                        $(document).ready(function () {
                            $("a").each(function() {
                              var a = new RegExp("/" + window.location.host + "/");
                              if(!a.test(this.href) && this.href != "javascript:;" && this.href != "#" && this.href != "" ) {
                                 $(this).click(function(event) {
                                    $(this) .attr("target","_blank");
                                 });
                              }
                            });
                        });
                        </script>
                    </div>';
                }
            }
            if (Auth::check()) {
                $user_id = Auth::user()->uid;
                $res = Announcement::updateAnnouncementsReaders($id, [$user_id]);
            }
            if (!empty($viewrec)) {
                $this->layout->content = view($this->theme_path . '.announcement.viewannuncement')
                    ->with('announcement', $viewrec[0])
                    ->with('announce_title', $announce_title);
            } else {
                $this->layout->content = view($this->theme_path . '.announcement.viewannuncement')
                    ->with('announcement', []);
            }
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->footer = view($this->theme_path . '.common.footer');
        }
    }

    public function getNextRecords()
    {
        $input = Input::all();
        $path = array_get($input, 'announcement', 'dashboard_or_notification');
        if (!is_null(Input::get('page_no'))) {
            $u_g_announcelist = [];
            $announce_list_id = [];
            $pageno = Input::get('page_no');
            $limit = 10;
            $start_id = ($pageno * $limit) - $limit;
            $user_id = 0;
            $list_count = 0;
            $char_limit = (int)SiteSetting::module('Notifications and Announcements', 'chars_announcment_list_page');

            if (Auth::check() && $path != 'public') {
                $user_id = Auth::user()->uid;

                if (is_admin_role(Auth::user()->role)) {
                    $over_all_announcement = $this->announcement_service->getAllAnnouncements([]);
                    if (!$over_all_announcement->isEmpty()) {
                        $announce_list_id = $over_all_announcement->pluck('announcement_id');
                    }
                } else {
                    $announce_list_id = $this->announcement_service->getAllPrivateAnnouncements();
                }

                $announcements = Announcement::getAnnouncementsforscroll($announce_list_id, $start_id, $limit, 'web');
                $list_ann_count = Announcement::getNotReadAnnouncementCount($user_id, $announce_list_id, 'web');
                if (is_null($announcements) || $announcements <= 0) {
                    return trans('admin/announcement.no_more_records');
                }
                $viewrec = [];
                $announce_list = [];

                foreach ($announcements as $key1 => $value1) {
                    $for_media = null;
                    $cf_tit_html = null;
                    if (isset($value1['media_assigned']) && !empty($value1['media_assigned']) && $value1['media_assigned'] != 'yet to fix') {
                        $for_media = $this->getMediaDetails($value1['media_assigned'], '_id');
                    } elseif (isset($value1['relations']['active_media_announcement_rel']) && !empty($value1['relations']['active_media_announcement_rel'])) {
                        $for_media = $this->getMediaDetails($value1['relations']['active_media_announcement_rel'][0]);
                    }

                    if (isset($value1['relations']['active_contentfeed_announcement_rel']) && !empty($value1['relations']['active_contentfeed_announcement_rel'])) {
                        $temp_cf = [];
                        foreach ($value1['relations']['active_contentfeed_announcement_rel'] as $key_cf => $value_cf) {
                            $temp_cf[] = $value_cf;
                        }
                        if (!empty($temp_cf)) {
                            $cf_tit_html = $this->getCFTitlesAry($temp_cf);
                        }
                    }
                    $created_by_name = '';
                    if (isset($value1['created_by_name'])) {
                        $created_by_name = $value1['created_by_name'];
                    }
                    $viewrec[] = '
                            <div class="tab-pane1">
                                <div class="row">
                                    <div class="col-md-12 col-sm-12 col-xs-12"  ><br>
                                         <div align="center">' . (($for_media instanceof \Illuminate\View\View) ? $for_media->render() : $for_media) . '</div>
                                    </div>
                                    <div class="col-md-12 col-sm-12 col-xs-12"  ><br>
                                     <div class="publish_on_id"><div class="publish_on_name">' . $created_by_name . '</div><div class="publish_on_date">' . Common::getPublishOnDisplay($value1['schedule']) . '</div> </div>
                                      ' . $cf_tit_html . '
                                     <p>' . html_entity_decode($value1['announcement_content']) . '</p>
                                    </div>
                                </div>
                                <script>
                                $(document).ready(function () {
                                    $("a").each(function() {
                                        var a = new RegExp("/" + window.location.host + "/");
                                        if(!a.test(this.href) && this.href != "javascript:;" && this.href != "#" && this.href != "" ) {
                                             $(this).click(function(event) {
                                                $(this) .attr("target","_blank");
                                             });
                                        }
                                    });
                                });
                                </script>
                            </div>
                            ';
                    if (isset($value1['readers'])) {
                        if (in_array($user_id, $value1['readers']['user'])) {
                            $announce_list[] = '
                                            <li data-info="' . $value1['announcement_id'] . '" id="' . $value1['announcement_id'] . '" >
                                                <div class="img-div">
                                                    <img src="' . URL::to('portal/theme/default/img/announce/announcementDefault.png') . '" alt='. trans("admin/announcement.announcement") .'>
                                                </div>
                                                <div class="data-div">
                                                    <strong>' . $value1['announcement_title'] . '</strong>
                                                    <br><span class="d-gray font-10">' . Common::getPublishOnDisplay($value1['schedule']) . '</span>
                                                </div>
                                            </li>
                                         ';
                        } else {
                            $announce_list[] = '
                                            <li data-info="' . $value1['announcement_id'] . '" id="' . $value1['announcement_id'] . '" >
                                                <div class="img-div">
                                                    <img src="' . URL::to('portal/theme/default/img/announce/announcementDefault.png') . '" alt='. trans("admin/announcement.announcement") .'>
                                                </div>
                                                <div class="data-div">
                                                    <strong>' . $value1['announcement_title'] . '<span class="badge badge-roundless badge-success pull-right" data-module="announcement" data-id = "' . $value1['announcement_id'] . '" data-url = "' . URL::to('/announcements/announcemnt-mark-read') . '/' . $value1['announcement_id'] . '">'. trans('admin/announcement.new') .'</span></strong>
                                                    <br><div class="d-gray font-10">' . Common::getPublishOnDisplay($value1['schedule']) . '</div>
                                                </div>
                                            </li>
                                            ';
                        }
                    } else {
                        $announce_list[] = '
                        <li data-info="' . $value1['announcement_id'] . '" id="' . $value1['announcement_id'] . '" >
                            <div class="img-div">
                                <img src="' . URL::to('portal/theme/default/img/announce/announcementDefault.png') . '" alt='. trans("admin/announcement.announcement") .'>
                            </div>
                            <div class="data-div">
                                <strong>' . $value1['announcement_title'] . '<span class="badge badge-roundless badge-success pull-right" data-module="announcement" data-id=' . $value1['announcement_id'] . ' data-url = "' . URL::to('/announcements/announcemnt-mark-read') . '/' . $value1['announcement_id'] . '">'. trans('admin/announcement.new') .'</span></strong>
                                <br><span class="d-gray font-10">' . Common::getPublishOnDisplay($value1['schedule']) . '</span>
                            </div>
                        </li>
                        ';
                    }
                }
                $finaldata = [
                    'announcement_list' => $announce_list,
                    'announcement_view' => $viewrec,
                    'list_ann_count' => $list_ann_count,
                ];

                return response()->json($finaldata);
            } elseif (Auth::check() && $path == 'public') {
                $public_announ_ids = [];
                $user_id = Auth::user()->uid;
                $announcements = $this->announcement_service->getAllPublicAnnouncements($start_id, $limit, 'web');
                foreach ($announcements as $key => $value) {
                    $public_announ_ids[] = $value['announcement_id'];
                }
                $list_ann_count = $this->announcement_service->getUnReadPublicAnnouncementsCount($user_id, $public_announ_ids, 'web');

                if (is_null($announcements) || $announcements <= 0) {
                    return trans('admin/announcement.no_more_records');
                }
                $viewrec = [];
                $announce_list = [];
                foreach ($announcements as $key => $value) {
                    $for_media = null;
                    if (isset($value['media_assigned']) && !empty($value['media_assigned']) && $value['media_assigned'] != 'yet to fix') {
                        $for_media = $this->getMediaDetails($value['media_assigned'], '_id');
                    } elseif (isset($value['relations']['active_media_announcement_rel']) && !empty($value['relations']['active_media_announcement_rel'])) {
                        $for_media = $this->getMediaDetails($value['relations']['active_media_announcement_rel'][0]);
                    }
                    $created_by_name = '';
                    if (isset($value['created_by_name'])) {
                        $created_by_name = $value['created_by_name'];
                    }

                    $viewrec[] = '
                    <div class="tab-pane1">
                        <div class="row">
                          <div class="col-md-12 col-sm-12 col-xs-12"  ><br>
                            <div align="center">' . (($for_media instanceof \Illuminate\View\View) ? $for_media->render() : $for_media) . '</div>
                          </div>
                          <div class="col-md-12 col-sm-12 col-xs-12"  ><br>
                           <div class="publish_on_id"><div class="publish_on_name">' . $created_by_name . '</div><div class="publish_on_date">' . Common::getPublishOnDisplay($value['schedule']) . '</div></div>
                            <p>' . html_entity_decode($value['announcement_content']) . '</p>
                          </div>
                        </div>
                        <script>
                        $(document).ready(function () {
                            $("a").each(function() {
                                var a = new RegExp("/" + window.location.host + "/");
                                if(!a.test(this.href) && this.href != "javascript:;" && this.href != "#" && this.href != "" ) {
                                    $(this).click(function(event) {
                                        $(this) .attr("target","_blank");
                                    });
                                }
                            });
                        });
                        </script>
                    </div>
                ';

                    if (isset($value['readers'])) {
                        if (in_array($user_id, $value['readers']['user'])) {
                            $announce_list[] = '
                                            <li data-info="' . $value['announcement_id'] . '" id="' . $value['announcement_id'] . '" >
                                                <div class="img-div">
                                                    <img src="' . URL::to('portal/theme/default/img/announce/announcementDefault.png') . '" '. trans("admin/announcement.announcement") .'>
                                                </div>
                                                <div class="data-div">
                                                    <strong>' . $value['announcement_title'] . '</strong>
                                                    <br><span class="d-gray font-10">' . Common::getPublishOnDisplay($value['schedule']) . '</span>
                                                </div>
                                            </li>
                                         ';
                        } else {
                            $announce_list[] = '
                                            <li data-info="' . $value['announcement_id'] . '" id="' . $value['announcement_id'] . '" >
                                                <div class="img-div">
                                                    <img src="' . URL::to('portal/theme/default/img/announce/announcementDefault.png') . '" '. trans("admin/announcement.announcement") .'>
                                                </div>
                                                <div class="data-div">
                                                    <strong>' . $value['announcement_title'] . '<span class="badge badge-roundless badge-success pull-right" data-module="announcement" data-id = "' . $value['announcement_id'] . '" data-url = "' . URL::to('/announcements/announcemnt-mark-read') . '/' . $value['announcement_id'] . '">'. trans('admin/announcement.new') .'</span></strong>
                                                    <br><span class="d-gray font-10">' . Common::getPublishOnDisplay($value['schedule']) . '</span>
                                                </div>
                                            </li>
                                            ';
                        }
                    } else {
                        $announce_list[] = '
                        <li data-info="' . $value['announcement_id'] . '" id="' . $value['announcement_id'] . '" >
                            <div class="img-div">
                                <img src="' . URL::to('portal/theme/default/img/announce/announcementDefault.png') . '" '. trans("admin/announcement.announcement") .'>
                            </div>
                            <div class="data-div">
                                <strong>' . $value['announcement_title'] . '<span class="badge badge-roundless badge-success pull-right" data-module="announcement" data-id=' . $value['announcement_id'] . ' data-url = "' . URL::to('/announcements/announcemnt-mark-read') . '/' . $value['announcement_id'] . '">'. trans('admin/announcement.new') .'</span></strong>
                                <br><span class="d-gray font-10">' . Common::getPublishOnDisplay($value['schedule']) . '</span>
                            </div>
                        </li>
                        ';
                    }
                }

                $final_data = [
                'announcement_list' => $announce_list,
                'announcement_view' => $viewrec,
                'list_ann_count' => $list_ann_count,
                ];

                return response()->json($final_data);
            }
            $public_announ_ids = [];
            $announcements = $this->announcement_service->getAllPublicAnnouncements($start_id, $limit, 'web');
            foreach ($announcements as $key => $value) {
                    $public_announ_ids[] = $value['announcement_id'];
                }
                $list_ann_count = $this->announcement_service->getUnReadPublicAnnouncementsCount($user_id, $public_announ_ids, 'web');
            if (is_null($announcements) || $announcements <= 0) {
                return trans('admin/announcement.no_more_records');
            }
            $viewrec = [];
            $announce_list = [];
            foreach ($announcements as $key => $value) {
                $for_media = null;
                if (isset($value['media_assigned']) && !empty($value['media_assigned']) && $value['media_assigned'] != 'yet to fix') {
                    $for_media = $this->getMediaDetails($value['media_assigned'], '_id');
                } elseif (isset($value['relations']['active_media_announcement_rel']) && !empty($value['relations']['active_media_announcement_rel'])) {
                    $for_media = $this->getMediaDetails($value['relations']['active_media_announcement_rel'][0]);
                }
                $created_by_name = '';
                if (isset($value['created_by_name'])) {
                    $created_by_name = $value['created_by_name'];
                }
                $viewrec[] = '
                    <div class="tab-pane1">
                        <div class="row">
                          <div class="col-md-12 col-sm-12 col-xs-12"  ><br>
                            <div align="center">' . (($for_media instanceof \Illuminate\View\View) ? $for_media->render() : $for_media) . '</div>
                          </div>
                          <div class="col-md-12 col-sm-12 col-xs-12"  ><br>
                           <div class="publish_on_id"><div class="publish_on_name">' . $created_by_name . '</div><div class="publish_on_date">' . Common::getPublishOnDisplay($value['schedule']) . '</div></div>
                            <p>' . html_entity_decode($value['announcement_content']) . '</p>
                          </div>
                        </div>
                        <script>
                        $(document).ready(function () {
                            $("a").each(function() {
                                var a = new RegExp("/" + window.location.host + "/");
                                if(!a.test(this.href) && this.href != "javascript:;" && this.href != "#" && this.href != "" ) {
                                    $(this).click(function(event) {
                                        $(this) .attr("target","_blank");
                                    });
                                }
                            });
                        });
                        </script>
                    </div>
                ';
                $announce_list[] = '
                                 <li data-info="' . $value['announcement_id'] . '" id="' . $value['announcement_id'] . '" >
                                    <div class="img-div">
                                        <img src="' . URL::to('portal/theme/default/img/announce/announcementDefault.png') . '" '. trans("admin/announcement.announcement") .'>
                                        </div>
                                    <div class="data-div">
                                    <strong>' . $value['announcement_title'] . '</strong>
                                    <br><span class="d-gray font-10">' . Common::getPublishOnDisplay($value['schedule']) . '</span>
                                    </div>
                                 </li>
                            ';
            }
            $finaldata = [
                'announcement_list' => $announce_list,
                'announcement_view' => $viewrec,
                'list_ann_count' => $list_ann_count,
            ];

            return response()->json($finaldata);
        } else {
            return '<p>Something went worng !!</p>';
        }
    }

    public function getPostLogin()
    {
        if (Auth::check()) {
            $user_id = Auth::user()->uid;
            if (isset(Auth::user()->relations)) {
                $relations = Auth::user()->relations;
                foreach ($relations as $key => $value) {
                    if ($key == 'active_usergroup_user_rel') {
                        $agl = UserGroup::getAnnouncementList($value);
                        foreach ($agl as $key3 => $value3) {
                            if (isset($value3['relations']['usergroup_announcement_rel'])) {
                                // array_push($announce_list_id, $value3['relations']['usergroup_announcement_rel']);
                                foreach ($value3['relations']['usergroup_announcement_rel'] as $key4 => $value4) {
                                    // array_push($announce_list_id,$value4);
                                    $announce_list_id[] = $value4;
                                }
                            }
                        }
                    }
                    if ($key == 'user_feed_rel') {
                        $acfl = Program::getAnnouncementList($value);
                        foreach ($acfl as $key6 => $value6) {
                            if (isset($value6['relations']['contentfeed_announcement_rel'])) {
                                foreach ($value6['relations']['contentfeed_announcement_rel'] as $key7 => $value7) {
                                    $announce_list_id[] = $value7;
                                }
                            }
                        }
                    }
                    if ($key == 'user_announcement_rel') {
                        if (!empty($value)) {
                            foreach ($value as $key5 => $value5) {
                                $announce_list_id[] = $value5;
                            }
                        }
                    }
                }
            }
            $announce_list_id = array_unique($announce_list_id);
            $announcements = Announcement::getAnnouncementsforscroll($announce_list_id, 0, 3, 'web');
            if (is_null($announcements) || $announcements <= 0) {
                return trans('admin/announcement.no_more_records');
            } else {
                $this->layout->content = view($this->theme_path . '.common.postlogin')
                    ->with('announcements', $announcements);
            }
            $this->layout->leftsidebar = view($this->theme_path . '.common.leftsidebar');
            $this->layout->footer = view($this->theme_path . '.common.footer');
            $this->layout->theme = 'portal/theme/' . $this->theme;
            $this->layout->header = view($this->theme_path . '.common.header');
            $this->layout->footer = view($this->theme_path . '.common.footer');
        }
    }

    public function getMediaDetails($key = null, $_id = null)
    {
        $query_field = null;
        if (!is_null($key) && $_id == '_id') {
            $query_field = '_id';
        } elseif (!is_null($key)) {
            $query_field = 'id';
        } else {
            return '';
        }

        try {
            $media = $this->dams_repository->getMedia($key, $query_field);
        } catch (MediaNotFoundException $e) {
            return trans('admin/exception.'.$e->getcode());
        }
        $asset = $media->toArray();
        if (empty($asset) || !$key) {
            return '';
        }
         
        $forret = '';
        $uniconf_id = Config::get('app.uniconf_id');
        $kaltura_url = Config::get('app.kaltura_url');
        $partnerId = Config::get('app.partnerId');

        //getToken method is in AkamaiTokenTrait
        $token = null;
        $token = $this->getToken($asset);

        $kaltura = $kaltura_url . 'index.php/kwidget/cache_st/1389590657/wid/_' . $partnerId . '/uiconf_id/' . $uniconf_id . '/entry_id/';

        return view('portal.theme.default.announcement.viewmedia')->with('kaltura', $kaltura)->with('token', $token)->with('media', $media);
    }

    public function getAnnouncemntMarkRead($aid = null)
    {
        if (!is_null($aid)) {
            if (Auth::check()) {
                $user_id = Auth::user()->uid;
                $res = Announcement::updateAnnouncementsReaders($aid, [$user_id]);
                if ($res) {
                    return 'Successfull';
                } else {
                    return 'Failed';
                }
            }
        }
    }

    public function getCFTitlesAry($cf_ids = [])
    {
        $titles = [];
        $html = '';
        foreach ($cf_ids as $key => $value) {
            $titles[] = Program::getCFTitleID($value);
        }
        if (!empty($titles)) {
            $html = "<ul class='announce-custom-list'>";
            foreach ($titles as $key => $value) {
                $html .= '<li><span><i class="fa fa-rss-square"></i> ' . $value . '</span></li>';
            }
            $html .= '</ul>';
        }

        return $html;
    }
}
