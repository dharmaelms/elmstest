@section('content')
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">


    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
   
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>


    <script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [6];
    </script>

    <div class="tabbable">
        <ul id="myTab1" class="nav nav-tabs">
            <li class="active">
                <a href="#overall_report" data-toggle="tab">
                    <i class="fa fa-home"></i> {{trans('admin/event.att_report')}}
                </a>
            </li>
            <!-- <li>
                <a href="#storage_report" data-toggle="tab">
                    <i class="fa fa-home"></i> {{trans('admin/event.storage_report')}}
                </a>
            </li> -->
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="webex-tab tab-content">
                <div class="tab-pane fade in active" id="overall_report">
                    <div class="box">
                        <div class="box-title">
                        </div>
                        <div class="box-content">
                            <div class="btn-toolbar clearfix">
                                <div class="col-md-6">
                                    <form name="webex-report" id="overall-report" class="form-horizontal" action="{{URL::to('cp/webex/event-history')}}" method="GET">
                                        <div class="form-group col-md-12">
                                            <label class="col-sm-6 col-lg-4 control-label">
                                                <b>
                                                    {{ trans('admin/event.webex_host') }} :
                                                </b>
                                            </label>
                                            <div class="col-sm-6 col-lg-8 controls">
                                                <select class="form-control input-sm chosen" id="hosts" name="event_host">
                                                    <option value="">{{ trans('admin/event.host_all') }}</option>
                                                    @foreach($hosts as $host)
                                                        <option value="{{$host['username']}}">{{$host['name']}}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label class="col-sm-6 col-lg-4 control-label">
                                                <b>
                                                    {{ trans('admin/event.session_type') }} :
                                                </b>
                                            </label>
                                            <div class="col-sm-6 col-lg-8 controls">
                                                <select class="form-control input-sm" id="types" name="event_type">
                                                    <option value="">{{ trans('admin/event.all') }}</option>
                                                    <option value="MC">{{ trans('admin/event.meeting_center') }}</option>
                                                    <option value="TC">{{ trans('admin/event.training_center') }}</option>
                                                </select>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label class="col-sm-6 col-lg-4 control-label">
                                                <b>
                                                    {{ trans('admin/event.start_date') }} :
                                                </b>
                                            </label>
                                            <div class="col-sm-6 col-lg-8 controls">
                                                <div class="input-group date">
                                            <span class="input-group-addon" onclick="$(this).next().focus()"><i
                                                        class="fa fa-calendar"></i></span>
                                                    <?php $last_week = time() - (7 * 24 * 60 * 60); ?>
                                                    <input type="text" name="start_date" id="startdate" class="form-control" value="{{ Timezone::convertFromUTC('@'.$last_week, Auth::user()->timezone, 'd-m-Y') }}" style="cursor: pointer"
                                                           readonly="readonly">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group col-md-12">
                                            <label class="col-sm-6 col-lg-4 control-label">
                                                <b>
                                                    {{ trans('admin/event.end_date') }} :
                                                </b>
                                            </label>
                                            <div class="col-sm-6 col-lg-8 controls">
                                                <div class="input-group date">
                                                    <span class="input-group-addon" onclick="$(this).next().focus()"><i
                                                                class="fa fa-calendar"></i></span>
                                                    <input type="text" name="end_date" id="enddate" class="form-control"
                                                           value="{{ Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y') }}" style="cursor: pointer" readonly="readonly">
                                                </div>
                                            </div>
                                        </div>
                                        <div class="form-group last">
                                            <div class="col-sm-2 col-sm-offset-2 col-lg-2 col-lg-offset-4">
                                                <button type="button" class="btn btn-success" id="submit-overall-report"><i class="fa fa-check"></i> {{trans('admin/event.submit')}} </button>
                                            </div>
                                            <div class="col-sm-3 col-lg-3">
                                                <button type="button" class="btn btn-primary event-download"><i class="fa fa-check"></i>{{ trans('admin/event.export') }}</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>   
                                <table class="table table-advance no-footer" id="report-table">
                                    <thead>
                                        <th class="text-left" style="width:250px">{{trans('admin/event.event_name')}}</th>
                                        <th class="text-left">{{trans('admin/event.host_name')}}</th>
                                        <th class="text-left">{{trans('admin/event.total_attendees')}}</th>
                                        <th class="text-left">{{trans('admin/event.duration_in_minutes')}}</th>
                                        <th class="text-left">{{trans('admin/event.start_time')}}</th>
                                        <th class="text-left">{{trans('admin/event.end_time')}}</th>
                                        <th class="text-left">{{trans('admin/event.webex_host')}}</th>
                                        <th class="text-left">{{trans('admin/event.session_type')}}</th>
                                    </thead>
                                    <tbody>
                                        <tr><td colspan="7" class="text-center">{{trans('admin/event.no_event')}}</td></tr>
                                    </tbody>
                                </table>
                        </div>
                    </div>
                </div>
                <div class="tab-pane fade" id="storage_report">
                    <div class="box">
                        <div class="box-title">
                        </div>
                        <div class="box-content">
                            <div class="btn-toolbar clearfix">
                                <div class="col-md-6">
                                    <form name="storage-report" id="storage-report" class="form-horizontal" action="{{URL::to('cp/webex/event')}}" method="POST" onsubmit="refalse">
                                        <div class="form-group col-md-12">
                                            <label class="col-sm-6 col-lg-4 control-label">
                                                <b>
                                                    {{ trans('admin/event.webex_host') }} :
                                                </b>
                                            </label>
                                            <div class="col-sm-6 col-lg-8 controls">
                                                <select class="form-control input-sm chosen" id="hosts" name="event_host">
                                                    <option class="storage-size" data-hostname="All Hosts" data-storage="{{$total_storage_limit}}" value="">{{ trans('admin/event.host_all') }}</option>
                                                    @foreach($hosts as $host)
                                                        <option class="storage-size" data-hostname="{{$host['name']}}" data-storage="{{array_get($host, 'storage_limit', 0)}}" value="{{$host['username']}}">{{$host['name']}}</option>
                                                    @endforeach
                                                </select>
                                                <span id="storage-info" class="hide">
                                                    <p id="storage-size"></p>
                                                </span>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            <div id="storage-msg"></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- modal pop up -->
    <div class="modal fade" id="attendees-detailed-report" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog modal-lg">
            <div class="modal-content dashWidth900">
                <div class="modal-header">
                    <div class="row custom-box">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3 class="modal-header-title" id="event-name-display">
                                        <i class="icon-file"></i>
                                             {{trans('admin/event.att_detailed_report')}}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div style="padding:15px;font-size:15px;">
                    <button type="button" class="btn btn-primary pull-right attendee-download"><i class="fa fa-check"></i>{{trans('admin/event.export')}}</button>
                    <div id="event_host_name"></div>
                    <div id="webex-host"></div>
                </div>
                <div id="attendees-detail-body" class="modal-body dashHeight450">
                <table class="table table-advance no-footer" id="attendee-table">
                    <thead>
                        <th class="text-left">{{trans('admin/event.attendee_name')}}</th>
                        <th class="text-left">{{trans('admin/event.attendee_type')}}</th>
                        <th class="text-left">{{trans('admin/event.attendee_email')}}</th>
                        <th class="text-left">{{trans('admin/event.duration')}}</th>
                        <th class="text-left">{{trans('admin/event.start_time')}}</th>
                        <th class="text-left">{{trans('admin/event.end_time')}}</th> 
                    </thead>
                    <tbody>
                        <tr>
                            <td colspan="7" class="text-center">
                                {{trans('admin/event.no_attendees')}}
                            </td>
                        </tr>
                    </tbody>
                </table>    
                </div>
                <div class="modal-footer">
                </div>
            </div>
        </div>
    </div>

    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>


    <script>
        var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";
        var  order_by_var= "{{Input::get('order_by','4 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];

        var  order_by_var2 = "{{Input::get('order_by','1 desc')}}";
        var  order2 = order_by_var2.split(' ')[0];
        var  _by2   = order_by_var2.split(' ')[1];
    
    /* Simple Loader */
    (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color: ;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
        simpleloader.init();

        $(document).ready(function () {
            displayStorageOnload();
            $("#startdate").datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                $('#enddate').datepicker('setStartDate', minDate);
            });

            $("#enddate").datepicker({
                autoclose: true,
                format: "dd-mm-yyyy",
            }).on('changeDate', function (selected) {
                var minDate = new Date(selected.date.valueOf());
                $('#startdate').datepicker('setEndDate', minDate);
            });

            $('.event-download').on('click', function(e){
                location.href = "{{URL::to('/cp/webex/event-history')}}?"+$('#overall-report').serialize()+'&download=download';
            });

            $('#storage-report').on('change', function(form){
                displayStorageOnload();
                form.preventDefault();
                return false;
            });
            function displayStorageOnload(){
                simpleloader.fadeIn();
                $.ajax({
                    url: "{{URL::to('/cp/webex/storage')}}",
                    method : "GET",
                    data : $('#storage-report').serialize(),
                    dataType: 'json',
                }).success(function (response) {
                    var hostname = $('#storage-report #hosts').find(':selected').data('hostname');
                    var used_data = response.data;
                    var total_data = $('#storage-report #hosts').find(':selected').data('storage')

                    $('#storage-msg').addClass('alert alert-success').css('font-size', '17px').html("<center> '"+ $('#storage-report #hosts').find(':selected').data('hostname') + "' {{ trans("admin/event.has_consumed") }}"+ response.data +" {{ trans("admin/event.out_of") }} "+ $('#storage-report #hosts').find(':selected').data('storage') +" {{ trans('admin/event.gigabytes') }}</center>"); 
                    $('#storage-info').removeClass('hide'); 
                    simpleloader.fadeOut();
                });
            }

            function changeDateFormate(date){
                var current_date = new Date(date);
                var changed_date_formate = 
                    ("0" + current_date.getDate()).slice(-2) +
                    '/'+("0" + (current_date.getMonth() + 1)).slice(-2) +
                    '/'+ current_date.getFullYear() +
                    ' '+("0" + current_date.getHours()).slice(-2) +
                    ":"+("0" + current_date.getMinutes()).slice(-2);
                return changed_date_formate;
            }

            /* overall Attendees report */
            $('#submit-overall-report').on('click', function (form) {
                $('.event-download').removeClass('disabled');
                datatableOBJ.draw(true);
            });
            /* overall Attendees report ends here*/
    
        /* code for overall attendees report's DataTable begins here */
            var $datatable = $('#report-table');
            var datatableOBJ = $('#report-table').on('processing.dt',function(event,settings,flag){
            $('#datatable_processing').hide();
                if(flag == true)
                    simpleloader.fadeIn();
                else
                    simpleloader.fadeOut();
                /*Simply loader code*/
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body'});
                }).DataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('/cp/webex/event-history')}}",
                        "data": function ( d ) {
                            d.event_host = $('[name="event_host"]').val();
                            d.event_type = $('[name="event_type"]').val();
                            d.start_date = $('[name="start_date"]').val();
                            d.end_date = $('[name="end_date"]').val();
                        }
                    },
                    "columns": [
                            { data: 'event_name' },
                            { data: 'host_name' },
                            { data: 'total_participants', "render": function(data, type, row, meta){
                                console.log(data, type, row, meta);
                                return '<a style="cursor:pointer" data-host-id="' +row.host_id+ '" data-host-name="' +row.host_name+ '" data-event-name="' +row.event_name+ '" data-conf-id="' + row.confID + '" class="participants">' + data + '</a>';
                                } 
                            },
                            { data: 'duration' },
                            { data: 'start_time', "render": function(start_time){
                                return changeDateFormate(start_time);
                                } 
                            },
                            { data: 'end_time', "render": function(end_time){
                                return changeDateFormate(end_time);
                                } 
                            },
                            { data: 'host_id' },
                            { data: 'session_type' },
                        ],
                    "aaSorting": [[ Number(order), _by]],
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ],   
                    "iDisplayStart":start_page,
                    "pageLength":length_page,
                    "oSearch": {"sSearch": search_var},
                    "language": { /* To remove (filtered from 1 total entries) msg form datatable */
                        "infoFiltered": ""
                    }
                });

            $('#report-table_filter input').unbind().bind('keyup', function(e) {
                if(e.keyCode == 13) {
                    $('#report-table').dataTable().fnFilter(this.value);
                }
            });
            /* Code for overall attendees report's dataTable ends here */

            /* loading Attendees details datatable */
            $datatable.on('click', '.participants', function(){
                $('#attendees-detailed-report').modal('show');
                var confID = $(this).data('conf-id');
                var event_name = $(this).data('event-name');
                var host_id = $(this).data('host-id');
                var host_name = $(this).data('host-name');
                
                $("#event-name-display").html( '{{ trans("admin/event.att_report_label") }} <b>"' +event_name+ '"</b> ');
                $("#event_host_name").html('<b>{{ trans("admin/event.host_name") }} : </b>' +host_name+ '');
                $("#webex-host").html('<b>{{ trans("admin/event.webex_host") }} : </b>' +host_id+ ''); 

                /* code for detailed attendees report's DataTable begins here */
                var $datatable2 = $('#attendee-table');
                var datatableOBJ2  = $('#attendee-table').on('processing.dt',function(event,settings,flag){
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                }).DataTable({
                    "serverSide": true,
                    "destroy": true,
                    "ajax": {
                      "url": "{{URL::to('cp/webex/attendees-details')}}",
                      "data": function ( d ) {
                        d.session_id = confID;
                      }
                    },
                    "columns": [
                                { data: 'attendee_name' },
                                { data: 'attendee_type' },
                                { data: 'attendee_email' },
                                { data: 'duration' },
                                { data: 'start_time', "render": function(start_time){
                                    return changeDateFormate(start_time);
                                    }  
                                },
                                { data: 'end_time', "render": function(end_time){
                                    return changeDateFormate(end_time);
                                    }  
                                }
                        ],
                    "aaSorting": [[ Number(order2), _by2]],
                    "columnDefs": [ { "targets": [], "orderable": false } ],
                    "language": {  /* To remove (filtered from 1 total entries) msg from datatable */    "infoFiltered": ""
                    }
                });

                $('#attendee-table_filter input').unbind().bind('keyup', function(e) {
                    if(e.keyCode == 13) {
                        $('#attendee-table').dataTable().fnFilter(this.value);
                    }
                });
                /* code for detailed attendees report's DataTable ends here */
                
                /*css for datatable */                 
                var $triggermodal = $('#attendees-detailed-report');
                $triggermodal.find('#attendees-detail-body').css({"padding-top":"26px","padding-bottom":"26px"}); 
                $triggermodal.find('#attendees-detail-body').css({"padding-left":"26px","padding-right":"26px"}); 
                $triggermodal.find('#attendees-detail-body').css({"padding":"10px","border":"1px solid #E6E6E6", "margin-right" : "10px", "margin-left" : "10px"});

                $('.attendee-download').on('click', function(e){
                    location.href = "{{URL::to('cp/webex/attendees-details')}}?session_id="+confID+'&download=download';
                });

            });
            /* loading Attendees details datatable */
        });
    </script>
@stop
