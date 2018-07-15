@section('content')
    @if ( Session::get('success'))
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert" id="alert-success">×</button>
            <!-- <strong>Success!</strong> -->
            {{ Session::get('success') }}
        </div>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
        <!--    <strong>Error!</strong> -->
            {{ Session::get('error') }}
        </div>
    @endif
    @if ( Session::get('warning'))
        <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('warning') }}
        </div>
        <?php Session::forget('warning'); ?>
    @endif
    <style type="text/css">
        .question-box {
            padding-bottom: 10px;
            border: 1px solid #e7e7e7;
            margin: 0px 25px 0px 25px;
        }
        .attempted-count {
            margin-top: 16px;
            padding-right: 10px;
        }
        .attempted-count ul li{
            display: inline;
        }
        .progress {
            height: 15px;
            margin-bottom: unset;
            position: relative;
            padding-left: 0px;
            padding-right: 0px
        }
        .progress-bar {
            background-color: rgb(124, 181, 236);
        }
        .question-title {
            padding: 10px 20px 10px 20px !important;
            border-bottom: 1px solid #e0e0e0;
            font-size: 16px;
        }
        .users-count {
            position: absolute;
            margin-top: -3px;
            right:0px;
            background-color: #36c6d3;
            padding:0px 10px 0px 10px
        }
    </style>
@include('admin.theme.survey.text_ans')
@include('admin.theme.survey.unattempted_users')
@include('admin.theme.survey.choice_users')
<div class = "row" style="padding-top: 20px">
    <div class="col-md-1">
    </div>
    <div class="col-md-10">
        <div class="pull-left">
            <span style="font-size:28px"><i class="fa fa-file-text"></i>&nbsp;{{trans('admin/survey.survey_report')}}</span>
        </div>
        <div class="btn-group pull-right">
            <div class="btn-group">
                <a class="btn btn-primary btn-sm " href="{{ URL::to('/cp/survey/list-survey/') }}">
                    <span class="show-tooltip custom-btm">
                        <i class="fa fa-angle-left"></i>
                    </span>&nbsp;{{trans('admin/survey.back')}}
                </a>
            </div>
        </div>
        <div class="pull-right" style="margin-right: 10px">
            <a class="btn btn-primary btn-sm font-14" href="{{ URL::to('/cp/survey/detail-report/'. $survey->id) }}">{{trans('admin/survey.detailed_report')}}</a>
        </div>
        <div class="clearfix"></div>
    </div>
</div>
<br>
<div class="row">
    <div class="col-md-1">
    </div>
    <div class="col-md-10">
        <div class="box">
            <div class="box-content">
                <div class="col-md-12">
                    <div class="pull-left clearfix">
                        <h2 style="padding-left:10px">{{$survey->survey_title}}</h2>
                    </div>
                    <div class="btn-toolbar pull-right clearfix margin-bottom-20">
                        <div class="btn-group attempted-count">
                            <ul>
                                <li>
                                    {{trans('admin/survey.unattempted')}}:
                                    @if($unattempt_users > 0)
                                        <a href="#unattempted-users" data-toggle="modal" class="show-tooltip badge badge-info unattempted-users" data-title="{{trans('admin/survey.unattempted_users')}}">
                                        {{$unattempt_users}}/{{$total_users}}
                                        </a>
                                    @else
                                        <label class="show-tooltip badge badge-info">
                                        {{$unattempt_users}}/{{$total_users}}
                                        </label>
                                    @endif
                                </li>&nbsp;&nbsp;
                                <li>
                                    {{trans('admin/survey.attempted')}}:
                                    <label class="show-tooltip badge badge-info">
                                        {{$user_count}}/{{$total_users}}
                                    </label>
                                </li>
                            </ul>
                        </div>&nbsp;&nbsp;
                    </div>
                </div>
                <div class="clearfix"></div>
                <?php $question_number = 0; ?>
                @foreach ($survey_question_details as $question_id => $survey_question)
                    <?php $question_number++; ?>
                    @if ($survey_question->type != "DESCRIPTIVE")
                        <?php
                        $questions_ans =  !is_null($user_responses->get($question_id)) ? $user_responses->get($question_id)->keyBy('user_answer') : collect([]);
                        ?>
                        <div class= "question-box">
                            <p class="question-title">{{$question_number}}.&nbsp;{{$survey_question->title}} </p>
                            @foreach ($survey_question->choices as $ans_index => $choices)
                                <?php
                                if (!is_null($questions_ans->get($ans_index))) {
                                    $ans_count =  $questions_ans->get($ans_index)->count;
                                    $ans_progress = 0 ;
                                    if ($total_users >= 1) {
                                        $ans_progress = ($ans_count/$total_users)*100;
                                    }
                                } else {
                                    $ans_count = 0;
                                    $ans_progress = 0;
                                }
                                 ?>
                                <div style="padding: 0px 20px 5px 20px;">
                                    <span><label class="choice-title">{{$choices}}</label></span>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: {{$ans_progress}}%;"></div>
                                        @if($ans_count > 0)
                                            <a href="#choice-users" data-toggle="modal" data-question={{$question_id}} data-choice = "{{$ans_index}}" data-title="{{$question_number}}. {{$survey_question->title}}" data-choicetitle = "{{$choices}}" class="choice-users" style="color: #000">
                                                <div class="users-count">
                                                    <span>{{$ans_count}}</span>
                                                </div>
                                            </a>
                                        @else
                                            <div class="users-count">
                                                <label><a>{{$ans_count}}</a></label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endforeach
                            @if ($survey_question->is_others == true)
                                <?php
                                    if (!is_null($desc_answers->get($question_id))) {
                                        $other_ans_count = $desc_answers->get($question_id)->count;
                                        $other_progress = 0 ;
                                        if ($total_users >= 1) {
                                            $other_progress = ($other_ans_count/$total_users)*100;
                                        }
                                    } else {
                                        $other_ans_count = 0;
                                        $other_progress = 0;
                                    }
                                 ?>
                                <div style="padding: 0px 20px 0px 20px">
                                    <span><label class="choice-title">{{trans('admin/survey.others')}}</label></span>
                                    <div class="progress">
                                        <div class="progress-bar" role="progressbar" style="width: {{$other_progress}}%;"></div>
                                        @if($other_ans_count > 0)
                                            <a href="#text-ans" data-toggle="modal" data-question={{$question_id}} data-title="{{$question_number}}. {{$survey_question->title}}" class="text-ans" style="color: #000">
                                                <div class="users-count">
                                                    {{$other_ans_count}}
                                                </div>
                                            </a>
                                        @else
                                            <div class="users-count">
                                                <label><a>{{$other_ans_count}}</a></label>
                                            </div>
                                        @endif
                                    </div>
                                </div>
                            @endif
                        </div>
                        <br>
                    @else
                        <?php
                            $desc_answers_count = !is_null($desc_answers->get($question_id)) ? $desc_answers->get($question_id)->count : 0;
                        ?>
                        <div class="question-box">
                            <p class="question-title">{{$question_number}}.&nbsp;{{$survey_question->title}}</p>
                            <div style="word-break: break-all;padding: 0px 20px 0px 20px;">
                                @if ( $desc_answers_count != 0 )
                                <?php
                                    $desc_ans = isset($desc_answers->get($question_id)->Other_text) ? $desc_answers->get($question_id)->Other_text : [''];
                                    print_r($desc_ans[0]);
                                ?><br>
                                <a href="#text-ans" data-toggle="modal" data-question={{$question_id}} data-title="{{$question_number}}. {{$survey_question->title}}" id="textans" class="text-ans read-more" style="background-color: unset">{{trans('admin/survey.view_details')}}</a>
                                @endif
                            </div>
                        </div><br>
                    @endif
                @endforeach
            </div>
        </div>
    </div>
     <div class="col-md-1">
    </div>
</div>
<script type="text/javascript">
    var page = 0;
    var surveyID = {{$survey->id}};
    var questionID = 0;
    $('.text-ans').click(function() {
        questionID = $(this).data('question');
        page = 0;
        $('#text-ans').find('#read-more-text').show();
        $('#text-ans').find('#title_question_model').html($(this).data('title'));
        $('#text-ans').find('.modal-body').empty();
        userResponceText(page);
    });
     $('#read-more-text').click(function() {
        page++;
        userResponceText(page);
    });

    function userResponceText(page) {
        $.ajax({
            type:'GET',
            url: '{{URL::to('/cp/survey/text-report-ajax')}}'+'?survey_id='+surveyID+'&question_id='+questionID+'&page='+page,
        })
        .done(function (e){
            var innerhtml = "";
            if(e.status && e.message != "") {
                $.each(e.message, function(index, value) {
                    innerhtml+="<div class='modal-table-data'><div class='row'><div class='col-md-2 text-right'><b>{{trans('admin/survey.username')}}:</b></div><div class='col-md-10 padding-left-0'>"+value[0]+"</div></div><div class='row'><div class='col-md-2 text-right'><b>{{trans('admin/survey.response')}}:</b></div><div class='col-md-10 padding-left-0'>"+value[1]+"</div></div></div><br>";
                })
            } else {
                innerhtml = '{{trans('admin/survey.no_more_records')}}';
                $('#text-ans').find('#read-more-text').hide();
            }
            $('#text-ans').find('.modal-body').append(innerhtml);
        })
        .fail(function(response) {
            alert('{{trans('admin/survey.data_error')}}');
        });
     }

    $('.unattempted-users').click(function() {
        page = 0;
        $('#unattempted-users').find('#read-more').show();
        $('#unattempted-users').find('#title_model').html($(this).data('title'));
        $('#unattempted-users').find('.modal-body').empty();
        innerhtml = "<table class='table table-advance table-bordered' id='unattempted-table'><thead><tr><th>{{trans('admin/survey.username')}}</th><th>{{trans('admin/dashboard.user_fullname')}}</th><th>{{trans('admin/survey.email')}}</th></tr></thead></table>";
        $('#unattempted-users').find('.modal-body').append(innerhtml);
        unattemptedUsers(page);
    });
    $('#read-more').click(function() {
        page++;
        unattemptedUsers(page);
    });

    function unattemptedUsers(page) {
         $.ajax({
            type:'GET',
            url: '{{URL::to('/cp/survey/unattempted-user-details')}}'+'?survey_id='+surveyID+'&page='+page,
        })
        .done(function (response){
            var innerhtml = "";
            if(response.message != "") {
                $('#unattempted-users').modal('show');
                $.each(response.message, function( index, value ) {
                    innerhtml += "<tr><td>"+value[0]+"</td> <td>"+value[1]+"</td><td>"+value[2]+"</td></tr>";
                });
                $('#unattempted-users').find('.modal-body').find('#unattempted-table').append(innerhtml);
            } else {
                innerhtml = '{{trans('admin/survey.no_more_records')}}';
                $('#unattempted-users').find('#read-more').hide();
                $('#unattempted-users').find('.modal-body').append(innerhtml);
            }
        })
        .fail(function(response) {
            alert('{{trans('admin/survey.data_error')}}');
        });
    }

    $('.choice-users').click(function() {
        questionID = $(this).data('question');
        choiceIndex = $(this).data('choice');
        page = 0;
        $('#choice-users').find('#load-more').show();
        $('#choice-users').find('#title_model').html($(this).data('title'));
        $('#choice-users').find('#choice').html($(this).data('choicetitle'));
        $('#choice-users').find('.modal-body').empty();
        innerhtml = "<table class='table table-advance table-bordered' id='attempted-table'><thead><tr><th>{{trans('admin/survey.username')}}</th><th>{{trans('admin/dashboard.user_fullname')}}</th><th>{{trans('admin/survey.email')}}</th></tr></thead></table>";
        $('#choice-users').find('.modal-body').append(innerhtml);
        attemptedUsers(page);
    });
    $('#load-more').click(function() {
        page++;
        attemptedUsers(page);
    });

    function attemptedUsers(page) {
         $.ajax({
            type:'GET',
            url: '{{URL::to('/cp/survey/attempted-user-details')}}'+'?survey_id='+surveyID+'&question_id='+questionID+'&choice_index='+choiceIndex+'&page='+page,
        })
        .done(function (response){
            var innerhtml = "";
            if(response.message != "") {
                $('#choice-users').modal('show');
                $.each(response.message, function( index, value ) {
                    innerhtml += "<tr><td>"+value[0]+"</td> <td>"+value[1]+"</td><td>"+value[2]+"</td></tr>";
                });
                $('#choice-users').find('.modal-body').find('#attempted-table').append(innerhtml);
            } else {
                innerhtml = '{{trans('admin/survey.no_more_records')}}';
                $('#choice-users').find('#load-more').hide();
                $('#choice-users').find('.modal-body').append(innerhtml);
            }
        })
        .fail(function(response) {
            alert('{{trans('admin/survey.data_error')}}');
        });
    }
</script>
@stop


