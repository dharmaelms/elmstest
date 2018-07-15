@section('content')
    @if ( Session::get('success'))
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert" id="alert-success">×</button>
            {{ Session::get('success') }}
        </div>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            {{ Session::get('error') }}
        </div>
    @endif
    @if ( Session::get('warning'))
        <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('warning') }}
        </div>
        <?php Session::forget('warning'); ?>
    @endif
    <script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [1, 3];
    </script>

    <style type="text/css">
        .survey-header th{
            text-align: left !important;
        }
        .table-advance tbody > tr:nth-child(even) > .readmore-js-toggle {
            background-color: #f6f6f6;
        }
        .font-16 {
            font-size: 16px;
        }
        .main-row {
        	border: 1px solid #e7e7e7;
        	border-bottom: none !important;
        	margin: 0px;
        }
        .survey-labels {
        	padding: 5px 15px 5px 5px;
            text-align: right;
        }
        .label-details{
        	border-left: 1px solid #e0e0e0;
        	padding: 5px 5px 5px 15px;
        }
        ul .list-row:nth-child(odd) {
        	background-color: #f9f9f9;
        }
    </style>

    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>

    @include('admin.theme.survey.unattempted_users')
    <div class = "row" style="padding-top: 10px">
        <div class="col-md-1">
        </div>
        <div class="col-md-10">
            <div class="pull-left">
                <span style="font-size:28px"><i class="fa fa-file-text"></i>&nbsp;{{trans('admin/survey.detailed_report')}}</span>
            </div>
            <div class="btn-group pull-right">
                <div class="btn-group">
                    <a class="btn btn-primary btn-sm " href="{{ URL::to('/cp/survey/survey-report/'.$survey->id) }}">
                        <span class="show-tooltip custom-btm">
                            <i class="fa fa-angle-left"></i>
                        </span>&nbsp;{{trans('admin/survey.back')}}
                    </a>
                </div>
            </div>
            <div class="pull-right" style="margin-right: 10px">
                <a class="btn btn-primary btn-sm font-14" href="{{ URL::to('/cp/survey/survey-report-export/'. $survey->id) }}"><i class="fa fa-download"></i>&nbsp;&nbsp;{{trans('admin/survey.export')}}</a>
            </div>
            <div class="clearfix"></div>
        </div>
    </div>
    <br>
    <div class="row">
        <div class="col-md-1">
        </div>
        <div class="col-md-10">
            <div class="box">
                <div class="box-content">
                	<ul style="list-style-type: none;padding: 0px">
                		<li class="list-row">
                			<div class="row main-row">
            					<div class="col-md-3 survey-labels font-16">
            						<span><b>{{trans('admin/survey.survey_name')}} </b></span>
            					</div>
            					<div class="col-md-9 label-details font-16">
            						<span><b>{{$survey->survey_title}}</b></span>
            					</div>
                			</div>
                		</li>
                        @if($channel_name)
                		<li class="list-row">
                			<div class="row main-row">
            					<div class="col-md-3 survey-labels">
            						<span>{{trans('admin/reports.channel_name')}} </span>
            					</div>
            					<div class="col-md-9 label-details">
            						<span>{{$channel_name->program_title}}</span>
            					</div>
                			</div>
                		</li>
                        @endif
                        @if($packet_title)
                		<li class="list-row">
                			<div class="row main-row">
            					<div class="col-md-3 survey-labels">
            						<span>{{trans('admin/reports.post_name')}} </span>
            					</div>
            					<div class="col-md-9 label-details">
            						<span>{{$packet_title['packet_title']}}</span>
            					</div>
                			</div>
                		</li>
                        @endif
                		<li class="list-row">
                			<div class="row main-row">
            					<div class="col-md-3 survey-labels">
            						<span>{{trans('admin/survey.total_assigned_users')}}</span>
            					</div>
            					<div class="col-md-9 label-details">
            						<span>{{$total_users}}</span>
            					</div>
                			</div>
                		</li>
                		<li class="list-row">
                			<div class="row main-row">
            					<div class="col-md-3 survey-labels">
            						<span>{{trans('admin/survey.no_of_user_responded')}}</span>
            					</div>
            					<div class="col-md-9 label-details">
            						<span>{{$attempt_users}}</span>
            					</div>
                			</div>
                		</li>
                		<li class="list-row">
                			<div class="row main-row" style="border-bottom: 1px solid #e7e7e7 !important">
            					<div class="col-md-3 survey-labels">
            						<span>{{trans('admin/survey.users_not_responded')}}</span>
            					</div>
            					<div class="col-md-9 label-details">
            						<span>
                                        @if($unattempt_users > 0)
                                            <a href="#unattempted-users" data-toggle="modal" class="unattempted-users" data-title="{{$survey->survey_title}}">{{$unattempt_users}}</a>
                                        @else
                                            <a href="#">{{$unattempt_users}}</a>
                                        @endif
                                    </span>
            					</div>
                			</div>
                		</li>
                	</ul>
                    <br>
                    <table class="table table-advance" id="datatable-userreport">
                        <thead>
                            <tr class="survey-header">
                                <th>{{trans('admin/dashboard.username')}}</th>
                                <th>{{trans('admin/dashboard.user_fullname')}}</th>
                                <th>{{trans('admin/survey.email')}}</th>
                                <th>{{trans('admin/survey.actions')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="col-md-1">
        </div>
    </div>
    <div class="modal fade" id="view_report" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog" style="width: 900px;">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3>{{trans('admin/survey.username')}}:&nbsp;&nbsp;</h3><h3 class="modal-header-title"></h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div style="padding: 10px 0px 0px 20px;font-size: 14px">
                        <b>{{trans('admin/survey.survey_name')}}:&nbsp;&nbsp;</b><span class="survey_name"></span>
                    </div>
                    <div class="modal-body" style="padding: 0px !important;">
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/survey.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
    <script type="text/javascript">
        var  order_by_var= "{{Input::get('order_by','0 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];
        var surveyID = {{$survey->id}};

          /* Simple Loader */
            simpleloader.init();

        $(document).ready(function(){
            /*Code to hide success message after 2 seconds*/
            $('.alert-success').delay(2000).fadeOut();

        /* code for survey DataTable begins here */
            var $datatable = $('#datatable-userreport');
            var datatableOBJ  = $('#datatable-userreport').on('processing.dt',function(event,settings,flag){
                if(flag == true)
                    simpleloader.fadeIn();
                else
                    simpleloader.fadeOut();
            }).on('draw.dt',function(event,settings,flag){
                $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                $('td:nth-child(2) div', '#datatable-userreport tr').each(function(){
                })
            }).dataTable({
                "serverSide": true,
                "destroy": true,
                "ajax": {
                  "url": '{{URL::to('cp/survey/user-report-ajax')}}'+'?survey_id='+surveyID
                },
                "columns": [
                            { data: 'username' },
                            {data: 'fullname'},
                            { data: 'email' },
                            { data: 'actions'},
                    ],
                "aaSorting": [[ Number(order), _by]],
                "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                "language": {  /* To remove (filtered from 1 total entries) msg from datatable */    "infoFiltered": ""
                },
            });

            $('#datatable-userreport_filter input').unbind();
            $('#datatable-userreport_filter input').bind('keyup', function(e) {
                if(e.keyCode == 13) {
                     datatableOBJ.fnFilter(this.value);
                }
            });
                /* code for survey DataTable ends here */
    });

    </script>

    <script type="text/javascript">
        var surveyID = {{$survey->id}};
        $('.unattempted-users').click(function() {
        page = 0;
        $('#unattempted-users').find('#read-more').show();
        $('#unattempted-users').find('#title_model').html($(this).data('title'));
        $('#unattempted-users').find('.modal-body').empty();
        innerhtml = "<table class='table table-advance table-bordered' id='unattempted-table'><thead><tr><th>{{trans('admin/survey.username')}}</th><th>{{trans('admin/dashboard.user_fullname')}}</th><th>{{trans('admin/survey.email')}}</th></tr></thead></table>";
        $('#unattempted-users').find('.modal-body').append(innerhtml);
        unattemptedUsers(page);
    });
    $('#read-more').click(function() {
        page++;
        unattemptedUsers(page);
    });
    function unattemptedUsers(page) {
         $.ajax({
            type:'GET',
            url: '{{URL::to('/cp/survey/unattempted-user-details')}}'+'?survey_id='+surveyID+'&page='+page,
        })
        .done(function (response){
            var innerhtml = "";
            if(response.message != "") {
                $('#unattempted-users').modal('show');
                $.each(response.message, function( index, value ) {
                    innerhtml += "<tr><td>"+value[0]+"</td> <td>"+value[1]+"</td> <td>"+value[2]+"</td></tr>";
                });
                $('#unattempted-users').find('.modal-body').find('#unattempted-table').append(innerhtml);
            } else {
                innerhtml = "No more records are there";
                $('#unattempted-users').find('#read-more').hide();
                $('#unattempted-users').find('.modal-body').append(innerhtml);
            }
        })
        .fail(function(response) {
            alert( "Error while updating the data. Please try again" );
        });
    }
    $(document).on('click','.userattemptdetails',function(e){
        e.preventDefault();
        var $this = $(this);
        var $usermodal = $('#userdetailmodal');
        $usermodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
        $usermodal.modal('show');

    });
     $(document).on('click','.show_report',function(e){
        var $surveyName = '{{$survey->survey_title}}';
        e.preventDefault();
        var $this = $(this);
        var $username = $this.data('username');
        var $triggermodal = $('#view_report');
        var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="500px" style="max-height:500px !important" frameBorder="0"></iframe>');
        $iframeobj.unbind('load').load(function(){
            if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                $triggermodal.modal('show');
            simpleloader.fadeOut();
        });
        $triggermodal.find('.modal-header-title').html($username);
        $triggermodal.find('.survey_name').html($surveyName);
        $triggermodal.find('.modal-body').html($iframeobj);
    });
    </script>
@stop