@section('content')
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                    <!-- <h3 style="color:black"><i class="fa fa-file"></i> Add section</h3> -->
                </div>
                <div class="box-content">
                    <form action="#" class="form-horizontal form-bordered form-row-stripped" method="post" accept-Charset="UTF-8">
                        <div class="form-group">
                            <label for="section_name" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.section_name') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="section_name" class="form-control" value="{{ Input::old('section_name') }}" >
                                {!! $errors->first('section_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="section_description" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.section_description') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="section_description" rows="5" id="addquiz" class="form-control ckeditor">{!! Input::old('section_description') !!}</textarea>
                                {!! $errors->first('section_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keywords_tags') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="keywords" class="form-control tags" value="{{ Input::old('keywords') }}">
                                {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.no_of_questions') }}<span class="red">*</span></label>
                            <div class="col-sm- col-lg-1 controls">
                                <input type="text" class="col-sm-3 col-lg-1 form-control" name="no_of_question"  value="{{ Input::old('keywords') }}">
                                {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-9 controls">
                                <a href="{{URL::to('/cp/section/list-section/'.$slug)}}"><button type="button" class="btn">{{ trans('admin/assessment.select_question') }}</button></a>
                                <a href="{{URL::to('/cp/section/list-section/'.$slug)}}"><button type="button" class="btn">{{ trans('admin/assessment.add_new_question') }}</button></a>
                                <a href="{{URL::to('/cp/section/list-section/'.$slug)}}"><button type="button" class="btn">{{ trans('admin/assessment.import_question') }}</button></a>
                                <a href="{{URL::to('/cp/section/list-section/'.$slug)}}"><button type="button" class="btn">{{ trans('admin/assessment.use_query_builder') }}</button></a>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.total_marks') }}<span class="red">*</span></label>
                            <div class="col-sm- col-lg-1 controls">
                                <input type="text" class="col-sm-3 col-lg-1 form-control" name="no_of_question"  value="{{ Input::old('keywords') }}" readonly="true">
                                {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <!-- <div class="form-group">
                            <label for="attempts" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.no_of_attempts') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-3 controls">
                                <select name="attempts" class="form-control chosen">
                                    <option value="0" {{ (Input::old('attempts') == "0") ? 'selected' : '' }}>{{ trans('admin/assessment.no_attempt_limit') }}</option>
                                    @for ($i = 1; $i <= 12; $i++)
                                        <option value="{{ $i }}" {{ (Input::old('attempts') == "$i" ) ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                {!! $errors->first('attempts', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="negative_mark" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.negative_mark') }}</label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <div class="col-sm-9 col-lg-6 ">
                                    {{ trans('admin/assessment.for_attempted_question') }}<input type="text" name="negative_mark_attempted_question" class="form-control" value="{{ Input::old('negative_mark_attempted_question') }}">
                                </div>
                                <div class="col-sm-9 col-lg-6">
                                    {{ trans('admin/assessment.for_un_attempted_question') }}<input type="text" name="negative_mark_un_attempted_question" class="form-control" value="{{ Input::old('negative_mark_un_attempted_question') }}">
                                </div>
                                <span class="help-inline" id="filetypehint">Value shoud be in percentage.</span></br>
                                    {!! $errors->first('negative_mark_un_attempted_question', '<span class="help-inline" style="color:#f00">:message</span>') !!}&nbsp;&nbsp;&nbsp;&nbsp;
                                    {!! $errors->first('negative_mark_attempted_question', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="time_limit_ques" class="col-sm-3 col-lg-2 control-label"><?php echo trans('admin/assessment.time_limit_question'); ?></label>
                            <div class="col-sm-9 col-lg-3 controls">
                                <input id ='time_limit_ques_id' type="checkbox" name="time_limit_ques" @if(Input::old('time_limit_ques') == 'on') checked @endif></input>
                                {!! $errors->first('time_limit_ques', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="question_per_page" class="col-sm-3 col-lg-2 control-label">Question Per Page <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-3 controls">
                                <select name="question_per_page" class="form-control chosen" id='question_per_page_id'>
                                    @for ($i = 1; $i <= 10; $i++)
                                        <option value="{{ $i }}" {{ (Input::old('question_per_page') == "$i" ) ? 'selected' : '' }}>{{ $i }}</option>
                                    @endfor
                                </select>
                                {!! $errors->first('question_per_page', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="practice_quiz" class="col-sm-3 col-lg-2 control-label">Mark as Practice section</label>
                            <div class="col-sm-9 col-lg-3 controls">
                                <input type="checkbox" name="practice_quiz" @if(Input::old('practice_quiz') == 'on') checked @endif></input>
                                {!! $errors->first('practice_quiz', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.schedule_date') }} <span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="start_date" class="form-control input-date" style="cursor: pointer" value="{{ Input::old('start_date', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y')) }}" readonly="readonly">
                                </div>
                                {!! $errors->first('start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="start_time" onclick="$(this).prev().click()" class="form-control input-time" style="cursor: pointer" value="{{ Input::old('start_time', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'H:i')) }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="start_time_reset" class="btn" ><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('start_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.expiry_date') }}&nbsp;<span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="end_date" class="form-control input-date" style="cursor: pointer" value="{{ Input::old('end_date') }}" readonly="readonly">
                                </div>
                                {!! $errors->first('end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="end_time" onclick="$(this).prev().click()" class="form-control input-time" style="cursor: pointer" value="{{ Input::old('end_time') }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="end_time_reset" class="btn" ><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('end_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.duration') }}&nbsp;<span class="red">*</span></label>
                            <div class="col-sm-5 col-lg-3 controls">
                                <div class="input-group">
                                    <a class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></a>
                                    <input type="text" name="duration" class="form-control input-time" onclick="$(this).prev().focus()" style="cursor: pointer" value="{{ Input::old('duration') }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="duration_reset" class="btn" ><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('duration', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.review_options') }}</label>
                            <div class="col-sm-5 col-lg-3 controls">
                                <div class="input-group">
                                    <input type="checkbox" name="review_the_attempt" @if(Input::old('review_the_attempt') == 'on') checked @endif @if(Input::old('practice_quiz') == 'on') readOnly @endif></input> {{ trans('admin/assessment.the_attempt') }}
                                    <br>
                                    <input type="checkbox" name="review_whether_correct" @if(Input::old('review_whether_correct') == 'on') checked @endif @if(Input::old('practice_quiz') == 'on') readOnly @endif></input> {{ trans('admin/assessment.whether_correct') }}
                                    <br>
                                    <input class="review" type="checkbox" name="review_marks" @if(Input::old('review_marks') == 'on') checked @endif></input> Marks
                                    <br>
                                    <input class="review" type="checkbox" name="review_rationale" @if(Input::old('review_rationale') == 'on') checked @endif></input> Rationale
                                    <br>
                                    <input class="review" type="checkbox" name="review_correct_answer" @if(Input::old('review_correct_answer') == 'on') checked @endif></input> {{ trans('admin/assessment.correct_answer') }}
                                </div>
                            </div>
                        </div> -->
                        @if(!empty(Input::old('editor_images')))
                        @foreach(Input::old('editor_images') as $image)
                            <input type="hidden" name="editor_images[]" value={{ $image }}>
                        @endforeach
                        @endif
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
                               <a href="{{URL::to('/cp/section/list-section/'.$slug)}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('.input-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy"
            });
            $('.input-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            });
            $('input.tags').tagsInput({
                width: "auto"
            });
            $('#start_time_reset').click(function(e) {
                e.preventDefault();
                $('input[name=start_date]').val('');
                $('input[name=start_time]').val('');
            });
            $('#end_time_reset').click(function(e) {
                e.preventDefault();
                $('input[name=end_date]').val('');
                $('input[name=end_time]').val('');
            });
            $('#duration_reset').click(function(e) {
                e.preventDefault();
                $('input[name=duration]').val('');
            });

            $("input[name=practice_quiz]").change(function(){
                var reviewOptions = $("input[name=review_the_attempt], input[name=review_whether_correct], .review");
                var mandatoryLabels = $("label[for=end_date] span.red, label[for=duration] span.red");
                if($(this).prop("checked"))
                {
                    reviewOptions.prop("checked", true).not(".review").prop({ readOnly : true });
                    mandatoryLabels.css({ display : "none" });
                }
                else
                {
                    reviewOptions.prop("checked", false).prop({ readOnly : false });
                    mandatoryLabels.css({ display : "inline" });
                }
            });

            $("input[name=review_the_attempt]").change(function(){
                if(!$(this).prop("checked"))
                    $("input[name=review_whether_correct], .review").prop("checked", false);
            });

            $("input[name=review_whether_correct]").change(function(){
                if($(this).prop("checked"))
                    $("input[name=review_the_attempt]").prop({ checked : true });
                else
                    $(".review").prop("checked", false);
            });

            $(".review").change(function(){
                $("input[name=review_the_attempt], input[name=review_whether_correct]").prop({ checked : true });
            });
        });

        CKEDITOR.replace( 'addquiz', {
            filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
        });
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;
    </script>
@stop