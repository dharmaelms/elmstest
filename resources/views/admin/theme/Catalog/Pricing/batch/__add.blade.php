<script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
<div class="row">
  
  <div class="col-md-12">
        
    <div class="box">
      
      <div class="box-title">
      </div>
            
      <div class="box-content">
      
      <?php
        
        $error_message = function($value) use($errors) {

            return $errors->first($value,
                                  '<span class="help-inline" style="color:#f00">
                                    :message
                                  </span>'
                                  );
          
        };

        $language_text = function($value)
        {
            return trans('admin/batch/add.'.$value);
        };



      ?>      	
        <form action="{{URL::to('cp/pricing/add-batch')}}" method="post" name="batch-tab" id="batch-tab" class="form-horizontal form-bordered form-row-stripped">

        <input type="hidden" id="program_slug" name="program_slug" value="{{$program_slug}}">
            	    
        <input type="hidden" id="sellable_id" name="sellable_id" value="{{$sellable_id}}">
            	    
        <input type="hidden" id="sellable_type" name="sellable_type" value="{{$sellable_type}}">
                    
        <div class="form-group">
        
          <label class="col-sm-3 col-lg-2 control-label">

            {{$language_text('batch_name')}} 
            
            <span class="red">*</span>

          </label>

          <div class="col-sm-9 col-lg-10 controls">
            
            <input type="text" class="form-control" id="batch_name" name="batch_name" placeholder="{{$language_text('batch_name_placeholder')}}" value="{{Input::old('batch_name')}}"/>

            {!! $error_message('batch_name') !!}
                      
          </div>
                  
        </div>

        <div class="form-group">

          <label class="col-sm-3 col-lg-2 control-label">
          
            {{$language_text('batch_description')}}
          
          </label>
          
          <div class="col-sm-9 col-lg-10 controls">
                           
            <textarea class="form-control" id="batch_description" name="batch_description" placeholder="{{$language_text('batch_description_placeholder')}}">

              {{Input::old('batch_description')}}
            
            </textarea>
                           
            {!! $error_message('batch_description') !!}

          </div>
                     
        </div>

        <div class="form-group">
                        
          <label class="col-sm-3 col-lg-2 control-label">
            
            {{$language_text('batch_start_date')}}
            
            <span class="red">*</span>
          
          </label>
          
          <div class="col-sm-6 col-lg-4 controls">
                    
            <div class="input-group date">
                           
              <span class="input-group-addon calender-icon">

                <i class="fa fa-calendar"></i>

              </span>
              
              <input type="text" class="form-control datepicker" id="batch_start_date" name="batch_start_date" placeholder="{{$language_text('batch_start_date_placeholder')}}" value="{{Input::old('batch_start_date')}}" title="{{$language_text('batch_start_date_title')}}"/>

            </div>
            
            {!! $error_message('batch_start_date') !!}
                      
          </div>
          
          <div class="col-sm-6 col-lg-6 controls">
            
            <div class="input-group date">
  	           
              <span class="input-group-addon calender-icon">
                
                <i class="fa fa-calendar"></i>
              
              </span>
  	         
              <input type="text" class="form-control datepicker" id="batch_end_date" name="batch_end_date" placeholder="{{$language_text('batch_end_date_placeholder')}}" value="{{Input::old('batch_end_date')}}" title="{{$language_text('batch_end_date_title')}}"/>
                           
            </div>
  	       
            {!! $error_message('batch_end_date') !!}
          
          </div>
                     
        </div>

        <div class="form-group">

          <label class="col-sm-3 col-lg-2 control-label">
            
            {{trans('admin/batch/add.batch_last_enrollment_date')}} 

            <span class="red">*</span>

          </label>
          
          <div class="col-sm-9 col-lg-4 controls">
                        	
            <div class="input-group date">

  	          <span class="input-group-addon calender-icon">
              
                <i class="fa fa-calendar"></i>
              
              </span>
  	          
              <input type="text" class="form-control datepicker" id="batch_last_enrollment_date" name="batch_last_enrollment_date" placeholder="{{$language_text('batch_last_enrollment_date_placeholder')}}" value="{{Input::old('batch_last_enrollment_date')}}" />
  	                        
            </div>
  	                         
            {!! $error_message('batch_last_enrollment_date') !!}

          </div>
                     
        </div>

        <div class="form-group">

          <label class="col-sm-3 col-lg-2 control-label">
            
            {{$language_text('batch_minimum_enrollment')}}
            
            <span class="red">*</span>

          </label>
                        
          <div class="col-sm-6 col-lg-4 controls">
                           
            <input type="number" class="form-control" id="batch_minimum_enrollment" name="batch_minimum_enrollment" placeholder="{{$language_text('batch_minimum_enrollment_placeholder')}}" value="{{Input::old('batch_minimum_enrollment')}}" title="{{$language_text('batch_minimum_enrollment_title')}}" min="0"/>

            {!! $error_message('batch_minimum_enrollment') !!}
                        
          </div>
          
          <div class="col-sm-6 col-lg-6 controls">

            <input type="number" class="form-control" id="batch_maximum_enrollment" name="batch_maximum_enrollment" placeholder="{{$language_text('batch_maximum_enrollment_placeholder')}}" value="{{Input::old('batch_maximum_enrollment')}}" title="{{$language_text('batch_maximum_enrollment_title')}}"/>

            {!! $error_message('batch_maximum_enrollment') !!}

          </div>
        
        </div>
        
        <div class="form-group">

          <label class="col-sm-3 col-lg-2 control-label">
            
            {{$language_text('batch_location')}}
          
          </label>
          
          <div class="col-sm-9 col-lg-10 controls">
                         
            <input type="text" class="form-control" id="batch_location" name="batch_location" placeholder="{{$language_text('batch_location_placeholder')}}" value="{{Input::old('batch_location')}}"/>

            {!! $error_message('batch_location') !!}
                      
          </div>
        
        </div>
           

        <span class="@if($program_sellability === 'no') hide @endif">    
          @if(!empty($currency_code_list))

            <?php 
            
              $str_lower = function($value){ return strtolower($value);};  

              foreach ($currency_code_list as $value) {

            ?>

              <div class="form-group" id="{{$value['currency_code']}}" name="{{$value['currency_code']}}">

                <label for="textfield1" class="col-sm-3 col-lg-2 control-label">

                  {{strtoupper($value['currency_code'])}} 

                  <span class="red">*</span>

                </label>
                
                <div class="col-sm-2 col-lg-4 controls" style="margin-top: 10px;">
                  
                  <?php

                    $price = Input::old($str_lower($value['currency_code']));
                    
                    if($program_sellability === 'no'){
                        
                        $price = 1;
                    
                    }
                  
                  ?>

                  <input type="number" name="{{$str_lower($value['currency_code'])}}" id="currency_{{$value['currency_code']}}" placeholder="Price" class="form-control" min="1" value="{{$price}}" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">

                   {!! $error_message(strtolower($value['currency_code'])) !!}

                </div>

                <div class="col-sm-6 col-lg-4 controls" style="margin-top: 10px;">

                  <input type="number" name="<?php echo "mark_".$str_lower($value['currency_code']);?>" id="currency_{{$value['currency_code']}}" placeholder="Discounted Price" class="form-control" min="1" value="{{Input::old("mark_".$str_lower($value['currency_code']))}}" pattern="[0-9][0-9]*(\.[0-9][0-9]?)?">	  
                                        
                  {!! $error_message(strtolower("mark_".$value['currency_code'])) !!}

                </div>
                                
              </div>
            <?php
                          
              }
            ?>
          @endif      
        </span>

        <div class="form-group last">

          <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">

            <button type="submit" class="btn btn-success" >

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