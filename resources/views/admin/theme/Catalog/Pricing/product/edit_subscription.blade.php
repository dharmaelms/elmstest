<div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-title">
                        <div class="box-content">
                            <form action="" class="form-horizontal form-bordered" id="variant_tab" name="variant_tab" method="post">
                              <!-- BEGIN Left Side -->
                              <!-- Hidden Fields -->
                              <?php //dd($subscription);?>
                             <input type="hidden" id="sellable_id" name="sellable_id" value="{{$subscription['sellable_id']}}">
                             <input type="hidden" id="sellable_type" name="sellable_type" value="{{$subscription['sellable_type']}}">
                             <input type='hidden' id="program_slug" name="program_slug" value="{{$subscription['program_slug']}}">
                            <?php
                                    $from = date("d-m-Y", strtotime("-1 month",time()));
                                    $to = date('d-m-Y',time());
                              ?>
                               <?php
                                    $countryArray = $currency_support_list;
                              ?>
                              <!-- Hidden Fields Ends -->
                              @if ( Session::get('success_price') )
								<div class="alert alert-success">
									<button class="close" data-dismiss="alert">×</button>
								<!-- 	<strong>Success!</strong> -->
									{{ Session::get('success_price') }}
								</div>
								<?php Session::forget('success_price'); ?>
								@endif
                                @if (count($errors) > 0)
								    <div class="alert alert-danger">
								        <ul>
								        	{{ Session::get('pricing') }}
								            @foreach ($errors->all() as $error)
								                <li>{{ $error }}</li>
								            @endforeach
								        </ul>
								    </div>
								@endif
                <?php
                    $title = '';
                    if(isset($subscription['subdata']['title']))
                    {
                      $title = $subscription['subdata']['title'];
                    }
                    $description = '';
                    if(isset($subscription['subdata']['desc']))
                    {
                      $description = $subscription['subdata']['desc'];
                    }
                    $duration_type = '';
                    if(isset($subscription['subdata']['duration_type']))
                    {
                      $duration_type = $subscription['subdata']['duration_type'];
                    }
                     $duration_count = '';
                    if(isset($subscription['subdata']['duration_count']))
                    {
                      $duration_count = $subscription['subdata']['duration_count'];
                    }
                    $subPrice = '';
                    if(isset($subscription['subdata']['price']) && !empty($subscription['subdata']['price']))
                    {
                      $subPrice = $subscription['subdata']['price'];
                    }
                ?>
                <input type="hidden" id="ctitle" name="ctitle" value="{{$title}}{{Input::old('ctitle')}}">                               
                                <div class="form-group">
                                    <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{trans('admin/catalog.title_edit')}}<span class="red">*</span></label>
                                    <div class="col-sm-6 col-lg-4 controls">
                                        <input type="text" name="edit_title" id="edit_title" placeholder="Variant Title" class="form-control" value="{{Input::old('title')}}{{$title}}">
                                        <span id="e_title" class="help-inline" style="color:#f00"></span>
                                       
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/catalog.description') }}</label>
                                    <div class="col-sm-8 col-lg-8 controls">
                                        <textarea name="edit_desc" id="edit_desc" rows="5" class="form-control" placeholder="Variant Description">{{Input::old('desc')}}{{$description}}</textarea>
                                        <span id="e_desc" class="help-inline" style="color:#f00"></span>
                                        
                                    </div>
                                </div>
                                <div class="form-group hide">
                                      <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/catalog.variant_type')}} </label>
                                      <div class="col-sm-9 col-lg-10 controls">
                                         <label class="radio-inline">
                                            <input type="radio" name="subscription_type_new" id="subscription_type" value="free" <?php if(empty($subPrice)){ echo 'checked="checked"'; } ?> onchange=""> {{trans('admin/catalog.free')}} 
                                         </label>
                                         <label class="radio-inline">
                                            <input type="radio" name="subscription_type_new" id="subscription_type" value="paid" <?php if(!empty($subPrice)){ echo 'checked="checked"'; } ?>> {{trans('admin/catalog.paid')}} 
                                         </label> 
                                      </div>
                                   </div>                                  
                               <div id="hidePrice" name="hidePrice" class="<?php if(empty($subPrice)){ echo "hide";}?>">
                                 <?php 
                                    foreach ($countryArray as $value) {
                                        $markprice = '';
                                        $price = '';
                                        ?>
                                        <?php
                                        if(!empty($subPrice)){
                                          foreach ($subPrice as $eachValue) {                                          
                                            if($eachValue['currency_code'] == strtoupper($value['currency_code']))
                                            {
                                                 $markprice = $eachValue['markprice'];
                                                 $price = $eachValue['price'];
                                            }
                                          }
                                        }
                                          ?>                                   
                                    <div class="form-group" id="{{$value['currency_code']}}" name="{{$value['currency_code']}}">
                                        <label for="textfield1" class="col-sm-3 col-lg-2 control-label" style="margin-top: 10px;margin-left: 1px;">{{strtoupper($value['currency_code'])}} <span class="red">*</span></label>
                                       <div class="col-sm-2 col-lg-4 controls" style="margin-top: 10px;">
                                            <input type="number" name="edit_{{strtolower($value['currency_code'])}}" id="edit_{{strtolower($value['currency_code'])}}" placeholder="Price" class="form-control" min="1" value="<?php $x = "currency_".$value['currency_code']; echo Input::old("$x");?>{{$price}}" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">
                                            <span id="e_{{strtolower($value['currency_code'])}}" class="help-inline" style="color:#f00"></span>
                                            </div>
                                        <div class="col-sm-6 col-lg-4 controls" style="margin-top: 10px;">
                                            <input type="number" name="<?php echo "edit_mark_".strtolower($value['currency_code']);?>" id="<?php echo "edit_mark_".strtolower($value['currency_code']);?>" placeholder="Discounted Price" class="form-control" min="1" value="<?php $x = "currency_discount_".$value['currency_code']; echo Input::old("$x");?>{{$markprice}}" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">    
                                            <span id="e_mark{{strtolower($value['currency_code'])}}" class="help-inline" style="color:#f00"></span>
                                            </div>
                                    </div>
                                        <?php
                                    }
                                ?>
                                </div>
                                <div class="form-group last">
                                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                                       <button type="submit" id="subscription-save" name="subscription-save" class="btn btn-success"><i class="fa fa-check"></i>{{trans('admin/catalog.save')}} </button>                                   
                                       <a class="btn btn-danger" onclick="listSubscription();"> {{trans('admin/catalog.cancel')}} </a>  
                                    </div>
                                </div>
                              <!-- END Left Side -->
                            </form>
                        </div>
                </div>
            </div>
        </div>
<script type="text/javascript">
$(document).ready(function() {
    $('input[type=radio][name=subscription_type_new]').change(function() {
        if (this.value == 'free') {
             $('#hidePrice').addClass('hide');
        }
        else
        {
          $('#hidePrice').removeClass('hide');
        }
    });
});

 $('#variant_tab').on('submit', function(e){
      e.preventDefault();
       $.ajax({
                method: "POST",
                url: "<?php echo URL::to('cp/pricing/save-variant');?>",
                data:
                { 
                  sellable_id:$('#sellable_id').val(),
                  sellable_type:$('#sellable_type').val(),
                  program_slug:$('#program_slug').val(),
                  ctitle: $('#ctitle').val(),
                  title : $('#edit_title').val(),
                  desc : $('#edit_desc').val(),
                  <?php 
                        foreach ($countryArray as $value) { 
                          $mrkp = "mark_".strtolower($value['currency_code']);
                          ?>
                  {{strtolower($value['currency_code'])}} : $("#edit_{{strtolower($value['currency_code'])}}").val(),
                  {{$mrkp}} : $("#edit_mark_{{strtolower($value['currency_code'])}}").val(),
                
                  <?php 
                    }
                ?>

                  subscription_type : $('input:radio[name=subscription_type_new]:checked').val()
                }
              })
                .done(function( msg ) {
                  $('#e_desc').html(msg.desc);                  
                   <?php 
                      foreach ($countryArray as $value) { ?>
                      $("#e_{{strtolower($value['currency_code'])}}").html(msg.{{strtolower($value['currency_code'])}});
                      $("#e_mark{{strtolower($value['currency_code'])}}").html(msg.{{"mark_".strtolower($value['currency_code'])}});
                    <?php 
                    }
                ?>
                  $('#e_title').html(msg.title);
                  if(msg.success === "error")
                  {
                    //some 
                  }
                  else
                  {
                    window.location = msg.success;
                  }
            });
    });

</script>