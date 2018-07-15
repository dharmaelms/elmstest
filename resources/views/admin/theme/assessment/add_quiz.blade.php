@section('content')

    <style type="text/css">
        [data-toggle="collapse"] {
            cursor: pointer;
        }

    </style>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                </div>
                <div class="box-content">
                    <div class="form-group">
                        <div class="col-sm-offset-3 col-lg-offset-2 col-sm-9 col-lg-10 controls">
                            <label class="radio-inline">
                                <input type="radio" name="quiz_type" value="GENERAL">{{ trans('admin/assessment.general') }}  
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="quiz_type" value="QUESTION_GENERATOR">{{ trans('admin/assessment.question_generator') }}
                            </label> 
                        </div>
                        <!-- Evnironment setup -->
                        <div class="pull-right">
                            <?php 
                                if(!is_null(Input::old('environment_selected')) && Input::old('environment_selected') === 'is_production'){
                                    $isProduction = 'checked="checked"';
                                    $isBeta = '';

                                }else if(!is_null(Input::old('environment_selected')) && Input::old('environment_selected') === 'is_beta'){
                                    $isBeta = 'checked="checked"';
                                    $isProduction = '';
                                }else{
                                    $isProduction = 'checked="checked"';
                                    $isBeta = '';
                                }
                            ?>
                            <label class="radio-inline">{{ trans('admin/assessment.environment') }} </label>
                            <label class="radio-inline">
                                <input type="radio" name="environment" value="is_production" {{$isProduction}} onchange="SetEnvironment(this)" >{{ trans('admin/assessment.live_quiz') }}
                            </label>
                            <label class="radio-inline">
                                <input type="radio" name="environment" value="is_beta" {{$isBeta}} onchange="SetEnvironment(this)" > {{ trans('admin/assessment.test_quiz') }}
                            </label> 
                        </div>
                        <!-- environment setup ends here -->
                    </div>
                    <div style="margin-top:4%;"></div>
                    <form action="{{ URL::to("cp/assessment/add-quiz") }}" class="form-horizontal form-bordered form-row-stripped" method="post" accept-Charset="UTF-8" data-custom-id="GENERAL" id="form-general" style="display:none;">
                        <input type="hidden" id="environment_selected" name="environment_selected" value="{{Input::old('environment_selected')}}"> 
                       
                        <div class="form-group">
                            <label for="quiz_name" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.quiz_name') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="quiz_name" class="form-control" value="{{ Input::old('quiz_name') }}" >
                                {!! $errors->first('quiz_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="quiz_description" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.instructions') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="quiz_description" rows="5" id="addquiz" class="form-control ckeditor">{!! Input::old('quiz_description') !!}</textarea>
                                {!! $errors->first('quiz_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keywords_tags') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="keywords" class="form-control tags" value="{{ Input::old('keywords') }}">
                                {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>                        
                        <div class="form-group">
                            <label for="practice_quiz" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.mark_as_practice_quiz') }}</label>
                            <div class="col-sm-9 col-lg-3 controls">
                                <input type="checkbox" name="practice_quiz" @if(Input::old('practice_quiz') == 'on') checked @endif></input>
                                {!! $errors->first('practice_quiz', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>                        
                        <div class="form-group">
                            <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.schedule_date') }} <span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="start_date" class="form-control general-input-start-date" style="cursor: pointer" value="{{ Input::old('start_date', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y')) }}" readonly="readonly">
                                </div>
                                {!! $errors->first('start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="start_time" onclick="$(this).prev().click()" class="form-control general-input-start-time" style="cursor: pointer" value="{{ Input::old('start_time', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'H:i')) }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="start_time_reset" class="btn" ><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('start_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.expiry_date') }}&nbsp;<span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="end_date" class="form-control general-input-end-date" style="cursor: pointer" value="{{ Input::old('end_date') }}" readonly="readonly">
                                </div>
                                {!! $errors->first('end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="end_time" onclick="$(this).prev().click()" class="form-control general-input-end-time" style="cursor: pointer" value="{{ Input::old('end_time', '0:00') }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="end_time_reset" class="btn" ><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('end_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.duration') }}&nbsp;<span class="red">*</span>
                            <div><span class="help-inline">(hh:mm)&nbsp;&nbsp;</span></div>    
                            </label>
                            <div class="col-sm-5 col-lg-3 controls">
                                <div class="input-group">
                                    <a class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></a>
                                    <input type="text" name="duration" class="form-control input-duration" onclick="$(this).prev().click()" style="cursor: pointer" value="{{ Input::old('duration', '0:00') }}" readonly="readonly">
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
                            <div class="col-sm-5 col-lg-3 controls score_display">
                                <div class="input-group">
                                    <label class="radio-inline">
                                        <input type="radio" value="on" id="score_on" name="score_display">{{ trans('admin/assessment.show') }}
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" value="off" id="score_off" name="score_display">{{ trans('admin/assessment.hide') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                        <!-- score options ends here -->

                        <div class="form-group">
                            <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.review_options') }}</label>
                            <div class="col-sm-5 col-lg-3 controls">
                                <div class="input-group">
                                    <input type="checkbox" name="review_the_attempt" @if(Input::old('review_the_attempt') == 'on') checked @endif @if(Input::old('practice_quiz') == 'on')  @endif></input> {{ trans('admin/assessment.the_attempt') }}
                                    <br>
                                    <input type="checkbox" name="review_whether_correct" @if(Input::old('review_whether_correct') == 'on') checked @endif @if(Input::old('practice_quiz') == 'on')  @endif></input> {{ trans('admin/assessment.whether_correct') }}
                                    <br>
                                    <input class="review" type="checkbox" name="review_marks" @if(Input::old('review_marks') == 'on') checked @endif></input> {{ trans('admin/assessment.marks') }}
                                    <br>
                                    <input class="review" type="checkbox" name="review_rationale" @if(Input::old('review_rationale') == 'on') checked @endif></input> {{ trans('admin/assessment.rationale') }}
                                    <br>
                                    <input class="review" type="checkbox" name="review_correct_answer" @if(Input::old('review_correct_answer') == 'on') checked @endif></input> {{ trans('admin/assessment.correct_answer') }}
                                </div>
                            </div>
                        </div>
                        <div class="form-group panel-group" id="accordion">
                            <div class="panel panel-default">
                                <div class="panel-heading advance_options" data-toggle="collapse" data-parent="#accordion" href="#advance-options">
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
                                                    <option value="0" {{ (Input::old('attempts') == "0") ? 'selected' : '' }}>{{ trans('admin/assessment.no_attempt_limit') }}</option>
                                                    @for ($i = 1; $i <= 12; $i++)
                                                        <option value="{{ $i }}" {{ (Input::old("attempts") == "$i" ) ? "selected" : (($i === 1)? "selected" : "") }}>{{ $i }}</option>
                                                    @endfor
                                                </select>
                                                {!! $errors->first('attempts', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="shuffle_questions" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.shuffle_questions') }}</label>
                                            <div class="col-sm-9 col-lg-3 controls">
                                                <input type="checkbox" name="shuffle_questions" @if(Input::old('shuffle_questions') == 'on') checked @endif></input>
                                                {!! $errors->first('shuffle_questions', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="negative_mark" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.negative_mark') }}</label>
                                            <div class="col-sm-9 col-lg-10 controls">
                                                <div class="col-sm-6 col-lg-4 padding-left-0">
                                                    {{ trans('admin/assessment.for_attempted_question') }}
                                                </div>
                                                <div class="col-sm-6 col-lg-8">
                                                    <input type="text" name="negative_mark_attempted_question" value="{{ Input::old('negative_mark_attempted_question') }}" style="width: 100px; border:1px solid #aaaaaa;margin-bottom:5px;">
                                                    <span> % </span>
                                                </div>
                                                <div class="col-sm-6 col-lg-4 padding-left-0">
                                                    {{ trans('admin/assessment.for_unattempted_question') }}
                                                </div>
                                                <div class="col-sm-6 col-lg-8"><input type="text" name="negative_mark_un_attempted_question" value="{{ Input::old('negative_mark_un_attempted_question') }}" style="width: 100px; border:1px solid #aaaaaa;margin-bottom:5px;">
                                                        <span> % </span>
                                                </div>
                                                <span class="help-inline" id="filetypehint">{{ trans('admin/assessment.note_negative') }}</span></br>
                                                    {!! $errors->first('negative_mark_un_attempted_question', '<span class="help-inline" style="color:#f00">:message</span>') !!}&nbsp;&nbsp;&nbsp;&nbsp;
                                                    {!! $errors->first('negative_mark_attempted_question', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="enable-sections" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.enable_sections') }}</label>
                                            <div class="col-sm-9 col-lg-3 controls">
                                                <input id="enable_section" type="checkbox" name="enable_sections" value="TRUE" {{ (Input::old("enable_sections") === "TRUE")? "checked" : "" }} style="margin-top:5%;">
                                                {!! $errors->first('enable_sections', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                            </div>
                                            <?php $timed_sections = 'disabled';?>
                                            @if(Input::old('enable_sections') == 'TRUE' && Input::old('duration') !='0:00' ) 
                                                <?php $timed_sections = ''?>
                                            @endif
                                            <div class="col-sm-5 col-lg-5 controls">
                                                <div class="input-group">
                                                    <input  {{ $timed_sections }} type="checkbox" id="timed_sections" name="timed_sections" value="TRUE" @if(Input::old('timed_sections') == 'TRUE') checked @endif</input> {{ trans('admin/assessment.timed_sections')}}
                                                    <p>{!! trans('admin/assessment.note_timed_sections') !!}</p>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <div class="row">
                                                <div class="col-sm-6 col-md-5 col-lg-5">
                                                    <?php $cut_off_format = Input::old('cut_off_format');?>
                                                    <label for="question_per_page" class="col-sm-4 col-md-4 col-lg-5 control-label">{{ trans('admin/assessment.cut_off') }}</label>
                                                    <div class="col-sm-6 col-lg-6 col-md-6 controls">
                                                        <input type="radio" name="cut_off_format" value="{{ QCFT::MARK }}" {{ $cut_off_format == QCFT::MARK ? 'checked' : ''}} id="mark"> {{trans('admin/assessment.mark')}}
                                                        <input type="radio" name="cut_off_format" value="{{ QCFT::PERCENTAGE }}" {{ $cut_off_format == QCFT::PERCENTAGE ? 'checked' : ''}} id="percentage"> {{trans('admin/assessment.percentage')}}
                                                        <p>{!! $errors->first('cut_off_format', '<span class="help-inline" style="color:#f00">:message</span>') !!}</p>
                                                        <input type="text" name="cut_off" class="form-control" value="{{ Input::old('cut_off') }}" >
                                                        {!! $errors->first('cut_off', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                                    </div>
                                                </div>
                                                <div class="col-sm-6 col-md-6 col-lg-6">
                                                    <label for="enable-sections" class="col-sm-3 col-md-3 col-lg-3 control-label">{{ trans('admin/assessment.pass_criteria') }}</label>
                                                     <div class="col-sm-9 col-md-9 col-lg-9  controls" id='box_viewer'>
                                                        <input type="radio" name="pass_criteria" value='QUIZ_ONLY' {{ (Input::old("pass_criteria") == "QUIZ_ONLY")? "checked" : "" }}> {{ trans('admin/assessment.quiz_cutoff') }} &nbsp
                                                        <input type="radio" name="pass_criteria" value='QUIZ_AND_SECTIONS'  {{ (Input::old("pass_criteria") == "QUIZ_AND_SECTIONS")? "checked" : "" }}> <span>(Quiz + Sections ){{ trans('admin/assessment.cut_off') }}</span>
                                                        {!! $errors->first('pass_criteria', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="{{ trans('admin/assessment.reference_cut_offs') }}" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.reference_cut_offs') }}</label>
                                            <div class="col-sm-9 col-lg-6 controls">
                                                <textarea name="reference_cut_off" class="form-control ckeditor">
                                                   {!! Input::old('reference_cut_off') !!}
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
                               <a href="{{URL::to('/cp/assessment/list-quiz')}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
                            </div>
                        </div>
                    </form>
                    <form action="{{ URL::to("cp/assessment/add-question-generator") }}" method="post" accept-Charset="UTF-8" id="form-question-generator" data-custom-id="QUESTION_GENERATOR" class="form-horizontal form-bordered form-row-stripped" style="display:none;">
                        <input type="hidden" id="r-q-g-is_production" name="r-q-g-is_production"> 
                        <div class="form-group">
                            <label for="r-q-g-name" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.name') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="r-q-g-name" class="form-control" value="{{ Input::old('r-q-g-name') }}" >
                                {!! $errors->first('r-q-g-name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="r-q-g-instructions" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.instructions') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="r-q-g-instructions" id="r-q-g-instructions" rows="5" class="form-control ckeditor">{!! Input::old('r-q-g-instructions') !!}</textarea>
                                {!! $errors->first('r-q-g-instructions', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="r-q-g-keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keywords_tags') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="r-q-g-keywords" class="form-control tags" value="{{ Input::old('r-q-g-keywords') }}">
                                {!! $errors->first('r-q-g-keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="row">
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <label for="r-q-g-total-question-limit" class="col-sm-5 col-md-5 col-lg-5 control-label">{{ trans('admin/assessment.total_question_limit') }}<span class="red">&nbsp;*</span></label>
                                    <div class="col-sm-6 col-md-6 col-lg-6 controls">
                                        <input type="text" name="r-q-g-total-question-limit" class="form-control" value="{{ Input::old('r-q-g-total-question-limit') }}" minlength="1" maxlength="4">
                                        {!! $errors->first('r-q-g-total-question-limit', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="col-sm-5 col-md-5 col-lg-5">
                                    <label for="r-q-g-enable-sections" class="col-sm-5 col-md-5 col-lg-5 control-label">{{ trans('admin/assessment.enable_sections') }}</label>
                                    <div class="col-sm-6 col-md-6 col-lg-6 controls">
                                        <input type="checkbox" name="r-q-g-enable-sections" value="TRUE" {{ (Input::old("r-q-g-enable-sections") === "TRUE")? "checked" : "" }} style="margin-top:5%;">
                                        {!! $errors->first('r-q-g-enable-sections', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="r-q-g-display-start-date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.display_start_date') }}</label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="r-q-g-display-start-date" class="form-control qg-input-start-date" style="cursor: pointer" value="{{ Input::old('r-q-g-display-start-date', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y')) }}" readonly="readonly">
                                </div>
                                {!! $errors->first('r-q-g-display-start-date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="r-q-g-display-start-time" onclick="$(this).prev().click()" class="form-control qg-input-start-time" style="cursor: pointer" value="{{ Input::old('r-q-g-display-start-time', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'H:i')) }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="r-q-g-display-start-time-reset" class="btn" ><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('r-q-g-display-start-time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="r-q-g-display-end-date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.display_end_date') }}</label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="r-q-g-display-end-date" class="form-control qg-input-end-date" style="cursor: pointer" value="{{ Input::old('r-q-g-display-end-date') }}" readonly="readonly">
                                </div>
                                {!! $errors->first('r-q-g-display-end-date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">   
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="r-q-g-display-end-time" onclick="$(this).prev().click()" class="form-control qg-input-end-time" style="cursor: pointer" value="{{ Input::old('r-q-g-display-end-time', '0:00') }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="r-q-g-display-end-time-reset" class="btn" ><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('r-q-g-display-end-time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
                               <a href="{{URL::to('/cp/assessment/list-quiz')}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">
        var $configPath = "{{ URL::asset('admin/assets/ckeditor/config.js')}}",  
        chost = "{{ config('app.url') }}"; 

        $(function(){
            var durationTotal = 0;
            var duration = $('.input-duration').val().split(':');
            durationTotal = parseInt(duration[0]*60)+parseInt(duration[1]);
            var activeQuizType = "{{ (Session::has("type") && (Session::get("type") === "QUESTION_GENERATOR"))? "QUESTION_GENERATOR" : "GENERAL" }}";

            $("input[name=quiz_type][value="+activeQuizType+"]").prop({
                checked : true
            });

            // Default option for score is set to "on"
            $("#score_on").prop({ checked : true });

            $("form").each(function(){
                if($(this).data("custom-id") === activeQuizType)
                    $(this).slideDown({
                        duration : 1200
                    });
            });

            $("input[name=quiz_type]").change(function(){
                var quizForm = $("#form-general");
                var randomQuestionGeneratorForm = $("#form-question-generator");
                if($(this).val() === "GENERAL")
                {
                    randomQuestionGeneratorForm.slideUp({
                        duration : 750,
                        complete : function(){
                            quizForm.slideDown({
                                duration : 750
                            });
                        }
                    });
                }
                else if($(this).val() === "QUESTION_GENERATOR")
                {
                    quizForm.slideUp({
                        duration : 750,
                        complete : function(){
                            randomQuestionGeneratorForm.slideDown({
                                duration : 750
                            });
                        }
                    });
                }
            });

            $('.general-input-start-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d'
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('general-input-start-time');
                checkDateAndTime('general-input-start-date', 'general-input-start-time', selectedTime);
            });
            $('.general-input-start-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('general-input-start-date', 'general-input-start-time', selectedTime);
            });

            $('.general-input-end-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d'
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('general-input-end-time');
                checkDateAndTime('general-input-end-date', 'general-input-end-time', selectedTime);
            });
            $('.general-input-end-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('general-input-end-date', 'general-input-end-time', selectedTime);
            });

            $('.qg-input-start-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d'
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('qg-input-start-time');
                checkDateAndTime('qg-input-start-date', 'qg-input-start-time', selectedTime);
            });
            $('.qg-input-start-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('qg-input-start-date', 'qg-input-start-time', selectedTime);
            });

            $('.qg-input-end-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d'
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('qg-input-end-time');
                checkDateAndTime('qg-input-end-date', 'qg-input-end-time', selectedTime);
            });
            $('.qg-input-end-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('qg-input-end-date', 'qg-input-end-time', selectedTime);
            });

            function selectTime(time_class_name) {
                var time = $('.'+ time_class_name).val().split(":");
                var hours = parseInt(time[0]);
                var minutes = parseInt(time[1]);
                return (hours * 60) + minutes;
            }
            function checkDateAndTime(date_class_name, time_class_name, selectedTime) {
                var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()).getTime();
                selectedDate = $('.'+ date_class_name).datepicker('getDate').getTime();
                if (today == selectedDate) {
                    now = new Date();
                    currentTime = (now.getHours() * 60) + now.getMinutes();
                    if (selectedTime < currentTime) {
                        $('.'+ time_class_name).timepicker('setTime', Math.floor(currentTime/60)+':'+currentTime%60);
                    }
                }
            }

            $('.input-duration').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e){
                durationTotal = e.time.hours * 60 + e.time.minutes;
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
            $('#r-q-g-display-start-time-reset').click(function(e) {
                e.preventDefault();
                $('input[name=r-q-g-display-start-date]').val('');
                $('input[name=r-q-g-display-start-time]').val('0:00');
            });
            $('#r-q-g-display-end-time-reset').click(function(e) {
                e.preventDefault();
                $('input[name=r-q-g-display-end-date]').val('');
                $('input[name=r-q-g-display-end-time]').val('0:00');
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
                var attempts = $("select[name=attempts]");
                if($(this).prop("checked")) {
                    if ($("input[name=score_display]").prop("checked") == true) {
                        reviewOptions.prop("checked", true).not(".review");    
                    }
                    attempts.find("option[value=0]").prop({ selected : true });
                    mandatoryLabels.css({ display : "none" });
                    mandatoryLabelsDuration.css({ display : "none" });
                }
                else
                {
                    attempts.find("option[value=1]").prop({ selected : true });
                    reviewOptions.prop("checked", false);
                    mandatoryLabels.css({ display : "inline" });
                    mandatoryLabelsDuration.css({ display : "inline" });
                }
            });

            
            $("input[name=review_the_attempt]").change(function(){
                if(!$(this).prop("checked"))
                {
                    if($("input[name=practice_quiz]").prop("checked"))
                        $(this).prop({ checked : true });
                    else
                        $("input[name=review_whether_correct], .review").prop("checked", false);
                }
            });

            $("input[name=review_whether_correct]").change(function(){
                if($(this).prop("checked"))
                    $("input[name=review_the_attempt]").prop({ checked : true });
                else
                {
                    if($("input[name=practice_quiz]").prop("checked"))
                        $(this).prop({checked : true});
                    else
                        $(".review").prop("checked", false);
                }
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
            $("input[name=r-q-g-total-question-limit]").on('keypress', function(event){
                var charCode = (event.which) ? event.which : event.keyCode;
                if (charCode != 46 && charCode > 31
                && (charCode < 48 || charCode > 57))
                    return false;
                if(this.value.length > 4)
                    this.value = this.value.slice(0,3); 
                return true;
            });
            $("input[name=cut_off]").keyup(function(){
                if($(this).val() != ''){
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

            $("input[name=practice_quiz]").trigger('change');
            $("input[name=pass_criteria]").eq(1).prop({disabled: true});
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
        
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;
        CKEDITOR.replace('reference_cut_off');
        function toggleChevron(e) {
            $(e.target)
                .prev('.panel-heading')
                .find("i.indicator")
                .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
        }
        $('#accordion').on('hidden.bs.collapse', toggleChevron);
        $('#accordion').on('shown.bs.collapse', toggleChevron);
        //setting default width as 100% for table

        function SetEnvironment(myRadio)
        {
           $("#environment_selected").val(myRadio.value);
           $("#r-q-g-is_production").val(myRadio.value); 
        }  
        $(document).ready(function(){
            $('#advance-options').find('.help-inline').length > 1?$('.advance_options').trigger('click'):'';
            showHideTimedSection();  
        });
    </script>

@stop