
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

    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker-channel.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div>
	            </div>
	            <div class="box-content">
                    <form class="form-horizontal form-bordered form-row-stripped" method="post">
						<div class="form-group">
                            <label for="package_title" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.title')}} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <input type="text" name="package_title" id="package_title" class="form-control" value="{{ Input::old('package_title') }}">
                                <input type="hidden" name="package_slug" id="package_slug" class="form-control" value="{{ Input::old('package_slug') }}">
                                <?php $msg = $errors->first('package_title', '<span class="help-inline" style="color:#f00">:message</span>'); ?>
                                <?php if($msg == "") echo $errors->first('package_slug', '<span class="help-inline" style="color:#f00">:message</span>'); else echo $msg; ?>
                            </div>
                        </div>
						
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.short_name')}}</label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text"  class="form-control" value="{{Input::old('package_shortname')}}" name="package_shortname" placeholder="{{trans('admin/package.short_name_nt_disp')}}"/>
                                <input type="hidden" name="package_shortname_slug" id="package_shortname_slug" class="form-control" value="{{ Input::old('package_shortname_slug') }}">
								{!! $errors->first('package_shortname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
					
                        <div class="form-group">
                            <label for="package_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.start_date')}} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                              <div class="input-group date">
                              	<span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                	<input type="text" readonly name="package_start_date" id="package_start_date" class="form-control datepicker" value="{{ (Input::old('package_start_date')) ? Input::old('package_start_date') : date('d-m-Y') }}" style="cursor: pointer">
                                </div>
                                {!! $errors->first('package_start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="package_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.end_date')}} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                               <div class="input-group date">
                              		<span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                		<input type="text" readonly name="package_end_date" id="package_end_date" class="form-control datepicker" value="{{ (Input::old('package_end_date')) ? Input::old('package_end_date') : date('d-m-Y',strtotime('+5 years', time())) }}" style="cursor: pointer">
                                </div>
                                {!! $errors->first('package_end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="package_display_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.display_start_date')}} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                               <div class="input-group date">
                              	<span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                	<input type="text" readonly name="package_display_start_date" id="package_display_start_date" class="form-control datepicker" value="{{ (Input::old('package_display_start_date')) ? Input::old('package_display_start_date') : date('d-m-Y') }}" style="cursor: pointer">
                                </div>
                                {!! $errors->first('package_display_start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="package_display_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.display_end_date')}} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-10 controls">
                               <div class="input-group date">
                              <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                <input type="text" readonly name="package_display_end_date" id="package_display_end_date" class="form-control datepicker" value="{{ (Input::old('package_display_end_date')) ? Input::old('package_display_end_date') : date('d-m-Y',strtotime('+5 years', time())) }}" style="cursor: pointer">
                                </div>
                                {!! $errors->first('package_display_end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        @if (config('app.ecommerce'))
                            <div class="form-group">
                                    <label for="sellability" class="col-sm-2 col-lg-2 control-label">{{trans('admin/package.sellability')}} <span class="red">*</span></label>
                                    <div class="col-sm-4 col-lg-4 controls">
                                        <select class="form-control" name="sellability" id="sellability" data-rule-required="true">
        								   <option <?php if(Input::old('sellability') == "no") echo "selected"?> value="no">{{trans('admin/package.no')}}</option>
                                            <option <?php if(Input::old('sellability') == "yes") echo "selected"?> value="yes">{{trans('admin/package.yes')}}</option>
                                         
                                        </select>
                                        {!! $errors->first('sellability', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                            </div>
                        @endif

                        <div class="form-group">
                            <label for="visibility" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.visibility')}} <span class="red">*</span></label>
                            <div class="col-sm-5 col-lg-5 controls">
                                <select class="form-control" name="visibility" id="visibility" data-rule-required="true">
                                    <option <?php if(Input::old('visibility') == "yes") echo "selected"?> value="yes">{{trans('admin/package.yes')}}</option>
                                    <option <?php if(Input::old('visibility') == "no") echo "selected"?> value="no">{{trans('admin/package.no')}}</option>
                                </select>
                                {!! $errors->first('visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>

                        <div class="form-group">
                            <label for="select" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.status')}} <span class="red">*</span></label>
                            <div class="col-sm-5 col-lg-5 controls">
                                <select class="form-control" name="status" id="status" data-rule-required="true">
                                    <option <?php if(Input::old('status') == "active") echo "selected"?> value="active">{{trans('admin/package.active')}}</option>
                                    <option <?php if(Input::old('status') == "inactive") echo "selected"?> value="inactive">{{trans('admin/package.in_active')}}</option>
                                </select>
                                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
						<!--start-->
                        @if (!config('app.ecommerce'))
    						<div class="form-group" id="access">
                                <label for="select" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.access')}}<span class="red">*</span></label>
                                <div class="col-sm-5 col-lg-5 controls">
                                    <select class="form-control" name="package_access" id="package_access" data-rule-required="true">
                                        <option <?php if(Input::old('package_access') == "restricted_access") echo "selected"?> value="restricted_access">{{trans('admin/package.restricted')}}</option>
                                        <option <?php if(Input::old('package_access') == "general_access") echo "selected"?> value="general_access">{{trans('admin/package.general')}}</option>
                                    </select>
                                    {!! $errors->first('access', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
                            </div>
                        @endif
						<!--end-->
                        <div class="form-group">
                            <label for="package_description" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.description')}} </label>
                            <div class="col-sm-9 col-lg-10 controls">
                                <textarea name="package_description" id="package_description" rows="5" class="form-control" >{{ Input::old('package_description') }}</textarea>
                                {!! $errors->first('package_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.keyword_tags')}}</label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text" class="form-control tags medium" value="{{Input::old('package_tags')}}" name="package_tags" />
								{!! $errors->first('package_tags', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
                        <div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.cover_image')}} </label>

							<div class="col-sm-9 col-lg-10 controls">
                                <div class="col-sm-12 col-lg-6">    
                                    <div class="fileupload fileupload-new">
                                        <div class="fileupload-new img-thumbnail" style="width: 200px;padding:0">
                                            <?php if (Input::old('banner')) { ?>
                                                <img src="{{URL::to('/cp/dams/show-media/'.Input::old('banner'))}}" width="100%" alt="" id="bannerplaceholder"/>
                                            <?php }else{ ?>
                                                <img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" id="bannerplaceholder"/>
                                            <?php } ?>
                                        </div>
                                        <div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                                        <div>
                                            <button class="btn" type="button" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&filter=image&from=add_package&select=radio')}}">{{trans('admin/program.select')}}</button>
                                            @if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
                                            <button class="btn" type="button" id="upload" data-url="{{URL::to("cp/dams/add-media?view=iframe&from=add_package&filter=image")}}">{{trans('admin/program.upload_new')}}</button>
                                            @endif
                                            <?php if (Input::old('banner')) { ?>
                                                    <button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/package.remove')}} </button>
                                            <?php } ?>
                                            <input type="hidden" name="banner" value="{{(Input::old('banner')) ? Input::old('banner') : ""}}" >
                                        </div>
                                    </div>
                                    {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                </div>
							</div>
						</div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{trans('admin/package.save')}}</button>
                               <a href="{{URL::to('/cp/package/'.$url)}}"><button type="button" class="btn">{{trans('admin/package.cancel')}}</button></a>
                            </div>
                        </div>
                     </form>
                </div>
	        </div>
	    </div>
	    <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
	        <div class="modal-dialog modal-lg">
	            <div class="modal-content">
	                <div class="modal-header">
	                    <div class="row">
	                        <div class="col-md-12">
	                            <div class="box">
	                                <div class="box-title">
	                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                    <h3 class="modal-header-title" >
	                                        <i class="icon-file"></i>
	                                            {{trans('admin/package.view_media_details')}}
	                                    </h3>                                                
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	                <div class="modal-body" style="padding-top: 0px;">
	                    ...
	                </div>
	                <div class="modal-footer">
                     <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/package.assign')}}</a>
                        <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/package.close')}}</a>
	                </div>
	            </div>
	        </div>
	    </div>
	    <script>
	    	$(document).ready(function(){
                
                $('.datepicker').datepicker({
                    format : "dd-mm-yyyy",
                    startDate: '+0d'
                }).on('changeDate',function(){
                    $(this).datepicker('hide')
                });

	    		$('[name="package_title"]').on("blur",function(){
					if($(this).val().trim() != ""){
						var slug=$('[name="package_slug"]').val($(this).val().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'))

                        //If name contains special characters,generated slug will be empty.Sending slug as special character to get proper validation.
                        if(!slug.val())
                        {
                            $('[name="package_slug"]').val('$*&');
                        }
					}
				});

                $('[name="package_shortname"]').on("blur",function(){
                    if($(this).val().trim() != ""){
                        var sort_slug=$('[name="package_shortname_slug"]').val($(this).val().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'))

                        //If name contains special characters,generated slug will be empty.Sending slug as special character to get proper validation.
                        if(!sort_slug.val())
                        {
                            $('[name="package_shortname_slug"]').val('$*&');
                        }
                    }
                });

				$('#selectfromdams, #upload').click(function(e){
	    			e.preventDefault();
	    			simpleloader.fadeIn();
	    			var $this = $(this);
	    			var $triggermodal = $('#triggermodal');
	    			var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="500px" style="max-height:500px !important" frameBorder="0"></iframe>');
	    			$iframeobj.unbind('load').load(function(){
	    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
	    					$triggermodal.modal('show');
	    				simpleloader.fadeOut();
	    			});
	    			$triggermodal.find('.modal-body').html($iframeobj);
	    			$triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());
	    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
	    				var $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked');
	    				if($selectedRadio.length){
	    					$('#bannerplaceholder').attr('src','{{URL::to('/cp/dams/show-media/')}}/'+$selectedRadio.val()).width("100%");
	    					$('#removethumbnail').remove();
	    					$('<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/package.remove')}} </button>').insertBefore($('input[name="banner"]').val($selectedRadio.val()));
	    					$triggermodal.modal('hide');
	    				}
	    				else{
	    					alert('Please select atleast one entry');
	    				}
	    			});
				});

	    		$('input.tags').tagsInput({
		            width: "auto"
		        });
				
                $(document).on('click','#removethumbnail',function(){
					$('#bannerplaceholder').attr('src','');
					$('#bannerplaceholder').attr('src', '{{URL::asset("admin/img/demo/200x150.png")}}');
					$('input[name="banner"]').val('');
					$(this).remove();
				});

            });
	    	
	    </script>
	</div>
@stop
