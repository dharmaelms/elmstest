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
?>
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
	
  
    
	
   <div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
       
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/lmscoursemanagement/add-batch/')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype="multipart/form-data" files="true">
        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label" for="name">{{trans('admin/lmscourse.course_name')}}</label>
            <div class="col-sm-9 col-lg-10 controls">
              <input type="text" class="form-control" name="course_name" value="{{$course}}" readonly="true">
              </div>
          </div>
        @include('admin.theme.sitesettings.attributeload', ['variant' => 'batch'])
		<input type="hidden" name="program_id" value="<?php echo $id;?>">
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
            <script>
	    	$(document).ready(function(){
                    $('.datepicker').datepicker({
                    format : "dd-mm-yyyy",
                    startDate: '+0d'
                })
				
            })
            </script>
</div>
@stop

