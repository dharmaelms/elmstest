@section('content')
<!-- BEGIN Main Content -->
<script type="text/javascript">
    if (!Array.prototype.remove) {
       Array.prototype.remove = function(val) {
           var i = this.indexOf(val);
           return i>-1 ? this.splice(i, 1) : [];
       };
    }
    var $targetarr =  [0, 1, 2, 4, 3, 5, 6];
</script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
<script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
<script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <!-- <h3><i class="icon-file"></i> Users Import History</h3> -->
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content">
                    <div class="clearfix"></div>
                        <table class="table table-advance" id="datatable">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin/assessment.file_name') }}</th>
                                    <th>{{ trans('admin/assessment.no_of_records') }}</th>
                                    <th>{{ trans('admin/assessment.success_count') }}</th> 
                                    <th>{{ trans('admin/assessment.failed_count') }}</th>
                                    <th>{{ trans('admin/assessment.status') }}</th>
                                    <th>{{ trans('admin/assessment.created_by') }}</th>
                                    <th>{{ trans('admin/assessment.created_on') }}</th>
                                </tr>
                            </thead>
                        </table>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
            var  flag_page = 'true';
            var  start_page  = {{Input::get('start',0)}};
            var  length_page = {{Input::get('limit',10)}};
            var  search_var  = "{{Input::get('search','')}}";
            var  order_by_var= "{{Input::get('order_by','6 desc')}}";
            var  order = order_by_var.split(' ')[0];
            var  _by   = order_by_var.split(' ')[0];

      (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
            simpleloader.init();
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
                            "url": "{{URL::to('cp/assessment/ajax-questionbank-import-history/')}}"
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
                        // datatableOBJ.fnStandingRedraw();
                    }
                });
            });
</script>
<!-- END Main Content -->
@stop
 
