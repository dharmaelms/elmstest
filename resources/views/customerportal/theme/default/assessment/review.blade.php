@section('content')
	<div class="page-bar">
		<ul class="page-breadcrumb">
			<li><a href="{{url('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
			<li><a href="{{url('/assessment?filter=attempted')}}">Assessment</a><i class="fa fa-angle-right"></i></li>
			<li><a href="{{url('/assessment/detail/'.$quiz->quiz_id)}}">{{$quiz->quiz_name}}</a><i class="fa fa-angle-right"></i></li>
			<li><a href="#">Report</a></li>
		</ul>
	</div>
	<!--content starts here-->
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="row">
				<div class="panel panel-default quiz-name">
					<div class="panel-heading qus-main-panel-head">
						<b>{{ $quiz->quiz_name }}</b>
					</div>
					<div class="panel-body">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin">
							<div class="panel-body question-panel sm-margin">
								<h4 style="margin-left:6px;"><strong>Questions</strong></h4>
								<ul class="question">
									<?php
									$j = 1;
									foreach ($attempt->questions as $q) {
										$data = (isset($attemptdata[(int)$q]))? $attemptdata[(int)$q] : false;
										if(!empty($data) && ($data->answer_status == 'CORRECT' || $data->correct_answer == $data->user_response)) {
											echo '<li><a class="selected-answered" onclick="return false">'.$j++.'</a></li>';
										} else {
											echo '<li><a class="selected-answered" style="border: 2px solid #D05F5F;" onclick="return false">'.$j++.'</a></li>';
										}
									}
									?>
								</ul>
								<div class="center border-radius">
									<a href="{{ url('assessment/detail/'.$quiz->quiz_id) }}" class="btn grey-cascade" role="button">Finish the Review</a>
								</div>			
							</div>
						</div>

						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin">
							<table class="table table-striped table-condensed table-hover">
								<tbody>
									@if(count($attempts) > 1)
									<tr>
										<td width="120px" align="right"><b>Attempts</b></td>
										<td>
											<?php $r = 1; ?>
											@foreach($attempts as $a)
												@if($a->attempt_id == $attempt->attempt_id)
												<b>{{ $r++ }}</b>
												@else
												<a href="{{ url('assessment/report/'.$a->attempt_id) }}">{{ $r++ }}</a>
												@endif
											@endforeach
										</td>
									</tr>
									@endif
									<tr>
										<td width="120px" align="right"><b>Started On</b></td>
										<td>{{ $attempt->started_on->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</td>
									</tr>
									<tr>
										<td width="120px" align="right"><b>Completed On</b></td>
										<td>{{ $attempt->completed_on->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</td>
									</tr>
									<tr>
										<td width="120px" align="right"><b>Time Taken</b></td>
										<td>{{ $attempt->completed_on->diffForHumans($attempt->started_on, true) }}</td>
									</tr>
									<tr>
										<td width="120px" align="right"><b>Marks</b></td>
										<td>@if($attempt->obtained_mark>=0) {{ $attempt->obtained_mark.'/'.$attempt->total_mark }} @else {{$attempt->obtained_mark.'/'.$attempt->total_mark }} @endif</td>
									</tr>
								</tbody>
							</table>
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 lg-margin qus-box">
								<?php 
								$qno = $count_hr = 1;
								foreach($attempt->questions as $question) {
									$q = (isset($attemptdata[(int)$question]))? $attemptdata[(int)$question] : false;
									if(!empty($q)) {
										?>
										<div class="panel-body qus-panel @if($count_hr++ < count($attempt->questions)) question-desc @endif">
											<div class="qus-number"><b>Q{{ $qno++ }}</b></div>
											<div class="qus-heading">
												@if($q->question_type == 'MCQ')
											 	<span>{!! $q->question_text !!}</span>
											 	<div class="radio-list row">
												<?php
													$answer_data = [];
													foreach ($q->answers as $key => $answer) {
														$checked = $correct = '';
														$acount = array_search($key, $q->answer_order);
														if($q->user_response == $answer['answer']) {
															$checked = ' checked = "checked"';
															if($quiz->review_options['whether_correct']) {
																if($q->answer_status == 'CORRECT' || $q->correct_answer == $q->user_response)
																	$correct = '<i class="fa fa-check green"></i>';
																else
																	$correct = '<i class="fa fa-times red"></i>';
															}
														}
														$answer_data[$acount]['a'] = '<div class="col-md-6 col-sm-12 col-xs-12"><label><input type="radio"'.$checked.' disabled> ';
														$answer_data[$acount]['b'] = $answer['answer'].'&nbsp;&nbsp;'.$correct.'</label></div>';
													}
													$a = range('a', 'z');
													array_unshift($a, 0);
													ksort($answer_data);
													$final = [];
													foreach ($answer_data as $key => $value) {
														$final[$key] = $value['a'].next($a).') '.$value['b'];
													}
													foreach ($final as $value) {
														echo $value;
													}
												?>
												@endif
												</div>
												@if($quiz->review_options['whether_correct'])
												<br>
												@if($quiz->review_options['correct_answer'])
												<div class="alert alert-success">
													<div class="row">
														<div class="col-md-1 col-sm-2 col-xs-3">
															<img src="{{ URL::to('portal/theme/default/img/icons/correct-icon.jpg') }}" alt="">
														</div>
														<div class="col-md-11 col-sm-10 col-xs-9">
														<p>
														@if($q->answer_status == 'CORRECT' || $q->correct_answer == $q->user_response)
															Your answer is correct
														@else
															Your answer is incorrect
														@endif
														</p>
														<p><b>Correct Answer:</b> {!! $q->correct_answer !!}</p>
														@if(isset($q->rationale) && !empty($q->rationale))
														<p><b>Rationale: </b> {!! $q->rationale !!}</p>
														@endif
														@if($quiz->review_options['marks'])
														<p><b>Marks: </b>
														@if($q->obtained_negative_mark>0){!! "<span class='red'>".'-'.$q->obtained_negative_mark."</span>/".$q->question_mark !!} @else {{ $q->obtained_mark.'/'.$q->question_mark }}</p> @endif
														@endif
														@if(isset($q->time_spend))
															{{-- <br> --}}
															<p><b> <?php echo Lang::get('assessment.time_taken'); ?>: </b> {{array_sum($q->time_spend)}} Second(s)</p>
														@endif
														</div>
													</div>													
												</div>
												@endif
												@endif
											</div>
										</div>
										<?php
									}
								}
								?>
							</div>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
<script type="text/javascript">
	$(document).ready(function(){
		var $this;
		$('.green, .red').each(function(i, v){
			$this = $(this);
			if($this.hasClass('green')){
				$this.parent().parent().each(function(){
					$(this).addClass('correct-text')
				})
			}
			else if($this.hasClass('red')){
				$this.parent().parent().each(function(){
					$(this).addClass('wrong-text')
				})
			}
		});
	});	
</script>	
@stop