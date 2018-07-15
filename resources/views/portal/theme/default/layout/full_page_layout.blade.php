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
<style type="text/css">
    .fc-calendar .fc-row > div, .fc-calendar .fc-head > div{
        width: 14.28% !important;
    }
</style>
<!-- END THEME STYLES -->
<link rel="shortcut icon" href="{{ URL::asset($theme.'/img/favicon.ico')}}"/>
<!-- BEGIN JQUERY -->
<script src="{{ URL::asset($theme.'/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/jquery-migrate.min.js')}}" type="text/javascript"></script>
<!-- END JQUERY -->
<body class="page-header-fixed page-quick-sidebar-over-content">
    <!-- BEGIN HEADER -->
        @yield('header')
    <!-- END HEADER -->
    <div class="clearfix"></div>
    <!-- BEGIN CONTAINER -->
    <div class="page-content">
        @yield('content')
    </div>
    <!-- END CONTAINER -->

<!-- IMPORTANT! Load jquery-ui.min.js before bootstrap.min.js to fix bootstrap tooltip conflict with jquery ui tooltip -->
<script src="{{ URL::asset($theme.'/plugins/jquery-ui/jquery-ui.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/bootstrap/js/bootstrap.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/bootstrap-hover-dropdown/bootstrap-hover-dropdown.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/jquery-slimscroll/jquery.slimscroll.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/jquery.blockui.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/jquery.cokie.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/uniform/jquery.uniform.min.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/bootstrap-switch/js/bootstrap-switch.min.js')}}" type="text/javascript"></script>
<!-- END CORE PLUGINS -->
<script src="{{ URL::asset($theme.'/js/metronic.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/js/layout.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/js/quick-sidebar.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/js/demo.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/js/components-pickers.js')}}" type="text/javascript"></script>

<script src="{{ URL::asset($theme.'/plugins/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}" type="text/javascript"></script>

<!-- BEGIN PAGE LEVEL PLUGINS-tabs drop -->
<script src="{{ URL::asset($theme.'/plugins/bootstrap-tabdrop/js/bootstrap-tabdrop.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/responsive-tabs/js/easyResponsiveTabs.js')}}"></script>
<script src="{{ URL::asset($theme.'/js/ui-general.js')}}" type="text/javascript"></script>
<script src="{{ URL::asset($theme.'/plugins/jquery-nestable/jquery.nestable.js')}}"></script>
<script src="{{ URL::asset($theme.'/js/ui-nestable.js')}}"></script>
<script src="{{ URL::asset($theme.'/js/readmore.js')}}"></script>

<!-- BEGIN PAGE LEVEL JAVASCRIPTS (REQUIRED ONLY FOR CURRENT PAGE) -->


<!-- END PAGE LEVEL SCRIPTS -->
<script>
    jQuery(document).ready(function(){    
        Metronic.init(); // init metronic core components
        Layout.init(); // init current layout
        QuickSidebar.init(); // init quick sidebar
        Demo.init(); // init demo features
        ComponentsPickers.init();
        //reloading the parent window before closing the window
    });
    var w = 880, h = 600,
        left = Number((screen.width/2)-(w/2)), tops = Number((screen.height/2)-(h/2)),
        params = [
            'height='+h,
            'width='+w,
            'resizable=1', 
            'scrollbars=1', 
            'left='+left,
            'top='+tops,   
        ].join(',');
    function lswindow(url, name, options){
        window.open(url, name, params+','+options);
    }
    var closeWindow = function(){
        reloadWindow();
        window.close();
    }
    var reloadWindow = function(){
        window.opener.location.reload(true);
    }   
    window.onunload = refreshParent;
    function refreshParent() {
        var loc = window.opener.location;
        window.opener.location = loc;
        //window.opener.location.reload();
    }
</script>
<script>
    $('article').readmore({maxHeight: 110});
    $('#demo article').readmore({maxHeight: 56});
</script>
<?php if (config('app.ganalytic.key')) { ?>
        <script>
              (function(i,s,o,g,r,a,m){i['GoogleAnalyticsObject']=r;i[r]=i[r]||function(){
              (i[r].q=i[r].q||[]).push(arguments)},i[r].l=1*new Date();a=s.createElement(o),
              m=s.getElementsByTagName(o)[0];a.async=1;a.src=g;m.parentNode.insertBefore(a,m)
              })(window,document,'script','//www.google-analytics.com/analytics.js','ga');
              ga('create','<?php echo config('app.ganalytic.key'); ?>','auto');
              ga('send', 'pageview');
        </script>
<?php } ?>
</body>
</html>