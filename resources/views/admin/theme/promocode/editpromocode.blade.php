@section('content')

@if ( Session::get('success') )
    
    <div class="alert alert-success" id="alert-success">
      
      <button class="close" data-dismiss="alert">×</button>
      
       <strong>{{trans('admin/promocode.success')}}</strong>
      {{ Session::get('success') }}
    
    </div>

    <?php Session::forget('success'); ?>

@endif

@if ( Session::get('error'))

    <div class="alert alert-danger">
    
      <button class="close" data-dismiss="alert">×</button>
      
      <strong>{{trans('admin/promocode.error')}}</strong>
      {{ Session::get('error') }}
    
    </div>

    <?php Session::forget('error'); ?>

@endif

<?php

$error_msg = function($value) use ($errors){

  return "<span class='help-inline' style='color:#f00'>".ucfirst($errors->first($value))."</span>";
}

?>
<script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
<div class="row">

  <div class="col-md-12">
    
    <div class="box">
      
      <div class="box-title">
      </div>
      
      <div class="box-content">
        
        <form action="{{URL::to('cp/promocode/edit-promocode/'.$promocode['id'])}}" 
              class="form-horizontal form-bordered form-row-stripped" method="post" >         
            
            <div class="form-group">
                
                <?php 
                  
                  $promotype = (Request::old('promotype')) ? Request::old('promotype') 
                  : (isset($promocode['promotype']) ? $promocode['promotype'] : '');

                  $promocode_value = (Request::old('promocode')) ? Request::old('promocode') 
                  : (isset($promocode['promocode']) ? $promocode['promocode'] : '');

                ?>

            <label class="col-sm-4 col-lg-3 control-label" for="promotype">
                
                {{trans('admin/promocode.promotype')}}
                <span class="red">*</span>
            
            </label>
              
            <div class="col-sm-6 col-lg-5 controls">
                
                <input type="radio" name="promotype" value="manual" 
                       @if($promotype == 'manual') checked @else disabled @endif>
                    
                {{trans('admin/promocode.promotype_manual')}}
                
                <input type="radio" name="promotype" value="auto" 
                       @if($promotype == 'auto') checked @else disabled @endif>
                       
                {{trans('admin/promocode.promotype_auto')}}
                
                <div class="input-group" style="margin-top:8px">
                  
                  <input type="text" readonly class="form-control" name="promocode" value="{{ $promocode_value }}">
                
                </div>
                
                {!! $error_msg('promocode') !!}
              
            </div>   
            
        </div>
        
        <div class="form-group">
                
                <?php 

                  $product_type = (Request::old('product_type') ? Request::old('product_type') :
                   (isset($promocode['program_type']) ? $promocode['program_type'] : 'all'));
                ?>
                
                <label class="col-sm-4 col-lg-3 control-label">
                    
                    {{trans('admin/promocode.program_type')}} 
                    <span class="red">*</span>
                
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                    <input type="radio" name="product_type" value="all" class="radio-inline" <?php echo ($product_type == 'all') ? 'checked' : 'disabled';?>  >
                    {{trans('admin/promocode.program_all')}} 
                
                    <input type="radio" name="product_type" value="content_feed" class="radio-inline" <?php echo ($product_type == 'content_feed') ? 'checked' : 'disabled';?> >
                    {{trans('admin/promocode.program_content_feed')}} 
                
                    <input type="radio" name="product_type" value="course" class="radio-inline" <?php echo ($product_type == 'course') ? 'checked' : 'disabled';?> >
                    {{trans('admin/promocode.program_course')}}
                    
                    <input type="radio" name="product_type" value="package" class="radio-inline" <?php echo ($product_type == 'package') ? 'checked' : 'disabled';?> >
                    {{trans('admin/promocode.program_package')}}
                
                    {!! $error_msg('product_type') !!}
                
                </div>
            
            </div>
            <div class="form-group">
                <?php 

                     $start_date = (Request::old('start_date')) ? 
                                    Request::old('start_date') : (isset($promocode['start_date']) ? 
                                    Timezone::convertFromUTC("@".$promocode['start_date'],Auth::user()->timezone,'d-m-Y') : 
                                    '');
                ?>

                <label for="start_date" class="col-sm-4 col-lg-3 control-label">

                    {{trans('admin/promocode.start_date')}}
                    <span class="red">*</span>

                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                    <input type="text" readonly name="start_date" class="form-control" value="{{ $start_date }}">
                    
                    {!! $error_msg('start_date') !!}
                
                </div>

            </div>

            <div class="form-group">

                <?php 

                    $end_date = (Request::old('end_date')) ? 
                                Request::old('end_date') : (isset($promocode['end_date']) ? 
                                Timezone::convertFromUTC("@".$promocode['end_date'],Auth::user()->timezone,'d-m-Y') : 
                                    '');
                ?>

                <label for="end_date" class="col-sm-4 col-lg-3 control-label">
                    
                    {{trans('admin/promocode.end_date')}}
                    <span class="red">*</span>

                </label>

                <div class="col-sm-6 col-lg-5 controls">

                   <div class="input-group date">
                        <span class="input-group-addon calender-icon">
                            <i class="fa fa-calendar"></i>
                        </span>
                        
                        <input type="text" readonly name="end_date" class="form-control datepicker" value="{{ $end_date }}" style="cursor: pointer">
                    
                    </div>
                    
                    {!! $error_msg('end_date') !!}

                </div>

            </div>

            <div class="form-group">

                <?php

                    $max_redeem_count = (Request::old('max_redeem_count') ? 
                                        Request::old('max_redeem_count') :
                                        (isset($promocode['max_redeem_count']) ? 
                                            $promocode['max_redeem_count'] : '')
                                        );

                ?>

                <label class="col-sm-4 col-lg-3 control-label" for="max_redeem_count">

                 
                   {{trans('admin/promocode.max_redeem')}}
                    <span class="red">*</span>

                </label>
                
                <div class="col-sm-6 col-lg-5 controls">

                    <input type="text" class="form-control" name="max_redeem_count" value="{{ $max_redeem_count }}"> 
                
                    <span class="help-inline">
                    
                      {{trans('admin/promocode.unlimited_reedem')}}
                    
                    </span><br />
                    
                    {!! $error_msg('max_redeem_count') !!}
                
                </div>

            </div>

            <div class="form-group">

                <?php

                    $redeemed_count = (isset($promocode['redeemed_count'])) ?
                                      $promocode['redeemed_count'] : 0 ;
                
                ?>

                <label for="end_date" class="col-sm-4 col-lg-3 control-label">
                    
                    {{ trans('admin/promocode.redeem_count') }}
                
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                    <div class="input-group">
                        
                        <input type="text" readonly name="redeemed_count" class="form-control" value="{{ $redeemed_count }}">
                    
                    </div>
                    
                    {!! $error_msg('redeemed_count') !!}
                
                </div>
            
            </div>
            

            <div class="form-group">

                <?php

                    $discount_type = (Request::old('discount_type') ? 
                                        Request::old('discount_type') :
                                        (isset($promocode['discount_type']) ? 
                                            $promocode['discount_type'] : '')
                                        );

                ?>

                <label class="col-sm-4 col-lg-3 control-label" for="discount_type">

                    {{trans('admin/promocode.discount_type')}}

                    <span class="red">*</span>

                </label>

                <div class="col-sm-6 col-lg-5 controls">
                
                    <input type="radio" name="discount_type" value="percentage" 
                           @if($discount_type == 'percentage') checked @else disabled @endif> 

                        {{trans('admin/promocode.percentage')}}
                
                    <input type="radio" name="discount_type" value="unit" @if($discount_type == 'unit') checked @else disabled @endif> 

                        {{trans('admin/promocode.unit')}}
                
                    {!! $error_msg('discount_type') !!}
                
                </div>
            
            </div>

            <div class="form-group">

                <?php

                    $discount_value = (Request::old('discount_value') ? 
                                        Request::old('discount_value') :
                                        (isset($promocode['discount_value']) ? 
                                            $promocode['discount_value'] : '')
                                        );

                ?>

                <label class="col-sm-4 col-lg-3 control-label" for="discount_value">
                
                    {{trans('admin/promocode.discount_value')}}
                
                    <span class="red">*</span>
                
                </label>

                <div class="col-sm-6 col-lg-5 controls">
                
                    <input type="text" class="form-control" readonly name="discount_value" value="{{ $discount_value }}"> 
                
                    {!! $errors->first('discount_value', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              
                </div>
            
            </div>
            
            <div class="form-group">
                
                <?php 

                  $minimum_order_amount = (Request::old('minimum_order_amount')) ? Request::old('minimum_order_amount') 
                  : (isset($promocode['minimum_order_amount']) ? $promocode['minimum_order_amount'] : '');
                 
                     if($minimum_order_amount == 0)
                    {
                        $minimum_order_amount = '';
                    }
                ?>
                
                <label class="col-sm-4 col-lg-3 control-label" for="minimum_order_amount">
                    
                    {{trans('admin/promocode.minimum_order_amount')}}
                
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                    <input type="text" class="form-control" name="minimum_order_amount" value="{{ $minimum_order_amount }}"> 
                    
                    {!! $error_msg('minimum_order_amount') !!}
                
                </div>
            
            </div>

            <div class="form-group @if($discount_type == 'unit') hide @endif">
                
                <?php 

                  $maximum_discount_amount = (Request::old('maximum_discount_amount')) ? Request::old('maximum_discount_amount') : (isset($promocode['maximum_discount_amount']) ? $promocode['maximum_discount_amount'] : '' );
                
                  if($maximum_discount_amount == 0)
                  {
                        $maximum_discount_amount = '';
                  }
                ?>

                <label class="col-sm-4 col-lg-3 control-label" for="maximum_discount_amount">       

                    {{trans('admin/promocode.maximum_discount_amount')}}
                
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                    <input type="text" class="form-control" name="maximum_discount_amount" value="{{ $maximum_discount_amount }}"> 
                    
                    {!! $error_msg('maximum_discount_amount') !!}
                
                </div>
            
            </div>

            <div class="form-group">
                <?php 

                  $terms_and_conditions = (Request::old('terms_and_conditions')) ? Request::old('terms_and_conditions') : (isset($promocode['terms_and_conditions']) ? $promocode['terms_and_conditions'] : '' );
                
                ?>

                <label class="col-sm-4 col-lg-3 control-label" for="terms_and_conditions">
                  {{trans('admin/promocode.terms_and_conditions')}}
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                        <textarea rows="5" id="terms_and_conditions" name="terms_and_conditions" class="form-control ckeditor">
                          {!! $terms_and_conditions !!}
                        </textarea>
                        
                        {!! $error_msg('terms_and_conditions') !!}
                
                </div>

            </div>

            <div class="form-group">

                <?php 

                  $status = (Request::old('status')) ? Request::old('status') : (isset($promocode['status']) ? $promocode['status'] : '' );
                
                ?>


                <label class="col-sm-4 col-lg-3 control-label" for="status">

                    {{trans('admin/promocode.status')}}

                </label>
              
                <div class="col-sm-6 col-lg-5 controls">
                
                    <select name="status" class="chosen gallery-cat form-control" 
                            data-placeholder="None">
                  
                        <option value="ACTIVE" @if($status == 'ACTIVE') selected @endif>
                    
                            {{trans('admin/promocode.active')}}
                  
                        </option>
                  
                        <option value="IN-ACTIVE" @if($status == 'IN-ACTIVE') selected @endif>
                    
                            {{trans('admin/promocode.inactive')}}
                  
                        </option>
                
                    </select>
                
                    {!! $error_msg('status') !!}
              
                </div>

            </div>

            <div class="form-group last">

                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="{{trans('admin/promocode.update_promocode')}}">
                    <a class="btn" href="{{ URL::to('cp/promocode') }}">
                        {{trans('admin/promocode.cancel')}}
                    </a>
                </div>

            </div>
          </div>
        </form>
    </div>
  </div><!--Register-->
</div>

<script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>

<script type="text/javascript">
    $(document).ready(function(){
        $('.datepicker').datepicker({
            format : "dd-mm-yyyy",
            startDate: '+0d'
        })
    });

    CKEDITOR.replace( 'terms_and_conditions', {
        filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
    });
    CKEDITOR.config.disallowedContent = 'script; *[on*]';
    CKEDITOR.config.height = 150;
    CKEDITOR.replace('reference_cut_off');
    function toggleChevron(e) {
        $(e.target)
            .prev('.panel-heading')
            .find("i.indicator")
            .toggleClass('glyphicon-chevron-down glyphicon-chevron-up');
    }
    //setting default width as 100% for table
    CKEDITOR.on('dialogDefinition', function( ev ) {
        
          var diagName = ev.data.name;
          var diagDefn = ev.data.definition;

          if(diagName === 'table') { //if dialog name equal to table
            var infoTab = diagDefn.getContents('info');
            
            var width = infoTab.get('txtWidth');
            width['default'] = "100%";
            
            
          }
    });
    
</script>
@stop