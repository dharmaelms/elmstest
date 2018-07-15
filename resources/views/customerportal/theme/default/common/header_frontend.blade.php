@section('header')
<?php
    use App\Model\SiteSetting;
    use App\Model\Common;
?>

<!-- BEGIN HEADER -->


<div class="container">
      <a class="site-logo" href="{{ URL::to('/') }}">
      <img src="{{ URL::to('customerportal/theme/default/img/default_logo.png') }}" alt="nbe" height="51px">
      <!--span class="hide-for-xs" style="padding-left: 40px"> OpenLink IT </span-->
      </a>

      <a href="javascript:void(0);" class="mobi-toggler"><i class="fa fa-bars"></i></a>

      <!-- BEGIN CART -->
      <?php if(!empty($is_loggedin)) { ?>
  <div class="top-cart-block">
    <div class="top-cart-info">
      <a href="#signinreg" data-toggle="modal" class="top-cart-info-count" style="text-decoration: none;">Log In</a>
    </div>
  </div>
  <?php } else {?>
  <div class="top-cart-block">
    <div class="top-cart-info">
      <ul>
        
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
          <li><a href="{{ URL::to('/') }}">Home</a></li>
          <li><a href="{{ URL::to('/about_us') }}">About</a></li>
          <li><a href="{{ URL::to('/#contact') }}">Contact Us</a></li>
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
