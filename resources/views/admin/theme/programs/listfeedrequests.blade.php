@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
 <!--  <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
<?php use App\Model\User;use App\Model\Common;  ?>

  
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>


<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                    <!-- <h3 style="color:black"><i class="fa fa-file"></i><?php echo trans("admin/program.program");?> Access Request</h3> -->
                    <div class="box-tool">
                        <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
                        <a data-action="close" href="#"><i class="fa fa-times"></i></a>
                    </div>
                </div>
            <div class="box-content">               
                <div class="btn-toolbar clearfix">
                <div class="col-md-6">
                        <form class="form-horizontal" action=" ">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.showing')}} :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                  <option value="ALL" <?php if (Input::get('filter')== 'ALL') echo 'selected';?>>{{trans('admin/program.all')}}</option>
                                        <option value="GRANTED" <?php if (Input::get('filter')== 'GRANTED') echo 'selected';?>>Granted</option>
                                        <option value="DENIED" <?php if (Input::get('filter')== 'DENIED') echo 'selected';?>>Denied</option>
                                        <option value="PENDING" <?php if (Input::get('filter')== 'PENDING') echo 'selected';?>>Pending</option>
                                </select>
                              </div>
                           </div>
                        </form>
                    </div>
                </div>
                <div class="clearfix"></div><br>
                
                    <div class="table-responsive">
                        <table class="table table-advance" id="datatable">
                            <thead>
                                <tr>
                                    <!-- <th style="width:18px"><input type="checkbox" id="allselect"/></th> -->
                                    <th><?php echo trans("admin/program.program");?> {{trans('admin/program.title')}}</th>
                                    <th>{{trans('admin/program.requested_url')}}</th>
                                    <th>Email</th>
                                    <th>Created At</th>
                                    <th>Updated At</th>
                                    <th>{{trans('admin/program.status')}}</th>
                                    <th>{{trans('admin/program.actions')}}</th>
                                </tr>
                            </thead>
                        </table>
                    </div>
            </div>
        </div>
    </div>
</div>

<div class="modal fade" id="deny" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                               {{trans('admin/program.deny_access_request')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding-left: 20px;">
                        {{trans('admin/program.modal_deny_request')}}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/program.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="modal fade" id="grant" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                {{trans('admin/program.grant_access_request')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding-left: 20px;">
                        Are you sure you want to grant this access?
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger" id="bulkdeletebtn">{{trans('admin/program.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
<script type="text/javascript">
    var  start_page  = {{Input::get('start',0)}};
    var  length_page = {{Input::get('limit',10)}};
    var  search_var  = "{{Input::get('search','')}}";
    var  order_by_var= "{{Input::get('order_by','6 desc')}}";
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

                /* code for DataTable begins here */
                var $datatable = $('#datatable');
                window.datatableOBJ = $('#datatable').on('processing.dt',function(event,settings,flag){
                    $('#datatable_processing').hide();
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                }).dataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('/cp/contentfeedmanagement/requested-feeds-ajax')}}",
                        "data": function ( d ) {
                            d.filter = $('[name="filter"]').val();
                        }
                    },
                    "aaSorting": [[Number(order), _by]],
                    "columnDefs": [ { "targets": [6], "orderable": false } ],
                    "drawCallback" : updateCheckBoxVals,
                    "iDisplayStart": start_page,
                    "pageLength": length_page,
                    "oSearch": {"sSearch": search_var}
                });

                $('#datatable_filter input').unbind().bind('keyup', function(e) {
                    if(e.keyCode == 13) {
                        datatableOBJ.fnFilter(this.value);
                       
                    }
                });


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
                  $('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
                  $('#datatable td input[type="checkbox"]').trigger('change');
                  e.stopImmediatePropagation();
                });
            /* Code to get the selected checkboxes in datatable ends here*/


                $datatable.on('click','.denyrequest',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#deny');
                    $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href')).end().modal('show');
                })

                $datatable.on('click','.grantrequest',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#grant');
                    $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href')).end().modal('show');
                })



                /* Code for role bulk delete starts here*/

               
                $(document).on('click','.bulkdeleterole',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $bulkdeletemodal = $('#bulkdeletemodal');
                    if($.isEmptyObject(checkedBoxes)){
                        alert('Please select atleast one role');
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
                        html += "</strong>Are you sure you want to delete these entries?<br />";
                        $bulkdeletemodal.find('.modal-body').html(html).end().modal('show');
                        $('#bulkdeletebtn').unbind('click').click(function(e){
                            e.preventDefault();
                            var $form = $('<form></form>').prop('action','{{URL::to('/cp/rolemanagement/bulk-delete')}}').attr('method','post');
                            var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','ids');
                            $form.append($input);
                            $form.appendTo('body').submit();
                        })
                    }
                })

                /* Code for role bulk delete ends here*/

            });
</script>
@stop
