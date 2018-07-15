<script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
<div class="row">
  <?php
  
    use App\Model\UserGroup;

  ?>
  <div class="col-md-12">
        
    <div class="box">
            
      <div class="box-title">
      </div>
      
      <div class="box-content">
        
        <form action="" id="batch_tab" name="batch_tab" method="post" class="form-horizontal form-bordered form-row-stripped">

        <input type="hidden" id="program_slug" name="program_slug" value="{{$batch_info['program_slug']}}">
            	    
        <input type="hidden" id="sellable_id" name="sellable_id" value="{{$batch_info['sellable_id']}}">
            	    
        <input type="hidden" id="sellable_type" name="sellable_type" value="{{$batch_info['sellable_type']}}">
                  
        <input type="hidden" id="ctitle" name="ctitle" value="{{array_get($batch_info, 'subdata.title')}}">
                  
        <input type="hidden" id="batch_enrolled" name="batch_enrolled" value="{{array_get($batch_info, 'subdata.batch_enrolled')}}"> 
                  
        <input type="hidden" id="course_id" name="course_id" value="{{array_get($batch_info, 'subdata.course_id')}}"> 
                    
        <div class="form-group">
        
            <label class="col-sm-3 col-lg-2 control-label">

            {{trans('admin/batch/add.batch_name')}} 

            <span class="red">*</span></label>
                      
            <div class="col-sm-9 col-lg-10 controls">
                           
              <input type="text" class="form-control" id="batch_name" name="batch_name" placeholder="{{trans('admin/batch/add.batch_name_placeholder')}}" value="{{array_get($batch_info, 'subdata.title')}}"/>
                           
              <span class="help-inline" id="error_batch_name" name="error_batch_name" style="color:#f00">
                
              </span>
            
            </div>
                   
          </div>

          <div class="form-group">
          
            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/batch/add.batch_description')}} </label>
                      
            <div class="col-sm-9 col-lg-10 controls">
                         
              <textarea class="form-control" id="batch_description" name="batch_description" placeholder="{{trans('admin/batch/add.batch_description_placeholder')}}">{{array_get($batch_info, 'subdata.batch_description')}}</textarea>
                          
              <span class="help-inline" id="error_batch_description" name="error_batch_description" style="color:#f00"></span>
                      
            </div>
                   
          </div>
                   
          <div class="form-group">
                      
            <label class="col-sm-3 col-lg-2 control-label">

              {{trans('admin/batch/add.batch_start_date')}} 

              <span class="red">*</span></label>
                      
              <div class="col-sm-6 col-lg-4 controls">
                       
                <div class="input-group date">
                         
                  <span class="input-group-addon calender-icon">

                    <i class="fa fa-calendar"></i>

                  </span>
                  
                  <input type="text" class="form-control datepicker" id="batch_start_date" name="batch_start_date" placeholder="{{trans('admin/batch/add.batch_start_date_placeholder')}}" value="{{array_get($batch_info, 'subdata.batch_start_date')}}" title="{{trans('admin/batch/add.batch_start_date_title')}}"/>
                      
                </div>
                
                <span class="help-inline" id="error_batch_start_date" name="error_batch_start_date" style="color:#f00">
                  
                </span>
                  
              </div>
                      
              <div class="col-sm-6 col-lg-6 controls">
                      	 
                <div class="input-group date">
	               
                  <span class="input-group-addon calender-icon">

                    <i class="fa fa-calendar"></i>

                  </span>
	               
                  <input type="text" class="form-control datepicker" id="batch_end_date" name="batch_end_date" placeholder="{{trans('admin/batch/add.batch_end_date_placeholder')}}" value="{{array_get($batch_info, 'subdata.batch_end_date')}}" title="{{trans('admin/batch/add.batch_end_date_title')}}"/>
                 
                </div>
	              
                <span class="help-inline" id="error_batch_end_date" name="error_batch_end_date" style="color:#f00">
                  
                </span>

              </div>
            
            </div>

            <div class="form-group">
                      
              <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/batch/add.batch_last_enrollment_date')}} <span class="red">*</span></label>
                      
                <div class="col-sm-9 col-lg-4 controls">
                      	
                  <div class="input-group date">
	                      	
                    <span class="input-group-addon calender-icon">

                      <i class="fa fa-calendar"></i>

                    </span>
	                         
                    <input type="text" class="form-control datepicker" id="batch_last_enrollment_date" name="batch_last_enrollment_date" placeholder="{{trans('admin/batch/add.batch_last_enrollment_date_placeholder')}}" value="{{array_get($batch_info, 'subdata.batch_last_enrollment_date')}}" />
	                
                  </div>
	                
                  <span class="help-inline" id="error_batch_last_enrollment_date" name="error_batch_last_enrollment_date" style="color:#f00">
                    
                  </span>
                      
                </div>

              </div>

              <div class="form-group">

                <label class="col-sm-3 col-lg-2 control-label">

                  {{trans('admin/batch/add.batch_minimum_enrollment')}} 

                  <span class="red">*</span>

                </label>
                      
                <div class="col-sm-6 col-lg-4 controls">
                  <?php

                      $user_count_user_group = 0;
                      $disable_enroll_count = false;
                      if($program_sellability === 'no')
                      {
                          
                          $user_group_data = UserGroup::whereIn('relations.usergroup_course_rel',[array_get($batch_info, 'subdata.course_id')])
                                          ->get(['relations.active_user_usergroup_rel'])
                                          ->toArray();

                          foreach ($user_group_data as $key => $ug_value)
                          {
                              $user_count_user_group += count(array_get($ug_value,'relations.active_user_usergroup_rel'));
                          }

                          if($user_count_user_group > 0 || array_get($batch_info, 'subdata.batch_enrolled') > 0)
                          {
                             $disable_enroll_count = true; 
                          }
                      }
                  ?>  
                  <input type="number" class="form-control" id="batch_minimum_enrollment" name="batch_minimum_enrollment" placeholder="{{trans('admin/batch/add.batch_minimum_enrollment_placeholder')}}" value="{{array_get($batch_info, 'subdata.batch_minimum_enrollment')}}" title="{{trans('admin/batch/add.batch_minimum_enrollment_title')}}" min="0" @if($disable_enroll_count === true) disabled="true" @endif />

                  <span class="help-inline">
                    
                      {{trans('admin/batch/add.batch_info')}} 
                    
                    </span>
                         
                  <span class="help-inline" id="error_batch_minimum_enrollment" name="error_batch_minimum_enrollment" style="color:#f00">
                    
                  </span>
                      
                </div>
                      
                <div class="col-sm-6 col-lg-6 controls">
                  
                  <input type="number" class="form-control" id="batch_maximum_enrollment" name="batch_maximum_enrollment" placeholder="{{trans('admin/batch/add.batch_maximum_enrollment_placeholder')}}" value="{{array_get($batch_info, 'subdata.batch_maximum_enrollment')}}" title="{{trans('admin/batch/add.batch_maximum_enrollment_title')}}" @if($disable_enroll_count === true) disabled="true" @endif/>
                  
                  <span class="help-inline">
                    
                      {{trans('admin/batch/add.batch_info')}} 
                    
                  </span>
                         
                      <span class="help-inline" id="error_batch_maximum_enrollment" name="error_batch_maximum_enrollment" style="color:#f00">
                        
                      </span>
                      
                </div>
  
              </div>

              <div class="form-group">
                      
                  <label class="col-sm-3 col-lg-2 control-label">

                    {{trans('admin/batch/add.batch_location')}}

                  </label>
                  
                  <div class="col-sm-9 col-lg-10 controls">
                         
                    <input type="text" class="form-control" id="batch_location" name="batch_location" placeholder="{{trans('admin/batch/add.batch_location_placeholder')}}" value="{{array_get($batch_info, 'subdata.batch_location')}}"/>
                         
                    <span class="help-inline" id="error_batch_location" name="error_batch_location" style="color:#f00">
                      
                    </span>
                      
                  </div>
                
                </div>
               <span class="@if($program_sellability === 'no') hide @endif">    
                @if(!empty($currency_code_list))
                    
                  <?php 
                        
                    $subPrice =$value = array_get($batch_info, 'subdata.price');
                        
                    foreach ($currency_code_list as $value) {
                            
                      $markprice = '';
                      $price = '';
                      
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
                            
                  <label for="textfield1" class="col-sm-3 col-lg-2 control-label">

                    {{strtoupper($value['currency_code'])}} 

                    <span class="red">*</span>

                  </label>
                           
                  <div class="col-sm-2 col-lg-4 controls">
                                
                    <input type="number" name="{{strtolower($value['currency_code'])}}" id="{{strtolower($value['currency_code'])}}" placeholder="Price" class="form-control" min="1" value="<?php $x = "currency_".$value['currency_code']; echo Input::old("$x");?>{{$price}}" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">
                                
                    <span id="error_{{strtolower($value['currency_code'])}}" class="help-inline" style="color:#f00">
                      
                    </span>
                      
                  </div>
                  
                  <div class="col-sm-6 col-lg-4 controls">
                                
                    <input type="number" name="<?php echo "mark_".strtolower($value['currency_code']);?>" id="<?php echo "mark_".strtolower($value['currency_code']);?>" placeholder="Discounted Price" class="form-control" min="1" value="<?php $x = "currency_discount_".$value['currency_code']; echo Input::old("$x");?>{{$markprice}}" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">    
                                
                    <span id="error_mark_{{strtolower($value['currency_code'])}}" class="help-inline" style="color:#f00">
                      
                    </span>
                  
                  </div>
                        
                </div>
                
                <?php
                  }
                ?>
                    
              @endif      
              </span>   
              <div class="form-group last">
                        
                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                           
                  <button type="submit" id="form_submit" class="btn btn-success" >

                    <i class="fa fa-check"></i>

                    {{ trans('admin/catalog.save') }}

                  </button>

                  <button type="button" class="btn" onclick="listSubscription()">

                    {{ trans('admin/catalog.cancel') }}

                  </button>
                        
                </div>
              
              </div>

            </form>

          </div>
        
        </div>
    
    </div>

</div>

<script type="text/javascript">
  $('#batch_tab').on('submit', function(e){
      e.preventDefault();
      
      $('.help-inline').html('');

      $('button[type="submit"]').attr('disabled','disabled');
       
       $.ajax(
            {
                method: "POST",
                url: "{{URL::to('cp/pricing/save-batch')}}",
                data: $('#batch_tab').serialize(),
                dataType: 'json',
            }).done(function( msg ) {
                  window.location = msg.success;
            }).fail(function(msg) {
                $('button[type="submit"]').removeAttr('disabled');
                var errors = msg.responseJSON;
                $.each(errors, function(index, value) {
                       $('#error_'+index).html(value);
                   });
              });
    });

</script>

<script type="text/javascript">
  $(document).ready(function(){
              $('.datepicker').datepicker({
              format : "dd-mm-yyyy",
              startDate: '+0d'
              }).on('changeDate',function(){
                      $(this).datepicker('hide')
                  });
            });
</script>