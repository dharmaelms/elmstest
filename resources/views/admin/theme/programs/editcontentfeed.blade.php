@section('content')

	<link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
	<script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker-channel.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
	<script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
	<script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	<script src="{{ URL::asset('admin/js/calendar.js')}}"></script>

	@if ( Session::get('ap_success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- 	<strong>Success!</strong> -->
			{{ Session::get('ap_success') }}
		</div>
		<?php Session::forget('ap_success'); ?>
	@endif
	<div class="tabbable">
		<?php
		$enble = array('pricing','edit','tab','pack');
		$enabled = 'edit';
		if(Session::get('pricing')) {
			$enabled = "pricing";
		}
		if(Session::get('tab')) {
			$enabled = "tab";
		}
		if(Session::get('feedcustomfield')) {
			$enabled = "feedcustomfield";
		}
		?>
		<ul id="myTab1" class="nav nav-tabs">
			<li class="<?php if($enabled === 'edit'){ echo "active";} ?>">
				<a href="#content-feed" data-toggle="tab">
					<i class="fa fa-home"></i> {{trans('admin/program.general_info')}}
				</a>
			</li>
			@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::CHANNEL_ASSIGN_USER))
				<li>
					<a href="#channel-users" data-toggle="tab" id="channel-users-tab">
						<i class="fa fa-user"></i>Channel Users
					</a>
				</li>
			@endif
			@if(config('app.ecommerce'))
				@if($pri_ser_info['pri_service'] === 'enabled')
				<?php $array_channel_data = array_get($programs, 0); ?>
					@if((isset($array_channel_data->program_sellability)) && ($array_channel_data->program_sellability == 'yes')) 	
						<li class="<?php if($enabled === 'pricing'){ echo " active";}?>">
							<a href="#pricing" data-toggle="tab">
								<i class="fa fa-shopping-cart"></i> Pricing
							</a>
						</li>
					@endif
				@endif

				<li class="<?php if($enabled === 'tab'){ echo " active";}?>">
					<a href="#tab-content" data-toggle="tab">
						{{trans('admin/program.other_details')}}
					</a>
				</li>
			@endif
			<li class="<?php if($enabled === 'feedcustomfield'){ echo " active";}?>">
				<a  class ="feedcustomfield" href="#feedcustomfield" data-toggle="tab">
					{{trans('admin/customfields.customfields')}}
				</a>
			</li>
		</ul>
	</div>

	<div id="myTabContent1" class="tab-content">
		<!-- Custom fields tab starts here -->
		<div class="tab-pane fade <?php if(Session::get('feedcustomfield') && Session::get('feedcustomfield')==='feedcustomfield'){ echo " active in";}?>" id="feedcustomfield">
			<div class="row">
				<div class="col-md-12">
					<div class="box">
						<div class="box-content">
							<form action="{{URL::to('cp/contentfeedmanagement/save-customfield/'.$prgm[0]['program_slug'].'?filter=channelfields')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped">
								@foreach($feedCF as $feedfield)
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
										elseif(isset($prgm[0][$feedfield['fieldname']]) && !empty($prgm[0][$feedfield['fieldname']]))
										{
											$field = $prgm[0][$feedfield['fieldname']];
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
										<a href="{{URL::to('/cp/contentfeedmanagement/')}}" >
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

		@if(config('app.ecommerce'))
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
								</span>&nbsp;{{trans('admin/program.add_new_subscription')}}
									</button>
									<button onclick="listSubscription();" id="list_subscription" name="list_subscription" class="btn btn-primary pull-right hide"><span class="btn blue show-tooltip custom-btm" data-original-title="" title="">

								</span>&nbsp;{{trans('admin/program.back')}}
									</button>
								</div>
							</div>
							<div class="" id="subscription_list" name="subscription_list">
								@if($pri_ser_info['pri_service'] === 'enabled')
									@include('admin/theme/Catalog/Pricing/list_subscription', ['pri_ser_info' => $pri_ser_info])
								@endif
							</div>
							<div class="hide" id="subscription_add" name="subscription_add">
								@if($pri_ser_info['pri_service'] === 'enabled')
									@include('admin/theme/Catalog/Pricing/add_subscription', ['pri_ser_info' => $pri_ser_info])
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
                                        url: "<?php echo URL::to('cp/pricing/edit-subscription');?>",
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
                                        @if(!empty($tabs['tabs']))
                                        	@foreach ($tabs['tabs'] as $key => $value)
												<tr>
													<td>{{$value['title']}}</td>
													<td>
                                                        @if(isset($value['created_at']))
															{{date('Y-m-d',$value['created_at'])}}
														@endif
													</td>
													<td>
														<a class="btn btn-circle show-tooltip openedittabb" title="" data-toggle="modal" data-target="#edittab" data-pid="{{$tabs['p_id']}}" data-tslug="{{$value['slug']}}">
															<i class="fa fa-edit"></i>
														</a>
														<a class="btn btn-circle show-tooltip deletefeed" title="" href="{{URL::to('cp/tab/delete/'.$tabs['p_id'].'/'.$value['slug'])}}" data-original-title="{{ trans('admin/program.delete') }}"><i class="fa fa-trash-o"></i></a>
													</td>
												</tr>
                                        	@endforeach
										@else
											<tr>
												<td colspan="3" class="text-center">{{trans('admin/program.on_this_points_are_no_tabs')}}</td>
											</tr>
										@endif
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
								@include(
                                    'admin/theme/Catalog/tabs/add',
                                    [
                                        "from" => "program",
                                        "program_type" => $prgm[0]["program_type"],
                                        "program_slug" => $prgm[0]["program_slug"],
                                        "pri_ser_info" => $pri_ser_info
                                    ]
                                )
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
		@endif

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
							<div class="box-tool">
								<a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
								<a data-action="close" href="#"><i class="fa fa-times"></i></a>
							</div>
						</div>
						<div class="box-content">
							<?php
								$program = $programs[0];
								if (isset($program->benchmarks['speed'])) {
									$varSpeed = $program->benchmarks['speed'];
									$hrSpeedTemp = (int)$varSpeed/3600;
									$hrSpeed = explode('.', $hrSpeedTemp)[0];
									$minSpeed = ((int)($varSpeed/60)) % 60;
								} else {
									$hrSpeed = 0;
									$minSpeed = 0;
								}
                            ?>
							<form action="" class="form-horizontal form-bordered form-row-stripped" method="post">
								<div class="form-group">
                                    <?php
                                    if(Input::old('feed_title'))
                                    {
                                        $feed_title = Input::old('feed_title');
                                    } elseif($errors->first('feed_title')) {
                                        $feed_title = Input::old('feed_title');
                                    } else {
                                        $feed_title = $program->program_title;
                                    }
                                    ?>
									<label for="feed_title" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.title')}} <span class="red">*</span></label>
									<div class="col-sm-9 col-lg-10 controls">
										<input type="text" name="feed_title" id="feed_title" placeholder="{{trans('admin/program.program')}} {{trans('admin/program.title')}}" class="form-control" value="{{ $feed_title }}">
										<input type="hidden" name="feed_slug" id="feed_slug" placeholder="Feed Slug" class="form-control" value="{{ $program->program_slug }}">
										<input type="hidden"  name="old_feed_slug"  class="form-control" value="{{ $program->program_slug }}">
                                        <?php $msg = $errors->first('feed_title', '<span class="help-inline" style="color:#f00">:message</span>'); ?>
                                        <?php if($msg == "") echo $errors->first('feed_slug', '<span class="help-inline" style="color:#f00">:message</span>'); else echo $msg; ?>

									</div>
								</div>
								<!--short name ends here-->
								<div class="form-group">
                                    <?php
                                    if(Input::old('program_shortname'))
                                    {
                                        $program_shortname = Input::old('program_shortname');
                                    } elseif($errors->first('feed_slug')) {
                                        $program_shortname = Input::old('program_shortname');
                                    } else {
                                        $program_shortname = $program->program_shortname;
                                    }
                                    ?>
									<label class="col-sm-3 col-lg-2 control-label" for="">{{trans('admin/program.short_name')}}</label>
									<div class="col-sm-9 col-lg-10 controls">
										<input type="hidden" name="old_shortname" class="form-control" value="{{$program->program_shortname}}">
										<input type="hidden" name="feed_shortname_slug" id="feed_shortname_slug" placeholder="Feed Slug" class="form-control" value="{{ Input::old('feed_shortname_slug') }}">
										<input type="text" class="form-control" name="program_shortname" value="{{$program_shortname}}"  placeholder="{{trans('admin/program.short_name_nt_disp')}}">
										{!! $errors->first('program_shortname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									</div>
								</div>
								<!--short name ends here-->
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
											<input type="text" readonly name="feed_end_date" id="feed_end_date" placeholder="{{trans('admin/program.program')}} {{trans('admin/program.end_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$program->program_enddate,Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
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
											<input type="text" readonly name="feed_display_end_date" id="feed_display_end_date" placeholder="{{trans('admin/program.program')}} {{trans('admin/program.display_end_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$program->program_display_enddate,Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
										</div>
										{!! $errors->first('feed_display_end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									</div>
								</div>
								@if (config('app.ecommerce') == true)
									<div class="form-group">
										<label for="sellability" class="col-sm-2 col-lg-2 control-label">{{trans('admin/program.sellability')}} <span class="red">*</span></label>
										<div class="col-sm-4 col-lg-4 controls">
											<select class="form-control" name="sellability" id="sellability" data-rule-required="true">
												<option <?php if($program->program_sellability == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
												<option <?php if($program->program_sellability == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
											</select>
											{!! $errors->first('sellability', '<span class="help-inline" style="color:#f00">:message</span>') !!}
										</div>
									</div>
								@endif
								<div class="form-group">
									<label for="visibility" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.visibility')}} <span class="red">*</span></label>
									<div class="col-sm-5 col-lg-5 controls">
										<select class="form-control" name="visibility" id="visibility" data-rule-required="true">
											<option <?php if($program->program_visibility == "yes") echo "selected"?> value="yes">{{trans('admin/program.yes')}}</option>
											<option <?php if($program->program_visibility == "no") echo "selected"?> value="no">{{trans('admin/program.no')}}</option>
										</select>
										{!! $errors->first('visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
									</div>
								</div>
								<div class="form-group">
									<label for="select" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.status')}} <span class="red">*</span></label>
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
								@if (config('app.ecommerce') == false)
									<div class="form-group" id="access">
										<label for="select" class="col-sm-3 col-lg-2 control-label">
											{{trans('admin/program.access')}}<span class="red">*</span>
										</label>
										<div class="col-sm-5 col-lg-5 controls">
											<select class="form-control" name="program_access" id="program_access" data-rule-required="true" @if(!empty($program->relations['active_user_feed_rel']) || !empty($program->relations['active_usergroup_feed_rel']) || (isset($program->parent_relations['active_parent_rel']) && !empty($program->parent_relations['active_parent_rel'])) || (isset($subscription_array) && !empty($subscription_array))) @endif>
												<option <?php if($program->program_access == "restricted_access") echo "selected"?> value="restricted_access">{{trans('admin/program.restricted')}}</option>
												<option <?php if($program->program_access == "general_access") echo "selected"?> value="general_access">{{trans('admin/program.general')}}</option>
											</select>
											{!! $errors->first('access', '<span class="help-inline" style="color:#f00">:message</span>') !!}
										</div>
										<div>
											<span class="glyphicon glyphicon-exclamation-sign" data-toggle="tooltip" title="{{ trans("admin/program.program_access") }}"></span>
										</div>
									</div>
							@endif
							<!--end-->
								<div class="form-group">
									<label for="feed_description" class="col-sm-3 col-lg-2 control-label">{{trans('admin/program.description')}} </label>
									<div class="col-sm-9 col-lg-10 controls">
										<textarea name="feed_description" id="feed_description" rows="5" class="form-control" placeholder="{{trans('admin/program.content_feed')}} Description">{{ $program->program_description }}</textarea>
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
									<div class="col-sm-12 col-lg-6">
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
													<button class="btn" type="button" id="selectfromdams"
															data-url="{{URL::to("/cp/dams?view=iframe&from=program&program_type={$program->program_type}&program_slug={$program->program_slug}&filter=image&select=radio")}}">
														{{trans('admin/program.select')}}
													</button>
													@if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
													<button class="btn" type="button" id="upload" data-url="{{URL::to("cp/dams/add-media?view=iframe&from=program&program_type={$program->program_type}&program_slug={$program->program_slug}&filter=image")}}">{{trans('admin/program.upload_new')}}</button>
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
									@if(config('app.channelAnalytic') == 'on')

										<div class="col-sm-12 col-lg-6">
											<label><strong>{{trans('admin/program.benchmarks')}}</strong></label><br>
											<div>
												<label class="col-sm-6 col-lg-4">{{trans('admin/program.accuracy')}}</label>
												<input class = 'matrics-accuracy col-sm-6 col-lg-4' type="text" name="accuracy" value="@if(isset($program->benchmarks['accuracy']) ){{$program->benchmarks['accuracy']}}@endif"></input>
												<span> &nbsp;&nbsp; %</span>
											</div>
											<br>
											<div>
												<label  class="col-sm-6 col-lg-4">{{trans('admin/program.score')}}</label>
												<input class="matrics-score col-sm-6 col-lg-4" type="text" name="score" value="@if(isset($program->benchmarks['score']) ){{$program->benchmarks['score']}}@endif"></input> <span> &nbsp;&nbsp; %</span>
											</div>
											<br>
											<div>
												<label  class="col-sm-6 col-lg-4">{{trans('admin/program.speed')}}
													<div><span class="help-inline">{{trans('admin/program.mm_ss')}}&nbsp;&nbsp;</span></div>
												</label>
												<div class="input-group col-sm-6 col-lg-5">
													<span class="input-group-addon" href="#"><i class="fa fa-clock-o"></i></span>
													<input type="text" name="speed" onclick="$(this).prev().click()" class="form-control input-time" style="cursor: pointer" value="{{$hrSpeed}}:{{$minSpeed}}" readonly="readonly">
													<span class="input-group-btn">
													<button id="speed_reset" class="btn"><i class="fa fa-undo"></i></button>
												</span>
												</div>
											</div>
										</div>
									@endif
								</div>
								<div class="form-group last">
									<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
										<button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{trans('admin/program.save')}}</button>
										<a href="{{URL::to('/cp/contentfeedmanagement/'.$url)}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}"><button type="button" class="btn">{{trans('admin/program.cancel')}}</button></a>
									</div>
								</div>
							</form>
						</div>
					</div>
				</div>
				<div class="modal fade" id="trigger_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
							format : "dd-mm-yyyy",
							startDate: '+0d'
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

						$('[name="program_shortname"]').on("blur",function(){
		                    if($(this).val().trim() != ""){
		                        var sort_slug=$('[name="feed_shortname_slug"]').val($(this).val().toLowerCase().replace(/[^\w ]+/g,'').trim().replace(/ +/g,'-'))

		                        //If name contains special characters,generated slug will be empty.Sending slug as special character to get proper validation.
		                        if(!sort_slug.val())
		                        {
		                            $('[name="feed_shortname_slug"]').val('$*&');
		                        }
		                        $('[name="feed_title"]').trigger('blur');

		                    }else{
		                    	$('[name="feed_shortname_slug"]').val('');
		                    }
		                });

						$('#selectfromdams, #upload').click(function(e){

							e.preventDefault();
							simpleloader.fadeIn();
							var $this = $(this);
							var $triggermodal = $('#trigger_modal');
							var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="500px" style="max-height:500px !important" frameBorder="0"></iframe>');
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


						$("input[name=accuracy]").on('keypress', function(event){
							console.log('on the way');
							var char = String.fromCharCode(event.which)
							if ( !char.match(/^[0-9]*$/) || ($("input[name=accuracy]").val().length > 2)
									|| ($("input[name=accuracy]").val() > 10)){
								event.preventDefault();
								return false;
							}
						});
						$("input[name=score]").on('keypress', function(event){
							var char = String.fromCharCode(event.which)
							if ( !char.match(/^[0-9]*$/) || ($("input[name=score]").val().length > 2)
									|| ($("input[name=score]").val() > 10)){
								event.preventDefault();
								return false;
							}
						});
						$('.input-time').timepicker({
							minuteStep: 1,
							secondsStep: 5,
							showSeconds: true,
							showHours: false,
							showMeridian: false,
							defaultTime: false
						});

						$('#speed_reset').click(function(e) {
							e.preventDefault();
							$('input[name=speed]').val('');
							$('input[name=speed]').val('00:00');
						});
						$('[name="program_shortname"]').trigger('blur');
					})
				</script>
			</div>
		</div>

		@if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::CHANNEL_ASSIGN_USER))
			<div class="tab-pane fade" id="channel-users">
				@include("admin.theme.users.partials._users_list_template1", [
				"module" => "program",
				"instance_id" => $programs[0]["program_id"],
				"slug" => $programs[0]["program_slug"],
				])
			</div>
		@endif
	</div>

	<script>
		var active_tab = window.location.hash;
		$(document).ready(function () {
			if (active_tab !== undefined) {
				$(active_tab).tab("show");
			}
		});
	</script>
	<!-- Content Feed Ends -->
@stop
