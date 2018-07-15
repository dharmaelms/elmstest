@section('content')
<?php
use App\Model\Banners;
?>

<!-- Page level plugin styles START -->
<link href="{{ URL::asset($custom_theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" rel="stylesheet">
<link href="{{ URL::asset($custom_theme.'/plugins/slider-revolution-slider/rs-plugin/css/settings.css')}}" rel="stylesheet">
<link href="{{ URL::asset($custom_theme.'/css/style-revolution-slider.css')}}" rel="stylesheet">

<!-- Page level plugin styles END -->


  <!-- BEGIN SLIDER -->
 <div class="page-slider margin-bottom-40">
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

            <img src="{{ URL::to('customerportal/theme/default/img/nbebanner.png') }}" alt="Home">

           <!-- <div class="caption lft slide_title_white slide_item_left"
                data-x="center"
                data-y="center"
                data-speed="400"
                data-start="1500"
                data-easing="easeOutExpo">
               <span class="myellow font-weight-500">INNOVATE YOUR CAREER SPACE</span>
            </div> -->
          </li>
          <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400">
            <!-- THE MAIN IMAGE IN THE FIRST SLIDE -->

            <img src="{{ URL::to('customerportal/theme/default/img/revolutionslider/Banner3.jpg') }}" alt="Home">
          </li>
          @endforeach
          @endif
        </ul>
      </div>
    </div>
  </div>
  <!-- END SLIDER -->


  <!--BEGIN Main Container -->
  <div class="main">
    <div class="container">
      <div class="row margin-bottom-40">
        <div class="col-md-5 col-sm-5 col-xs-12">
          <img src="{{ URL::to('customerportal/theme/default/img/eleaerning.jpg') }}" alt="elearning" title="E-LEARNING COURSE NAME HERE" class="img-responsive">
        </div>
        <div class="col-md-7 col-sm-7 col-xs-12 center">
          <div class="margin-bottom-20"></div>
          <h2 class="black green-border margin-bottom-30">NBE Pediatric E-learning</h2>
          <p class="font-15 margin-bottom-20" style="text-align: justify;">NBE in its continuous endeavor to strengthen post graduate training and assessment has undertaken initiatives to introduce Pediatric E-learning Programme for Pediatric PG residents. Pediatric E-learning Programme seeks to develop, maintain, and increase the knowledge, skills, and professional performance of PG Residents through conducting various tructured Webinar classes covering the major primary care pediatrics & pediatric subspecialties topics for PG residents that will be delivered by distinguished faculties from eminent national / regional institutes.The Programme shall includes live weekly e-lectures, journal articles, videos and annual quiz from centers in India  Registered PG Residents shall access to all content, which includes more than 100 e-learning videos & participate quiz annually.</p>
          <!-- <p class="center"><a href="#" class="btn btn-success">GO ONLINE</a></p> -->
        </div>
      </div>

      <hr class="margin-bottom-40">

      <div class="row margin-bottom-60">

        <!-- login -->
        <div id="announcement-div" class="col-md-7 col-sm-7 col-xs-12">
          <div class="row">
            <div class="col-md-3 col-sm-3 col-xs-4">
              <div class="margin-bottom-20"></div>
              <img src="{{ URL::to('customerportal/theme/default/img/icons/announcement-icon.jpg') }}" alt="announcement" class="img-responsive">
            </div>

             @if(isset($announcements) && !empty($announcements))
            <div class="col-md-9 col-sm-9 col-xs-8" style="border-left:1px solid #FCD091;">
            <?php $i = 0;?>
             @foreach ($announcements as $key => $value)
             <?php
              $ann_content= html_entity_decode($value['announcement_content']);
              $ann =strip_tags($ann_content);
              $i++;?>

              <ul class="margin-bottom-20">
                <li class="margin-bottom-20">
                  <br>
                  <h4 class="myellow font-weight-500 margin-bottom-5">{{ $value["announcement_title"] }}</h4>
                  <p class="font-15" style="text-align: justify;">{{str_limit($ann,$limit = 280, $end ='...')}}</p>
                </li>
              </ul>
              <?php if($i == 1) {break;}?>
              @endforeach
              <p class="center"><a href="{{ URL::to('announcements') }}" class="btn btn-success">View All</a></p>
              <!-- <p class="pagination-icons">
                <a href="#" class="prev"><i class="fa fa-angle-left"></i></a>
                <a href="#" class="next"><i class="fa fa-angle-right"></i></a>
              </p> -->
            <!-- </div>
            <div class="col-md-12 col-sm-12 col-xs-8">
               <p class="pagination-icons">
              <p class="center"><a href="{{ URL::to('announcements') }}" class="btn btn-success">View All</a></p>
            </div> -->
            @else
            <div class="col-md-9 col-sm-9 col-xs-8" style="border-left:1px solid #FCD091;">
              <br>
              <br>
              <br>
                <p>No Announcement</p>
              <br>
              <br>
              <br>
            </div>
            @endif
          </div>
        </div>
        <!-- announcements -->
      </div>

      <hr>
    </div>


  </div>

   <div class="contact-bg">
      <div class="container" id="contact">
        <div class="row">
          <div class="col-md-12 col-sm-12 col-xs-12">
            <h3 class="blue font-weight-400 margin-bottom-30">Contact Details</h3>
          </div>
          <div class="col-md-5 col-sm-5 col-xs-12 margin-bottom-40">

            <form action="" class="contact-form contact-details">
              <div class="form-group">
                <label>Address</label>
                <div class="form-control" style="height: 60px">National Board of Examinations.Medical Enclave, Ansari Nagar, Ring Road, New Delhi – 110029</div>
              </div>
              <div class="form-group">
                <label>Phone Number</label>
                <div class="form-control">011-45593000</div>
              </div>
              <div class="form-group">
                <label>Email Address</label>
                <div class="form-control"><a href="mailto:mail@natboard.edu.in">mail@natboard.edu.in</a>
                <!-- &nbsp;&nbsp;|&nbsp;&nbsp;<a href="#">sales@nbe.com</a>&nbsp;&nbsp;|&nbsp;&nbsp;<a href="#">user@nbe.com -->
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
              </div> -->
            </form>
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
            <form action="{{URL::to('contactus/enquiry')}}" method="post" class="contact-form">
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
                <input type="submit" class="btn btn-success" value="SUBMIT">
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