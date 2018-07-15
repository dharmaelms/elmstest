@section('content')
<?php use App\Model\SiteSetting;
 ?>  
 <link rel="stylesheet" href="{{ URL::asset('admin/css/jquery.Jcrop.min.css')}}">
    <script src="{{ URL::asset('admin/js/jquery.Jcrop.min.js')}}"></script>
<style type="text/css">
  /* Apply these styles only when #preview-pane has
   been placed within the Jcrop widget */
.jcrop-holder #preview-pane {
  display: block;
  position: absolute;
  z-index: 2000;
  top: 10px;
  right: -280px;
  padding: 6px;
  border: 1px rgba(0,0,0,.4) solid;
  background-color: white;

  -webkit-border-radius: 6px;
  -moz-border-radius: 6px;
  border-radius: 6px;

  -webkit-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
  -moz-box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
  box-shadow: 1px 1px 5px 2px rgba(0, 0, 0, 0.2);
}

/* The Javascript code will set the aspect ratio of the crop
   area based on the size of the thumbnail preview,
   specified here */
#preview-pane .preview-container {
  width: 250px;
  height: 170px;
  overflow: hidden;
}
</style>
@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
      <button class="close" data-dismiss="alert">×</button>
     
      {{ Session::get('success') }}
    </div>
<?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
      <button class="close" data-dismiss="alert">×</button>
    
      {{ Session::get('error') }}
    </div>
<?php Session::forget('error'); ?>
@endif
<?php $quotes = SiteSetting::module('Homepage', 'Quotes'); ?>
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
      </div>
      <div class="box-content">
      
    
        <form action="{{ URL::to('/cp/testimonials/edit-testimonial/'.$testimonial['id']) }}" class="form-horizontal form-bordered form-row-stripped" enctype="multipart/form-data" method="post" >         
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/testimonial.add_page_name_label') }}  <span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" max='20' name="name" <?php if(Input::old('name')) {?>value="{{Input::old('name')}}"<?php } elseif($errors->first('name')) {?> value="{{Input::old('name')}}"<?php } elseif(isset($testimonial['name'])) {?> value="{{$testimonial['name']}}"<?php } ?>>
               <!--  <input type="text" class="form-control" name="name" value="{{ Input::old('name') }}">  -->
                {!! $errors->first('name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
              <input type="hidden" name='type' value='home_page'>
                  <!-- logo image -->
              <?php 
              $logo = $testimonial['logopath'];
              $logo_name = $testimonial['logoname'];
              $logo_diamension = $testimonial['diamension'];
              ?>
                <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/testimonial.add_page_logo_label') }} <span class="red">*</span></label>
                <div class="col-sm-2 col-lg-2 controls">
                <?php if(!empty($logo) ) {?>
                <img src="{{URL::to($logo)}}" height="100%" width="100%"  class="preview1">
                <?php } ?>
                </div>
               
                <div class="col-sm-6 col-lg-7 controls">
              
                <span >
                    <span class="">{{ trans('admin/testimonial.selected_pic_is') }} <strong>{{ $logo_name }}</strong></span>
                </span>
               
                
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/testimonial.change') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/testimonial.change') }}</span>
                                    <input type="file" class="file-input" name="file" id='uploadphoto'>
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/testimonial.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="old_diamension" value="{{ $logo_diamension }}">
                    <input type="hidden" name="old_file" value="{{$logo_name}}">
                    <span class="help-inline"> {{ trans('admin/testimonial.upload_image_note') }}</span><br /> 
                     {!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!} 
                </div>
                </div>

            <?php
                if(Input::old('testimonial_description'))
                {
                  $description = Input::old('testimonial_description');
                }
                elseif(isset($testimonial['description']))
                {
                  $description = $testimonial['description'];
                }
                else
                {
                  $description = "";
                }
            ?>


            <div class="form-group">
                <?php 
                  if(Input::old('sort_order'))
                  {
                    $sort_order=Input::old('sort_order');
                  }
                  elseif(isset($testimonial['sort_order']))
                  {
                    $sort_order=$testimonial['sort_order'];
                  }
                  else
                  {
                    $l = $order;
                  }
                ?>
                 <?php
                if(Input::old('testimonial_short_description'))
                {
                  $testimonial_short_description = Input::old('testimonial_short_description');
                }
                elseif(isset($testimonial['short_description']))
                {
                  $testimonial_short_description = $testimonial['short_description'];
                }
                else
                {
                  $testimonial_short_description = "";
                }
            ?>
                <label class="col-sm-4 col-lg-3 control-label" for="sort_order">{{ trans('admin/testimonial.add_page_sort_order_label') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <select name="sort_order" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/testimonial.add_page_sort_order_label') }}">
                        @for($i=1;$i<=$order;$i++)
                            <option value="{{$i}}" <?php if($sort_order == $i) echo "selected"?>>{{$i}}</option>
                        @endfor
                    </select>
                    <input type="hidden" name="curval" value="{{$testimonial['sort_order']}}"><br>
                    <span class="help-inline">At a time only {{ $quotes['number_of_quotes_display'] }} {{ trans('admin/testimonial.note_on_homepage') }}</span><br>
                    {!! $errors->first('sort_order', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
       <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="testimonial_description">{{ trans('admin/testimonial.add_page_description_label') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="testimonial_description">{{ $description}}</textarea><br>
                  <span class="help-inline">{{ trans('admin/testimonial.quotes_description') }}</span><br />
                  {!! $errors->first('testimonial_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="testimonial_short_description">{{ trans('admin/testimonial.short_description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="testimonial_short_description">{{ $testimonial_short_description}}</textarea><br>
                  <span class="help-inline">{{ trans('admin/testimonial.quotes_short_description') }}</span><br />
                  {!! $errors->first('testimonial_short_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
              </div>
            </div>
            
             <?php
                if(Input::old('home_page_display_status'))
                {
                  $home_page_display_status = Input::old('home_page_display_status');
                }
                elseif(isset($testimonial['home_page_display_status']))
                {
                  $home_page_display_status = $testimonial['home_page_display_status'];
                }
                else
                {
                  $home_page_display_status = "";
                }
            ?>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="type"></label>
            <div class="col-sm-6 col-lg-3 controls">
                <input type="checkbox" value="YES" name="home_page_display_status" <?php if($home_page_display_status == 'YES') { ?> checked <?php } ?> > {{ trans('admin/testimonial.add_page_home_display_label') }}
              </div>
              </div>
            <div class="form-group">
            <?php
                if(Input::old('status'))
                {
                  $status = Input::old('status');
                }
                elseif(isset($testimonial['status']))
                {
                  $status = $testimonial['status'];
                }
                else
                {
                  $status = "";
                }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">Status</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value="ACTIVE" <?php if($status == 'ACTIVE'){ ?> selected <?php } ?>>Active</option>
                  <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE'){ ?> selected <?php } ?>>In-active</option> 
                </select> 
                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>

            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Save">
                    <a class="btn" href="{{ URL::to('cp/testimonials') }}">{{ trans('admin/testimonial.cancel') }}</a>
                </div>
            </div>
          </div>
        </form>
    </div>
  </div><!--Register-->
</div>
<script type="text/javascript">
  jQuery(function($) {
  var jcrop_api;  
initJcrop();

function readURL(input) {

    if (input.files && input.files[0]) {
        var reader = new FileReader();
         $('.jcrop-holder').replaceWith('');
        reader.onload = function (e) {
            $('.preview1').attr('src', e.target.result).show();
             $('.preview1').show();
          initJcrop();
           
        }

        reader.readAsDataURL(input.files[0]);
    }
}

$("#uploadphoto").change(function () {
  $('.preview1').hide();

  if(jcrop_api){
                       jcrop_api.destroy();
                   }
    readURL(this);
});

function initJcrop()
            {
                jcrop_api = $.Jcrop('.preview1',{onChange: updatePreview, onSelect: updatePreview });
              }
  function updatePreview(c)
    {
         $('#x').val(c.x);
        $('#y').val(c.y);
        $('#w').val(c.w);
        $('#h').val(c.h);
       


    }


});
</script>
@stop