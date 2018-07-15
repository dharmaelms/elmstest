<?php
header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=exportpromocode.xls");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table class="table table-advance" id="table1">
    <thead>
        <tr>
            <th>{{ trans('admin/promocode.promo_code') }}</th>
            <th>{{ trans('admin/promocode.start_date') }}</th>
            <th>{{ trans('admin/promocode.end_date') }}</th>
            <th>{{ trans('admin/promocode.max_redeem') }}</th>
            <th>{{ trans('admin/promocode.redeem_count') }}</th>
            <th>{{ trans('admin/promocode.discount_type') }}</th>
            <th>{{ trans('admin/promocode.discount_value') }}</th>
            <th>{{ trans('admin/promocode.status') }}</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($promocodes as $promocode)                
        <tr>
            <td>{{$promocode['promocode']}}</td>
            <td>{{ Timezone::convertFromUTC('@'.$promocode['start_date'], Auth::user()->timezone, Config('app.date_format'))}}</td>
            <td>{{ Timezone::convertFromUTC('@'.$promocode['end_date'], Auth::user()->timezone, Config('app.date_format'))}}</td>
            <td>@if($promocode['max_redeem_count'] == 0) Unlimited @else {{$promocode['max_redeem_count']}} @endif</td>
            <td>{{$promocode['redeemed_count']}}</td>
            <td>{{ucwords(strtolower($promocode['discount_type']))}}</td>
            <td>{{$promocode['discount_value']}}</td>
            <td>{{$promocode['status']}}</td>
        </tr>
        @endforeach                          
    </tbody>
</table>