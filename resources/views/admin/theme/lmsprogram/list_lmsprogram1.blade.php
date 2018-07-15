@section('content')
    <!--<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src='//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js'></script>-->
        
        
    <!--today start-->
    <script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>
    <script type="text/javascript" src="https://cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
    <link href="https://cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css" rel="stylesheet">
    <style>
    td.details-control {
    background: url('details_open.png') no-repeat center center;
    cursor: pointer;
    }
    tr.shown td.details-control {
    background: url('details_close.png') no-repeat center center;
    }
    </style>
    <!--today end-->

@if ( Session::get('success') )
  <div class="alert alert-success" id="alert-success">
  <button class="close" data-dismiss="alert">×</button>
  <!-- <strong>Success!</strong><br> -->
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
 <script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [1,6];
    </script>
<?php
use App\Model\ManageLmsProgram;
use App\Model\Common;
use App\Model\ManageAttribute;
use App\Model\SiteSetting;
$variant_type='batch';
$variants=ManageAttribute::getVariants($variant_type);

?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
            </div>
            <div class="box-content">               
                <div class="btn-toolbar clearfix">
                <div class="col-md-6">
                        
                    
                    <div class="pull-right">
                       
                            <a  class="btn btn-primary btn-sm" href="{{url::to('cp/lmscoursemanagement/create-lmsprogram')}}">
                                <span class="btn btn-circle blue show-tooltip custom-btm">
                                    <i class="fa fa-plus"></i>
                                </span>&nbsp;{{trans('admin/lmscourse.add_new_lmsprogram')}}
                            </a>&nbsp;&nbsp;
                       
                      
<a class="btn btn-circle show-tooltip bulkdeletelmsprogram" title="<?php echo trans('admin/lmscourse.action_delete');?>" href="#"><i class="fa fa-trash-o"></i></a><br></br>
      
                    </div>
                </div>
                <div class="clearfix"></div>
               
                    <div class="table-responsive">
                        <table class="table table-advance" id="datatable" class="display">
                            <thead>
                                <tr>
                                    <th></th>
                                    <th style="width:18px"><input type="checkbox" id="allselect"/></th>
                                    <th>{{trans('admin/lmscourse.course_name')}}</th>
                                    <th>{{trans('admin/lmscourse.start_date')}}</th>
                                    <th>{{trans('admin/lmscourse.end_date')}}</th>
                                    <th>{{trans('admin/lmscourse.display_order')}}</th>
                                    @if(!empty($variants) && SiteSetting::module('Lmsprogram', 'more_batches')=='on')
                                    <th>Batches</th>
                                    @endif
                                    <th>{{trans('admin/lmscourse.status')}}</th>
                                    <th>{{trans('admin/lmscourse.actions')}}</th>
                                    <script>$targetarr.pop()</script> 
                                </tr>
                            </thead>
                        </table>
                    </div>
              
            </div>
        </div>
    </div>
</div>


        
<script type="text/javascript">

    

   
    $(document).ready(function() {
    var table = $('#datatable').DataTable( {
        "ajax": "{{URL::to('/cp/lmscoursemanagement/lmsprogram-list-ajax1')}}",
        "columns": [
            {
                "className":      'details-control',
                "orderable":      false,
                "data":           null,
                "defaultContent": ''
            },
             
            { "data": "checkbox" },
            { "data": "coursename" },
            { "data": "startdate" },
            { "data": "enddate" },
            { "data": "displayorder" },
            { "data": "batches" },
            { "data": "status" },
            { "data": "actions" }
        ],
        "order": [[1, 'asc']]
    } );
     
    // Add event listener for opening and closing details
    $('#datatable tbody').on('click', 'td.details-control', function () {
  
        var tr = $(this).closest('tr');
        //var row = table.row( tr );
 var row = $('#datatable').DataTable().row(tr);
        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            // Open this row
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    } );
});
            
function format ( d ) {
var result = '<table cellpadding="5" cellspacing="0" border="0" style="padding-left:50px;"><tr><th>{{trans('admin/lmscourse.batch_name')}}</th><th>{{trans('admin/lmscourse.start_date')}}</th><th>{{trans('admin/lmscourse.end_date')}}</th><th>{{trans('admin/lmscourse.display_order')}}</th><th>{{trans('admin/lmscourse.actions')}}</th></tr>';
//var result +='';
for (var i=0;i<d.count;i++) {
result +='<tr><td>'+d.batch[i]['batchname']+'</td>';
result +='<td>'+d.batch[i]['startdate']+'</td>';
result +='<td>'+d.batch[i]['enddate']+'</td>';
result +='<td>'+d.batch[i]['sortorder']+'</td>';
result +='<td>'+d.batch[i]['actions']+'</td></tr>';
}
result += '</table>';
return result;
}
</script>
@stop