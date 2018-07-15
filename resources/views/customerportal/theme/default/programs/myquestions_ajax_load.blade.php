<?php use App\Model\PacketFaqAnswers;?>
<?php $i=0; ?>
@foreach($user_ques as $user)
	<?php $i=$i+1;?>
	<li class="media">
		<a class="pull-left" href="javascript:;">
		<img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px">
		</a>
		<div class="media-body todo-comment">
			<p class="todo-comment-head">
				<span class="todo-comment-username"><strong class="gray">{{$user['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($user['created_at'])}}</span>&nbsp;| <span>{{$user['like_count']}} @if($user['like_count'] == 1 || $user['like_count'] == 0) Like @else Likes @endif</span>
			</p>
			<?php 
				$public_answers=PacketFaqAnswers::getAnswersByQuestionID($user['id'], $user['user_id']);
			?>
			<?php $edit_active="faq_inactive"; $faq_active="faq_active"; ?>
			<span class="todo-text-color {{$faq_active}}" id="faq_sec{{$user['id']}}">
				<span id="update_ques{{$user['id']}}">
					{{$user['question']}}
				</span>&nbsp;
				@if(count($public_answers) > 0)
					<a style="cursor:default;color:#aaa" title="<?php echo Lang::get('program.faq_edit');?>"><i class="fa fa-edit font-20 blue"></i></a>&nbsp;
					<a style="cursor:default;color:#aaa" title="<?php echo Lang::get('program.faq_delete');?>"><i class="fa fa-trash-o font-20 red"></i></a>
				@else
					<a class="faq_edit" data-value="{{$user['id']}}" title="Edit Question"><i class="fa fa-edit blue"></i></a>&nbsp;
					<a class="faq_delete" title="Delete Question" data-value="{{$user['id']}}" data-action="{{URL::to('program/question-delete/'.$user['id'].'/'.$user['user_id'])}}"><i class="fa fa-trash-o font-20 red"></i></a>
				@endif
			</span>
			<span class="{{$edit_active}}" id="edit_faq{{$user['id']}}" >
				<a class="pull-left" href="javascript:;">
					<img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px">
				</a>
				<div class="media-body">
				<form action="{{URL::to('program/question-edit/'.$user['id'].'/'.$user['user_id'])}}">
					<div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
						<textarea class="form-control todo-taskbody-taskdesc" name="" rows="1">{{$user['question']}}</textarea>
						<span class="help-inline errorspan" style="color:#f00"></span>
					</div>
					<div class="col-md-2 col-sm-3 col-xs-12">
						<input type="hidden" id="question_value{{$user['id']}}" value="{{$user['question']}}">
						<a class="btn btn-default btn-xs edit_submit" Title="Update" data-value="{{$user['id']}}" data-action="{{URL::to('program/question-edit/'.$user['id'].'/'.$user['user_id'])}}"><em class="fa fa-check"></em></a>
						<a class="btn btn-default btn-xs edit_cancel" Title="Cancel" data-value="{{$user['id']}}"><em class="fa fa-remove"></em></a>
					</div>
				</form>
				</div>
			</span>

			<?php 
				$answers=PacketFaqAnswers::getAnswersByQuestionID($user['id']);
			?>
			<!-- Nested media object -->
			<div id="answers_div{{$user['id']}}" class="media">
				@include('portal.theme.default.programs.myquestion_answers', ['answers' => $answers])
			</div>
			<div class="media">
				<a class="pull-left" href="javascript:;">
					<img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px">
				</a>
				<div class="media-body">
					<form action="{{URL::to('program/answer/'.$user['id'])}}">
						<div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
							<textarea class="form-control todo-taskbody-taskdesc" name="" rows="1" placeholder="Type comment..."></textarea>
							<span class="help-inline errorspan" style="color:#f00"></span>
						</div>
						<div class="col-md-2 col-sm-3 col-xs-12">
							@if($manage_questions==false)
								<input type="button" data-value="{{$user['id']}}" class="btn red-sunglo ans_submit" data-action="{{URL::to('program/answer/'.$user['id'])}}" value="Send">
							@else
								<input type="button" data-value="{{$user['id']}}" class="btn red-sunglo ans_submit" data-action="{{URL::to('program/answer/'.$user['id'])}}?admin=1" value="Send">
						    @endif
						</div>
					</form>
				</div>
			</div>
		</div>
	</li>
@endforeach