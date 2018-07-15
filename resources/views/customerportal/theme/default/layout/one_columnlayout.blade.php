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
<!-- BEGIN GLOBAL MANDATORY STYLES -->

  <link rel="shortcut icon" href="img/favicon.ico">

  <link href='https://fonts.googleapis.com/css?family=Roboto:400,500,300,700,400italic' rel='stylesheet' type='text/css'>
  <!-- Global styles START -->          
  <link href="{{ URL::asset($theme.'plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet">
  <link href="{{ URL::asset($theme.'plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet">
  <!-- Global styles END --> 
 

  <!-- Theme styles START -->
  <link href="{{ URL::asset($theme.'css/components.css')}}" rel="stylesheet">
  <link href="{{ URL::asset($theme.'css/style.css')}}" rel="stylesheet">
  <link href="{{ URL::asset($theme.'css/style-shop.css')}}" rel="stylesheet" type="text/css">
  <link href="{{ URL::asset($theme.'css/style-responsive.css')}}" rel="stylesheet">
  <link href="{{ URL::asset($theme.'css/themes/red.css')}}" rel="stylesheet" id="style-color">
  <link href="{{ URL::asset($theme.'css/custom.css')}}" rel="stylesheet">





<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/font-awesome/css/font-awesome.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/simple-line-icons/simple-line-icons.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/bootstrap/css/bootstrap.min.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/uniform/css/uniform.default.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/plugins/bootstrap-switch/css/bootstrap-switch.min.css')}}" rel="stylesheet" type="text/css"/>
<!-- END GLOBAL MANDATORY STYLES -->
<!-- Nestable List STYLES -->
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/jquery-nestable/jquery.nestable.css')}}"/>
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/responsive-tabs/css/easy-responsive-tabs.css')}}" />



<!-- BEGIN THEME STYLES -->
<link href="{{ URL::asset($theme.'/css/components.css')}}" id="style_components" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/css/plugins.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/css/layout.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/css/timeline.css')}}" rel="stylesheet" type="text/css"/>
<link id="style_color" href="{{ URL::asset($theme.'/css/darkblue.css')}}" rel="stylesheet" type="text/css"/>
<link href="{{ URL::asset($theme.'/css/custom.css')}}" rel="stylesheet" type="text/css"/>


<link href="{{ URL::asset($theme.'/plugins/bootstrap-datepicker/css/bootstrap-datepicker3.min.css')}}" rel="stylesheet" type="text/css"/>

<!-- END THEME STYLES -->
<link rel="shortcut icon" href="{{ URL::asset($theme.'/img/favicon.ico')}}"/>
<style type="text/css">
    .page-footer{
       /* width: 100%;
        background-color: #9e9e9e;
        color: #ffffff;
        font-size: 13px;
        margin: 0;
        height:70px;
        clear:both;
        bottom:0;*/
    }
</style>
<!-- BEGIN JQUERY -->
<script src="{{ URL::asset($theme.'/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/jquery-migrate.min.js')}}" type="text/javascript"></script>
<!-- END JQUERY -->
<body class="page-header-fixed page-quick-sidebar-over-content">
    <!-- BEGIN HEADER -->
    <div class="page-header navbar navbar-fixed-top">
        @yield('header')
    </div>
    <!-- END HEADER -->
    <div class="clearfix"></div>
    <!-- BEGIN CONTAINER -->
    <div class="page-container">
        @if(Auth::check())
        <div class="page-sidebar-wrapper">
            @include($theme_path.'.common.default_sidebar')
        </div>
        @endif
        <!-- BEGIN CONTENT -->
        <div class="page-content-wrapper">
            <div class="page-content">
                @if(isset($breadcrumbs))
                    <?php echo $breadcrumbs; ?>
                @endif
                @yield('content')
            </div>
        </div>
        <!-- END CONTENT -->
    </div>
    <!-- END CONTAINER -->
    <!-- BEGIN FOOTER -->
    <div class="page-footer">
        @yield('footer')
    </div>
    <!-- END FOOTER -->

<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->

  <script src="{{ URL::asset($theme.'/plugins/jquery.min.js')}}" type="text/javascript"></script>
  <script src="{{ URL::asset($theme.'/plugins/jquery-migrate.min.js')}}" type="text/javascript"></script>
  <script src="{{ URL::asset($theme.'/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>      
  <script src="{{ URL::asset($theme.'/js/back-to-top.js')}}" type="text/javascript"></script>
  <script src="{{ URL::asset($theme.'/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
  <!-- END CORE PLUGINS -->

 


  <script src="{{ URL::asset($theme.'/js/layout.js')}}" type="text/javascript"></script>

  <script type="text/javascript">
    jQuery(document).ready(function() {
        Layout.init();    
        Layout.initOWL();
        RevosliderInit.initRevoSlider();
        Layout.initTwitter();
        Layout.initFixHeaderWithPreHeader();
        Layout.initNavScrolling();
    });
  </script>

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->


<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function(){    
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
        QuickSidebar.init(); // init quick sidebar
        Demo.init(); // init demo features
        ComponentsPickers.init();
    });
</script>

<script>
  $('article').readmore({maxHeight: 110});
</script>
</body>
</html>

 