<?php
    $i=0;
$now=time();
?>
@if(isset($programs) && empty($programs))
    <div class="col-md-4 col-sm-6 col-xs-12 xs-margin">
        <p style="word-wrap:break-word;">
            {{trans('program.no_records_found')}}
        </p>
    </div>
@elseif(is_array($programs) && !empty($programs))
   @foreach($programs as $program)
    <?php
        $program_package_name = null;
        if (!empty($program["packages"])) {
            foreach ($program["packages"] as $package) {
                $program_package_name = $package["package_title"];
                break;
            }
        }
        $i=$program['program_id'];
    ?>
   @if((Timezone::getTimeStamp($program['program_startdate']) <= $now) && (Timezone::getTimeStamp($program['program_enddate']) >= $now)) 
        <!--start content feed 1-->
        <?php
            $array_packets['new'] = array();
            $array_packets['completed'] = array();
        ?>
        <div>
            <?php
                $program_title = $program['program_title'];
                if ($program['program_type'] == 'course') {
                    $program_title = $program['course_batch_name'];
                }
            ?>
            <!--h3 class="page-title margin-top-10"-->

            <h3 class="page-title margin-top-10 padding-left-0 font-weight-500 black uppercase" style="
    /*background: linear-gradient(#353e38d1, #96a9a7);*/
    border: 3px inset #49bbaf;
    width: 90%;
    padding-left: 8px;
"><i class="fa fa-book green font-20"></i> 
                <a href="{{URL::to('program/packets/'.$program['program_slug'])}}" style="color: #0000009e !important">
                    {{$program_title}}
                </a>&nbsp;
            @if(empty($program['packets']))
                <?php $info="";
                $packet_view="display:none;"; ?>
            @else
                <?php $info="display:none;";
                $packet_view=""; ?>
                <span class="l-gray">|</span>
                <small>
                    <!--a href="javascript:SwapDivsWithClick('cf1-packets<?php // echo $i; ?>','cf1-info<?php  //echo $i; ?>', <?php  //echo $i; ?>,this)" id="contentfeed{{$i}}" class="btn btn-success btn-sm margin-bottom-5" >
                        {{ trans('program.more_info') }}
                    </a-->
                    <a href="{{URL::to('program/what-to-watch')}}" class="btn btn-success btn-sm margin-bottom-5 pull-right" style="margin-top: 2px"><i class="fa fa-video-camera"></i> {{ trans('program.tab_watch_now') }}</a>
                     <a href="{{URL::to('program/packets/'.$program['program_slug'])}}" class="btn btn-success btn-sm margin-bottom-5 pull-right" style="margin-top: 2px"><i class="fa fa-file-text-o"></i> {{ trans('program.tab_posts') }}</a>
                </small>
            </h3>
            @endif
        </div>
        <div class="md-margin cf-info white-bg" id="cf1-info<?php  echo $i; ?>" style="{{$info}};display: block!important;">
            <div class="row">
                <div class="col-md-2 col-sm-6 col-xs-12 xs-margin">
                    <?php $url = (!empty($program['program_cover_media'])) ?  URL::to('media_image/'.$program['program_cover_media']) : URL::asset($theme.'/img/science.jpg'); ?>
                    <img src="{{ $url }}" alt="Channel" class="packet-img img-responsive center-align img-circle">
                </div>
                <!--static code for about-->
                <div class="col-md-2 col-sm-6 col-xs-12" style="
    background-color: #6aab472e;
    border: 1px dashed #6aab;
    height: 135px;
    margin-left: -17px
">
<h3 class="font-weight-500 margin-top-10 text-center">
    About subject
    </h3>
<hr style="
    margin: 8px 0px 20px 31px;
    border: 0;
    width: 71%;
    height: 2px;
    background-image: -webkit-linear-gradient(left, #45b6af30, #45b6af, #45b6af30);
">
    <p style="/* text-indent: 15px; */font-family: &quot;Open Sans&quot;, sans-serif;" class="text-center">
        
        About dummy content sometghifsdsdssdfsdfdfs
</p>
</div>
                <!--div class="col-md-5 col-sm-5 col-xs-12"-->
                <div class="col-md-4 col-sm-5 col-xs-12" style="
    background-color: #b3e5fce0;
    margin-left: 14px;
    height: 134px;
    border: 1px dashed #49bbaf;
    width: 25%;
">
   
                    <p style="word-wrap:break-word;">
                        {!! $program['program_description'] !!}
                    </p>
                    <table>
                        <tr>
                            @if(!empty($program['assigned_cat_details']))
                            <td width="140px" valign="top">
                                <strong>
                                    <?php echo $category_name = (count($program['assigned_cat_details']) > 1) ? trans('category.categories') : trans('category.category'); ?>
                                </strong>
                            </td>
                            <td style="word-break: break-all;">
                                {{ implode(', ', collect($program['assigned_cat_details'])->pluck(['category_name'])->toArray()) }}
                            </td>
                            @endif
                        </tr>
                        <tr>
                            <td width="140px" valign="top"><strong>{{ trans('program.start_date') }}</strong></td>
                            <td>
                                {{ Timezone::convertFromUTC('@'.$program['s_date'], Auth::user()->timezone, Config('app.date_format')) }}
                            </td>
                        </tr>
                        <tr>
                            <td width="140px" valign="top"><strong>{{ trans('program.end_date') }}</strong></td>
                            <td>
                                {{ Timezone::convertFromUTC('@'.$program['e_date'], Auth::user()->timezone, Config('app.date_format')) }}
                            </td>
                        </tr>
                        @if((isset($program['program_sub_type'])  && $program['program_sub_type']=='single') || !isset($program['program_sub_type']))
                            <tr>
                                <td width="140px" valign="top"><strong>No. of {{trans('program.packets')}}</strong></td>
                                <td>{{$program['packet_count']}}</td>
                            </tr>
                        @endif
                        <tr>
                            <td width="140px" valign="top"><strong>{{ trans('program.status') }}</strong></td>
                            <td>{{$program['status']}}</td>
                        </tr>
                        @if(!is_null($program_package_name))
                            <tr>
                                <td width="140px" valign="top"><strong>{{ trans('program.package') }}</strong></td>
                                <td>{{ $program_package_name }}</td>
                            </tr>
                        @endif
                    </table>
                    <p>
                        <a href="{{URL::to('program/packets/'.$program['program_slug'])}}" class="btn btn-success btn-sm margin-bottom-5">{{ trans('program.more') }} <i class="fa fa-angle-double-right" aria-hidden="true"></i> </a>
                    </p>
                </div>
                <!-- over all channel analytics  -->
                <?php
                    $sepecificChannelAnaltic = $channelAnalytics->get((int)$program['program_id']);
                ?>
                @if(!is_null($sepecificChannelAnaltic) && config('app.channelAnalytic') == 'on')
                <?php
                    if (isset($program['benchmarks'])) {
                        $benchMarkSpeed = isset($program['benchmarks']['speed']) ?
                                            $program['benchmarks']['speed'] : 1;
                        $benchMarkScore = isset($program['benchmarks']['score']) ?
                                            $program['benchmarks']['score'] : 1;
                        $benchMarkAccuracy = isset($program['benchmarks']['accuracy']) ?
                                            $program['benchmarks']['accuracy'] : 1;
                    } else {
                        $benchMarkSpeed = 0;
                        $benchMarkScore = 0;
                        $benchMarkAccuracy = 0;
                    }

                    $score = array_get($sepecificChannelAnaltic, 'score', 0);
                    $accuracy = array_get($sepecificChannelAnaltic, 'accuracy', 0);
                    $speed = array_get($sepecificChannelAnaltic, 'speed', "0:0:0");
                    $completion = array_get($sepecificChannelAnaltic, 'completion', 0);
                    $analyticBenchMarkSpeed = array_get($sepecificChannelAnaltic, 'speed_secs', 1);
                    $statusSpeed = ($analyticBenchMarkSpeed > $benchMarkSpeed) ? "LOW" :"HIGH";
                    $ssSpeed = ($analyticBenchMarkSpeed % 60) > 10 ?
                                    $analyticBenchMarkSpeed % 60 :
                                    '0'.$analyticBenchMarkSpeed % 60;
                    $mmSpeed = intval($analyticBenchMarkSpeed / 60);
                    $mmSpeed = ($mmSpeed > 10) ?
                                $mmSpeed  : '0'.$mmSpeed;
                ?>
                <div class=" col-md-4 col-sm-4 col-xs-12 custom-analytics">
                    <div class="analytic-div">
                    @if(isset($general->setting['quiz_marics']['quiz_score']) && $general->setting['quiz_marics']['quiz_score'] == 'on')
                        <div>
                          <div class="left">
                            <?php
                                if ($benchMarkScore > 0) {
                                    $scoretile = trans('assessment.score_channel_benchmark');
                                } else {
                                    $scoretile = trans('assessment.score_channel');
                                }
                            ?>
                            <img src="{{URL::asset($theme.'/img/icons/icons-05.png')}}" alt="score" title="{{$scoretile}}" width="22">
                          </div>
                          <div class="right">
                            <div class="progress score-bar">
                                <div style="width: {{$score}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar progress-bar-striped active progress-bar-animated">
                                </div>
                                <div style="position: absolute;" class="white">
                                    {{$score}}%
                                </div>
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
                            <?php
                            if ($benchMarkAccuracy > 0) {
                                $accuracyTitle = trans('assessment.accuracy_channel_benchmark');
                            } else {
                                $accuracyTitle = trans('assessment.accuracy_channel');
                            }
                            ?>
                            <img src="{{URL::asset($theme.'/img/icons/icons-07.png')}}" alt="accuracy"
                            title="{{$accuracyTitle}}" width="22">
                          </div>
                          <div class="right">
                            <div class="progress accuracy-bar">
                                <div style="width: {{$accuracy}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar progress-bar-striped active progress-bar-animated"> 
                                </div>
                                <div style="position: absolute;" class="white">
                                    {{$accuracy}}%
                                </div>
                              @if($benchMarkAccuracy > 0)
                                <span class="bench-mark" style="left:{{$benchMarkAccuracy}}%;">
                                </span
                                >
                              @endif
                            </div>
                          </div>
                        </div><!--  accuracy-bar -->
                    @endif
                    @if(isset($general->setting['quiz_marics']['channel_completion']) && $general->setting['quiz_marics']['channel_completion'] == 'on')
                        <div>
                          <div class="left">
                            <img src="{{URL::asset($theme.'/img/icons/completion.png')}}" alt="Completion" title="{{trans('assessment.completion_level')}}" width="22">
                          </div>
                          <div class="right">
                            <div class="progress completion-bar">
                                <div style="width: {{$completion}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar progress-bar-striped active progress-bar-animated"> 
                                </div>
                                <div style="position: absolute;" class="white">
                                    {{$completion}}%
                                </div>
                            </div>
                          </div>
                        </div><!-- completion-bar -->
                    @endif
                    @if(isset($general->setting['quiz_marics']['quiz_speed']) && $general->setting['quiz_marics']['quiz_speed'] == 'on')
                        <?php
                            if ($benchMarkSpeed > 0) {
                                $speedTitle = trans('assessment.speed_detail_benchmark');
                            } else {
                                $speedTitle = trans('assessment.speed_detail');
                            }
                        ?>
                        <div>
                          <div class="left">
                            <img src="{{URL::asset($theme.'/img/icons/icons-03.png')}}" alt="time" title="{{$speedTitle}}" width="22">
                          </div>
                          <div class="right">
                            <span>{{$mmSpeed}}:{{$ssSpeed}}</span>
                            <span>&nbsp;(MM:SS) </span>
                            @if($benchMarkSpeed > 0)
                                &nbsp;|<span>&nbsp;{{$statusSpeed}}</span>
                            @endif
                          </div>
                        </div><!-- time-bar -->
                    @endif
                      </div>
                </div>
                @endif
            </div>
        </div><!-- END CF Info-->
        
        <div class="row cf-packets" id="cf1-packets<?php  echo $i; ?>" style="{{$packet_view}}; display: none!important;" >
            <div class="col-md-12 nav-space md-margin border-btm">
                <div class="owl-carousel owl-theme sm-margin">
                @foreach($program['packets'] as $packet)
                    <div class="item white-bg">
                        <a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}" title="{{$packet['packet_title']}}">
                            <div class="packet">
                                <figure>
                                    @if(empty($packet['packet_cover_media']))
                                        <img src="{{URL::asset($theme.'/img/book.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
                                    @else
                                        <img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
                                    @endif
                                    @if(in_array($packet['packet_id'], $array_packets['new']))
                                        <img class="new-label" src="{{URL::asset($theme.'/img/new-label.png')}}" alt="New label">
                                    @endif
                                    @if(in_array($packet['packet_id'], $array_packets['completed']))
                                        <span class="completed-overlay">
                                            <img src="{{URL::asset($theme.'/img/completed.png')}}" class="img-responsive" alt="Completed">
                                        </span>
                                    @endif
                                </figure>
                                <div>
                                    <h4 class="packet-title bold uppercase center margin-top-10">
                                        {{str_limit(($packet['packet_title']), $limit = 40, $end = '...')}}
                                    </h4>
                                    <p class="packet-data">
                                        <span class="gray">
                                            {{count($packet['elements'])}} @if(count($packet['elements']) <= 1) {{str_singular('items')}}@else items @endif<br>
                                        </span>
                                        <span class="l-gray font-12  gray">
                                            {{ date('d M Y', strtotime(Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))))}}
                                        </span>
                                        <span class="pull-right">
                                            @if(in_array($packet['packet_id'], $favorites))
                                                <?php
                                                    $action="unfavourite";
                                                    $class="red";
                                                ?>
                                            @else
                                                <?php
                                                    $action="favourite";
                                                    $class="gray";
                                                ?>
                                            @endif
                                            <span class="favourite">
                                                <i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}} fav-packet" style="cursor:pointer;background-color: #f95246;">
                                                </i>
                                            </span>
                                        </span>
                                    </p>
                                </div>
                            </div><!--packet-->
                        </a>
                    </div><!--packet div-->
                @endforeach
                </div>
            </div>
        </div><!--ENd Packets div-->
        <!--end content feed 1-->
    @else
        <?php
            $program_title = $program['program_title'];
        if ($program['program_type'] == 'course') {
            $program_title = $program['course_batch_name'];
        }
        ?>
        <div>
            <h3 class="page-title-small">{{$program_title}}</h3>
        </div>
        @if((Timezone::getTimeStamp($program['program_enddate'])) < $now) 
            <div class="md-margin cf-info pkt-opacity">
        @else
            <div class="md-margin cf-info">
        @endif
            <div class="row">
                <div class="col-md-4 col-sm-6 col-xs-12">
                    <?php  $url = !empty($program['program_cover_media']) ? URL::to('media_image/'.$program['program_cover_media']) : URL::asset($theme.'/img/default_channel.png'); ?>
                    <img src="{{ $url }}" alt="Program" class="img-responsive">
                </div>
                <div class="col-md-5 col-sm-5 col-xs-12">
                    <table>
                        <tr>
                            <td colspan="2">
                                @if((Timezone::getTimeStamp($program['program_startdate'])) > $now) 
                                    <label class="label label-primary">
                                        <strong>
                                            {{ trans('program.coming_soon') }}
                                        </strong>
                                    </label>
                                @endif
                                @if((Timezone::getTimeStamp($program['program_enddate']) < $now)) 
                                    <label class="disable-lable">{{ trans('program.expired') }}</label>
                                @endif
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2">
                                {!! $program['program_description'] !!}
                            </td>
                        </tr>
                        <tr>
                            @if(!empty($program['assigned_cat_details']))
                            <td width="140px" valign="top">
                                <strong>
                                    <?php echo $category_name = (count($program['assigned_cat_details']) > 1) ? trans('category.categories') : trans('category.category'); ?>
                                </strong>
                            </td>
                            <td>
                                {{ implode(', ', collect($program['assigned_cat_details'])->pluck(['category_name'])->toArray()) }}
                            </td>
                            @endif
                        </tr>
                        <tr>
                            <td width="140px"><strong>{{ trans('program.start_date') }}</strong></td>
                            <td>
                                {{ Timezone::convertFromUTC('@'.$program['s_date'], Auth::user()->timezone, Config('app.date_format')) }}
                            </td>
                        </tr>
                        <tr>
                            <td width="140px"><strong>{{ trans('program.end_date') }}</strong></td>
                            <td>
                                {{ Timezone::convertFromUTC('@'.$program['e_date'], Auth::user()->timezone, Config('app.date_format')) }}
                            </td>
                        </tr>
                        @if((isset($program['program_sub_type'])  && $program['program_sub_type']=='single') || !isset($program['program_sub_type']))
                        <tr>
                            <td width="140px"><strong>No. of {{trans('program.packets')}}</strong></td>
                            <td>{{$program['packet_count']}}</td>
                        </tr>
                        @endif
                        <tr>
                            <td width="140px"><strong>{{ trans('program.status') }}</strong></td>
                            <td>{{$program['status']}}</td>
                        </tr>
                    </table>
                </div>
            </div>
        </div><!-- END CF Info-->
        @endif
    @endforeach
@endif