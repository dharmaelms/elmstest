@section('content')
	<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
	<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />
	<div class="row">
		<div class="col-md-4 col-sm-4 col-xs-12" style="border-right: 1px solid #ddd">
			<div class="tabbable color-tabs">
				<ul class="nav nav-tabs center" style="height:34px">
					<li></li>
				</ul>
			</div><!--dummy tabs for space-->
			<section class="main">
				<div class="custom-calendar-wrap">
					<div id="custom-inner" class="custom-inner">
						<div class="custom-header clearfix">
							<nav>
								<span id="custom-prev" class="custom-prev"></span>
								<span id="custom-next" class="custom-next"></span>
							</nav>
							<h2 id="custom-month" class="custom-month"></h2>
							<h3 id="custom-year" class="custom-year"></h3>
						</div>
						<div id="calendar" class="fc-calendar-container"></div>
					</div>
				</div>
			</section>
			<div class="sm-margin"></div><!--space-->

			<div class="portlet box blue-hoki" id="upcoming-event">
				<div class="portlet-title">
					<div class="caption"><i class="fa fa-arrow-circle-right font-20"></i> {{ Lang::get('event.upcoming_events') }}</div>
					<!-- <div class="tools"><a href="#" class="white">{{ Lang::get('event.view_all') }}</a></div> -->
				</div>
				<div class="portlet-body padding-0">
					<div class="list-group">
						@if(!$upcoming_events->isEmpty())
							<?php $i = 1; ?>
							@foreach($upcoming_events as $event)
							<div class="list-group-item">
								<h4 class="list-group-item-heading text-capitalize"><a data-toggle="modal" href="#up-event-{{$i}}">{{ $event->event_name }}</a></h4>
								<p class="list-group-item-text gray font-10 bold">
									@if($event->event_type == 'general')
										<span class="start">{{ Lang::get('event.starts') }}</span> {{ $event->start_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }} &nbsp;
										<span class="end">{{ Lang::get('event.ends') }}</span> {{ $event->end_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}
									@elseif($event->event_type == 'live')
										<span class="start">{{ Lang::get('event.starts') }}</span> {{ $event->start_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }} &nbsp;
										<b><i class="fa fa-clock-o"></i></b> 
										@if(($event->duration % 60) > 0)
											{{ sprintf('%02d hr %02d mins', ($event->duration / 60), ($event->duration % 60)) }}
										@else
											{{ sprintf('%02d hr 0 min', ($event->duration / 60)) }}
										@endif
									@endif
								</p>
							</div>
							<div id="up-event-{{$i++}}" class="modal fade" tabindex="-1" aria-hidden="true">
								<div class="modal-dialog">
									<div class="modal-content">
										<div class="modal-header">
											<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
											<h4 class="modal-title font-16">{{ $event->event_name }}</h4>
										</div>
										<div class="modal-body">
											<div class="scroller" style="height:300px" data-always-visible="1" data-rail-visible1="1">
												<div class="row">
													<div class="col-md-12">
														@if($event->event_type == 'live')
														<p class="font-12">
															<strong>{{ Lang::get('event.time') }}</strong> {{ $event->start_time->timezone(Auth::user()->timezone)->format('h:i A') }}&nbsp;|&nbsp;<strong>{{ Lang::get('event.duration') }}</strong> 
															@if(($event->duration % 60) > 0)
																{{ sprintf('%02d hr %02d mins', ($event->duration / 60), ($event->duration % 60)) }}
															@else
																{{ sprintf('%02d hr 0 min', ($event->duration / 60)) }}
															@endif
														</p>
														<p>{{ str_limit(strip_tags($event->event_description), 40) }}</p>
														<p><strong>{{ Lang::get('event.host') }}</strong> {{ $event->event_host_name }}</p>
														<p>
															<strong>{{ Lang::get('event.speakers') }}:</strong>
															@foreach($event->speakers as $speaker)
															{{ $speaker }} &nbsp;|&nbsp;
															@endforeach
														</p>
														<p>
															@if(isset($event->user_liked))
																@if(in_array(Auth::user()->uid, $event->user_liked))
																	<i class="pull-right fa fa-star yellow"></i>
																@endif
															@endif
														</p>
														@elseif($event->event_type == 'general')
														<p class="font-12">
															<span class="start">{{ Lang::get('event.starts') }}</span> {{ $event->start_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}
															<br>
															<span class="end">{{ Lang::get('event.ends') }}</span> {{ $event->end_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}
														</p>
														<p>{{ str_limit(strip_tags($event->event_description), 40) }}</p>
														@if(!empty($event->location))
														<p><strong>Location:</strong> {{ $event->location }}</p>
														@endif
														<div class="event-btn">
															@if(isset($event->user_liked))
																@if(in_array(Auth::user()->uid, $event->user_liked))
																	<i class="pull-right fa fa-star yellow"></i>
																@endif
															@endif
														</div>
														@endif
													</div>
												</div>
											</div>
										</div>
										<div class="modal-footer">
											<button type="button" data-dismiss="modal" class="btn default">{{ Lang::get('event.close') }}</button>
										</div>
									</div>
								</div>
							</div>
							@endforeach
						@else
							<p>&nbsp;</p>
							<center>{{ Lang::get('event.no_events_to_display') }}</center>
						@endif
					</div> <!--END list group-->
				</div>
			</div><!-- END Category-->
		</div><!--calendar-->
		<div class="col-md-8 col-sm-8 col-xs-12">
			<div class="tabbable tabbable-tabdrop color-tabs">
				<ul class="nav nav-tabs center">
					@if($show == 'custom')
					<li class="active">
						<a href="#"><i class="fa fa-calendar"></i>&nbsp; {{Input::get('day').'-'.Input::get('month').'-'.Input::get('year')}}</a>
					</li>
					@endif
					<li @if($show == 'today') class="active" @endif>
						<a href="{{ url('event?show=today') }}"><i class="fa fa-calendar"></i>&nbsp;{{ Lang::get('event.today') }} </a>
					</li>
					<li @if($show == 'starred') class="active" @endif>
						<a href="{{ url('event?show=starred') }}"><i class="fa fa-star"></i>&nbsp; {{ Lang::get('event.starred') }} </a>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active">
						<div id="event-master" class="row">
							@include('portal.theme.default.event.event_ajax_load', [$events])
						</div>
						<div id='no-records' style='display:none' class='col-md-12 center l-gray'>
							<p><strong>{{Lang::get('pagination.no_more_records')}}</strong></p>
						</div>
					</div><!--today tab section-->
				</div><!-- tab-content -->
			</div><!--main tab div-->
		</div><!--tabs-->
	</div><!-- main row-->
	<script type="text/javascript" src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/js/modernizr.custom.63321.js') }}"></script>
	<script type="text/javascript" src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/js/jquery.calendario.js') }}"></script>
	<script type="text/javascript">
	var codropsEvents = {!! json_encode($cal) !!};
	$(function() {
        var transEndEventNames = {
			'WebkitTransition' : 'webkitTransitionEnd',
			'MozTransition' : 'transitionend',
			'OTransition' : 'oTransitionEnd',
			'msTransition' : 'MSTransitionEnd',
			'transition' : 'transitionend'
		},
		transEndEventName = transEndEventNames[ Modernizr.prefixed('transition') ],
		$wrapper = $('#custom-inner'),
		$calendar = $('#calendar'),
        cal = $calendar.calendario( {
			onDayClick : function($el, data, dateProperties) {
				if(data.length > 0)
					window.location.href = "{{url('event?show=custom')}}&day="+dateProperties.day+"&month="+dateProperties.month+"&year="+dateProperties.year;
			},
			@if(Input::get('show') == 'custom')
				@if(!empty(Input::get('day')) && !empty(Input::get('month')) && !empty(Input::get('year')))
					customDate : new Date('{{Carbon::createFromFormat("d-m-Y", Input::get("day")."-".Input::get("month")."-".Input::get("year"), Auth::user()->timezone)->toDateString()}}'),
				@else
					customDate : new Date('{{ Carbon::now(Auth::user()->timezone)->toDateString() }}'),
				@endif
			@endif
			caldata : codropsEvents,
			displayWeekAbbr : true,
		}),
		$month = $('#custom-month').html(cal.getMonthName()),
		$year = $('#custom-year').html(cal.getYear());

		$('#custom-next').on('click', function() {
			cal.gotoNextMonth(updateMonthYear);
		});
		$('#custom-prev').on('click', function() {
			cal.gotoPreviousMonth(updateMonthYear);
		});

		function updateMonthYear() {
			$.ajax({
                type: "GET",
                url: "{{ url('event/cal-dates') }}?month="+cal.getMonth()+"&year="+cal.getYear()
            })
            .done(function(response) {
                cal.setData(response);
            })
            .fail(function(response) {
                alert( "Error while updating the calendar. Please try again" );
            });		
			$month.html(cal.getMonthName());
			$year.html(cal.getYear());
		}

		$('#event-master').on('click', '.star-event', function() {
			var action = $(this).data('action');
			var event_id = $(this).attr('id');
			if(action == 'star') {
				$("#"+event_id).removeClass("l-gray").addClass("yellow");
				$.ajax({
					type: 'GET',
	                url: "{{ url('event/star-event/star') }}/"+event_id
	            })
	            .done(function(response) {
	            	if(response.status == true) {
	            		$("#"+response.event_id).data('action', 'unstar');
	            	} else {
	            		$("#"+response.event_id).removeClass("yellow").addClass("l-gray");
	            	}
	            })
	            .fail(function(response) {
	            	$("#"+event_id).removeClass("yellow").addClass("l-gray");
	                alert( "Error while updating the event. Please try again" );
	            });
	        }
	        if(action == 'unstar') {
	        	$("#"+event_id).removeClass("yellow").addClass("l-gray");
				$.ajax({
					type: 'GET',
	                url: "{{ url('event/star-event/unstar') }}/"+event_id
	            })
	            .done(function(response) {
	            	if(response.status == true) {
	            		$('#'+response.event_id).data('action', 'star');
	            	} else {
	            		$('#'+response.event_id).removeClass('l-gray').addClass('yellow');
	            	}
	            })
	            .fail(function(response) {
	            	$('#'+response.event_id).removeClass("l-gray").addClass("yellow");
	                alert( "Error while updating the event. Please try again" );
	            });
	        }
		});
		
		if({{($show == 'starred')? 1 : 0 }}) {
			var event_display_count = {{ count($events) }};
			var start = 9;
			var stop = flag = true;
			$(window).scroll(function() {
				if(event_display_count > 8 && stop) {
		        	if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
		        		if(flag) {
		        			flag = false;
			        		$.ajax({
			        			type: 'GET',
			        			url: "{{ url('event?show=starred&start=') }}"+start
			        		}).done(function(e) {
			        			if(e.status == true) {
			        				$('#event-master').append(e.data);
			        				flag = true;
			        			}
			        			else {
			        				$('#no-records').show();
			        				stop = false;
			        			}
			        			start += 9;
			        		}).fail(function(e) {
			        			alert('Failed to get event data');
			        		});
			        	}
		        	}
		        }
		    });
		}
    });
	</script>
@stop