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
	<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
	<div class="row custom-box">
		<div class="col-md-4">
	        <div class="box">
	           <!--  <div class="box-title">
	                <h3 style="color:black">Actions for <i>{{$asset['name']}}</i></h3>
	            </div>   -->
	        </div>
	    </div>
    </div>
	<div class="row custom-box">
	    <div class="col-md-4">
	        <div class="box box-lightgray">
	            <div class="box-title">
	                <h3>{{ trans('admin/dams.additional_actions')}}</h3>
	            </div>
	            <div class="box-content">
        			@if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
						<a class="btn btn-lightblue" href="{{URL::to('/cp/dams/add-media/')}}" >
							{{ trans('admin/dams.add_another_media')}}
						</a>
					@endif
        			@if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::LIST_MEDIA))
						<a class="btn btn-lightblue" href="{{URL::to('/cp/dams')}}" >
							{{ trans('admin/dams.view_all_media')}}
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
                	<div style="float: left;" id="selectedcount"> 0 {{ trans('admin/dams.selected')}}</div>
                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/dams.assign')}}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/dams.close')}}</a>
                </div>
            </div>
        </div>
    </div>
    <script>
    	var $key = '{{$key}}';
    	$(document).ready(function(){
            $('#alert-success').delay(5000).fadeOut();
    		$('.triggermodal').click(function(e){
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
    				if(typeof $this.data('json') != "undefined")
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
					/* Code to refresh selected count ends here*/
    			})
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
    				if(!$.isEmptyObject($checkedboxes)){
    					var $postdata = "";
    					$.each($checkedboxes,function(index,value){
    						jsondata.push(index);
    						if(!$postdata)
    							$postdata += index;
    						else
    							$postdata += "," + index;
    					});

    					// Post to server
    					$this.data('json',jsondata);
    					var action = $this.data('info');

    					simpleloader.fadeIn();
    					$.ajax({
							type: "POST",
							url: '{{URL::to('/cp/dams/assign-media/')}}/'+action+'/'+$key,
							data: 'ids='+$postdata
						})
						.done(function( response ) {
							if(response.flag == "success")
								$('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/dams.media_success');?></div>').insertAfter($('.page-title'));
							else
								$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
							$triggermodal.modal('hide');
							// setTimeout(function(){
							// 	$('.alert').alert('close');
							// },5000);
							simpleloader.fadeOut(200);
						})
						.fail(function() {
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
							simpleloader.fadeOut(200);
						})
    				}
    				else{
    					alert('Please select atleast one entry');
    				}
    			})
    		});
    	})
    </script>
@stop