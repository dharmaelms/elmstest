<style>
.recordings-table2>tbody {
	display: block;
	max-height: 185px;
	overflow: auto;
}
.recordings-table2 td {
	padding: 7px 10px !important;
}
</style>
<?php $k = 0; ?>
@foreach($events as $e)
<?php
// echo '<pre>';print_r($e);
?>
<?php $k++; ?>
<div class="col-lg-4 col-md-6 col-sm-6 col-xs-12 xs-margin">
    @if($e->event_type == 'live')
        <div class="event-box live">
            <p class="font-16" style="word-wrap: break-word;">{{ $e->event_name }}</p>
            <div class="font-12 xs-margin">
                 <span class="start"><?php echo Lang::get('event.starts'); ?></span> 
                 <em>{{ $e->start_time->timezone(Auth::user()->timezone)->format('h:i A') }}</em>
                 &nbsp;&nbsp;&nbsp;&nbsp;
                 <strong><i class="fa fa-clock-o"></i></strong>
                 @if(($e->duration % 60) > 0)
                 <em>{{ sprintf('%02d hr %02d mins', ($e->duration / 60), ($e->duration % 60)) }}</em>
                 @else
                 <em>{{ sprintf('%02d hr 0 min', ($e->duration / 60)) }}</em>
                 @endif
            </div>
            <!-- <p class="border-btm margin-btm-3"><strong><?php echo Lang::get('event.host'); ?></strong></p>
            <p>{{ $e->event_host_name }}</p>
            @if(!empty($e->speakers))
            <p class="border-btm margin-btm-3"><strong><?php echo Lang::get('event.speaker'); ?></strong></p>
            <p>
            @foreach($e->speakers as $speaker)
            {{ $speaker }}&nbsp;
            @endforeach
            </p>
            @endif -->
            @if(isset($e->recordings) && !empty($e->recordings) && count($e->recordings) > 0)
            <?php $events = $e->recordings;   ?>
            <div class="table-responsive" style="overflow: unset">
                <p class="margin-btm-3"><strong>{{ Lang::get('event.recordings') }}</strong></p>
                <table class="table border-btm recordings-table recordings-table2">  
                    <?php $j = 1; ?>                    
                    @foreach($events as $event_recording)   
                        @if(isset($event_recording['created']) && !empty($event_recording['created']))
                            <tr>
                                <td>
                                    <div>
										@if(count($e->recordings) > 1) 
											{{ array_get($event_recording, 'display_name') }} 
										@endif
									</div>
                                    <div class="font-10 l-gray"><strong>{{ Lang::get('event.start_time') }}</strong>{{ date("H:i",strtotime($event_recording['created'])) }}</div>
                                </td>
                                <td width="83">
                                    @if(isset($event_recording['streamURL']) && !empty($event_recording['streamURL']))
                                    <a href="{{ $event_recording['streamURL'] }}" target="_blank" class="btn btn-warning btn-sm">{{ Lang::get('event.stream_url') }}</a>
                                    @endif
                                </td>
                            </tr>
                        @endif
                        <?php $j++ ?>
                    @endforeach
                </table>                    
            </div>
            @endif
            <div class="event-btn">
                @if(($e->start_time->timestamp - ($e->open_time * 60)) < time() && $e->end_time->timestamp > time())
                @if(Auth::user()->uid == $e->event_host_id)
                <a href="{{ url('event/live-join/'.$e->event_id) }}" class="btn btn-primary btn-sm"><?php echo Lang::get('event.start_now'); ?></a>
                @else
                <a href="{{ url('event/live-join/'.$e->event_id) }}" class="btn btn-primary btn-sm"><?php echo Lang::get('event.join_now'); ?></a>
                @endif
                @endif
                @if(isset($e->users_liked))
                @if(in_array(Auth::user()->uid, $e->users_liked))
                <i id="{{$e->event_id}}" data-action="unstar" class="pull-right fa fa-star yellow star-event cursor-pointer"></i>
                @else
                <i id="{{$e->event_id}}" data-action="star" class="pull-right fa fa-star gray star-event cursor-pointer"></i>
                @endif
                @else
                <i id="{{$e->event_id}}" data-action="star" class="pull-right fa fa-star gray star-event cursor-pointer"></i>
                @endif
                <span class="white label label-primary pull-right"><?php echo Lang::get('event.live'); ?></span>
            </div>
            <div class="video-btn">
                <a class="eventid btn btn-block btn-success btn-sm" data-toggle="modal" href="#live-event1{{ $k }}"><i class="fa fa-hand-o-right"></i> More Details</a>
            </div>
            <!-- Live event pop up -->
            <div id="live-event1{{ $k }}" class="modal fade" tabindex="-1" aria-hidden="true">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                            <h4 class="modal-title"><strong>{{ $e->event_name }}</strong></h4>
                        </div>
                    <div class="modal-body" style="margin-bottom: 20px;">
                        <div class="scroller" style="height:400px" data-always-visible="1" data-rail-visible1="1">
                            <div class="table-responsive">
                                <table class="table table-bordered">

                                <tr>
                                    <td><strong>{{ Lang::get('event.start_date') }}</strong></td><td>{{ $e->start_time->timezone(Auth::user()->timezone)->format('d/m/Y') }}</td>
                                    <td><strong>{{ Lang::get('event.start_time') }}</strong></td><td>{{ $e->start_time->timezone(Auth::user()->timezone)->format('h:i A') }}</td>
                                    <td><strong>Duration:</strong></td>
                                    <td>
                                    @if(($e->duration % 60) > 0)
                                    {{ sprintf('%02d hr %02d mins', ($e->duration / 60), ($e->duration % 60)) }}
                                    @else
                                    {{ sprintf('%02d hr 0 min', ($e->duration / 60)) }}
                                    @endif
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>{{ Lang::get('event.host_name') }}</strong></td><td>{{ $e->event_host_name }}</td>
                                    <td><strong>{{ Lang::get('event.speakers') }}</strong></td><td colspan="3">
                                    @if(isset($e->speakers) && !empty($e->speakers))
                                    <?php $spk = array(); ?>
                                    @foreach($e->speakers as $speaker)
                                    <?php $spk[] = $speaker; ?>
                                    @endforeach
                                    <?php $speaker = implode(",&nbsp;", $spk); ?>
                                    {{ $speaker }}
                                    @endif
                                    </td>
                                </tr>
                                </table>
                            </div>
                            @if(isset($e->event_description) && !empty($e->event_description))
                            <p class="margin-btm-3"><strong>Description: </strong></p>
                            <div class="box-content">
                                <div class="col-md-12"> 
                                    <p>{!! $e->event_description !!}</p>
                                </div>
                            </div>
                            @endif
                            @if(isset($e->recordings) && !empty($e->recordings) && count($e->recordings) > 0)

                            <?php $events = $e->recordings;
                            $i = 0; ?>
                            <div class="table-responsive"  style="overflow-x: unset">
                                <p class="margin-btm-3"><strong>{{ Lang::get('event.recordings') }}</strong></p>
                                <table class="table border-btm recordings-table">
                                @foreach($events as $recordings)

                                <?php $i++; ?>
                                <tr>
                                    <td>
                                        <div>
											@if(count($e->recordings) > 1) 
												{{ array_get($recordings, 'display_name') }}
										 	@endif
										</div>

                                        @if(isset($recordings['created']) && !empty($recordings['created']))
                                        <div class="font-10 l-gray"><strong>Start Time:</strong>{{ date("H:i",strtotime($recordings['created'])) }} &nbsp;&nbsp;</div>
                                        @endif
                                    </td>

                                    <td width="83">
                                        @if(isset($recordings['streamURL']) && !empty($recordings['streamURL']))
                                        <a href="{{ $recordings['streamURL'] }}" target="_blank" class="btn btn-warning btn-sm">Stream Url</a>
                                        @endif
                                    </td>

                                </tr>
                                @endforeach
                                </table>
                            </div>
                            @endif
                        </div>
                    </div>
                    </div>
                </div>
            </div>
            <!-- Live event pop up ends here -->
        </div><!--event-box-->
    @elseif($e->event_type == 'general')
    <div class="event-box general">
        <p class="font-16" style="word-wrap: break-word;font-weight: 600;
    text-transform: capitalize;
    background-image: -webkit-linear-gradient(left, #45b6af30, #abd6c4, #45b6af30);
    border-bottom: 2px solid #b39ddbcc;">{{ $e->event_name }}</p>
        <div class="font-12 xs-margin">
            <span class="start"><?php echo Lang::get('event.starts'); ?></span> <em>{{ $e->start_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</em>
            <br>
            <span class="end"><?php echo Lang::get('event.ends'); ?></span> <em>{{ $e->end_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</em> 
        </div>
        @if(!empty($e->location))
            <p class="border-btm margin-btm-3"><strong><?php echo Lang::get('event.location'); ?></strong></p>
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
                <span class="white label label-danger pull-right"><?php echo Lang::get('event.General'); ?></span>
            </div>
        <div class="video-btn">
            <a class="eventid btn btn-block btn-success btn-sm" data-toggle="modal" href="#gen-event1{{ $k }}"><i class="fa fa-hand-o-right"></i> {{ Lang::get('event.more_details') }}</a>
        </div>
    </div><!--event-box-->
    <div id="gen-event1{{ $k }}" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
                    <h4 class="modal-title text-capitalize bold" style="
    background: linear-gradient(#08c375,#7cb5b9);
    border: 1px dotted #5a7575;
    padding: 5px;
    width: 98%;
"> {{ $e->event_name }}</h4>
                </div>
                <div class="modal-body">
                    <div class="scroller" style="height:300px" data-always-visible="1" data-rail-visible1="1">
                        <div class="table-responsive">
                            <table class="table table-bordered">
                                <tr>
                                    <td width="100"><strong>{{ Lang::get('event.start_date') }}</strong></td><td>{{ $e->start_time->timezone(Auth::user()->timezone)->format('d/m/Y') }}</td>
                                    <td width="100"><strong>{{ Lang::get('event.start_time') }}</strong></td><td>{{ $e->start_time->timezone(Auth::user()->timezone)->format('h:i A') }}</td>
                                </tr>
                                <tr>
                                    <td width="100"><strong>{{ Lang::get('event.end_date') }}</strong></td><td>{{ $e->end_time->timezone(Auth::user()->timezone)->format('d/m/Y') }}</td>
                                    <td width="100"><strong>{{ Lang::get('event.end_time') }}</strong></td><td>{{ $e->end_time->timezone(Auth::user()->timezone)->format('h:i A') }}</td>
                                </tr>
                                @if(!empty($e->location))
                                <tr>
                                    <td width="100"><strong><?php echo Lang::get('event.location'); ?></strong></td><td colspan="3">{{ $e->location }}</td>
                                </tr>
                                @endif 
                            </table>
                        </div>
                        <p class="margin-btm-3" style="background: linear-gradient(#08c375,#7cb5b9);
    border: 1px dotted #5a7575;padding: 5px;"><strong>{{ Lang::get('event.description') }}</strong></p>
                        @if(isset($e->event_description) && !empty($e->event_description))
                        <div class="box-content">
                            <div class="col-md-12"> 
                                <p>{!! $e->event_description !!}</p>
                            </div>
                        </div>
                        @endif
                         <div class="modal-footer">
          <button  class="btn btn-danger btn-sm" data-dismiss="modal"><i class="fa fa-arrow-left"></i> Back</button>
        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    @endif
</div><!--event-->
@endforeach



