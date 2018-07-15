@section('content')
<?php
use App\Model\Program;
?>
    <div class="white-bg">
        <div class="row md-margin">
            <div class="col-md-12">
	          <div class="sm-margin"></div><!--space-->
			  <h3 class="page-title-small margin-top-0"><?php echo Lang::get('order.my_order'); ?></h3>
			</div>
			<div class="col-md-12 table-responsive">
					<table class="table">
				        <thead>
				            <tr>
				                <th>#</th>
				                <th><?php echo Lang::get('order.order_id'); ?></th>
				                <th colspan="2"><?php echo Lang::get('order.item_details'); ?></th>
				                <th><?php echo Lang::get('order.total'); ?></th>
				                <th><?php echo Lang::get('order.payment_types'); ?></th>
				                <th><?php echo Lang::get('order.order_status'); ?></th>
				                <th><?php echo Lang::get('order.action'); ?></th>
				            </tr>
				        </thead>
				        <tbody>
				        <?php $i = 0;?>
					    @foreach ($data as $eachOrder)
						<?php 
						$eo_data = $eachOrder->toArray();
						$i++;
						?>
				            <tr>
				            	<td>
				            		{{$i}}
				            	</td>
					       		<td>
					       			<?php print_r($eo_data['order_label']);?>
					       		</td>
					       		<td width="30">
												<?php
												$c_data = $eo_data['items_details'];
												$program=Program::getProgram($c_data['p_slug']);
												
												if(isset($c_data['p_img']) && !empty($c_data['p_img']))
												{
												?>
												                        <img src='{{URL::to("media_image/".$c_data['p_img'])}}' height="30" alt="Course Name" style="max-width:47px;">
												<?php
												}
												else
												{
												?>
												                        <img src='{{URL::to("portal/theme/default/img/packets/packet_video.png")}}' height="30" alt="Course Name" style="max-width:47px;">
												<?php
												}
												?>
						                      </td>
						                      <td class="checkout-description">
						                        <h5 class="margin-0">
												<?php if(isset($c_data["p_type"]) && ($c_data["p_type"] === "package")) { ?>
													<a href='{{URL::to("catalog/course/".$c_data['p_slug']."/package")}}'>
						                           {{$c_data['p_tite']}}
												   <sup><b class="show-tooltip badge badge-grey badge-info" style="color:white">Pack</b></sup>
						                          </a>
												<?php } else {?>
												
												<a href='{{URL::to("catalog/course/".$c_data['p_slug'])}}'>
						                           {{$c_data['p_tite']}}
						                          </a>
												<?php }?>
						                        </h5>
						                        <p class="font-12">
						                          <strong>Type: </strong>
						                            {{$c_data['s_title']}}
						                        </p>
						                      </td>
						        <td class="checkout-total" width="150">
							          <strong>
							            <span>
							             @foreach($suppoted_currency as $value)
							             	@if(isset($eo_data['currency_code']) && strtoupper($value['currency_code']) === $eo_data['currency_code'])
							             	{!! $value['currency_symbol'] !!}
							             	@endif
							             @endforeach
							            </span>
							            @if(isset($eo_data['net_total']))
							             {{number_format((float)$eo_data['net_total'])}}
							            @endif
							          </strong>
							    </td>
					       		<td>
					       			{{$eo_data['payment_type']}}
					       		</td>
					       		<td>
					       			{{$eo_data['status']}}	
					       		</td>
					       		<td>
					       			<a href='{{URL::to("ord/view-order/".$eo_data['order_id'])}}?requestUrl=myorder'><?php echo Lang::get('order.view'); ?></a>
					       		</td>
				            </tr>
					    @endforeach
<?php
if($i===0)
{
?>
								<tr>
									<td colspan="6" class="text-center"><?php echo Lang::get('order.no_order_available'); ?><br/><a href="{{URL::to('catalog')}}" class="text-warning"><?php echo Lang::get('order.make_ur_first_order'); ?></a></td>
								</tr>
<?php
}
?>
				        </tbody>
				    </table>
				    <span class="pull-right">
					{!! $data->render() !!}
				    </span>
			</div>
		</div>
</div>
@stop