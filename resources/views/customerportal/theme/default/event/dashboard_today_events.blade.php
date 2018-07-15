<style>
	.event-label
	{
		font-weight:bold;
		text-decoration: underline;
	}
</style>
<?php $count = 0; ?>
@if(isset($events))
@foreach($events as $event)
	@if($event->event_type === "live")
	<div class="row event-data content">
		<div class="col-xs-8 col-sm-8 col-md-8 col-lg-8">
			<span>{{ $event->event_name }}</span>
		</div>
		<div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
		@if(($event->start_time->timestamp - ($event->open_time * 60)) < time())
			@if($event->end_time->timestamp > time())
				@if(Auth::user()->uid == $event->event_host_id)
					<a href="{{ url('event/live-join/'.$event->event_id) }}" target="_blank" class="event-label">Start Now</a>
				@else
					<a href="{{ url('event/live-join/'.$event->event_id) }}" target="_blank" class="event-label">Join Now</a>
				@endif
			@else
				<a href="javascript:;">Closed</a>
			@endif
		@endif
		</div>	
		@if(++$count === 2)
		<?php break; ?>
		@endif
	</div>
	@endif
@endforeach
@endif
<script>
	calendarData = {!! $calendar_data !!};
</script>
@if($count === 0)
<div class="row event-data content">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<span>There are no live events today.</span>
	</div>
</div>
@endif