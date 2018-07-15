<script type="text/javascript" src="{{ URL::asset('/portal/theme/default/js/postq_and_a.js')}}"></script>
<?php use App\Model\ChannelFaqAnswers; use App\Model\PacketFaqAnswers; use App\Model\Common;?>
<?php $i=0; ?>
@foreach($questions as $question)
    <?php $i=$i+1;?>
	<li class="media">
		<a class="pull-left" href="javascript:;">
			@if(Auth::user()->uid == $question['user_id'])
                <?php
                $pic = (isset(Auth::user()->profile_pic) && !empty(Auth::user()->profile_pic)) ? URL::asset(config('app.user_profile_pic') . Auth::user()->profile_pic ) : URL::asset($theme.'/img/green.png');
                ?>
				<img class="todo-userpic" src="{{ $pic }}" width="27px" height="27px" alt="User pic">
			@else
                <?php
                $pic = (isset($question['profile_pic']) && !empty($question['profile_pic'])) ? URL::asset(config('app.user_profile_pic') . $question['profile_pic'] ) : URL::asset($theme.'/img/green-light.png');
                ?>
				<img class="todo-userpic" src="{{ $pic }}" width="27px" height="27px" alt="User pic">
			@endif
		</a>
		<div class="media-body todo-comment">
			<p class="todo-comment-head">
				<span class="todo-comment-username"><strong class="gray">{{$question['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($question['created_at'])}}</span>&nbsp;| <span id="like_count{{$question['id']}}">{{$question['like_count']}} @if($question['like_count'] == 1 || $question['like_count'] == 0) Like @else Likes @endif</span>
			</p>
            <?php $edit_active="faq_inactive"; $faq_active="faq_active"; ?>
			@if(strlen(html_entity_decode($question['question'])) >= PostChannels::MAX_ALLOWED_TEXT)
				<span class="todo-text-color {{$faq_active}}" id="faq_sec{{$question['id']}}">
					<span id="update_ques{{$question['id']}}" class="update_ques_cls">{{$question['question']}}
					</span>
					<div id="update_question{{$question['id']}}" class="update_question_hide">{{$question['question']}}</div>
					<div>
						<a data-toggle="collapse" data-target="#update_question{{$question['id']}}" aria-expanded="false" aria-controls="update_question" class="text-underline pull-right" onclick="question_hide_show('update_ques{{$question["id"]}}'+' '+ 'update_question{{$question["id"]}}');"><i class="fa fa-caret-down"></i></a>
						@if(Auth::user()->uid == $question['user_id'])

							@if($question['status'] == 'ANSWERED' || $question['like_count'] != 0)
								<a style="cursor:default;color:#aaa" title="<?php echo Lang::get('program.channel_question_edit');?>"><i class="fa fa-edit font-16 blue"></i></a>&nbsp;
							@else
								<a class="channelfaq_edit pull-right" data-value="{{$question['id']}}" title="Edit Question"><i class="fa fa-edit font-16 blue"></i></a>&nbsp;
							@endif

							@if(($question['status'] == 'ANSWERED' || $question['like_count'] != 0))
								<a style="cursor:default;color:#aaa" title="<?php echo Lang::get('program.channel_question_delete');?>"><i class="fa fa-trash-o font-16 red"></i></a>
							@else
								<a class="channelfaq_delete pull-right" title="Delete Question" data-value="{{$question['id']}}" data-action="{{URL::to('program/channel-question-delete/'.$program_id.'/'.$question['id'])}}"><i class="fa fa-trash-o font-16 red"></i></a>
							@endif
						@else
							<a class="channelfaq_delete" title="Delete Question" data-value="{{$question['id']}}" data-action="{{URL::to('program/channel-question-delete/'.$program_id.'/'.$question['id'])}}"><i class="fa fa-trash-o font-16"></i></a>
							@if(isset($question['users_liked']) && in_array(Auth::user()->uid, $question['users_liked']))
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
							<span>
								<i id="{{$question['id']}}" data-action="{{$action}}" class="cursor-pointer fa fa-thumbs-up {{$class}} like-channelfaq"></i>
							</span>
						@endif
					</div>
				</span>
			@else
				<span class="todo-text-color {{$faq_active}}" id="faq_sec{{$question['id']}}">
					<span id="update_ques{{$question['id']}}">
						{{$question['question']}}
					</span>&nbsp;
					@if(Auth::user()->uid == $question['user_id'])

						@if($question['status'] == 'ANSWERED' || $question['like_count'] != 0)
							<a style="cursor:default;color:#aaa" title="<?php echo Lang::get('program.channel_question_edit');?>"><i class="fa fa-edit font-16 blue"></i></a>&nbsp;
						@else
							<a class="channelfaq_edit" data-value="{{$question['id']}}" title="Edit Question"><i class="fa fa-edit font-16 blue"></i></a>&nbsp;
						@endif

						@if(($question['status'] == 'ANSWERED' || $question['like_count'] != 0))
							<a style="cursor:default;color:#aaa" title="<?php echo Lang::get('program.channel_question_delete');?>"><i class="fa fa-trash-o font-16 red"></i></a>
						@else
							<a class="channelfaq_delete" title="Delete Question" data-value="{{$question['id']}}" data-action="{{URL::to('program/channel-question-delete/'.$program_id.'/'.$question['id'])}}"><i class="fa fa-trash-o font-16 red"></i></a>
						@endif
					@else
						<a class="channelfaq_delete" title="Delete Question" data-value="{{$question['id']}}" data-action="{{URL::to('program/channel-question-delete/'.$program_id.'/'.$question['id'])}}"><i class="fa fa-trash-o font-16 red"></i></a>
						@if(isset($question['users_liked']) && in_array(Auth::user()->uid, $question['users_liked']))
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
						<span>
							<i id="{{$question['id']}}" data-action="{{$action}}" class="cursor-pointer fa fa-thumbs-up {{$class}} like-channelfaq"></i>
						</span>
					@endif
				</span>
			@endif
			<span class="{{$edit_active}}" id="edit_faq{{$question['id']}}" >
				<a class="pull-left" href="javascript:;">
					<img class="todo-userpic" src="{{ $pic }}" width="27px" height="27px" alt="User pic">
				</a>
				<div class="media-body">
					<form action="">
						<div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
							<textarea class="form-control todo-taskbody-taskdesc" name="" rows="2">{{$question['question']}}</textarea>
							<span class="help-inline errorspan red"></span>
						</div>
						<div class="col-md-2 col-sm-3 col-xs-12 margin-top-10">
							<input type="hidden" id="question_value{{$question['id']}}" value="{{$question['question']}}">
							<a class="btn btn-primary btn-xs edit_submit" Title="Update" data-value="{{$question['id']}}" data-action="{{URL::to('program/channel-question-edit/'.$question['id'])}}" style="padding: 1px 5px;
    border-bottom: 0px;"><em class="fa fa-check"></em></a>
							<a class="btn btn-danger btn-xs edit_cancel" Title="Cancel" data-value="{{$question['id']}}"><em class="fa fa-remove"></em></a>
						</div>
					</form>
				</div>
			</span>
        <?php
        $answers=ChannelFaqAnswers::getAnswersByQuestionID($question['id']);
        Common::getUserProfilePicture($answers);
        ?>
		<!-- Nested media object -->
			<div id="answers_div{{$question['id']}}" class="media">
				@include('portal.theme.default.programs.channelquestion_answers', ['answers' => $answers, 'question_id' => $question['id']])
			</div>
			<div class="media">
				<a class="pull-left" href="javascript:;">
					@if(isset(Auth::user()->profile_pic) && !empty(Auth::user()->profile_pic))
						<img alt="User pic" class="todo-userpic" src="{{ URL::asset(config('app.user_profile_pic') . Auth::user()->profile_pic) }}" height="30" width="30"/>
					@else
						<img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px" alt="User pic">
					@endif
				</a>
				<div class="media-body">
					<form action="">
						<div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
							<textarea class="form-control todo-taskbody-taskdesc" name="" rows="2" placeholder="Type comment..." style="max-width:100%;"></textarea>
							<span class="help-inline errorspan red"></span>
						</div>
						<div class="col-md-2 col-sm-3 col-xs-12">
							<button type="button" data-value="{{$question['id']}}" class="btn btn-primary btn-sm ans_submit margin-top-10" data-action="{{URL::to('program/answer-channel/'.$question['id'])}}"><i class="fa fa-send"></i> Send</button>
						</div>
					</form>
				</div>
			</div>
		</div>
	</li>
@endforeach