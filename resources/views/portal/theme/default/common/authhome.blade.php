@section('content')
	<div class="row">
	</div>
	<div class="row">		
		@foreach($packets as $packet)
		<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}">
			<div class="cs-box">
				<figure>
					<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}">
				</figure>
				<p>{{$packet['packet_title']}}</p>
				<p>{{ Timezone::convertFromUTC('@'.$packet['created_at'], Auth::user()->timezone, Config('app.date_format')) }}</p>
				<p>{{$packet['feed_slug']}}</p>
			</div>
		</a>	
		@endforeach
	</div>
	
@stop