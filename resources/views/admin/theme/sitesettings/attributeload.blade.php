<?php
use App\Model\ManageAttribute;
$fields=ManageAttribute::getVariants($variant);
if(!empty($fields)) {
    foreach($fields as $field) {
?>


        <div class="form-group">
            <label class="col-sm-3 col-lg-2 control-label" for="name"><?php echo ucfirst($field['attribute_label'])?>
            <?php if($field['mandatory']==1) { ?>
            <span class="red">*</span>
            <?php } ?>
            </label>
               <div class="col-sm-9 col-lg-10 controls">
            <?php if($field['datatype']=='text') {?>
            <input type="text" class="form-control" name="<?php echo $field['attribute_name']?>" value="{{Input::old($field["attribute_name"])}}">
            {!! $errors->first($field["attribute_name"], '<span class="help-inline" style="color:#f00">:message</span>')!!}
            @if( Session::get($field["attribute_name"]) )
                <span class="help-inline" style="color:#f00">{!! Session::get($field["attribute_name"]) !!}</span>
                <?php Session::forget($field["attribute_name"]); ?>
              @endif
            <?php }?>
            <?php if($field['datatype']=='date') {?>
            <div class="input-group date">
            <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
            <input type="text" readonly name="<?php echo $field['attribute_name']?>" id="<?php echo $field['attribute_name']?>" class="form-control datepicker"
            value="{{ (Input::old($field["attribute_name"])) ? Input::old($field["attribute_name"]) : date('d-m-Y') }}" style="cursor: pointer">
            </div>
        {!! $errors->first($field["attribute_name"], '<span class="help-inline" style="color:#f00">:message</span>') !!}
            <?php
            }
                  if($field['datatype']=='dropdown') {
             
                  if(Input::old($field["attribute_name"]))
                  {
                    $option=Input::old($field["attribute_name"]);
                  }
                  else
                  {
                    $option=Input::old($field["attribute_name"]);
                  }
              
            ?>
        <select name="<?php echo $field['attribute_name']?>" class="chosen gallery-cat form-control" data-placeholder="Select Default">
            <option value="Yes"  <?php if($option == 'Yes') echo "selected"?>>{{ trans('admin/attribute.yes') }}</option>
            <option value="No"  <?php if($option == 'No') echo "selected"?>>{{ trans('admin/attribute.no') }}</option>
        </select>
            <?php }?>
           
            </div>
          </div>
        <?php }}?>
 
        
