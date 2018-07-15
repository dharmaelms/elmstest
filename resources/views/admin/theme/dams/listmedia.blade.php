@section('content')@section('content')
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
@if ( Session::get('warning'))
	<div class="alert alert-warning">
	<button class="close" data-dismiss="alert">×</button>
	<strong>{{ trans('admin/dams.warning')}}</strong>
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
		var $targetarr = [0,4,5,6];
	</script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <div class="box-tool">
	                	<!-- <button class="btn btn-success" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);return false;">Refresh <i class="fa fa-refresh"></i></button> -->
	                    <!-- <a href="#" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);var a = $(this).addClass('anim-turn180');setTimeout(function(){a.removeClass('anim-turn180');},500);return false;"><i class="fa fa-refresh"></i></a> -->
	                   <!--  <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a> -->
	                </div>
	            </div>
	            <div class="box-content">
	            	<div class="btn-toolbar clearfix">
	            		<div class="col-md-6">
	                        <form class="form-horizontal" action="">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
	                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
	                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>{{ trans('admin/dams.all')}}</option>
	                                    <option value="IMAGE" <?php if ($filter == 'image') echo 'selected';?>>{{ trans('admin/dams.image')}}</option>
	                                    <option value="DOCUMENT" <?php if ($filter == 'document') echo 'selected';?>>{{ trans('admin/dams.document')}}</option>
	                                    <option value="VIDEO" <?php if ($filter == 'video') echo 'selected';?>>{{ trans('admin/dams.video')}}</option>
	                                    <option value="AUDIO" <?php if ($filter == 'audio') echo 'selected';?> >{{ trans('admin/dams.audio')}}</option>
	                                    <option value="SCORM" <?php if ($filter == 'scorm') echo 'selected';?> >{{ trans('admin/dams.scorm')}}</option>
	                                </select>
	                              </div>
	                           </div>
	                        </form>
	                    </div>
                        <div class="pull-right">
        				<a class="btn btn-circle show-tooltip" title="Refresh" href="#" onclick="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);var a=$(this).addClass('anim-turn180');setTimeout(function(){a.removeClass('anim-turn180');},500);"><i class="fa fa-refresh"></i></a>
	                        @if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
								<div class="btn-group">
									<a  class="btn btn-primary btn-sm"  href="{{url::to('/cp/dams/add-media')}}">
	                            		<span class="btn btn-circle blue show-tooltip custom-btm">
	                            			<i class="fa fa-plus"></i>
                            			</span>&nbsp;{{ trans('admin/dams.add_media')}}
									</a>&nbsp;&nbsp;
								</div>
							@endif

							@if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::DELETE_MEDIA))
								<a class="btn btn-circle show-tooltip bulkdeletemedia" title="Bulk Delete" href="#">
									<i class="fa fa-trash-o"></i>
								</a>
							@endif
	                        
                        </div>
                    </div>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall" /></th>
				                <th style="width:25%">{{ trans('admin/dams.media_resource')}}</th>
				                <th>{{ trans('admin/dams.type')}}</th>
				                <th>{{ trans('admin/dams.created_on')}}</th>
				                <th>{{ trans('admin/dams.added_by')}}</th>
				                <th>{{ trans('admin/dams.preview')}}</th>
								<th>Actions </th>
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
                                               {{ trans('admin/dams.view_media_details')}}
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
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/dams.close')}}</a>
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
                                                {{ trans('admin/dams.media_delete')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        {{ trans('admin/dams.media_del_confirmation')}}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{ trans('admin/dams.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/dams.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="mediarelations" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{ trans('admin/dams.media_delete')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        {{ trans('admin/dams.media_del_confirmation')}}
                    </div>
                    <div class="modal-footer">
                    	<b>{{ trans('admin/dams.media_del_confirmation')}}</b>
                        <a class="btn btn-danger">{{ trans('admin/dams.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/dams.close')}}</a>
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
                                                {{ trans('admin/dams.media_delete')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;max-height:400px;overflow-y:auto">
                        {{ trans('admin/dams.media_del_confirmation')}}
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger" id="bulkdeletebtn">{{ trans('admin/dams.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/dams.close')}}</a>
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
	                                           {{ trans('admin/dams.view_media_details')}}
	                                    </h3>                                                
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	                <div class="modal-body">
	                    ...
	                </div>
	                <div class="modal-footer" style="padding-right: 32px">
	                	<div style="float: left;" id="selectedcount"> 0 {{ trans('admin/dams.selected')}}</div>
		                <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/dams.select')}}</a>
		                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/dams.close')}}</a>
	                </div>
	            </div>
	        </div>
	    </div>
	    <script>
			var  start_page  = {{Input::get('start',0)}};
	        var  length_page = {{Input::get('limit',10)}};
	        var  search_var  = "{{Input::get('search','')}}";
	        var  order_by_var= "{{Input::get('order_by','3 desc')}}";
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
	    	$(document).ready(function(){
	    		$('#alert-success').delay(5000).fadeOut();
	    		$('#modal').find('[data-dismiss="modal"]').click(function(){
	    			setTimeout(function(){
	    				$('#modal').find('.modal-body').empty();
	    			},500);
	    		});
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
	    			"autoWidth": false,
					"serverSide": true,
					"ajax": {
			            "url": "{{URL::to('/cp/dams/media-list-ajax')}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			            },
			            "complete" : function(a,b){
			            	if(a.status == 401)
			            		window.location.replace("{{URL::to('/auth/login')}}");
			            }
			        },
		            "aaSorting": [[ Number(order), _by ]],
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

    			$(document).on('click','.bulkdeletemedia',function(e){
	    			e.preventDefault();
	    			var $this = $(this);
	    			var $bulkdeletemodal = $('#bulkdeletemodal');
	    			console.log(checkedBoxes);
	    			if($.isEmptyObject(checkedBoxes)){
	    				alert('Please select atleast one media');
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
	    				html += "</strong>Are you sure you want to delete these entries?<br />";
	    				$bulkdeletemodal.find('.modal-body').html(html).end().modal('show');
	    				$('#bulkdeletebtn').unbind('click').click(function(e){
	    					e.preventDefault();
	    					var $form = $('<form></form>').prop('action','{{URL::to('/cp/dams/bulk-delete')}}').attr('method','post');
	    					var $input = $('<input/>').attr('type','hidden').attr('value',ids).attr('name','ids');
	    					$form.append($input);
	    					$form.appendTo('body').submit();
	    				})
	    			}
	    		})

				/* Code for deleting DAMS media starts here*/
	    		$(document).on('click','.deletemedia',function(e){
	    			e.preventDefault();
	    			var $this = $(this);
	    			var $deletemodal = $('#deletemodal');
    				$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
	    			$deletemodal.modal('show');
	    		});
				/* Code for deleting DAMS media ends here*/

				/* Code for viewing DAMS media starts here*/
	    		$(document).on('click','.ajax',function(e){
	    			jwplayer = null;
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
				/* Code for viewing DAMS media ends here*/

				/* Code for media relations starts here*/
	    		$(document).on('click','.mediarelations',function(e){
	    			jwplayer = null;
	    			e.preventDefault();
	    			simpleloader.fadeIn(200);
	    			$.ajax({
                        type: "GET",
                        url: $(this).attr('href')
                    })
                    .done(function( response ) {
                    	$('#mediarelations').find('.modal-body').html(response.rel_details).end().modal('show');
                    	$('#mediarelations').find('.modal-footer .btn-danger').prop('href',response.del_url).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
	    		});
				/* Code for media relations ends here*/

	    		/* Code for user dams rel starts here */
	    		$datatable.on('click','.damsrel',function(e){
	    			e.preventDefault();
	    			simpleloader.fadeIn();
	    			var $this = $(this);
	    			var $triggermodal = $('#triggermodal');
	    			var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
	    			
	    			$iframeobj.unbind('load').load(function(){
					//css code for the alignment	    			
	    			
	    			var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
	    			if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible'))
	    				$triggermodal.find('.modal-assign').css({"top": "53px"});
	    			else
	    				$triggermodal.find('.modal-assign').css({"top": "8px"});

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
								url: '{{URL::to('/cp/dams/assign-media/')}}/'+action+'/'+$this.data('key'),
								data: 'ids='+$postdata+"&empty=true"
							})
							.done(function( response ) {
								if(response.flag == "success")
									$('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/dams.media_success');?></div>').insertAfter($('.page-title'));
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
								$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
								window.datatableOBJ.fnDraw(true);
								simpleloader.fadeOut(200);
							})
	    			})
	    		});
	    		/* Code for user dams rel ends here */
	    	});
	    </script>
	</div>
@stop