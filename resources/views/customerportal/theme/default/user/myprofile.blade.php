@section('content')
	@if(Session::get('success'))
        <span class="help-inline" style="color:green">
            <!-- <strong>Success!</strong><br> -->
            {{ Session::get('success') }}
        </span>
        <?php Session::forget('success'); ?>
    @endif

<div class="row">
	<div class="col-md-10 col-sm-12 col-xs-12">
	<div class="sm-margin"></div><!--space-->
		
			<h3 class="page-title-small margin-top-0">My Profile</h3>
			<!-- <a href="{{URL::to('user/update-email/'.$user['uid'])}}"><button class="pull-right">Update Email</button></a> -->
	</div>
</div>

<div class="row">
	<div class="col-md-10 col-sm-12 col-xs-12">
		<form class="form-horizontal" method="POST" action="{{URL::to('user/my-profile/'.$user['uid'])}}">
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">First Name <span class="red">*</span></label>
				<div class="col-md-6 col-sm-6">
					<input type="text" class="form-control" name="firstname" <?php if(Input::old('firstname')) {?>value="{{Input::old('firstname')}}"<?php } elseif($errors->first('firstname')) {?> value="{{Input::old('firstname')}}"<?php } elseif(isset($user['firstname'])) {?> value="{{$user['firstname']}}"<?php } ?>> 
            		@if(!$errors->first('firstname'))
						
					    {!! $errors->first('firstname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					@endif
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">Last Name</label>
				<div class="col-md-6 col-sm-6">
					<input type="lastname" class="form-control" name="lastname" <?php if(Input::old('lastname')) {?>value="{{Input::old('lastname')}}"<?php } elseif($errors->first('lastname')) {?> value="{{Input::old('lastname')}}"<?php } elseif(isset($user['lastname'])) {?> value="{{$user['lastname']}}"<?php } ?>> 
            		@if(!$errors->first('lastname'))
					
					@else
					    {!! $errors->first('lastname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					@endif
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">Email Id <span class="red">*</span></label>
				<div class="col-md-6 col-sm-6">
					<input type="email" class="form-control" name="email" <?php if(Input::old('email')) {?>value="{{Input::old('email')}}"<?php } elseif($errors->first('email')) {?> value="{{Input::old('email')}}"<?php } elseif(isset($user['email'])) {?> value="{{$user['email']}}"<?php } ?>> 
            		{!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
            		@if ( Session::get('email_exist') )
	                  <span class="help-inline" style="color:#f00">{{ Session::get('email_exist') }}</span>
	                  <?php Session::forget('email_exist'); ?>
	                @endif
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">Username</label>
				<div class="col-md-6 col-sm-6">
					<input type="username" class="form-control" name="username" readonly <?php if(Input::old('username')) {?>value="{{Input::old('username')}}"<?php } elseif($errors->first('username')) {?> value="{{Input::old('username')}}"<?php } elseif(isset($user['username'])) {?> value="{{$user['username']}}"<?php } ?>> 
            		{!! $errors->first('username', '<span class="help-inline" style="color:#f00">:message</span>') !!}
            		@if ( Session::get('name_exist') )
	                  <span class="help-inline" style="color:#f00">{{ Session::get('name_exist') }}</span>
	                  <?php Session::forget('name_exist'); ?>
	                @endif
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">Old Password </label>
				<div class="col-md-6 col-sm-6">
					<input type="password" class="form-control" name="old_password">
              		{!! $errors->first('old_password', '<span class="help-inline" style="color:#f00">:message</span>') !!}
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">New Password </label>
				<div class="col-md-6 col-sm-6">
					<input type="password" class="form-control" name="password">
					@if(!$errors->first('password'))
					
					@else
					    {!! $errors->first('password', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					@endif
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">Confirm Password </label>
				<div class="col-md-6 col-sm-6">
					<input type="password" class="form-control" name="password_confirmation">
                	{!! $errors->first('password_confirmation', '<span class="help-inline" style="color:#f00">:message</span>') !!}
				</div>
			</div>

			<div class="form-group">
				<?php 
	                if(Input::old('timezone'))
	                {
	                  $timezone=Input::old('timezone');
	                }
	                elseif(isset($user['timezone']))
	                {
	                  $timezone=$user['timezone'];
	                }
	                else
	                {
	                  $timezone='';
	                }
	             ?>
				<label class="col-md-4 col-sm-4 control-label">Time Zone </label>
				<div class="col-md-6 col-sm-6">
					<select name="timezone" class="chosen gallery-cat form-control" data-placeholder="Select Timezone">
						<optgroup label="Most used">
							@foreach($frequent_tz as $value)
							<option  value="{{$value}}" <?php if($timezone == $value) echo "selected"?>>{{$value}}</option>
							@endforeach  
						</optgroup>
						<optgroup label="All">
							@foreach($timezones as $value)
							<option  value="{{$value}}" <?php if($timezone == $value) echo "selected"?>>{{$value}}</option>
							@endforeach  
						</optgroup>
	                </select>
	                {!! $errors->first('timezone', '<span class="help-inline" style="color:#f00">:message</span>') !!}
				</div>
			</div>

			<div class="form-group">
				<?php 
	              if(Input::old('gender'))
	              {
	                $gender=Input::old('gender');
	              }
	              elseif(isset($user['gender']))
	              {
	                $gender=$user['gender'];
	              }
	              else
	              {
	                $gender='';
	              }
	            ?>
				<label class="col-md-4 col-sm-4 control-label">Gender </label>
				<div class="col-md-6 col-sm-6">
					<select name="gender" class="chosen gallery-cat form-control" data-placeholder="None">
	                  <option value="">Select gender</option>
	                  <option value="Male" <?php if($gender == 'Male') echo "selected"?>>Male</option>
	                  <option value="Female" <?php if($gender == 'Female') echo "selected"?>>Female</option>
	                </select>
            		{!! $errors->first('gender', '<span class="help-inline" style="color:#f00">:message</span>') !!}
				</div>
			</div>
			<div class="form-group">
				<?php 
	              if(Input::old('dob'))
	              {
	                $dob=Input::old('dob');
	              }
	              elseif(isset($user['dob']))
	              {
	                $dob=$user['dob'];
	              }
	              else
	              {
	                $dob='';
	              }
	            ?>
				<label class="col-md-4 col-sm-4 control-label">DOB </label>
				<div class="col-md-6 col-sm-6">
				<div class="input-group date">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<input class="form-control form-control-inline input-medium date-picker" type="text"  readonly="readonly" name="dob" id="dob" value="{{$dob}}"  style="cursor: pointer" />
				</div>
						{!! $errors->first('dob', '<span class="help-inline" style="color:#f00">:message</span>') !!}
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label">Mobile no.</label>
				<div class="col-md-6 col-sm-6">
					<input type="mobile" class="form-control" name="mobile" <?php if(Input::old('mobile')) {?>value="{{Input::old('mobile')}}"<?php } elseif($errors->first('mobile')) {?> value="{{Input::old('mobile')}}"<?php } elseif(isset($user['mobile'])) {?> value="{{$user['mobile']}}"<?php } ?>> 
            		{!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
				</div>
			</div>
			<div class="form-group">
				<div class="col-md-6 col-md-offset-4 col-sm-6 col-sm-offset-4">
					<button type="submit" class="btn red-sunglo xs-margin btn-sm">
						Update
					</button>
				</div>
			</div>
		</form>
	</div>
	 <script type="text/javascript">
	    	$(document).ready(function(){
	    		var nowTemp = new Date();
				var now = new Date(nowTemp.getFullYear(), nowTemp.getMonth(), nowTemp.getDate(), 0, 0, 0, 0);
	    		$('.date-picker').datepicker({
	    			format : "dd-mm-yyyy",
	    			startDate: '-100y',
	    		 	endDate: '+0d'
        // autoclose: true
	    		})
	   
	    	});
	    </script>
</div>
@stop