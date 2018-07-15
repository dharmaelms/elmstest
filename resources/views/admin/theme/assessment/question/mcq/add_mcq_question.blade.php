@section('content')

    
        {!! $errors->first('answer_dupe', '<div class="alert alert-danger">
        <button class="close" data-dismiss="alert">×</button>
        :message
        </div>') !!}
    <link rel="stylesheet" type="text/css" href="{{ URL::to("css/responsive-iframe.css") }}">
    <link rel="stylesheet" href="{{ URL::asset('admin/css/assessment/question.css') }}">
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-tagsinput/bootstrap-tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-tagsinput/bootstrap-tagsinput.min.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                </div>
                <div class="box-content">
                    <form action="#" class="form-horizontal form-bordered form-row-stripped form-question" method="post" accept-Charset="UTF-8">
                        <input type="hidden" name="_qtype" value="mcq">
                        
                        <div class="form-group">
                            <label for="question_text" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.question_text') }} <span class="red">*</span></label>
                            <div class="col-md-10 col-lg-10 controls">
                                <div class="col-md-8 col-lg-8 rich-text-content">
                                    <textarea name="question_text" rows="5" id="question_text" class="form-control" contenteditable="true">{!! Input::old('question_text') !!}</textarea>
                                </div>
                                <div class="col-md-2 col-lg-2 editor-media">
                                    <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="question_text">
                                        <i class="fa fa-video-camera"></i>
                                        <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                    </button>
                                </div>
                                <div class="col-md-8 col-lg-8">
                                    {!! $errors->first('question_text', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="answer[0]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} 1 <span class="red">*</span></label>
                            <div class="col-md-10 col-lg-10 controls">
                                <div class="col-md-8 col-lg-8 rich-text-content">
                                    <textarea name="answer[0]" id="answer-0" placeholder="Please type the answer" class="form-control">{{ Input::old('answer.0') }}</textarea>
                                </div>
                                <div class="col-md-4 col-lg-4">
                                    <div class="editor-media">
                                        <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="answer-0">
                                            <i class="fa fa-video-camera"></i>
                                            <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                        </button>
                                    </div>
                                    <input type="radio" name="correct_answer" value="0" required @if(Input::old('correct_answer') == 0) checked @endif/> {{ trans('admin/assessment.mark_as_correct_answer') }}
                                </div>
                                <div class="col-md-8 col-lg-8">
                                    {!! $errors->first('answer.0', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                                @if(empty(Input::old('rationale.0')))
                                <div class="col-md-8 col-lg-8">
                                    <a href="javascript:;" class="rationale" data-id="rationale_box_0">
                                        <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button>{{ trans('admin/assessment.add_rationale') }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>
                        
                        <div class="form-group" id="rationale_box_0" style="display: {{ (!empty(Input::old("rationale.0")))? "block" : "none" }}">
                            <label for="rationale[0]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} 1 {{ trans('admin/assessment.rationale') }} </label>
                            <div class="col-md-10 col-lg-10 controls">
                                <div class="col-md-8 col-lg-8 rich-text-content">
                                    <textarea name="rationale[0]" id="rationale-0" placeholder="Please type the rationale" class="form-control">{{ Input::old('rationale.0') }}</textarea>
                                </div>
                                <div class="col-md-4 col-lg-4">
                                    <div class="editor-media">
                                        <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="rationale-0">
                                            <i class="fa fa-video-camera"></i>
                                            <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                        </button>
                                    </div>
                                </div>                              
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="answer[1]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} 2<span class="red">*</span></label>
                            <div class="col-md-10 col-lg-10 controls">
                                <div class="col-md-8 col-lg-8 rich-text-content">
                                    <textarea name="answer[1]" id="answer-1" placeholder="Please type the answer" class="form-control">{{ Input::old('answer.1') }}</textarea>
                                </div>
                                <div class="col-md-4 col-lg-4">
                                    <div class="editor-media">
                                        <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="answer-1">
                                            <i class="fa fa-video-camera"></i>
                                            <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                        </button>
                                    </div>
                                    <input type="radio" name="correct_answer" value="1" required @if(Input::old('correct_answer') == 1) checked @endif/> {{ trans('admin/assessment.mark_as_correct_answer') }}
                                </div>
                                <div class="col-md-8 col-lg-8">
                                    {!! $errors->first('answer.1', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                                @if(empty(Input::old('rationale.1')))
                                <div class="col-md-8 col-lg-8">
                                    <a href="javascript:;" class="rationale" data-id="rationale_box_1">
                                        <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button>{{ trans('admin/assessment.add_rationale') }}
                                    </a>
                                </div>
                                @endif
                            </div>
                        </div>

                        <div class="form-group" id="rationale_box_1" style="display: {{ (!empty(Input::old("rationale.1")))? "block" : "none" }}">
                            <label for="rationale[1]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} 2 {{ trans('admin/assessment.rationale') }}</label>
                            <div class="col-md-10 col-lg-10 controls">
                                <div class="col-md-8 col-lg-8 rich-text-content">
                                    <textarea name="rationale[1]" id="rationale-1" placeholder="Please type the rationale" class="form-control">{{ Input::old('rationale.1') }}</textarea>
                                </div>
                                <div class="col-md-4 col-lg-4">
                                    <div class="editor-media">
                                        <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="rationale-1">
                                            <i class="fa fa-video-camera"></i>
                                            <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                        </button>
                                    </div>
                                </div>                              
                            </div>
                        </div>

                        @if(Input::old("choice_count") > 2)
                            @for($i=2;$i<Input::old("choice_count");$i++)

                            <div class="form-group">
                                <label for="answer[{{ $i }}]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} {{ $i + 1 }}</label>
                                <div class="col-md-10 col-lg-10 controls">
                                    <div class="col-md-8 col-lg-8 rich-text-content">
                                        <textarea name="answer[{{ $i }}]" id="answer-{{ $i }}" placeholder="Please type the answer" class="form-control">{{ Input::old("answer.$i") }}</textarea>
                                    </div>
                                    <div class="col-md-4 col-lg-4">
                                        <div class="editor-media">
                                            <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="answer[{{ $i }}]">
                                                <i class="fa fa-video-camera"></i>
                                                <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                            </button>
                                        </div>
                                        <input type="radio" name="correct_answer" value="{{ $i }}" required @if(Input::old('correct_answer') == $i) checked @endif/> {{ trans('admin/assessment.mark_as_correct_answer') }}
                                    </div>
                                    <div class="col-md-8 col-lg-8">
                                        {!! $errors->first("answer.$i", "<span class=\"help-inline\" style=\"color:#f00\">:message</span>") !!}
                                    </div>
                                    @if(empty(Input::old("rationale.$i")))
                                    <div class="col-md-8 col-lg-8">
                                        <a href="javascript:;" class="rationale" data-id="rationale_box_{{ $i }}">
                                            <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button>{{ trans('admin/assessment.add_rationale') }}
                                        </a>
                                    </div>
                                    @endif
                                </div>
                            </div>


                            <div class="form-group" id="rationale_box_{{ $i }}" style="display: {{ (!empty(Input::old("rationale.$i")))? "block" : "none" }}">
                                <label for="rationale[{{ $i }}]" class="col-md-2 col-lg-2 control-label">{{ trans('admin/assessment.choice') }} {{ $i+1 }} {{ trans('admin/assessment.rationale') }}</label>
                                <div class="col-md-10 col-lg-10 controls">
                                    <div class="col-md-8 col-lg-8 rich-text-content">
                                        <textarea name="rationale[{{ $i }}]" id="rationale-{{ $i }}" placeholder="Please type the rationale" class="form-control">{{ Input::old("rationale.$i") }}</textarea>
                                    </div>
                                    <div class="col-md-4 col-lg-4">
                                        <div class="editor-media">
                                            <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="rationale-{{ $i }}">
                                                <i class="fa fa-video-camera"></i>
                                                <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                            </button>
                                        </div>
                                    </div>                              
                                </div>
                            </div>
                            @endfor
                        @endif
                        <div id="choice_div" class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label"></label>
                            <div class="col-lg-10 controls">
                                <input type="hidden" id="choice_count" name="choice_count" value="{{ Input::old('choice_count', 2) }}">
                                <a href="javascript:;" id="add-choice">
                                    <button class="btn btn-circle btn-success btn-xs" type="button"><i class="fa fa-plus"></i></button> {{ trans('admin/assessment.add_one_more_choice') }}  
                                </a>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-5 col-lg-5">
                                    <label for="difficulty_level" class="col-md-offset-1 col-lg-offset-1 col-md-4 col-lg-4 control-label">{{ trans('admin/assessment.difficulty_level') }} <span class="red">*</span></label>
                                    <div class="col-md-7 col-lg-7 controls">
                                        <select name="difficulty_level" class="form-control chosen">
                                            <option value="easy" @if(Input::old('difficulty_level') == 'easy') selected @endif>{{ trans('admin/assessment.easy') }}</option>
                                            <option value="medium" @if(Input::old('difficulty_level') == 'medium') selected @endif>{{ trans('admin/assessment.medium') }}</option>
                                            <option value="difficult" @if(Input::old('difficulty_level') == 'difficult') selected @endif>{{ trans('admin/assessment.difficult') }}</option>
                                        </select>
                                        {!! $errors->first('difficulty_level', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="col-md-5 col-lg-5">
                                    <label for="default_mark" class="col-sm-4 col-lg-4 control-label">{{ trans('admin/assessment.default_mark') }} <span class="red">*</span></label>
                                    <div class="col-sm-7 col-lg-7 controls">
                                        <input type="text" name="default_mark" class="form-control" value="{{ Input::old('default_mark', 1) }}" data-toggle="tooltip" data-placement="top" title="If default mark which you are providing is less than 1, System will consider default mark as 1.">
                                        {!! $errors->first('default_mark', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <div class="row">
                                <div class="col-md-5 col-lg-5">
                                    <label for="question_bank" class="col-md-offset-1 col-lg-offset-1 col-sm-4 col-lg-4 control-label">{{ trans('admin/assessment.question_bank') }} <span class="red">*</span></label>
                                    <div class="col-sm-7 col-lg-7 controls">
                                        <select name="question_bank" class="form-control chosen">
                                <?php   $selectedQBID = null;
                                        if(!empty(Input::old('question_bank')))
                                            $selectedQBID = Input::old('question_bank');
                                        elseif(Input::has('qb'))
                                            $selectedQBID = Input::get('qb');
                                ?>
                                            <option value="null">{{ trans('admin/assessment.select_question_bank') }}</option>
                                            @foreach ($questionbank as $qb)
                                            <option value="{{ $qb->question_bank_id }}" {{ (isset($selectedQBID)? (($selectedQBID == $qb->question_bank_id)? "selected" : "") : ((isset($qb->default) && ($qb->default)) ? "selected" : "")) }}>{{ $qb->question_bank_name }}</option>
                                            @endforeach
                                        </select>
                                        {!! $errors->first('question_bank', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="col-md-5 col-lg-5">
                                    <label for="shuffle_answers" class="col-md-4 col-lg-4 control-label">{{ trans('admin/assessment.shuffle_answers') }}</label>
                                    <div class="col-md-7 col-lg-7 controls">
                                        <input type="checkbox" name="shuffle_answers" @if(Input::old('shuffle_answers') == 'on') checked @endif></input>
                                        {!! $errors->first('shuffle_answers', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keywords_tags') }}</label>
                            <div class="col-sm-9 col-lg-7 controls">
                                <input type="text" name="keywords" class="form-control tags" value="{{ Input::old('keywords') }}">
                                {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>

                        @if(!empty(Input::old('editor_images')))
                        @foreach(Input::old('editor_images') as $image)
                            <input type="hidden" name="editor_images[]" value={{ $image }}>
                        @endforeach
                        @endif

                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" name="submit" value="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
                               <button type="submit" name="draft" value="draft" class="btn btn-info" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save_as_draft') }}</button>
                               <a href="{{ url(Input::get('return', '/cp/assessment/list-questionbank')) }}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
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
        $(document).ready(function(){
            $("input.tags").tagsinput({              
                confirmKeys:[13, 32, 44] //-- Only enter, spacebar and semicolon
            });
        });

        CKEDITOR.inline("question_text",{customConfig: $configPath});
        CKEDITOR.inline("answer-0",{customConfig: $configPath});
        CKEDITOR.inline("rationale-0",{customConfig: $configPath});
        CKEDITOR.inline("answer-1",{customConfig: $configPath});
        CKEDITOR.inline("rationale-1",{customConfig: $configPath});

        @if(Input::old("choice_count") > 2)

        @for($i=2;$i<Input::old("choice_count");$i++)
        CKEDITOR.inline("answer-{{ $i }}",{customConfig: $configPath});
        CKEDITOR.inline("rationale-{{ $i }}",{customConfig: $configPath});
        @endfor

        @endif

        $(document).ready(function(){
            $(document).on('click', '.rationale', function(e){
                e.preventDefault();
                $(this).hide();
                $('#'+$(this).data('id')).slideDown({ duration : 400 });
            });
        });
        
    </script>  
    <?php
    $mathml_editor = \App\Model\SiteSetting::module('MathML', 'mathml_editor');
    ?>
    @if($mathml_editor && $mathml_editor == 'on')
    <script type="text/javascript">
        CKEDITOR.config.extraPlugins += (CKEDITOR.config.extraPlugins.length == 0 ? '' : ',') + 'ckeditor_wiris';
    </script>
    @endif
    @include("admin/theme/assessment/media_embed", ["from" => "question", "media_types" => ["image", "audio", "video"]])
@stop
