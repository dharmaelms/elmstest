<?php
use App\Model\Program;

$course_title_fun = function ($program_id, $program_title) {
        $parent_course = Session::get('parent_course');
        $course_title = '';
        array_where($parent_course, function ($key, $value)
        use($program_id, &$course_title) {
            if ($program_id == $value['program_id']) {
                $course_title = $value['program_title'];
            }
        });
        return $course_title." - " .$program_title;
        };

?>
@if ($favorites)
@foreach($packets as $packet)
<?php $class=''; ?>
	<div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
		@if(in_array($packet['packet_id'], $expired_packets))
				<?php $class='pkt-opacity'; ?>
				<a title="Post Expired">
		@else
			<a href="{{URL::to('program/packet/'.$packet['packet_slug'])}}" title="{{$packet['packet_title']}}">
		@endif
		    <div class="packet {{$class}}">
		      	<figure>
					@if(empty($packet['packet_cover_media']))
						<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{str_limit($packet['packet_title'], $limit = 30, $end = '...')}}">
					@else
						<img src="{{URL::to('media_image/'.$packet['packet_cover_media'])}}" title="{{$packet['packet_title']}}" class="packet-img img-responsive" alt="{{str_limit($packet['packet_title'], $limit = 30, $end = '...')}}">
					@endif
					@if(in_array($packet['packet_id'], $array_packets['new']))
						<img class="new-label" src="{{URL::asset($theme.'/img/new-label.png')}}" alt="New label">
					@endif
					@if(in_array($packet['packet_id'], $array_packets['completed']))
						<span class="completed-overlay">
		              		<img src="{{URL::asset($theme.'/img/completed.png')}}" class="img-responsive" alt="Completed">
		              	</span>
					@endif
					 <!--package tooltip starts here-->
					<?php
					$program = Program::getFeedArray($packet['feed_slug']);
					$title = Program::getDashboardChannelPack($program[0]['program_id']);
					?>
					@if(!empty($title))
                    <a class="pckg-name tooltip tooltip-effect-1" href="#">
						<i class="fa fa-info"></i>
                          @if(strlen($title) > 10)
                          <span class="tooltip-content">{{substr($title,0,10)}}..</span>
                          @else
                          <span class="tooltip-content">{{$title}}</span>
                          @endif
						
					</a>
                    @endif
                    <!--package tooltip starts here-->
		     	</figure>
		      	<div>
		            
		            <p class="packet-title" title="{{$packet['packet_title']}}">
		            	{{str_limit($packet['packet_title'], $limit = 30, $end = '...')}}
		            </p>
					<p class="packet-data">
					  	<span class="gray">
					  		<?php
					  			$programs = Program::pluckFeedName($packet['feed_slug'])->toArray();
					  			$program = $programs[0];
								$program_title = $program['program_title'];
								if ($program['program_type'] == 'course') {
									$program_title = $course_title_fun($program['parent_id'],$program_title);
								}
					  		?>
					  		{{str_limit($program_title, $limit = 24, $end = '...')}}
					  		<br>
					  		{{count($packet['elements'])}} 
					  		@if (count($packet['elements']) <= 1)
					  			{{str_singular('items')}}
					  		@else 
					  			items 
					  		@endif
					  		<br>
					  	</span>
					  	<span class="l-gray font-12">
					  		{{ date('d M Y', strtotime(Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))))}}
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
@else
<p style="margin-top: 20px;" class="text-center"> {{trans('program.no_favorites')}} </p>
@endif
