@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::to("portal/theme/default/css/responsive-iframe.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/jquery.scrolling-tabs.css') }}" />
	<!--content starts here-->
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div>
				<div class="panel panel-default quiz-name">
					<div class="panel-heading qus-main-panel-head">
						<strong>{{ $quiz->quiz_name }}</strong>
						@if(isset($quiz->quiz_description) && !empty($quiz->quiz_description))
							<a href="#info-modal-quiz" class="btn l-gray info-btn" data-toggle="modal" title="{{Lang::get('assessment/summary.i_title')}}"><i class="fa fa-question"></i></a>
						@endif
						<button onclick="closeWindow();" class="btn btn-primary pull-right" style="margin-top:-7px;">
				            <strong>{{ trans('assessment.close') }}</strong>
				        </button>
					</div>
					<div class="panel-body padding-0">
						<div id="jquery-script-menu">
							<div class="row">
								<div class="col-md-12">
									<!-- Nav tabs -->
									<?php
										//dd($active_section);
									?>
									<ul class="nav nav-tabs" role="tablist">
									  	@if(isset($section_details) && !empty($section_details))			
									  		@foreach ($section_details as $key => $eachSection) 
									  			@if(isset($eachSection['title']) && !empty($eachSection['title']))
									  				<li role="presentation" class="@if(isset($eachSection['section_id']) && $eachSection['section_id'] === $active_section) active @endif" ><a href='{{URL::to("assessment/section-with-question/".$attempt->attempt_id."/".$eachSection['section_id'])}}?{{$requestUrl}}'>{{$eachSection['title']}}</a></li>
									  			@endif	
									  		@endforeach
									  	@endif					
									</ul>

									<!-- Tab panes -->
									<div class="tab-content">
									  <div role="tabpanel" class="tab-pane active" id="tab1">
											<div class="question-panel">
												<div class="row col-md-12 xs-margin">
													<h4 style="display:inline"><strong><?php echo Lang::get('assessment.questions'); ?></strong> 
														@if(isset($section_details) && !empty($section_details))			
													  		@foreach ($section_details as $key => $eachSection) 
													  			@if(isset($eachSection['title']) && !empty($eachSection['title']))
													  				@if(isset($eachSection['section_id']) && $eachSection['section_id'] === $active_section && !empty($eachSection['description']))
													  					<a href="#info-modal" class="btn l-gray info-btn" data-toggle="modal" title="{{Lang::get('assessment/section.i_title')}}"><i class="fa fa-question"></i></a></h4>													  					
													  				@endif
													  			@endif	
													  		@endforeach
													  	@endif


													<!-- <div> -->
									@if(!empty($active_question))
													
													@if(config('app.question_per_block') < $attemptdatapagination->total())													
														<form action="" class="pull-right" >
															<a href='{{$attemptdatapagination->url(1)}}' class="btn btn-primary btn-sm"><i class="fa fa-fast-backward"></i></a> 
															<a href='{{$attemptdatapagination->previousPageUrl()}}' class="btn btn-primary btn-sm"><i class="fa fa-backward"></i></a>
																														
															<span style="border: 1px #dddddd;padding: 4px;width: 68px;" id="sequenceno_short" name="sequenceno_short">&nbsp;1 - 5&nbsp;</span>
															
															<a href='{{$attemptdatapagination->nextPageUrl()}}' class="btn btn-primary btn-sm"><i class="fa fa-forward"></i></a>
															<a href='{{$attemptdatapagination->url($attemptdatapagination->lastPage())}}' class="btn btn-primary btn-sm"><i class="fa fa-fast-forward"></i> </a>
														</form>
													@endif
													<!-- </div> -->
												</div>
												<ul class="question">
												<?php
													$question_sequence_no = 0;										
													$page = Input::get('page');
													if(empty($page)) $page = 1;
												?>
													@foreach ($section_details as $key => $eachSection)
														@if(isset($eachSection['questions']) && !empty($eachSection['questions']))
															@if(isset($eachSection['section_id']) && $eachSection['section_id'] === $active_section)
																<?php
																	break;
																?> 
															@endif 
															<?php 
																$question_sequence_no += count($eachSection['questions']);
															?>
														@endif
													@endforeach
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
												@foreach ($ques_order_ary as $ques_id)
													<?php 
														if($attemptdata->get((int)$ques_id) ){
															$value = $attemptdata->get((int)$ques_id);
														}else{
															continue;
														}
														$question_sequence_no++;
														$url = URL::to("assessment/section-with-question/$value->attempt_id/$value->section_id?q_id=$value->question_id&page=$page&$requestUrl");
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
												<script type="text/javascript">
													$('#sequenceno_short').html('<?php echo ($begin_section_no+1)." - ".$question_sequence_no;?>');
												</script>
											</div>
									 @else
									 <p class="text-center"><?php echo Lang::get('assessment.section_donot_have_questions_to_review'); ?></p>
									@endif
									  </div>
												<div class="center border-radius xs-margin">
													<a href="{{ url('assessment/detail/'.$quiz->quiz_id) }}" class="btn grey-cascade" role="button"><?php echo Lang::get('assessment.finish_review'); ?></a>
												</div>
									  <!-- ENDS tab-pane -->
									</div>
								</div>
							</div>
						</div>
						@if(!empty($active_question))
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
													$answers = [];
													foreach($q->answers as $key => $answer)
														$answers[array_search($key, $q->answer_order)] = $answer;
													ksort($answers);
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
																@if($quiz->review_options["whether_correct"])
																	@if($q->user_response === $answerData["answer"])
																		@if(($q->answer_status == "CORRECT") || ($q->correct_answer == $q->user_response))
																			<?php $flag_align_checkmark = 1;?>
																			<i class="fa fa-check green"></i>
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
																<input type="radio" name="q:{{ $q->question_id }}" value="{{ $answerCount }}" {{ ($q->user_response === $answerData["answer"])? "checked" : "" }} disabled>
															</label>
															<div class="right-div">
															{{ chr($asciiCharVal+$answerCount++) }}&#41;&#32;
															</div>
															<div class="right-div1">
															{!!html_entity_decode($answerData["answer"]) !!}
															</div>
														</div>
														@endforeach
													</div>
												<?php
													}
												?>
												@endif
												</div>
												@if($quiz->review_options['whether_correct'])
												<br>
												@if($quiz->review_options['correct_answer'])
												@if($q->answer_status == 'CORRECT' || $q->correct_answer == $q->user_response)
												<div class="alert alert-success">
													<table>
														<tr>
															<td width="70px"><img src="{{ URL::to('portal/theme/default/img/icons/correct-icon.jpg') }}" alt="Correct Answer"></td>
															<td><p class="margin-0">
																		<?php echo Lang::get('assessment.your_ans_correct'); ?> 
																	</p>
																	@if($quiz->review_options['marks'])
																	<p class="margin-0"><b>Marks: </b>
																	@if($q->obtained_negative_mark>0){!! "<span class='red'>".'-'.$q->obtained_negative_mark."</span>/".$q->question_mark !!} @else {{ $q->obtained_mark.'/'.$q->question_mark }}</p> @endif
																	@endif
																	@if(isset($q->time_spend))
																		<p class="margin-0"><b> <?php echo Lang::get('assessment.time_taken'); ?>: </b> {{ Helpers::secondsToTimeString(array_sum($q->time_spend))}}</p>
																	@endif
																	@if(isset($quiz->review_options['rationale']) && $quiz->review_options['rationale'])
																		@if(isset($q->rationale) && !empty($q->rationale))
																				<p><b>{{Lang::get('assessment/review.solution')}}</b> </p>
																				<p>{!! html_entity_decode($q->rationale) !!}</p>
																		@endif
																	@endif
															</td>
														</tr>
													</table>
												</div>												
												@else
													<div class="alert alert-warning">
														<table>
															<tr>
																<td width="70px"><img src="{{ URL::to('portal/theme/default/img/icons/wrong-icon.png') }}" alt="Wrong Answer"></td>
																<td>
																	<p class="margin-0">
																	@if($q->answer_status === "INCORRECT")
																		<?php echo Lang::get('assessment.your_ans_incorrect'); ?>
																	@else
																		<?php echo Lang::get('assessment.not_attempted'); ?>
																	@endif
																	</p>
																	@if($quiz->review_options['marks'])
																	<p class="margin-0"><b>Marks: </b>
																	@if($q->obtained_negative_mark>0)
																	{!! "<span class='red'>".'-'.$q->obtained_negative_mark."</span>/".$q->question_mark !!} @else {{ $q->obtained_mark.'/'.$q->question_mark }}
																	</p> 
																	@endif
																	@endif
																	@if(isset($q->time_spend))
																		<p class="margin-0"><b> <?php echo Lang::get('assessment.time_taken'); ?>: </b> {{ Helpers::secondsToTimeString(array_sum($q->time_spend))}} </p>
																	@endif
																	@if(isset($quiz->review_options['rationale']) && $quiz->review_options['rationale'])
																		@if(isset($wrongChoiceRationale) && !empty($wrongChoiceRationale))
																			<p class="margin-0">
																				<b>Rationale: </b>
																				{!! html_entity_decode($wrongChoiceRationale) !!}
																			</p>
																		@endif
																	@endif
																</td>
															</tr>
														</table>
													</div>
													<div class="alert alert-success">
														<p class="margin-0">
															<b><?php echo Lang::get('assessment.correct_answer'); ?></b> 
															{!! html_entity_decode($q->correct_answer) !!}
														</p>
														@if(isset($quiz->review_options['rationale']) && $quiz->review_options['rationale'])
															@if(isset($q->rationale) && !empty($q->rationale))
																	<p>
																	<b>{{Lang::get('assessment/review.solution')}}</b> </p>
																	<p>
																		{!! html_entity_decode($q->rationale) !!}
																	</p>
															
														    @endif
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
						@endif
					</div>
				</div>
			</div>
		</div>
	</div>

<div id="info-modal" class="modal fade" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title center"><strong>{{Lang::get('assessment/section.modal_header_text')}}</strong></h4>
			</div>
			<div class="modal-body">
				<div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
					<div class="row">
						<div class="col-md-12">
							<p>
								@if(isset($section_details) && !empty($section_details))			
							  		@foreach ($section_details as $key => $eachSection) 
							  			@if(isset($eachSection['title']) && !empty($eachSection['title']))
							  				@if(isset($eachSection['section_id']) && $eachSection['section_id'] === $active_section)
							  					<?php echo $eachSection['description'];?>
							  				@endif
							  			@endif	
							  		@endforeach
							  	@endif
							</p>
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
<div id="info-modal-quiz" class="modal fade" tabindex="-1" aria-hidden="true">
	<div class="modal-dialog">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title center"><strong>{{Lang::get('assessment/section.modal_header_text')}}</strong></h4>
			</div>
			<div class="modal-body">
				<div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
					<div class="row">
						<div class="col-md-12">
							<p>
								{!! $quiz->quiz_description !!}
							</p>
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

<script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
<script src="{{ asset('portal/theme/default/js/jquery.scrolling-tabs.js') }}"></script>

<script>
$(document).ready(function(){
	$('.nav-tabs').scrollingTabs();
});
</script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
@stop