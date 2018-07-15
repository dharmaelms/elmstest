@section('content')
<?php
use App\Model\Country;
?>
<style>
  .panel-default { border-bottom: 0  !important;}
</style>
<div class="container">
  <div class="row margin-bottom-40 checkout-page">
      <!-- BEGIN CONTENT -->
      <form action="{{URL::to('checkout/pay')}}" method="post">
      <div class="col-md-12 col-sm-12">
        <h2>CHECKOUT</h2>
        <?php 
        if(Input::old('fullname') !== null)
        {
          $flag = 0;
        }
        else
        {
          $flag = 1;
        }
        ?>
        <!-- BEGIN CHECKOUT PAGE -->
        <div class="panel-group checkout-page" id="checkout-page">
          <input type="hidden" name="p_slug" value="{{$c_data['p_slug']}}">
          <input type="hidden" name="s_slug" value="{{$c_data['s_slug']}}">
          <!-- BEGIN PAYMENT ADDRESS -->
          <div id="payment-address" class="panel panel-default">
            <div class="panel-heading">
              <h2 class="panel-title">Address Details</h2>
            </div>
            <div id="payment-address-content">
              <div class="panel-body row">
                <div class="xs-margin"></div> <!-- space-->             
                <div class="col-md-4 col-sm-4">
                  <div class="form-group" style="">
                      <input type="text" id="fullname" name="fullname" class="form-control" placeholder="Full Name" value="{{Input::old('fullname')}}<?php if($flag === 1) echo $c_data['d_addrs']['fullname'];?>">
                       <span class="text-danger">
                       <?php echo $errors->first('fullname'); ?>
                       </span>
                  </div>
                  <div class="form-group">
                      <input type="text" id="address" name="address" class="form-control" placeholder="Street" value="{{Input::old('address')}}<?php if($flag === 1) echo $c_data['d_addrs']['street'];?>">
                      <span class="text-danger">
                       <?php echo $errors->first('address'); ?>
                       </span>
                  </div>
                  
                  <div class="form-group">
                    <input type="text" id="city" name="city" class="form-control" placeholder="City" value="{{Input::old('city')}}<?php if($flag === 1) echo $c_data['d_addrs']['city'];?>">
                    <span class="text-danger">
                      <?php echo $errors->first('city'); ?>
                    </span>
                  </div>
                </div>
                <div class="col-md-4 col-sm-4">
                    <div class="form-group">
<?php
  $states = array("Andhra Pradesh","Arunachal Pradesh","Assam","Bihar","Chhattisgarh",
"Goa","Gujarat","Haryana","Himachal Pradesh","Jammu and Kashmir","Jharkhand","Karnataka","Kerala","Madhya Pradesh","Maharashtra","Manipur","Meghalaya","Mizoram","Nagaland","Orissa","Punjab","Rajasthan",
"Sikkim","Tamil Nadu","Tripura","Uttar Pradesh","Uttarakhand","West Bengal");
?>                      
                   <input type="text" name="region_state" id="region_state"  class="form-control" placeholder="State" value="{{Input::old('region_state')}}<?php if($flag === 1) echo $c_data['d_addrs']['state'];?>" />

                    <!-- <select class="form-control input-sm" id="region_state" name="region_state">
                      <option value="">Select State</option>
                      @foreach($states as $value)
                        <option value="{{$value}}" <?php if(Input::old('region_state') === $value) echo "selected";?><?php if($flag === 1 && $value === $c_data['d_addrs']['state']) echo "selected";?>>{{$value}}</option>
                      @endforeach                                                                                                              
                    </select> -->
                    <span class="text-danger">
                       <?php echo $errors->first('region_state'); ?>
                    </span>
                  </div>
                  <?php
                      $countries = Country::getCountries();
                    // dd($countries);
                  ?>
                  <div class="form-group">                    
                    <select class="form-control input-sm" id="country" name="country">
                      <option value="">Select Country</option>
                      @foreach($countries as $value)
                      <option value="{{$value['name']}}" <?php if(Input::old('country') === $value['name']) echo "selected";?><?php if($flag === 1 && $value['country_code'] === $c_data['d_addrs']['country']) echo "selected";?>>{{$value['name']}}</option>   
                      @endforeach                                                                 
                    </select>
                    <span class="text-danger">
                       <?php echo $errors->first('country'); ?>
                    </span>
                  </div>
                </div>
                <div class="col-md-4 col-sm-4">
                  <div class="form-group">
                    <input type="text" id="post_code" name="post_code" class="form-control" placeholder="Post Code " value="{{Input::old('post_code')}}<?php if($flag === 1) echo $c_data['d_addrs']['pincode'];?>">
                    <span class="text-danger">
                       <?php echo $errors->first('post_code'); ?>
                       </span>
                  </div>
                  <div class="form-group">
                    <input type="text" id="telephone" name="telephone" class="form-control" placeholder="Mobile Number" value="{{Input::old('telephone')}}<?php if($flag === 1) echo $c_data['d_addrs']['phone'];?>">
                     <span class="text-danger">
                       <?php echo $errors->first('telephone'); ?>
                       </span>
                  </div>
                </div>
              </div>
            </div>
            <!-- END PAYMENT ADDRESS -->
            <!-- BEGIN CONFIRM -->
            <div id="confirm" class="panel panel-default">
              <div class="panel-heading">
                <h2 class="panel-title">Order Summary</h2>
              </div>
              <div id="confirm-content">
                <div class="panel-body row">
                  <div class="col-md-12 clearfix">
                    <div class="table-responsive custom-order-table">
                      <table class="table margin-btm-0">
                        <thead>
                          <tr>
                            <th class="checkout-description" colspan="2">Name</th>
                             @if(isset($c_data['s_duration']))
                            <th class="checkout-quantity" width="100" align="center">Duration</th>
                            @endif
                            <th class="checkout-price center" width="150">Price</th>
                            <th class="checkout-total center" width="150">Amount</th>
                          </tr>
                        </thead>
                        <tbody>
                          <tr>
                            <td width="60">
                              <?php
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
                              <h4 class="margin-top-0 margin-bottom-0">
                                <a href='{{URL::to("catalog/course/".$c_data['p_slug'])}}'>
                                 {{$c_data['p_tite']}}
                                </a>
                              </h4>
                              <p class="font-12">
                                <strong>Type: </strong>
                                  {{$c_data['s_title']}}
                              </p>
                            </td>
                            @if(isset($c_data['s_duration']))
                            <td class="checkout-quantity" width="100" align="center">
                              {{$c_data['s_duration']}}
                            </td>
                            @endif
                            <td class="checkout-price center" width="150">
                              <strong>
                                <span>
                                  <i class="fa fa-rupee"></i>
                                </span>
                                  {{$c_data['price']}}
                                </strong>
                            </td>
                            <td class="checkout-total center" width="150">
                                <strong>
                                  <span>
                                    <i class="fa fa-rupee"></i>
                                  </span>
                                   {{$c_data['price']}}
                                </strong>
                            </td>
                          </tr>
                        </tbody>
                      </table>
                    </div>
                  </div> 
                  <div class="total-table">
                    <div class="col-md-8 col-sm-6 col-xs-12">
                      <div class="xs-margin"></div>
                      <div class="form-group promo-div">
                        <input type="hidden" id="promocode_url" name="promocode_url" value="{{URL::to('checkout/apply-coupon')}}">
                        <span id="success_coupanCode" name="success_coupanCode" class="text-success"></span>
                        <span id="coupan_section" name="coupan_section" class="">                    
                        <div class="col-md-6 col-sm-6">
                        <input type="text" id="promo_code" name="promo_code" class="form-control" placeholder="Promotional/Voucher Code" value="{{Input::old('promo_code')}}" onblur="this.value=this.value.toUpperCase()">                  
                        <div id="error_coupanCode" name="error_coupanCode" class="text-danger"></div>
                        </div>
                        <div class="col-md-6 col-sm-6">
                        </div>
                        <button class="btn btn-danger btn-sm" type="button" onclick="doPromoCode()">Apply</button>
                        </span>
                      </div>
                    </div>
                    <div class="col-md-4 col-sm-6 col-xs-12">  
                      <table class="table xs-margin">
                        <tr>
                          <td align="right">Sub Total</td>
                          <td width="150" align="center">
                            <strong class="price">
                            <span><i class="fa fa-rupee"></i>&nbsp;</span> {{$c_data['price']}}
                            </strong>
                          </td>
                        </tr>
                        <tr>
                          <td align="right">Discount</td>
                          <td width="150" align="center">
                            <strong class="price">
                              <i class="fa fa-rupee"></i>&nbsp;                        
                              <span id="discount_availed" name="discount_availed">
                              @if(Input::old('d_hidden'))
                              {{Input::old('d_hidden')}}
                              @else 
                              0
                              @endif
                              </span>
                              <input type="hidden" name="d_hidden" id="d_hidden" value="">
                            </strong>
                          </td>
                        </tr>
                        <tr>
                          <td align="right">TOTAL</td>
                          <td width="150" align="center">
                            <strong class="price"><i class="fa fa-rupee"></i>&nbsp;
                              <input type="hidden" name="net_total_input" id="net_total_input" value="{{$c_data['price']}}">
                              <input type="hidden" name="h_net_total" id="h_net_total" value="">
                              <span id="net_total" name="net_total">
                                @if(Input::old('h_net_total'))
                                {{Input::old('h_net_total')}}
                                @else
                                {{$c_data['price']}}
                                @endif
                              </span>
                            </strong>
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
              <h2 class="panel-title">Payment</h2>
            </div>
            <div id="payment-method-content">
              <div class="panel-body row">
                <div class="xs-margin"></div> <!-- space-->
                <div class="col-md-12">
                  <p>Please select the preferred payment method to use on this order.</p>
                  <div class="radio-list">
                   <label>
                      <input type="radio" name="pay_way" value="PayUMoney" <?php if($c_data['priceService'] === "free"){ echo "disabled";}else{echo "checked='checked'";}?>> PayUMoney
                    </label>
                  </div>
                  <div class="radio-list">
                    <label>
                      <input type="radio" name="pay_way" value="COD" <?php if($c_data['priceService'] === "free") echo "disabled";?>>Cash on delivery 
                    </label>
                  </div>
                  <div class="radio-list hide">
                    <label>
                      <input type="radio" name="pay_way" value="FREE" <?php if($c_data['priceService'] === "free") echo "checked='checked';";?>>Free
                    </label>
                  </div>
                  <button class="btn btn-danger pull-right xs-margin" type="submit" id="button-payment-method">Proceed</button> 
                </div>
              </div>
            </div>
          </div>
          <!-- END PAYMENT METHOD -->
        </div>
        <!-- END CHECKOUT PAGE -->
      </div>
      
      <!-- END CONTENT -->
  </div>
  </form>
    <!-- END SIDEBAR & CONTENT -->
  </div>
</div>
<!-- container -->
<script type="text/javascript">
function doPromoCode()
{
  if($('#promo_code').val() != ""){
  var loginURL = $('#promocode_url').val();
        $.ajax({
            type : 'post', // define the type of HTTP verb we want to use (POST for our form)
            url : $('#promocode_url').val(), // the url where we want to POST
            data : {
              'coupanCode':$('#promo_code').val(),
              'price' : $('#net_total_input').val() 
            }, 
        }).success(function(data) {
          var errors = $.parseJSON(data);
          // console.log(errors);
          if(typeof errors['success'] != 'undefined' && errors['success'])
          {
            $('#coupan_section').addClass('hide');
            $('#success_coupanCode').text(errors['success']);
            $('#net_total').text(errors['net_total']);
            $('#h_net_total').val(errors['net_total']);
            $('#discount_availed').text(errors['discount']);
            $('#d_hidden').val(errors['discount']);
            $('#error_coupanCode').text('');
          }
          else
          {
            $('#error_coupanCode').text(errors['error']);
          }
      });
    }
    else
    {
      alert("Please enter the text to the right promocode.");
    }
}
</script>
@stop
