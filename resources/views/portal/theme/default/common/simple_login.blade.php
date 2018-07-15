@section('content')

 <link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/plugin_login.css')}}" />

<?php
$site_logo=SiteSetting::module('Contact Us', 'site_logo');
if (isset($site_logo) && !empty($site_logo)) {
    $logo=config('app.site_logo_path').$site_logo;
} else {
    $logo=config('app.default_logo_path');
}
?>

<div class="header-sign-in">
  <div class="container-fluid header-container">
    <a href="{{ url('/') }}">
        <img src="{{ URL::to($logo) }}" alt="logo" height="51" />
    </a>
    <div class="header-navigation pull-right">
      <ul>
        <li>
          <a href="{{ url('/') }}">Home</a>
        </li>
      </ul>
    </div>
  </div>
</div>

<div class="formWidth formCont">
    <h1 class="center">{{ trans('user.sign_in') }}</h1>
    <div class="form-group">
        <span id="error_text_popup" name="error_text_popup" class="text-danger" style="margin-top: -20px;"></span>
    </div>
    <form action="#" class="default-form xs-margin" role="form" id="signin_popup" name="signin_popup" method="post">
        <input type="hidden" name="login_url" id="login_url" value="{{URL::to('auth/login')}}">
        <input type="hidden" name="email_verification" id="email_verification" value="<?php echo config('app.email_verification'); ?>">
        <input type="hidden" name="baseurl" id="baseurl" value="{{URL::to('/')}}">
        <input type="hidden" name="dashboard_url" id="dashboard_url" value="{{URL::to('/dashboard/')}}">
        <div class="form-group">
            <input type="text" class="form-control" id="email" name="email" placeholder="Email/Username">
            <input type="password" class="form-control" id="password" name="password" placeholder="Password">
        </div>
        <div class="form-group">
            <button class="btn btn-primary" type="submit">{{ trans('user.sign_in') }}</button>
             <a class="forgotPassword" href="{{URL::to('password/forgot')}}">{{ trans('user.forgot_your_password') }}</a>
        </div>
       
    </form>
</div>
    <script src="{{ asset($theme.'/js/custom-front-end/header_frontend.js')}}"></script>
    <script src="{{ asset($theme.'/js/custom-front-end/pwstrength.js')}}"></script>
    @if(config("app.leadsquared.enabled"))
        <!--LeadSquared Tracking Code Start-->
        <script type="text/javascript" src="//web.mxradon.com/t/Tracker.js"></script>
        <script type="text/javascript">
            pidTracker({{ config("app.leadsquared.pid_tracker") }});
        </script>

        <!--LeadSquared Tracking Code End-->
    @endif
@stop
