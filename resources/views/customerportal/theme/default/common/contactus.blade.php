@section('content')
<script src="http://maps.google.com/maps/api/js" type="text/javascript"></script>
<script src="{{ URL::asset($custom_theme.'/plugins/gmaps/gmaps.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($custom_theme.'/js/contact-us.js')}}" type="text/javascript"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>

<style>
  .header { margin-bottom: 0; }
</style>

<?php
use App\Libraries\Timezone;
$sitekey=Config('app.captcha_site_key');
?>
<div id="map" class="gmaps" style="height:300px;"></div>
<div class="margin-bottom-40"></div>
<!-- <div class="container">
  <div class="row">
    <div class="col-md-4 col-sm-8 col-xs-12 margin-bottom-40">
      <h4><strong>iNurture Education Solutions Private Limited</strong></h4>
      <p>Niton Compound,<br>
      # 11/4 A, Block – B1,<br>
      Palace Road, Vasanthnagar,<br>
      Bangalore – 560052</p>
      <p>Ph: 91-80-42576666</p>
    </div>
  </div>
</div> -->

 <div class="container">
      <div class="row">
        <div class="col-md-5 col-sm-5 col-xs-12 margin-bottom-40">
          <h3 class="gray font-weight-400">Contact Details</h3>
          <form action="" class="contact-form contact-details">
            <div class="form-group">
              <label><strong>Address</strong></label>
              <div class="border-btm myellow"><p>Niton Compound,# 11/4 A, Block – B1,Palace Road, Vasanthnagar,Bangalore – 560052</p></div>
            </div>
            <div class="form-group">
              <label><strong>Phone Number</strong></label>
              <div class="border-btm myellow"><p>+91 96332 85632&nbsp;&nbsp;|&nbsp;&nbsp; +91 91-80-42576666</p></div>
            </div>
            <div class="form-group">
              <label><strong>Email Address</strong></label>
             <!--  <div class="form-control"><a href="#">info@inurture.com</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#">sales@inurture.com</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#">user@inurture.com</a></div> -->
             <div class="border-btm"><p><a href="mailto:elearn@inurture.co.in">elearn@inurture.co.in</a></p></div>
             
            </div>
            <!-- <div class="form-group">
              <label class="col-md-3 control-label no-padding" style="border-right: 1px solid #FCD8A4"><img src="{{URL::to($custom_theme.'/img/Home-peopleloveus.png')}}" alt="People Love Us"></label>
              <div class="col-md-9">
                <ul class="social-links">
                  <li><a href="#"><i class="sprite sprite-facebook"></i></a></li>
                  <li><a href="#"><i class="sprite sprite-twitter"></i></a></li>
                  <li><a href="#"><i class="sprite sprite-linkedin"></i></a></li>
                  <li><a href="#"><i class="sprite sprite-Quora"></i></a></li>
                </ul>
              </div>
            </div> -->
          </form>
        </div>
        <div class="col-md-2 col-sm-2 center hide-for-xs">
          <img src="{{URL::to($custom_theme.'/img/custom-border1.png')}}" alt="Custom border" height="320px">
        </div>
        <div id="contact_us" class="col-md-5 col-sm-5 col-xs-12 margin-bottom-40">
          <h3 class="gray font-weight-400 center">Contact Us</h3>
          <!-- <p class="gray center margin-bottom-20">Lorem Ipsum is simply dummy text of the printing and typesetting industry. Lorem Ipsum has been the industry's standard dummy text ever since the 1500s, when an unknown printer took a galley of type and scrambled.</p> -->

          <form action="{{url('contactus')}}" method="post" class="contact-form">
            <div class="form-group">
              <input type="text" name="name" class="form-control input-sm" placeholder="Name*" value="{{Input::old('name')}}">
              {!! $errors->first('name', '<span class="help-inline" style="color:#f00">:message</span>')!!}
            </div>
            <div class="form-group">
              <input type="text" class="form-control input-sm" name="mobile" placeholder="Mobile" value="{{Input::old('mobile')}}">
               {!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>')!!}
            </div>
            <div class="form-group">
              <input type="text" class="form-control input-sm" name="email" placeholder="Email*" value="{{Input::old('email')}}">
              {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>')!!}
            </div>
            <div class="form-group">
              <textarea id="" cols="30" rows="3" name="message" placeholder="Message*" class="form-control">{{Input::old('message')}}</textarea>
               {!! $errors->first('message', '<span class="help-inline" style="color:#f00">:message</span>') !!}
            </div>
            <div class="form-group">
              <div class="controls">
                <div class="g-recaptcha" data-sitekey={{$sitekey}}  style="transform:scale(0.86);transform-origin:0;-webkit-transform:scale(0.86);transform:scale(0.86);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>
              </div>
            </div>
              
            <div class="form-group">
              <div class="controls">
                {!! $errors->first('g-recaptcha-response', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>  
            </div>
            <div class="form-group">
              <!-- <a href="#" class="btn btn-success pull-right">Submit</a> -->
              <input type="submit" class="btn btn-success" value="SUBMIT">
            </div>
          </form>
        </div>
      </div>
    </div>

<script type="text/javascript">
  jQuery(document).ready(function() {
    ContactUs.init();
  });
</script>
@stop