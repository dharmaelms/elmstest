@section('content')

<?php
use App\Libraries\Timezone;
use App\Model\Program;
$package_flag=0;
$sitekey=Config('app.captcha_site_key');
$s_flag = 1;
$site_currency = config('app.site_currency');
$site_currency_symbol = $currency_symbol;
?>
<?php 
$timezone = 'Asia/Kolkata';
?>
<style type="text/css">
  .category-list .packet {
    height: 170px !important;
    min-height: 160px !important;
  }
  .tabs-div ul { padding-left: 12px; }
</style>

<script src='https://www.google.com/recaptcha/api.js'></script>

  
  <!--BEGIN Main Container -->
  <div class="main">
    <div class="container">
      <div class="margin-bottom-20"></div>
      <div class="row">
        <div class="col-md-9 col-sm-8 col-xs-12">
          <div class="col-md-12 col-sm-12 col-xs-12 margin-bottom-20" style="border-bottom:1px solid #dddddd;">
          
            <h3 class="font-weight-400 black margin-bottom-10">{{$p_det_basic['program_title']}}</h3>
            <?php $list=Program::getAllProgramByIDOrSlug('all',$slug);
            // print_r($program_sub_type);die;
           ?>
            @if($program_sub_type=='collection')
              <h4 class="prod-title m-top-0">No of {{Lang::get('program.programs')}}: {{$channel_count}}</h4>
              <?php
              if($channel_count==0) {
              $s_flag = 0;
              }
              ?>
              @endif
              @if($list[0]['program_type'] == 'content_feed' && $list[0]['program_sub_type']=='single' && isset($list[0]['parent_relations']['active_parent_rel']) && !empty($list[0]['parent_relations']['active_parent_rel']))
                <?php
                $second_slug=Program::getProgramDetailsByID($list[0]['parent_relations']['active_parent_rel'][0]);
                ?>
                
              <a href="<?php echo URL::to("catalog/course/{$second_slug['program_slug']}");?>" class="prod-title m-top-0 btn btn-info" style="float:right">{{Lang::get('program.buy')}}</a>
              
              @endif
          </div>
          <div class="col-md-3 col-sm-4 col-xs-12 margin-bottom-5">
           <?php
              
              
              if(isset($p_det_basic['program_cover_media']) && !empty($p_det_basic['program_cover_media']))
              {
              ?>
                <img alt="Channel Name" class="img-responsive margin-bottom-10 catalog-img" src="{{ URL::to('media_image/'.$p_det_basic['program_cover_media']) }}">
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
           <!--  <img src="img/course1.jpg" alt="Course Name" class="img-responsive"> -->
          </div>
          <div class="col-md-9 col-sm-8 col-xs-12 margin-bottom-5">
            <p class="font-15" style="max-height:148px;overflow-y:auto;">{{$p_det_basic['program_description']}}</p>
          </div>
             <?php
              if(isset($p_det_subscription) && !empty($p_det_subscription))
              {
                $is_buyed_flag = 0;
                $subscription_bayed = null;
                foreach ($p_det_subscription as $key => $value) {                
                  if(isset($is_buyed['subscription_slug']) && $value['slug'] === $is_buyed['subscription_slug']
                    && time() < (int)$is_buyed['end_time'])
                  {
                     $is_buyed_flag = 1;
                     $subscription_bayed = $value;
                  }
                }
              

              ?>
          @if(!$is_buyed_flag)


          <div class="col-md-12 col-sm-12 col-xs-12 table-responsive course-table">
            <table class="table margin-bottom-5">
              <thead>
                <tr>
                  <th>Title</th>
                  <!-- <th>Start date</th>
                  <th>End date</th> -->
                  <th>Duration</th>
                  <th>Price</th>
                  <th width="140">Discounted Price</th>
                  <th width="100" align="center"></th>
                </tr>
              </thead>
              <tbody style="padding: 0 10px;">

                    <?php
                      foreach ($p_det_subscription as $key => $value) {
                        // echo "<pre>";print_r($p_det_subscription);
                    ?>
                     <tr>
                        <td style="text-align:left">{{$value['title']}}</td>
                        <!-- <td>
                       {{Timezone::convertFromUTC('@'.$program_detail[0]['program_startdate'], $timezone, 'd/m/Y')}}
                        </td>
                        <td>{{Timezone::convertFromUTC('@'.$program_detail[0]['program_enddate'], $timezone, 'd/m/Y')}}</td> -->
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
                          $count = count($value['price']);
                          $i = 0;
                          foreach ($value['price'] as $key => $eachPrice)
                           {
                             if($eachPrice['currency_code'] === $site_currency)
                             {
                                $pricestring .= $site_currency_symbol.number_format($eachPrice['price']);
                                if(!empty($eachPrice['markprice']))
                                {
                                  $offPrice .= $site_currency_symbol.number_format($eachPrice['markprice']); 
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
                      <td>
                          <?php if($pricestring != "" && $offPrice != ""){?>                           
                        <span style="text-decoration:line-through;">
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
                      <td width="140" class="red">
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
                      <td width="100" align="center">                       
                        <?php
                        $subUrl = "checkout/place-order/{$p_det_basic['program_slug']}/{$value['slug']}";?>
                        @if(isset($is_buyed['start_time']) && $flag != 0)
                        <?php
                            if(time() > (int) $is_buyed['start_time'] && time() < (int) $is_buyed['end_time'])
                            {
                                ?>
                               
                                <?php
                            }
                            else
                            {
                              $package_flag=1;

                                ?>
                                <a href="{{URL::to($subUrl)}}" class="btn btn-danger btn-sm {{$is_loggedin}} subscribe">Enroll Now</a>
                                <?php
                            }
                        ?>                        
                        @elseif(empty($is_buyed) || $is_buyed === 'disable' && $s_flag > 0)
                        <a href="{{URL::to($subUrl)}}"  class="btn btn-success btn-sm {{$is_loggedin}} subscribe" href="#">ENROLL</a>
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
          @if(Auth::check() && !empty($is_buyed) && $is_buyed != 'disable' && time() < (int) $is_buyed['end_time'])
                  <tr>
                    <td colspan="5" class="text-center">
                    Subscribed for {{$subscription_bayed['duration_count']}}                   
                    @if($subscription_bayed['duration_type'] === "WW" || $subscription_bayed['duration_type'] === "ww")
                      weeks
                    @elseif($subscription_bayed['duration_type'] === "MM" || $subscription_bayed['duration_type'] === "mm")
                      months
                    @elseif($subscription_bayed['duration_type'] === "DD" || $subscription_bayed['duration_type'] === "dd")
                      days
                    @else($subscription_bayed['duration_type'] === "YY" || $subscription_bayed['duration_type'] === "yy")
                      years
                    @endif
                    until
                    {{Timezone::convertFromUTC('@'.$is_buyed['end_time'], Auth::user()->timezone, 'F jS, Y')}}
                   
                
                       <?php
                       $package_flag=1;
                       $st_learn = "program/packets/{$p_det_basic['program_slug']}";
                       ?>
                       &nbsp;&nbsp;<a class="btn btn-success pull-right" href="{{URL::to($st_learn)}}">Learn Now</a>
                    </td>
                  </tr>
              @endif

                <?php
              } ?>
       
              <?php if($p_det_basic['program_type'] == "product")
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
                                      $count = count($eachVariant['price']);
                                      $i = 0;
                                      foreach ($eachVariant['price'] as $key => $eachPrice)
                                       {
                                         if($eachPrice['currency_code'] === "INR")
                                         {
                                            $pricestring .= "<i class='fa fa-rupee'></i> ".$eachPrice['price'];
                                            if(!empty($eachPrice['markprice']))
                                            {
                                              $offPrice .= "<i class='fa fa-rupee'></i> ".$eachPrice['markprice']; 
                                            }
                                         }
                                         else
                                         {
                                            $pricestring .= "<i class='fa fa-dollar'></i> ".$eachPrice['price'];
                                            if(!empty($eachPrice['markprice']))
                                            {
                                               $offPrice .= "<i class='fa fa-dollar'></i> ".$eachPrice['markprice'];
                                            }
                                         }
                                         if($count === ($i+1))
                                         {

                                         }
                                         else
                                         {
                                            $pricestring .= "<br/>";
                                            if($offPrice != "")
                                            {
                                             $offPrice .= "<br/>";  
                                            }
                                         }
                                        $i++;
                                      }
                                    }
                     
                                  ?>
                                  <tr>
                                    <td>{{ $eachVariant['title'] }} </td>
                                    <td >
                                        <?php if($offPrice != "" && $pricestring != ""){?>
                                          <span style="text-decoration: line-through;color:RED">&nbsp;
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
                                      <a class="btn btn-danger btn-sm  {{$is_loggedin}} subscribe" data-baseurl="" data-productid="{{ $p_det_basic['program_id'] }}" href="{{URL::to($subUrl)}}">Buy Now</a>
                                      @else
                                      <a class="btn btn-danger btn-sm  {{$is_loggedin}}" data-baseurl="" data-productid="{{ $p_det_basic['program_id'] }}" href="{{URL::to($subUrl)}}">Buy Now</a>
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


          <?php if($p_det_subscription[0]['type'] == 'paid' && $package_flag ==0) {?> 
            <div class="col-md-12 margin-bottom-20">
              <div class="font-12 lgray pull-right">Inclusive of service taxes</div>
            </div>
            <?php } ?>
        </div>
        <!-- details div -->

        <div class="col-md-3 col-sm-4 col-xs-12 margin-bottom-20">
          <h3 class="font-weight-400 margin-bottom-5">Enquiry</h3>
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
          <div style="border:1px solid #dddddd;padding:20px 15px;">
            <form action="{{ url('contactus/enquiry') }}" method="post" class="contact-form">
          
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
              <input type="text" class="form-control input-sm" placeholder="Name*" name="name" value="{{$name}}" >
               {!! $errors->first('name', '<span class="help-inline" style="color:#f00">:message</span>')!!}
            </div>
             <div class="form-group">
              <input type="text" class="form-control input-sm" name="mobile" placeholder="Mobile*" value="{{$mobile}}">
               {!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>')!!}
            </div>
            <div class="form-group">
              <input type="text" class="form-control input-sm" name="email" placeholder="Email*" value="{{$email}}">
               {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>')!!}
            </div>
            <div class="form-group">
              <textarea  id="textarea" cols="30" rows="2" name="message" placeholder="Message*" class="form-control">{{Input::old('message')}}</textarea>
               {!! $errors->first('message', '<span class="help-inline" style="color:#f00">:message</span>') !!}
            </div>
           
          
            
            <div class="form-group">
            <div class="controls">
            <div class="g-recaptcha" data-sitekey={{$sitekey}} style="transform:scale(0.75);transform-origin:0;-webkit-transform:scale(0.75);
transform:scale(0.75);-webkit-transform-origin:0 0;transform-origin:0 0;"></div>
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
              
            <div class="form-group center margin-bottom-5">
            <input type="submit" class="btn btn-primary" value="SUBMIT">
          </div>

            
          </form>
    
          </div>
        </div>
        <!-- form div -->
      </div>

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
                <?php //print_r($post);die; ?>
                    @if(isset($p_det_basic['program_access']) && $p_det_basic['program_access'] == "general_access")
                      <a href="{{URL::to("program/packet/{$post['packet_slug']}")}}" data-productid="{{ $p_det_basic['program_id'] }}" data-baseurl="{{ URL::to('/') }}" class="{{$is_loggedin}} subscribe">@if(isset($post['packet_cover_media']) && !empty($post['packet_cover_media']))<img src="{{ URL::to('media_image/'.$post['packet_cover_media'].'/?thumb=178x114') }}" class="img-responsive" alt="{{ $post['packet_title'] }}">@else<img src="{{ URL::to('portal/theme/default/img/default_packet.jpg') }}" class="packet-img img-responsive" alt="{{ $post['packet_title'] }}">@endif</a>
                  
                      @endif
                    </figure>
                    <p class="packet-title">
                    @if(isset($p_det_basic['program_access']) && $p_det_basic['program_access'] == "general_access")
                    <a href="{{URL::to("program/packet/{$post['packet_slug']}")}}" data-productid="{{ $p_det_basic['program_id'] }}" data-baseurl="{{ URL::to('/') }}" class="{{$is_loggedin}} subscribe"><b>{{ $post['packet_title'] }}</b></a>
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
<!--start of channels in collection-->
@if($program_sub_type =='collection' && $channel_count == 0)
<h4 style="margin-left:20%;">{{Lang::get('program.no_child')}}</h4>
@endif
@if($program_sub_type =='collection' && $channel_count > 0)
<!--display starts here-->
<div class="container">
<div class="row"><!--start3-->
                         <div class="col-md-12">
                            <!-- <h3 class="page-title-small" style="margin-top: -4px;">
                              <a href=""></a>
                            </h3> -->
                            <span class="center">
                            <h3>{{Lang::get('program.channels_list')}}</h3>
                            </span>
                            <hr class="border-black">
                        </div>
     <div class="facets-data"><!--start2-->
           <?php
             $child_list = Program::getAllProgramByIDOrSlug('content_feed',$slug);
             foreach($child_list[0]['child_relations']['active_channel_rel'] as $child_id)
             { 
                $details=Program::getProgramDetailsByID($child_id);
                
            ?>
                        
                <div class="col-lg-3 col-md-3 col-sm-6 col-xs-12 md-margin"><!--start1-->
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
                    ?>
     </div><!--start2-->
</div><!--start3-->
</div>
<!--display ends here-->
@endif
<!--end of channels in collection-->

  <!-- Tabs Begins-->
  <?php
  if(!empty($p_det_tabs))
  {
  ?>
  <hr class="margin-bottom-20 course-detail-hr">
  
    <div class="row">
    <!-- start -->

    <div class="col-md-9 col-sm-8 col-xs-12 margin-bottom-30">
          <div class="row details-tabs">
            <div class="col-md-4 col-sm-4 col-xs-12">
              <ul class="nav nav-tabs tabs-left">
              <?php
              $active = 0;
              foreach ($p_det_tabs as $key => $value) {
                if($active === 0)
                {
              ?>
                <li class="active">
                  <a href="#{{$value['slug']}}" data-toggle="tab">{{$value['title']}}</a>
                </li>
                <?php
                $active = 1;
                }
                else
                {
              ?>
                 <li><a href="#{{$value['slug']}}" data-toggle="tab">{{$value['title']}}</a></li>
              <?php        
                }
                }
              ?>
               
              </ul>
            </div>
            <div class="col-md-8 col-sm-8 col-xs-12">
              <div class="tab-content">
                
                 <?php
                $active = 0;
                foreach ($p_det_tabs as $key => $value) {
                  if($active === 0)
                  {
                ?>
                  <div class="tab-pane active" id="{{$value['slug']}}">
                    <?php echo $value['description'];?>
                  </div>
                <?php
                  $active = 1;
                  }
                  else
                  {
                ?>
                  <div class="tab-pane" id="{{$value['slug']}}">
                    <?php echo $value['description'];?>
                  </div>
                <?php        
                  }
                  }
                ?>
                
                
              </div>
            </div>
          </div>
        </div>
    <!-- end -->
      
      <div class="col-md-3 col-sm-4 col-xs-12 margin-bottom-20 recommend">
          <h4 class="blue font-weight-500">Recommendations</h4>
            <ul class="fs-right-ul">
            <?php
            if(!empty($p_det_related_program))
            {
              foreach ($p_det_related_program as $key => $eachProgram) 
              {
            ?>
              <li>
                <div class="fs-img">
                  <?php
                      if(isset($eachProgram['program_cover_media']) && 
                        !empty($eachProgram['program_cover_media']))
                        {
                  ?>
                <img src="{{ URL::to('media_image/'.$eachProgram['program_cover_media'].'/?thumb=178x114') }}" alt="Video Name" width="60px">
                    <?php
                         }
                         else
                         {
                    ?>
                   <img src="{{URL::to('portal/theme/default/img/default_packet.jpg')}}" alt="Video Name" width="60px" >
                  <?php
                        }
                  ?> </div>
                <div class="fs-data"><a href="<?php echo URL::to("catalog/course/{$eachProgram['program_slug']}");?>"><h4 class="font-weight-500">{{$eachProgram['program_title']}}</h4></a></div>
              </li>
              <?php
              }
             }
            ?>
            </ul>
        </div>

    </div>
     <!-- <hr> -->
  
  <?php
  }
  ?>
  <!-- Tab Ends-->



   <!-- Posts -->
  <!--End-->
  </div>
</div>
  <!--END Main Container -->



<script type="text/javascript">
<?php if(Auth::check()){
$user_id = Auth::user()->uid;
 ?>
$('.subscribe').on('click',function(event){
  var UID = '<?php echo $user_id; ?>';
  var EnrollProductId = $(this).data('productid');
  var Baseurl = $(this).data('baseurl');
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

<script src="{{ URL::asset($custom_theme.'/js/custom-front-end/product_details.js')}}"></script>

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
 @stop