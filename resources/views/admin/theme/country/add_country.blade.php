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
            
            <div class="box-content">
                <form action="{{URL::to('cp/country/add-country')}}" class="form-horizontal form-bordered form-row-stripped"  method="post" >         
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.add_country_name_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="name" value="{{ Input::old('name') }}"> 
                                {!! $errors->first('name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.add_currency_code_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="currency_code" value="{{ Input::old('currency_code') }}"> 
                                {!! $errors->first('currency_code', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.add_currency_name_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="currency_name" value="{{ Input::old('currency_name') }}"> 
                                {!! $errors->first('currency_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.add_currency_symbol_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="currency_symbol" value="{{ Input::old('currency_symbol') }}"> 
                                {!! $errors->first('currency_symbol', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.add_iso_code_two_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="iso_code_two" value="{{ Input::old('iso_code_two') }}"> 
                                {!! $errors->first('iso_code_two', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.add_iso_code_three_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="iso_code_three" value="{{ Input::old('iso_code_three') }}"> 
                                {!! $errors->first('iso_code_three', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname">{{ trans('admin/country.add_status_label') }}<span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="radio"  name="status" value="ACTIVE" checked> {{ trans('admin/country.add_active_status_label') }} <br>
                                <input type="radio"  name="status" value="INACTIVE" id="inactive_status"> {{ trans('admin/country.add_inactive_status_label') }}<br>
                                <span>{{ trans('admin/country.note_enables_currency_for_setting_pricing_options') }}</span>
                                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="firstname"></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                <input type="checkbox" name="default" value="YES" id="default"> {{ trans('admin/country.add_default_label') }}<br>
                                <span>{{ trans('admin/country.note_currency_for_all_endusers') }}</span>
                                {!! $errors->first('default', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/country.payment_options') }} <span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-5 controls">
                                @foreach($payment_options as $key => $value)
                                <input type="checkbox" name="payment_option[]" value="{{$key}}" <?php if(Input::old('payment_option') == $key){ echo "checked"; } ?>> {{ $value['payment_name'] }}<br>
                                @endforeach
                                {!! $errors->first('payment_option', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                    </div>
                    <div class="form-group last">
                        <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                            <input type="submit" class="btn btn-info" value="Add Country">
                            <a class="btn" href="{{ URL::to('cp/country') }}">{{ trans('admin/country.cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div>
        </div><!--Register-->
    </div>
</div>

@stop