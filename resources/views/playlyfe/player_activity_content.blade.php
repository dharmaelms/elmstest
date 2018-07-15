@foreach($playerActivity["data"]["activity_collection"] as $activity)
	@if(($activity["action_result"]["points"]["value"] > 0) || (!empty($activity["action_result"]["badges"])))
	<div class="player-activity">
		<div class="player-activity-info">
		@if($activity["action_id"] === "signup")
			You gained the following on registration
		@elseif($activity["action_id"] === "login")
			You gained the following on login
		@elseif($activity["action_id"] === "content_viewed")
			@if($activity["action_data"]["content_type"] === "video")
				You gained the following on watching "{{ $activity["action_data"]["content_name"] }}"
			@else
				You gained the following on viewing "{{ $activity["action_data"]["content_name"] }}"
			@endif
		@elseif($activity["action_id"] === "quiz_completed")
			You gained the following on completing quiz
		@elseif($activity["action_id"] === "question_asked")
			You gained the following on asking a question
		@elseif($activity["action_id"] === "question_marked_as_faq")
			You gained the following since the question you asked has been marked as FAQ
		@elseif($activity["action_id"] === "favorite")
			You gained the following since you favorited the channel
		@elseif($activity["action_id"] === "post_completed")
			You gained the following since you completed watching all items in the post
		@endif
		</div>
		<div class="player-activity-score">
			<div class="row">
			@if($activity["action_result"]["points"]["value"] > 0)
				<div class="col-sm-2 col-md-2">
					<b class="gained-points">&#43;{{ $activity["action_result"]["points"]["value"] }}</b>&nbsp;points
				</div>
			@endif
			@if(!empty($activity["action_result"]["badges"]))
				<div class="col-sm-10 col-md-10">
				@foreach($activity["action_result"]["badges"] as $badge)
					<div title="Badge : {{$badge["name"]}}" class="activity-tooltip">
						<b class="gained-points">&#43;{{$badge["value"]}}</b>&nbsp;&nbsp;<img src="/pl/image?size=small&amp;metric_id={{$badge["metric_info"]["id"]}}&amp;item={{$badge["name"]}}">&nbsp;&nbsp;
					</div>
				@endforeach
				</div>
			@endif
			</div>
		</div>
	</div>
	@endif
@endforeach