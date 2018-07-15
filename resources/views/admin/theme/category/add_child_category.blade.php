@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
  <!-- <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php 
    Session::forget('success'); 
  ?>
@endif
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/categorymanagement/add-children')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype="multipart/form-data" files="true">
          <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name">{{ trans('admin/category.parent_category_name') }}</label>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="parent_name" value="{{ html_entity_decode($cat_info['category_name']) }}" disabled="disabled">
            </div>
          </div>
          <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name">{{ trans('admin/category.sub_category_name') }}<span class="red">*</span></label>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="sub_category_name" value="{{ Input::old('sub_category_name') }}"> 
              <span class="help-inline"> 
                  {{trans('admin/category.category_name_limit')}}
              </span><br/>
              {!! $errors->first('sub_category_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
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
              <input type="hidden" class="form-control" name="parent_slug" value="{{$cat_info['slug']}}">
              <input type="hidden" class="form-control" name="parent_id" value="{{$cat_info['category_id']}}">                 
          <div class="form-group last">
              <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                  <input type="submit" class="btn btn-info" value="Save">
                   <?php
                      if(isset($start_serv)){
                          $start = $start_serv;
                      }else{
                          $start = 0;
                      }
                      if(isset($length_page_serv)){
                          $limit =  $length_page_serv;
                      }else{
                          $limit = 10;
                      }
                      if(isset($filter)){
                          $filter_ser = $filter;
                      }else{
                          $filter_ser = "ACTIVE";
                      }
                  ?>
                  <a href="{{URL::to('/cp/categorymanagement/categories/')}}?start_serv={{$start}}&length_page_serv={{$limit}}&filter={{$filter_ser}}" ><button type="button" class="btn">{{ trans('admin/category.cancel') }}</button></a>

                  <!-- <form><input type="button" class="btn" value="Cancel " onclick="history.go(-1);return false;" /></form> -->
              </div>
          </div>
        </form> 
      </div>
    </div>
  </div><!--Register-->
</div>
@stop