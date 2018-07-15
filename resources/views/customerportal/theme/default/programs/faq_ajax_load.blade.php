<?php use App\Model\PacketFaqAnswers;?>
@foreach($public_ques as $public)
	<li class="media">
		<a class="pull-left" href="javascript:;">
		<img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px">
		</a>
		<div class="media-body todo-comment">
			<p class="todo-comment-head">
				<span class="todo-comment-username"><strong class="gray">{{$public['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($public['created_at'])}}</span>&nbsp;| <span id="like_count{{$public['id']}}">{{$public['like_count']}} @if($public['like_count'] == 1 || $public['like_count'] == 0) {{str_singular('Likes')}}@else Likes @endif</span>
			</p>
			<p class="todo-text-color">{{$public['question']}}&nbsp;&nbsp;
				@if(isset($public['users_liked']) && in_array(Auth::user()->uid, $public['users_liked']))
					<?php 
						$action="unlike";
						$class="blue"; 
					?>
				@else
					<?php 
						$action="like";
						$class="gray"; 
					?>
				@endif
				<span class="faq-like">
					<i id="{{$public['id']}}" data-action="{{$action}}" data-packet="{{$packet['packet_id']}}" class="fa fa-thumbs-up {{$class}} like-faq" style="cursor:pointer"></i>
				</span>	
			</p>
			<!-- Nested media object -->
			<?php 
				$answers=PacketFaqAnswers::getAnswersByQuestionID($public['id']);
			?>
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
		</div>
	</li>
@endforeach	

