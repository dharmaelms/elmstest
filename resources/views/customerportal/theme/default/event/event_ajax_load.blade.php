@foreach($events as $e)
	<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 xs-margin">
		@if($e->event_type == 'live')
		<div class="event-box live">
			<p class="font-16">{{ $e->event_name }}</p>
			<div class="font-12 xs-margin">
				<span class="start">STARTS:</span> 
				<em>{{ $e->start_time->timezone(Auth::user()->timezone)->format('h:i A') }}</em>
				&nbsp;&nbsp;&nbsp;&nbsp;
				<strong><i class="fa fa-clock-o"></i></strong>
				@if(($e->duration % 60) > 0)
				<em>{{ sprintf('%02d hr %02d mins', ($e->duration / 60), ($e->duration % 60)) }}</em>
				@else
				<em>{{ sprintf('%02d hr 0 min', ($e->duration / 60)) }}</em>
				@endif
			</div>
			<p class="border-btm margin-btm-3"><strong>Host</strong></p>
			<p>{{ $e->event_host_name }}</p>
			@if(!empty($e->speakers))
			<p class="border-btm margin-btm-3"><strong>Speakers</strong></p>
			<p>
				@foreach($e->speakers as $speaker)
					{{ $speaker }}&nbsp;
				@endforeach
			</p>
			@endif
			<div class="event-btn">
				@if(($e->start_time->timestamp - ($e->open_time * 60)) < time() && $e->end_time->timestamp > time())
					@if(Auth::user()->uid == $e->event_host_id)
						<a href="{{ url('event/live-join/'.$e->event_id) }}" class="btn btn-success">Start now</a>
					@else
						<a href="{{ url('event/live-join/'.$e->event_id) }}" class="btn btn-success">Join now</a>
					@endif
				@endif
				@if(isset($e->users_liked))
					@if(in_array(Auth::user()->uid, $e->users_liked))
						<i id="{{$e->event_id}}" data-action="unstar" class="pull-right fa fa-star yellow star-event" style="cursor:pointer"></i>
					@else
						<i id="{{$e->event_id}}" data-action="star" class="pull-right fa fa-star gray star-event" style="cursor:pointer"></i>
					@endif
				@else
					<i id="{{$e->event_id}}" data-action="star" class="pull-right fa fa-star gray star-event" style="cursor:pointer"></i>
				@endif
				<span class="white label label-primary pull-right">LIVE</span>
			</div>
		</div><!--event-box-->
		@elseif($e->event_type == 'general')
		<div class="event-box general">
			<p class="font-16">{{ $e->event_name }}</p>
			<div class="font-12 xs-margin">
				<span class="start">STARTS:</span> <em>{{ $e->start_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</em>
				<br>
				<span class="end">ENDS:</span> <em>{{ $e->end_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</em> 
			</div>
			@if(!empty($e->location))
			<p class="border-btm margin-btm-3"><strong>Location</strong></p>
			<p>{{ $e->location }}</p>
			@endif
			<div class="event-btn">
				@if(isset($e->users_liked))
					@if(in_array(Auth::user()->uid, $e->users_liked))
						<i id="{{$e->event_id}}" data-action="unstar" class="pull-right fa fa-star yellow star-event" style="cursor:pointer"></i>
					@else
						<i id="{{$e->event_id}}" data-action="star" class="pull-right fa fa-star l-gray star-event" style="cursor:pointer"></i>
					@endif
				@else
					<i id="{{$e->event_id}}" data-action="star" class="pull-right fa fa-star l-gray star-event" style="cursor:pointer"></i>
				@endif
				<span class="white label label-danger pull-right">GENERAL</span>
			</div>
		</div><!--event-box-->
		@endif
	</div><!--event-->
@endforeach