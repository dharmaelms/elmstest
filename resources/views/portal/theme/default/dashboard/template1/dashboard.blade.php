@section('content')
    <style type="text/css">
        .asssesment-type{top: 11px;}
        .asssesment-type .label{font-size: 10px;}
        .event-details-table>tbody>tr>td {padding: 4px 8px;line-height: 1.2;vertical-align: middle;border-top: 1px solid #ddd; }
        .event-details-table { width: auto; }
    </style>
    <?php
    if(config('app.ecommerce')){
        $site = 'external';
    }else{
        $site = 'internal';
    }

    use App\Model\Common;
    use App\Model\Program;
    ?>
    <link rel="stylesheet" href="{{ URL::asset("portal/theme/".config("app.portal_theme_name")."/css/postlogin.css") }}">

<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />
<style>
  /*.page-content-wrapper .page-content { margin-left: 0 !important; }
  .page-sidebar.navbar-collapse { display: none !important; max-height: none !important; }
  .page-header.navbar .menu-toggler.sidebar-toggler { display: none; }*/

        .events-list .panel .panel-title .accordion-toggle {padding: 14px 8px 12px !important;}
    </style>
    <div class="row dashboard">
        <div class="col-md-8 col-sm-8 col-xs-12">
            @if($lhs_menu_settings->setting['programs'] == "on")
                @if($general->setting['posts'] == 'on' || $general->setting['general_category_feeds'] == 'on')
                    <div>
                        <div class="row">
                            <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                                <span class="title">{{ Lang::get("dashboard.$site.courses")}}</span>
                            </div>
                            <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
                                <span class="l-gray">|</span>
                                @if($general->setting['posts'] == 'on')
                                    <span class="sub-title"><a href="{{ URL::to('/program/my-feeds') }}">{{ Lang::get('program.view_all') }}</a></span>
                                @else
                                    <span class="sub-title"><a href="{{ URL::to('/program/category-channel') }}">{{ Lang::get(program.view_all) }}</a></span>
                                @endif
                            </div>
                        </div>
                    </div>
                    <div class="title-border1"></div>
                    @if($programs->count() > 0)
                        <div class="row facets-data">
                            @foreach($programs as $program)
                                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-6 sm-margin cs-packet">
                                <!-- <a href="{{ URL::to('program/packets/'.$program->program_slug)}}"> -->
                                    <div class="packet">
                                        <figure>
                                            <a href="{{ URL::to('program/packets/'.$program->program_slug)}}" title="{{ $program->program_title }}">
                                                @if(isset($program->program_cover_media) && !empty($program->program_cover_media))
                                                    <img src="{{URL::to('media_image/'.$program->program_cover_media)}}" alt="Channel" class="packet-img img-responsive">
                                                @else
                                                    <img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Channel" class="packet-img img-responsive">
                                                @endif
                                            </a>
                                            <!--package tooltip starts here-->
                                            <?php
                                            $title = Program::getDashboardChannelPack($program->program_id);
                                            //$count = Program::getDashboardChannelPackCount($program->program_id);
                                            ?>
                                            @if(!empty($title))
                                                <a class="pckg-name tooltip tooltip-effect-1" href="#">
                                                    <i class="fa fa-info"></i>
                                                    @if(strlen($title) > 10)
                                                        <span class="tooltip-content">{{substr($title,0,10)}}..</span>
                                                    @else
                                                        <span class="tooltip-content">{{$title}}</span>
                                                    @endif

                                                </a>
                                        @endif
                                        <!--package tooltip starts here-->
                                        </figure>
                                        <p class="packet-title" title="{{$program->program_title}}">{{str_limit($program->program_title, $limit = 15, $end = '...')}}</p>
                                    </div><!--packet-->
                                    <?php
                                    $sepecificChannelAnalticAry = $channelAnalytics->get((int)$program->program_id);
                                    ?>
                                    @if(!is_null($sepecificChannelAnalticAry) && config('app.channelAnalytic') == 'on')
                                        <?php
                                        if(isset($program['benchmarks'])){
                                            // $benchMarks = $program['benchmarks'];
                                            $benchMarkSpeed = isset($program['benchmarks']['speed']) ?
                                                $program['benchmarks']['speed'] : 1;
                                            $benchMarkScore = isset($program['benchmarks']['score']) ?
                                                $program['benchmarks']['score'] : 1;
                                            $benchMarkAccuracy = isset($program['benchmarks']['accuracy']) ?
                                                $program['benchmarks']['accuracy'] : 1;
                                        }else{
                                            $benchMarkSpeed = 0;
                                            $benchMarkScore = 0;
                                            $benchMarkAccuracy = 0;
                                        }
                                        ?>

                                        <?php
                                        $sepecificChannelAnaltic = $sepecificChannelAnalticAry[0];
                                        $score = isset($sepecificChannelAnaltic['score']) ?  $sepecificChannelAnaltic['score'] : 0;
                                        $accuracy = isset($sepecificChannelAnaltic['accuracy']) ?
                                            $sepecificChannelAnaltic['accuracy'] : 0;
                                        $speed = isset($sepecificChannelAnaltic['speed']) ?  $sepecificChannelAnaltic['speed'] : "0:0:0";
                                        $completion = isset($sepecificChannelAnaltic['completion']) ?
                                            $sepecificChannelAnaltic['completion'] : 0;
                                        $analyticBenchMarkSpeed = isset($sepecificChannelAnaltic['speed_secs']) ?
                                            $sepecificChannelAnaltic['speed_secs'] : 1;

                                        $statusSpeed = ($analyticBenchMarkSpeed > $benchMarkSpeed) ? "LOW" :"HIGH";
                                        $ssSpeed = ($analyticBenchMarkSpeed % 60) > 10 ?
                                            $analyticBenchMarkSpeed % 60 :
                                            '0'.$analyticBenchMarkSpeed % 60;
                                        $mmSpeed = intval($analyticBenchMarkSpeed / 60);
                                        $mmSpeed = ($mmSpeed > 10) ?
                                            $mmSpeed  : '0'.$mmSpeed;
                                        ?>
                                        <div class="analytic-div">
                                            @if(isset($general->setting['quiz_marics']['quiz_score']) && $general->setting['quiz_marics']['quiz_score'] == 'on')
                                                <div>
                                                    <div class="left">
                                                        <img src="{{URL::asset($theme.'/img/icons/icons-05.png')}}" alt="score" title="@if($benchMarkScore > 0)
                                                        {{Lang::get('assessment.score_channel_benchmark')}}
                                                        @else
                                                        {{Lang::get('assessment.score_channel')}}
                                                        @endif" width="18px">
                                                    </div>
                                                    <div class="right">
                                                        <div class="progress score-bar">
                                                            <div style="width: {{$score}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{$score}}" role="progressbar" class="progress-bar"> </div>
                                                            <div style="position: absolute;" class="white">{{$score}}%</div>
                                                            @if($benchMarkScore > 0)
                                                                <span class="bench-mark" style="left:{{$benchMarkScore}}%;"></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div><!-- score-bar -->
                                            @endif
                                            @if(isset($general->setting['quiz_marics']['quiz_accuracy']) && $general->setting['quiz_marics']['quiz_accuracy'] == 'on')
                                                <div>
                                                    <div class="left">
                                                        <img src="{{URL::asset($theme.'/img/icons/icons-07.png')}}" alt="accuracy" title="
                      @if($benchMarkAccuracy > 0)
                                                        {{Lang::get('assessment.accuracy_channel_benchmark')}}
                                                        @else
                                                        {{Lang::get('assessment.accuracy_channel')}}
                                                        @endif
                                                                " width="18px">
                                                    </div>
                                                    <div class="right">
                                                        <div class="progress accuracy-bar">
                                                            <div style="width: {{$accuracy}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{$accuracy}}" role="progressbar" class="progress-bar"> </div>
                                                            <div style="position: absolute;" class="white">{{$accuracy}}%</div>
                                                            @if($benchMarkAccuracy > 0)
                                                                <span class="bench-mark" style="left:{{$benchMarkAccuracy}}%;"></span>
                                                            @endif
                                                        </div>
                                                    </div>
                                                </div><!--  accuracy-bar -->
                                            @endif
                                            @if(isset($general->setting['quiz_marics']['channel_completion']) && $general->setting['quiz_marics']['channel_completion'] == 'on')
                                                <div>
                                                    <div class="left">
                                                        <img src="{{URL::asset($theme.'/img/icons/completion.png')}}" alt="Completion" title="{{Lang::get('assessment.completion_level')}}" width="18px">
                                                    </div>
                                                    <div class="right">
                                                        <div class="progress completion-bar">
                                                            <div style="width: {{$completion}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{$completion}}" role="progressbar" class="progress-bar"> </div>
                                                            <div style="position: absolute;" class="white">{{$completion}}%</div>
                                                        </div>
                                                    </div>
                                                </div><!-- completion-bar -->
                                            @endif
                                            @if(isset($general->setting['quiz_marics']['quiz_speed']) && $general->setting['quiz_marics']['quiz_speed'] == 'on')
                                                <div>
                                                    <div class="left">
                                                        <img src="{{URL::asset($theme.'/img/icons/icons-03.png')}}" alt="time" title="@if($benchMarkSpeed > 0)
                                                        {{Lang::get('assessment.speed_detail_benchmark')}}
                                                        @else
                                                        {{Lang::get('assessment.speed_detail')}}
                                                        @endif"
                                                             width="18px">
                                                    </div>
                                                    <div class="right">
                                                    <!--  <div class="progress time-bar">
                       <div style="width: {{$speed}}" aria-valuemax="100" aria-valuemin="0" aria-valuenow="{{$speed}}" role="progressbar" class="progress-bar">{{$speed}}  </div>
                     </div> -->
                                                        <span>{{$mmSpeed}}:{{$ssSpeed}}</span><span>&nbsp;(MM:SS)</span>
                                                        @if($benchMarkSpeed > 0)
                                                            &nbsp;|<span>&nbsp;{{$statusSpeed}}</span>
                                                        @endif
                                                    </div>
                                                </div><!-- time-bar -->
                                            @endif
                                        </div>
                                    @endif
                                </div>
                            @endforeach

                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-12">
                                <div id="announceTab" class="announce-tabs" >
                                    <h4 align="center"> {{ Lang::get('dashboard.'.$site.'.no_courses')}}</h4>
                                </div>
                            </div>
                        </div>
                    @endif
                @endif
            @endif
            <?php
            use App\Model\MyActivity;
            $array_packets['new'] = array();
            $array_packets['completed'] = array();
            //Disable beause of mongo load
            /*$array_packets=MyActivity::getNewCompletedPackets($packets);
            if(empty($array_packets['new']))
            {
              $array_packets['new']=array();
            }
            if(empty($array_packets['completed']))
            {
              $array_packets['completed']=array();
            }*/
            ?>
            @if($general->setting['watch_now'] == 'on' && $lhs_menu_settings->setting['programs'] == "on")
                <div>
                    <div class="row">
                        <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                            <span class="title">{{ Lang::get('dashboard.'.$site.'.posts')}}</span>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
                            <span class="l-gray">|</span>
                            <span class="sub-title"><a href="{{ URL::to('program/what-to-watch') }}">{{ Lang::get('program.view_all') }}</a></span>
                        </div>
                    </div>
                    <div class="title-border1"></div>
                    <?php $i=0; ?>
                    @if(count($packets) > 0)
                        <div class="row facets-data">
                            @foreach($packets as $packet)
                                <?php $i=$i+1; ?>
                                @if($i <=5 )
                                    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-6 sm-margin">
                                        <a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}">
                                            <div class="packet">
                                                <figure>
                                                    @if(empty($packet['packet_cover_media']))
                                                        <img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
                                                    @else
                                                        <img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
                                                    @endif
                                                    @if(in_array($packet['packet_id'], $array_packets['new']))
                                                        <img class="new-label" src="{{URL::asset($theme.'/img/new-label.png')}}">
                                                    @endif
                                                    @if(in_array($packet['packet_id'], $array_packets['completed']))
                                                        <span class="completed-overlay">
                                <img src="{{URL::asset($theme.'/img/completed.png')}}" class="img-responsive">
                              </span>
                                                    @endif
                                                </figure>
                                                <div>
                                                    <p class="packet-title">{{str_limit($packet['packet_title'], $limit = 40, $end = '...')}}</p>

                                                </div>
                                            </div><!--packet-->
                                        </a>
                                    </div>
                                @endif
                            @endforeach
                        </div>
                    @else
                        <div class="row">
                            <div class="col-md-12">
                                <div id="announceTab" class="announce-tabs" >
                                    <h4 align="center"> {{ Lang::get('dashboard.'.$site.'.no_posts')}}</h4>
                                </div>
                            </div>
                        </div>
                    @endif
                </div>
            @endif
            <div>
                @if($general->setting['assessments'] == 'on')
                    <div class="row">
                        <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                            <span class="title">{{ Lang::get('dashboard.'.$site.'.assessments')}}</span>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
                            <span class="l-gray">|</span>
                            <span class="sub-title"><a href="{{ URL::to('assessment') }}">{{ Lang::get('assessment.view_all')}}</a></span>
                        </div>
                    </div>
            </div>
            <div class="title-border1"></div>
            @if($quizzes->count() > 0)
                <div class="row facets-data">
                    @foreach($quizzes as $quiz)
                        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-6 sm-margin">
                            <a href="{{ URL::to('assessment/detail/'.$quiz->quiz_id)}}">
                                <div class="assessment">
                                    <figure>
                                        <a href="{{ URL::to('assessment/detail/'.$quiz->quiz_id)}}" title="{{ $quiz->quiz_name }}">
                                            <img src="{{URL::asset($theme.'/img/assessment-default.png')}}" alt="Channel" class="packet-img img-responsive">
                                            @if(isset($quiz->practice_quiz) && !empty($quiz->practice_quiz) && $quiz->practice_quiz)
                                                <div class="asssesment-type"><span class="label label-success">Practice Quiz</span></div>
                                            @elseif(isset($quiz->type) && !empty($quiz->type) && ($quiz->type == 'QUESTION_GENERATOR'))
                                                <div class="asssesment-type"><span class="label label-warning"><?php echo Lang::get('assessment.question_generator');?></span></div>
                                        @endif
                                    </figure>
                                    <div>
                                        <p class="assessment-title">
                                            <a href="{{ URL::to('assessment/detail/'.$quiz->quiz_id) }}">
                                                <strong>
                                                    {{str_limit($quiz->quiz_name, $limit = 40, $end = '...')}}
                                                </strong>
                                            </a>
                                        </p>
                                        <p class="xs-margin font-12">
                                            @if($quiz->end_time && !empty($quiz->end_time))
                                                <strong>
                                                    {{ Lang::get('assessment.end_time').': '.$quiz->end_time->timezone(Auth::user()->timezone)->format("Y-m-d, g:i a")}}
                                                </strong>
                                            @else
                                                @if(!$quiz->attempts)
                                                    {{ Lang::get('dashboard.'.$site.'.assessment_no_time_limit')}}
                                                @else
                                                    {{ $quiz->attempts.' attempt(s) limit'}}
                                                @endif
                                            @endif
                                        </p>
                                    </div>
                                </div><!--packet-->
                            <!-- @if(!is_null($quizAnalytics->get((int)$quiz->quiz_id)) // now remove the feature
                && config('app.channelAnalytic') == 'on')
                                <div class="analytic-div">
                                  <div>
                                    <div class="left">
                                      <img src="{{URL::asset($theme.'/img/icons/icons-05.png')}}" alt="score" title="Score" width="18px">
                  </div>
                  <div class="right">
                    <div class="progress score-bar">
                      <div style="width: 70%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">70% </div>
                    </div>
                  </div>
                </div>score-bar
                <div>
                  <div class="left">
                    <img src="{{URL::asset($theme.'/img/icons/icons-03.png')}}" alt="time" title="Time" width="18px">
                  </div>
                  <div class="right">
                    <div class="progress time-bar">
                      <div style="width: 50%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">50% </div>
                    </div>
                  </div>
                </div>time-bar
                <div>
                  <div class="left">
                    <img src="{{URL::asset($theme.'/img/icons/icons-07.png')}}" alt="accuracy" title="Accuracy" width="18px">
                  </div>
                  <div class="right">
                    <div class="progress accuracy-bar">
                      <div style="width: 80%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">80% </div>
                    </div>
                  </div>
                </div>accuracy-bar
                <div>
                  <div class="left">
                    <img src="{{URL::asset($theme.'/img/icons/correct-icon.jpg')}}" alt="Completion" title="Completion" width="18px">
                  </div>
                  <div class="right">
                    <div class="progress completion-bar">
                      <div style="width: 100%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">100% </div>
                    </div>
                  </div>
                </div>completion-bar
              </div>
            @endif -->
                            </a>
                        </div>
                    @endforeach
                </div>
            @else
                @if(empty($quiz_list))
                    <div class="row">
                        <div class="col-md-12">
                            <div class="announce-tabs resp-vtabs hor_1" id="announceTab" style="display: block; width: 100%; margin: 0px;">
                                <h4 align="center">{{ Lang::get('dashboard.'.$site.'.no_quiz')}}</h4>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-md-12">
                            <div class="announce-tabs resp-vtabs hor_1" id="announceTab" style="display: block; width: 100%; margin: 0px;">
                                <h4 align="center">{{ Lang::get('dashboard.'.$site.'.no_assessments')}}</h4>
                            </div>
                        </div>
                    </div>
                @endif
            @endif
            @endif
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
            <!-- Announcement Start Here -->
            <div class="md-margin" id="annoucements-div">
                <?php
                $announcementFlag = ((isset($announcements) && !empty($announcements)) || (isset($buff_announcement) && !empty($buff_announcement)));
                ?>
                <div class="row">
                    <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                        <span  class="title">{{ Lang::get("dashboard.$site.announcements")}}</span> &nbsp;
                    </div>
                    @if(count($announcements) > 0)
                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
                            <span class="l-gray">|</span>
                            <span class="sub-title"><a href="{{ URL::to('announcements') }}"><?php echo Lang::get('announcement.view_all'); ?></a></span>
                        </div>
                    @endif
                </div>
                <div class="title-border"></div>
                @if($announcementFlag)
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div class="announce-tabs">
                                <!-- accordian code -->
                                <div class="panel-group accordion announce-tabs" id="accordion3">
                                <?php
                                $count_anno = 0;
                                $count1 = 0;
                                $maintain_uniqe =array();
                                $i = 1;
                                foreach ($announcements as $key => $value)
                                {
                                $maintain_uniqe[] = $value['announcement_id'];
                                $count_anno++;
                                $count1++;
                                ?>
                                <!-- announcement with new label is displayed in this module -->
                                    <div class="panel panel-default border-btm-0">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_1_{{$i}}">

                                                    @if(strlen($value["announcement_title"]) > 40)
                                                        {{ substr($value["announcement_title"], 0, (strrpos(substr($value["announcement_title"], 0, 40), " "))) }}....
                                                    @else
                                                        {{ $value["announcement_title"] }}
                                                    @endif
                                                    <span class="badge badge-roundless badge-success pull-right" data-module="announcement" data-id = "{{ $value['announcement_id'] }}" data-url = "{{URL::to('/announcements/announcemnt-mark-read/'.$value['announcement_id'])}}" style="position: absolute;right: 56px;top:8px;"><?php echo Lang::get('announcement.new'); ?></span>

                                                    <p class="font-10 gray margin-0">{{Common::getPublishOnDisplay(Timezone::getTimeStamp($value['schedule']))}}</p>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="collapse_1_{{$i}}" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <p>
                                                    <!-- desc -->
                                                    <span>
                      @if(strlen($value["announcement_content"]) > 150)
                                                            {!! html_entity_decode(str_limit(ucwords(strip_tags(html_entity_decode($value["announcement_content"]))))) !!}
                                                        @else
                                                            {!! html_entity_decode($value["announcement_content"]) !!}
                                                        @endif
                      </span>
                                                    <!-- desc ends here -->
                                                </p>
                                                <p>
                                                    <a href="{{ URL::to('/announcements/index/'.$value['announcement_id'].'') }}" ><?php echo Lang::get('announcement.more'); ?></a>
                                                </p>
                                            </div>
                                        </div>
                                        <?php $i++; ?>
                                    </div>
                                    <!-- announcement with new label ends here -->

                                    @if(($count_anno < 5) && ($count1 < (count($announcements) + count($buff_announcement))))
                                        <div class="announcement-border"></div>
                                    @endif

                                    <?php
                                    }
                                    if($count_anno < 5)
                                    {
                                    $count2 = 0;
                                    $j = 1;
                                    foreach ($buff_announcement as $key1 => $value1)
                                    {
                                    $count2++;
                                    if(in_array($value1['announcement_id'], $maintain_uniqe)){
                                        continue;
                                    }
                                    if($count_anno > 5)
                                        break;
                                    $count_anno++;
                                    ?>
                                <!-- here read announcement are being displayed -->
                                    <div class="panel panel-default border-btm-0">
                                        <div class="panel-heading">
                                            <h4 class="panel-title">
                                                <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_2_{{$j}}">
                                                    <strong>
                                                        @if(strlen($value1["announcement_title"]) > 40)
                                                            {{ substr($value1["announcement_title"], 0, (strrpos(substr($value1["announcement_title"], 0, 40), " "))) }}....
                                                        @else
                                                            {{ $value1["announcement_title"] }}
                                                        @endif
                                                    </strong>

                                                    <p class="font-11">{{Common::getPublishOnDisplay(Timezone::getTimeStamp($value1['schedule']))}}</p>
                                                </a>
                                            </h4>
                                        </div>
                                        <div id="collapse_2_{{$j}}" class="panel-collapse collapse">
                                            <div class="panel-body">
                                                <p>
                                                    <!-- desc -->
                                                    <span>
                  @if(strlen($value1["announcement_content"]) > 150)
                                                            {!! html_entity_decode(str_limit(ucwords($value1["announcement_content"]), $limit = 150, $end = '...')) !!}
                                                        @else
                                                            {!! html_entity_decode($value1["announcement_content"]) !!}
                                                        @endif
                  </span>
                                                    <!-- desc ends here -->
                                                </p>
                                                <p>
                                                    <a href="{{ URL::to('/announcements/index/'.$value1['announcement_id'].'') }}" ><?php echo Lang::get('announcement.more'); ?></a>
                                                </p>
                                            </div>
                                        </div>
                                        <?php $j++; ?>
                                    </div>
                                    <!-- read announcement ends here -->
                                    @if(($count_anno < 5) && ($count2 < count($buff_announcement)))
                                        <div class="announcement-border"></div>
                                    @endif
                                    <?php
                                    }
                                    }
                                    ?>
                                </div>
                                <!-- /accordian ends here -->
                            </div>
                        </div>
                    </div>
                @else
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <span class="font-13">{{ Lang::get('dashboard.'.$site.'.no_announcements')}}</span>
                        </div>
                    </div>
                @endif
            </div>
        <!-- Announcement Ends Here -->
            <!-- Events Starts Here-->
            @if($general->setting['events'] == 'on')
                <div class="event-container lg-margin">
                    <div class="row">
                        <div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
                            <span class="title">{{ Lang::get('event.events') }} </span>
                        </div>
                        <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
                            <span class="l-gray">|</span>
                            <span class="sub-title"><a href="{{ URL::to('event') }}">{{ Lang::get('event.view_all')}}</a></span>
                        </div>
                    </div>
                    <div class="title-border"></div>
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <div id="custom-inner" class="custom-inner">
                                <div class="custom-header clearfix">
                                    <h3><span id="custom-month" class="custom-month"></span> <span id="custom-year" class="custom-year"></span></h3>
                                </div>
                                <div id="calendar" class="fc-calendar-container"></div>
                            </div>
                        </div>
                    </div>
                    <div class="row" id="today-events">
                        <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                            <h4>{{ Lang::get('event.todays_event') }}</h4>
                        </div>
                    </div>
                </div>
        @endif
        <!-- Events Ends Here-->
        </div>
    </div>
    <!--Plug-in Initialisation-->
    <script type="text/javascript">
        $(document).ready(function() {
            //Vertical Tab
            $('#announceTab').easyResponsiveTabs({
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

    <script type="text/javascript">
        $('.favourite').on('click', '.fav-packet', function(e) {
            e.preventDefault();
            var action = $(this).data('action');
            var packet_id = $(this).attr('id');
            if(action == 'favourite') {
                $("#"+packet_id).removeClass("l-gray").addClass("red");
                $.ajax({
                    type: 'GET',
                    url: "{{ url('program/packet-favourited/favourite') }}/"+packet_id
                })
                    .done(function(response) {
                        if(response.status == true) {
                            $("#"+response.packet_id).data('action', 'unfavourite');
                        } else {
                            $("#"+response.packet_id).removeClass("red").addClass("gray");
                        }
                    })
                    .fail(function(response) {
                        $("#"+packet_id).removeClass("red").addClass("gray");
                        alert( "Error while updating the post. Please try again" );
                    });
            }
            if(action == 'unfavourite') {
                $("#"+packet_id).removeClass("red").addClass("gray");
                $.ajax({
                    type: 'GET',
                    url: "{{ url('program/packet-favourited/unfavourite') }}/"+packet_id
                })
                    .done(function(response) {
                        if(response.status == true) {
                            $('#'+response.packet_id).data('action', 'favourite');
                        } else {
                            $('#'+response.packet_id).removeClass('gray').addClass('red');
                        }
                    })
                    .fail(function(response) {
                        $('#'+response.packet_id).removeClass("gray").addClass("red");
                        alert( "Error while updating the post. Please try again" );
                    });
            }
        });
    </script>
    <script type="text/javascript" src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/js/jquery.calendario.js') }}"></script>
    <script>


        $(document).ready(function(){
                <?php if($general->setting['events'] == "on") { ?>
            var calendar = $("#calendar").calendario({
                    displayWeekAbbr : true,
                    onDayClick: function($el, $content, dateProperties){
                        showEvents(dateProperties);
                        $("#calendar").find('.date-active').removeClass('date-active').css("border","");
                        $el.closest("div").css('border','1px solid #ef6c6c').addClass("date-active");//date-active is a dummy class
                    }
                });
            $("#custom-month").html(calendar.getMonthName());
            $("#custom-year").html(calendar.getYear());

            xmlHTTPRequestObj = $.ajax({
                type : "GET",
                url : "{!! URL::to("event?show=today&context=user-dashboard") !!}",
                dataType : "json",
                contentType : "application/x-www-form-urlencoded; charset=UTF-8"
            });

            xmlHTTPRequestObj.done(function(response, textStatus, jqXHR){
                eventsContainer = $("#today-events");

                eventsContainer.after(response.data);
                calendar.setData(calendarData);
            });
            function convertTime24to12(time24){
                var tmpArr = time24.split(':'), time12;
                if(+tmpArr[0] == 12) {
                    time12 = tmpArr[0] + ':' + tmpArr[1] + ' pm';
                } else {
                    if(+tmpArr[0] == 00) {
                        time12 = '12:' + tmpArr[1] + ' am';
                    } else {
                        if(+tmpArr[0] > 12) {
                            time12 = (+tmpArr[0]-12) + ':' + tmpArr[1] + ' pm';
                        } else {
                            time12 = (+tmpArr[0]) + ':' + tmpArr[1] + ' am';
                        }
                    }
                }
                return time12;
            }
            <?php $a = 1; ?>
                showEvents = function(date){

                var events = $.ajax({url:"{{ URL::to('event')}}?show=today&context=popover&day="+date.day+"&month="+date.month+"&year="+date.year, dataType: 'json'});
                events.done(function(response){
                    var titles = '';

                    if(response.status){
                        $.each(response.data.events, function(index, event){
                            titles += '<li>'
                            if(event.type == "live")
                                $class = 'label-primaryaq';
                            else
                                $class = 'label-danger';
                            var url = "{{ URL::to('event')}}?show=custom&day="+date.day+"&month="+date.month+"&year="+date.year;

                            //accordian
                            titles+= '<div class="panel-group accordion announce-tabs" id="accordion'+index+'">';
                            titles += '<div class="panel panel-default border-btm-0"><div class="panel-heading"><h4 class="panel-title"><a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion'+index+'" href="#collapse_3_'+index+'">';

                            titles +='<div class="event-btn"><span class="white label '+$class+'">'+event.event_type.toUpperCase()+'</span></div>';
                            titles += '<div class="right"><span class="event-label">'+event.event_name+' starts at '+convertTime24to12(event.start_time_label)+'</span></div>';

                            titles += '</a></h4></div></div>';
                            titles +=  '<div id="collapse_3_'+index+'" class="panel-collapse collapse"> <div class="panel-body"><p>';


                            //description
                            titles += '<div><span>'+((event.event_description != '' ) ? limitTo(event.event_description) : '')+'</span>'+'<span><a href="'+url+'">More </a></span></div>';
                            // description
                            // Event Stream Url
                            if(typeof events.recordings !== 'undefined') {
                                var eventsarray = $.map(event.recordings, function(value, index) {
                                    return [value];
                                });
                                titles += '<table class="table event-details-table" cellspacing="0" cellpadding="2" border="0">';
                                var eventi = 0;
                                eventsarray.forEach(function(entry) {
                                    if(typeof entry.streamURL != 'undefined')
                                    {
                                        eventi++;
                                        var d = new Date(entry.created);
                                        var eventmin = d.getMinutes();
                                        var eventhour = d.getHours();
                                        titles += '<tr>';
                                        titles += '<td>Recording :'+eventi+'</td>';
                                        titles += '<td>'+eventmin+': '+eventhour+'</td>';
                                        titles += '<td><a class="btn sm-btn" style="background-color: #FF9900" href="'+ entry.streamURL +'" target="_blank">'+"{{ Lang::get('event.stream_url') }}"+'</a></td>';
                                        titles += '</tr>';
                                    }

                                });
                                titles += '</table>';
                            }
                            //event stream url ends here
                            titles +=  '</p></div></div>';
                            titles += '</div></div>';
                            titles += '</li>';
                            //  accordian ends
                        });

                    }
                    else{
                        titles = response.message;
                    }
                    $('#today-events').children().children().html('Events on '+date.day+' - '+date.month+' - '+date.year);
                    $('.events-list').html(titles);
                });
                events.fail(function(response){
                });
            };
            <?php } ?>
        });

        function limitTo(str)
        {
            if(str.length > 0)
            {
                return (str.length >= 150)? str.substr(0, 150).concat("..."): str;
            }

            return;
        }
    </script>
@stop