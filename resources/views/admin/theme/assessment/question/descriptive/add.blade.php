@extends("admin.theme.assessment.question.common")
@section("descriptive_attributes")
<form method="post" accept-Charset="UTF-8" action="{{ URL::route("post-add-question") }}" class="form-horizontal form-bordered form-row-stripped form-question">
    <input type="hidden" name="_qtype" value="DESCRIPTIVE">
	<div class="form-group">
        @include("admin/theme/assessment/question/partials/_question_text", ["question_text" => (Input::has("question_text")? Input::old("question_text") : "")])
    </div>
	<div class="form-group">
        <div class="row">
            <div class="col-md-5 col-lg-5">
                @include("admin/theme/assessment/question/partials/_difficulty_level", ["difficulty_level" => (Input::has("difficulty_level")? Input::old("difficulty_level") : "easy")])
            </div>
            <div class="col-md-4 col-lg-4">
                @include("admin/theme/assessment/question/partials/_default_mark", ["default_mark" => (Input::has("default_mark")? Input::old("default_mark") : 1)])
            </div> 
        </div>
    </div>
    <div class="form-group">
        <div class="row">
            <div class="col-md-5 col-lg-5">
                @include("admin/theme/assessment/question/partials/_question_bank", [
                    "questionBanks" => $questionbank,
                    "selectedQB" => (!empty(Input::old("question_bank"))? Input::old("question_bank") : (Input::has("qb")? Input::get("qb") : null))
                ])
            </div>
        </div>
    </div>
    <div class="form-group">
        @include("admin/theme/assessment/question/partials/_tags", [
            "tags" => (!empty(Input::old("keywords")) ? Input::old("keywords") : null)
        ])
    </div>
    <div class="form-group last">
        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
           <button type="submit" name="status" value="ACTIVE" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
           <button type="submit" name="status" value="DRAFT" class="btn btn-info" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save_as_draft') }}</button>
           <a href="{{ URL::to(Input::get('return', '/cp/assessment/list-questionbank')) }}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
        </div>
    </div>
</form>
@stop