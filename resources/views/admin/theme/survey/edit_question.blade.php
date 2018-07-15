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
                <div class="box-title box-heading"><b>{{ trans("admin/survey.survey_name") }} : {{ $survey_name }}</b></div>
                <div class="box-title">
                    <div class="alert alert-danger" style="display: none;">
                        <button class="close" data-dismiss="alert">×</button>
                        <span>{{trans('admin/survey.minimum_one_choice')}}</span>
                    </div>
                </div>
                <div class="box-content">
                    <form action="{{ URL::to('/cp/survey/edit-question/'.$question['id'].'/'. $survey_id) }}" class="form-horizontal form-bordered form-row-stripped" method="post" accept-charset="UTF-8" data-custom-id="GENERAL" id="survey_form">
                        <div class="form-group">
                            <?php
                            if (Input::old('question_name')) {
                                $question_name = Input::old('question_name');
                            } elseif (isset($question['title'])) {
                                $question_name = $question['title'];
                            }
                            ?>
                            <label for="question_name" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.question_title')}}<span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="question_name" class="form-control" value="{{ $question_name }}">
                                {!! $errors->first('question_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.question_type')}}
                                <span class="red">*</span>
                            </label>
                            <?php
                            if ($question['type'] == "MCQ-SINGLE") {
                                $question_type = trans('admin/survey.single_answer');
                            } elseif ($question['type'] == "MCQ-MULTIPLE") {
                                $question_type = trans('admin/survey.multiple_answers');
                            } elseif ($question['type'] == "DESCRIPTIVE") {
                                $question_type = trans('admin/survey.text');
                            } elseif ($question['type'] == "RATE-5") {
                                $question_type = trans('admin/survey.range');
                            } elseif (Input::get('question_type')) {
                                $question_type = Input::get('question_type');
                            }
                            ?>

                            <div class="col-sm-9 col-lg-6 controls">
                                <select disabled="disbaled" id="question_type" class="form-control width420" name="select">
                                @foreach($type as $key => $t)
                                    <option id="ques-option" value="{{$question['type']}}" @if($question['type'] == $key) selected @endif>
                                        @if ($question['type'] == $t)
                                            {{ $question['type'] }}
                                        @else
                                            {{ $t }}
                                        @endif
                                    </option>
                                @endforeach
                                </select>
                                <input type="hidden" name="q_type" id="q_type" value="{{ $question['type'] }}">
                                {!! $errors->first('question_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group" id="choice_block">
                            <label for="question_name" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.choices')}}<span class="red">*</span>
                            </label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <div id='choices'>
                                @foreach($question['choices'] as $choice)
                                    <div>
                                        <label>
                                            <input class="form-control" id="ques_choice" name="choice[]" value="{{ $choice }}" style="width: 240px;" placeholder="Enter Choice">
                                        </label>
                                        <i class="fa fa-close box-close" data-dismiss="modal" aria-hidden="true"></i>
                                    </div>
                                @endforeach
                                    <div id="choice-error-js"></div> <!-- To append the error msg on deletion-->
                                    <div id="add-more-choice"></div> <!-- To append more text boxes -->
                                </div>
                                {!! $errors->first('choice', '<span id="choice-error-ctrl" class="help-inline" style="color:#f00">:message</span>') !!}
                                <div>
                                    <a class="btn btn-link btn-sm" id="add-choice-btn"><em class="fa fa-plus add-choice"></em><span>{{trans('admin/survey.add_more_choice')}}</span></a>
                                </div>
                                <div style="padding-top: 15px;">
                                    <?php
                                    if (Input::old('others')) {
                                        $is_others = Input::old('others');
                                    } elseif (isset($question['is_others'])) {
                                        $is_others = $question['is_others'];
                                    }
                                    ?>
                                    <input class="md-check" name="others" type="checkbox" value="on" @if($is_others) checked @endif >
                                    <label>
                                        <p>{{trans('admin/survey.add_others')}}</p>
                                    </label>
                                </div>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="is_mandatory" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.mandatory')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <?php
                                if (Input::old('is_mandatory')) {
                                    $is_mandatory = Input::old('is_mandatory');
                                } elseif (isset($question['is_mandatory'])) {
                                    $is_mandatory = $question['is_mandatory'];
                                }
                                ?>
                                <input class="md-check" name="is_mandatory" type="checkbox" value="on" @if($question['is_mandatory']) checked @endif>
                            </div>
                        </div>
                        <div class="form-group">
                            <?php
                            if (Input::old('order_by')) {
                                $order_by = Input::old('order_by');
                            } elseif (isset($question['order_by'])) {
                                $order_by = $question['order_by'];
                            } else{
                                 $order_by = '';
                            }
                            ?>
                            <label for="order_by" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.order_by')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input class="md-check" name="order_by" type="number" min="1" value="{{ $order_by }}" @if(!empty(Input::old('order_by'))) checked @else @endif >
                                {!! $errors->first('order_by', '<span id="choice-error-ctrl" class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{trans('admin/survey.save')}}</button>
                               <button type="submit" class="btn btn-success"><a href="{{ URL::to('/cp/survey/survey-questions/'.$question['survey_id']) }}">{{trans('admin/survey.cancel')}}</a></button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
       $(document).ready(function() {
            var count = {{count($question['choices'])}};
            var question_type = $('#ques-option').val();
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
                    $("#choice-error-js").html("Two choices are mandatory for question").css("color", "red");
                    $("#choice-error-ctrl").html("");
                }
            });
       });
    </script>
@stop