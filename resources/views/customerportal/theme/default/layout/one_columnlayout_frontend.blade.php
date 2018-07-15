<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
<meta charset="utf-8"/>
<title>
@if(isset($pagetitle))
    {{$pagetitle}}
@endif
</title>
<meta http-equiv="X-UA-Compatible" content="IE=edge">
<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
<meta http-equiv="Content-type" content="text/html; charset=utf-8">
<meta name="keywords" content="<?php if(isset($metakeys)) echo $metakeys; ?>"/>
<meta name="description" content="<?php if(isset($metadescription)) echo $metadescription; ?>"/>

<link rel="shortcut icon" href="/customerportal/theme/default/img/favicon.ico"/>
<!-- BEGIN GLOBAL MANDATORY STYLES -->
<!--link href='https://fonts.googleapis.com/css?family=Roboto+Slab:400,700,300' rel='stylesheet' type='text/css'-->
<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/simple-line-icons/simple-line-icons.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->

<!-- Page level plugin styles START -->
<link href="{{ URL::asset($theme.'/plugins/fancybox/source/jquery.fancybox.css')}}" rel="stylesheet">
<!-- Page level plugin styles END -->
<!-- BEGIN THEME STYLES --> 
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/components.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/style.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/style-shop.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/style-responsive.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/custom.css')}}" />

<!-- END THEME STYLES -->

<script src="{{ URL::asset($theme.'/plugins/jquery.min.js')}}" type="text/javascript"></script>

<body class="ecommerce">
  <!-- BEGIN HEADER -->
  <div class="header">
      @yield('header')
  </div>
  <!-- END HEADER -->
  <div class="clearfix"></div>

  <!-- BEGIN CONTENT -->
  <div class="page-content-wrapper">
      <div class="page-content">
          @if(isset($breadcrumbs))
              <?php echo $breadcrumbs; ?>
          @endif

        <!--
        div class="fixed-social-icons">
            <ul>
              <li><a href="https://www.facebook.com/inurturepro/" target="_blank"><i class="sprite sprite-facebook"></i></a></li>
              <li><a href="https://twitter.com/?lang=en" target="_blank"><i class="sprite sprite-twitter"></i></a></li>
              <li><a href="https://www.linkedin.com/company/inurture-education-solution-pvt-ltd" target="_blank"><i class="sprite sprite-linkedin"></i></a></li->
              <li><a href="https://www.quora.com/" target="_blank"><i class="sprite sprite-Quora"></i></a></li>
              <li><a class="call"><i class="sprite sprite-call"></i><span class="phone-hover">+91 96332 85632</span></a></li>
              <li><a href="mailto:elearn@inurture.co.in" class="mail"><i class="sprite sprite-mail"><span class="mail-hover">elearn@inurture.co.in</span></i></a></li>
            </ul>
          </div-->

          @yield('content')
      </div>
  </div>
  <!-- END CONTENT -->
    
  <!-- BEGIN FOOTER -->
  @yield('footer')
  <!-- END FOOTER -->

  <script src="{{ URL::asset($theme.'/plugins/jquery-migrate.min.js')}}" type="text/javascript"></script>
  <script src="{{ URL::asset($theme.'/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>      
  <script src="{{ URL::asset($theme.'/js/back-to-top.js')}}" type="text/javascript"></script>
  <script src="{{ URL::asset($theme.'/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
  <!-- END CORE PLUGINS -->

  <!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->
  <script src="{{ URL::asset($theme.'/plugins/fancybox/source/jquery.fancybox.pack.js')}}" type="text/javascript"></script><!-- pop up -->
  <script src="{{ URL::asset($theme.'/plugins/bootstrap-touchspin/bootstrap.touchspin.js')}}" type="text/javascript"></script><!-- Quantity -->
  <script src="{{ URL::asset($theme.'/js/layout.js')}}"></script>

<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function(){    
        Layout.init();    
        Layout.initTwitter();
        Layout.initFixHeaderWithPreHeader();
        Layout.initNavScrolling();
    });
</script>
</body>
</html>