@section('content')
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
	<div class="sm-margin"></div><!--space-->
		<h3 class="page-title-small margin-top-0">My Address</h3>
	</div>
</div>
	<!-- New address form -->
	<div class="row">
		<div class="col-md-10 col-sm-12 col-xs-12">
			<form class="form-horizontal" method="POST" action="{{URL::to('user/my-address/'.$uid.'/'.$address_id)}}">
				<div class="form-group">
					<?php 
	                    if(Input::old('fullname'))
	                    {
	                        $fullname=Input::old('fullname');
	                    }
	                    elseif($errors->first('fullname'))
	                    {
	                        $fullname=Input::old('fullname');
	                    }
	                    elseif(isset($address['fullname']))
	                    {
	                        $fullname=$address['fullname'];
	                    }
	                    else
	                    {
	                        $fullname='';
	                    }
	                ?>
					<label class="col-md-4 col-sm-4 control-label">Full Name <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="fullname" value="{{$fullname}}"> 
						{!! $errors->first('fullname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				<div class="form-group">
					<?php 
	                    if(Input::old('street'))
	                    {
	                        $street=Input::old('street');
	                    }
	                    elseif($errors->first('street'))
	                    {
	                        $street=Input::old('street');
	                    }
	                    elseif(isset($address['fullname']))
	                    {
	                        $street=$address['street'];
	                    }
	                    else
	                    {
	                        $street='';
	                    }
	                ?>
					<label class="col-md-4 col-sm-4 control-label">Street Address<span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="street" value="{{$street}}"> 
						{!! $errors->first('street', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				<div class="form-group">
					<?php 
	                    if(Input::old('landmark'))
	                    {
	                        $landmark=Input::old('landmark');
	                    }
	                    elseif($errors->first('landmark'))
	                    {
	                        $landmark=Input::old('landmark');
	                    }
	                    elseif(isset($address['landmark']))
	                    {
	                        $landmark=$address['landmark'];
	                    }
	                    else
	                    {
	                        $landmark='';
	                    }
	                ?>
					<label class="col-md-4 col-sm-4 control-label">Landmark </label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="landmark" value="{{$landmark}}">
						{!! $errors->first('landmark', '<span class="help-inline" style="color:#f00">:message</span>') !!} 
					</div>
				</div>
				<div class="form-group">
					<?php 
	                    if(Input::old('city'))
	                    {
	                        $city=Input::old('city');
	                    }
	                    elseif($errors->first('city'))
	                    {
	                        $city=Input::old('city');
	                    }
	                    elseif(isset($address['city']))
	                    {
	                        $city=$address['city'];
	                    }
	                    else
	                    {
	                        $city='';
	                    }
	                ?>
					<label class="col-md-4 col-sm-4 control-label">City <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="city" value="{{$city}}"> 
						{!! $errors->first('city', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				<div class="form-group">
					<?php 
		                if(Input::old('country'))
		                {
		                  $country=Input::old('country');
		                }
		                elseif(isset($address['country']))
		                {
		                  $country=$address['country'];
		                }
		                else
		                {
		                  $country='IN';
		                }
		            ?>
					<label class="col-md-4 col-sm-4 control-label">Country <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<select name="country" class="chosen gallery-cat form-control" data-placeholder="Select Country" onchange="states(this.value)">
							<option value="select">Select Country</option>
							@foreach($countries as $value)
							<option value="{{$value['country_code']}}" <?php if($country == $value['country_code']) echo "selected"?>>{{$value['country_name']}}</option>
							@endforeach
		                </select>
						{!! $errors->first('country', '<span class="help-inline" style="color:#f00">:message</span>') !!} 
					</div>
				</div>
				<div class="form-group">
					<?php 
	                    if(Input::old('state'))
	                    {
	                        $state=Input::old('state');
	                    }
	                    elseif($errors->first('state'))
	                    {
	                        $state=Input::old('state');
	                    }
	                    elseif(isset($address['state']))
	                    {
	                        $state=$address['state'];
	                    }
	                    else
	                    {
	                        $state='';
	                    }
	                ?>
					<label class="col-md-4 col-sm-4 control-label">State <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<select name="state" id="myStates" class="chosen gallery-cat form-control" data-placeholder="Select State">
							<option value="">Select States</option>
							@if($country == 'IN')
								@foreach($states as $value)
									<option value="{{$value}}" <?php if($state == $value) echo "selected"?>>{{$value}}</option>
								@endforeach
							@endif
		                </select>
						{!! $errors->first('state', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				<div class="form-group">
					<?php 
	                    if(Input::old('pincode'))
	                    {
	                        $pincode=Input::old('pincode');
	                    }
	                    elseif($errors->first('pincode'))
	                    {
	                        $pincode=Input::old('pincode');
	                    }
	                    elseif(isset($address['pincode']))
	                    {
	                        $pincode=$address['pincode'];
	                    }
	                    else
	                    {
	                        $pincode='';
	                    }
	                ?>
					<label class="col-md-4 col-sm-4 control-label">Pin Code <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="pincode" value="{{$pincode}}"> 
						{!! $errors->first('pincode', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				<div class="form-group">
					<?php 
	                    if(Input::old('phone'))
	                    {
	                        $phone=Input::old('phone');
	                    }
	                    elseif($errors->first('phone'))
	                    {
	                        $phone=Input::old('phone');
	                    }
	                    elseif(isset($address['phone']))
	                    {
	                        $phone=$address['phone'];
	                    }
	                    else
	                    {
	                        $phone='';
	                    }
	                ?>
					<label class="col-md-4 col-sm-4 control-label">Phone No. </label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="phone" value="{{$phone}}"> 
						{!! $errors->first('phone', '<span class="help-inline" style="color:#f00">:message</span>') !!}
					</div>
				</div>
				<div class="form-group">
					<?php 
						if(Input::old('default_address'))
	                    {
	                        $checked = 'checked';
	                    }
	                    elseif(Auth::user()->default_address_id == $address_id)
	                    {
	                        $checked = 'checked';
	                    }
	                    elseif(!empty($address_id))
	                    {
	                    	$checked = '';
	                    }
	                    else
	                    {
	                        $checked = 'checked';
	                    }
					?>
					<label class="col-md-4 col-sm-4 control-label"></label>
					<div class="col-md-6 col-sm-6">
						<input type="checkbox" name="default_address" value="true" {{$checked}}> &nbsp;Make this as default address
					</div>
				</div>

				<div class="form-group">
					<div class="col-md-6 col-md-offset-4 col-sm-6 col-sm-offset-4">
						<button type="submit" class="btn red-sunglo xs-margin btn-sm">
							Save
						</button>
						<a class="btn btn-default btn-sm xs-margin" href="{{ URL::to('user/my-address') }}">Cancel</a>
					</div>
				</div>
			</form>
		</div>
	</div>
	<!-- New address form -->

	<script type="text/javascript">
		function states(value)
		{
			var country_code='country_code='+value;
			var value='id='+value;
			$.ajax(
			{
				type: 'GET',
                url: "{{ url('user/states') }}/",
				data:country_code,
				success: function(selectValues)
				{
					var select = $('#myStates');
					select.empty().append('<option value="">Select State</option>');
					$.each(selectValues, function(key, value) {
						select.append($("<option></option>").attr("value",value).text(value)); 
					});
				}
			});
		}	
	</script>
@stop