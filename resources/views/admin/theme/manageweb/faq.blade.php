@section('content')
@if ( Session::get('success') )
    <div class="alert alert-success">
    <button class="close" data-dismiss="alert">×</button>
    <!-- <strong>Success!</strong> -->
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
        var $targetarr =  [0,3,4];
    </script>
    <?php
        $filter_ser   =  Input::get('filter','ACTIVE');
    ?>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                    <!-- <h3 style="color:black"><i class="fa fa-file"></i> List Faq</h3> -->
                        <!-- <div class="box-tool">
                            <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
                            <a data-action="close" href="#"><i class="fa fa-times"></i></a>
                        </div> -->
                </div>
                <div class="box-content">
                    <div class="btn-toolbar clearfix">
                        <div class="col-md-6">
                            <form class="form-horizontal" action="">
                                <div class="form-group">
                                  <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
                                  <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                    <select class="form-control chosen" name="filter" data-placeholder="ACTIVE" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                        <option value="ACTIVE" <?php if ($filter_ser == 'ACTIVE') echo 'selected';?>>ACTIVE</option>
                                        <option value="INACTIVE" <?php if ($filter_ser == 'INACTIVE') echo 'selected';?>>INACTIVE</option>
                                    </select>
                                  </div>
                               </div>
                            </form>
                        </div>
                        <div class="pull-right">
                            <div class="btn-group">
                                    <!-- <a class="btn btn-circle show-tooltip" title="Add Faq" href="{{url::to('/cp/manageweb/add-faq')}}"><i class="fa fa-plus"></i></a>-->
                                    <a class="btn btn-primary btn-sm" href="{{url::to('/cp/manageweb/add-faq')}}">
                                        <span class="btn btn-circle blue show-tooltip custom-btm">
                                            <i class="fa fa-plus"></i>
                                        </span>{{trans('admin/manageweb.add_faq')}}
                                    </a>&nbsp;&nbsp;
                            </div>
                            <a class="btn btn-circle show-tooltip bulkdeletemedia" title="{{trans('admin/manageweb.bulk_delete')}}" href="#"><i class="fa fa-trash-o"></i></a>
                           
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
                        <thead>
                            <tr>
                                <th style="text-align:left"><input type="checkbox"/></th>
                                <th style="text-align:left">{{trans('admin/manageweb.question')}}</th>
                                <th style="text-align:left">{{trans('admin/manageweb.answer')}}</th>
                                <!-- <th>Asset Type</th> -->
                                <th style="text-align:left">{{trans('admin/manageweb.status')}}</th>
                                <th style="text-align:left">{{trans('admin/manageweb.action')}}</th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>

        <div class="modal fade" id="modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{trans('admin/manageweb.view_faq_details')}}
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
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/manageweb.close')}}</a>
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
                                                {{trans('admin/manageweb.faq_delete')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        {{trans('admin/manageweb.modal_delete_faq')}}?
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/manageweb.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/manageweb.close')}}</a>
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
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                                {{trans('admin/manageweb.faq_delete')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        {{trans('admin/manageweb.modal_delete_faq')}}?
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger" id="bulkdeletebtn">{{trans('admin/manageweb.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/manageweb.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
    <script>
        var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";
        var  order_by_var= "{{Input::get('order_by','1 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];

            /* Simple Loader */
            (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
            simpleloader.init();
            $(document).ready(function(){
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
                        "url": "{{URL::to('/cp/manageweb/faq-list-ajax')}}",
                        "data": function ( d ) {
                            d.filter = $('[name="filter"]').val();
                        }
                    },
                    "aaSorting": [[ Number(order), _by  ]],
                    "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                    "drawCallback" : updateCheckBoxVals,
                    "iDisplayStart":start_page,
                    "pageLength":length_page,
                    "oSearch": {"sSearch": search_var}
                });

                $('#datatable_filter input').unbind().bind('keyup', function(e) {
                    if(e.keyCode == 13) {
                        datatableOBJ.fnFilter(this.value);
                        //datatableOBJ.fnStandingRedraw();
                    }
                });

                /* Code for dataTable ends here */

                $('.bulkdeletemedia').click(function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $bulkdeletemodal = $('#bulkdeletemodal');
                    var checkedboxes = $('input[type="checkbox"]:checked',datatableOBJ.fnGetNodes());
                    if(!checkedboxes.length){
                        alert('Please select atleast one Faq');
                    }
                    else{
                        var html = "<strong>";
                        var ids = ""
                        $.each(checkedboxes,function(index,value){
                            var $value = $(value);
                            ids += $value.val()+",";
                            html += (index + 1)+". "+ $value.parent().next().text()+"</br>";
                        })
                        html += "</strong>Are you sure you want to delete these entries?<br />";
                        $bulkdeletemodal.find('.modal-body').html(html).end().modal('show');
                        $('#bulkdeletebtn').unbind('click').click(function(e){
                            e.preventDefault();
                            var $form = $('<form></form>').prop('action','{{URL::to('/cp/manageweb/bulk-delete-faq')}}').attr('method','post');
                            var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','ids');
                            $form.append($input);
                            $form.appendTo('body').submit();
                        })
                    }
                })

                $(document).on('click','.deletemedia',function(e){
                    e.preventDefault();
                    var $this = $(this);
                    var $deletemodal = $('#deletemodal');
                    // if(typeof window.location.href.split('?')[1] != "undefined")
                    //  $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href') + "?" + window.location.href.split('?')[1])
                    // else
                        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
                    $deletemodal.modal('show');
                });
                $(document).on('click','.ajax',function(e){
                    e.preventDefault();
                    simpleloader.fadeIn(200);
                    $.ajax({
                        type: "GET",
                        url: $(this).attr('href')
                    })
                    .done(function( response ) {
                        $('#modal').find('.modal-body').html(response).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
                });
            });
        </script>
    </div>
@stop