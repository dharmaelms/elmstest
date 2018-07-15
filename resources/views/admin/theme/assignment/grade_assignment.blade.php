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
        var $targetarr = [0, 1, 2, 3, 5, 6, 7, 8];
    </script>
     <style type="text/css">
        .survey-header th{
            text-align: left !important;
        }
    </style>
    <script src="{{ URL::asset('admin/assets/jquery/jquery-2.1.1.min.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.css')}}">

    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>

    <script src="{{ URL::asset('admin/js/readmore.js')}}"></script>

    <style type="text/css">
        @media (min-width: 1200px){
            .wdthChange {
                margin-left: 3.667%;
                width: 100%;
            }
        }
        @media screen and (-webkit-min-device-pixel-ratio:0) {
            .form-group .control-label{width: 47%;}
            .form-group .controls{width: 53%;}
            .top-field{width: 34.5% !important;}
        }
        .table-advance tbody > tr:nth-child(even) > .readmore-js-toggle {
            background-color: #f6f6f6;
        }
        .font-14{
            font-size: 14px;
        }
        .form-group .control-label{width: 40%;}
        .form-group .controls{width: 60%;}
        .top-field{width: 30%;}
        .summary li{
            display: inline;
            padding-left:10px;
        }
        .box-title {
            padding: 10px !important;
            color: #6C7A89;
        }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
            <div class="box-title">
                <b>{{trans('admin/assignment.assignment_name')}} : {{ $name }}</b>
            </div>
                <div class="box-content">
                    <div class="col-md-3">
                      <form class="form-horizontal" action="">
                            <label class="col-sm-5 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
                            <div class="col-sm-7 col-lg-7 controls" style="padding-left: 0px">
                            
                              <select class="form-control chosen" name="submission_type" data-placeholder="YET TO REVIEW" id="submission_type-filter" tabindex="1">
                                    <option value="YET_TO_REVIEW" <?php if ($submission_type == 'YET_TO_REVIEW') {echo 'selected';}?>>
                                        {{ trans('admin/assignment.yet_to_review') }}
                                    </option>
                                    <option value="REVIEWED" <?php if ($submission_type == 'REVIEWED') {echo 'selected';}?>>
                                        {{ trans('admin/assignment.reviewed') }}
                                    </option>
                                    <option value="SAVE_AS_DRAFT" <?php if ($submission_type == 'SAVE_AS_DRAFT') {echo 'selected';}?>>
                                        {{ trans('admin/assignment.save_as_draft') }}
                                    </option>
                                    <option value="LATE_SUBMISSION" <?php if ($submission_type == 'LATE_SUBMISSION') {echo 'selected';}?>>
                                        {{ trans('admin/assignment.late_submission') }}
                                    </option>
                              </select>
                            </div>
                      </form>
                  </div>
                  <div class="col-md-9 margin-bottom-20">
                        <ul class="summary" style="list-style-type: none;" >
                            <li><strong>{{trans('admin/assignment.total_assigned_user')}}:</strong>&nbsp;<span style="color:#6C7A89">{{ $total_assigned_users  }}</span></li>
                            <li><strong>{{trans('admin/assignment.submitted_users')}}:</strong>&nbsp;{{ $submitted_users }}</li>
                            <li><strong>{{trans('admin/assignment.yet_to_grade_users')}}:</strong>&nbsp;{{ $yet_to_grade_count }}</li>
                            <li><strong>{{trans('admin/assignment.not_submitted_users')}}:</strong>&nbsp;<?php echo ($not_submitted_users < 0) ? 0 : $not_submitted_users; ?></li>&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;
                            <li>
                                <div class="btn-group pull-right">
                                    <div class="btn-group">
                                        <a class="btn btn-primary btn-sm" href="{{ URL::route('get-list') }}">
                                            <span class="show-tooltip custom-btm">
                                                <i class="fa fa-angle-left"></i>
                                            </span>&nbsp;{{trans('admin/assignment.back')}}
                                        </a>&nbsp;&nbsp;
                                    </div>
                                </div>
                            </li>
                        </ul>
                    </div>
                    <div class="clearfix"></div><br>
                    <table class="table table-advance" id="gradelist-datatable">
                        <thead>
                            <tr class="survey-header">
                                <th>{{trans('admin/assignment.username')}}</th>
                                <th>{{trans('admin/assignment.email')}}</th>
                                <th>{{trans('admin/assignment.status')}}</th>
                                <th>{{trans('admin/assignment.grade')}}</th>
                                <th>{{trans('admin/assignment.submitted_date')}}</th>
                                <th>{{trans('admin/assignment.online_text')}}</th>
                                <th>{{trans('admin/assignment.file')}}</th>
                                <th>{{trans('admin/assignment.comments')}}</th>
                                <th>{{trans('admin/assignment.actions')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="onlinetextmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" style="width: 800px;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row custom-box">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3 class="modal-header-title">
                                        <i class="icon-file"></i>
                                        {{trans('admin/assignment.uploaded_text')}}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="uploaded-text" style="padding: 20px;margin:20px;max-height: 300px; overflow: auto"></div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-success" data-dismiss="modal">{{trans('admin/assignment.close')}}</a>
                </div>
            </div>
        </div>
    </div>

    <div class="modal fade" id="commentsmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog" style="width: 800px;">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row custom-box">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3 class="modal-header-title">
                                        <i class="icon-file"></i>
                                        {{trans('admin/assignment.review_comments')}}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    <div id="review-comments" style="padding: 20px;margin:20px;max-height: 300px; overflow: auto"></div>
                </div>
                <div class="modal-footer">
                    <a class="btn btn-success" data-dismiss="modal">{{trans('admin/assignment.close')}}</a>
                </div>
            </div>
        </div>
    </div>

    <script type="text/javascript">
        var  order_by_var= "{{Input::get('order_by','4 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];

        simpleloader.init();
        /*Code to hide success message after 2 seconds*/
        $('.alert-success').delay(2000).fadeOut();

        /* code for assignment DataTable begins here */
        $(document).ready(function(){
            var $datatable = $('#gradelist-datatable');
            var datatableOBJ  = $('#gradelist-datatable').on('processing.dt',function(event,settings,flag){
                if(flag == true)
                    simpleloader.fadeIn();
                else
                    simpleloader.fadeOut();
            }).on('draw.dt',function(event,settings,flag){
                $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                $('td:nth-child(2) div', '#gradelist-datatable tr').each(function(){
                if($(this).height() > 75){
                    $(this).readmore({maxHeight: 54, moreLink: '<a href="#">Read more</a>',lessLink: '<a href="#">{{trans('admin/program.close')}}</a>'});
                    }
                })
            }).dataTable({
                "serverSide": true,
                "destroy": true,
                "ajax": {
                  "url": "{{ URL::route('grade-list') }}",
                  "data": function ( d ) {
                        d.submission_type = $('[name="submission_type"]').val();
                        d.assignment_id = <?php echo $assignment_id; ?>
                    }
                },
                "columns": [
                            { data: 'username' },
                            { data: 'email' },
                            { data: 'status' },
                            { data: 'grade' },
                            { data: 'submitted_date' },
                            { data: 'online_text' },
                            { data: 'file' },
                            { data: 'comments' },
                            { data: 'actions'},
                    ],
                "aaSorting": [[ Number(order), _by]],
                "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                "language": {  /* To remove (filtered from 1 total entries) msg from datatable */    "infoFiltered": ""
                },
                "bFilter": false
            });

            $('#gradelist-datatable_filter input').unbind();
                $('#gradelist-datatable_filter input').bind('keyup', function(e) {
                    if(e.keyCode == 13) {
                        datatableOBJ.fnFilter(this.value);
                    }
                });
            /* code for assignment DataTable ends here */

            $('#submission_type-filter').change(function() {
                datatableOBJ.fnDraw();
            });

            //show individual uploaded text
            $(document).on('click','.showonlinetext',function(e){
                e.preventDefault();
                $.ajax({
                    type: "GET",
                    url: $(this).attr('href')
                })
                    .done(function( response ) {
                        if(response.data.trim()) {
                            $('#onlinetextmodal').find('#uploaded-text').html(response.data);
                        } else {
                            $('#onlinetextmodal').find('#uploaded-text').html("No text is added");
                        }
                        $('#onlinetextmodal').modal('show');
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                    })

            });

            //show individual comments
            $(document).on('click','.reviewcomments',function(e){
                e.preventDefault();
                $.ajax({
                    type: "GET",
                    url: $(this).attr('href')
                })
                    .done(function( response ) {
                        if (response.data.trim()) {
                            $('#commentsmodal').find('#review-comments').html(response.data);
                        } else{
                            $('#commentsmodal').find('#review-comments').html("No commnets ");
                        }
                        $('#commentsmodal').modal('show');
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                    })
            });
        });
    </script>

@stop