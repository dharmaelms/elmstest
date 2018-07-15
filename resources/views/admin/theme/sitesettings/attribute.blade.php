@section('content')
   @if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>

  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
   <div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
       
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/manageattribute/add-attribute/')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype="multipart/form-data" files="true">
         
          <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name">{{ trans('admin/attribute.attribute_type') }} <span class="red">*</span></label>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="attribute_type" value="{{Input::old('attribute_type')}}">
              {!! $errors->first('attribute_type', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              @if( Session::get('attributetype_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('attributetype_exist') !!}</span>
                <?php Session::forget('attributetype_exist'); ?>
              @endif
            </div>
          </div>
            
             <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name">{{ trans('admin/attribute.attribute_name') }} <span class="red">*</span></label>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="attribute_name" value="{{Input::old('attribute_name')}}" >
              {!! $errors->first('attribute_name', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              @if( Session::get('attributename_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('attributename_exist') !!}</span>
                <?php Session::forget('attributename_exist'); ?>
              @endif
            </div>
          </div>
            
             <div class="form-group">
            <label class="col-sm-4 col-lg-3 control-label" for="name">{{ trans('admin/attribute.attribute_label') }} <span class="red">*</span></label>
            <div class="col-sm-6 col-lg-5 controls">
              <input type="text" class="form-control" name="attribute_label" value="{{Input::old('attribute_label')}}" >
              {!! $errors->first('attribute_label', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              @if( Session::get('attributelabel_exist') )
                <span class="help-inline" style="color:#f00">{!! Session::get('attributelabel_exist') !!}</span>
                <?php Session::forget('attributelabel_exist'); ?>
              @endif
            </div>
          </div>
            
             <div class="form-group">
              <?php 
                  if(Input::old('visibility'))
                  {
                    $visibility=Input::old('visibility');
                  }
                  else
                  {
                    $visibility='';
                  }
              ?>
            <label class="col-sm-4 col-lg-3 control-label" for="item_type">{{ trans('admin/attribute.attribute_visibility') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="visibility" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/attribute.select_visibility') }}">
                  <option value="1" <?php if($visibility == '1') echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value="0" <?php if($visibility == '0') echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
               
              </div>
                  </div>
                
                
                <div class="form-group">
              <?php 
                  if(Input::old('mandatory'))
                  {
                    $mandatory=Input::old('mandatory');
                  }
                  else
                  {
                    $mandatory='';
                  }
              ?>
                <label class="col-sm-4 col-lg-3 control-label" for="item_type1">{{ trans('admin/attribute.attribute_mandatory') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="mandatory" class="chosen gallery-cat form-control" data-placeholder="Select {{ trans('admin/attribute.mandatory') }}">
                  <option value="1" <?php if($mandatory == '1') echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value="0" <?php if($mandatory == '0') echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
              </div>
              </div>
                
                <div class="form-group">
              <?php 
                  if(Input::old('default'))
                  {
                    $default=Input::old('default');
                  }
                  else
                  {
                    $default='';
                  }
              ?>
                <label class="col-sm-4 col-lg-3 control-label" for="item_type2">{{ trans('admin/attribute.attribute_default') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="default" class="chosen gallery-cat form-control" data-placeholder="Select Default">
                  <option value="1" <?php if($default == '1') echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value="0" <?php if($default == '0') echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
                </div>
              </div>

            @if(config('app.ecommerce') === true)
                <div class="form-group">
              <?php 
                  if(Input::old('ecommerce'))
                  {
                    $ecommerce=Input::old('ecommerce');
                  }
                  else
                  {
                    $ecommerce='';
                  }
              ?>
                <label class="col-sm-4 col-lg-3 control-label" for="item_type2">{{ trans('admin/attribute.ecommerce') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="ecommerce" class="chosen gallery-cat form-control" data-placeholder="Select Default">
                  <option value="1" <?php if($ecommerce == '1') echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value="0" <?php if($ecommerce == '0') echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
                </div>
              </div>
                <!--start-->
                <div class="form-group">
              <?php 
                  if(Input::old('unique'))
                  {
                    $unique=Input::old('unique');
                  }
                  else
                  {
                    $unique='';
                  }
              ?>
                <label class="col-sm-4 col-lg-3 control-label" for="item_type1">{{ trans('admin/attribute.unique') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="unique" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/attribute.data_type') }}">
                  <option value="1" <?php if($unique == '1') echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
                  <option value="0" <?php if($unique == '0') echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
                </select>
              </div>
              </div>
            @endif
                <!--end-->
                
                <div class="form-group">
              <?php 
                  if(Input::old('datatype'))
                  {
                    $datatype=Input::old('datatype');
                  }
                  else
                  {
                    $datatype='';
                  }
              ?>
                <label class="col-sm-4 col-lg-3 control-label" for="item_type2">{{ trans('admin/attribute.data_type') }}</label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="datatype" class="chosen gallery-cat form-control" data-placeholder="Select Default">
                  <option value="text" <?php if($datatype == 'text') echo "selected"?>>{{ trans('admin/attribute.text_box') }}</option>
                  <option value="date" <?php if($datatype == 'date') echo "selected"?>>{{ trans('admin/attribute.date') }}</option>
                  <option value="dropdown" <?php if($datatype == 'dropdown') echo "selected"?>>{{ trans('admin/attribute.drop_down') }}</option>
                </select>
                </div>
              </div>
                
                          
          <div class="form-group last">
              <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                  <input type="submit" class="btn btn-info" value="Add Attribute">
                  <form><input type="button" class="btn" value="{{ trans('admin/attribute.cancel') }}" onclick="history.go(-1);return false;" /></form>
              </div>
          </div>
        </form> 
      </div>
    </div>
  </div>
</div>
@stop	