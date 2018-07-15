@section('content')
    <?php
    use App\Model\Common;
    $pl = App::make("App\Services\Playlyfe\IPlaylyfeService");
    ?>
	<link rel="stylesheet" href="{{ URL::asset("portal/theme/".config("app.portal_theme_name")."/css/postlogin.css") }}">
	<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />
	<link rel="stylesheet" type="text/css" href="playlyfe/app.css" />
	<div class="row dashboard">
		<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 custom-border-right">
            <?php
            $announcementFlag = ((isset($announcements) && !empty($announcements)) || (isset($buff_announcement) && !empty($buff_announcement)));
            ?>
			<div class="row">
				<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
					<span  class="title">Announcements</span> &nbsp;
				</div>
				@if($announcementFlag)
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
						<span class="l-gray">|</span>
						<span class="sub-title"><a href="{{ URL::to('announcements') }}">{{ Lang::get('announcement.view_all') }}</a></span>
					</div>
				@endif
			</div>
			<div class="title-border"></div>
			@if($announcementFlag)
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <?php
                        $count_anno = 0;
                        $count1 = 0;
                        $maintain_uniqe =array();
                        foreach ($announcements as $key => $value)
                        {
                        $maintain_uniqe[] = $value['announcement_id'];
                        $count_anno++;
                        $count1++;
                        ?>
						<div class="row announcement-container">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="row">
									<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 announcement-img">
										<img src="{{ URL::to('portal/theme/default/img/announce/announcementDefault.png') }}" alt="Announcement">
									</div>
									<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9 announcement-title">
										<strong>
											@if(strlen($value["announcement_title"]) > 65)
												{{ substr($value["announcement_title"], 0, (strrpos(substr($value["announcement_title"], 0, 65), " "))) }}....
											@else
												{{ $value["announcement_title"] }}
											@endif
											<span class="badge badge-roundless badge-success pull-right" data-module="announcement" data-id = "{{ $value['announcement_id'] }}" data-url = "{{URL::to('/announcements/announcemnt-mark-read/'.$value['announcement_id'])}}">NEW</span>
										</strong>
										<br><span class="d-gray font-10">{{Common::getPublishOnDisplay((int)$value['schedule'])}}</span>
									</div>
								</div>
							</div>
						</div>
						@if(($count_anno < 5) && ($count1 < (count($announcements) + count($buff_announcement))))
							<div class="announcement-border"></div>
						@endif
                        <?php
                        }
                        if($count_anno < 5)
                        {
                        $count2 = 0;
                        foreach ($buff_announcement as $key1 => $value1)
                        {
                        $count2++;
                        if(in_array($value1['announcement_id'], $maintain_uniqe)){
                            continue;
                        }
                        if($count_anno > 5)
                            break;
                        $count_anno++;
                        ?>
						<div class="row announcement-container">
							<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
								<div class="row">
									<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3 announcement-img">
										<img src="{{ URL::to('portal/theme/default/img/announce/announcementDefault.png') }}" alt="Announcement">
									</div>
									<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9 announcement-title">
										<strong>
											@if(strlen($value1["announcement_title"]) > 65)
												{{ substr($value1["announcement_title"], 0, (strrpos(substr($value1["announcement_title"], 0, 65), " "))) }}....
											@else
												{{ $value1["announcement_title"] }}
											@endif
										</strong>
										<br><span class="d-gray font-10">{{Common::getPublishOnDisplay((int)$value1['schedule'])}}</span>
									</div>
								</div>
							</div>
						</div>
						@if(($count_anno < 5) && ($count2 < count($buff_announcement)))
							<div class="announcement-border"></div>
						@endif
                        <?php
                        }
                        }
                        ?>
					</div>
				</div>
			@else
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<span class="font-13"> {{ Lang::get('announcement.no_announce_published') }}</span>
					</div>
				</div>
			@endif
		</div>
		<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 custom-border-right">
			@if($lhs_menu_settings->setting['my_activity'] == "on")
				<div class="row">
					<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
						<span class="title">{{ Lang::get('announcement.my_recent_activity') }}&nbsp;</span>
					</div>
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
						<span class="l-gray">|</span>
						<span class="sub-title"><a href="{{ URL::to('user') }}">{{ Lang::get('announcement.view_all') }}</a></span>
					</div>
				</div>
				<div class="title-border"></div>
				<div class="row activity-container first margin-0">
					<div class="col-md-12">
						<div class="row">
							<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
								<span class="activity-title">{{ Lang::get('assessment.overall_quiz_performance') }}</span>
							</div>
						</div>
						<div class="row">
							@if($myRecentActivity["quiz_perfomance"]["data_flag"])
								<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="padding: 10px ! important; margin: 5px ! important; background-color: rgb(77, 139, 192); border-radius: 5px ! important; width: {{ $myRecentActivity["quiz_perfomance"]["percentage"] }}%;; max-width: 80%;">
								</div>
								<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 mactivity-quizperformance">
									{{ $myRecentActivity["quiz_perfomance"]["percentage"] }}%
								</div>
							@else
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<span class="font-13">{{ Lang::get('assessment.you_have_not_taken_any_quiz_in_recent_times') }}</span>
								</div>
							@endif
						</div>
					</div>
				</div>
				<div class="row activity-container subsequent ">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div class="row">
							<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
								<span class="activity-title">Overall {{ Lang::get('assessment.channel') }} completion</span>
							</div>
						</div>
						<div class="row">
							@if($myRecentActivity["channel_completion"]["data_flag"])
								<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10" style="padding: 10px ! important; margin: 5px ! important; background-color: rgb(77, 139, 192); border-radius: 5px ! important; width: {{ $myRecentActivity["channel_completion"]["percentage"] }}%;; max-width: 80%;">
								</div>
								<div class="col-xs-2 col-sm-2 col-md-2 col-lg-2 mactivity-quizperformance">
									{{ $myRecentActivity["channel_completion"]["percentage"] }}%
								</div>
							@else
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<span class="font-13">You are not subscribed to any {{ Lang::get('assessment.channel') }} in recent times.</span>
								</div>
							@endif
						</div>
					@if($pl->isPlaylyfeEnabled())
                        <?php
                        try
                        {
                        $playerProfile = $pl->getPlayerProfile(Auth::user()->uid);
                        $pl_leaderboard = $pl->getUserRank(Auth::user()->uid);
                        $activities = $pl->getActivity(Auth::user()->uid);
                        $pl_score = $pl->getUserLevel(Auth::user()->uid);
                        $badges = array_slice($playerProfile["profile_info"]["playlyfe_player_profile"]["badges"], 0, 7);
                        ?>
						<!-- <div class="row">
							<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
								<span class="activity-title">My ScoreCard</span>
							</div>
						</div> -->
							<div class="custom-border1"></div>
							<div class="row">
								<div>
									<div class="col-md-4">
										<div class="dashboard-header-item-title">CURRENT LEVEL</div>
										<div class="dashboard-header-item-value">
											<img src="/pl/image?size=small&metric_id={{$pl_score["metric"]["id"]}}&state={{$pl_score["value"]["name"]}}" alt="Player Profile">
										</div>
										<div class="dashboard-header-item-value">{{$pl_score["value"]["name"]}}</div>
										<div class="dashboard-header-item-score">({{$playerProfile['profile_info']['playlyfe_player_profile']['points']}} XP)</div>
									</div>

									<div class="col-md-4">
										<div class="dashboard-header-item-title">NEXT LEVEL</div>
										<div class="dashboard-header-item-value">
											<img src="/pl/image?size=small&metric_id={{$pl_score["metric"]["id"]}}&state={{$pl_score["value"]["name"]}}" alt="Score">
										</div>
										<div class="dashboard-header-item-value">{{$pl_score["meta"]["next"]}}</div>
										<div class="dashboard-header-item-score">({{$pl_score["meta"]["high"]}} XP)</div>
									</div>
									<div class="col-md-4">
										<div class="dashboard-header-item-title">LEVEL COMPLETION</div>
										<div class="dashboard-center">
											<div class="dashboard-center-progress">
												<div class="progress">
													<div class="progress-bar" role="progressbar" style="width: {{$pl_score['percent']}}%;">
														<span class="show">{{$pl_score['percent']}}%</span>
													</div>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
							<div class="row margin-top-20">
								<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
									<span class="activity-title">Your Recent Achievements</span>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									@if(count($badges) == 0)
										<span class="font-13">You dont have any badges.</span>
									@else
										<div class="profile-badges-list">
											@foreach($badges as $badge)
												<div class="profile-sub-title-1">
													<div class="profile-badges-list-item badge-tooltip" title="{{$badge["name"]}}&nbsp;&nbsp;{{$badge["description"]}}">
														<img class="{{ ($badge["count"] === 0) ?  "grayscale" : "" }}" src="/pl/image?size=small&metric_id={{$badge["type_info"]["id"]}}&item={{$badge["name"]}}" alt="badges"></img>
													</div>
												</div>
											@endforeach
										</div>
									@endif
								</div>
							</div>
                            <?php
                            }
                            catch(Exception $e) {
                            }
                            ?>
						@endif
					</div>
				</div>
				@if($myRecentActivity["areas_of_improvement"]["channel_count"] > 0)
					<div class="row activity-container subsequent">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<div class="row">
								<div class="col-xs-10 col-sm-10 col-md-10 col-lg-10">
									<span class="activity-title">Areas of improvement</span>
								</div>
							</div>
							<div class="row">
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									<span>Quiz performance can improve in <b>{{ $myRecentActivity["areas_of_improvement"]["channel_count"] }}</b> {{ ($myRecentActivity["areas_of_improvement"]["channel_count"] > 1) ? "course" : "course" }}</span>
								</div>
							</div>
							<div class="row channel-quiz-performance">
								<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
									@foreach($myRecentActivity["areas_of_improvement"]["quiz_performance"] as $performanceByChannel)
										<div class="row content">
											<div class="col-xs-9 col-sm-9 col-md-9 col-lg-9">
												<span>{{ $performanceByChannel["name"] }}</span>
											</div>
											<div class="col-xs-3 col-sm-3 col-md-3 col-lg-3">
												<span>{{ round($performanceByChannel["total_percentage"]) }}&nbsp;%</span>
											</div>
										</div>
									@endforeach
								</div>
							</div>
						</div>
					</div>
				@endif
			@endif
		</div>
		@if($general->setting['events'] == 'on')
			<div class="col-xs-12 col-sm-12 col-md-4 col-lg-4 event-container">
				<div class="row">
					<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
						<span class="title">Events &nbsp;</span>
					</div>
					<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4 sub-title-container">
						<span class="l-gray">|</span>
						<span class="sub-title"><a href="{{ URL::to('event') }}">View All</a></span>
					</div>
				</div>
				<div class="title-border"></div>
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<div class="custom-calendar-wrap">
							<div id="custom-inner" class="custom-inner">
								<div class="custom-header clearfix">
									<h2 id="custom-month" class="custom-month"></h2>
									<h3 id="custom-year" class="custom-year"></h3>
								</div>
								<div id="calendar" class="fc-calendar-container"></div>
							</div>
						</div>
					</div>
				</div>
				<div class="row event-data" id="today-events">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<h4>Today's Events</h4>
					</div>
				</div>
			</div>
		@endif
	</div>
	<div class="md-margin"></div><!--space-->
	<!--START Watch Now-->
	@if($lhs_menu_settings->setting['programs'] == "on")
		<div class="row">
			<div class="col-sm-12 col-md-12 col-lg-12">
				<div class="page-title-bg">
					<h3 class="page-title">Recent Posts
						@if(count($packets) > 5)
							&nbsp;<span class="l-gray">|</span>
							<small>
								<a href="{{URL::to('program/what-to-watch')}}">View All</a>
							</small>
						@endif
					</h3>
				</div>
				@endif
                <?php
                use App\Model\MyActivity;
                use App\Model\Program;
                $array_packets['new'] = array();
                $array_packets['completed'] = array();
                //disable actvity query beause of mongo load
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
				@if($lhs_menu_settings->setting['programs'] == "on")
					<div class="row video-packets">
                        <?php $i=0; ?>
						@if(count($packets) > 0)
							@foreach($packets as $packet)
                                <?php $i=$i+1; ?>
								@if($i <=5 )
									<div class="col-lg-3 col-md-3 col-sm-4 col-xs-6 sm-margin">
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
													<p class="packet-title">{{str_limit($packet['packet_title'], $limit = 40, $end = '...')}}</p>
													<p class="packet-data">
										  	<span class="gray"><?php echo str_limit(Program::getPacketName($packet['feed_slug']), $limit = 24, $end = '...'); ?><br>
												{{count($packet['elements'])}} @if(count($packet['elements']) <= 1) {{str_singular('items')}}@else items @endif<br></span>
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
									</div>
								@endif
							@endforeach
						@else
							<div class="row">
								<div class="col-md-12">
									<div id="announceTab" class="announce-tabs" >
										<h4 align="center"> No {{Lang::get('program.packets')}} to watch</h4>
									</div>
								</div>
							</div>
						@endif
					</div>
				@endif
			</div>
		</div>
		<!--Plug-in Initialisation-->
		<script type="text/javascript">
            $(document).ready(function() {
                //Vertical Tab
                $('#announceTab').easyResponsiveTabs({
                    type: 'vertical', //Types: default, vertical, accordion
                    width: 'auto', //auto or any width like 600px
                    fit: true, // 100% fit in a container
                    closed: 'accordion', // Start closed if in accordion view
                    tabidentify: 'hor_1', // The tab groups identifier
                    activate: function(event) { // Callback function if tab is switched
                        var $tab = $(this);
                        var $info = $('#nested-tabInfo2');
                        var $name = $('span', $info);
                        $name.text($tab.text());
                        $info.show();
                    }
                });
            });
		</script>

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
		<script type="text/javascript" src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/js/jquery.calendario.js') }}"></script>
		<script>
            $(document).ready(function(){
                    <?php if($general->setting['events'] == "on") { ?>
                var calendar = $("#calendar").calendario({
                        displayWeekAbbr : true
                    });
                $("#custom-month").html(calendar.getMonthName());
                $("#custom-year").html(calendar.getYear());

                xmlHTTPRequestObj = $.ajax({
                    type : "GET",
                    url : "{!! URL::to("event?show=today&context=user-dashboard") !!}",
                    dataType : "json",
                    contentType : "application/x-www-form-urlencoded; charset=UTF-8"
                });

                xmlHTTPRequestObj.done(function(response, textStatus, jqXHR){
                    eventsContainer = $("#today-events");
                    eventsContainer.after(response.data);
                    calendar.setData(calendarData);
                });
                <?php } ?>
            });
		</script>
@stop