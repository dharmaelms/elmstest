@section('content')
<section>
    @if($sitessets['lat'] && $sitessets['lng'])
        <div id="map" class="gmaps" style="height:300px;"></div>
    @endif
    </br>
    <div class="container">
        <!-- BEGIN SIDEBAR & CONTENT -->
        <div class="row margin-bottom-40 content-page">
            <div class="col-md-12">
                @if(Session::get('success'))
                    <span class="help-inline green">
                        <!-- <strong>Success!</strong><br> -->
                        <div class="alert alert-success">
                            <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
                            {{ Session::get('success') }}
                        </div>
                    </span>
                    <?php Session::forget('success'); ?>
                @endif
                <h1>Contact</h1>
            </div>
            <div class="col-md-4 col-sm-8 col-xs-12 margin-bottom-20">
                <!-- BEGIN FORM-->
                <form action="{{ url('contactus') }}" class="default-form" role="form" method="post">
                    <div class="form-group">
                        <label for="name">{{ trans('user.name') }} <span class="require">*</span></label>
                        <input type="text" class="form-control" id="name" name ="name" value="{{ Input::old('name')}}">
                        {!! $errors->first('name', '<span class="help-inline red">:message</span>') !!}
                    </div>
                    <div class="form-group">
                        <label for="email">{{ trans('user.emailid') }}<span class="require">*</span></label>
                        <input type="text" class="form-control" name="email" value="{{ Input::old('email') }}" id="email">
                        {!! $errors->first('email', '<span class="help-inline red">:message</span>') !!}
                    </div>
                    <div class="form-group">
                        <label for="phone">{{ trans('user.phone') }}</label>
                        <input type="text" class="form-control" id="phone" name ="phone" value="{{ Input::old('phone')}}">
                        {!! $errors->first('phone', '<span class="help-inline red">:message</span>') !!}
                    </div>
                    <div class="form-group">
                        <label for="mobile">{{ trans('user.mobile_no') }}</label>
                        <input type="text" class="form-control" id="mobile" name ="mobile" value="{{ Input::old('mobile')}}">
                        {!! $errors->first('mobile', '<span class="help-inline red">:message</span>') !!}
                    </div>
                    <div class="form-group">
                        <label for="message">{{ trans('user.message') }} <span class="require">*</span></label>
                        <textarea class="form-control" rows="2" id="message" name="message">{{ Input::old('message')}}</textarea>
                        {!! $errors->first('message', '<span class="help-inline red">:message</span>') !!}
                    </div>
                    <div class="form-group">
                        <div class="controls">
                            <div class="g-recaptcha" data-sitekey={{ config('app.captcha_site_key') }} style="transform:scale(0.86);transform-origin:0;-webkit-transform:scale(0.86);
                                 transform:scale(0.86);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>
                        </div>
                    </div>
                    <div class="form-group">
                        {!! $errors->first('g-recaptcha-response', '<span class="help-inline red">:message</span>') !!}
                    </div>
                    <div>
                        <button type="submit" class="btn btn-danger">{{ trans('user.submit') }}</button>
                    </div>
                </form>
                <!-- END FORM-->
            </div>

            <!-- BEGIN SIDEBAR -->
            <div class="sidebar col-md-offset-3 col-md-5 col-sm-4 col-xs-12">
                <h3 class="red-title">{{ Lang::get('user.registered_office') }}</h3>
                <address><strong>{{$sitessets['company_name'] }}</strong><br>
                    {{$sitessets['address'] }}
                </address>
                <address>
                    @if($sitessets['phone'])<i class="fa fa-phone-square yellow"></i> {{ $sitessets['phone'] }}<br>@endif
                    @if($sitessets['mobile_no'])<i class="fa fa-phone-square yellow"></i>  {{$sitessets['mobile_no']}}<br>@endif
                    @if($sitessets['email'])<i class="fa fa-envelope yellow"></i> <a href="mailto:{{$sitessets['email']}}" class="red">{{$sitessets['email']}}</a><br>@endif
                </address>
            </div>
            <!-- END SIDEBAR -->
        </div>
        <!-- END SIDEBAR & CONTENT -->
    </div>
</section>
<script type="text/javascript">
    address = '<?php echo $sitessets['address']; ?>';
    title = '<?php echo $sitessets['company_name']; ?>';
    var lat = '<?php echo $sitessets['lat']; ?>';
    var lng = '<?php echo $sitessets['lng'] ?>';

    jQuery(document).ready(function() {
        ContactUs.init(lat, lng, address, title);
    });
</script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<script src="//maps.google.com/maps/api/js" type="text/javascript"></script>
<script src="{{ asset($theme.'/plugins/gmaps/gmaps.js')}}" type="text/javascript"></script>
<script src="{{ asset($theme.'/js/contact-us.js')}}" type="text/javascript"></script>
@stop
