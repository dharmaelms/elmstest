@section('content')
<?php 
      $start    =  Input::get('start', 0);
      $limit    =  Input::get('limit', 10);
      $filter   =  Input::get('filter','all');
      $search   =  Input::get('search','');
      $order_by =  Input::get('order_by','4 desc');
      
     

?>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
	  <script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
  
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
     
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/lmscoursemanagement/edit-lmsprogram/'.$lmsprogram['program_id'])}}" class="form-horizontal form-bordered form-row-stripped" method="post" >         
            <!--start-->
            <div class="form-group">
              <label class="col-sm-3 col-lg-2 control-label" for="">{{trans('admin/lmscourse.title')}} <span class="red">*</span></label>
              <div class="col-sm-9 col-lg-10 controls">
                <input type="text" class="form-control" name="program_title" <?php if(Input::old('program_title')) {?>value="{{Input::old('program_title')}}"<?php } elseif($errors->first('program_title')) {?> value="{{Input::old('program_title')}}"<?php } elseif(isset($lmsprogram['program_title'])) {?> value="{{$lmsprogram['program_title']}}"<?php } ?>> 
                {!! $errors->first('program_title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
             @if( Session::get('programtitle_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('programtitle_exist') !!}</span>
                <?php Session::forget('programtitle_exist'); ?>
              @endif
              </div>
            </div>
            <!--end-->
            <!--start-->
            <div class="form-group"> 
              <label class="col-sm-3 col-lg-2 control-label" for="">{{trans('admin/lmscourse.short_name')}}<span class="red">*</span></label>
              <div class="col-sm-9 col-lg-10 controls">
                <input type="text" class="form-control" name="title_lower" <?php if(Input::old('title_lower')) {?>value="{{Input::old('title_lower')}}"<?php } elseif($errors->first('title_lower')) {?> value="{{Input::old('title_lower')}}"<?php } elseif(isset($lmsprogram['title_lower'])) {?> value="{{$lmsprogram['title_lower']}}"<?php } ?>> 
                {!! $errors->first('title_lower', '<span class="help-inline" style="color:#f00">:message</span>') !!}
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
 value="{{ Timezone::convertFromUTC("@".$lmsprogram['program_startdate'],Auth::user()->timezone,'d-m-Y') }}"
 style="cursor: pointer">
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
                value="{{ Timezone::convertFromUTC("@".$lmsprogram['program_enddate'],Auth::user()->timezone,'d-m-Y') }}"
                style="cursor: pointer">
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
                        <input type="text" readonly name="program_display_startdate" id="program_display_startdate"
                        class="form-control datepicker"
        value="{{ Timezone::convertFromUTC("@".$lmsprogram['program_display_startdate'],Auth::user()->timezone,'d-m-Y') }}"
        style="cursor: pointer">
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
                value="{{ Timezone::convertFromUTC("@".$lmsprogram['program_display_enddate'],Auth::user()->timezone,'d-m-Y') }}"
                style="cursor: pointer">
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
                  elseif(isset($lmsprogram['program_visibility'])) {
                  $visibility=$lmsprogram['program_visibility'];
                  }
                  else
                  {
                    $visibility=1;
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
                  elseif(isset($lmsprogram['status']))
                  {
                    $status=$lmsprogram['status'];
                  }
                  else
                  {
                    $status='active';
                  }
              ?>
            <label class="col-sm-1 col-lg-1 control-label" for="item_type">{{trans('admin/lmscourse.status')}}</label>
              <div class="col-sm-5 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="{{trans('admin/lmscourse.select_status')}}">
                  <option value="active" <?php if($status == 'active') echo "selected"?>>Active</option>
                  <option value="inactive" <?php if($status == 'inactive') echo "selected"?>>In-Active</option>
                </select>
                  {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
               </div>
            </div>
            <!--end-->
           <!--start-->
            <div class="form-group">
             <?php
             if(Input::old('program_description'))
             {
               $description=Input::old('program_description');
             }
             
             elseif(isset($lmsprogram['program_description']))
             {
               $description=$lmsprogram['program_description'];
             }
             else
             {
               $description='';
             }
           ?>
              <label class="col-sm-3 col-lg-2 control-label" for="address">{{trans('admin/lmscourse.description')}}</label>
              <div class="col-sm-9 col-lg-10 controls">
                <textarea id="textarea" class="form-control" rows="5" name="program_description">{{$description}}</textarea>
                {!! $errors->first('program_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
          </div>
           
           <!--end-->
              <!--start-->
              <div class="form-group">
									<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/lmscourse.keywords_tags')}}</label>
									<div class="col-sm-9 col-lg-10 controls">
										<input type="text" class="form-control tags medium"
      value="<?php echo (isset($lmsprogram['program_keyword']) && is_array($lmsprogram['program_keyword'])) ?
      implode(',',$lmsprogram['program_keyword']) : ""; ?>" name="program_keyword" />
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
                  elseif(isset($lmsprogram['sort_order']))
                  {
                    $order=$lmsprogram['sort_order'];
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
                    <input type="hidden" name="curval" value="{{$lmsprogram['sort_order']}}">
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
												<?php if($lmsprogram['program_cover_media']){ ?>
													<img src="{{URL::to('/cp/dams/show-media/'.$lmsprogram['program_cover_media'])}}" width="100%" alt="" id="bannerplaceholder"/>
												<?php } else{ ?>
													<img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" id="bannerplaceholder"/>
												<?php } ?>
											</div>
											<div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
											<div>
												<button class="btn" type="button" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&filter=image&select=radio')}}">{{trans('admin/lmscourse.select_img_from_library')}}</button>
												<?php
														if($lmsprogram['program_cover_media']){ ?>
															<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/lmscourse.remove')}} </button>
												<?php 	}
													?>
												<input type="hidden" name="banner" value="{{(isset($lmsprogram['program_cover_media'])) ? $lmsprogram['program_cover_media'] : ""}}">
											</div>
										</div>
		                                {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									</div>
								</div>
           <!--end-->
            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Save">
                    <a class="btn" href="{{ URL::to('cp/lmscoursemanagement') }}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}">{{trans('admin/lmscourse.cancel')}}</a>
                </div>
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
	                    <div class="row custom-box">
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
	                <div class="modal-body">
	                    ...
	                </div>
	                <div class="modal-footer">
	                       <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/lmscourse.assign')}}</a>
		                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/lmscourse.close')}}</a>
	                </div>
	            </div>
	        </div>
	    </div> 
      <script>
	    	$(document).ready(function(){
                    $('.datepicker').datepicker({
                    format : "dd-mm-yyyy",
                    startDate: '+0d'
                })
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
					$('#bannerplaceholder').attr('src', '{{URL::asset("admin/img/demo/200x150.png")}}');
					$('input[name="banner"]').val('');
					$(this).remove();
				});
				
            })
            </script>
@stop