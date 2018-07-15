<?php use App\Model\PacketFaqAnswers;?>
@foreach($answers as $answer)
	<div class="media">
		<a class="pull-left" href="javascript:;">
		<img class="todo-userpic" src="{{URL::asset($theme.'/img/grey.png')}}" width="27px" height="27px">
		</a>
		<div class="media-body">
			<p class="todo-comment-head">
				<span class="todo-comment-username"><strong class="gray">{{$answer['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($answer['created_at'])}}</span>
			</p>
			<p class="todo-text-color">{{$answer['answer']}}</p>
		</div>
	</div>
@endforeach