<?php
use App\Libraries\Timezone;
use App\Model\SiteSetting;
use App\Model\Program;
//use Lang;
$email_currency_symbol = $currency_symbol ? $currency_symbol : '&#8377;';
?>
<!DOCTYPE html>
<html>
<head>
  <meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />

</head>
<body  leftmargin="0" bgcolor="#232323" topmargin="0" marginwidth="0" marginheight="0" style="width: 100%; height: 100%; background-color: #232323;font-size:13px;">
  <table width="800" bgcolor="#ffffff" cellpadding="0" cellspacing="0" align="center" style="font-family: 'Source Sans Pro', sans-serif;
    color:#333333;
    font-size:13px;line-height: 1.4;">
    <tr>
      <td width="800" height="5" bgcolor="#232323" style="font-size: 0px; line-height: 1px">&nbsp;</td>
    </tr><!-- Space row -->
    <tr>
      <td>
        <table width="800" border="0" cellpadding="0" cellspacing="0">
          <tr>
            <td width="40" height="90" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
            <td width="720" height="90" valign="bottom">
              <?php
                $site_logo=SiteSetting::module('Contact Us', 'site_logo');
                if(isset($site_logo) && !empty($site_logo))
                {
                    $logo=config('app.site_logo_path').$site_logo;
                }
                else
                {
                    $logo=config('app.default_logo_path');
                }
            ?>
            <a href="{{ url('/') }}">
                <img src="{{ URL::to($logo) }}" alt="logo" class="logo-default" />
            </a>
            </td>
            <td width="40" height="90" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
          </tr>
          <tr>
            <td width="40" height="150" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
            <td width="720">
              <table width="720">
                <tr>
                  <td width="350"  valign="top">
                    <strong>Order ID:</strong> {{$o_data['order_label']}} <br>
                    <strong>Order Date:</strong>
                    @if(isset($user_timezone))
                      {{Timezone::convertFromUTC('@'.strtotime($o_data['created_at']), $user_timezone , Config('app.date_time_format'))}}
                    @else
                       {{Timezone::convertFromUTC('@'.strtotime($o_data['created_at']), Auth::user()->timezone, Config('app.date_time_format'))}}
                    @endif
                   <br>
                    <strong>Customer Name:</strong> {{$o_data['user_details']['firstname']}} <br>
                    <strong>Email ID:</strong> {{$o_data['user_details']['email']}}<br>
                    <?php
                        $address = array_filter(array_except($o_data['address'], array('contact_no', 'to', 'remove')));
                      ?>
                      @if(!empty($address))
                    <strong>Billing Address:</strong><?php echo implode(", ",$address);?> <br>
                    <strong>Contact No.:</strong> {{$o_data['address']['contact_no']}} <br>
                    @endif
                  </td>
                  <td width="20" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
                  <td width="350" valign="top">
                    <h3 style="margin:0 0 10px"><strong>ORDER STATUS:</strong>{{$o_data['status']}}</h3>
                    <strong>Order Amount:</strong> <?php echo $email_currency_symbol; ?> <?php if($o_data['items_details']['price'] > 0){ ?> {{$o_data['items_details']['price']}}<?php }else { echo "0";}?> <br>
                     <strong>Payment Status: </strong>{{$o_data['payment_status']}}<br/>
                    <strong>Payment Mode:</strong> {{$o_data['payment_type']}}<br/>
                    @if(isset($o_data['promo_code']) && !empty($o_data['promo_code']))
                    <strong>Promocode :</strong>{{$o_data['promo_code']}}
                    @endif
                  </td>
                </tr>
              </table>
            </td>
            <td width="40" height="150" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
          </tr>
          <tr>
            <td width="40" height="50" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
            <td width="720" height="50" style="border-bottom:1px solid #dddddd;"><h3>Order Details</h3></td>
            <td width="40" height="50" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
          </tr>
          <tr>
            <td width="40" height="50" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
            <td width="720" height="50" style="border-bottom:1px solid #dddddd;">
              <table width="100%">
                <thead>
                  <tr>
                    <th colspan="2" style="text-align:left;border-bottom:1px solid #dddddd;background:#eeeeee;"><strong>Product Name</strong></th>
                     @if(isset($o_data['items_details']['s_duration']))
                    <th width="100" style="border-bottom:1px solid #dddddd;background:#eeeeee;"><strong>Duration</strong></th>
                    @endif
                    <th width="160" style="border-bottom:1px solid #dddddd;background:#eeeeee;"><strong>Price</strong></th>
                    <th width="160" style="border-bottom:1px solid #dddddd;background:#eeeeee;"><strong>Amount</strong></th>
                  </tr>
                </thead>
                <tbody>
                  <tr>
                    <td style="text-align:left;border-bottom:1px solid #eeeeee;" width="70">
<?php
if(isset($o_data['items_details']['p_img']) && !empty($o_data['items_details']['p_img']))
{
?>
                        <img src='{{URL::to("media_image/".$o_data['items_details']['p_img'])}}' style="max-height: 40px;" alt="Product Name">
<?php
}
else
{
?>
                        <img src='{{URL::to("portal/theme/default/img/packets/packet_video.png")}}' style="max-height: 40px;" alt="Product Name">
<?php
}
?>
                    </td>
                    <td style="text-align:left;border-bottom:1px solid #eeeeee;">
                      <h3 class="margin-0">
                      <?php
                      $program = Program::getProgram($o_data['items_details']['p_slug']);
                      ?>
                      @if(isset($program[0]['program_sub_type']) && $program[0]['program_sub_type']=='collection')
                       <a href="{{URL::to('catalog/course/'.$o_data['items_details']['p_slug'])}}">{{$o_data['items_details']['p_tite']}}</a>
                      @else
                       <a href="{{URL::to('catalog/course/'.$o_data['items_details']['p_slug'])}}">{{$o_data['items_details']['p_tite']}}</a>
                      @endif

                      <br>
                      <span style="font-size:11px"><strong>Type:</strong>  {{$o_data['items_details']['s_title']}}</span><br>
                      <?php
                      $i=0;
                      if(isset($program[0]['program_sub_type']) && $program[0]['program_sub_type']=='collection') {
                      $count = Program::getchildrencount($o_data['items_details']['p_slug']);

                      ?>
                      <span style="font-size:11px"><strong>No of {{Lang::get('program.channel')}}s:</strong>{{$count}}</span><br>
                      @if($count > 0)
                        <span style="font-size:11px"><strong>List of {{Lang::get('program.channel')}}s:</strong></span><br>
                        @foreach($program[0]['child_relations']['active_channel_rel'] as $child_id)
                          <?php
                          $i++;
                          $child_program=Program:: getProgramDetailsByID($child_id);
                          ?>
                        <span style="font-size:11px"><strong>{{$i}}.</strong>{{$child_program['program_title']}}</span><br>
                        @endforeach
                      @endif
                      <?php
                      }

                      ?>
                    </td>
                       @if(isset($o_data['items_details']['s_duration']))
                      <td align="center" width="100" style="border-bottom:1px solid #eeeeee;">{{$o_data['items_details']['s_duration']}}</td>
                      @endif
                    <td align="center" width="160"style="border-bottom:1px solid #eeeeee;"><?php echo $email_currency_symbol; ?>{{$o_data['items_details']['price']}}</td>
                    <td align="center" width="160"style="border-bottom:1px solid #eeeeee;"><?php echo $email_currency_symbol; ?> {{$o_data['items_details']['price']}}</td>
                  </tr>
                </tbody>
                <tfoot>
<?php
  if(isset($o_data['items_details']['s_duration']))
  {
    $span = 4;
  }
  else
  {
    $span = 3;
  }
?>
                @if(isset($o_data['sub_total']) && isset($o_data['promo_code']))
                  <tr>
                    <td colspan="{{$span}}" align="right" style="border-top:1px solid #dddddd;"><strong>Subtotal</strong></td>
                    <td width="160" align="center" style="border-top:1px solid #dddddd;"><?php echo $email_currency_symbol; ?>{{$o_data['sub_total']}}</td>
                  </tr>
                  @endif
                   @if(isset($o_data['discount']) && isset($o_data['promo_code']))
                  <tr>
                   <td colspan="{{$span}}" align="right"><strong>Discount</strong></td>
                   <td width="160" align="center"><?php echo $email_currency_symbol; ?> {{$o_data['discount']}}</td>
                  </tr>
                  @endif
                   @if(isset($o_data['net_total']))
                  <tr>
                    <td colspan="{{$span}}" align="right"><strong>Total</strong></td>
                    <td width="160" align="center"><?php echo $email_currency_symbol; ?>{{$o_data['net_total']}}</td>
                  </tr>
                  <tr>
                    <td align="right" class="font-10 gray" colspan="{{$span}}">
                      *Inclusive of all taxes
                    </td>
                    <td width="160" align="center">&nbsp;&nbsp;</td>
                  </tr>
                  @endif
                </tfoot>
              </table>
            </td>
            <td width="40" height="50" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
          </tr>
          <tr>
            <td width="40" height="10" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
            <td width="520" height="10"style="font-size: 0px; line-height: 1px;">&nbsp;</td>
            <td width="40" height="10" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
    @if($o_data['payment_type'] == "BANK TRANSFER")
    <tr>
      <td>
        <table width="800" border="0" cellpadding="0" cellspacing="0" bgcolor="#EEEEEE">
          <tr>
            <td width="40" height="50" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
            <td width="720" height="50" style="border-bottom:1px solid #dddddd;"><h3>Bank Details</h3>
            <?php $bank_details = SiteSetting::module('BankDetails', 'bank_details'); ?>
              {!! $bank_details !!}</td>
            <td width="40" height="50" style="font-size: 0px; line-height: 1px;">&nbsp;</td>
          </tr>
        </table>
      </td>
    </tr>
    @endif
    <tr>
      <td width="800" height="5" bgcolor="#232323" style="font-size: 0px; line-height: 1px">&nbsp;</td>
    </tr><!-- Space row -->

  </table>

 </body>
</html>