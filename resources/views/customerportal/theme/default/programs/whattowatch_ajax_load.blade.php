<?php use App\Model\Program; ?>
@foreach($packets as $packet)
	<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
		<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}">
            <div class="packet">
				<figure>
					@if(empty($packet['packet_cover_media']))
						<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
					@else
						<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{$packet['packet_title']}}">
					@endif
					@if(in_array($packet['packet_id'], $new_packets))
						<img class="new-label" src="{{URL::asset($theme.'/img/new-label.png')}}">
					@endif
					@if(in_array($packet['packet_id'], $completed_packets))
						<img class="completed-overlay" src="{{URL::asset($theme.'/img/completed.png')}}">
					@endif
				</figure>
				<div>
					<p class="packet-title">{{str_limit(ucwords($packet['packet_title']), $limit = 40, $end = '...')}}</p>
					<p class="packet-data">
					  	<span class="gray"><?php echo str_limit(ucwords(Program::getPacketName($packet['feed_slug'])), $limit = 24, $end = '...'); ?><br>
					  		{{count($packet['elements'])}} @if(count($packet['elements']) <= 1) {{str_singular('items')}}@else items @endif<br></span>
					  	<span class="l-gray font-12">{{ Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))}}</span>
					  	<span class="pull-right">
					  		@if(in_array($packet['packet_id'], $favorites))
								<?php 
									$action="unfavourite";
									$class="red"; 
								?>
							@else
								<?php 
									$action="favourite";
									$class="gray";
								?>
							@endif
					  		<span class="favourite">
								<i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}} fav-packet" style="cursor:pointer"></i>
							</span>
					  	</span>
					</p>
				</div>
            </div><!--packet-->
        </a>
  	</div><!--packet div-->
@endforeach

<script type="text/javascript">
	$('.favourite').on('click', '.fav-packet', function(e) {
		e.preventDefault();
		var action = $(this).data('action');
		var packet_id = $(this).attr('id');
		if(action == 'favourite') {
			$("#"+packet_id).removeClass("l-gray").addClass("red");
			$.ajax({
				type: 'GET',
                url: "{{ url('program/packet-favourited/favourite') }}/"+packet_id
            })
            .done(function(response) {
            	if(response.status == true) {
            		$("#"+response.packet_id).data('action', 'unfavourite');
            	} else {
            		$("#"+response.packet_id).removeClass("red").addClass("gray");
            	}
            })
            .fail(function(response) {
            	$("#"+packet_id).removeClass("red").addClass("gray");
                alert( "Error while updating the post. Please try again" );
            });
        }
        if(action == 'unfavourite') {
        	$("#"+packet_id).removeClass("red").addClass("gray");
			$.ajax({
				type: 'GET',
                url: "{{ url('program/packet-favourited/unfavourite') }}/"+packet_id
            })
            .done(function(response) {
            	if(response.status == true) {
            		$('#'+response.packet_id).data('action', 'favourite');
            	} else {
            		$('#'+response.packet_id).removeClass('gray').addClass('red');
            	}
            })
            .fail(function(response) {
            	$('#'+response.packet_id).removeClass("gray").addClass("red");
                alert( "Error while updating the post. Please try again" );
            });
        }
	});
</script>