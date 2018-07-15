@section('content')
<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
	<div class="sm-margin"></div><!--space-->
		<h3 class="page-title-small margin-top-0"><?php echo Lang::get('user.my_address'); ?></h3>
	</div>
</div>
<?php

if(isset($errors))
{
	$error_message = function($field) use ($errors)
	{
		return "<span class='help-inline red'>".ucfirst($errors->first($field))."</span>";
	};
	
}

?>
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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.full_name'); ?> <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="fullname" value="{{$fullname}}"> 

						{!! $error_message('fullname') !!}
					
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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.street_address'); ?><span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="street" value="{{$street}}"> 
						
						{!! $error_message('street') !!}
					
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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.landmark'); ?></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="landmark" value="{{$landmark}}">
						
						{!! $error_message('landmark') !!}

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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.city'); ?><span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="city" value="{{$city}}"> 
						{!! $error_message('city') !!}

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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.country'); ?> <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<select name="country" class="chosen gallery-cat form-control" data-placeholder="Select Country" onchange="states(this.value)">
							<option value="select">Select Country</option>
							@foreach($countries as $value)
							<option value="{{$value['country_code']}}" <?php if($country == $value['country_code']) echo "selected"?>>{{$value['country_name']}}</option>
							@endforeach
		                </select>
						
						{!! $error_message('country') !!}

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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.state'); ?> <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						@if($country == 'IN')
							<select name="state" id="myStates" class="chosen gallery-cat form-control" data-placeholder="Select State">
								<option value="">Select States</option>
								@if($country == 'IN' && $states)
									@foreach($states as $value)
										<option value="{{$value}}" <?php if($state == $value) echo "selected"?>>{{$value}}</option>
									@endforeach
								@endif
			                </select>
			            @else
			            	<input type="text" id="state_text" class="form-control" name="state" value="{{$state}}">
			            @endif
						
						{!! $error_message('state') !!}

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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.pin_code'); ?> <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="pincode" value="{{$pincode}}"> 
						
						{!! $error_message('pincode') !!}

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
					<label class="col-md-4 col-sm-4 control-label"><?php echo Lang::get('user.phone_no'); ?>
					 <span class="red">*</span></label>
					<div class="col-md-6 col-sm-6">
						<input type="text" class="form-control" name="phone" value="{{$phone}}"> 
						
						{!! $error_message('phone') !!}


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
						<input type="checkbox" name="default_address" value="true" {{$checked}}> &nbsp;<?php echo Lang::get('user.make_this_default_address'); ?>
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
			if(value == 'IN')
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
						$('<select name="state" id="myStates" class="chosen gallery-cat form-control" data-placeholder="Select State"></select>').insertBefore('#state_text');
						var select = $('#myStates');
						select.empty().append('<option value="">Select State</option>');
						$.each(selectValues, function(key, value) {
							select.append($("<option></option>").attr("value",value).text(value)); 
						});
						$( "#state_text").remove();
					}
				});
			}
			else
			{
				$('<input type="text" id="state_text" class="form-control" name="state" value="">').insertBefore('#myStates');
				$( "#myStates").remove();
			}
			
		}	
	</script>
@stop