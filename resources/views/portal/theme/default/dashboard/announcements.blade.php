@foreach($announcements as $announcement)
	<li class="xs-margin" data-page-id="{{$page}}">
	  <p class="black margin-0">{{ $announcement->title }}</p>
	  <p class="gray margin-0">{!! $announcement->description !!}</p>
	  <p class="font-10"><a href="#" class="pull-right">{{ Lang::get('dashboard.view_more') }}</a></p>
	</li>
@endforeach