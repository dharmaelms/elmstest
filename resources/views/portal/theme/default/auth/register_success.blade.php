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

<div class="redirect-page center white">
	<div class="row">
		<div class="col-md-12 margin-100">
			<h1 class="font-weight-500">Thank you for signing up!</h1>
			<p class="font-16 font-weight-500 md-margin">Before you can continue, you must verify your email address.</p>

			<div class="white-trans-bg lg-margin">
				<h4 class="font-weight-600 xs-margin">A confirmation email has been sent to {{Request::get('email')}}</h4>
				<h4 class="font-weight-600">Click on the confirmation link in the email to activate your account.</h4>
			</div>
		</div>
	</div>
</div>
@stop
