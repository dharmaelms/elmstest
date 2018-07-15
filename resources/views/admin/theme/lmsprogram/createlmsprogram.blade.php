@section('content')
   @if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>

  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
<?php
use App\Model\ManageAttribute;
use App\Model\SiteSetting;
$varianttype='batch';
$fields=ManageAttribute::getVariants($varianttype);
?>
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
	
  
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
    <script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
	
   <div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
       
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/lmscoursemanagement/add-lmsprogram/')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype="multipart/form-data" files="true">
         <!--start-->
          <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label" for="name">{{trans('admin/lmscourse.title')}}<span class="red">*</span></label>
            <div class="col-sm-9 col-lg-10 controls">
              <input type="text" class="form-control" name="program_title" value="{{Input::old('program_title')}}">
              {!! $errors->first('program_title', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              @if( Session::get('programtitle_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('programtitle_exist') !!}</span>
                <?php Session::forget('programtitle_exist'); ?>
              @endif
            </div>
          </div>
            <!--end-->
             <!--start-->
          <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label" for="name">{{trans('admin/lmscourse.short_name')}}<span class="red">*</span></label>
            <div class="col-sm-9 col-lg-10 controls">
              <input type="text" class="form-control" name="title_lower" value="{{Input::old('title_lower')}}">
              {!! $errors->first('title_lower', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              @if( Session::get('titlelower_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('titlelower_exist') !!}</span>
                <?php Session::forget('titlelower_exist'); ?>
              @endif
            </div>
          </div>
            <!--end-->
            <!--start-->
            <div class="form-group">
                 <label for="lmsprogram_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/lmscourse.start_date')}} <span class="red">*</span></label>
                    <div class="col-sm-9 col-lg-10 controls">
                        <div class="input-group date">
                        <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                        <input type="text" readonly name="program_startdate" id="program_startdate" class="form-control datepicker"
                value="{{ (Input::old('program_startdate')) ? Input::old('program_startdate') : date('d-m-Y') }}" style="cursor: pointer">
                        </div>
                  {!! $errors->first('program_startdate', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                    </div>
            </div>
			  <!--end-->
			  <!--start-->
			  <div class="form-group">
           <label for="lmsprogram_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/lmscourse.end_date')}} <span class="red">*</span></label>
                    <div class="col-sm-9 col-lg-10 controls">
                        <div class="input-group date">
                        <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                        <input type="text" readonly name="program_enddate" id="program_enddate" class="form-control datepicker"
                value="{{ (Input::old('program_enddate')) ? Input::old('program_enddate') : date('d-m-Y') }}" style="cursor: pointer">
                        </div>
                    {!! $errors->first('program_enddate', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                    </div>
            </div>
            <!--end-->
            <!--start-->
            <div class="form-group">
                 <label for="lmsprogram_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/lmscourse.display_start_date')}} <span class="red">*</span></label>
                    <div class="col-sm-9 col-lg-10 controls">
                        <div class="input-group date">
                        <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                        <input type="text" readonly name="program_display_startdate" id="program_display_startdate" class="form-control datepicker"
                value="{{ (Input::old('program_display_startdate')) ? Input::old('program_display_startdate') : date('d-m-Y') }}" style="cursor: pointer">
                        </div>
                    {!! $errors->first('program_display_startdate', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                    </div>
			</div>
			  <!--end-->
			  <!--start-->
			  <div class="form-group">
                 <label for="lmsprogram_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/lmscourse.display_end_date')}} <span class="red">*</span></label>
                    <div class="col-sm-9 col-lg-10 controls">
                        <div class="input-group date">
                        <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                        <input type="text" readonly name="program_display_enddate" id="program_display_enddate" class="form-control datepicker"
                value="{{ (Input::old('program_display_enddate')) ? Input::old('program_display_enddate') : date('d-m-Y') }}" style="cursor: pointer">
                        </div>
                {!! $errors->first('program_display_enddate', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                    </div>
            </div>
            <!--end-->
			
            
        <!--start-->
             <div class="form-group">
              <?php 
                  if(Input::old('program_visibility'))
                  {
                    $visibility=Input::old('program_visibility');
                  }
                  else
                  {
                    $visibility=Input::old('program_visibility');
                  }
              ?>
            <label class="col-sm-2 col-lg-2 control-label" for="item_type">{{trans('admin/lmscourse.visibility')}}</label>
              <div class="col-sm-4 col-lg-4 controls">
                <select name="program_visibility" class="chosen gallery-cat form-control" data-placeholder="Select">
                  <option value="1" <?php if($visibility == '1') echo "selected"?>>{{trans('admin/lmscourse.yes')}}</option>
                  <option value="0" <?php if($visibility == '0') echo "selected"?>>{{trans('admin/lmscourse.no')}}</option>
                </select>
               </div>
                  <?php 
                  if(Input::old('status'))
                  {
                    $status=Input::old('status');
                  }
                  else
                  {
                    $status=Input::old('status');
                  }
              ?>
            <label class="col-sm-1 col-lg-1 control-label" for="item_type">{{trans('admin/lmscourse.status')}}</label>
              <div class="col-sm-5 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="{{trans('admin/lmscourse.select_status')}}">
                  <option value="active" <?php if($status == 'active') echo "selected"?>>Active</option>
                  <option value="inactive" <?php if($status == 'inactive') echo "selected"?>>In-Active</option>
                </select>
               </div>
              </div>
		<!--end-->
		<!--start-->
			<div class="form-group">
              <label class="col-sm-3 col-lg-2 control-label" for="address">{{trans('admin/lmscourse.description')}}</label>
              <div class="col-sm-9 col-lg-10 controls">
                <textarea id="textarea" class="form-control" rows="5" name="program_description"><?php if(Input::old('program_description'))
                { echo Input::old('program_description'); }  ?></textarea>
                {!! $errors->first('program_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
          </div>
			<!--end-->
		<!--start-->
		<div class="form-group">
			<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/lmscourse.keywords_tags')}}</label>
			<div class="col-sm-9 col-lg-10 controls">
			<input type="text" class="form-control tags medium" value="{{Input::old('program_keyword')}}" name="program_keyword" />
	{!! $errors->first('program_keyword', '<span class="help-inline" style="color:#f00">:message</span>') !!}
			</div>
	  </div>
		<!--end-->
		<!--start-->
		<div class="form-group">
                <?php 
                  if(Input::old('sort_order'))
                  {
                    $order=Input::old('sort_order');
                  }
                  else
                  {
                    $order=$sort_order;
                  }
                ?>
                <label class="col-sm-3 col-lg-2 control-label" for="sort_order">{{trans('admin/lmscourse.sort_order')}}</label>
                <div class="col-sm-9 col-lg-10 controls">
                    <select name="sort_order" class="chosen gallery-cat form-control" data-placeholder="{{trans('admin/lmscourse.sort_order')}}">
                        @for($i=1;$i<=$sort_order;$i++)
                            <option value="{{$i}}" <?php if($order == $i) echo "selected"?>>{{$i}}</option>
                        @endfor
                    </select>
                    <input type="hidden" name="curval" value="{{$sort_order}}">
                    {!! $errors->first('sort_order', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                </div>
            </div>
		<!--end-->
		<!--start-->
                            <div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/lmscourse.cover_image')}}</label>
							<div class="col-sm-9 col-lg-10 controls">
								<div class="fileupload fileupload-new">
									<div class="fileupload-new img-thumbnail" style="width: 200px;padding:0">
										<?php if(Input::old('banner')){ ?>
											<img src="{{URL::to('/cp/dams/show-media/'.Input::old('banner'))}}" width="100%" alt="" id="bannerplaceholder"/>
										<?php } else{ ?>
											<img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" id="bannerplaceholder"/>
										<?php } ?>
									</div>
									<div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
									<div>
										<button class="btn" type="button" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&filter=image&select=radio')}}">{{trans('admin/lmscourse.select_img_from_library')}}</button>
										<?php
											if(Input::old('banner')){ ?>
												<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/lmscourse.remove')}} </button>
									<?php 	}
										?>
										<input type="hidden" name="banner" value="{{(Input::old('banner')) ? Input::old('banner') : ""}}" >
										
									</div>
								</div>
                                {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
                        <!--end-->
						@if(!empty($fields) && SiteSetting::module('Lmsprogram', 'more_batches') == 'on')
		@include('admin.theme.sitesettings.attributeload', ['variant' => 'batch'])
	  @endif
         <!--start-->
          <div class="form-group last">
              <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                  <input type="submit" class="btn btn-info" value="Save">
                  <form><input type="button" class="btn" value="Cancel" onclick="history.go(-1);return false;" /></form>
              </div>
          </div>
        <!--end-->
            
        </form> 
      </div>
    </div>
  </div>
	<!--start-->
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
	                                            {{trans('admin/lmscourse.view_media_details')}}
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
                     <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/lmscourse.assign')}}</a>
                        <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/lmscourse.close')}}</a>
	                </div>
	            </div>
	        </div>
	    </div>
	<!--end-->
            <script>
	    	$(document).ready(function(){
                    $('.datepicker').datepicker({
                    format : "dd-mm-yyyy",
                    startDate: '+0d'
                })
				//$('#filetypehint').html('JPEG and PNG are supported');
				$('input.tags').tagsInput({
		            width: "auto"
		        });
				$('#selectfromdams').click(function(e){
	    			e.preventDefault();
	    			simpleloader.fadeIn();
	    			var $this = $(this);
	    			var $triggermodal = $('#triggermodal');
	    			var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="" style="max-height:500px !important" frameBorder="0"></iframe>');
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
	    					$('<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/lmscourse.remove')}} </button>').insertBefore($('input[name="banner"]').val($selectedRadio.val()));
	    					$triggermodal.modal('hide');
	    				}
	    				else{
	    					alert('Please select atleast one entry');
	    				}
	    			});
				});
				$(document).on('click','#removethumbnail',function(){
					$('#bannerplaceholder').attr('src','');
					$('#bannerplaceholder').attr('src', '{{URL::asset("admin/img/demo/200x150.png")}}');
					$('input[name="banner"]').val('');
					$(this).remove();
				});
            })
            </script>
</div>
@stop

