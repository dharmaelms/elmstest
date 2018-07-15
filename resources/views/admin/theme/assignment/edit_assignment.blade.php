@section('content')
    @if ( Session::get('success') )
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Success!</strong> -->
            {{ Session::get('success') }}
        </div>
        <?php Session::forget('success'); ?>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Error!</strong> -->
            {{ Session::get('error') }}
        </div>
        <?php Session::forget('error'); ?>
    @endif
    <style type="text/css">
    .cke_contents {
        height: auto !important;
    }
    </style>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    <form action="{{ URL::route('put-edit-assignment') }}" class="form-horizontal form-bordered form-row-stripped" method="post" id="survey_form" onsubmit="formSubmit()">
                        {{ method_field('PUT') }}
                        <div class="form-group">
                            <input name="_a" type="hidden" value="{{ $assignment->id }}">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.assignment_title')}}<span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <?php
                                if (Input::old('assignment_title')) {
                                    $assignment_title = Input::old('assignment_title');
                                } elseif (isset($assignment->name)) {
                                    $assignment_title = $assignment->name;
                                }
                                ?>
                                <input type="text" name="assignment_title" id="assignment_title" class="form-control" value="{{$assignment_title}}">
                                {!! $errors->first('assignment_title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assignment.assignment_description') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <?php
                                if (Input::old('assignment_description')) {
                                    $assignment_description = Input::old('assignment_description');
                                } elseif (isset($assignment->description)) {
                                    $assignment_description = $assignment->description;
                                }
                                ?>
                                <textarea name="assignment_description" rows="5" class="form-control ckeditor">{!! $assignment_description !!}</textarea>
                                {!! $errors->first('assignment_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.start_date')}} <span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="start_date" class="form-control assignment-start-date" style="cursor: pointer" value="{{ Input::old('start_date', Timezone::convertFromUTC('@'.$assignment->start_time, Auth::user()->timezone, 'd-m-Y')) }}" readonly="readonly">
                                </div>
                                {!! $errors->first('start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="start_time" onclick="$(this).prev().click()" class="form-control assignment-start-time" style="cursor: pointer" value="{{ Input::old('start_time',Timezone::convertFromUTC('@'.$assignment->start_time, Auth::user()->timezone, 'H:i')) }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="start_time_reset" class="btn"><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('start_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.end_date')}}&nbsp;<span class="red" style="display: inline;">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="end_date" class="form-control assignment-end-date" style="cursor: pointer" value="{{ Input::old('end_date', Timezone::convertFromUTC('@'.$assignment->end_time, Auth::user()->timezone, 'd-m-Y')) }}" readonly="readonly">
                                </div>
                                {!! $errors->first('end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="end_time" onclick="$(this).prev().click()" class="form-control assignment-end-time" style="cursor: pointer" value="{{ Input::old('end_time',Timezone::convertFromUTC('@'.$assignment->end_time, Auth::user()->timezone, 'H:i')) }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="end_time_reset" class="btn"><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('end_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.cutoff_date')}}&nbsp;<span class="red" style="display: inline;">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="cutoff_date" class="form-control assignment-cutoff-date" style="cursor: pointer" value="{{ Input::old('cutoff_date', Timezone::convertFromUTC('@'.$assignment->cutoff_time, Auth::user()->timezone, 'd-m-Y')) }}" readonly="readonly">
                                </div>
                                {!! $errors->first('cutoff_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="cutoff_time" onclick="$(this).prev().click()" class="form-control assignment-cutoff-time" style="cursor: pointer" value="{{ Input::old('cutoff_time',Timezone::convertFromUTC('@'.$assignment->cutoff_time, Auth::user()->timezone, 'H:i')) }}" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="cutoff_time_reset" class="btn"><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('cutoff_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <a style="font-size: 16px"><i class="fa fa-question-circle" style="padding-top: 20px" title="{{ trans('admin/assignment.cutoff_info') }}"></i></a>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assignment.submission_type') }}</label>
                            <div class="col-sm-5 col-lg-3 controls submission_type">
                                <div class="input-group">
                                    <?php
                                    if (Input::old('submission_type')) {
                                        $submission_type = Input::old('submission_type');
                                    } elseif (isset($assignment->submission_type)) {
                                        $submission_type = $assignment->submission_type;
                                    }
                                ?>
                                    <label class="radio-inline">
                                        <input type="radio" @if($submission_type == "online_text") checked @endif value="online_text" id="online_text" name="submission_type">{{ trans('admin/assignment.inline_text') }}
                                    </label>&nbsp;&nbsp;&nbsp;
                                    <label class="radio-inline">
                                        <input type="radio" value="file_submission" @if($submission_type == "file_submission") checked @endif id="file_submission" name="submission_type">{{ trans('admin/assignment.file_submission') }}
                                    </label>
                                </div>
                            </div>
                        </div>
                            <div class="form-group">
                                <div class="upload-file">
                                    <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.max_files')}}</label>
                                    <div class="col-sm-9 col-lg-6 controls">
                                        <?php
                                        if (Input::old('max_files')) {
                                            $max_files = Input::old('max_files');
                                        } elseif (isset($assignment->max_no_file_allowed)) {
                                            $max_files = $assignment->max_no_file_allowed;
                                        }
                                        ?>
                                        <input type="number" name="max_files" id="max_files" class="form-control" value="{{ $max_files }}" min="1" max="10" style="width: 30%;">
                                        {!! $errors->first('max_files', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                            </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.template_file')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <div class="fileupload fileupload-new" data-provides="fileupload">
                                    <?php
                                        if (Input::old('template_file_id')) {
                                            $template_file_id = Input::old('template_file_id');
                                        } elseif (isset($assignment->template_file_id)) {
                                            $template_file_id = $assignment->template_file_id;
                                        }

                                        if (Input::old('template_file_name')) {
                                            $template_file_name = Input::old('template_file_name');
                                        } elseif (isset($assignment->template_file_name)) {
                                            $template_file_name = $assignment->template_file_name;
                                        }
                                        ?> 

                                    <div class="input-group template_uploads">
                                        <input type="hidden" class="file-input" name="template_file_id" id="template_file_id" value="{{ $template_file_id }}"> 
                                        <input type="hidden" class="file-input" name="template_file_name" id="template_file_name" value="{{ $template_file_name }}"> 
                                        <i class="fa fa-file fileupload-exists"></i> 
                                        <?php
                                        if (Input::old('template_file_name')) {
                                            $template_file_name = Input::old('template_file_name');
                                        } elseif (isset($assignment->template_file_name)) {
                                            $template_file_name = $assignment->template_file_name;
                                        }
                                        ?> 
                                        <input class="fileupload-preview form-control uneditable-input" type="text" value="{{ $template_file_name }}" disabled>
                                        <div class="input-group-btn">
                                            <a class="btn bun-default btn-file" style="margin: 0px 10px 0px 5px;padding:8px;color: #393939;">
                                                <span>{{trans('admin/assignment.upload_new')}}</span> 
                                                <input type="file" class="file-input" name="template_file" id="upload" data-url="{{URL::to('cp/dams/add-media?view=iframe&filter=document&from=add-assignment&select=radio')}}" data-name="Upload New"/>
                                            </a>
                                            <a class="btn bun-default btn-file from-library" style="padding:8px;color: #393939;margin-right:8px;">
                                                <span>{{trans('admin/assignment.from_library')}}</span>
                                                <input type="file" class="file-input" name="template_file" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&from=add-assignment&filter=document&select=radio')}}" data-name="From Library"/>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                                {!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.grade')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <?php
                                if (Input::old('grade')) {
                                    $grade = Input::old('grade');
                                } elseif (isset($assignment->grade)) {
                                    $grade = $assignment->grade;
                                }
                                ?>
                                <input type="number" min="1" max="100"  name="grade" id="grade" class="form-control" value="{{ $grade }}" style="width: 30%;">
                                {!! $errors->first('grade', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.grade_cutoff')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <?php
                                if (Input::old('grade_cutoff')) {
                                    $grade_cutoff = Input::old('grade_cutoff');
                                } elseif (isset($assignment->grade_cutoff)) {
                                    $grade_cutoff = $assignment->grade_cutoff;
                                }
                                ?>
                                <input type="number" min="0" name="grade_cutoff" id="grade_cutoff" class="form-control" value="{{ $grade_cutoff }}" style="width: 30%;">
                                {!! $errors->first('grade_cutoff', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{trans('admin/assignment.save')}}</button>
                               <a href="{{URL::route('get-list')}}"><button type="button" class="btn">{{trans('admin/assignment.cancel')}}</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3 class="modal-header-title" >
                                        <i class="icon-file"></i>
                                            {{trans('admin/program.view_media_details')}}
                                    </h3>                                                
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body" style="padding-top: 0px;">
                    ...
                </div>
                <div class="modal-footer">
                 <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/program.assign')}}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/program.close')}}</a>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            if($("input[name='submission_type']:checked").val() == "online_text"){
                $(".upload-file").hide();     
            }

            $('#assignment_title,.assignment-start-date,.assignment-end-date,.assignment-start-time,.assignment-end-time,.submission_type').on('keyup keypress', function(e) {
              var keyCode = e.keyCode || e.which;
              if (keyCode === 13) {
                e.preventDefault();
                return false;
              }
            });

            $('#start_time_reset').click(function(e) {
                e.preventDefault();
                $('input[name=start_date]').val('');
                $('input[name=start_time]').val('0:00');
            });

            $('#end_time_reset').click(function(e) {
                e.preventDefault();
                $('input[name=end_date]').val('');
                $('input[name=end_time]').val('0:00');
            });
            $('#cutoff_time_reset').click(function(e) {
                e.preventDefault();
                $('input[name=cutoff_date]').val('');
                $('input[name=cutoff_time]').val('0:00');
            });

            $('.assignment-start-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d'
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('assignment-start-time');
                checkDateAndTime('assignment-start-date', 'assignment-start-time', selectedTime);
            });
            $('.assignment-start-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('assignment-start-date', 'assignment-start-time', selectedTime);
            });

            $('.assignment-end-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d'
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('assignment-end-time');
                checkDateAndTime('assignment-end-date', 'assignment-end-time', selectedTime);
            });
            $('.assignment-end-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('assignment-end-date', 'assignment-end-time', selectedTime);
            });
            $('.assignment-cutoff-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d'
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('assignment-cutoff-time');
                checkDateAndTime('assignment-cutoff-date', 'assignment-cutoff-time', selectedTime);
            });
            $('.assignment-cutoff-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
                defaultTime: false
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('assignment-cutoff-date', 'assignment-cutoff-time', selectedTime);
            });

            function selectTime(time_class_name) {
                var time = $('.'+ time_class_name).val().split(":");
                var hours = parseInt(time[0]);
                var minutes = parseInt(time[1]);
                return (hours * 60) + minutes;
            }
            function checkDateAndTime(date_class_name, time_class_name, selectedTime) {
                var today = new Date(new Date().getFullYear(), new Date().getMonth(), new Date().getDate()).getTime();
                selectedDate = $('.'+ date_class_name).datepicker('getDate').getTime();
                if (today == selectedDate) {
                    now = new Date();
                    currentTime = (now.getHours() * 60) + now.getMinutes();
                    if (selectedTime < currentTime) {
                        $('.'+ time_class_name).timepicker('setTime', Math.floor(currentTime/60)+':'+currentTime%60);
                    }
                }
            }
        });
        function formSubmit() {
            $(".btn-success").attr('disabled', true);
        }
        $("input[name=submission_type]").on("click", function(){
            var submission_type = $(this).val();
            if(submission_type == "online_text"){
                $(".upload-file").slideUp();
            }
            if (submission_type == "file_submission"){
                $("#max_files").val(1);
                $(".upload-file").slideDown();
            }
        });
        $('#selectfromdams, #upload').click(function(e){
            e.preventDefault();
            simpleloader.fadeIn();
            var $this = $(this);
            var $triggermodal = $('#triggermodal');
            var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="500px" style="max-height:500px !important" frameBorder="0"></iframe>');
            $iframeobj.unbind('load').load(function(){
                if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                    $triggermodal.modal('show');
                simpleloader.fadeOut();
            });
            $triggermodal.find('.modal-body').html($iframeobj);
            $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('name'));
            $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                var $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked');
                if($selectedRadio.length){
                    $("#template_file_id").val($selectedRadio.val()); 
                    $("#template_file_name").val($selectedRadio.closest('td').next().html());
                    $(".fileupload-preview").val($selectedRadio.closest('td').next().html());
                    $('#removethumbnail').remove();
                    $('<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/program.remove')}} </button>').insertAfter($('.from-library').val($selectedRadio.val()));
                    $triggermodal.modal('hide');
                }
                else{
                    alert('Please select atleast one entry');
                }
            });
            $(document).on('click','#removethumbnail',function(){ 
                $("#template_file").val(''); 
                $(".fileupload-preview").html('');
                $(".uneditable-input").val('');
                $("#template_file_id").val(''); 
                $("#template_file_name").val('');
                $(this).remove(); 
            }); 
        });
    </script>
@stop