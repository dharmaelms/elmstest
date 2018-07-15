@section('content')
<link rel="stylesheet" href="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/css/bootstrap.min.css">
<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.0/jquery.min.js"></script>
<script src="//maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>


<style type="text/css">
	table tr > td{
		color: dimgray;
	}
</style>
	<div class="page-bar margin-left-10">
		<ul class="page-breadcrumb">
			<li><a href="{{url('dashboard')}}"><?php echo Lang::get('dashboard.dashboard');?></a><i class="fa fa-angle-right"></i></li>
			@if($attempts->count() > 0)
			<li><a href="{{url('/assessment?filter=attempted')}}"><?php echo Lang::get('assessment.assessment');?></a><i class="fa fa-angle-right"></i></li>
			@else
			<li><a href="{{url('assessment')}}"><?php echo Lang::get('assessment.assessment');?></a><i class="fa fa-angle-right"></i></li>
			@endif
			<li><a href="#">{{$quiz->quiz_name}}</a></li>
		</ul>
	</div>
	<div class="row md-margin quiz-details">
		<div class="col-md-8 col-sm-7 col-xs-12">
			<div class="custom-box">
				<h3 class="page-title-small margin-top-0 text-capitalize">
					{{ $quiz->quiz_name }} 
					&nbsp;
					@if(isset($quiz->users_liked))
						@if(in_array(Auth::user()->uid, $quiz->users_liked))
							<!--<i id="{{$quiz->quiz_id}}" data-action="unstar" class="fa fa-star font-20 yellow star-quiz" style="cursor:pointer"></i>-->
						@else
							<!--<i id="{{$quiz->quiz_id}}" data-action="star" class="fa fa-star font-20 l-gray star-quiz" style="cursor:pointer"></i>-->
						@endif
					@else
						<!--<i id="{{$quiz->quiz_id}}" data-action="star" class="fa fa-star font-20 l-gray star-quiz" style="cursor:pointer"></i>-->
					@endif	
					@if(isset($quiz->quiz_description) && !empty($quiz->quiz_description))
						<a class="btn l-gray info-btn" title="{{Lang::get('assessment.instructions')}}"><i class="fa fa-question"></i></a>
					@endif
				</h3>
				<div>
					<div class="col-lg-4 col-md-4 col-sm-12 col-xs-12 sm-margin">
						<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-01.png') }}" class="img-responsive center center-align" width="180px" alt="{{ $quiz->quiz_name }}">
						<br><br>
						@if(isset($quiz->type) && $quiz->type == QuizType::QUESTION_GENERATOR)
							<div class="col-lg-12 col-sm-12 col-xs-12 xs-margin">
								@if(count($quiz->questions) == 0)
									<a href="javascript:;" class="btn btn-default1 btn-lg">START </a><br>
								@elseif((isset($replace_qdate['start_time']) &&  $replace_qdate['start_time'] == 0|| Timezone::getTimeStamp($replace_qdate['start_time']) < time()) && ( isset($replace_qdate['end_time']) && $replace_qdate['end_time'] == 0 || Timezone::getTimeStamp($replace_qdate['end_time']) > time()))
									@if((is_null($attempt_pp) || $attempt_pp->status == 'OPENED') && is_null($attempt_pp) || $attempt_pp->status != 'CLOSED')
										<form action="{{ url('assessment/question-generator/'.$quiz->quiz_id).'?'.$requestUrl }}" method="POST" accept-charset="utf-8">
											<input type="hidden" name="return" value="{{ Request::path() }}">
											
											@if(isset($attempt_pp->status) && $attempt_pp->status == 'OPENED')	
											<button type="submit" class="btn btn-success btn-lg"><?php echo Lang::get('assessment.resume');?></button>
											@else
											<button type="submit" class="btn btn-success btn-lg" ><?php echo Lang::get('assessment.start');?> </button>
											@endif
										</form>
									@else
										<a href="javascript:;" class="btn btn-default1 btn-lg">


										 <?php echo Lang::get('assessment.completed');?></a>
									@endif
								@else
									<a href="javascript:;" class="btn btn-default1 btn-lg"><?php echo Lang::get('assessment.start');?> </a>
								@endif
							</div>
						@else
							<div class="sm-margin center-align center">
								<!-- <a href="javascript:;" class="btn btn-gray">Completed</a>
								<p>No attempt left</p> -->
								<?php 
									$closed = $attempts->where('status','CLOSED');
									
									if($quiz->attempts == 0){
										$attempt_left = Lang::get('assessment.no_attempt_limit');
									}
									else{
										$attempt_left_count = ($quiz->attempts - $attempts->count());
										if($attempt_left_count == 1){
											$attempt_left = $attempt_left_count.' '.Lang::get('assessment.attempt_left');
										}else{
											$attempt_left = $attempt_left_count.' '.Lang::get('assessment.attempts_left');
										}
									}
								?>
								<div class="col-lg-12 col-sm-12 col-xs-12 xs-margin">
									@if(count($quiz->questions) == 0)
										<a href="javascript:;" class="btn btn-default1 btn-lg">START QUIZ</a><br><span class="font-13">{{ $attempt_left }}</span>
									@elseif((isset($replace_qdate['start_time']) &&  $replace_qdate['start_time'] == 0|| Timezone::getTimeStamp($replace_qdate['start_time']) < time()) && ( isset($replace_qdate['end_time']) && $replace_qdate['end_time'] == 0 || Timezone::getTimeStamp($replace_qdate['end_time']) > time()))
										@if($quiz->attempts == 0 || $quiz->attempts > $attempts->where('status','CLOSED')->count())
											<form id="quiz-attempt" action="{{ url('assessment/start-attempt/'.$quiz->quiz_id).'?'.$requestUrl }}" method="POST" accept-charset="utf-8">
												<input type="hidden" name="return" value="{{ Request::path() }}">
												@if( isset($yet_closed_attempt->status) && $yet_closed_attempt->status == 'OPENED')
												<button type="submit" class="btn btn-success btn-lg"><?php echo Lang::get('assessment.resume');?></button><br><span class="font-13">{{ $attempt_left }}</span>
												@else
												<a data-id="{{ $quiz->quiz_id }}" class="btn btn-success btn-md begin"><i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo Lang::get('assessment.begin_quiz');?> </a><br><span class="font-13">{{ $attempt_left }}</span>
												@endif
											</form>
										@else
											<a href="javascript:;" class="btn btn-default1 btn-lg"><i class="fa fa-check green font-16
											"></i> <?php echo Lang::get('assessment.completed');?></a><br><span class="font-13"><?php echo Lang::get('assessment.no_attempts_eft');?></span>
										@endif
									@else
										<a href="javascript:;" class="btn btn-success btn-lg"><i class="fa fa-long-arrow-right" aria-hidden="true"></i> <?php echo Lang::get('assessment.start_quiz');?></a><br><span class="font-13">{{ $attempt_left }}</span>
									@endif
								</div>
							</div>
						@endif
					</div>
					<div class="col-lg-8 col-md-8 col-sm-12 col-xs-12" id='col_mid'>
						<div class="font-14 sm-margin" style="background: beige;
    border: 1px dotted #ada7a7;">
							<table>
								<tbody>
									<tr>
										<td width="80px"><span class="start margin-left-10"><?php echo Lang::get('assessment.starts');?>:</span></td>
										<td>
											@if(isset($replace_qdate['start_time']) && $replace_qdate['start_time'] != 0)
												{{Timezone::convertFromUTC('@'.$replace_qdate['start_time'], Auth::user()->timezone, 'D, d  M Y, g:i a')}}
											@else
												@if(!empty($quiz->start_time))
													{{ $quiz->start_time->timezone(Auth::user()->timezone)->format('D, d M, h:i A') }}
												@else
													{{ Lang::get('assessment/detail.not_available') }}
												@endif
											@endif
										</td>
									</tr>
									<tr>
										<td width="80px"><span class="end margin-left-10"><?php echo Lang::get('assessment.ends');?>:</span></td>
										<td>
											@if(isset($replace_qdate['end_time']) && $replace_qdate['end_time'] != 0)
												{{Timezone::convertFromUTC('@'.$replace_qdate['end_time'], Auth::user()->timezone, 'D, d  M Y, g:i a')}}
											@else
												@if(!empty($quiz->end_time))
													{{ $quiz->end_time->timezone(Auth::user()->timezone)->format('D, d M, h:i A') }}
												@else
													{{ Lang::get('assessment/detail.not_available') }}
												@endif
											@endif
										</td>
									</tr>
									@if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR))
										<tr>
											<td width="200px"><span class="black margin-left-10">{{ Lang::get('assessment/detail.no_of_questions') }}</span></td>
											<td>{{ count($quiz->questions) }}</td>
										</tr>
										<tr>
											<td width="200px"><span class="black margin-left-10">{{ Lang::get('assessment/detail.no_of_marks') }}</span></td>
											<td>{{ $total_marks }}</td>
										</tr>
									@endif
								</tbody>
							</table>
						</div>
						<div class="font-16 xs-margin">
							<table>
								<tbody>
								@if($quiz->duration != 0)
									<tr>
										<td width="25"><img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-02.png') }}" width="15px" alt="Duration" class="duration-icon"></td>
										<td>{{ $quiz->duration.' mins' }}</td>
									</tr>
									@endif
									@if(!$program->isEmpty())
										<tr>
											<td width="25" valign="top"><i class="fa fa-rss" ></i></td>
											<td>
											<?php $p_name = ''; ?>
												@foreach($program as $p)
													@if(isset($pack_name[$p->program_slug]))
														<span class="gray" title="{{implode(',' ,$pack_name[$p->program_slug])}}">
														<?php $p_name = $p_name.' '.str_limit($p->program_title, 40).', '; ?>
														</span>
													@endif
												@endforeach {!!rtrim($p_name, ', ')!!}
											</td>
										</tr>
									@endif
									<!-- Channel name and Back to post button  -->
									<!--  <tr>
										<td width="25" valign="top"><i class="fa fa-archive"></i></td>
										<td>
											<span class="gray" title="Post Name">Posts Names 1, Posts Name 2, Posts Name3</span>
										</td>
									</tr> -->
									@if( !empty($requestUrl) )
									<tr>
										<td width="25" valign="top"></td>
										<td>
											<a href="{{Url::to('/'.Input::get('requestUrl')) }}">Back to Post</a>
										</td>
									</tr>
									@endif 
								</tbody>
							</table>
						</div>
						@if(!isset($quiz->type) && (isset($quiz->type) || $quiz->type != QuizType::QUESTION_GENERATOR) && !is_null($last_attempt) && (isset($quiz->is_score_display)) && ($quiz->is_score_display == true))
						<div class="xs-margin center-align center">
							<h3><strong><?php echo Lang::get('assessment.score');?></strong></h3>
							<!--  Container  -->
							<div class="progress_cir">
							  <!--  Item  -->
							  <li data-name="" data-percent="{{ $last_attempt->obtained_mark}}/{{ $last_attempt->total_mark}}"> <svg viewBox="-10 -10 220 220">
							    <g fill="none" stroke-width="15" transform="translate(100,100)">
							      <path d="M 0,-100 A 100,100 0 0,1 86.6,-50" stroke="url(#cl1)"/>
							      <path d="M 86.6,-50 A 100,100 0 0,1 86.6,50" stroke="url(#cl2)"/>
							      <path d="M 86.6,50 A 100,100 0 0,1 0,100" stroke="url(#cl3)"/>
							      <path d="M 0,100 A 100,100 0 0,1 -86.6,50" stroke="url(#cl4)"/>
							      <path d="M -86.6,50 A 100,100 0 0,1 -86.6,-50" stroke="url(#cl5)"/>
							      <path d="M -86.6,-50 A 100,100 0 0,1 0,-100" stroke="url(#cl6)"/>
							    </g>
							    </svg> <svg viewBox="-10 -10 220 220">
							    <path d="M200,100 C200,44.771525 155.228475,0 100,0 C44.771525,0 0,44.771525 0,100 C0,155.228475 44.771525,200 100,200 C155.228475,200 200,155.228475 200,100 Z" stroke-dashoffset="{{($last_attempt->obtained_mark/$last_attempt->total_mark)*100*6.29}}"></path>
							    </svg> </li>
							</div>
							<!--  Defining Angle Gradient Colors  --> 
							@if($last_attempt->obtained_mark >= 0) 
								<?php $color = '#02FA4D' ?>
							@else
								<?php $color = '#FF0000' ?>
							@endif
							<svg width="0" height="0">
							  <defs>
							    <linearGradient id="cl1" gradientUnits="objectBoundingBox" x1="0" y1="0" x2="1" y2="1">
							      <stop stop-color="{{ $color }}"/>
							      <stop offset="100%" stop-color="{{ $color }}"/>
							    </linearGradient>
							    <linearGradient id="cl2" gradientUnits="objectBoundingBox" x1="0" y1="0" x2="0" y2="1">
							      <stop stop-color="{{ $color }}"/>
							      <stop offset="100%" stop-color="{{ $color }}"/>
							    </linearGradient>
							    <linearGradient id="cl3" gradientUnits="objectBoundingBox" x1="1" y1="0" x2="0" y2="1">
							      <stop stop-color="{{ $color }}"/>
							      <stop offset="100%" stop-color="{{ $color }}"/>
							    </linearGradient>
							    <linearGradient id="cl4" gradientUnits="objectBoundingBox" x1="1" y1="1" x2="0" y2="0">
							      <stop stop-color="{{ $color }}"/>
							      <stop offset="100%" stop-color="{{ $color }}"/>
							    </linearGradient>
							    <linearGradient id="cl5" gradientUnits="objectBoundingBox" x1="0" y1="1" x2="0" y2="0">
							      <stop stop-color="{{ $color }}"/>
							      <stop offset="100%" stop-color="{{ $color }}"/>
							    </linearGradient>
							    <linearGradient id="cl6" gradientUnits="objectBoundingBox" x1="0" y1="1" x2="1" y2="0">
							      <stop stop-color="{{ $color }}"/>
							      <stop offset="100%" stop-color="{{ $color }}"/>
							    </linearGradient>
							  </defs>
							</svg>
							</div>
							@endif
						</div>
				  </div>
			</div>
			@if(isset($quiz->type) && $quiz->type == QuizType::QUESTION_GENERATOR)
				<div class="col-md-12 qg-data-box">
					<div>
						<h3 class="center black"><?php echo Lang::get('assessment.total_questions');?>: <span class="font-32 red-light">
						@if($quiz->total_question_limit < $totalQuestions)
						{{($last_attempt_data_count)}}/{{(int)$quiz->total_question_limit}}
						@else
						{{($last_attempt_data_count)}}/{{(int)$totalQuestions}} 
						@endif</span> <?php echo Lang::get('assessment.completed_s');?></h3>
						@if(!is_null($sections) && !empty($sections))
							<?php $flag = true;?>	
							<ul class="md-margin">
							@foreach($sections as $section)
							<?php
								if($flag == true){
									?>
									<p><?php echo Lang::get('assessment.available_sections');?></p>
									
									<?php
									$flag = false;
								}
							?>
								<li>
									{{$section->title}}
								</li>

							@endforeach
							</ul>
						@endif
					</div>
				</div>

			@endif
		</div>	
		@if(isset($quiz->type) && $quiz->type == QuizType::QUESTION_GENERATOR &&!is_null($attempt_pp))
			<div class="col-md-4 col-sm-5 col-xs-12">
				<div class="custom-box">
					<h4 class="margin-top-0 border-btm padding-btm-10 blue text-capitalize" style="line-height: 1.3;    font-weight: 600;">
						<?php echo Lang::get('assessment.attempt_details');?>
					</h4>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-xs-6 border-bottom-gray sm-margin" title="Total time taken for completing the quiz" style="background: #00008b2e;">
						<div class="xs-margin"><span class="pull-left"><img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-03.png') }}" alt="Total time spent" class="img-inline" width="40px"></span> <span class="right-div-count"><?php echo Lang::get('assessment.time_spent');?></span></div>
						<div class="xs-margin center">
							<div class="font-32 orange"><!-- need work here fo prac plat -->
							@if(isset($time_spend))
								{{$time_spend}}
							@else
								<?php echo Lang::get('assessment.00_00_00'); ?>
							@endif
							</div>
							<div class="gray font-12"><?php echo Lang::get('assessment.hh_mm_ss'); ?></div>
						</div>
					</div>
					<div class="col-md-6 col-sm-6 col-xs-6 border-bottom-gray sm-margin" title="Average time taken per question" style="background: bisque;">
						<div class="xs-margin">
							<span class="pull-left"><img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-04.png') }}" alt="Speed" class="img-inline" width="40px"></span> <span class="right-div-count"><?php echo Lang::get('assessment.speed');?></span>
						</div>
						<div class="xs-margin center">
							<div class="font-32 red-light">
							@if(isset($time_speed))
								{{$time_speed}}
							@else
								<?php echo Lang::get('assessment.00_00'); ?>
							@endif
							</div>
							<div class="gray font-12"><?php echo Lang::get('assessment.mm_ss'); ?></div>
						</div>
					</div>
				</div>

				<div class="row col-md-12 sm-margin" title="Break up of correct({{$correct_count}}), incorrect({{$incorrect_count}}) and skipped questions({{$skip_count}})">
					<div class="left-div1 center">
						<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-06.png') }}" alt="Marks" class="img-inline" width="40px"><br>
						<span><?php echo Lang::get('assessment.split');?></span>
					</div>
					<div class="right-div1">
						<div class="progress custom-progress" style="margin-bottom:4px;">
							<div class="progress-bar progress-bar-success progress-bar-striped active progress-bar-animated" style="width: {{$correct_per}}%">
								<span >{{$correct_count}}</span>
							</div>
							<div class="progress-bar progress-bar-danger progress-bar-striped active progress-bar-animated" style="width: {{$incorrect_per}}%">
								<span >{{$incorrect_count}}</span>
							</div>
			   		</div>
			   		<div class="font-12">
			   			<span class="green-circle circle-btn"></span>&nbsp;<?php echo Lang::get('assessment.correct');?> &nbsp; &nbsp;
			   			<span class="red-circle circle-btn"></span>&nbsp;<?php echo Lang::get('assessment.incorrect');?> &nbsp; &nbsp;
			   		</div>
				  </div>
				</div>

				<div class="row col-md-12 sm-margin" title=" Percentage of correctly answered questions out of the attempted questions">
					<div class="left-div1 center">
						<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-07.png') }}" alt="Marks" class="img-inline" width="44px">
						<br>
						<span><?php echo Lang::get('assessment.accuracy');?></span>
					</div>
					<div class="right-div1">
						<div class="progress accuracy-bar">
							<div class="progress-bar progress-bar-striped active progress-bar-animated" role="progressbar" aria-valuenow="70"aria-valuemin="0" aria-valuemax="100" style="width:{{$acuracy}}%;">
							<span >{{round($acuracy, 2)}}% </span>
							</div>
						</div>
				  </div>
				</div>
				<hr>
				@if(isset($attempt_pp) && !empty($attempt_pp) && 
					isset($last_attempt_data_count) && $last_attempt_data_count >0)

				<div class="row col-md-12 sm-margin">
					<a class="btn btn-default btn-circle xs-margin popup-link" href="{{ url('assessment/report/'.$attempt_pp->attempt_id).'?'.$requestUrl }}" title="Review Answers"><img src="{{ asset('portal/theme/default/img/icons/review-answers-icon.png') }}" alt="Review Answers" title="Review answers">&nbsp;Review answers</a>
					<a class="btn btn-default btn-circle xs-margin popup-link" href="{{ url('assessment/question-detail/'.$attempt_pp->attempt_id).'?'.$requestUrl }}" title="Detailed Analysis"><img src="{{ asset('portal/theme/default/img/icons/analytics-icon.png') }}" alt="Detailed Analytics" title="Detailed Analytics">&nbsp;Detailed Analytics</a>
				</div>
				@endif
			</div>
		@elseif((!is_null($last_attempt)) && (isset($quiz->is_score_display)) && ($quiz->is_score_display == true))
			<div class="col-md-4 col-sm-5 col-xs-12">
				<div class="custom-box">
					<h4 class="margin-top-0 border-btm padding-btm-10 blue text-capitalize" style="line-height: 1.3;font-weight: 600">
						<?php echo Lang::get('assessment.last_attempt_details');?>
						@if(!is_null($pass_criteria))
							@if($pass_criteria)
								- <strong class='correct-text'><?php echo Lang::get('assessment.pass'); ?></strong>
							@else
								- <strong class='wrong-text'> <?php echo Lang::get('assessment.fail'); ?></strong>
							@endif
						@endif
					</h4>
				</div>
				<div class="row">
					<div class="col-md-6 col-sm-6 col-xs-6 border-bottom-gray sm-margin"title="Total time taken for completing the quiz" style="background: #00008b2e;">
						<div class="xs-margin"><span class="pull-left"><img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-03.png') }}" alt="Total time spent" class="img-inline" width="40px"></span> <span class="right-div-count">Time spent</span></div>
						<div class="xs-margin center">
							<div class="font-32 orange">
							@if(!is_null($last_attempt) && isset($last_attempt->completed_on) && !empty($last_attempt->completed_on)&&!is_string($last_attempt->completed_on))
								<?php
									$secs = $last_attempt->started_on->diffInSeconds($last_attempt->completed_on);
									if(isset($quiz->duration) && $quiz->duration > 0 && ($quiz->duration * 60) < $secs){
										$secs = ($quiz->duration * 60);
									}
									$days = intval($secs / 86400); 
									$remainder = $secs % 86400;
									$hrs = intval($remainder / 3600);
									$remainder = $remainder % 3600;
									$min = intval($remainder / 60);
									$remainder = $remainder % 60;
									$sec = $remainder;
								?>
							@else
								$days = 0;
								$hrs = 0;
								$min = 0;
								$sec = 0;
							@endif
							<?php
								$hrs = ($hrs >= 10) ? $hrs : ('0'.$hrs);
						        $min = ($min >= 10) ? $min : ('0'.$min);
						        $sec = ($sec >= 10) ? $sec : ('0'.$sec);
							?>
							{{$hrs}}:{{$min}}:{{$sec}}
							</div>
							<div class="gray font-12"><?php echo Lang::get('assessment.hh_mm_ss'); ?></div>
						</div>
					</div>
					<div class="col-md-6 col-sm-6 col-xs-6 border-bottom-gray sm-margin" title="Average time taken per question" style="background: bisque;">
						<div class="xs-margin">
							<span class="pull-left"><img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-04.png') }}" alt="Speed" class="img-inline" width="40px"></span> <span class="right-div-count"><?php echo Lang::get('assessment.speed'); ?></span>
						</div>
						<div class="xs-margin center">
							<div class="font-32 red-light">
								@if( !is_null($last_attempt) && isset($last_attempt->completed_on) && !empty($last_attempt->completed_on)&&!is_string($last_attempt->completed_on))
								<?php
									$totalQues = isset($attempt_qes_count) ? $attempt_qes_count
																: count($last_attempt->questions);
									$totalQues = $totalQues > 0	?$totalQues:1;																
									$secs_s = $last_attempt->started_on->diffInSeconds($last_attempt->completed_on) / $totalQues;
									
									$min_s = intval($secs_s / 60);
									$sec_s = $secs_s % 60;
									
								?>
								@else
									$min_s = 0;
									$sec_s = 0;
								@endif
							<?php 
							$min_s = ($min_s >= 10) ? $min_s : ('0'.$min_s);
	    					$sec_s = ($sec_s >= 10) ? $sec_s : ('0'.$sec_s);
							?>
							{{$min_s}}:{{$sec_s}}
							</div>
							<div class="gray font-12">{{trans('assessment.mm_ss')}}</div>
						</div>
					</div>
				</div>
				<div class="row col-md-12 sm-margin" title="Score in percentage">
					<div class="left-div1 center">
						<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-05.png') }}" alt="Marks" class="img-inline" width="40px">
						<br>
						<span><?php echo Lang::get('assessment.score'); ?>(%)</span>
					</div>
					<div class="right-div1">
		      	<div class="progress score-bar">
							<div class="progress-bar progress-bar-striped active progress-bar-animated" role="progressbar" aria-valuenow="70" aria-valuemin="0" aria-valuemax="100" style="width:
							@if(!is_null($last_attempt))
								{{number_format((float)($last_attempt->obtained_mark/$last_attempt->total_mark)*100, 2)}}%"
							@else
								"0%">	
							@endif
							
							<span>
							@if(!is_null($last_attempt))
								{{number_format((float)($last_attempt->obtained_mark/$last_attempt->total_mark)*100, 2)}}% 
							@else
								0%
							@endif
							</span>
							</div>
						</div>
				  </div>
				</div>

				<div class="row col-md-12 sm-margin" title="Break up of correct({{$correct_count}}), incorrect({{$incorrect_count}}) and skipped questions({{$skip_count}})">
					<div class="left-div1 center">
						<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-06.png') }}" alt="Marks" class="img-inline" width="40px"><br>
						<span><?php echo Lang::get('assessment.split'); ?></span>
					</div>
					<div class="right-div1" >
						<div class="progress custom-progress" style="margin-bottom:4px;">
							<div class="progress-bar progress-bar-success progress-bar-striped active progress-bar-animated" style="width: {{$correct_per}}%">
								<span >{{$correct_count}}</span>
							</div>
							<div class="progress-bar progress-bar-danger progress-bar-striped active progress-bar-animated" style="width: {{$incorrect_per}}%">
								<span >{{$incorrect_count}}</span>
							</div>
							<div class="progress-bar progress-bar-warning progress-bar-striped active progress-bar-animated" style="width: {{$skip_ques}}%">
								<span >{{$skip_count}}</span>
							</div>
			   		</div>
			   		<div class="font-12">
			   			<span class="green-circle circle-btn"></span>&nbsp;<?php echo Lang::get('assessment.correct'); ?> &nbsp; &nbsp;
			   			<span class="red-circle circle-btn"></span>&nbsp;<?php echo Lang::get('assessment.incorrect'); ?> &nbsp; &nbsp;
			   			<span class="black-circle circle-btn"></span>&nbsp;<?php echo Lang::get('assessment.skipped'); ?>
			   		</div>
				  </div>
				</div>

				<div class="row col-md-12 sm-margin" title=" Percentage of correctly answered questions out of the attempted questions">
					<div class="left-div1 center">
						<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/icons-07.png') }}" alt="Marks" class="img-inline" width="44px">
						<br>
						<span><?php echo Lang::get('assessment.accuracy'); ?></span>
					</div>
					<div class="right-div1">
						<div class="progress accuracy-bar">
							<div class="progress-bar progress-bar-striped active progress-bar-animated" role="progressbar" aria-valuenow="70"aria-valuemin="0" aria-valuemax="100" style="width:{{$acuracy}}%;">
							<span >{{number_format((float)$acuracy, 2)}}% </span>
							</div>
						</div>
				  </div>
				</div>
			</div>
		@endif
	</div>
	
	@if( !isset($quiz->type) || (isset($quiz->type) && $quiz->type != QuizType::QUESTION_GENERATOR))
		@if(($attempts->count() > 0 ) && (isset($quiz->is_score_display)) && ($quiz->is_score_display == true))
		<div class="row">
			<div class="col-md-12">
				<div class="panel panel-default">
					<div class="panel-heading sequential-panel-header center">
						<?php echo Lang::get('assessment.summary_o_a'); ?>
					</div>
					<div class="panel-body">
						<div class="table-responsive">
							<table class="table">
								<thead>
									<th>#</th>
									<th><?php echo Lang::get('assessment.score'); ?> </th>
									<th><?php echo Lang::get('assessment.time_taken'); ?></th>
									<th><?php echo Lang::get('assessment.status'); ?></th>
									<th><?php echo Lang::get('assessment.started_on'); ?> </th>
									<th><?php echo Lang::get('assessment.completed_on'); ?> </th>
									@if(array_get($quiz, 'review_options.the_attempt', false))
									<th>{{ trans('assessment.review')}}</th>
									@endif
								</thead>
								<tbody>
									<?php $i = $attempts->count() ?>
									@foreach($attempts->reverse() as $attempt)
									<tr>
										<td>{{$i--}}</td>
										<td>
											@if($attempt->status == 'CLOSED' || !empty($attempt->obtained_mark)) 
				
											{{ number_format(((float)($attempt->obtained_mark/$attempt->total_mark)*100), 2).'%' }} 
												
											@endif
										</td>
										<td>
											@if(!empty($attempt->started_on) && !empty($attempt->completed_on))
												<?php
													$timeSpendSecs  = $attempt->completed_on->diffInSeconds($attempt->started_on, true); 
													if((isset($quiz->duration) && ($quiz->duration*60) < $timeSpendSecs) && $quiz->duration > 0)
														$timeSpendSecs = $quiz->duration*60;
								                    $days = intval($timeSpendSecs / 86400); 
								                    $remainder = $timeSpendSecs % 86400;
								                    $hrs = intval($remainder / 3600);
								                    $remainder = $remainder % 3600;
								                    $min = intval($remainder / 60);
								                    $remainder = $remainder % 60;
								                    $sec = $remainder;
								                    $hrs = ($hrs >= 10) ? $hrs : ('0'.$hrs);
								                    $min = ($min >= 10) ? $min : ('0'.$min);
								                    $sec = ($sec >= 10) ? $sec : ('0'.$sec);
								                    $timeSpend = $hrs.':'.$min.':'.$sec;
												?>
												{{$timeSpend}}
											@endif
										</td>
										<td>
											@if($attempt->status == 'CLOSED')
											<span class="end">CLOSED</span>
											@elseif($attempt->status == 'OPENED')
											<span class="start">OPENED</span>
											@endif
										</td>
										<td>{{ $attempt->started_on->timezone(Auth::user()->timezone)->format('d M Y h:i A') }}</td>
										<td>
											@if(!empty($attempt->completed_on)) 
												{{ $attempt->completed_on->timezone(Auth::user()->timezone)->format('d M Y h:i A') }}
											@endif
										</td>
										@if(array_get($quiz, 'review_options.the_attempt', false))
										<td>
											@if($attempt->status == 'CLOSED')
											<a class="report" data-url="{{ url('assessment/question-detail/'.$attempt->attempt_id.'/'.($i+1)).'?'.$requestUrl }}" title="Detailed Analysis">{{ trans('assessment.detailed_report') }}</a>
											@endif
										</td>
										@endif
									</tr>
									@endforeach
								</tbody>
							</table>
						</div>
					</div>
				</div>
			</div>
		</div>
		@endif
	@endif


	<script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.3/jquery.min.js"></script>
<script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.6/js/bootstrap.min.js"></script>


	<script type="text/javascript">
	$(function() {
		$('.star-quiz').on('click', function() {
			var action = $(this).data('action');
			var quiz_id = $(this).attr('id');
			if(action == 'star') {
				$("#"+quiz_id).removeClass("l-gray").addClass("yellow");
				$.ajax({
					type: 'GET',
	                url: "{{ url('assessment/star-quiz/star') }}/"+quiz_id
	            })
	            .done(function(response) {
	            	if(response.status == true) {
	            		$("#"+response.quiz_id).data('action', 'unstar');
	            	} else {
	            		$("#"+response.quiz_id).removeClass("yellow").addClass("l-gray");
	            	}
	            })
	            .fail(function(response) {
	            	$("#"+quiz_id).removeClass("yellow").addClass("l-gray");
	                alert( "Error while updating the assessment. Please try again" );
	            });
	        }
	        if(action == 'unstar') {
	        	$("#"+quiz_id).removeClass("yellow").addClass("l-gray");
				$.ajax({
					type: 'GET',
	                url: "{{ url('assessment/star-quiz/unstar') }}/"+quiz_id
	            })
	            .done(function(response) {
	            	if(response.status == true) {
	            		$('#'+response.quiz_id).data('action', 'star');
	            	} else {
	            		$('#'+response.quiz_id).removeClass('l-gray').addClass('yellow');
	            	}
	            })
	            .fail(function(response) {
	            	$('#'+response.quiz_id).removeClass("l-gray").addClass("yellow");
	                alert( "Error while updating the assessment. Please try again" );
	            });
	        }
		});
    });
	</script>
<div id="info-modal" class="modal fade" style="" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title center"><strong>{{Lang::get('assessment/detail.instructions_header_title')}}</strong></h4>
			</div>
			<div class="modal-body">
				<div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
					<div class="row">
						<div class="col-md-12">
							<p>
								{!!$quiz->quiz_description!!}
							</p>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer center">
				<button type="button" class="btn-success" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong>OK</strong></button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$(document).ready(function(){    
		$('.info-btn').on('click', function(){
			$('#info-modal').modal('show');
		});
		$('.report').on('click', function(e){
	        lswindow($(this).data('url'), name, '');
	    });
	    var attemptUrl = "{{ url('assessment/attempt/') }}";
	    var instructUrl = "{{ url('assessment/instructions') }}";
	    var requestUrl = "{{ $requestUrl }}";
	    $('#quiz-attempt').on('submit', function(e){
	    	var $this = $(this);
	    	$.ajax({
	    		url: $this.attr('action'),
	    		method: 'POST',
	    		success: function(response){
	    			if(response.status != 'undefined') {
	    				if (response.attempt) {
		    				lswindow(instructUrl+'/'+response.quiz_id+'/'+response.attempt_id, name, '');
	    				}
		    			else {
		    				lswindow(attemptUrl+'/'+response.attempt_id+'?'+requestUrl, name, '');
		    			}
	    			}		    			
	    		},
	    	});
	    	return false;
	    });
	    $('.popup-link').on('click', function(e){
	        lswindow($(this).attr('href'), name, 'toolbar=0,location=0,menubar=0');
	        return false;
	    });
	    $('.begin').on('click',function(e){
	        @if($quiz->duration != 0)
	            lswindow("{{ url('assessment/instructions/')}}"+'/'+$(this).data('id'));
	        @else       
	            lswindow("{{ url('assessment/instructions/')}}"+'/'+$(this).data('id'));
	        @endif
	        return false;
	    });
	});
</script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
@stop