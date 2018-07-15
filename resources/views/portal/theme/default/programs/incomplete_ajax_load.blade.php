<?php 
$course_title_fun = function($program_id,$program_title){
		$parent_course = Session::get('parent_course');
		$course_title = '';
		array_where($parent_course, function($key, $value) 
			use ($program_id,&$course_title)
	            {
	                if($program_id == $value['program_id'])
	                {
	                    $course_title = $value['program_title'];
	                }
	            });
		return $course_title." - " .$program_title;
	};
?>
@foreach($packets as $packet)
	<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
		<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}" title="{{$packet['packet_title']}}">
            <div class="packet" style="background: linear-gradient(#ff170573,#ff170533);
    box-shadow: 4px 4px 4px #c79b9773;">
				<figure>
					@if(empty($packet['packet_cover_media']))
						<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{str_limit($packet['packet_title'], $limit = 30, $end = '...')}}">
					@else
						<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{str_limit($packet['packet_title'], $limit = 30, $end = '...')}}">
					@endif
				</figure>
				<div>
					
					<p class="packet-title uppercase font-15 bold red center">
						{{str_limit($packet['packet_title'], $limit = 30, $end = '...')}}
					</p>
					
					<p class="packet-data">
					  	<span class="gray">
					  		<?php
					  			$program_slug = array_get($packet, 'feed_slug'); 
					  			$program = array_get($program_details, $program_slug, []);
					  			$program_title = array_get($program, 'program_title');
								$parent_id = array_get($program, 'parent_id'); 
								if(array_get($program, 'program_type') == 'course')
								{
									$program_title = $course_title_fun($parent_id,$program_title);
								}

					  		?>
					  		{{str_limit($program_title, $limit = 24, $end = '...')}}
					  		<br>
					  		{{count($packet['elements'])}} 
					  		@if(count($packet['elements']) <= 1) 
					  			{{str_singular('items')}}
					  		@else 
					  				items 
					  		@endif
					  		<br>
					  	</span>
					  	
					  	<span class="l-gray font-12">
					  		{{ Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))}}
					  	</span>
					  	
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
								<i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="cursor-pointer fa fa-heart {{$class}} fav-packet"></i>
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