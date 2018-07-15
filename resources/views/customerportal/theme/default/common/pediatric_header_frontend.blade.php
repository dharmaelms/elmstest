@section('header')
<?php
    use App\Model\Common;
?>

<!-- BEGIN HEADER -->


    <div class="container">
      <a class="site-logo" href="{{ URL::to('/') }}">
      <img src="{{ URL::to('customerportal/theme/default/img/default_logo.png') }}" alt="nbe" height="94px">
      <span class="hide-for-xs">National Board Of Examinations</span>
      </a>

      <a href="javascript:void(0);" class="mobi-toggler"><i class="fa fa-bars"></i></a>

      <!-- BEGIN CART -->
      <?php if(!empty($is_loggedin)) { ?>
  <div class="top-cart-block">
    <div class="top-cart-info">
      <a href="#signinreg" data-toggle="modal" class="top-cart-info-count">Log In</a>
    </div>
  </div>
  <?php } else {?>
  <div class="top-cart-block">
    <div class="top-cart-info">
      <ul>
        <li class="dropdown dropdown-user  pull-right">
          <a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown" data-close-others="true">
            <img alt="cart" class="img-circle" src="{{ URL::to('customerportal/theme/default/img/avatar.png') }}" height="32"/>
            <span class="username username-hide-on-mobile p-0">&nbsp;{{Auth::user()->firstname}} {{Auth::user()->lastname}}</span>
            <i class="fa fa-angle-down"></i>
          </a>
          <ul class="dropdown-menu dropdown-menu-default pull-right">
              @if(has_admin_portal_access())
                <li>
                  <a id="user" href="{{URL::to('cp')}}" class="signin-dropdown-link">
                  <i class="fa fa-user-secret"></i> Admin View </a>
                </li>
              @endif
              <li>
                <a href="{{URL::to('/dashboard')}}" class="signin-dropdown-link">
                <i class="fa fa-dashboard"></i>&nbsp;&nbsp;My Dashboard </a>
              </li>
              <li>
                <a href="{{URL::to('user/my-profile')}}" class="signin-dropdown-link">
                <i class="fa fa-user"></i>&nbsp;&nbsp;My Profile </a>
              </li>
              <li>
                <a href="{{URL::to('user/my-address')}}" class="signin-dropdown-link">
                  <i class="fa fa-map-marker"></i>&nbsp;&nbsp;My Address </a>
              </li>
                <!-- <li>
                  <a href="{{URL::to('/ord/list-order')}}" class="signin-dropdown-link">
                    <i class="fa fa-shopping-cart"></i>&nbsp;&nbsp;My Orders </a>
                </li> -->
                <li>
                  <a href="{{URL::to('auth/logout')}}" class="signin-dropdown-link">
                    <i class="fa fa-key"></i>&nbsp;&nbsp;Sign Out </a>
                </li>
          </ul>
      </li>
      </ul>
    </div>
  </div>
  <?php } ?>
      <!--END CART -->

      <!-- BEGIN NAVIGATION -->
      <div class="header-navigation">
        <ul>
          <li><a href="{{ URL::to('#') }}">Home</a></li>
          <!-- <li><a href="{{ URL::to('about-us') }}">About Us</a></li> -->
          <li><a href="{{ URL::to('/pediatric/#contact') }}">Contact Us</a></li>
        </ul>
      </div>
      <!-- END NAVIGATION -->
    </div>

<!-- Header END -->

<script>
  $(function(){
    // this will get the full URL at the address bar
    var url = window.location.href;

    // passes on every "a" tag
    $(".header-navigation a").each(function() {
            // checks if its the same on the address bar
        if(url == (this.href)) {
            $(this).closest("li").addClass("active");
        }
    });
});
</script>

@stop
