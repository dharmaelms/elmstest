@section('content')
<style type="text/css">.accordion .panel .panel-heading {
    padding: 8px 15px;
}</style>

	@if(Session::get('success'))
        <div class="alert alert-success" id="alert-success">
    	<button class="close" data-dismiss="alert">×</button>
            {{ Session::get('success') }}
        </div>
        <?php Session::forget('success'); ?>
    @endif
    
    <div class="row">
		<div class="col-md-10 col-sm-12 col-xs-12">
			<div class="sm-margin"></div><!--space-->
			<h3 class="page-title-small margin-top-0"><?php echo Lang::get('user.change_password'); ?></h3>
		</div>
	</div>

	<form class="form-horizontal" method="POST" action="{{URL::to('user/change-password/'.$user['uid'])}}">
		<div class="row">
			<div class="col-md-8 col-sm-9 col-xs-12">
				<?php if((is_array($user) && isset($user['created_by']) ) && $user['created_by'] == 'facebook' || $user['created_by'] == 'google'){

					}else{ ?>

				<div class="form-group">
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.old_password'); ?> </label>
					<div class="col-md-6 col-sm-6">
						<input type="password" class="form-control" name="old_password">
			      		{!! $errors->first('old_password', '<span class="help-inline red">:message</span>') !!}
					</div>
				</div>
				<?php } ?>

				<div class="form-group">
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.new_password'); ?> </label>
					<div class="col-md-6 col-sm-6">
						<input type="password" class="form-control" name="password" id="reg_password" autocomplete="off">
						<div id="passwordDescription">Password Strength: </div>
		                <div class="passwordStrength-bg"><div id="passwordStrength" class="strength0"></div></div>
						@if(!$errors->first('password'))
						
						@else
						    {!! $errors->first('password', '<span class="help-inline red">:message</span>') !!}
						@endif
					</div>
				</div>


				<div class="form-group">
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.confirm_password'); ?> </label>
					<div class="col-md-6 col-sm-6">
						<input type="password" class="form-control" name="password_confirmation">
	                	{!! $errors->first('password_confirmation', '<span class="help-inline red">:message</span>') !!}
					</div>
				</div>
				
				<div class="form-group">
					<div class="col-md-6 col-md-offset-4 col-sm-6 col-sm-offset-4">
						<button type="submit" class="btn red-sunglo xs-margin btn-sm">
							<?php echo Lang::get('user.update'); ?>
						</button>
					</div>
				</div>

			</div>
		</div>
	</form>
    <script type="text/javascript">
        $(document).ready(function(){
            $('#alert-success').delay(5000).fadeOut();
        });
    </script>
@stop