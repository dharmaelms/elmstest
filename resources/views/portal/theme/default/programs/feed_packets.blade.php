@section('content')
<?php
    use App\Model\MyActivity;
    use App\Model\SiteSetting;
    use App\Model\Program;
    use App\Model\TransactionDetail;

    $general = SiteSetting::module('General');
    $subscriptionCollection = collect(Auth::user()->subscription);
	$subsCripedProgramCollection = $subscriptionCollection->groupBy('program_id');
	$subsProgId = $subsCripedProgramCollection->keys();
	$get_date = TransactionDetail::getUserProgramDates(Auth::user()->uid,$program['program_id']);
	if(empty($get_date)) {
	$s_date = $program['program_startdate'];
	$e_date = $program['program_enddate'];
	}
	else if($get_date[0]['start_date'] > 0 || $get_date[0]['end_date'] > 0) {
	$s_date = $get_date[0]['start_date'];
	$e_date = $get_date[0]['end_date'];
	}
	else {
	$s_date = $program['program_startdate'];
	$e_date = $program['program_enddate'];
	}
	if(in_array($program['program_id'], $subsProgId->toArray())) {
		$spcificProg = $subsCripedProgramCollection->get($program['program_id']);
		if(!is_null($spcificProg) &&
			 isset($spcificProg[0]['start_time']) &&
			 isset($spcificProg[0]['end_time'])){
			$s_date = $spcificProg[0]['start_time'];
			$e_date = $spcificProg[0]['end_time'];
		}
	}
    $package = Program::getProgramDetailsByID($program['program_id']);
	$child_program_list='';
	if($package['program_sub_type'] == 'collection') {
	   if(isset($package['child_relations']['active_channel_rel']) && !empty($package['child_relations']['active_channel_rel'])) {
	      foreach($package['child_relations']['active_channel_rel'] as $child_id) {
		          $child_program = Program::getProgramDetailsByID($child_id);
				  $child_program_list.= '<a style="color:#297076" href="'.URL::to('program/packets/'.$child_program['program_slug']).'">'.$child_program['program_title'].'<a>, ';
		  }
	   }
	}

	$records_per_page = SiteSetting::module('General', 'products_per_page', 10);
    if($records_per_page == '')
    {
        $records_per_page = 10;
    }
?>
<style type="text/css">
	.faq_inactive{
	    display:none;
	}
	.faq_active{
	    display:block;
	}
	.nav-tabs > li > a, .nav-pills > li > a {
    font-size: 14px;
    width: 100%;
    border: 2px outset #928a8a;}
    
	.nav-pills>li.active>a, .nav-pills>li.active>a:focus, .nav-pills>li.active>a:hover {
    color: #fff;
    background-color: #02b7bf!important;
    border: 2px outset #839696!important;
}
tabbable-line > .tab-content {
    margin-top: -3px;
    background-color: #fff;
    border: 0;
    border-top: 1px outset #7cb5b878!important;
    padding: 15px 0;
}
</style>
<?php

	$course_title_fun = function($program_id,$program_title){

		$parent_course = Session::get('parent_course');

		$course_title = '';

		array_where($parent_course, function($key, $value)

			use ($program_id,&$course_title)
	            {

	                if($program_id == $value['program_id'])
	                {

	                    $course_title = $value['program_title'];

	                }

	            });

		return $course_title." - " .$program_title;

	};

	$program_title = $program['program_title'];

	if($program['program_type'] == 'course')
	{

		$program_title = $course_title_fun($program['parent_id'],$program_title);

	}

?>

<!-- BEGIN PAGE HEADER-->
@if($other_channel != 1)
    <div class="page-bar margin-left-10">
        <ul class="page-breadcrumb">
            <li><a href="{{url('dashboard')}}">{{ trans('program.my_course') }}</a>
                <i class="fa fa-angle-right"></i>
            </li>
        @if($lms_menu_settings->setting['programs'] == "off") 
            <li><a href="{{url('program/my-packages')}}">{{ trans('program.my_course') }}</a>
                <i class="fa fa-angle-right"></i>
            </li>
        @else
            <li><a href="{{url('program/my-feeds')}}">{{ trans('program.my_course') }}</a>
                <i class="fa fa-angle-right"></i>
            </li>
        @endif
            <li><a href="#">{{str_limit($program_title, $limit = 50, $end = '...')}}</a></li>
        </ul>
    </div>
@else
    <div class="page-bar">
		<ul class="page-breadcrumb">
			<li><a href="{{url('dashboard')}}"><?php echo Lang::get('dashboard.dashboard');?></a><i class="fa fa-angle-right"></i></li>
			<li><a href="{{url('program/more-feeds')}}"><?php echo Lang::get('program.other_courses');?></a><i class="fa fa-angle-right"></i></li>
			<li><a href="#">{{str_limit($program_title, $limit = 50, $end = '...')}}</a></li>
		</ul>
	</div>
@endif
<!-- END PAGE HEADER-->

<div class="row">
	<div class="col-lg-offset-1 col-lg-10 col-lg-ofset-1">
	<div class="sm-margin"></div><!--space-->
		<div class="panel-group accordion" id="accordion3">
			<div class="panel panel-info " style="background: #d9edf7;">
				<div class="panel-heading">
					@if(empty($packets) || ($channel_status != false))
						<?php $class=''; $aria='true'; $in='in';?>
					@else
						<?php $class='collapsed'; $aria='false'; $in='';?>
					@endif
					<h4 class="panel-title panel-info m-btm-12">
						<div class="row">
							<div class="col-md-9 col-sm-9">
								<span class="caption gray font-20 bold padding-10 "><i class="fa fa-gear green font-10" style="margin-top: 13px;"></i> {{$program_title}} </span>
							</div>
							<div class="col-md-3 col-sm-3">
							<!--a class="accordion-toggle accordion-toggle-styled {{$class}}" data-toggle="collapse" data-parent="#accordion3" href="#collapse_1"><!-- <span class="badge badge-roundless badge-success">NEW</span> -></a-->
								@if($channel_status == 'coming_soon')
								<?php $label_class="coming-label"; $label="Coming soon"; ?>
								@elseif($channel_status == 'expired')
								<?php $label_class="expired-label"; $label="Expired"; ?>
								@elseif($channel_status == 'inactive')
								<?php $label_class="inactive-label"; $label="Inactive" ?>
								@else
								<?php $label_class=""; $label="" ?>
								@endif
								<span class="badge {{$label_class}} pull-right">{{$label}}</span>

							</div>
						</div>
					</h4>
				</div>
				<div id="collapse_1" class="panel-collapse collapse {{$in}}">
					<div class="panel-body">
						<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 xs-margin">
							@if(isset($program['program_cover_media']) && !empty($program['program_cover_media']))
								<img src="{{URL::to('media_image/'.$program['program_cover_media'])}}" alt="Program" class="img-responsive">
							@else
								<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Default Image" class="img-responsive">
							@endif
						</div>
						<?php $category=''; ?>
						@foreach($categories as $info)
							<?php $category.= html_entity_decode($info['category_name']).',';?>
						@endforeach
						<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
							<h6><strong>Description</strong></h6>
							<p style="word-wrap: break-word;">{!! $program['program_description'] !!}</p>

							<div class="font-12 xs-margin">
								<span class="start">STARTS:</span>&nbsp; <strong>{{ Timezone::convertFromUTC('@'.$s_date, Auth::user()->timezone, Config('app.date_format')) }}</strong> <br>
								<span class="end">ENDS:</span>&nbsp; <strong>{{ Timezone::convertFromUTC('@'.$e_date, Auth::user()->timezone, Config('app.date_format')) }}</strong><br>
							    @if(!empty($child_program_list))
								<span class="start black">{{Lang::get('program.child_course')}}:</span>&nbsp; <strong><?php echo trim($child_program_list,', '); ?></strong>
								@endif
							</div>

							@if((isset($program['program_sub_type'])  && $program['program_sub_type']=='single') || !isset($program['program_sub_type']))
							<div class="xs-margin">
								<p><img src="{{URL::asset($theme.'/img/packet-icon.png')}}" alt="Packet Icon" class="img-ver-btm">&nbsp;&nbsp;<strong>{{$packets_count}} {{Lang::get('program.packets')}}</strong> </p>
								<p><img src="{{URL::asset($theme.'/img/packet-fav-icon.png')}}" alt="Packet Favourite Icon"  class="img-ver-btm">&nbsp;&nbsp;<strong>{{$liked_packets}} {{Lang::get('program.packet')}}(s)</strong></p>
							</div>
						    @endif
						</div>
						<div class="col-lg-4 col-md-4 col-sm-6 col-xs-12">
						<!-- over all channel analytics  -->
							<?php
			                	$sepecificChannelAnalticAry = isset($channelAnalytics[0]) ?
			                									$channelAnalytics[0] : null;
			                	?>
			                	@if(!is_null($sepecificChannelAnalticAry) && config('app.channelAnalytic') == 'on')
							<?php
			                	if(isset($program['benchmarks'])){
			                		// $benchMarks = $program['benchmarks'];
			                		$benchMarkSpeed = isset($program['benchmarks']['speed']) ?
			                								$program['benchmarks']['speed'] : 0;
									$benchMarkScore = isset($program['benchmarks']['score']) ?
			                								$program['benchmarks']['score'] : 0;
									$benchMarkAccuracy = isset($program['benchmarks']['accuracy']) 					? $program['benchmarks']['accuracy'] : 0;
			                	}else{
			                		$benchMarkSpeed = 0;
			                		$benchMarkScore = 0;
			                		$benchMarkAccuracy = 0;
			                	}
			              	?>

			                <?php
			                    $sepecificChannelAnaltic = $sepecificChannelAnalticAry;
			                    $score = isset($sepecificChannelAnaltic['score']) ?
			                    					$sepecificChannelAnaltic['score'] : 0;
			                    $accuracy = isset($sepecificChannelAnaltic['accuracy']) ?
			                                        $sepecificChannelAnaltic['accuracy'] : 0;
			                    $speed = isset($sepecificChannelAnaltic['speed']) ?
			                    					$sepecificChannelAnaltic['speed'] : "0:0:0";
			                    $completion = isset($sepecificChannelAnaltic['completion']) ?
			                                        $sepecificChannelAnaltic['completion'] : 0;
								$analyticBenchMarkSpeed = isset($sepecificChannelAnaltic['speed_secs']) ?
			                                                    $sepecificChannelAnaltic['speed_secs'] : 1;

								$statusSpeed = ($analyticBenchMarkSpeed > $benchMarkSpeed) ? "LOW" :"HIGH";

								$ssSpeed = ($analyticBenchMarkSpeed % 60) > 10 ?
			                                    $analyticBenchMarkSpeed % 60 :
			                                    '0'.$analyticBenchMarkSpeed % 60;
			                    $mmSpeed = intval($analyticBenchMarkSpeed / 60);
			                    $mmSpeed = ($mmSpeed > 10) ?
			                                $mmSpeed  : '0'.$mmSpeed;
			                ?>
							<div class=" col-md-4 col-sm-4 col-xs-12 custom-analytics">
								<div class="analytic-div">
								@if(isset($general->setting['quiz_marics']['quiz_score']) && $general->setting['quiz_marics']['quiz_score'] == 'on')
					                <div>
					                <?php
					                if($benchMarkScore > 0)
										$scoreChannelBenchmark = Lang::get('assessment.score_channel_benchmark');
									else
										$scoreChannelBenchmark = Lang::get('assessment.score_channel');

					                ?>


					                  <div class="left">
					                    <img src="{{URL::asset($theme.'/img/icons/icons-05.png')}}" alt="score" title="{{$scoreChannelBenchmark}}" width="22">
					                  </div>
					                  <div class="right">
					                    <div class="progress score-bar">
					                      <div style="width: {{$score}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar"></div>
					                      <div style="position: absolute;" class="white">{{$score}}%</div>
					                      @if($benchMarkScore > 0)
					                      	<span class="bench-mark" style="left:{{$benchMarkScore}}%;"></span>
					                      @endif
					                    </div>
					                  </div>
					                </div><!-- score-bar -->
					            @endif
					            @if(isset($general->setting['quiz_marics']['quiz_accuracy']) && $general->setting['quiz_marics']['quiz_accuracy'] == 'on')
					                <div>
					                <?php
					                if($benchMarkAccuracy > 0)
										$accuracyChannel = Lang::get('assessment.accuracy_channel_benchmark');
									else
										$accuracyChannel = Lang::get('assessment.accuracy_channel');

					                ?>
					                  <div class="left">
					                    <img src="{{URL::asset($theme.'/img/icons/icons-07.png')}}" alt="accuracy" title="{{$accuracyChannel}}" width="22">
					                  </div>
					                  <div class="right">
					                    <div class="progress accuracy-bar">
					                      <div style="width: {{$accuracy}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar"> </div>
					                      <div style="position: absolute;" class="white">{{$accuracy}}%</div>
					                      @if($benchMarkAccuracy > 0)
					                      	<span class="bench-mark" style="left:{{$benchMarkAccuracy}}%;"></span
					                      	>
					                      @endif
					                    </div>
					                  </div>
					                </div><!--  accuracy-bar -->
					            @endif
					            @if(isset($general->setting['quiz_marics']['channel_completion']) && $general->setting['quiz_marics']['channel_completion'] == 'on')
					                <div>
					                  <div class="left">
					                    <img src="{{URL::asset($theme.'/img/icons/completion.png')}}" alt="Completion" title="{{Lang::get('assessment.completion_level')}}" width="22">
					                  </div>
					                  <div class="right">
					                    <div class="progress completion-bar">
					                      <div style="width: {{$completion}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar"> </div><div style="position: absolute;" class="white">{{$completion}}%</div>
					                    </div>
					                  </div>
					                </div><!-- completion-bar -->
					            @endif
					            @if(isset($general->setting['quiz_marics']['quiz_speed']) && $general->setting['quiz_marics']['quiz_speed'] == 'on')
					             <?php
					            	if($benchMarkSpeed > 0)
				                    	$speedTitle = Lang::get('assessment.speed_detail_benchmark');
									else
										$speedTitle = Lang::get('assessment.speed_detail');
					            ?>
					                <div>
					                  <div class="left">
					                    <img src="{{URL::asset($theme.'/img/icons/icons-03.png')}}" alt="time" title="{{$speedTitle}}" width="22">
					                  </div>
					                  <div class="right">
					                    <span>{{$mmSpeed}}:{{$ssSpeed}}</span><span>&nbsp;(MM:SS) </span>
					                    @if($benchMarkSpeed > 0)
					                   	&nbsp;|<span>&nbsp;{{$statusSpeed}}</span>
					                    @endif
					                  </div>
					                </div><!-- time-bar -->
					            @endif
					              </div>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div><!--contenfeed info panel-->
		</div>
	</div>
</div><!--main-row-->
<div class="row">
	<div class="col-lg-offset-1 col-lg-10 col-lg-ofset-1">
		<div class="panel panel-info sort-panel">
			@if($other_channel == 1)
			@if((isset($program['program_sub_type'])  && $program['program_sub_type']=='single') || !isset($program['program_sub_type']))
			<div class="panel-heading sequential-panel-header">
				<?php echo Lang::get('program.packet');?>s in This <?php echo Lang::get('program._course');?> ({{count($packets)}})
					<!-- <span class="pull-right panel-sort">
							<label>Sort By</label>
							<select>
								<option value="">Newest to Oldest</option>
								<option value="">Oldest to Newest</option>
								<option value="">A to Z</option>
								<option value="">Z to A</option>
							</select>
					</span> -->
			</div>
			@endif
			@endif
			<?php
				// $array_packets=MyActivity::getNewCompletedPackets($packets);
				$array_packets['new'] = array();
				$array_packets['completed'] = array();
				//Disable due to heavy mongo load
				/*if(empty($array_packets['new']))
				{
					$array_packets['new']=array();
				}
				if(empty($array_packets['completed']))
				{
					$array_packets['completed']=array();
				}*/
			?>
			<div class="panel-body">
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="xs-margin"></div>
						@if($other_channel == 1)
							@foreach($packets as $packet)
								<div class="facets-data1" title="{{$packet['packet_title']}}">
					    			<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
			            				<div class="packet">
			              					<figure>
			              					@if(empty($packet['packet_cover_media']))
			              						<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" class="img-responsive" alt="{{$packet['packet_title']}}">
			              							<div class="completed-overlay">
				              							<img src="{{URL::asset($theme.'/img/lock-icon.png')}}" class="img-responsive" alt="Lock">
				              						</div>
			              					@else
				              					<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" alt="{{$packet['packet_title']}}" class="packet-img img-responsive">
				              						<div class="completed-overlay">
				              							<img src="{{URL::asset($theme.'/img/lock-icon.png')}}" class="img-responsive" alt="lock">
				              						</div>
			              					@endif
			              					</figure>
			             					<div>
			                					<p class="packet-title"><strong>
													{{str_limit($packet['packet_title'], $limit = 40, $end = '...')}}
			             						</strong></p>
			                					<p class="packet-data">
			                  						<span class="gray">{{str_limit($program_title, $limit = 24, $end = '...')}}<br>
			                  							{{count($packet['elements'])}} @if(count($packet['elements']) == 1) {{str_singular('items')}}@else items @endif<br></span>
                                    				<span class="l-gray font-12">{{ Timezone::convertFromUTC($packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))}}</span> 
			                					</p>
			              					</div>
			            				</div>
			          				</div>
			          			</div>
							@endforeach
						@else
							<div class="custom-box">
	                        	<div class="portlet box">
									<div class="portlet-body">
										<div class="tabbable-line">
											<ul class="nav nav-pills ">
<!--ul class="nav nav-tabs "-->
												<?php
													if(Session::get('tab_enabled'))
													{
														$tab_enabled = Session::get('tab_enabled');
													}
													else
													{
														$tab_enabled = 'posts';
													}
												?>

						                        @if((isset($program['program_sub_type'])  && $program['program_sub_type']=='single') || !isset($program['program_sub_type']))
												<li class="<?php if($tab_enabled == 'posts'){ echo "active";} ?>">
						                            <a href="#tab_15_1" data-toggle="tab">{{Lang::get('program.packets')}}({{count($packets)}})</a>
						                        </li>
												@endif
												@if( config('app.display_portal_q&a'))
						                        <li class="<?php if($tab_enabled == 'qanda'){ echo "active";} ?>">
						                            <a href="#q-a" data-toggle="tab">{{Lang::get('program.channel_questions')}}({{$questions_count}})</a>
						                        </li>
						                        @endif
						                    </ul>
				                    		<div class="tab-content">
		                                        <div class="clearfix"></div>
		                                        <span class="success_text"></span>
	                                        	<span class="error_text"></span>

							                    <div class="tab-pane <?php if($tab_enabled && $tab_enabled == 'posts'){ echo " active in";}?> posts_tab facets-data" id="tab_15_1">
													@if((empty($packets) && (isset($program['program_sub_type']) && $program['program_sub_type']=='single')) || (!isset($program['program_sub_type']) && empty($packets)))
														<br><h4 align="center"> <?php echo Lang::get('program.no_posts'); ?> </h4>
													@else
														@foreach($packets as $packet)
															<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
																@if($channel_status != false)
																		<?php $class='pkt-opacity'; $fav_class='';?>
																		<a title="Cannot Access">
																@else
																	<?php $class=''; $fav_class='favourite';?>
																	<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}" title="{{$packet['packet_title']}}">
																@endif
														            <div class="packet {{$class}}" style="background: linear-gradient(#056ea28c,#d9edf78c);box-shadow: 4px 4px 3px;">
																		<figure>
																			@if(empty($packet['packet_cover_media']))
		<img src="{{URL::asset($theme.'/img/book.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
																			@else
																				<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
																			@endif
																			@if(in_array($packet['packet_id'], $array_packets['new']))
																				<img class="new-label" src="{{URL::asset($theme.'/img/new-label.png')}}" alt="New label">
																			@endif
																			@if(in_array($packet['packet_id'], $array_packets['completed']))
																				<span class="completed-overlay">
																              		<img src="{{URL::asset($theme.'/img/completed.png')}}" class="img-responsive"alt="Completed">
																              	</span>
																			@endif
																		</figure>
																		<div>
																			<p class="packet-title uppercase bold center">{{str_limit($packet['packet_title'], $limit = 48, $end = '...')}}</p>
																			<p class="packet-data">
																			  	<span class="gray">{{str_limit($program_title, $limit = 24, $end = '...')}}<br>
																			  		{{count($packet['elements'])}} @if(count($packet['elements']) == 1) {{str_singular('items')}}@else items @endif<br></span>
                                          										<span class="l-gray font-12">{{ Timezone::convertFromUTC($packet['packet_publish_date'], Auth::user()->timezone,Config('app.date_format'))}}</span> 
																			  	<span class="pull-right">
																			  	@if($other_channel != 1)
																			  		@if(in_array($packet['packet_id'], $favorites))
																						<?php
																							$action="unfavourite";
																							$class="red";
																						?>
																					@else
																						<?php
																							$action="favourite";
																							$class="gray";
																						?>
																					@endif

																			  		<span class="{{$fav_class}}">
																						<i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}} fav-packet cursor-pointer"></i>
																					</span>
																				@endif
																			  	</span>
																			</p>
																		</div>
														            </div><!--packet-->
														        </a>
													        </div><!--packet div-->
												        @endforeach
												    @endif
												</div>
												<div class="tab-pane <?php if($tab_enabled && $tab_enabled == 'qanda'){ echo " active in";}?> faq_tab" id="q-a">
													<div class="row">	<div class="col-md-2 col-sm-3 col-xs-2 pull-right">
															@if(count($questions) != 0)
																<div class="btn-group pull-right">
																	<a aria-expanded="false" data-toggle="dropdown"><img width="20px" src="{{URL::asset($theme.'/img/icons/filter-icon.png')}}" alt="Filter icon"></a>
																	<ul role="menu" class="dropdown-menu pull-right">
																		<li class="filter-title border-btm"><h4>&nbsp;&nbsp;{{ Lang::get('program.showing') }}</h4></li>
																		<li class="active"><a class="filter_faq" data-name="all" data-action="{{ URL::to('program/question-channel/'.$program['program_id'].'/'.$program['program_slug'].'?filter=all') }}">{{ Lang::get('program.all') }}</a></li>
																		<li><a class="filter_faq" data-name="my_questions" data-action="{{ URL::to('program/question-channel/'.$program['program_id'].'/'.$program['program_slug'].'?filter=my_questions') }}">{{ Lang::get('program.my_questions') }}</a></li>
																		<li><a class="filter_faq" data-name="other_questions" data-action="{{ URL::to('program/question-channel/'.$program['program_id'].'/'.$program['program_slug'].'?filter=other_questions') }}">{{ Lang::get('program.other_questions') }}</a></li>
																	</ul>
															  	</div>
															@endif
														</div><!-- filter -->
														<div class="col-md-10 col-sm-9 col-xs-10">
															<div class="form-group">
				                                                <ul class="media-list">
				                                                    <li class="media">
				                                                    <?php
																		$pic = (isset(Auth::user()->profile_pic) && !empty(Auth::user()->profile_pic)) ? URL::asset(config('app.user_profile_pic') . Auth::user()->profile_pic ) : URL::asset($theme.'/img/green.png');
																	?>
				                                                        <a class="pull-left" href="javascript:;">
				                                                            <img class="todo-userpic margin-top-10" src="{{ $pic }}" width="27px" height="27px" alt="User pic">
				                                                        </a>
				                                                        <div class="media-body">
				                                                            <form action="">
				                                                                <div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
				                                                                    <textarea class="form-control todo-taskbody-taskdesc" name="question" rows="2" placeholder="Type new question..." style="max-width:100%;"></textarea>
				                                                                    <span class="help-inline errorspan red"></span>
				                                                                </div>
				                                                                <div class="col-md-2 col-sm-3 col-xs-12">
				                                                                    <button  id="ques_submit" class="btn btn-primary btn-sm margin-top-10" data-action="{{URL::to('program/question-channel/'.$program['program_id'].'/'.$program['program_slug'])}}" ><i class="fa fa-send"></i> Send</button>
				                                                                </div>
				                                                            </form>
				                                                        </div>
				                                                    </li>
				                                                    <div>
				                                                        <input type="hidden" id="page_no" value="0">
				                                                    </div>
				                                                    <div>
				                                                    	<input type="hidden" id="filter" value="all">
				                                                    </div>
				                                                    <div class="myquestion_div">
				                                                        @include('portal.theme.default.programs.channelquestions_ajax_load', ['questions' => $questions, 'program_id' => $program['program_id']])
				                                                    </div>
				                                                </ul>
				                                            </div>
														</div><!--Q&A list-->
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
					    @endif
			        </div><!--facets data div-->
				</div>
			</div>
		</div><!--contenfeed - packets panel-->
	</div>
</div><!--main-row-->

<!-- delete window -->
<div class="modal fade deletemodal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="font-weight-500" style="border: 2px inset #fb6c7285;
    width: 97%;"><i class="fa fa-file-text font-18 green  margin-left-10 margin-bottom-5"></i> {{ Lang::get('assessment.delete_question') }} </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body padding-20 bold">
               {{ Lang::get('assessment.are_you_sure_you_want_to_delete_question') }}
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn  btn-danger btn-sm "><i class="fa fa-check"></i> {{ Lang::get('assessment.yes') }}</a>
              <a class="btn btn-default  btn-sm" data-dismiss="modal"><i class="fa fa-remove"></i> {{ Lang::get('assessment.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->

<!-- delete answer window -->
<div class="modal fade deleteanswer">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="font-weight-500" style="border: 2px inset #fb6c7285;
    width: 97%;"><i class="fa fa-file-text font-18 green  margin-left-10 margin-bottom-5"></i> {{ Lang::get('assessment.delete_ans') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body padding-20">
                {{ Lang::get('assessment.are_you_sure_you_want_to_delete_answer') }}
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-danger btn-sm"><i class="fa fa-check"></i> {{ Lang::get('assessment.yes') }}</a>
              <a class="btn btn-default btn-sm" data-dismiss="modal"><i class="fa fa-remove"></i> {{ Lang::get('assessment.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- delete answer window ends -->


<!-- hide question iframe -->
	<div class="modal fade hidemodal">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <!--header-->
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3 class="font-weight-500" style="border: 2px inset #fb6c7285;
    width: 97%;"><i class="fa fa-file-text green font-18 margin-left-10 margin-bottom-5"></i> {{ Lang::get('assessment.hide_question') }}</h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <!--content-->
	            <div class="modal-body padding-20">
	                {{ Lang::get('assessment.are_you_sure_you_want_to_hide_question') }}
	            </div>
	            <!--footer-->
	            <div class="modal-footer">
	              <a class="btn btn-danger btn-sm"></a><i class="fa fa-check"></i> {{ Lang::get('assessment.yes') }}</a>
	              <a class="btn btn-default btn-sm" data-dismiss="modal"><i class="fa fa-remove"></i> {{ Lang::get('assessment.close') }}</a>
	            </div>
	        </div>
	    </div>
	</div>
<!-- hide question iframe ends -->

<!-- unhide question iframe -->
	<div class="modal fade unhidemodal">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <!--header-->
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3><i class="icon-file"></i>{{ Lang::get('assessment.unhide_question') }}</h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <!--content-->
	            <div class="modal-body padding-20">
	                {{ Lang::get('assessment.are_you_sure_you_want_to_unhide_question') }}
	            </div>
	            <!--footer-->
	            <div class="modal-footer">
	              <a class="btn btn-danger btn-sm">{{ Lang::get('assessment.yes') }}</a>
	              <a class="btn btn-success btn-sm" data-dismiss="modal">{{ Lang::get('assessment.close') }}</a>
	            </div>
	        </div>
	    </div>
	</div>
<!-- unhide question iframe ends -->

<!-- hide question iframe -->
	<div class="modal fade hideanswer">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <!--header-->
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3 class="font-weight-500" style="border: 2px inset #fb6c7285;
    width: 97%;"><i class="fa fa-file-text green font-18 margin-left-10  margin-bottom-5"></i>{{ Lang::get('assessment.hide_answer') }}</h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <!--content-->
	            <div class="modal-body padding-20">
	                {{ Lang::get('assessment.are_you_sure_you_want_to_hide_answer') }}
	            </div>
	            <!--footer-->
	            <div class="modal-footer">
	              <a class="btn btn-danger btn-sm"><i class="fa fa-check"></i> {{ Lang::get('assessment.yes') }}</a>
	              <a class="btn btn-default btn-sm" data-dismiss="modal"><i class="fa fa-remove"></i> {{ Lang::get('assessment.close') }}</a>
	            </div>
	        </div>
	    </div>
	</div>
<!-- hide question iframe ends -->

<!-- unhide question iframe -->
	<div class="modal fade unhideanswer">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <!--header-->
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3><i class="icon-file"></i>{{ Lang::get('assessment.unhide_answer') }}</h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
	            <!--content-->
	            <div class="modal-body padding-20">
	                {{ Lang::get('assessment.are_you_sure_you_want_to_unhide_answer') }}
	            </div>
	            <!--footer-->
	            <div class="modal-footer">
	              <a class="btn btn-danger btn-sm">{{ Lang::get('assessment.yes') }}</a>
	              <a class="btn btn-success btn-sm" data-dismiss="modal">{{ Lang::get('assessment.close') }}</a>
	            </div>
	        </div>
	    </div>
	</div>
<!-- unhide question iframe ends -->


<script type="text/javascript">
	$('.favourite').on('click', '.fav-packet', function(e) {
		e.preventDefault();
		var action = $(this).data('action');
		var packet_id = $(this).attr('id');
		if(action == 'favourite') {
			$("#"+packet_id).removeClass("l-gray").addClass("red");
			$.ajax({
				type: 'GET',
                url: "{{ url('program/packet-favourited/favourite') }}/"+packet_id
            })
            .done(function(response) {
            	if(response.status == true) {
            		$("#"+response.packet_id).data('action', 'unfavourite');
            	} else {
            		$("#"+response.packet_id).removeClass("red").addClass("gray");
            	}
            })
            .fail(function(response) {
            	$("#"+packet_id).removeClass("red").addClass("gray");
                alert( "Error while updating the post. Please try again" );
            });
        }
        if(action == 'unfavourite') {
        	$("#"+packet_id).removeClass("red").addClass("gray");
			$.ajax({
				type: 'GET',
                url: "{{ url('program/packet-favourited/unfavourite') }}/"+packet_id
            })
            .done(function(response) {
            	if(response.status == true) {
            		$('#'+response.packet_id).data('action', 'favourite');
            	} else {
            		$('#'+response.packet_id).removeClass('gray').addClass('red');
            	}
            })
            .fail(function(response) {
            	$('#'+response.packet_id).removeClass("gray").addClass("red");
                alert( "Error while updating the post. Please try again" );
            });
        }
	});

	//Faq Question submit
	$(document).on('click','#ques_submit',function(e){
        e.preventDefault();
        var page_no = $('#page_no').val();
        var filter = $('#filter').val();
        var $this = $(this);
        var action=$this.data('action');
        var ques=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                ques:ques,
                page_no:page_no,
                filter:filter,
                ques_submit:'yes',
            }
        }).done(function(response) {
            if(response.status == true) {
                $('.myquestion_div').html(response.data);
                $this.parent().prev().find('textarea').val('');
                $this.parent().prev().find('span.errorspan').text('');
                $('.success_text').html('<div class="alert alert-success">'+response.message+'</div>');
            } else {
                $('.success_text').html('');
                $this.parent().prev().find('textarea').val(ques);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }
        }).fail(function(response) {
            alert( "Error while sending the question. Please try again" );
        });
        /*Code to hide success message after 5seconds*/
		$('.success_text').delay(5000).fadeOut();
    });

    //Faq Filter Submit

    $(document).on('click','.filter_faq',function(e){
        e.preventDefault();
        var $this = $(this);
        var page_no = $('#page_no').val();
        var action=$this.data('action');
        var filter=$this.data('name');
        $.ajax({
            type: 'GET',
            url: action+"&page_no="+page_no
        }).done(function(response) {
            if(response.status == true) {
                $('.myquestion_div').html(response.data);
                $this.parent().parent().find('li').removeClass("active");
                $this.parent('li').addClass('active');
                $("#filter").val(filter);
            } else {
                alert( "Something went wrong. Please try again" );
            }
        }).fail(function(response) {
            alert( "Error while filtering. Please try again" );
        });
    });

    $(document).on('click','.ans_submit',function(e){
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('value');
        var action=$this.data('action');
        var ans=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                ans:ans
            }
        }).done(function(response) {
            if(response.status == true) {
                $('#answers_div'+id).html(response.data);
                $this.parent().prev().find('textarea').val('');
                $this.parent().prev().find('span.errorspan').text('');
            } else {
                $this.parent().prev().find('textarea').val(ans);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }
        }).fail(function(response) {
            alert( "Error while commenting. Please try again" );
        });
    });

    $(document).on('click','.channelfaq_edit',function(e){
      e.preventDefault();
      var id = $(this).data('value');
      $("#edit_faq"+id).removeClass("faq_inactive").addClass("faq_active");
      $("#faq_sec"+id).removeClass("faq_active").addClass("faq_inactive");
    });

    $(document).on('click','.edit_cancel',function(e){
      e.preventDefault();
      var $this = $(this);
      var id = $this.data('value');
      var ques = $('#question_value'+id).val();
      $this.parent().prev().find('textarea').val(ques);
      $("#faq_sec"+id).removeClass("faq_inactive").addClass("faq_active");
      $("#edit_faq"+id).removeClass("faq_active").addClass("faq_inactive");
      $this.parent().prev().find('span.errorspan').text('');
    });

    $(document).on('click','.edit_submit',function(e){
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('value');
        var action=$this.data('action');
        var edit=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                edit:edit
            }
        }).done(function(response) {
            if(response.status == true) {
                $('#update_ques'+id).html(response.data);
                $("#question_value"+id).val(response.data);
                $("#faq_sec"+id).removeClass("faq_inactive").addClass("faq_active");
                $("#edit_faq"+id).removeClass("faq_active").addClass("faq_inactive");
                $this.parent().prev().find('textarea').val(response.data);
                $this.parent().prev().find('span.errorspan').text('');
            } else {
                $("#edit_faq"+id).removeClass("faq_inactive").addClass("faq_active");
                $("#faq_sec"+id).removeClass("faq_active").addClass("faq_inactive");
                $this.parent().prev().find('textarea').val(edit);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }
        }).fail(function(response) {
            alert( "Error while updating. Please try again" );
        });
    });

    $(document).on('click','.channelfaq_delete',function(e){
        e.preventDefault();
        var page_no = $('#page_no').val();
        var filter = $('#filter').val();
        var $this = $(this);
        var $deletemodal = $('.deletemodal');
        var action = $this.data('action');
        $deletemodal.modal('show');
        $deletemodal.find('.modal-footer .btn-danger').unbind('click').click(function(){
            $deletemodal.modal('hide');
            $.ajax({
                type: 'GET',
                url: action+"?page_no="+page_no+"&filter="+filter
            })
            .done(function(response) {
                if(response.status == true) {
                    $('.myquestion_div').html(response.data);
                } else {
                    alert("Error while deleting the question. Please try again");
                }
            })
            .fail(function(response) {
                alert( "Error while deleting the question. Please try again" );
            });
        });
    });

    $(document).on('click','.channelfaq_hide',function(e){
        e.preventDefault();
        var page_no = $('#page_no').val();
        var filter = $('#filter').val();
        var $this = $(this);

        var action = $this.data('action');
        var type = $this.data('type');
        if(type == 'hide')
        {
        	var $modal = $('.hidemodal');
        }
        else
        {
        	var $modal = $('.unhidemodal');
        }
        $modal.modal('show');
        $modal.find('.modal-footer .btn-danger').unbind('click').click(function(){
            $modal.modal('hide');
            $.ajax({
                type: 'GET',
                url: action+"?page_no="+page_no+"&filter="+filter+"&type="+type
            })
            .done(function(response) {
                if(response.status == true) {
                    $('.myquestion_div').html(response.data);
                } else {
                    alert("Error! Please try again");
                }
            })
            .fail(function(response) {
                alert( "Error! Please try again" );
            });
        });
    });

    $(document).on('click','.answer_delete',function(e){
        e.preventDefault();

        var $this = $(this);
        var $deletemodal = $('.deleteanswer');

        var id = $this.data('value');
        var action = $this.data('action');

        $deletemodal.modal('show');
        $deletemodal.find('.modal-footer .btn-danger').unbind('click').click(function(){
            $deletemodal.modal('hide');
            $.ajax({
                type: 'GET',
                url: action
            })
            .done(function(response) {
                if(response.status == true) {
	                $('#answers_div'+id).html(response.data);
	            } else {
	                alert( "Error while deleting the answer. Please try again" );
	            }
            })
            .fail(function(response) {
                alert( "Error while deleting the answer. Please try again" );
            });
        });
    });

    $(document).on('click','.answer_hide',function(e){
        e.preventDefault();

        var $this = $(this);
        var action = $this.data('action');
        var type = $this.data('type');
        if(type == 'hide')
        {
        	var $modal = $('.hideanswer');
        }
        else
        {
        	var $modal = $('.unhideanswer');
        }

        var id = $this.data('value');

        $modal.modal('show');
        $modal.find('.modal-footer .btn-danger').unbind('click').click(function(){
            $modal.modal('hide');
            $.ajax({
                type: 'GET',
                url: action+"?type="+type
            })
            .done(function(response) {
                if(response.status == true) {
	                $('#answers_div'+id).html(response.data);
	            } else {
	                alert( "Error! Please try again" );
	            }
            })
            .fail(function(response) {
                alert( "Error! Please try again" );
            });
        });
    });

    $(document).on('click','.like-channelfaq',function(e){
        e.preventDefault();
        var action = $(this).data('action');
        var qid = $(this).attr('id');
        if(action == 'like') {
            $("#"+qid).removeClass("gray").addClass("blue");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/channel-question-liked/like') }}/"+qid
            })
            .done(function(response) {
                if(response.status == true) {
                    $("#"+response.qid).data('action', 'unlike');
                    $("#like_count"+response.qid).html(response.like_count);
                } else {
                    $("#"+response.qid).removeClass("blue").addClass("gray");
                }
            })
            .fail(function(response) {
                $("#"+qid).removeClass("blue").addClass("gray");
                alert( "Error while processing. Please try again" );
            });
        }
        if(action == 'unlike') {
            $("#"+qid).removeClass("blue").addClass("gray");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/channel-question-liked/unlike') }}/"+qid
            })
            .done(function(response) {
                if(response.status == true) {
                    $('#'+response.qid).data('action', 'like');
                    $("#like_count"+response.qid).html(response.like_count);
                } else {
                    $('#'+response.qid).removeClass('gray').addClass('blue');
                }
            })
            .fail(function(response) {
                $('#'+response.qid).removeClass("gray").addClass("blue");
                alert( "Error while processing. Please try again" );
            });
        }
    });

    $(document).ready(function () {
		var page_no=1;
		var count='<?php echo isset($questions) ? count($questions) : 0; ?>';
		var records_per_page='<?php echo $records_per_page; ?>';
		var filter = $('#filter').val();
		var program_id = '<?php echo $program['program_id']; ?>';
		var stop = flag = true;
		$(window).scroll(function() {
			var period_val = $(".tab-pane.active").attr('id');
            if(period_val == 'q-a')
            {
				if(count >= records_per_page && stop) {
					if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
						if(flag) {
							flag = false;
							$.ajax({
								type: 'GET',
								url: "{{ url('program/channel-next-questions') }}/"+program_id,
								data :{
					                page_no:page_no,
					                filter:filter,
					            }
							}).done(function(e) {
								if(e.status == true) {
									$('.myquestion_div').append(e.data);
									count=e.count;
									$("#page_no").val(page_no);
									stop=true;
									flag = true;
									if(count < records_per_page)
									{
										$('#end_search').append("<div	class='col-md-12 center l-gray'><p><strong><?php echo Lang::get('pagination.no_more_records'); ?></strong></p></div>");
									}
								}
								else {
									$('.myquestion_div').append(e.data);
									stop = false;
								}
								page_no += 1;
							}).fail(function(e) {
								alert('Failed to get the data');
							});
						}
					}
				}
			}
		});
		/*Code to hide success message after 5seconds*/
		$('#success_text').delay(5000).fadeOut();
	});

</script>
@stop