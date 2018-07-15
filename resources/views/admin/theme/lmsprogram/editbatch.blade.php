@section('content')
<script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
     
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/lmscoursemanagement/edit-batch/'.$id.'/'.$pid)}}" class="form-horizontal form-bordered form-row-stripped" method="post" >  
        <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name">{{trans('admin/lmscourse.course_name')}}</label>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="course_name" value="{{$course}}" readonly="true">
              </div>
          </div>
<?php
//.$attribute['attribute_id']
use App\Model\ManageAttribute;
$variant='batch';
$fields=ManageAttribute::getVariants($variant);
if(!empty($fields)) {
    foreach($fields as $field) {
?>


        <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name"><?php echo ucfirst($field['attribute_label'])?>
            <?php if($field['mandatory']==1) { ?>
            <span class="red">*</span>
            <?php } ?>
            </label>
               <div class="col-sm-6 col-lg-5 controls">
            <?php if($field['datatype']=='text') {?>
            <input type="text" class="form-control" name="<?php echo $field['attribute_name']?>"
            <?php if(Input::old($field["attribute_name"]))
            {?>value="{{Input::old($field["attribute_name"])}}"<?php
            } elseif($errors->first($field["attribute_name"]))
            {?> value="{{Input::old($field["attribute_name"])}}"<?php }
            elseif(isset($batch[$field["attribute_name"]])) {?>
            value="{{$batch[$field["attribute_name"]]}}"<?php } ?>>
            {!! $errors->first($field["attribute_name"], '<span class="help-inline" style="color:#f00">:message</span>')!!}
			 @if( Session::get($field["attribute_name"]) )
                <span class="help-inline" style="color:#f00">{!! Session::get($field["attribute_name"]) !!}</span>
                <?php Session::forget($field["attribute_name"]); ?>
              @endif
            <?php }?>
            <?php if($field['datatype']=='date') {?>
            <div class="input-group date">
            <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
            <input type="text" readonly name="<?php echo $field['attribute_name']?>"
            id="<?php echo $field['attribute_name']?>" class="form-control datepicker"
            value="{{ $batch[$field["attribute_name"]] }}" style="cursor: pointer">
            </div>
            {!! $errors->first($field["attribute_name"], '<span class="help-inline" style="color:#f00">:message</span>') !!}
            <?php
            }
                  if($field['datatype']=='dropdown') {
             
                  if(Input::old($field["attribute_name"]))
                  {
                    $option=Input::old($field["attribute_name"]);
                  }
                  elseif(isset($batch[$field["attribute_name"]]))
                  {
                   $option=$batch[$field["attribute_name"]];
                  }
                  else
                  {
                    $option=Input::old($field["attribute_name"]);
                  }
              
            ?>
        <select name="<?php echo $field['attribute_name']?>" class="chosen gallery-cat form-control" data-placeholder="Select Default">
            <option value="Yes"  <?php if($option == 'Yes') echo "selected"?>>{{trans('admin/lmscourse.yes')}}</option>
            <option value="No"  <?php if($option == 'No') echo "selected"?>>{{trans('admin/lmscourse.no')}}</option>
        </select>
            <?php }?>
           
            </div>
          </div>
        <?php }}?>
		<!--start-->
		 <div class="form-group">
                <?php 
                  if(Input::old('sort_order'))
                  {
                    $order=Input::old('sort_order');
                  }
                  elseif(isset($batch['sort_order']))
                  {
                    $order=$batch['sort_order'];
                  }
                  else
                  {
                    $order=$sort_order;
                  }
                ?>
                <label class="col-sm-4 col-lg-3 control-label" for="sort_order">{{trans('admin/lmscourse.sort_order')}}</label>
                <div class="col-sm-6 col-lg-5 controls">
                    <select name="sort_order" class="chosen gallery-cat form-control" data-placeholder="{{trans('admin/lmscourse.sort_order')}}">
                        @for($i=1;$i<=$sort_order;$i++)
                            <option value="{{$i}}" <?php if($order == $i) echo "selected"?>>{{$i}}</option>
                        @endfor
                    </select>
                    <input type="hidden" name="curval" value="{{$batch['sort_order']}}">
                    {!! $errors->first('sort_order', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
		<!--end-->
        <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Update">
                    <!--<a class="btn" href="{{ URL::to('cp/lmscoursemanagement/manage-batch?pid='.$pid.'') }}">Cancel</a>-->
					<a class="btn" href="{{ URL::to('cp/lmscoursemanagement/') }}">{{trans('admin/lmscourse.cancel')}}</a>
                </div>
            </div>
</div>
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
@stop      
