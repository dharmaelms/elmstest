@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::to("portal/theme/default/css/responsive-iframe.css") }}">
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
	<!--content starts here-->
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="row">
				<div class="panel panel-default quiz-name">
					<div class="panel-heading qus-main-panel-head">
				        <b>
				            {{ $quiz->quiz_name }}
				            @if(!empty($quiz->quiz_description))
					            <a href="#info-modal-quiz" class="btn l-gray info-btn" data-toggle="modal" title="Instructions">
					                <i class="fa fa-question"></i>
					            </a>
					        @endif
				        </b>
				        <button onclick="window.close();" class="btn btn-primary pull-right" style="margin-top:-7px;">
				            <strong>{{ trans('assessment.close') }}</strong>
				        </button>  
				    </div>
				    <br>
					<div class="panel-body padding-0">
						<div id="jquery-script-menu">
							<div class="row">
								<div class="col-md-12">								
									
											<div class="question-panel">
												<div class="row col-md-12 xs-margin">
													<h4 style="display:inline"><strong> <?php echo Lang::get('assessment.questions'); ?></strong> 
													@if(config('app.question_per_block') < $attemptdatapagination->total())
														<form action="" class="pull-right" >
																<a href='{{$attemptdatapagination->url(1)}}' class="btn btn-primary btn-sm"><i class="fa fa-fast-backward"></i></a> 
																<a href='{{$attemptdatapagination->previousPageUrl()}}' class="btn btn-primary btn-sm"><i class="fa fa-backward"></i></a>
																															
																<span style="border: 1px #dddddd;padding: 4px;width: 68px;" id="sequenceno_short" name="sequenceno_short">&nbsp;1 - 5&nbsp;</span>
																
																<a href='{{$attemptdatapagination->nextPageUrl()}}' class="btn btn-primary btn-sm"><i class="fa fa-forward"></i></a>
																<a href='{{$attemptdatapagination->url($attemptdatapagination->lastPage())}}' class="btn btn-primary btn-sm"><i class="fa fa-fast-forward"></i> </a>
														</form>
													@endif
												</div>
												<ul class="question">
												<?php
													$question_sequence_no = 0;										
													$page = Input::get('page');
													$active_question_sequence_no = 1;
													if(empty($page)) $page = 1;
													 $review_options = [
							                            'the_attempt'=>'1', 
							                            'whether_correct'=> '1',
							                            'marks'=> '1',
							                            'rationale'=> '1',
							                            'correct_answer'=> '1'];     
												?>
												<?php
													if($page > 1)
													{
														$question_sequence_no += ($page - 1) * config('app.question_per_block');
													}
													$begin_section_no = $question_sequence_no;
														$activeQuestionflag = 0; 
														if(!Input::has('q_id'))
														{
															$active_question_sequence_no = $question_sequence_no + 1;
															$activeQuestionflag = 1;
														}
												?>
												@foreach ($attemptdatapagination as $key => $value)
													<?php 
														$question_sequence_no++;
														$url = URL::to("assessment/report/$value->attempt_id?q_id=$value->question_id&page=$page&$requestUrl");
													?>
													<li>
														<a href='{{$url}}' class="														
														<?php
															if($value->answer_status === "CORRECT")
															{
																echo "correct";
															}
															else if($value->answer_status === "INCORRECT")
															{
																echo "wrong";
															}
															else
															{
																echo "skipped";
															}
														?>
													">
															<?php
															if($activeQuestionflag === 1) {
																?>
																{{$question_sequence_no}}
																<div class="active-line"></div>
																<?php
																$activeQuestionflag = 0;
															}
															elseif(Input::has('q_id') && Input::get('q_id') == $value->question_id)
															{
																?>
																{{$question_sequence_no}}
																<div class="active-line"></div>
																<?php
																$active_question_sequence_no = $question_sequence_no;
															}
															else
															{
																?>
																{{$question_sequence_no}}
																<?php
															}
															
														?>
														</a>
													</li>	
												@endforeach
												</ul>
											</div>
									  </div>
							</div>							
						</div>

							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 lg-margin qus-box">
								<?php 
								$qno = $count_hr = 1;								
									$q = $active_question;
									if(!empty($q)) {
										?>
										<div class="panel-body qus-panel @if($count_hr++ < count($attempt->questions)) question-desc @endif">
											<?php
												$question_no = Input::get('page');
												if(empty($question_no) || $question_no === 0 )
												{
													$question_no = 1;
												}
											?>
											<div class="qus-number"><b>Q{{$active_question_sequence_no}}</b></div>
											<div class="qus-heading">
												@if($q->question_type == 'MCQ')
											 	<div class="table-responsive">{!! $q->question_text !!}</div>
											 	<div class="radio-list">
												<?php
													$answers = $q->answers;													
													$answerChunks = collect($answers)->chunk(2);
													$answerCount = 0;
													$asciiCharVal = 97;
													foreach($answerChunks as $chunk)
													{
														$flag_align_checkmark = 0;
												?>
													<div class="row">
														@foreach($chunk as $answerData)
														<div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-bottom-10" style="max-height:300px;overflow-y:auto;">
															<div class="lft-div">
																@if(isset($review_options["whether_correct"]) && $review_options["whether_correct"])
																	@if(!is_null($q->user_answer_index) && isset($q->answers[$q->user_answer_index]['answer']) && $q->answers[$q->user_answer_index]['answer'] === $answerData["answer"])
																		@if(($q->answer_status == "CORRECT"))
																			<?php $flag_align_checkmark = 1;?>
																			<i class="fa fa-check green"></i>
																			<?php $rightChoiceRationale = $answerData["rationale"]; ?>
																		@else
																			<?php $flag_align_checkmark = 1;?>
																			<?php $wrongChoiceRationale = $answerData["rationale"]; ?>
																			<i class="fa fa-times red"></i>
																		@endif
																	@endif
																@endif
															</div>
															<?php																
																$class_align_checkmark = "margin-left-14";
																if($flag_align_checkmark == 1)
																{
																	$class_align_checkmark = '';
																	$flag_align_checkmark = 0;
																}																
															?>
															<label class="lft-div {{$class_align_checkmark}}">
																<input type="radio" name="q:{{ $q->question_id }}" value="{{ $answerCount }}" 
																@if(!is_null($q->user_answer_index) &&isset($q->answers[$q->user_answer_index]['answer']))
																{{ (!is_null($q->user_answer_index) && $q->answers[$q->user_answer_index]['answer'] === $answerData["answer"])? "checked" : "" }}
																@endif
																 disabled>
															</label>
															<div class="right-div">
															{{ chr($asciiCharVal+$answerCount++) }}&#41;&#32;
															</div>
															<div class="right-div1">
															{!! html_entity_decode($answerData["answer"]) !!}
															</div>
														</div>
														@endforeach
													</div>
												<?php
													}
												?>
												@endif
												</div>
												@if($review_options['whether_correct'])
												<br>
												@if($review_options['correct_answer'])
												@if($q->answer_status == 'CORRECT')
												<div class="alert alert-success">
													<table>
														<tr>
															<td width="70px"><img src="{{ URL::to('portal/theme/default/img/icons/correct-icon.jpg') }}" alt="Correct Answer"></td>
															<td><p class="margin-0">
																		<?php echo Lang::get('assessment.your_ans_correct'); ?>
																	</p>
																	@if($review_options['marks'])
																	<p class="margin-0"><b>Marks: </b>
																	@if($q->obtained_negative_mark>0){!! "<span class='red'>".'-'.$q->obtained_negative_mark."</span>/".$q->question_mark !!} @else {{ $q->obtained_mark.'/'.$q->question_mark }}</p> @endif
																	@endif
																	@if(isset($q->time_spend))
																		<p class="margin-0"><b> <?php echo Lang::get('assessment.time_taken'); ?>: </b> {{ Helpers::secondsToTimeString(array_sum($q->time_spend))}}</p>
																	@endif
																	@if(isset($rightChoiceRationale) && !empty($rightChoiceRationale))
																		<p><b>{{Lang::get('assessment/review.solution')}}</b></p>
																		<p>{!! html_entity_decode($rightChoiceRationale) !!}</p>
																	@endif
														  </td>
														</tr>
													</table>
												</div>										

												@else
													
													<div class="alert alert-warning">
														<table>
															<tr>
																<td width="70px"><img src="{{ URL::to('portal/theme/default/img/icons/wrong-icon.png') }}" alt="Wrong"></td>
																<td><p class="margin-0">
																		@if($q->answer_status === "INCORRECT")
																			Your answer is incorrect
																		@else
																			Not attempted
																		@endif
																		</p>
																		@if($review_options['marks'])
																		<p class="margin-0"><b>Marks: </b>
																		@if($q->obtained_negative_mark>0)
																		{!! "<span class='red'>".'-'.$q->obtained_negative_mark."</span>/".$q->question_mark !!} @else {{ $q->obtained_mark.'/'.$q->question_mark }}
																		</p> 
																		@endif
																		@endif
																		@if(isset($q->time_spend))
																			<p class="margin-0"><b> <?php echo Lang::get('assessment.time_taken'); ?>: </b> {{ Helpers::secondsToTimeString(array_sum($q->time_spend))}} </p>
																		@endif
																		@if(isset($wrongChoiceRationale) && !empty($wrongChoiceRationale))
																			<p class="margin-0">
																				<b>Rationale: </b>
																				{!! html_entity_decode($wrongChoiceRationale) !!}
																			</p>
																		@endif
																</td>
															</tr>
														</table>
													</div>
													<div class="alert alert-success">
														<p class="margin-0">
															<b> <?php echo Lang::get('assessment.correct_answer'); ?></b> 
															{!! $q->answers[$q->correct_answer_index]['answer'] !!}
														</p>
														@if(isset($q->answers[$q->correct_answer_index]['rationale']) && !empty($q->answers[$q->correct_answer_index]['rationale']))
														 <p><b>{{Lang::get('assessment/review.solution')}}</b></p>
															<p>{!! $q->answers[$q->correct_answer_index]['rationale'] !!}</p>
														
														@endif
													</div>
												@endif
												@endif
												@endif
											</div>
										</div>
										<?php
									}								
								?>
								
							</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>

<div id="info-modal-quiz" class="modal fade" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title center"><strong>{{Lang::get('assessment/random_question_generator.modal_header_text')}}</strong></h4>
			</div>
			<div class="modal-body">
				<div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
					<div class="row">
						<div class="col-md-12">
							<p>{!!$quiz->quiz_description!!}</p>
						</div>
					</div>
				</div>
			</div>
			<div class="modal-footer center">
				<button type="button" class="btn-success" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong><?php echo Lang::get('assessment.ok'); ?></strong></button>
			</div>
		</div>
	</div>
</div>
<script type="text/javascript">
	$('#sequenceno_short').html('<?php echo ($begin_section_no+1)." - ".$question_sequence_no;?>');
</script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
@stop