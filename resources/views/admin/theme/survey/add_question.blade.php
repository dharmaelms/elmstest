@section('content')
<style>
    .fa-close {
        margin-left: 10px;
    }
    .btn a{color: #fff;}
    .add-choice {
        margin: 0 5px 0 -10px;
    }
    .box-heading {padding: 10px !important;color: #6C7A89;}
</style>
	<div class="row">
        <div class="col-md-12">
            <div class="box">
             <div class="box-title box-heading"><b>{{ trans("admin/survey.survey_name") }} : {{ $survey_name }} </b></div>
                <div class="box-title">
                    <div class="alert alert-danger" style="display: none;">
                        <button class="close" data-dismiss="alert">×</button>
                        <span>{{trans('admin/survey.minimum_one_choice')}}</span>
                    </div>
                </div>
                <div class="box-content">
                    <form action="{{ URL::to('/cp/survey/add-question/'.$sid) }}" class="form-horizontal form-bordered form-row-stripped" method="post" accept-charset="UTF-8" data-custom-id="GENERAL" id="survey_form" onsubmit="questionSubmit()">
                        <div class="form-group">
                            <label for="question_name" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.question_title')}}<span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="question_name" class="form-control" value="{{ Input::old('question_name') }}">
                                {!! $errors->first('question_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.question_type')}}<span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <select id="question_type" class="form-control width420" name="question_type" value="{{ Input::old('question_type') }}">
                                    <option value="MCQ-SINGLE" @if(Input::old('question_type') == 'MCQ-SINGLE') selected @endif>{{trans('admin/survey.single_answer')}}</option>
                                    <option value="MCQ-MULTIPLE" @if(Input::old('question_type') == 'MCQ-MULTIPLE') selected @endif>{{trans('admin/survey.multiple_answers')}}</option>
                                    <option value="DESCRIPTIVE" @if(Input::old('question_type') == 'DESCRIPTIVE') selected @endif>{{trans('admin/survey.text')}}</option>
                                    <option value="RATE-5" @if(Input::old('question_type') == 'RATE-5') selected @endif>{{trans('admin/survey.range')}}</option>
                                </select>
                                {!! $errors->first('question_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group" id="choice_block">
                            <label for="question_name" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.choices')}}<span class="red">*</span>
                            </label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <div id='choices'>
                                    <?php
                                        $choices_old = Input::old('choice');
                                        $choice_count = 2;
                                    if (!is_null($choices_old)) {
                                        $choice_count = count($choices_old);
                                    }
                                    for ($i=0; $i < $choice_count; $i++) {
                                    ?>
                                    <div>
                                    <label>
                                        <input class="form-control" id="ques_choice" name="choice[]" style="width: 240px;" placeholder="Enter Choice" value="{{ Input::old('choice.'.$i) }}">
                                    </label>
                                    <i class="fa fa-close box-close" data-dismiss="modal" aria-hidden="true"></i>
                                    </div>
                                    <?php                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              } ?>
                                    <div id="choice-error-js"></div> <!-- To append the error msg on deletion-->
                                    <div id="add-more-choice"></div> <!-- To append more text boxes -->
                                </div>
                                {!! $errors->first('choice', '<span id="choice-error-ctrl" class="help-inline" style="color:#f00">:message</span>') !!}
                                <div>
                                    <a class="btn btn-link btn-sm" id="add-choice-btn"><em class="fa fa-plus add-choice"></em><span>{{trans('admin/survey.add_more_choice')}}</span></a>
                                </div>
                                <div style="padding-top: 15px;">
                                    <input class="md-check" name="others" type="checkbox" value="on" @if(!empty(Input::old('others'))) checked @else @endif >
                                    <label>
                                        <p>{{trans('admin/survey.add_others')}}</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="is_mandatory" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.mandatory')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input class="md-check" name="is_mandatory" type="checkbox" value="on" @if(!empty(Input::old('is_mandatory'))) checked @else @endif >
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="order_by" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.order_by')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input class="md-check" name="order_by" type="number" min="1" value="{{$question_count}}" @if(!empty(Input::old('order_by'))) checked @else @endif >
                                {!! $errors->first('order_by', '<span id="choice-error-ctrl" class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" id="ques_submit"><i class="fa fa-check"></i> {{trans('admin/survey.save')}}</button>
                               <button type="submit" class="btn btn-success"><a href="{{ URL::to('/cp/survey/survey-questions/'.$sid) }}">{{trans('admin/survey.cancel')}}</a></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
       $(document).ready(function() {
            var count = {{$choice_count}};
            var question_type = $('#question_type').val();
            if(question_type == 'MCQ-SINGLE' || question_type == 'MCQ-MULTIPLE') {
                $('#choice_block').show();
                $("#choice-error-js").html('');
            } else{
                $('#choice_block').hide();
            }
            $('#survey_form').on('keyup keypress', function(e) {
                  var keyCode = e.keyCode || e.which;
                  if (keyCode === 13) {
                    e.preventDefault();
                    return false;
                }
            });
            /* Adding more text boxes code */
            $("#add-choice-btn").click(function(){
                count++;
                $("#choice-error-ctrl").html("");
                $("#choice-error-js").html("");
                $('#add-more-choice').append('<div><label><input class="form-control" id="ques_choice" name="choice[]" style="width: 240px;" placeholder="Enter Choice"></label><i class="fa fa-close box-close" data-dismiss="modal" aria-hidden="true"></i><div id="choice-error-js"></div></div>');
            });

            /* Text box removing code */
            $('#choices, #add-more-choice').on("click",".box-close", function(e) {
                if(count > 2) {
                    $(this).parent('div').remove();
                    $("#choice-error-ctrl").html("");
                    $("#choice-error-js").html("");
                    count--;
                } else if(count == 2) {
                    $("#choice-error-js").html("Enter atleast 2 choice").css("color", "red");
                    $("#choice-error-ctrl").html("");
                }
            });

            /* Selecting question type code */
            $('#question_type').click(function(){
                var question_type = $('#question_type').val();
                if(question_type == 'MCQ-SINGLE' || question_type == 'MCQ-MULTIPLE') {
                    $('#choice_block').show();
                    $("#choice-error-js").html('');
                } else{
                    $('#choice_block').hide();
                }
            });
       });
        function questionSubmit() {
            $("#ques_submit").attr('disabled', true);
        }
    </script>
@stop