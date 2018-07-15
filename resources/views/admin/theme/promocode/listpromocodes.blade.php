@section('content')
    @if ( Session::get('success') )
        <div class="alert alert-success" id="alert-success">
          <button class="close" data-dismiss="alert">×</button>
          {{ Session::get('success') }}
        </div>
    <?php Session::forget('success'); ?>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
          <button class="close" data-dismiss="alert">×</button>
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

  <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
  <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
  <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
  <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
  <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
   <script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [0,6,7,9];
    </script>
<!-- BEGIN Main Content -->
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content"> 
                <div class="btn-toolbar clearfix">
                    <div class="col-md-6">
                        <form class="form-horizontal" action="">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/promocode.status') }} :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                <?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
                                <select class="form-control chosen" name="filter" id="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>All</option>
                                    <option value="ACTIVE" <?php if ($filter == 'active') echo 'selected';?>>{{ trans('admin/promocode.active') }}</option>
                                    <option value="IN-ACTIVE" <?php if ($filter == 'in-active') echo 'selected';?>>{{ trans('admin/promocode.inactive') }}</option>
                                </select>
                              </div>
                           </div>
                            <!--start of filter-->
                            <div class="form-group">
                            <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/promocode.program_type') }} :</b></label>
                                <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                        <?php $filters = Input::get('filters'); $filters = strtolower($filters); ?>
                                        <select class="form-control chosen" name="filters" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                            <option value="all" <?php if ($filters == 'all') echo 'selected';?>>{{ trans('admin/promocode.program_all') }}</option>
                                            <option value="package" <?php if ($filters == 'package') echo 'selected';?>>{{ trans('admin/promocode.program_package') }}</option>
                                            <option value="course" <?php if ($filters == 'course') echo 'selected';?>>{{ trans('admin/promocode.program_course') }}</option>
                                            <option value="content_feed" <?php if ($filters == 'content_feed') echo 'selected';?>>{{ trans('admin/promocode.program_content_feed') }}</option>
                                        </select>
                                      </div>
                                   </div>
                            <!-- end of filter-->
                        </form>
                    </div>
                    <div class="btn-toolbar pull-right clearfix">
                        
                        <div class="btn-group">
                           <div class="pull-right">
                           <?php
                            $add = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::ADD_PROMO_CODE);
                            $export = has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::EXPORT_PROMO_CODE);
                            ?>
                            @if($add)
                                <a class="btn btn-primary btn-sm" href="{{ URL::to('cp/promocode/add-promocode') }}">
                                  <span class="btn btn-circle blue show-tooltip custom-btm">
                                    <i class="fa fa-plus"></i>
                                  </span>&nbsp;{{ trans('admin/ecommerce.add_promocode') }}
                                </a>&nbsp;&nbsp;
                            @endif
                            @if($export)
                                <a class="btn btn-circle show-tooltip" title="{{ trans('admin/ecommerce.export_promocode') }}" href="{{ URL::to('cp/promocode/export-promocode') }}"><i class="fa fa-sign-out"></i></a>
                            @endif
                    </div>
                        <!--Channel - User mapping -->
                        <?php 
                            $program_type = 'content_feed';
                            $program_sub_type = 'single'; 
                        ?>
                        </div>
                    </div>
                    <br/><br/>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
                        <thead>
                            <tr>
                                <th style="width:18px"><input type="checkbox" id="checkall" /></th>
                                <th style="width:20% !important">{{ trans('admin/promocode.promocode') }}</th>
                                <th>{{ trans('admin/promocode.start_date') }}</th>
                                <th>{{ trans('admin/promocode.end_date') }}</th>
                                <th>{{ trans('admin/promocode.max_redeem') }}</th>
                                <th>{{ trans('admin/promocode.redeemcount') }}</th>
                                <th>{{ trans('admin/promocode.applied_to') }}</th>
                                <th>{{ trans('admin/promocode.status') }}</th>
                                <th>{{ trans('admin/promocode.actions') }}</th>
                                <?php if(isset($permissions) && is_array($permissions) && (array_key_exists('view-content-feeds', $permissions) || array_key_exists('edit-content-feeds', $permissions) || array_key_exists('delete-content-feeds', $permissions))) {?>
                                <th style="min-width:113px">{{ trans('admin/promocode.actions') }}</th>
                                <?php } else { ?> <script>$targetarr.pop()</script>  <?php } ?>
                            </tr>
                        </thead>
                    </table>
                </div>
                        
                        <div class="row">
                            
                            <div class="col-md-2">
                            </div>
                            
                            <div class="col-md-2">
                               <a class="show-tooltip feedrel badge badge-grey thick btn-lime">
                                A
                               </a> {{ trans('admin/promocode.program_all') }}
                            </div>

                            <div class="col-md-2">
                              <a class="show-tooltip feedrel badge badge-grey badge-success">0</a> {{ trans('admin/promocode.program_content_feed') }}  
                            </div>
                            
                            <div class="col-md-2">
                                <a class="show-tooltip feedrel badge badge-grey badge-info">0</a>
                                {{ trans('admin/promocode.program_course') }} 
                            </div>

                            <div class="col-md-2">
                                <a class="show-tooltip feedrel badge badge-grey badge-important">0</a> {{ trans('admin/promocode.program_package') }} 
                            </div>

                        </div>
                    </div>
              
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->

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
                                <h3><i class="icon-file"></i>{{ trans('admin/promocode.delete_promocode') }}</h3>                                                 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body" style="padding: 20px">               
                {{ trans('admin/promocode.modal_delete_promocode') }}
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-danger">{{ trans('admin/promocode.yes') }}</a>
              <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/promocode.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->

<!-- View Promocode -->
<div class="modal fade" id="viewpromocode" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{ trans('admin/promocode.view_promocode_details') }}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/promocode.close') }}</a>
                    </div>
                </div>
            </div>
        </div>
<!-- View Promocode Ends-->

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
                                        {{ trans('admin/promocode.view_media_details') }}
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
                <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/promocode.assign') }}</a>
                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/promocode.close') }}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on('click','.viewfeed',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $viewpromocode = $('#viewpromocode');
                    simpleloader.fadeIn(200);
                    $.ajax({
                        type: "GET",
                        url: $(this).attr('href')
                    })
                    .done(function( response ) {
                        $viewpromocode.find('.modal-body').html(response).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
                });
</script>
<script type="text/javascript">

    //individual user delete
    $(document).on('click','.deletepromocode',function(e){
      e.preventDefault();
      var $this = $(this);
      var $deletemodal = $('#deletemodal');
        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
      $deletemodal.modal('show');
    });

    /* Simple Loader */
    (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
    simpleloader.init();

    $(document).ready(function(){
     
     var  start_page  = {{Input::get('start',0)}};
            var  length_page = {{Input::get('limit',10)}};
            var  search_var  = "{{Input::get('search','')}}";
            var  order_by_var= "{{Input::get('order_by','2 desc')}}";
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

     $('#alert-success').delay(5000).fadeOut();
                /* code for DataTable begins here */
                var $datatable = $('#datatable');
                window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag){
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body'});
                }).dataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('/cp/promocode/promocode-list-ajax')}}",
                        "data": function ( d ) {
                            d.filter = $('[name="filter"]').val();
                            d.filters = $('[name="filters"]').val();
                            //d.filters = 'single';
                        },
                        "error" : function(){
                            //alert('Please check if you have an active session.');
                            //window.location.replace("{{URL::to('/')}}");
                            simpleloader.fadeOut(200);
                        }
                    },
                    "aLengthMenu": [
                        [10, 15, 25, 50, 100],
                        [10, 15, 25, 50, 100]
                    ],
                    "iDisplayLength": 10,
                    "aaSorting": [[ Number(order), _by]],
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
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
          url: '{{URL::to('/cp/promocode/assign-promocode/')}}/'+$this.data('key'),
          data: 'ids='+$postdata+"&empty=true&program_type="+action
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
          simpleloader.fadeOut(200);
          window.datatableOBJ.fnDraw(true);
        })
        .fail(function() {
          $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong>  <?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
          simpleloader.fadeOut(200);
          window.datatableOBJ.fnDraw(true);
        })
      })
    });
    /* Code for user dams rel ends here */
   });
</script>

@stop