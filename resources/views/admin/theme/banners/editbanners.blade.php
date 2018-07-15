@section('content')
@if ( Session::get('success') )
    <div class="alert alert-success">
      <button class="close" data-dismiss="alert">×</button>
      <strong>Success!</strong>
      {{ Session::get('success') }}
    </div>
<?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
      <button class="close" data-dismiss="alert">×</button>
      <strong>Error!</strong>
      {{ Session::get('error') }}
    </div>
<?php Session::forget('error'); ?>
@endif
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/banners/edit-banner/'.$banner['id'])}}" class="form-horizontal form-bordered form-row-stripped" enctype="multipart/form-data" method="post" >    
             
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="banner_name">{{ trans('admin/banner.banner_name') }} <span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="banner_name" <?php if(Input::old('banner_name')) {?>value="{{Input::old('banner_name')}}"<?php } elseif($errors->first('banner_name')) {?> value="{{Input::old('banner_name')}}"<?php } elseif(isset($banner['name'])) {?> value="{{$banner['name']}}"<?php } ?>> 
                {!! $errors->first('banner_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>

            <!-- banner type -->
            <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label" for="banner_type">{{ trans('admin/banner.banner_type') }}<span class="red">*</span></label>
                <div class="col-sm-6 col-lg-5 controls">
                    <select name="banner_type" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/banner.banner_type') }}" value="">
                    <?php
                        $home_banner_image = config('app.banner_type.home_banner_image');
                        $catergory_banner_image = config('app.banner_type.category_banner_image');
                      if(Input::old('banner_type'))
                      {
                        $banner_type = Input::old('banner_type');
                      }
                      elseif(isset($banner['banner_type']) )
                      {
                        $banner_type = $banner['banner_type'];
                      }
                  ?>
                    <option value="{{$home_banner_image}}" <?php if($banner_type == $home_banner_image) echo "selected" ?> >Home Banner Image</option>
                    <option value="{{$catergory_banner_image}}" <?php if($banner_type == $catergory_banner_image) echo "selected" ?> >Category Banner Image</option>
                    </select>
                   
                    {!! $errors->first('banner_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}     
                </div>
              </div>

            <!-- web image -->
            <div class="form-group">
                  <?php
                    $noimage="noimage.png";
                    $no_image=config('app.no_image');
                    $banner_file_name = $banner['file_client_name'];
                   
                    $banner_file_path = config('app.site_banners_path').$banner_file_name;
                    $flag = 'web';
                  ?>
                <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/banner.web_banner_image') }} <span class="red">*</span></label>
                <div class="col-sm-2 col-lg-2 controls">
                  <?php if(!empty($banner_file_name) && (file_exists($banner_file_path)) ) { ?>
                    <img src="{{URL::to($banner_file_path)}}" height="100%" width="100%" id="banner_imageweb">
                    <?php } else{ ?>
                    <img src="{{URL::to($no_image)}}" height="30%" width="30%">
                    <?php } ?>
                </div>
                <div class="col-sm-6 col-lg-7 controls">
                <?php if(!empty($banner_file_name) && (file_exists($banner_file_path)) ) { ?>
                <span id="hideweb">
                    <span class="">{{ trans('admin/banner.selected_banner') }} <strong>{{$banner_file_name}}</strong></span>
                    <div class="btn btn-default" onclick="remove_uploaded('{{$banner_file_name}}','{{$flag}}');">{{ trans('admin/banner.remove') }}</div><br><br>
                </span>
               
                <?php }?>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/banner.select_file') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/banner.change') }}</span>
                                    <input type="file" class="file-input" name="file">
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/banner.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="deleteweb" value="" id="deleteweb">
                     <input type="hidden" id="x" name="x" />
                                    <input type="hidden" id="y" name="y" />
                                    <input type="hidden" id="w" name="w" />
                                    <input type="hidden" id="h" name="h" />
                  <!-- emptying the values filename and diamension file -->
                    <input type="hidden" id="old_fileweb" name="old_file" value="{{$banner_file_name}}">

                    <!-- sending hidden values to controller -->
                    <input type="hidden" name="old_web" value="{{$banner_file_name}}">
                    <span class="help-inline"> {{ trans('admin/banner.upload_size_note') }}</span><br /> 
                     {!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!} 
                </div>
                </div>

                <!-- mobile portrait -->
                 <div class="form-group">
                <?php
                  $banner_file_name1 = $banner['mobile_portrait'];
              
                  $banner_file_path1 = config('app.site_banners_path').$banner_file_name1;
                  $flag = 'portrait';
                ?>
                <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/banner.mobile_banner_portrait') }} <span class="red">*</span></label>
                <div class="col-sm-2 col-lg-2 controls">
                 <?php if(!empty($banner_file_name1) && (file_exists($banner_file_path1)) ) { ?>
                    <img src="{{URL::to($banner_file_path1)}}" height="100%" width="100%" id="banner_imageportrait" >
                    <?php } else{?>
                     <img src="{{URL::to($no_image)}}" height="30%" width="30%" >
                     <?php } ?>
                </div>
                <div class="col-sm-6 col-lg-7 controls">
                  <?php if(!empty($banner_file_name1) && (file_exists($banner_file_path1)) ) { ?>
                    <span id="hideportrait">
                    <span class="">{{ trans('admin/banner.selected_banner') }} <strong>{{$banner_file_name1}}</strong></span>
                    <div class="btn btn-default" onclick="remove_uploaded('{{$banner_file_name1}}','{{$flag}}');">{{ trans('admin/banner.remove') }}</div><br><br>
                </span>
                 <span class="help-inline" style="color:#f00" id="msgportrait"></span>
                <?php }?>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/banner.select_file') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/banner.change') }}</span>
                                    <input type="file" class="file-input" name="mobile_file">
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/banner.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="deleteportrait" value="" id="deleteportrait">
                      <!-- emptying the values filename and diamension file -->
                    <input type="hidden" id="old_fileportrait" name="old_file1" value="{{$banner_file_name1}}">

                    <!-- sending hidden values to controller -->
                     <input type="hidden" name="old_portrait" value="{{$banner_file_name1}}">

                    <span class="help-inline"> {{ trans('admin/banner.upload_size_note') }}</span><br /> 
                     {!! $errors->first('mobile_file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
                </div>

                <!--mobile landscape -->
                <div class="form-group">
                <?php
                    $banner_file_name2 = $banner['mobile_landscape'];
                    $banner_file_path2 = config('app.site_banners_path').$banner_file_name2;
                    $flag = 'landscape'; 
                ?>
                <label class="col-sm-4 col-lg-3 control-label">Mobile Banner Landscape <span class="red">*</span></label>
                <div class="col-sm-2 col-lg-2 controls">
                <?php if(!empty($banner_file_name2) && (file_exists($banner_file_path2)) ){?>
                    <img src="{{URL::to($banner_file_path2)}}" height="100%" width="100%" id="banner_imagelandscape">
                    <?php } else{ ?>
                    <img src="{{URL::to($no_image)}}" height="30%" width="30%">
                    <?php } ?>
                </div>
                <div class="col-sm-6 col-lg-7 controls">
                <?php if(!empty($banner_file_name2) && (file_exists($banner_file_path2)) ) { ?>
                <span id="hidelandscape">
                    <span class="">{{ trans('admin/banner.selected_banner') }} <strong>{{$banner_file_name2}}</strong></span>
                    <div class="btn btn-default" onclick="remove_uploaded('{{$banner_file_name2}}','{{$flag}}');">{{ trans('admin/banner.remove') }}</div><br><br>
                </span>
                 <span class="help-inline" style="color:#f00" id="msglandscape"></span>
                <?php }?>
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/banner.select_file') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/banner.change') }}</span>
                                    <input type="file" class="file-input" name="mobile_landscape_file">
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/banner.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>
                    <input type="hidden" name="deletelandscape" value="" id="deletelandscape">

                      <!-- emptying the values filename and diamension file -->
                    <input type="hidden" id="old_filelandscape" name="old_file2" value="{{$banner_file_name2}}">

                     <!-- sending hidden values to controller -->
                     <input type="hidden" name="old_landscape" value="{{$banner_file_name2}}">
                   
                    <span class="help-inline"></span><br />  
                     {!! $errors->first('mobile_landscape_file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
                </div>

            <div class="form-group">
              <?php 
              if(Input::old('banner_url'))
              {
                $banner_url=Input::old('banner_url');
              }
              elseif($errors->first('banner_url'))
              {
                $banner_url=Input::old('banner_url');
              }
              elseif(isset($banner['banner_url']))
              {
                $banner_url=$banner['banner_url'];
              }
              else
              {
                $banner_url='';
              }
            ?>
                <label class="col-sm-4 col-lg-3 control-label" for="banner_url">{{ trans('admin/banner.banner_link') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <input type="text" name="banner_url" placeholder="Enter URL" class="form-control" value="{{$banner_url}}">
                    <span class="help-inline"> {{ trans('admin/banner.url_preceded') }}</span><br />
                    {!! $errors->first('banner_url', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                </div>
            </div>

            <div class="form-group">
                <?php 
                  if(Input::old('sort_order'))
                  {
                    $order=Input::old('sort_order');
                  }
                  elseif(isset($banner['sort_order']))
                  {
                    $order=$banner['sort_order'];
                  }
                  else
                  {
                    $l=$sort_order;
                  }
                ?>
                <label class="col-sm-4 col-lg-3 control-label" for="sort_order">{{ trans('admin/banner.sort_order') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <select name="sort_order" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/banner.sort_order') }}">
                        @for($i=1;$i<=$sort_order;$i++)
                            <option value="{{$i}}" <?php if($order == $i) echo "selected"?>>{{$i}}</option>
                        @endfor
                    </select>
                    <input type="hidden" name="curval" value="{{$banner['sort_order']}}">
                    {!! $errors->first('sort_order', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group">
              <?php 
              if(Input::old('description'))
              {
                $description=Input::old('description');
              }
              elseif($errors->first('description'))
              {
                $description=Input::old('description');
              }
              elseif(isset($banner['description']))
              {
                $description=$banner['description'];
              }
              else
              {
                $description='';
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="description">{{ trans('admin/banner.description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="description">{{$description}}</textarea>
                  <span class="help-inline"> {{ trans('admin/banner.max_char_allowed') }}</span><br />
                  {!! $errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
              </div>
            </div>
            
          <!-- mobile description -->
            <div class="form-group">
            <?php 
              if(Input::old('mobile_description'))
              {
                $mob_description=Input::old('mobile_description');
              }
              elseif($errors->first('mobile_description'))
              {
                $mob_description=Input::old('mobile_description');
              }
              elseif(isset($banner['mobile_description']))
              {
                $mob_description=$banner['mobile_description'];
              }
              else
              {
                $mob_description='';
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="mobile_description">{{ trans('admin/banner.mobile_description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="mobile_description">{{$mob_description}}</textarea>
                  <span class="help-inline"> {{ trans('admin/banner.max_char_allowed') }}</span><br />
                  {!! $errors->first('mobile_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
              </div>
              </div>
           
              <div class="form-group">
              <?php 
              if(Input::old('status'))
              {
                $status=Input::old('status');
              }
              elseif($errors->first('status'))
              {
                $status=Input::old('status');
              }
              elseif(isset($banner['status']))
              {
                $status=$banner['status'];
              }
              else
              {
                $status='ACTIVE';
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">Status</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>>Active</option>
                  <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>>In-active</option>
                </select>
                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Update Banner">
                    <a class="btn" href="{{ URL::to('cp/banners') }}">{{ trans('admin/banner.cancel') }}</a>
                </div>
            </div>
          </div>
        </form>
    </div>
  </div><!--Register-->
</div>
<script type="text/javascript">
  function remove_uploaded(name,flag)
  { 
    // alert(banner_dimension); 
    var id = "banner_image"+flag;
    var hide = "hide"+flag;
    var del_image = "delete"+flag;
    var old_file = "old_file"+flag;
    // var old_dim_file = "old_dim_file"+flag;
    // alert(old_dim_file);

    $('#'+id).hide();
    $('#'+hide).hide();
    $('#'+del_image).val(1);

     $('#'+id).attr('src','');
     $('#'+old_file).val('');
     // $('#'+old_dim_file).val('');

}
</script>
@stop