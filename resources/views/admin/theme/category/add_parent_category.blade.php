@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
  <!-- <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
        <!-- <h3 style="color:black" ><i class="icon-file" ></i> Add New Parent Category</h3> -->
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/categorymanagement/add-parent')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype="multipart/form-data" files="true">
          <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name">{{ trans('admin/category.category_name') }} <span class="red">*</span></label>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="category_name" value="{{ Input::old('category_name') }}" >
              <span class="help-inline"> 
                  {{trans('admin/category.category_name_limit')}}
              </span><br/>
              {!! $errors->first('category_name', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              @if( Session::get('category_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('category_exist') !!}</span>
                <?php Session::forget('category_exist'); ?>
              @endif
            </div>
          </div>
          <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="address">{{ trans('admin/category.description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <textarea id="textarea" class="form-control" rows="5" name="category_desc"><?php if(Input::old('category_desc')) { echo Input::old('category_desc'); }  ?></textarea>
                <span class="help-inline"> 
                  {{trans('admin/category.category_description_limit')}}
                </span><br/>
                {!! $errors->first('category_desc', '<span class="help-inline" style="color:#f00">:message</span>') !!}
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
                    $status='';
                  }
              ?>
              <label class="col-sm-4 col-lg-3 control-label" for="item_type">{{ trans('admin/category.status') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="Select {{ trans('admin/category.status') }}">
                  <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>>Active</option>
                  <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>>In-active</option>
                </select>
                {!!$errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
          </div>
         <input type="hidden" class="form-control" name="parent_slug" value="">
         <input type="hidden" class="form-control" name="parent_id" value="">                  
          <div class="form-group last">
              <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                  <input type="submit" class="btn btn-info" value="Save">
                  <a href="{{URL::to('/cp/categorymanagement/categories/')}}" ><button type="button" class="btn">{{ Lang::get('admin/category.cancel') }}</button></a>
              </div>
          </div>
        </form> 
      </div>
    </div>
  </div><!--Register-->
</div>
@stop