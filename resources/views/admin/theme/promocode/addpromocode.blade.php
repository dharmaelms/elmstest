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
        
        <form action="{{URL::to('cp/promocode/add-promocode')}}" 
              class="form-horizontal form-bordered form-row-stripped" 
              method="post" >         
          <div class="form-group">
            
            <?php 
              $promotype = (Request::old('promotype')) ? Request::old('promotype') : 'manual';
            ?>

            <label class="col-sm-4 col-lg-3 control-label" for="promotype">
              
              {{trans('admin/promocode.promotype')}}
              <span class="red">*</span>
            
            </label>

            <div class="col-sm-6 col-lg-5 controls">
              
              <input type="radio" class="promotype" name="promotype" value="manual" 
                     @if($promotype == 'manual') checked @endif > 
                
                {{trans('admin/promocode.promotype_manual')}}

              <input type="radio" class="promotype" name="promotype" value="auto" 
                     @if($promotype == 'auto') checked @endif > 
                
                {{trans('admin/promocode.promotype_auto')}}

                <div class="input-group" style="margin-top:8px">
                  
                  <input type="text" id="promocode" class="form-control" name="promocode" maxlength="10"
                         @if(Request::old('promotype') == 'auto') readonly @endif  
                         value="{{ Request::old('promocode') }}"> 
                    
                      <span class="input-group-addon @if(Request::old('promotype') != 'auto') hide @endif"      id="generate">
                        
                        <a class="generate" style="cursor: pointer;">
                          <i class="fa fa-refresh"></i>
                        </a>
                      
                      </span>
                
                </div>
                
                <span class="help-inline"> 

                  {{trans('admin/promocode.promocode_limit')}}
                
                </span><br />

                {!! $error_msg('promocode') !!}

              </div>

            </div>

            <div class="form-group">
                
                <?php 
                  $product_type = Request::old('product_type') ? Request::old('product_type') : 'all';
                ?>

                <label class="col-sm-4 col-lg-3 control-label" for="product_type">
                  {{trans('admin/promocode.program_type')}} 
                  <span class="red">*</span>
                </label>

                <div class="col-sm-6 col-lg-5 controls">

                  <input type="radio" name="product_type" value="all" class="radio-inline" <?php if($product_type == 'all'){ ?> checked <?php } ?> > 
                    {{trans('admin/promocode.program_all')}}

                  <input type="radio" name="product_type" value="content_feed" class="radio-inline" <?php if($product_type == 'content_feed'){ ?> checked <?php } ?>> {{trans('admin/promocode.program_content_feed')}}
                
                  <input type="radio" name="product_type" value="course" class="radio-inline" <?php if($product_type == 'course'){ ?> checked <?php } ?>>
                  {{trans('admin/promocode.program_course')}}
                
                  <input type="radio" name="product_type" value="package" class="radio-inline" <?php if($product_type == 'package'){ ?> checked <?php } ?>>
                  {{trans('admin/promocode.program_package')}}
                
                  {!! $error_msg('product_type') !!}
                
                </div>

            </div>

            <div class="form-group">

                <label for="start_date" class="col-sm-4 col-lg-3 control-label">
                  
                  {{trans('admin/promocode.start_date')}}
                  <span class="red">*</span>
                
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                  
                  <?php

                    $start_date = (Request::old('start_date')) ? Request::old('start_date') : date('d-m-Y');

                  ?>

                  <div class="input-group date">
                    
                    <span class="input-group-addon calender-icon">
                      <i class="fa fa-calendar"></i>
                    </span>

                    <input type="text" name="start_date" class="form-control datepicker" readonly        value="{{$start_date}}" style="cursor: pointer;">
                  
                  </div>

                    {!! $error_msg('start_date') !!}

                </div>

            </div>

            <div class="form-group">

            <?php

              $end_date = (Request::old('end_date')) ?
                           Request::old('end_date') : 
                           date('d-m-Y',strtotime('+1 years', time()));

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
                      
                  <input type="text" readonly name="end_date" class="form-control datepicker" 
                         value="{{ $end_date }}" style="cursor: pointer">
                </div>
                    
                {!! $error_msg('end_date') !!}

              </div>

            </div>

            <div class="form-group">

                <label class="col-sm-4 col-lg-3 control-label" for="max_redeem_count">
                
                 
                  {{trans('admin/promocode.max_redeem')}}
                  <span class="red">*</span>
                
                </label>

                <div class="col-sm-6 col-lg-5 controls">
                    
                    <input type="text" class="form-control" name="max_redeem_count" value="{{ Request::old('max_redeem_count') }}"> 
                    
                    <span class="help-inline">
                    
                      {{trans('admin/promocode.unlimited_reedem')}}
                    
                    </span><br />
                    
                    {!! $error_msg('max_redeem_count') !!}
                 
                </div>
            </div>

            <div class="form-group">
                <?php 

                  $discount_type  = (Request::old('discount_type')) ?
                                    Request::old('discount_type') : 'percentage';
                
                ?>
                
                <label class="col-sm-4 col-lg-3 control-label" for="discount_type">
                
                  {{trans('admin/promocode.discount_type')}}

                  <span class="red">*</span>

                </label>
                
                <div class="col-sm-6 col-lg-5 controls">

                  <input type="radio" name="discount_type" id="discount_type" class="discount_type"       value="percentage" @if($discount_type == 'percentage') checked @endif > {{trans('admin/promocode.percentage')}}
                  
                  <input type="radio" name="discount_type" id="discount_type" class="discount_type "       value="unit" @if($discount_type == 'unit') checked @endif> 
                  {{trans('admin/promocode.unit')}}

                  {!! $error_msg('discount_type') !!}

                </div>

            </div>

            <div class="form-group">

              <label class="col-sm-4 col-lg-3 control-label" for="discount_value">
                
                {{trans('admin/promocode.discount_value')}}
                
                <span class="red">*</span>
              
              </label>
              
              <div class="col-sm-6 col-lg-5 controls">
              
                <input type="text" class="form-control" name="discount_value" value="{{ Request::old('discount_value') }}"> 
                
                {!! $error_msg('discount_value') !!}
              
              </div>

            </div>

            <div class="form-group">
                
                <label class="col-sm-4 col-lg-3 control-label" for="minimum_order_amount">
                  {{trans('admin/promocode.minimum_order_amount')}}
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                    <input type="text" class="form-control" name="minimum_order_amount" value="{{ Request::old('minimum_order_amount') }}"> 
                    
                    {!! $error_msg('minimum_order_amount') !!}
                
                </div>
            </div>

            <div class="form-group @if($discount_type == 'unit') hide @endif" id="div_max_discount">
                
                <label class="col-sm-4 col-lg-3 control-label" for="maximum_discount_amount">
                  {{trans('admin/promocode.maximum_discount_amount')}}
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                    <input type="text" class="form-control" name="maximum_discount_amount" value="{{ Request::old('maximum_discount_amount') }}"> 
                    
                    {!! $error_msg('maximum_discount_amount') !!}
                
                </div>

            </div>

            <div class="form-group">
                
                <label class="col-sm-4 col-lg-3 control-label" for="terms_and_conditions">
                  {{trans('admin/promocode.terms_and_conditions')}}
                </label>
                
                <div class="col-sm-6 col-lg-5 controls">
                    
                        <textarea rows="5" id="terms_and_conditions" name="terms_and_conditions" class="form-control ckeditor">
                          {!! Request::old('terms_and_conditions') !!}
                        </textarea>
                        
                        {!! $error_msg('terms_and_conditions') !!}
                
                </div>

            </div>

            <div class="form-group">
              
              <?php 

                $status = (Request::old('status')) ? Request::old('status') : 'ACTIVE';
              
              ?>

              <label class="col-sm-4 col-lg-3 control-label" for="status">
                
                {{trans('admin/promocode.status')}}

              </label>

              <div class="col-sm-6 col-lg-5 controls">
                
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="None">
                  
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
                    
                    <input type="submit" class="btn btn-info" value="{{trans('admin/promocode.add_promocode')}}">
                    
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
  
  $('input[name=discount_type]:radio').click(
        function(){
            if('unit' == $('#discount_type:checked').val())
            {
              $('#div_max_discount').addClass('hide');
            } 
            else
            {
              $('#div_max_discount').removeClass('hide');
            }  
        }
    );

    var promocode = '{{$promocode}}';
    $(document).ready(function(){
        $('.datepicker').datepicker({
            format : "dd-mm-yyyy",
            startDate: '+0d'
        })
    });
    $('.promotype').click(function(e){
      var type = $('input[name="promotype"]:checked').val();
      if(type == 'auto')
      {
        document.getElementById("promocode").readOnly = true;
        $('#generate').removeClass('hide');
        $('input[name="promocode"]').val(promocode);
      }
      else
      {
        document.getElementById("promocode").readOnly = false;
        $('#generate').addClass('hide');
        $('input[name="promocode"]').val('');
      }
    });
    $('.generate').click(function(e){
        e.preventDefault();
        $.ajax({
            type: 'GET',
            url: "{{ url('cp/promocode/generate-promocode') }}"
        })
        .done(function(response) {
            $('input[name="promocode"]').val(response.promocode);
            window.promocode = response.promocode;
        })
        .fail(function(response) {
            alert( "Error while generating promocode. Please try again" );
        });
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
 

</script>
@stop