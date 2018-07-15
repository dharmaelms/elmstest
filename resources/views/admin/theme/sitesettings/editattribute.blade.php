@section('content')
<?php 
      //$start    =  Input::get('start', 0);
      //$limit    =  Input::get('limit', 10);
      //$filter   =  Input::get('filter','all');
      $search   =  Input::get('search','');
      $order_by =  Input::get('order_by','4 desc');
      
     

?>
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
     
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/manageattribute/edit-attribute/'.$attribute['attribute_id'])}}" class="form-horizontal form-bordered form-row-stripped" method="post" >         
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="">{{ trans('admin/attribute.attribute_type') }} <span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <?php if($attribute['attribute_id'] < 4) { ?>
                <input type="text"  readonly="true" class="form-control" name="attribute_type" <?php if(Input::old('attribute_type')) {?>value="{{Input::old('attribute_type')}}"<?php } elseif($errors->first('attribute_type')) {?> value="{{Input::old('attribute_type')}}"<?php } elseif(isset($attribute['attribute_type'])) {?> value="{{$attribute['attribute_type']}}"<?php } ?>> 
                <?php } else {?>
                <input type="text" class="form-control" name="attribute_type" <?php if(Input::old('attribute_type')) {?>value="{{Input::old('attribute_type')}}"<?php } elseif($errors->first('attribute_type')) {?> value="{{Input::old('attribute_type')}}"<?php } elseif(isset($attribute['attribute_type'])) {?> value="{{$attribute['attribute_type']}}"<?php } ?>> 
                <?php }?>
                {!! $errors->first('attribute_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group"> 
              <label class="col-sm-4 col-lg-3 control-label" for="">{{ trans('admin/attribute.attribute_name') }}<span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
              <?php if($attribute['attribute_id'] < 4) { ?>
                <input type="text" readonly="true" class="form-control" name="attribute_name" <?php if(Input::old('attribute_name')) {?>value="{{Input::old('attribute_name')}}"<?php } elseif($errors->first('attribute_name')) {?> value="{{Input::old('attribute_name')}}"<?php } elseif(isset($attribute['attribute_name'])) {?> value="{{$attribute['attribute_name']}}"<?php } ?>> 
                <?php } else {?>
                <input type="text" class="form-control" name="attribute_name" <?php if(Input::old('attribute_name')) {?>value="{{Input::old('attribute_name')}}"<?php } elseif($errors->first('attribute_name')) {?> value="{{Input::old('attribute_name')}}"<?php } elseif(isset($attribute['attribute_name'])) {?> value="{{$attribute['attribute_name']}}"<?php } ?>> 
                 <?php }?>
                {!! $errors->first('attribute_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                @if( Session::get('attributename_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('attributename_exist') !!}</span>
                <?php Session::forget('attributename_exist'); ?>
              @endif
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="">{{ trans('admin/attribute.attribute_label') }} <span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="attribute_label" <?php if(Input::old('attribute_label')) {?>value="{{Input::old('attribute_label')}}"<?php } elseif($errors->first('attribute_label')) {?> value="{{Input::old('attribute_label')}}"<?php } elseif(isset($attribute['attribute_label'])) {?> value="{{$attribute['attribute_label']}}"<?php } ?>> 
                {!! $errors->first('attribute_label', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                <!--@if ( Session::get('email_exist') )
                  <span class="help-inline" style="color:#f00">{{ Session::get('email_exist') }}</span>
                  <?php Session::forget('email_exist'); ?>
                @endif-->
              </div>
            </div>  
            
            <!--visibility start-->
            <div class="form-group">
              <?php 
              if(Input::old('visibility'))
              {
                $visibility=Input::old('visibility');
              }
              elseif(isset($attribute['visibility']))
              {
                $visibility=$attribute['visibility'];
              }
              else
              {
                $visibility=1;
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">{{ trans('admin/attribute.attribute_visibility') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="visibility" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value=1 <?php if($visibility == 1) echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value=0 <?php if($visibility == 0) echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
                <!--{!! $errors->first('visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}-->
              </div>
            </div>
            <!--visibility end-->
            
            <!--mandatory start-->
            <div class="form-group">
              <?php 
              if(Input::old('mandatory'))
              {
                $mandatory=Input::old('mandatory');
              }
              elseif(isset($attribute['mandatory']))
              {
                $mandatory=$attribute['mandatory'];
              }
              else
              {
                $mandatory=1;
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">{{ trans('admin/attribute.attribute_mandatory') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="mandatory" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value=1 <?php if($mandatory == 1) echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value=0 <?php if($mandatory == 0) echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
               <!-- {!! $errors->first('mandatory', '<span class="help-inline" style="color:#f00">:message</span>') !!}-->
              </div>
            </div>
            <!--mandatory end-->
            
            <!--default start-->
             <div class="form-group">
              <?php 
              if(Input::old('default'))
              {
                $default=Input::old('default');
              }
              elseif(isset($attribute['default']))
              {
                $default=$attribute['default'];
              }
              else
              {
                $default=1;
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">{{ trans('admin/attribute.attribute_default') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="default" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value=1 <?php if($default == 1) echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value=0 <?php if($default == 0) echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
                
              </div>
            </div>
            <!--default end-->
            
             <!--ecommerce start-->
             <div class="form-group">
              <?php 
              if(Input::old('ecommerce'))
              {
                $ecommerce=Input::old('ecommerce');
              }
              elseif(isset($attribute['ecommerce']))
              {
                $ecommerce=$attribute['ecommerce'];
              }
              else
              {
                $ecommerce=1;
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">{{ trans('admin/attribute.ecommerce') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="ecommerce" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value=1 <?php if($ecommerce == 1) echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value=0 <?php if($ecommerce == 0) echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
                
              </div>
            </div>
            <!--ecommerce end-->
            
            <!--ecommerce start-->
             <div class="form-group">
              <?php 
              if(Input::old('unique'))
              {
                $unique=Input::old('unique');
              }
              elseif(isset($attribute['unique']))
              {
                $unique=$attribute['unique'];
              }
              else
              {
                $unique=1;
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">{{ trans('admin/attribute.unique') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="unique" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value=1 <?php if($unique == 1) echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value=0 <?php if($unique == 0) echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
                
              </div>
            </div>
            <!--ecommerce end-->
            
             <!--datatype start-->
              <div class="form-group">
              <?php 
              if(Input::old('datatype'))
              {
                $datatype=Input::old('datatype');
              }
              elseif(isset($attribute['datatype']))
              {
                $datatype=$attribute['datatype'];
              }
              else
              {
                $datatype='text';
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status">{{ trans('admin/attribute.data_type') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="datatype" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value="text" <?php if($datatype == 'text') echo "selected"?>>{{ trans('admin/attribute.text_box') }}</option>
                  <option value="date" <?php if($datatype == 'date') echo "selected"?>>{{ trans('admin/attribute.date') }}</option>
                  <option value="dropdown" <?php if($datatype == 'dropdown') echo "selected"?>>{{ trans('admin/attribute.drop_down') }}</option>
                </select>
                </div>
            </div>
            <!--datatype end-->
            
            
            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Update Attribute">
                    <a class="btn" href="{{ URL::to('cp/manageattribute') }}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}">{{ trans('admin/attribute.cancel') }}</a>
                </div>
            </div>
          </div>
        </form>
    </div>
  </div>
</div>
@stop