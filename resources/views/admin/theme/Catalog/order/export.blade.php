<?php
header("Content-type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=orders.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table class="table table-advance" id="table1">
    <thead>
        <tr>
            <th>{{trans('admin/catalog.order_id')}} </th>
            <th>{{trans('admin/catalog.order_date')}}  </th>
            <th>{{trans('admin/catalog.product')}} </th>
            <th>{{trans('admin/catalog.variant')}} </th>
            <th>{{trans('admin/catalog.customer_name')}} </th>
            <th>{{trans('admin/catalog.payment_type')}} </th>
            <th>{{trans('admin/catalog.payment_status')}} </th>
            <th>{{trans('admin/catalog.order_status')}} </th>
            <th>{{trans('admin/catalog.order_amount')}} </th>
            <th>{{trans('admin/catalog.discount_amount')}} </th>
            <th>{{trans('admin/catalog.promo_code')}} </th>
            <th>{{trans('admin/catalog.email')}} </th>
            <th>{{trans('admin/catalog.phone_number')}}</th>
            <th>{{trans('admin/catalog.address')}}</th>
        </tr>
    </thead>
    <tbody>
            @foreach ($data as $eachOrder)
            <tr>

                <?php
                    $order = $eachOrder->toArray();
                ?>
                
                <td>

                    {{$order['order_label']}}

                </td>

                <td>
                    
                    {{Timezone::convertFromUTC('@'.strtotime($order['created_at']), Auth::user()->timezone, Config('app.date_time_format'))}}

                </td>

                
                <td>

                    {{$order['items_details']['p_tite']}}

                </td>
                
                <td>

                    {{$order['items_details']['s_title']}}

                </td>

                <td>

                    {{ $order['user_details']['firstname']." ".$order['user_details']['lastname'] }}

                </td>
                
                <td>

                    {{$order['payment_type']}}

                </td>

                @if($order['payment_type'] != 'FREE')
                    <td>

                        {{$order['payment_status']}}

                    </td>
                @else
                    <td>

                        {{trans('admin/catalog.n_a')}} 

                    </td>
                @endif
               
                <td>

                    {{$order['status']}}

                </td>

                @if($order['payment_type'] != 'FREE')
                    <td>

                        {{$order['items_details']['price']}}

                    </td>
                @else
                    <td>
                        
                        {{trans('admin/catalog.n_a')}} 
                    
                    </td>
                @endif
                
                @if($order['payment_type'] != 'FREE')
                    <td>

                        {{isset($order['discount']) ? $order['discount'] : 'N/A' }}

                    </td>
                @else
                    <td>

                        {{trans('admin/catalog.n_a')}} 

                    </td>
                @endif

                @if($order['payment_type'] != 'FREE')
                    <td>

                        {{isset($order['promo_code']) ? $order['promo_code'] : 'N/A'}}

                    </td>
                @else
                    <td>

                        {{trans('admin/catalog.n_a')}} 

                    </td>
                @endif

                <td>

                    {{$order['user_details']['email']}}

                </td>
                @if(array_get($order, 'user_details.mobile'))
                    <td>

                        {{$order['user_details']['mobile']}}

                    </td>
                @else
                    
                    <td>
                    
                            {{trans('admin/catalog.n_a')}} 
                    
                    </td>
                @endif
                
                @if($order['payment_type'] != 'FREE')
                    
                    <td>

                    {{ implode(", ",$order['address']) }}
                    
                    </td>

                @else
                    
                    <td>
                        {{trans('admin/catalog.n_a')}} 
                    </td>

                @endif
                
            </tr>
          @endforeach                  
    </tbody>
</table>