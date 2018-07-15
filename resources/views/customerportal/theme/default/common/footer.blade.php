@section('footer')
<?php 
    use App\Model\Faq;
    use App\Model\SiteSetting; 
?>
<!-- BEGIN FOOTER -->
<div class="page-footer-inner">
    <div class="row">
        <div class="col-md-8 col-sm-8 col-xs-12">
            <span>
            <?php
                if(isset($pages)) {
                    foreach ($pages as $key => $value) {
                        echo '<a style="color:white" href="'.URL::to("/".$value["slug"]).'" title="'.$value["title"].'">'.$value["title"].'</a>&nbsp;|&nbsp;';
                    }
                }
                if(SiteSetting::module('General','faq') == "on") {
                    echo '<a title="Faq" style="color:white" href="'.URL::to('/faq').'">FAQ</a>';
                }
            ?>
            </span>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
            <ul class="social-icons pull-right">
                <li><a target="_blank" href="https://www.facebook.com/" data-original-title="facebook" class="facebook"></a></li>
                <li><a target="_blank" href="https://twitter.com/" data-original-title="twitter" class="twitter"></a></li>
                <li><a target="_blank" href="https://www.linkedin.com/company/" data-original-title="linkedin" class="linkedin"></a></li>
            </ul>
        </div>
    </div>
    <div class="row">
        <div class="col-md-8 col-sm-8 col-xs-12">
            <p>Copyright &copy; {{ config('app.site_name', 'Openlink IT') }} &nbsp;|&nbsp; All rights reserved</p>
        </div>
        <div class="col-md-4 col-sm-4 col-xs-12">
            <div class="pull-right">
            Powered by <a href="http://openlink.in/" title="Openlink IT" class="white" target="_blank">Openlink IT</a>
            </div>
        </div>
    </div>
</div>
<div class="scroll-to-top">
    <i class="icon-arrow-up"></i>
</div>

<!-- login modal -->
<div id="signinreg" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <ul class="nav nav-tabs">
          <li class="active">
            <a href="#signin" data-toggle="tab">SIGN IN</a>
          </li>
          <li>
            <a href="#reg" data-toggle="tab">REGISTER</a>
          </li>
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade active in" id="signin">
            <div class="form-group">
                <span id="error_text_popup" name="error_text_popup" class="text-danger" style="margin-top: -20px;"></span>
               </div>
            <!-- BEGIN FORM-->
            <form action="#" class="default-form xs-margin" role="form" id="signin_popup" name="signin_popup" method="post">
              <input type="hidden" name="login_url" id="login_url" value="{{URL::to('auth/login')}}">
              <div class="form-group">
                <input type="text" class="form-control" id="email" name="email" placeholder="Email/Username">
              </div>
              <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>
              
              <div class="margin-bottom-5">                  
                <button type="submit" class="btn btn-danger btn-block">Sign In</button>
              </div>
              <p class="margin-bottom-15"><a href="{{URL::to('password/forgot')}}">Forgot your password?</a></p>

              <div class="row">
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a class="btn btn-danger btn-block" href="{{ route('social.login', ['google']) }}" style="background-color:#d14836;border-color:#d14836"><i class="fa fa-google-plus"></i> Google</a>
                </div>
                <div class="col-md-6 col-sm-6 col-xs-6">
                  <a class="btn btn-danger btn-block" href="{{ route('social.login', ['facebook']) }}" style="background-color:#3a5795;border-color:#3a5795"><i class="fa fa-facebook"></i> Facebook</a>
                </div>
              </div>
            </form>
            <!-- END FORM-->
          </div>
          <div class="tab-pane fade" id="reg">
            <!-- BEGIN FORM-->
            <form action="#" class="default-form" role="form">
              <input type="hidden" id="register_url" name="register_url" value="{{URL::to('auth/register')}}">

              <div class="form-group">
                <input type="text" class="form-control" id="reg_firstname" name="reg_firstname" placeholder="First Name">
                <span class="text-danger" id="err_reg_firstname" name="err_reg_firstname"></span>
              </div>
               <div class="form-group">
                <input type="text" class="form-control" id="reg_lastname" nmae="reg_lastname" placeholder="Last Name">
                <span class="text-danger" id="err_reg_lastname" name="err_reg_lastname"></span>
              </div>
              <div class="form-group">
                <input type="text" class="form-control" id="reg_username" name="reg_username" placeholder="Username">
                <span class="text-danger" id="err_reg_username" name="err_reg_username"></span>
              </div>
              <div class="form-group">
                <input type="email" class="form-control" id="reg_email" name="reg_email" placeholder="Email">
                <span class="text-danger" id="err_reg_email" name="err_reg_email"></span>
              </div>
              <div class="form-group">
                <input type="tel" class="form-control" id="reg_phone" name="reg_phone" placeholder="Contact Number">
                <span class="text-danger" id="err_reg_phone" name="err_reg_phone"></span>
              </div>
              <div class="form-group">
                <input type="password" class="form-control" id="reg_password" name="reg_password" placeholder="Password">
                <span class="text-danger" id="err_reg_password" name="err_reg_password"></span>
              </div>
               <div class="form-group">
                <input type="password" class="form-control" id="reg_confpassword" name="reg_confpassword" placeholder="Confirm Password">
                <span class="text-danger" id="err_reg_confpassword" name="err_reg_confpassword"></span>
              </div>
              
               <div class="form-group">
              <input type="checkbox" id="terms_and_condition" name="terms_and_condition" checked><a href="{{URL::to('terms-and-conditions')}}" target="_blank"> Terms and Conditions</a>     
              </div>          
              <div>
                <button type="submit" class="btn btn-danger btn-block">Create Account</button>
              </div>
            </form>
            <!-- END FORM-->
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
<script src="{{ URL::asset($theme.'/js/custom-front-end/header_frontend.js')}}"></script>
@if(config::get("app.leadsquared.enabled"))
<!--LeadSquared Tracking Code Start-->
<script type="text/javascript" src="http://web.mxradon.com/t/Tracker.js"></script>
<script type="text/javascript">
     pidTracker(config::get("app.leadsquared.pid_tracker"));
</script>
<!--LeadSquared Tracking Code End-->
@endif
@if(Session::has("userQuizAttempt"))

<script>
    var siteURL = "{{ URL::to("/") }}";
    var qData = {!! json_encode(Session::get("userQuizAttempt")) !!};
    var currentTime = {{ time() }};
    var timeSpentOnQuiz = (currentTime - qData.started_at);
    var timeOutDuration = ((tmpVal = ((qData.duration * 60) - timeSpentOnQuiz)) > 0)? (tmpVal * 1000) : 0;
    setTimeout(function(){
        var xmlHTTPRequest = $.ajax({
            url : siteURL+"/assessment/close-attempt/"+qData.attempt_id,
            type : "post"
        });

        xmlHTTPRequest.done(function(response, status, jqXHR){
            if(response.status !== null && response.status !== undefined)
            {
                alert("You have exceeded the maximum assessment duration. Assessment has been submitted automatically.");
                window.location.href = siteURL+"/assessment/detail/"+qData.quiz_id;
            }
        });
    }, timeOutDuration); 
</script>

@endif
<!-- END FOOTER -->
@stop
