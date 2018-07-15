	<div class="" style="text-align:center">
		    <a href="{{URL::to('cp/reports/admin-reports')}}" title="{{ trans('admin/reports.channels_perf') }}" class="show-tooltip btn btn-gray channelperformance">{{trans('admin/reports.performance_tab')}}</a>
		    <a href="{{URL::to('cp/reports/channel-completion')}}" title="{{ trans('admin/reports.channel_compl') }}" class="show-tooltip btn btn-gray channelcontent" >{{trans('admin/reports.completion_tab')}}</a>
		    <a href="{{URL::to('cp/reports/announcement-viewed')}}" title="{{ trans('admin/reports.announ_viewed') }}" class="show-tooltip btn btn-gray announcementview" >{{trans('admin/reports.announcement_tab')}}</a>
        	@if($general->setting['scorm_reports'] == "on")        
            	<a href="{{URL::to('cp/reports/scorm-reports')}}" title="{{ trans('admin/reports.scorm_reports') }}" class="show-tooltip btn btn-gray scormreports">{{trans('admin/reports.scorm')}}</a>
        	@endif
    </div>

    <script>
        $('.{{$selector}}').removeClass('btn-gray').addClass('btn-primary');
    </script>
    <br />
