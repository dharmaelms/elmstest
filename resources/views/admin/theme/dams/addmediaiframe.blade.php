@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">x</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
		<?php Session::forget('success'); ?>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">x</button>
			<!-- <strong>Error!</strong> -->
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<style>
		.center {
		    text-align: center !important;
		}
		.page-title {
		    display: none;
		}
		#main-content{
			background: white !important;
			padding-top: 0px;
		}
		body{
			padding-bottom: 0px !important;
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
	            <div class="box-content">
	            <?php 
	            	$filter = Input::get('filter', ['all']);
	            	if (!is_array($filter) ) {
	            		$filter = [$filter];
	            	}
	            ?>
	            	@if(in_array('all', $filter))
	            	<div class="row" style="margin-top: 10px;">
	            		<div class="col-md-12">
		                    <div class="pull-right">
		                    	<div class="btn-group">
									@if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::VIEW_MEDIA))
										<a class="btn btn-primary btn-sm" href="{{url::to('/cp/dams/list-media?view=iframe')}}
											{{(Input::get('select') == "radio") ? "&select=radio" : ""}}
											{{(Input::get('filter') == "media") ? "&filter=all" : ""}}
											{{((Input::get('id') == "id") ? "&id=id" : "")}}"
										   	title="List media" data-placement="left">
											<span class="" data-original-title="" title="">
												<i class="fa fa-list-ul"></i>
											</span>&nbsp;{{ trans('admin/dams.list_media')}}
										</a>&nbsp;&nbsp;
									@endif
	                			</div>
		                    </div>
						</div>
		            </div>
		            @endif
                    <form action="#" class="form-horizontal form-bordered form-row-stripped" method="post">
						<div class="form-group media_type_grp">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.media_type')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
	            				@if(in_array('all', $filter) || in_array('image', $filter))
								<label class="radio-inline">
									<input type="radio" name="media_type" value="image" {{(Input::old('media_type') == 'image') ? "checked" : ""}} /> {{ trans('admin/dams.image')}}
								</label>
								@endif
	            				@if(in_array('all', $filter) || in_array('video', $filter))
									<label class="radio-inline">
										<input type="radio" name="media_type" value="video" {{(Input::old('media_type') == 'video') ? "checked" : ""}} /> {{ trans('admin/dams.video')}}
									</label>
								@endif
	            				@if(in_array('all', $filter) || in_array('document', $filter))
									<label class="radio-inline">
										<input type="radio" name="media_type" value="document" {{(Input::old('media_type') == 'document') ? "checked" : ""}} /> {{ trans('admin/dams.document')}}
									</label>
								@endif
	            				@if(in_array('all', $filter) || in_array('audio', $filter))
									<label class="radio-inline">
										<input type="radio" name="media_type" value="audio" {{(Input::old('media_type') == 'audio') ? "checked" : ""}} /> {{ trans('admin/dams.audio')}}
									</label>
								@endif
	            				@if(in_array('all', $filter) || in_array('scorm', $filter))
									<label class="radio-inline">
										<input type="radio" name="media_type" value="scorm" {{(Input::old('media_type') == 'scorm') ? "checked" : ""}} /> {{ trans('admin/dams.scorm')}}
									</label>
								@endif
								{!! $errors->first('media_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group input_type_grp" style="display:none" id="scorm_hide">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.from')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<label class=""  style="display:none;">
									<input type="radio" name="input_type" value="bulk_import" {{(Input::old('input_type') == 'bulk_import') ? "checked" : ""}}/> {{ trans('admin/dams.bulk_import')}}
								</label>
								<label class="radio-inline">
									<input type="radio" name="input_type" value="uploadform" {{(Input::old('input_type') == 'uploadform') ? "checked" : ""}} /> {{ trans('admin/dams.upload')}}
								</label>
								<?php if(Input::get('filter') != "media") :?>
									<label class="radio-inline">
										<input type="radio" name="input_type" value="links" {{(Input::old('input_type') == 'links') ? "checked" : ""}} />{{ trans('admin/dams.link')}}
									</label>
								<?php endif; ?>
								{!! $errors->first('input_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
                    </form>
                    <style>
                    	<?php if(Input::get('filter') == "media") :?>
                    		.media_type_grp , .input_type_grp{
                    			display:none;
                    		}
						<?php endif; ?>
                    </style>
                    <form action="{{URL::to('cp/dams/upload-handler').'?view=iframe'}}{{(Input::get('select') == "radio") ? "&select=radio" : ""}}&{{http_build_query(["filter" => $filter])}}{{((Input::get('id') == "id") ? "&id=id" : "")}}" class="form-horizontal form-bordered form-row-stripped dams_form" method="post" id="uploadform" style="margin-top:10px;display:none" enctype='multipart/form-data'>
                    	<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.title')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text" name="title" class="form-control" value="{{Input::old('title')}}" />
								<!-- <span class="help-inline">Some hint here</span> -->
								{!! $errors->first('title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group" id="visibility-field-container">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.visibility')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<select class="form-control chosen" tabindex="1" name="visibility">
									<option value="public" {{(Input::old('visibility') == "public") ? "selected" : ""}}>{{ trans('admin/dams.public')}}</option>
									<option value="private" {{(Input::old('visibility') == "private") ? "selected" : ""}}>{{ trans('admin/dams.private')}}</option>
								</select>
								{!! $errors->first('visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group" id="fileselector">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.file')}}  <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<div class="fileupload fileupload-new" data-provides="fileupload">
									<div class="input-group">
										<div class="input-group-btn">
											<a class="btn bun-default btn-file">
												<span class="fileupload-new">{{ trans('admin/dams.select_file')}}</span>
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
								<textarea class="form-control" rows="3" name="description">{{Input::old('description')}}</textarea>
								<!-- <span class="help-inline">Some hint here</span> -->
								{!! $errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.keywords_tags')}}</label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text" class="form-control tags medium" value="{{Input::old('keyword')}}" name="keyword" />
								{!! $errors->first('keyword', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
								<input type="hidden" name="is_only_video" value="{{Input::get('is_only_video', '')}}">
								@if (!is_null(Input::get('from')))
									<input type="hidden" name="from" value="{{Input::get('from')}}">
								@endif
								@if (!is_null(Input::get('program_type')))
									<input type="hidden" name="program_type" value="{{Input::get('program_type')}}">
								@endif
								@if (!is_null(Input::get('program_slug')))
									<input type="hidden" name="program_slug" value="{{Input::get('program_slug')}}">
								@endif
								@if (!is_null(Input::get('post_slug')))
									<input type="hidden" name="post_slug" value="{{Input::get('post_slug')}}">
								@endif
								<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ trans('admin/dams.upload')}}</button>
								@if (!is_null(Input::get('from')) && Input::get('from', '') == 'add-assignment')
									<a href="{{URL::to('/cp/dams?view=iframe&filter=document&from=add-assignment&select=radio')}}" ><button type="button" class="btn">{{ trans('admin/dams.cancel')}}</button></a>
								@else
									<a href="{{URL::to('/cp/dams?view=iframe&filter=image&from=edit_package&select=radio')}}" ><button type="button" class="btn">{{ trans('admin/dams.cancel')}}</button></a>
								@endif

							</div>
						</div>
                    </form>
                    <form action="{{URL::to('cp/dams/upload-handler').'?view=iframe'}}{{(Input::get('select') == "radio") ? "&select=radio" : ""}}&{{http_build_query(["filter" => $filter])}}{{((Input::get('id') == "id") ? "&id=id" : "")}}" class="form-horizontal form-bordered form-row-stripped dams_form" method="post" id="links" style="margin-top:10px;display:none">
                    	<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.title')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text" name="link_title" class="form-control" value="{{Input::old('link_title')}}"/>
								{!! $errors->first('link_title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								<!-- <span class="help-inline">Some hint here</span> -->
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.url')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text" class="form-control medium" name="link_url" value="{{Input::old('link_url')}}"/>
								{!! $errors->first('link_url', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.visibility')}}  <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<select class="form-control chosen" tabindex="1" name="link_visibility">
									<option value="public" {{(Input::old('link_visibility') == "public") ? "selected" : ""}}>{{ trans('admin/dams.public')}}</option>
									<option value="private" {{(Input::old('link_visibility') == "private") ? "selected" : ""}}>{{ trans('admin/dams.private')}}</option>
								</select>
								{!! $errors->first('link_visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
                    	<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.description')}} </label>
							<div class="col-sm-9 col-lg-10 controls">
								<textarea class="form-control" rows="3" name="link_description">{{Input::old('link_description')}}</textarea>
								{!! $errors->first('link_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								<!-- <span class="help-inline">Some hint here</span> -->
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.keywords_tags')}}</label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text" class="form-control tags medium" name="link_keyword" value="{{Input::old('link_keyword')}}"/>
								{!! $errors->first('link_keyword', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
								<input type="hidden" name="is_only_video" value="{{Input::get('is_only_video', '')}}">
								@if (!is_null(Input::get('from')))
									<input type="hidden" name="from" value="{{Input::get('from')}}">
								@endif
								@if (!is_null(Input::get('program_type')))
									<input type="hidden" name="program_type" value="{{Input::get('program_type')}}">
								@endif
								@if (!is_null(Input::get('program_slug')))
									<input type="hidden" name="program_slug" value="{{Input::get('program_slug')}}">
								@endif
								@if (!is_null(Input::get('post_slug')))
									<input type="hidden" name="post_slug" value="{{Input::get('post_slug')}}">
								@endif
								<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ trans('admin/dams.upload')}}</button>
								<a href="{{URL::to('/cp/dams/list-media?view=iframe')}}{{(Input::get('select') == "radio") ? "&select=radio" : ""}}&{{http_build_query(["filter" => $filter])}}{{((Input::get('id') == "id") ? "&id=id" : "")}}" ><button type="button" class="btn">{{ trans('admin/dams.cancel')}}</button></a>
							</div>
						</div>
                    </form>
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
	    			$('#transcodingselector').remove();

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
	    						$('<div class="form-group" id="srtselector"> <label class="col-sm-3 col-lg-2 control-label">Subtitle File</label> <div class="col-sm-9 col-lg-10 controls"> <div class="fileupload fileupload-new" data-provides="fileupload"> <div class="input-group"> <div class="input-group-btn"> <a class="btn bun-default btn-file"> <span class="fileupload-new">Select file</span> <span class="fileupload-exists">Change</span> <input type="file" class="file-input" name="srtfile" /> </a> <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a> </div> <div class="form-control uneditable-input"> <i class="fa fa-file fileupload-exists"></i> <span class="fileupload-preview"></span> </div> </div> </div> <span class="help-inline" id="srtfiletypehint">Only SRT files are supported</span><br /> </div> </div>').insertAfter('#fileselector');
	    						$('<div class="form-group" id="transcodingselector"> <label class="col-sm-3 col-lg-2 control-label">Transcoding <span class="red">*</span></label> <div class="col-sm-9 col-lg-10 controls"> <select class="form-control" name="transcoding" id="transcoding" data-rule-required="true"> <option <?php if(Input::old('transcoding') == "yes") echo "selected"?> value="yes">Yes</option> <option <?php if(Input::old('transcoding') == "no") echo "selected"?> value="no">No</option> </select> </div></div>').insertAfter('#srtselector');
	    						var error = '{!! $errors->first('srtfile', '<span class="help-inline" style="color:#f00">:message</span>') !!}';
	    						var transcoding_error = '{!! $errors->first('transcoding', '<span class="help-inline" style="color:#f00">:message</span>') !!}';
	    						if(error)
	    							$(error).insertAfter($('#srtselector').find('span').last().next());
	    						if(transcoding)
	    							$(transcoding).insertAfter($('#transcodingselector').find('span').last().next());
	    					break;
	    				case "image" :
	    						$('#filetypehint').html('JPEG and PNG are supported');
	    					break;
	    				case "document" :
	    						$('#filetypehint').html('{{trans("admin/dams.document_types")}}');
	    					break;
	    				case "audio" :
	    						$('#filetypehint').html('Only Mp3 file is supported.');
	    					break;
	    				case "scorm" :
	    						$( "#scorm_hide").hide();
	    						$( "#uploadform").show();
	    						$('#filetypehint').html('Compatible Zip file for scorm file are supported.');
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
		        $('form').slice(1).submit(function(e){
					var $new_media_elem = $('<input type="hidden" name="media_type">');
					var $new_input_elem = $('<input type="hidden" name="input_type">');
					var $this = $(this);
					$new_media_elem.val($('[name="media_type"]:checked').val());
					$this.find('[name="media_type"]').remove();
					$this.append($new_media_elem);
					$new_input_elem.val($('[name="input_type"]:checked').val());
					$this.find('[name="input_type"]').remove();
					$this.append($new_input_elem);
					simpleloader.fadeIn();
				});

				// To select the option if there is only one
				if($('[name="media_type"]').length == 1){
					$('[name="media_type"]').trigger('click');
					if($('[name="input_type"][value="uploadform"]').length == 1){
						$('[name="input_type"][value="uploadform"]').trigger('click').parents('.input_type_grp').hide();
					}
				}
	    	})
	    </script>
	</div>
@stop