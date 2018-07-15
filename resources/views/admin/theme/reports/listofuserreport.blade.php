@section('content')@section('content')
<style type="text/css">
    .modal-table-data{
        overflow-y: auto;
        float: none !important;
        border: 1px solid #e6e6e6;
        padding: 10px;
        margin-top: 20px;
        margin-bottom: 20px;
        overflow: hidden;
    }
</style>
<script>
    /* Function to remove specific value from array */
    if (!Array.prototype.remove) {
       Array.prototype.remove = function(val) {
           var i = this.indexOf(val);
           return i>-1 ? this.splice(i, 1) : [];
       };
    }
    var $targetarr =  []; 
</script>
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
<script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
<script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
<div class="row custom-box">
    <div class="col-md-12">
        <div class="box">
             <div class="box-title">
                <h3 style="color:white">{{$title}}</h3>
            </div>
            <!-- <div style="text-align: right; padding: 5px;">
            <a class="btn btn-circle show-tooltip" title="{{ trans('admin/reports.help') }}" data-toggle="modal" href="#help" style="margin-left: 6px;"><i class="fa fa-question"></i></a>
            <span class="btn btn-large btn-primary">
                <div class="content">
                    <a href="" data-toggle="modal" id="yet-to-start-users" class="yet-to-start" style=" color: #fff !important;">{{trans('admin/reports.yet_to_start')}}</a>
                </div>
            </span>
            </div> -->
            <div class="box-content">
                <div class="clearfix"></div>
                <table class="table table-advance" id="datatable">
                    <thead>
                        <tr>
                            <th class= 'text-left'>{{trans('admin/reports.user_name')}}</th>
                            <th class= 'text-left'>{{trans('admin/reports.performance')}}</th>
                            <th class= 'text-left'>{{trans('admin/reports.completion')}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
   
    <script>
  
        var  flag_page = 'true';
        var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";
        var  order_by_var= "{{Input::get('order_by','1 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];
        $('.ranges ul li').eq(3).hide();
        $('.range_inputs').hide();
        $(document).ready(function(){
            /* code for DataTable begins here */
            window.datatableOBJ = $('#datatable').on('processing.dt',function(event,settings,flag){
                if(flag == true)
                    simpleloader.fadeIn();
                else
                    simpleloader.fadeOut();
                
            }).on('draw.dt',function(event,settings,flag){
                $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                }).dataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('cp/reports/ajax-user-list/')}}",
                        "data": function ( d ) {
                        }
                    },
                    "aaSorting": [[ Number(order), _by  ]],
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                    "iDisplayStart":start_page,
                    "pageLength":length_page,
                    "oSearch": {"sSearch": search_var}
                });

            $('#datatable_filter input').unbind().bind('keyup', function(e) {
                if(e.keyCode == 13) {
                    datatableOBJ.fnFilter(this.value);
                }
            });
            function fordopage(){
                if(flag_page == 'true'){
                    if(datatableOBJ){
                          datatableOBJ.fnPageChange(1);
                    }
                }
                flag_page = 'false';
                return false;
            }
            });
            $('.yet-to-start').on('click', function(){
                $('#assigned-user').modal('show');
                var $datatable2 = $('#yet-to-start-table');
                window.datatableOBJ2 = $datatable2.on('processing.dt',function(event,settings,flag){
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                }).dataTable({
                    "serverSide": true,
                    "destroy": true,
                    "ajax": {
                        "url": "{{URL::to('cp/reports/assigned-users-list')}}",
                        "data": function ( d ) {
                        }
                    },
                    "aaSorting": [[ 2, 'desc' ]],
                    "iDisplayStart":start_page,
                    "pageLength":length_page,
                    "oSearch": {"sSearch": search_var},
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ]
                });
                $('#yet-to-start-table_filter input').unbind().bind('keyup', function(e) {
                if(e.keyCode == 13) {
                    datatableOBJ2.fnFilter(this.value);
                }
            });
            });

    </script>
</div>
<div id="help" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3><i class="icon-file"></i><?php echo trans('admin/reports.report_info'); ?></h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <!--content-->
            <div class="modal-body">
                <br>
                <ul>
                    <?php echo trans('admin/reports.report_info_note'); ?>
                </ul>
                <br>
            </div>
            <!--footer-->
            <div class="modal-footer">
                <a class="btn btn-success" data-dismiss="modal" >
                    <i class="icon-file"></i><?php echo trans('admin/user.ok'); ?></a>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="assigned-user" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
<div class="modal-dialog modal-lg">
    <div class="modal-content dashWidth900">
        <div class="modal-header">
            <div class="row custom-box">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-title">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h3 class="modal-header-title">
                                <i class="icon-file"></i>
                                     {{ trans('admin/reports.user_not_viewed_count')}} {{trans('admin/reports.any_item')}}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="box-content col-md-10 col-md-offset-1 modal-table-data">
            <div class="clearfix"></div>
            <table class="table table-advance" id="yet-to-start-table">
                <thead>
                    <th class= 'text-left'>{{trans('admin/dashboard.username')}}</th>
                    <th class= 'text-left'>{{trans('admin/dashboard.user_fullname')}}</th>
                    <th class= 'text-left'>{{trans('admin/dashboard.user_email')}}</th>
                </thead>
            </table>    
            </div>
        <div class="modal-footer">
            <a class="btn btn-success" href="{{URL::to('/cp/reports/export-assigned-users/')}}">{{ trans('admin/reports.export_reports') }}</a>
            <a class="btn btn-danger" data-dismiss="modal">{{ trans('admin/dashboard.close') }}</a>
        </div>
    </div>
</div>
</div>
<style>
#modal .xs-margin{
    padding-left: 45px;
}
</style>
@stop
