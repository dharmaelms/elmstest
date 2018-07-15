@section('content')
    <style type="text/css">
        .webexStorageLimit::-webkit-inner-spin-button, 
        .webexStorageLimit::-webkit-outer-spin-button { 
        -webkit-appearance: none;
        -moz-appearance: none;
        appearance: none;
        margin: 0; 
    }
    </style>
<?php use App\Model\Common;
use App\Libraries\moodle\MoodleAPI;?>
@if (Session::get('success'))
    <div class="alert alert-success">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('success') }}
    </div>
    <?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('error') }}
    </div>
    <?php Session::forget('error'); ?>
@endif
<?php 
$pl = App::make("App\Services\Playlyfe\IPlaylyfeService");
$isPlaylyfeEnabled = $pl->isPlaylyfeEnabled();

?>
    <?php
        $moodleapi=MoodleAPI::get_instance();
        $paramlist['']='';
//        $categorylist=$moodleapi->moodle_get_category_list($paramlist);
        if(empty($categorylist)){
        $categorylist[0]['id']=1;
        $categorylist[0]['name']='Miscellaneous';
        }
        $lang_ary=Config('app.Language');
        if(empty($lang_ary)){
           $lang_ary=['english'] ;
        }
        $category_categories_or_feeds=10;
        $ann_displayed_in_popup=5;
        $flush_notifications_days_limit = 15;
        $ann_category_chars_announcment_list_page=100;
        $ann_expire_date=30;
        $general_products_per_page=10;
        $general_faq="on";
        $general_static_pages="on";
        $general_notification="on";
        $general_email="on";
        $mathml_editor = 'on';
        $general_watch_now = 'on';
        $general_package = 'off';
        $general_posts = 'on';
        $general_favorites = 'on';
        $sort_by = 'updated_at';
        $general_more_feeds="on";
        $general_category_feeds="off";
        $general_more_batches="on";
        $general_my_activities='';
        $general_default_page_on_login="My Activity";
        $general_site_Type="Internal";
        $general_ecommerce="off";  
        $general_area_improve="off";  
        $general_language=$lang_ary[0];
        $general_events="on";
        $general_assessments="on";
        $general_moodle_courses="on";
        $general_email_for_add_user="off";
        $search_results_per_page=10;
        $search_simple="on";
        $search_advanced="on";
        $search_facet="on";
        $assessment_key="on";
        $assessment_assign_quz_to_cf="on";
        $assessment_min_no_of_qus=1;
        $assessment_defult_marks_for_qus=1;
        $assessment_allow_attempts=0;
        $event_assign_to_cf="on";
        $event_key="on";
        $event_service_lay_url="";
        $event_username="";
        $event_password="";
        $event_app_key="";
        $event_open_time=15;
        $event_duration=60;
        $logo='';
        $logo_name='';
        $logo_file_name='';
        $mobile='';
        $mobile_name='';
        $mobile_file_name='';
        $homepage='';
        $email='';
        $address = '';
        $lat = '';
        $lng = '';
        $phone='';
        $answer = '';
        $company_name ='';
        $mobile_no = '';
        $social_media=array();
        $box_view = 'on'; 
        $file_download = false;
        $text_selectable = false;
        $session_expires_at = '60'; 
        $box_failure = 'download_link';
        $bank_details = ''; 
        $quiz_60 = 0;
        $quiz_70 = 0;
        $quiz_80 = 0;
        $quiz_90 = 0;
        $quiz_100 = 0;
        $edit_quiz_till=0;
        $mathml_editor = 'off';
        $socialite_info = null;
        $homepage_info = null;
        $quizSpeed = false;
        $quizAccuracy = false;
        $quizScore = false;
        $channelCompletion = false;
        $certificates_visibility = true;
        $nda_acceptance = false;
        $quiz_reminders = [];
        $lhs_menu_settings_programs="on";
        $lhs_menu_settings_my_activity="on";
        $scorm_reports = "on";
        if (isset($sitesets) && !is_null($sitesets)){
            foreach ($sitesets as $key => $value) {
                if ($value['module']=="{{ trans('admin/category.category') }}"){
                    if (isset($value['setting']['categories_or_feeds']) && !empty($value['setting']['categories_or_feeds'])){
                        $category_categories_or_feeds=$value['setting']['categories_or_feeds'];    
                    }
                }
                elseif($value['module'] == 'MathML'){
                    $mathml_editor = $value['setting']['mathml_editor'];
                }
                elseif($value['module'] == 'certificates') {
                    $certificates_visibility = $value['setting']['visibility'];   
                }
                 elseif($value['module']=="Lmsprogram") {
                    $categoryid=$value['setting']['categoryid'];
                    $wstoken=$value['setting']['wstoken'];
                    $siteurl=$value['setting']['site_url'];
                    if (isset($value['setting']['more_batches']) && !empty($value['setting']['more_batches'])){
                    $general_more_batches=$value['setting']['more_batches'];    
                    }
                
                    }
                    elseif ($value['module'] == 'mathml') {
                        if (isset($value['setting']['mathml_editor']) && !empty($value['setting']['mathml_editor'])){
                            $general_mathml_editor=$value['setting']['mathml_editor'];    
                        }
                    }
                    elseif($value['module'] == 'Socialite')
                    {
                        $socialite_info = $value;
                    }
                    elseif($value['module'] == 'Homepage')
                    {
                        $homepage_info = $value;
                    }
                    elseif ($value['module']=="Contact Us") {
                    if (isset($value['setting']['site_logo']) && !empty($value['setting']['site_logo'])){
                        $logo=config('app.site_logo_path').$value['setting']['site_logo']; 
                        $logo_name=$value['setting']['logo_original_name']; 
                        $logo_file_name=$value['setting']['site_logo']; 
                    }
                    if (isset($value['setting']['mobile_logo']) && !empty($value['setting']['mobile_logo'])){
                        $mobile=config('app.site_logo_path').$value['setting']['mobile_logo']; 
                        $mobile_name=$value['setting']['mobile_original_name']; 
                        $mobile_file_name=$value['setting']['mobile_logo']; 
                    }
                    if (isset($value['setting']['homepage']) && !empty($value['setting']['homepage'])){
                        $homepage=$value['setting']['homepage'];    
                    }
                    if (isset($value['setting']['company_name']) && !empty($value['setting']['company_name'])){
                        $company_name = $value['setting']['company_name'];    
                    }
                    if (isset($value['setting']['email']) && !empty($value['setting']['email'])){
                        $email=$value['setting']['email'];    
                    }
                    if (isset($value['setting']['address']) && !empty($value['setting']['address'])){
                        $address = $value['setting']['address'];    
                    }
                    if (isset($value['setting']['lat']) && !empty($value['setting']['lat'])){
                        $lat = $value['setting']['lat'];    
                    }
                    if (isset($value['setting']['lng']) && !empty($value['setting']['lng'])){
                        $lng = $value['setting']['lng'];    
                    }
                    if (isset($value['setting']['phone']) && !empty($value['setting']['phone'])){
                        $phone=$value['setting']['phone'];    
                    }
                    if (isset($value['setting']['mobile_no']) && !empty($value['setting']['mobile_no'])){
                        $mobile_no = $value['setting']['mobile_no'];    
                    }
                    if (isset($value['setting']['social_media']) && !empty($value['setting']['social_media'])){
                        $social_media=$value['setting']['social_media'];    
                    }
                }elseif ($value['module']=="Notifications and Announcements") {
                    if (isset($value['setting']['displayed_in_popup']) && !empty($value['setting']['displayed_in_popup'])){
                        $ann_displayed_in_popup=$value['setting']['displayed_in_popup'];    
                    }
                    if (isset($value['setting']['chars_announcment_list_page']) && !empty($value['setting']['chars_announcment_list_page'])){
                        $ann_category_chars_announcment_list_page=$value['setting']['chars_announcment_list_page'];    
                    }
                    if (isset($value['setting']['ann_expire_date']) && !empty($value['setting']['ann_expire_date'])){
                        $ann_expire_date=$value['setting']['ann_expire_date'];    
                    }
                    if (isset($value['setting']['flush_notifications_days_limit']) && !empty($value['setting']['flush_notifications_days_limit'])){
                        $flush_notifications_days_limit=$value['setting']['flush_notifications_days_limit'];    
                    }
                }elseif ($value['module']=="General") {

                    if (isset($value['setting']['products_per_page']) && !empty($value['setting']['products_per_page'])){
                        $general_products_per_page=$value['setting']['products_per_page'];    
                    }
                    if (isset($value['setting']['faq']) && !empty($value['setting']['faq'])){
                        $general_faq=$value['setting']['faq'];    
                    }
                    if (isset($value['setting']['static_pages']) && !empty($value['setting']['static_pages'])){
                        $general_static_pages=$value['setting']['static_pages'];    
                    }
                    if (isset($value['setting']['edit_quiz_till']) && !empty($value['setting']['edit_quiz_till'])){
                        $edit_quiz_till=$value['setting']['edit_quiz_till'];    
                    }
                    if (isset($value['setting']['quiz_marics']['quiz_speed']) && !empty($value['setting']['quiz_marics']['quiz_speed'])){
                        $quizSpeed = ($value['setting']['quiz_marics']['quiz_speed'] == "on") ? true : false;    
                    }
                    if (isset($value['setting']['quiz_marics']['quiz_accuracy']) && !empty($value['setting']['quiz_marics']['quiz_accuracy'])){
                        $quizAccuracy = ($value['setting']['quiz_marics']['quiz_accuracy'] == "on") ? true : false;    
                    }
                    if (isset($value['setting']['quiz_marics']['quiz_score']) && !empty($value['setting']['quiz_marics']['quiz_score'])){
                        $quizScore = ($value['setting']['quiz_marics']['quiz_score'] == "on") ? true : false;    
                    }
                    if (isset($value['setting']['quiz_marics']['channel_completion']) && !empty($value['setting']['quiz_marics']['channel_completion'])){
                        $channelCompletion = ($value['setting']['quiz_marics']['channel_completion'] == "on") ? true : false;    
                    }
                    if (isset($value['setting']['notification']) && !empty($value['setting']['notification'])){
                        $general_notification=$value['setting']['notification'];    
                    }
                    if (isset($value['setting']['email']) && !empty($value['setting']['email'])){
                        $general_email=$value['setting']['email'];    
                    }                    
                    if (isset($value['setting']['watch_now']) && !empty($value['setting']['watch_now'])){
                        $general_watch_now=$value['setting']['watch_now'];    
                    }
                    if (isset($value['setting']['package']) && !empty($value['setting']['package'])){
                        $general_package=$value['setting']['package'];    
                    }
                    if (isset($value['setting']['posts']) && !empty($value['setting']['posts'])){
                        $general_posts=$value['setting']['posts'];    
                    }
                    if (isset($value['setting']['favorites']) && !empty($value['setting']['favorites'])){
                        $general_favorites=$value['setting']['favorites'];    
                    }
                    if (isset($value['setting']['sort_by']) && !empty($value['setting']['sort_by'])){
                        $sort_by=$value['setting']['sort_by'];    
                    }

                    if (isset($value['setting']['more_feeds']) && !empty($value['setting']['more_feeds'])){
                        $general_more_feeds=$value['setting']['more_feeds'];
                    }

                    if (isset($value['setting']['general_category_feeds']) && !empty($value['setting']['general_category_feeds'])){
                        $general_category_feeds=$value['setting']['general_category_feeds'];    
                    }
                    if (isset($value['setting']['default_page_on_login']) && !empty($value['setting']['default_page_on_login'])){
                        $general_default_page_on_login=$value['setting']['default_page_on_login'];    
                    }
                    if (isset($value['setting']['site_Type']) && !empty($value['setting']['site_Type'])){
                        $general_site_Type=$value['setting']['site_Type'];    
                    }
                    if (isset($value['setting']['ecommerce']) && !empty($value['setting']['ecommerce'])){
                        $general_ecommerce=$value['setting']['ecommerce'];    
                    }
                    if (isset($value['setting']['language']) && !empty($value['setting']['language'])){
                        $general_language=$value['setting']['language'];    
                    }
                    if (isset($value['setting']['my_activities']) && !empty($value['setting']['my_activities'])){
                        $general_my_activities=$value['setting']['my_activities'];    
                    }
                    if (isset($value['setting']['area_improve']) && !empty($value['setting']['area_improve'])){
                        $general_area_improve=$value['setting']['area_improve'];    
                    }
                    if (isset($value['setting']['events']) && !empty($value['setting']['events'])){
                        $general_events=$value['setting']['events'];    
                    }
                    if (isset($value['setting']['assessments']) && !empty($value['setting']['assessments'])){
                        $general_assessments=$value['setting']['assessments'];    
                    }
                    if (isset($value['setting']['moodle_courses']) && !empty($value['setting']['moodle_courses'])){
                        $general_moodle_courses=$value['setting']['moodle_courses'];    
                    }
                    
                    $scorm_reports = array_get($value, 'setting.scorm_reports', 'on');
                    $general_email_for_add_user=array_get($value, 'setting.email_for_add_user', 'off');
                }elseif ($value['module']=="Search") {
                    if (isset($value['setting']['results_per_page']) && !empty($value['setting']['results_per_page'])){
                        $search_results_per_page=$value['setting']['results_per_page'];    
                    }
                    if (isset($value['setting']['simple']) && !empty($value['setting']['simple'])){
                        $search_simple=$value['setting']['simple'];    
                    }
                    if (isset($value['setting']['advanced']) && !empty($value['setting']['advanced'])){
                        $search_advanced=$value['setting']['advanced'];    
                    }
                    if (isset($value['setting']['facet']) && !empty($value['setting']['facet'])){
                        $search_facet=$value['setting']['facet'];    
                    }
                }elseif ($value['module']=="Event") {
                    if (isset($value['setting']['event_key']) && !empty($value['setting']['event_key'])){
                        $event_key=$value['setting']['event_key'];    
                    }
                    if (isset($value['setting']['event_assign_to_cf']) && !empty($value['setting']['event_assign_to_cf'])){
                        $event_assign_to_cf=$value['setting']['event_assign_to_cf'];    
                    }
                    if (isset($value['setting']['event_service_lay_url']) && !empty($value['setting']['event_service_lay_url'])){
                        $event_service_lay_url=$value['setting']['event_service_lay_url'];    
                    }
                    if (isset($value['setting']['event_username']) && !empty($value['setting']['event_username'])){
                        $event_username=$value['setting']['event_username'];    
                    }
                    if (isset($value['setting']['event_password']) && !empty($value['setting']['event_password'])){
                        $event_password=$value['setting']['event_password'];    
                    }
                    if (isset($value['setting']['event_app_key']) && !empty($value['setting']['event_app_key'])){
                        $event_app_key=$value['setting']['event_app_key'];    
                    }
                    if (isset($value['setting']['event_open_time']) && !empty($value['setting']['event_open_time'])){
                        $event_open_time=$value['setting']['event_open_time'];    
                    }
                    if (isset($value['setting']['event_duration']) && !empty($value['setting']['event_duration'])){
                        $event_duration=$value['setting']['event_duration'];    
                    }
                }elseif ($value['module']=="Library") {
                    # code...
                }elseif ($value['module']=="Assessment") {
                    if (isset($value['setting']['assessment_key']) && !empty($value['setting']['assessment_key'])){
                        $assessment_key=$value['setting']['assessment_key'];    
                    }
                    if (isset($value['setting']['assessment_assign_quz_to_cf']) && !empty($value['setting']['assessment_assign_quz_to_cf'])){
                        $assessment_assign_quz_to_cf=$value['setting']['assessment_assign_quz_to_cf'];    
                    }
                    if (isset($value['setting']['assessment_min_no_of_qus']) && !empty($value['setting']['assessment_min_no_of_qus'])){
                        $assessment_min_no_of_qus=$value['setting']['assessment_min_no_of_qus'];    
                    }
                    if (isset($value['setting']['assessment_defult_marks_for_qus']) && !empty($value['setting']['assessment_defult_marks_for_qus'])){
                        $assessment_defult_marks_for_qus=$value['setting']['assessment_defult_marks_for_qus'];    
                    }
                    if (isset($value['setting']['assessment_allow_attempts']) && !empty($value['setting']['assessment_allow_attempts'])){
                        $assessment_allow_attempts=$value['setting']['assessment_allow_attempts'];    
                    }
                } elseif ($value['module']=="Viewer") {
                    $box_view = $value['setting']['box_view']; 
                    $file_download = $value['setting']['file_download']; 
                    $text_selectable = $value['setting']['text_selectable']; 
                    $session_expires_at = $value['setting']['session_expires_at']; 
                    $box_failure = $value['setting']['box_failure']; 
                } elseif ($value['module']=="BankDetails") {
                    $bank_details = $value['setting']['bank_details'];
                } elseif ($value['module'] == 'UserSetting') {
                    $nda_acceptance = $value['setting']['nda_acceptance'];
                } elseif ($value['module'] == 'QuizReminders') {
                    $quiz_reminders = $value['setting'];
                } elseif ($value['module']=="LHSMenuSettings") {
                    if (isset($value['setting']['programs']) && !empty($value['setting']['programs'])){
                        $lhs_menu_settings_programs=$value['setting']['programs'];    
                    }
                    if (isset($value['setting']['my_activity']) && !empty($value['setting']['my_activity'])){
                        $lhs_menu_settings_my_activity=$value['setting']['my_activity'];    
                    }
                }                   
            }
        } else {
            echo "im not in part";
            die;
        }
    ?>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
<style type="text/css">
    .tab-content > .active {
    display: block;
    padding: 0 10px;
}
#other_integrations .box .box-title h3 {
    color: #fff;
    display: inline-block;
    font-size: 1.2em;
    line-height: 4px;}
#other_integrations .box .box-title { padding: 6px; }
#other_integrations .box .box-title .box-tool { top: 4px; }
#other_integrations .box .box-content { border:0 !important;}
.custom-back-drop
{
    position: fixed;
    top : 0%;
    right: 0%;
    left: 0%;
    bottom: 0%;
    display: none;
    z-index : 1100;
}
</style>


    <div class="row">
        <div class="col-md-14">
            <div>
                <div>
                    <ul class="nav nav-tabs">
                        <li class=""><a href="#general" data-toggle="tab">{{ trans('admin/sitesetting.general') }}</a></li>
                         <li><a href="#lms" data-toggle="tab">{{ trans('admin/sitesetting.lms_settings') }}</a></li>
                        <li><a href="#search" data-toggle="tab">{{ trans('admin/sitesetting.search') }}</a></li>
                        <li><a href="#notifications_announcements" data-toggle="tab">{{ trans('admin/sitesetting.notifications_and_announcement') }}</a></li>
                        <li><a href="#socialite" data-toggle="tab">{{trans('admin/sitesetting.socialite')}}</a></li>
                        <li><a href="#contact_us" data-toggle="tab">Logo &amp; Contact Us</a></li>
                        <li><a href="#other_integrations" data-toggle="tab">3 rd Party Integrations</a></li>
                        <li><a href="#homepage" data-toggle="tab">{{trans('admin/sitesetting.homepage')}}</a></li>
                        <li><a href="#certificates" data-toggle="tab">{{trans('admin/sitesetting.certificates')}}</a></li>
                        <li><a href="#usersettings" data-toggle="tab">{{trans('admin/sitesetting.user_settings')}}</a>
                        <li><a href="#lhs_menu_settings" data-toggle="tab">{{trans('admin/sitesetting.lhs_menu_settings')}}</a>
                        </li>
                        <!-- <li><a href="#webexhost-list" data-toggle="tab">{{trans('admin/sitesetting.webex_host_list')}}</a> -->
                        </li>
                    </ul>
                </div>

                <div style="background-color:#ffffff;">
                    <div class="tab-content">
                        <div class="tab-pane" id="general">
                            <form action="{{URL::to('cp/sitesetting/update/general')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                  
                            <div class="form-group">
                                <label class="col-sm-6 col-lg-6 control-label">Number of products to be displayed per Page</label>
                                <div class="col-sm-6 col-lg-4 controls">
                                    <select class="form-control chosen"  id="display_per_page" name="display_per_page">   
                                        <option value="9" <?php echo $general_products_per_page==9?"selected":"";?>>9</option>
                                        <option value="12" <?php echo $general_products_per_page==12?"selected":"";?>>12</option>
                                        <option value="15" <?php echo $general_products_per_page==15?"selected":"";?>>15</option>
                                    </select>
                                </div>
                            </div>
               
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.faq') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" value="on" name="faq" <?php echo $general_faq=="on"?"checked":"";?> >
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label"> {{ trans('admin/sitesetting.static_pages') }} </label>
                        <div class="col-sm-6 col-lg-4 controls">
                           <input type="checkbox" value="on" name="static_pages"  <?php echo $general_static_pages=="on"?"checked":"";?> >
                            
                        </div>
                </div>
                <div class="form-group">
                    <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.allow_editing_of_quiz_until') }}</label>
                    <div class="col-sm-6 col-lg-4 controls">
                    <input type="text" name="edit_quiz_till" class="form-control" value='{{$edit_quiz_till}}' number/> mins before start time.
                    {!! $errors->first('edit_quiz_till', '<span class="help-inline" style="color:#f00">:message</span>') !!}   
                    </div>
                </div>
                @if(config('app.channelAnalytic') == 'on')
                <div class="form-group">
                    <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.quiz_metrics') }}</label>
                    <div class="col-sm-6 col-lg-4 controls">
                        <input  class = 'matrics-speed' type="checkbox" name="quiz_speed" @if($quizSpeed) checked @endif ></input> {{ trans('admin/sitesetting.speed') }}
                        <br>
                        <input class = 'matrics-accuracy' type="checkbox" name="quiz_accuracy" @if($quizAccuracy) checked @endif></input> {{ trans('admin/sitesetting.accuracy') }}
                        <br>
                        <input class="matrics-score" type="checkbox" name="quiz_score" @if($quizScore) checked @endif></input> {{ trans('admin/sitesetting.score') }}
                        <br>
                        <input class="matrics-completion" type="checkbox" name="channel_completion" @if($channelCompletion) checked @endif></input> {{ trans('admin/sitesetting.channel_completion') }}
                        <br>
                    </div>
                </div>
                @endif
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.watch_now') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input type="checkbox" value="on" name="watch_now"  <?php echo $general_watch_now == "on"?"checked":"";?> >
                            
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.posts') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input type="checkbox" value="on" name="posts"  <?php echo $general_posts =="on"?"checked":"";?> >
                            
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.favourites') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input type="checkbox" value="on" name="favorites"  <?php echo $general_favorites=="on"?"checked":"";?> >
                            
                        </div>
                </div>

                <!-- sortby option -->
                <div class="form-group">
                    <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.sort_by_post') }}</label>
                    <div class="col-sm-6 col-lg-4 controls" id='sort_by'>
                        <input type="radio" name="sort_by" value='updated_at' <?php if($sort_by == 'updated_at') echo "checked"; ?> /> {{ trans('admin/program.updated_at') }}
                        <input type="radio" name="sort_by" value='created_at' <?php if($sort_by == 'created_at') echo "checked"; ?> /> {{ trans('admin/program.created_at') }}
                    </div>
                </div>
                <!-- sort by option ends here -->

                @if (!config("app.ecommerce"))
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.more_channels') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" value="on" name="more_feeds"  <?php echo $general_more_feeds=="on"?"checked":"";?> >

                        </div>
                    </div>
                @endif

                 <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.category_related_channels') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input type="checkbox" value="on" name="general_category_feeds"  <?php echo $general_category_feeds=="on"?"checked":"";?> >
                            
                        </div>
                </div>
                <!--start of package-->
                @if (config("app.ecommerce"))
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.my_packages')}}s</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" name="package"  <?php echo $general_package=="on"?"checked":"";?> >
                        </div>
                    </div>
                @endif
                <!--ens of package-->
                <!--start of events-->
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.events') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" value="on" name="events" <?php echo $general_events=="on"?"checked":"";?> >
                        </div>
                </div>
                <!--end of events-->
                <!--start of assessments-->
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.assessments') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" value="on" name="assessments" <?php echo $general_assessments=="on"?"checked":"";?> >
                        </div>
                </div>
                <!--end of assessments-->
                <!--start of moodle courses-->
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.moodle_courses') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" value="on" name="moodle_courses" <?php echo $general_moodle_courses=="on"?"checked":"";?> >
                        </div>
                </div>
                <!--end of moodle courses-->
                <!--start of enable activity-->
                <div class="form-group">
                <label class="col-sm-6 col-lg-6 control-label" title="{{ trans('admin/sitesetting.area_improvement_title')}}"> {{ trans('admin/sitesetting.area_improvement')}}</label>
                <div class="col-sm-6 col-lg-4 controls">
                <input type="checkbox" name="general_area_improve"  <?php echo $general_area_improve=="on"?"checked":"";?> >
                </div>
                </div>
                <!--ens of enable activity-->
                <!--start of number of activities displayed per page-->
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.no_of_activities_to_be_disp_per_page') }}</label>
                                <div class="col-sm-6 col-lg-4 controls">
                                    <input type="number" name="my_activities" min="1" max="20" value="<?php echo $general_my_activities?>">                                    
                                </div>
                </div>
                <!--end of number of activities displayed per page-->
                <!--start of Enable emails for add user-->
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.email_for_add_user') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" value="on" name="email_for_add_user" <?php echo $general_email_for_add_user=="on"?"checked":"";?> >
                        </div>
                </div>
                <!--end of Enable emails for add user-->
                <!-- start of Enable scorm reports -->
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.scorm_reports') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                            <input type="checkbox" value="on" name="scorm_reports" <?php echo $scorm_reports=="on"?"checked":"";?> >
                        </div>
                </div>
                <!--end of Enable scorm reports -->
                
                <?php
                if (has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING) == true) {
                ?>
                <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                                <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                            </div>
                 </div>
                 <?php }?>
                </form>
            </div>
<!--start of lms settings-->
 <div class="tab-pane" id="lms">
 <form action="{{URL::to('cp/sitesetting/update/lmsprogram')}}" method="post" accept-charset="utf-8"
                class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                            <div class="form-group">
                                <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.site_url') }}</label>
                                <div class="col-sm-6 col-lg-4 controls">
                                <input type="text" name="site_url" class="form-control" <?php if(Input::old('site_url')) {?>
                                value="{{Input::old('site_url')}}"<?php } elseif($errors->first('site_url')) {?>
                                value="{{Input::old('site_url')}}"<?php } elseif(!empty($siteurl)) {?>
                                value="{{$siteurl}}"<?php } ?>/>
                                <span class="help-inline"> Lms Site base url</span><br />
                            {!! $errors->first('site_url', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                   
                                </div>
                            </div>
                            <div class="form-group">
                                <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.token') }}</label>
                                <div class="col-sm-6 col-lg-4 controls">
                                <input type="text" name="wstoken" class="form-control" <?php if(Input::old('wstoken')) {?>
                                value="{{Input::old('wstoken')}}"<?php } elseif($errors->first('wstoken')) {?>
                                value="{{Input::old('wstoken')}}"<?php } elseif(!empty($wstoken)) {?> value="{{$wstoken}}"<?php } ?>/>
                           
                            {!! $errors->first('wstoken', '<span class="help-inline" style="color:#f00">:message</span>') !!}   
                                </div>
                            </div>
                                
                             <div class="form-group">
                                <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/category.category') }}</label>
                                <div class="col-sm-6 col-lg-4 controls">
                                    <select class="form-control chosen"  name="categoryid">
                                    @if(!empty($categorylist) && is_array($categorylist))
                                        @foreach($categorylist as $category)
                                        <option value="{{ array_get($category,'id') }}" <?php if(isset($categoryid)) echo $categoryid==array_get($category,'id')?"selected":"";?>>{{ array_get($category,'name') }}</option>
                                        @endforeach
                                    @endif    
                                    </select>
                                </div>
                            </div>
                                
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.more_batches') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input type="checkbox" value="on" name="more_batches"  <?php echo $general_more_batches=="on"?"checked":"";?> >
                        </div>
                    </div>
               
                
                <?php
                    if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING) == true)
                    {
                ?>
                 <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                                <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                            </div>
                 </div>
                 <?php }?>
 </form>
 </div>
 <!--end of lms settings-->
            <div class="tab-pane" id="contact_us">
                <form action="{{URL::to('cp/sitesetting/update/contact_us')}}" method="post" enctype="multipart/form-data" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">             
                    <div class="form-group">
                        @if(!empty($logo))
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.site_logo') }} </label>
                            <div class="col-sm-2 col-lg-2 controls">
                                <img src="{{URL::to($logo)}}" height="100%" width="100%">
                            </div>
                            <div class="col-sm-7 col-lg-8 controls">
                                <span class="">{{ trans('admin/sitesetting.selected_logo_is') }}<strong>{{$logo_name}}</strong></span><br><br>
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <a class="btn bun-default btn-file">
                                                <span class="fileupload-new">{{ trans('admin/sitesetting.change') }}</span>
                                                <span class="fileupload-exists">{{ trans('admin/sitesetting.change') }}</span>
                                                <input type="file" class="file-input" name="file">
                                            </a>
                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/sitesetting.remove') }}</a>
                                        </div>
                                        <div class="form-control uneditable-input">
                                            <i class="fa fa-file fileupload-exists"></i> 
                                            <span class="fileupload-preview"></span>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" name="old_file" value="{{$logo_file_name}}">
                                <input type="hidden" name="old_name" value="{{$logo_name}}">
                                <span class="help-inline"> {{ trans('admin/sitesetting.max_dia_logo_150_28px') }}</span><br />  
                            </div>
                        @else
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.site_logo') }} </label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <a class="btn bun-default btn-file">
                                                <span class="fileupload-new">{{ trans('admin/sitesetting.select_file') }}</span>
                                                <span class="fileupload-exists">{{ trans('admin/sitesetting.change') }}</span>
                                                <input type="file" class="file-input" name="file"/>
                                            </a>
                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/sitesetting.remove') }}</a>
                                        </div>
                                        <div class="form-control uneditable-input">
                                            <i class="fa fa-file fileupload-exists"></i> 
                                            <span class="fileupload-preview"></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="help-inline"> {{ trans('admin/sitesetting.max_dia_logo_150_28px') }}</span><br />
                                {!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        @endif
                    </div>

            <!-- mobile logo -->
             <div class="form-group">
                       @if(!empty($mobile)) 
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.mobile_logo') }}</label>
                            <div class="col-sm-2 col-lg-2 controls">
                                 <img src="{{URL::to($mobile)}}" height="100%" width="100%">
                            </div>
                            <div class="col-sm-7 col-lg-8 controls">
                                <span class="">{{ trans('admin/sitesetting.selected_logo_is') }}<strong>{{$mobile_name}} </strong></span><br><br>
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <a class="btn bun-default btn-file">
                                                <span class="fileupload-new">{{ trans('admin/sitesetting.change') }}</span>
                                                <span class="fileupload-exists">{{ trans('admin/sitesetting.change') }}</span>
                                                <input type="file" class="file-input" name="mobile_file">
                                            </a>
                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/sitesetting.remove') }}</a>
                                        </div>
                                        <div class="form-control uneditable-input">
                                            <i class="fa fa-file fileupload-exists"></i> 
                                            <span class="fileupload-preview"></span>
                                        </div>
                                    </div>
                                </div>
                                 <input type="hidden" name="old_mobile_file" value="{{$mobile_file_name}}"> 
                                <input type="hidden" name="old_mobile_name" value="{{$mobile_name}}"> 
                                <span class="help-inline"> {{ trans('admin/sitesetting.max_dia_of_logo') }}</span><br />  
                            </div>
                       @else
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.mobile_logo') }}</label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <div class="input-group">
                                        <div class="input-group-btn">
                                            <a class="btn bun-default btn-file">
                                                <span class="fileupload-new">{{ trans('admin/sitesetting.select_file') }}</span>
                                                <span class="fileupload-exists">{{ trans('admin/sitesetting.change') }}</span>
                                                <input type="file" class="file-input" name="mobile_file"/>
                                            </a>
                                            <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/sitesetting.remove') }}</a>
                                        </div>
                                        <div class="form-control uneditable-input">
                                            <i class="fa fa-file fileupload-exists"></i> 
                                            <span class="fileupload-preview"></span>
                                        </div>
                                    </div>
                                </div>
                                <span class="help-inline"> {{ trans('admin/sitesetting.max_dia_of_logo') }}</span><br />
                                {!! $errors->first('mobile_file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        @endif 
                    </div>

                    <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.home_page') }}</label>
                        <div class="col-sm-9 col-lg-10 controls">
                            <input type="text" name="home_page" class="form-control" <?php if(Input::old('home_page')) {?>value="{{Input::old('home_page')}}"<?php } elseif($errors->first('home_page')) {?> value="{{Input::old('home_page')}}"<?php } elseif(!empty($homepage)) {?> value="{{$homepage}}"<?php } ?>/>
                            <span class="help-inline">(Eg. google.com)</span><br />
                            {!! $errors->first('home_page', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>

                    <!-- contact us title -->
                    <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.company_name') }}</label>
                        <div class="col-sm-9 col-lg-10 controls">
                            <input type="text" name="company_name" class="form-control" <?php if(Input::old('company_name')) {?>value="{{Input::old('company_name')}}"<?php } elseif($errors->first('company_name')) {?> value="{{Input::old('company_name')}}"<?php } elseif(!empty($company_name)) {?> value="{{$company_name}}"<?php } ?> />
                            {!! $errors->first('company_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>
                    <!-- contact us ends here -->

                    <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.email') }}</label>
                        <div class="col-sm-5 col-lg-5 controls">
                            <input type="email" name="email" class="form-control" <?php if(Input::old('email')) {?>value="{{Input::old('email')}}"<?php } elseif($errors->first('email')) {?> value="{{Input::old('email')}}"<?php } elseif(!empty($email)) {?> value="{{$email}}"<?php } ?> />
                            {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>

                    <!-- address field  -->
                    <div class="form-group">
                        <label for="address" class="col-sm-3 col-lg-2 control-label" id="add1">{{ trans('admin/sitesetting.address') }}</label>
                        <div class="col-sm-5 col-lg-5 controls">
                        <textarea name="address"  id="address" rows="5" class="form-control">
                        <?php if(Input::old('address')) {?>
                                {{Input::old('address')}} 
                            <?php } elseif($errors->first('address')) {?>       
                                {{Input::old('address')}}
                            <?php } elseif(!empty($address)) {?>
                                {{$address}}
                            <?php } ?>
                        </textarea>
                        
                          <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.latitude') }}</label>
                            <div class="col-sm-3 col-lg-3 controls">
                                <input type="text" name="lat" id="lat" class="form-control"
                                 <?php if(Input::old('lat')) {?>value="{{Input::old('lat')}}"<?php } elseif($errors->first('lat')) {?> value="{{Input::old('lat')}}"<?php } elseif(!empty($lat)) {?> value="{{$lat}}"<?php } ?>/>
                                 {!! $errors->first('lat', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                             <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.longitude') }}</label>
                            <div class="col-sm-3 col-lg-3 controls">
                                <input type="text" name="lng" id="lng" class="form-control"  <?php if(Input::old('lng')) {?>value="{{Input::old('lng')}}"<?php } elseif($errors->first('lng')) {?> value="{{Input::old('lng')}}"<?php } elseif(!empty($lng)) {?> value="{{$lng}}"<?php } ?>/>
                                 {!! $errors->first('lng', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        {!! $errors->first('address', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                        </div>
                    <!-- address field ends here -->
                    
                    <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.phone') }}</label>
                        <div class="col-sm-5 col-lg-5 controls">
                            <input type="text" name="phone" class="form-control" <?php if(Input::old('phone')) {?>value="{{Input::old('phone')}}"<?php } elseif($errors->first('phone')) {?> value="{{Input::old('phone')}}"<?php } elseif(!empty($phone)) {?> value="{{$phone}}"<?php } ?>/>
                            {!! $errors->first('phone', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>

                    <!-- mobile number-->
                    <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.mobile_no') }}</label>
                        <div class="col-sm-5 col-lg-5 controls">
                            <input type="text" name="mobile_no" class="form-control" <?php if(Input::old('mobile_no')) {?>value="{{Input::old('mobile_no')}}"<?php } elseif($errors->first('mobile_no')) {?> value="{{Input::old('mobile_no')}}"<?php } elseif(!empty($mobile_no)) {?> value="{{$mobile_no}}"<?php } ?>/>
                            {!! $errors->first('mobile_no', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>
                    <!-- end of mobile number -->

                    @if(Input::old('choice_count'))
                    <?php $s_count=Input::old('choice_count');?>
                        @for($si=0;$si<$s_count;$si++)
                            <?php $social_value=Input::old('social_media'.$si); $url_value=Input::old('url'.$si); $social_name='social_media'.$si; $url_name='url'.$si;?>
                            <div class="form-group">
                                <label class="col-sm-3 col-lg-2 control-label">@if($si == 0) {{ trans('admin/sitesetting.social_media_url') }} @else @endif</label>
                                <div class="col-lg-10 controls">
                                    <div class="row">
                                        <div class="col-lg-5">
                                            <input type="text" name="{{$social_name}}" placeholder="Name" class="form-control" value="{{$social_value}}">
                                            {!! $errors->first($social_name, '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="{{$url_name}}" placeholder="Url" class="form-control" value="{{$url_value}}">
                                            {!! $errors->first($url_name, '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                </div>
                            </div>
                        @endfor
                        <div class="form-group" id="choice_div">
                            <label class="col-sm-3 col-lg-2 control-label"></label>
                            <div class="col-lg-10 controls">
                                <input type="hidden" id="choice_count" name="choice_count" value="{{ Input::old('choice_count') }}">
                                <a href="#" id="add-choice">
                                    <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button> {{ trans('admin/sitesetting.add_more_social_media_url') }}
                                </a>
                            </div>
                        </div>
                    @elseif(!empty($social_media) && (count($social_media) >= 1))
                        <?php $si=0; ?>
                        @foreach($social_media as $key => $value)
                            <div class="form-group">
                                <label class="col-sm-3 col-lg-2 control-label">@if($si == 0) {{ trans('admin/sitesetting.social_media_url') }} @else @endif</label>
                                <div class="col-lg-10 controls">
                                    <div class="row">
                                        <div class="col-lg-5">
                                            <input type="text" name="social_media{{$si}}" placeholder="Name" class="form-control" value="{{$key}}">
                                        </div>
                                        <div class="col-lg-6">
                                            <input type="text" name="url{{$si}}" placeholder="Url" class="form-control" value="{{$value}}">
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <?php $si=$si+1; ?>
                        @endforeach
                        <div class="form-group" id="choice_div">
                            <label class="col-sm-3 col-lg-2 control-label"></label>
                            <div class="col-lg-10 controls">
                                <input type="hidden" id="choice_count" name="choice_count" value="{{ Input::old('choice_count', count($social_media)) }}">
                                <a href="#" id="add-choice">
                                    <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button> {{ trans('admin/sitesetting.add_more_social_media_url') }}
                                </a>
                            </div>
                        </div>
                    @else
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.social_media_url') }}</label>
                            <div class="col-lg-10 controls">
                                <div class="row">
                                    <div class="col-lg-5">
                                        <input type="text" name="social_media0" placeholder="Name" class="form-control">
                                        <span class="help-inline"> {{ trans('admin/sitesetting.eg_facebook') }}</span><br />
                                    </div>
                                    <div class="col-lg-6">
                                        <input type="text" name="url0" placeholder="Url" class="form-control">
                                        <span class="help-inline">{{ trans('admin/sitesetting.facebook_link') }}</span><br />
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group" id="choice_div">
                            <label class="col-sm-3 col-lg-2 control-label"></label>
                            <div class="col-lg-10 controls">
                                <input type="hidden" id="choice_count" name="choice_count" value="{{ Input::old('choice_count', 1) }}">
                                <a href="#" id="add-choice">
                                    <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button>{{ trans('admin/sitesetting.add_more_social_media_url') }}
                                </a>
                            </div>
                        </div>
                    @endif
                    
                    @if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING))
                        <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                <button type="submit" class="btn btn-info text-right">{{ trans('admin/sitesetting.update') }} </button>
                                <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                            </div>
                        </div>
                    @endif
                </form>
            </div>
            <!-- #rd party integration -->
            <div class="tab-pane row" id="other_integrations">
                <div class="col-md-12">
                    
                
                @if($isPlaylyfeEnabled)
                <div class="box box-black">
                    <div class="box-title">
                        <h3>{{ trans('admin/sitesetting.gaming_settings') }}</h3>
                        <div class="box-tool">
                            <a data-action="collapse" href="#"><i class="fa fa-chevron-down"></i></a>
                        </div>
                    </div>
                    <div class="box-content">
                        <?php
                          $pl = App::make("App\Services\Playlyfe\IPlaylyfeService");
                          $actions = $pl->getActions();
                          foreach($actions as $action) {
                            switch($action["id"]) {
                              case "signup":
                                $signup = $pl->getPoints($action);
                                break;
                              case "login":
                                $login = $pl->getPoints($action);
                                break;
                              case "favorite":
                                $favorite = $pl->getPoints($action);
                                break;
                              case "quiz_completed":
                                foreach($action['rules'] as $rule) {
                                  if ($rule['requires']['type'] == 'and') {
                                    switch($rule['requires']['expression'][0]['context']['rhs']) {
                                      case '60':
                                        $quiz_60 = $rule['rewards'][0]['value'];
                                        break;
                                      case '70':
                                        $quiz_70 = $rule['rewards'][0]['value'];
                                        break;
                                      case '80':
                                        $quiz_80 = $rule['rewards'][0]['value'];
                                        break;
                                      case '90':
                                        $quiz_90 = $rule['rewards'][0]['value'];
                                        break;
                                      case '100':
                                        $quiz_100 = $rule['rewards'][0]['value'];
                                        break;
                                    }
                                  }
                                  if ($rule['requires']['type'] == 'var') {
                                    if ($rule['requires']['context']['rhs'] == '100') {
                                      $quiz_100 = $rule['rewards'][0]['value'];
                                    }
                                  }
                                }
                                break;
                              case "question_asked":
                                $question_asked = $pl->getPoints($action);
                                break;
                              case "question_marked_as_faq":
                                $question_marked_as_faq = $pl->getPoints($action);
                                break;
                              case "content_viewed":
                                $content_viewed = $pl->getPoints($action);
                                break;
                              case "post_completed":
                                $post_completed = $pl->getPoints($action);
                                break;
                            }
                          }
                        ?>
                        <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
                        <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
                        <link rel="stylesheet" href="{{ URL::asset('playlyfe/app.css')}}">
                        <script src="{{ URL::asset('playlyfe/app.js')}}"></script>
                        <div class="modal fade" id="resetModal">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <div class="row custom-box">
                                  <div class="col-md-12">
                                      <div class="box">
                                          <div class="box-title">
                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                              <h3><i class="icon-file"></i>{{ trans('admin/sitesetting.reset_all_user_scores') }}!</h3>                                                 
                                          </div>
                                      </div>
                                  </div>
                                </div>
                              </div>
                              <div class="modal-body">
                                <div class="row custom-box">
                                  <div class="col-md-12">
                                    <div class="box">
                                      <div class="box-title" style="height:100px;background-color:white;">
                                        <h5>{{ trans('admin/sitesetting.are_you_sure_to_reset_user_scores') }}</h5>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button id="reset_all" type="button" class="btn btn-primary">{{ trans('admin/sitesetting.reset') }}</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('admin/sitesetting.close') }}</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="modal fade" id="exportModal">
                          <div class="modal-dialog">
                            <div class="modal-content">
                              <div class="modal-header">
                                <div class="row custom-box">
                                  <div class="col-md-12">
                                      <div class="box">
                                          <div class="box-title">
                                              <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
                                              <h3><i class="icon-file"></i>{{ trans('admin/sitesetting.export_all_users_to_playlyfe') }}</h3>                                                 
                                          </div>
                                      </div>
                                  </div>
                                </div>
                              </div>
                              <div class="modal-body">
                                <div class="row custom-box">
                                  <div class="col-md-12">
                                    <div class="box">
                                      <div class="box-title" style="height:100px;background-color:white;">
                                        <h5>{{ trans('admin/sitesetting.export_all_userif_gamification_is_enabled') }}</h5>
                                      </div>
                                    </div>
                                  </div>
                                </div>
                              </div>
                              <div class="modal-footer">
                                <button id="export_all" type="button" class="btn btn-primary">{{ trans('admin/sitesetting.sync') }}</button>
                                <button type="button" class="btn btn-default" data-dismiss="modal">{{ trans('admin/sitesetting.close') }}</button>
                              </div>
                            </div>
                          </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <h3></h3>
                                        <ul class="nav nav-tabs" style="top:0;">
                                            <li class="active"><a href="#general_playlyfe" data-toggle="tab">{{ trans('admin/sitesetting.general') }}</a></li>
                                            <li><a href="#points" data-toggle="tab">{{ trans('admin/sitesetting.points') }}</a></li>
                                        </ul>
                                    </div>

                                    <div class="box-content">
                                      <div class="tab-content">
                                        <div class="tab-pane active" id="general_playlyfe">
                                          <form class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">             
                                              <div class="form-group">
                                                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.sync_all_user_to_playlyfe') }}</label>
                                                <div class="col-sm-9 col-lg-10 controls">
                                                  <div id="export_btn" class="btn btn-info">{{ trans('admin/sitesetting.sync') }}</div><br />
                                                </div>
                                              </div>
                                              <div class="form-group">
                                                  <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.reset_all_users_of_game') }}</label>
                                                  <div class="col-sm-9 col-lg-10 controls">
                                                    <div id="reset_btn" class="btn btn-info">{{ trans('admin/sitesetting.reset') }}</div><br />
                                                    <span class="help-inline">{{ trans('admin/sitesetting.warning_this_cant_be_undone') }}</span><br />
                                                  </div>
                                              </div>
                                          </form>
                                        </div>
                                        <div class="tab-pane" id="points">
                                          <form action="{{URL::to('cp/sitesetting/gamification-settings')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">   
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_for_signup') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="signup" class="form-control" value="{{$signup}}"/><br />
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_that_user_can_earn_for_login') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="login" class="form-control" value="{{$login}}"/><br />
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_that_user_can_earn_for_favoriting') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="favorite" class="form-control" value="{{$favorite}}"/><br />
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_that_user_can_earn_for_view_content') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="content_viewed" class="form-control" value="{{$content_viewed}}"/><br />
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_that_user_can_earn_for_asking_question') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="question_asked" class="form-control" value="{{$question_asked}}"/><br />
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_that_user_can_earn_when_ques_marked_as_faq') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="question_marked_as_faq" class="form-control" value="{{$question_marked_as_faq}}"/><br /><br />
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_that_user_can_earn_for_completing_post') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="post_completed" class="form-control" value="{{$post_completed}}"/><br /><br />
                                              </div>
                                            </div>
                                            <div class="form-group">
                                              <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.points_that_user_can_earn_for_completing_quiz') }}</label>
                                              <div class="col-sm-9 col-lg-10 controls">
                                                <input type="text" name="quiz_100" class="form-control" value="{{$quiz_100}}"/>
                                                <span class="help-inline">{{ trans('admin/sitesetting.when_user_score_is_100') }} </span><br /><br />
                                                <input type="text" name="quiz_90" class="form-control" value="{{$quiz_90}}"/>
                                                <span class="help-inline">{{ trans('admin/sitesetting.when_user_score_is_greater_than_90%') }} </span><br /><br />
                                                <input type="text" name="quiz_80" class="form-control" value="{{$quiz_80}}"/>
                                                <span class="help-inline">{{ trans('admin/sitesetting.when_user_score_is_greater_than_80%') }} </span><br /><br />
                                                <input type="text" name="quiz_70" class="form-control" value="{{$quiz_70}}"/>
                                                <span class="help-inline">{{ trans('admin/sitesetting.when_user_score_is_greater_than_70%') }} </span><br /><br />
                                                <input type="text" name="quiz_60" class="form-control" value="{{$quiz_60}}"/>
                                                <span class="help-inline">{{ trans('admin/sitesetting.when_user_score_is_greater_than_60%') }} </span><br /><br />
                                             </div>
                                            </div>
                                            @if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING))
                                              <div class="form-group">
                                                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                                  <button type="submit" class="btn btn-info text-right">{{ trans('admin/sitesetting.update') }} </button>
                                                  <a href="{{URL::to('/cp/sitesetting/')}}" >
                                                        <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button>
                                                    </a> 
                                                </div>
                                              </div>
                                            @endif
                                          </form>
                                        </div>
                                      </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                @endif
                <div class="box box-black">
                    <div class="box-title">
                        <h3>{{ trans('admin/sitesetting.view_settings') }}</h3>
                    </div>
                    <div class="box-content" >
                        <!-- Viewer -->
                        <div class="tab-pane" id="viewer">
                            <form action="{{URL::to('cp/sitesetting/update/viewer')}}" method="post" enctype="multipart/form-data" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">             
                               <div class="form-group">
                                    <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.doc_viewer') }}</label>
                                    <div class="col-sm-9 col-lg-10 controls" id='box_viewer'>
                                        <input type="radio" name="box_view" value='on' <?php if($box_view == 'on') echo "checked"; ?> /> On
                                        <input type="radio" name="box_view" value='off' <?php if($box_view == 'off') echo "checked"; ?> /> Off
                                        {!! $errors->first('box_view', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group box_param">
                                    <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.enable_download_in_view') }}</label>
                                    <div class="col-sm-9 col-lg-10 controls">
                                        <input type="radio" name="file_download" value='true' <?php if($file_download == true) echo "checked"; ?>/> On
                                        <input type="radio" name="file_download" value='false'  <?php if($file_download == false) echo "checked"; ?>/> Off
                                        {!! $errors->first('file_download', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group box_param">
                                    <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.enable_text_selection_in_viewer') }}</label>
                                    <div class="col-sm-9 col-lg-10 controls">
                                        <input type="radio" name="text_selectable" value='true' <?php if($text_selectable == true) echo "checked"; ?>/> On
                                        <input type="radio" name="text_selectable" value='false' <?php if($text_selectable == false) echo "checked"; ?> /> Off
                                        {!! $errors->first('text_selectable', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group box_param">
                                    <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.expiry_duration') }}</label>
                                    <div class="col-sm-9 col-lg-10 controls">
                                        <input type="number" name="session_expires_at" value="{{ $session_expires_at }}" />
                                        {!! $errors->first('session_expires_at', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group box_param">
                                    <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.on_doc_view_failure') }}</label>
                                    <div class="col-sm-9 col-lg-10 controls">
                                        <input type="radio" name="box_failure" value='show_error' <?php if($box_failure == 'show_error') echo "checked"; ?>/> Show Error Msg 
                                        <input type="radio" name="box_failure" value='download_link'  <?php if($box_failure == 'download_link') echo "checked"; ?>/> Show Download Link
                                        {!! $errors->first('box_failure', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                        <button type="submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
                                        <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    <!-- End -->
                    </div>                    
                </div>
                <div class="box box-black">
                    <div class="box-title">
                        <h3>{{ trans('admin/sitesetting.mathml_setting') }}</h3>
                    </div>
                    <div class="box-content" >
                        <!-- Viewer -->
                        <div class="tab-pane" id="mathml">
                            <form action="{{URL::to('cp/sitesetting/update/mathml')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="mathml-form">             
                               <div class="form-group">
                                    <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.mathml_editor') }}</label>
                                    <div class="col-sm-9 col-lg-10 controls">
                                        <input  type="radio" name="mathml_editor" value='on' <?php if($mathml_editor == 'on') echo "checked"; ?> /> On
                                        <input type="radio" name="mathml_editor" value='off' <?php if($mathml_editor == 'off') echo "checked"; ?> /> Off
                                        <div>
                                            <span class="help-block error" id="mathml_editor_error"></span>
                                        </div>
                                    </div>                                      
                                </div>                                                               
                                <div class="form-group">
                                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                        <button type="submit" id="mathml-submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
                                        <a href="{{URL::to('/cp/sitesetting/')}}" >
                                        <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                                    </div>
<!--                                     <label>Please make sure you have given valid folder path with given permission for web server to write.</label>
 -->                                </div>
                            </form>
                        </div>
                    <!-- End -->
                    </div>
                </div>
                <div class="box box-black">
                    <div class="box-title">
                        <h3>{{ trans('admin/sitesetting.bank_details') }}</h3>
                    </div>
                    <div class="box-content" >
                        <div class="tab-pane" id="bankdetails">
                            <form action="{{URL::to('cp/sitesetting/update/bankdetails')}}" method="post" >             
                                <div class="form-group ">
                                    <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.bank_details') }}</label>
                                    <div class="col-sm-9 col-lg-10 controls" >
                                       <textarea name="bank_details" class="ckeditor">{{ $bank_details }}</textarea>
                                        {!! $errors->first('bank_details', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="form-group">
                                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6"><br>
                                        <button type="submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
                                        <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                </div>
            </div>
            <!--End-->
            

            <div class="tab-pane" id="search">
                <form action="{{URL::to('cp/sitesetting/update/search')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                            <div class="form-group">
                                <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.advance_search') }} </label>
                                <div class="col-sm-6 col-lg-4 controls">
                                   <select class="form-control chosen"id="search_results_per_page " name="search_results_per_page">   
                                <option value="10" <?php echo $search_results_per_page==10?"selected":"";?>>10</option>
                                <option value="15" <?php echo $search_results_per_page==15?"selected":"";?>>15</option>
                                <option value="20" <?php echo $search_results_per_page==20?"selected":"";?>>20</option>
                            </select>
                                </div>
                            </div>
                <?php
                    if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING) == true)
                    {
                ?>
                <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                                <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                            </div>
                 </div>
                 <?php }?>
                </form>
            </div>
                
            <div class="tab-pane" id="notifications_announcements">
                <div class="row">
                    <div class="col-md-12">
                        <ul class="nav nav-tabs" style=" background: #b6d1f2;">
                            <li class="active"><a href="#general_notification" data-toggle="tab">{{ trans('admin/sitesetting.general') }}</a></li>
                            <li ><a href="#announcement_notification" data-toggle="tab">{{ trans('admin/sitesetting.announcement') }}</a></li>
                            <li ><a href="#quiz_reminder" data-toggle="tab">{{ trans('admin/sitesetting.quiz_reminders') }}</a></li>
                        </ul>
                    
                    <div class="tab-content">
                     <!-- 1st tab -->
                        <div class="tab-pane active in" id="general_notification">
                        <form action="{{URL::to('cp/sitesetting/update/notifications_announcements')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                      
                        <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.no_of_notif_and_Annouce_to_be_disp_popup') }}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                               <select class="form-control chosen" id="no_of_announce_notifi_popup" name="no_of_announce_notifi_popup">   
                                    <option value="5" <?php echo $ann_displayed_in_popup==5?"selected":"";?>>5</option>
                                    <option value="10" <?php echo $ann_displayed_in_popup==10?"selected":"";?>>10</option>
                                    <option value="15" <?php echo $ann_displayed_in_popup==15?"selected":"";?>>15</option>
                                </select>
                            </div>
                        </div>
                    
                    <?php
                        if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING))
                        {
                    ?>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                            <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                        </div>
                     </div>
                    <?php }?>
                </form>
            </div><!--  div tab-pane -->
             <!--  1st tab ends here-->
            
        <!--  2nd tab -->
             <div class="tab-pane" id="announcement_notification">
               <form action="{{URL::to('cp/sitesetting/update/notifications_announcements')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.flush_notification_days_limit') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                           <select class="form-control chosen" id="flush_notifications_days_limit" name="flush_notifications_days_limit">   
                                <option value="15" <?php echo $flush_notifications_days_limit==15?"selected":"";?>>15</option>
                                <option value="30" <?php echo $flush_notifications_days_limit==30?"selected":"";?>>30</option>
                                <option value="45" <?php echo $flush_notifications_days_limit==45?"selected":"";?>>45</option>
                            </select>
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">{{ trans('admin/sitesetting.default_expiry_for_announce') }}</label>
                        <div class="col-sm-6 col-lg-4 controls">
                           <select class="form-control chosen" id="ann_expire_date" name="ann_expire_date">   
                                <option value="7" <?php echo $ann_expire_date==7?"selected":"";?>>7 Days</option>
                                <option value="15" <?php echo $ann_expire_date==15?"selected":"";?>>15 Days</option>
                                <option value="30" <?php echo $ann_expire_date==30?"selected":"";?>>30 Days</option>
                            </select>
                        </div>
                    </div>
                         
                    <?php
                        if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING))
                        {
                    ?>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                            <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                        </div>
                     </div>
                    <?php }?>
               </form>
             </div><!--  div tab-pane -->
            <!--  2nd tab ends here-->
            
            <!--  3rd tab -->
            <div class="tab-pane" id="quiz_reminder">
                @include('admin.theme.sitesettings.quizreminder', ['quiz_reminders' => $quiz_reminders])
            </div>
            <!--  3rd tab ends here-->
   
        </div><!--  div tab-content -->
        </div> <!--  div col-md-12 -->
        </div>    <!--  div row -->        
    </div> <!-- div tab-pane -->
               
                
            <!-- Socialite Begin -->
            <div class="tab-pane" id="socialite">
                @include('admin.theme.sitesettings.socialite', ['socialite_info'=>$socialite_info])
            </div>
            <!-- Socialite Ends -->

            <!-- Socialite Begin -->
            <div class="tab-pane" id="homepage">
                @include('admin.theme.sitesettings.homepage', ['homepage_info'=>$homepage_info])
            </div> 
            <div class="tab-pane" id="certificates">
                <form action="{{URL::to('cp/sitesetting/update/certificates')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="certificates-form">             
                   <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.display_certificates')}}</label>
                        <div class="col-sm-9 col-lg-10 controls">
                            <input type="radio" name="visibility" value='true' @if($certificates_visibility == 'true') {{"checked"}} @endif  /> {{ trans('admin/sitesetting.certificates_show')}}
                            <input type="radio" name="visibility" value='false' @if($certificates_visibility == 'false') {{"checked"}} @endif /> {{ trans('admin/sitesetting.certificates_hide')}}
                            <div>
                                <span class="help-block error" id="certificates_error"></span>
                            </div>                            
                        </div>                                      
                    </div>                                                               
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                            <button type="submit" id="mathml-submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" >
                            <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                        </div>
                    </div>
                </form>
            </div>           
            <!-- Socialite Ends -->

            <div class="tab-pane" id="usersettings">
                <form action="{{URL::to('cp/sitesetting/update/UserSetting')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" >
                  <div class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/sitesetting.nda_acceptance') }}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                                <input type="checkbox"  name="nda" <?php echo $nda_acceptance=="on"?"checked":"";?> >
                            </div>
                            <div class="form-group">
                                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                    <button type="submit" id="nda_submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
                                </div>
                            </div>   
                    </div>
                </form>
            </div> 
            <!-- LHS MENU Settings Begin -->
            <div class="tab-pane" id="lhs_menu_settings">
                <form action="{{URL::to('cp/sitesetting/update/LHSMenuSettings')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label"> {{ trans('admin/sitesetting.programs') }} </label>
                        <div class="col-sm-6 col-lg-4 controls">
                           <input type="checkbox" value="on" name="programs"  <?php echo $lhs_menu_settings_programs=="on"?"checked":"";?> >
                            
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label"> {{ trans('admin/sitesetting.my_activity') }} </label>
                        <div class="col-sm-6 col-lg-4 controls">
                           <input type="checkbox" value="on" name="my_activity"  <?php echo $lhs_menu_settings_my_activity=="on"?"checked":"";?> >
                            
                        </div>
                    </div>
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                            <button type="submit" id="mathml-submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" >
                            <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                        </div>
                    </div>
                </form>
            </div>
            <!-- LHS MENU Settings Ends -->
            
            <!-- Webex storage Settings Begin -->
            <div class="tab-pane" id="webexhost-list">
                <form action="{{URL::to('cp/sitesetting/update/WebexHostStorage')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" >
                  <div class="form-group">
                    @foreach($host_list as $each_host)
                        <label class="col-sm-6 col-lg-6 control-label">{{ $each_host['name'] }}&nbsp;&nbsp;</label>
                            <div class="col-sm-6 col-lg-4 controls">
                                <input type="number" class="webexStorageLimit" min="1" max="200" name="webex_host_list[ {{$each_host['webex_host_id']}} ]" value="{{ $each_host['storage_limit'] }}">&nbsp;
                                {{ trans('admin/sitesetting.gigabyes') }}
                            </div>
                    @endforeach
                    <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                            <button type="submit" id="webex_submit" class="btn btn-info text-right" >{{ trans('admin/sitesetting.update') }} </button>
                        </div>
                    </div>   
                    </div>
                </form>
            </div> 
        <!-- Webex storage Settings ends here -->
        
                    <!-- <div class="tab-pane" id="library">
                        <p><strong>Library</strong> -- Duis autem vel eum iriure dolor in hendrerit in vulputate velit esse molestie consequat, vel illum dolore eu feugiat nulla facilisis at vero eros et accumsan et iusto odio dignissim qui blandit praesent luptatum zzril delenit augue duis dolore te feugait nulla facilisi. Lorem ipsum dolor sit amet, consectetuer adipiscing elit, sed diam nonummy nibh euismod tincidunt ut laoreet dolore magna aliquam erat volutpat.ut laoreet dolore magna ut laoreet dolore magna. ut laoreet dolore magna. ut laoreet dolore magna.</p>
                    </div> -->
            <!-- <div class="tab-pane" id="event">
                <form action="{{URL::to('cp/sitesetting/update/event')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                      
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Events</label>
                        <div class="col-sm-6 col-lg-4 controls">
                           <select class="form-control chosen" id="event_key" name="event_key">   
                                <option value="on" <?php echo $event_key=="on"?"selected":"";?>>On</option>
                                <option value="off" <?php echo $event_key=="off"?"selected":"";?>>Off</option>
                            </select>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Assign Quiz to Content Feed
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <select class="form-control chosen" id="event_assign_to_cf" name="event_assign_to_cf">   
                                <option value="on" <?php echo $event_assign_to_cf=="on"?"selected":"";?>>On</option>
                                <option value="off" <?php echo $event_assign_to_cf=="off"?"selected":"";?>>Off</option>
                            </select>
                        </div>
                </div>
                 <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Open Time
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <select class="form-control chosen" id="event_open_time" name="event_open_time">   
                                <option value="15" <?php echo $event_assign_to_cf=="15"?"selected":"";?>>15</option>
                                <option value="30" <?php echo $event_assign_to_cf=="30"?"selected":"";?>>30</option>
                                <option value="45" <?php echo $event_assign_to_cf=="45"?"selected":"";?>>45</option>
                                <option value="60" <?php echo $event_assign_to_cf=="60"?"selected":"";?>>60</option>
                                <option value="90" <?php echo $event_assign_to_cf=="90"?"selected":"";?>>90</option>
                                <option value="120" <?php echo $event_assign_to_cf=="120"?"selected":"";?>>120</option>
                                <option value="150" <?php echo $event_assign_to_cf=="150"?"selected":"";?>>150</option>
                                <option value="240" <?php echo $event_assign_to_cf=="240"?"selected":"";?>>240</option>
                            </select>
                            <span class="help-inline">Min</span>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Service Layer URL
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input id="" class="form-control" placeholder="<?php echo $event_service_lay_url;?>" type="text" name="event_service_lay_url" value="<?php echo $event_service_lay_url;?>">
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">User Name
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input id="" class="form-control" placeholder="<?php echo $event_username;?>" type="text" name="event_username" value="<?php echo $event_username;?>">
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Password
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input id="password" class="form-control" type="password" placeholder="<?php echo $event_password;?>" name="event_password" value="<?php echo $event_password;?>">
                          <span id="numberinputspan"></span>
                        </div>
                </div>
                <div class="form-group" id="conformpass_f_id">
                        <label class="col-sm-6 col-lg-6 control-label">Conform Password
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input id="conformpass" class="form-control"  placeholder="Re type Password" type="password" name="">
                          <span id="conformpassspan" class="danger" style="color:red"></span>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Application Key
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input id="" class="form-control" type="text" name="event_app_key" placeholder="<?php echo $event_app_key;?>" value="<?php echo $event_app_key;?>">
                          <span id="numberinputspan"></span>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Duration
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input id="numberinput" class="form-control"  placeholder="<?php echo $event_duration;?>" type="text" name="event_duration" value="<?php echo $event_duration;?>">
                          <span class="help-inline">Min</span>
                        </div>
                </div>
                <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                                <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                            </div>
                 </div>
                </form>
            </div>
            <div class="tab-pane" id="assessment">
                <form action="{{URL::to('cp/sitesetting/update/assessment')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                      
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Quiz</label>
                        <div class="col-sm-6 col-lg-4 controls">
                           <select class="form-control chosen" id="assessment_key" name="assessment_key">   
                                <option value="on" <?php echo $assessment_key=="on"?"selected":"";?>>On</option>
                                <option value="off" <?php echo $assessment_key=="off"?"selected":"";?>>Off</option>
                            </select>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Assign Quiz to Content Feed
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <select class="form-control chosen" id="assessment_assign_quz_to_cf" name="assessment_assign_quz_to_cf">   
                                <option value="on" <?php echo $assessment_assign_quz_to_cf=="on"?"selected":"";?>>On</option>
                                <option value="off" <?php echo $assessment_assign_quz_to_cf=="off"?"selected":"";?>>Off</option>
                            </select>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Min no-of Question per Assessment
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <select class="form-control chosen" id="assessment_min_no_of_qus" name="assessment_min_no_of_qus">   
                                <option value="1" <?php echo $assessment_min_no_of_qus==1?"selected":"";?>>1</option>
                                <option value="2" <?php echo $assessment_min_no_of_qus==2?"selected":"";?>>2</option>
                                <option value="3" <?php echo $assessment_min_no_of_qus==3?"selected":"";?>>3</option>
                                <option value="4" <?php echo $assessment_min_no_of_qus==4?"selected":"";?>>4</option>
                                <option value="5" <?php echo $assessment_min_no_of_qus==5?"selected":"";?>>5</option>
                                <option value="6" <?php echo $assessment_min_no_of_qus==6?"selected":"";?>>6</option>
                                <option value="7" <?php echo $assessment_min_no_of_qus==7?"selected":"";?>>7</option>
                                <option value="8" <?php echo $assessment_min_no_of_qus==8?"selected":"";?>>8</option>
                                <option value="9" <?php echo $assessment_min_no_of_qus==9?"selected":"";?>>9</option>
                                <option value="10" <?php echo $assessment_min_no_of_qus==10?"selected":"";?>>10</option>
                            </select>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Allowed Attempts
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <select class="form-control chosen" id="assessment_allow_attempts" name="assessment_allow_attempts">   
                                <option value="0" <?php echo $assessment_allow_attempts==0?"selected":"";?>>0</option>
                                <option value="1" <?php echo $assessment_allow_attempts==1?"selected":"";?>>1</option>
                                <option value="2" <?php echo $assessment_allow_attempts==2?"selected":"";?>>2</option>
                                <option value="3" <?php echo $assessment_allow_attempts==3?"selected":"";?>>3</option>
                                <option value="4" <?php echo $assessment_allow_attempts==4?"selected":"";?>>4</option>
                                <option value="5" <?php echo $assessment_allow_attempts==5?"selected":"";?>>5</option>
                                <option value="6" <?php echo $assessment_allow_attempts==6?"selected":"";?>>6</option>
                                <option value="7" <?php echo $assessment_allow_attempts==7?"selected":"";?>>7</option>
                                <option value="8" <?php echo $assessment_allow_attempts==8?"selected":"";?>>8</option>
                                <option value="9" <?php echo $assessment_allow_attempts==9?"selected":"";?>>9</option>
                                <option value="10" <?php echo $assessment_allow_attempts==10?"selected":"";?>>10</option>
                            </select>
                        </div>
                </div>
                <div class="form-group">
                        <label class="col-sm-6 col-lg-6 control-label">Default Marks per Question
                        </label>
                        <div class="col-sm-6 col-lg-4 controls">
                          <input id="numberinput_dmpq" class="form-control"  type="text" name="assessment_defult_marks_for_qus" value="<?php echo $assessment_defult_marks_for_qus;?>">
                          <span id="numberinputspan_dmpq"></span>
                        </div>
                </div>
                <div class="form-group">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                                <a href="{{URL::to('/cp/sitesetting/')}}" ><button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button></a>
                            </div>
                 </div>
                </form>
            </div> -->
                </div>
            </div>
        </div>
    </div>
                    
</div>

<div class="custom-back-drop">    
</div>

<script type="text/javascript">
    jQuery(document).ready(function ($) {
        // Tab implementation
        var hash_values = window.location.hash;
        var active_tabs = hash_values.split(";");
        if(active_tabs[0] != "") {
            $selector_one = '[href="'+ active_tabs[0] +'"]';
        } else {
            $selector_one ='[href="#general"]';
        }
        $($selector_one).tab('show');

        if(active_tabs[1] != "") {
            $selector_two = '[href="'+ active_tabs[1] +'"]';
            $($selector_two).tab('show');
        }
        $('html, body').animate({scrollTop:0}, 'fast');
        //Tab implementation end 

        $('#tabs').tab();
         $("#conformpass_f_id").hide();

        $("#numberinput").keydown(function(event){
            var num_val = $(this).val();
            if((event.keyCode < 48 || event.keyCode > 57) && event.keyCode !=8 ){
                event.preventDefault();
            } 
        });
         $("#numberinput_dmpq").keydown(function(event){
            var num_val = $(this).val();
            if((event.keyCode < 48 || event.keyCode > 57) && event.keyCode !=8 ){
                event.preventDefault();
            } 
        });
        $("#conformpass").focusout(function(){
            if($(this).val() != $("#password").val())
            {
                $("#conformpassspan").text("Password miss match");
                $("#conformpassspan").show();
            }else{
                $("#conformpassspan").text("");
                $("#conformpassspan").hide();
            }
        });
        $("#password").focusout(function(){
            $("#conformpass_f_id").show();
        });
    });  
</script> 

<script type="text/javascript">
    $(function(){
        $('#add-choice').click( function(){
            var count = parseInt($("input[name='choice_count']").val());
            var html = '';
            for(i=count; i<count+1; i++)
            {
                html += '<div class="form-group"><label class="col-sm-3 col-lg-2 control-label"></label><div class="col-lg-10 controls"><div class="row"><div class="col-lg-5"><input type="text" name="social_media'+i+'" placeholder="Name" class="form-control"></div><div class="col-lg-6"><input type="text" name="url'+i+'" placeholder="Url" class="form-control"></div></div></div></div>';
            }
            $(html).insertBefore('#choice_div');
            $("input[name='choice_count']").val(count+1);
        });
    });
</script>
<script type="text/javascript">
    $(document).ready(function(){
        $('#export_btn').click(function(){
            $('#exportModal').modal();
        });

        $('#reset_btn').click(function(){
            $('#resetModal').modal();
        });

        $("#export_all").click(function(){
            var exportButton = $(this);
            $(".custom-back-drop").css({
                "display" : "block"
            });
            exportBtnText = exportButton.html();
            exportButton.html("Please wait....");

            var xmlHttpRequest = $.ajax({
              url: "{{ URL::to("pl/export") }}",
              type : "post"
            });

            xmlHttpRequest.done(function(response, textStatus, jqXHR){
                alert("Users have been successfully synced in playlyfe server.");
            });

            xmlHttpRequest.fail(function(jqXHR, textStatus, errorThrown){
                alert("Something went wrong! please try later.");
            });

            xmlHttpRequest.always(function(){
                $(".custom-back-drop").css({
                    "display" : "none"
               });

               $("#exportModal").modal("hide");

               exportButton.html(exportBtnText);
            });
        });

        $("#reset_all").click(function(){
            var resetButton = $(this);
            $(".custom-back-drop").css({
                "display" : "block"
            });
            resetBtnText = resetButton.html();
            resetButton.html("Please wait....");

            var xmlHttpRequest = $.ajax({
              url: "{{ URL::to("pl/reset") }}",
              type : "post"
            });

            xmlHttpRequest.done(function(response, textStatus, jqXHR){
                alert("Users have been reset successfully.");
            });

            xmlHttpRequest.fail(function(jqXHR, textStatus, errorThrown){
                alert("Something went wrong! please try later.");
            });

            xmlHttpRequest.always(function(){
                $(".custom-back-drop").css({
                    "display" : "none"
                });

                $("#resetModal").modal("hide");
                resetButton.html(resetBtnText);
            });
        });
        setTimeout(function(){$('.alert').alert('close');},5000);
    });
    $('#mathml-form').submit(function(event){
        var self = $(this);
        var data = self.serialize();
        var mathml = $.ajax({
            type: 'POST',
            url: self.attr('action'),
            data: self.serialize(),
            dataType: 'json',            
        });
        mathml.done(function(response){
            $('.error').hide();
            if(response.status == 'validation'){
                $.each(response.errors, function(key, value){
                    self.find('#'+key+'_error').html(value).addClass('required').show().parent().addClass('has-error');
                });
            }
            else if(response.status == 'success'){
                $('div.alert').remove();
                $('.page-title').append('<div class="alert alert-success">'+response.message+'</div>');
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                setTimeout(function(){
                    $('div.alert').remove();
                }, 3000);
            }
        });
        mathml.fail(function(response){
            console.log(response);
        });
        event.preventDefault();
    });
    $('#certificates-form').submit(function(event){
        var self = $(this);
        var data = self.serialize();
        var certificates = $.ajax({
            type: 'POST',
            url: self.attr('action'),
            data: self.serialize(),
            dataType: 'json',            
        });
        certificates.done(function(response){
            $('.error').hide();
            if(response.status == 'validation'){
                $.each(response.errors, function(key, value){
                    self.find('#'+key+'_error').html(value).addClass('required').show().parent().addClass('has-error');
                });
            }
            else if(response.status == 'success'){
                $('div.alert').remove();
                $('.page-title').append('<div class="alert alert-success">'+response.message+'</div>');
                $('html, body').animate({ scrollTop: 0 }, 'slow');
                setTimeout(function(){
                    $('div.alert').remove();
                }, 3000);
            }
        });
        certificates.fail(function(response){
            console.log(response);
        });
        event.preventDefault();
    });
</script>
@stop	
