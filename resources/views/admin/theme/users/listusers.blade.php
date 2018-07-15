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
        var $targetarr = [0,5,6,7,8];
    </script>
<?php use App\Model\Common; ?>

  <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
  <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
  <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
  <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
  <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>

<!-- BEGIN Main Content -->
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <!-- <h3><i class="icon-file"></i> Users List</h3> -->
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content">               
              <div class="btn-toolbar clearfix">
                  <div class="col-md-6">
                      <form class="form-horizontal" action="">
                          <div class="form-group">
                            <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
                            <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                            <?php $filter = Input::get('filter');?>
                              <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="datatableOBJ.fnDraw();" tabindex="1">
                                  <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>All</option>
                                  <option value="ACTIVE" <?php if ($filter == 'ACTIVE') echo 'selected';?>>Active</option>
                                  <option value="IN-ACTIVE" <?php if ($filter == 'IN-ACTIVE') echo 'selected';?>>In-active</option>
                              </select>
                            </div>
                         </div>
                      </form>
                  </div>                       
                  <div class="pull-right">
                      @if(has_admin_permission(ModuleEnum::USER, UserPermission::ADD_USER))
                    
                        <a class="btn btn-primary btn-sm" href="{{ URL::to('cp/usergroupmanagement/add-user') }}">
                          <span class="btn btn-circle blue show-tooltip custom-btm">
                            <i class="fa fa-plus"></i>
                          </span>&nbsp;<?php echo trans('admin/user.add_user'); ?>
                        </a>&nbsp;&nbsp;
                      @endif

                      @if(has_admin_permission(ModuleEnum::USER, UserPermission::DELETE_USER))
                        <a class="btn btn-circle show-tooltip bulkdeleteuser" title="<?php echo trans('admin/user.bulk_user_delete'); ?>" href="#"><i class="fa fa-trash-o"></i></a>
                      @endif

                      @if(has_admin_permission(ModuleEnum::USER, UserPermission::EDIT_USER))
                        <a class="btn btn-circle show-tooltip bulkactiveuser" title="<?php echo trans('admin/user.bulk_active_user'); ?>" href="#"><i class="fa fa-check"></i></a>
                      @endif

                        @if(has_admin_permission(ModuleEnum::USER, UserPermission::EDIT_USER))
                        <a class="btn btn-circle show-tooltip bulkinactiveuser" title="<?php echo trans('admin/user.bulk_inactive_user'); ?>" href="#"><i class="fa fa-times"></i></a>
                      @endif

                      @if(has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS))
                        <a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/user.import_users_in_bulk'); ?>" href="{{ URL::to('cp/usergroupmanagement/import-users') }}"><i class="fa fa-sign-in"></i></a>
                      @endif

                      @if(has_admin_permission(ModuleEnum::USER, UserPermission::EXPORT_USERS))
                        <a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/user.export_user_in_bulk'); ?>" href="{{ URL::to('cp/usergroupmanagement/export-users') }}"><i class="fa fa-sign-out"></i></a>
                      @endif

                      @if(has_admin_permission(ModuleEnum::USER, UserPermission::IMPORT_USERS))
                        <a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/user.user_import_history') ?>" href="{{ URL::to('/cp/usergroupmanagement/userimport-history')}}"><i class="fa fa-eye"></i></a>
                      @endif

                      @if(has_admin_permission(ModuleEnum::USER, UserPermission::DELETE_USER))
                        <a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/user.bulk_delete_users'); ?>" href="{{ URL::to('/cp/usergroupmanagement/bulk-delete-users') }}"><i class="fa fa-trash-o"></i></a>
                      @endif 

                  </div>
              </div>            
              <div class="clearfix"></div>
              <table class="table table-advance" id="datatable">
                  <thead>
                      <tr>
                          <th style="width:18px"><input type="checkbox" id="allselect" /></th>
                          <th>{{ trans('admin/user.username') }}</th>
                          <th>{{ trans('admin/user.full_name') }}</th>
                          <th>{{ trans('admin/user.email_id') }}</th>
                          <th>{{ trans('admin/user.created_on') }}</th>
                          <th>{{ trans('admin/user.status') }}</th>
                          <th>{{ trans('admin/user.user_groups') }}</th>
                          <th>{{ trans('admin/user.direct') }} {{ trans('admin/assessment.channel') }}</th>
                          <th>{{ trans('admin/user.group') }} {{ trans('admin/assessment.channel') }}</th>
                          <th style="min-width:98px">Actions</th>
                      </tr>
                  </thead>
              </table>
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->

<!-- Usergroup feeds window -->
<div class="modal fade" id="groupfeeds" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                </h3>                                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body" style="padding-left: 20px;">
                ...
            </div>
            <div class="modal-footer">
                <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/user.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- Usergroup feeds window ends -->


<!-- delete window -->
<div id="deletemodal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3><i class="icon-file"></i>{{ trans('admin/user.delete_user') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body" style="padding: 20px">
                {{ trans('admin/user.modal_delete_user') }}
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-danger">{{ trans('admin/user.yes') }}</a>
              <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/user.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->

<!-- view window -->
<div class="modal fade" id="viewuser" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                        {{ trans('admin/user.view_user_details') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body" style="padding-left: 20px;">
                ...
            </div>
            <div class="modal-footer">
                <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/user.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- view window ends -->

<!-- bulk delete -->
<div id="bulkdelete" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <!--header-->
      <div class="modal-header">
          <div class="row custom-box">
              <div class="col-md-12">
                  <div class="box">
                      <div class="box-title">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h3><i class="icon-file"></i>{{ trans('admin/user.delete_users') }}</h3>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <!--content-->
      <div class="modal-body"  style="padding: 20px">
          {{ trans('admin/user.modal_deleted_selected_users') }}
      </div>
      <!--footer-->
      <div class="modal-footer">
          <a class="btn btn-danger" id="bulkdeletebtn">{{ trans('admin/user.yes') }}</a>
          <a class="btn" data-dismiss="modal" aria-hidden="true" >{{ trans('admin/user.cancel') }}</a>
      </div>
    </div>
  </div>
</div>
<!-- bulk delete ends -->

<!-- bulk activate -->
<div id="bulkactive" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <!--header-->
      <div class="modal-header">
          <div class="row custom-box">
              <div class="col-md-12">
                  <div class="box">
                      <div class="box-title">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h3><i class="icon-file"></i>{{ trans('admin/user.active_users') }}</h3>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <!--content-->
      <div class="modal-body" style="padding: 20px">
          {{ trans('admin/user.modal_active_selected_users') }}
      </div>
      <!--footer-->
      <div class="modal-footer">
          <a class="btn btn-danger" id="bulkactivebtn">{{ trans('admin/user.yes') }}</a>
          <a class="btn" data-dismiss="modal" aria-hidden="true" >{{ trans('admin/user.cancel') }}</a>
      </div>
    </div>
  </div>
</div>
<!-- bulk activate ends -->

<!-- bulk inactivate -->
<div id="bulkinactive" class="modal fade">
  <div class="modal-dialog">
    <div class="modal-content">
      <!--header-->
      <div class="modal-header">
          <div class="row custom-box">
              <div class="col-md-12">
                  <div class="box">
                      <div class="box-title">
                          <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                          <h3><i class="icon-file"></i>{{ trans('admin/user.in_active_users') }}</h3>
                      </div>
                  </div>
              </div>
          </div>
      </div>
      <!--content-->
      <div class="modal-body" style="padding: 20px">
          {{ trans('admin/user.modal_to_inactivate_selected_users') }}
      </div>
      <!--footer-->
      <div class="modal-footer">
          <a class="btn btn-danger" id="bulkinactivebtn">{{ trans('admin/user.yes') }}</a>
          <a class="btn" data-dismiss="modal" aria-hidden="true" >{{ trans('admin/user.cancel') }}</a>
      </div>
    </div>
  </div>
</div>
<!-- bulk inactivate ends -->

<!-- Assigning relation to users window -->
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
                                        {{ trans('admin/user.view_media_details') }}
                                </h3>                                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer" style="padding-right: 47px">
              <div style="float: left;" id="selectedcount"> 0 selected</div>
                <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/user.assign') }}</a>
                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/user.close') }}</a>
            </div>
        </div>
    </div>
</div>


<script type="text/javascript">

  var  start_page  = {{Input::get('start',0)}};//{{ isset($start_serv)? (int)$start_serv : 0 }};
  var  length_page = {{Input::get('limit',10)}};//{{ isset($length_page_serv)? (int)$length_page_serv : 10 }};
  var  search_var  = "{{Input::get('search','')}}";
  var  order_by_var= "{{Input::get('order_by','4 desc')}}";
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
              "url": "{{URL::to('/cp/usergroupmanagement/user-list-ajax')}}",
              "data": function ( d ) {
                  d.filter = $('[name="filter"]').val();
              }
          },
            "aaSorting": [[ Number(order), _by]],
            "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
            "drawCallback" : updateCheckBoxVals,
            "iDisplayStart":start_page,
            "pageLength":length_page,
            "oSearch": {"sSearch": search_var}
        });

    $('#datatable_filter input').unbind().bind('keyup', function(e) {
      if(e.keyCode == 13) {
        datatableOBJ.fnFilter(this.value);
        datatableOBJ.fnStandingRedraw();
      }
    });
    /* Code for dataTable ends here */

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

    //individual user delete
    $(document).on('click','.deleteuser',function(e){
      e.preventDefault();
      var $this = $(this);
      var $deletemodal = $('#deletemodal');
        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
      $deletemodal.modal('show');
    });

    //view user details
    $(document).on('click','.viewuser',function(e){
      e.preventDefault();
      simpleloader.fadeIn(200);
      $.ajax({
          type: "GET",
          url: $(this).attr('href')
      })
      .done(function( response ) {
        $('#viewuser').find('.modal-body').html(response).end().modal('show');
          simpleloader.fadeOut(200);
      })
      .fail(function() {
          alert( "Error while fetching data from server. Please try again later" );
          simpleloader.fadeOut(200);
      })
    });

    //usergroup feeds details
    $(document).on('click','.groupfeeds',function(e){
      e.preventDefault();
      var $this = $(this);
      simpleloader.fadeIn(200);
      $.ajax({
          type: "GET",
          url: $(this).attr('href')
      })
      .done(function( response ) {
        $('#groupfeeds').find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
        $('#groupfeeds').find('.modal-body').html(response).end().modal('show');
          simpleloader.fadeOut(200);
      })
      .fail(function() {
          alert( "Error while fetching data from server. Please try again later" );
          simpleloader.fadeOut(200);
      })
    });

    //user delete in bulk
    $('.bulkdeleteuser').click(function(e){
      e.preventDefault();
      var $this = $(this);
      var $bulkdelete = $('#bulkdelete');
      if($.isEmptyObject(checkedBoxes)){
          alert('Please select atleast one user');
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
        html += "</strong>Are you sure you want to delete these user(s)?<br />";
        $bulkdelete.find('.modal-body').html(html).end().modal('show');
        $('#bulkdeletebtn').unbind('click').click(function(e){
          e.preventDefault();
          var $form = $('<form></form>').prop('action','{{URL::to('/cp/usergroupmanagement/bulk-delete')}}').attr('method','post');
          var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','uids');
          $form.append($input);
          $form.appendTo('body').submit();
        })
      }
    })

    //user activate in bulk
    $('.bulkactiveuser').click(function(e){
      e.preventDefault();
      var $this = $(this);
      var $bulkactive = $('#bulkactive');
      if($.isEmptyObject(checkedBoxes)){
          alert('Please select atleast one user');
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
        html += "</strong>Are you sure you want to activate these users?<br />";
        $bulkactive.find('.modal-body').html(html).end().modal('show');
        $('#bulkactivebtn').unbind('click').click(function(e){
          e.preventDefault();
          var $form = $('<form></form>').prop('action','{{URL::to('/cp/usergroupmanagement/bulk-activate')}}').attr('method','post');
          var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','uids');
          $form.append($input);
          $form.appendTo('body').submit();
        })
      }
    })

    //user in-activate in bulk
    $('.bulkinactiveuser').click(function(e){
      e.preventDefault();
      var $this = $(this);
      var $bulkinactive = $('#bulkinactive');
      if($.isEmptyObject(checkedBoxes)){
          alert('Please select atleast one user');
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
        html += "</strong>Are you sure you want to in-activate these users?<br />";
        $bulkinactive.find('.modal-body').html(html).end().modal('show');
        $('#bulkinactivebtn').unbind('click').click(function(e){
          e.preventDefault();
          var $form = $('<form></form>').prop('action','{{URL::to('/cp/usergroupmanagement/bulk-inactivate')}}').attr('method','post');
          var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','uids');
          $form.append($input);
          $form.appendTo('body').submit();
        })
      }
    })

    /* Code for user rel starts here */
    $datatable.on('click','.userrel',function(e){
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
          url: '{{URL::to('/cp/usergroupmanagement/assign-user/')}}/'+action+'/'+$this.data('key'),
          data: 'ids='+$postdata+"&empty=true"
        })
        .done(function( response ) {
          if(response.flag == "success")
            $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
          else
            $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
          $triggermodal.modal('hide');
          setTimeout(function(){
            $('.alert').alert('close');
          },5000);
          window.datatableOBJ.fnDraw(true);
          simpleloader.fadeOut(200);
        })
        .fail(function() {
          $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong>  <?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
          window.datatableOBJ.fnDraw(true);
          simpleloader.fadeOut(200);
        })
      })
    });
    /* Code for user dams rel ends here */

    /*Code to hide success message after 5seconds*/
    $('#alert-success').delay(5000).fadeOut();
});
  
</script>

@stop
 