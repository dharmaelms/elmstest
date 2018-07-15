@section('content')
<?php use App\Model\MyActivity; ?>

<!-- BEGIN PAGE HEADER-->
@if($other_channel != 1)
    <div class="page-bar">
		<ul class="page-breadcrumb">
			<li><a href="{{url('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
			<li><a href="{{url('program/my-feeds')}}">My <?php echo Lang::get('program.programs');?></a><i class="fa fa-angle-right"></i></li>
			<li><a href="#">{{str_limit(ucwords($program['program_title']), $limit = 50, $end = '...')}}</a></li>
		</ul>
	</div>
@else
    <div class="page-bar">
		<ul class="page-breadcrumb">
			<li><a href="{{url('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
			<li><a href="{{url('program/more-feeds')}}">Other <?php echo Lang::get('program.programs');?></a><i class="fa fa-angle-right"></i></li>
			<li><a href="#">{{str_limit(ucwords($program['program_title']), $limit = 50, $end = '...')}}</a></li>
		</ul>
	</div>
@endif
<!-- END PAGE HEADER-->

<div class="row">
	<div class="col-lg-offset-1 col-lg-10 col-lg-ofset-1">
	<div class="sm-margin"></div><!--space-->
		<div class="panel-group accordion" id="accordion3">
			<div class="panel panel-default transparent-bg">
				<div class="panel-heading">
					@if(empty($packets) || ($channel_status != false))
						<?php $class=''; $aria='true'; $in='in';?>
					@else
						<?php $class='collapsed'; $aria='false'; $in='';?>
					@endif
					<h4 class="panel-title m-btm-12">
						<div class="row">
							<div class="col-md-9 col-sm-9">
								<span class="caption gray">{{$program['program_title']}} </span>
							</div>
							<div class="col-md-3 col-sm-3">
							<a class="accordion-toggle accordion-toggle-styled {{$class}}" data-toggle="collapse" data-parent="#accordion3" href="#collapse_1"><!-- <span class="badge badge-roundless badge-success">NEW</span> --></a>
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
						<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 xs-margin">
							@if(isset($program['program_cover_media']) && !empty($program['program_cover_media']))
								<img src="{{URL::to('media_image/'.$program['program_cover_media'])}}" class="img-responsive">
							@else
								<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Doctor" class="img-responsive">
							@endif
						</div>
						<?php $category=''; ?>
						@foreach($categories as $info)
							<?php $category.=$info['category_name'].',';?>
						@endforeach
						<div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
							<h6><strong>Description</strong></h6>
							<p>{!! $program['program_description'] !!}</p>

							<div class="font-12 xs-margin">
								<span class="start">STARTS:</span>&nbsp; <strong>{{ Timezone::convertFromUTC('@'.$program['program_startdate'], Auth::user()->timezone, Config('app.date_format')) }}</strong> <br>
								<span class="end">ENDS:</span>&nbsp; <strong>{{ Timezone::convertFromUTC('@'.$program['program_enddate'], Auth::user()->timezone, Config('app.date_format')) }}</strong>
							</div>

							
							<div class="xs-margin">
								<p><img src="{{URL::asset($theme.'/img/packet-icon.png')}}" alt="Packet Icon" class="img-ver-btm">&nbsp;&nbsp;<strong>{{$packets_count}} Posts</strong> </p>
								<p><img src="{{URL::asset($theme.'/img/packet-fav-icon.png')}}" alt="Packet Favourite Icon"  class="img-ver-btm">&nbsp;&nbsp;<strong>{{$liked_packets}} Liked Post(s)</strong></p>
							</div>
						</div>
					</div>
				</div>
			</div><!--contenfeed info panel-->
		</div>
	</div>
</div><!--main-row-->
<div class="row">
	<div class="col-lg-offset-1 col-lg-10 col-lg-ofset-1">
		<div class="panel panel-default sort-panel">
			<div class="panel-heading sequential-panel-header">
				<?php echo Lang::get('program.packet');?>s in This <?php echo Lang::get('program.program');?> ({{count($packets)}})
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
			<?php 
				$array_packets=MyActivity::getNewCompletedPackets($packets);
				if(empty($array_packets['new']))
				{
					$array_packets['new']=array();	
				}
				if(empty($array_packets['completed']))
				{
					$array_packets['completed']=array();
				}
			?>
			<div class="panel-body">
				<div class="row">
					<div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12">
						<div class="xs-margin"></div>

<!-- code added now -->		<div class="panel-body">
								<div class="row">
									@if($other_channel == 1)
									@foreach($packets as $packet)
									<div class="facets-data1">
						    			<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
				            				<div class="packet">
				              					<figure>
				              					@if(empty($packet['packet_cover_media']))
				              						<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" class="img-responsive" alt="{{$packet['packet_title']}}">
				              							<div class="completed-overlay">
					              							<img src="{{URL::asset($theme.'/img/lock-icon.png')}}" class="img-responsive">
					              						</div> 
				              					@else
					              					<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" alt="{{$packet['packet_title']}}" class="packet-img img-responsive">
					              						<div class="completed-overlay">
					              							<img src="{{URL::asset($theme.'/img/lock-icon.png')}}" class="img-responsive">
					              						</div> 
				              					@endif
				              					</figure>
				             					<div>
				                					<p class="packet-title"><strong>
														{{str_limit(ucwords($packet['packet_title']), $limit = 40, $end = '...')}}
				             						</strong></p>
				                					<p class="packet-data">
				                  						<span class="gray">{{str_limit(ucwords($program['program_title']), $limit = 24, $end = '...')}}<br>
				                  							{{count($packet['elements'])}} @if(count($packet['elements']) == 1) {{str_singular('items')}}@else items @endif<br></span>
				                  						<span class="l-gray font-12">{{ date('d M Y', strtotime(Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))))}}</span>
				                					</p>
				              					</div>
				            				</div><!--packet
				          </div><!--packet div-->
				          				</div>
				          			</div>
									@endforeach
								</div>
				        	</div>
						@else
						@if(empty($packets))
							<h4 align="center"> <?php echo Lang::get('program.no_posts'); ?> </h4>
						@else
							@foreach($packets as $packet)
								<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
									@if($channel_status != false)
											<?php $class='pkt-opacity'; $fav_class='';?>
											<a title="Cannot Access">
									@else
										<?php $class=''; $fav_class='favourite';?>
										<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}">
									@endif
							            <div class="packet {{$class}}">
											<figure>
												@if(empty($packet['packet_cover_media']))
													<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
												@else
													<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
												@endif
												@if(in_array($packet['packet_id'], $array_packets['new']))
													<img class="new-label" src="{{URL::asset($theme.'/img/new-label.png')}}">
												@endif
												@if(in_array($packet['packet_id'], $array_packets['completed']))
													<span class="completed-overlay">
									              		<img src="{{URL::asset($theme.'/img/completed.png')}}" class="img-responsive">
									              	</span>
												@endif
											</figure>
											<div>
												<p class="packet-title">{{str_limit(ucwords($packet['packet_title']), $limit = 48, $end = '...')}}</p>
												<p class="packet-data">
												  	<span class="gray">{{str_limit(ucwords($program['program_title']), $limit = 24, $end = '...')}}<br>
												  		{{count($packet['elements'])}} @if(count($packet['elements']) == 1) {{str_singular('items')}}@else items @endif<br></span>
												  	<span class="l-gray font-12">{{ date('d M Y', strtotime(Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))))}}</span>
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
															<i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}} fav-packet" style="cursor:pointer"></i>
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
					    @endif
			        </div><!--facets data div-->
				</div>
			</div>
		</div><!--contenfeed - packets panel-->
	</div>
</div><!--main-row-->

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
</script>
@stop