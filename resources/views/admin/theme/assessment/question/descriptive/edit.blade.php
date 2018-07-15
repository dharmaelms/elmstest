@extends("admin.theme.assessment.question.common")

@section("descriptive_attributes")

@if($question["data_flag"])
<form method="post" action="{{ URL::route("post-edit-question") }}" accept-Charset="UTF-8" class="form-horizontal form-bordered form-row-stripped form-question">
	<input type="hidden" name="question_bank_id" name="question_bank_id" value={{ $question_bank_id }}>
	<input type="hidden" name="_qtype" id="_qtype" value="DESCRIPTIVE">
	<input type="hidden" name="question_id" id="question_id" value="{{ $question["details"]->_id }}">
	<input type="hidden" name="return" id="return" value="{{ Request::has("return")? Request::input("return") : Request::old("return") }}">

	<div class="form-group">
        @include("admin/theme/assessment/question/partials/_question_text", ["question_text" => $question["details"]->question_text])
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-5 col-lg-5">
                @include("admin/theme/assessment/question/partials/_difficulty_level", ["difficulty_level" => $question["details"]->difficulty_level])
            </div>
            <div class="col-md-4 col-lg-4">
                @include("admin/theme/assessment/question/partials/_default_mark", ["default_mark" => $question["details"]->default_mark])
            </div> 
        </div>
    </div>
    <div class="form-group">
        @include("admin/theme/assessment/question/partials/_tags", [
            "tags" => (!empty($question["details"]->keywords)? implode(",", $question["details"]->keywords) : "")
        ])
    </div>
    <div class="form-group last">
        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
            <button {{ !$isQuestionAttempted? "type=\"submit\"" : "disabled" }} name="status" value="ACTIVE" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
            <a href="{{ URL::to(Request::has("return")? Request::input("return") : Request::old("return")) }}?start={{ Input::get("start", 0) }}&limit={{ Input::get("limit", 10) }}&search={{ Input::get("search","") }}&order_by={{ Input::get("order_by","7 desc") }}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
            @if($isQuestionAttempted)
            <p class="red">{{ trans('admin/assessment.cant_update_question_note') }}</p>
            @endif
        </div>
    </div>
</form>
@endif

@stop