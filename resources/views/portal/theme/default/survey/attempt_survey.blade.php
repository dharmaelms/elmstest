@section('content')
<style>
    .form-horizontal .radio > span {
        margin-top: -3px !important;
    }
    .checker, .radio{
        margin-right: 10px !important;
        cursor: pointer !important;
    }
    .margin-15{
        margin: 15px !important;
    }
    .survey-question {
        background-color: #f9f9f9;
        border: 1px solid #e0e0e0;
        padding:10px;
        margin-bottom:20px;
    }
    .textarea {
        background-color: #f9f9f9 !important;
    }
    .q-left{ float: left; }
    .q-right { margin-top: 2px;margin-bottom: 15px; display: block; }
</style>
<?php
use App\Enums\Survey\SurveyType; 
$survey_id = $survey->id;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title padding-10">
                <h3 class="page-title-small text-capitalize margin-top-0">
                    {{$survey->survey_title}}
                    @if(!empty($survey->description))
                        <a class="btn btn-circle show-tooltip ajax" title=" {{ $survey->description }}"><i class="fa fa-question-circle"></i></a>
                    @endif    
                </h3>
            </div>
            @if($survey_questions->count() == 0)
                {{ trans('survey.no_survey_questions') }}
            @endif
            @if ( Session::get('error'))
               <div class="alert alert-danger">
                   <button class="close" data-dismiss="alert">×</button>
                   {{ Session::get('error') }}
               </div>
            @endif
            <div class="box-content">
                <form action="{{ URL::to('survey/submit-survey/'.$packet_slug.'/'.$survey_id) }}" class="form-horizontal form-bordered form-row-stripped" method="POST" accept-charset="UTF-8" data-custom-id="GENERAL" id="form-general">
                <?php $ques_num = 0; ?>
                @foreach($survey_questions as $survey_question)
                    <?php
                        $ques_num = $ques_num+1;
                        $survey_question_id = $survey_question->id;
                        $survey_question_type = $survey_question->type;
                        $survey_question_title = $survey_question->title;
                        $survey_question_choices = $survey_question->choices;
                        $survey_question_choices_others = $survey_question->is_others;
                        $survey_question_mandatory = $survey_question->is_mandatory;
                    ?>
                    <!-- code for checkbox type question -->
                    @if($survey_question_type == SurveyType::MULTIPLE_ANSWER)
                        <div class="survey-question">
                            @if($survey_question_mandatory)
                                <label class="bold"><span class="red">*</span>{{ $ques_num.') '. $survey_question_title }}</label>
                            @else
                                <label class="bold">{{ $ques_num.') '. $survey_question_title }}</label>  
                            @endif
                            <div class="form-group">
                                <div class="answer-section padding-10">
                                    @if(!empty($survey_question_choices))
                                        @foreach($survey_question_choices as $answer)
                                            <?php
                                            $submitted_inputs = Input::old('checkbox_'.$survey_question_id);
                                            ?>
                                            <div class="row">
                                                <div class="col-md-1" style="width:2% !important;">
                                                    <input type="checkbox" class="checkbox" name="checkbox_{{$survey_question_id}}[]" value="{{ $answer }}"
                                                    @if(!empty($submitted_inputs))
                                                        @foreach ($submitted_inputs as $value)
                                                            @if($value == $answer)
                                                                checked
                                                            @endif
                                                        @endforeach
                                                    @endif
                                                    >
                                                </div>
                                                <div class="col-md-11">
                                                    <label>{{ $answer }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if($survey_question_choices_others)
                                        <div class="margin-15">
                                            <label>{{ trans('survey.others') }}</label>
                                            <textarea placeholder="{{ trans('survey.textarea_placeholder') }}" type="textarea" class="form-control textarea" name="checkbox_textarea_{{$survey_question_id}}" id="checkbox_textarea_{{$survey_question_id}}">{{ Input::old('checkbox_textarea_'.$survey_question_id) }}</textarea>
                                        </div>
                                    @endif
                                </div>
                                {!! $errors->first('checkbox_'.$survey_question_id, '<span class="help-inline" style="color:#f00">:message</span>') !!}         
                            </div>
                        </div>
                    @endif
                    <!-- code for checkbox type question ends-->

                    <!-- code for radio type questions -->
                    @if($survey_question_type == SurveyType::SINGLE_ANSWER) 
                        <div class="survey-question">
                            @if($survey_question_mandatory)
                                <label><span class="red">*</span>{{ $ques_num.') '. $survey_question_title }}</label>
                            @else
                                <label>{{ $ques_num.') '. $survey_question_title }}</label>  
                            @endif
                            <div class="form-group">
                                <div class="answer-section padding-10">
                                    @if(!empty($survey_question_choices))
                                        @foreach($survey_question_choices as $answer)
                                            <?php
                                            $submitted_input = array_get(Input::old('radio_'.$survey_question_id), 0); 
                                            ?>
                                            <div class="row">
                                                <div class="col-md-12">
                                                    <label class="q-left">
                                                        <input type="radio" class="form-control radio" name="radio_{{$survey_question_id}}[]" value="{{ $answer }}" style="margin-left:unset;" @if($submitted_input == $answer) checked @endif>
                                                    </label>
                                                    <div class="q-right" style="font-weight:normal;">
                                                    {{ $answer }}
                                                    </div>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if($survey_question_choices_others)
                                        <div class="margin-15">
                                            <label>{{ trans('survey.others') }}</label>
                                            <textarea placeholder="{{ trans('survey.textarea_placeholder') }}" type="textarea" class="form-control textarea" name="radio_textarea_{{$survey_question_id}}" id="radio_textarea_{{$survey_question_id}}">{{ Input::old('radio_textarea_'.$survey_question_id) }}</textarea>
                                        </div>
                                    @endif    
                                </div>
                                {!! $errors->first('radio_'.$survey_question_id, '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                    @endif
                    <!-- code for radio type questions ends-->

                    <!-- code for radio type questions -->
                    @if($survey_question_type == SurveyType::RANGE)   
                        <div class="survey-question">
                            @if($survey_question_mandatory)
                                <label><span class="red">*</span>{{ $ques_num.') '. $survey_question_title }}</label>
                            @else
                                <label>{{ $ques_num.') '. $survey_question_title }}</label>  
                            @endif
                            <div class="form-group">
                                <div class="answer-section padding-10">
                                    @if(!empty($survey_question_choices))
                                        @foreach($survey_question_choices as $answer)
                                            <?php
                                            $submitted_input = array_get(Input::old('rate_'.$survey_question_id), 0);
                                            ?>
                                            <input type="radio" class="form-control radio" name="rate_{{$survey_question_id}}[]" value="{{ $answer }}" style="margin-left: unset !important;" 
                                            @if($submitted_input == $answer)
                                                checked
                                            @endif
                                            ><label>{{ $answer }}</label><br>
                                        @endforeach
                                    @endif
                                    @if($survey_question_choices_others)
                                        <div class="margin-15">
                                            <label>{{ trans('survey.others') }}</label>
                                            <textarea placeholder="{{ trans('survey.textarea_placeholder') }}" type="textarea" class="form-control textarea" name="rate_textarea_{{$survey_question_id}}" id="rate_textarea_{{$survey_question_id}}">{{ Input::old('rate_textarea_'.$survey_question_id) }}</textarea>
                                        </div>
                                    @endif    
                                </div>
                                {!! $errors->first('rate_'.$survey_question_id, '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                    @endif
                    <!-- code for radio type questions ends-->

                    <!-- code for descriptve type  question-->
                    @if($survey_question_type == SurveyType::DESCRIPTIVE)
                        <div class="survey-question">
                            @if($survey_question_mandatory)
                                <label><span class="red">*</span>{{ $ques_num.') '. $survey_question_title }}</label>
                            @else
                                <label>{{ $ques_num.') '. $survey_question_title }}</label>  
                            @endif
                            <div class="form-group">
                                <div class="answer-section margin-15 padding-10">
                                    <textarea type="text" placeholder="{{ trans('survey.textarea_placeholder') }}" class="form-control textarea" name="textarea_{{$survey_question_id}}" id="textarea_{{$survey_question_id}}">{{ Input::old('textarea_'.$survey_question_id) }}</textarea>
                                    {!! $errors->first('textarea_'.$survey_question_id, '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                @if(!$survey_questions->isEmpty())
                    <!-- code for descriptve type  question ends-->
                    <div class="form-group last" style="margin-left: 10px;">
                       <button type="submit" class="btn btn-success attempt_survey_save"><i class="fa fa-check font-20"></i> {{ trans('survey.submit') }}</button>
                       @if($packet_slug == "unattempted")
                            <a href="{{ URL::to('survey') }}" class="btn btn-danger"><i class="fa fa-times font-20"></i> {{ trans('survey.cancel') }}</a>
                        @else
                            <a href="{{ URL::to('program/packet/'.$packet_slug.'/element/'.$survey_id.'/survey') }}" class="btn btn-success">{{ trans('survey.cancel') }}</a>
                        @endif
                    </div> 
                @endif
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.attempt_survey_save').on('click', function(e){
        $('#form-general').submit();
    });
    /*Code to hide success message after 5seconds*/
    $('.alert-danger').delay(5000).fadeOut();

    $("textarea").on("keypress", function(e) {
        if (e.which === 32 && !this.value.length)
            e.preventDefault();
    });
</script>
@stop