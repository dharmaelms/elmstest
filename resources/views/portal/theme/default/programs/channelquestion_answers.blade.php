<script type="text/javascript" src="{{ URL::asset('/portal/theme/default/js/postq_and_a.js')}}"></script>
<?php use App\Model\PacketFaqAnswers;?>
	@if(count($answers) > 1)
		<div class="media">
			<a class="pull-left" href="javascript:;">
				<?php $pic = (isset($answers[0]['profile_pic']) && !empty($answers[0]['profile_pic']) ) ? URL::asset(config('app.user_profile_pic') . $answers[0]['profile_pic'] ) : URL::asset($theme.'/img/grey.png');
			?>
			<img class="img-circle" src="{{ $pic }}" width="27px" height="27px">
			</a>
			<div class="media-body">
				<p class="todo-comment-head">
					<span class="todo-comment-username"><strong class="gray">{{$answers[0]['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($answers[0]['created_at'])}}</span>
				</p>
				@if(strlen(html_entity_decode(array_get($answers, '0.answer'))) >= PostChannels::MAX_ALLOWED_TEXT)
					<p class="todo-text-color">
						<span id="first_answer_if_more_char{{array_get($answers, '0.id')}}" class="answer_cls">{{array_get($answers, '0.answer')}}
						</span>
						<span id="first_answer{{array_get($answers, '0.id')}}" class="answer_hide_if_more_char">{{array_get($answers, '0.answer')}}</span>
						<div>
							<a data-toggle="collapse" data-target="#first_answer{{array_get($answers, '0.id')}}" aria-expanded="false" aria-controls="first_answer" class="text-underline pull-right" onclick="click_answer_hide_show('first_answer_if_more_char{{array_get($answers, '0.id')}}'+' '+ 'first_answer{{array_get($answers, '0.id')}}');"><i class="fa fa-caret-down"></i></a>

						<a class="answer_delete pull-right" title="Delete Answer" data-value="{{$question_id}}"
						   data-action="{{URL::to('program/channel-answer-delete/'.$question_id.'/'.$answers[0]['id'])}}">
							<i class="fa fa-trash-o red font-16"></i>
						</a>
						</div>
					</p>
				@else
					<p class="todo-text-color">{{$answers[0]['answer']}}
						<a class="answer_delete" title="Delete Answer" data-value="{{$question_id}}"
						   data-action="{{URL::to('program/channel-answer-delete/'.$question_id.'/'.$answers[0]['id'])}}">
							<i class="fa fa-trash-o red font-16"></i>
						</a>
					</p>
				@endif
			</div>
		</div>
		<p>
			<!-- <button class="btn btn-primary" type="button" data-toggle="collapse" data-target="#collapseExample" aria-expanded="false" aria-controls="collapseExample">{{count($answers)-1}} more comment(s)</button> -->
			<a data-toggle="collapse" data-target="#collapseExample{{$question_id}}" aria-expanded="false" aria-controls="collapseExample" class="text-underline"><strong>{{count($answers)-1}} more comment(s) <i class="fa fa-caret-down"></i></strong></a>
		</p>
		<div class="collapse" id="collapseExample{{$question_id}}">
		  	<div class="card card-block">
		  		<?php $i = 1; ?>
		    	@foreach($answers as $answer)
		    		@if($i > 1)
						<div class="media">
							<a class="pull-left" href="javascript:;">
							<?php 
								$pic = (isset($answer['profile_pic']) && !empty($answer['profile_pic']) ) ? URL::asset(config('app.user_profile_pic') . $answer['profile_pic'] ) : URL::asset($theme.'/img/grey.png');
							?>
							<img class="img-circle" src="{{ $pic }}" width="27px" height="27px" alt="User pic">
							</a>
							<div class="media-body">
								<p class="todo-comment-head">
									<span class="todo-comment-username"><strong class="gray">{{$answer['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($answer['created_at'])}}</span>
								</p>
								@if(strlen(html_entity_decode($answer['answer'])) >= PostChannels::MAX_ALLOWED_TEXT)
									<p class="todo-text-color">
									<span id="multiple_answer_if_more_char{{$answer['id']}}" class="answer_cls">{{$answer['answer']}}
									</span>
									<span id="multiple_answer{{$answer['id']}}" class="answer_hide_if_more_char">{{$answer['answer']}}</span>
									<div>
										<a data-toggle="collapse" data-target="#multiple_answer{{$answer['id']}}" aria-expanded="false" aria-controls="multiple_answer" class="text-underline pull-right" onclick="click_answer_hide_show('multiple_answer_if_more_char{{$answer['id']}}'+' '+ 'multiple_answer{{$answer['id']}}')"><i class="fa fa-caret-down"></i></a>

										<a class="answer_delete pull-right" title="Delete Answer" data-value="{{$question_id}}" data-action="{{URL::to('program/channel-answer-delete/'.$question_id.'/'.$answer['id'])}}"><i class="fa fa-trash-o redfont-16"></i></a>
									</div>
								</p>
								@else
									<p class="todo-text-color">{{$answer['answer']}}
										<a class="answer_delete" title="Delete Answer" data-value="{{$question_id}}" data-action="{{URL::to('program/channel-answer-delete/'.$question_id.'/'.$answer['id'])}}"><i class="fa fa-trash-o red font-16"></i></a>
								</p>
								@endif
							</div>
						</div>
					@endif
					<?php $i =$i+1; ?>
				@endforeach
		  	</div>
		</div>
	@else
		@foreach($answers as $answer)
		<?php 
			$pic = (isset($answer['profile_pic']) && !empty($answer['profile_pic'])) ? URL::asset(config('app.user_profile_pic') . $answer['profile_pic'] ) : URL::asset($theme.'/img/grey.png');
		?>
			<div class="media">
				<a class="pull-left" href="javascript:;">
				<img class="img-circle" src="{{ $pic }}" width="27px" height="27px" alt="User pic">
				</a>
				<div class="media-body">
					<p class="todo-comment-head">
						<span class="todo-comment-username"><strong class="gray">{{$answer['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($answer['created_at'])}}</span>
					</p>
					@if(strlen(html_entity_decode($answer['answer'])) >= PostChannels::MAX_ALLOWED_TEXT)
						<p class="todo-text-color">
							<span id="answer_if_more_char{{$answer['id']}}" class="answer_cls">{{$answer['answer']}}
							</span>
							<span id="answer{{$answer['id']}}" class="answer_hide_if_more_char">{{$answer['answer']}}</span>
							<div>
								<a data-toggle="collapse" data-target="#answer{{$answer['id']}}" aria-expanded="false" aria-controls="answer" class="text-underline pull-right" onclick="click_answer_hide_show('answer_if_more_char{{$answer['id']}}'+' '+ 'answer{{$answer['id']}}');"><i class="fa fa-caret-down"></i></a>

							<a class="answer_delete pull-right" title="Delete Answer" data-value="{{$question_id}}" data-action="{{URL::to('program/channel-answer-delete/'.$question_id.'/'.$answer['id'])}}"><i class="fa fa-trash-o red font-16"></i></a>
							</div>
					    </p>
					@else
						<p class="todo-text-color">{{$answer['answer']}}
							<a class="answer_delete" title="Delete Answer" data-value="{{$question_id}}" data-action="{{URL::to('program/channel-answer-delete/'.$question_id.'/'.$answer['id'])}}"><i class="fa fa-trash-o red font-16"></i></a>
						</p>
					@endif
				</div>
			</div>
		@endforeach
	@endif
	

