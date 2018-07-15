<script type="text/javascript" src="{{ URL::asset('/portal/theme/default/js/postq_and_a.js')}}"></script>
<?php use App\Model\PacketFaqAnswers;?>
<?php $i=0; ?>
@foreach($user_ques as $user)
    <?php $i=$i+1;
    $pic = (isset($user['profile_pic']) && !empty($user['profile_pic']) ) ? URL::asset(config('app.user_profile_pic') . $user['profile_pic'] ) : URL::asset($theme.'/img/green.png');

    ?>
    <li class="media">
        <a class="pull-left" href="javascript:;">
            <img alt="User pic" class="todo-userpic" src="{{$pic }}" height="27" width="27"/>
        </a>
        <div class="media-body todo-comment">
            <p class="todo-comment-head">
                <span class="todo-comment-username"><strong class="gray">{{$user['created_by_name']}}</strong></span> &nbsp;| <span class="todo-comment-date">{{PacketFaqAnswers::getDisplayDate($user['created_at'])}}</span>&nbsp;| <span>{{$user['like_count']}} @if($user['like_count'] == 1 || $user['like_count'] == 0) Like @else Likes @endif</span>
            </p>
            <?php 
                $public_answers=PacketFaqAnswers::getAnswersByQuestionID($user['id'], $user['user_id']);
                Common::getUserProfilePicture($public_answers);
            ?>
            <?php $edit_active="faq_inactive"; $faq_active="faq_active"; ?>
            @if(strlen(html_entity_decode($user['question'])) >= PostChannels::MAX_ALLOWED_TEXT)
                <span class="todo-text-color {{$faq_active}}" id="faq_sec{{$user['id']}}">
                    <span id="update_ques{{$user['id']}}" class="update_ques_cls">
                        {{$user['question']}}
                    </span>
                    <div id="update_question{{$user['id']}}" class="update_question_hide">{{$user['question']}}</div>
                    <a data-toggle="collapse" data-target="#update_question{{$user['id']}}" aria-expanded="false" aria-controls="update_question" class="text-underline pull-right" onclick="question_hide_show('update_ques{{$user["id"]}}'+' '+ 'update_question{{$user["id"]}}');"><i class="fa fa-caret-down"></i></a>
                    <div>
                        @if( $user['user_id'] == Auth::user()->uid)
                            <a class="faq_edit" data-value="{{$user['id']}}" title="Edit Question"><i class="fa fa-edit pull-right blue"></i></a>&nbsp;
                            <a class="faq_delete" title="Delete Question" data-value="{{$user['id']}}" data-action="{{URL::to('program/question-delete/'.$user['id'].'/'.$user['user_id'])}}"><i class="fa fa-trash-o pull-right font-16 red"></i></a>
                        @endif
                    </div>
                </span>
            @else
                <span class="todo-text-color {{$faq_active}}" id="faq_sec{{$user['id']}}">
                    <span id="update_ques{{$user['id']}}">
                        {{$user['question']}}
                    </span>&nbsp;
                    @if( $user['user_id'] == Auth::user()->uid)
                        <a class="faq_edit" data-value="{{$user['id']}}" title="Edit Question"><i class="fa fa-edit font-16 blue"></i></a>&nbsp;
                        <a class="faq_delete" title="Delete Question" data-value="{{$user['id']}}" data-action="{{URL::to('program/question-delete/'.$user['id'].'/'.$user['user_id'])}}"><i class="fa fa-trash-o font-16 red"></i></a>
                    @endif
                </span>
            @endif
            <span class="{{$edit_active}}" id="edit_faq{{$user['id']}}" >
                <a class="pull-left" href="javascript:;">
                @if(isset(Auth::user()->profile_pic) && !empty(Auth::user()->profile_pic))
                    <img class="todo-userpic" src="{{URL::asset(config('app.user_profile_pic') . Auth::user()->profile_pic)}}" width="27px" height="27px" alt="User pic">
                @else
                    <img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px" alt="User pic">
                @endif
                </a>
                <div class="media-body">
                <form action="{{URL::to('program/question-edit/'.$user['id'].'/'.$user['user_id'])}}">
                    <div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
                        <textarea class="form-control todo-taskbody-taskdesc" name="" rows="4">{{$user['question']}}</textarea>
                        <span class="help-inline errorspan red"></span>
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
                $answers=PacketFaqAnswers::getAnswersByQuestionID($user['id'])->toArray();
                $answers = Common::getUserProfilePicture($answers);
            ?>
            <!-- Nested media object -->
            <div id="answers_div{{$user['id']}}" class="media">
                @include('portal.theme.default.programs.myquestion_answers', ['answers' => $answers])
            </div>
            <div class="media">
                <a class="pull-left" href="javascript:;">
                @if(isset(Auth::user()->profile_pic) && !empty(Auth::user()->profile_pic))
                    <img alt="Profile pic" class="img-circle" src="{{ URL::asset(config('app.user_profile_pic') . Auth::user()->profile_pic) }}" height="30" width="30"/>
                @else
                    <img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px" alt="User pic">
                @endif
                </a>
                <div class="media-body">
                    <form action="{{URL::to('program/answer/'.$user['id'])}}">
                        <div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
                            <textarea class="form-control todo-taskbody-taskdesc" name="" rows="2" placeholder="Type comment..."></textarea>
                            <span class="help-inline errorspan red"></span>
                        </div>
                        <div class="col-md-2 col-sm-3 col-xs-12 margin-top-10">
                            <button type="button" data-value="{{$user['id']}}" class="btn btn-primary ans_submit btn-sm" data-action="{{URL::to('program/answer/'.$user['id'])}}" value="Send"><i class="fa fa-send"></i> Send</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </li>
@endforeach

