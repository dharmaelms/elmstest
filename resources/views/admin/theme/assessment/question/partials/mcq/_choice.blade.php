<div class="form-group">
    <label for="answer[{{ $answerCount }}]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} {{ $answerCount+1 }}<span class="red" style="{{ ($answerCount+1) <= 2 ? "display : inline" : "display : none" }}">*</span></label>
    <div class="col-md-10 col-lg-10 controls">
        @if(!isset($view_mcq))

            <div class="col-md-8 col-lg-8 rich-text-content">
                @include("admin/theme/assessment/question/partials/_rich_text_area", [
                    "name" => "answer[".$answerCount."]",
                    "id" => "answer-".$answerCount,
                    "content" => isset($answerContent)? $answerContent : ""                   
                ])

            </div>

            <div class="col-md-4 col-lg-4">
                <div class="editor-media">
                    <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="answer-{{ $answerCount }}">
                        <i class="fa fa-video-camera"></i>
                        <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                    </button>
                </div>
                <input type="radio" name="correct_answer" value="{{ $answerCount }}" required {{ $correctAnswer? "checked" : "" }}/> {{ trans('admin/assessment.mark_as_correct_answer') }}
            </div>
            
            <div class="col-md-8 col-lg-8">
                {!! $errors->first("answer.".$answerCount, '<span class="help-inline" style="color:#f00">:message</span>') !!}
            </div>

        @else if(isset($view_mcq) && $view_mcq === true)

            <div class="col-md-8 col-lg-8 rich-text-content">
            
             @include("admin/theme/assessment/question/partials/mcq/__label_choice", [
                    "name" => "answer[".$answerCount."]",
                    "id" => "answer-".$answerCount,
                    "content" => isset($answerContent)? $answerContent : ""
                ])
            </div>
        
        @endif

        @if(empty($rationaleContent))
        <div class="col-md-8 col-lg-8">
            <a href="javascript:;" class="rationale" data-id="rationale_box_{{ $answerCount }}">
                <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button>{{ trans('admin/assessment.add_rationale') }}
            </a>
        </div>
        @endif
    </div>
</div>

<div class="form-group" id="rationale_box_{{ $answerCount }}" style="display: {{ (!empty($rationaleContent))? "block" : "none" }}">
    <label for="rationale[{{ $answerCount }}]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} {{ $answerCount+1 }} {{ trans('admin/assessment.rationale') }}</label>
    <div class="col-md-10 col-lg-10 controls">
        <div class="col-md-8 col-lg-8 rich-text-content">
            @include("admin/theme/assessment/question/partials/_rich_text_area", [
                "name" => "rationale[".$answerCount."]",
                "id" => "rationale-".$answerCount,
                "content" => isset($rationaleContent)? $rationaleContent : ""
            ])
        </div>
        <div class="col-md-4 col-lg-4">
            <div class="editor-media">
                <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="rationale-{{ $answerCount }}">
                    <i class="fa fa-video-camera"></i>
                    <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                </button>
            </div>
        </div>                              
    </div>
</div>