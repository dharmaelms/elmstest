@section('content')
<?php
use App\Libraries\Timezone;
use App\Model\SiteSetting;

?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content"> 
                <div class="btn-toolbar clearfix">
                   <div class="table-responsive">
                   		<div class="row margin-bottom-40">
      <!-- BEGIN CONTENT -->
      <div class="col-md-offset-1 col-md-10 col-sm-12">
        
       <div class="row">
        <div class="col-md-6">
          <h2>
            {{trans('admin/catalog.order_summary')}} 
          </h2>
        </div>
        <div class="col-md-6">
          <a href="{{ URL::to('cp/order/list-order') }}" class="btn btn-info btn-sm pull-right" style="margin-top: 10px;">
            <span class="btn btn-circle blue custom-btm"> 
                <i class="fa fa-chevron-left"></i> 
            </span>
             {{ trans('admin/order.back_to_order') }}
        </a>
        </div>
    </div>
       <hr>
        <div class="panel panel-default m-0">
          <div class="panel-body p-0">
          <div class="row">
                <div class="col-md-8 col-sm-7 col-xs-12 margin-bottom-20">
                  <div class="table-wrapper-responsive">
                    <table class="cs-table">
                      <tbody><tr>
                        <td width="140px" valign="top"><strong>{{trans('admin/catalog.order_id')}} </strong></td><td valign="top">{{$o_data['order_label']}}</td>
                      </tr>
                      <tr>
                        <td width="140px" valign="top"><strong>{{trans('admin/catalog.order_date')}}  </strong></td><td valign="top">{{Timezone::convertFromUTC('@'.strtotime($o_data['created_at']), Auth::user()->timezone, Config('app.date_time_format'))}}</td>
                      </tr>
                      <tr>
                        <td width="140px" valign="top"><strong>{{trans('admin/catalog.customer_name')}} </strong></td><td valign="top">{{$o_data['user_details']['firstname']}} </td>
                      </tr>
                      <tr>
                        <td width="140px" valign="top"><strong>{{trans('admin/catalog.email_id')}} </strong></td><td valign="top">{{$o_data['user_details']['email']}} </td>
                      </tr>
                      <?php
                        $address = array_filter(array_except($o_data['address'], array('contact_no', 'to', 'remove')));
                      ?>
                      @if(!empty($address))
                      <tr>
                        <td width="140px" valign="top"><strong>{{trans('admin/catalog.billing_address')}} </strong></td><td valign="top">
                          <?php echo implode(", ",$address);?>
                        </td>
                      </tr>
                      <tr>
                        <td width="140px" valign="top"><strong>{{trans('admin/catalog.contact_no')}} </strong></td><td valign="top">{{$o_data['address']['contact_no']}}</td>
                      </tr>
                      @endif
                    </tbody></table>
                  </div>
                </div>
                <div class="col-md-4 col-sm-5 col-xs-12 margin-bottom-20">
                  <div class="grey-box">
                    <h4><strong>{{trans('admin/catalog.order_status')}} : </strong>&nbsp;<span class="green">{{$o_data['status']}}</span></h4>
                    <p><strong>{{trans('admin/catalog.order_amount')}}  : </strong> <span>{!! $currency_symbol !!}</span> {{number_format($o_data['items_details']['price'])}}<br>
                    <strong>Payment Mode: </strong>{{$o_data['payment_type']}}<br/>
                    <strong>{{trans('admin/catalog.payment_status')}} : </strong>{{$o_data['payment_status']}}<br/>
                    @if(isset($o_data['promo_code']) && !empty($o_data['promo_code']))
                    <strong>Promocode: </strong> {{$o_data['promo_code']}}<br/>
                    @endif
                    </p>
                  </div>
                </div>
              </div>
          </div>
        </div>

        <div class="panel-group checkout-page" id="checkout-page">
          <!-- BEGIN CONFIRM -->
          <div id="confirm" class="panel panel-default">
            <div class="panel-heading">
              <h2 class="panel-title">{{trans('admin/catalog.order_details')}} </h2>
            </div>
            <div id="confirm-content">
              <div class="row">
                <div class="col-md-12 clearfix">
                  <div class="table-wrapper-responsive">
                    <table class="table table-bordered">
                    <tbody>
                    <tr>
                      <th class="checkout-description" colspan="2">{{trans('admin/catalog.name')}} </th>
                      @if(isset($o_data['items_details']['s_duration']))
                      <th class="checkout-quantity" width="100" align="center">{{trans('admin/catalog.duration')}} </th>
                      @endif
                      <th class="checkout-price" width="150">{{trans('admin/catalog.price')}} </th>
                      <th class="checkout-total" width="150">{{trans('admin/catalog.amount')}} </th>
                    </tr>
                    <tr>
                      <td width="60">
<?php
if(isset($o_data['items_details']['p_img']) && !empty($o_data['items_details']['p_img']))
{
?>
                        <img src='{{URL::to("media_image/".$o_data['items_details']['p_img'])}}' height="30" alt="Course Name" style="max-width:47px;">
<?php
}
else
{
?>
                        <img src='{{URL::to("admin/img/packets/packet_video.png")}}' height="30" alt="Course Name" style="max-width:47px;">
<?php
}
?>
                      </td>
                      <td class="checkout-description">
                        <h3>
                           {{$o_data['items_details']['p_tite']}}
                        </h3>
                        <p class="font-12">
                          <strong>Type: </strong>
                            {{$o_data['items_details']['s_title']}}
                        </p>
                      </td>
                      @if(isset($o_data['items_details']['s_duration']))
                      <td class="checkout-quantity" width="100" align="center">
                        {{$o_data['items_details']['s_duration']}}
                      </td>
                      @endif
                      <td class="checkout-price" width="150">
                        <strong>
                          <span>
                            {!! $currency_symbol !!}
                          </span>
                            {{number_format($o_data['items_details']['price'])}}
                          </strong>
                      </td>
                      <td class="checkout-total" width="150">
                          <strong>
                            <span>
                              {!! $currency_symbol !!}
                            </span>
                             {{number_format($o_data['items_details']['price'])}}
                          </strong>
                      </td>
                    </tr>
                  </tbody>
                </table>
                  </div>
                </div>               
              </div>
            </div>
            <div class="row total-table">
            <div class="col-md-offset-7 col-md-5 col-sm-12 col-xs-12">
              <table class="table xs-margin">
                @if(isset($o_data['sub_total']) && isset($o_data['promo_code']))
                <tr>
                  <td align="right">{{trans('admin/catalog.sub_total')}} </td>
                  <td width="150" align="center">
                    <strong class="price">
                    {!! $currency_symbol !!}&nbsp; {{number_format($o_data['sub_total'])}}
                    </strong>
                  </td>
                </tr>
                @endif 
                @if(isset($o_data['discount']) && isset($o_data['promo_code']))
                <tr>
                  <td align="right">{{trans('admin/catalog.discount')}} </td>
                  <td width="150" align="center">
                    <strong class="price">
                      {!! $currency_symbol !!}&nbsp; {{number_format($o_data['discount'])}}
                    </strong>
                  </td>
                </tr>
                @endif
                @if(isset($o_data['net_total']))
                <tr>
                  <td align="right">{{trans('admin/catalog.total')}} </td>
                  <td width="150" align="center">
                    <strong class="price">{!! $currency_symbol !!}&nbsp; {{number_format($o_data['net_total'])}}
                    </strong>
                  </td>
                </tr>
                @endif
                <tr>
                  <td align="right" class="font-10 gray">
                     {{trans('admin/catalog.billing_note')}} 
                    </td>
                    
                    <td width="150" align="center">
                    &nbsp;&nbsp;
                    </td>
                </tr>
              </table>
            </div>
          </div>
          </div>
          
          <!-- END CONFIRM -->

        </div>
        @if($o_data['payment_type'] == "BANK TRANSFER")
              <div class ="col-md-12 panel panel-default m-0" id="bank_details"><div class="panel-body p-0">
              <?php $bank_details = SiteSetting::module('BankDetails', 'bank_details'); ?>
              {!! $bank_details !!}
              </div></div>
            @endif 
      </div>
      <!-- END CONTENT -->
    </div>
				   </div>
              </div>
            </div>
            
        </div>
        
      </div>
</div>
@stop