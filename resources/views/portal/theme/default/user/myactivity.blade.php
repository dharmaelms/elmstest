@section('content')
    <?php
    use App\Model\SiteSetting;

    $pl = App::make("App\Services\Playlyfe\IPlaylyfeService");
    $playlyfeExceptionFlag = false;
    if($pl->isPlaylyfeEnabled())
    {
        try
        {
            $pl_leaderboard = $pl->getUserRank(Auth::user()->uid);
        }
        catch(\Exception $e)
        {
            $playlyfeExceptionFlag = true;
        }
    }
    $isEnableAOI = SiteSetting::module('General', 'area_improve');
    ?>
    <link rel="stylesheet" type="text/css" href="{{  URL::asset("playlyfe/app.css") }}">
    <link rel="stylesheet" type="text/css" href="{{ URL::to("portal/theme/default/css/select2.min.css") }}">
    <link rel="stylesheet" type="text/css" href="{{URL::to('/portal/theme/default/plugins/bootstrap-daterangepicker/daterangepicker-bs3.css')}}" />
    <script type="text/javascript" src="{{ URL::asset("playlyfe/app.js") }}"></script>
    <script type="text/javascript" src="{{ URL::asset("portal/theme/default/plugins/jquery.twbsPagination.min.js") }}"></script>
    <script type="text/javascript" src="{{URL::to('/portal/theme/default/plugins/bootstrap-daterangepicker/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{URL::to('/portal/theme/default/plugins/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
    <script type="text/javascript" src="{{ URL::to("portal/theme/default/plugins/select2.min.js") }}"></script>


    <style type="text/css">
        .user-profile-info {clear: both;}
        #report_chart_cf { float: left; }
        #report_chart { float: left;}
        #report_chart_cf { float: left;}
        #report_tbl_fc { border:1px solid #eeeeee; float: left;}
        #report_tbl_fc th, #tbl_aofimp_cf th { border-bottom: 2px solid #dddddd; padding: 5px 15px !important; }
        #report_tbl_fc td, #tbl_aofimp_cf td { border-bottom: 1px solid #eeeeee; padding: 5px 15px !important; }
        #report_tbl { border:1px solid #eeeeee; }
        #report_tbl th, #tbl_quiz th, #tbl_pref th { border-bottom: 2px solid #dddddd;padding: 5px 15px !important; }
        #report_tbl td, #tbl_quiz td, #tbl_pref td { border-bottom: 1px solid #eeeeee; padding: 5px 15px !important;}
        #tbl_quiz_cf td { border-bottom: 1px solid #eeeeee; padding: 15px; }
        #tbl_quiz_cf th { border-bottom: 1px solid #eeeeee; padding: 15px; }
        #tbl_pref { width: 100%; }
        #tbl_quiz { width: 100%; }
        #tbl_quiz_cf { width: 100%; }
        .resp-tab-content { border-bottom: 0px; border-left: 0px;border-right:0px;border-top: 1px solid #5AB1D0; }
        #tbl_aofimp_cf .btn { background: transparent; color: blue; }
        .cs-daterange .control-label { width: 60px;margin-top: 8px; }
        div.radio { margin-left: 0;margin-right: 0;margin-top: -4px; }
        .radio-inline , .radio-inline + .radio-inline { padding-left: 6px; margin-left: 0; }
        .checkbox, .form-horizontal .checkbox { padding: 0; }
        .checkbox, .radio { display: block; color: #555555 !important; margin-bottom: 4px;margin-top: 0;position: relative;}
        .portlet > .portlet-title > .tools { padding: 8px 0; }
        .portlet > .portlet-title > .caption { font-size: 14px;line-height: 14px; font-weight: 600;}
        .portlet > .portlet-title{ min-height: 34px; }
        .myactivity-section .resp-tab-content { /*border: 0 none !important; padding: 10px 0 !important;*/padding: 10px 10px !important;
    background-color: aliceblue;
    /* border-top: none!important; */
   /* border: 1px inset #00bcd433!important;}
        .portlet.box > .portlet-body { background-color: #e7ffff; box-shadow: 0 0 4px 0 #cccccc; }
        .portlet.box.blue { border-width: 0; }
        .portlet.box label { font-size: 13px; }
        .checkbox-list > label.checkbox-inline, .radio-list > label.radio-inline { color: #555555; }
    </style>

    <style type="text/css">
        .user-profile-info{
            clear: both;
        }

        #report_chart_cf{
            float: left;
        }
        #report_chart{
            float: left;
        }

        #report_chart_cf{
            float: left;
        }
        #report_tbl_fc{
            /*padding-left: 200px;*/
            border:1px solid #eeeeee;
            float: left;
        }
        #report_tbl_fc th, #tbl_aofimp_cf th{
            border-bottom: 2px solid #dddddd;
            padding: 5px 15px !important;

        }
        #report_tbl_fc td, #tbl_aofimp_cf td {
            border-bottom: 1px solid #eeeeee;
            padding: 5px 15px !important;
            /*text-align: center;*/
        }
        #report_tbl{
            border:1px solid #eeeeee;
            /*padding: 5px 15px !important;*/
        }
        #report_tbl th, #tbl_quiz th, #tbl_pref th {
            border-bottom: 2px solid #dddddd;
            padding: 5px 15px !important;
        }
        #report_tbl td, #tbl_quiz td, #tbl_pref td {
            border-bottom: 1px solid #eeeeee;
            padding: 5px 15px !important;
        }
        #tbl_quiz_cf td{
            border-bottom: 1px solid #eeeeee;
            padding: 15px;
        }
        #tbl_quiz_cf th{
            border-bottom: 1px solid #eeeeee;
            padding: 15px;
        }
        #tbl_pref {
            width: 100%;
        }
        #tbl_quiz {
            width: 100%;
        }
        #tbl_quiz_cf{
            width: 100%;
        }
        .resp-tab-content {
            border-bottom: 0px;
            border-left: 0px;
            border-right:0px;
            border-top: 1px solid #5AB1D0;
        }

        #tbl_aofimp_cf .btn{
            background: transparent;
            color: blue;
        }
        .cs-daterange .control-label {
            width: 60px;margin-top: 8px;
        }

    </style>
    <style type="text/css">
        .select2-container .select2-selection--single {
            box-sizing: border-box;
            cursor: pointer;
            display: block;
            height: 28px;
            width: 200px !important;
            user-select: none;
            -webkit-user-select: none;
        }
        .select2 .select2-container .select2-container--default .select2-container--below .select2-container--focus{
            width: 200px !important;
        }
        .select2-container--open{
            width: 200px !important;
        }


    </style>
    <style type="text/css">
      .green-col{background-color: #00c10f;}
      .yellow-col{background-color: #ffad00;}
       #scorm_tbl_id td:first-child {
            padding: 16px;
            text-align: left;
        }
        #scorm_tbl_id td, th {
            padding: 3px;
            vertical-align: middle;
            text-align: center;
        }
        .scormTableData tbody tr td:first-child{word-break: break-all;}

        .myactivity-section .resp-tabs-list li {
    border: 2px outset
 #839696!important;
    padding: 6px 30px !important;
    font-size: 14px !important;
    color: #5f5b5b;
    font-weight: 400;
}
    </style>
    <div class="tabbable tabbable-tabdrop color-tabs">
        <ul class="nav nav-tabs center margin-btm-0">
          <li class="active" ><a href="#attempted" data-toggle="tab"><i class="fa fa-line-chart"></i>&nbsp;&nbsp;{{ trans('reports.reports') }}</a></li>
            <li ><a href="#unattempted" data-toggle="tab" id="recent_activity"><i class="fa fa-list-ul"></i>&nbsp;&nbsp;{{ trans('reports.recent_activity') }}</a></li>
            @if($isPlaylyfeEnabled && !$playlyfeExceptionFlag)
                <li><a href="#profile" data-toggle="tab" id="player_profile"><i class="fa fa-list-ul"></i>&nbsp;&nbsp;{{ trans('reports.profile') }}</a></li>
                <li ><a href="#leaderboard" data-toggle="tab" id="recent_leaderboard"><i class="fa fa-list-ul"></i>&nbsp;&nbsp;{{ trans('reports.leaderboard') }}</a></li>
            @endif
  
        </ul>

        <div class="tab-content margin-top-10">
            @if($isPlaylyfeEnabled && !$playlyfeExceptionFlag)
                <div class="tab-pane" id="leaderboard">
                    <div>
                        <div>
                            <div class="col-md-12">
                                <div class="col-md-5">
                                    <div class="leaderboard-header-title">{{ trans('reports.top_user_this_week') }}</div>
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-5">
                                    <div class="leaderboard-header-title">{{ trans('reports.top_users_all_time') }}</div>
                                </div>
                            </div>
                        </div>
                        <div class="row">
                            <div class="col-md-12">
                                <div class="col-md-5">
                                    @include('playlyfe.player_leaderboard', [ 'cycle' => 'week', 'total' => ceil($pl_leaderboard['total']/10)])
                                </div>
                                <div class="col-md-2">
                                </div>
                                <div class="col-md-5">
                                    @include('playlyfe.player_leaderboard', ['cycle' => 'alltime', 'total' => ceil($pl_leaderboard['total']/10)])
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            @endif
            <div class="tab-pane active" id="attempted">
                <div class="row">
                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 myactivity-section">
                        <div class="panel panel-info">
                            <div class="panel-body btm-shadow">
                                <div id="parentHorizontalTab1">
                                    <ul class="resp-tabs-list hor_1">
                                        <li data-info = "pref">{{ trans('reports.performance_tab') }}</li>
                                        <li data-info = "comp">{{ trans('reports.completion_tab') }}</li>
                                        @if($general->setting['scorm_reports'] == "on") 
                                            <li data-info = "scorm">{{ trans('reports.scorm_reports') }}</li>
                                        @endif
                                        @if($isEnableAOI == 'on')
                                            <li data-info = "aoi">{{ trans('reports.area_of_improvement') }}</li>
                                        @endif

                                    </ul>
                                    <div class="resp-tabs-container hor_1">
                                        <!--view page Quize performance starts here -->
                                        <?php
                                        $chart = Input::get('chart', 'performance');
                                        $channelId = Input::get('channel_id',0);
                                        ?>
                                        @if($chart == 'performance' && $channelId > 0)
                                            @include('portal.theme.default.reports.specific_channel_performance', ['channelId'=>$channelId, 'channnelIdName' => $channnelIdName,'chart'=> $chart])
                                            @include('portal.theme.default.reports.channel_completion', ['channnelIdName' => $channnelIdName,'chart'=> $chart])
                                        @elseif($chart == 'performance')
                                            @include('portal.theme.default.reports.channel_performance', ['channnelIdName' => $channnelIdName,'chart'=> $chart])
                                            @include('portal.theme.default.reports.channel_completion', ['channnelIdName' => $channnelIdName,'chart'=> $chart])
                                            @include('portal.theme.default.reports.scorm_reports',['general' => $general])
                                        @elseif($chart == 'completion' && $channelId > 0)
                                            @include('portal.theme.default.reports.channel_performance', ['channnelIdName' => $channnelIdName,'chart'=> $chart])
                                            @include('portal.theme.default.reports.specific_channel_completion', ['channelId'=>$channelId, 'channnelIdName' => $channnelIdName,'chart'=> $chart])
                                        @elseif($chart == 'completion')
                                            @include('portal.theme.default.reports.channel_performance', ['channnelIdName' => $channnelIdName,'chart'=> $chart])
                                            @include('portal.theme.default.reports.channel_completion',['channnelIdName' => $channnelIdName,'chart'=> $chart])
                                        @else
                                            @include('portal.theme.default.reports.channel_performance', ['cahnnelPerformance' => $cahnnelPerformance, 'channnelIdName' => $channnelIdName,'chart'=> $chart])
                                            @include('portal.theme.default.reports.channel_completion', ['channnelIdName' => $channnelIdName,'chart'=> $chart])
                                        @endif
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div><!--announce tab-->

            <div class="tab-pane " id="unattempted">
                <div class="row">
                    <div class="col-lg-10 col-md-12 col-sm-12 col-xs-12 myactivity-section">
                        <div class="panel panel-info">
                            <div class="panel-body">
                                <div id="parentHorizontalTab">
                                    <ul class="resp-tabs-list hor_1">
                                        <li class="tab" id="tab_general">{{ trans('reports.general') }}</li>
                                        <li class="tab" id="tab_feed">{{ trans('program.courses')}}</li>
                                        <!--<li class="tab" id="tab_library">Library Media</li>-->
                                        <li class="tab" id="tab_assessment">{{ trans('assessment.assessments') }}</li>
                                        <li class="tab" id="tab_event">{{ trans('event.events') }}</li>
                                        <li class="tab" id="tab_QAs">{{ trans('reports.Q_&_A') }}</li>
                                    </ul>
                                    <div class="resp-tabs-container hor_1">
                                        <div>
                                            <div id="general_tab" class="no-results" >
                                            </div>
                                            <a class='view_more_btn btn btn-primary xs-margin btn-sm' id="general" ><i class="fa fa-street-view" aria-hidden="true"></i> {{ trans('reports.view_more') }}</a>
                                        </div>
                                        <div>
                                            <div id="feed_tab" class="no-results">
                                            </div>
                                            <a class='view_more_btn btn btn-primary xs-margin btn-sm' id="feed"><i class="fa fa-street-view" aria-hidden="true"></i> {{ trans('reports.view_more') }}</a>
                                        </div>
                                        <!--<div>
                                            <p>Library Media</p>
                                        </div>-->
                                        <div>
                                            <div id="assessment_tab" class="no-results">
                                            </div>
                                            <a class='view_more_btn btn btn-primary xs-margin btn-sm' id="assessment"><i class="fa fa-street-view" aria-hidden="true"></i> {{ trans('reports.view_more') }}</a>
                                        </div>
                                        <div>
                                            <div id="event_tab" class="no-results">
                                            </div>
                                            <a class='view_more_btn btn btn-primary xs-margin btn-sm' id="event"><i class="fa fa-street-view" aria-hidden="true"></i> {{ trans('reports.view_more') }}</a>
                                        </div>
                                        <div>
                                            <div id="QAs_tab" class="no-results">
                                            </div>
                                            <a class='view_more_btn btn btn-primary xs-margin btn-sm' id="QAs"><i class="fa fa-street-view" aria-hidden="true"></i> {{ trans('reports.view_more') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div><!--tab-content-->
            </div><!-- tabdrop tabs-->
            @if($isPlaylyfeEnabled && !$playlyfeExceptionFlag)
                <div class="tab-pane" id="profile">
                    <div id="profile-content" style="display:none;">
                    </div>
                </div>

                <script type="text/javascript">
                    var isProfileLoaded = false;
                    $(document).ready(function(){
                        $("#player_profile").on("shown.bs.tab", function(e){
                            if(!isProfileLoaded)
                            {
                                var xmlHTTPRequest = $.ajax({
                                    url : "{{ URL::to("pl/player-profile") }}",
                                    type : "get",
                                    contentType : "application/x-www-form-urlencoded; charset=UTF-8",
                                    dataType : "html",
                                    beforeSend : function(jqXHR, settings){
                                        $("#player-profile-progress-bar").show();
                                    }
                                });

                                xmlHTTPRequest.done(function(response, status, jqXHR){
                                    $("#profile #profile-content").append(response).slideDown({
                                        duration : 500
                                    });
                                });

                                xmlHTTPRequest.fail(function(jqXHR, status, errorThrown){
                                    alert(status);
                                });

                                xmlHTTPRequest.always(function(){
                                    $("#player-profile-progress-bar").hide();
                                });

                                isProfileLoaded = true;
                            }
                        });
                    });
                </script>
            @endif
        </div>
    </div>
    <div class="row" style="display:none;" id="player-profile-progress-bar">
        <div class="md-margin"></div>
        <div class="col-md-offset-4 col-md-3">
            <div class="progress">
                <div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="100" aria-valuemin="0" aria-valuemax="100" style="width: 100%;">
                    <b>{{ trans('reports.loading..') }}</b>
                </div>
            </div>
        </div>
    </div>
    </div>
    <script src="{{URL::asset('portal/theme/default/js/Chart.js')}}"></script>

    <script src="{{URL::asset('portal/theme/default/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>
    <script type="text/javascript">
        var current = 0;
        var testChartVar = 0;
        var post_detail_ary = new Array();
        var post_detail_ary_title = new Array();
        var chart = '{{$chart}}';
        $(document).ready(function() {
            //Horizontal Tab
            $('#parentHorizontalTab').easyResponsiveTabs({
                type: 'default', //Types: default, vertical, accordion
                width: 'auto', //auto or any width like 600px
                fit: true, // 100% fit in a container
                tabidentify: 'hor_1', // The tab groups identifier
                activate: function(event) { // Callback function if tab is switched
                    var $tab = $(this);
                    var $info = $('#nested-tabInfo');
                    var $name = $('span', $info);
                    $name.text($tab.text());
                    $info.show();
                }
            });
            // Child Tab
            $('#ChildVerticalTab_1').easyResponsiveTabs({
                type: 'vertical',
                width: 'auto',
                fit: true,
                tabidentify: 'ver_1', // The tab groups identifier
                activetab_bg: '#fff', // background color for active tabs in this group
                inactive_bg: '#F5F5F5', // background color for inactive tabs in this group
                active_border_color: '#c1c1c1', // border color for active tabs heads in this group
                active_content_border_color: '#5AB1D0' // border color for active tabs contect in this group so that it matches the tab head border
            });
            //Vertical Tab
            $('#parentVerticalTab').easyResponsiveTabs({
                type: 'vertical', //Types: default, vertical, accordion
                width: 'auto', //auto or any width like 600px
                fit: true, // 100% fit in a container
                closed: 'accordion', // Start closed if in accordion view
                tabidentify: 'hor_1', // The tab groups identifier
                activate: function(event) { // Callback function if tab is switched
                    var $tab = $(this);
                    var $info = $('#nested-tabInfo2');
                    var $name = $('span', $info);
                    $name.text($tab.text());
                    $info.show();
                }
            });
        });
    </script>

    <!-- quize performance -->
    <script type="text/javascript">

        $(document).ready(function() {
            var url_js = '{{URL::to('/reports/quizz-performance/')}}';
            var url_js_cf = '{{URL::to('/reports/c-f-completion-status-report/')}}';
            var url_js_ai = '{{URL::to('/reports/area-improve/')}}';
            var from_pref_js = $("#from_pref").val();
            var to_pref_js = $("#to_pref").val();
            var date_range = $("#chart_show_by_id_pref").val();


            /*$('#parentHorizontalTab1').easyResponsiveTabs({
             type: 'default', //Types: default, vertical, accordion
             width: 'auto', //auto or any width like 600px
             fit: true, // 100% fit in a container
             tabidentify: 'hor_1', // The tab groups identifier
             activate: function(event) { // Callback function if tab is switched

             var $tab = $(this);
             var $info = $('#nested-tabInfo');
             var $name = $('span', $info);
             var shift =  $.trim($(this).attr("data-info"));
             $name.text($tab.text());
             $info.show();
             // alert("tab.text"+$tab.text());

             }
             });*/
            $('#parentHorizontalTab1').easyResponsiveTabs({
                type: 'default', //Types: default, vertical, accordion
                width: 'auto', //auto or any width like 600px
                fit: true, // 100% fit in a container
                tabidentify: 'hor_1', // The tab groups identifier
                activate: function(event) { // Callback function if tab is switched

                    var $tab = $(this);
                    var $info = $('#nested-tabInfo');
                    var $name = $('span', $info);
                    var shift =  $.trim($(this).attr("data-info"));
                    $name.text($tab.text());
                    $info.show();
                    if("comp" == shift){
                        var flagc = true;
                        if (flagc && chart == 'performance') {
                            var urlccompl = '{{URL::to('/reports/channel-completion/')}}';
                            flagc = false;
                            ajaxSpecificChannelCompletion(urlccompl);
                            return false;
                        }
                    }else if("pref" == shift ){
                        var flagp = true;
                        console.log(flagp+chart);
                        if ( flagp && chart == 'completion') {
                            var urlcperf = '{{URL::to('/reports/channel-performance-till-date/')}}';;//'{{URL::to('/reports/channel-performance-till-date/')}}';
                            flagc = false;
                            ajaxCPerformance(urlcperf);
                            return false;
                        }
                    }else if("scorm" == shift ){
                        var flags = true;
                        if ( flags ) {
                            var urlscorm = '{{URL::to('/reports/ajax-scorm-reports/')}}';
                            flagc = false;
                            ajaxScormReport(urlscorm);
                            return false;
                        }
                    }
                }
            });
            //Vertical Tab
            $('#parentVerticalTab1').easyResponsiveTabs({
                type: 'vertical', //Types: default, vertical, accordion
                width: 'auto', //auto or any width like 600px
                fit: true, // 100% fit in a container
                closed: 'accordion', // Start closed if in accordion view
                tabidentify: 'hor_1', // The tab groups identifier
                activate: function(event) { // Callback function if tab is switched
                    var $tab = $(this);
                    var $info = $('#nested-tabInfo2');
                    var $name = $('span', $info);
                    $name.text($tab.text());
                    $info.show();

                }
            });
            // quize performance


        });

    </script>

    <script type="text/javascript">
        $(document).ready(function () {
            var url='<?php echo URL::to('/'); ?>';
            var active_tab;
            $('.tab').data('id',0);
            $('.view_more_btn').data('id',0); // Initializing all the data ids to 0
            $('.view_more_btn').click(function(){
                var $this = $(this);
                var currenttab = $this.attr('id');
                var id = $this.attr('id');
                var $pageno = $this.data('id');
                active_tab = currenttab;
                currenttab = currenttab+"_tab";
                loadData($pageno, currenttab,active_tab,id);
                $pageno += 1;
                $this.data('id',$pageno);
            });

            $('.tab').click(function(){

                var $this = $(this);
                var $pageno = $this.data('id');

                if($pageno == 0)
                    $("[aria-labelledby='"+$(this).attr("aria-controls")+"']").find("a.view_more_btn").trigger('click');
                $pageno += 1;
                $this.data('id',$pageno);
                console.log("Page No", $pageno);

            });
            // $('#parentHorizontalTab .resp-tab-active').eq(0).trigger('click');

            function loadData(pageno, currenttab,active_tab,id)
            {
                if($('#active_tab').data('flag') != "done"){
                    $.ajax(
                        {
                            type: 'GET',
                            url: url+'/nextrecords/' + id + '/' + pageno,
                            success: function(html)
                            {
                                if(html==0)
                                {
                                    $("#"+currenttab).append( "<h6>No More Records</h6>");
                                  
                                    document.getElementById(active_tab).style.display = "none";
                                    $('#active_tab').data('flag',"done");
                                }else
                                {
                                    $("#"+currenttab).append(html);
                                }
                            }
                        });
                }
            }

            $('#recent_activity').click(function(){
                var $this = $('#tab_general');
                var $pageno = $this.data('id');
                if($pageno == 0){
                    $("#general").trigger('click');
                }
            });

        });

    </script>

@stop
// groups
// journey
// scalability batching requests