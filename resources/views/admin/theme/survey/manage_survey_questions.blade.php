@section('content')
    @if ( Session::get('success'))
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert" id="alert-success">×</button>
            <!-- <strong>Success!</strong> -->
            {{ Session::get('success') }}
        </div>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
        <!--    <strong>Error!</strong> -->
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
        var $targetarr =  [0, 2, 3, 4, 5];
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
            .font-14{
                font-size: 14px;
            }
            .form-group .control-label{width: 40%;}
            .form-group .controls{width: 60%;}
            .top-field{width: 30%;}
            .box-title {padding: 10px !important;color: #6C7A89;}
        @media screen and (-webkit-min-device-pixel-ratio:0) {
            .form-group .control-label{width: 47%;}
            .form-group .controls{width: 53%;}
            .top-field{width: 34.5% !important;}
      }
    </style>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
            <div class="box-title"><b>{{ trans("admin/survey.survey_name") }} : {{ $survey_name }}</b></div>
                <div class="box-content">
                    <div class="col-md-12 margin-bottom-20">
                        <div class="btn-toolbar pull-right clearfix margin-bottom-20">
                            @if($question_count < 25) 
                                <div class="btn-group">
                                    <div class="btn-group">
                                        <a class="btn btn-primary btn-sm" href="{{url::to('/cp/survey/add-question/'.$sid)}}">
                                            <span class="btn btn-circle blue show-tooltip custom-btm">
                                                <i class="fa fa-plus"></i>
                                            </span>&nbsp;{{trans('admin/survey.add_question')}}
                                        </a>&nbsp;&nbsp;
                                    </div>
                                </div>
                            @else
                                <div class="btn-group">
                                    <div class="btn-group">
                                        <a style="cursor:not-allowed" class="btn btn-gray btn-sm" href="#" title="{{ trans("admin/survey.max_question_msg") }}">
                                            <span class="btn btn-circle btn-gray blue show-tooltip custom-btm">
                                                <i class="fa fa-plus"></i>
                                            </span>&nbsp;{{trans('admin/survey.add_question')}}
                                        </a>&nbsp;&nbsp;
                                    </div>
                                </div>
                            @endif
                            <div class="btn-group">
                                <div class="btn-group">
                                    <a class="btn btn-primary btn-sm" href="{{ URL::to('/cp/survey/list-survey/') }}">
                                        <span class="show-tooltip custom-btm">
                                            <i class="fa fa-angle-left"></i>
                                        </span>&nbsp;{{trans('admin/survey.back')}}
                                    </a>&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <!-- <form class="form-horizontal" action="" name="filterform"> -->
                        <div id="advancesearch" style="display:none;">
                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
                            </div>
                            <div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
                            </div>
                        </div>
                    <!-- </form> -->
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable-questions">
                        <thead>
                            <tr class="survey-header">
                                <th><input type="checkbox" id="checkall" /></th>
                                <th>{{trans('admin/survey.order_by')}}</th>
                                <th style="width: 350px">{{trans('admin/survey.question_title')}}</th>
                                <th>{{trans('admin/survey.type')}}</th>
                                <th>{{trans('admin/survey.mandatory')}}</th>
                                <th style="min-width: 100px">{{trans('admin/survey.actions')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                            {{trans('admin/survey.delete_question')}}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        Are you sure you want to delete this {{ trans('admin/survey.survey_question') }} ?
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/survey.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/survey.close')}}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                            {{trans('admin/program.view_program_details')}}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer" style="padding-right: 38px">
                        <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/program.assign')}}</a>
                        <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript">
        var  order_by_var= "{{Input::get('order_by','1 asc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];
        $(document).ready(function(){
            /*Code to hide success message after 2 seconds*/
            $('.alert-success').delay(2000).fadeOut();

            /* code for survey DataTable begins here */
            var $datatable = $('#datatable-questions');
            var datatableOBJ  = $('#datatable-questions').on('processing.dt',function(event,settings,flag){
                if(flag == true)
                    simpleloader.fadeIn();
                else
                    simpleloader.fadeOut();
            }).on('draw.dt',function(event,settings,flag){
                $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                $('td:nth-child(3) div ', '#datatable-questions tr').each(function(){
                    if($(this).height() > 75){
                        $(this).readmore({maxHeight: 54, moreLink: '<a href="#">Read more</a>',lessLink: '<a href="#">{{trans('admin/program.close')}}</a>'});
                        }
                    })
            }).dataTable({
                "serverSide": true,
                "destroy": true,
                "ajax": {
                  "url": "{{URL::to('cp/survey/list-survey-questions')}}",
                  "data": function ( d ) {
                            d.survey_id = <?php echo $sid; ?>;
                    }
                },
                "columns": [
                            { data: 'checkbox', render: function ( data, type, row ) {
                                    return '<input type="checkbox">';
                                }
                            },
                            { data: 'order_by' },
                            { data: 'question_title' },
                            { data: 'type' },
                            { data: 'mandatory' },
                            { data: 'actions'},
                    ],
                "aaSorting": [[ Number(order), _by]],
                "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                "language": {  /* To remove (filtered from 1 total entries) msg from datatable */    
                        "infoFiltered": ""
                }
            });

           $('#datatable-questions_filter input').unbind();
            $('#datatable-questions_filter input').bind('keyup', function(e) {
                if(e.keyCode == 13) {
                     datatableOBJ.fnFilter(this.value);
                }
            });
            /* code for survey DataTable ends here */

            $(document).on('click','.deletequestion',function(e){
              e.preventDefault();
              var $this = $(this);
              var $deletemodal = $('#deletemodal');
                $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
              $deletemodal.modal('show');
            });

        });

    </script>
@stop