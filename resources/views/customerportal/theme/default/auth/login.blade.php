@section('content')

<style type="text/css">
  	.page-content-wrapper{
		width: 100%;
		margin:0px;
  	}
  	.page-content-wrapper .page-content{
		margin-left: 0px !important;
		background-color: transparent;
		padding-bottom: 0px !important;
  	}
</style>

<div class="row">
	<div class="col-md-offset-3 col-md-6 col-sm-12 col-xs-12 white-bg">
		<div class="portlet box register-panel" style="min-height:510px !important">
		  	<div class="col-md-12 reg-title">
				USER 
		  	</div>
		  	<div class="clearfix"></div>
		  	@if(Session::get('success'))
				<div class="alert alert-success">
					<!-- <strong>Success!</strong><br> -->
					{{ Session::get('success') }}
				</div>
				<?php Session::forget('success'); ?>
			@endif
		  	@if(Session::get('error'))
                <div class="alert alert-danger">
                    {{ Session::get('error') }}
                </div>
                <?php Session::forget('error'); ?>
            @endif
		  	<div class="portlet-body">
			  	<form class="form-horizontal" method="POST" action="{{URL::to('auth/login')}}">
					<div class="sm-margin"></div>
						<div class="form-group">
						  	<div class="col-md-12">
								<input type="text" class="form-control" name="email" placeholder="Email/Username"/>
						  	</div>
						</div>
						<div class="form-group">
						  	<div class="col-md-12">
								<input type="password" class="form-control" name="password" placeholder="Password"/>
						  	</div>
						</div>
						<div class="col-md-6 col-sm-6 col-xs-12 padding-lft-0">
						  	<div class="checkbox">
								@if(Common::checkPermission('portal', 'user', 'forgot-password') == true)
						        	<label class="margin-top-10"><a href="{{URL::to('/password/forgot')}}">Forgot Your Password?</a></label>
						    	@endif
						  	</div>
						</div>
						<div class="col-md-6 col-sm-6 col-xs-12 padding-lft-0">
							<button type="submit" class="btn red-sunglo pull-right">Sign In</button>
						</div>
				</form>
				<div class="col-md-12">				
						@include('socialite.socialite')
						</div>
		  	</div>
		</div>
	</div>
</div>
@stop