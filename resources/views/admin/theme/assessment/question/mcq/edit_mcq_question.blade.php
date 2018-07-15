@section('content')
<?php
    $start    =  Input::get('start', 0);
    $limit    =  Input::get('limit', 10);
    $search   =  Input::get('search','');
    $order_by =  Input::get('order_by','7 desc');
?>
   {!! $errors->first('answer_dupe', '<div class="alert alert-danger">
        <button class="close" data-dismiss="alert">×</button>
        :message
        </div>') 
    !!}
<link rel="stylesheet" type="text/css" href="{{ URL::to("css/responsive-iframe.css") }}">
<link rel="stylesheet" href="{{ URL::asset('admin/css/assessment/question.css') }}">
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
<script src="{{ URL::asset('admin/assets/bootstrap-tagsinput/bootstrap-tagsinput.min.js')}}"></script>
<div class="box-tool" style="top:4px">
    @if(isset($question->parent_question_id))
        <a class="btn btn-info" href='{{ URL::to("cp/assessment/edit-rationale/$question->parent_question_id/$question->question_bank") }}'>{{ trans('admin/assessment.parent_question_link') }}</a>
    @endif
</div>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
            </div>
            @if(Session()->has('attempted'))
                <div class="alert alert-warning">{{ trans('admin/assessment.no_edit_for_attempted_question') }}</div>
            @endif
            <div class="box-content">
                <form action="#" class="form-horizontal form-bordered form-row-stripped form-question" method="post" accept-Charset="UTF-8">

                    <input type="hidden" name="return" id="return" value="{{ Request::has("return")? Request::input("return") : Request::old("return") }}">
                    <input type="hidden" name="_qtype" value="mcq">
                    <input type="hidden" name="_q" value={{ $question->question_id }}>
                    
                    <div class="form-group">
                        @include("admin/theme/assessment/question/partials/_question_text", [
                            "question_text" => $question->question_text,
                            "copied" => isset($question->parent_question_id)?$question->parent_question_id:false,
                        ])
                    </div>

<?php
                    for($i=0; $i<count($question->answers); ++$i)
                    {
                        if(!is_null(Input::old('answer.'.$i)))
                            $answerContent = Input::old('answer.'.$i);
                        else
                            $answerContent =  $question->answers[$i]["answer"];
?>
                        @include("admin/theme/assessment/question/partials/mcq/_choice", [
                            "answerCount" => $i,
                            "answerContent" => $answerContent,
                            "correctAnswer" => ($question->answers[$i]["correct_answer"] === true),
                            "rationaleContent" => $question->answers[$i]["rationale"]
                        ])
<?php
                    }
?>

                    <div id="choice_div" class="form-group">
                        <label class="col-sm-3 col-lg-2 control-label"></label>
                        <div class="col-lg-10 controls">
                            <input type="hidden" id="choice_count" name="choice_count" value="{{ count($question->answers) }}">
                            <a href="javascript:;" id="add-choice">
                                <button class="btn btn-circle btn-success btn-xs" type="button"><i class="fa fa-plus"></i></button> {{ trans('admin/assessment.add_one_more_choice') }}  
                            </a>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-lg-5">
                                @include("admin/theme/assessment/question/partials/_difficulty_level", ["difficulty_level" => $question->difficulty_level])
                            </div>
                            <div class="col-md-4 col-lg-4">
                                @include("admin/theme/assessment/question/partials/_default_mark", ["default_mark" => $question->default_mark])
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <div class="row">
                            <div class="col-md-5 col-lg-5">
                                <label for="shuffle_answers" class="col-md-5 col-lg-5 control-label">{{ trans('admin/assessment.shuffle_answers') }}</label>
                                <div class="col-md-6 col-lg-6 controls">
                                    <input type="checkbox" name="shuffle_answers" @if($question->shuffle_answers) checked @endif></input>
                                    {!! $errors->first('shuffle_answers', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>  
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        @include("admin/theme/assessment/question/partials/_tags", [
                            "tags" => (!empty($question->keywords)? implode(",", $question->keywords) : "")
                        ])
                    </div>

                    <div class="form-group last">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                            @if($attempt == 0)
                            <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
                            <a href="{{ URL::to(Request::has("return")? Request::input("return") : Request::old("return")) }}?start={{$start}}&limit={{$limit}}&search={{$search}}&order_by={{$order_by}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
                            @else
                            <span class="btn btn-success" disabled><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</span>
                            <a href="{{ URL::to(Request::has("return")? Request::input("return") : Request::old("return")) }}?start={{$start}}&limit={{$limit}}&search={{$search}}&order_by={{$order_by}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
                            <p class="red">{{ trans('admin/assessment.cant_update_question_note') }}</p>
                            @endif
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript" src="{{ URL::asset("admin/assets/ckeditor/ckeditor.js")}}"></script>
<script type="text/javascript" src="{{ URL::asset("admin/js/assessment/dynamic_choice.js") }}"></script>
<script type="text/javascript" src="{{ URL::asset("admin/js/assessment/editor_media.js") }}"></script> 
<script type="text/javascript">

var $configPath = "{{ URL::asset('admin/assets/ckeditor/config.js')}}", 
    chost = "{{ config('app.url') }}";

<?php
    $mathml_editor = \App\Model\SiteSetting::module('MathML', 'mathml_editor');
?>
    @if($mathml_editor && $mathml_editor == 'on')
        CKEDITOR.config.extraPlugins += (CKEDITOR.config.extraPlugins.length == 0 ? '' : ',') + 'ckeditor_wiris';
    @endif

    $(document).ready(function(){
        $("input.tags").tagsinput({              
            confirmKeys:[13, 32, 44] //-- Only enter, spacebar and semicolon
        });
    });

    $(document).ready(function(){
        $(document).on('click', '.rationale', function(e){
            e.preventDefault();
            $(this).hide();
            $('#'+$(this).data('id')).slideDown({ duration : 400 });
        });
        $(".alert").delay(10000).slideUp(200, function() { //removing alert after 10 seconds
            $(this).alert('close');
        });
    });

</script>

@include("admin/theme/assessment/media_embed", ["from" => "question", "media_types" => ["image", "audio", "video"]])
@stop
