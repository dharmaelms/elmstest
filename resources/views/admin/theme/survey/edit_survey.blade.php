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
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    <form action="{{ URL::to("cp/survey/edit-survey")}}" class="form-horizontal form-bordered form-row-stripped" method="post" accept-charset="UTF-8" data-custom-id="GENERAL" id="form-general">
                        <input type="hidden" name="_s" value="{{ $survey->id }}">
                        <div class="form-group">
                            <label for="survey_title" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.title')}}<span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="survey_title" id="survey_title" class="form-control" value="{{ $survey->survey_title }}">
                                {!! $errors->first('survey_title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="description" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.description')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="survey_description" id="survey_description" value="" class="form-control">{!! $survey->description !!}</textarea>
                            </div>
                            {!! $errors->first('survey_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                        <div class="form-group">
                            <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.start_date')}}<span class="red">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="start_date" class="form-control general-input-start-date" style="cursor: pointer" value="@if(!empty($survey->start_time)) {{ $survey->start_time->timezone(Auth::user()->timezone)->format('d-m-Y'
                                    ) }} @endif" readonly="readonly">
                                </div>
                                {!! $errors->first('start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="start_time" onclick="$(this).prev().click()" class="form-control general-input-start-time" style="cursor: pointer" value="@if(!empty($survey->start_time)) {{ $survey->start_time->timezone(Auth::user()->timezone)->format('H:i') }} @endif" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="start_time_reset" class="btn"><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('start_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.end_date')}}&nbsp;<span class="red" style="display: inline;">*</span></label>
                            <div class="col-lg-3 controls">
                                <div class="input-group date">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" name="end_date" class="form-control general-input-start-date" style="cursor: pointer" value="@if(!empty($survey->end_time)) {{ $survey->end_time->timezone(Auth::user()->timezone)->format('d-m-Y'
                                    ) }} @endif" readonly="readonly">
                                </div>
                                {!! $errors->first('end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                            <div class="col-lg-2 controls">
                                <div class="input-group">
                                    <span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
                                    <input type="text" name="end_time" onclick="$(this).prev().click()" class="form-control general-input-end-time" style="cursor: pointer" value="@if(!empty($survey->end_time)) {{ $survey->end_time->timezone(Auth::user()->timezone)->format('H:i') }} @endif" readonly="readonly">
                                    <span class="input-group-btn">
                                        <button id="end_time_reset" class="btn"><i class="fa fa-undo"></i></button>
                                    </span>
                                </div>
                                {!! $errors->first('end_time', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                            <!-- score option starts here -->
                        <!-- <div class="form-group">
                            <label for="display_report" class="col-sm-3 col-lg-2 control-label">{{trans('admin/survey.display_report')}}</label>
                            <div class="col-sm-5 col-lg-3 controls display_report">
                                <?php
                                if (Input::old('display_report')) {
                                    $display_report = Input::old('display_report');
                                } elseif (isset($survey->display_report)) {
                                    $display_report = $survey->display_report;
                                }
                                ?>
                                <div class="input-group">
                                    <label>
                                        <input type="checkbox" name="display_report" vaue="on" @if($survey->display_report) checked @endif>
                                    </label>
                                </div>
                            </div>
                        </div> -->
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{trans('admin/survey.save')}}</button>
                               <a href="{{URL::to('/cp/survey')}}"><button type="button" class="btn">{{trans('admin/survey.cancel')}}</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
    $(function(){
        $('#survey_title,.general-input-start-date,.general-input-end-date,.general-input-start-time,.general-input-end-time,.display_report').on('keyup keypress', function(e) {
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

        $('.general-input-start-date').datepicker({
            autoclose: true,
            format: "dd-mm-yyyy",
            startDate: '+0d'
        }).on('changeDate.datepicker', function(event) {
            selectedTime = selectTime('general-input-start-time');
            checkDateAndTime('general-input-start-date', 'general-input-start-time', selectedTime);
        });
        $('.general-input-start-time').timepicker({
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false,
            defaultTime: false
        }).on('changeTime.timepicker', function(e) {
            selectedTime = (e.time.hours * 60) + e.time.minutes;
            checkDateAndTime('general-input-start-date', 'general-input-start-time', selectedTime);
        });

        $('.general-input-end-date').datepicker({
            autoclose: true,
            format: "dd-mm-yyyy",
            startDate: '+0d'
        }).on('changeDate.datepicker', function(event) {
            selectedTime = selectTime('general-input-end-time');
            checkDateAndTime('general-input-end-date', 'general-input-end-time', selectedTime);
        });
        $('.general-input-end-time').timepicker({
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false,
            defaultTime: false
        }).on('changeTime.timepicker', function(e) {
            selectedTime = (e.time.hours * 60) + e.time.minutes;
            checkDateAndTime('general-input-end-date', 'general-input-end-time', selectedTime);
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
    </script>
@stop