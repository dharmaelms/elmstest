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
	<style>
		.thumbnailclose{
			position:absolute;
			right:5px;
			margin:0;
			padding:5px 3px;
			top: -10px;
			width: 25px;
			text-align: -webkit-center;
		}

		.elementsoverlay{
			position: absolute; 
			padding: 5px 6px; 
			top: 5px; 
			left: 20px;
			cursor: default !important;
		}

		#elementsplaceholder{
			overflow-y:auto;
		}
		/*name highlight		*/
		 .name_higlight{
		    background-color: black;
			color: white;
			height: auto;
			margin-top: 150px;
			padding: 10px;
			position: absolute;
			right: 0;
			text-align: center;
			font-size:15px ;
			top: 10px;
			width: 100%;
			cursor:pointer;
		}
		.name_higlight {
			display:none;
		}
	
	</style>
	<script src="//code.jquery.com/jquery-1.10.2.js"></script>
	<script src="//code.jquery.com/ui/1.11.4/jquery-ui.js"></script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>

    <div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> {{trans('admin/program.elements')}}</h3> -->
	                <div class="box-tool" style="z-index:9">
	                	<button class="btn btn-success" type="button" id="updateelements">{{trans('admin/program.update')}}</button>
	                </div>
	            </div>
	            <div class="box-content">
	            	<div class="row">
	    				<div class="col-md-12">
	    					<form class="form-horizontal form-bordered form-row-stripped" method="post">
		                        <div class="form-group">
		                            <label for="select" class="col-sm-3 col-lg-2 control-label"> {{trans('admin/program.add_elements')}} <span class="red">*</span></label>
		                            <div class="col-sm-5 col-lg-5 controls">
		                                <select class="form-control" name="elements" id="elements" data-rule-required="true">
		                                    <option value="">-- Select any {{trans('admin/program.element')}} --</option>
		                                    <option value="media" data-url="{{URL::to('/cp/dams?view=iframe&id=id')}}" data-json="{{isset($mediaelements['ids']) ? json_encode($mediaelements['ids']) : '[]'}}" data-media-names="{{isset($mediaelements['names']) ? json_encode($mediaelements['names']) : '[]'}}" data-text="Add Media">{{trans('admin/program.media')}}</option>
		                                    <option value="assessment" data-url="{{URL::to('/cp/assessment/list-quiz?view=iframe')}}" data-json="{{isset($assessmentelements['ids']) ? json_encode($assessmentelements['ids']) : '[]'}}" data-media-names="{{isset($assessmentelements['names']) ? json_encode($assessmentelements['names']) : '[]'}}" data-text="Add Assessment">{{trans('admin/program.assessment')}}</option>
		                                    <option value="event" data-url="{{URL::to('/cp/event?view=iframe')}}" data-json="{{isset($eventelements['ids']) ? json_encode($eventelements['ids']) : '[]'}}" data-media-names="{{isset($eventelements['names']) ? json_encode($eventelements['names']) : '[]'}}" data-text="Add Event">{{trans('admin/program.event_type')}}</option>
		                                    <option value="flashcard" data-url="{{URL::to('/cp/flashcards/list-iframe?view=iframe')}}" data-json="{{isset($flashcardelements['ids']) ? json_encode($flashcardelements['ids']) : '[]'}}" data-media-names="{{isset($flashcardelements['names']) ? json_encode($flashcardelements['names']) : '[]'}}" data-text="Add Flashcard">{{trans('admin/program.flashcards')}}</option>
		                                </select>
		                                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                            <div class="col-sm-4 col-lg-5 controls" style="border-left: 0;">
		                            	<button class="btn btn-success" type="button" id="addelements">{{trans('admin/program.add')}}</button>
		                            </div>
		                        </div>
	    					</form>
	            		</div>
	            	</div>
	            	<div class="row">
	    				<div class="" style="max-height:500px" id="elementsplaceholder">
	    					<br />
	    					<?php
	    					if(isset($packet['elements']) && is_array($packet['elements']) && count($packet['elements'])){ ?>
	    						<script type="text/javascript">media_types = {};</script>
	    						<?php
	    						$var = 1;
					   			foreach ($packet['elements'] as $key => $value) { 
					   				if($value['type'] == "media"){
						   				?>
						   				<div class="col-xs-2 col-md-2 mediadata" id="data_media.{{$value['id']}}" style="margin-bottom:40px;">
										<a href="{{ URL::to('/cp/dams/edit-media/'.$value['id'].'/'.$var) }}?post_slug={{$packet['packet_slug']}} ">
											<div class="thumbnail" style="height: 150px;">
												<img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/cp/dams/show-media/'.$value['id'].'?preview=true&id=id&width=185&height=150')}}">
												<span class="name_higlight" >{{ $mediaelements['names'][$value['id']] }}</span>
											</div>
										</a>
											<div class="caption">
												<h5 style="text-align: center;margin-bottom:10px;" >{{(isset($mediaelements['names'][$value['id']])) ? (strlen($mediaelements['names'][$value['id']]) > 18) ? substr($mediaelements['names'][$value['id']],0,30) . "..." : $mediaelements['names'][$value['id']] : ""}}</h5>
											<span class="name_higlight" >{{ $mediaelements['names'][$value['id']] }}</span>
											</div>
											<a class="btn btn-circle btn-danger thumbnailclose">
												<i class="fa fa-times"></i>
											</a>
											<a class="label label-success elementsoverlay" onclick="return false"><?php echo isset($value['media_type']) ? $value['media_type']: ""; ?></a>
											<script type="text/javascript">
												<?php if(isset($value['media_type'])): ?>
													media_types[<?php echo (int)$value['id']; ?>] = "<?php echo $value['media_type'] ?>";
												<?php endif; ?>
											</script>
										</div>
						   	<?php 	}
						   			elseif($value['type'] == "assessment"){ 
						   				$var = 1;
						   				?>
						   				<div class="col-xs-2 col-md-2 assessmentdata" id="data_assessment.{{$value['id']}}" style="margin-bottom:40px;">
											<a href="{{ URL::to('/cp/assessment/edit-quiz/'.$value['id'].'/'.$var)}}?post_slug={{$packet['packet_slug']}} ">
											<div class="thumbnail" style="height: 150px;">
												<img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/quiz.png')}}">
												<span class="name_higlight" >{{ $assessmentelements['names'][$value['id']] }}</span>
											</div>
											</a>
											<div class="caption">
												<h5 style="text-align: center;margin-bottom:10px;" >{{(isset($assessmentelements['names'][$value['id']])) ? (strlen($assessmentelements['names'][$value['id']]) > 18) ? substr($assessmentelements['names'][$value['id']],0,30) . "..." : $assessmentelements['names'][$value['id']] : ""}}</h5>
												<span class="name_higlight" >{{ $assessmentelements['names'][$value['id']] }}</span>
											</div>
											<a class="btn btn-circle btn-danger thumbnailclose">
												<i class="fa fa-times"></i>
											</a>
											<a class="label label-success elementsoverlay" onclick="return false">{{trans('admin/program.assessment')}}</a>
										</div>
						   	<?php 	}
						   			elseif($value['type'] == "event"){
						   				$var = 1;
						   			 ?>
						   				<div class="col-xs-2 col-md-2 eventdata" id="data_event.{{$value['id']}}" style="margin-bottom:40px;">
										<a href="{{ URL::to('/cp/event/edit-event/'.$value['id'].'/'.$var )}}?post_slug={{$packet['packet_slug']}} ">
											<div class="thumbnail" style="height: 150px;">
												<img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/intro_events.png')}}">
												<span class="name_higlight" >{{ $eventelements['names'][$value['id']] }}</span>
											</div>
										</a>
											<div class="caption">
												<h5 style="text-align: center;margin-bottom:10px;" >{{(isset($eventelements['names'][$value['id']])) ? (strlen($eventelements['names'][$value['id']]) > 18) ? substr($eventelements['names'][$value['id']],0,30) . "..." : $eventelements['names'][$value['id']] : ""}}</h5>
												<span class="name_higlight" >{{ $eventelements['names'][$value['id']] }}</span>
											</div>
											<a class="btn btn-circle btn-danger thumbnailclose">
												<i class="fa fa-times"></i>
											</a>
											<a class="label label-success elementsoverlay" onclick="return false">{{trans('admin/program.event')}}</a>
												</div>
								   	<?php 	}elseif($value['type'] == "flashcard"){
								   			$var = 1;
								   	 ?>
								   				<div class="col-xs-2 col-md-2 flashcarddata" data-id="{{$value['id']}}" id="data_flashcard.{{$value['id']}}" style="margin-bottom:40px;">
												<a href="{{ URL::to('/cp/flashcards/edit/'.$value['id'].'/'.$var )}}?post_slug={{$packet['packet_slug']}} ">
													<div class="thumbnail" style="height: 150px;">
														<img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/intro_events.png')}}">
														<span class="name_higlight" >{{ $flashcardelements['names'][$value['id']] }}</span>
													</div>
												</a>
													<div class="caption">
														<h5 style="text-align: center;margin-bottom:10px;" >{{(isset($flashcardelements['names'][$value['id']])) ? (strlen($flashcardelements['names'][$value['id']]) > 18) ? substr($flashcardelements['names'][$value['id']],0,30) . "..." : $flashcardelements['names'][$value['id']] : ""}}</h5>
														<span class="name_higlight" >{{ $flashcardelements['names'][$value['id']] }}</span>
													</div>
													<a class="btn btn-circle btn-danger thumbnailclose">
														<i class="fa fa-times"></i>
													</a>
													<a class="label label-success elementsoverlay" onclick="return false">{{trans('admin/program.flashcard')}}</a>
												</div>
								   	<?php 	}


					   			}
					   		}
	    					?>
	            		</div>
	            	</div>
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
		$(document).ready(function(){
			if (!Array.prototype.remove) {
				Array.prototype.remove = function(val) {
					var i = this.indexOf(val);
					return i>-1 ? this.splice(i, 1) : [];
				};
			}
			var $triggermodal = $('#triggermodal');
			$('#addelements').click(function(e){
    			e.preventDefault();
				var $elementObj = $('#elements');
				var $this = $('option:selected',$elementObj);
				var $value = $elementObj.val();
				if($value == "media"){
	    			simpleloader.fadeIn();
	    			var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
	    			$iframeobj.unbind('load').load(function(){
						$('#selectedcount').text('0 selected');
						if(typeof $iframeobj.get(0).contentWindow.checkedBoxes == "undefined")
							$iframeobj.get(0).contentWindow.checkedBoxes = {};

	    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
	    					$triggermodal.modal('show');
	    				simpleloader.fadeOut();
	    				// console.log($this.data('mediaNames'));
	    				/* Code to Set Default checkedboxes starts here*/
	    				$.each($this.data('json'),function(index,value){
	    					// console.log(typeof $this.data('mediaNames')[value]);
	    					if(typeof $this.data('mediaNames')[value] != "undefined"){
	    						$iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
	    						$iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
	    					}
	    					else
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
	    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
	    				var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
	    				var $media_types = $iframeobj.get(0).contentWindow.media_types;
	    				if(!$.isEmptyObject($checkedboxes)){
	    					var $placeholder = $('#elementsplaceholder');
	    					var jsonarr = [];
	    					var jsonNameArr = {};
	    					$placeholder.find('[id^="data_media"]').remove();
	    					$.each($checkedboxes,function(index,value){
	    						index = parseInt(index,10);
	    						jsonarr.push(index);
	    						var temptext = $checkedboxes[index];
	    						jsonNameArr[index] = temptext;
	    						var labeltext = $media_types[index];
	    						temptext = (temptext.length > 18) ? temptext.substr(0,30) + '...' : temptext;
	    						$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 mediadata" id="data_media.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/cp/dams/show-media/')}}/' + index + '?preview=true&'+ new Date().getTime() +'&id=id&width=185&height=150" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">'+labeltext+'</a></div>'));
	    					});
	    					$this.data('json',jsonarr)
	    					$this.data('mediaNames',jsonNameArr)
	    					if($placeholder.hasClass('ui-sortable'))
								$placeholder.sortable('destroy');
	    					$placeholder.sortable();
							$placeholder.disableSelection();
	    					$triggermodal.modal('hide');
	    					media_types = $media_types;
	    				}
	    				else{
	    					alert('Please select atleast one media');
	    				}
	    			})
		    		/* Code for user media rel ends here */
				}
				else if($value == 'assessment'){
	    			simpleloader.fadeIn();
	    			var $iframeobj = $('<iframe id="assessmentiframe" src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
	    			$iframeobj.unbind('load').load(function(){
						$('#selectedcount').text('0 selected');

	    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
	    					$triggermodal.modal('show');
	    				simpleloader.fadeOut();

	    				/* Code to Set Default checkedboxes starts here*/
	    				$.each($this.data('json'),function(index,value){
	    					if(typeof $this.data('mediaNames')[value] != "undefined"){
	    						$iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
	    						$iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
	    					}
	    					else
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
	    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
	    				var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
	    				if(!$.isEmptyObject($checkedboxes)){
	    					var $placeholder = $('#elementsplaceholder');
	    					var jsonarr = [];
	    					$placeholder.find('[id^="data_assessment"]').remove();
	    					$.each($checkedboxes,function(index,value){
	    						index = parseInt(index,10);
	    						jsonarr.push(index);
	    						var temptext = $checkedboxes[index];
	    						temptext = (temptext.length > 18) ? temptext.substr(0,15) + '...' : temptext;
	    						$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 assessmentdata" id="data_assessment.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/quiz.png')}}" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">Assessment</a></div>'));
	    					});
	    					$this.data('json',jsonarr)
	    					if($placeholder.hasClass('ui-sortable'))
								$placeholder.sortable('destroy');
	    					$placeholder.sortable();
							$placeholder.disableSelection();
	    					$triggermodal.modal('hide');
	    				}
	    				else{
	    					alert('Please select atleast one assessment');
	    				}
	    			})
		    		/* Code for user assessment rel ends here */
				}
				else if($value == 'event'){
	    			simpleloader.fadeIn();
	    			var $iframeobj = $('<iframe id="eventiframe" src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
	    			$iframeobj.unbind('load').load(function(){
						$('#selectedcount').text('0 selected');

	    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
	    					$triggermodal.modal('show');
	    				simpleloader.fadeOut();

	    				/* Code to Set Default checkedboxes starts here*/
	    				$.each($this.data('json'),function(index,value){
	    					if(typeof $this.data('mediaNames')[value] != "undefined"){
	    						$iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
	    						$iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
	    					}
	    					else
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
	    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
	    				var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
	    				if(!$.isEmptyObject($checkedboxes)){
	    					var $placeholder = $('#elementsplaceholder');
	    					var jsonarr = [];
	    					$placeholder.find('[id^="data_event"]').remove();
	    					$.each($checkedboxes,function(index,value){
	    						index = parseInt(index,10);
	    						jsonarr.push(index);
	    						var temptext = $checkedboxes[index];
	    						temptext = (temptext.length > 18) ? temptext.substr(0,15) + '...' : temptext;
	    						$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 eventdata" id="data_event.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/intro_events.png')}}" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">Event</a></div>'));
	    					});
	    					$this.data('json',jsonarr)
	    					if($placeholder.hasClass('ui-sortable'))
								$placeholder.sortable('destroy');
	    					$placeholder.sortable();
							$placeholder.disableSelection();
	    					$triggermodal.modal('hide');
	    				}
	    				else{
	    					alert('Please select atleast one event');
	    				}
	    			})
		    		/* Code for user event rel ends here */
				}
				else if($value == 'flashcard'){

	    			simpleloader.fadeIn();
	    			var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="" frameBorder="0"></iframe>');
	    			$iframeobj.unbind('load').load(function(){

						$('#selectedcount').text('0 selected');
						if(typeof $iframeobj.get(0).contentWindow.checkedBoxes == "undefined")
							$iframeobj.get(0).contentWindow.checkedBoxes = {};

	    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
	    					$triggermodal.modal('show');
	    				simpleloader.fadeOut();
	    				 // console.log($this.data('mediaNames'));
	    				/* Code to Set Default checkedboxes starts here*/
	    				// $.each($this.data('json'),function(index,value){
	    				// 	// console.log(typeof $this.data('mediaNames')[value]);
	    				// 	if(typeof $this.data('mediaNames')[value] != "undefined"){
	    				// 		$iframeobj.get(0).contentWindow.checkedBoxes[value] = $this.data('mediaNames')[value];
	    				// 		$iframeobj.get(0).contentWindow.media_types[value] = media_types[value];
	    				// 	}
	    				// 	else
	    				// 		$iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
	    				// })
	    				/* Code to Set Default checkedboxes ends here*/

						/* Code to refresh selected count starts here*/
						$('#selectedcount').text(Object.keys($iframeobj.get(0).contentWindow.checkedBoxes).length+ ' selected');
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
	    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
	    				var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
	    				var $media_types = $iframeobj.get(0).contentWindow.media_types;
	    				if(!$.isEmptyObject($checkedboxes)){
	    					var $placeholder = $('#elementsplaceholder');
	    					var jsonarr = [];
	    					var jsonNameArr = {};
	    					$placeholder.find('[id^="data_flashcard"]').remove();
	    					$.each($checkedboxes,function(index,value){
	    						index = parseInt(index,10);
	    						jsonarr.push(index);
	    						var temptext = value.title;
	    						jsonNameArr[index] = temptext;
	    						var labeltext = 'flashcard';
	    						temptext = (temptext.length > 18) ? temptext.substr(0,30) + '...' : temptext;
	    						$placeholder.append($('<div style="margin-bottom:40px;" class="col-xs-2 col-md-2 flashcarddata" data-id="'+index+'" id="data_flashcard.'+index+'">').append('<div class="thumbnail" style="height: 150px;"><img class="" style="max-width: 100%; display: block; max-height: 100%;" src="{{URL::to('/admin/img/icons/intro_events.png')}}" > </div><div class="caption"> <h5 style="text-align: center;margin-bottom:10px;">' + temptext + '</h5></div><a class="btn btn-circle btn-danger thumbnailclose"><i class="fa fa-times"></i></a><a class="label label-success elementsoverlay" onclick="return false">'+labeltext+'</a></div>'));
	    					});
	    					$this.data('json',jsonarr)
	    					$this.data('mediaNames',jsonNameArr)
	    					if($placeholder.hasClass('ui-sortable'))
								$placeholder.sortable('destroy');
	    					$placeholder.sortable();
							$placeholder.disableSelection();
	    					$triggermodal.modal('hide');
	    					media_types = $media_types;
	    				}
	    				else{
	    					alert('Please select atleast one flashcard');
	    				}
	    			})
		    		/* Code for user media rel ends here */
				
				}
				else{
					//alert('Please select an {{ trans("admin/flashcards.channel") }} type');
					alert('Please select item type');
				}
			});
			$('#updateelements').click(function(){
				var $elements = $('#elementsplaceholder div');
				if($elements.length){
					var $elementdata = $('#elementsplaceholder').sortable('serialize');
					simpleloader.fadeIn();
					$.ajax({
						type: "POST",
						url: '{{URL::to('/cp/contentfeedmanagement/assign-elements/'.$slug.'/'.$type)}}',
						data: $elementdata
					})
					.done(function( response ) {

						if(response.flag == "success")
							window.location.replace('{{URL::to('/cp/contentfeedmanagement/add-element-success/'.$packet['packet_slug'].'/'.$type)}}')
						else
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong> <?php echo trans('admin/category.server_error');?></div>').insertAfter($('.page-title'));
						$triggermodal.modal('hide');
						setTimeout(function(){
							$('.alert').alert('close');
						},5000);
						simpleloader.fadeOut(200);
					})
					.fail(function() {
						setTimeout(function(){
							$('.alert').alert('close');
						},5000);
						$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong>  <?php echo trans('admin/category.server_error');?></div>').insertAfter($('.page-title'));
						simpleloader.fadeOut(200);
					})
				}
				else{
					alert('Please select atleast one item');
					window.location.reload(true);
				}
			});
			$('#elementsplaceholder').on('click','.thumbnailclose',function(e){
				e.preventDefault();
				if(confirm('Are you sure that you want to remove this item?')){
					var $this = $(this);
					if($this.parent().hasClass('mediadata')){
						var $jsondata = $('option[value="media"]').data('json');
						$jsondata.remove(parseInt($this.parent().attr('id').split('.')[1],10));
						$('option[value="media"]').data('json',$jsondata);
					}
					else if($this.parent().hasClass('assessmentdata')){
						var $jsondata = $('option[value="assessment"]').data('json');
						$jsondata.remove(parseInt($this.parent().attr('id').split('.')[1],10));
						$('option[value="assessment"]').data('json',$jsondata);
					}
					$this.parent().fadeOut(300, function(){ $(this).remove();});
				}
			}).sortable().disableSelection();
		})
	</script>
	
	<script>
	
	$('.thumbnail').on('mouseover', function(event){
    $(this).find('.name_higlight').fadeIn();
	});
	
	$('.thumbnail').on('mouseout', function(event){
    $(this).find('.name_higlight').stop().fadeOut();
	});
	
	$('.caption').on('mouseover', function(event){
    $(this).find('.name_higlight').fadeIn();
	});
	
	$('.caption').on('mouseout', function(event){
    $(this).find('.name_higlight').stop().fadeOut();
	});
	
	</script>
		
@stop