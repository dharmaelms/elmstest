@section('footer')
<?php 
    use App\Model\Faq;
    use App\Model\SiteSetting; 
?>

<!-- BEGIN PRE-FOOTER -->
 <!--    <div class="pre-footer">
      <div class="container">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-xs-12 center">
            <ul>
              <li><img src="{{ URL::to('customerportal/theme/default/img/icons/faq-icon.jpg') }}" alt="FAQ" width="35"> <a href="#">FAQ's</a></li>
              <li><img src="{{ URL::to('customerportal/theme/default/img/icons/faq-icon.jpg') }}" alt="Privacy Policy" width="35"> <a href="#">Privacy Policy</a></li>
              <li><img src="{{ URL::to('customerportal/theme/default/img/icons/faq-icon.jpg') }}" alt="Terms &amp; Conditions" width="35"> <a href="#">Terms &amp; Conditions</a></li>
            </ul>
          </div>
        </div>
      </div>
    </div> -->
    <!-- END PRE-FOOTER -->
    
    <!-- BEGIN FOOTER -->
    <div class="footer">
      <div class="container">
        <!--img src="{{ URL::to('customerportal/theme/default/img/custom-border-footer.png') }}" alt="Footer border" class="center margin-bottom-10 img-responsive"-->
        <div class="row">
          <!-- BEGIN COPYRIGHT -->
          <div class="col-md-6 col-sm-6">
            Copyright &copy; <span class="blue">OpenLink IT Solutions Pvt Ltd</span>
          </div>
          <!-- END COPYRIGHT -->
          <!-- BEGIN PAYMENTS -->
          <div class="col-md-6 col-sm-6">
            <span class="pull-right">Powered by <a href="http://openlink.in/" class="blue style="text-decoration:none;">OpenLink IT</a></span>
          </div>
          <!-- END PAYMENTS -->
        </div>
      </div>
    </div>
    <!-- END FOOTER -->

<!-- login modal -->
<div id="signinreg" class="modal fade" tabindex="-1" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-body">
        <ul class="nav nav-tabs">
          <li class="active">
            <a href="#signin" data-toggle="tab">Sign In</a>
          </li>
         <!--  <li>
            <a href="#reg" data-toggle="tab">Register</a>
          </li> -->
        </ul>
        <div class="tab-content">
          <div class="tab-pane fade active in" id="signin">
            <div class="form-group">
                <span id="error_text_popup" name="error_text_popup" class="text-danger" style="margin-top: -20px;"></span>
               </div>
            <!-- BEGIN FORM-->
            <form action="#" class="default-form xs-margin" role="form" id="signin_popup" name="signin_popup" method="post">
              <input type="hidden" name="login_url" id="login_url" value="{{URL::to('auth/login')}}">
                <input type="hidden" name="baseurl" id="baseurl" value="{{URL::to('/')}}">
             <input type="hidden" name="dashboard_url" id="dashboard_url" value="{{URL::to('dashboard')}}">

              <div class="form-group">
                <input type="text" class="form-control" id="email" name="email" placeholder="Email/Username">
              </div>
              <div class="form-group">
                <input type="password" class="form-control" id="password" name="password" placeholder="Password">
            </div>
              
              <div class="margin-bottom-10 center">                  
                <button type="submit" class="btn btn-success btn-block"><i class="fa fa-sign-in" aria-hidden="true"></i> Login</button>
              </div>
              <p class="md-margin center"><a href="{{URL::to('password/forgot')}}">Forgot your password?</a></p>

              <div class="row center">
                
              <?php $sitessets = SiteSetting::module('Socialite'); ?>
              @if($sitessets['google'] == "on")
                <div class="col-md-12">
                or Connect with us&nbsp;
             
                  <a class="btn btn-danger btn-sm googleplus-btn" href="{{ route('social.login', ['google']) }}" title="Google plus"><i class="fa fa-google-plus"></i></a>
                  &nbsp;
                @endif
                @if($sitessets['facebook'] == "on")
                  <a class="btn btn-danger btn-sm facebook-btn" href="{{ route('social.login', ['facebook']) }}" title="Facebook"><i class="fa fa-facebook"></i></a>
                </div>
                @endif
              </div>
            </form>
            <!-- END FORM-->
          </div>
          <div class="tab-pane fade" id="reg">
            <!-- BEGIN FORM-->
            <form action="#" class="default-form" role="form">
              <input type="hidden" id="register_url" name="register_url" value="{{URL::to('auth/register')}}">

              <div class="form-group">
                <input type="text" class="form-control" id="reg_firstname" name="reg_firstname" placeholder="First Name*">
                <span class="text-danger" id="err_reg_firstname" name="err_reg_firstname"></span>
              </div>
               <div class="form-group">
                <input type="text" class="form-control" id="reg_lastname" nmae="reg_lastname" placeholder="Last Name">
                <span class="text-danger" id="err_reg_lastname" name="err_reg_lastname"></span>
              </div>
              <div class="form-group">
                <input type="text" class="form-control" id="reg_username" name="reg_username" placeholder="Username*">
                <span class="text-danger" id="err_reg_username" name="err_reg_username"></span>
              </div>
              <div class="form-group">
                <input type="email" class="form-control" id="reg_email" name="reg_email" placeholder="Email*">
                <span class="text-danger" id="err_reg_email" name="err_reg_email"></span>
              </div>
              <div class="form-group">
                <input type="tel" class="form-control" id="reg_phone" name="reg_phone" placeholder="Contact Number">
                <span class="text-danger" id="err_reg_phone" name="err_reg_phone"></span>
              </div>
              <div class="form-group">
                <input type="password" class="form-control" id="reg_password" name="reg_password" placeholder="Password*">
                <span class="text-danger" id="err_reg_password" name="err_reg_password"></span>
              </div>
               <div class="form-group">
                <input type="password" class="form-control" id="reg_confpassword" name="reg_confpassword" placeholder="Confirm Password*">
                <span class="text-danger" id="err_reg_confpassword" name="err_reg_confpassword"></span>
              </div>
              
               <div class="form-group">
              <input type="checkbox" id="terms_and_condition" name="terms_and_condition" checked><a href="{{URL::to('terms-and-conditions')}}" target="_blank"> Terms and Conditions</a>     
              </div>          
              <div class="center">
                <button type="submit" class="btn btn-success btn-block">Create Account</button>
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
     pidTracker('9855');
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