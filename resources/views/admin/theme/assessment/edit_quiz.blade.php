@section('content')
<?php 
    $start    =  Input::get('start', 0);
    $limit    =  Input::get('limit', 10);
    $search   =  Input::get('search','');
    $order_by =  Input::get('order_by','5 desc');
?>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Edit quiz</h3> -->
	            </div>
	            <div class="box-content">
                    <div class="row">
                        <div class="col-xs-12 col-sm-12 col-md-offset-9 col-md-3 col-lg-offset-9 col-lg-3" style="margin-bottom:2%;">
                            <div class="btn-group pull-right">
                            @if(isset($quiz->is_sections_enabled) && ($quiz->is_sections_enabled))
                                <a class="btn btn-primary btn-sm" href="{{ URL::to("cp/section/list-section/{$quiz->quiz_id}") }}">
                                    </span>&nbsp;{{ trans('admin/assessment.manage_sections') }}
                                </a>&nbsp;&nbsp;
                            @else
                                <a class="btn btn-primary btn-sm" href="{{ URL::to("cp/assessment/quiz-questions/{$quiz->quiz_id}?qbank=0") }}">
                                    </span>&nbsp;{{ trans('admin/assessment.manage_question') }}
                                </a>&nbsp;&nbsp;
                            @endif
                            </div>
                        </div>
                    </div>
                @if(isset($type) && ($type === "QUESTION_GENERATOR"))
                    @include("admin.theme.assessment._question_generator")
                @else
                    @include("admin.theme.assessment._general_quiz")
                @endif
                </div>
	        </div>
	    </div>
	</div>
    <script type="text/javascript">
    $(function(){
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

        $('.qg-input-start-date').datepicker({
            autoclose: true,
            format: "dd-mm-yyyy",
            startDate: '+0d'
        }).on('changeDate.datepicker', function(event) {
            selectedTime = selectTime('qg-input-start-time');
            checkDateAndTime('qg-input-start-date', 'qg-input-start-time', selectedTime);
        });
        $('.qg-input-start-time').timepicker({
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false,
            defaultTime: false
        }).on('changeTime.timepicker', function(e) {
            selectedTime = (e.time.hours * 60) + e.time.minutes;
            checkDateAndTime('qg-input-start-date', 'qg-input-start-time', selectedTime);
        });

        $('.qg-input-end-date').datepicker({
            autoclose: true,
            format: "dd-mm-yyyy",
            startDate: '+0d'
        }).on('changeDate.datepicker', function(event) {
            selectedTime = selectTime('qg-input-end-time');
            checkDateAndTime('qg-input-end-date', 'qg-input-end-time', selectedTime);
        });
        $('.qg-input-end-time').timepicker({
            minuteStep: 5,
            showSeconds: false,
            showMeridian: false,
            defaultTime: false
        }).on('changeTime.timepicker', function(e) {
            selectedTime = (e.time.hours * 60) + e.time.minutes;
            checkDateAndTime('qg-input-end-date', 'qg-input-end-time', selectedTime);
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