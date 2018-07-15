@section('content')
<?php
if(Auth::check()){
  date_default_timezone_set(Auth::user()->timezone);
}else{
  date_default_timezone_set(Config('app.default_timezone'));
}

?>
<link rel="stylesheet" type="text/css" href="{{ asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}" />
<script src="{{ asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>
<script src="{{ asset($theme.'/plugins/jquery.min.js')}}" type="text/javascript"></script>
<script src='https://www.google.com/recaptcha/api.js'></script>
<?php
use App\Libraries\Timezone;
use App\Model\Program;
use App\Model\Package\Entity\Package;

$package_flag=0;
$sitekey=Config('app.captcha_site_key');
$s_flag = 1;
$site_currency = config('app.site_currency');
$site_currency_symbol = $currency_symbol;
?>
<div>
<div class="white-bg">
  <div class="container">
    <div class="row">
      <div class="md-margin"></div>
      <!-- 1st container -->
      <div class="col-md-9 col-sm-9 col-xs-12">
      <div class="row">
        @if(isset($type) && $type == "package")
          <div class="col-md-3 col-sm-3 col-xs-12">
            <?php $p_list = Package::getAllPackageByIDOrSlug($slug); ?>
              @if(isset($p_det_basic['package_cover_media']) && !empty($p_det_basic['package_cover_media']))
                <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{ URL::to('media_image/'.$p_det_basic['package_cover_media'].'/?thumb=178x114') }}">
              @else
                <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{URL::to('portal/theme/default/img/default_channel.png')}}">
              @endif
          </div>
        @else
            <div class="col-md-3 col-sm-3 col-xs-12">
              <?php
                $list = Program::getAllProgramByIDOrSlug('all',$slug);
                    
                if(isset($p_det_basic['program_cover_media']) && !empty($p_det_basic['program_cover_media']))
                    {
                ?>
                <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{ URL::to('media_image/'.$p_det_basic['program_cover_media'].'/?thumb=178x114') }}">
                <?php
                }
                else
                {
                ?>
                @if($p_det_basic['program_type'] == "product")
                <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{URL::to('portal/theme/default/img/default_product.png')}}">
                @else
                <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{URL::to('portal/theme/default/img/default_channel.png')}}">
                @endif
                <?php
                }
                ?>
            </div>
        @endif

        <!-- 1st container ends--> 
        <!-- 2nd container starts -->
        @if(isset($type) && $type == "package")
           <div class="col-md-9 col-sm-9 col-xs-12">
            <h3 class="prod-title m-top-0">{{$p_det_basic['package_title']}}</h3>

                <h4 class="prod-title m-top-0">No of {{Lang::get('program.programs')}}: {{$channel_count}}</h4>
                
                @if($channel_count==0)
                <?php $s_flag = 0; ?>
                @endif

                <p data-rail-visible1="1" data-always-visible="1" style="height: 68px; padding-right: 12px; overflow: auto; width: auto;" class="scroller font-12 sm-margin" data-initialized="1">
                {{$p_det_basic['package_description']}}
                </p>
          </div>
        @else
          <div class="col-md-9 col-sm-9 col-xs-12">
            <h3 class="prod-title m-top-0">{{$p_det_basic['program_title']}}</h3>
                @if($list[0]['program_type'] == 'content_feed' && $list[0]['program_sub_type']=='single' && isset($list[0]['package_ids']) && !empty($list[0]['package_ids']))
                  <?php
                  $second_slug = Package::getPackageDetailsByID($list[0]['package_ids'][0]);
                  ?>
                  
                <a href="<?php echo URL::to("catalog/course/{$second_slug['package_slug']}/package");?>" class="prod-title m-top-0 btn btn-info" style="float:right">{{Lang::get('program.buy')}}</a>
                
                @endif
                <p data-rail-visible1="1" data-always-visible="1" style="height: 68px; padding-right: 12px; overflow: auto; width: auto;" class="scroller font-12 sm-margin" data-initialized="1">
                {{$p_det_basic['program_description']}}
                </p>
          </div>
        @endif

      </div>


      @if(isset($type) && $type == "package")
        <div>
        @if($program_sub_type =='collection' && $channel_count == 0)
           {{ trans('program.unavailable_package_since_no_child') }}
        @else
          <?php  $is_buyed_flag = 0; ?> 
          @if(isset($p_det_subscription) && !empty($p_det_subscription))
            
            <?php $subscription_bayed = null; ?>
            
            <?php foreach($p_det_subscription as $key => $value){
              if(isset($is_buyed['source_id']) && $value['slug'] === $is_buyed['source_id']
                    && time() < (int)$is_buyed['expire_on']) {
                $is_buyed_flag = 1;
                $subscription_bayed = $value;
              }} ?>

            @if(!$is_buyed_flag)
              <div class="table-responsive">
                <table class="custom-table">
                  <thead>
                    <tr>
                      <th>{{Lang::get('catalog/template_two.title')}}</th>
                      <th>{{Lang::get('catalog/template_two.duration')}}</th>
                      <th>{{Lang::get('catalog/template_two.price')}}</th>
                      <th>{{Lang::get('catalog/template_two.disc_price')}}</th>
                      <th width="80"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      foreach ($p_det_subscription as $key => $value) {
                    ?>
                     <tr>
                        <td>{{$value['title']}}</td>
                        <td>{{$value['duration_count']}} 
                        <?php
                            $duration_count = $value['duration_count'];
                            $duration_type = $value['duration_type'];
                            $duration = ($duration_type === 'WW') ? (($duration_count > 1) ? "Weeks" : 'Week'): (($duration_type === 'MM') ? (($duration_count > 1) ? "Months" : 'Month') : (($duration_type === 'DD') ? (($duration_count > 1) ? "Days" : 'Day') : (($duration_count > 1) ? "Years" : 'Year')));
                         ?>
                         {{$duration}}
                        </td>
                        <?php
                          $pricestring = "";
                          $offPrice = "";
                        if(!empty($value['price']))
                        {
                          foreach ($value['price'] as $key => $eachPrice)
                           {
                             if($eachPrice['currency_code'] === $site_currency)
                             {
                                $pricestring .= $site_currency_symbol.number_format((float)$eachPrice['price']);
                                if(!empty($eachPrice['markprice']))
                                {
                                  $offPrice .= $site_currency_symbol.number_format((float)$eachPrice['markprice']); 
                                }
                             }
                          }
                          $flag = 1;
                        }
                        else
                        {
                          $flag = 0;
                        }
                        ?>
                      <td width="70px">
                          <?php if($pricestring != "" && $offPrice != ""){?>                           
                        <span style="text-decoration: line-through;color:RED">
                          <?php echo $pricestring;?>
                        </span>
                            <?php }else if($offPrice === "" && $pricestring != ""){
                              ?>
                               <?php echo $pricestring;?>
                              <?php 
                            }else{
                                echo "FREE";
                              }
                              ?>
                      </td>
                      <td>
                        <span>
                          <?php if($offPrice != ""){?>
                            <?php echo $offPrice;?>
                            <?php }else if($pricestring === ""){
                                echo "FREE";
                             }else{
                              ?>
                                N/A
                              <?php  } ?>
                          </span>
                      </td>
                      <td width="80">                       
                        <?php
                        $subUrl = "checkout/place-order/{$p_det_basic['package_slug']}/{$value['slug']}/package";?>
                        @if(isset($is_buyed['valid_from']) && $flag != 0)
                        <?php
                            if(time() > (int) $is_buyed['valid_from'] && time() < (int) $is_buyed['expire_on'])
                            {
                                ?>
                               
                                <?php
                            }
                            else
                            {
                                ?>
                                <a href="{{URL::to($subUrl)}}" class="btn btn-danger btn-sm {{$is_loggedin}} subscribe catalog" data-catalog="{{$p_det_basic['package_slug'].'/'.$value['slug']}}" href="#">{{Lang::get('catalog/template_two.subscribe')}}</a>
                                <?php
                            }
                        ?>                          
                        @elseif(empty($is_buyed) || $is_buyed === 'disable' && $s_flag > 0)
                        <a href="{{URL::to($subUrl)}}" class="btn btn-danger btn-sm {{$is_loggedin}} subscribe catalog" data-catalog="{{$p_det_basic['package_slug'].'/'.$value['slug']}}" href="#">{{Lang::get('catalog/template_two.subscribe')}}</a>
                        @else
                          <a href="{{URL::to($subUrl)}}" class="btn btn-danger btn-sm {{$is_loggedin}} subscribe catalog" data-catalog="{{$p_det_basic['package_slug'].'/'.$value['slug']}}" href="#">{{Lang::get('catalog/template_two.subscribe')}}</a>
                        @endif
                      </td>
                    </tr>
                    <?php
                      }
                      ?>
                  </tbody>
                </table>
              </div>
            @endif

            @if(Auth::check() && !empty($is_buyed) && $is_buyed != 'disable' && isset($is_buyed['expire_on']) && time() < (int) $is_buyed['expire_on'])
                  <tr>
                    <td colspan="5" class="text-center">
                    Subscribed for {{$subscription_bayed['duration_count']}}                   
                     <?php
                            $duration_type = $value['duration_type'];
                            $duration_count = $value['duration_count'];
                            $duration = ($duration_type === 'WW') ? (($duration_count > 1) ? "Weeks" : 'Week'): (($duration_type === 'MM') ? (($duration_count > 1) ? "Months" : 'Month') : (($duration_type === 'DD') ? (($duration_count > 1) ? "Days" : 'Day') : (($duration_count > 1) ? "Years" : 'Year')));
                           
                      ?>
                    {{$duration}}
                    until
                    {{Timezone::convertFromUTC('@'.$is_buyed['expire_on'], Auth::user()->timezone, 'F jS, Y')}}
                   
                
                       <?php
                       $package_flag=1;
                       $st_learn = "package/detail/{$p_det_basic['package_slug']}";
                       ?>
                       &nbsp;&nbsp;<a class="btn btn-success pull-right" href="{{URL::to($st_learn)}}">{{Lang::get('catalog/template_two.learn_now')}}</a>
                    </td>
                  </tr>
            @endif
          @endif
        @endif

        </div>
      @else 
        <div>  
          <?php
                $is_buyed_flag = 0;
              if(isset($p_det_subscription) && !empty($p_det_subscription))
              {
                $subscription_bayed = null;
                foreach ($p_det_subscription as $key => $value) {                
                  
                  if(isset($is_buyed['source_id']) && $value['slug'] === $is_buyed['source_id']
                    && time() < (int)$is_buyed['expire_on'])
                  {
                     $is_buyed_flag = 1;
                     $subscription_bayed = $value;
                  }
                }

              ?>
              @if(!$is_buyed_flag && $p_det_basic['program_type'] == "content_feed")
                <div class="table-responsive">
                <table class="custom-table">
                  <thead>
                    <tr>
                      <th>{{Lang::get('catalog/template_two.title')}}</th>
                      <th>{{Lang::get('catalog/template_two.duration')}}</th>
                      <th>{{Lang::get('catalog/template_two.price')}}</th>
                      <th>{{Lang::get('catalog/template_two.disc_price')}}</th>
                      <th width="80"></th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                      foreach ($p_det_subscription as $key => $value) {
                    ?>
                     <tr>
                        <td>{{$value['title']}}</td>
                        <td>{{$value['duration_count']}} 
                        <?php
                            $duration_count = $value['duration_count'];
                            $duration_type = $value['duration_type'];
                            $duration = ($duration_type === 'WW') ? (($duration_count > 1) ? "Weeks" : 'Week'): (($duration_type === 'MM') ? (($duration_count > 1) ? "Months" : 'Month') : (($duration_type === 'DD') ? (($duration_count > 1) ? "Days" : 'Day') : (($duration_count > 1) ? "Years" : 'Year')));
                         ?>
                         {{$duration}}
                        </td>
                        <?php
                          $pricestring = "";
                          $offPrice = "";
                        if(!empty($value['price']))
                        {
                          foreach ($value['price'] as $key => $eachPrice)
                           {
                             if($eachPrice['currency_code'] === $site_currency)
                             {
                                $pricestring .= $site_currency_symbol.number_format((float)$eachPrice['price']);
                                if(!empty($eachPrice['markprice']))
                                {
                                  $offPrice .= $site_currency_symbol.number_format((float)$eachPrice['markprice']); 
                                }
                             }
                          }
                          $flag = 1;
                        }
                        else
                        {
                          $flag = 0;
                        }
                        ?>
                      <td width="70px">
                          <?php if($pricestring != "" && $offPrice != ""){?>                           
                        <span style="text-decoration: line-through;color:RED">
                          <?php echo $pricestring;?>
                        </span>
                            <?php }else if($offPrice === "" && $pricestring != ""){
                              ?>
                               <?php echo $pricestring;?>
                              <?php 
                            }else{
                                echo "FREE";
                              }
                              ?>
                      </td>
                      <td>
                        <span>
                          <?php if($offPrice != ""){?>
                            <?php echo $offPrice;?>
                            <?php }else if($pricestring === ""){
                                echo "FREE";
                             }else{
                              ?>
                                N/A
                              <?php  } ?>
                          </span>
                      </td>
                      <td width="80">                       
                        <?php
                        $subUrl = "checkout/place-order/{$p_det_basic['program_slug']}/{$value['slug']}";?>
                        @if(isset($is_buyed['valid_from']) && $flag != 0)
                        <?php
                            if(time() > (int) $is_buyed['valid_from'] && time() < (int) $is_buyed['expire_on'])
                            {
                                ?>
                               
                                <?php
                            }
                            else
                            {
                                ?>
                                <a href="{{URL::to($subUrl)}}" class="btn btn-danger btn-sm {{$is_loggedin}} subscribe catalog" data-catalog="{{$p_det_basic['program_slug'].'/'.$value['slug']}}" href="#">{{Lang::get('catalog/template_two.subscribe')}}</a>
                                <?php
                            }
                        ?>                          
                        @elseif(empty($is_buyed) || $is_buyed === 'disable' && $s_flag > 0)
                        <a href="{{URL::to($subUrl)}}" class="btn btn-danger btn-sm {{$is_loggedin}} subscribe catalog" data-catalog="{{$p_det_basic['program_slug'].'/'.$value['slug']}}" href="#">{{Lang::get('catalog/template_two.subscribe')}}</a>
                        @else
                          <a href="{{URL::to($subUrl)}}" class="btn btn-danger btn-sm {{$is_loggedin}} subscribe catalog" data-catalog="{{$p_det_basic['program_slug'].'/'.$value['slug']}}" href="#">{{Lang::get('catalog/template_two.subscribe')}}</a>
                        @endif
                      </td>
                    </tr>
                    <?php
                      }
                      ?>
                  </tbody>
                </table>
              </div>
              @endif
              @if($p_det_basic['program_type'] == "content_feed" && Auth::check() && !empty($is_buyed) && $is_buyed != 'disable' && isset($is_buyed['expire_on']) && time() < (int) $is_buyed['expire_on'])
                  <tr>
                    <td colspan="5" class="text-center">
                    Subscribed for {{$subscription_bayed['duration_count']}}                   
                     <?php
                            $duration_type = $value['duration_type'];
                            $duration_count = $value['duration_count'];
                            $duration = ($duration_type === 'WW') ? (($duration_count > 1) ? "Weeks" : 'Week'): (($duration_type === 'MM') ? (($duration_count > 1) ? "Months" : 'Month') : (($duration_type === 'DD') ? (($duration_count > 1) ? "Days" : 'Day') : (($duration_count > 1) ? "Years" : 'Year')));
                           
                      ?>
                    {{$duration}}
                    until
                    {{Timezone::convertFromUTC('@'.$is_buyed['expire_on'], Auth::user()->timezone, 'F jS, Y')}}
                   
                
                       <?php
                       $package_flag=1;
                       $st_learn = "program/packets/{$p_det_basic['program_slug']}";
                       ?>
                       &nbsp;&nbsp;<a class="btn btn-success pull-right" href="{{URL::to($st_learn)}}">{{Lang::get('catalog/template_two.learn_now')}}</a>
                    </td>
                  </tr>
              @endif

                <?php
              }
              if($p_det_basic['program_type'] == "product")
              {
                if(isset($p_phy_details) && !empty($p_phy_details))
                { 
                  ?>
                    <div class="table-wrapper-responsive">
                        <table class="custom-table">
                          <thead>
                            <tr>
                              <th>Name</th>
                              <th>Price</th>
                              <th>Discounted Price</th>
                              <th></th>
                            </tr>
                          </thead>
                          <tbody>
                            <?php 
                              foreach($p_phy_details['vertical'] as $eachVariant)
                                {
                                  $pricestring = "";
                                  $offPrice = "";
                                  if(!empty($eachVariant['price']))
                                    {
                                      foreach ($eachVariant['price'] as $key => $eachPrice)
                                       {
                                         if($eachPrice['currency_code'] === $site_currency)
                                         {
                                            $pricestring .= $site_currency_symbol.number_format((float)$eachPrice['price']);
                                            if(!empty($eachPrice['markprice']))
                                            {
                                              $offPrice .= $site_currency_symbol.number_format((float)$eachPrice['markprice']); 
                                            }
                                         }
                                      }
                                    }
                                   ?>
                                  <tr>
                                    <td>{{ $eachVariant['title'] }} </td>
                                    <td >
                                        <?php if($offPrice != "" && $pricestring != ""){?>
                                          <span style="text-decoration: line-through;color:RED">
                                            <?php echo $pricestring;?>
                                          </span>
                                        <?php }else if($offPrice === ""){ ?>
                                            <?php echo $pricestring;?>
                                        <?php }else{ ?>
                                            <span>
                                              N/A
                                            </span>
                                        <?php } ?>
                                    </td>
                                   <td width="100">
                                    <span>
                                      <?php if($offPrice === ""){?>
                                        N/A
                                      <?php }else{
                                        echo $offPrice;
                                         } ?>
                                    </span>
                                   </td>
                                    <td width="100">
                                     <?php $subUrl = "checkout/place-order/{$p_det_basic['program_slug']}/{$eachVariant['slug']}";?>
                                     @if(isset($p_det_basic['program_access']) && $p_det_basic['program_access'] == "general_access")
                                      <a class="btn btn-danger btn-sm  {{$is_loggedin}} subscribe catalog" data-baseurl="" data-productid="{{ $p_det_basic['program_id'] }}" data-catalog="{{$p_det_basic['program_slug'].'/'.$eachVariant['slug']}}" href="{{URL::to($subUrl)}}">{{Lang::get('catalog/template_two.buy_now')}}</a>
                                      @else
                                      <a class="btn btn-danger btn-sm  {{$is_loggedin}} catalog" data-baseurl="" data-productid="{{ $p_det_basic['program_id'] }}" data-catalog="{{$p_det_basic['program_slug'].'/'.$eachVariant['slug']}}" href="{{URL::to($subUrl)}}">{{Lang::get('catalog/template_two.buy_now')}}</a>
                                      @endif

                                    </td>
                                  </tr>
                            
                          <?php } ?>
                            
                          </tbody>
                        </table>
                      </div>
                  <?php

                }
              
               }
                ?> 
                @if($p_det_basic['program_type'] == "course")  
                  <div class="table-wrapper-responsive">
                    @include('portal.theme.default.catalog.__batch',['list'=>$p_det_subscription,'site_currency_symbol'=>$site_currency_symbol,'site_currency'=>$site_currency,'program_slug'=>$p_det_basic['program_slug'],'is_buyed' => $is_buyed])
                  </div>
                @endif
        </div>
      @endif



        </div>
        <!-- 2nd container ends -->
        <!-- 3rd container starts -->
        <div class="col-md-3 col-sm-3 col-xs-12">
            
              <div class="row">
                <div class="col-md-12 sm-margin center">
                
                <?php

                  $is_paid_program = function() use ($p_det_subscription)
                  {

                    if(!empty($p_det_subscription))
                    {
                      
                      foreach ($p_det_subscription as $key => $value) 
                      {
                          
                          if(array_get($value, 'type', '') === 'paid')
                          {
                          
                            return true;
                          
                          }

                      }

                    }
                    
                    return false;
                  };

                ?>

                @if(config('app.promocode_user_enabled') === true && $is_paid_program())
                    <?php $offer = "Offer";?>
                        @if(isset($promocode_details) 
                          && !empty($promocode_details) 
                          && is_array($promocode_details))
                          <a href="#" class="btn btn-primary" data-toggle="modal" data-target="#viewpromocode-modal">
                              
                                <?php

                                  $offers = (count($promocode_details) > 1 ) ? 
                                            $offer.'s' : 
                                            '';
                                
                                ?>
                              
                              View {{$offer}}
                    </a>
                    @endif
                  @endif
                </div>
              </div>
              <!-- promocode -->

              <div style="background: #F3F3F3;border: 1px solid #ccc;padding: 0 15px;">
                
            
            <!-- From here catalog name and discription are removed as per BUG-2597 -->
              <div class="form-div-title"><h3>Contact Us</h3></div>
                
              @if(Session::get('success'))
                <div class="alert alert-success" id="alert-success">
                <button class="close" data-dismiss="alert">×</button>
                  {{ Session::get('success') }}
                  </div>
              <span class="help-inline green">
                  <!-- <strong>Success!</strong><br> -->
                  
              </span>
                
              <?php Session::forget('success'); ?>
          @endif

          <form action="{{ url('contactus/enquiry') }}" method="post">
          
            <?php
            if(Auth::check()) {
            $name=Auth::user()->firstname.' '.Auth::user()->lastname;
            $email=Auth::user()->email;
            $mobile=Auth::user()->mobile;
            }
            else {
            $name='';
            $email='';
            $mobile='';
            }
            
            if(Input::old('name')!='')
            $name=Input::old('name');
            if(Input::old('email')!='')
            $email=Input::old('email');
            if(Input::old('mobile')!='')
            $mobile=Input::old('mobile');
            
            ?>
            
            <div class="form-group">
            <!--<label class="col-sm-3 col-lg-2 control-label" for="name">Name<span class="red">*</span></label>-->
            <div class="controls">
              <input type="text" class="form-control" name="name" value="{{$name}}" placeholder="Name*">
              {!! $errors->first('name', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              </div>
          </div>
            
             <div class="form-group">
            <!--<label class="col-sm-3 col-lg-2 control-label" for="email">Email<span class="red">*</span></label>-->
            <div class="controls">
              <input type="text" class="form-control" name="email" value="{{$email}}" placeholder="Email*">
              {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              </div>
          </div>
            
             <div class="form-group">
            <!--<label class="col-sm-3 col-lg-2 control-label" for="mobile">Mobile</label>-->
            <div class="controls">
              <input type="text" class="form-control" name="mobile" value="{{$mobile}}" placeholder="Mobile">
              {!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>')!!}
              </div>
          </div>
            
            <div class="form-group">
             <!-- <label class="col-sm-3 col-lg-2 control-label" for="message">Message<span class="red">*</span></label>-->
              <div class="controls">
                <textarea id="textarea" class="form-control" rows="2" name="message" placeholder="Your Message*">{{Input::old('message')}}</textarea>
                {!! $errors->first('message', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
          </div>
            
            <div class="form-group">
            <div class="controls">
            <div class="g-recaptcha" data-sitekey={{$sitekey}} style="transform:scale(0.86);transform-origin:0;-webkit-transform:scale(0.86);
transform:scale(0.86);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>
            </div>
            </div>
              
              <div class="form-group">
           <div class="controls">
            {!! $errors->first('g-recaptcha-response', '<span class="help-inline" style="color:#f00">:message</span>') !!}
               </div>  
            </div>
              
              
              
              
              
            <div class="form-group">
            <div class="col-sm-9 col-lg-10 controls">
            <input type="hidden" name="slug" value="{{$slug}}">
            </div>
              
          </div>        
              
            <div class="form-group last">
            <input type="submit" class="btn btn-info" value="SUBMIT">
          </div>
            
          </form>
      
          </div>  
        </div>
        <!-- 3rd container ends -->

    </div>
  </div>
</div><br>    
<style type="text/css">
.category-list .packet {
    height: 170px !important;
    min-height: 160px !important;
}
.table-responsive{ margin-top:30px; }
.nav-tabs>li {
  float:none !important;
}
#myTab1 {
  float:left !important;
}
.nav-tabs li a{
  border-bottom: 2px solid #d0d0d0 !important;
  color: #000 !important;
  -moz-border-bottom: 2px solid #d0d0d0 !important;
  -o-border-bottom: 2px solid #d0d0d0 !important;
  -webkit-border-bottom: 2px solid #d0d0d0 !important;
}
.nav-tabs li a:focus {  
  /*border:0px !important;*/
  background: #eeeeee !important;
  color:  #000 !important;
}
.nav-tabs>li.active a{  
  border:0px !important;
  background: #fff !important;
  color:  #000 !important;
  font-weight: bold;
  border-bottom: 3px solid #72b8f2 !important;

}
@media screen and (max-width: 519px) and (min-width: 320px) {
    iframe{width: 100%;}
    .alert-success iframe{width: 100%; min-height: 150px;}
}
@media screen and (max-width: 699px) and (min-width: 520px) {
    iframe{width:100%; height:221px;}
    .alert-success iframe{width: 400px; min-height:235px;}
}
@media screen and (max-width: 1000px) and (min-width: 700px) {
    iframe{width:100%; height:337px;}
    .alert-success iframe{width: 400px; min-height:170px;}
}
@media screen and (min-width: 1001px) {
    iframe{width:100%; min-height:350px;}
    .alert-success iframe{width: 550px;min-height: 350px;}
}
.need_login{
  width: 100% !important;
}
.overlay{
  position:absolute;
  top:0px;
  width:100%;
  display:block;
  height:100%;
  opacity: 0.7;
}
.default-img{
  width: 100%;
  position:relative;
  display: table;
}
</style>

@if(isset($p_det_basic['program_access']) && $p_det_basic['program_access'] == "general_access")
@if(isset($posts) && !empty($posts))
<div class="container">
  <div class="row category-list">
    <div class="col-md-12 sale-product">
      <span class="center"><h3>Start Learning For Free</h3></span>
      <hr class="border-black">
      <div id="payment-address-content" class="lg-margin">
        <div class="facets-data">
          <div id="grid" class="row product-list margin-bottom-10">
            @foreach($posts as $post)
              <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 md-margin">
                <div class="packet">
                <figure>
                    @if(isset($p_det_basic['program_access']) && $p_det_basic['program_access'] == "general_access")
                      <a href="{{URL::to("program/packet/{$post['packet_slug']}?requestUrl=general")}}" data-productid="{{ $p_det_basic['program_id'] }}" data-baseurl="{{ URL::to('/') }}" data-posts="{{ $p_det_basic['program_id'].'/'.$post['packet_slug'] }}" class="{{$is_loggedin}} subscribe posts_url">@if(isset($post['packet_cover_media']) && !empty($post['packet_cover_media']))<img src="{{ URL::to('media_image/'.$post['packet_cover_media'].'/?thumb=178x114') }}" class="img-responsive" alt="{{ $post['packet_title'] }}">@else<img src="{{ URL::to('portal/theme/default/img/book.jpg') }}" class="packet-img img-responsive" alt="{{ $post['packet_title'] }}">@endif</a>
                  
                      @endif
                    </figure>
                    <p class="packet-title">
                    @if(isset($p_det_basic['program_access']) && $p_det_basic['program_access'] == "general_access")
                    <a href="{{URL::to("program/packet/{$post['packet_slug']}?requestUrl=general")}}" data-productid="{{ $p_det_basic['program_id'] }}" data-baseurl="{{ URL::to('/') }}" data-posts="{{ $p_det_basic['program_id'].'/'.$post['packet_slug'] }}" class="{{$is_loggedin}} subscribe posts_url"><b>{{ $post['packet_title'] }}</b></a>
                    @else
                    <a class="disabled" title="Restricted Access"><b>{{ $post['packet_title'] }}</b></a>
                    @endif</p>
                </div>
              </div>
              @endforeach
          </div>
        </div>
      </div>
    </div>
  </div>
</div>
@endif
@endif

<!--End-->
<!--start of channels in collection-->
@if($type =='package' && $channel_count == 0)
<h4 style="margin-left:20%;">{{Lang::get('program.no_child')}}</h4>
@endif

@if($type =='package' && $channel_count > 0)
<!--display starts here-->
<div class="container">
<div class="row"><!--start3-->
                         <div class="col-md-12">
                            <h3 class="page-title-small" style="margin-top: -4px;">
                              <a href="">{{Lang::get('program.courses_list')}}</a>
                            </h3>
                        </div>
     <div class="facets-data"><!--start2-->
           <?php
             $child_list = Package::getAllPackageByIDOrSlug($slug);

            if(isset($child_list[0]['program_ids']) && !empty($child_list[0]['program_ids']))
            {
             foreach($child_list[0]['program_ids'] as $child_id)
             { 
                $details=Program::getProgramDetailsByID($child_id);
                
            ?>
                        
                <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 md-margin"><!--start1-->
                    <div class="packet">
                          <figure>
                          @if($details['program_sellability']=='yes')
                          <a href="<?php echo URL::to("catalog/course/{$details['program_slug']}");?>" title="Video name">
                             <?php
                             if(isset($details['program_cover_media']) && !empty($details['program_cover_media'])) {
                             ?>
                     <img src="{{ URL::to('media_image/'.$details['program_cover_media'].'/?thumb=178x114') }}" alt="Program" class="packet-img img-responsive">
                     <?php
                      }
                      else
                      {
                      ?>
                      <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{URL::to('portal/theme/default/img/default_channel.png')}}">
                      <?php
                      }
                      ?>
                      </a>
                      @endif
                      @if($details['program_sellability']=='no')
                          <a title="{{Lang::get('program.error_msg')}}">
                             <?php
                             if(isset($details['program_cover_media']) && !empty($details['program_cover_media'])) {
                             ?>
                     <img src="{{ URL::to('media_image/'.$details['program_cover_media'].'/?thumb=178x114') }}" alt="Program" class="packet-img img-responsive">
                     <?php
                      }
                      else
                      {
                      ?>
                      <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{URL::to('portal/theme/default/img/default_channel.png')}}">
                      <?php
                      }
                      ?>
                      </a>
                      @endif
                      </figure>
                          <div>
                          <p class="packet-title">
                          @if($details['program_sellability']=='yes')
                          <a href="<?php echo URL::to("catalog/course/{$details['program_slug']}");?>">
                          <strong>
                          {{str_limit($details['program_title'], $limit = 50, $end = '...')}}                             
                          </strong>
                           </a>
                          @endif
                          @if($details['program_sellability']=='no')
                          <a title="{{Lang::get('program.error_msg')}}">
                          <strong>
                          {{str_limit($details['program_title'], $limit = 50, $end = '...')}}                             
                          </strong>
                           </a>
                          @endif
                           </p>
                            @if($package_flag==1)
                            
                            <p class="packet-title">
                            <a href= "<?php echo URL::to("program/packets/{$details['program_slug']}");?>" class="btn btn-success btn-sm">Learn Now</a>
                            </p>
                            @endif
                           </div>
                        </div><!--packet-->
                  </div><!--start1-->
                        
                   <?php
                    }
                    }
                    ?>
     </div><!--start2-->
</div><!--start3-->
</div>
<!--display ends here-->
@endif

<!--end of channels in collection-->
<!-- Tabs Begins-->
<div class="box">
<?php
if(!empty($p_det_tabs))
{
?>
<div class="container">
  <div class="row">
    <div class="col-md-9 col-sm-8 col-xs-12">
      <div class="tabbable coursedetail-tabs thumbnail">
        <ul id="myTab1" class="nav nav-tabs">
        <?php
            $active = 0;
            foreach ($p_det_tabs as $key => $value) {
              if($active === 0)
              {
        ?>
                <li class="active"><a href="#{{$value['slug']}}" data-toggle="tab" title="{{$value['title']}}">{{str_limit($value['title'], config('app.tab_char_limit'))}}</a></li>
        <?php
              $active = 1;
              }
              else
              {
        ?>
                <li><a href="#{{$value['slug']}}" data-toggle="tab" title="{{$value['title']}}">{{str_limit($value['title'],  config('app.tab_char_limit'))}}</a></li>
        <?php        
              }
            }
        ?>
         </ul>
         <div class="tab-content">
         <?php
            $active = 0;
            foreach ($p_det_tabs as $key => $value) {
              if($active === 0)
              {
        ?>
                 <div class="tab-pane fade active in" id="{{$value['slug']}}">
                   <?php echo $value['description'];?>
                </div>
        <?php
              $active = 1;
              }
              else
              {
        ?>
                <div class="tab-pane fade" id="{{$value['slug']}}">
                      <?php echo $value['description'];?>
                </div>
        <?php        
              }
            }
        ?>
        </div>
      </div>
    </div>
    @if(isset($type) && $type == "package")
      <div class="col-md-3 col-sm-4 col-xs-12 recommend-list">
        <h4>Recommendations</h4>
        <div>
          <ul>
          <?php
            if(!empty($p_det_related_program))
            {
              foreach ($p_det_related_program as $key => $eachProgram) 
              {
            ?>
            <li>
              <div class="col-md-12 padding-0">
                <div class="img-div">
                  <?php
                      if(isset($eachProgram['package_cover_media']) && 
                        !empty($eachProgram['package_cover_media']))
                        {
                  ?>
                    <img src="{{ URL::to('media_image/'.$eachProgram['package_cover_media'].'/?thumb=178x114') }}" alt="Video Name">
                    <?php
                         }
                         else
                         {
                    ?>
                   <img src="{{URL::to('portal/theme/default/img/default_packet.jpg')}}" alt="Video Name" >
                  <?php
                        }
                  ?> </div>
                <div class="data-div">
                  <a href="<?php echo URL::to("catalog/course/{$eachProgram['package_slug']}/package");?>"><strong>{{$eachProgram['package_title']}}</strong></a>
                </div>
              </div>
            </li>
            <?php
              }
             }
            ?>
          </ul>
        </div>
      </div>
    @else
      <div class="col-md-3 col-sm-4 col-xs-12 recommend-list">
        <h4>Recommendations</h4>
        <div>
          <ul>
          <?php
            if(!empty($p_det_related_program))
            {
              foreach ($p_det_related_program as $key => $eachProgram) 
              {
            ?>
            <li>
              <div class="col-md-12 padding-0">
                <div class="img-div">
                  <?php
                      if(isset($eachProgram['program_cover_media']) && 
                        !empty($eachProgram['program_cover_media']))
                        {
                  ?>
                    <img src="{{ URL::to('media_image/'.$eachProgram['program_cover_media'].'/?thumb=178x114') }}" alt="Video Name">
                    <?php
                         }
                         else
                         {
                    ?>
                   <img src="{{URL::to('portal/theme/default/img/default_packet.jpg')}}" alt="Video Name" >
                  <?php
                        }
                  ?> </div>
                <div class="data-div">
                  <a href="<?php echo URL::to("catalog/course/{$eachProgram['program_slug']}");?>"><strong>{{$eachProgram['program_title']}}</strong></a>
                </div>
              </div>
            </li>
            <?php
              }
             }
            ?>
          </ul>
        </div>
      </div>
    @endif

  </div>
</div>
<?php
}
?>
</div>
<!-- Tab Ends-->
<!--posts list END-->
</div>

</div>

<!-- Promocode Modal Starts-->
<div id="viewpromocode-modal" class="modal fade" style="" tabindex="-1" aria-hidden="true">

  <div class="modal-dialog">
    
    <div class="modal-content">
      
      <div class="modal-header">
        
        <button type="button" class="close red" data-dismiss="modal" aria-hidden="true">
        </button>
        
        <h4 class="modal-title center">
          <strong>Offers</strong>
        </h4>

      </div>

      <div class="modal-body">
        <div class="scroller" style="height:310px" data-always-visible="1" 
          data-rail-visible1="1">         
             @if(!empty($promocode_details) && is_array($promocode_details))
                  @foreach($promocode_details as $each_promocode)
                 <div class="promocode-modal">
                    
                    <div class="col-md-12 sm-margin">
                    
                    <label>
                      Promo Code:&nbsp;&nbsp;
                      <div class="pcode-name"> 
                        {{$each_promocode['promocode']}} 
                      </div>
                    </label>
                    
                    <ul style="padding-left: 10px;">
                        
                        <li>

                            {{ucwords(trans('promocode.valid_upto'))}} : {{date('d-m-Y',Timezone::getTimeStamp($each_promocode['end_date']))}}

                        </li>
                        
                        <li>
                          <?php
                            $discount = ($each_promocode['discount_type'] != 'unit') ?
                                        $each_promocode['discount_value'].'%' :
                                        $site_currency_symbol." ".$each_promocode['discount_value'];
                          ?>
                          {{ucwords(trans('promocode.discount_amount'))}}: {{$discount}}
                        </li>
                        @if(array_get($each_promocode, 'minimum_order_amount'))
                          <li>
                            {{ucwords(trans('promocode.minimun_order_amount'))}}: {{$each_promocode['minimum_order_amount']}}
                          </li>
                        @endif
                        @if(array_get($each_promocode, 'maximum_discount_amount'))
                         <li>
                           {{ucwords(trans('promocode.maximum_discount_amount'))}}: {{$each_promocode['maximum_discount_amount']}}
                          </li>
                        @endif
                        @if(array_get($each_promocode,'terms_and_conditions'))
                          <li>
                            <a href="#" onclick="toggle_visibility('{{$each_promocode['promocode']}}');">{{ucwords(trans('promocode.terms_and_conditions'))}}</a><div id="{{$each_promocode['promocode']}}" style="display: none;">{!! html_entity_decode($each_promocode['terms_and_conditions']) !!}</div>
                          </li>
                        @endif
                    </ul>
                   
                   </div>
                  
                  </div>
                  @endforeach
                @endif
        </div>

      </div>

      <div class="modal-footer center">

        <p id="copy_promocode" name="copy_promocode" class="text-success"></p>

        <button type="button" class="btn-success pull-right" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;">
          <strong>OK</strong>
        </button>
      
      </div>
    </div>
  </div>
</div>
<!-- Promocode Modal End-->



<script type="text/javascript">
$('.overlay').click(function(){
  $('#signinreg').modal('show');
});
<?php if(Auth::check()){
$user_id = Auth::user()->uid;
 ?>
$('.subscribe').on('click',function(event){
  var UID = '<?php echo $user_id; ?>';
  var EnrollProductId = $(this).data('productid');
  var Baseurl = $(this).data('baseurl');
  $(this).attr('disabled','disabled');
  $.ajax({
            type : 'post', // define the type of HTTP verb we want to use (POST for our form)
            url : Baseurl+'/enroll-user-to-product', // the url where we want to POST
            data : {
                'product_id' : EnrollProductId,
                'user_id' : UID
                
            }, 
        }).success(function(data) {   
      });
});

<?php }else{ ?>
var URLToRedirect;
 $('.is_loggedin').on('click',function(event){
  window.URLToRedirect = $(this).attr('href');
     event.preventDefault();
      $('#signinreg').modal('show');
});
jQuery(".is_loggedin").keypress(function (e) {
       if (e.keyCode == 13 && !(jQuery(this).hasClass('classTest')) ) {           
               alert('enter!');                   
       }
});
<?php } ?>

</script>

<script src="{{ URL::asset($theme.'/js/custom-front-end/product_details.js')}}"></script>

<script type="text/javascript">
  function random(){
      $('.owl-carousel').each(function(pos,value){
        $(value).owlCarousel({
          items:4,
          navigation: true,
          navigationText: [
          "<i class='fa fa-caret-left'></i>",
          "<i class='fa fa-caret-right'></i>"
          ],
          beforeInit : function(elem){
            
          } 
        });
    });
  }
    $(document).ready(function() {
    //Sort random function
      random();
      $('#alert-success').delay(5000).fadeOut();
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

    function listPromocodeShow()
    {
      if($('#promocode').hasClass('hide'))
      {
        $('#promocode').removeClass('hide');
      }
      else
      {
        $('#promocode').addClass('hide');
      }
    }
</script>
<script type="text/javascript">
  $('.catalog').on('click',function(event){
  var url_catalog = $(this).data('catalog');
  catalog_url = url_catalog;
  });
  $('.posts_url').on('click',function(event){
  var url_posts = $(this).data('posts');
  posts_url = url_posts;
  });
</script>
@stop
