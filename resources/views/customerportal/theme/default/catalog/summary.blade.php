@section('content')
<?php
use App\Libraries\Timezone;
?>
<div class="container">
  <div class="row margin-bottom-40">
      <!-- BEGIN CONTENT -->
      <div class="col-md-12 col-sm-12">
        <h2>Order Summary
        <a href="javascript:window.print()" class="pull-right" style="font-size: 18px;line-height: 50px;"><i class="fa fa-print"> Print</i></a></h2>
        <hr class="border-black margin-bottom-0">
        <div class="panel panel-default margin-bottom-0">
          <div class="panel-body p-0">
            <div class="row">
                <div class="col-md-8 col-sm-7 col-xs-12 margin-bottom-20">
                  <div class="table-wrapper-responsive">
                    <table class="cs-table">
                      <tbody>
                        <tr>
                          <td width="140px" valign="top"><strong>Order Id</strong></td><td valign="top">{{$o_data['order_label']}}</td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>Order Date</strong></td><td valign="top">                         
                            {{Timezone::convertFromUTC('@'.strtotime($o_data['created_at']), Auth::user()->timezone, Config('app.date_time_format'))}}
                          </td>                      
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>Customer Name</strong></td><td valign="top">{{$o_data['user_details']['firstname']}} </td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>Email Id</strong></td><td valign="top">{{$o_data['user_details']['email']}} </td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>Billing Address</strong></td><td valign="top">
                            <?php echo implode(", ",array_except($o_data['address'], array('contact_no', 'to', 'remove')));?>
                          </td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>Contact No.</strong></td><td valign="top">{{$o_data['address']['contact_no']}}</td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="col-md-4 col-sm-5 col-xs-12 margin-bottom-20">
                  <div class="grey-box">
                    <h4><strong>Order Status: </strong>&nbsp;<span class="green">{{$o_data['status']}}</span></h4>
                    @if(isset($o_data['net_total']))
                    <strong>Amount Paid: </strong> <span><i class="fa fa-rupee"></i></span> {{$o_data['net_total']}}<br>
                    @endif
                    <strong>Payment Mode: </strong>{{$o_data['payment_type']}}<br/>
                    @if(isset($o_data['promo_code']) && !empty($o_data['promo_code']))
                    <strong>Promocode: </strong> {{$o_data['promo_code']}}<br/>
                    @endif             
                  </div>
                </div>
            </div>
          </div>
        </div>

        <div class="panel-group checkout-page" id="checkout-page">
          <!-- BEGIN CONFIRM -->
          <div id="confirm" class="panel panel-default">
            <div class="panel-heading">
              <h2 class="panel-title">Order Details</h2>
            </div>
            <div id="confirm-content">
              <div class="row">
                <div class="col-md-12 clearfix">
                  <div class="table-responsive custom-order-table">
                    <table class="table">
                      <tbody>
                        <tr>
                          <th class="checkout-description" colspan="2">Name</th>
                           @if(isset($o_data['s_duration']))
                          <th class="checkout-quantity" width="100" align="center">Duration</th>
                           @endif
                          <th class="checkout-price center" width="150">Price</th>
                          <th class="checkout-total center" width="150">Total</th>
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
                            <img src='{{URL::to("portal/theme/default/img/packets/packet_video.png")}}' height="30" alt="Course Name" style="max-width:47px;">
                            <?php
                            }
                            ?>
                          </td>
                          <td class="checkout-description">
                            <h4 class="margin-0">
                              <a href='{{URL::to("program/packets/".$o_data['items_details']['p_slug'])}}'>
                               {{$o_data['items_details']['p_tite']}}
                              </a>
                            </h4>
                            <p class="font-12">
                              <strong>Type: </strong>
                                {{$o_data['items_details']['s_title']}}
                            </p>
                          </td>
                           @if(isset($o_data['s_duration']))
                          <td class="checkout-quantity" width="100" align="center">
                            {{$o_data['items_details']['s_duration']}}
                          </td>
                          @endif
                          <td class="checkout-price center" width="150">
                            <strong>
                              <span>
                                <i class="fa fa-rupee"></i>
                              </span>
                                {{$o_data['items_details']['price']}}
                              </strong>
                          </td>
                          <td class="checkout-total center" width="150">
                              <strong>
                                <span>
                                  <i class="fa fa-rupee"></i>
                                </span>
                                 {{$o_data['items_details']['price']}}
                              </strong>
                          </td>
                        </tr>
                      </tbody>
                    </table>
                  </div>
                </div>
              </div>
              <!-- END CONFIRM -->
            </div>
            <div class="row total-table">
            <div class="col-md-offset-8 col-md-4 col-sm-12 col-xs-12">
              <table class="table xs-margin">
                @if(isset($o_data['sub_total']) && isset($o_data['promo_code']))
                <tr>
                  <td align="right">Sub Total</td>
                  <td width="150" align="center">
                    <strong class="price">
                    <i class="fa fa-rupee"></i>&nbsp; {{$o_data['sub_total']}}
                    </strong>
                  </td>
                </tr>
                @endif 
                @if(isset($o_data['discount']) && isset($o_data['promo_code']))
                <tr>
                  <td align="right">Discount</td>
                  <td width="150" align="center">
                    <strong class="price">
                      <i class="fa fa-rupee"></i>&nbsp; {{$o_data['discount']}}
                    </strong>
                  </td>
                </tr>
                @endif
                @if(isset($o_data['net_total']))
                <tr>
                  <td align="right">TOTAL</td>
                  <td width="150" align="center">
                    <strong class="price"><i class="fa fa-rupee"></i>&nbsp; {{$o_data['net_total']}}
                    </strong>
                  </td>
                </tr>
                @endif
              </table>
            </div>
          </div>
          </div>
        </div>               
      </div>
  </div>
</div>
@stop