@section('content')
<?php
if(Auth::check()){
  date_default_timezone_set(Auth::user()->timezone);
}else{
  date_default_timezone_set(Config('app.default_timezone'));
}

?>
<style>
  .panel-default { border-bottom: 0  !important;}
</style>

<?php

  use App\Model\Country;
  use App\Model\SiteSetting;
  $site_currency_symbol = $currency_symbol;
?>

<div class="container">

  <div class="row margin-bottom-40 checkout-page">

      <form action="{{URL::to('checkout/pay')}}" method="post">

        <div class="col-md-12 col-sm-12">
          <h2>{{trans('catalog/template_two.CHECKOUT')}}</h2>
          @if(Session::get('last_item') === 'yes')

              <div class="alert alert-info text-center">
                  <p class="">
                    {{trans('batch/course.race_bay_alert')}}
                    <span id="timer"></span> / 00:07:00 minutes.
                  </p>
              </div>

              <script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.plugin.js') }}"></script>
              <script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.countdown.js') }}"></script>

              <script type="text/javascript">

                  $(function () {
                    $('#timer').countdown({
                      until: +420,
                      onExpiry:reload,
                      compact: true,
                      layout: '{hnn}{sep}{mnn}{sep}{snn}'
                    });
                  });

                  function reload()
                  {
                    window.location.reload();
                  }

              </script>

          @endif

        @if(Session::get('no_item') === 'yes')

          <div class="alert alert-warning text-center">
            {{trans('batch/course.race_bay_warning')}}
          </div>

        @endif

        <?php

          $flag = (Input::old('fullname') !== null) ? 0 : 1 ;

        ?>

        <div class="panel-group checkout-page" id="checkout-page">

          <input type="hidden" name="p_slug" id="p_slug" value="{{$items_details['p_slug']}}">
          <input type="hidden" name="s_slug" id="s_slug" value="{{$items_details['s_slug']}}">
          <input type="hidden" name="program_type" id="program_type" value="{{$items_details['program_type']}}">

          <div id="payment-address" class="panel panel-default">

            <div class="panel-heading">
              <h2 class="panel-title">{{trans('catalog/template_two.address_details')}}</h2>
            </div>

            <div id="payment-address-content">

              <div class="panel-body row">

                <div class="xs-margin">
                </div>

                <div class="col-md-4 col-sm-4">

                  <div class="form-group" style="">

                      <?php
                           $fullname = (Input::old('fullname')) ? Input::old('fullname') :
                            $items_details['d_addrs']['fullname'];
                       ?>

                      <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Full Name" value="{{$fullname}}">

                      <span class="text-danger">
                        {{$errors->first('fullname')}}
                      </span>

                  </div>

                  <div class="form-group">

                      <?php
                        $adress = (Input::old('address')) ? Input::old('address') :
                        $items_details['d_addrs']['street'];
                      ?>

                      <input type="text" id="address" name="address" class="form-control" placeholder="Street" value="{{$adress}}">

                      <span class="text-danger">
                        {{$errors->first('address')}}
                      </span>

                  </div>

                  <div class="form-group">

                    <?php
                        $city = (Input::old('city')) ? Input::old('city') : $items_details['d_addrs']['city'];
                    ?>

                    <input type="text" id="city" name="city" class="form-control" placeholder="City" value="{{$city}}">

                    <span class="text-danger">
                      {{$errors->first('city')}}
                    </span>

                  </div>

                </div>

                <div class="col-md-4 col-sm-4">
                   <div class="form-group">

                        <?php
                          $states = [
                                      "Andhra Pradesh","Arunachal Pradesh","Assam",
                                      "Bihar","Chhattisgarh","Goa","Gujarat",
                                      "Haryana","Himachal Pradesh","Jammu and Kashmir",
                                      "Jharkhand","Karnataka","Kerala","Madhya Pradesh",
                                      "Maharashtra","Manipur","Meghalaya","Mizoram",
                                      "Nagaland","Orissa","Punjab","Rajasthan",
                                      "Sikkim","Tamil Nadu","Tripura",
                                      "Uttar Pradesh","Uttarakhand","West Bengal"
                                    ];

                          $region_state = (Input::old('region_state')) ?
                                          Input::old('region_state') :
                                          $items_details['d_addrs']['state'];
                        ?>

                      <input type="text" id="region_state" name="region_state" class="form-control" placeholder="State" value="{{$region_state}}">

                      <span class="text-danger">
                         {{$errors->first('region_state')}}
                      </span>

                  </div>

                  <div class="form-group">

                    <?php
                      $countries = Country::getCountries();
                      $country = (Input::old('country')) ?
                                  Input::old('country') :
                                  $items_details['d_addrs']['country'];
                    ?>

                    <select class="form-control input-sm" id="country" name="country">

                       <option value="India" selected>India</option>
                         @foreach($countries as $value)

                            <option value="{{$value['name']}}"
                                @if($country == $value['name'])selected @endif
                                >
                            {{$value['name']}}
                            </option>

                         @endforeach

                    </select>

                    <span class="text-danger">
                       {{$errors->first('country')}}
                    </span>

                  </div>

                </div>

                <div class="col-md-4 col-sm-4">

                  <div class="form-group">

                    <?php
                      $pincode = (Input::old('post_code')) ?
                                 Input::old('post_code') :
                                 $items_details['d_addrs']['pincode'];
                    ?>

                    <input type="text" id="post_code" name="post_code" class="form-control" placeholder="Post Code " value="{{$pincode}}">

                    <span class="text-danger">
                       {{$errors->first('post_code')}}
                    </span>

                  </div>

                  <div class="form-group">

                    <?php
                      $phone = (Input::old('telephone')) ?
                                Input::old('telephone') :
                                $items_details['d_addrs']['phone'];
                    ?>

                    <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Mobile Number" value="{{$phone}}">

                     <span class="text-danger">
                       {{$errors->first('telephone')}}
                     </span>

                  </div>

                </div>

              </div>

            </div>
            <!-- END PAYMENT ADDRESS -->
            <!-- BEGIN CONFIRM -->
            <div id="confirm" class="panel panel-default">

              <div class="panel-heading">
                <h2 class="panel-title">{{trans('catalog/template_two.order_summary')}}</h2>
              </div>

              <div id="confirm-content">

                <div class="panel-body row">

                  <div class="col-md-12 clearfix">

                    <div class="table-responsive custom-order-table">

                      <table class="table margin-btm-0">

                        <thead>
                          <tr>
                            <th class="checkout-description" colspan="2">
                              {{trans('catalog/template_two.name')}}
                            </th>

                            @if(isset($items_details['s_duration']))
                              <th class="checkout-quantity" width="100" align="center">
                                 {{trans('catalog/template_two.duration')}}
                              </th>
                            @endif

                            <th class="checkout-price center" width="150">
                               {{trans('catalog/template_two.price')}}
                            </th>

                            <th class="checkout-total center" width="150">
                               {{trans('catalog/template_two.amount')}}
                            </th>

                          </tr>
                        </thead>

                        <tbody>
                          <tr>
                            <td width="60">

                              <?php

                              $image_url = (isset($items_details['p_img']) &&
                                            !empty($items_details['p_img'])) ?
                                            URL::to("media_image/".$items_details['p_img']) :
                                            URL::to("portal/theme/default/img/packets/packet_video.png");

                              ?>

                              <img src='{{$image_url}}' height="30" alt="Course Name" style="max-width:47px;">

                            </td>

                            <td class="checkout-description">

                              <h4 class="margin-top-0 margin-bottom-0">
                                <a href='{{URL::to("catalog/course/".$items_details['p_slug'])}}'>
                                  {{$items_details['p_tite']}}
                                </a>
                              </h4>

                              <p class="font-12">
                                  <strong> Type: </strong>
                                  {{$items_details['s_title']}}
                              </p>

                            </td>

                            @if(isset($items_details['s_duration']))
                              <td class="checkout-quantity" width="100" align="center">
                                {{$items_details['s_duration']}}
                              </td>
                            @endif

                            <td class="checkout-price center" width="150">
                              <strong>

                                <span>
                                  {!! $site_currency_symbol !!}
                                </span>

                                {{number_format((float)$items_details['price'])}}

                              </strong>
                            </td>

                            <td class="checkout-total center" width="150">
                                <strong>

                                  <span>
                                    {!! $site_currency_symbol !!}
                                  </span>

                                   {{number_format((float)$items_details['price'])}}

                                </strong>
                            </td>

                          </tr>

                        </tbody>

                      </table>

                    </div>

                  </div>

                  <div class="total-table">

                      <div class="col-md-6 col-sm-6 col-xs-12">

                        <div class="xs-margin"></div>



                          <div class="form-group promo-div row">

                             <input type="hidden" id="promocode_url" name="promocode_url" value="{{URL::to('checkout/apply-coupon')}}">


                              <span id="coupan_section" name="coupan_section" class="">
                                  <div class="col-md-6 col-sm-6 xs-margin">

                                    <input type="text" id="promo_code" name="promo_code" class="form-control" placeholder="Promotional/Voucher Code" value="{{Input::old('promo_code')}}" onblur="this.value=this.value.toUpperCase()">

                                      <div id="error_coupanCode" name="error_coupanCode" class="text-danger">

                                      </div>
                                      <span id="success_coupanCode" name="success_coupanCode" class="text-success"></span>

                                  </div>


                                  <div class="col-md-6 col-sm-6 xs-margin">

                                    <button class="btn btn-danger" type="button" onclick="doPromoCode()"> {{trans('catalog/template_two.apply')}}</button>
                                     <?php $offer = "Offer";?>
                                      <button id="promocode_cancel" name="promocode_cancel" onclick="cancelPromocode()" class="btn btn-danger hide">
                                        Cancel
                                      </button>
                                    @if(config('app.promocode_user_enabled') === true)
                                        @if(isset($items_details['promocode'])
                                          && !empty($items_details['promocode'])
                                          && is_array($items_details['promocode']))

                                    &nbsp;&nbsp;<a type="button" class="" onclick="resetPaymentOptions()" data-toggle="modal" data-target="#info-modal">

                                          <?php

                                            $offers = (count($items_details['promocode']) > 1 ) ?
                                                      $offer.'s' :
                                                      '';

                                          ?>

                                        View {{$offer}}
                                    </a>


                                        @endif

                                  @endif
                                  </div>
                              </span>

                          </div>

                      </div>

                      <div class="col-md-6 col-sm-6 col-xs-12">
                      <br>
                        <table class="table xs-margin">

                          <tr>

                              <td align="right">{{trans('catalog/template_two.sub_total')}}</td>

                              <td width="150" align="center">

                                <strong class="price">
                                  {!! $site_currency_symbol !!}                            {{number_format((float)$items_details['price'])}}
                                </strong>

                              </td>

                          </tr>

                          <tr>

                            <td align="right">
                              {{trans('catalog/template_two.discount')}}
                            </td>

                            <td width="150" align="center">
                              <strong class="price">

                                {!! $site_currency_symbol !!}

                                <span id="discount_availed" name="discount_availed">

                                  @if(Input::old('d_hidden'))
                                    {{number_format((float)Input::old('d_hidden'))}}
                                  @else
                                    0
                                  @endif

                                </span>

                                <input type="hidden" name="d_hidden" id="d_hidden" value="">

                              </strong>
                            </td>
                        </tr>

                        <tr>

                          <td align="right">
                            {{trans('catalog/template_two.TOTAL')}}
                          </td>

                          <td width="150" align="center">

                            <strong class="price">

                              {!! $site_currency_symbol !!}

                              <input type="hidden" name="net_total_input" id="net_total_input" value="{{$items_details['price']}}">

                              <input type="hidden" name="h_net_total" id="h_net_total" value="">

                              <span id="net_total" name="net_total">

                                @if(Input::old('h_net_total'))
                                {{number_format((float)Input::old('h_net_total'))}}
                                @else
                                {{number_format((float)$items_details['price'])}}
                                @endif

                              </span>

                            </strong>

                          </td>

                        </tr>

                        <tr>
                          <td align="right" class="font-10 gray">
                             {{trans('catalog/template_two.inculsive_all_taxes')}}
                            </td>

                            <td width="150" align="center">
                            &nbsp;&nbsp;
                            </td>
                        </tr>

                      </table>

                    </div>

                  </div>

                </div>

              </div>

            </div>

          </div>
          <!-- END CONFIRM -->

          <!-- BEGIN PAYMENT METHOD -->
          <div id="payment-method" class="panel panel-default margin-top-0">

            <div class="panel-heading">
              <h2 class="panel-title">
                  {{trans('catalog/template_two.payment')}}
              </h2>
            </div>

            <div id="payment-method-content">

              <div class="panel-body row">

                <div class="xs-margin"></div> <!-- space-->

                <div class="col-md-6 col-sm-6 col-xs-12">

                  <p>
                    {{trans('catalog/template_two.please_select_the_preferred_payment')}}
                  </p>

                  <?php
                    $paymentSetting = SiteSetting::module('PaymentGateway');
                  ?>

                   <div class="radio-list">
                      <?php
                      $flag = 0;
                      if (Input::has('pay_way')) {
                          $flag = 1;
                      }
                      ?>
                   
                     @foreach($paymentSetting->setting as $eachGateway)
                      @if($eachGateway['status'] === 'ACTIVE')
                        <label>
                          <input type="radio" id="pay_way" name="pay_way" value="{{$eachGateway['slug']}}"
                          @if(Input::old('pay_way') == $eachGateway['slug']) checked @endif
                          @if($flag == 0) checked <?php $flag = 1;?>@endif>

                          {{$eachGateway['name']}}

                        </label>
                      @endif
                     @endforeach

                  </div>

                </div>

                <div class="col-md-6 col-sm-6 col-xs-12 sm-margin well" style="display:none;" id="bank_details">

                    <?php $bank_details = SiteSetting::module('BankDetails', 'bank_details'); ?>

                    {!! $bank_details !!}

                </div>

                <div class="col-md-12">

                    <button class="btn btn-danger pull-right xs-margin" type="submit" id="button-payment-method">
                      {{trans('catalog/template_two.proceed')}}
                    </button>

                </div>

              </div>

            </div>

          </div>

        </div>

      </div>

    </div>

    </form>

  </div>

</div>

<!-- Promocode Modal Starts-->
<div id="info-modal" class="modal fade" style="" tabindex="-1" aria-hidden="true">

  <div class="modal-dialog">

    <div class="modal-content">

      <div class="modal-header">

        <button type="button" class="close red" data-dismiss="modal" aria-hidden="true">
        </button>

        <h4 class="modal-title center">
          <?php $offer = "Offer";?>
          @if(isset($items_details['promocode'])
            && !empty($items_details['promocode'])
            && is_array($items_details['promocode']))

            <?php

              $offers = (count($items_details['promocode']) > 1 ) ? $offer.'s' : '';

            ?>

          @endif

          <strong>Apply {{$offer}}</strong>

        </h4>

      </div>

      <div class="modal-body">
        <div class="scroller" style="height:300px" data-always-visible="1"
          data-rail-visible1="1">
          <?php

          if(!empty($items_details['promocode']))
          {
            ?>

                @foreach($items_details['promocode'] as $each_promocode)

                  @if("promocode_used" != $each_promocode['offer_price'] && ($each_promocode['offer_price']))

                    <div class="row promocode-modal">

                      <div class="col-md-12 sm-margin">

                      <label>

                        <input type="radio" name="promocode_list" id="promocode_list" value="{{$each_promocode['promocode']}}">
                        <div class="pcode-name"> {{$each_promocode['promocode']}} </div>
                        <div class="pull-right">{{ucwords(trans('promocode.offer_discount'))}}: {{$site_currency_symbol . "" .number_format((float)$each_promocode['offer_price'])}}</div>
                      </label>

                      <ul>

                          <li>

                              {{ucwords(trans('promocode.valid_upto'))}} : {{date('d-m-Y',Timezone::getTimeStamp($each_promocode['end_date']))}}

                          </li>

                          @if(array_get($each_promocode, 'maximum_discount_amount'))

                            <li>
                              {{ucwords(trans('promocode.maximum_discount_amount'))}}: {{number_format((float)$each_promocode['maximum_discount_amount'])}}
                            </li>

                          @endif

                          @if(!empty($each_promocode['terms_and_conditions']))

                            <li>
                              <a href="#" onclick="toggle_visibility('{{$each_promocode['promocode']}}');">{{ucwords(trans('promocode.terms_and_conditions'))}}</a><div id="{{$each_promocode['promocode']}}" style="display: none;">{!! html_entity_decode($each_promocode['terms_and_conditions']) !!}</div>

                            </li>

                          @endif

                      </ul>

                     </div>

                    </div>

                  @endif
                @endforeach

            <?php
          }
          ?>

        </div>

      </div>

      <div class="modal-footer center">

        <p id="copy_promocode" name="copy_promocode" class="text-success"></p>

        <button type="button" class="btn-success pull-right" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;">
          <strong>{{trans('catalog/template_two.ok')}}</strong>
        </button>

      </div>
    </div>
  </div>
</div>
<!-- Promocode Modal End-->

<!-- container -->
<script type="text/javascript">

function doPromoCode()
{

  if($('#promo_code').val() != "")
  {
    var loginURL = $('#promocode_url').val();
    $('#success_coupanCode').text('');
    $('#error_coupanCode').text('');
    $.ajax({
                type : 'post', // define the type of HTTP verb we want to use (POST for our form)
                url : $('#promocode_url').val(), // the url where we want to POST
                data : {
                  'coupanCode':$('#promo_code').val(),
                  'program_slug' :$('#p_slug').val(),
                  'price' : $('#net_total_input').val(),
                  'program_type' : $('#program_type').val(),
                },
          }).success(function(data) {

                 var errors = $.parseJSON(data);
                // console.log(errors);
                if(typeof errors['success'] != 'undefined' && errors['success'])
                {
                  //$('#coupan_section').addClass('hide');
                  $('#success_coupanCode').text(errors['success']);
                  $('#net_total').text(errors['number_format_net_total']);
                  $('#h_net_total').val(errors['net_total']);
                  $('#discount_availed').text(errors['numbeber_format_discount']);
                  $('#d_hidden').val(errors['discount']);
                  $('#error_coupanCode').text('');
                  $('#info-modal').modal('hide');
                  $("#promocode_cancel").removeClass('hide');
                  if(errors['number_format_net_total'] == 0)
                  {
                    checkProgramTotalIsZero();
                  }
                }
                else
                {
                  $('#net_total').text($('#net_total_input').val());
                  $('#discount_availed').text('0');
                  $('#success_coupanCode').text('');
                  $('#error_coupanCode').text('');

                  $('#h_net_total').val($('#net_total_input').val());
                  $('#d_hidden').val('0');
                  $('#error_coupanCode').text(errors['error']);

                }
          });
    }
    else
    {
      alert("Please enter the text to the right promocode.");
    }

}


$( document ).ready(function()
{

  //Bank Transfer
  var value = $( 'input[name=pay_way]:checked' ).val();
  if(value == "BANK TRANSFER"){
    $("#bank_details").show();
  }else{
    $("#bank_details").hide();
  }

  //Form Disbaled
  $('form').submit(function() {
    $(this).find("button[type='submit']").prop('disabled',true);
  });

  $('input[name=promocode_list]:radio').click(function(){

           //$('#copy_promocode').;

           $('#copy_promocode').html('We have copied <strong>'+$('#promocode_list:checked').val()+'</strong> promocode successfully.');
            $('#promo_code').val($('#promocode_list:checked').val());
            $('#net_total').text($('#net_total_input').val());
            $('#discount_availed').text('0');
            $('#success_coupanCode').text('');
            $('#error_coupanCode').text('');

            $('#h_net_total').val($('#net_total_input').val());
            $('#d_hidden').val('0');
            $('#error_coupanCode').text('');
            $("#promocode_cancel").addClass('hide');

        }
    );
});

   $('#info-modal').on('shown.bs.modal', function() {
      $('#copy_promocode').html('');
      //$('#promocode_list:checked').parent().removeClass('checked');
   });

</script>
<script type="text/javascript">
     function toggle_visibility(id) {
       var e = document.getElementById(id);
       if(e.style.display == 'block')
          e.style.display = 'none';
       else
          e.style.display = 'block';
    }

    $("#promocode_cancel").click(function(event){

      event.preventDefault();
      resetPaymentOptions();
      $('#net_total').text($('#net_total_input').val());
      $('#discount_availed').text('0');
      $('#success_coupanCode').text('');
      $('#error_coupanCode').text('');
      $('#promo_code').val('');

      $('#h_net_total').val($('#net_total_input').val());
      $('#d_hidden').val('0');
      $('#error_coupanCode').text('');
      $("#promocode_cancel").addClass('hide');

    });

</script>

<script type="text/javascript">
function checkProgramTotalIsZero()
{
  $('.radio-list').find('input').attr('disabled', true);
  $('.radio-list').find('input:checked').removeAttr('checked').parent().removeClass('checked');
}

function resetPaymentOptions()
{
  if($('#h_net_total').val() == 0)
  {
    $('.radio-list').find('input').attr('disabled', false);
    $('input[name=pay_way]:first').attr('checked', true);
    $('.radio-list').find('input:checked').attr('checked',true).parent().addClass('checked');

  } 
}
</script>

@stop
