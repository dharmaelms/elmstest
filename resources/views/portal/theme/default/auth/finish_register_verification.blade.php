@section('content')

<style>
	.page-content-wrapper .page-content { margin-left: 0;padding: 0;background: #36B9D9; }
	.redirect-page { background-image: url(portal/theme/default/img/redirecting-page.png);background-position: center;padding: 20px; }
	.redirect-page h1 { font-size:3.6em; }
	.white-trans-bg { background-color: rgba(119, 119, 119, 0.2);
    border-radius: 10px !important;
    display: inline-block;
    padding: 12px 24px 20px; }
	.font-weight-600 { font-weight: 600 !important; }
		.redirect-page .margin-100 { margin:100px 0 158px; }

	@media (max-width: 767px ) {
		.redirect-page h1 { font-size:2.6em;}
	}
</style>
@if(true == $email_verify_status)

	<div class="redirect-page center white">
		<div class="row">
			<div class="col-md-12 margin-100">
				<h1 class="font-weight-500">Email verification successful!</h1>
				<p class="font-16 font-weight-500 md-margin">You will be redirected in <span id="timer" name="timer"></span> secs. <img src="../portal/theme/default/img/redirecting-icon.png" alt="redirecting-icon"></p>

				<div class="lg-margin">
					<h4 class="font-weight-600 xs-margin"><a href="javascript:submit();" class="white" style="text-decoration:underline;">Click here if you are not redirected automatically.</a></h4>
				</div>
			</div>
		</div>
	</div>


	<form action="{{URL::to('auth/auto-login')}}" id="auto_login" name="auto_login" class="hide">
		<input type="hidden" id="security_key" name="security_key" value="{{Session::get('authentication.security_key')}}">
		<input type="submit" value="Login">
	</form>

@else

	<div class="redirect-page center white">
		<div class="row">
			<div class="col-md-12 margin-100">
				<h1 class="font-weight-500">Email verification is already done. </h1>
				<p class="font-16 font-weight-500 md-margin">You will be redirected in <span id="timer" name="timer"></span> secs to login. <img src="../portal/theme/default/img/redirecting-icon.png" alt="redirecting-icon"></p>

				<div class="lg-margin">
					<h4 class="font-weight-600 xs-margin"><a href="{{URL::to('/')}}" class="white" style="text-decoration:underline;">Click here if you are not redirected automatically.</a></h4>
				</div>

				<form action="{{URL::to('/')}}" id="auto_login" name="auto_login" class="hide">
					
				</form>
			
			</div>
		</div>
	</div>

@endif

	<script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.plugin.js') }}"></script>
    
    <script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.countdown.js') }}"></script>
	
	<script type="text/javascript">

	  $(function () {
	    $('#timer').countdown({
	      until: +10,
	      onExpiry:submit,
	      compact: true,
	      layout: '{snn}'
	    });
	  });
                  
      function submit(){

      	$('#auto_login').submit();
      
      }

    </script>
@stop