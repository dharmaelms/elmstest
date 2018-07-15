@section('content')
    @if (Session::get('success'))
        <div class="alert alert-success" id="alert-success">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Success!</strong> -->
            {{ Session::get('success') }}
        </div>
        <?php Session::forget('success'); ?>
    @endif
    @if (Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Error!</strong> -->
            {{ Session::get('error') }}
        </div>
        <?php Session::forget('error'); ?>
    @endif
<?php
    $sec_id_hold = 0;
    $title = '';
    $description = '' ;
    $keywords = '';
    $no_of_questions = '0';
    $total_marks = '0';
    $sec_ques_ids = '';
    $cut_off = '';

    if(!empty(Input::old('sec_id_hold')) ||  isset($section)){
        $sec_id_hold = Input::old('sec_id_hold') ? Input::old('sec_id_hold') : $section['section_id'] ;
        if(!empty(Input::old('title')))
        {
            $title=Input::old('title');
        }
        else
        {
            $title = isset($section['title']) ? $section['title'] : '' ;
        }
        if(!empty(Input::old('description')))
        {
            $description=Input::old('description');
        }
        else
        {
            $description = isset($section['description']) ? $section['description'] : '';
        }

        if(!empty(Input::old('keywords')))
        {
            $keywords=Input::old('keywords');
        }
        else
        {
            $keywords = isset($section['keywords']) ? $section['keywords'] : '';
        }

        if(!empty(Input::old('cut_off')))
        {
            $cut_off=Input::old('cut_off');
        }
        else
        {
            $cut_off = isset($section['cut_off']) ? $section['cut_off'] : '';
        }


        if(!empty(Input::old('no_of_questions')))
        {
            $no_of_questions=Input::old('no_of_questions');
        }
        else
        {
            $no_of_questions = isset($section['no_of_questions']) ? $section['no_of_questions'] : '0';
        }

        if(!empty(Input::old('total_marks')))
        {
            $total_marks=Input::old('total_marks');
        }
        else
        {
            $total_marks = isset($section['total_marks']) ? $section['total_marks'] : '0';
        }


        if(!empty(Input::old('sec_ques_ids')))
        {
            $sec_ques_ids=Input::old('sec_ques_ids');
        }
        else
        {
            $sec_ques_ids = isset($section['questions']) ? implode(',',$section['questions']) : '';
        }
    }else{
        if(!empty(Input::old('title')))
        {
            $title=Input::old('title');
        }
        if(!empty(Input::old('description')))
        {
            $description=Input::old('description');
        }
        if(!empty(Input::old('cut_off')))
        {
            $cut_off=Input::old('cut_off');
        }
    }
?>
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
                    <form id ="form_id" action="#" class="form-horizontal form-bordered form-row-stripped" method="post" accept-Charset="UTF-8">
                        <input name="quiz_id" value="{{$slug}}" type="hidden">
                        <input type="hidden" name="sec_id_hold" value="{{$sec_id_hold}}">
                        <input type="hidden" name="sec_ques_ids" value="{{$sec_ques_ids}}">
                        <input id ="next_page_id" type="hidden" name="next_page" value="">
                        <div class="form-group">
                            <label for="title" class="col-sm-3 col-lg-2 control-label"><?php echo trans('admin/assessment.section_name');?> <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input id="title_id" type="text" name="title" class="form-control" value="{{ $title }}" >
                                {!! $errors->first('title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-sm-3 col-lg-2 control-label"><?php echo trans('admin/assessment.section_instruction');?></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="description" rows="5" id="addquiz" class="form-control ckeditor">{!! $description !!}</textarea>
                                {!! $errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keywords" class="col-sm-3 col-lg-2 control-label"><?php echo trans('admin/assessment.keywords_tags');?></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="keywords" class="form-control tags" value="{{ $keywords }}">
                                {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        @if($timed_sections)
                            <?php $time = '00:00';?>
                            @if(isset($section['duration']) && $section['duration'] > 0)
                                <?php $time = gmdate('H:i', $section['duration']*60); ?>
                            @endif
                            @if(!is_null(Input::old('duration')))
                                <?php $time =  (Input::old('duration'));?>
                            @endif
                            <div class="form-group">
                                <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.duration') }}&nbsp;<span class="red">*</span>
                                    <div>
                                        <span class="help-inline">hh:mm &nbsp;&nbsp;</span>
                                    </div>    
                                </label>
                                <div class="col-sm-5 col-lg-3 controls">
                                    <div class="input-group">
                                        <a class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></a>
                                        <input type="text" name="duration" class="form-control input-time" onclick="$(this).prev().click()" style="cursor: pointer" value="{{ $time }}" readonly="readonly">
                                        <span class="input-group-btn">
                                            <button id="duration_reset" class="btn" ><i class="fa fa-undo"></i></button>
                                        </span>
                                    </div>
                                    @if($duration_flag)
                                    <p class="note">{!! trans('admin/assessment.section_duration_note').Helpers::secondsToTimeString($remaining_time*60).' ('.gmdate('H:i', $remaining_time*60).')' !!}</p>
                                    @endif
                                    {!! $errors->first('duration', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                            </div>
                        @endif
                        @if(isset($quiz_type) && $quiz_type != "QUESTION_GENERATOR")
                        <div class="form-group">
                            <div class="col-sm-3 col-lg-2">
                                 <label for="question_per_page" class="pull-right col-sm-6 col-lg-12 control-label">{{ trans('admin/assessment.cut_off') }} <br/>
                                    <span class="help-inline pull-right">
                                         {{ isset($quiz->cut_off_format) ? trans('admin/assessment.'.strtolower($quiz->cut_off_format)) : '' }}
                                    </span>  
                                 </label>
                            </div>
                            <div class="col-sm-6 col-lg-6 controls">
                                <input type="text" name="cut_off" class="form-control" value="{{ $cut_off }}" >
                                {!! $errors->first('cut_off', '<span class="row help-inline" style="color:#f00">:message</span>') !!}
                            </div>                           
                        </div>
                        @endif
                        <div class="form-group">
                            <label for="no_of_questions" class="col-sm-3 col-lg-2 control-label"><?php echo trans('admin/assessment.no_of_questions');?></label>
                            <div class="col-sm- col-lg-1 controls">
                                <input type="text" class="col-sm-3 col-lg-1 form-control" name="no_of_questions"  value="{{ $no_of_questions }}" readonly="true">
                                {!! $errors->first('no_of_questions', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-9 controls" id='question_added_id' style="display:none">
                                <a id ="add_ques_bank_id"><button type="button" class="btn"><?php echo trans('admin/assessment.manage_question');?></button></a>
                            </div>
                        </div>
                        <div class="form-group ">
                            <label for="total_marks" class="col-sm-3 col-lg-2 control-label"><?php echo trans('admin/assessment.total_marks');?></label>
                            <div class="col-sm- col-lg-1 controls">
                                <input type="text" class="col-sm-3 col-lg-1 form-control" name="total_marks"  value="{{ $total_marks }}" readonly="true">
                                {!! $errors->first('total_marks', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                         @if(!empty(Input::old('editor_images')))
                        @foreach(Input::old('editor_images') as $image)
                            <input type="hidden" name="editor_images[]" value={{ $image }}>
                        @endforeach
                        @endif
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <!-- <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> Save</button> -->
                               <a id ="form_submit_id" style="display:none"><button type="button" class="btn btn-success"><i class="fa fa-check"></i>{{ trans('admin/assessment.save') }}</button></a>
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
        $(function() {
            @if($duration_flag)
                var remainingTime = "{{ $remaining_time }}";
                var maxTime = Math.floor(remainingTime/60)+':'+remainingTime%60;
            @endif
            $('.input-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy"
            });
            $('.input-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
            }).
            on('changeTime.timepicker', function(e) {
                if(typeof remainingTime !=='undefined') {
                    currentTime = e.time.hours * 60 + e.time.minutes;
                    if(currentTime > remainingTime) {
                        $('.input-time').timepicker('setTime', maxTime);
                    } 
                }
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
                $('input[name=duration]').val('0:00');
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
    <script type="text/javascript">
        var section_status = 'no';
        var section_id = 0;
        $('#add_ques_bank_id').click(function(){
            $('#next_page_id').val('add_ques_bank');
            $('#form_id').submit();
        });
        $('#form_submit_id').click(function(){
            $('#next_page_id').val('list section');
            $('#form_id').submit();
        });

        <?php
            if( !empty(Input::old('sec_id_hold'))  || isset($section)){
                ?>
                section_status="yes";
                $("#question_added_id").show();
                $("#form_submit_id").show();
                section_id = <?php !empty(Input::old('sec_id_hold')) ? Input::old('sec_id_hold') : (isset($section['section_id']) ? $section['section_id'] : '');

            }
        ?>

        $("#title_id").focusout(function(){
                if(section_status=='yes'){
                    return false;
                }
                // console.log("on the way");
                var title=$.trim($(this).val());

                if(title.length >= 1 || title.length < 512) {
                    $("#validate_client").hide();
                    if(title !="" && section_status=='no')
                    {
                        $.ajax({
                            type: "GET",
                            url: '{{URL::to('/cp/section/create-section/'.$slug)}}',
                            data: 'title='+title
                        })
                        .done(function( response ) {
                            if(response.flag == "success"){
                                section_status="yes";
                                $('#section_exist').hide();
                               /* $('<div class="alert alert-success" id="alert-success"><button class="close" data-dismiss="alert">×</button> Section successfully Created with Pending Status</div>').insertAfter($('.page-title'));*/
                                section_id=response.sec_id;
                                $('input[name=sec_id_hold]').val(section_id);
                                $("#question_added_id").show();
                                $("#form_submit_id").show();
                            }else if(response.flag =="duplicate"){
                                $('<div class="alert alert-danger" id = "section_exist"><button class="close" data-dismiss="alert">×</button> Section title exist </div>').insertAfter($('.page-title'));
                            }else if(response.flag =="attempted"){
                                $('<div class="alert alert-danger" id = "section_exist"><button class="close" data-dismiss="alert">×</button> Sections not able to create for attempted quiz. </div>').insertAfter($('.page-title'));
                            }
                            else{
                                $('#section_exist').hide();
                                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>  <?php echo trans('admin/manageweb.server_error');?> </div>').insertAfter($('.page-title'));
                            }
                        })
                        .fail(function() {
                            $('#section_exist').hide();
                            alert("error");
                             permision_chk='no';
                        })
                    }else{
                        permision_chk='no';
                    }
                }//console.log(permision_chk);

            });
        $("input[name=cut_off]").on('keypress', function(event){
            var char = String.fromCharCode(event.which)
            if ( !char.match(/^[0-9]*$/) || ($("input[name=cut_off]").val().length > 3)){
                event.preventDefault();
                return false;
            }
        });
    </script>
@stop