             
        <div class="row">
            <div class="col-md-12">
                <div class="box">
                    <div class="box-title">
                        <div class="box-content">
                            <form action="{{URL::to('cp/pricing/add-variant')}}" class="form-horizontal form-bordered" id="subscription-tab" name="subscription-tab" method="post">
                              <!-- BEGIN Left Side -->
                              <!-- Hidden Fields -->

                             <input type="hidden" id="sellable_id" name="sellable_id" value="{{$pri_ser_info['sellable_id']}}">
                             <input type="hidden" id="sellable_type" name="sellable_type" value="{{$pri_ser_info['sellable_type']}}">
                             <input type='hidden' id="program_slug" name="program_slug" value="{{$pri_ser_info['program_slug']}}">
                              <?php
                                    $from = date("d-m-Y", strtotime("-1 month",time()));
                                    $to = date('d-m-Y',time());
                              ?>
                               <?php
                                    $countryArray = $pri_ser_info['currency_support_list'];
                              ?>
                              <!-- Hidden Fields Ends -->                             
                               
                                <div class="form-group">
                                    <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/catalog.title') }} <span class="red">*</span></label>
                                    <div class="col-sm-6 col-lg-4 controls">
                                        <input type="text" name="title" id="title" placeholder="Variant Title" class="form-control" value="{{Input::old('title')}}">
                                     <?php echo $errors->first('title', '<span class="help-inline" style="color:#f00">:message</span>'); ?>
                                    </div>
                                </div>
                                                         
                                <div class="form-group">
                                    <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/catalog.description') }}</label>
                                    <div class="col-sm-8 col-lg-8 controls">
                                        <textarea name="desc" id="desc" rows="5" class="form-control" placeholder="Variant Description">{{Input::old('desc')}}</textarea>
                                    </div>
                                </div>
                                 <div class="form-group hide">
                                      <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/catalog.variant_type')}} </label>
                                      <div class="col-sm-9 col-lg-10 controls">
                                         <label class="radio-inline">
                                            <input type="radio" class="" name="subscription_type" id="subscription_type" value="free" <?php if(Input::old('subscription_type')== "free") { echo 'checked="checked"'; } ?>> {{trans('admin/catalog.free')}} 
                                         </label>
                                         <label class="radio-inline">
                                            <input type="radio" class=""  name="subscription_type" id="subscription_type" value="paid" <?php if(Input::old('subscription_type') != "free") { echo 'checked="checked"'; } ?>> {{trans('admin/catalog.paid')}} 
                                         </label> 
                                      </div>
                                   </div>                              
                                <span id="hidePriceSub" name="hidePriceSub" class="<?php if(Input::old('subscription_type')== "free") echo "hide";?>">
                                <?php 
                                    foreach ($countryArray as $value) {
                                        ?>                                  
		                                <div class="form-group" id="{{$value['currency_code']}}" name="{{$value['currency_code']}}">
		                                    <label for="textfield1" class="col-sm-3 col-lg-2 control-label" style="margin-top: 10px;margin-left: 1px;">{{strtoupper($value['currency_code'])}}&nbsp;<span class="red">*</span></label>
		                                   <div class="col-sm-2 col-lg-4 controls" style="margin-top: 10px;margin-left: 1px;">
		                                        <input type="number" name="{{strtolower($value['currency_code'])}}" id="currency_{{$value['currency_code']}}" placeholder="Price" class="form-control" min="1" value="<?php echo Input::old(strtolower($value['currency_code']));?>" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">
		                                        <?php 
                                                    echo $errors->first(strtolower($value['currency_code']), '<span class="help-inline" style="color:#f00">:message</span>'); 
                                                ?>
                                            </div>
		                                    <div class="col-sm-6 col-lg-4 controls" style="margin-top: 10px;margin-left: 1px;">
		                                        <input type="number" name="<?php echo "mark_".strtolower($value['currency_code']);?>" id="currency_{{$value['currency_code']}}" placeholder="Discounted Price" class="form-control" min="1" value="<?php $x = "mark_".strtolower($value['currency_code']); echo Input::old("$x");?>" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">	  
		                                        <?php echo $errors->first("mark_".strtolower($value['currency_code']), '<span class="help-inline" style="color:#f00">:message</span>'); ?>
                                            </div>
		                                </div>
                                        <?php
                                    }
                                ?>
                                </span>
                                <div class="form-group last">
                                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                                       <button type="submit" class="btn btn-success"><i class="fa fa-check"></i>{{trans('admin/catalog.save')}} </button>
                                       <a class="btn btn-danger" onclick="listSubscription();"> {{trans('admin/catalog.cancel')}}</a>                                   
                                    </div>
                                </div>
                              <!-- END Left Side -->
                            </form>
                        </div>
                </div>
            </div>
        </div>
    </div>  						
<script type="text/javascript">
    $(document).ready(function() {
    $('input[type=radio][name=subscription_type]').change(function() {
        if (this.value == 'free') {
             $('#hidePriceSub').addClass('hide');
        }
        else
        {
          $('#hidePriceSub').removeClass('hide');
        }
    });
});
</script>