@section('content')
<style>
    .margin-15{
        margin: 15px !important;
        border-bottom: 1px solid #e7e7e7;
    }
    .answer-section {
        padding-left: 15px;
    }
    .answer-section .labels {
        padding-left: 5px !important;
    }
    .form-horizontal .radio {
        padding-top: 5px !important;
    }
    .checker {
        margin-top: 1px !important;
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
    .survey-title {
        font-size: 16px;
        padding: 5px;
    }
    .box-title {
        padding: 10px !important;
        color: #6C7A89;
    }
    #main-content {
        background-color: #fff !important;
    }
</style>
<?php
use App\Enums\Survey\SurveyType;
$survey_id = $survey->id;
?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
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
                            <label><span class="red">*</span>{{ $ques_num.') '. $survey_question_title }}</label>
                        @else
                            <label>{{ $ques_num.') '. $survey_question_title }}</label>
                        @endif
                        <div class="form-group">
                            <div class="answer-section">
                                @if(!empty($survey_question_choices))
                                    @foreach($survey_question_choices as $answer)
                                        <?php
                                            $submitted_answer =  array_get($survey_answer, 'checkbox_'.$survey_question_id, []);
                                            $answered = in_array($answer, $submitted_answer);
                                            ?>
                                        <div class="row">
                                            <div class="col-md-1 col-sm-1 col-xs-1" style="width:2% !important;">
                                                <input type="checkbox"  name="checkbox_{{$survey_question_id}}[]" value="{{ $answer }}" disabled="disabled" @if($answered) checked @endif>
                                            </div>
                                            <div class="col-md-11 col-sm-11 col-xs-11 labels">
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
                            <div class="answer-section">
                                @if(!empty($survey_question_choices))
                                    @foreach($survey_question_choices as $answer)
                                        <?php
                                            $submitted_answer =  array_get($survey_answer, 'radio_'.$survey_question_id, []);
                                            $submitted_answer = array_get($submitted_answer, 0);
                                        ?>
                                        <div class="row">
                                            <div class="col-md-1 col-sm-1 col-xs-1" style="width:2% !important;">
                                                <input type="radio" name="radio_{{$survey_question_id}}[]" value="{{ $answer }}" style="margin-left:unset;" disabled="disabled"
                                                @if((!empty($submitted_answer)) && ($submitted_answer == $answer))
                                                    checked
                                                @endif
                                                >
                                            </div>
                                            <div class="col-md-11 col-sm-11 col-xs-11 labels">
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

                <!-- code for range type questions -->
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
                                        ><label style="padding: 0px 0px 0px 5px;">{{ $answer }}</label><br>
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
<script type="text/javascript">
    $(document).ready(function(){
        $('#alert-success').delay(5000).fadeOut();
    });
</script>
@stop