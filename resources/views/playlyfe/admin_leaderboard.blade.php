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
            var $targetarr =  [0,1,2]; 
</script>

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('playlyfe/app.css')}}">
    <script src="{{ URL::asset('playlyfe/app.js')}}"></script>

    <div class="row">
        <div class="col-md-12">
            <div class="box">
                 <div class="box-title">
                    <h3 style="color:white">{{$title}}</h3>
                </div>
                <div class="box-content">
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable" style="cusor: pointer">
                        <thead>
                            <tr>
                                <th> Rank </th>
                                <th> Username </th>
                                <th> Points </th>
                            </tr>
                        </thead>
                    </table>
                </div>
            </div>
        </div>
        <!-- Modal -->
		<div class="modal fade" id="playerModal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
		  <div class="modal-dialog" role="document">
		    <div class="modal-content">
		      <div class="modal-header padding-15">
		        <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span></button>
		        <h4 class="modal-title" id="myModalLabel">Player Profile</h4>
		      </div>
		      <div class="modal-body padding-15">
		        <div class="profile m-top0">
                    <div class="row col-md-12">
                        <div class="col-md-5">
                            <div id="profile_pic">
                              <div class="profile-avatar">
                                <img src="{{ asset('portal/theme/default/img/avatar.png') }}" class="img-circle" alt="">
                              </div>
                              <div class="profile-name">
                              </div>
                            </div>
                        </div>
                        <div class="col-md-7" style="border: 1px solid #ddd;">
                            <div class="profile-point">
                            <div class="profile-point-name"></div>
                            <div class="profile-point-circle"></div>
                          </div>
                            <div class="profile-leaderboard">
                              <div class="admin-profile-leaderboard-txt">Rank</div>
                              <div class="profile-leaderboard-icon">
                                <div class="profile-leaderboard-icon-txt">
                                </div>
                              </div>
                            </div>
                        </div>
                    </div>

	               <div class="row col-md-12">
	                  <div id="profile_scores">
                        <div class="profile-level">
                            <div class="col-md-6">
                                <!-- <div class="profile-level-circle">
                              </div> -->
                              <div class="profile-level-wrapper">
                                <div class="profile-level-name-small">Current Level</div>
                                <div class="profile-level-name"></div>
                              </div>
                            </div>

                            <div class="col-md-6">
                                <div class="profile-level-name-small">Level Completion</div>
                                <div class="c100 p10 primary">
                                <span class="profile-percent">
                                </span>
                                <div class="slice">
                                <div class="bar"></div>
                                  <div class="fill"></div>
                                </div>
                              </div>
                            </div>
                        </div>
	                  </div>
	                </div>

                    <div class="row col-md-12">
                        <div class="profile-title">Badges</div>
                        <div id="profile_badges">
                          <div class="profile-badges-list">
                          </div>
                        </div>
                    </div>
	            </div>
		      </div>
		      <div class="modal-footer">
		        <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
		      </div>
		    </div>
		  </div>
		</div>
        <script>
        var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";

                    /* Simple Loader */
            (function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
            simpleloader.init();
            $(document).ready(function(){
                /* code for DataTable begins here */
                window.datatableOBJ = $('#datatable')
                .on('processing.dt',function(event,settings,flag){
                    if(flag == true)
                        simpleloader.fadeIn();
                    else
                        simpleloader.fadeOut();
	              })
                .on('draw.dt',function(event,settings,flag){
                    $('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                    $('#datatable tbody tr td:nth-child(2)').css({ cursor: "pointer"});
                }).dataTable({
                    "serverSide": true,
                    "ajax": {
                        "url": "{{URL::to('/pl/leaderboard')}}?cycle=alltime",
                    },
                    "columnDefs": [ { "targets": $targetarr } ],
                    "aaSorting": [],
                    "aoColumnDefs": [
                          { 'bSortable': false, 'aTargets': [ 0 , 1 , 2 ] }
                       ],
                    "columns": [
    			            { "data": "rank" },
    			            { "data": "player.alias" },
    			            { "data": "score" },
      			        ],
                    "iDisplayStart": start_page,
                    "pageLength": length_page,
                    "oSearch": {"sSearch": search_var}
                });
                $('#datatable tbody')
                .on( 'click', 'tr', function () {
                    simpleloader.fadeIn();
                	var leader = window.datatableOBJ.fnGetData(this);
                	$.ajax({
    					      type: 'GET',
    					      url: '/pl/profile?id='+leader.player.id,
    					      dataType: 'json',
    					      contentType : 'application/json',
        					})
        			  .done(function(player) {
        						var points = 0;
        						var badges = [];
        						var badge_text = "";
        						for(var i=0; i < player.pl_details.scores.length; i++) {
        							var score = player.pl_details.scores[i];
        							if (score.metric.type === "point") {
        								points =score.value;
        							}
        							if (score.metric.type === "set") {
        								for(var j=0; j < score.value.length; j++) {
        									var badge = score.value[j];
        									badge.metric = score.metric;
        									badge_text += '<div class="profile-badges-list-item"><img src="/pl/image?size=small&metric_id='+score.metric.id+'&item='+badge.name+'"></img></div>'
        									// badges.push(badge);
        								}
        							}
        						}
                    $('.profile-name').html(player.pl_details.alias);
          						$('.profile-point-circle').html(points);
          					    $('.profile-point-name').html('Points');
          					    $('.profile-badges-list').html(badge_text);
                                $('.profile-level-name').html(player.pl_score.value.name);
                                $('.profile-percent').html(player.pl_score.percent+"%");
                                $('#playerModal').modal();
                                simpleloader.fadeOut();
          					});
                    $.ajax({
                          type: 'GET',
                          url: '/pl/rank?id='+leader.player.id,
                          dataType: 'json',
                          contentType : 'application/json',
                    })
                    .done(function(leaderboard) {
                        $('.profile-leaderboard-icon-txt').html("#"+leaderboard["data"][0]["rank"]);
                    });
		    	});
            });
        </script>
    </div>
<style>
/*#modal .xs-margin{
    padding-left: 45px;
}
table.dataTable thead .sorting, 
table.dataTable thead .sorting_asc, 
table.dataTable thead .sorting_desc {
    background : none;
}*/
</style>
@stop
