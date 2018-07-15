@section('content')
<?php
use App\Model\Common;
use App\Model\Banners;
use App\Model\StaticPage;

?>
   <link rel="stylesheet" href="customerportal/theme/default/css/my-custom.css">
<!-- Page level plugin styles START -->
<link href="{{ URL::asset($custom_theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" rel="stylesheet">
<link href="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/css/settings.css')}}" rel="stylesheet">
<link href="{{ URL::asset($custom_theme.'/css/style-revolution-slider.css')}}" rel="stylesheet">

<!-- Page level plugin styles END -->

<style>
/*.container{
  width:100%;
}*/
.test {
  position: relative;
 /* width: 50%;*/
 padding: 0px;
 background-color: #a63c41;
}

.image {
  display: block;
  width: 100%;
  height: 320px;
}

.overlay {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  opacity:.8;
  background-color: #210b54;
  overflow: hidden;
  width: 100%;
  height: 320px;
  border-style: double;
  -webkit-transform:scale(0);
  transition: .3s ease;
}

/*.overlay:hover{
    border: 5px groove #f3565d;
}*/
.test:hover .overlay {
  transform: scale(1);
 
}
.test:hover .overlay1 {
  transform: scale(1);
 
}

/*.overlay1:hover{
  border: 5px groove #2291e6;
   text-align: justify;
 
}*/
.text {
  color: white;
 text-align: justify;
 
  font-size: 16px;
  position: absolute;
  top: 50%;
  left: 50%;
  transform: translate(-50%, -50%);
  -ms-transform: translate(-50%, -50%);
   
 
}
.overlay1 {
  position: absolute;
  bottom: 0;
  left: 0;
  right: 0;
  opacity:.8;
  background-color: #540509;
  overflow: hidden;
  width: 100%;
  height: 320px;
 border-style: double;
  -webkit-transform:scale(0);
  transition: .3s ease;
}
.overlay a.btn-danger{

    width: 180px;
    position: absolute;
    top: 87%;
    left: 50%;
    transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    /* background-color: #555; */
    color: white;
    font-size: 14px;
    /* padding: 12px 24px; */
    border: none;
    cursor: pointer;
}
.overlay1 a.btn-primary{

   width: 180px;
    position: absolute;
    top: 87%;
    left: 50%;
    transform: translate(-50%, -50%);
    -ms-transform: translate(-50%, -50%);
    /* background-color: #555; */
    color: white;
    font-size: 14px;
    /* padding: 12px 24px; */
    border: none;
    cursor: pointer;

}

/*.zoomin img:hover {
  width: 300px;
  height: 300px;
}
 .countdown{
    background: rgba(0, 0, 0, 0) -moz-linear-gradient(center top , #ddd297 0%, #85C441 100%) repeat scroll 0 0;
    border: 1px solid #5ea617;
    border-radius: 10px;
    box-shadow: 0 0 6px #fff inset;
    color: #000;
    display: block;
    font: bold 55px/25px Helvetica,sans-serif;
    height: 116px;
    padding: 45px 0 0;
    text-align: center;
    text-decoration: none;
    text-shadow: 0 1px 2px;
    text-transform: none;
    transition: color 0.25s ease-in-out 0s;
    width: 450px;
}*/


#about{padding-left: 15px !important;}
</style>

  <!-- BEGIN SLIDER -->
 <div class="page-slider ">
    <div class="fullwidthbanner-container revolution-slider">
      <div class="fullwidthabnner">
        <ul id="revolutionul">
          <!-- THE NEW SLIDE -->
          <?php $banners=Banners::getAllBanners("ACTIVE"); ?>
            @if(isset($banners) && ($banners != '') && (!empty($banners)))
            @foreach($banners as $banner)
            <?php
                $banner_file_name=$banner['file_client_name'];
                $banner_file_path=config('app.site_banners_path').$banner_file_name;
            ?>
          <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400">
            <!-- THE MAIN IMAGE IN THE FIRST SLIDE -->

            <img src="{{URL::to($banner_file_path)}}" alt="Home">

            <div class="caption lft slide_title_white slide_item_left"
                data-x="center"
                data-y="center"
                data-speed="400"
                data-start="1500"
                data-easing="easeOutExpo">
               <span class="myellow font-weight-500">INNOVATE YOUR CAREER SPACE</span>
            </div> ->
          </li>
          @endforeach
          @endif
        </ul>
      </div>
       </div>
  
        <!-- End Carousel Inner -->
        <!--ul class="nav nav-pills nav-justified">
            <li data-target="#myCarousel" data-slide-to="0" class="active"><a href="#">About<small>Lorem
                ipsum dolor sit</small></a></li>
            <li data-target="#myCarousel" data-slide-to="1"><a href="#">Projects<small>Lorem ipsum
                dolor sit</small></a></li>
            <li data-target="#myCarousel" data-slide-to="2"><a href="#">Portfolio<small>Lorem ipsum
                dolor sit</small></a></li>
            <li data-target="#myCarousel" data-slide-to="3"><a href="#">Services<small>Lorem ipsum
                dolor sit</small></a></li>
        </ul-->
    </div>
    <!-- End Carousel -->
</div>
</div>
 
  <!-- END SLIDER -->


  <!--BEGIN Main Container -->
  <div class="main">
    <div class="container-fluid margin-top-10" style="overflow-x: hidden;    margin-top: -8px!important;">
      <div class="row margin-bottom-10">
        <div class="col-md-6 col-sm-6 col-xs-12 test">
          <img src="{{URL::to('customerportal/theme/default/img/m.jpg')}}" alt="Avatar" class="image">
  <div class="overlay">

<div id="f1_container">
<div id="f1_card" class="shadow">
  <div class="front face">
    <!-- <img src="customerportal/theme/default/img/m.jpg"/> -->
  </div>
  <div class="back face center ">
    <p class=" text">This is nice for exposing more information about an image.
 Any content can go here.</p>
     <!-- <img src="customerportal/theme/default/img/m.jpg"/> -->
     <a href="{{URL::to('http://www.openlink.in/ims.html')}}" class="btn btn-danger">IMS</a>
  </div>
</div>
</div>

  <!--h3 align="center">Hello World</h3-->
    
    
   
        </div>
          <!--div id="pediatric" class="nbe-content">
            <div class="data">
              <img src="{{URL::to('customerportal/theme/default/img/1.png')}}" width="50%">
              <p class="black font-15" style="margin-bottom:52px;">Our offerings cover datacenter infrastructure, end-user computing services, application support & services with 24/7 technical support.</p>
              <a href="{{URL::to('http://www.openlink.in/ims.html')}}" class="btn btn-danger">IMS</a>
            </div>
          </div-->
          
      </div>
    
        <div class="col-md-6 col-sm-6 col-xs-12  test">
          <img src="{{URL::to('customerportal/theme/default/img/l.jpg')}}" alt="Avatar" class="image">
  <div class="overlay1">
  <!--h3 align="center">Hello World</h3-->
   <div id="f1_container">
<div id="f1_card" class="shadow">
  <div class="front face">
    <!-- <img src="customerportal/theme/default/img/m.jpg"/> -->
  </div>
  <div class="back face center ">
    <p class=" text">This is nice for exposing more information about an image.
    Any content can go here.</p>
    
     <a href="{{URL::to('http://www.openlink.in/ott.html')}}" class="btn btn-primary">OTT</a>
        </div>

        </div>
</div>
</div>
          <!--div id="pedicardio" class="nbe-content">
            <div class="data" style="top:52px;">
              <img src="{{URL::to('customerportal/theme/default/img/3.png')}}" width="50%">
              <p class="black font-15">Over-the-top content (OTT) is the delivery of  media over the Internet without the involvement of a multiple-system operator.</p>
            <a href="{{URL::to('http://www.openlink.in/ott.html')}}" class="btn btn-danger">OTT</a>
            </div>
          </div-->
        </div>
          <div class="clearfix"></div>

        <div class="col-md-6 col-sm-6 col-xs-12  test">
           <img src="{{URL::to('customerportal/theme/default/img/man.jpg')}}" alt="Avatar" class="image">
          <!--div id="pediatric" class="nbe-content">
            <div class="data">
              <img src="{{URL::to('customerportal/theme/default/img/4.png')}}" width="50%">
              <p class="black font-15" style="margin-bottom:52px;">Are you confused about your licensing strategy? Do you struggle with an present performance? Are you looking for a new strategy?</p>
              <a href="{{URL::to('http://www.openlink.in/lcs.html')}}" class="btn btn-danger">Software Licensing</a>
                <img src="{{URL::to('customerportal/theme/default/img/man.jpg')}}" alt="Avatar" class="image">
            </div>
          </div-->
           <div class="overlay1">
            <div id="f1_container">
<div id="f1_card" class="shadow">
  <div class="front face">
    <!-- <img src="customerportal/theme/default/img/m.jpg"/> -->
  </div>
  <div class="back face center ">
           <p class=" text">Are you confused about your licensing strategy? Do you struggle with an present performance? Are you looking for a new strategy?</p>
            <a href="{{URL::to('http://www.openlink.in/lcs.html')}}" class="btn btn-primary" >Software Licensing</a>
        </div>
         </div>
          </div>
           </div>
           </div>
        <div class="col-md-6 col-sm-6 col-xs-12  test">
          <img src="{{URL::to('customerportal/theme/default/img/c1.jpg')}}" alt="Avatar" class="image">
           <div class="overlay">
            <div id="f1_container">
<div id="f1_card" class="shadow">
  <div class="front face">
    <!-- <img src="customerportal/theme/default/img/m.jpg"/> -->
  </div>
  <div class="back face center 


  ">
<p class="text">
Consider Moving to Cloud? Whatever path you choose, welcome to the Cloud!
</p>
          <a href="{{URL::to('http://www.openlink.in/cloud.html')}}" class="btn btn-danger" >Cloud</a>


        </div>
         </div>
          </div>
           </div>
            </div>
          <!--div id="pedicardio" class="nbe-content">
            <div class="data" style="top:52px;">
              <img src="{{URL::to('customerportal/theme/default/img/5.png')}}" width="50%">
              <p class="black font-15">Consider Moving to Cloud? Whatever path you choose, welcome to the Cloud!</p>
            <a href="{{URL::to('http://www.openlink.in/cloud.html')}}" class="btn btn-danger">Cloud</a>
            </div>
          </div-->
        </div>
        <div class="clearfix"></div>

      </div>

      <!--hr class="margin-bottom-40">

      <!--div class="row margin-bottom-60">
      <?php
        $slug = 'about-us';
        $page = StaticPage::getOneStaticPageforSlug($slug);
        $about = '';
        if(isset($page) && !empty($page))
        {
        $about = strip_tags($page[0]['content']);
        }
       ?>
        <div class="col-md-12 margin-bottom-20 center"  id="about">
          <h2 class="margin-bottom-20 black">About Us</h2>
          <p class="font-15 margin-bottom-20" style="text-align: justify;">{{str_limit($about,$limit = 520, $end ='...')}}</p>
          <p class="center"><a href="{{URL::to('about-us')}}" class="btn btn-danger">View More</a></p>
        </div>
    </div-->


  </div>

   <div class="contact-bg" style="margin-bottom: -39px;margin-top: -8px;" >
      <div class="container" id="contact">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <h2 class="white font-weight-800 margin-bottom-30 margin-top-10 text-uppercase">Contact Us</h2>
          </div>
          <div class="col-md-5 col-sm-5 col-xs-12 justify margin-bottom-40">
            <h3 class="white text-uppercase text-align-left">Address</h3><br>
<address style="color:#eae9e9;font-size: 16px;">
  4th Floor, Sy No-5/3 <br>
  BEML Layout Thubarahalli Village <br>
  Varthur Main Road,  Kundalahalli Gate <br>
   Bangalore-560066.<br/><br/>
<b>Phone: </b>+91 8028475318<br/> <br/>
<b>E-mail: </b>admin@openlink.in
</address>
            <!--form action="" class="contact-form contact-details">
              <div class="form-group">
                <label>Address</label>
                <div class="form-control" style="height: 60px">4th Floor, Sy No-5/3,BEML Layout,Thubarahalli Village,Varthur Main Road,Kundalahalli Gate, Bangalore-560066.</div>
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <div class="form-control">+91 8028475318</div>
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <div class="form-control"><a href="mailto:admin@openlink.in">admin@openlink.in</a>
                <!-- &nbsp;&nbsp;|&nbsp;&nbsp;<a href="#">sales@nbe.com</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#">user@nbe.com ->
                </div>
              </div>
            <!--   <div class="form-group">
                <label class="col-md-3 control-label no-padding" style="border-right: 1px solid #FCD8A4"><img src="img/Home-peopleloveus.png" alt="People Love Us"></label>
                <div class="col-md-9">
                  <ul class="social-links">
                    <li><a href="#"><i class="sprite sprite-linkedin"></i></a></li>
                    <li><a href="#"><i class="sprite sprite-facebook"></i></a></li>
                    <li><a href="#"><i class="sprite sprite-twitter"></i></a></li>
                    <li><a href="#"><i class="sprite sprite-googleplus"></i></a></li>
                    <li><a href="#"><i class="sprite sprite-youtube"></i></a></li>
                    <li><a href="#"><i class="sprite sprite-pinterest"></i></a></li>
                  </ul>
                </div>
              </div> >
            </form-->
          </div>
          <div class="col-md-2 col-sm-2 center hide-for-xs"><img height="320px" alt="Custom border" src="{{ URL::to('customerportal/theme/default/img/custom-border1.png') }}"></div>
          <div class="col-md-5 col-sm-5 col-xs-12 margin-bottom-40" id="contact_us">
              @if(Session::get('success'))
                <div class="alert alert-success" id="alert-success">
                  <button class="close" data-dismiss="alert">×</button>
                    {{ Session::get('success') }}
                </div>
                <span class="help-inline green">
                    <!-- <strong>Success!</strong><br> -->
                </span>

              <?php Session::forget('success'); ?>
              @endif
            <form action="{{URL::to('contactus')}}" method="post" class="contact-form">
              <?php
              if(Auth::check()) {
              $name=Auth::user()->firstname.' '.Auth::user()->lastname;
              $email=Auth::user()->email;
              $mobile=Auth::user()->mobile;
              }
              else {
              $name='';
              $email='';
              $mobile='';
              }

              if(Input::old('name')!='')
              $name=Input::old('name');
              if(Input::old('email')!='')
              $email=Input::old('email');
              if(Input::old('mobile')!='')
              $mobile=Input::old('mobile');

              ?>
              <div class="form-group">
                <input type="text" name="name" class="form-control input-sm" placeholder="Name*" value="{{$name}}">
                {!! $errors->first('name', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              </div>
              <div class="form-group">
                <input type="text" class="form-control input-sm" name="mobile" placeholder="Mobile" value="{{$mobile}}">
                {!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              </div>
              <div class="form-group">
                <input type="text" class="form-control input-sm" name="email" placeholder="Email*" value="{{$email}}">
                {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              </div>
              <div class="form-group">
                <textarea id="" cols="30" rows="3" name="message" placeholder="Message*" class="form-control">{{Input::old('message')}}</textarea>
               {!! $errors->first('message', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
              <div class="form-group">
                  <div class="controls">
                      <!-- <div class="g-recaptcha" data-sitekey={{ config('app.captcha_site_key') }} style="transform:scale(0.86);transform-origin:0;-webkit-transform:scale(0.86);
                           transform:scale(0.86);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>
                  </div> -->
              </div>
              <div class="form-group">
                  {!! $errors->first('g-recaptcha-response', '<span class="help-inline red">:message</span>') !!}
              </div>
              <div class="form-group">
                <input type="submit" class="btn btn-danger" value="SUBMIT">
              </div>
            </form>
          </div>
        </div>
      </div>
    </div>
  <!--END Main Container -->

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->
<script src="{{ URL::asset($custom_theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.min.js')}}" type="text/javascript"></script><!-- slider for products -->

<!-- BEGIN RevolutionSlider -->
<!-- <script src="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.plugins.min.js')}}" type="text/javascript"></script>  -->
<script src="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.revolution.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.tools.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($custom_theme.'/js/revo-slider-init.js')}}" type="text/javascript"></script>
<!-- END RevolutionSlider -->
<!--Adding reCAPTCHA to your site -->
<script src='https://www.google.com/recaptcha/api.js'></script>

<script>
    jQuery(document).ready(function() {
        RevosliderInit.initRevoSlider();
        Layout.initOWL();
    });
</script>
 <script>
    // $(document).ready(function() {
    //   $("#owl-demo").owlCarousel({
    //     autoPlay: 3000,
    //     items :4,
    //     itemsDesktop : [1199,3],
    //     itemsDesktopSmall : [979,3]
    //   });

    // });
    
    </script>

<!--Plug-in Initialisation-->
  <script type="text/javascript">
  function fetchprograms(cid){
    var programs = [];
    $.ajax({
    type: "GET",
    url: "{{URL::to('latestprograms')}}",
    data: "cid="+cid,
    success: function(html){
    if(html){
        $("#programdiv").replaceWith(html);
        $("#owl-demo").owlCarousel({
        autoPlay: 3000,
        items :3,
        itemsDesktop : [1199,2],
        itemsDesktopSmall : [979,2]
      });
        random();

        }else{
            alert("false");
        }
   }
 });


  }



  function random(){
      $('.owl-carousel').each(function(pos,value){
        $(value).owlCarousel({
          items:4,
          navigation: true,
          navigationText: [
          "<i class='fa fa-caret-left'></i>",
          "<i class='fa fa-caret-right'></i>"
          ],
          beforeInit : function(elem){
          }
        });
    });
  }
    $(document).ready(function() {
    //Sort random function
      random();
      // $('#contact_us').scroll();
      $('#alert-success').delay(5000).fadeOut();
      var err_ar_js = false;
        <?php

          $err_all = $errors->all();
          if(is_array($err_all)){
            foreach($err_all as $err){
            ?>
             err_ar_js = true;
            <?php
              break;
            }
          }
        ?>

       if(err_ar_js == true){
        scroll(0, $('#contact_us').offset().top)
       }
        $(this).addClass('active');
  $('.course_act li').click(function(){

    $('.course_act li').removeClass('active');
    $(this).addClass('active');
});

    });




</script>


@stop
