@section('content')
    @if ( Session::get('success') )
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert">×</button>
            {{ Session::get('success') }}
        </div>
        <?php Session::forget('success'); ?>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger" id='alert-danger-head'>
            <button class="close" data-dismiss="alert">×</button>
            {{ Session::get('error') }}
        </div>
        <?php Session::forget('error'); ?>
    @endif
    <style>
        .center {
            text-align: center !important;
        }
        .select2-container{
            width: 100% !important;
        }
        .select2-selection__rendered {
            width: 100% !important;
        }
    </style>
    <script type="text/javascript" src="{{URL::to('admin/js/bootstrap-daterangepicker/moment.min.js')}}"></script>
    <script type="text/javascript" src="{{URL::to('admin/js/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
    <link rel="stylesheet" type="text/css" href="{{ URL::to("admin/css/select2.min.css") }}">
    <script type="text/javascript" src="{{ URL::to("admin/js/select2.min.js") }}"></script>
    <link rel="stylesheet" type="text/css" href="{{URL::to('admin/js/bootstrap-daterangepicker/daterangepicker-bs3.css')}}" />
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    <div class="alert alert-danger" id="alert-danger" style="display: none;">
                        <button class="close" >×</button>
                         {{trans('admin/reports.warning_msg')}}.
                    </div>
                    <form id='export_report' class="form-horizontal form-bordered form-row-stripped" method="post">
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/reports.reports')}} 
                                <span class="red">*</span>
                            </label>
                            <div class="col-sm-4 col-lg-4 controls" style="padding-left:10px;">
                                <select id="report_title" class="form-control chosen" name="report_title" data-placeholder="Select report" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                    <option value="user_act_course" 
                                        {{(Input::old('report_name') == 'user_act_course') ? "selected" : ""}}>
                                            {{ trans('admin/reports.user_act_course')}}
                                    </option>
                                    <option value="course_act_user" 
                                        {{(Input::old('report_name') == 'course_act_user') ? "selected" : ""}}>
                                            {{ trans('admin/reports.course_act_user')}}
                                    </option>
                                    <option value="course_act_ug"
                                        {{(Input::old('report_name') == 'course_act_ug') ? "selected" : ""}}>
                                            {{trans('admin/reports.course_act_ug')}}
                                    </option>
                                    <option value="ug_summary"
                                        {{(Input::old('report_name') == 'ug_summary') ? "selected" : ""}}>
                                            {{ trans('admin/reports.ug_summary')}}
                                    </option>
                                    <option value="ug_details" id="ug_details"
                                        {{(Input::old('report_name') == 'ug_details') ? "selected" : ""}}>
                                            {{ trans('admin/reports.ug_details')}}
                                    </option>
                                    <option value="course_com_item_lev"
                                        {{(Input::old('report_name') == 'course_com_item_lev') ? "selected" : ""}}>
                                            {{ trans('admin/reports.course_com_item_lev')}}
                                    </option>
                                    <option value="course_completed"
                                        {{(Input::old('report_name') == 'course_completed') ? "selected" : ""}}>
                                            {{ trans('admin/reports.course_completed')}}
                                    </option>
                                    <option value="course_inprogress"
                                        {{(Input::old('report_name') == 'course_inprogress') ? "selected" : ""}}>
                                            {{ trans('admin/reports.course_inprogress')}}
                                    </option>
                                </select>
                            </div>
                        </div>
                         <div class="form-group" id="date_filter_div" >
                            <label class="col-sm-3 col-lg-2 control-label" >
                                {{ trans('admin/reports.select_date')}}
                                <span class="red">*</span>
                            </label>
                            <div class="col-sm-9 col-lg-10 controls" id='date_filter'>
                                <label class="radio-inline">
                                    <input type="radio" name="dates" value="custom_date" 
                                        {{(Input::old('dates') == 'custom_date') ? "checked" : ""}}/>
                                        {{ trans('admin/reports.custom_dates')}}
                                </label>
                                <label class="radio-inline">
                                    <input type="radio" name="dates" value="all_time"
                                        {{(Input::old('dates') == 'all_time') ? "checked" : ""}}/>
                                        {{ trans('admin/reports.all_time')}}
                                </label>
                                {!! $errors->first('dates', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group" id="date_range" >
                            <label class="col-sm-3 col-lg-2 control-label" > {{ trans('admin/reports.custom_dates')}}
                                 <span class="red">*</span>
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:50;">
                                <div class="input-group">
                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                                    <input type="text" class="form-control daterange" name="range" id="cus_range" value="{{Timezone::convertFromUTC('@'.$start_date, Auth::user()->timezone, 'd-m-Y') . " to " . Timezone::convertFromUTC('@'.$end_date, Auth::user()->timezone, 'd-m-Y')}}" readonly/> 
                                </div>
                            </div>
                        </div>
                        <div class="form-group" id='filter_by'>
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/reports.filter_by')}}
                                <span class="red">*</span>
                            </label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <label class="radio-inline">
                                    <input type="radio" name="filter_by" value="channel"
                                        {{(Input::old('filter_by') == 'channel') ? "checked" : ""}}/>
                                    {{trans('admin/reports.c_channel')}}
                                </label>
                                @if (config('app.ecommerce'))
                                    <label class="radio-inline" id='course_filter_div'>
                                        <input type="radio" name="filter_by" value="course" id="course_filter"
                                            {{(Input::old('filter_by') == 'course') ? "checked" : ""}}/>
                                        {{trans('admin/reports.course')}}
                                    </label>
                                    <label class="radio-inline">
                                        <input type="radio" name="filter_by" value="package" id="package_filter"
                                            {{(Input::old('filter_by') == 'package') ? "checked" : ""}}/>
                                        {{trans('admin/reports.package')}}
                                    </label>
                                 @endif
                                {!! $errors->first('filter_by', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group" id="channels">
                            <label class="col-sm-2 col-lg-2 control-label">
                                {{trans('admin/reports.channel')}} <span class="red">*</span>
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:50;">
                                <select id="selected_feed" class="form-control chosen">
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id='packages'>
                            <label class="col-sm-2 col-lg-2 control-label">
                                {{trans('admin/reports.package')}} <span class="red">*</span>
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:50;">
                                <select id="selected_package" class="form-control chosen">
                                </select>
                            </div>

                            <label class="col-sm-2 col-lg-2 control-label" >
                                {{trans('admin/reports.package_channel')}} <span class="red">*</span>
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" >
                                <select id="selected_package_channel" class="form-control chosen">
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id='course'>
                            <label class="col-sm-2 col-lg-2 control-label" > 
                                {{trans('admin/reports.course')}} <span class="red">*</span> 
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:50;">
                                <select id="selected_course" class="form-control chosen">
                                </select>
                            </div>

                            <label class="col-sm-2 col-lg-2 control-label" >
                                {{trans('admin/reports.batch')}} <span class="red">*</span>
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:50;">
                                <select id="selected_course_batch" class="form-control chosen">
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id='usergroup'>
                            <label class="col-sm-2 col-lg-2 control-label" >
                                {{trans('admin/reports.user_group')}} <span class="red">*</span>
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:50;">
                                <select id="selected_ug" class="form-control chosen">
                                </select>
                            </div>
                        </div>
                        <div class="form-group" id='users'>
                            <label class="col-sm-2 col-lg-2 control-label" >
                                {{trans('admin/reports.users')}} <span class="red">*</span> 
                            </label>
                            <div class="col-sm-3 col-lg-3 controls" style="padding-left:50;">
                                <select id="selected_user" class="form-control chosen">
                                </select>
                            </div>
                        </div>

                        <div class="form-group last" id='action_div'>
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" ><i class="fa fa-download"></i> {{ trans('admin/reports.export') }}</button>
                            </div>
                        </div> 
                    </form>         
                </div>
            </div>
        </div>
    </div>
<script>
var channel_id = 0;
var package_id = 0;
var user_id = 0;
var user_group_id = 0;
var package_channel_id = 0;
var max_date = {{(int) config('app.max_date_range_selected')}};
var select_user_placeholder = '{{trans('admin/reports.select_user_placeholder')}}';
var select_ug_placeholder = '{{trans('admin/reports.select_ug_placeholder')}}';
var select_channel_placeholder = '{{trans('admin/reports.select_channel_placeholder')}}';
var select_course_placeholder = '{{trans('admin/reports.select_course_placeholder')}}';
var select_package_placeholder = '{{trans('admin/reports.select_package_placeholder')}}';
var select_batch_placeholder = '{{trans('admin/reports.select_batch_placeholder')}}';
var select_package_channel_placeholder = '{{trans('admin/reports.select_package_channel_placeholder')}}';

$('#alert-danger-head').delay(5000).fadeOut();
$('.daterange').daterangepicker({
    format: 'DD-MM-YYYY',
    maxDate: moment(),
    dateLimit: { days:  max_date},
    showDropdowns: true,
    showWeekNumbers: true,
    timePicker: false,
    timePickerIncrement: 1,
    timePicker12Hour: true,
    ranges: {
       'Yesterday':  [moment().subtract(1, 'days'), moment().subtract(1, 'days')],
       'Last 15 Days': [moment().subtract(15, 'days'), moment()],
       'Last 30 Days': [moment().subtract(30, 'days'), moment()],
       'Last 3 months': [moment().subtract(3, 'months'), moment()],
    },
    opens: 'right',
    drops: 'down',
    buttonClasses: ['btn', 'btn-sm'],
    applyClass: 'btn-primary',
    cancelClass: 'btn-default',
    separator: ' to ',
    locale: {
        applyLabel: 'Apply',
        cancelLabel: 'Cancel',
        fromLabel: 'From',
        toLabel: 'To',
        customRangeLabel: 'Custom',
        daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
        monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
        firstDay: 1
    }
});
$(document).ready(function(){
    $('#export_report').submit(function(){
        var report_name = $('#report_title').val();
        var date_range =  $('#cus_range').val();
        var start_date = '';
        var end_date = '';
        selected_date = $('[name="dates"]:checked').val()
        if(selected_date == 'all_time') {
            date_range = 'all_time';
        } else {
            date_splited = date_range.split(' to ');
            start_date = date_splited[0];
            end_date = date_splited[1];
        }
        var url = '{{URL::to('/cp/exportreports/')}}';
        filter_by = $('[name="filter_by"]:checked').val();
        if (filter_by == 'course') {
            selected_package = $('#selected_course').val();
            selected_feed = $('#selected_course_batch').val();
        } else if (filter_by == 'package') {
            selected_package = $('#selected_package').val();
            selected_feed = $('#selected_package_channel').val();
        } else { 
            selected_package = null;
            selected_feed = $('#selected_feed').val();
        } 
        $('.close').trigger('click');
        switch(report_name){
            case "user_act_course" :
                if (selected_feed != null && selected_feed >= 0 && typeof(selected_date) != "undefined"){ 
                    location.href = url+'/user-activity-by-course/'+selected_feed+'/'+date_range+'/'+selected_package+'/'+filter_by+'?start_date='+start_date+'&end_date='+end_date;    
                }else{ 
                   $('#alert-danger').show() ;
                   $('#alert-danger').delay(5000).fadeOut();
                }
                break;
            case "course_act_user" :
                selected_user = $('#selected_user').val();
                if (selected_user != null && typeof(selected_date) != "undefined") {
                    location.href = url +'/course-activity-by-user/'+selected_user+'/'+date_range+'?start_date='+start_date+'&end_date='+end_date;
                }else{
                    $('#alert-danger').show() ;
                    $('#alert-danger').delay(5000).fadeOut();
                }
                
                break;
            case "course_act_ug" :
                selected_ug = $('#selected_ug').val();
                if(selected_feed != null && selected_feed >= 0 && selected_ug != null && selected_ug > 0){ 
                    location.href = url +'/course-activity-by-group/'+selected_ug+'/'+selected_feed+'/'+selected_package+'/'+filter_by;
                }else{ 
                    $('#alert-danger').show() ;
                    $('#alert-danger').delay(5000).fadeOut();
                }
                break;
            case "ug_summary" :
                if (typeof(selected_date) != "undefined") {
                    location.href = url +'/group-summary/'+date_range+'?start_date='+start_date+'&end_date='+end_date;
                } else {
                    $('#alert-danger').show() ;
                    $('#alert-danger').delay(5000).fadeOut();
                }
                break;
            case "ug_details" :
                selected_ug = $('#selected_ug').val();
                if (selected_ug != null && selected_ug > 0) {
                    location.href = url +'/group-details/'+selected_ug;
                } else {
                    $('#alert-danger').show() ;
                    $('#alert-danger').delay(5000).fadeOut();
                }
                break;
            case "course_com_item_lev" :
                if(selected_feed != null && selected_feed >= 0 && typeof(selected_date) != "undefined"){ 
                    location.href = url +'/post-level-completion/'+selected_feed+'/'+date_range+'/'+selected_package+'/'+filter_by+'?start_date='+start_date+'&end_date='+end_date;
                }else{ 
                    $('#alert-danger').show() ;
                    $('#alert-danger').delay(5000).fadeOut();
                }
                break;  
            case "course_completed" :
                if (typeof(selected_date) != "undefined") {
                    location.href = url +'/programs-completion/'+date_range+'/'+'course_completed'+'?start_date='+start_date+'&end_date='+end_date;
                } else {
                    $('#alert-danger').show() ;
                    $('#alert-danger').delay(5000).fadeOut();
                }
                break;
            case "course_inprogress" :
                if (typeof(selected_date) != "undefined") {
                    location.href = url +'/programs-completion/'+date_range+'/'+'course_inprogress'+'?start_date='+start_date+'&end_date='+end_date;
                } else {
                    $('#alert-danger').show() ;
                    $('#alert-danger').delay(5000).fadeOut();
                }
                break;              
        }
        return false;
    });


    $('[name="filter_by"]').change(function(){
        selected = $('[name="filter_by"]:checked').val();
        report_name = $('#report_title').val();
        if(selected == 'course' && report_name != 'course_act_ug') {
            $('#channels').hide();
            $('#packages').hide();
            $('#course').show();
        }else if(selected == 'package') {
            $('#channels').hide();
            $('#course').hide();
            $('#packages').show();
        }else if(selected == 'channel') {
            $('#course').hide();
            $('#packages').hide();
            $('#channels').show();
        }else { 
            $('#course').hide();
            $('#channels').hide();
            $('#packages').hide();
        }   
    });

    $('[name="dates"]').change(function(){
        selected = $('[name="dates"]:checked').val()
        if(selected == 'custom_date') {
            $('#date_range').show();
        }else if(selected == 'all_time') {
            $('#date_range').hide();
        }else { 
            $('#date_range').hide();
        }   
    });

    $('#report_title').change(function(){
        var $this = $(this);
        $this.closest('.form-group').next().slideDown();
        $('#channels').hide();
        $('#packages').hide();
        $('#course').hide();
        $('#usergroup').hide();
        $('#users').hide();
        $('#filter_by').hide();
        $('#action_div').show();
        $('[name="filter_by"]').prop('checked', false);
        $('#selected_feed').trigger('change');
        $('#selected_package').trigger('change');
        $('#selected_course').trigger('change');
        switch($this.val()){
            case "user_act_course" :
                $('#filter_by').show();
                $('#course_filter_div').show();
                $('[name="filter_by"]').trigger('change');
                $('[name="dates"]').trigger('change');
                break;
            case "course_act_user" :
                $('#users').show();
                $('[name="dates"]').trigger('change');
                break;
            case "course_act_ug" :
                $('#filter_by').show();
                $('#course_filter_div').hide();
                $('[name="filter_by"]').trigger('change');
                $('#usergroup').show();
                $('#date_range').hide();
                $('#date_filter_div').hide();
                break;
            case "ug_summary" :
                $('[name="dates"]').trigger('change');
                break;
            case "ug_details" :
                $('#date_range').hide();
                $('#date_filter_div').hide();
                $('#usergroup').show();
                break;
            case "course_com_item_lev" :
                $('#filter_by').show();
                $('#course_filter_div').show();
                $('[name="filter_by"]').trigger('change');
                $('[name="dates"]').trigger('change');
                break;
            case "course_completed" :
                $('[name="dates"]').trigger('change');
                break;
            case "course_inprogress" :
                $('[name="dates"]').trigger('change');
                break;                           
        }
    });
    
    $('#date_range').hide();
    $('#channels').hide();
    $('#packages').hide();
    $('#usergroup').hide();
    $('#action_div').hide();
    $('#users').hide();
    $('#filter_by').hide();
    $('#date_filter_div').hide();
    $('#course').hide();
    $("#selected_user").select2({
        placeholder : select_user_placeholder,
        allowClear : true,
        ajax : {
            type : "POST",
            url : '{{URL::to('/cp/exportreports/users')}}',
            data : function(params){
                var query = {
                        search: params.term
                      }
              return query;
            },
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
            cache : false
        }
    });

    $("#selected_feed").select2({
        placeholder : select_channel_placeholder,
        allowClear : true,
        ajax : {
            delay : 500,
            type : "POST",
            url : '{{URL::to('/cp/exportreports/channels')}}',
            data : function(params){
                return {
                    query: params,
                    is_ug_channel_report: $('#report_title').val()
                };
            },
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
            cache : false
        }
    });

    $("#selected_package").select2({
        placeholder : select_package_placeholder,
        allowClear : true,
        ajax : {
            delay : 500,
            type : "POST",
            url : '{{URL::to('/cp/exportreports/packages')}}',
            data : function(params){
                return {
                    query: params,
                    is_ug_channel_report: $('#report_title').val()
                };
            },
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
            cache : false
        }
    });

    $("#selected_package_channel").select2({
        placeholder : select_package_channel_placeholder,
        allowClear : true,
        ajax : {
            type : "POST",
            url : "{{URL::to('/cp/exportreports/package-channels')}}",
            data : function(params){
                var query = {
                        search: params.term,
                        package_id: document.getElementById('selected_package').value
                      }
              return query;
            },
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
        }
    }); 

    $("#selected_course").select2({
        placeholder : select_course_placeholder,
        allowClear : true,
        ajax : {
            delay : 500,
            type : "POST",
            url : '{{URL::to('/cp/exportreports/list-courses')}}',
            data : function(params){
                return {
                    query: params
                };
            },
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
            cache : false
        }
    });

    $("#selected_course_batch").select2({
        placeholder : select_batch_placeholder,
        allowClear : true,
        ajax : {
            type : "POST",
            url : "{{URL::to('/cp/exportreports/list-course-batch')}}",
            data : function(params){
                var query = {
                        search: params.term,
                        course_id: document.getElementById('selected_course').value
                      }
              return query;
            },
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
        }
    }); 

    $("#selected_ug").select2({
        placeholder : select_ug_placeholder,
        allowClear : true,
        ajax : {
            delay : 500,
            type : "POST",
            url : '{{URL::to('/cp/exportreports/usergroups')}}',
            data : function(params){
                var query = {
                        search: params.term,
                        channel_id: document.getElementById('selected_feed').value,
                        package_id: document.getElementById('selected_package').value,
                        is_package: (document.getElementById('package_filter') != null) ? document.getElementById('package_filter').checked : false,
                        is_ug_report: document.getElementById('ug_details').selected,
                    }
                return query;
            },
            contentType : "application/x-www-form-urlencoded; charset=UTF-8",
            dataType : "json",
            processResults : function(response, params){
                return { results : response };
            },
            cache : false
        }
    });

    $('#selected_feed').change(function(){
        channel_id = $(this).val();
        $('#selected_ug').next().find('.select2-selection__clear').trigger('mousedown');
        $('#select2-selected_ug-results').empty();
        $('#selected_ug').next().find('.select2-selection__clear').trigger('mousedown');
    });

    $('#selected_package').change(function(){
        package_id = $(this).val();
        $('#select2-selected_package_channel-results').empty();
        $('#selected_package_channel').next().find('.select2-selection__clear').trigger('mousedown');
        $('#select2-selected_ug-results').empty();
        $('#selected_ug').next().find('.select2-selection__clear').trigger('mousedown');
    });

    $('#selected_course').change(function(){
        $('#select2-selected_course_batch-results').empty();
        $('#selected_course_batch').next().find('.select2-selection__clear').trigger('mousedown');
    });

    $('#selected_package_channel').change(function(){
        package_channel_id = $(this).val();
    });

    $('#selected_ug').change(function(){
        user_group_id = $(this).val();
    });

    $('#selected_user').change(function(){
        user_id = $(this).val();
    }); 

    $('.close').click(function(){$('#alert-danger').hide()});

    $('#report_title').trigger('change');
})
        
</script>
@stop