<label for="question_text" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.question_text') }}&nbsp;<span class="red">*</span></label>

<div class="col-md-10 col-lg-10 controls">
    @if(!isset($view_mcq))
    <?php
            if(!is_null(Input::old('question_text'))) { 
                $question_text = Input::old("question_text"); 
            }
    ?>
        <div class="col-md-8 col-lg-8 rich-text-content">
            @include("admin/theme/assessment/question/partials/_rich_text_area", [
                "name" => "question_text",
                "id" => "question_text",
                "content" => $question_text
            ])
        
        </div>
        <div class="col-md-2 col-lg-2 editor-media">
            <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="question_text">
                <i class="fa fa-video-camera"></i>
                <span>{{ trans('admin/assessment.add_media_lib') }}</span>
            </button>
        </div>
        <p class="pull-right">
            @if(isset($copied) && $copied)
                <strong>
                    <span class="badge badge-success">{{ trans('admin/assessment.copy')}}</span>
                </strong>
            @endif
        </p>
        <div class="col-md-8 col-lg-8">
            {!! $errors->first("question_text", "<span class=\"help-inline\" style=\"color:#f00\">:message</span>") !!}
        </div>        
    @else if(isset($view_mcq) && $view_mcq === true)
        
        <div class="col-md-8 col-lg-8 rich-text-content">
        
            @include("admin/theme/assessment/question/partials/__label_question_text", [
                "name" => "question_text",
                "id" => "question_text",
                "content" => isset($question_text)? $question_text : ""
            ])

        </div>
        <p class="pull-right">
            @if(isset($copied) && $copied)
                <strong>
                    <span class="badge badge-success">{{ trans('admin/assessment.copy')}}</span>
                </strong>
            @endif
        </p>
    @endif
    
</div>