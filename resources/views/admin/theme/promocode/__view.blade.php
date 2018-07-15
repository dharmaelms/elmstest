<div class="row">
	<div class="col-md-12">
		<table class="table table-bordered feedinfo" style="table-layout: fixed;csword-wrap: break-word;">
		<?php
            $class_product_type = function ($product_type='all'){
              $product_type_class = [
                              'Channel'=>'content_feed',
                              'Product' => 'product',
                              'Course' => 'course',
                              'Package' => 'package',
                              'All' => 'all'
                           ];
            return array_search($product_type, $product_type_class);
            };
                ?> 
			<tbody><tr>
				<th>{{ trans('admin/promocode.promocode') }}</th>
				<td>{{$promocode_detail->promocode}}</td>
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.applied_to') }}</th>
				<td>{{$class_product_type($promocode_detail->program_type)}}</td>	
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.start_date') }}</th>
				<td>{{Timezone::convertFromUTC('@'.$promocode_detail->start_date, Auth::user()->timezone, 'd-m-Y')}}</td>
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.end_date') }}</th>
				<td>{{ Timezone::convertFromUTC('@'.$promocode_detail->end_date, Auth::user()->timezone, 'd-m-Y')}}</td>
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.max_redeem') }} </th>
				<td>{{$promocode_detail->max_redeem_count}}</td>
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.redeem_count') }}</th>
				<td>{{$promocode_detail->redeemed_count}}</td>
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.discount_type') }}</th>
				<td>{{$promocode_detail->discount_type}}</td>
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.discount_value') }}</th>
				<td>
				
					<?php
						$discount_value = ($promocode_detail->discount_value != 0 ) ?
							$promocode_detail->discount_value : 'N/A';						
					?>

					{{$discount_value}}
					
				</td>
			</tr>
			<tr>
				<th>{{trans('admin/promocode.minimum_order_amount')}}</th>
				<td>{{!($promocode_detail->minimum_order_amount)? 'N/A' : $promocode_detail->minimum_order_amount  }}</td>
			</tr>
			
			@if(!empty($promocode_detail->maximum_discount_amount))
				
				<tr>
					<th>{{trans('admin/promocode.maximum_discount_amount')}}</th>
					<td>{{$promocode_detail->maximum_discount_amount}}</td>
				</tr>

			@endif
			
			<tr>
				<th>{{trans('admin/promocode.terms_and_conditions')}}</th>
				<td>{!! html_entity_decode($promocode_detail->terms_and_conditions) !!}</td>
			</tr>
			<tr>
				<th>{{ trans('admin/promocode.status') }}</th>
				<td>{{$promocode_detail->status}}</td>
			</tr>
		</tbody>
	</table>
	</div>
</div>