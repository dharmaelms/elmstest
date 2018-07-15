<style type="text/css">
    [data-toggle="collapse"] {
        cursor: pointer;
    }

</style>
<?php
$liveChecked    = ($quiz->is_production === 1) ? "checked":'';
$betaChecked    = ($quiz->is_production === 0) ? "checked":'';
$disabled       = ($quiz->is_production === 1) ? "disabled":'';

$is_score_display = isset($quiz->is_score_display) ? $quiz->is_score_display : true;
?>
 <!-- Evnironment setup -->
    <div class="pull-right">
        <label class="radio-inline">Environment: </label>
        <label class="radio-inline">
            <input type="radio" name="environment" value="is_production" <?php echo $liveChecked; ?> onchange="SetEnvironment(this)" >{{ trans('admin/assessment.live_quiz') }}
        </label>
        <label class="radio-inline">
            <input type="radio" name="environment" value="is_beta"  <?php echo $betaChecked; echo $disabled; ?>  onchange="SetEnvironment(this)" > {{ trans('admin/assessment.test_quiz') }}
        </label> 
    </div>
    <!-- environment setup ends here -->

<form action="#" class="form-horizontal form-bordered form-row-stripped" method="post" accept-Charset="UTF-8">
    <input type="hidden" name="_q" value="{{ $quiz->quiz_id }}">
    <input type="hidden" id="environment_selected" name="environment_selected" value="{{ $quiz->beta?'is_beta':'is_production' }}"> 
    <div class="form-group">
        <label for="quiz_name" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.quiz_name') }} <span class="red">*</span></label>
        <div class="col-sm-9 col-lg-6 controls">
            <input type="text" name="quiz_name" class="form-control" value="{{ $quiz->quiz_name }}" >
            {!! $errors->first('quiz_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="quiz_description" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.instructions') }}</label>
        <div class="col-sm-9 col-lg-6 controls">
            <textarea name="quiz_description" rows="5" id="editquiz" class="form-control ckeditor">{!! $quiz->quiz_description !!}</textarea>
            {!! $errors->first('quiz_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keywords_tags') }}</label>
        <div class="col-sm-9 col-lg-6 controls">
            <input type="text" name="keywords" class="form-control tags" value="@if(is_array($quiz->keywords)) {{ implode(',', $quiz->keywords) }} @endif">
            {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="practice_quiz" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.mark_as_practice_quiz') }}</label>
        <div class="col-sm-9 col-lg-3 controls">
            <input type="checkbox" name="practice_quiz" @if($quiz->practice_quiz) checked @endif></input>
            {!! $errors->first('practice_quiz', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>    
    <div class="form-group">
        <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.schedule_date') }} <span class="red">*</span></label>
        <div class="col-lg-3 controls">
            <div class="input-group date">
                <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                <input type="text" name="start_date" class="form-control general-input-start-date" style="cursor: pointer" value="@if(!empty($quiz->start_time)) {{ $quiz->start_time->timezone(Auth::user()->timezone)->format('d-m-Y'
                ) }} @endif" readonly="readonly">
            </div>
            {!! $errors->first('start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
        <div class="col-lg-2 controls">
            <div class="input-group">
                <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                <input type="text" name="start_time" onclick="$(this).prev().click()" class="form-control general-input-start-time" style="cursor: pointer" value="@if(!empty($quiz->start_time)) {{ $quiz->start_time->timezone(Auth::user()->timezone)->format('H:i'
                ) }} @endif" readonly="readonly">
                <span class="input-group-btn">
                    <button id="start_time_reset" class="btn" ><i class="fa fa-undo"></i></button>
                </span>
            </div>
            {!! $errors->first('start_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="end_date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.expiry_date') }}&nbsp;
        <span class="red"
        @if(!$quiz->practice_quiz)
           style="display: inline;"
        @else
            style="display: none;"
        @endif
         >*</span>
        </label>
        <div class="col-lg-3 controls">
            <div class="input-group date">
                <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                <input type="text" name="end_date" class="form-control general-input-end-date" style="cursor: pointer" value="@if(!empty($quiz->end_time)) {{ $quiz->end_time->timezone(Auth::user()->timezone)->format('d-m-Y'
                ) }} @endif" readonly="readonly">
            </div>
            {!! $errors->first('end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
        <div class="col-lg-2 controls">
            <div class="input-group">
                <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                <input type="text" name="end_time" onclick="$(this).prev().click()" class="form-control general-input-end-time" style="cursor: pointer" value="@if(!empty($quiz->end_time)) {{ $quiz->end_time->timezone(Auth::user()->timezone)->format('H:i') }} @endif" readonly="readonly">
                <span class="input-group-btn">
                    <button id="end_time_reset" class="btn" ><i class="fa fa-undo"></i></button>
                </span>
            </div>
            {!! $errors->first('end_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>
    <div class="form-group">
        <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.duration') }}&nbsp;
        <span class="red"
        @if(!$quiz->practice_quiz)
           style="display: inline;"
        @else
            style="display: none;"
        @endif
         >*</span>
        <div><span class="help-inline">(hh:mm)&nbsp;&nbsp;</span></div>    
        </label>
        <div class="col-sm-5 col-lg-3 controls">
            <div class="input-group">
                <a class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></a>
                <input type="text" name="duration" class="form-control input-duration" onclick="$(this).prev().click()" value="@if(!empty($quiz->duration)) {{ (int)($quiz->duration/60) }}:{{ $quiz->duration%60 }}@else @endif" readonly="readonly">
                <span class="input-group-btn">
                    <button id="duration_reset" class="btn" ><i class="fa fa-undo"></i></button>
                </span>
            </div>
            {!! $errors->first('duration', '<span class="help-inline" style="color:#f00">:message</span>') !!}
        </div>
    </div>

    <!-- score option starts here -->
        <div class="form-group">
            <label for="score_display" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.score_display') }}</label>
            <div class="col-sm-5 col-lg-3 controls">
                <div class="input-group">
                    <label class="radio-inline">
                        <input type="radio" name="score_display" value="on" @if($is_score_display) checked @endif> {{ trans('admin/assessment.show') }}
                    </label>
                    <label class="radio-inline">
                        <input type="radio" name="score_display" value="off" @if(!$is_score_display) checked @endif>{{ trans('admin/assessment.hide') }}
                    </label>
                </div>
            </div>
        </div>
    <!-- score options ends here -->

    <div class="form-group">
        <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.review_options') }}</label>
        <div class="col-sm-5 col-lg-3 controls">
            <div class="input-group">
                <input type="checkbox" name="review_the_attempt" @if($quiz->review_options['the_attempt']) checked @endif @if($quiz->practice_quiz)  @endif></input> {{ trans('admin/assessment.the_attempt') }}
                <br>
                <input type="checkbox" name="review_whether_correct" @if($quiz->review_options['whether_correct']) checked @endif @if($quiz->practice_quiz)  @endif></input> {{ trans('admin/assessment.whether_correct') }}
                <br>
                <input class="review" type="checkbox" name="review_marks" @if($quiz->review_options['marks'] == 'on') checked @endif></input> {{ trans('admin/assessment.marks') }}
                <br>
                <input class="review" type="checkbox" name="review_rationale" @if($quiz->review_options['rationale']) Checked @endif></input> {{ trans('admin/assessment.rationale') }}
                <br>
                <input class="review" type="checkbox" name="review_correct_answer" @if($quiz->review_options['correct_answer']) checked @endif></input> {{ trans('admin/assessment.correct_answer') }}
            </div>
        </div>
    </div>    
    <div class="form-group panel-group" id="accordion">
        <div class="panel panel-default">
            <div class="panel-heading advance-toggle" data-toggle="collapse" data-parent="#accordion" href="#advance-options">
                <h4 class="panel-title">
                    <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#advance-options">
                      {{ trans('admin/assessment.advanced_options') }} 
                    </a>
                    <i class="indicator glyphicon glyphicon-chevron-down  pull-right"></i>
                </h4>
            </div>
            <div id="advance-options" class="panel-collapse collapse">
                <div class="panel-body">
                    <div class="form-group">
                        <label for="attempts" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.no_of_attempts') }} <span class="red">*</span></label>
                        <div class="col-sm-9 col-lg-3 controls">
                            <select name="attempts" class="form-control chosen">
                                <option value="0" {{ ($quiz->attempts == "0") ? 'selected' : '' }}>{{ trans('admin/assessment.no_attempt_limit') }}</option>
                                @for ($i = 1; $i <=12; $i++)
                                    <option value="{{ $i }}" {{ ($quiz->attempts == "$i" ) ? 'selected' : '' }}>{{ $i }}</option>
                                @endfor
                            </select>
                            {!! $errors->first('attempts', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>   
                    <div class="form-group">
                        <label for="shuffle_questions" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.shuffle_questions') }}</label>
                        <div class="col-sm-9 col-lg-3 controls">
                            <input type="checkbox" name="shuffle_questions" @if($quiz->shuffle_questions) checked @endif></input>
                            {!! $errors->first("shuffle_questions", '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="negative_mark" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.negative_mark') }}</label>
                        <div class="col-sm-9 col-lg-10 controls">
                            <div class="col-sm-6 col-lg-4 padding-left-0">
                                {{ trans('admin/assessment.for_attempted_question') }}
                            </div>
                            <div class="col-sm-6 col-lg-8">
                                <input type="text" name="negative_mark_attempted_question"  value="{{ $quiz->attempt_neg_mark }}" style="width: 100px; border:1px solid #aaaaaa;margin-bottom:5px;"><span> % </span>
                            </div>
                            <div class="col-sm-6 col-lg-4 padding-left-0">
                                {{ trans('admin/assessment.for_unattempted_question') }}
                            </div>
                            <div class="col-sm-6 col-lg-8">
                                <input type="text" name="negative_mark_un_attempted_question" value="{{ $quiz->un_attempt_neg_mark }}" style="width: 100px; border:1px solid #aaaaaa;margin-bottom:5px;"><span> % </span>
                            </div>
                            <span class="help-inline" id="filetypehint">{{ trans('admin/assessment.note_negative') }}</span></br>
                                {!! $errors->first('negative_mark_attempted_question', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                {!! $errors->first('negative_mark_un_attempted_question', '<span class="help-inline" style="color:#f00">:message</span>') !!}&nbsp;&nbsp;&nbsp;&nbsp;
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="enable-sections" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.enable_sections') }}</label>
                        <div class="col-sm-9 col-lg-3 controls">
                            <?php
                                if(Input::old("enable_sections"))
                                    $e_s = Input::old("enable_sections") === "TRUE" ? "checked" : "";
                                else
                                    $e_s = $quiz->is_sections_enabled ? "checked" : "";
                            ?>
                            <input id="enable_section" type="checkbox" name="enable_sections" value="TRUE"  {{$e_s}}  {{!empty($quiz->questions)? "disabled" : ""}} style="margin-top:5%;">
                            {!! $errors->first('enable-sections', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                        <?php $checked = ''; $timed_sections = 'disabled' ?>
                        @if( (isset($quiz->is_sections_enabled) && $quiz->is_sections_enabled) && empty($quiz->questions))
                            <?php $timed_sections = '';?>
                        @endif
                        @if((isset($quiz->is_timed_sections) && $quiz->is_timed_sections) || Input::old('timed_sections') == 'on')
                            <?php $checked = 'checked';?>
                        @endif
                        @if(Input::old('enable_sections') == 'TRUE') 
                            <?php $timed_sections = '';?>
                        @endif                     
                        <div class="col-sm-5 col-lg-5 controls">
                            <div class="input-group">
                                <input {{ $timed_sections }} id="timed_sections" value="TRUE" type="checkbox" name="timed_sections" {{ $checked }} </input> {{ trans('admin/assessment.timed_sections')}}
                                <p>{!! trans('admin/assessment.note_timed_sections') !!}</p>
                            </div>
                        </div>
                    </div>
                     <div class="form-group">
                        <div class="row">
                            <div class="col-sm-6 col-md-5 col-lg-5">
                                <label for="question_per_page" class="col-sm-4 col-md-4 col-lg-5 control-label">{{ trans('admin/assessment.cut_off') }} </label>
                                <div class="col-sm-6 col-lg-6 col-md-6 controls">
                                    <?php
                                        $cut_off_format = Input::old('cut_off_format', $quiz->cut_off_format);
                                        $readonly = $cut_off_readonly ? 'disabled = "disabled"' : '';
                                    ?>
                                    <input {{ $readonly }} id="mark" type="radio" name="cut_off_format" value="{{QCFT::MARK}}" {{ $cut_off_format == QCFT::MARK ? 'checked' : ''}}> {{trans('admin/assessment.mark')}}
                                    <input {{ $readonly }} id="percentage" type="radio" name="cut_off_format" value="{{QCFT::PERCENTAGE}}" {{ $cut_off_format == QCFT::PERCENTAGE ? 'checked' : ''}}> {{trans('admin/assessment.percentage')}}
                                    <p>{!! $errors->first('cut_off_format', '<span class="help-inline" style="color:#f00">:message</span>') !!}</p>
                                    <?php
                                        $cut_off = Input::old('cut_off', $quiz->cut_off);
                                    ?>
                                    <input id="cut_off" type="text" name="cut_off" class="form-control" value="{{$cut_off}}" >
                                    {!! $errors->first('cut_off', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-7 col-lg-7">
                                <label for="enable-sections" class="col-sm-3 col-md-3 col-lg-3 control-label">{{ trans('admin/assessment.pass_criteria') }}</label>
                                 <div class="col-sm-9 col-md-9 col-lg-9  controls" id='box_viewer'>
                                    <input type="radio" name="pass_criteria" value='QUIZ_ONLY' 
                                            @if(Input::old("pass_criteria"))
                                                {{Input::old("pass_criteria") == "QUIZ_ONLY" ? "checked" : ""}}
                                            @else
                                                {{$quiz->pass_criteria == 'QUIZ_ONLY' ? "checked" : ""}}
                                            @endif
                                    > {{ trans('admin/assessment.quiz_cutoff') }} &nbsp
                                    <input type="radio" name="pass_criteria" value='QUIZ_AND_SECTIONS'  
                                            @if(Input::old("pass_criteria"))
                                                {{Input::old("pass_criteria") === "QUIZ_AND_SECTIONS" ? "checked" : ""}}
                                            @else
                                                {{$quiz->pass_criteria === 'QUIZ_AND_SECTIONS' ? "checked" : ""}}
                                            @endif
                                    > <span>(Quiz + Sections ){{ trans('admin/assessment.cut_off') }}</span>
                                    {!! $errors->first('pass_criteria', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="{{ trans('admin/assessment.reference_cut_offs') }}" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.reference_cut_offs') }}</label>
                        <div class="col-sm-9 col-lg-6 controls">
                            <textarea name="reference_cut_off" class="form-control ckeditor">
                                @if(isset($quiz->reference_cut_off))
                                    {!! $quiz->reference_cut_off !!}
                                @endif
                            </textarea>
                            {!! $errors->first('reference_cut_off', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @if(!empty(Input::old('editor_images')))
    @foreach(Input::old('editor_images') as $image)
        <input type="hidden" name="editor_images[]" value={{ $image }}>
    @endforeach
    @endif
    <div class="form-group last">
        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
           <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
           <a href="{{URL::to('/cp/assessment/list-quiz')}}?start={{$start}}&limit={{$limit}}&search={{$search}}&order_by={{$order_by}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
        </div>
    </div>
</form>
<script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
<script type="text/javascript">
    @if(isset($quiz->is_timed_sections) && $quiz->is_timed_sections && !empty($quiz->duration) && $quiz->duration > 0 && !empty($quiz->questions))
        var minTime = {{ $quiz->duration }};
        var maxTime = Math.floor(minTime/60)+':'+minTime%60;
    @endif
    $(function(){

        var durationTotal = 0;
        var duration = $('.input-duration').val().split(':');
        durationTotal = parseInt(duration[0]*60)+parseInt(duration[1]);
        $('.input-duration').timepicker({
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false,
            defaultTime: false
        })
        .on('changeTime.timepicker', function(e) {
            currentTime = e.time.hours * 60 + e.time.minutes;
            if(typeof minTime !=='undefined') {                    
                if(currentTime < minTime) {
                    $(this).timepicker('setTime', maxTime);
                } 
            }
            durationTotal = currentTime;
            showHideTimedSection();
        });
        $('input.tags').tagsInput({
            width: "auto"
        });
        $('#start_time_reset').click(function(e) {
            e.preventDefault();
            $('input[name=start_date]').val('');
            $('input[name=start_time]').val('0:00');
        });
        $('#end_time_reset').click(function(e) {
            e.preventDefault();
            $('input[name=end_date]').val('');
            $('input[name=end_time]').val('0:00');
        });
        $('#duration_reset').click(function(e) {
            e.preventDefault();
            $('input[name=duration]').val('0:00');
            durationTotal = 0;
            showHideTimedSection(); 
        });

        $("input[name=practice_quiz]").change(function(){
            var reviewOptions = $("input[name=review_the_attempt], input[name=review_whether_correct], .review");
            var mandatoryLabels = $("label[for=end_date] span.red");
            var mandatoryLabelsDuration = $("label[for=duration] span.red");
            if($(this).prop("checked")) {
                if ($("input[name=score_display]").prop("checked") == true) {
                    reviewOptions.prop("checked", true).not(".review");    
                }
                mandatoryLabels.css({ display : "none" });
                mandatoryLabelsDuration.css({ display : "none" });
            }
            else
            {
                reviewOptions.prop("checked", false);
                mandatoryLabels.css({ display : "inline" });
                mandatoryLabelsDuration.css({ display : "inline" });
            }
        });

        $("input[name=review_the_attempt]").change(function(){
            if(!$(this).prop("checked"))
                $("input[name=review_whether_correct], .review").prop("checked", false);
        });

        $("input[name=review_whether_correct]").change(function(){
            if($(this).prop("checked"))
                $("input[name=review_the_attempt]").prop({ checked : true });
            else
                $(".review").prop("checked", false);
        });

        $(".review").change(function(){
            $("input[name=review_the_attempt], input[name=review_whether_correct]").prop({ checked : true });
        });

            $('#enable_section').change(function(){
                if($(this).prop("checked")){
                    $("input[name=pass_criteria]").eq(1).prop({disabled: false});
                }else{
                    if($("input[name=cut_off]").val !=""){
                        $("input[name=pass_criteria]").eq(1).prop({checked : false }).prop({disabled: true});
                        $("input[name=pass_criteria]").eq(0).prop({checked : true });
                    }
                }
                showHideTimedSection();
            }); 

            var timedSection = $('#timed_sections');
            showHideTimedSection = function(){
                if(durationTotal && $('#enable_section').prop('checked')) {
                    timedSection.attr('disabled', false);
                } else {
                    timedSection.attr('checked', false);
                    timedSection.attr('disabled', true);   
                }                
            }

            $("input[name=cut_off]").on('keypress', function(event){
                    var char = String.fromCharCode(event.which)
                    var $maxlength = 3;
                    var isPercentageChecked = $('#percentage').prop('checked');
                    if (isPercentageChecked) {
                        $maxlength = 2;
                    }
                    if ( !char.match(/^[0-9]*$/) || ($("input[name=cut_off]").val().length > $maxlength)){
                        event.preventDefault();
                        return false;
                    }
                    
            });
            $("input[name=cut_off]").keyup(function(){
                if($(this).val() != ''){
                    // $("input[name=pass_criteria]").eq(0).prop({disabled: false}).prop({ checked : true });
                    if($("input[name=pass_criteria]").eq(1).prop('checked')){
                        $("input[name=pass_criteria]").eq(0).prop({disabled: false});
                    }else{
                        $("input[name=pass_criteria]").eq(0).prop({disabled: false}).prop({ checked :
                        true });
                    }
                }else if($(this).val() == ''){
                    $("input[name=pass_criteria]").eq(0).prop({checked : false }).prop({disabled: "disabled"});
                }
            });
            if (!$('#enable_section').prop("checked")) {
                $("input[name=pass_criteria]").eq(1).prop({disabled: true});
            } else {
                $("input[name=pass_criteria]").eq(1).prop({disabled: false});
            }
            // $("input[name=practice_quiz]").trigger('change'); karhikeyan : No need to reset the review option here 
    });

    // based on score option enabling and disabling other options
    $("input[name=score_display]").click(function(){
        if($(this).val() == "off") {
            $("input[value=off]").prop({"checked" : true});
            $("input[name=review_the_attempt]").prop({ checked : false }).prop({disabled: "disabled"});
            $("input[name=review_whether_correct]").prop({ checked : false }).prop({disabled: "disabled"});
            $("input[name=review_marks]").prop({ checked : false }).prop({disabled: "disabled"});
            $("input[name=review_rationale]").prop({ checked : false }).prop({disabled: "disabled"});
            $("input[name=review_correct_answer]").prop({ checked : false }).prop({disabled: "disabled"});
        }
         if($(this).val() == "on") {
            $("input[name=review_the_attempt]").prop({disabled: false });
            $("input[name=review_whether_correct]").prop({disabled: false });
            $("input[name=review_marks]").prop({disabled: false });
            $("input[name=review_rationale]").prop({disabled: false });
            $("input[name=review_correct_answer]").prop({disabled: false });
         }
    });

    CKEDITOR.replace( 'editquiz', {
        filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
    });
    CKEDITOR.config.disallowedContent = 'script; *[on*]';
    CKEDITOR.config.height = 150;
    CKEDITOR.replace('reference_cut_off');
    function toggleChevron(e) {
        $(e.target)
            .prev('.panel-heading')
            .find("i.indicator")
            .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
    }
    //setting default width as 100% for table
    CKEDITOR.on('dialogDefinition', function( ev ) {
        
          var diagName = ev.data.name;
          var diagDefn = ev.data.definition;

          if(diagName === 'table') { //if dialog name equal to table
            var infoTab = diagDefn.getContents('info');
            
            var width = infoTab.get('txtWidth');
            width['default'] = "100%";
            
            
          }
    });
    $('#accordion').on('hidden.bs.collapse', toggleChevron);
    $('#accordion').on('shown.bs.collapse', toggleChevron);

    function SetEnvironment(myRadio)
    {
       $("#environment_selected").val(myRadio.value);
    }    
    $(document).ready(function(){
      $('#advance-options').find('.help-inline').length > 1?$('.advance-toggle').trigger('click'):'';  
        @if(empty($quiz->questions))
            showHideTimedSection();  
        @endif
    });
</script>