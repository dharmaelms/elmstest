@section('content')
@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
      <button class="close" data-dismiss="alert">×</button>
      <strong>{{ trans('admin/country.success') }}</strong>
      {{ Session::get('success') }}
    </div>
<?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
      <button class="close" data-dismiss="alert">×</button>
      <strong>{{ trans('admin/country.error') }}</strong>
      {{ Session::get('error') }}
    </div>
<?php Session::forget('error'); ?>
@endif
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
            </div>
            @if(isset($details) && !empty($details))
            <div class="box-content">
                <form action="{{URL::to('cp/country/edit-country')}}" class="form-horizontal form-bordered form-row-stripped"  method="post" >         
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.edit_country_name_label') }} <span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="name" <?php if(Input::old('name')) {?>value="{{Input::old('name')}}"<?php } elseif($errors->first('name')) { ?> value="{{Input::old('name')}}"<?php } elseif(isset($details['name'])) { ?> value="{{$details['name']}}"<?php } ?> disabled> 
                                {!! $errors->first('name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.edit_currency_code_label') }} <span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="currency_code" <?php if(Input::old('currency_code')) {?>value="{{Input::old('currency_code')}}"<?php } elseif($errors->first('currency_code')) { ?> value="{{Input::old('currency_code')}}"<?php } elseif(isset($details['currency_code'])) { ?> value="{{$details['currency_code']}}"<?php } ?>> 
                                {!! $errors->first('currency_code', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.edit_currency_name_label') }} <span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="currency_name" <?php if(Input::old('currency_name')) {?>value="{{Input::old('currency_name')}}"<?php } elseif($errors->first('currency_name')) { ?> value="{{Input::old('currency_name')}}"<?php } elseif(isset($details['currency_name'])) { ?> value="{{$details['currency_name']}}"<?php } ?>> 
                                {!! $errors->first('currency_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.edit_currency_symbol_label') }} <span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="currency_symbol" <?php if(Input::old('currency_symbol')) {?>value="{{Input::old('currency_symbol')}}"<?php } elseif($errors->first('currency_symbol')) { ?> value="{{Input::old('currency_symbol')}}"<?php } elseif(isset($details['currency_symbol'])) { ?> value="{{$details['currency_symbol']}}"<?php } ?>> 
                                {!! $errors->first('currency_symbol', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.edit_iso_code_two_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="iso_code_two"  <?php if(Input::old('iso_code_two')) { ?>value="{{Input::old('iso_code_two')}}"<?php } elseif($errors->first('iso_code_two')) { ?> value="{{Input::old('iso_code_two')}}"<?php } elseif(isset($details['country_code'])) { ?> value="{{$details['country_code']}}"<?php } ?> disabled> 
                                {!! $errors->first('iso_code_two', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.edit_iso_code_three_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="iso_code_three"  <?php if(Input::old('iso_code_three')) { ?>value="{{Input::old('iso_code_three')}}"<?php } elseif($errors->first('iso_code_three')) { ?> value="{{Input::old('iso_code_three')}}"<?php } elseif(isset($details['iso3'])) { ?> value="{{$details['iso3']}}"<?php } ?>> 
                                <input type="hidden" name="iso_code_three" value="{{ $details['iso3'] }}">
                                {!! $errors->first('iso_code_three', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.edit_status_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="radio"  name="status" value="ACTIVE" <?php if(Input::old('status') == 'ACTIVE') { ?> checked <?php } elseif($details['status'] == "ACTIVE") { ?> checked <?php } ?>> {{ trans('admin/country.edit_active_status_label') }} <br>
                                <input type="radio"  name="status" value="INACTIVE" id="inactive_status" <?php if(Input::old('status') == 'INACTIVE') { ?> checked <?php } elseif($details['status'] == "INACTIVE") { ?> checked <?php } ?>> {{ trans('admin/country.edit_inactive_status_label') }}<br>
                                <span>{{ trans('admin/country.note_enables_currency_for_setting_pricing_options') }}</span>
                                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname"></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="checkbox" name="default" value="YES" id="default" <?php if(Input::old('default') == 'YES') { ?> checked <?php } elseif($details['default'] == "YES"){ ?> checked <?php }  ?>> {{ trans('admin/country.edit_default_label') }}<br>
                                <span>{{ trans('admin/country.note_currency_for_all_endusers') }}</span>
                                {!! $errors->first('default', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/country.payment_options') }} <span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <?php
                                $payment_options_arry = array();
                                if(Input::old('payment_option'))
                                {
                                    $payment_options_arry = Input::old('payment_option');
                                }
                                elseif(isset($details['payment_options']) && is_array($details['payment_options']))
                                {
                                    $payment_options_arry = $details['payment_options'];
                                } 
                                ?>
                                @foreach($payment_options as $key => $value)
                                <input type="checkbox" name="payment_option[]" value="{{$key}}" <?php if(in_array($key,$payment_options_arry)){ echo "checked"; } ?>> {{ $value['payment_name'] }}<br>
                                @endforeach
                                {!! $errors->first('payment_option', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group last">
                        <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                            <input type="submit" class="btn btn-info" value="Save">
                            <a class="btn" href="{{ URL::to('cp/country') }}">{{ trans('admin/country.cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
            @endif
        </div><!--Register-->
    </div>
</div>
<script type="text/javascript">
  $(document).ready(function(){
    if(document.getElementById('default').checked) {
         document.getElementById("inactive_status").disabled = true;
    }
    $('#alert-success').delay(5000).fadeOut();
  });
   $('#default').click(function(){
    if($(this).is(':checked')){
        document.getElementById("inactive_status").disabled = true;
    }
    else
    {
        document.getElementById("inactive_status").disabled = false;
    } 
});
</script>
@stop