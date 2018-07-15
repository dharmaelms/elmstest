@section('content')
<?php use App\Model\User; use App\Model\CustomFields\Entity\CustomFields;?>

<style type="text/css">.accordion .panel .panel-heading {
    padding: 8px 15px;
}</style>

<link rel="stylesheet" type="text/css" href="{{ URL::to('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css') }}"/>
<script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.min.js')}}"></script>
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
		
			<h3 class="page-title-small margin-top-0"><?php echo Lang::get('user.my_profile'); ?></h3>
			<!-- <a href="{{URL::to('user/update-email/'.$user['uid'])}}"><button class="pull-right">Update Email</button></a> -->
	</div>
</div>
 	

<div class="row">
	<div class="col-md-2 col-sm-3 col-xs-12">
		<?php
  		$photo = User::getProfilePicture($user['uid']);
  		$profilePicPath = config('app.user_profile_pic').$photo;
  		$default_photo = config('app.no_profile_pic');
	?>
        	<form class="form-horizontal" method="post" class="form-horizontal form-bordered form-row-stripped" action="{{URL::to('user/profile-picture/'.$user['uid'])}}" enctype="multipart/form-data">
                <div>
                  <div class="controls">
                     <div class="fileupload fileupload-new" data-provides="fileupload">
                     <input type="hidden" value="" name="">
                        <div class="fileupload-new img-thumbnail">
                           	<?php if(!empty($photo) &&file_exists($profilePicPath) ) {?>
                				<img src="{{URL::to($profilePicPath)}}" style="width:109px; max-height: 109px;" alt="Profile Pic">
                			<?php }else{ ?>
                				<img src="{{URL::to($default_photo)}}" style="width:109px; max-height: 109px;" alt="Profile Pic">
                			<?php } ?>

                        </div>

	                    <div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 100px; max-height: 100px; line-height: 10px;"></div>
	                    <div class="fileupload fileupload-new" data-provides="fileupload">
	                     	<span class="font-10">Max. dimension to be 109px x 109px</span><br />
	                        {!! $errors->first('file', '<span class="help-inline" style="color:#f00">:message</span>') !!}
	                        <div class="input-group">
	                            <div class="input-group-btn">
	                                <a class="btn btn-file btn-default xs-margin" style="margin-right: 5px;">
	                                    <span class="fileupload-new">{{ trans('admin/banner.select_file') }}</span>
	                                    <span class="fileupload-exists">{{ trans('admin/banner.change') }}</span>
	                                    <input type="file" class="file-input" name="file"/>
	                                </a>
	                                <?php $modal = (!empty($photo) &&file_exists($profilePicPath)) ? "modal" : "" ;
		                            if (!empty($photo)) { ?>
		                                <a class="btn btn-default xs-margin" data-toggle="{{$modal}}" data-target="#delete" title="{{trans('user.remove_image')}}"><i class="fa fa-trash-o"></i></a>
		                            <?php } ?>
		                            <br>
	                                <a href="#" class="btn fileupload-exists" data-dismiss="fileupload">Remove</a>
	                                <button type="submit" class="btn btn-primary">Submit</button>
	                            </div>
	                        </div>
	                    </div>
                     </div>
                    @if(isset($from))
                    <input type="hidden" value="{{$from}}" id="from" name="from" >
                    @endif
                    </div>
                </div>
            </form>

	</div>
	<div class="col-md-8 col-sm-9 col-xs-12">
		<form class="form-horizontal" method="POST" action="{{URL::to('user/my-profile/'.$user['uid'])}}">
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.first_name'); ?> <span class="red">*</span></label>
				<div class="col-md-6 col-sm-6">
					<input type="text" class="form-control" name="firstname" <?php if(Input::old('firstname')) {?>value="{{Input::old('firstname')}}"<?php } elseif($errors->first('firstname')) {?> value="{{Input::old('firstname')}}"<?php } elseif(isset($user['firstname'])) {?> value="{{$user['firstname']}}"<?php } ?>> 
            		@if(!$errors->first('firstname'))
					@else	
					    {!! $errors->first('firstname', '<span class="help-inline red">:message</span>') !!}
					@endif
				</div>
			</div>

			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.last_name'); ?></label>
				<div class="col-md-6 col-sm-6">
					<input type="lastname" class="form-control" name="lastname" <?php if(Input::old('lastname')) {?>value="{{Input::old('lastname')}}"<?php } elseif($errors->first('lastname')) {?> value="{{Input::old('lastname')}}"<?php } elseif(isset($user['lastname'])) {?> value="{{$user['lastname']}}"<?php } ?>> 
            		@if(!$errors->first('lastname'))
					
					@else
					    {!! $errors->first('lastname', '<span class="help-inline red">:message</span>') !!}
					@endif
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.emailid'); ?> <span class="red">*</span></label>
				<div class="col-md-6 col-sm-6">
					<input type="email" class="form-control" name="email" <?php if(Input::old('email')) {?>value="{{Input::old('email')}}"<?php } elseif($errors->first('email')) {?> value="{{Input::old('email')}}"<?php } elseif(isset($user['email'])) {?> value="{{$user['email']}}"<?php } ?>> 
            		{!! $errors->first('email', '<span class="help-inline red">:message</span>') !!}
            		@if ( Session::get('email_exist') )
	                  <span class="help-inline red">{{ Session::get('email_exist') }}</span>
	                  <?php Session::forget('email_exist'); ?>
	                @endif
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.username'); ?></label>
				<div class="col-md-6 col-sm-6">
					<input type="username" class="form-control" name="username" readonly <?php if(Input::old('username')) {?>value="{{Input::old('username')}}"<?php } elseif($errors->first('username')) {?> value="{{Input::old('username')}}"<?php } elseif(isset($user['username'])) {?> value="{{$user['username']}}"<?php } ?>> 
            		{!! $errors->first('username', '<span class="help-inline red">:message</span>') !!}
            		@if ( Session::get('name_exist') )
	                  <span class="help-inline red">{{ Session::get('name_exist') }}</span>
	                  <?php Session::forget('name_exist'); ?>
	                @endif
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
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.time_zone'); ?> </label>
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
	                {!! $errors->first('timezone', '<span class="help-inline red">:message</span>') !!}
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
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.gender'); ?></label>
				<div class="col-md-6 col-sm-6">
					<select name="gender" class="chosen gallery-cat form-control" data-placeholder="None">
	                  <option value="">Select gender</option>
	                  <option value="Male" <?php if($gender == 'Male') echo "selected"?>><?php echo Lang::get('user.male'); ?><Male</option>
	                  <option value="Female" <?php if($gender == 'Female') echo "selected"?>><?php echo Lang::get('user.female'); ?><Female</option>
	                </select>
            		{!! $errors->first('gender', '<span class="help-inline red">:message</span>') !!}
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
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.dob'); ?></label>
				<div class="col-md-6 col-sm-6">
				<div class="input-group date date-picker">
                    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
						<input class="cursor-pointer form-control form-control-inline input-medium" type="text"  readonly="readonly" name="dob" id="dob" value="{{$dob}}"/>
				</div>
						{!! $errors->first('dob', '<span class="help-inline red">:message</span>') !!}
				</div>
			</div>
			<div class="form-group">
				<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.mobile_no'); ?><span class="red">*</span></label>
				<div class="col-md-6 col-sm-6">
					<input type="mobile" class="form-control" name="mobile" <?php if(Input::old('mobile')) {?>value="{{Input::old('mobile')}}"<?php } elseif($errors->first('mobile')) {?> value="{{Input::old('mobile')}}"<?php } elseif(isset($user['mobile'])) {?> value="{{$user['mobile']}}"<?php } ?>> 
            		{!! $errors->first('mobile', '<span class="help-inline red">:message</span>') !!}
				</div>
			</div>
			<!--start of custom fields-->
			@if(!empty($customFieldList) || !empty($newcustomfield))
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
				       <label class="col-md-4 col-sm-4 control-label" > <?php if(isset($record[0]['fieldlabel'])) echo $record[0]['fieldlabel']; ?>
					       @if(isset($record[0]['mark_as_mandatory']) &&  $record[0]['mark_as_mandatory']=='yes')
					       		<span class="red">*</span>
					       @endif
				       </label>
				       <div class="col-md-6 col-sm-6">
				       		<input type="text" class="form-control" @if(array_get($record, '0.edited_by_user') == "no")readonly @endif name="{{$key}}" value="{{ $val }}">
				       		{!! $errors->first($key, '<span class="help-inline red">:message</span>') !!}
				       </div>
			       </div>
		       @endforeach
		       <?php session::forget('session_arr'); ?>
        @endif
			<!--end of custom fields-->
			<div class="form-group">
				<div class="col-md-6 col-md-offset-4 col-sm-6 col-sm-offset-4">
					<button type="submit" class="btn red-sunglo xs-margin btn-sm">
						Update
					</button>
				</div>
			</div>
		</form>
	</div>

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
                                    <h3><?php echo Lang::get('user.delete_profile_pic'); ?></h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <!--content-->
                <div class="modal-body">               
                   <?php echo Lang::get('user.are_you_sure_delete_profile_pic'); ?>
                </div>
                <!--footer-->
                <div class="modal-footer">
                    <a class="btn btn-danger" href="{{ URL::to('user/delete-profile-picture/'.$user['uid'])}}"><?php echo Lang::get('user.delete'); ?></a>
                    <a class="btn btn-custom" data-dismiss="modal" aria-hidden="true"><?php echo Lang::get('user.cancel'); ?></a>

                </div>
            </div>
        </div>
    </div>
    <!-- delete profile pic ends here -->



	<script src="{{ URL::asset($theme.'/js/custom-front-end/pwstrength.js')}}"></script>

    <script type="text/javascript">
        $(document).ready(function(){
            $('#alert-success').delay(5000).fadeOut();
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