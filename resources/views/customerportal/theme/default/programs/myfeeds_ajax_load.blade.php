<?php use App\Model\SiteSetting; ?>
<?php use App\Model\Packet; use App\Model\MyActivity; use App\Model\Category;?>
<?php $i=0; $now=time();?>
@foreach($programs as $program)
	<?php $i=$program['program_id'];?>
	@if((Timezone::getTimeStamp($program['program_startdate']) <= $now) && (Timezone::getTimeStamp($program['program_enddate']) >= $now))
		<!--start content feed 1-->
		<?php 
			$packets=Packet::getPacketsUsingSlug($program['program_slug']); 
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
		<div>
			<h3 class="page-title"><a href="{{URL::to('program/packets/'.$program['program_slug'])}}">{{$program['program_title']}}</a>&nbsp;
			@if(empty($packets))
				<?php $info=""; $packet_view="display:none;"; ?>
			@else
				<?php $info="display:none;"; $packet_view=""; ?>
				<span class="l-gray">|</span><small><a href="javascript:SwapDivsWithClick('cf1-packets<?php  echo $i; ?>','cf1-info<?php  echo $i; ?>', <?php  echo $i; ?>,this)" id="contentfeed{{$i}}" class="btn btn-default">More Info</a></small></h3>
			@endif
		</div>
		<div class="md-margin cf-info" id="cf1-info<?php  echo $i; ?>" style="{{$info}}">
			<div class="row">
				<div class="col-md-4 col-sm-4 col-xs-12 xs-margin">
					@if(isset($program['program_cover_media']) && !empty($program['program_cover_media']))
						<img src="{{URL::to('media_image/'.$program['program_cover_media'])}}" class="packet-img img-responsive center-align">
					@else
						<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Channel" class="packet-img img-responsive center-align">
					@endif
				</div>
				<?php 
					$categories=Category::getFeedRelatedCategory($program['program_id']);
					if(empty($categories))
							$categories=array();

					$category='';
					foreach($categories as $info)
					{
						$category.=$info['category_name'].',';
					}
				?>
				<div class="cl-lg-8 col-md-7 col-sm-8 col-xs-11">
					<p>{!! $program['program_description'] !!}</p>
					<table width="900px">
						<tr>
							<td width="140px"><strong>Category</strong></td>
							<td><?php echo trim($category,','); ?></td>
						</tr>
						<tr>
							<td width="140px"><strong>Start Date</strong></td>
							<td>{{ Timezone::convertFromUTC('@'.$program['program_startdate'], Auth::user()->timezone, Config('app.date_format')) }}</td>
						</tr>
						<tr>
							<td width="140px"><strong>End Date</strong></td>
							<td>{{ Timezone::convertFromUTC('@'.$program['program_enddate'], Auth::user()->timezone, Config('app.date_format')) }}</td>
						</tr>
						<tr>
							<td width="140px"><strong>No. of {{Lang::get('program.packets')}}</strong></td>
							<td>{{Packet::getPacketsCountUsingSlug($program['program_slug'])}}</td>
						</tr>
						<tr>
							<td width="140px"><strong>Status</strong></td>
							<td>{{$program['status']}}</td>
						</tr>
					</table>
					<p><a href="{{URL::to('program/packets/'.$program['program_slug'])}}">More >></a></p>
				</div>
			</div>
		</div><!-- END CF Info-->
		<div class="md-margin" id="cf1-packets<?php  echo $i; ?>" style="{{$packet_view}}">
			<div class="col-md-12 nav-space md-margin border-btm">
				<div class="owl-carousel owl-theme sm-margin">
				@foreach($packets as $packet)
					<div class="item">
						<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}">
				            <div class="packet">
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
									<p class="packet-title">{{str_limit(ucwords($packet['packet_title']), $limit = 40, $end = '...')}}</p>
									<p class="packet-data">
									  	<span class="gray">{{count($packet['elements'])}} @if(count($packet['elements']) <= 1) {{str_singular('items')}}@else items @endif<br></span>
									  	<span class="l-gray font-12">{{ date('d M Y', strtotime(Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))))}}</span>
									  	<span class="pull-right">
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
									  		<span class="favourite">
												<i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}} fav-packet" style="cursor:pointer"></i>
											</span>
									  	</span>
									</p>
								</div>
				            </div><!--packet-->
				        </a>
		          	</div><!--packet div-->
		        @endforeach
		        </div>
		    </div>
		</div><!--ENd Packets div-->
		<!--end content feed 1-->
	@else
		<div>
			<h3 class="page-title-small">{{$program['program_title']}}</h3>
		</div>
		<div class="md-margin cf-info pkt-opacity">
			<div class="row">
				<div class="col-md-6 col-sm-6 col-xs-12">
					@if(isset($program['program_cover_media']) && !empty($program['program_cover_media']))
						<img src="{{URL::to('media_image/'.$program['program_cover_media'])}}" class="img-responsive">
					@else
						<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Doctor" class="img-responsive">
					@endif
				</div>
				<?php 
					$categories=Category::getFeedRelatedCategory($program['program_id']);
					if(empty($categories))
							$categories=array();

					$category='';
					foreach($categories as $info)
					{
						$category.=$info['category_name'].',';
					}
				?>
				<div class="col-md-6 col-sm-6 col-xs-12">
					
					<table width="900px">
						<tr>
							@if(Timezone::getTimeStamp($program['program_startdate']) > $now) 
							<label class="disable-lable"><?php echo Lang::get('program.coming_soon');?></label>
							@endif
							@if(Timezone::getTimeStamp($program['program_enddate']) < $now)
							<label class="disable-lable"><?php echo Lang::get('program.expired');?></label>
							@endif
						</tr>
						<tr>
							<td>
								<p>{!! $program['program_description'] !!}</p>
							</td>
						</tr>
						<tr>
							<td width="140px"><strong>Category</strong></td>
							<td><?php echo trim($category,','); ?></td>
						</tr>
						<tr>
							<td width="140px"><strong>Start Date</strong></td>
							<td>{{ Timezone::convertFromUTC('@'.$program['program_startdate'], Auth::user()->timezone, Config('app.date_format')) }}</td>
						</tr>
						<tr>
							<td width="140px"><strong>End Date</strong></td>
							<td>{{ Timezone::convertFromUTC('@'.$program['program_enddate'], Auth::user()->timezone, Config('app.date_format')) }}</td>
						</tr>
						<tr>
							<td width="140px"><strong>No. of {{Lang::get('program.packets')}}</strong></td>
							<td>{{Packet::getPacketsCountUsingSlug($program['program_slug'])}}</td>
						</tr>
						<tr>
							<td width="140px"><strong>Status</strong></td>
							<td>{{$program['status']}}</td>
						</tr>
						
					</table>
				</div>
			</div>
		</div><!-- END CF Info-->
	@endif
@endforeach