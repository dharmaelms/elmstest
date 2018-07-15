<?php
use App\Model\SiteSetting;
use App\Model\Packet;
use App\Model\MyActivity;
use App\Model\Category;
use App\Model\Program;
use App\Model\TransactionDetail;
$programs = Program::where('parent_relations.active_parent_rel','=',(int) $pack_id)->get()->toArray();
?>
@if(!empty($programs))
@foreach($programs as $program)
        <?php
            $i=$program['program_id'];
            $sort_by = SiteSetting::module('General', 'sort_by');
			$packets=Packet::getPacketsUsingSlug($program['program_slug'], $sort_by);
			$array_packets['new'] = array();
			$array_packets['completed'] = array();
			//Disable due to heavy mongo load
			/*$array_packets=MyActivity::getNewCompletedPackets($packets);
			if(empty($array_packets['new']))
			{
				$array_packets['new']=array();
			}
			if(empty($array_packets['completed']))
			{
				$array_packets['completed']=array();
			}*/
		?>
        <div>
			<h3 class="page-title"><a href="{{URL::to('program/packets/'.$program['program_slug'])}}">{{$program['program_title']}}</a>&nbsp;
			@if(empty($packets))
				<?php $info=""; $packet_view="display:none;"; ?>
			@else
				<?php $info="display:none;"; $packet_view=""; ?>
				<span class="l-gray">|</span><small><a href="javascript:SwapDivsWithClick('cf1-packets<?php  echo $i; ?>package<?php echo $pack_name; ?>', 'cf1-info<?php  echo $i; ?>package<?php echo $pack_name; ?>', '<?php  echo $i; ?>package<?php echo $pack_name; ?>', this)" id="contentfeed{{$i}}package<?php echo $pack_name; ?>">More Info</a></small></h3>
			@endif
		</div>

        <div class="md-margin cf-info white-bg" id="cf1-info<?php  echo $i; ?>package<?php echo $pack_name; ?>" style="{{$info}}">
			<div class="row">
				<div class="col-md-4 col-sm-6 col-xs-12 xs-margin">
					@if(isset($program['program_cover_media']) && !empty($program['program_cover_media']))
						<img src="{{URL::to('media_image/'.$program['program_cover_media'])}}" class="packet-img img-responsive center-align" alt="program">
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
						$category.= html_entity_decode($info['category_name']).',';
					}
				?>
				<div class="col-md-5 col-sm-5 col-xs-12">
					<p style="word-wrap:break-word;">{!! $program['program_description'] !!}</p>
					<table>
						<!--<tr>
							<td width="140px" valign="top"><strong>{{ Lang::get('category.category') }}</strong></td>
							@if(empty($category))
							<td>NA</td>
							@else
							<td style="word-break: break-all;"><?php echo trim($category,','); ?></td>
							@endif
						</tr>-->
						<!--<tr>
							<td width="140px" valign="top"><strong>{{ Lang::get('program.start_date') }}</strong></td>
							<td>{{ Timezone::convertFromUTC('@'.$s_date, Auth::user()->timezone, Config('app.date_format')) }}</td>
						</tr>
						<tr>
							<td width="140px" valign="top"><strong>{{ Lang::get('program.end_date') }}</strong></td>
							<td>{{ Timezone::convertFromUTC('@'.$e_date, Auth::user()->timezone, Config('app.date_format')) }}</td>
						</tr>-->
						@if(isset($program['program_sub_type'])  && $program['program_sub_type']=='single')
						<tr>
							<td width="140px" valign="top"><strong>No. of {{Lang::get('program.packets')}}</strong></td>
							<td>{{Packet::getPacketsCountUsingSlug($program['program_slug'])}}</td>
						</tr>
						@endif
						<tr>
							<td width="140px" valign="top"><strong>{{ Lang::get('program.status') }}</strong></td>
							<td>{{$program['status']}}</td>
						</tr>
						@if(isset($program['child_relations']['active_channel_rel']) && !empty($program['child_relations']['active_channel_rel']))
                        <?php
                        $child_program_list='';
					    foreach($program['child_relations']['active_channel_rel'] as $child_id)
					      {
                             $child_program = Program::getProgramDetailsByID($child_id);
						     $child_program_list.= '<a href="'.URL::to('program/packets/'.$child_program['program_slug']).'">'.$child_program['program_title'].'<a>, ';
					      }
                        ?>
                        <!--<tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('program.child_channels')}}</strong></td>

                          <td style="word-break: break-all;"><?php echo trim($child_program_list,', '); ?></td>
                        </tr>-->
                        @endif
                        @if(isset($program['parent_relations']['active_parent_rel']) && !empty($program['parent_relations']['active_parent_rel']))
                        <?php
                        $parent_program_list='';
					    foreach($program['parent_relations']['active_parent_rel'] as $parent_id)
					     {
						     $parent_program = Program::getProgramDetailsByID($parent_id);
							 $count = TransactionDetail::getUserActiveParent(Auth::user()->uid,$parent_id);
							 $count1 = TransactionDetail::getUserGroupActiveParent(Auth::user()->uid,$parent_id);
                             if($count > 0 || $count1 > 0) {
							 $parent_program_list.='<a href="'.URL::to('program/packets/'.$parent_program['program_slug']).'">'.$parent_program['program_title'].'<a>, ';
							 }
					     }

                        ?>
                        <!--<tr>
                          <td width="140px" valign="top"><strong>{{Lang::get('program.parent_channels')}}</strong></td>
                          <td style="word-break: break-all;"><?php echo trim($parent_program_list,', '); ?></td>
                        </tr>-->
                        @endif
					</table>
					<p><a href="{{URL::to('program/packets/'.$program['program_slug'])}}">More >></a></p>
				</div>
				<!-- over all channel analytics  -->
				<?php
                	$sepecificChannelAnalticAry = $channelAnalytics->get((int)$program['program_id']);
                	?>
                	@if(!is_null($sepecificChannelAnalticAry) && config('app.channelAnalytic') == 'on')
				<?php
                	if(isset($program['benchmarks'])){
                		// $benchMarks = $program['benchmarks'];
                		$benchMarkSpeed = isset($program['benchmarks']['speed']) ?
                								$program['benchmarks']['speed'] : 1;
						$benchMarkScore = isset($program['benchmarks']['score']) ?
                								$program['benchmarks']['score'] : 1;
						$benchMarkAccuracy = isset($program['benchmarks']['accuracy']) ?
												$program['benchmarks']['accuracy'] : 1;
                	}else{
                		$benchMarkSpeed = 0;
                		$benchMarkScore = 0;
                		$benchMarkAccuracy = 0;
                	}
              	?>

                <?php
                    $sepecificChannelAnaltic = $sepecificChannelAnalticAry[0];
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
		                <div>
		                  <div class="left">
		                    <img src="{{URL::asset($theme.'/img/icons/icons-05.png')}}" alt="score" title="Score" width="22">
		                  </div>
		                  <div class="right">
		                    <div class="progress score-bar">
		                      <div style="width: {{$score}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">{{$score}}% </div>
		                      @if($benchMarkScore > 0)
		                      	<span class="bench-mark" style="left:{{$benchMarkScore}}%;"></span>
		                      @endif
		                    </div>
		                  </div>
		                </div><!-- score-bar -->

		                <div>
		                  <div class="left">
		                    <img src="{{URL::asset($theme.'/img/icons/icons-07.png')}}" alt="accuracy" title="Accuracy" width="22">
		                  </div>
		                  <div class="right">
		                    <div class="progress accuracy-bar">
		                      <div style="width: {{$accuracy}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">{{$accuracy}}% </div>
		                      @if($benchMarkAccuracy > 0)
		                      	<span class="bench-mark" style="left:{{$benchMarkAccuracy}}%;"></span
		                      	>
		                      @endif
		                    </div>
		                  </div>
		                </div><!--  accuracy-bar -->
		                <div>
		                  <div class="left">
		                    <img src="{{URL::asset($theme.'/img/icons/correct-icon.jpg')}}" alt="Completion" title="Completion" width="22">
		                  </div>
		                  <div class="right">
		                    <div class="progress completion-bar">
		                      <div style="width: {{$completion}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">{{$completion}} % </div>
		                    </div>
		                  </div>
		                </div><!-- completion-bar -->
		                <div>
		                  <div class="left">
		                    <img src="{{URL::asset($theme.'/img/icons/icons-03.png')}}" alt="time" title="Time" width="22">
		                  </div>
		                  <div class="right">
		                    <!-- <div class="progress time-bar">
		                      <div style="width: {{$speed}}" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">{{$speed}} </div>
		                    </div> -->
		                    <span>{{$mmSpeed}}:{{$ssSpeed}}</span><span>&nbsp;(MM:SS) </span>
		                    @if($benchMarkSpeed > 0)
		                   	&nbsp;|<span>&nbsp;{{$statusSpeed}}</span>
		                    @endif
		                  </div>
		                </div><!-- time-bar -->
		              </div>
				</div>
				@endif
			</div>
		</div><!-- END CF Info-->



		<div class="row cf-packets" id="cf1-packets<?php  echo $i; ?>package<?php echo $pack_name; ?>" style="{{$packet_view}}">
			<div class="col-md-12 nav-space md-margin border-btm">
				<div class="owl-carousel owl-theme sm-margin">
				@foreach($packets as $packet)
					<div class="item white-bg" title="{{$packet['packet_title']}}">
						<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}">
				            <div class="packet">
								<figure>
									@if(empty($packet['packet_cover_media']))
										<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
									@else
										<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
									@endif
									@if(in_array($packet['packet_id'], $array_packets['new']))
										<img class="new-label" src="{{URL::asset($theme.'/img/new-label.png')}}" alt="New label">
									@endif
									@if(in_array($packet['packet_id'], $array_packets['completed']))
										<span class="completed-overlay">
						              		<img src="{{URL::asset($theme.'/img/completed.png')}}" class="img-responsive" alt="Completed">
						              	</span>
									@endif

								</figure>
								<div>
									<p class="packet-title">{{str_limit(($packet['packet_title']), $limit = 40, $end = '...')}}</p>
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
												<i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="cursor-pointer fa fa-heart {{$class}} fav-packet"></i>
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
      @endforeach
  @endif