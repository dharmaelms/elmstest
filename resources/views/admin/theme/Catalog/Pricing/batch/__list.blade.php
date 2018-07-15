<div class="row">
    
    <div class="col-md-12">
        
        <?php
        
            use App\Model\UserGroup;

            if($pri_ser_info['pri_service'] === 'enabled' &&
             isset($pri_ser_info['pri_ser_data']) &&
             !empty($pri_ser_info['pri_ser_data']))
		    {
        			
                    $i = 1;	    
        ?>
			    	<table class="table table-bordered">
			    		
                        <tr>
			    			
                            <th>#</th>
			    			
                            <th>
                             
                                {{trans('admin/batch/list.batch_name')}}
                            
                            </th>

                            <th>
                            
                                {{trans('admin/batch/list.batch_start_date')}}
                            
                            </th>

                            <th>

                                {{trans('admin/batch/list.batch_end_date')}}

                            </th>

                            <th colspan="3">

                                {{trans('admin/batch/list.batch_enrollment')}}

                            </th>	   		
			    			
                            <th class="@if($program_sellability === 'no') hide @endif">

                                {{trans('admin/batch/list.price')}}

                            </th>
			    			
                            <th>

                                {{trans('admin/batch/list.batch_actions')}}

                            </th>

			    		</tr>

        			    <?php

        				    foreach ($pri_ser_info['pri_ser_data'] as $value)
        				    {
        			     ?>
			    		       <tr>
        				            
                                    <td>
                    		
                                        {{$i}}
                    	           
                                    </td>
                    	           
                                    <td>
                    		              
                                        {{$value['title']}}
                    	            
                                    </td>

                                    <td>

                                        {{$value['batch_start_date']}}

                                    </td>
                                    
                                    <td>

                                        {{$value['batch_end_date']}}

                                    </td>
                        
                                    <td>

                                        {{($value['batch_maximum_enrollment']>0)? $value['batch_maximum_enrollment'] : 'N/A'}}

                                    </td>

                                    <td>

                                        {{($value['batch_minimum_enrollment'] > 0) ? $value['batch_minimum_enrollment'] : 'N/A'}}
                        
                                    </td>
                        
                                     <td>
                                        @if($program_sellability === 'no')
                                        
                                        <?php

                                            $user_count_user_group = 0;
                                            if($program_sellability === 'no')
                                            {
                                                
                                                $user_group_data = UserGroup::whereIn('relations.usergroup_course_rel',[$value['course_id']])
                                                                ->get(['relations.active_user_usergroup_rel'])
                                                                ->toArray();

                                                foreach ($user_group_data as $key => $ug_value)
                                                {
                                                    $user_count_user_group += count(array_get($ug_value,'relations.active_user_usergroup_rel'));
                                                }

                                            }

                                        ?>

                                        {{$user_count_user_group}}
                                        
                                        @else

                                        {{($value['batch_enrolled'] > 0 || $value['batch_maximum_enrollment']>0 ) ? $value['batch_enrolled'] : 'N/A'}}

                                        @endif

                                    </td>
                    	            
                                    <td class="@if($program_sellability === 'no') hide @endif">
                                        @if(isset($value['price']) && !empty($value['price']))
                                		<div class="box box-magenta">			                            
            		                        <table class="table table-bordered">
                                                <thead>
                                                    <tr>
                                                        <th>#</th>
                                                        <th>{{trans('admin/batch/list.currency_type')}}</th>
                                                        <th>{{trans('admin/batch/list.price')}}</th>
                                                        <th>{{trans('admin/batch/list.discounted_price')}}</th>
                                                    </tr>
                                                </thead>
                                                <tbody>
            	                                    <?php
            		                                    $j = 1;
            		                                    foreach ($value['price'] as $values)
            		                                     {				                                    	
            		                                    	?>
            		                                        <tr>
            		                                            <td>{{$j}}</td>
            		                                            <td>{{$values['currency_code']}}</td>
            		                                            <td>{{number_format($values['price'])}}</td>
            		                                            <td>{{$values['markprice'] !== '' ? number_format(($values['markprice'] > 0) ? $values['markprice'] : '') : ''}}</td>
            		                                        </tr>
            		                                   <?php
            		                                    $j++; 
            		                                   	} 
            	                                   ?>
                                                </tbody>
            		                        </table>				                           
            		                    </div>
                                        @else
                                            <p>---</p>
                                        @endif
		                            
                                    </td>
            		                <td>
            		                    
                                        <?php $delURL = "cp/pricing/delete-batch/".$value['title']."/".$pri_ser_info['sellable_id']."/".$pri_ser_info['sellable_type']."/".$pri_ser_info['program_slug'];?>
            		                    
                                            @if(has_admin_permission(ModuleEnum::COURSE, CoursePermission::EDIT_BATCH))      
                                                <a class="btn btn-circle show-tooltip open-AddBookDialog" title="" data-original-title="Edit" data-toggle="modal" data-slug="{{$value['title']}}"><i class="fa fa-edit"></i></a>
                                            @endif
                                        
                                        @if($value['batch_enrolled'] <= 0)
            		                        
                                            @if(has_admin_permission(ModuleEnum::COURSE, CoursePermission::DELETE_BATCH))   
                                                <a class="btn btn-circle show-tooltip deletefeed" title="" href="{{URL::to($delURL)}}" data-original-title="Delete"><i class="fa fa-trash-o"></i></a>
                                            @endif
                                        
                                        @endif

            		                </td>
                                
                                </tr>
                            <?php
               
                                 $i++;	                        	
                	           }	                       
                            }
                            else
                            {
                 	          
                            ?>
                 		
                            <table class="table table-bordered">
			    		       
                                <tr>

                                   <th>#</th>
                                   
                                    <th>{{trans('admin/batch/list.batch_name')}}</th>
                                    
                                    <th>{{trans('admin/batch/list.batch_start_date')}}</th>
                                    
                                    <th>{{trans('admin/batch/list.batch_end_date')}}</th>
                                    
                                    <th colspan="3">{{trans('admin/batch/list.batch_enrollment')}}</th>
                                    
                                    <th>{{trans('admin/batch/list.price')}}</th>
                                    
                                    <th>{{trans('admin/batch/list.batch_actions')}}</th>
			    		
                                </tr>

                                <tr>
    			    			
                                    <td colspan="9" class="text-center">

                                        {{trans('admin/batch/list.there_is_no_batch')}}

                                    </td>
    			    		
                                </tr>
                 	  
                      <?php
                        
                        }
                        
                        ?>
                    
                    </table>	                      
            </div>

    </div>

<div class="modal fade" id="subscriptionDel" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                               {{trans('admin/batch/list.delete_batch')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-body" style="padding: 20px">
                        {{trans('admin/batch/list.batch_del_confirmation')}}
                    </div>

                    <div class="modal-footer">
                        <a href="" class="btn btn-danger" id="bulkdeletebtn">{{ trans('admin/catalog.yes') }}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/catalog.close') }}</a>
                    </div>

                </div>
            </div>
        </div>
<script type="text/javascript">
$(document).on("click", ".deletefeed", function (event) 
{
    event.preventDefault();
    $('#bulkdeletebtn').attr('href',$(this).attr('href'));
    $('#subscriptionDel').modal();       
});
$('#subscriptionDel').on('hidden.bs.modal', function () {
 $('.modal-backdrop').removeClass('modal-backdrop');
 $('#tabDel').modal('hide');
});
   // $('#subscriptionDel').modal();
</script>
<script type="text/javascript">
        function listSubscription()
        {
            $('#subscription_add').addClass('hide');
            $('#subscription_list').removeClass('hide');
            $('#list_subscription').addClass('hide');
            $('#add_subscription').removeClass('hide');
            $('#subscription_edit').addClass('hide');
            $("#batch-tab input[type=text],input[type=number], textarea").val("");
            $(".help-inline").text('');
        }
        function addSubscription()
        {
            $('#subscription_list').addClass('hide');
            $('#subscription_add').removeClass('hide');
            $('#add_subscription').addClass('hide');
            $('#list_subscription').removeClass('hide');
        }
</script>
@if(Session::get('pricing_action') === 'add')
<script type="text/javascript">
  $( document ).ready(function() {
        addSubscription();
    });
</script>
@endif
  <script type="text/javascript">
        $(document).on("click", ".open-AddBookDialog", function () {
             var slug = $(this).data('slug');    
             $('#subscription_list').addClass('hide');
             $('#add_subscription').addClass('hide');
             $('#list_subscription').removeClass('hide');
             $('#subscription_edit').removeClass('hide');
             $.ajax({
                      method: "POST",
                      url: "<?php echo URL::to('cp/pricing/edit-batch');?>",
                      data:{ 
                        slug: slug,
                        sellable_id:$('#sellable_id').val(),
                        sellable_type:$('#sellable_type').val(),
                        program_slug:$('#program_slug').val(),
                        program_sellability:$('#hiddensellability').val(),
                      }
                    })
                      .done(function( msg ) {
                        $('#edisubscriptioncontent').html(msg);
                });
});
</script>
<script type="text/javascript">
 $(document).ready(function () {
    window.setTimeout(function() {
        $(".alert").fadeTo(1500, 0).slideUp(500, function(){
            $(this).remove(); 
        });
    }, 1000);
    });
</script>