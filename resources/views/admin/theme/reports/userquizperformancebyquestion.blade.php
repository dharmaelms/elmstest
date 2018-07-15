
@section('content')@section('content')

@if ( Session::get('success') )
    <div class="alert alert-success">
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
<script>
            if (!Array.prototype.remove) {
               Array.prototype.remove = function(val) {
                   var i = this.indexOf(val);
                   return i>-1 ? this.splice(i, 1) : [];
               };
            }
            var $targetarr =  []; 
            var for_count = {{count($ques_list)}};
            for (i = 0; i < for_count; i++) {
                $targetarr.push(i+4);
            }
</script>

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
                   <!--  <div class="input-group pull-right" >
                       <button class="show-tooltip" type="button" id="exp_report"><i class="fa fa-download"></i></button>
                   </div> -->
                    <div class="pull-right" > &nbsp; </div>
                    <div class="btn-toolbar pull-right" > 
                        <a href="{{URL::to('cp/reports/individual-channel-user-performance/')}}/{{$channel_id}}/{{$user_id}}"  class="show-tooltip btn btn-primary fa fa-angle-left"> <?php echo trans('admin/reports.back'); ?></a>
                    </div>

                </div>
                <div class="box-content" style="overflow-x:scroll"> 
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
                        <thead>
                            <tr>
                                <th> {{ trans('admin/reports.user_name') }}</th>
                                <th> {{trans('admin/reports.score_wp')}} ({{trans('admin/reports.out_of')}} {{$total_mark}})</th>
                                <th> {{ trans('admin/reports.score') }}</th>
                                <th title="{{$quiz_max_time}}"> {{ trans('admin/reports.time_taken') }}</th>
                                @if(isset($ques_list))
                                    @foreach($ques_list as $key => $ques)
                                        <th title="{{strip_tags($ques)}}" >Q {{$key+1}}</th>
                                    @endforeach
                                @endif
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
            var  order_by_var= "{{Input::get('order_by','0 desc')}}";
            var  order = order_by_var.split(' ')[0];
            var  _by   = order_by_var.split(' ')[0];
            var quiz_id_js = "{{$quiz_id}}";
            var ques_ids_js = new Array();
            var channel_id_js = "{{$channel_id}}";
            var total_mark_js = "{{$total_mark}}";
            var total_time_js = "{{$quiz_max_time}}";
            var quiz_name_js = "{{$quiz_name}}";
            var user_id_js = "{{$user_id}}";
            var ques_list_js = new Array();
            // var temp = '';
            @if(isset($ques_ids))
                @foreach($ques_ids as $ques)
                    ques_ids_js.push({{$ques}});
                @endforeach
            @endif

           /* $('#exp_report').click(function(){
                var url = '{{URL::to('/')}}';
                location.href = url+'/cp/reports/csv-quiz-performance-by-question/'+quiz_name_js+'/'+quiz_id_js+'/'+channel_id_js+'/'+total_mark_js+'/'+total_time_js+'?ques_ids='+ques_ids_js;
            });*/
            // console.log('ques_ids_js'+ques_ids_js);
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
                        "url": "{{URL::to('cp/reports/ajax-user-quiz-performance-by-question/')}}",
                        "type": "POST",
                        "data": function ( d ) {
                            // d.filter = $('[name="filter"]').val();
                            d.status_filter = $('[name="status_filter"]').val();
                            d.quiz_id = quiz_id_js;
                            d.ques_ids = ques_ids_js;
                            d.channel_id = channel_id_js;
                            d.user_id = user_id_js;
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
                        // datatableOBJ.fnStandingRedraw();
                    }
                });
                function fordopage(){
                    console.log("inside point 1"+flag_page);
                    if(flag_page == 'true'){
                        console.log("inside loop Start" );
                        if(datatableOBJ){
                            console.log("inside the if")
                              datatableOBJ.fnPageChange(1);
                        }
                        console.log("inside loop End");
                    }else{
                        console.log("else Loop");
                    }
                    flag_page = 'false';
                    return false;
                }
                });

        </script>
    </div>
<style>
#modal .xs-margin{
    padding-left: 45px;
}
</style>
@stop
