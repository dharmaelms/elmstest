@section('content')
	@if ( Session::get('success'))
		<div class="alert alert-success" id="alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Error!</strong> -->
			{{ Session::get('error') }}
		</div>
	@endif
	<script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [0,6,7,8,9];
    </script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <style>
		@media all and (min-width: 992px), (min-device-width: 992){
			.modal-dialog-event-details{width: 630px !important;}
		}
	</style>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	            </div>
	            <div class="box-content">
	            	<div class="btn-toolbar clearfix">
	            		<div class="col-md-6">
	                        <form class="form-horizontal" action="{{ URL::to('cp/event') }}">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/event.show') }} :</b></label>
	                              <div class="col-sm-4 col-lg-4 controls" style="padding-left:0;">
	                              	<?php $show = strtolower(Input::get('show', 'all')); ?>
	                                <select class="form-control input-sm chosen" name="show" onchange="this.form.submit();" tabindex="1">
	                                    <option value="all" <?php if ($show == 'all') echo 'selected';?>>All</option>
	                                    <option value="live" <?php if ($show == 'live') echo 'selected';?>>{{ trans('admin/event.live_event') }}</option>
	                                    <option value="general" <?php if ($show == 'general') echo 'selected';?>>{{ trans('admin/event.general') }}</option>
	                                </select>
	                              </div>
	                           </div>
	                        </form>
	                    </div>
                        <div class="pull-right">
	                        <div class="btn-group">
	                        <?php 
	                           $add_event = has_admin_permission(ModuleEnum::EVENT, EventPermission::ADD_EVENT); 
	                            if($add_event == true)
	                            {?>
	                             <a class="btn btn-primary btn-sm" href="{{ URL::to('cp/event/add-event') }}">
	                              <span class="btn btn-circle blue show-tooltip custom-btm">
	                              	<i class="fa fa-plus"></i>
	                              </span>&nbsp;{{ trans('admin/event.add_events') }}
	                            </a>
	                             <?php } ?>
	                        </div>
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall" /></th>
				                <th>{{ trans('admin/event.event') }}</th>
				                <th>{{ trans('admin/event.type') }}</th>
				                <th>{{ trans('admin/event.start_date') }}</th>
				                <th>{{ trans('admin/event.duration') }}</th>
				                <th>{{ trans('admin/event.created_on') }}</th>
				                <th><?php echo trans('admin/program.program'); ?></th>
				                <th>{{ trans('admin/event.users') }}</th>
				                <th>{{ trans('admin/event.usergroups') }}</th>
				                <th>{{ trans('admin/event.actions') }}</th>
				            </tr>
				        </thead>
				    </table>
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
										{{ trans('admin/event.event_delete') }}
									</h3>                 
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-body" style="padding: 20px">
					{{ trans('admin/event.modal_delete_event') }}
				</div>
				<div class="modal-footer">
					<a class="btn btn-danger">{{ trans('admin/event.yes') }}</a>
					<a class="btn btn-success" data-dismiss="modal">{{ trans('admin/event.close') }}</a>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="viewevent" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
		<div class="modal-dialog modal-dialog-event-details">
			<div class="modal-content">
				<div class="modal-header">
					<div class="row custom-box">
						<div class="col-md-12">
							<div class="box">
								<div class="box-title">
									<button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
									<h3 class="modal-header-title">
										<i class="icon-file"></i>
										{{ trans('admin/event.event_details') }}
									</h3>                 
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-body" style="padding-left: 20px; top: 1px; right: 10px">
					
				</div>
				<div class="modal-footer">
					<a class="btn btn-success" data-dismiss="modal">{{ trans('admin/event.close') }}</a>
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
                                    <h3 class="modal-header-title">
                                        <i class="icon-file"></i>
                                            {{ trans('admin/event.view_details') }}
                                    </h3>                                                
                                </div>
                            </div>
                            <div class="feed-list" style="display:none;">
                              	<label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b><?php echo trans('admin/program.programs');?> :</b></label>
                              	<div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                                <select name="feed" class="chosen">
	                                    @foreach($feeds as $feed)
		                         		<option value="{{ $feed->program_slug }}" data-type="{{ $feed->program_type }}" data-id="{{ $feed->program_id }}">{{ $feed->program_title }}</option>
		                         		@endforeach
	                                </select>
                                </div>`
                           </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer" style="padding-right: 38px">
                	<!-- <div style="float: left;" id="selectedcount"> 0 Entrie(s) selected</div> -->
	                <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/event.assign') }}</a>
	                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/event.close') }}</a>
                </div>
            </div>
        </div>
    </div>
	<script>

		var  start_page  = {{Input::get('start',0)}};
		var  length_page = {{Input::get('limit',10)}};
		var  search_var  = "{{Input::get('search','')}}";
		var  order_by_var= "{{Input::get('order_by','5 desc')}}";
		var  order = order_by_var.split(' ')[0];
		var  _by   = order_by_var.split(' ')[1];

    	function updateCheckBoxVals() {
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
  		(function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:	;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
    	simpleloader.init();
    	$(document).ready(function() {
    		$('#alert-success').delay(5000).fadeOut();
    		/* code for DataTable begins here */
    		var $datatable = $('#datatable');
    		window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag) {
    			if(flag == true)
    				simpleloader.fadeIn();
    			else
    				simpleloader.fadeOut();
    		}).on('draw.dt',function(event,settings,flag) {
    			$('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
    		}).dataTable({
    			"autoWidth": false,
				"serverSide": true,
				"ajax": {
		            "url": "{{ URL::to('/cp/event/list-index-ajax?show='.$show) }}",
		            "data": function ( d ) {
		                d.filter = $('[name="filter"]').val();
		            }
		        },
	            "aaSorting": [[Number(order), _by ]],
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

			/* Code for dataTable ends here */

			/* Code to get the selected checkboxes in datatable starts here*/
			if(typeof window.checkedBoxes == 'undefined')
				window.checkedBoxes = {};
			$datatable.on('change','td input[type="checkbox"]',function(){
				var $this = $(this);
				console.log($this);
				if($this.prop('checked'))
					checkedBoxes[$this.val()] = $($this).parent().next().text();
				else
					delete checkedBoxes[$this.val()];
			});

			$('#checkall').change(function(e){
				$('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
				$('#datatable td input[type="checkbox"]').trigger('change');
				e.stopImmediatePropagation();
			});
			/* Code to get the selected checkboxes in datatable ends here*/

			/* Code for deleting event starts here*/
			$(document).on('click','.delete-event',function(e){
				e.preventDefault();
				var $this = $(this);
				var $deletemodal = $('#deletemodal');
				$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
				$deletemodal.modal('show');
			});
			/* Code for deleting event ends here*/

			/* Code for user event rel ends here */
            var event_id = null;
            $("select[name='feed']").change(function() {
                $('iframe').attr(
                    'src', "{{ URL::to('cp/contentfeedmanagement/packets') }}/"+$(this).find("option[value='"+$(this).val()+"']").data("type")+"/"+this.value+
                    "?view=iframe&from=event&relid="+event_id
                );
            });

			/* Code for user event rel starts here */
    		$datatable.on('click','.eventrel',function(e){
    			e.preventDefault();
    			simpleloader.fadeIn();
    			var $this = $(this);
    			event_id = $this.data("key");
    			var $triggermodal = $('#triggermodal');
    			var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
    			
    			$iframeobj.unbind('load').load(function(){
    				//css code for the alignment	    			
	    			
	    			var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
	    			if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){	    					
	    					// $triggermodal.find('.modal-body').css({"top":"-27px"});
	    			}	    				
	    			else
	    				$triggermodal.find('.modal-assign').css({"top": "8px"});

	    			if($triggermodal.find('.feed-list').is(':visible')){
	    				$triggermodal.find('.feed-list').css({"padding-top":"12px","padding-bottom":"26px"});	    					
	    				// $triggermodal.find('.modal-body').css({"top":"-30px"});
	    				$triggermodal.find('.modal-assign').css({"top": "-45px"});
	    			}
	    			else{
	    				if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible'))
	    				$triggermodal.find('.modal-assign').css({"top": "30px"});
	    			}
	    			//code ends here

					// $('#selectedcount').text('0 Entrie(s) selected');

    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
    					$triggermodal.modal('show');
    				simpleloader.fadeOut();

    				/* Code to Set Default checkedboxes starts here*/
    				$iframeobj.get(0).contentWindow.checkedBoxes = {};
    				if($this.data('info') == 'feed') {
    					var feed_id = $("select[name='feed']").find(':selected').data('id');
    					var json = $this.data('json')[feed_id];
    					if(json === undefined) json = [];
    					console.log(json);
	    				$.each(json, function(index,value){
	    					$iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
	    				})
	    			} else {
	    				$.each($this.data('json'),function(index,value){
	    					$iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
	    				})
	    			}
    				/* Code to Set Default checkedboxes ends here*/

					/* Code to refresh selected count starts here*/
					// $iframeobj.contents().click(function(){
					// 	setTimeout(function(){
					// 		var count = 0;
					// 		$.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
					// 			count++;
					// 		});
					// 		$('#selectedcount').text(count+ ' Entrie(s) selected');
					// 	},10);
					// });
					// $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
					/* Code to refresh selected count ends here*/
    			})
    			$triggermodal.find('.modal-body').html($iframeobj);
    			$triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

                    //code for top assign button click
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
                    //code for top assign button ends here


    			if($this.data('info') == 'feed') {
    				$(".feed-list").show();
    				$("select[name='feed']").trigger('change');
    			} else {
    				$(".feed-list").hide();
    			}
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
    					var feed = '';
    					if(action == 'feed')
    						var feed = $("select[name='feed']").val();

    					simpleloader.fadeIn();
    					$.ajax({
							type: "POST",
							url: '{{ URL::to("/cp/event/assign-event") }}/'+action+'/'+$this.data('key'),
							data: 'ids='+$postdata+'&empty=true&feed='+feed
						})
						.done(function( response ) {
							if(response.flag == "success")
								$('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
							else
								$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
							$triggermodal.modal('hide');
							setTimeout(function(){
								$('.alert').alert('close');
							},5000);
							window.datatableOBJ.fnDraw(true);
							simpleloader.fadeOut(200);
						})
						.fail(function() {
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
							window.datatableOBJ.fnDraw(true);
							simpleloader.fadeOut(200);
						})
    			})
    		});

    		/* View event */
		    $(document).on('click','.view-event',function(e) {
    			e.preventDefault();
    			simpleloader.fadeIn(200);
				$.ajax({
					type: "GET",
					url: $(this).attr('href')
				})
				.done(function( response ) {
				$('#viewevent').find('.modal-body').html(response).end().modal('show');
					simpleloader.fadeOut(200);
				})
				.fail(function() {
					alert( "Error while fetching data from server. Please try again later" );
					simpleloader.fadeOut(200);
				})
    		});
    		/* View event */
            $(document).on('click', '.delete-record', function(e){
                if(confirm("{{ trans('admin/event.confirmation_delete_recordings') }}")) {
                    simpleloader.fadeIn(200);
                    $this = $(this);
                    $.ajax({
                        url: "{{URL::to('cp/event/delete-record')}}/" + $(this).data('id'),
                        method: 'GET',
                        data: {event_id: $(this).data('event-id')},
                        dataType: 'json',
                    })
                    .done(function( response ) {
                        if( response.status == true) {
                            $this.parent().parent().parent().remove();
                            $('#recordings-list tbody').children().length == 0 ? $('#recordings-list').hide() : '';
                        } else{
                            alert(response.error);
                        }
                        simpleloader.fadeOut(200);
                    })
                }
                return false;
            });
    	})
    </script>
@stop