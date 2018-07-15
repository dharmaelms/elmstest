@section('content')
<?php use App\Model\Program; ?>
<div class="row">
	@foreach($feed_info as $feed)
		<h3>{{$feed['program_title']}}</h3>
		@if(isset($feed['relations']['access_request_pending']) && in_array(Auth::user()->uid,$feed['relations']['access_request_pending']))
           <b>Your request for this <?php echo Lang::get('program.course');?> is in process</b>
        @else
            <a href="{{url::to('program/feed-access-request/'.$feed['program_id'])}}">Request Access</a>
        @endif
		<ul><?php $category=''; $packets = Program::getPacketsCount($feed['program_slug']);?>
			@foreach($assigned_cat_info as $info)
					<?php $category.= html_entity_decode($info['category_name']).',';?>
				@endforeach
			<li><?php if(count($assigned_cat_info)>1){ echo trans('category.categories');}else{ echo trans('category.category'); } ?>: {{trim($category,',')}}</li>
			<li>{{ Lang::get('category.start_date') }}: {{Timezone::convertFromUTC('@'.$feed['program_startdate'], Auth::user()->timezone)}}</li>
            <li>{{ Lang::get('category.end_date') }}: {{Timezone::convertFromUTC('@'.$feed['program_enddate'], Auth::user()->timezone)}}</li>
			<li>{{ Lang::get('category.no_of_posts') }}: {{$packets}}</li>
			<li>{{ Lang::get('category.no_of_liked') }} {{Lang::get('program.packets')}}:</li>
		</ul>
		<ul>
			<li>Full Description: {!! $feed['program_description'] !!}</li>
		</ul>
	@endforeach
</div>
	
@stop