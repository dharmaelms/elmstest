@section('content')
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/loader.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ URL::to("portal/theme/default/css/responsive-iframe.css") }}">
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/jquery.scrolling-tabs.css') }}" />
<style type="text/css">
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
        if($quiz->practice_quiz == '')
        {
            $mock_quiz = "yes";
        }
        else
        {
            $mock_quiz = "no";
        }

    $i = $qno = 1;
    $page_layout  = [];
    if(isset($attempt->section_details)){
        foreach($attempt->section_details[$sec_id]['page_layout'] as $pno => $layout) {
            if($page == $pno) {
                $qno = $i;
            }
            $i += count($layout);
        }
        $page_layout = $attempt->section_details;
    }else{
        foreach($attempt->page_layout as $pno => $layout) {
            if($page == $pno) {
                $qno = $i;
            }
            $i += count($layout);
        }
        $page_layout = $attempt->page_layout;
    }
    
    ?>
    <style type="text/css">
        #ques_timer {
            padding-left: 100px;
        }
        .shiftingpageclass{
            cursor: pointer;
        }
    </style>
    <script type="text/javascript">
        var is_score_display = "{{$is_score_display}}";
    </script>   
    <div class="row" id="allow_click">
        <!--content starts here-->
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
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
            <div class="qg-backdrop" style="display:none;"></div>
            <div class="row">
                <div class="panel panel-default quiz-name">
                    <div class="panel-heading qus-main-panel-head">
                        <b>{{ $quiz->quiz_name }}</b>&nbsp;
                        @if(isset($quiz->quiz_description) && !empty($quiz->quiz_description))
                    <a href="#quiz-info-modal" class="btn l-gray info-btn" data-toggle="modal" title="{{Lang::get('assessment/attempt.i_title')}}" style="margin-top: -3px;"><i class="fa fa-question"></i></a></h4>
            @endif
                        <div class="pull-right">
                            <div id="timer">            
                            </div>
                        </div>
                    </div>
                    <div class="panel-body padding-0">
                        <div class="xs-margin"></div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin">
                        @if(!empty($section_ids))
                            <!-- <div class="btn-group"> -->
                        <div id="jquery-script-menu">
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                    <?php $flag = true;?>
                            @foreach($section_ids as $index => $section_id)
                                    <?php 
                                      // $acctive_sec = (int)Input::get('section', 0);
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
                                      <li role="presentation" data-section="{{$section_id}}" class="{{$class_css}}"><a class = "getsection" href="#tab1" role="tab" data-preq="0" data-toggle="tab" data-section="{{$section_id}}">{{$section_names[$index]}}</a></li>
                            @endforeach
                                    </ul>
                                </div>
                            </div>  
                        </div>
                            <!-- </div> -->
                        @endif
                            
                            <div class="panel-body question-panel default sm-margin">
                                <div class="row">
                                    <div class="col-md-4">
                                        <h4 style="margin-left:6px;"><strong><?php echo Lang::get('assessment.questions'); ?></strong> &nbsp;
                                        <?php
                                        $active_section = $sec_id;
                                        ?>
                                        @if(isset($section_details) && !empty($section_details))            
                                            @foreach ($section_details as $key => $eachSection) 
                                                @if(isset($eachSection['title']) && !empty($eachSection['title']))
                                                    @if(isset($eachSection['section_id']) && $eachSection['section_id'] === $active_section && !empty($eachSection['description']))
                                                        <a href="#info-modal" class="btn l-gray info-btn" data-toggle="modal" title="{{Lang::get('assessment/attempt.i_title')}}"><i class="fa fa-question"></i></a></h4>
                                                    @endif
                                                @endif  
                                            @endforeach                             
                                        @endif
                                        <?php
                                            if($section_details->isEmpty())
                                            {
                                                if(isset($quiz->description) && !empty($quiz->description))
                                                {
                                                    ?>
                                                        <a href="#info-modal" class="btn l-gray info-btn" data-toggle="modal" title="{{Lang::get('assessment/attempt.i_title')}}"><i class="fa fa-question"></i></a></h4>
                                                    <?php
                                                }
                                            }
                                        ?>

                                        
                                    </div>
                                    <!-- Pagination logic -->

                                    <?php
                                        $active_section = $sec_id;
                                        $question_sequence_no = 0;                                      
                                        $page = Input::get('page');
                                    //  if(empty($page)) $page = 1;
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
                                </div>
<?php
$quiz_review_questions = $attempt->details['reviewed'];
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
                                <ul class="question">
                                    @if(isset($attempt->section_details))
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
                                                            $answered = $attempt->details['answered'];
                                                            $blur = (in_array($value, $answered))? 'answered' : 'not-answered';
                                                            $urlConnect = URL::to('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno.'&section='.$key_section_id);
                                                            echo '<li><a style="'.$cursor.'" data-section-id="'.$key_section_id.'" href='.$urlConnect.' class = "page shiftingpageclass hide '.$class.'" data-preq="'.$pno.'" data-info="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno).'" >'.$question_sequence_no++.'</a></li>';
                                                        }
                                                    }
                                                }
                                            }
                                        ?>
                                    @else
                                    <?php
                                    $j = $question_sequence_no + 1;
                                    $question_sequence_no = $question_sequence_no + 1;
                                    foreach($page_layout as $pno => $layout) {
                                        if($page == $pno) {
                                            foreach($layout as $q) {
                                                $class = AttemptHelper::getQuestionStatus($attempt->details, $q);
                                                $active_question_sequence_no = $question_sequence_no;
                                                if(in_array($q,$quiz_review_questions))
                                                {
                                                    echo '<li><div></div><a data-preq="'.$pno.'" class="page '.$class.'" href="#" onclick="return false">'.$question_sequence_no++.'</a></li>';
                                                }
                                                else
                                                {
                                                    echo '<li><div></div><a data-preq="'.$pno.'" class="page '.$class.'" href="#" onclick="return false">'.$question_sequence_no++.'</a></li>';
                                                }
                                            }
                                        } else {
                                            $answered = $attempt->details['answered'];
                                            foreach($layout as $q) {
                                                $blur = AttemptHelper::getQuestionStatus($attempt->details, $q);
                                                if(in_array($q,$quiz_review_questions))
                                                {
                                                    echo '<li><div></div><a class="page shiftingpageclass '.$blur.'" data-preq="'.$pno.'" data-info="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno).'" >'.$question_sequence_no++.'</a></li>';
                                                }
                                                else
                                                {
                                                    echo '<li><div></div><a class="page shiftingpageclass '.$blur.'" data-preq="'.$pno.'" data-info="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno).'" >'.$question_sequence_no++.'</a></li>';
                                                }
                                            }   
                                        }
                                    }
                                    ?>
                                    @endif
                                </ul>
                            </div>
                                <div class="center border-radius">
                                    @if($quiz->is_timed_sections && !$last_section)
                                        <button class="submit_section btn grey-cascade">
                                            <i class="fa fa-check-square-o"></i> 
                                            {{ trans('assessment/attempt.submit_section') }}
                                        </button>
                                    @else
                                    <button class="submit_quiz btn grey-cascade">
                                        <i class="fa fa-check-square-o"></i> 
                                            {{ trans('assessment.finish_the_attempt') }}
                                    </button>
                                    @endif
                                </div>      
                        </div>
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin">
                            <div id='ques_timer' class="pull-right" style="display:none;">
                                <p>
                                    <?php echo Lang::get('assessment.time_spend_on_ques'); ?> : <span id='ques_time'>0 : 0 : 0</span>   
                                </p>
                            </div>
                            <form id="quizForm" action="{{ url('assessment/attempt/'.$attempt->attempt_id) }}?{{$requestUrl}}" method="POST" accept-charset="UTF-8" onsubmit="return false;">
                                <input id = 'ques_time_taken_id' name = 'ques_time_taken' type="hidden" >
                                <input id = 'section_id_str' name = 'section' type="hidden" >
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 lg-margin qus-box">
                                    <?php $p = $page; $count_hr = 1;?>
                                    @foreach($attemptdata as $q)
                                    @if(isset($q->question_mark))
                                        <span class="pull-right sm-margin">
                                            <strong>
                                                {{Lang::get('assessment.marks')}} :
                                            </strong>
                                            {{ $q->question_mark }}
                                        </span>
                                    @endif
                                    <div class="panel-body qus-panel @if($count_hr++ < count($attemptdata)) question-desc @endif">
                                        
                                        <div class="ques-no qus-number">{{ $active_question_sequence_no }}</div>
                                        <div class="qus-heading">
                                            @if($q->question_type == 'MCQ')
                                            <input id='qes_ids' type="hidden" name="q[]" value="{{$q->question_id}}">
                                            <div class="q-text ques-div table-responsive">{!! $q->question_text !!}</div>
                                            <div class="ques-list radio-list" style="margin-top:30px;">
                                            <?php
                                                $answers = [];
                                                foreach($q->answers as $key => $answer)
                                                    $answers[array_search($key, $q->answer_order)] = $answer;
                                                ksort($answers);
                                                $answerChunks = collect($answers)->chunk(2);
                                                $answerCount = 0;
                                                $answered_option = $q->user_response;
                                                Session::forget('answered_option');                     
                                                $asciiCharVal = 97;
                                                foreach($answerChunks as $chunk)
                                                {
                                            ?>
                                                <div class="row">
                                                    @foreach($chunk as $answerData)
                                                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-bottom-10" style="max-height:300px;overflow-y:auto;">
                                                            <label class="lft-div">
                                                                <input type="radio" name="q:{{ $q->question_id }}" value="{{ $answerCount }}" @if(!is_null($answered_option) && $answerData["answer"] == $answered_option) {{"checked"}} @elseif(is_null($answered_option)) {{ ($q->user_response === $answerData["answer"])? "checked" : "" }} @endif >
                                                            </label>
                                                            <div class="right-div">
                                                                {{ chr($asciiCharVal+$answerCount++) }}&#41;&#32;
                                                            </div>
                                                            <div class="right-div1">
                                                                {!! html_entity_decode($answerData["answer"]) !!}
                                                            </div>
                                                        </div>
                                                    @endforeach
                                                </div>
                                            <?php
                                                }
                                            ?>
                                            @endif
                                            </div>
                                        </div>
                                    </div>
                                    @endforeach
                                </div>
                                @if(isset($_GET['requestUrl']) )
                                    <input type="hidden" id='requestUrl' name="requestUrl" value="{{Input::get('requestUrl') }}">
                                @endif
                                <input type="hidden" id='next_page_id' name="next_page" value="{{++$p }}">
                                <a href="{{$clearURL}}" id="clear" class="btn btn-default xs-margin"><i class="fa fa-eraser"></i> <?php echo Lang::get('assessment.clear_answer'); ?></a>&nbsp;&nbsp;
                                <a href="javascript:submitReview();" data-url="{{$reviewURL}}" id="review_url" class="btn btn-info xs-margin"><i class="fa fa-star"></i><?php echo Lang::get('assessment.mark_the_review'); ?></a>
                                <!-- <div class="page pull-left"> -->
                                    &nbsp;&nbsp;<button type="submit" id="next" class="btn btn-success xs-margin" name=""> <i class="fa fa-check"></i> {{ Lang::get('assessment.save_continue')}}</button>
                                <!-- </div> -->
                            </form>
                            <script type="text/javascript">
                            function submitReview()
                            {
                                $('#quizForm').attr('action',$('#review_url').data('url'));
                                $('#quizForm').attr('method','GET');
                                $('#quizForm').submit();
                            }
                            </script>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>
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
                    toggleSectionLoader();
                    $('#next_page_id').val("submit_quiz");
                    closeQuiz();
                @else
                    var xmlHTTPRequest = $.ajax({
                        url : "{{ config('app.url') }}/assessment/close-attempt/{{ $attempt->attempt_id.'?'.$requestUrl}}",
                        type : "post"
                    });

                    xmlHTTPRequest.done(function(response, status, jqXHR){
                        var is_score_display = "{{$is_score_display}}";
                        if(response.status !== null && response.status !== undefined)
                        {               
                            alert("{{ trans('assessment.assessment_time_over') }}");
                            if(is_score_display == 'yes') {
                                redirectToAnalytics();  
                            } else {
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
                    alert("{{ trans('assessment.assessment_time_over') }}");
                    if(is_score_display == 'yes') {
                        redirectToAnalytics();  
                    } else {
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
    $('.submit_section').click( function(){
            if(confirm("{{ trans('assessment.section_submit') }}")) {
                $(this).attr('disabled', true).addClass('disabled');
                $('#next_page_id').val(0);
                closeSection();
        }
    });
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
                    if(is_score_display == 'yes') {
                        redirectToAnalytics();  
                    } else {
                        closeWindow();
                    }
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
<script>
    var share_tk= {{$total_tk}};
    var page = {{$page}};
    var attempt_id = {{$quiz_attempt_id}};
    var section = {{$active_section}};
    var submit_messge = "{{trans('assessment.quiz_submit')}}";
    var timed_sections = "{{ array_get($quiz, 'is_timed_sections', false) }}";
    var options_class = '.ques-list',
        options = '<div class="row pull-left col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-bottom-10" style="max-height:300px;overflow-y:auto;">'+
                    '<label class="lft-div">'+
                        '<div class="radio"><span><input type="radio" name="{value}" value="{value}"></span></div>'+
                    '</label>'+
                    '<div class="right-div">{a-z}</div>'+
                    '<div class="right-div1">{text}</div>'+
                '</div>';
    var time = share_tk%60;
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
        $('.shiftingpageclass').click(function(){
            $('#next_page_id').val($(this).data("preq"));
            $('#section_id_str').val(sec_id);
            $('#quizForm').submit();
        }); 
        $('.getsection').click(function(){
            $('#next_page_id').val(0);
            $('#section_id_str').val($(this).data('section'));
            $('#quizForm').submit();
        }); 

        $('.submit_quiz').click(
            function(){
                $('#section_id_str').val(sec_id);
                $('#next_page_id').val("submit_quiz");
                $('#quizForm').submit();
            }
        ); 

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
                        if (response.status && is_score_display == 'yes') {
                            redirectToAnalytics();
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
    var redirectToAnalytics = function(){
        if(is_score_display == 'no') {
            closeWindow();
        }
        var analyticUrl = "{{ url('assessment/question-detail/'.$attempt->attempt_id.'/'.$no_of_attempts) }}";
        $.ajax({
            url: analyticUrl,
            method: 'GET',
            dataType: 'json',
            success: function(response){
                reloadWindow();
                if(response.attempt != 'undefined' && response.attempt){
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
@include('portal.theme.default.assessment.success')
<div id="info-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title center"><strong>{{Lang::get('assessment/attempt.modal_header_text')}}</strong></h4>
            </div>
            <div class="modal-body">
                <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
                    <div class="row">
                        <div class="col-md-12">
                            <p>

                                @if(isset($section_details) && !empty($section_details))            
                                    @foreach ($section_details as $key => $eachSection) 
                                        @if(isset($eachSection['title']) && !empty($eachSection['title']))
                                            @if(isset($eachSection['section_id']) && $eachSection['section_id'] === $active_section)
                                                <?php echo $eachSection['description'];?>
                                            @endif
                                        @endif  
                                    @endforeach                             
                                @endif
                                <?php
                                    if($section_details->isEmpty())
                                    {
                                        echo $quiz->description;
                                    }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer center">
                <button type="button" class="btn-success" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong><?php echo Lang::get('assessment.ok'); ?></strong></button>
            </div>
        </div>
    </div>
</div>
<div id="quiz-info-modal" class="modal fade" tabindex="-1" aria-hidden="true">
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
                            {!! $quiz->quiz_description !!}
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
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
<script type="text/javascript" src="{{ asset('portal/theme/default/js/assessment/attempt.js') }}"></script>
@stop