@section('content')
     @if (Session::has('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Error!</strong> -->
            {!! Session::get('error') !!}
        </div>
        <?php Session::forget('error'); ?>
    @endif
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <?php
        $timezone = Auth::user()->timezone;
        if(empty($timezone)) $timezone = Config::get('app.default_timezone');
        $offset = (new DateTimeZone($timezone))->getOffset(new DateTime('now'))/3600;
        $webex_tz = $compare[number_format((float)$offset, 1, '.', '')];
        $webex_tz = $webex_tz[0];
    ?>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Add event</h3> -->
	            </div>
	            <div class="box-content">
                    <form action="#" class="form-horizontal form-bordered form-row-stripped">
                        <div class="form-group">
                            <label for="event_type" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.event_type') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <label class="radio-inline">
                                    <input type="radio" name="event_type" value="live" checked {{(Input::old('event_type') == 'live') ? "checked" : ""}} /> {{ trans('admin/event.live') }}
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="event_type" value="general" {{(Input::old('event_type') == 'general') ? "checked" : ""}} /> {{ trans('admin/event.general') }}
                                </label>
                                {!! $errors->first('event_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                    </form>
                    <form action="#" id="live-single" class="form-horizontal form-bordered form-row-stripped" method="post" accept-Charset="UTF-8" style="display:none; padding-top:10px;">
                        <input type="hidden" name="event_type" value="live">
                        <input type="hidden" name="event_cycle" value="single">
                        @include('admin/theme/event/partials/_add_event', ['editor' => 'editor-live-single'])
                        <div class="form-group">
                            <label for="speakers" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.speakers') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="speakers" class="form-control speakers" value="{{ Input::old('speakers') }}">
                                {!! $errors->first('speakers', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="event_host" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.event_host') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-5 controls">
                                <select name="event_host" class="form-control chosen">
                                    <option>{{ trans('admin/event.select_event_host') }}</option>
                                @if(isset($user) && !empty($user))
                                    @foreach ($user as $u)
                                    @if($u->super_admin!='1')
                                    <option value="{{ $u->uid }}" @if(Input::old('event_host') == $u->uid) selected @endif>{{ ucwords($u->firstname.' '.$u->lastname) }} ( {{$u->email}} )</option>
                                    @endif
                                    @endforeach
                                @endif
                                </select>
                                {!! $errors->first('event_host', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.start_date') }} <span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="start_date" id="datepicker" class="form-control input-date" value="{{ Input::old('start_date', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y')) }}" style="cursor: pointer" readonly="readonly">
                                </div>
                                 <span id="dateid" style="color:#f00"></span>
                                {!! $errors->first('start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="start_time" id="starttime" onclick="$(this).prev().click()" class="form-control live-input-time" value="{{ Input::old('start_time', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'H:i')) }}" style="cursor: pointer" readonly="readonly">
                                </div>
                                {!! $errors->first('start_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="duration" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.duration') }} <span class="red">*</span></label>
                            <div class="col-sm-5 col-lg-3 controls">
                                <div class="input-group">
                                    <a class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></a>
                                    <input type="text" name="duration" id="duration" class="form-control input-duration" data-default-time="1:00" onclick="$(this).prev().click()" style="cursor: pointer" readonly="readonly">
                                </div>
                                {!! $errors->first('duration', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="session_type" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.session_type') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-3 controls">
                                <select name="session_type" class="form-control chosen">
                                    <option value="MC" @if(Input::old('session_type') == 'MC') selected @endif>{{ trans('admin/event.meeting_center') }}</option>
                                    <option value="TC" @if(Input::old('session_type') == 'TC') selected @endif>{{ trans('admin/event.training_center') }}</option>
                                </select>
                                {!! $errors->first('session_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="timezone" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.timezone') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-5 controls">
                                <?php $webex_tz = Input::old('timezone', $webex_tz); ?>
                                <select name="timezone" id="timezone" class="form-control chosen">
                                    @foreach ($tz as $tz_key => $tz_value)
                                    <option value="{{ $tz_key }}" @if($webex_tz == $tz_key) selected @endif>{{ $tz_value }}</option>
                                    @endforeach
                                </select>
                                {!! $errors->first('timezone', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="webex_host" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.webex_host') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-3 controls">
                                <?php $whost = Input::old('webex_host'); ?>
                                <select id="web_host" name="webex_host" class="form-control chosen">
                                    <option>{{ trans('admin/event.select_webex_host') }}</option>
                                    @foreach ($host as $h)
                                    <option value="{{ $h['webex_host_id'] }}" @if($whost == $h['webex_host_id']) selected @endif>{{ $h['name'] }}</option>
                                    @endforeach
                                </select>
                                <span id="host" style="color:#f00"></span>
                                <span class="error_host" style="color:#f00"></span>
                                {!! $errors->first('webex_host', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>

                    <div class="panel-group" id="accordion1">
                       <div class="panel panel-default">
                        <div class="panel-heading">
                          <h4 class="panel-title">
                             <a class="accordion-toggle accordion-btn collapsed" data-toggle="collapse" data-parent="#accordion1" data-target="#collapseOne">
                            <center><button class="btn btn-primary btn-sm" type="button" id="button" ><i class="fa fa-check"></i>{{ trans('admin/event.show_availability') }}</button></center>                      
                            </a>
                          </h4>
                         </div>
                         <div id="collapseOne" class="panel-collapse collapse">
                         </div>
                       </div>
                        <span id="noshedule" style="color:#f00"></span>
                        </div>
                        @if(!empty(Input::old('editor_images')))
                        @foreach(Input::old('editor_images') as $image)
                            <input type="hidden" name="editor_images[]" value={{ $image }}>
                        @endforeach
                        @endif
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/event.save') }}</button>
                               <a href="{{URL::to('/cp/event/')}}"><button type="button" class="btn">{{ trans('admin/event.cancel') }}</button></a>
                            </div>
                        </div>
                    </form>
                    <form action="#" id="general-single" class="form-horizontal form-bordered form-row-stripped" method="post" accept-Charset="UTF-8" style="display:none; padding-top:10px;">
                        <input type="hidden" name="event_type" value="general">
                        <input type="hidden" name="event_cycle" value="single">
                        @include('admin/theme/event/partials/_add_event', ['editor' => 'editor-general-single'])
                        <div class="form-group">
                            <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.start_date') }} <span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="start_date" class="form-control general-input-date" value="{{ Input::old('start_date', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y')) }}" style="cursor: pointer" readonly="readonly">
                                </div>
                                {!! $errors->first('start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="start_time" onclick="$(this).prev().click()" class="form-control input-time" value="{{ Input::old('start_time', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'H:i')) }}" style="cursor: pointer" readonly="readonly">
                                </div>
                                {!! $errors->first('start_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_time" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.end_date') }} <span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="end_date" class="form-control general-input-end-date" value="{{ Input::old('end_date', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y')) }}" style="cursor: pointer" readonly="readonly">
                                </div>
                                {!! $errors->first('end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="end_time" onclick="$(this).prev().click()" class="form-control input-end-time" value="{{ Input::old('end_time', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'H:i')) }}" style="cursor: pointer" readonly="readonly" data-default-time="false">
                                </div>
                                {!! $errors->first('end_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="location" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/event.location') }}</label>
                            <div class="col-sm-9 col-lg-5 controls">
                                <input type="text" name="location" class="form-control" value="{{ Input::old('location') }}" >
                                {!! $errors->first('location', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        @if(!empty(Input::old('editor_images')))
                        @foreach(Input::old('editor_images') as $image)
                            <input type="hidden" name="editor_images[]" value={{ $image }}>
                        @endforeach
                        @endif
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/event.save') }}</button>
                               <a href="{{URL::to('/cp/event/')}}"><button type="button" class="btn">{{ trans('admin/event.cancel') }}</button></a>
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
            var todaySelected = true;
            now = new Date();
            $('.input-duration').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
            });

            $('.input-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d',
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('live-input-time');
                checkDateAndTime('input-date', 'live-input-time', selectedTime);
            });

            $('.general-input-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d',
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('input-time');
                checkDateAndTime('general-input-date', 'input-time', selectedTime);
            });

            $('.general-input-end-date').datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
                startDate: '+0d',
            }).on('changeDate.datepicker', function(event) {
                selectedTime = selectTime('input-end-time');
                checkDateAndTime('general-input-end-date', 'input-end-time', selectedTime);
            });

            $('.live-input-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('input-date', 'live-input-time', selectedTime);
            });

            $('.input-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('general-input-date', 'input-time', selectedTime);
            });

            $('.input-end-time').timepicker({
                minuteStep: 5,
                showSeconds: false,
                showMeridian: false,
            }).on('changeTime.timepicker', function(e) {
                selectedTime = (e.time.hours * 60) + e.time.minutes;
                checkDateAndTime('general-input-end-date', 'input-end-time', selectedTime);
            });

            $('input.tags').tagsInput({
                width: "auto"
            });
            $('input.speakers').tagsInput({
                width: "auto",
                defaultText:'Add'
            });
            $('[name="event_type"]').change(function(){
                $('#live-single').hide();
                $('#general-single').hide();
                $('#'+$('[name="event_type"]:checked').val()+'-single').slideDown("slow");
            });
            $('[name="event_type"]:checked').trigger('change');

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
        $('textarea[name="event_description"]').each( function() {
            CKEDITOR.replace( $(this).attr('id'),{
                filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
            });
        });
        
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;

$('#button').on('click', function(){       
    var url='<?php echo URL::to('/'); ?>';
    selectedValue = $('#web_host').find('option:selected').val();
    var date = $('#datepicker').val(); // dd-mm-yyyy
    timezone = $('#timezone').val();
  
    $.ajax({
        type: "GET",
        dataType: 'json',
        url: url+'/cp/event/show-availability/'+selectedValue+'/'+date+'/'+timezone
    })
    .done(function (response) {  
     if(response.host_required)
        {
            $("#collapseOne").html(""); 
            $("#host").html(response.host_required);  
        }
        else if(response.scheduled_events)
        {
            $("#host").html('');
            $("#collapseOne").html(response.scheduled_events); 
        }
        else if(response.no_schedule)
        {
            $("#host").html('');
            $("#noshedule").html(response.no_schedule);
        }

    })

    });
    </script>
    </script>
@stop