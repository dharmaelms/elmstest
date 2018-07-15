<label for="question_text" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.question_text') }}&nbsp;</label>
<div class="col-md-10 col-lg-10 controls">
       
       
    @if(!isset($view_mcq) && $view_mcq != true)
        <div class="col-md-8 col-lg-8 rich-text-content">
            @include("admin/theme/assessment/question/partials/_rich_text_area", [
                "name" => "question_text",
                "id" => "question_text",
                "content" => isset($question_text)? $question_text : ""
            ])
        </div>

    @else
        <div class="col-md-7 col-lg-7">
            @include("admin/theme/assessment/question/partials/__label_question_text", [
            "name" => "question_text",
            "id" => "question_text",
            "content" => isset($question_text)? $question_text : ""
        ])
         
    @endif
    </div>
    <div class="col-md-1 col-lg-1 pull-left">
        @if(isset($copied) && $copied)
            <h3>
                <span class="badge badge-success">{{ trans('admin/assessment.copy')}}</span>
            </h3>
        @endif
    </div>
    <div class="col-md-8 col-lg-8">
        {!! $errors->first("question_text", "<span class=\"help-inline\" style=\"color:#f00\">:message</span>") !!}
    </div>
</div>