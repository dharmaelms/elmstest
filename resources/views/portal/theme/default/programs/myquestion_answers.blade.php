<script type="text/javascript" src="{{ URL::asset('/portal/theme/default/js/postq_and_a.js')}}"></script>
<?php use App\Model\PacketFaqAnswers;?>
@foreach($answers as $answer)
	<div class="media">  
		<a class="pull-left" href="javascript:;">
		<?php 
			$pic = (isset($answer['profile_pic']) && !empty($answer['profile_pic']) ) ? URL::asset(config('app.user_profile_pic') . $answer['profile_pic'] ) : URL::asset($theme.'/img/grey.png');
		?>
            <img alt="Profile Pic" class="img-circle" src="{{ $pic }}" height="27" width="27"/>
		</a>
		<div class="media-body">
			<p class="todo-comment-head">
				<span class="todo-comment-username"><strong class="gray">{{$answer['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($answer['created_at'])}}</span>
			</p>
			@if(strlen(html_entity_decode($answer['answer'])) >= PostChannels::MAX_ALLOWED_TEXT)
			<p class="todo-text-color">
				<span id="multiple_answer_if_more_char{{$answer['id']}}" class="answer_cls">{{$answer['answer']}}</span>
				<span id="multiple_answer{{$answer['id']}}" class="answer_hide_if_more_char">{{$answer['answer']}}</span>
				<a data-toggle="collapse" data-target="#multiple_answer{{$answer['id']}}" aria-expanded="false" aria-controls="multiple_answer" class="text-underline pull-right" onclick="click_answer_hide_show('multiple_answer_if_more_char{{$answer['id']}}'+' '+ 'multiple_answer{{$answer['id']}}')"><i class="fa fa-caret-down"></i></a>
			</p>
			@else
			<p class="todo-text-color">{{$answer['answer']}}</p>
			@endif
		</div>
	</div>
@endforeach
