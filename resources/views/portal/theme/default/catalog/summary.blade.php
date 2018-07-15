@section('content')
<?php
use App\Libraries\Timezone;
use App\Model\SiteSetting;
?>

<style type="text/css">
  .order-backdrop
  {
    position : fixed;
    top : 0%;
    right : 0%;
    bottom : 0%;
    left : 0%;
    z-index : 1000;
    background-color : rgba(102, 102, 102, 0.32);
  }
  .order-loading-bar
  {
    position: fixed;
    top: 29%;
    left: 20%;
    z-index: 1100;
    min-width: 175px;
    padding: 2px;
    background-color: rgb(66, 139, 202);
    text-align: center;
  }
  .order-loading-bar span
  {
    color: white;
    font-weight: bold;
  }
</style>
@if(!isset($requestUrl) && ($requestUrl != "myorder"))
  @if($o_data['status'] == 'COMPLETED' && $o_data['payment_status'] == 'PAID')
    <div class="order-backdrop" style="display:none;">
    </div>
    <div class="order-loading-bar" style="display:none;">
        <span>{{Lang::get('catalog/template_two.order_success_thankyou_msg')}} <span id="timer" name="timer"></span> {{Lang::get('catalog/template_two.secs')}}.</span>
    </div>

      <form action="{{ URL::to(Config::get('app.program_auto_redirect')) }}" id="auto_login" name="auto_login" class="hide">
          <input type="submit" value="Login">
      </form>
  @endif
@endif
<div class="">
  <div class="row margin-bottom-40">
      <!-- BEGIN CONTENT -->
      <div class="col-md-12 col-sm-12">
        <h2>{{Lang::get('catalog/template_two.order_summary')}}
        <a href="javascript:printOrder()" class="pull-right" style="font-size: 18px;line-height: 50px;"><i class="fa fa-print">{{Lang::get('catalog/template_two.print')}}</i></a></h2>
        <hr class="border-black margin-bottom-0">
        <div class="panel panel-default margin-bottom-0">
          <div class="panel-body p-0">
        @if(Session::get('order_placed') == 'yes')
          <div class="alert alert-success">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <p class="text-center">{{Lang::get('catalog/template_two.you_placed_ur_order')}}</p>
          </div>
        @endif
        @if(Session::get('order_placed') == 'no')
          <div class="alert alert-warning">
              <a href="#" class="close" data-dismiss="alert" aria-label="close">&times;</a>
              <p class="text-center">{{Lang::get('catalog/template_two.your_order_is')}} {{strtolower($o_data['status']) }}</p>
          </div>
        @endif
            <div class="row">
                <div class="col-md-8 col-sm-7 col-xs-12 margin-bottom-20">
                  <div class="table-wrapper-responsive">
                    <table class="cs-table">
                      <tbody>
                        <tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('catalog/template_two.order_id')}}</strong></td><td valign="top">{{$o_data['order_label']}}</td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('catalog/template_two.order_date')}}</strong></td><td valign="top">
                            {{Timezone::convertFromUTC('@'.strtotime($o_data['created_at']), Auth::user()->timezone, Config('app.date_time_format'))}}
                          </td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('catalog/template_two.customer_name')}}</strong></td><td valign="top">{{$o_data['user_details']['firstname']}} </td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('catalog/template_two.emailid')}}</strong></td><td valign="top">{{$o_data['user_details']['email']}} </td>
                        </tr>
                         <?php
                        $address = array_filter(array_except($o_data['address'], array('contact_no', 'to', 'remove')));
                      ?>
                      @if(!empty($address))
                        <tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('catalog/template_two.billing_address')}}</strong></td><td valign="top">
                            <?php echo implode(", ",array_except($o_data['address'], array('contact_no', 'to', 'remove')));?>
                          </td>
                        </tr>
                        <tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('catalog/template_two.contact_no')}}</strong></td><td valign="top">{{$o_data['address']['contact_no']}}</td>
                        </tr>
                      @endif
                      </tbody>
                    </table>
                  </div>
                </div>
                <div class="col-md-4 col-sm-5 col-xs-12 margin-bottom-20">
                  <div class="grey-box">
                    <h4><strong>{{Lang::get('catalog/template_two.order_status')}}: </strong>&nbsp;<span class="green">{{$o_data['status']}}</span></h4>
                    @if(isset($o_data['net_total']))
                    <strong>{{Lang::get('catalog/template_two.order_amount')}}: </strong> <span>{!! $currency_symbol !!}</span> {{number_format((float)$o_data['net_total'])}}<br>
                    @endif
                    <strong>{{Lang::get('catalog/template_two.payment_mode')}}: </strong>{{$o_data['payment_type']}}<br/>
                    <strong>{{Lang::get('catalog/template_two.payment_status')}}: </strong>{{$o_data['payment_status']}}<br/>
                    @if(isset($o_data['promo_code']) && !empty($o_data['promo_code']))
                    <strong>{{Lang::get('catalog/template_two.promocode')}}: </strong> {{$o_data['promo_code']}}<br/>
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
              <h2 class="panel-title">{{Lang::get('catalog/template_two.order_details')}}</h2>
            </div>
            <div id="confirm-content">
              <div class="row">
                <div class="col-md-12 clearfix">
                  <div class="table-responsive custom-order-table">
                    <table class="table">
                      <tbody>
                        <tr>
                          <th class="checkout-description" colspan="2">{{Lang::get('catalog/template_two.name')}}</th>
                           @if(isset($o_data['s_duration']))
                          <th class="checkout-quantity" width="100" align="center">{{Lang::get('catalog/template_two.duration')}}</th>
                           @endif
                          <th class="checkout-price center" width="150">{{Lang::get('catalog/template_two.price')}}</th>
                          <th class="checkout-total center" width="150">{{Lang::get('catalog/template_two.total')}}</th>
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
                              <a id="product_page_link" name="product_page_link" href='{{URL::to("catalog/course/".$o_data['items_details']['p_slug'])}}'>
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
                                {!! $currency_symbol !!}
                              </span>
                                {{number_format((float)$o_data['items_details']['price'])}}
                              </strong>
                          </td>
                          <td class="checkout-total center" width="150">
                              <strong>
                                <span>
                                  {!! $currency_symbol !!}
                                </span>
                                 {{number_format((float)$o_data['items_details']['price'])}}
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
           <div class="col-md-offset-8 col-md-4 col-sm-offset-6 col-sm-6 col-xs-12">
              <table class="table xs-margin">
                @if(isset($o_data['sub_total']) && isset($o_data['promo_code']))
                <tr>
                  <td align="right">{{Lang::get('catalog/template_two.sub_total')}}</td>
                  <td width="150" align="center">
                    <strong class="price">
                    {!! $currency_symbol !!}&nbsp; {{number_format((float)$o_data['sub_total'])}}
                    </strong>
                  </td>
                </tr>
                @endif
                @if(isset($o_data['discount']) && isset($o_data['promo_code']))
                <tr>
                  <td align="right">{{Lang::get('catalog/template_two.discount')}}</td>
                  <td width="150" align="center">
                    <strong class="price">
                      {!! $currency_symbol !!}&nbsp; {{number_format((float)$o_data['discount'])}}
                    </strong>
                  </td>
                </tr>
                @endif
                @if(isset($o_data['net_total']))
                <tr>
                  <td align="right">{{Lang::get('catalog/template_two.TOTAL')}}</td>
                  <td width="150" align="center">
                    <strong class="price">{!! $currency_symbol !!}&nbsp; {{number_format((float)$o_data['net_total'])}}
                    </strong>
                  </td>
                </tr>
                @endif
                <tr>
                          <td align="right" class="font-10 gray">
                             {{Lang::get('catalog/template_two.inculsive_all_taxes')}}
                            </td>

                            <td width="150" align="center">
                            &nbsp;&nbsp;
                            </td>
                        </tr>
              </table>
            </div>
          </div>
          </div>
        </div><br>
        @if($o_data['payment_type'] == "BANK TRANSFER")
          <div class ="col-md-12 sm-margin well" id="bank_details">
          <?php $bank_details = SiteSetting::module('BankDetails', 'bank_details'); ?>
          {!! $bank_details !!}
          </div>
        @endif
      </div>
  </div>

</div>
<script type="text/javascript">

  function printOrder()
  {

    var href = $('#product_page_link').attr('href');

    $('#product_page_link').removeAttr('href');

    window.print();

    $('#product_page_link').attr('href',href);

  }

</script>

<script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.plugin.js') }}"></script>
<script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.countdown.js') }}"></script>

<script type="text/javascript">
  $(function () {
    $('#timer').countdown({
      until: +10,
      onExpiry:submit,
      compact: true,
      layout: '{snn}'
    });
  });       
  function submit(){
    $('#auto_login').submit();
  }

  $(".order-backdrop, .order-loading-bar").css({
    display : "block"
  });
</script>
@stop