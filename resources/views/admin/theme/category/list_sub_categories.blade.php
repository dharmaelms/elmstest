@section('content')
<?php use App\Model\Common;use App\Model\Category; ?>

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
        <script src='//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js'></script>
@if ( Session::get('success') )
  <div class="alert alert-success">
        <button class="close" data-dismiss="alert">×</button>
        <!-- <strong>Success!</strong><br> -->
        {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
 <script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [0,3,5];
    </script>
<div class="row custom-box">
        <div class="col-md-12">
            <div class="box">
            <?php 
             $cat_name=Category::where('slug','=',$parentslug)->value('category_name'); ?>
                <div class="box-title">
                    <h3 style="color:black"><i class="fa fa-folder"></i>{{ trans('admin/category.sub_cat_of') }}' {{ html_entity_decode($cat_name) }} '</h3>
                </div>

                <div class="box-content">    
                    <div class="col-md-6">
                        <form class="form-horizontal" action=" ">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                  <option value="ALL" <?php if (Input::get('filter')== 'ALL') echo 'selected';?>>{{trans('admin/category.all')}}</option>
                                        <option value="ACTIVE" <?php if (Input::get('filter')== 'ACTIVE') echo 'selected';?>>{{trans('admin/category.active')}}</option>
                                        <option value="IN-ACTIVE" <?php if (Input::get('filter')== 'IN-ACTIVE') echo 'selected';?>>{{trans('admin/category.in_active')}}</option>
                                        <option value="EMPTY" <?php if (Input::get('filter')== 'EMPTY') echo 'selected';?>>{{trans('admin/category.unassigned')}}</option>
                                </select>
                              </div>
                           </div>
                        </form>
                    </div>
                    <div class="btn-toolbar pull-right clearfix">
                        <div class="btn-group">
                        <?php 
                            if (has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::ADD_CATEGORY)) {
                        ?>
                            <div class="btn-group">
                                <a class="btn btn-primary btn-sm" href="{{url::to('cp/categorymanagement/add-children/'.$parentslug)}}">
                                    <span class="btn btn-circle blue show-tooltip custom-btm">
                                        <i class="fa fa-plus"></i>
                                    </span>&nbsp;{{ trans('admin/category.add_new_sub_cat') }}
                                </a>&nbsp;&nbsp;
                            </div>
                        <?php }
                            $delete_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::DELETE_CATEGORY);
                            if($delete_category == true){ ?>
                            <a class="btn btn-circle show-tooltip bulkdeletecategory" title="<?php echo trans("admin/manageweb.action_delete"); ?>"  href="#"><i class="fa fa-trash-o"></i></a>
                            <?php } ?>
                        </div>
                    </div>
                    <br/><br/>
                
                    <div class="clearfix"></div>
                    <?php
                       $edit_category = has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::EDIT_CATEGORY);
                    ?>
<!--                     
<form method="get" action="{{ URL::to('cp/categorymanagement/remove-child-category/') }}" name="parent_category_form">
 -->                        <table class="table table-advance" id="datatable">
                            <thead>
                                <tr>
                                    <th style="width:18px"><input type="checkbox" id="allselect" /></th>
                                    <th>{{ trans('admin/category.sub_category_name') }}</th>
                                     <th>{{ trans('admin/category.created_on') }}</th>
                                    <th>{{trans('admin/program.programs')}}</th>
                                    <th>{{trans('admin/category.status')}}</th>
                                    <?php if($edit_category==true || $delete_category==true) {?>
                                    <th>{{trans('admin/category.actions')}}</th>
                                     <?php } else { ?> <script>$targetarr.pop()</script>  <?php } ?>
                                </tr>
                            </thead>
                        </table> 
                        
         <input type="hidden" value="{{$parentslug}}" id='par_slug' name='parent_slug'>
        <!-- </form>         -->
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
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                {{ trans('admin/category.delete_sub_category') }}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding-left: 20px;">
                        {{ trans('admin/category.modal_delete_single_sub_cat') }}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{ trans('admin/category.yes') }}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/category.close') }}</a>
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
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                {{ trans('admin/category.view_media_details') }}
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
                        <div style="float: left;" id="selectedcount"> 0 selected</div>
                        <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/category.assign') }}</a>
                        <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/category.close') }}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="bulkdeletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{ trans('admin/category.delete_sub_categories') }}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding-left: 20px;">
                        {{ trans('admin/category.modal_delete_sub_category') }}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger" id="bulkdeletebtn">{{ trans('admin/category.yes') }}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/category.close') }}</a>
                    </div>
                </div>
            </div>
        </div>

<script type="text/javascript">
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
    /* Simple Loader */
    (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
    simpleloader.init();
    $(document).ready(function(){
        var parent='<?php echo $parentslug; ?>';
                
                var $datatable = $('#datatable');
                /* code for DataTable begins here */
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
                        "url": "{{URL::to('/cp/categorymanagement/category-list-ajax/')}}"+'/'+parent,
                        "data": function ( d ) {
                            d.filter = $('[name="filter"]').val();
                        }
                    },
                    "aaSorting": [[ Number(order), _by]],
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
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

                $(document).on('click','.deletesubcategory',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#deletemodal');
                    // if(typeof window.location.href.split('?')[1] != "undefined")
                    //  $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href') + "?" + window.location.href.split('?')[1])
                    // else
                        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
                    $deletemodal.modal('show');
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
                  $('#datatable td input[type="checkbox"]:enabled').prop('checked',$(this).prop('checked'));
                  $('#datatable td input[type="checkbox"]').trigger('change');
                  e.stopImmediatePropagation();
                });
            /* Code to get the selected checkboxes in datatable ends here*/
                /* Code for category bulk delete starts here*/

                $(document).on('click','.bulkdeletecategory',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $bulkdeletemodal = $('#bulkdeletemodal');
                    if($.isEmptyObject(checkedBoxes)){
                        alert('Please select atleast one category');
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
                        if($count > 2){
                            html += "</strong>Are you sure you want to delete these sub-categories ?<br />";
                        }
                        else{
                            html += "</strong>Are you sure you want to delete these sub-category ?<br />";
                        }
                        console.log($count);
                        $bulkdeletemodal.find('.modal-body').html(html).end().modal('show');
                        $('#bulkdeletebtn').unbind('click').click(function(e){
                            e.preventDefault();
                            var $form = $('<form></form>').prop('action','{{URL::to('/cp/categorymanagement/bulk-delete')}}'+'/'+parent).attr('method','post');
                            var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','ids');
                            $form.append($input);
                            $form.appendTo('body').submit();
                        })
                    }
                })

                /* Code for category bulk delete ends here*/


                $datatable.on('click','.categoryrel',function(e){
                    e.preventDefault();
                    simpleloader.fadeIn();
                    var $this = $(this);
                    var $triggermodal = $('#triggermodal');
                    var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
                    $iframeobj.unbind('load').load(function(){
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
                            var count = 0;
                            $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                count++;
                            });
                            $('#selectedcount').text(count+ ' selected');
                        });
                        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                        /* Code to refresh selected count ends here*/
                    })
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                       
                            var $postdata = "";
                            $.each($checkedboxes,function(index,value){
                                if(!$postdata)
                                    $postdata += index;
                                else
                                    $postdata += "," + index;
                            });

                            // Post to server
                            var action = $this.data('info');

                            simpleloader.fadeIn();
                            $.ajax({
                                type: "POST",
                                url: '{{URL::to('/cp/categorymanagement/assign-feed/')}}/'+action+'/'+$this.data('key'),
                                data: 'ids='+$postdata
                            })
                            .done(function( response ) {
                                if(response.flag == "success")
                                    $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                                else
                                    $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/category.server_error');?></div>').insertAfter($('.page-title'));
                                $triggermodal.modal('hide');
                                setTimeout(function(){
                                    $('.alert').alert('close');
                                },5000);
                                window.datatableOBJ.fnDraw(true);
                                simpleloader.fadeOut(200);
                            })
                            .fail(function() {
                                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/category.server_error');?>.</div>').insertAfter($('.page-title'));
                                window.datatableOBJ.fnDraw(true);
                                simpleloader.fadeOut(200);
                            })
                        
                    })
                });

            });


</script>
@stop