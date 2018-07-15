<!-- BEGIN Main Content -->
<style type="text/css">
    .event-details-table {
     width: 100%;
}
.event-details-table>tbody>tr>td {
    padding: 4px 45px;
}
.event-details-table>thead>tr>th {
    padding: 4px 45px;
}
.event-details-table>tbody {
    display: block;
    max-height: 150px;
    overflow: auto;
}
.record-details-table>tbody {
    display: block;
    max-height: 150px;
    overflow: auto;
}
.record-details-table>thead>tr>th {
    padding: 4px 31px;
}
.record-details-table>tbody>tr>td {
    padding: 4px 32px;
}
.recordTableDetails>thead>tr>th{
    text-align: center;
    padding: 4px 0px;
    width: 145px;
}
.recordTableDetails>tbody>tr>td{
    text-align: center;
    padding: 4px 0px;
    width: 145px;
}
.recordTableDetails>thead>tr>th:last-child{width: 132px;}
.eventDetailsTable>thead>tr>th{
    text-align: center;
    padding: 4px 0px;
    width: 190px;
}
.eventDetailsTable>tbody>tr>td{
    text-align: center;
    padding: 4px 0px;
    width: 190px;
}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <p><strong>{{ trans('admin/event.type') }}:</strong> {{ $event->event_type }}</p>
                    	<p><strong>{{ trans('admin/event.name') }}:</strong> {{ $event->event_name }}</p>
                    	<p><strong>{{ trans('admin/event.description') }}:</strong> {!! $event->event_description !!}</p>
                    	@if($event->event_type == 'live')
                            <p><strong>{{ trans('admin/event.host_name') }}:</strong> {{ $event->event_host_name }}</p>
                            <p><strong>{{ trans('admin/event.event_start_date_time') }}:</strong> {{ $event->start_time->timezone(Auth::user()->timezone)->format('d-m-Y H:i') }}</p>
							@if($event->event_cycle == 'single')
                                <p><strong>{{ trans('admin/event.duration') }}:</strong> {{ $event->duration.' Mins' }}</p>
                                <p><strong>{{ trans('admin/event.session_type') }}:</strong> <?php echo ($event->session_type == 'MC') ? trans('admin/event.meeting_center') : trans('admin/event.training_center'); ?></p>
                                <p><strong>{{ trans('admin/event.webex_host') }}:</strong> {{ $event->webex_host_username }}</p>
                            @endif
                            @if($event->event_cycle == 'recurring')
                                
                            @endif
                            @if(isset($event->recordings) && !empty($event->recordings) && count($event->recordings) > 0)
                                <table class="table event-details-table eventDetailsTable" id="recordings-list" cellspacing="0" cellpadding="2" border="0">
                                    <thead style="display: block;">
                                        <th>
                                            {{ trans('admin/event.recording') }}
                                        </th>
                                        <th>
                                            {{ trans('admin/event.start_time') }}
                                        </th>
                                        <th>
                                           {{ trans('admin/event.action') }}
                                        </th>
                                    </thead>
                                    <tbody>
                                        @foreach($event->recordings as $recordings)
                                            <tr>
                                                <td>
                                                    <center>
                                                    @if(isset($recordings['display_name']) && !empty($recordings['display_name']))
                                                        {{ $recordings['display_name'] }}
                                                    @endif
                                                    </center>
                                                </td>
                                                <td>
                                                @if(isset($recordings['created']) && !empty($recordings['created']))
                                                <span><center>{{ date("H:i",strtotime($recordings['created'])) }}</center></span>
                                                @endif
                                                </td>
                                                <td>
                                                    <center>
                                                        @if(isset($recordings['downloadURL']) && !empty($recordings['downloadURL']))
                                                            <a class='btn btn-sm' style='background-color: #3399FF'  href="{{ $recordings['downloadURL'] }}" target="_blank" title="{{ trans('admin/event.download_url') }}"><i class="fa fa-download"></i></a>&nbsp;&nbsp;|&nbsp;&nbsp;
                                                        @endif
                                                        @if(isset($recordings['streamURL']) && !empty($recordings['streamURL']))
                                                            <a class='btn btn-sm' style='background-color: #FF9900' href="{{ $recordings['streamURL'] }}" target="_blank" title="{{ trans('admin/event.stream_url') }}"><i class="fa fa-video-camera"></i></a>
                                                        @endif
                                                        @if(isset($recordings['recordingID']) && !empty($recordings['recordingID']))
                                                            &nbsp;  | &nbsp; <a class='btn btn-sm delete-record' style='background-color: #fb3838' data-event-id="{{$event->event_id}}" data-id="{{ $recordings['recordingID'] }}" target="_blank" title="{{ trans('admin/event.delete_recording') }}"><i class="fa fa-trash"></i></a>
                                                        @endif
                                                        </center>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                                <br>
                            @if(!empty($deletedRecordingds) && config('app.show_deleted_events_recordings'))
                                <table class="table record-details-table recordTableDetails" id="delete-recordings-list" cellspacing="0" cellpadding="2" border="0">
                                    <thead style="display: block;">
                                        <th>
                                            {{ trans('admin/event.recording') }}
                                        </th>
                                        <th>
                                            {{ trans('admin/event.start_time') }}
                                        </th>
                                        <th>
                                           {{ trans('admin/event.deleted_at') }}
                                        </th>
                                        <th>
                                           {{ trans('admin/event.deleted_by') }}
                                        </th>
                                    </thead>
                                    <tbody>
                                        @foreach($deletedRecordingds->recordings as $recordings)
                                            <tr>
                                                <td>
                                                    @if(isset($recordings['display_name']) && !empty($recordings['display_name']))
                                                        {{ $recordings['display_name'] }}
                                                    @endif
                                                </td>
                                                <td>
                                                    <span>
                                                    @if(isset($recordings['created']) && !empty($recordings['created']))
                                                    {{ date("H:i",strtotime($recordings['created'])) }}
                                                    @endif
                                                    </span>
                                                </td>
                                                <td>
                                                    <span>
                                                    @if(isset($recordings['deleted_at']) && !empty($recordings['deleted_at']))
                                                        {{
                                                        Timezone::convertFromUTC('@' . $recordings['deleted_at'], Auth::user()->timezone, config('app.date_time_format'))
                                                        }}
                                                    @endif
                                                    </span>
                                                </td>
                                                <td>
                                                    @if(isset($recordings['deleted_by']) && !empty($recordings['deleted_by']))
                                                        {{ $recordings['deleted_by'] }}
                                                    @endif
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            @endif
                        @endif

                        @if($event->event_type == 'general')
                            @if($event->event_cycle == 'single')
                                <p><strong>{{ trans('admin/event.event_start_date_time') }}:</strong> {{ $event->start_time->timezone(Auth::user()->timezone)->format('d-m-Y H:i') }}</p>
                                <p><strong>{{ trans('admin/event.event_end_date_time') }}:</strong> {{ $event->end_time->timezone(Auth::user()->timezone)->format('d-m-Y H:i') }}</p>
                                <p><strong>{{ trans('admin/event.location') }}:</strong> {{ $event->location }}</p>
                            @endif
                            @if($event->event_cycle == 'recurring')
                            
                            @endif
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
