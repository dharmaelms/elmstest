@section('content')
	<?php use App\Model\Packet ?>
	@if ( Session::get('success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">{{ trans('admin/dams.select_file')}}</button>
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

	<?php 
	    $start    =  Input::get('start', 0);
        $limit    =  Input::get('limit', 10);
        $filter   =  Input::get('filter','ALL');
        $search   =  Input::get('search','');
        $order_by =  Input::get('order_by','3 desc');
	?>
	<style>
		.center {
		    text-align: center !important;
		}
	</style>
	<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Edit Media</h3> -->
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div>
	            </div>
	            <div class="box-content">
                    <form action="#" class="form-horizontal form-bordered form-row-stripped" method="post">
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.media_type')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<label class="radio-inline">
									<input type="radio" name="media_type" disabled value="video" {{(isset($asset['type']) && $asset['type'] == 'video') ? "checked" : ""}} /> {{ trans('admin/dams.video')}}
								</label>
								<label class="radio-inline">
									<input type="radio" name="media_type" disabled value="image" {{(isset($asset['type']) && $asset['type'] == 'image') ? "checked" : ""}} /> {{ trans('admin/dams.image')}}
								</label>
								<label class="radio-inline">
									<input type="radio" name="media_type" disabled value="document" {{(isset($asset['type']) && $asset['type'] == 'document') ? "checked" : ""}} /> {{ trans('admin/dams.document')}}
								</label>
								<label class="radio-inline">
									<input type="radio" name="media_type" disabled value="audio" {{(isset($asset['type']) && $asset['type'] == 'audio') ? "checked" : ""}} /> {{ trans('admin/dams.audio')}}
								</label>
								<label class="radio-inline">
									<input type="radio" name="media_type" disabled value="scorm" {{(isset($asset['type']) && $asset['type'] == 'scorm') ? "checked" : ""}} /> {{ trans('admin/dams.scorm')}}
								</label>
								{!! $errors->first('media_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group" style="display:none" id="scorm_hide">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.from')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<label class="" style="display:none;">
									<input type="radio" name="input_type" disabled value="bulk_import" {{(isset($asset['input_type']) && $asset['input_type'] == 'bulk_import') ? "checked" : ""}} /> {{ trans('admin/dams.bulk_import')}}
								</label>
								<label class="radio-inline">
									<input type="radio" name="input_type" disabled value="uploadform" {{(isset($asset['asset_type']) && $asset['asset_type'] == 'file') ? "checked" : ""}} /> {{ trans('admin/dams.upload')}}
								</label>
								<label class="radio-inline">
									<input type="radio" name="input_type" disabled value="links" {{(isset($asset['asset_type']) && $asset['asset_type'] == 'link') ? "checked" : ""}} />{{ trans('admin/dams.link')}}
								</label>
								{!! $errors->first('input_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
                    </form>
                    <?php if($asset['asset_type'] == "file"){ $type = 1; ?>
	                    <form action="{{URL::to('cp/dams/edit-handler/'.$key.'/'.$type)}}?post_slug={{$packet}}" class="form-horizontal form-bordered form-row-stripped dams_form" method="post" id="uploadform" style="margin-top:10px;display:none" enctype='multipart/form-data'>
	                    	<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.title')}} <span class="red">*</span></label>
								<div class="col-sm-9 col-lg-10 controls">
									<input type="text" name="title" class="form-control" value="<?php echo (isset($asset['name'])) ? $asset['name'] : "" ; ?>" />
									<!-- <span class="help-inline">Some hint here</span> -->
									{!! $errors->first('title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									<input type="hidden" value=" {{ $asset['_id'] }}" name="media_id">
								</div>
							</div>
							<div class="form-group" id="visibility-field-container">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.visibility')}} <span class="red">*</span></label>
								<div class="col-sm-9 col-lg-10 controls">
									<select class="form-control chosen" tabindex="1" name="visibility">
										<option value="public" {{(isset($asset['visibility']) && $asset['visibility'] == "public") ? "selected" : ""}}>{{ trans('admin/dams.public')}}</option>
										<option value="private" {{(isset($asset['visibility']) && $asset['visibility'] == "private") ? "selected" : ""}}>{{ trans('admin/dams.private')}}</option>
									</select>
									{!! $errors->first('visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
							<div class="form-group" id="fileselector">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.file')}} <!-- <span class="red">*</span> --></label>
								<?php if($asset['type'] == "image" && $asset['asset_type'] == "file"){ ?>
									<div class="col-sm-2 col-lg-2 controls">
										<img src="{{URL::to('/cp/dams/show-media/'.$asset['_id'])}}" height="100%" width="100%">
									</div>
									<div class="col-sm-7 col-lg-8 controls">
								<?php } 
								elseif($asset['type'] == "video" && $asset['asset_type'] == "file" && isset($asset['kaltura_details']['thumbnailUrl'])){ ?>
									<div class="col-sm-2 col-lg-2 controls">
										<img src="{{$asset['kaltura_details']['thumbnailUrl'].'/width/300'}}" height="100%" width="100%">
									</div>
									<div class="col-sm-7 col-lg-8 controls">
								<?php }
								else { ?>
									<div class="col-sm-9 col-lg-10 controls">
								<?php } ?>
								<span class="">{{ trans('admin/dams.selected_file_text')}} <strong>{{$asset['file_client_name']}}</strong></span><br /><br />
									<div class="fileupload fileupload-new" data-provides="fileupload">
										<div class="input-group">
											<div class="input-group-btn">
												<a class="btn bun-default btn-file">
													<span class="fileupload-new">{{ trans('admin/dams.change')}}</span>
													<span class="fileupload-exists">{{ trans('admin/dams.change')}}</span>
													<input type="file" class="file-input" name="file" />
												</a>
												<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/dams.remove')}}</a>
											</div>
											<div class="form-control uneditable-input">
												<i class="fa fa-file fileupload-exists"></i> 
												<span class="fileupload-preview"></span>
											</div>
										</div>
									</div>
									<span class="help-inline" id="filetypehint"></span><br />
									{!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
	                    	<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.description')}} </label>
								<div class="col-sm-9 col-lg-10 controls">
									<textarea class="form-control" rows="3" name="description"><?php echo (isset($asset['description'])) ? $asset['description'] : ""; ?></textarea>
									<!-- <span class="help-inline">Some hint here</span> -->
									{!! $errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.keywords_tags')}}</label>
								<div class="col-sm-9 col-lg-10 controls">
									<input type="text" class="form-control tags medium" value="<?php echo (isset($asset['tags'])) ? implode(',',$asset['tags']) : ""; ?>" name="keyword" />
									{!! $errors->first('keyword', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
									<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ trans('admin/dams.update')}}</button>
									@if(isset($packet) && $packet!='')
										<a href="{{URL::to('/cp/contentfeedmanagement/elements/'.$packet)}}" ><button type="button" class="btn"> {{ trans('admin/dams.cancel')}}</button></a>
									@else
										<a href="{{URL::to('/cp/dams/')}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}" ><button type="button" class="btn"> {{ trans('admin/dams.cancel')}}</button></a>
									@endif
								</div>
							</div>
	                    </form>
	                <?php } ?>
                    <?php if($asset['asset_type'] == "link"){ $type = 1; ?>
	                    <form action="{{URL::to('cp/dams/edit-handler/'.$key.'/'.$type)}}?post_slug={{$packet}}" class="form-horizontal form-bordered form-row-stripped dams_form" method="post" id="links" style="margin-top:10px;display:none">
	                    	<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.title')}} <span class="red">*</span></label>
								<div class="col-sm-9 col-lg-10 controls">
									<input type="text" name="link_title" class="form-control" value="<?php echo (isset($asset['name'])) ? $asset['name'] : "" ; ?>"/>
									{!! $errors->first('link_title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									<!-- <span class="help-inline">Some hint here</span> -->
										<input type="hidden" value=" {{ $asset['_id'] }}" name="media_id">
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.url')}} <span class="red">*</span></label>
								<div class="col-sm-9 col-lg-10 controls">
									<input type="text" class="form-control medium" name="link_url" value="<?php echo (isset($asset['url'])) ? $asset['url'] : "" ; ?>"/>
									{!! $errors->first('link_url', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.visibility')}}  <span class="red">*</span></label>
								<div class="col-sm-9 col-lg-10 controls">
									<select class="form-control chosen" tabindex="1" name="link_visibility">
										<option value="public" {{(isset($asset['visibility']) && $asset['visibility'] == "public") ? "selected" : ""}}>{{ trans('admin/dams.public')}}</option>
										<option value="private" {{(isset($asset['visibility']) && $asset['visibility'] == "private") ? "selected" : ""}}>{{ trans('admin/dams.private')}}</option>
									</select>
									{!! $errors->first('link_visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
	                    	<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.description')}} </label>
								<div class="col-sm-9 col-lg-10 controls">
									<textarea class="form-control" rows="3" name="link_description"><?php echo (isset($asset['description'])) ? $asset['description'] : "" ; ?></textarea>
									{!! $errors->first('link_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									<!-- <span class="help-inline">Some hint here</span> -->
								</div>
							</div>
							<div class="form-group">
								<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.keywords_tags')}}</label>
								<div class="col-sm-9 col-lg-10 controls">
									<input type="text" class="form-control tags medium" name="link_keyword" value="<?php echo (isset($asset['tags'])) ? implode(',',$asset['tags']) : ""; ?>"/>
									{!! $errors->first('link_keyword', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
							<div class="form-group">
								<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
									<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ trans('admin/dams.update')}}</button>
									@if(isset($packet) && $packet!='')
										<a href="{{URL::to('/cp/contentfeedmanagement/elements/'.$packet)}}" ><button type="button" class="btn">{{ trans('admin/dams.cancel')}}</button></a>
									@else
										<a href="{{URL::to('/cp/dams/')}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}" ><button type="button" class="btn">{{ trans('admin/dams.cancel')}}</button></a>
									@endif
										
								</div>
							</div>
	                    </form>
	                <?php } ?>
                </div>
	        </div>
	    </div>
	    <script>
	    	$(document).ready(function(){
	    		var slideflag = 0;
	    		$('[name="media_type"]').change(function(){
	    			var $this = $(this);
	    			$this.closest('.form-group').next().slideDown();
	    			$('[name="input_type"]').eq(0).trigger('change');
	    			$('#srtselector').remove();

                    var visibilityFieldContainer = $("#visibility-field-container");

                    if (($(this).val() == "scorm")) {
                        visibilityFieldContainer.css({
                            display : "none"
                        });
                    } else if (visibilityFieldContainer.css("display") === "none") {
                        visibilityFieldContainer.css({
                            display : "block"
                        });
                    }

	    			switch($this.val()){
	    				case "video" :
	    						$('#filetypehint').html("{{ trans('admin/dams.video_types', ['size' => config('app.dams_max_upload_size')]) }}");
	    						$('<div class="form-group" id="srtselector"> <label class="col-sm-3 col-lg-2 control-label">Subtitle File</label> <div class="col-sm-9 col-lg-10 controls"><?php echo (isset($asset['srt_client_name'])) ? '<span class="">The Selected file is <strong>'.$asset['srt_client_name'].'</strong></span><br /><br />' : ""; ?> <div class="fileupload fileupload-new" data-provides="fileupload"> <div class="input-group"> <div class="input-group-btn"> <a class="btn bun-default btn-file"> <span class="fileupload-new">Select file</span> <span class="fileupload-exists">Change</span> <input type="file" class="file-input" name="srtfile" /> </a> <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a> </div> <div class="form-control uneditable-input"> <i class="fa fa-file fileupload-exists"></i> <span class="fileupload-preview"></span> </div> </div> </div> <span class="help-inline" id="srtfiletypehint">Only SRT files are supported</span><br /> </div> </div>').insertAfter('#fileselector');
	    						@if (config('app.dams_media_library_transcoding'))
		    						$('<div class="form-group" id="transcodingselector"> <label class="col-sm-3 col-lg-2 control-label">Transcoding <span class="red">*</span></label> <div class="col-sm-9 col-lg-10 controls"> <strong> <?php if(isset($asset['transcoding'])) ucfirst($asset['transcoding'].'.');?> </strong> This option cannot be changed. </div></div>').insertBefore('#fileselector');
		    						var error = "{!! $errors->first('srtfile', '<span class="help-inline" style="color:#f00">:message</span>') !!}";
		    						if(error)
		    							$(error).insertAfter($('#srtselector').find('span').last().next());
	    						@endif
	    					break;
	    				case "image" :
	    						$('#filetypehint').html('JPEG and PNG are supported. Max file size allowed is 512 MB.');
	    					break;
	    				case "document" :
	    						$('#filetypehint').html('{{trans("admin/dams.document_types")}}');
	    					break;
	    				case "audio" :
	    						$('#filetypehint').html('Only Mp3 file is supported.');
	    					break;
	    				case "scorm" :
	    						$( "#scorm_hide").hide();
	    						$('#filetypehint').html("{{ trans("admin/dams.scorm_file_upload_hint") }}");
	    					break;
	    			}
	    			if(slideflag){
	    				$this.closest('.form-group').next().find('input[type="radio"]').prop('checked',false);
	    				$('.help-inline[style="color:#f00"]').remove();
	    			}
	    			slideflag = 1;
	    		});
	    		$('[name="media_type"]:checked').trigger('change');
	    		$('[name="input_type"]').change(function(){
	    			$('.dams_form').slideUp();
	    			$('#'+$(this).val()).slideDown();
	    		});
	    		$('[name="input_type"]:checked').trigger('change');
	    		$('input.tags').tagsInput({
		            width: "auto"
		        });
		        $('form').slice(2).submit(function(e){
					var $new_media_elem = $('<input type="hidden" name="media_type">');
					var $new_input_elem = $('<input type="hidden" name="input_type">');
					var $this = $(this);
					$new_media_elem.val($('[name="media_type"]:checked').val());
					$this.find('[name="media_type"]').remove();
					$this.append($new_media_elem);
					$new_input_elem.val($('[name="input_type"]:checked').val());
					$this.find('[name="input_type"]').remove();
					$this.append($new_input_elem);
					console.log($this.serialize());
					// e.preventDefault();
				})
	    	})
	    </script>
	</div>
@stop