@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/loader.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/responsive-iframe.css') }}" />
<style type="text/css">
    @media (min-width: 768px){
        .quiz-custom-box1{min-height: 74vh;}
        .quiz2-height{min-height: 64.8vh;}
    }
    .qg-backdrop
    {
        position : fixed;
        top : 0%;
        right : 0%;
        bottom : 0%;
        left : 0%;
        z-index : 1000;
        background-color : rgba(102, 102, 102, 0.32);
    }
    #overlay {
        position: fixed !important;
        width: 100% !important;
        height: 100% !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        right: 0 !important;
        display: none;
    }​
</style>
<?php
    $is_score_display = 'yes';
    if (!isset($quiz->is_score_display) || !$quiz->is_score_display) {
        $is_score_display = 'no';
    }
?>
<div id="overlay" style="display:none;">
    <div class="sk-fading-circle">
        <div class="sk-circle1 sk-circle"></div>
        <div class="sk-circle2 sk-circle"></div>
        <div class="sk-circle3 sk-circle"></div>
        <div class="sk-circle4 sk-circle"></div>
        <div class="sk-circle5 sk-circle"></div>
        <div class="sk-circle6 sk-circle"></div>
        <div class="sk-circle7 sk-circle"></div>
        <div class="sk-circle8 sk-circle"></div>
        <div class="sk-circle9 sk-circle"></div>
        <div class="sk-circle10 sk-circle"></div>
        <div class="sk-circle11 sk-circle"></div>
        <div class="sk-circle12 sk-circle"></div>
    </div>
    @if($last_section)
        @if($is_score_display == 'yes')
        <p id="section-message">{{ trans('assessment.quiz_submit_message') }}</p>
        @endif
    @else
        <p id="section-message">{{ trans('assessment.section_submit_message') }}</p>
    @endif
</div>
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
<?php
    if($quiz->practice_quiz == '')
    {
        $mock_quiz = "yes";
    }
    else
    {
        $mock_quiz = "no";
    }

    $question_sequence_no = 0;
    $duration_flag = 0;
    $i = $qno = 1;
    $page_layout  = [];
    $quiz_review_questions = Session::get('assessment.'.$attempt->attempt_id.'.question_review');
    if(isset($attempt->section_details)){
        foreach($attempt->section_details[$sec_id]['page_layout'] as $pno => $layout) {
            if($page == $pno) {
                $qno = $i;
            }
            $i += count($layout);
        }
        $page_layout = $attempt->section_details[$sec_id]['page_layout'];
    }else{
        foreach($attempt->page_layout as $pno => $layout) {
            if($page == $pno) {
                $qno = $i;
            }
            $i += count($layout);
        }
        $page_layout = $attempt->page_layout;
    }
    $active_section = $sec_id;
    $question_sequence_no = 0;                                      
    $page = $current_page = Input::get('page', 0);
?>
    @foreach ($section_details as $key => $eachSection)
        @if(isset($eachSection['questions']) && !empty($eachSection['questions']))
            @if(isset($eachSection['section_id']) && $eachSection['section_id'] === $active_section)
                <?php
                    break;
                ?> 
            @endif 
            <?php 
                $question_sequence_no += count($eachSection['questions']);
            ?>
        @endif
    @endforeach
<?php
    /*if($page > 1)
    {
        $question_sequence_no += ($page - 1) * config('app.question_per_block');
    }*/
    $begin_section_no = $question_sequence_no;
        $activeQuestionflag = 0; 
        if(!Input::has('q_id'))
        {
            $active_question_sequence_no = $question_sequence_no + 1;
            $activeQuestionflag = 1;
        }
?>
<?php 
    $paginationPageLayout = collect($page_layout); 
    $view_page = Input::get('page');
    if(!empty($view_page) && $view_page != 0)
    {
        $view_block = ceil(($page+1)/config('app.question_per_block'));
    }
    else
    {
        $view_block = 1;
    }
    $all_block = ceil($paginationPageLayout->count()/config('app.question_per_block'));                                     
?>
<div class="qg-backdrop" style="display:none;">
</div>
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/jquery.scrolling-tabs.css') }}" />
<script type="text/javascript">
    var is_score_display = "{{$is_score_display}}";
</script>
<div class="row" id="allow_click">
    <!-- questions no. list sidebar -->
    <div class="col-md-3 col-sm-4 col-xs-12 pull-right margin-10">

        @if(!empty($quiz->duration) || $quiz->is_timed_sections)
        <div class="xs-margin center quiz-custom-box">
            <h4 style="font-weight:normal">
            <?php
                $duration_flag = 1; 
            ?>
                <i class="fa fa-clock-o font-16"></i>
                &nbsp;
                <span id="timer"></span>
                </h4>
        </div>
        @endif
        <div class="template2 sm-margin quiz-custom-box1">
            <ul class="legends">
                <li style="display: inline-block;">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="row">
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <span class="answered attempt-legends"></span>
                                    <span class="review-count">{{count(array_diff($attempt->details['answered'],$attempt->details['reviewed']))}}</span>
                                </div>
                                <div class="col-xs-8 col-sm-8 col-md-8 margin-10">
                                    <p class="font-10">{{trans('assessment/attempt.answered')}}</p>
                                </div>    
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="row">
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <span class="not-answered attempt-legends"></span>
                                    <span class="review-count">{{ count(array_diff($attempt->details['viewed'], $attempt->details['answered'], $attempt->details['reviewed'])) }}</span>
                                </div>
                                <div class="col-xs-8 col-sm-8 col-md-8">
                                    <p class="font-10">{{trans('assessment/attempt.not_answered')}}</p>
                                </div>    
                            </div>
                        </div>                
                    </div>        
                </li>
                <li style="display: inline-block;">
                    <div class="row">
                        <div class="col-xs-6 col-sm-6 col-md-6">
                            <div class="row">
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <span class="not-viewed attempt-legends"></span>
                                    <span class="review-count">{{count($attempt->details['not_viewed'])}}</span>                                   
                                </div>
                                <div class="col-xs-8 col-sm-8 col-md-8 margin-10">
                                    <p class="font-10">{{trans('assessment/attempt.not_viewed')}}</p>
                                </div>    
                            </div>
                        </div>
                        <div class="col-xs-6 col-sm-6 col-md-6" style="margin-left: -10px;">
                            <div class="row">
                                <div class="col-xs-4 col-sm-4 col-md-4">
                                    <span class="review-not-answered attempt-legends"></span>
                                    <span class="review-count">{{ count(array_diff($attempt->details['reviewed'], $attempt->details['answered'])) }}</span>
                                </div>
                                <div class="col-xs-8 col-sm-8 col-md-8">
                                    <p class="font-10">{{trans('assessment/attempt.reviewed')}}</p>
                                </div>    
                            </div>
                        </div>                
                    </div>        
                </li>
                <li style="display: inline-block;">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-12">
                            <div class="row">
                                <div class="col-xs-3 col-sm-3 col-md-3">
                                    <span class="review-answered attempt-legends"></span>
                                    <span class="review-count review-answered-count">{{count(array_intersect($attempt->details['answered'], $attempt->details['reviewed']))}}</span>                                   
                                </div>
                                <div class="col-xs-8 col-sm-8 col-md-8">
                                    <p class="font-10" style="margin-left: -10px;">{{trans('assessment/attempt.answered_reviewed')}}</p>
                                </div>
                            </div>
                        </div>                
                    </div>        
                </li>
            </ul>
            <div>
            @if(isset($attempt->section_details))
                <ul class="question @if(isset($duration_flag) && $duration_flag === 1) quiz2-ul-dr-height @else quiz2-ul-height @endif">
                <?php
                    $question_sequence_no = 1;
                    foreach($attempt->section_details as $key_section_id => $eachSection) {
                        foreach($eachSection['page_layout'] as $pno=>$q)
                        {
                            foreach ($q as $key => $value) 
                            {

                                $class = AttemptHelper::getQuestionStatus($attempt->details, $value);
                                if($page == $pno && $active_section === $key_section_id)
                                {
                                    $active_question_sequence_no = $question_sequence_no;
                                    echo '<li><a class="page hide '.$class.'" href="#" data-preq="'.$pno.'" data-section-id="'.$key_section_id.'" onclick="return false">'.$question_sequence_no++.'</a></li>';
                                }
                                else
                                {
                                    $cursor = '';
                                    if($key_section_id !== $sec_id && $quiz->is_timed_sections) {
                                        $cursor = 'cursor:default';
                                    }
                                    $answered = Session::get('assessment.'.$attempt->attempt_id.'.question_answered');
                                    $blur = (in_array($value, $answered))? 'answered' : 'not-answered';
                                    $urlConnect = URL::to('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno.'&section='.$key_section_id);
                                    echo '<li><a style="'.$cursor.'" data-section-id="'.$key_section_id.'" href='.$urlConnect.' class = "page shiftingpageclass hide '.$class.'" data-preq="'.$pno.'" data-info="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno).'" >'.$question_sequence_no++.'</a></li>';
                                }
                            }
                        }
                    }
                ?>
                </ul>
            @else
            <ul class="question @if(isset($duration_flag) && $duration_flag === 1) quiz2-ul-dr-height @else quiz2-ul-height @endif">
            <?php 
                $j = $question_sequence_no + 1;
                $question_sequence_no = $question_sequence_no + 1;
                foreach($page_layout as $pno => $layout) {
                    if($page == $pno) {
                        foreach($layout as $q) {
                            $active_question_sequence_no = $question_sequence_no;
                            $class = AttemptHelper::getQuestionStatus($attempt->details, $q);
                            echo '<li><a class="page selected-answered '.$class.'" href="#" data-preq="'.$pno.'" onclick="return false">'.$question_sequence_no++.'</a></li>';
                        }
                    } else {
                        $answered = Session::get('assessment.'.$attempt->attempt_id.'.question_answered');
                        foreach($layout as $q) {
                            $class = AttemptHelper::getQuestionStatus($attempt->details, $q);
                            echo '<li><a class ="page shiftingpageclass '.$class.'" data-preq="'.$pno.'" data-info="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno).'" >'.$question_sequence_no++.'</a></li>';
                        }
                    }                       
                }
                ?>
            </ul>
            @endif
            </div>
        </div>
        @if($paginationPageLayout->count() > config('app.question_per_block'))
        <div class="xs-margin center quiz-custom-box hide">
            <h4 style="font-weight:normal">
                    <form action="">
                        <?php
                            $url = URL::to("assessment/attempt/".$attempt->attempt_id."?page=0&section=".$sec_id);
                        ?>
                        <a href='{{$url}}' class="btn btn-primary btn-sm"><i class="fa fa-fast-backward"></i></a> 
                        <?php
                            $first_block = (($view_block - 1) * config('app.question_per_block'));                              
                            $first_block_view = $first_block;
                            if($first_block <= 0)
                            {
                                $first_block = 1;
                                $first_block_view = 0;
                            }
                            $url = URL::to("assessment/attempt/".$attempt->attempt_id."?page=".($first_block - 1 )."&section=".$sec_id);
                        ?>
                        <a href='{{$url}}' class="btn btn-primary btn-sm"><i class="fa fa-backward"></i></a>
                                                                                    
                        <?php
                            if($view_block > 1)
                            {
                                $next_block = (($view_block) * config('app.question_per_block'));                               
                            }
                            else
                            {
                                $next_block = config('app.question_per_block'); 
                            }
                            $next_block_view = $next_block;
                            //$next_block = $next_block + 1;
                            if($next_block >= $paginationPageLayout->count())
                            {
                                $next_block_view = $paginationPageLayout->count();
                                $next_block = $paginationPageLayout->count() - 1;
                            }
                            $url = URL::to("assessment/attempt/".$attempt->attempt_id."?page=".($next_block)."&section=".$sec_id);
                        ?>
                        <span style="border: 1px #dddddd;padding: 4px;width: 68px;" id="sequenceno_short" name="sequenceno_short">&nbsp;{{$begin_section_no + $first_block_view + 1}} - {{$begin_section_no + $next_block_view}}&nbsp;</span>
                        <a href='{{$url}}' class="btn btn-primary btn-sm"><i class="fa fa-forward"></i></a>
                        <?php
                            $url = URL::to("assessment/attempt/".$attempt->attempt_id."?page=".($paginationPageLayout->count()-1)."&section=".$sec_id);
                        ?>
                        <a href='{{$url}}' class="btn btn-primary btn-sm"><i class="fa fa-fast-forward"></i> </a>
                    </form>
            </h4>
        </div>
        @endif

        <div class="xs-margin">
            @if($quiz->is_timed_sections && !$last_section)
                <button class="submit_section btn btn-primary btn-block">
                    <i class="fa fa-check-square-o"></i> 
                    {{ trans('assessment/attempt.submit_section') }}
                </button>
            @else
            <button class="submit_quiz btn btn-primary btn-block">
                <i class="fa fa-check-square-o"></i> 
                    {{ trans('assessment/attempt.submit_quiz') }}
            </button>
            @endif
        </div>
    
    </div>
    <!-- questions no. list sidebar -->
<?php
$answer_count = 0;
$ascii_char_value = 97;
$page = Input::get('page');
if(empty($page))
{
    $page = 0;
}
$quiz_title = $quiz->quiz_name;
$quiz_description = $quiz->quiz_description;
$quiz_attempt_id = $attempt->attempt_id;
$mark_review = false;
$marks_label = null;
$user_response = '';
foreach($attemptdata as $q)
{
    $question_definition = $q->question_text;
    $question_answer_list = $q->answers;
    $question_id = $q->question_id;
    $answered_option = $q->user_response;
    if(isset($q->mark_review) && $q->mark_review === true)
    {
        $mark_review = true;
    }
    if(isset($q->question_mark))
    {
        $marks_label = $q->question_mark;
    }
    if(isset($q->user_response))
    {
        $user_response = $q->user_response;
    }
}
$clearURL = URL::to('assessment/clear-answer/'.$quiz_attempt_id.'?page='.$page.'&section='.$active_section.'&questionID='.$question_id.'&'.$requestUrl);
$reviewURL = URL::to('assessment/mark-review/'.$quiz_attempt_id.'/'.$page.'/'.$active_section.'/'.$question_id.'?'.$requestUrl);
$finishReview = URL::to('assessment/finish-review/'.$quiz_attempt_id.'/'.$page.'/'.$active_section.'/'.$question_id.'?'.$requestUrl);
if(!empty($page) && ($page-1) > 0) 
{
    $prev_page = ($page -1);
}
else
{
    $prev_page = 0;
}
$previousURL = URL::to('assessment/attempt/'.$quiz_attempt_id.'?page='.$prev_page.'&section='.$active_section.'&'.$requestUrl);
?>
    <!-- questions tabs -->
    <div class="col-md-9 col-sm-8 col-xs-12 cs-quiz-tabs margin-10">
        <h4 class="xs-margin margin-top-0">
            <strong>{{$quiz_title}}</strong> 
            <span class="pull-right font-12">
                @if(!empty($quiz_description))
                    <a href="#info-modal" data-toggle="modal" class="btn l-gray info-btn">
                        <i class="fa fa-question"></i>
                    </a>
                @endif
            </span>
        </h4>
        <hr class="margin-bottom-0 margin-top-0">
        @if(!empty($section_ids))
                            <!-- <div class="btn-group"> -->
                        <div id="jquery-script-menu">
                            <div class="row" >
                                <div class="col-md-12">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                    <?php $flag = true;?>
                            @foreach($section_ids as $index => $section_id)
                                    <?php 
                                      if($sec_id == 0 && $flag){
                                        $class_css ='active';
                                        $flag = false;
                                      }elseif ($section_id == $sec_id) {
                                        $class_css ='active';
                                        $flag = false;
                                      } else{
                                        $class_css = '';
                                      }
                                    ?>
                                      <li data-section="{{$section_id}}" role="presentation" class="{{$class_css}}"><a style="{{ $section_id !== $sec_id && $quiz->is_timed_sections?'cursor:default' : '' }}" class = "getsection" href="#tab1" role="tab" data-toggle="tab" data-preq="0" data-section="{{$section_id}}"><?php echo $section_names[$index];?></a></li>
                            @endforeach
                                    </ul>
                                </div>
                            </div>  
                        </div>
        @else
            <div class="lg-margin"></div><br>
        @endif
        <form id="quizForm" action="{{ url('assessment/attempt/'.$quiz_attempt_id) }}?{{$requestUrl}}" method="POST" accept-charset="UTF-8">
            <div class="row quiz2-height">
                <input id = 'ques_time_taken_id' name = 'ques_time_taken' type="hidden" >
                <input id = 'section_id_str' name = 'section' type="hidden" >
                <input id='qes_ids' type="hidden" name="q[]" value="{{$question_id}}">
                <div class="col-md-5 col-sm-4 col-xs-12">
                    <div class="ques-no">{{$active_question_sequence_no}}.</div>
                    <div class="ques-div">
                        <div class="q-text table-responsive">
                            {!! $question_definition !!}
                        </div>
                    </div>
                </div>
                <div class="col-md-7 col-sm-8 col-xs-12">
                    <p>
                        @if(!empty($marks_label))
                        <span style="border: 2px solid #fad160;padding: 1px 5px;" class="font-10 pull-right">
                                    Marks: <span id="mark">{{$marks_label}}</span>
                            </span>
                        @endif
                    </p>
                    <div class="ques-div">
                        <div class="ques-list">
                            <ul style="padding-left:10px;">
                            <?php
                            $answered_option = Session::get('answered_option');
                            Session::forget('answered_option');
                            ?>
                            <?php
                                $asciiCharVal = 65;
                                $order = $q->answer_order;
                                uksort($question_answer_list, function ($key1, $key2) use ($order) {
                                    return (array_search($key1, $order) > array_search($key2, $order));
                                });
                            ?>
                            @foreach($question_answer_list as $key => $each_answer)
                                <li>
                                    <div class="left">  
                                        {{ chr($asciiCharVal+$key++)}}&#41;&#32;            
                                        <input type="radio" name="q:{{$question_id}}" value="{{ $answer_count++ }}" @if(!is_null($answered_option) && $answer_count == ($answered_option + 1)) {{"checked"}} @elseif(is_null($answered_option)) {{ ($user_response === $each_answer["answer"])? "checked" : "" }} @endif>
                                    </div>
                                    <div class="right">{!! $each_answer['answer']!!}</div>
                                </li>
                            @endforeach                         
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
            <hr>    
            <div class="row">
                <div class="col-md-8 col-sm-8 col-xs-12 xs-margin">
                    <a id="review_url" data-url ="{{$reviewURL}}" class="btn btn-default"><i class="fa fa-star"></i><?php echo Lang::get('assessment/attempt.mark_the_review'); ?> </a>&nbsp;&nbsp;
                    <input type="hidden" id="reviewed" name="reviewed" value="">
                    <a href="{{$clearURL}}" class="btn btn-default" id="clear"><i class="fa fa-eraser"></i><?php echo Lang::get('assessment/attempt.clear_answer'); ?></a>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <div class="pull-right">
                        <input type="hidden" id='next_page_id' name="next_page" value="{{++$page }}">
                        &nbsp;&nbsp;
                        <button type="submit" class="btn btn-primary" name="next" id="next" value="{{ $last_question ? '0': $page }}">
                            {{ trans('assessment/attempt.next') }}
                        </button>
                    </div>
                </div>
            </div>
        </form>
            
    </div>
    <!-- questions tabs -->
</div>
<div id="info-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close red" id="unclick_event2" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title center"><strong>{{Lang::get('assessment/attempt.modal_header_text')}}</strong></h4>
            </div>
            <div class="modal-body">
                <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
                    <div class="row">
                        <div class="col-md-12">
                            <p>
                                {!! $quiz_description !!}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer center">
                <button type="button" class="btn-success" id="unclick_event" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong><?php echo Lang::get('assessment.ok'); ?></strong></button>
            </div>
        </div>
    </div>
</div>
@include('portal.theme.default.assessment.success')
<script>
    var share_tk= {{$total_tk}};
    var page = {{$current_page}};
    var attempt_id = {{$quiz_attempt_id}};
    var section = {{$active_section}};
    var submit_messge = "{{trans('assessment.quiz_submit')}}";
    var timed_sections = "{{ array_get($quiz, 'is_timed_sections', false) }}";
    var time = share_tk%60;
    var options_class = '.ques-list ul',
        options = '<li>'+
                    '<div class="left">'+  
                        '{a-z}'+             
                        '<div class="radio"><span class=""><input type="radio" name="{value}" value="{value}"></span></div>'+
                    '</div>'+
                    '<div class="right">{text}</div>'+
                '</li>';
    var tt = 0;
    var h = parseInt(share_tk / 3600);
    var m = parseInt(share_tk / 60);
    var s = 0;
    var sec_id = "{{$sec_id}}";
    var time_html = '';
    time_html = h+' : '+m+' : '+time;
    $('#ques_time').html(time_html);
    $('#ques_time_taken_id').val(tt);
    $(document).ready(function(){
        $('#section_id_str').val(sec_id);
        @if($section_details->sum('duration') !== 0)
            var currentSection = sec_id;
        @endif
        $('.submit_section').click( function(){
                if(confirm("{{ trans('assessment.section_submit') }}")) {
                    $(this).attr('disabled', true).addClass('disabled');
                    $('#next_page_id').val(0);
                    closeSection();
            }
        });
        setInterval(function(){
            time++;
            tt++;
            if(time >= 60){
                m++;
                time = 0;
            }
            if(m >= 60){
                h++;
                m = 0;
            }
            time_html = h+' : '+m+' : '+time;
            $('#ques_time').html(time_html);
            $('#ques_time_taken_id').val(tt);
        }, 1000);
        attempt.moveToView();  
    });
    submitForm = function(){
        @if($quiz->is_timed_sections)
            setTimeout(function(){
                $('#quizForm').submit();
                return false;
            }, 2000);
        @else
            $('#quizForm').submit();
        @endif
    }
    var closeQuiz = function(){
        setTimeout(function(){
            var $this = $('#quizForm');
            var data = $this.serializeArray();
            $.ajax({
                url: $this.attr('action'),
                method: $this.attr('method'),
                data: data,
                dataType: 'json',
                success: function(response){
                    if (response.status != 'undefined') {
                        if (response.status) {
                            @if($is_score_display == 'yes')
                                redirectToAnalytics();
                            @else 
                                closeWindow();
                            @endif
                        } else {
                            closeWindow();
                        }
                    } else {
                        closeWindow();
                    }
                }
            });
        }, 2000);
    };
</script>
<script type="text/javascript">
    var redirectToAnalytics = function(){
        if(is_score_display == 'no'){
            closeWindow();
        }
        var analyticUrl = "{{ url('assessment/question-detail/'.$attempt->attempt_id.'/'.$no_of_attempts) }}";
        $.ajax({
            url: analyticUrl,
            method: 'GET',
            dataType: 'json',
            success: function(response){
                if(response.attempt != 'undefined' && response.attempt){
                    reloadWindow();
                    window.location = analyticUrl;
                } else {
                    $('#success').modal('show');
                    setTimeout(function(){
                        closeWindow();
                    }, 5000);
                }
            },
        });
    };
</script>
@if(!empty($quiz->duration) || $quiz->is_timed_sections)
    <script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.plugin.js') }}"></script>
    <script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.countdown.js') }}"></script>
    <script>
    <?php
        $is_timed_quiz = $quiz->duration >= 1;
        $duration = ($attempt->started_on->timestamp + ($quiz->duration * 60)) - time();
        if($duration < 0) $duration = 0;
    ?>
    @if($section_duration) 
    $(function () {
        $('#timer').countdown({
            until: +{{ $section_duration>0?$section_duration - time():0 }},
            compact: true,
            layout: '{hnn}{sep}{mnn}{sep}{snn}'
        });
    });
    @else
    $(function () {
        $('#timer').countdown({
            until: +{{ $duration }},
            compact: true,
            layout: '{hnn}{sep}{mnn}{sep}{snn}'
        });
    });
    @endif
    @if($is_timed_quiz)
    setTimeout(function(){
        @if(isset($quiz->is_timed_sections) && $quiz->is_timed_sections)
            @if(!$last_section)
                $('#section-message').html("{{ trans('assessment.section_submit_message') }}");
            @endif
            toggleSectionLoader();
            $('#next_page_id').val("submit_quiz");
            closeQuiz();
        @else
            var xmlHTTPRequest = $.ajax({
                url : "{{ config('app.url') }}/assessment/close-attempt/{{ $attempt->attempt_id.'?'.$requestUrl}}",
                type : "post"
            });

            xmlHTTPRequest.done(function(response, status, jqXHR){
                if(response.status !== null && response.status !== undefined)
                {               
                    alert("{{ trans('assessment.assessment_time_over') }}");
                    if (is_score_display == 'yes') {
                        redirectToAnalytics();
                    } else{
                        closeWindow();
                    }
                }
            });
        @endif
    }, {{ $duration }} * 1000);
    @endif
    </script>   
@endif
<script src="{{ asset('portal/theme/default/js/jquery.scrolling-tabs.js') }}"></script>
<script>
$('.nav-tabs').scrollingTabs();
</script>
@if(isset($remaining_seconds) && $remaining_seconds)
<script type="text/javascript">
    setTimeout(function(){
            var xmlHTTPRequest = $.ajax({
                url : "{{ config('app.url') }}/assessment/close-attempt/{{ $attempt->attempt_id }}",
                type : "post"
            });

            xmlHTTPRequest.done(function(response, status, jqXHR){
                if(response.status !== null && response.status !== undefined)
                {
                    attempt.submitAnswer();
                    alert("{{ trans('assessment.assessment_time_over') }}");
                    reloadWindow();
                    if (is_score_display == 'yes') {
                        redirectToAnalytics();
                    } else{
                        closeWindow();
                    }
                }
            });
        }, {{ $remaining_seconds}}*1000);
</script>
@endif
@if($section_duration)
<script type="text/javascript">
    setTimeout(function(){
        closeSection();
    }, {{ $section_duration>0?$section_duration - time():0 }}*1000);

    var closeSection = function(){
        var sectionSubmit = $.ajax({
            url: "{{ config('app.url') }}/assessment/close-section/{{ $attempt->attempt_id }}",
            method: 'post',
            dataType: 'json',
        });
        sectionSubmit.done(function(response){
            if(response.status) {
                @if($last_section)
                    $('#next_page_id').val("submit_quiz");
                @else
                    $('#next_page_id').val(0);
                @endif              
                $('#section_id_str').val(response.section);
                toggleSectionLoader();
                attempt.submitAnswer();
                $(".qg-backdrop").hide();
                @if($last_section)                   
                    attempt.closeAttempt();
                    reloadWindow();
                    @if($is_score_display == 'yes')
                        redirectToAnalytics();
                    @endif
                @else
                    window.location.reload();
                @endif
            }
        });
    }
    toggleSectionLoader = function(){
        $(".qg-backdrop, .qg-loading-bar").hide();
        $('#overlay').toggle();
    }   
</script>
@endif
<script type="text/javascript">
$(document).ready(function(){
    if($('.quiz-custom-box').length == 0)
    {
        $(".quiz-custom-box1").css("margin-top","5.5vh");
    }    
});
</script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
<script type="text/javascript" src="{{ asset('portal/theme/default/js/assessment/attempt.js') }}"></script>
@stop