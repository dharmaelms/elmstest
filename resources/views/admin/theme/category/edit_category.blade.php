@section('content')
<?php use App\Model\Category; ?>
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
  <!-- <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); 
        
  ?>
@endif
<?php
    $start    =  Input::get('start', 0);
    $limit    =  Input::get('limit', 10);
    $filter   =  Input::get('filter','ALL');
    $search   =  Input::get('search','');
    $order_by =  Input::get('order_by','2 desc');
?>
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
        <!-- <h3><i class="icon-file"></i> Edit Category</h3> -->
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/categorymanagement/edit-category')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype="multipart/form-data" files="true">
          <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" >{{ trans('admin/category.name') }}</label>
            <?php if(Input::old('cat_name')) 
            { 
              $cat_name=Input::old('cat_name'); 
            } if(isset($cat_info['category_name'])){ 
              $cat_name=$cat_info['category_name'];
            }?>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="category_name" value="{{ html_entity_decode($cat_name) }}">
              <span class="help-inline"> 
                  {{trans('admin/category.category_name_limit')}}
              </span><br/>
              {!!$errors->first('category_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              @if( Session::get('category_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('category_exist') !!}</span>
                <?php Session::forget('category_exist'); ?>
              @endif
            </div>
          </div>
          <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" >{{ trans('admin/category.description') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <textarea id="textarea" class="form-control" rows="5" name="category_desc"><?php if(Input::old('category_desc')) { echo Input::old('category_desc'); } elseif(isset($cat_info['category_description'])){ ?>{{ html_entity_decode($cat_info['category_description']) }}<?php } ?></textarea>
                <span class="help-inline"> 
                  {{trans('admin/category.category_description_limit')}}
              </span><br/>
                {!!$errors->first('category_desc', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
          </div>
          @if($categories!='')
          <div class="form-group">
           <?php
            if(Input::old('category'))
            {
              $category=Input::old('category');
            }
            elseif(isset($cat_info['parents']))
            {
              $category=$cat_info['parents'];
            }
            else
            {
              $category='';
            }
          ?>
            <label class="col-sm-4 col-lg-3 control-label" >{{ trans('admin/category.category') }}<span class="red">*</span></label>
            <div class="col-sm-6 col-lg-5 controls">
              <select id='catlist' name='category' class="chosen gallery-cat form-control" data-placeholder="Select Category">
              @foreach($categories as $each)
              <?php 
                  $childrencode=Category::getAdminChildrencode($each['slug']);
                  $chilecounter = 0;
                  $totalchildren = count($childrencode);

                  $children = array();
                  if(isset($childrencode[$chilecounter]['category_id']))
                  {
                      while ($chilecounter < $totalchildren) 
                      {           
                          $children[]=$childrencode[$chilecounter]['category_id'];
                          $chilecounter++;            
                      }
                  }
                  $childrencategory=Category::getAdminChildrenForEdit($children);
                  ?>
                  <option value="{{$each['category_id']}}" <?php if($category == $each['category_id']) echo "selected"?>>{{ html_entity_decode(ucwords(strtolower($each['category_name'])))}}</option>
              @endforeach
             
              </select>
               {!! $errors->first('category','<span class="help-inline" style="color:#f00">:message</span>') !!}
          </div>
          </div>
          @else
           <input type="hidden" class="form-control" name="category" value="{{$cat_info['parents']}}">
           @endif
              <input class="form-control" name="cat_code" value="{{ $cat_info['category_id'] }}" type='hidden'>
              <input type="hidden" class="form-control" name="old_slug" value="{{$cat_info['slug']}}">
              <input type="hidden" class="form-control" name="parent_id" value="{{$cat_info['parents']}}">
              <div class="form-group">
                <?php 
                if(Input::old('status'))
                {
                  $status=Input::old('status');
                }
                elseif(isset($cat_info['status']))
                {
                  $status=$cat_info['status'];
                }
                else
                {
                  $status='';
                }
                $chil_count=$rel_count=0;
                if(isset($cat_info['children'])) { $chil_count=count($cat_info['children']); }
                if(isset($cat_info['relations']['assigned_feeds'])) { $chil_count=count($cat_info['relations']['assigned_feeds']); }


              ?>
                <label class="col-sm-4 col-lg-3 control-label" for="status">{{ trans('admin/category.status') }}</label>
                <div class="col-sm-6 col-lg-5 controls">
                  <select name="status" class="chosen gallery-cat form-control" data-placeholder="None" <?php if($chil_count>0 || $rel_count>0 ) echo "disabled";?>>
                    <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>>Active</option>
                    <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>>In-active</option>
                  </select>
                  {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>       
          <div class="form-group last">
              <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                  <input type="submit" class="btn btn-info" value="Update">
                  <!-- <form><input type="button" class="btn" value="Cancel " onclick="history.go(-1);return false;" /></form> -->
                  <?php
                      if(isset($slug) && $slug != ''){
                        ?>
                      <a href="{{URL::to('/cp/categorymanagement/categories/')}}/{{$slug}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}" ><button type="button" class="btn">{{ trans('admin/category.cancel') }}</button></a>
                        <?php
                      }else{
                  ?>
                  <a href="{{URL::to('/cp/categorymanagement/categories/')}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}" ><button type="button" class="btn">{{ trans('admin/category.cancel') }}</button></a>
                <?php  }?>

              </div>
          </div>
        </form> 
      </div>
    </div>
  </div><!--Register-->
</div>
@stop