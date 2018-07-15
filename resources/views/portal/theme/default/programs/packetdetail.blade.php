@section('content')
<script src="{{ asset("portal/theme/default/js/sec_to_hh_mm_ss.js") }}"></script>
<style type="text/css">
.wrapper-video, #akamai_player_wrapper {width:100%;height:100%;margin:0 auto;}
.h_iframe        {position:relative;}
.h_iframe .ratio {display:block;width:100%;height:auto;}
.h_iframe iframe, .h_iframe object {position:absolute;top:0;left:0;width:100%; height:100%;}
.faq_inactive{
    display:none;
}
.faq_active{
    display:block;
}
.accordion .panel .panel-title .accordion-toggle {
    padding: 4px 15px;
}
#demo article { word-wrap: break-word;}
#demo article p { margin-bottom: 0; }
#demo article.readmore-js-section { margin-bottom: 5px; }
.recordings-table>tbody>tr>td {
    border: none !important;
}
.blueTxt{
    color: #3598dc !important;
    font-size: 16px;
}
.recordings-table-cont {
    max-height: 140px !important;
}
.panel, .panel-default {
    border: none !important;
}
.opacity-40 { opacity: 0.4;}
.nav-tabs > li > a, .nav-pills > li > a {
    font-size: 14px;
    width: 100%;
    border: 2px outset #928a8a;}
    
    .nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {
    color: #fff;
    background-color: #02b7bf!important;
    border: 2px outset #839696!important;
}
</style>
<?php
    use App\Enums\Assignment\SubmissionType;
    $course_title_fun = function($program_id,$program_title){

        $parent_course = Session::get('parent_course');

        $course_title = '';

        array_where($parent_course, function($key, $value)

            use ($program_id,&$course_title)
                {

                    if($program_id == $value['program_id'])
                    {

                        $course_title = $value['program_title'];

                    }

                });

        return $course_title." - " .$program_title;

    };

    $program_title = $program['program_title'];

    if($program['program_type'] == 'course')
    {

        $program_title = $course_title_fun($program['parent_id'],$program_title);

    }

?>
    <!-- BEGIN PAGE HEADER-->
    <div class="page-bar margin-left-10">
        <ul class="page-breadcrumb">
            <li><a href="{{url('program/my-feeds')}}">{{trans('program.my_course')}}</a><i class="fa fa-angle-right"></i></li>

            <li><a href="{{url('program/packets/'.$packet['feed_slug'])}}">{{str_limit($program_title, $limit = 50, $end = '...')}}</a><i class="fa fa-angle-right"></i></li>
            <li><a href="#">{{str_limit($packet['packet_title'], $limit = 50, $end = '...')}}</a></li>
        </ul>
    </div>
    <!-- END PAGE HEADER-->
    <!--content starts here-->
    @if(!$inactive)
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <!--packet item timeline for mobile-->
                    <div id="panel-mob" class="panel-mob">
                        <!--pocket items starts here-->
                        <div class="pkt-items">
                            <div class="panel panel-default">
                                <div class="center border-btm">
                                    <a type="button" href="{{URL::to('program/packets/'.$packet['feed_slug'])}}" class="btn btn-primary xs-margin btn-sm"><i class="fa fa-street-view"></i> View All {{trans('program.packets')}}</a>
                                </div>
                                @if($packet['sequential_access'] == 'yes')
                                    <!-- <em class="font-13">(Sequential)</em> -->
                                    <?php $sequential="timeline"; ?>
                                @else
                                    <?php $sequential=""; ?>
                                @endif
                                <div class="panel-body">
                                    <div class="{{$sequential}}">
                                    <!-- TIMELINE ITEM -->
                                    <?php
                                        if(!empty($elements))
                                        {
                                            $le=0;
                                            $last_element_id=$elements[0]['id'];
                                            $last_element_type=$elements[0]['type'];
                                        }
                                    ?>
                                    @foreach($elements as $element)
                                    <?php $le=1; ?>
                                        <?php
                                            $media_id = array_get($element, 'id');
                                            $elements_data = array_get($posts_data, $media_id, []);
                                            $activity = array_get($viewdEleTypeIds, $element['type'], []);
                                            $seen_class='pkt-item-gray';
                                            if(is_array($asset) && !empty($asset))
                                            {
                                                if(($asset['element_type'] == $element['type']) && ($asset['id'] == $element['id']))
                                                {
                                                    $seen_class='pkt-item-progress';
                                                }
                                                elseif(in_array($element['id'], $activity))
                                                {
                                                    $seen_class='pkt-item-blue';
                                                }
                                                else
                                                {
                                                    $seen_class='pkt-item-gray';
                                                }
                                            }
                                        ?>
                                        <div class="timeline-item">
                                            <div class="timeline-badge">
                                                <div class="{{$seen_class}}"><i class="fa {{array_get($elements_data, 'class')}}"></i></div>
                                            </div>
                                            <div class="timeline-body transparent-bg">
                                                <div class="timeline-body-head">
                                                    <div class="timeline-body-head-caption">
                                                        <div href="javascript:;" class="timeline-body-title font-blue-madison">
                                                            <?php $last_element_activity = array_get($viewdEleTypeIds, $last_element_type, []);
                                                            ?>
                                                            @if($packet['sequential_access'] == 'yes' && !in_array($last_element_id, $last_element_activity) )
                                                                <a title="{{trans('program.sequential_elements_with_order')}}">{{array_get($elements_data, 'name')}}</a>

                                                            @else
                                                                @if(!isset($isQuizzesPass[$last_element_id]) || !(isset($isQuizzesPass[$last_element_id])
                                                                && !$isQuizzesPass[$last_element_id]))
                                                                    <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/element/'.$element['id'].'/'.$element['type'])}}">{{array_get($elements_data, 'name')}}</a>
                                                                @else
                                                                    <a title="{{trans('program.sequential_elements_with_order')}}">{{array_get($elements_data, 'name')}}</a>
                                                                @endif
                                                            @endif
                                                        </div>
                                                        <span class="timeline-body-time font-grey-cascade"><i>{{array_get($elements_data, 'type')}}</i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php
                                            $last_element_id=$element['id']; $last_element_type=$element['type'];
                                        ?>
                                    @endforeach
                                    <!-- END TIMELINE ITEM -->
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!--pocket items end here-->
                    </div>
                    <a id="pkt-trigger" class="pkt-trigger">
                        <i class="fa fa-arrow-circle-left"></i>&nbsp;{{count($elements)}}
                                @if(count($elements) > 1)
                                    Items
                                @else
                                    Item
                                @endif
                    </a>
                <!--end here-->


                <div id="accordion1" class="panel-group accordion">
                    <div class="panel-default transparent-bg">
                        <div class="panel-heading">
                            <h4 class="panel-title m-btm-12">
                                <div class="row">
                                    <div class="col-md-10">
                                        <span class="caption gray uppercase">{{$packet['packet_title']}}&nbsp;&nbsp;</span>
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
                                        <span id="favourite">
                                            <i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}} fav-packet" style="cursor:pointer"></i>
                                        </span>
                                        <span class="font-grey-cascade">&nbsp;&nbsp;|&nbsp;<em class="font-13">{{ Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))}}</em></span>
                                    </div>
                                    @if(!empty($asset))
                                        <?php $collapse=""; $collapse_class="collapsed"; ?>
                                    @else
                                        <?php $collapse="in"; $collapse_class=""; ?>
                                    @endif
                                    @if(!empty($packet['packet_description']))
                                        <div class="col-md-2">
                                            <a class="accordion-toggle accordion-toggle-styled {{$collapse_class}}" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_1" aria-expanded="false">
                                            <span class="description font-grey-cascade">{{ trans('program.description') }}</span>
                                            </a>
                                        </div>
                                    @endif
                                </div>
                            </h4>
                        </div>
                        <div id="accordion1_1" class="panel-collapse collapse {{$collapse}}" aria-expanded="false" >
                            <div class="panel-body">{!! $packet['packet_description'] !!}</div>
                        </div>
                    </div>
                </div>
                @if(!empty($asset))
                    <div class="panel panel-default">
                        <div class="panel-heading sequential-panel-header">
                            <?php
                                if(isset($asset['display_name']) && $asset['display_name'] != '')
                                {
                                    $name=$asset['display_name'];
                                }
                                else
                                {
                                    $name=$asset['name'];
                                }
                            ?>
                                {{$name}}&nbsp;&nbsp;
                                @if((array_get($asset, 'type') ==  'document') && $asset['asset_type'] == "file" && $file_download == true &&  !empty($viewer_session_id))
                                    <a href="{{URL::to('/media_image/'.$asset['_id'])}}"><span class="pkt-document-download"><i class="icon-arrow-down" title="{{trans('program.document_download_btn')}}"></i></span></a>
                                @endif&nbsp;&nbsp;&nbsp;&nbsp;
                                @if((array_get($asset, 'element_type') ==  'assessment'))
                                    @if(isset($asset['quiz_description']) && !empty($asset['quiz_description']))
                                        <a href="javascript:showModal();" class="btn l-gray info-btn" title="{{trans('assessment.instructions')}}"><i class="fa fa-question"></i></a>
                                    @endif
                                @endif
                            @if(isset($asset['users_liked']) && in_array(Auth::user()->uid, $asset['users_liked']))
                                <?php
                                    $action="unstar";
                                    $class="yellow";
                                ?>
                            @else
                                <?php
                                    $action="star";
                                    $class="gray";
                                ?>
                            @endif
                            <!--<span id="element-like">
                                <i id="{{$asset['id']}}" data-action="{{$action}}" data-type="{{$asset['element_type']}}" data-packet="{{$packet['packet_id']}}" class="fa fa-star {{$class}} star-element" style="cursor:pointer"></i>
                            </span>-->
                            <div class="page pull-right">
                                <?php $element_i=0;$elemnt_count=count($elements);
                                    $original_elements=$elements;
                                ?>
                                @foreach($elements as $element)
                                    <?php $element_i=$element_i+1; ?>
                                    @if(($asset['element_type'] == $element['type']) && ($asset['id'] == $element['id']))
                                        @if($element_i == 1)
                                            @if($element_i != $elemnt_count && ((!isset($isQuizzesPass[$element['id']]) || !(isset($isQuizzesPass[$element['id']])
                                                                && !$isQuizzesPass[$element['id']]))))
                                                <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i]['id'].'/'.$original_elements[$element_i]['type'])}}"><button>Next <i class="fa fa-arrow-right"></i></button></a>
                                            @endif
                                        @elseif($element_i == $elemnt_count)
                                            <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i-2]['id'].'/'.$original_elements[$element_i-2]['type'])}}"><button> <i class="fa fa-arrow-left"></i> Prev </button></a>
                                        @else
                                            <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i-2]['id'].'/'.$original_elements[$element_i-2]['type'])}}"><button> <i class="fa fa-arrow-left"></i> Prev</button></a>
                                            @if((!isset($isQuizzesPass[$element['id']]) || !(isset($isQuizzesPass[$element['id']])
                                                                && !$isQuizzesPass[$element['id']])))
                                            <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i]['id'].'/'.$original_elements[$element_i]['type'])}}"><button>Next  <i class="fa fa-arrow-right"></i></button></a>
                                            @endif

                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="panel-body">
                            @if(!empty($asset) && $asset['element_type'] == "media")
                                <?php switch ($asset['type']) {
                                        case "video" : {
                                             ?>
                                                @include('media.displayvideo', ['media' => $media])
                                            <?php

                                            break;
                                        }
                                        case "image" :{
                                            if($asset['asset_type'] == "file"){
                                                ?>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 download-align custom-box center sm-margin">
                                                        <img src="{{URL::to('media_image/'.$asset['_id'])}}" class="img-responsive" alt="Image">
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        @if(!empty($asset['description']))
                                                            <p><strong>{{ trans('program.description') }}</strong></p>
                                                            <div id="demo">
                                                                <article>{!! $asset['description'] !!}</article>
                                                            </div>
                                                        @endif
                                                    </div>
                                                <?php
                                            }
                                            else{ ?>
                                                    <h4>{{trans('program.to_view_image')}}</h4>

                                                    <a href="{{$asset['url']}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                    <br><br>
                                                    @if(!empty($asset['description']))
                                                        <p><strong>{{ trans('program.description') }}</strong></p>
                                                        <div id="demo">
                                                            <article>{!! $asset['description'] !!}</article>
                                                        </div>
                                                    @endif
                                                <?php
                                            }
                                            break;
                                        }
                                        case "scorm" : {
                                        ?>
                                        <script src="{{ asset("portal/theme/default/js/scorm_api.js") }}"></script>
                                        <script>
                                            window.API = getScormAPI(
                                                {
                                                    lmsUrl : "{{ URL::to("/") }}",
                                                    packet_id : {{ $packet["packet_id"] }},
                                                    element_id : {{ $asset["id"] }}
                                                },
                                                {!! json_encode($userActivityDataForElement) !!}
                                            );
                                        </script>
                                        <?php
                                            if (!empty($asset["launch_file"])) {
                                                $scorm_file_name = "/".$asset["launch_file"];
                                            } else {
                                                $scorm_file_name = config("app.scorm_file_name");
                                            }

                                            if($asset['visibility'] == 'public')
                                            {
                                                $file_location = $asset['public_file_location'];
                                            }
                                            else
                                            {
                                                $file_location = $asset['private_file_location'];
                                            }

                                            if($asset['asset_type'] == "file"){
                                                ?>

                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 download-align custom-box center xs-margin">

                                                   <a href="javascript: void(0)"
                                                      onclick="popup('{{URL::to($file_location.$scorm_file_name)}}')">
                                                       <img class="img-responsive" src="{{URL::asset($theme.'/img/scormview.png')}}">
                                                   </a>

                                                    <script type="text/javascript">
                                                    function popup(url)
                                                    {
                                                         params  = 'width='+screen.width;
                                                         params += ', height='+screen.height;
                                                         params += ', top=0, left=0'
                                                         params += ', fullscreen=yes';

                                                         var newWindow = window.open(url,'windowname4', params);
                                                         if (window.focus) { newWindow.focus() }
                                                         return false;
                                                    }
                                                    </script>
                                                </div>

                                                <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                                    @if(!empty($asset['description']))
                                                        <p><strong>{{ trans('program.description') }}</strong></p>
                                                        <div id="demo">
                                                            <article>{!! $asset['description'] !!}</article>
                                                        </div>
                                                    @endif
                                                </div>

                                                <?php
                                            }
                                            else{ ?>
                                                    <h4>{{ trans('program.to_view_scorm')}}</h4>

                                                    <a href="{{URL::to($file_location.$scorm_file_name)}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                    <br><br>
                                                    @if(!empty($asset['description']))
                                                        <p><strong>{{ trans('program.description') }}</strong></p>
                                                        <div id="demo">
                                                            <article>{!! $asset['description'] !!}</article>
                                                        </div>
                                                    @endif
                                                <?php
                                            }
                                            break;
                                        }
                                        case "document" :{
                                            if($asset['asset_type'] == "file"){
                                                ?>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 download-align custom-box center xs-margin">
                                                        @if(isset($viewer_session_id) && !empty($viewer_session_id) && $viewer_session_id !='')
                                                        <iframe src="{{ $viewer_session_id }}" style="width:100%; max-width: 100%; height: 500px; border-radius: 5px; border: 1px solid #d9d9d9;"  allowfullscreen="allowfullscreen"></iframe>
                                                        @else
                                                            <a href="{{URL::to('/media_image/'.$asset['_id'])}}"><img src="{{URL::asset($theme.'/img/downloadfile.png')}}" class="img-responsive" alt="Document"></a>
                                                        @endif
                                                    </div>

                                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                                        @if(!empty($asset['description']))
                                                            <p><strong>{{ trans('program.description') }}</strong></p>
                                                            <div id="demo">
                                                                <article>{!! $asset['description'] !!}</article>
                                                            </div>
                                                        @endif
                                                    </div>
                                                <?php
                                            }
                                            else{ ?>
                                                    <h4>{{ trans('program.to_view_document')}}</h4>
                                                    <a href="{{$asset['url']}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                    <br><br>
                                                    @if(!empty($asset['description']))
                                                        <p><strong>{{ trans('program.description') }}</strong></p>
                                                        <div id="demo">
                                                            <article>{!! $asset['description'] !!}</article>
                                                        </div>
                                                    @endif
                                                <?php
                                            }
                                            break;
                                        }
                                        case "audio" :{
                                            ?>
                                                @include('media.displayaudio', ['media' => $media])
                                            <?php
                                            break;
                                        }
                                    }
                                ?>
                            @elseif(!empty($asset) && $asset['element_type'] == "assessment")
                                <?php
                                    $closedAttemepts = $attempts->where('status', 'CLOSED')->reverse();
                                ?>
                                <div class="post quiz-details">
                                    <div class="col-lg-7 col-md-4 col-sm-4 col-xs-7 sm-margin">
                                        <div class="col-lg-7 col-md-4 col-sm-4 col-xs-6 sm-margin">
                                            <div class="center">
                                                <img src="{{URL::asset($theme.'/img/packetpage_assessment.png')}}" class="img-responsive" alt="Assessment image" style="width:100%;">
                                                @if(isset($asset["practice_quiz"]) && !empty($asset["practice_quiz"]) && $asset["practice_quiz"])
                                                    <div class="asssesment-type" style="position: absolute; top: 3px; right: 15px; border: 1px none ! important; border-radius: 5px ! important;"><span style="font-weight: bold;" class="label label-success">{{ $asset['beta']?(trans("assessment.practice_quiz").' - '.trans("assessment.test_quiz")):trans("assessment.practice_quiz") }}</span></div>
                                                @elseif(isset($asset["type"]) && !empty($asset["type"]) && $asset["type"] == QuizType::QUESTION_GENERATOR)
                                                    <div class="asssesment-type" style="position: absolute; top: 3px; right: 15px; border: 1px none ! important; border-radius: 5px ! important;"><span style="font-weight: bold;" class="label label-warning">{{ $asset['beta']?(trans("assessment.question_generator").'-'.trans("assessment.test_quiz")):trans("assessment.question_generator") }}</span></div>
                                                @endif
                                            </div>
                                        </div>
                                        <div class="col-lg-5 col-md-3 col-sm-3 col-xs-6">
                                            <p class="font-16 sm-margin">
                                                {{count($asset['questions'])}}<strong> {{trans('assessment.questions')}}</strong>
                                            </p>
                                            @if(isset($asset['duration']) && $asset['duration'] != 0)
                                            <p class="font-16 sm-margin">
                                                <i class="fa fa-clock-o font-18"></i>&nbsp;&nbsp;<strong>{{ $asset['duration'].' Mins' }}</strong>
                                            </p>
                                            @endif
                                            <div class="row">
                                                <?php
                                                       $closed = $attempts->where('status','CLOSED')->reverse();
                                                    $lettAttemptCount = 0;
                                                    if (!(isset($asset["type"]) && ($asset["type"] === QuizType::QUESTION_GENERATOR)) && isset($asset['attempts']) )
                                                    {
                                                        if ($asset['attempts'] == 0){
                                                            $attempt_left = 'Unlimited Attempts';
                                                            $lettAttemptCount = -1;
                                                        } else {
                                                            if (($asset['attempts'] - $attempts->count()) == 1  || ($asset['attempts'] - $attempts->count()) == 0) {
                                                                $attempt_left = $asset['attempts'] - $attempts->count().' attempt left';
                                                                $lettAttemptCount = $asset['attempts'] - $attempts->count();
                                                            } else {
                                                                $attempt_left = $asset['attempts'] - $attempts->count().' attempts left';
                                                                $lettAttemptCount = $asset['attempts'] - $attempts->count();
                                                            }
                                                        }
                                                    } else {
                                                        $lettAttemptCount = -1;
                                                        $attempt_left = 'Unlimited Attempts';
                                                    }
                                                ?>
                                                <div class="col-lg-12 col-md-12 col-sm-3 col-xs-12 xs-margin">
                                                <?php $requestUrl = request::path(); ?>
                                                    @if(count($asset['questions']) == 0)
                                                        <a href="javascript:;" class="btn btn-default1 btn-lg">{{ trans('assessment.start') }}</a><br>
                                                    @elseif($program->program_startdate->timestamp < time() &&  $program->program_enddate->timestamp > time())
                                                        @if( (isset($asset["type"]) && ($asset["type"] === QuizType::QUESTION_GENERATOR)))
                                                            @if( is_null($attempts->first()) || $attempts->first()->status !== 'CLOSED')
                                                                <form id="question-generator" action="{{ url('assessment/question-generator/'.$asset['quiz_id']. '?requestUrl='.$requestUrl) }}" method="POST" accept-charset="utf-8">
                                                                    <input type="hidden" name="return" value="{{ request::path() }}">
                                                                    @if(isset($attempts->first()->status) && $attempts->first()->status == 'OPENED')
                                                                        <button type="submit" class="btn btn-success btn-lg">
                                                                        {{ trans('assessment.resume') }}
                                                                        </button>
                                                                    @else
                                                                        <button type="submit" class="btn btn-success btn-lg" >{{ trans('assessment.start') }} </button>
                                                                    @endif
                                                                </form>
                                                            @elseif($attempts->first()->status == 'CLOSED')
                                                                <a href="javascript:;" class="btn btn-default1 btn-lg">{{ trans('assessment.completed') }}</a>
                                                            @else
                                                                <a href="javascript:;" class="btn btn-default1 btn-lg">{{ trans('assessment.start') }}</a>
                                                            @endif
                                                        @else
                                                            @if(isset($asset['attempts']) )
                                                                @if($asset['attempts'] == 0 || $asset['attempts'] > $closed->count())
                                                                    <form id="quiz-attempt" action="{{ url('assessment/start-attempt/'.$asset['quiz_id']. '?requestUrl=' . $requestUrl) }}" method="POST" accept-charset="utf-8">
                                                                        <input type="hidden" name="return" value="{{ Request::path() }}">
                                                                        @if($attempts->where('status', 'OPENED')->count() > 0)
                                                                                <button type="submit" class="btn btn-success btn-lg">{{ trans('assessment.resume') }}
                                                                                </button><br><span class="font-12">{{ $attempt_left }}</span>
                                                                        @else
                                                                            <a data-id="{{ $asset['quiz_id'] }}" class="btn btn-success btn-lg begin">{{ trans('assessment.begin_quiz') }} </a><br><span class="font-12">{{ $attempt_left }}</span>
                                                                        @endif
                                                                    </form>
                                                                @else
                                                                    <a href="javascript:;" class="btn btn-default1 btn-lg">{{ trans('assessment.completed') }}</a><br><span class="font-13">{{ trans('assessment.no_attempts_eft') }}</span>
                                                                @endif
                                                            @endif
                                                        @endif
                                                    @else
                                                        <a  class="btn btn-success btn-lg" href= "{{ url('assessment/detail/'.$asset['quiz_id']) }}?requestUrl={{ $requestUrl }}">
                                                            <strong>
                                                             @if($lettAttemptCount == 0)
                                                                {{trans('assessment.quiz_results')}}
                                                            @else
                                                                {{trans('assessment.go_to_quiz')}}
                                                            @endif
                                                            </strong>
                                                            <br>
                                                            @if(!(isset($asset["type"]) && ($asset["type"] === QuizType::QUESTION_GENERATOR)))
                                                                <span class="font-13">{{ $attempt_left }}</span>
                                                            @endif
                                                        </a>
                                                    @endif
                                                </div>
                                            </div>
                                        </div><!--data-->
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 xs-margin">
                                            <div class="font-16 xs-margin">
                                                <span class="start col-lg-3">STARTS:</span>
                                                {{ $program->program_startdate->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}
                                                <br><span class="end col-lg-3">ENDS:</span>
                                                    {{ $program->program_enddate->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-lg-5 col-md-5 col-sm-5 col-xs-12 xs-margin">
                                        @if(($attempts->where('status','CLOSED')->count() > 0 || (isset($asset['type']) && $asset['type'] == QuizType::QUESTION_GENERATOR)) && (array_get($asset, 'is_score_display', true) == true))
                                            @if(isset($asset['type']) && $asset['type'] == QuizType::QUESTION_GENERATOR)
                                                <?php
                                                    $last_attempt = $attempts->first();
                                                ?>
                                            @else
                                                <?php
                                                    $last_attempt = $closedAttemepts->first();
                                                ?>
                                            @endif
                                            @if(!empty($last_attempt) )
                                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin border-btm">
                                                    <span class="blue font-16">
                                                        @if(array_get($asset, 'type', '') == QuizType::QUESTION_GENERATOR)
                                                            <strong>
                                                                {{trans('assessment.attempt_details')}}
                                                            </strong>
                                                            <br>
                                                        @else
                                                            <strong>
                                                                {{ trans('assessment.last_attempt_details') }}
                                                            </strong>
                                                            @if(isset($last_attempt->pass))
                                                                -
                                                                @if($last_attempt->pass)
                                                                    <strong class="correct-text">
                                                                        {{ trans('assessment.pass')}}
                                                                    </strong>
                                                                @else
                                                                    <strong class="wrong-text">
                                                                        {{ trans('assessment.fail') }}
                                                                    </strong>
                                                                @endif
                                                            @endif
                                                            <br>
                                                        @endif
                                                    </span>
                                                </div>
                                                <div class="col-md-6 col-sm-6 col-xs-6 border-bottom-gray sm-margin" title="{{trans('assessment.title_time_spent')}}"><!-- Time spent starts here-->
                                                    <div class="xs-margin">
                                                        <span class="pull-left">
                                                            <img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-03.png') }}" alt="Total time spent" class="img-inline" width="40px">
                                                        </span>
                                                        <span class="right-div-count">{{ trans('assessment.time_spent') }}</span>
                                                    </div>
                                                    <div class="xs-margin center">
                                                        <div class="font-16 orange">
                                                            {{ Helpers::secondsToString($last_attempt->started_on->diffInSeconds($last_attempt->completed_on)) }}
                                                        </div>
                                                        <div class="gray font-12">
                                                            {{ trans('assessment.hh_mm_ss') }}
                                                        </div>
                                                    </div>
                                                </div><!-- Time spent ends here-->
                                                <div class="col-md-6 col-sm-6 col-xs-6 border-bottom-gray sm-margin" title="{{trans('assessment.title_speed')}}"> <!-- Speed starts here-->
                                                    <div class="xs-margin">
                                                        <span class="pull-left">
                                                            <img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-04.png') }}" alt="Speed" class="img-inline" width="40px">
                                                        </span>
                                                        <span class="right-div-count">{{ trans('assessment.speed') }}</span>
                                                    </div>
                                                    <div class="xs-margin center">
                                                        <div class="font-16 red-light">
                                                            {{ Helpers::secondsToString(round($last_attempt->started_on->diffInSeconds($last_attempt->completed_on) / (($last_attempt->correct_answer_count + $last_attempt->in_correct_answer_count) > 0 ? $last_attempt->correct_answer_count + $last_attempt->in_correct_answer_count : 1 )), 'i:s') }}
                                                        </div>
                                                        <div class="gray font-12">
                                                            {{ trans('assessment.mm_ss') }}
                                                        </div>
                                                    </div>
                                                </div><!-- Speed ends here-->
                                                @if(!isset($asset['type'])) <!-- general quiz starts here -->
                                                    <!-- score bar starts here -->
                                                    <div class="row col-md-12 sm-margin" title="{{trans('assessment.title_score')}}">
                                                        <div class="left-div1 center">
                                                            <img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-05.png') }}" alt="Marks" class="img-inline" width="40px">
                                                            <br>
                                                            <span>{{ trans('assessment.score') }}(%)</span>
                                                        </div>
                                                        <div class="right-div1">
                                                            <div class="progress score-bar">
                                                                <?php
                                                                    $score = Helpers::getPercentage($last_attempt->obtained_mark, $last_attempt->total_mark);
                                                                ?>
                                                                <div class="progress-bar" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:{{ $score }}%">
                                                                    <span>
                                                                        {{ round($score, 2) }}%
                                                                    </span>
                                                                </div>
                                                            </div>
                                                      </div>
                                                    </div>
                                                     <!-- Score bar ends here -->
                                                    <!-- Split starts here -->
                                                    <div class="row col-md-12 sm-margin" title="Break up of correct({{$last_attempt->correct_answer_count}}), incorrect({{ $last_attempt->in_correct_answer_count }}) and skipped questions({{ $last_attempt->un_attempted_question_count }})">
                                                        <div class="left-div1 center">
                                                            <img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-06.png') }}" alt="Marks" class="img-inline" width="40px"><br>
                                                            <span>{{ trans('assessment.split') }}</span>
                                                        </div>
                                                        <div class="right-div1" >
                                                            <div class="progress custom-progress" style="margin-bottom:4px;">
                                                                <div class="progress-bar progress-bar-success" style="width: {{ Helpers::getPercentage($last_attempt->correct_answer_count, count($asset['questions'])) }}%">
                                                                    <span >{{$last_attempt->correct_answer_count}}</span>
                                                                </div>
                                                                <div class="progress-bar progress-bar-danger" style="width: {{ Helpers::getPercentage($last_attempt->in_correct_answer_count, count($asset['questions'])) }}%">
                                                                    <span >{{ $last_attempt->in_correct_answer_count }}</span>
                                                                </div>
                                                                <div class="progress-bar progress-bar-warning" style="width: {{ Helpers::getPercentage($last_attempt->un_attempted_question_count, count($last_attempt->questions)) }}%">
                                                                    <span >{{ $last_attempt->un_attempted_question_count }}</span>
                                                                </div>
                                                        </div>
                                                        <div class="font-11">
                                                            <span class="green-circle circle-btn"></span>
                                                            {{ trans('assessment.correct')}}
                                                            <span class="red-circle circle-btn"></span>
                                                            {{ trans('assessment.incorrect')}}
                                                            <span class="black-circle circle-btn"></span>
                                                            {{ trans('assessment.skipped')}}
                                                        </div>
                                                      </div>
                                                    </div>
                                                    <!--Split ends here-->
                                                    <!-- Accuracy starts here -->
                                                    <div class="row col-md-12 sm-margin" title="{{ trans('assessment.title_accuracy') }}">
                                                        <div class="left-div1 center">
                                                            <img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-07.png') }}" alt="Marks" class="img-inline" width="44px">
                                                            <br>
                                                            <span>{{ trans('assessment.accuracy') }}</span>
                                                        </div>
                                                        <div class="right-div1">
                                                            <div class="progress accuracy-bar">
                                                                <?php
                                                                    $accuracy = Helpers::getPercentage($last_attempt->correct_answer_count, $last_attempt->correct_answer_count+$last_attempt->in_correct_answer_count);
                                                                ?>
                                                                <div class="progress-bar" role="progressbar" aria-valuenow="70"aria-valuemin="0" aria-valuemax="100" style="width:{{ $accuracy  }}%;">
                                                                    <span>
                                                                        {{ $accuracy }}%
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Accuracy ends here -->
                                                <!-- general quiz ends here -->
                                                @else <!-- question generator starts here-->
                                                    <!-- Split starts here -->
                                                    <div class="row col-md-12 sm-margin" title="Break up of correct({{$last_attempt->correct_answer_count}}) and incorrect({{ $last_attempt->in_correct_answer_count }})">
                                                        <div class="left-div1 center">
                                                            <img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-06.png') }}" alt="Marks" class="img-inline" width="40px"><br>
                                                            <span>{{ trans('assessment.split') }}</span>
                                                        </div>
                                                        <div class="right-div1" >
                                                            <div class="progress custom-progress" style="margin-bottom:4px;">
                                                                <div class="progress-bar progress-bar-success" style="width: {{ Helpers::getPercentage($last_attempt->correct_answer_count, $last_attempt->correct_answer_count+$last_attempt->in_correct_answer_count) }}%">
                                                                    <span >{{$last_attempt->correct_answer_count}}</span>
                                                                </div>
                                                                <div class="progress-bar progress-bar-danger" style="width: {{ Helpers::getPercentage($last_attempt->in_correct_answer_count, $last_attempt->correct_answer_count+$last_attempt->in_correct_answer_count) }}%">
                                                                    <span >{{ $last_attempt->in_correct_answer_count }}</span>
                                                                </div>
                                                        </div>
                                                        <div class="font-11">
                                                            <span class="green-circle circle-btn"></span>
                                                            {{ trans('assessment.correct')}}
                                                            <span class="red-circle circle-btn"></span>
                                                            {{ trans('assessment.incorrect')}}
                                                        </div>
                                                      </div>
                                                    </div>
                                                    <!--Split ends here-->
                                                    <!-- Accuracy starts here -->
                                                    <div class="row col-md-12 sm-margin" title="{{ trans('assessment.title_accuracy') }}">
                                                        <div class="left-div1 center">
                                                            <img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-07.png') }}" alt="Marks" class="img-inline" width="44px">
                                                            <br>
                                                            <span>{{ trans('assessment.accuracy') }}</span>
                                                        </div>
                                                        <div class="right-div1">
                                                            <div class="progress accuracy-bar">
                                                                <?php
                                                                    $accuracy = Helpers::getPercentage($last_attempt->correct_answer_count, $last_attempt->correct_answer_count+$last_attempt->in_correct_answer_count);
                                                                ?>
                                                                <div class="progress-bar" role="progressbar" aria-valuenow="70"aria-valuemin="0" aria-valuemax="100" style="width:{{ $accuracy  }}%;">
                                                                    <span>
                                                                        {{ $accuracy }}%
                                                                    </span>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <!-- Accuracy ends here -->
                                                    <div class="row col-md-12 sm-margin">
                                                        <a class="btn btn-default btn-circle xs-margin popup-link" href="{{ url('assessment/report/'.$last_attempt->attempt_id).'?requestUrl='.$requestUrl }}" title="{{ trans('assessment.title_review_answers')}}">
                                                            <img src="{{ asset('portal/theme/default/img/icons/review-answers-icon.png') }}" alt="{{ trans('assessment.title_review_answers')}}" title="{{ trans('assessment.title_review_answers')}}">&nbsp;{{ trans('assessment.review_answers')}}
                                                        </a>
                                                        <a class="btn btn-default btn-circle xs-margin popup-link" href="{{ url('assessment/question-detail/'.$last_attempt->attempt_id).'?requestUrl='.$requestUrl }}" title="{{ trans('assessment.title_detailed_analytics')}}">
                                                            <img src="{{ asset('portal/theme/default/img/icons/analytics-icon.png') }}" alt="{{ trans('assessment.title_detailed_analytics')}}">&nbsp;{{ trans('assessment.detailed_analytics')}}
                                                        </a>
                                                    </div>
                                                @endif <!-- question generator ends here -->
                                            @endif
                                        @endif
                                    </div>
                                    <!-- summary of attempts starts here -->
                                    @if( !isset($asset['type']) || $asset['type'] != QuizType::QUESTION_GENERATOR)
                                        @if(($closedAttemepts->count() >= 1) && (array_get($asset, 'is_score_display', true) == true))
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <div id="accordion">
                                                        <div class="panel-heading sequential-panel-header center collapsed" data-toggle="collapse" data-parent="#summary" href="#summary">
                                                            <strong>
                                                                <a data-toggle="collapse" data-parent="#summary" href="#summary">{{ trans('assessment.summary_o_a') }}</a>
                                                                <i class="indicator glyphicon  pull-right glyphicon-chevron-up"></i>
                                                            </strong>
                                                        </div>
                                                        <div class="panel-collapse collapse in" id="summary">
                                                            <div class="table-responsive">
                                                                <table class="table">
                                                                    <thead>
                                                                        <th>#</th>
                                                                        <th>{{ trans('assessment.score') }}</th>
                                                                        <th>{{ trans('assessment.time_taken') }}</th>
                                                                        <th>{{ trans('assessment.status') }}</th>
                                                                        <th>{{ trans('assessment.started_on') }}</th>
                                                                        <th>{{ trans('assessment.completed_on') }}</th>
                                                                        @if(array_get($asset, 'review_options.the_attempt', false))
                                                                            <th>{{ trans('assessment.review')}}</th>
                                                                        @endif
                                                                    </thead>
                                                                    <tbody>
                                                                        <?php $count = $closedAttemepts->count(); ?>
                                                                        @foreach($closedAttemepts as $attempt)
                                                                            <tr>
                                                                                <td>{{ $count-- }}</td>
                                                                                <td>
                                                                                    {{ Helpers::getPercentage($attempt->obtained_mark, $attempt->total_mark) }}%
                                                                                </td>
                                                                                <td>
                                                                                    {{ Helpers::secondsToString($attempt->started_on->diffInSeconds($attempt->completed_on)) }}
                                                                                </td>
                                                                                <td>
                                                                                    <span>{{$attempt->status}}</span>
                                                                                </td>
                                                                                <td>
                                                                                    {{ $attempt->started_on->timezone(Auth::user()->timezone)->format('d M Y h:i A') }}
                                                                                </td>
                                                                                <td>
                                                                                    {{ $attempt->completed_on->timezone(Auth::user()->timezone)->format('d M Y h:i A') }}
                                                                                </td>
                                                                                <td>
                                                                                @if(array_get($asset, 'review_options.the_attempt', false))
                                                                                    <a class="report" data-url="{{ url('assessment/question-detail/'.$attempt->attempt_id.'/'.($count+1))}}" title="Detailed Report">{{ trans('assessment.detailed_report') }}</a>
                                                                                @endif
                                                                                </td>
                                                                            </tr>
                                                                        @endforeach
                                                                    </tbody>
                                                                </table>
                                                            </div>
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        @endif
                                    @endif
                                    <!-- summary of attempts ends here -->
                                </div>
                            @elseif(!empty($asset) && $asset['element_type'] == "event")

                                @if($asset['event_type'] == 'live')
                                    <div class="col-lg-12 col-md-12 col-sm-6 col-xs-12 nonsequential-panel">
                                        <div>
                                            <div class="pull-left">
                                                <img src="{{URL::asset($theme.'/img/live-event.svg')}}" class="img-responsive" alt="event image" width="100">
                                            </div>
                                            <div>
                                                <div class="font-14">
                                                    <strong>{{ trans('event.live') }} event</strong>
                                                </div>
                                                <ul style="list-style-type: none;margin-top: 10px !important;padding-left: 10px !important">
                                                    <li style="display: inline-block;">
                                                        <strong>{{ trans('event.start_date') }} </strong>
                                                        <span>{{Timezone::convertFromUTC($asset['start_time'], Auth::user()->timezone, 'D, d M Y')}}</span>
                                                    </li>&nbsp;&nbsp;&nbsp;&nbsp;
                                                    <li style="display: inline-block;">
                                                        <strong>{{ trans('event.start_time') }}</strong>
                                                        <span>{{Timezone::convertFromUTC($asset['start_time'], Auth::user()->timezone, 'h:i A')}}</span>
                                                    </li>&nbsp;&nbsp;&nbsp;&nbsp;
                                                    @if(!isset($asset['duration'])) $asset['duration'] = 0; @endif
                                                    <li style="display: inline-block;">
                                                        <strong>{{ trans('event.duration') }}</strong>
                                                        <span>{{ gmdate('H:i', $asset['duration'] * 60) }}</span>
                                                    </li>
                                                    <br>
                                                    <li style="padding-top:10px;">
                                                        <strong>{{ trans('event.host_name') }} </strong>
                                                        <span>{{ $asset['event_host_name'] }}</span>
                                                    </li>
                                                </ul>
                                                @if(!empty($asset['speakers']))
                                                <div>
                                                    <div class="col-md-1 col-sm-1 col-lg-1 pull-left padding-0">
                                                        <strong>{{ trans('event.speakers') }} </strong>
                                                    </div>
                                                    <div class="col-md-8 col-sm-8 col-lg-8 pull-left">
                                                        <span>{{implode(', ', $asset['speakers'])}}</span>
                                                    </div>
                                                    <div class="clearfix"></div>
                                                </div>
                                                @endif
                                                @if((Timezone::getTimeStamp($asset['start_time']) - ($asset['open_time'] * 60)) < time() && Timezone::getTimeStamp($asset['end_time']) > time())
                                                <div class="text-center padding-10">
                                                <br>
                                                    @if(Auth::user()->uid == $asset['event_host_id'])
                                                        <a href="{{ url('event/live-join/'.$asset['event_id']) }}" class="btn-success btn-lg">{{ trans('event.start_now') }}</a>
                                                    @else
                                                        <a href="{{ url('event/live-join/'.$asset['event_id']) }}" class="btn-success btn-lg">{{ trans('event.join_now') }}</a>
                                                    @endif
                                                </div>
                                                @endif
                                            </div>
                                            <div class="clearfix"></div>
                                        </div>
                                        @if(!empty($asset['event_description']))
                                        <p class="sm-margin"></p>
                                        <div class="description-contt">
                                           <p><strong class="font-15 blueTxt">{{ trans('event.description') }}</strong></p>
                                            <div id="demo">
                                                <article>{!! $asset['event_description'] !!}</article>
                                            </div>
                                        </div>
                                        @endif
                                        @if(!empty($asset['recordings']))
                                        <p class="sm-margin"></p>
                                        <p class="margin-btm-3 borderBtmGry padding-btm-3">
                                            <strong class="font-15 blueTxt">{{ trans('event.recordings') }}</strong>
                                        </p>
                                        <div class="table-responsive brdrBottomNon">
                                            <div class="recordings-table-cont">
                                                <table class="table recordings-table margin-btm-0 brdrBottomNon">
                                                    <tbody>
                                                    @foreach($asset['recordings'] as $recordings)
                                                            <tr style="border-bottom: 1px solid #e7e7e7;">
                                                                <td width="150px">
                                                                {{$recordings['display_name']}}
                                                                </td>
                                                                <?php
                                                                $recordings['created'] = Timezone::convertToUTC($recordings['created'], Auth::user()->timezone, 'U')?>
                                                                <td width="300px">
                                                                    <strong>{{ trans('event.start_time') }} </strong>
                                                                    {{Timezone::convertFromUTC($recordings['created'], Auth::user()->timezone, 'h:i A')}}
                                                                </td>
                                                                <td class="verAlignMiddle">
                                                                @if(isset($recordings['streamURL']) && !empty($recordings['streamURL']))
                                                                <a class="btn btn-warning btn-sm" href="{{ $recordings['streamURL'] }}" target="_blank">{{ Lang::get('event.stream_url') }}</a>
                                                                @endif
                                                                </td>
                                                            </tr>
                                                        @endforeach
                                                    </tbody>
                                                </table>
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                    @elseif($asset['event_type'] == 'general')
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 nonsequential-panel sm-margin">
                                            <span class="white label label-danger font-10 general-label border-white"><strong>{{trans('event.General') }}</strong></span>
                                            <div class="center">
                                                <img src="{{URL::asset($theme.'/img/packetpage_event.png')}}" class="img-responsive" alt="Event">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <div class="font-16 xs-margin">
                                              <span class="start">{{trans('event.starts') }}</span> <strong>{{ Timezone::convertFromUTC($asset['start_time'], Auth::user()->timezone, 'D, d M Y') }}</strong> <br>
                                                <span class="end">{{trans('event.ends') }}</span> <strong>{{ Timezone::convertFromUTC($asset['end_time'], Auth::user()->timezone, 'D, d M Y') }}</strong>
                                            </div>
                                            @if(!empty($asset['location']))
                                            <p class="font-16">
                                                <i class="fa fa-map-marker font-18"></i>&nbsp;&nbsp;<strong>{{ $asset['location'] }}</strong>
                                            </p>
                                            @endif
                                        </div>
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                            @if(!empty($asset['event_description']))
                                                <p><strong>{{ trans('event.description') }}</strong></p>
                                                <div id="demo">
                                                    <article>{!! $asset['event_description'] !!}</article>
                                                </div>
                                            @endif
                                        </div>
                                    @endif
                                @elseif(!empty($asset) && $asset['element_type'] == "flashcard")
                                    @include('admin.theme.flashcards.preview', ['flashcards' => $asset['cards'], 'height' => '400px', 'disable' => true])
                                @elseif(!empty($asset) && (array_get($asset, 'element_type') == "survey"))
                                    <?php
                                        $survey_id = array_get($asset, 'id');
                                        $survey_type = array_get($asset, 'element_type').'_'.$survey_id;
                                        $survey_attempt = array_get($survey_attempt_details, $survey_id);
                                        $survey = array_get($survey_details, $survey_id);
                                        $survey_questions_count = array_get($survey_questions_count, $survey_id);
                                        $survey_start_date = $survey->start_time;
                                        $survey_expiry_date = $survey->end_time;
                                        $current_time = Carbon::now(Auth::user()->timezone);
                                    ?>
                                    <div class="post quiz-details">
                                        <div class="col-lg-7 col-md-4 col-sm-4 col-xs-7 sm-margin">
                                            <div class="col-lg-7 col-md-4 col-sm-4 col-xs-6 sm-margin">
                                                <div class="center">
                                                    @if($current_time <= $survey_expiry_date)
                                                        <img src="{{URL::asset($theme.'/img/survey-default.png')}}" class="img-responsive" alt="Assessment image" style="width:100%;height:140px;margin-left: -20px;">
                                                    @else
                                                        <img src="{{URL::asset($theme.'/img/survey-default.png')}}" class="img-responsive opacity-40" alt="Assessment image" style="width:100%;height:140px;margin-left: -20px;">
                                                    @endif
                                                </div>
                                            </div>
                                            <div class="col-lg-5 col-md-3 col-sm-3 col-xs-6">
                                                <p class="font-16 sm-margin">
                                                    {{ $survey_questions_count }}
                                                    @if($survey_questions_count == 1)
                                                        <strong> {{trans('survey.question')}}</strong>
                                                    @else
                                                        <strong> {{trans('survey.questions')}}</strong>
                                                    @endif
                                                </p>
                                                <p class="font-16 sm-margin">
                                                    @if($survey_questions_count > 0)
                                                        @if(!empty($survey_attempt))
                                                            @if($survey_attempt->status == "CLOSED")
                                                                <a href="{{ URL::to('survey/view-reports/'.array_get($packet, 'packet_slug').'/'.array_get($asset, 'id')) }}" class="btn btn-success btn-lg survey-begin">{{ trans('survey.survey_result') }}</a>
                                                            @elseif(($survey_attempt->status == "OPEN") && ($current_time >= $survey_start_date) && ($current_time < $survey_expiry_date))
                                                                <a href="{{ URL::to('survey/start-survey/'.array_get($packet, 'packet_slug').'/'.array_get($asset, 'id')) }}" class="btn btn-success btn-lg survey-begin">{{ trans('survey.start') }}</a>
                                                            @else
                                                                <a href="#" class="btn btn-default1 btn-lg survey-begin">{{ trans('survey.start') }}</a>
                                                            @endif
                                                        @else
                                                            @if(($current_time < $survey_expiry_date) && ($current_time >= $survey_start_date))
                                                                <a href="{{ URL::to('survey/start-survey/'.array_get($packet, 'packet_slug').'/'.array_get($asset, 'id')) }}" class="btn btn-success btn-lg survey-begin">{{ trans('survey.start') }}</a>
                                                            @else
                                                                <a href="#" class="btn btn-default1 btn-lg survey-begin">{{ trans('survey.start') }}</a>
                                                            @endif
                                                        @endif
                                                    @endif
                                                </p>
                                            </div>
                                            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 xs-margin">
                                                <div class="font-16 xs-margin">
                                                    <span class="start">STARTS:</span>&nbsp;
                                                    <span>{{ $survey_start_date->timezone(Auth::user()->timezone)->format('D, d M Y') }}</span>&nbsp;
                                                    <span>{{ $survey_start_date->timezone(Auth::user()->timezone)->format('h:i A') }}</span>
                                                    <br><span class="end">ENDS:</span>&nbsp;
                                                        <span>{{ $survey_expiry_date->timezone(Auth::user()->timezone)->format('D, d M Y') }}</span>&nbsp;
                                                        <span>{{ $survey_expiry_date->timezone(Auth::user()->timezone)->format('h:i A') }}</span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                @elseif(!empty($asset) && (array_get($asset, 'element_type') == "assignment"))
                                    <?php
                                        $assignment_id = (int)array_get($asset, 'id');
                                        $assignment_type = array_get($asset, 'element_type').'_'.$assignment_id;
                                        $assignment_attempt = array_get($assignment_attempt_details, $assignment_id);
                                        $assignment = array_get($assignment_details, $assignment_id);
                                        $assignment_start_date = $assignment->start_time;
                                        $assignment_expiry_date = $assignment->end_time;
                                        $assignment_cut_off_date = $assignment->cutoff_time;
                                        $current_time = Carbon::now(Auth::user()->timezone);
                                    ?>
                                    <div class="post quiz-details">
                                        <div class="row">
                                            <div class="col-md-3 col-lg-3">
                                                @if((($current_time >= $assignment_start_date) && ($current_time <= $assignment_cut_off_date)) || ($current_time < $assignment_start_date))
                                                    <img src="{{URL::asset($theme.'/img/survey-default.png')}}" class="img-responsive" alt="Assessment image" style="width:100%;height:140px;margin-left: -20px;">
                                                @else
                                                    <img src="{{URL::asset($theme.'/img/survey-default.png')}}" class="img-responsive opacity-40" alt="Assessment image" style="width:100%;height:140px;margin-left: -20px;">
                                                @endif
                                            </div>
                                            <div class="col-md-9 col-lg-9 font-16">
                                                <ul style="list-style-type: none;padding-left: 0px">
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-9 col-lg-9">
                                                                <span class="start">STARTS :</span>
                                                                &nbsp;&nbsp;&nbsp;&nbsp;
                                                                <span>{{ $assignment_start_date->timezone(Auth::user()->timezone)->format('D, d M Y') }}</span>&nbsp;
                                                                <span>{{ $assignment_start_date->timezone(Auth::user()->timezone)->format('h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-9 col-lg-9">
                                                                <span class="end" style="color: #E08283">ENDS :</span>
                                                                &nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                                                                <span>{{ $assignment_expiry_date->timezone(Auth::user()->timezone)->format('D, d M Y') }}</span>&nbsp;
                                                                <span>{{ $assignment_expiry_date->timezone(Auth::user()->timezone)->format('h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    </li>
                                                    <li>
                                                        <div class="row">
                                                            <div class="col-md-9 col-lg-9">
                                                                <span class="end" style="color: #D24D57">CUT OFF :</span>
                                                                &nbsp;&nbsp;
                                                                <span>{{ $assignment_cut_off_date->timezone(Auth::user()->timezone)->format('D, d M Y') }}</span>&nbsp;
                                                                <span>{{ $assignment_cut_off_date->timezone(Auth::user()->timezone)->format('h:i A') }}</span>
                                                            </div>
                                                        </div>
                                                    </li><br>
                                                    <li>
                                                        @if (!empty($assignment_attempt))
                                                        <?php
                                                            $submission_status = $assignment_attempt->submission_status;
                                                        ?>
                                                        @if (($submission_status == SubmissionType::YET_TO_REVIEW) || ($submission_status == SubmissionType::REVIEWED) || ($submission_status == SubmissionType::LATE_SUBMISSION))
                                                            <a href="{{ URL::Route('assignment-result',['packet_slug' => array_get($packet, 'packet_slug'), 'assignment_id' => array_get($asset, 'id')]) }}" class="btn btn-success btn-lg survey-begin">{{ trans('assignment.result') }}</a>
                                                        @elseif(($current_time >= $assignment_start_date) && ($current_time <= $assignment_cut_off_date))
                                                            @if($submission_status == SubmissionType::SAVE_AS_DRAFT)
                                                                <a href="{{ URL::Route('submit-assignment',['packet_slug' => array_get($packet, 'packet_slug'), 'assignment_id' => array_get($asset, 'id')]) }}" class="btn btn-success btn-lg survey-begin">{{ trans('assignment.resume') }}</a>
                                                            @else
                                                                <a href="{{ URL::Route('submit-assignment',['packet_slug' => array_get($packet, 'packet_slug'), 'assignment_id' => array_get($asset, 'id')]) }}" class="btn btn-success btn-lg survey-begin">{{ trans('assignment.start') }}</a>
                                                            @endif
                                                        @else
                                                            @if (($submission_status == SubmissionType::SAVE_AS_DRAFT))
                                                                <a href="#" class="btn btn-default1 btn-lg survey-begin">{{ trans('assignment.resume') }}</a>
                                                            @else
                                                                <a href="#" class="btn btn-default1 btn-lg survey-begin">{{ trans('assignment.start') }}</a>
                                                            @endif
                                                        @endif
                                                    @else
                                                        @if(($current_time >= $assignment_start_date) && ($current_time <= $assignment_cut_off_date))
                                                            <a href="{{ URL::Route('submit-assignment',['packet_slug' => array_get($packet, 'packet_slug'), 'assignment_id' => array_get($asset, 'id')]) }}" class="btn btn-success btn-lg survey-begin">{{ trans('assignment.start') }}</a>
                                                        @else
                                                            <a href="#" class="btn btn-default1 btn-lg survey-begin">{{ trans('assignment.start') }}</a>
                                                        @endif
                                                    @endif
                                                    </li>
                                                </ul>
                                            </div>
                                        </div>
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                @if($packet['qanda'] == 'yes')
                    <div class="custom-box">
                        <div class="portlet box">
                            <div class="portlet-body">
                                <div class="tabbable-line">
                                    <ul class="nav nav-pills ">
                                    @if( config('app.display_portal_q&a'))
                                        <li class="active">
                                            <a href="#tab_15_1" data-toggle="tab">
                                                 {{ trans('program.all_questions')}}
                                            </a>
                                        </li>
                                    @endif
                                        <li class="<?php if(!config('app.display_portal_q&a')){ echo " active";}?>">
                                            <a href="#q-a" data-toggle="tab">
                                            FAQ</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="clearfix"></div>
                                        <span class="success_text"></span>
                                        <span class="error_text"></span>

                                        <div class="tab-pane <?php if(config('app.display_portal_q&a')){ echo " active in";}?> myquestion_tab" id="tab_15_1">
                                            <div class="form-group">
                                                <ul class="media-list">
                                                    <li class="media">
                                                        <a class="pull-left" href="javascript:;">
                                                        <?php
                                                            $pic = (isset(Auth::user()->profile_pic) && !empty(Auth::user()->profile_pic)) ? URL::asset(config('app.user_profile_pic') . Auth::user()->profile_pic ) : URL::asset($theme.'/img/green.png');
                                                        ?>
                                                            <img class="todo-userpic margin-top-10" src="{{ $pic }}" width="27px" height="27px" alt="User pic">
                                                        </a>
                                                        <div class="media-body">
                                                            <form action="{{URL::to('program/question/'.$packet['packet_id'].'/'.$packet['packet_slug'].'/'.$packet['feed_slug'])}}">
                                                                <div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
                                                                    <textarea class="form-control todo-taskbody-taskdesc" name="question" rows="2" placeholder="Type new question..."></textarea>
                                                                    <span class="help-inline errorspan red"></span>
                                                                </div>
                                                                <div class="col-md-2 col-sm-3 col-xs-12">
                                                                    <button id="ques_submit" class="btn btn-primary btn-sm margin-top-10" data-action="{{URL::to('program/question/'.$packet['packet_id'].'/'.$packet['packet_slug'].'/'.$packet['feed_slug'])}}"><i class="fa fa-send"></i> Send</button>
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </li>
                                                    <div>
                                                        <input type="hidden" id="page_no" value="0">
                                                    </div>
                                                    <div class="myquestion_div">
                                                        @include('portal.theme.default.programs.myquestions_ajax_load', ['user_ques' => $user_ques])
                                                    </div>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-pane <?php if(!config('app.display_portal_q&a')){ echo " active in";}?> faq_tab" id="q-a">
                                            @if(count($public_ques) > 0)
                                                <div class="form-group">
                                                    <ul class="media-list">
                                                        <div class="faq_div">
                                                            @include('portal.theme.default.programs.faq_ajax_load', ['public_ques' => $public_ques])
                                                        </div>
                                                    </ul>
                                                </div>
                                            @else
                                                <h4 align="center"> There are no faq's to show</h4>
                                            @endif
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!--content end here-->
            <!--packet items starts here-->
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 pkt-items pkt-items-desktop">
                <div class="panel panel-default">
                    <div class="center border-btm">
                        <a type="button" href="{{URL::to('program/packets/'.$packet['feed_slug'])}}" class="btn btn-primary xs-margin btn-sm"><i class="fa fa-street-view"></i> View All {{trans('program.packets')}}</a>
                    </div>
                    <div class="panel-heading">
                        {{count($elements)}}
                        @if(count($elements) > 1)
                            Items
                        @else
                            Item
                        @endif

                        @if($packet['sequential_access'] == 'yes')
                            <!-- <em class="font-13">(Sequential)</em> -->
                            <?php $sequential="timeline"; ?>
                        @else
                            <?php $sequential=""; ?>
                        @endif
                    </div><!--panel heading-->
                    <div class="panel-body">
                        <div class="{{$sequential}}">
                            <!-- TIMELINE ITEM -->
                            <?php
                                if(!empty($elements))
                                {
                                    $le=0;
                                    $last_element_id=$elements[0]['id'];
                                    $last_element_type=$elements[0]['type'];
                                }
                            ?>
                            @foreach($elements as $element)
                            <?php $le=1; ?>
                                <?php
                                    $media_id = array_get($element, 'type').'_'.array_get($element, 'id');
                                    $elements_data = array_get($posts_data, $media_id, []);
                                    $activity = array_get($viewdEleTypeIds, $element['type'], []);
                                    $seen_class='pkt-item-gray';
                                    if(is_array($asset) && !empty($asset) )
                                    {
                                        if(($asset['element_type'] == $element['type']) && ($asset['id'] == $element['id']))
                                        {
                                            $seen_class='pkt-item-progress';
                                        }
                                         elseif(in_array($element['id'], $activity))
                                        {
                                            $seen_class='pkt-item-blue';
                                        }
                                        else
                                        {
                                            $seen_class='pkt-item-gray';
                                        }
                                    }

                                ?>
                                <div class="timeline-item">
                                    <div class="timeline-badge">
                                        <div class="{{$seen_class}}"><i class="fa {{array_get($elements_data, 'class')}}"></i></div>
                                    </div>
                                    <div class="timeline-body transparent-bg">
                                        <div class="timeline-body-head">
                                            <div class="timeline-body-head-caption">
                                                <div href="javascript:;" class="timeline-body-title font-blue-madison">
                                                    <?php $last_element_activity = array_get($viewdEleTypeIds, $last_element_type, []);?>
                                                     @if($packet['sequential_access'] == 'yes' && !in_array( $last_element_id,$last_element_activity))
                                                        <a title="{{trans('program.sequential_access')}}">{{array_get($elements_data, 'name')}}</a>
                                                    @else
                                                        @if(!isset($isQuizzesPass[$last_element_id]) || !(isset($isQuizzesPass[$last_element_id]) && !$isQuizzesPass[$last_element_id]))
                                                            <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/element/'.$element['id'].'/'.$element['type'])}}">{{array_get($elements_data, 'name')}}</a>
                                                        @else
                                                            <a title="{{trans('program.sequential_elements_with_order')}}">{{array_get($elements_data, 'name')}}</a>
                                                        @endif
                                                    @endif
                                                </div>
                                                <span class="timeline-body-time font-grey-cascade"><i>{{array_get($elements_data, 'type')}}</i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php
                                    $last_element_id=$element['id']; $last_element_type=$element['type'];
                                ?>
                            @endforeach
                            <!-- END TIMELINE ITEM -->
                        </div>
                    </div><!--panel-body-->
                </div>
            </div>
            <!--packet items end here-->
        </div>
    @else
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div id="accordion1" class="panel-group accordion">
                    <div class="panel transparent-bg">
                        <div class="panel-heading">
                            <h4 class="panel-title m-btm-12">
                                <div class="row">
                                    <div class="col-md-10">
                                        <span class="caption gray">{{$packet['packet_title']}}&nbsp;&nbsp;</span>
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
                                        <span>
                                            <i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}}"></i>
                                        </span>
                                        <span class="font-grey-cascade">&nbsp;&nbsp;|&nbsp;<em class="font-13">{{ Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))}}</em>
                                            &nbsp;&nbsp;|&nbsp;<label class="pkt-inactive-label">Inactive</label>
                                        </span>

                                    </div>
                                </div>
                            </h4>
                        </div>
                        <div>
                            <div class="panel-body border-0" >{!! $packet['packet_description'] !!}</div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    @endif

<div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">

            <div class="modal-body" style="margin:170px; ">
                <div id="content"></div>

            </div>
            <div class="modal-footer border-0">
            <a class="btn btn-success" data-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
@if((array_get($asset, 'element_type') ==  'assessment'))
    @if(isset($asset['quiz_description']) && !empty($asset['quiz_description']))
        <div id="info-modal" class="modal fade" style="" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title center"><strong>{{trans('assessment/detail.instructions_header_title')}}</strong></h4>
                    </div>
                    <div class="modal-body">
                        <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
                            <div class="row">
                                <div class="col-md-12">
                                    <p>
                                        {!! $asset['quiz_description'] !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer center">
                        <button type="button" class="btn-success" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong>OK</strong></button>
                    </div>
                </div>
            </div>
        </div>
    @endif
@endif
<!-- delete window -->
<div class="modal fade deletemodal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="font-weight-500"><i class="fa fa-file-text-o"></i>Delete Question</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body padding-20">
                Are you sure you want to delete this question?
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-success btn-sm"><i class="fa fa-check"></i> Yes</a>
              <a class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-remove"></i> Close</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->
<script type="text/javascript">

    $(document).on('click','#ques_submit',function(e){
        e.preventDefault();
        var page_no = $('#page_no').val();
        var $this = $(this);
        var action=$this.data('action');
        var ques=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                ques:ques,
                page_no:page_no
            }
        }).done(function(response) {
            if(response.status == true) {
                $('.myquestion_div').html(response.data);
                $this.parent().prev().find('textarea').val('');
                $this.parent().prev().find('span.errorspan').text('');
                $('.success_text').html('<div class="alert alert-success" id="alert-success">'+response.message+'</div>');
                $('#alert-success').delay(5000).fadeOut();
            } else {
                $('.success_text').html('');
                $this.parent().prev().find('textarea').val(ques);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }
        }).fail(function(response) {
            alert( "Error while deleting the question. Please try again" );
        });
    });

    $(document).on('click','.ans_submit',function(e){
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('value');
        var action=$this.data('action');
        var ans=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                ans:ans
            }
        }).done(function(response) {
            if(response.status == true) {
                $('#answers_div'+id).html(response.data);
                $this.parent().prev().find('textarea').val('');
                $this.parent().prev().find('span.errorspan').text('');
            } else {
                $this.parent().prev().find('textarea').val(ans);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }
        }).fail(function(response) {
            alert( "Error while deleting the question. Please try again" );
        });
    });

    $(document).on('click','.faq_delete',function(e){
        e.preventDefault();
        var page_no = $('#page_no').val();
        var $this = $(this);
        var $deletemodal = $('.deletemodal');
        var action = $this.data('action');
        $deletemodal.modal('show');
        $deletemodal.find('.modal-footer .btn-danger').unbind('click').click(function(){
            $deletemodal.modal('hide');
            $.ajax({
                type: 'GET',
                url: action+"?page_no="+page_no
            })
            .done(function(response) {
                if(response.status == true) {
                    $('.myquestion_div').html(response.data);
                } else {
                    alert("Error while deleting the question. Please try again");
                }
            })
            .fail(function(response) {
                alert( "Error while deleting the question. Please try again" );
            });
        });
    });

    $(document).on('click','.faq_edit',function(e){
      e.preventDefault();
      var id = $(this).data('value');
      $("#edit_faq"+id).removeClass("faq_inactive").addClass("faq_active");
      $("#faq_sec"+id).removeClass("faq_active").addClass("faq_inactive");
    });

    $(document).on('click','.edit_cancel',function(e){
      e.preventDefault();
      var $this = $(this);
      var id = $this.data('value');
      var ques = $('#question_value'+id).val();
      $this.parent().prev().find('textarea').val(ques);
      $("#faq_sec"+id).removeClass("faq_inactive").addClass("faq_active");
      $("#edit_faq"+id).removeClass("faq_active").addClass("faq_inactive");
      $this.parent().prev().find('span.errorspan').text('');
    });

    $(document).on('click','.edit_submit',function(e){
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('value');
        var action=$this.data('action');
        var edit=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                edit:edit
            }
        }).done(function(response) {
            if(response.status == true) {
                $('#update_ques'+id).html(response.data);
                $("#question_value"+id).val(response.data);
                $("#faq_sec"+id).removeClass("faq_inactive").addClass("faq_active");
                $("#edit_faq"+id).removeClass("faq_active").addClass("faq_inactive");
                $this.parent().prev().find('textarea').val(response.data);
                $this.parent().prev().find('span.errorspan').text('');
            } else {
                $("#edit_faq"+id).removeClass("faq_inactive").addClass("faq_active");
                $("#faq_sec"+id).removeClass("faq_active").addClass("faq_inactive");
                $this.parent().prev().find('textarea').val(edit);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }
        }).fail(function(response) {
            alert( "Error while deleting the question. Please try again" );
        });
    });


    $('#favourite').on('click', '.fav-packet', function() {
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

    $('#element-like').on('click', '.star-element', function() {
        var action = $(this).data('action');
        var element_id = $(this).attr('id');
        var element_type = $(this).data('type');
        var packet_id = $(this).data('packet');
        if(action == 'star') {
            $("#"+element_id).removeClass("gray").addClass("yellow");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/element-liked/star') }}/"+element_id+"/"+element_type+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $("#"+response.element_id).data('action', 'unstar');
                } else {
                    $("#"+response.element_id).removeClass("yellow").addClass("gray");
                }
            })
            .fail(function(response) {
                $("#"+element_id).removeClass("yellow").addClass("gray");
                alert( "Error while updating the element. Please try again" );
            });
        }
        if(action == 'unstar') {
            $("#"+element_id).removeClass("yellow").addClass("gray");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/element-liked/unstar') }}/"+element_id+"/"+element_type+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $('#'+response.element_id).data('action', 'star');
                } else {
                    $('#'+response.element_id).removeClass('gray').addClass('yellow');
                }
            })
            .fail(function(response) {
                $('#'+response.element_id).removeClass("gray").addClass("yellow");
                alert( "Error while updating the item. Please try again" );
            });
        }
    });

</script>
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<script type="text/javascript">
    $(document).ready(function () {

    var settings = {
        objSlideTrigger: '#pkt-trigger', // link button id
        objSlidePanel: '.panel-mob' // slide div class or id
    }

    $(settings.objSlideTrigger).bind('click' , function() {
        //If the panel isn't out
        if(!$(settings.objSlidePanel).hasClass('out')) {
            slidePanelOut();
        } else if($(settings.objSlidePanel).hasClass('out')) {
            slidePanelIn();
        }
    });

    function slidePanelOut() {
        //Animate it to left
        $(settings.objSlidePanel).animate({
            'left' : '-2%'
        });
        //Add the out class
        $(settings.objSlidePanel).addClass('out');
    }
    function slidePanelIn() {
        //Otherwise, animate it back in
        $(settings.objSlidePanel).animate({
            'left' : '-123%'
        });
        //Remove the out class
        $(settings.objSlidePanel).removeClass('out');
    }
});
</script>

<script type="text/javascript">
    $(document).ready(function () {
        var m_pageno=1;
        var f_pageno=1;
        var myquestion_count='<?php echo count($user_ques); ?>';
        var faq_count='<?php echo count($public_ques); ?>';
        var packet_id='<?php echo $packet['packet_id']; ?>';
        var stop = flag = true;
        $(window).scroll(function() {
            var period_val = $(".tab-pane.active").attr('id');
            if(period_val == 'tab_15_1')
            {
                if(myquestion_count > 8 && stop) {
                    if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
                        if(flag) {
                            flag = false;
                            $.ajax({
                                type: 'GET',
                                url: "{{ url('program/next-questions?pageno=') }}"+m_pageno+"&packet_id="+packet_id
                            }).done(function(e) {
                                if(e.status == true) {
                                    $('.myquestion_div').append(e.data);
                                    myquestion_count=e.count;
                                    $("#page_no").val(m_pageno);
                                    stop=true;
                                    flag = true;
                                    if(myquestion_count < 9)
                                    {
                                        $('.myquestion_div').append("<div class='col-md-12 center l-gray'><p><strong>{{trans('pagination.no_more_records')}}</strong></p></div>");
                                    }
                                }
                                else {
                                    $('.myquestion_div').append(e.data);
                                    stop = false;
                                }
                                m_pageno += 1;
                            }).fail(function(e) {
                                alert('Failed to get the data');
                            });

                        }
                    }
                }
            }
            else if(faq_count > 8 && stop) {
                if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
                    if(flag) {
                        flag = false;
                        $.ajax({
                            type: 'GET',
                            url: "{{ url('program/next-faqs?pageno=') }}"+f_pageno+"&packet_id="+packet_id
                        }).done(function(e) {
                            if(e.status == true) {
                                $('.faq_div').append(e.data);
                                faq_count=e.count;
                                stop=true;
                                flag = true;
                                if(faq_count < 9)
                                {
                                    $('.faq_div').append("<div  class='col-md-12 center l-gray'><p><strong>{{trans('pagination.no_more_records')}}</strong></p></div>");
                                }
                            }
                            else {
                                $('.faq_div').append(e.data);
                                stop = false;
                            }
                            f_pageno += 1;
                        }).fail(function(e) {
                            alert('Failed to get the data');
                        });
                    }
                }
            }
        });
    });
</script>
<script type="text/javascript">
    $('.faq_div').on('click', '.like-faq', function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var qid = $(this).attr('id');
        var packet_id = $(this).data('packet');
        if(action == 'like') {
            $("#"+qid).removeClass("gray").addClass("blue");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/question-liked/like') }}/"+qid+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $("#"+response.qid).data('action', 'unlike');
                    $("#like_count"+response.qid).html(response.like_count);
                } else {
                    $("#"+response.qid).removeClass("blue").addClass("gray");
                }
            })
            .fail(function(response) {
                $("#"+qid).removeClass("blue").addClass("gray");
                alert( "Error while updating the item. Please try again" );
            });
        }
        if(action == 'unlike') {
            $("#"+qid).removeClass("blue").addClass("gray");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/question-liked/unlike') }}/"+qid+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $('#'+response.qid).data('action', 'like');
                    $("#like_count"+response.qid).html(response.like_count);
                } else {
                    $('#'+response.qid).removeClass('gray').addClass('blue');
                }
            })
            .fail(function(response) {
                $('#'+response.qid).removeClass("gray").addClass("blue");
                alert( "Error while updating the item. Please try again" );
            });
        }
    });
    var attemptUrl = "{{ url('assessment/attempt/') }}";
    var instructUrl = "{{ url('assessment/instructions') }}";
    var requestUrl = "{{ request::path() }}";
    $('#quiz-attempt').on('submit', function(e){
        var $this = $(this);
        $.ajax({
            url: $this.attr('action'),
            method: 'POST',
            success: function(response){
                if(response.status != 'undefined') {
                    if (response.attempt) {
                        lswindow(instructUrl+'/'+response.quiz_id+'/'+response.attempt_id+'?requestUrl='+requestUrl, name, '');
                    }
                    else {
                        lswindow(attemptUrl+'/'+response.attempt_id+'?requestUrl='+requestUrl, name, '');
                    }
                }
            },
        });
        return false;
    });
    $('.report').on('click', function(e){
        lswindow($(this).data('url'), name, 'toolbar=0,location=0,menubar=0');
    });
    $('.popup-link').on('click', function(e){
        lswindow($(this).attr('href'), name, 'toolbar=0,location=0,menubar=0');
        return false;
    });
    $(".hdng-click").click(function(){
        $(".cnt-none").slideToggle("slow");
    });
    function toggleChevron(e) {
        $(e.target)
            .prev('.panel-heading')
            .find("i.indicator")
            .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
    }
    $('.begin').on('click',function(e){
        @if(isset($asset['duration']) && $asset['duration']!= 0)
            lswindow("{{ url('assessment/instructions/')}}"+'/'+$(this).data('id'));
        @else
            lswindow("{{ url('assessment/instructions/')}}"+'/'+$(this).data('id'));
        @endif
        return false;
    });
    $('#summary').on('hidden.bs.collapse', toggleChevron);
    $('#accordion').on('shown.bs.collapse', toggleChevron);
    function showModal()
    {
        $('#info-modal').modal('show');
    }
</script>
@stop
