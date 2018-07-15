<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge,chrome=1">
        <title>{{ $pagetitle }}</title>
        <meta name="description" content="">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">

        <!-- Place favicon.ico and apple-touch-icon.png in the root directory -->

        <!--base css styles-->
        <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap/css/bootstrap.min.css')}}">
        <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-datepicker/css/bootstrap-datepicker.min.css')}}">
        <link rel="stylesheet" href="{{ URL::asset('admin/assets/font-awesome/css/font-awesome.min.css')}}">

        <!--page specific css styles-->

        <!--flaty css styles-->
        <link rel="stylesheet" href="{{ URL::asset('admin/css/flaty.css')}}">
        <link rel="stylesheet" href="{{ URL::asset('admin/css/flaty-responsive.css')}}">
        <link rel="stylesheet" href="{{ URL::asset('admin/assets/dropzone/downloads/css/basic.css')}}">
        <link rel="stylesheet" href="{{ URL::asset('admin/assets/dropzone/downloads/css/dropzone.css')}}">
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css') }}" />
        <link rel="stylesheet" type="text/css" href="{{ URL::asset('admin/css/customcss.css')}}?version=0.0.1" />


        <link rel="shortcut icon" href="{{ URL::asset('admin/img/favicon.ico')}}">

        <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
        <script>window.jQuery || document.write('<script src="{{ URL::asset("admin/assets/jquery/jquery-2.1.1.min.js")}}"><\/script>')</script>
    </head>
    <body class="skin">
        @yield('header')
        <div class="container" id="main-container">
            @yield('sidebar')
			<div id="main-content">
				@if(isset($breadcrumbs))
					<div class="adminbreadcrumb">
						<?php echo $breadcrumbs; ?>
					</div>
				@endif
				<!-- End Breadcrumbs -->
				<!-- BEGIN Page Title -->
                @if($pagetitle != null)
				<div class="page-title">
					<div>
						@if(isset($pageimage)) 
							<h1><img src="{{{ URL::to($pageimage) }}}" style="color:#000"> @if($pagetitle != "Error"){{ $pagetitle }}@endif</h1>
						@else
							<h1><i class="{{ $pageicon }}"></i> @if($pagetitle!="Error"){{ $pagetitle }}@endif</h1>
						@endif
					</div>
				</div>
                @endif
				<!-- END Page Title -->

				<!-- BEGIN Breadcrumb -->
				@yield('breadcrumb')
				<!-- END Breadcrumb -->

				<!-- BEGIN Main Content -->
                <!-- @if($pagetitle == null)
                <div align="right">
                    <a class="btn btn-primary">Assign</a>&nbsp;&nbsp;&nbsp;&nbsp;
                </div>
                @endif -->
				@yield('content')
				<!-- END Main Content -->

				<!-- BEGIN Footer -->
				@yield('footer')
				<!-- END Footer -->

				<a id="btn-scrollup" class="btn btn-circle btn-lg" href="#"><i class="fa fa-arrow-up"></i></a>
			</div>
        </div>
        <script src="{{ URL::asset('admin/assets/bootstrap/js/bootstrap.min.js')}}"></script>
         <script src="{{ URL::asset('admin/assets/bootstrap-datepicker/js/bootstrap-datepicker.min.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/jquery-slimscroll/jquery.slimscroll.min.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/jquery-cookie/jquery.cookie.js')}}"></script>

        <!--page specific plugin scripts-->

		<script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.min.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/flot/jquery.flot.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/flot/jquery.flot.resize.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/flot/jquery.flot.pie.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/flot/jquery.flot.stack.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/flot/jquery.flot.crosshair.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/flot/jquery.flot.tooltip.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/dropzone/downloads/dropzone.min.js')}}"></script>
        <script src="{{ URL::asset('admin/assets/sparkline/jquery.sparkline.min.js')}}"></script>

        <!--flaty scripts-->
        <script src="{{ URL::asset('admin/js/flaty.js')}}"></script>
        <script src="{{ URL::asset('admin/js/flaty-demo-codes.js')}}"></script>

        <script src="{{ URL::asset('admin/js/footerjs.js?version=2.0.4')}}"></script>

    </body>
</html>
