@section('content')
@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
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
        <form action="{{URL::to('cp/banners/add-banner')}}" class="form-horizontal form-bordered form-row-stripped" enctype="multipart/form-data" method="post" >         
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/banner.banner_name') }} <span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="banner_name" value="{{ Input::old('banner_name') }}"> 
                {!! $errors->first('banner_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>

              <!-- banner type -->
              <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label" for="sort_order">{{ trans('admin/banner.banner_type') }}<span class="red">*</span></label>
                <div class="col-sm-6 col-lg-5 controls">
                    <select name="banner_type" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/banner.banner_type') }}">
                    <?php
                        $home_banner_image = config('app.banner_type.home_banner_image');
                        $catergory_banner_image = config('app.banner_type.category_banner_image');
                    ?>
                            <option value="{{$home_banner_image}}" >{{ trans('admin/banner.banner_home_image') }}</option>
                            <option value="{{$catergory_banner_image}}">{{ trans('admin/banner.banner_category_image') }}</option>
                    </select>
                    {!! $errors->first('banner_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                </div>
              </div>

            <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/banner.web_banner_image') }}<span class="red">*</span></label>
                <div class="col-sm-6 col-lg-5 controls">
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/banner.select_file') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/banner.change') }}</span>
                                    <input type="file" class="file-input" name="file"/>
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/banner.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>
                    @if(!config('app.ecommerce'))
                      <span class="help-inline">{{ trans('admin/banner.ecommerce_banner_note') }}</span><br />
                    @else
                      <span class="help-inline">{{ trans('admin/banner.upload_size_note') }} </span><br />
                    @endif
                    {!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <!-- mobile  portrait-->
             <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/banner.mobile_banner_portrait') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/banner.select_file') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/banner.change') }}</span>
                                    <input type="file" class="file-input" name="mobile_file"/>
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/banner.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>

                    @if(!config('app.ecommerce'))
                      <span class="help-inline">{{ trans('admin/banner.ecommerce_banner_note') }}</span><br />
                    @else
                      <span class="help-inline">{{ trans('admin/banner.upload_size_note') }} </span><br />
                    @endif
                    {!! $errors->first('mobile_file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <!-- mobile landscape -->
             <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/banner.mobile_banner_landscape') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <div class="fileupload fileupload-new" data-provides="fileupload">
                        <div class="input-group">
                            <div class="input-group-btn">
                                <a class="btn bun-default btn-file">
                                    <span class="fileupload-new">{{ trans('admin/banner.select_file') }}</span>
                                    <span class="fileupload-exists">{{ trans('admin/banner.change') }}</span>
                                    <input type="file" class="file-input" name="mobile_landscape_file"/>
                                </a>
                                <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/banner.remove') }}</a>
                            </div>
                            <div class="form-control uneditable-input">
                                <i class="fa fa-file fileupload-exists"></i> 
                                <span class="fileupload-preview"></span>
                            </div>
                        </div>
                    </div>
                    <span class="help-inline"></span><br />
                    {!! $errors->first('mobile_file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label" for="banner_url">{{ trans('admin/banner.banner_link') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <input type="text" name="banner_url" placeholder="Enter URL" class="form-control" value="{{Input::old('banner_url')}}">
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
                  else
                  {
                    $order=$sort_order;
                  }
                ?>
                <label class="col-sm-4 col-lg-3 control-label" for="sort_order">{{ trans('admin/banner.sort_order') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <select name="sort_order" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/banner.sort_order') }}">
                        @for($i=1;$i<=$sort_order;$i++)
                            <option value="{{$i}}" <?php if($order == $i) echo "selected"?>>{{$i}}</option>
                        @endfor
                    </select>
                    <input type="hidden" name="curval" value="{{$sort_order}}">
                    {!! $errors->first('sort_order', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                </div>
            </div>

            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="description">{{ trans('admin/banner.description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="description">{{Input::old('description')}}</textarea>
                  <span class="help-inline"> {{ trans('admin/banner.max_char_allowed') }}</span><br />
                  {!! $errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
              </div>
            </div>
            <!-- mobile description -->
             <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="description">{{ trans('admin/banner.mobile_description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="mobile_description">{{Input::old('mobile_description')}}</textarea>
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
                    <input type="submit" class="btn btn-info" value="Add Banner">
                    <a class="btn" href="{{ URL::to('cp/banners') }}">{{ trans('admin/banner.cancel') }}</a>
                </div>
            </div>
          </div>
        </form>
    </div>
  </div><!--Register-->
</div>
<script type="text/javascript">
  $(document).ready(function(){
    $('#alert-success').delay(5000).fadeOut();
  })
</script>
@stop