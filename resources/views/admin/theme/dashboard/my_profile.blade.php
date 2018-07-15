@section('content')
<?php use App\Model\User;use App\Model\CustomFields\Entity\CustomFields;?>
@if(Session::get('success'))
    <span class="alert alert-success">
        <!-- <strong>Success!</strong><br> -->
        {{ Session::get('success') }}
    </span>
    <?php Session::forget('success'); ?>
@endif
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
        <!-- <h3><i class="icon-file"></i> My Profile</h3> -->
      </div>
      	<div class="box-content row">
	      	<?php
	      		$photo = User::getProfilePicture($user['uid']);
	      		$profilePicPath = config('app.user_profile_pic').$photo;
	      		$default_photo = config('app.no_profile_pic');
	        ?>
	        <div class="col-md-2 col-sm-3 col-xs-12">
				<form class="form-horizontal" method="post" class="form-horizontal form-bordered form-row-stripped" action="{{URL::to('cp/profile-picture/'.$user['uid'])}}" enctype="multipart/form-data">
	                <div>
	                  <div class="controls">
	                     <div class="fileupload fileupload-new" data-provides="fileupload">
	                     <input type="hidden" value="" name="">
	                        <div class="fileupload-new img-thumbnail">
	                           	<?php if(!empty($photo) &&file_exists($profilePicPath) ) {?>
	                				<img src="{{URL::to($profilePicPath)}}" style="width:109px; max-height: 109px;">
	                			<?php }else{ ?>
	                				<img src="{{URL::to($default_photo)}}" style="width:109px; max-height: 109px;">
	                			<?php } ?>

	                        </div>

	                        <div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 100px; max-height: 100px; line-height: 10px;"></div>
	                        <div>
	                        <span class="font-10">Max. dimension to be 109px x 109px</span><br />
	                           <span class="btn btn-file btn-default xs-margin" ><span class="fileupload-new">Select image</span>
	                           <span class="fileupload-exists">Change</span>
	                           <input type="file" class="default" name="file"></span>
	                           <?php $modal = (!empty($photo) &&file_exists($profilePicPath)) ? "modal" : "" ; ?>
                               <a class="btn btn-default xs-margin" data-toggle="{{$modal}}" data-target="#delete" title="Remove the image"><i class="fa fa-trash-o"></i></a>
	                           <br>
	                           <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
	                           <button type="submit" class="btn btn-primary">Submit</button>
	                           
	                        </div>
	                     </div>
	                    @if(isset($from))
	                    <input type="hidden" value="{{$from}}" id="from" name="from" >
	                    @endif
	                    </div>
	                </div>
	            </form>
            </div>

            <div class="col-md-10 col-sm-9 col-xs-12">
      		<form class="form-horizontal" method="POST" class="form-horizontal form-bordered form-row-stripped" action="{{URL::to('cp/my-profile/'.$user['uid'])}}">      
      			<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">First Name <span class="red">*</span></label>
					<div class="col-sm-6 col-lg-5 controls">
						<input type="text" class="form-control" name="firstname" <?php if(Input::old('firstname')) {?>value="{{Input::old('firstname')}}"<?php } elseif($errors->first('firstname')) {?> value="{{Input::old('firstname')}}"<?php } elseif(isset($user['firstname'])) {?> value="{{$user['firstname']}}"<?php } ?>> 
                		@if(!$errors->first('firstname'))
							<!-- <span class="help-inline">Hint: Numbers & symbols are not allowed except <b> _ </b> & <b>-</b></span> -->
						@else
						    {!! $errors->first('firstname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
						@endif
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">Last Name</label>
					<div class="col-sm-6 col-lg-5 controls">
						<input type="lastname" class="form-control" name="lastname" <?php if(Input::old('lastname')) {?>value="{{Input::old('lastname')}}"<?php } elseif($errors->first('lastname')) {?> value="{{Input::old('lastname')}}"<?php } elseif(isset($user['lastname'])) {?> value="{{$user['lastname']}}"<?php } ?>> 
                		@if(!$errors->first('lastname'))
							<!-- <span class="help-inline">Hint: Numbers & symbols are not allowed except <b> _ </b> & <b>-</b></span> -->
						@else
						    {!! $errors->first('lastname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
						@endif
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">Email Id <span class="red">*</span></label>
					<div class="col-sm-6 col-lg-5 controls">
						<input type="email" class="form-control" name="email" <?php if(Input::old('email')) {?>value="{{Input::old('email')}}"<?php } elseif($errors->first('email')) {?> value="{{Input::old('email')}}"<?php } elseif(isset($user['email'])) {?> value="{{$user['email']}}"<?php } ?>> 
                		{!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                		@if ( Session::get('email_exist') )
		                  <span class="help-inline" style="color:#f00">{{ Session::get('email_exist') }}</span>
		                  <?php Session::forget('email_exist'); ?>
		                @endif
					</div>
				</div>
            	<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">Username</label>
					<div class="col-sm-6 col-lg-5 controls">
						<input type="username" class="form-control" name="username" readonly <?php if(Input::old('username')) {?>value="{{Input::old('username')}}"<?php } elseif($errors->first('username')) {?> value="{{Input::old('username')}}"<?php } elseif(isset($user['username'])) {?> value="{{$user['username']}}"<?php } ?>> 
                		{!! $errors->first('username', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                		@if ( Session::get('name_exist') )
		                  <span class="help-inline" style="color:#f00">{{ Session::get('name_exist') }}</span>
		                  <?php Session::forget('name_exist'); ?>
		                @endif
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">Old Password </label>
					<div class="col-sm-6 col-lg-5 controls">
						<input type="password" class="form-control" name="old_password">
                  		{!! $errors->first('old_password', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">New Password </label>
					<div class="col-sm-6 col-lg-5 controls">
						<input type="password" class="form-control" name="password">
						@if(!$errors->first('password'))
							<!-- <span class="help-inline">Hint: minimum length 6</span> -->
						@else
						    {!! $errors->first('password', '<span class="help-inline" style="color:#f00">:message</span>') !!}
						@endif
					</div>
				</div>

				<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">Confirm Password </label>
					<div class="col-sm-6 col-lg-5 controls">
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
					<label class="col-sm-4 col-lg-3 control-label">Time Zone </label>
					<div class="col-sm-6 col-lg-5 controls">
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
					<label class="col-sm-4 col-lg-3 control-label">Gender </label>
					<div class="col-sm-6 col-lg-5 controls">
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
					<label class="col-sm-4 col-lg-3 control-label">DOB </label>
					<div class="col-sm-6 col-lg-5 controls">

					<div class="input-group date date-picker">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<input class="form-control form-control-inline input-medium" size="16" type="text" readonly="readonly" name="dob" id="dob" value="{{ $dob}}"  style="cursor: pointer" />
					</div>

							{!! $errors->first('dob', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-4 col-lg-3 control-label">Mobile no.</label>
					<div class="col-sm-6 col-lg-5 controls">
						<input type="mobile" class="form-control" name="mobile" <?php if(Input::old('mobile')) {?>value="{{Input::old('mobile')}}"<?php } elseif($errors->first('mobile')) {?> value="{{Input::old('mobile')}}"<?php } elseif(isset($user['mobile'])) {?> value="{{$user['mobile']}}"<?php } ?>> 
                		{!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				   <!-- custom fields tab -->
        @if(!empty($customFieldList) || !empty($newcustomfield))
        <div class="form-group">
            <div id="collapseOne" class="panel-collapse collapse in">
                <div class="panel-body">
                        <!-- dispalying text boxes -->                      
                         @foreach($customFieldList as $key => $val)
	                         <?php
	                         $record = CustomFields::getCustomField($key,'userfields');
	                         	if(in_array($key,$session_arr))
				                {
				                    $val = "";
				                }
	                         ?>
	                         <div class="form-group">
		                        <label class="col-sm-4 col-lg-3 control-label" > <?php if(isset($record[0]['fieldlabel'])) echo $record[0]['fieldlabel']; ?>
		                         	@if(isset($record[0]['mark_as_mandatory']) && $record[0]['mark_as_mandatory']=='yes')
		                         		<span class="red">*</span>
		                         	@endif
		                        </label>
		                        <div class="col-sm-6 col-lg-5 controls">
		                         	<input type="text" class="form-control"  name="{{ $key }}" value="{{ $val }}"> 
		                         	{!! $errors->first($key, '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                        </div>
	                         </div>
                         @endforeach
                         <?php session::forget('session_arr'); ?>
                         <!-- displaying text boxes ends here -->
                </div>
            </div>
        </div>
        @endif
        <!-- ends here -->
	            <div class="form-group last">
	                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
	                    <input type="submit" class="btn btn-info" value="Update">
	                    <a class="btn" href="{{ URL::to('cp/dashboard') }}">Cancel</a>
	                </div>
	            </div>
	        </form>
	    </div>
        </div>
    </div>
  </div><!--Register-->


  <!-- delete profile pic -->
	<div id="delete" class="modal fade delete">
        <div class="modal-dialog">
            <div class="modal-content">
                <!--header-->
                <div class="modal-header">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="box">
                               <div class="box-title">
                                   <button type="button" class="close " data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3>Delete Profile Picture</h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--content-->
                <div class="modal-body">               
                    Are you sure you want to delete the profile picture?
                </div>
                <!--footer-->
                <div class="modal-footer">
                    <a class="btn btn-danger" href="{{ URL::to('cp/delete-profile-picture/'.$user['uid'])}}">Delete</a>
                    <a class="btn btn-custom" data-dismiss="modal" aria-hidden="true">Cancel</a>

                </div>
            </div>
        </div>
    </div>
    <!-- delete profile pic ends here -->
    
    <script type="text/javascript">
	    	 $(document).ready(function() {
          
			$('.date-picker').datepicker({
			   	format : "dd-mm-yyyy",
			   	 startDate: '-100y',
			   		endDate: '+0d'
			//    	onRender: function(date) {
			//   //   $('.switch').unbind('click').click(function(e){
			// 		// e.stopImmediatePropagation()
			// 		// })
			// 		return date.valueOf() > now.valueOf() ? 'disabled' : '';
			// 	}

			})
            });
  </script>
</div>
</html>
@stop
