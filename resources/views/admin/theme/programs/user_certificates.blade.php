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
        var $targetarr =  [0, 1, 2, 4];
    </script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                </div>
                <div class="box-content">
                    <div class="btn-toolbar clearfix">
                        <div class="col-md-6">
                            <form class="form-horizontal" action="">
                                <div class="form-group">
                                  <label class="col-sm-2 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/program.select_channel') }} :</b></label>
                                  <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                    <?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
                                    <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                        @foreach($programs as $key => $program)
                                            <option value="{{ $program->program_id }}">{{ $program->program_title }}</option>
                                        @endforeach
                                    </select>
                                  </div>
                               </div>
                            </form>
                        </div>
                    </div>
                    <div class="pull-right" style="padding-bottom: 10px">
                        <b>Export</b> <a class="btn btn-circle show-tooltip" id="export_users" title="<?php echo trans('admin/user.export_user_in_bulk'); ?>" href="{{ URL::to('cp/contentfeedmanagement/export-certificate-users') }}"><i class="fa fa-sign-out"></i></a>
                        <input type="hidden" id="export_hidden_users" name="export_hidden_users" value="{{URL::to('/cp/contentfeedmanagement/export-certificate-users' )}}">
                    </div>
                    <br/>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
                        <thead>
                            <tr>
                                <th class="text-left">{{ trans('admin/user.username') }}</th>
                                <th class="text-left">{{ trans('admin/user.full_name') }}</th>
                                <th class="text-left">{{ trans('admin/user.email_id') }}</th>
                                <th class="text-left">{{ trans('admin/program.issued_date') }}</th>
                                <th class="text-left">{{trans('admin/program.action')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script>
        var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";
        var  order_by_var= "{{Input::get('order_by','3 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];

        $(document).ready(function() {
            $('#alert-success').delay(5000).fadeOut();
            /* code for DataTable begins here */
            var $datatable = $('#datatable');
            window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag) {
                if(flag == true)
                    simpleloader.fadeIn();
                else
                    simpleloader.fadeOut();
            }).on('draw.dt',function(event,settings,flag) {
                $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
            }).dataTable({
                "autoWidth": false,
                "serverSide": true,
                "ajax": {
                    "url": "{{ URL::to('/cp/contentfeedmanagement/certificate-users-list/') }}",
                    "data": function ( d ) {
                        d.filter = $('[name="filter"]').val();
                    }
                },
                "aaSorting": [[Number(order), _by ]],
                "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                "iDisplayStart": start_page,
                "pageLength": length_page,
                "oSearch": {"sSearch": search_var}
            });

            $('#datatable_filter input').unbind().bind('keyup', function(e) {
                if(e.keyCode == 13) {
                    datatableOBJ.fnFilter(this.value);
                }
            });
            /* Code for dataTable ends here */

            

            /* Code for Export users starts here */
            $("#export_users").click(function(){
                var channel = $('[name="filter"]').val();
                var export_hidden_users = $("#export_hidden_users").val();
                var url = export_hidden_users+"/"+channel;
                $(this).attr('href', url);
            });
            /* Code for Export users ends here */
        })
    </script>
@stop