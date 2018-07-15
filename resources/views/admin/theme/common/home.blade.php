@section('content')

<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
<link rel="stylesheet" type="text/css" href="{{URL::to('admin/js/bootstrap-daterangepicker/daterangepicker-bs3.css')}}" />
<link rel="stylesheet" type="text/css" href="{{URL::to('admin/css/dashboard.css')}}" />

<div class="row dashoard">
    <div class="col-md-12 col-sm-12 col-ld-12">
        <div class="box">
            <div class="box-title" style="padding:15px";>
                <ul class="nav flaty-nav navbar-collapse collapse">
                    @if(has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ADD_ANNOUNCEMENT))
                        <li>
                            <a href="{{URL::to('/cp/announce/add')}}" style="font-size:15px">
                                <i class="fa fa-bullhorn"></i> {{trans('admin/dashboard.create_announcement')}}
                            </a>
                        </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUIZ))
                        <li>
                            <a href="{{URL::to('/cp/assessment/add-quiz')}}" style="font-size:15px">
                                <i class="fa fa-edit"></i> {{trans('admin/dashboard.create_quiz')}}
                            </a>
                        </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST))
                        <li>
                            <a href="{{URL::to('/cp/contentfeedmanagement/add-packets')}}" style="font-size:15px;display:none;">
                                <i class="fa fa-archive"></i> {trans('admin/dashboard.create_post')}}
                            </a>
                        </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
                        <li>
                            <a href="{{URL::to('/cp/dams/add-media')}}" style="font-size:15px">
                                <i class="fa fa-upload"></i> {{trans('admin/dashboard.upload_media')}}
                            </a>
                        </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::EVENT, EventPermission::ADD_EVENT))
                        <li>
                            <a href="{{URL::to('/cp/event/add-event')}}" style="font-size:15px">
                                <i class="fa fa-calendar"></i> {{trans('admin/dashboard.create_event')}}
                            </a>
                        </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::ADD_SURVEY))
                        <li>
                            <a href="{{URL::to('/cp/survey/add-survey')}}" style="font-size:15px">
                                <i class="fa fa-file-text"></i> {{trans('admin/dashboard.create_survey')}}
                            </a>
                        </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::ADD_ASSIGNMENT))
                        <li>
                            <a href="{{URL::route('get-add-assignment')}}" style="font-size:15px">
                                <i class="fa fa-file-text"></i> {{trans('admin/dashboard.create_assignment')}}
                            </a>
                        </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT))
                    <li>
                        <a href="{{URL::to('/cp/reports')}}" style="font-size:15px">
                            <i class="fa fa-bar-chart-o"></i> {{trans('admin/dashboard.view_report')}}
                        </a>
                    </li>
                    @endif
                    @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUIZ))
                    <li>
                        <a href="{{URL::to('cp/assessment/list-quiz')}}" style="font-size:15px">
                            <i class="fa fa-bar-chart-o"></i> {{trans('admin/dashboard.quiz_report')}}
                        </a>
                    </li>
                    @endif
                </ul>
            </div>
            
            <div class="box-content">
                <div class="row range-div" style="padding:0px">
                    <div class="col-md-12">
                        <form class="form-horizontal" action="">
                        <div class="form-group">
                            <label class="col-sm-1 col-lg-1 control-label" style="padding-right:0;text-align:right"><b>{{trans('admin/dashboard.range')}} : &nbsp;</b></label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:0;">
                                <div class="input-group">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control daterange" name="range" id="range" value="{{date('d-m-Y',$start) . " to " . date('d-m-Y',$end)}}"/>
                                </div>
                            </div>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:0;">
                                <div class="input-group">
                                    <input type="button" class="form-control btn btn-success" value="Submit" id="statisticcall"/>
                                </div>
                            </div>
                        </div>
                        </form>
                    </div>
                </div>
                <div class="row" >
                    <div class="col-md-12 col-sm-12 col-ld-12 for-margin btn-custom">
                        @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_QUESTION))
                        <span class="btn btn-large show-tooltip" title="{{ Lang::get('admin/homepage.channel_questions_unanswered') }}">
                            <a href="{{URL::to('/cp/contentfeedmanagement/channel-questions?filter=UNANSWERED')}}">
                                <div class="content">
                                    <strong class="big red" id="channelunansqus"></strong>
                                    <br><div><small id="channelunansqus_small"></small></div>
                                </div>
                            </a>
                        </span>
                        @endif
                        @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST))
                        <span class="btn btn-large">
                            <div class="content">
                                <a href="#" data-toggle="modal" id="hide-posts" class="new-posts">
                                    <strong class="big green" id="packets"></strong>
                                    <br><div><small id="packets_small"></small></div>
                                </a>
                            </div>
                        </span>
                        @endif
                        @if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
                        <span class="btn btn-large">
                            <div class="content">
                                <a href="#new-items" data-toggle="modal" id="hide-items" class="new-items">
                                    <strong class="big green" id="items"></strong>
                                    <br><div><small id="item_small"></small></div>
                                </a>
                            </div>
                        </span>
                        @endif
                        @if(has_admin_permission(ModuleEnum::USER, UserPermission::LIST_USER))
                        <span class="btn btn-large">
                            <div class="content">
                             <a href="#new-user" data-toggle="modal" id="hide-new-users" class="new-user">
                                <strong class="big green" id="users"></strong>
                                <br><div><small id="users_small"></small></div>
                            </a>
                            </div>
                        </span>
                        @endif
                        @if(!config('app.ecommerce'))
                            @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_ACCESS_REQUEST))
                            <span class="btn btn-large">
                                <div class="content">
                                <a href="#access-requests" data-toggle="modal" id="hide-access-request" class="access-request">
                                    <strong class="big red" id="accessrequests"></strong>
                                    <br><div><small id="accessrequests_small"></small></div>
                                </a>
                                </div>
                            </span>
                            @endif
                        @endif
                        @if(has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::LIST_ANNOUNCEMENT))
                        <span class="btn btn-large">
                            <div class="content">
                            <a href="#new-announcements" data-toggle="modal" id="hide-new-announcements" class="new-announcements">
                                <strong class="big green" id="announcements"></strong>
                                <br><div><small id="announcements_small"></small></div>
                            </a>
                            </div>
                        </span>
                        @endif
                        @if(has_admin_permission(ModuleEnum::USER, UserPermission::LIST_USER))
                        <span class="btn btn-large" >
                            <div class="content">
                            <a href="#active-users" data-toggle="modal" id="hide-active-users" class="active-users">
                                <strong class="big green" id="activeuser"></strong>
                                <br><div><small id="activeuser_small"></small></div>
                            </a>
                            </div>
                        </span>
                        @endif
                        @if(has_admin_permission(ModuleEnum::EVENT, EventPermission::LIST_EVENT))
                        <span class="btn btn-large" >
                            <div class="content">
                            <a href="#new-events" data-toggle="modal" id="hide-new-events" class="new-events">
                                <strong class="big green" id="newevents"></strong>
                                <br><div><small id="newevents_small"></small></div>
                            </a>
                            </div>
                        </span> 
                        @endif
                        @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::LIST_CHANNEL))
                        <span class="btn btn-large">
                            <div class="content">
                            <a href="#new-feeds" data-toggle="modal" id="hide-new-feeds" class="new-feeds">
                                <strong class="big green" id="newfeeds"></strong>
                                <br><div><small id="newfeeds_samll"></small></div>
                            </a>
                            </div>
                        </span>   
                        @endif
                        @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::LIST_CHANNEL))
                        <span class="btn btn-large" >
                            <div class="content">
                                <a href="#active-feeds" data-toggle="modal" id="hide-active-feeds" class="active-feeds">
                                    <strong class="big green" id="activefeeds"></strong>
                                    <br><div><small id="activefeeds_small"></small></div>
                                </a>
                            </div>
                        </span>
                        @endif
                    </div>
                </div>

                <!-- Total inventary start here -->
                <div style="margin:20px 0"></div>
                    <div class="row">
                        <div class="box">
                            <div class="box-title" style="padding:15px;color:white;font-size:20px";>
                                {{trans('admin/dashboard.total_inventory')}}
                            </div>
                        </div>
                        <div class="col-md-5 col-md-offset-1">
                            <div class="box">
                                <table class="table table-advance" id="datatable" >
                                    <thead>
                                        <tr>
                                            <th class="tbl-head">{{trans('admin/dashboard.name_of_module')}}</th>
                                            <th class="tbl-head">{{trans('admin/dashboard.active')}}</th>
                                            <th class="tbl-head">{{trans('admin/dashboard.inactive')}}</th>
                                        </tr>
                                    </thead>
                                    @if(has_admin_permission(ModuleEnum::USER, UserPermission::LIST_USER))
                                    <tr>
                                        <td><b>{{trans('admin/dashboard.users')}}</b></td>
                                        <td><b>{{array_get($inventory, 'users.active', -1)}}</b></td>
                                        <td><b>{{array_get($inventory, 'users.in_active', -1)}}</b></td>
                                    </tr>
                                    @endif
                                    @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::LIST_CHANNEL))
                                    <tr>
                                        <td><b>{{trans('admin/program.programs')}}</b></td>
                                        <td><b>{{array_get($inventory, 'channels.active', -1)}}</b></td>
                                        <td><b>{{array_get($inventory, 'channels.in_active', -1)}}</b></td>
                                    </tr>
                                    @endif
                                    @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST))
                                    <tr>
                                        <td><b>{{trans('admin/dashboard.posts')}}</b></td>
                                        <td><b>{{array_get($inventory, 'posts.active', -1)}}</b></td>
                                        <td><b>{{array_get($inventory, 'posts.in_active', -1)}}</b></td>
                                    </tr>
                                    @endif
                                    @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUIZ))
                                    <tr>
                                        <td><b>{{trans('admin/dashboard.quizzes')}}</b></td>
                                        <td><b>{{array_get($inventory, 'quizzes.active', -1)}}</b></td>
                                        <td><b>{{array_get($inventory, 'quizzes.in_active', -1)}}</b></td>                            
                                    </center>
                                    @endif
                                    @if(has_admin_permission(ModuleEnum::EVENT, EventPermission::LIST_EVENT))
                                    </tr>
                                    <tr>
                                        <td><b>{{trans('admin/dashboard.events')}}</b></td>
                                        <td><b>{{array_get($inventory, 'events.active', -1)}}</b></td>
                                        <td><b>{{array_get($inventory, 'events.in_active', -1)}}</b></td>
                                    </tr>
                                    @endif
                                </table>
                            </div>
                        </div>

                        <!-- Item details table -->
                        @if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::LIST_MEDIA))
                        <div class="col-md-4">
                            <div class="box">
                                <table class="table table-advance" id="datatable">
                                    <thead>
                                        <tr>
                                            <th colspan="2" class="tbl-head"><center><b>{{trans('admin/dashboard.asset')}}</b></center>
                                        </tr>
                                    </thead>
                                        <tr>
                                            <td><b>{{trans('admin/dashboard.images')}}</b></td>
                                            <td><b>{{array_get($inventory, 'items.image', -1)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td><b>{{trans('admin/dashboard.audio')}}</b></td>
                                            <td><b>{{array_get($inventory, 'items.audio', -1)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td><b>{{trans('admin/dashboard.video')}}</b></td>
                                            <td><b>{{array_get($inventory, 'items.video', -1)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td><b>{{trans('admin/dashboard.document')}}</b></td>
                                            <td><b>{{array_get($inventory, 'items.document', -1)}}</b></td>
                                        </tr>
                                        <tr>
                                            <td><b>{{trans('admin/dashboard.scorm')}}</b></td>
                                            <td><b>{{array_get($inventory, 'items.scorm', -1)}}</b></td>
                                        </tr>
                                </table>
                            </div>
                        </div><!-- Item details table ends here -->
                        @endif
                    </div><!-- Total inventory ends here -->
            </div>
        </div>
    </div>
</div>


<!-- 1st row modal view pages -->
@include('admin.theme.dashboard.new_posts')
@include('admin.theme.dashboard.new_items')
@include('admin.theme.dashboard.new_users')
@include('admin.theme.dashboard.access_request')
@include('admin.theme.dashboard.new_announcements')
@include('admin.theme.dashboard.active_users')
@include('admin.theme.dashboard.new_events')
@include('admin.theme.dashboard.new_channels')
@include('admin.theme.dashboard.active_channels')
<!-- 1st row modal view pages ends here -->

 <script type="text/javascript" src="{{URL::to('/admin/js/bootstrap-daterangepicker/moment.min.js')}}"></script>
 <script type="text/javascript" src="{{URL::to('/admin/js/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
 <script type="text/javascript" src="{{URL::to('/admin/js/dashboard.js')}}"></script>
 <script src="{{URL::asset('admin/js/Chart.js')}}"></script>
 <script>
    var current=0;
    var range = $('#range').val();
    date_splited = range.split(' to ');
    start_date = date_splited[0];
    end_date = date_splited[1];
    
    $(document).ready(function(){
        callajaxgetstaticreport();
        $('#statisticcall').click(function(){
            callajaxgetstaticreport();
        });

        function callajaxgetstaticreport(){
            var range = $('#range').val();
            date_splited = range.split(' to ');
            start_date = date_splited[0];
            end_date = date_splited[1];
            $.ajax({
                type:"GET",
                url: '{{URL::to('/cp/dashboard/static-report-ajax/')}}'+'?start='+start_date+'&end='+end_date,
            })
            .done(function(statistics){
                $.each(statistics.statistic_ary, function( index11, value11 ) {
                switch(index11) {
                    case "PacketUnAnsQus":
                        $('#packetunansqus').text(value11);
                        if(value11 > 1){
                            $('#packetunansqus_small').html("{{trans('admin/homepage.post_questions')}}");
                        }else{
                            $('#packetunansqus_small').html("{{trans('admin/homepage.post_question')}}");
                        }
                    break;

                    case "ChannelFaqunAns":
                        $('#channelunansqus').text(value11);
                        if(value11 > 1){
                            $('#channelunansqus_small').html("{{trans('admin/homepage.channel_questions')}}");
                        }else{
                            $('#channelunansqus_small').html("{{trans('admin/homepage.channel_question')}}");
                        }
                    break;

                    case "users":
                        $('#users').text(value11);
                        if(value11 >= 1){
                            $('#hide-new-users').attr("href", '#new-users').css('color', '#4682B4');
                            $('#users_small').html("{{trans('admin/homepage.new_users')}}");
                        }else{
                            $('#hide-new-users').attr("href", '#').css('color', 'black');
                            $('#users_small').html("{{trans('admin/homepage.new_user')}}");
                        }
                    break;

                    case "activeuser":
                        $('#activeuser').text(value11);
                        if(value11 >= 1){
                            $('#hide-active-users').attr("href", '#active-users').css('color', '#4682B4');
                            $('#activeuser_small').html("{{trans('admin/homepage.active_users')}}")
                        }else{
                            $('#hide-active-users').attr("href", '#').css('color', 'black');
                            $('#activeuser_small').html("{{trans('admin/homepage.active_user')}}")
                        }
                    break;

                    case "activefeeds":
                        $('#activefeeds').text(value11);
                        if(value11 >= 1){
                            $('#hide-active-feeds').attr("href", '#active-feeds').css('color', '#4682B4');
                            $('#activefeeds_small').html("{{trans('admin/homepage.active_channels')}}");
                        }else{
                            $('#hide-active-feeds').attr("href", '#').css('color', 'black');
                            $('#activefeeds_small').html("{{trans('admin/homepage.active_channel')}}");
                        }
                    break;

                    case "newfeeds":
                        $('#newfeeds').text(value11);
                        if(value11 >= 1){
                            $('#hide-new-feeds').attr("href", '#new-feeds').css('color', '#4682B4');
                            $('#newfeeds_samll').html(" {{ trans('admin/homepage.new_channels') }} ");
                        }else{
                            $('#hide-new-feeds').attr("href", '#').css('color', 'black');
                            $('#newfeeds_samll').html(" {{ trans('admin/homepage.new_channel') }} ");
                        }
                    break;

                    case "newevents":
                        $('#newevents').text(value11);
                        if(value11 >= 1){
                            $('#hide-new-events').attr("href", '#new-events').css('color', '#4682B4');
                            $('#newevents_small').html("{{trans('admin/homepage.new_events')}}");
                        }else{
                            $('#hide-new-events').attr("href", '#').css('color', 'black');
                            $('#newevents_small').html("{{trans('admin/homepage.new_event')}}");
                        }
                    break;

                    case "Announcements":
                        $('#announcements').text(value11);
                        if(value11 >= 1){
                            $('#hide-new-announcements').attr("href", '#new-announcements').css('color', '#4682B4');
                            $('#announcements_small').html("{{trans('admin/homepage.new_announcements')}}");
                        }else{
                            $('#hide-new-announcements').attr("href", '#').css('color', 'black');
                            $('#announcements_small').html("{{trans('admin/homepage.new_announcement')}}");
                        }
                    break;

                    case "Packets":
                        $('#packets').text(value11);

                        if(value11 >= 1){
                            $('#hide-posts').attr("href", '#new-posts').css('color', '#4682B4');
                            $('#packets_small').html("{{trans('admin/homepage.new_posts')}}");
                        }else{
                            $('#hide-posts').attr("href", "#").css('color', 'black');
                            $('#packets_small').html("{{trans('admin/homepage.new_post')}}");
                        }
                    break;

                    case "accessrequests":
                        $('#accessrequests').text(value11);
                        if(value11 >= 1){
                            $('#hide-access-request').attr("href", '#access-requests').css('color', '#4682B4');
                            $('#accessrequests_small').html("{{trans('admin/homepage.access_requests')}}");
                        }else{
                            $('#hide-access-request').attr("href", '#').css('color', 'black');
                            $('#accessrequests_small').html("{{trans('admin/homepage.access_request')}}");
                        }
                    break;

                    case "newitems":
                        $('#items').text(value11);
                        if(value11 >= 1){
                            $('#hide-items').attr("href", '#new-items').css('color', '#4682B4');
                            $('#item_small').html("{{trans('admin/dashboard.new_assets')}}");
                        }else{
                            $('#hide-items').attr("href", '#').css('color', 'black');
                            $('#item_small').html("{{trans('admin/dashboard.new_assets')}}");
                        }
                    break;
                }
                });
            })
        }

        $('.daterange').daterangepicker({
            format: 'DD-MM-YYYY',
            maxDate: moment(),
            dateLimit: { days: 60 },
            showDropdowns: true,
            showWeekNumbers: true,
            timePicker: false,
            timePickerIncrement: 1,
            timePicker12Hour: true,
            ranges: {
               'Yesterday': [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
               'Today': [moment().startOf('day'), moment().endOf('day')],
               'This week': [moment().startOf('week'), moment().endOf('week')],
               'This Month': [moment().startOf('month'), moment().endOf('month')],
               'This Year': [moment().startOf('year'), moment()]
            },
            opens: 'right',
            drops: 'down',
            buttonClasses: ['btn', 'btn-sm'],
            applyClass: 'btn-primary',
            cancelClass: 'btn-default',
            separator: ' to ',
            locale: {
                applyLabel: 'Apply',
                cancelLabel: 'Cancel',
                fromLabel: 'From',
                toLabel: 'To',
                customRangeLabel: 'Custom',
                daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
                monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
                firstDay: 1
            }
        });
    });
</script>
@stop