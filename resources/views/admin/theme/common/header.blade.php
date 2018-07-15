@section('header')
	<!-- BEGIN Navbar -->
	<div id="navbar" class="navbar">
	    <button type="button" class="navbar-toggle navbar-btn collapsed" data-toggle="collapse" data-target="#sidebar">
	        <span class="fa fa-bars"></span>
	    </button>
	    <a class="navbar-brand" href="{{ url('/') }}">
	        <small>
	            <i class="fa fa-desktop"></i>
	            {{ config('app.site_name', 'Ultron Admin') }}
	        </small>
	    </a>
	    <!-- BEGIN Navbar Buttons -->
	    <ul class="nav flaty-nav pull-right">
	        <!-- BEGIN Button User -->
	        <li class="user-profile">
	            <a data-toggle="dropdown" href="#" class="user-menu dropdown-toggle">
	                <!-- <img class="nav-user-photo" src="img/demo/avatar/avatar1.jpg" alt="Penny's Photo" /> -->
	                <span class="hhh" id="user_info">
	                    {{Auth::user()->firstname}} {{Auth::user()->lastname}}
	                </span>
	                <i class="fa fa-caret-down"></i>
	            </a>
	            <!-- BEGIN User Dropdown -->
	            <ul class="dropdown-menu dropdown-navbar" id="user_menu">
	                <!-- <li class="nav-header">
	                    <i class="fa fa-clock-o"></i>
	                    Logined From 20:45
	                </li>
					-->
					<li>
						<a id="user" href="{{URL::to('/dashboard')}}">
							<i class="fa fa-laptop"></i>
							User View
						</a>
					</li>
					<li>
						<a href="{{URL::to('/cp/my-profile')}}">
							<i class="fa fa-user"></i>
							My Profile
						</a>
					</li>
	                <li>
	                    <a href="{{URL::to('/auth/logout')}}">
	                        <i class="fa fa-key"></i>
	                        Sign Out
	                    </a>
	                </li>
	            </ul>
	            <!-- BEGIN User Dropdown -->
	        </li>
	        <!-- END Button User -->
	    </ul>
	    <!-- END Navbar Buttons -->
	</div>
	<!-- END Navbar -->

<script type="text/javascript">
	$("#user").click(function() {
	$("#user").attr('target', '_blank');
	return true;
	});
</script>
@stop