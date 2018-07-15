@section('content')
<style>
    .form-horizontal .radio > span {
        margin-top: -3px !important;
    }
    .checker, .radio{
        margin-right: 10px !important;
        cursor: pointer !important;
    }
    .form-horizontal .radio {
        padding-top: 5px !important;
    }
    .checker {
        margin-top: 1px !important;
    }
    .margin-15{
        margin: 15px !important;
        border-bottom: 1px solid #e7e7e7;
    }
    .survey-question {
        background-color: #f9f9f9;
        border: 1px solid #e0e0e0;
        padding:10px;
        margin-bottom:20px;
    }
    .textarea {
        border-top: none !important;
        border-left: none !important;
        border-right: none !important;
        background-color: #f9f9f9 !important;
    }
</style>
<?php 
use App\Enums\Survey\SurveyType;
$survey_id = $survey->id;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title padding-10">
                @if($success)
                    <div class="alert alert-success" id="alert-success">
                        <button class="close" data-dismiss="alert">×</button>
                        {{ trans('survey.success') }}
                    </div>
                @endif
                <p class="page-title-small text-capitalize margin-top-0">
                    {{$survey->survey_title}}
                    @if(!empty($survey->description))
                        <a class="btn btn-circle show-tooltip ajax" title=" {{ $survey->description }}"><i class="fa fa-question-circle"></i></a>
                    @endif
                    @if($packet_slug == "unattempted")
                        <a href="{{ URL::to('survey') }}" style="padding: 8px;" class="btn btn-primary pull-right fa fa-angle-left">&nbsp;Back</a></button>
                    @else
                        <a href="{{ URL::to('program/packet/'.$packet_slug.'/element/'.$survey_id.'/survey') }}" style="padding: 8px;" class="btn btn-primary pull-right fa fa-angle-left">&nbsp;Back</a>
                    @endif
                </p>
            </div>
            <div class="box-content">
                <form action="{{ URL::to('survey/submit-survey/'.$survey_id) }}" class="form-horizontal form-bordered form-row-stripped" method="POST" accept-charset="UTF-8" data-custom-id="GENERAL" id="form-general">
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
                                                $submitted_answer =  array_get($survey_answer, 'checkbox_'.$survey_question_id, []);
                                                $answered = in_array($answer, $submitted_answer);
                                                ?>
                                            <div class="row">
                                                <div class="col-md-1" style="width:2% !important;">
                                                    <input type="checkbox" class="checkbox" name="checkbox_{{$survey_question_id}}[]" value="{{ $answer }}" disabled="disabled" @if($answered) checked @endif>
                                                </div>
                                                <div class="col-md-11">
                                                    <label>{{ $answer }}</label>
                                                </div>
                                            </div>
                                        @endforeach
                                    @endif
                                    @if($survey_question_choices_others)
                                        <div class="margin-15">
                                            <label>{{ trans('survey.others') }}:</label>
                                            <p>{{ array_get($survey_answer, 'checkbox_textarea_'.$survey_question_id) }}</p>
                                        </div>
                                    @endif
                                </div>
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
                                                $submitted_answer =  array_get($survey_answer, 'radio_'.$survey_question_id, []);
                                                $submitted_answer = array_get($submitted_answer, 0); 
                                            ?>
                                            <div class="row">
                                                <div class="col-md-1" style="width:2% !important;">
                                                    <input type="radio" name="radio_{{$survey_question_id}}[]" value="{{ $answer }}" style="margin-left:unset;" disabled="disabled"
                                                    @if((!empty($submitted_answer)) && ($submitted_answer == $answer))
                                                        checked
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
                                            <label>{{ trans('survey.others') }}:</label>
                                            <p>{{array_get($survey_answer, 'radio_textarea_'.$survey_question_id)}}</p>
                                        </div>
                                    @endif    
                                </div>
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
                                                $submitted_answer =  array_get($survey_answer, 'rate_'.$survey_question_id, []);
                                                $submitted_answer = array_get($submitted_answer, 0);
                                            ?>
                                            <input type="radio" name="rate_{{$survey_question_id}}[]" value="{{ $answer }}" style="margin-left: unset !important;" disabled="disabled"
                                            @if((!empty($submitted_answer)) && ($submitted_answer == $answer))
                                                checked 
                                            @endif
                                            ><label>{{ $answer }}</label><br>
                                        @endforeach
                                    @endif
                                    @if($survey_question_choices_others)
                                        <div class="margin-15">
                                            <label>{{ trans('survey.others') }}:</label>
                                            <p>{{ array_get($survey_answer, 'rate_textarea_'.$survey_question_id) }}</p>
                                        </div>
                                    @endif    
                                </div>
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
                                    <p>{{ array_get($survey_answer, 'textarea_'.$survey_question_id) }}</p>
                                </div>
                            </div>
                        </div>
                    @endif
                @endforeach
                </form>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).ready(function(){
        $('#alert-success').delay(5000).fadeOut();
    });
</script>
@stop