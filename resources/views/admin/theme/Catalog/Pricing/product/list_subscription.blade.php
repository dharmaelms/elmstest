<div class="row">
    <div class="col-md-12">
        <?php
       // dd($pri_ser_info);
        	if($pri_ser_info['pri_service'] === 'enabled')
				{
        ?>
        		<?php 
        			if(isset($pri_ser_info['pri_ser_data']))
        			{
        			    $i = 1;
        			    ?>
			    	<table class="table table-bordered">
			    		<tr>
			    			<th>#</th>
			    			<th>{{ trans('admin/catalog.title') }}</th>			    			
			    			<th>{{ trans('admin/catalog.price') }}</th>
			    			<th>{{ trans('admin/catalog.action') }}</th>
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
                    		<?php if(isset($value['price']) && !empty($value['price'])){ ?>
                    		<div class="box box-magenta">			                            
		                        <table class="table table-bordered">
                                    <thead>
                                        <tr>
                                            <th>#</th>
                                            <th>{{trans('admin/catalog.currency_type')}} </th>
                                            <th>{{ trans('admin/catalog.price') }}</th>
                                            <th>{{trans('admin/catalog.discounted_price')}} </th>
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
		                                            <td>{{$values['markprice'] !== '' ? number_format(($values['markprice'] > 0) ? $values['markprice'] : '') : '' }}</td>
		                                        </tr>
		                                   <?php
		                                    $j++; 
		                                   	} 
	                                   ?>
                                    </tbody>
		                        </table>				                           
		                    </div>
		                    <?php } else { echo "Free";}?>
		                </td>
		                <td>
		                    <?php $delURL = "cp/pricing/delete-variant/".$value['title']."/".$pri_ser_info['sellable_id']."/".$pri_ser_info['sellable_type']."/".$pri_ser_info['program_slug'];?>
		                    <a class="btn btn-circle show-tooltip open-AddBookDialog" title="" data-original-title="Edit" data-toggle="modal" data-slug="{{$value['title']}}"><i class="fa fa-edit"></i></a>
		                    <a class="btn btn-circle show-tooltip deletefeed" title="" href="{{URL::to($delURL)}}" data-original-title="Delete"><i class="fa fa-trash-o"></i></a>
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
			    			<th>{{ trans('admin/catalog.title') }}</th>			    			
			    			<th>{{trans('admin/catalog.price')}} </th>
			    			<th>{{trans('admin/catalog.action')}} </th>
			    		</tr>
			    		<tr>
			    			<td colspan="4" class="text-center">{{trans('admin/catalog.add_variant')}} </td>
			    		</tr>
                 	<?php
                 }
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
                                               {{trans('admin/catalog.delete_variant')}} 
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        {{trans('admin/catalog.delete_variant_confirmation')}} 
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