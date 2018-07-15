@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success" id="alert-success">
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
	<div class="row custom-box">
		<div class="col-md-12">
	        <div class="box">
	        </div>
	    </div>
    </div>
	<div class="row custom-box">
	    <div class="col-md-4">
	        <div class="box box-lightgreen">
	            <div class="box-title">
	                <h3>{{trans('admin/program.channel_management')}}</h3>
	            </div>
	            <div class="box-content">
					@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST))
						<a class="btn btn-blue" href="{{URL::to("/cp/contentfeedmanagement/add-packets/{$program["program_type"]}/{$program["program_slug"]}")}}" >
							{{trans('admin/program.add_packet_to_channel')}}
						</a>
					@endif
					@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::CHANNEL_ASSIGN_CATEGORY))
						<a href="{{URL::to('/cp/categorymanagement/categories?view=iframe&filter=ACTIVE')}}"
						   class="btn btn-blue triggermodal" data-info="category">
							{{trans('admin/program.assign_cat_to_channel')}}
						</a>
					@endif
					@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::CHANNEL_ASSIGN_USER))
						<a href="{{URL::to("/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=contentfeed&relid={$program["program_id"]}&disable_filter=TRUE")}}"
						   class="btn btn-blue triggermodal" data-info="user">
							{{trans('admin/program.assign_user_to_channel')}}
						</a>
					@endif
					@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::CHANNEL_ASSIGN_USER_GROUP))
						<a href="{{URL::to("/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=contentfeed&relid={$program["program_id"]}&disable_filter=TRUE")}}"
						   class="btn btn-blue triggermodal" data-info="usergroup">
							{{trans('admin/program.assign_ug_to_channel')}}
						</a>
					@endif
	            </div>
	   		</div>  
	    </div>
	    <div class="col-md-4">
	        <div class="box box-lightgray">
	            <div class="box-title">
	                <h3> {{trans('admin/program.additional_actions')}}</h3>
	            </div>
	            <div class="box-content">
					@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST))
						<a class="btn btn-lightblue" href="{{URL::to("/cp/contentfeedmanagement/packets/{$program["program_type"]}/{$program["program_slug"]}")}}" >
							{{trans('admin/program.list_packet_channel')}}
						</a>
					@endif
					@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::ADD_CHANNEL))
						<a class="btn btn-lightblue" href="{{URL::to('/cp/contentfeedmanagement/add-feeds')}}" >
							{{trans('admin/program.add_another_channel')}}
						</a>
					@endif
					@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::LIST_CHANNEL))
						<a class="btn btn-lightblue" href="{{URL::to('/cp/contentfeedmanagement')}}">
							{{trans('admin/program.view_all_channel')}}
						</a>
					@endif
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
                                            {{trans('admin/program.view_media_details')}}
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
                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/program.assign')}}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/program.close')}}</a>
                </div>
            </div>
        </div>
    </div>
    <script>
    	var $slug = "{{$program["program_slug"]}}";
    	$(document).ready(function(){
    		$('#alert-success').delay(5000).fadeOut();
    		$('.triggermodal').click(function(e){
    			e.preventDefault();
    			simpleloader.fadeIn();
    			var $this = $(this);
    			var $triggermodal = $('#triggermodal');
    			var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
    			
    			$iframeobj.unbind('load').load(function(){
    				
    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
    					$triggermodal.modal('show');
    				simpleloader.fadeOut();
    				var checkeditems = JSON.stringify($this.data('json')); 
    				
					if(checkeditems){
						checkeditems = JSON.parse(checkeditems);
						for(var x in checkeditems)
						  $iframeobj.get(0).contentWindow.checkedBoxes[x] = "";
					}

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
					/* Code to refresh selected count ends here*/
    			});

    			$triggermodal.find('.modal-body').html($iframeobj);
    			$triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());

    			//code for top assign button click
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
                    //code for top assign button ends here


    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
    				var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
    				var jsondata = [];
					var $postdata = "";
    				if(!$.isEmptyObject($checkedboxes)){
    					$.each($checkedboxes,function(index,value){
    						jsondata.push(index);
    						if(!$postdata)
    							$postdata += index;
    						else
    							$postdata += "," + index;
    					});
    				}
    				else{
    					alert('Please select at least one entry');
    					return false;
    				}
    				$this.data('json',jsondata);
					// Post to server
					var action = $this.data('info');

					simpleloader.fadeIn();
					$.ajax({
						type: "POST",
						url: '{{URL::to('/cp/contentfeedmanagement/assign-feed/')}}/'+action+'/'+$slug,
						data: 'ids='+$postdata
					})
					.done(function( response ) {
						if(response.flag == "success"){
							$this.data('json',$checkedboxes);
							$('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
						}
						else
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/program.server_error');?></div>').insertAfter($('.page-title'));
						$triggermodal.modal('hide');
						setTimeout(function(){
							$('.alert').alert('close');
						},5000);
						simpleloader.fadeOut(200);
					})
					.fail(function() {
						$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong><?php echo trans('admin/program.server_error');?></div>').insertAfter($('.page-title'));
						simpleloader.fadeOut(200);
					})
    			})
    		});
			window.onload = function(){
	    		(function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
	    		simpleloader.init();
	    	}
    	})
    </script>
@stop
