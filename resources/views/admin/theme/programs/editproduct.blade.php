@section('content')
@if ( Session::get('ap_success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">×</button>
		<!-- 	<strong>Success!</strong> -->
			{{ Session::get('ap_success') }}
		</div>
		<?php Session::forget('ap_success'); ?>
	@endif
<div class="tabbable">
    <ul id="myTab1" class="nav nav-tabs">
<?php
	$enble = array('pricing','edit','tab');
	$enabled = 'edit';
	if(Session::get('pricing'))
	{
		$enabled = "pricing";
	}
	if(Session::get('tab'))
	{
		$enabled = "tab";
	}

	if(Session::get('productcustomfield'))
    {
        $enabled = "productcustomfield";
    }
?>
        <li class="<?php if($enabled === 'edit'){ echo "active";} ?>"><a href="#content-feed" data-toggle="tab"><i class="fa fa-home"></i> {{trans('admin/program.general_info')}}</a></li>
        <?php if($pri_ser_info['pri_service'] === 'enabled'){?>
        <li class="<?php if($enabled === 'pricing'){ echo " active";}?>"><a href="#pricing" data-toggle="tab"> Variant</a></li>
    	<li class="<?php if($enabled === 'tab'){ echo " active";}?>"><a href="#tab-content" data-toggle="tab"> {{trans('admin/program.other_details')}}</a></li>
    	<?php } ?>


    	@if(!empty($productCF))
    		<li class="<?php if($enabled === 'productcustomfield'){ echo " active";}?>"><a href="#productcustomfield" data-toggle="tab"> {{trans('admin/customfields.customfields')}}</a></li>
    	@endif
    </ul>
</div>
<script src="{{ URL::asset('admin/js/calendar.js')}}"></script>
<div id="myTabContent1" class="tab-content">  

	<!-- Custom fields tab starts here -->

		<div class="tab-pane fade <?php if(Session::get('productcustomfield') && Session::get('productcustomfield')==='productcustomfield'){ echo " active in";}?>" id="productcustomfield">
	        <div class="row">
	            <div class="col-md-12">
	                <div class="box">
	                    <div class="box-content">
	                        <form action="{{URL::to('cp/contentfeedmanagement/save-customfield/'.$slug.'?filter=productfields')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped">
	                            @foreach($productCF as $feedfield)
	                            <div class="form-group">
	                                <?php 
	                                    if(Input::old($feedfield['fieldname'])) 
	                                    {
	                                        $field = Input::old($feedfield['fieldname']);
	                                    }
	                                    elseif($errors->first($feedfield['fieldname'])) 
	                                    {
	                                        $field = Input::old($feedfield['fieldname']);
	                                    }
	                                    elseif(isset($programs[0][$feedfield['fieldname']]) && !empty($programs[0][$feedfield['fieldname']]))
	                                    {
	                                        $field = $programs[0][$feedfield['fieldname']];
	                                    }
	                                    else
	                                    {
	                                        $field = "";
	                                    }
	                                ?>
	                                <label class="col-sm-3 col-lg-2 control-label">{{$feedfield['fieldlabel']}}@if($feedfield['mark_as_mandatory'] == 'yes') <span class="red">*</span> @endif</label>
	                                <div class="col-sm-9 col-lg-10 controls">
	                                    <input type="text" value="{{$field}}" name="{{$feedfield['fieldname']}}"> <br>
	                                    {!! $errors->first($feedfield['fieldname'], '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
	                                </div>
	                            </div>
	                            @endforeach
	                            <div class="form-group">
	                                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
	                                   <button type="submit" class="btn btn-info text-right">{{trans('admin/program.save')}} </button>
	                                    <a href="{{URL::to('/cp/contentfeedmanagement/list-products')}}" >
	                                        <button type="button" class="btn">{{trans('admin/program.cancel')}}</button>
	                                    </a> 
	                                </div>
	                            </div>
	                        </form>
	                    </div>
	                </div>
	            </div>
	        </div>
	    </div>

	<!-- Custom fields tab ends here -->


    <div class="tab-pane fade <?php if(Session::get('pricing') && Session::get('pricing')==='enabled'){ echo " active in";}?>" id="pricing">
		<div class="box">
    		<div class="box-title">
        		<div class="box-content">
					@if ( Session::get('success_price') )
						<div class="alert alert-success">
							<button class="close" data-dismiss="alert">×</button>
							{{ Session::get('success_price') }}
						</div>
						<?php Session::forget('success_price'); ?>
					@endif
					<div class="row">
			            <div class="col-md-12" style="padding-bottom: 10px;">
		                   <button onclick="addSubscription();" id="add_subscription" name="add_subscription" class="btn btn-primary pull-right" ><span class="btn btn-circle blue show-tooltip custom-btm" data-original-title="" title="">
		                        <i class="fa fa-plus"></i>
								</span>&nbsp;{{trans('admin/program.add_new_variant')}}
							</button>
							<button onclick="listSubscription();" id="list_subscription" name="list_subscription" class="btn btn-primary pull-right hide"><span class="btn blue show-tooltip custom-btm" data-original-title="" title="">
		                       
								</span>&nbsp;{{trans('admin/program.back')}}
							</button>
		                </div>
            		</div>
					<div class="" id="subscription_list" name="subscription_list">
					@if($pri_ser_info['pri_service'] === 'enabled')
						@include('admin/theme/Catalog/Pricing/product/list_subscription', ['pri_ser_info' => $pri_ser_info])
					@endif
					</div>
					<div class="hide" id="subscription_add" name="subscription_add">
					@if($pri_ser_info['pri_service'] === 'enabled')
						@include('admin/theme/Catalog/Pricing/product/add_subscription', ['pri_ser_info' => $pri_ser_info])
					@endif
					</div>
					<div class="" id="subscription_edit" name="subscription_edit">
						<div id="edisubscriptioncontent" name="edisubscriptioncontent">

			            </div>
					</div>
					<script type="text/javascript">
							function listSubscription()
							{
								$('#subscription_add').addClass('hide');
								$('#subscription_list').removeClass('hide');
								$('#list_subscription').addClass('hide');
								$('#add_subscription').removeClass('hide');
								$('#subscription_edit').addClass('hide');
								$("#subscription-tab input[type=text],input[type=number], textarea").val("");
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
						addSubscription();
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
										  url: "<?php echo URL::to('cp/pricing/edit-variant');?>",
										  data:{ 
										  	slug: slug,
										  	sellable_id:$('#sellable_id').val(),
										  	sellable_type:$('#sellable_type').val(),
										  	program_slug:$('#program_slug').val()
										  }
										})
										  .done(function( msg ) {
										    $('#edisubscriptioncontent').html(msg);
									});
					});
					</script>
        	</div>
    	</div>
	</div>
</div>
	<div class="tab-pane fade <?php if($enabled === 'tab'){ echo " active in";}?>" id="tab-content">
		<div class="box">
    		<div class="box-title">
        		<div class="box-content">
					@if ( Session::get('success_tab') )
						<div class="alert alert-success">
							<button class="close" data-dismiss="alert">×</button>
							{{ Session::get('success_tab') }}
						</div>
						<?php Session::forget('success_tab'); ?>
					@endif
					<div class="row">
			            <div class="col-md-12" style="padding-bottom: 10px;">
							<button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus"></i>
								</span>&nbsp;{{trans('admin/program.add_new_tab')}}
							</button>
							
		                </div>
            		</div>
            		<div class="">
            			<div class="table-responsive">
					    <table class="table table-advance" id="sample">
					        <thead>
					            <tr>
					                <th>{{trans('admin/program.tab_title')}}</th>
					                <th>{{trans('admin/program.created_on')}}</th>
					                <th>{{trans('admin/program.action')}}</th>
					            </tr>
					        </thead>
					        <tbody>
<?php
if(!empty($tabs['tabs'])){
	foreach ($tabs['tabs'] as $key => $value)
	 {
			
?>
					            <tr>
					                <td>
					                {{$value['title']}}
					                </td>
					                <td>
<?php
if(isset($value['created_at'])){
?>
					                {{date('Y-m-d',$value['created_at'])}}
<?php
}
?>
					                </td>
					                <td>
					                 <a class="btn btn-circle show-tooltip openedittabb" title="" data-toggle="modal" data-target="#edittab" data-pid="{{$tabs['p_id']}}" data-tslug="{{$value['slug']}}">
					                    <i class="fa fa-edit"></i>
					                 </a>
					                 <a class="btn btn-circle show-tooltip deletefeed" title="" href="{{URL::to('cp/tab/delete/'.$tabs['p_id'].'/'.$value['slug'].'/product')}}" data-original-title="Delete"><i class="fa fa-trash-o"></i></a>
					                </td>
					             </tr>
<?php
	}
}
else
{
?>
								<tr>
									<td colspan="3" class="text-center">{{trans('admin/program.on_this_points_are_no_tabs')}}</td>
								</tr>
<?php
}
?>                
                    		</tbody>
					    </table>
<!-- Edit tab -->
<div id="edittab" class="modal fade" role="dialog">
  <div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <div class="row custom-box">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-title">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h3 class="modal-header-title">{{trans('admin/program.edit_tab')}}</h3>                                                
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-body">
	        <div id="edittabBody">
	        </div>
        </div>
        
    </div>
</div>
</div>
<script type="text/javascript">
        $(document).on("click", ".openedittabb", function () {
        
             $.ajax({
                      method: "POST",
                      url: "<?php echo URL::to('cp/tab/edit');?>/"+$(this).data('pid')+"/"+$(this).data('tslug'),
                      data:{                      
                      }
                    })
                      .done(function( msg ) {
                        $('#edittabBody').html(msg);
                });
});
</script> 
<!-- Edit tab -->
					</div>
            		</div>
					<div class="" id="tab_list" name="tab_list">
					@if($pri_ser_info['pri_service'] === 'enabled')
						@include('admin/theme/Catalog/tabs/add', ['pri_ser_info' => $pri_ser_info])
					@endif
					</div>
        	</div>
    	</div>
	</div> 
	<script type="text/javascript">
	$(document).ready(function () {
 
		window.setTimeout(function() {
		    $(".alert").fadeTo(1500, 0).slideUp(500, function(){
		        $(this).remove(); 
		    });
		}, 1000);
		 
		});
	</script>
	</div>        
    	
<!-- Content Feed -->
<div class="tab-pane fade <?php if($enabled === 'edit'){ echo " active in";}?>" id="content-feed">
    @if ( Session::get('success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">×</button>
		<!-- 	<strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
		<?php Session::forget('success'); ?>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Error!</strong> -->
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<?php 
		$start    =  Input::get('start', 0);
		$limit    =  Input::get('limit', 10);
		$filter   =  Input::get('filter','all');
		$search   =  Input::get('search','');
		$order_by =  Input::get('order_by','2 desc');
  	?>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Edit {{trans('admin/program.program')}}</h3> -->
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div>
	            </div>
	            <?php if(isset($programs)){ ?>
		            <?php foreach($programs as $program){ ?>
			            <div class="box-content">
		                    <form action="" class="form-horizontal form-bordered form-row-stripped" method="post">
		                        <div class="form-group">
		                            <label for="feed_title" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.title')}} <span class="red">*</span></label>
		                            <div class="col-sm-9 col-lg-10 controls">
                             <input type="text" name="feed_title" id="feed_title" placeholder="{{trans('admin/program.product')}} {{trans('admin/program.title')}}" class="form-control" value="{{ $program->program_title }}">
		                                <input type="hidden" name="feed_slug" id="feed_slug" placeholder="Feed Slug" class="form-control" value="{{ $program->program_slug }}">
		                                <input type="hidden" name="old_feed_slug"  placeholder="Feed Slug" class="form-control" value="{{ $program->program_slug }}">
			                            <?php $msg = $errors->first('feed_title', '<span class="help-inline" style="color:#f00">:message</span>'); ?>
			                            <?php if($msg == "") echo $errors->first('feed_slug', '<span class="help-inline" style="color:#f00">:message</span>'); else echo $msg; ?>

		                            </div>
		                        </div>

		                        <div class="form-group">
		                            <label for="feed_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.start_date')}} <span class="red">*</span></label>
		                            <div class="col-sm-9 col-lg-10 controls">
		                               <div class="input-group date">
                              				<span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
		                                		<input type="text" readonly name="feed_start_date" id="feed_start_date" placeholder="{{trans('admin/program.program')}} {{trans('admin/program.start_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$program->program_startdate,Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
		                                	</div>
		                                {!! $errors->first('feed_start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div>
		                        <div class="form-group">
		                            <label for="feed_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.end_date')}} <span class="red">*</span></label>
		                            <div class="col-sm-9 col-lg-10 controls">
		                               <div class="input-group date">
                              				<span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
		                                		<input type="text" readonly name="feed_end_date" id="feed_end_date" placeholder="{{trans('admin/program.program')}} {{trans('admin/program.end_date')}}" class="form-control datepicker_end_date" value="{{ Timezone::convertFromUTC("@".$program->program_enddate,Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
		                                </div>
		                                {!! $errors->first('feed_end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div>
		                        <div class="form-group">
		                            <label for="feed_display_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.display_start_date')}} <span class="red">*</span></label>
		                            <div class="col-sm-9 col-lg-10 controls">
		                               <div class="input-group date">
                              				<span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
		                                		<input type="text" readonly name="feed_display_start_date" id="feed_display_start_date" placeholder="{{trans('admin/program.program')}} {{trans('admin/program.display_start_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$program->program_display_startdate,Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
		                                </div>
		                                {!! $errors->first('feed_display_start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div>
		                        <div class="form-group">
		                            <label for="feed_display_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.display_end_date')}} <span class="red">*</span></label>
		                            <div class="col-sm-9 col-lg-10 controls">
		                               <div class="input-group date">
                             				<span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
		                                		<input type="text" readonly name="feed_display_end_date" id="feed_display_end_date" placeholder="{{trans('admin/program.program')}} {{trans('admin/program.display_end_date')}}" class="form-control datepicker_end_date" value="{{ Timezone::convertFromUTC("@".$program->program_display_enddate,Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
		                                </div>
		                                {!! $errors->first('feed_display_end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div>
		                        <div class="form-group">
		                            <label for="sellability" class="col-sm-2 col-lg-2 control-label">{{trans('admin/program.sellability')}}</label>
		                            <div class="col-sm-4 col-lg-4 controls">
		                                <select class="form-control" name="sellability" id="sellability" data-rule-required="true"  disabled="true">
		                                    <option <?php if($program->program_sellability == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
		                                    <option <?php if($program->program_sellability == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
		                                </select>
		                                {!! $errors->first('sellability', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                            <label for="visibility" class="col-sm-1 col-lg-1 control-label">{{trans('admin/program.visibility')}}</label>
		                            <div class="col-sm-5 col-lg-5 controls">
		                                <select class="form-control" name="visibility" id="visibility" data-rule-required="true">
		                                    <option <?php if($program->program_visibility == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
		                                    <option <?php if($program->program_visibility == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
		                                </select>
		                                {!! $errors->first('visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div>
		                        <div class="form-group">
		                            <label for="select" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.status')}}</label>
		                            <div class="col-sm-5 col-lg-5 controls">
		                                <select class="form-control" name="status" id="status" data-rule-required="true">
		                                    <option <?php if($program->status == "ACTIVE") echo "selected"?> value="active">{{trans('admin/program.active')}}</option>
		                                    <option <?php if($program->status == "IN-ACTIVE") echo "selected"?> value="inactive">{{trans('admin/program.in_active')}}</option>
		                                </select>
		                                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div>
									<input type="hidden" name="hiddensellability" value="{{$program->program_sellability}}">
									<!--start-->
									<?php if($program->program_sellability=='yes') {?>
						           <div class="form-group" id="access">
                            <label for="select" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.access')}}</label>
                            <div class="col-sm-5 col-lg-5 controls">
                                <select class="form-control" name="program_access" id="program_access" data-rule-required="true">
                                    <option <?php if($program->program_access == "general_access") echo "selected"?> value="general_access">{{trans('admin/program.general')}}</option>
                                    <!-- <option <?php if($program->program_access == "restricted_access") echo "selected"?> value="restricted_access">{{trans('admin/program.restricted')}}</option> -->
                                </select>
                                {!! $errors->first('access', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
							<?php } ?>
						<!--end-->
		                        <div class="form-group">
		                            <label for="feed_description" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.description')}} </label>
		                            <div class="col-sm-9 col-lg-10 controls">
		                                <textarea name="feed_description" id="feed_description" rows="5" class="form-control" placeholder="Product Description">{{ $program->program_description }}</textarea>
		                                {!! $errors->first('feed_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div>
		                        <div class="form-group">
									<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.keyword_tags')}}</label>
									<div class="col-sm-9 col-lg-10 controls">
										<input type="text" class="form-control tags medium" value="<?php echo (isset($program->program_keywords) && is_array($program->program_keywords)) ? implode(',',$program->program_keywords) : ""; ?>" name="feed_tags" />
										{!! $errors->first('feed_tags', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									</div>
								</div>
		                        <div class="form-group">
									<label class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.cover_image')}} </label>
									<div class="col-sm-9 col-lg-10 controls">
										<div class="fileupload fileupload-new">
											<div class="fileupload-new img-thumbnail" style="width: 200px;padding:0">
												<?php if($program->program_cover_media){ ?>
													<img src="{{URL::to('/cp/dams/show-media/'.$program->program_cover_media)}}" width="100%" alt="" id="bannerplaceholder"/>
												<?php } else{ ?>
													<img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" id="bannerplaceholder"/>
												<?php } ?>
											</div>
											<div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
											<div>
												<button class="btn" type="button" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&filter=image&select=radio')}}">{{trans('admin/program.select')}}</button>
												@if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
												<button class="btn" type="button" id="upload" data-url="{{URL::to('cp/dams/add-media?view=iframe&filter=image')}}">{{trans('admin/program.upload_new')}}</button>
												@endif
												<?php
														if($program->program_cover_media){ ?>
															<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/program.remove')}} </button>
												<?php 	}
													?>
												<input type="hidden" name="banner" value="{{(isset($program->program_cover_media)) ? $program->program_cover_media : ""}}">
											</div>
										</div>
		                                {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									</div>
								</div>
		                        <!-- <div class="form-group">
		                            <label for="review" class="col-sm-2 col-lg-2 control-label">Review <span class="red">*</span></label>
		                            <div class="col-sm-4 col-lg-4 controls">
		                                <select class="form-control" name="review" id="review" data-rule-required="true">
		                                    <option <?php if($program->program_review == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
		                                    <option <?php if($program->program_review == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
		                                </select>
		                                {!! $errors->first('review', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                            <label for="rating" class="col-sm-1 col-lg-1 control-label">Rating <span class="red">*</span></label>
		                            <div class="col-sm-5 col-lg-5 controls">
		                                <select class="form-control" name="rating" id="rating" data-rule-required="true">
		                                    <option <?php if($program->program_rating == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
		                                    <option <?php if($program->program_rating == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
		                                </select>
		                                {!! $errors->first('rating', '<span class="help-inline" style="color:#f00">:message</span>') !!}
		                            </div>
		                        </div> -->
		                        <div class="form-group last">
		                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
		                               <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{trans('admin/program.save')}}</button>
		                               <a href="{{URL::to('/cp/contentfeedmanagement/list-products')}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}"><button type="button" class="btn">{{trans('admin/program.cancel')}}</button></a>
		                            </div>
		                        </div>
		                     </form>
		                </div>
                	<?php } ?>
                <?php } ?>
	        </div>
	    </div>
	    <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
	        <div class="modal-dialog modal-lg">
	            <div class="modal-content">
	                <div class="modal-header">
	                    <div class="row custom-box">
	                        <div class="col-md-12">
	                            <div class="box">
	                                <div class="box-title">
	                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                    <h3 class="modal-header-title" >
	                                        <i class="icon-file"></i>
	                                            {{trans('admin/program.view_media_details')}}
	                                    </h3>                                                
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	                <div class="modal-body">
	                    ...
	                </div>
	                <div class="modal-footer">
	                       <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/program.assign')}}</a>
		                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/program.close')}}</a>
	                </div>
	            </div>
	        </div>
	    </div>
	    <script>
	    	$(document).ready(function(){
	    		
	    		$('.datepicker').datepicker({
	    			format : "dd-mm-yyyy"
	    			}).on('changeDate',function(){
	    				$(this).datepicker('hide')
	    			});
	   

	    		$('.datepicker_end_date').datepicker({
	    			format : "dd-mm-yyyy",
	    			startDate : "+0d"
	    			}).on('changeDate',function(){
	    				$(this).datepicker('hide')
	    			});
	   
	    		$('[name="feed_title"]').on("blur",function(){
					if($(this).val().trim() != ""){
						var slug=$('[name="feed_slug"]').val($(this).val().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'))

                        //If name contains special characters,generated slug will be empty.Sending slug as special character to get proper validation.
                        if(!slug.val())
                        {
                            $('[name="feed_slug"]').val('$*&');
                        }
					}
				});
				
				

				$('#selectfromdams, #upload').click(function(e){
	    			e.preventDefault();
	    			simpleloader.fadeIn();
	    			var $this = $(this);
	    			var $triggermodal = $('#triggermodal');
	    			var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="" style="max-height:500px !important" frameBorder="0"></iframe>');
	    			$iframeobj.unbind('load').load(function(){
	    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
	    					$triggermodal.modal('show');
	    				simpleloader.fadeOut();
	    			});
	    			$triggermodal.find('.modal-body').html($iframeobj);
	    			$triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());
	    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
	    				var $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked');
	    				if($selectedRadio.length){
	    					$('#bannerplaceholder').attr('src','{{URL::to('/cp/dams/show-media/')}}/'+$selectedRadio.val()).width("100%");
	    					$('#removethumbnail').remove();
	    					$('<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/program.remove')}} </button>').insertBefore($('input[name="banner"]').val($selectedRadio.val()));
	    					$triggermodal.modal('hide');
	    				}
	    				else{
	    					alert('Please select atleast one entry');
	    				}
	    			});
				});
	    		$('input.tags').tagsInput({
		            width: "auto"
		        });
				$(document).on('click','#removethumbnail',function(){
					$('#bannerplaceholder').attr('src', '{{URL::asset("admin/img/demo/200x150.png")}}');
					$('input[name="banner"]').val('');
					$(this).remove();
				});
	    	})
	    </script>
	</div>
    </div>
</div>
<!-- Content Feed Ends -->
@stop
