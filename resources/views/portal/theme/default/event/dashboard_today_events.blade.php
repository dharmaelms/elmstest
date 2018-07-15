
<style>
	.event-label
	{
		font-weight:bold;
		text-decoration: underline;
	}
	.events-list .panel .panel-title .accordion-toggle {padding: 14px 8px 12px !important;}
	.event-details-table>tbody>tr>td {padding: 4px 8px;line-height: 1.2;vertical-align: middle;border-top: 1px solid #ddd; }
                                    .event-details-table { width: auto; }
</style>

<?php $count = 0; $k=1; $l =1; ?>
@if(isset($events))
<div class="events-list">
<div class="panel-group accordion announce-tabs" id="accordion3">
@foreach($events as $event)
<?php	
	$date_val = explode("-",$event->start_date_label);	
?>

	@if($event->event_type === "live")

<!-- accordian -->
		<div class="panel panel-default border-btm-0">
	          <div class="panel-heading">
	            <h4 class="panel-title">
	            <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_3_{{$k}}">
	              <div class="event-btn">
			           	<span class="white label label-primary">
			                    {{ strtoupper($event->event_type) }}
			                </span>
			      </div>
						<span class="event-label">
							{{ $event->event_name }}
							@if(($event->start_time->timestamp - ($event->open_time * 60)) < time())
								@if($event->end_time->timestamp > time())
									@if(Auth::user()->uid == $event->event_host_id)
										<a href="{{ url('event/live-join/'.$event->event_id) }}" target="_blank" class="event-label"><?php echo Lang::get('event.start_now'); ?></a>
									@else
										<a href="{{ url('event/live-join/'.$event->event_id) }}" target="_blank" class="event-label"><?php echo Lang::get('event.join_now'); ?></a>
									@endif
								@else
									<a href="javascript:;"><?php echo Lang::get('event.closed'); ?></a>
								@endif
							@else
								<span>{{ Lang::get('event.starts_at') }} <?php echo date("g:i a", strtotime("$event->start_time_label")); ?></span>
							@endif
						</span>
	          </a>
	            </h4>
	          </div>
	          <div id="collapse_3_{{$k}}" class="panel-collapse collapse">
	            <div class="panel-body">					
	              <p>
		            <span>
						@if(strlen($event->event_description) > 150)
		                    {!! html_entity_decode(substr($event->event_description, 0, (strrpos(substr($event->event_description, 0, 150), " ")))) !!}....	
		                @else
		                    {!! html_entity_decode($event->event_description) !!}
		                @endif
		            </span>
	              </p>
	              <p>
	                <a href="{{ URL::to('event')}}?show=custom&day={{$date_val[0]}}&month={{$date_val[1]}}&year={{$date_val[2]}}"><?php echo Lang::get('event.more'); ?></a>
	               	@if(isset($event->recordings) && count($event->recordings > 0))
                                <?php $i = 0; ?>
                                <table class="table event-details-table" cellspacing="0" cellpadding="2" border="0">
                                    
                                    @foreach($event->recordings as $recordings)
                                    <?php $i++; ?>
                                        <tr>
                                            <td>Recording @if(count($event->recordings) > 1) {{ $i }} @endif</td>
                                            <td>
                                            @if(isset($recordings['created']) && !empty($recordings['created']))
                                            <span>{{ date("H:i",strtotime($recordings['created'])) }}</span>
                                            @endif
                                            </td>
                                            @if(isset($recordings['streamURL']) && !empty($recordings['streamURL']))
                                                <td><a class='btn sm-btn' style='background-color: #FF9900' href="{{ $recordings['streamURL'] }}" target="_blank">{{ Lang::get('event.stream_url') }}</a></td>
                                            @endif
                                        </tr>
                                    @endforeach
                                </table>
                            @endif
	              </p>
	            </div>
	          </div>
          <?php $k++; ?>
        </div>
<!-- accordian ends -->

	@elseif($event->event_type === 'general')
    <!-- accordian -->
		<div class="panel panel-default border-btm-0">
          <div class="panel-heading">
            <h4 class="panel-title">
            <a class="accordion-toggle accordion-toggle-styled collapsed" data-toggle="collapse" data-parent="#accordion3" href="#collapse_4_{{$l}}">
            <div class="event-btn">
                <span class="white label label-danger">
                    {{ strtoupper($event->event_type) }}
                </span>

            </div>
            <span class="event-label">{{ $event->event_name }}</span> starts at <?php echo date("g:i a", strtotime("$event->start_time_label")); ?> <?php echo Lang::get('event.today'); ?>
					
          </a>
            </h4>
          </div>
          <div id="collapse_4_{{$l}}" class="panel-collapse collapse">
            <div class="panel-body">
              <p>
	           <span class="event-label">
                @if(strlen($event->event_description) > 150)
                   {!! html_entity_decode(str_limit(ucwords($event->event_description), $limit = 150, $end = '...')) !!}
                @else
                    {!! html_entity_decode($event->event_description) !!}
                @endif
                </span> 
              </p>
           		<p>
           			 <a href="{{ URL::to('event')}}?show=custom&day={{$date_val[0]}}&month={{$date_val[1]}}&year={{$date_val[2]}}"><?php echo Lang::get('event.more'); ?></a>
           		</p>
            </div>
          </div>
          <?php $l++; ?>
        </div>
<!-- accordian ends -->
      
	@endif
	<?php $count++; ?>
@endforeach
	</div></div>
@endif
<script>
	calendarData = {!! $calendar_data !!};
</script>
@if(!$count)
<ul class="events-list" >
	<li>
		<span><?php echo Lang::get('event.there_are_no_events_today'); ?></span>
	</li>
</ul>
@endif
