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
  .form-horizontal .control-label{
  	text-align: left;
  }
</style>
<div class="row">
    <div class="col-md-offset-3 col-md-6 col-sm-12 col-xs-12 white-bg">
        <div class="portlet box register-panel" style="min-height:510px !important">
            <div class="col-md-12 reg-title">
                REGISTER
            </div>
            <div class="clearfix"></div>
		  	@if(Session::get('success'))
				<div class="alert alert-success">
					<!-- <strong>Success!</strong><br> -->
					{{ Session::get('success') }}
				</div>
				<?php Session::forget('success'); ?>
			@endif
            <div class="portlet-body">
                <form class="form-horizontal" method="POST" action="/auth/register">
                  	<div class="sm-margin"></div>
                        <div class="form-group">
                    		<label class="col-md-3 control-label padding-rgt-0">Username <span class="star-mark">*</span></label>
							<div class="col-md-9">
								<input type="username" class="form-control" name="username" value="{{ Input::old('username') }}"> 
		                		@if(!$errors->first('username'))
									
								@else
								    {!! $errors->first('username', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								@endif
							</div>
                        </div>
                    	<div class="form-group">
                      		<label class="col-md-3 control-label padding-rgt-0">Password <span class="star-mark">*</span></label>
							<div class="col-md-9">
								<input type="password" class="form-control" name="password">
		                  		@if(!$errors->first('password'))
									
								@else
								    {!! $errors->first('password', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								@endif
							</div>
                    	</div>
                    	<div class="form-group">
							<label class="col-md-3 control-label padding-rgt-0 padding-top-0">Confirm Password <span class="star-mark">*</span></label>
							<div class="col-md-9">
								<input type="password" class="form-control" name="password_confirmation">
		                    	{!! $errors->first('password_confirmation', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label padding-rgt-0">First Name <span class="star-mark">*</span></label>
							<div class="col-md-9">
								<input type="text" class="form-control" name="firstname" value="{{ Input::old('firstname') }}"> 
		                		@if(!$errors->first('firstname'))
								
								@else
								    {!! $errors->first('firstname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								@endif
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label padding-rgt-0">Last Name</label>
							<div class="col-md-9">
								<input type="lastname" class="form-control" name="lastname" value="{{ Input::old('lastname') }}"> 
		                		@if(!$errors->first('lastname'))
									
								@else
								    {!! $errors->first('lastname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								@endif
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label padding-rgt-0">Email Id <span class="star-mark">*</span></label>
							<div class="col-md-9">
								<input type="email" class="form-control" name="email" value="{{ Input::old('email') }}"> 
		                		{!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<label class="col-md-3 control-label padding-rgt-0">Mobile No.</label>
							<div class="col-md-9">
								<input type="mobile" class="form-control" name="mobile" value="{{ Input::old('mobile') }}"> 
		                		{!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<!-- <div class="form-group">
							<?php 
				                if(Input::old('timezone'))
				                {
				                  $timezone=Input::old('timezone');
				                }
				                else
				                {
				                  $timezone='';
				                }
				             ?>
							<label class="col-md-3 control-label padding-rgt-0">Time Zone </label>
							<div class="col-md-9">
								<select name="timezone" class="chosen gallery-cat form-control" data-placeholder="Select Timezone">
									<option value="">Select Time Zone </option>
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
						</div> -->
                    	
                      	<button type="submit" class="btn red-sunglo pull-right md-margin">REGISTER</button>
                    	
                </form>
            </div>
        </div>
    </div>
</div>  
@stop
