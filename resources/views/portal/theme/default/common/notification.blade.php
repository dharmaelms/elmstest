
@section('content')

<style type="text/css">
    .main-content{
      width:90%;
  /* padding: 0; */
  min-height: 500px;
  float: right;
  background-color: #FFFAFF;
}
</style>
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap.min.css">
<link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/css/bootstrap-theme.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.2/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.4/js/bootstrap.min.js"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>

            <div class="box">
                <div class="box-title">
                    <h3 style="color:black"><i class="fa fa-file"></i>{{ Lang::get('notification.list_notification') }}</h3>
                </div>
                <div class="box-content" id='notifi_pages_box'>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
                        <thead>
                            <tr>
                                <!-- <th style="width:18px"><input type="checkbox"/></th> -->
                                <th>{{ Lang::get('notification.from_module') }}</th>
                                <th>{{ Lang::get('notification.message') }}</th>
                                <th>{{ Lang::get('notification.is_read') }}</th>
                                <th>{{ Lang::get('notification.notification_at') }}</th>
                                <!-- <th>Action</th> -->
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        <script>
            /* Simple Loader */
            (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
            simpleloader.init();
            $(document).ready(function(){
                var timer_read;
                /* code for DataTable begins here */
                $('.left-sidebar').remove();
                window.datatableOBJ = $('#datatable').on('processing.dt',function(event,settings,flag){
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
                }).on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                    $('#datatable tr td:nth-child(3)').each(function(index,value){if($(value).text() == "true"){ $(value).closest('tr').css('background-color','#F8E0E6')}})    
                    var noti_list = $.trim($('.notification_ids').val());
                    clearTimeout(timer_read);
                    if(noti_list !=""){
                        // alert('inside ');
                        timer_read=setTimeout(function(){ 
                        var notifications = $('.notification_ids').val();
                        $.ajax({
                            method: "GET",
                            url: "{{URL::to('/notification/mark-read')}}",
                            data: { notification_ids:$('.notification_ids').val()}
                        })
                        .done(function( msg ) {
                           datatableOBJ.fnDraw(true);
                        });
                    }, <?php echo $notifi_delay;?>); 
                    }else{
                          clearTimeout(timer_read);
                          console.log("notification_ids are empty");
                    }
                }).dataTable({
                    'autoWidth':false,
                    "serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('notification/notification-list-ajax')}}",
                        "data": function ( d ) {
                            d.filter = $('[name="filter"]').val();
                        }
                    },
                    "aaSorting": [[ 3, 'desc' ]],
                    "columnDefs": [ { "targets": [1], "orderable": false } ]
                });
                $('#datatable_filter').hide();
                $('.dataTables_filter').hide();
            });
        </script>
    </div>
@stop
