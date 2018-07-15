@section('content')
	@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
      <button class="close" data-dismiss="alert">×</button>
   <!--    <strong>Success!</strong> -->
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

  @if ( Session::get('warning'))
    <div class="alert alert-warning">
    <button class="close" data-dismiss="alert">×</button>
    <strong>Warning!</strong>
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
        var $targetarr = [5,6,7,8];
    </script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
<script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
<script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <style>
    /*td.details-control {
    background-image:url({{ URL::asset('admin/assets/chosen-bootstrap/details_open.png')}});
	background-repeat: no-repeat;
    cursor: pointer;
	background-position:center center;
    }
    tr.shown td.details-control {
   
	background:url({{ URL::asset('admin/assets/chosen-bootstrap/details_close.png')}});
	background-repeat: no-repeat;
	cursor: pointer;
		background-position:center center;
    }*/
	table.dataTable thead th, table.dataTable thead td {
    border-bottom: none;
    padding: 10px 18px;
    }
    table.dataTable {
    border-collapse: separate;
    border-spacing: 0;
    border:none !important;
	}
	</style>

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
        var $targetarr = [2,6];
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
               <!--div deleted-->
                    
                    <div class="pull-right">
                        <a  class="btn btn-primary btn-sm" href="{{url::to('cp/lmscoursemanagement/create-lmsprogram')}}">
                                <span class="btn btn-circle blue show-tooltip custom-btm">
                                    <i class="fa fa-plus"></i>
                                </span>&nbsp;{{trans('admin/lmscourse.add_course')}}
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
                                    <!---@if(!empty($variants) && SiteSetting::module('Lmsprogram', 'more_batches')=='on')-->
                                   
                                    <!--@endif-->
									 <th>Batches</th>
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
                                                {{trans('admin/lmscourse.delete_lmsprogram')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        {{trans('admin/lmscourse.modal_delete_single_lmsprogram')}}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/lmscourse.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/lmscourse.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
<!--start batch delete-->
<div class="modal fade" id="deletebatchmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{trans('admin/lmscourse.delete_batch')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        {{trans('admin/lmscourse.modal_delete_batch')}}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/lmscourse.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/lmscourse.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
<!--end batch delete-->

<!--start-->
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
                                                {{trans('admin/lmscourse.assign')}} <?php echo trans('admin/program.programs');?>
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
                        <div style="float: left;" id="selectedcount"> 0 selected</div>
                        <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/lmscourse.assign')}}</a>
                        <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/lmscourse.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
<!--end-->

        <div class="modal fade" id="bulkdeletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{trans('admin/lmscourse.delete_lmsprogram')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        {{trans('admin/lmscourse.modal_delete_lmsprogram')}}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger" id="bulkdeletebtn">{{trans('admin/lmscourse.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/lmscourse.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
<script type="text/javascript">

    var  start_page  = {{Input::get('start',0)}};
    var  length_page = {{Input::get('limit',10)}};
    var  search_var  = "{{Input::get('search','')}}";
    var  order_by_var= "{{Input::get('order_by','3 desc')}}";
    var  order = order_by_var.split(' ')[0];
    var  _by   = order_by_var.split(' ')[1];


    
function updateCheckBoxVals(){
    $allcheckBoxes = $('#datatable td input[type="checkbox"]');
    if(typeof window.checkedBoxes != 'undefined'){
      $allcheckBoxes.each(function(index,value){
      var $value = $(value);
      if(typeof checkedBoxes[$value.val()] != "undefined")
      $('[value="'+$value.val()+'"]').prop('checked',true);
      })
    }
    if($allcheckBoxes.length > 0)
      if($allcheckBoxes.not(':checked').length > 0)
        $('#datatable thead tr th:first input[type="checkbox"]').prop('checked',false);
      else
        $('#datatable thead tr th:first input[type="checkbox"]').prop('checked',true);
  }
    /* Simple Loader */
    (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
    simpleloader.init();
    $(document).ready(function(){
                //$('#alert-success').delay(5000).fadeOut();
                /* code for DataTable begins here */
                var $datatable = $('#datatable');
               /* window.datatableOBJ = $('#datatable').on('processing.dt',function(event,settings,flag){
                   $('#datatable_processing').hide();
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                })*/
				//.DataTable({
				var table = $('#datatable').DataTable( {
                    //"serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('/cp/lmscoursemanagement/lmsprogram-list-ajax-new')}}",
                        /*"data": function ( d ) {
                            d.filter = $('[name="filter"]').val();
                        }*/
                    },
                    //"aaSorting": [[  Number(order), _by ]],
					"columns": [
            {
                //"className":'details-control',
                "orderable":false,
                "data":null,
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
		 "order": [[5, 'asc']],
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                    "drawCallback" : updateCheckBoxVals,
                    "iDisplayStart": start_page,
                    "pageLength": length_page
                    //"oSearch": {"sSearch": search_var}
                });

               /* $('#datatable_filter input').unbind().bind('keyup', function(e) {
                    if(e.keyCode == 13) {
                        datatableOBJ.fnFilter(this.value);
                       
                    }
                });*/


                /* Code to get the selected checkboxes in datatable starts here*/
                if(typeof window.checkedBoxes == 'undefined')
                  window.checkedBoxes = {};
                $datatable.on('change','td input[type="checkbox"]',function(){
                  var $this = $(this);
                  if($this.prop('checked'))
                    checkedBoxes[$this.val()] = $($this).parent().next().text();
                  else
                    delete checkedBoxes[$this.val()];
                });

                $('#allselect').change(function(e){
                  $('#datatable td input[type="checkbox"]:enabled').prop('checked',$(this).prop('checked'));
                  $('#datatable td input[type="checkbox"]').trigger('change');
                  e.stopImmediatePropagation();
                });
            /* Code to get the selected checkboxes in datatable ends here*/


                $(document).on('click','.deletelmsprogram',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#deletemodal');
                    $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
                    $deletemodal.modal('show');
                });
				
				//start batch
				$(document).on('click','.deletebatch',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#deletebatchmodal');
                    $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
                    $deletemodal.modal('show');
                });
				//end batch


				
				//dec 31 start
	  $datatable.on('click','.categoryrels',function(e){
      e.preventDefault();
      simpleloader.fadeIn();
      var $this = $(this);
      var $triggermodal = $('#triggermodal');
      var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
            
      $iframeobj.unbind('load').load(function(){
        //css code for the alignment  
        // $triggermodal.find('.modal-body').css({"top":"-21px"});         
          var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
            if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){                
                // $triggermodal.find('.modal-body').css({"top":"-25px"});
            }             
            else
              // $triggermodal.find('modal-assign').css({"top": "8px"});
            // $('#triggermodal .modal-assign').css({"top": "28px"});

            //code ends here

      $('#selectedcount').text('0 selected');

        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
          $triggermodal.modal('show');
        simpleloader.fadeOut();

        /* Code to Set Default checkedboxes starts here*/
        $.each($this.data('json'),function(index,value){
          $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
        })
        /* Code to Set Default checkedboxes ends here*/

      /* Code to refresh selected count starts here*/
      $iframeobj.contents().click(function(){
        setTimeout(function(){
		
          var count = 0;
          $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
            count++;
          });
          $('#selectedcount').text(count+ ' selected');
        },10);
      });
      $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
      /* Code to refresh selected count ends here*/
      })
      $triggermodal.find('.modal-body').html($iframeobj);
      $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

      //code for top assign button click starts here
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
      //code for top assign button click ends here


      $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
          var $postdata = "";
          if(!$.isEmptyObject($checkedboxes)){
            $.each($checkedboxes,function(index,value){
              if(!$postdata)
                $postdata += index;
              else
                $postdata += "," + index;
            });
          }
          // Post to server
          var action = $this.data('info');

          simpleloader.fadeIn();
          $.ajax({
          type: "POST",
          url: '{{URL::to('/cp/lmscoursemanagement/assign-feed/')}}/'+action+'/'+$this.data('key')+'/'+$this.data('cid')
+'/'+$this.data('bid')+'/'+$this.data('pid'),
                               
          data: 'ids='+$postdata+"&empty=true"
        })
        .done(function( response ) {
          if(response.flag == "success")
            $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/lmscourse.user_assigned');?></div>').insertAfter($('.page-title'));
          else
            $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
          $triggermodal.modal('hide');
          setTimeout(function(){
            $('.alert').alert('close');
          },5000);
          //window.datatableOBJ.fnDraw(true);
         simpleloader.fadeOut(200);
		 location.reload();
        })
        .fail(function() {
          $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong>  <?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
          window.datatableOBJ.fnDraw(true);
          simpleloader.fadeOut(200);
        })
      })
    });
	$('#alert-success').delay(5000).fadeOut();
	
				//dec 31 end
                /* Code for role bulk delete starts here*/

               
                $(document).on('click','.bulkdeletelmsprogram',function(e){
                
                    e.preventDefault();
                    var $this = $(this);
                    var $bulkdeletemodal = $('#bulkdeletemodal');
                    if($.isEmptyObject(checkedBoxes)){
                        alert('Please select atleast one lms program');
                    }
                    else{
                        var html = "<strong>";
                        var ids = ""
                        var $count = 1;
                        $.each(checkedBoxes,function(index,value){
                            ids += index+",";
                            html += $count+". "+ value+"</br>";
                            $count++;
                        })
                        html += "</strong>Are you sure you want to delete these lmsprograms?<br />";
                        $bulkdeletemodal.find('.modal-body').html(html).end().modal('show');
                        $('#bulkdeletebtn').unbind('click').click(function(e){
                            e.preventDefault();
                            var $form = $('<form></form>').prop('action','{{URL::to('/cp/lmscoursemanagement/bulk-delete')}}').attr('method','post');
                            var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','ids');
                            $form.append($input);
                            $form.appendTo('body').submit();
                        })
                    }
                })

                /* Code for role bulk delete ends here*/
//td.details-control
				
        $('#datatable tbody').on('click', 'td > a.details-control', function () {
				
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    } );
	
});
            
function format ( d ) {
if (d.count > 0) {
var result = '<table class="table table-bordered table-striped batch-table" cellpadding="5" cellspacing="0" border="0"><tr><th>{{trans('admin/lmscourse.batch_name')}}</th><th>{{trans('admin/lmscourse.start_date')}}</th><th>{{trans('admin/lmscourse.end_date')}}</th><th>{{trans('admin/lmscourse.display_order')}}</th><th>Users</th><th>{{trans('admin/lmscourse.actions')}}</th></tr>';

for (var i=0;i<d.count;i++) {
result +='<tr><td>'+d.batch[i]['batchname']+'</td>';
result +='<td>'+d.batch[i]['startdate']+'</td>';
result +='<td>'+d.batch[i]['enddate']+'</td>';
result +='<td>'+d.batch[i]['sortorder']+'</td>';
result +='<td>'+d.batch[i]['batchusers']+'</td>';
result +='<td>'+d.batch[i]['actions']+'</td></tr>';
}
result += '</table>';
}
else{
  result ="Batches not avaliable in this course";
}

return result;
}  
</script>
@stop