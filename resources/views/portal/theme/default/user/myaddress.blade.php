@section('content')
<?php use App\Model\Country; ?>

<style type="text/css">
.address-box {
    background: #fcfcfc none repeat scroll 0 0;
    border: 1px solid #dddddd;
    border-radius: 4px;
    height: 208px;
    margin-bottom: 20px;
    padding: 10px 10px 5px;
}
.address-box address {
    margin-bottom: 12px;
}
.address-box .label-success {
    background-color: #297076;
    font-weight: normal;
}
.addr-actions {
    bottom: 20px;
    position: absolute;
}
@media (max-width: 399px) {
	.address-div .col-xs-6 {
	    width: 100% !important;
	}
}
</style>

<div class="row">
	<div class="col-md-12 col-sm-12 col-xs-12">
	<div class="sm-margin"></div><!--space-->
		<h3 class="page-title-small margin-top-0"><?php echo Lang::get('user.my_address'); ?></h3>
	</div>
</div>
@if(Session::get('success'))
    <div class="alert alert-success" id="alert-success">
    <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('success') }}
    </div>
    <?php Session::forget('success'); ?>
@endif
@if(Session::get('error'))
    <span class="help-inline red">
        <!-- <strong>Success!</strong><br> -->
        {{ Session::get('error') }}
    </span>
    <?php Session::forget('error'); ?>
@endif
	<!-- list of addresses -->
	<div class="row address-div">
		<?php 
			$key = array_search(Auth::user()->default_address_id, array_column($addresses, 'address_id'));
			if(isset($key) && is_int($key))
			{
				$default_address = $addresses[$key];
			}
		?>
		@if(isset($default_address) && !empty($default_address))
			<div class="col-md-4 col-sm-4 col-xs-6">
				<div class="address-box">
					<p><span class="label label-success" style="float:left"><?php echo Lang::get('user.default_address'); ?></span></p><br>
					<address class="scroller" style="height: 130px;padding-right:8px;" data-always-visible="1" data-rail-visible1="1">{{$default_address['fullname']}}<br>
					{{$default_address['street']}},@if(isset($default_address['landmark']) && !empty($default_address['landmark'])) {{$default_address['landmark']}},@endif<br>
					{{$default_address['city']}} <br>
					<?php echo Country::getCountry($default_address['country']); ?> <br>
					{{$default_address['state']}} <br>
					{{$default_address['pincode']}} <br>
					@if(isset($default_address['phone']) && !empty($default_address['phone']))P: {{$default_address['phone']}}@endif</address>
					<p class="addr-actions"><a href="{{URL::to('user/my-address?edit_address=true&address_id='.$default_address['address_id'])}}" title="Edit Address"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;
					<a class="deleteaddress" href="{{URL::to('user/delete-address/'.$uid.'/'.$default_address['address_id'])}}" title="Delete Address"><i class="fa fa-trash"></i></a></p>
				</div>
			</div>
		@endif
		@foreach($addresses as $address)
			@if(Auth::user()->default_address_id != $address['address_id'])
				<div class="col-md-4 col-sm-4 col-xs-6">
					<div class="address-box">
						<address class="scroller" style="height: 154px;padding-right:8px;" data-always-visible="1" data-rail-visible1="1">{{$address['fullname']}}<br>
						{{$address['street']}},@if(isset($address['landmark']) && !empty($address['landmark'])) {{$address['landmark']}},@endif<br>
						{{$address['city']}} <br>
						<?php echo Country::getCountry($address['country']); ?> <br>
						{{$address['state']}} <br>
						{{$address['pincode']}} <br>
						@if(isset($address['phone']) && !empty($address['phone']))P: {{$address['phone']}}@endif</address>
						<p class="addr-actions"><a href="{{URL::to('user/my-address?edit_address=true&address_id='.$address['address_id'])}}" title="Edit Address"><i class="fa fa-edit"></i></a>&nbsp;&nbsp;
						<a class="deleteaddress" href="{{URL::to('user/delete-address/'.$uid.'/'.$address['address_id'])}}" title="Delete Address"><i class="fa fa-trash"></i></a></p>
					</div>
				</div>
			@endif
		@endforeach
	</div>
	<div class="row">
		<div class="col-md-12">
			<hr>
			<div class="md-margin"></div>
			<center><p><a href="{{URL::to('user/my-address?new_address=true')}}" class="btn red-sunglo xs-margin btn-sm"><?php echo Lang::get('user.add_new_address'); ?></a></p></center>
		</div>
	</div>
	<!-- list of addresses -->
		<div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3 class="modal-header-title">
	                                    <i class="icon-file"></i>
	                                       <?php echo Lang::get('user.my_address_delete'); ?>
	                                </h3>                                                
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <div class="modal-body padding-20">
	                <?php echo Lang::get('user.are_you_sure_delete_address'); ?>
	            </div>
	            <div class="modal-footer">
	                <a class="btn btn-success"><i class="fa fa-check"></i> <?php echo Lang::get('user.yes'); ?></a>
	                <a class="btn btn-danger" data-dismiss="modal"><i class="fa fa-remove"></i> <?php echo Lang::get('user.close'); ?></a>
	            </div>
	        </div>
	    </div>
	</div>
	<script type="text/javascript">
		$(document).on('click','.deleteaddress',function(e){
            e.preventDefault();
            var $this = $(this);
            var $deletemodal = $('#deletemodal');
            $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
            $deletemodal.modal('show');
        });

        $(document).ready(function(){
            $('#alert-success').delay(5000).fadeOut();
        });
	</script>
@stop