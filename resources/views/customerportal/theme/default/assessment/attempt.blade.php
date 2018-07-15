@section('content')
	<div class="page-bar">
		<ul class="page-breadcrumb">
			<li><a href="{{url('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
			<li><a href="{{url('/assessment?filter=attempted')}}">Assessment</a><i class="fa fa-angle-right"></i></li>
			<li><a href="#">{{$quiz->quiz_name}}</a></li>
		</ul>
	</div>
	<?php 
	$i = $qno = 1; 
	foreach($attempt->page_layout as $pno => $layout) {
		if($page == $pno) {
			$qno = $i;
		}
		$i += count($layout);
	}
	?>
	<style type="text/css">
		#ques_timer {
			padding-left: 100px;
		}
		.shiftingpageclass{
			cursor: pointer;
		}
	</style>
	<!--content starts here-->
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="row">
				<div class="panel panel-default quiz-name">
					<div class="panel-heading qus-main-panel-head">
						<b>{{ $quiz->quiz_name }}</b>
						<div class="pull-right">
							<div id="timer">			
							</div>
						</div>
					</div>
					<div class="panel-body">
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin">
							<div class="panel-body question-panel sm-margin">
								<h4 style="margin-left:6px;"><strong>Questions</strong></h4>
								<ul class="question">
									<?php 
									$j = 1;
									foreach($attempt->page_layout as $pno => $layout) {
										if($page == $pno) {
											foreach($layout as $q) {
												echo '<li><a class="selected-answered" href="#" onclick="return false">'.$j++.'</a></li>';
											}
										} else {
											$answered = Session::get('assessment.'.$attempt->attempt_id.'.question_answered');
											foreach($layout as $q) {
												$blur = (in_array($q, $answered))? 'answered' : 'not-answered';
												echo '<li><a class = "shiftingpageclass '.$blur.'" data-preq="'.$pno.'" data-info="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno).'" >'.$j++.'</a></li>';
											}
										}
									}
									?>
								</ul>
								<div class="center border-radius">
									<a href="{{ url('assessment/summary/'.$attempt->attempt_id) }}" class="btn grey-cascade" role="button">Finish the attempt</a>
								</div>			
							</div>
						</div>
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin">
							<div id='ques_timer' class="pull-right" style="display:none;">
								<p>
									<?php echo Lang::get('assessment.time_spend_on_ques'); ?> : <span id='ques_time'>0 : 0 : 0</span>	
								</p>
							</div>
							<form id="quizForm" action="{{ url('assessment/attempt/'.$attempt->attempt_id) }}" method="POST" accept-charset="UTF-8">
								<input id = 'ques_time_taken_id' name = 'ques_time_taken' type="hidden" >
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 lg-margin qus-box">
									<?php $p = $page; $count_hr = 1;?>
									@foreach($attemptdata as $q)
									<div class="panel-body qus-panel @if($count_hr++ < count($attemptdata)) question-desc @endif">
										
										<div class="qus-number"><b>Q{{ $qno++ }}</b></div>
										<div class="qus-heading">
											@if($q->question_type == 'MCQ')
											<input id='qes_ids' type="hidden" name="q[]" value="{{$q->question_id}}">
										 	<span>{!! $q->question_text !!}</span>
										 	<div class="radio-list row">
											<?php
												$answer_data = [];
												foreach ($q->answers as $key => $answer) {
													$checked = '';
													$acount = array_search($key, $q->answer_order);
													if($q->user_response == $answer['answer'])
														$checked = ' checked = "checked"';
													$answer_data[$acount]['a'] = '<div class="col-md-6 col-sm-12 col-xs-12"><label><input type="radio" name="q:'.$q->question_id.'" value="'.$acount.'"'.$checked.' > ';
													$answer_data[$acount]['b'] = $answer['answer'].'</label></div>';
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
										</div>
									</div>
									@endforeach
								</div>
								<input type="hidden" id='next_page_id' name="next_page" value="{{++$p }}">
								<div class="page pull-left">
		    						<input type="submit" class="btn red" name="next" value="Save & Continue">
								</div>
							</form>
						</div>
						
					</div>
				</div>
			</div>
		</div>
	</div>
	@if(!empty($quiz->duration))
	<?php
		$duration = ($attempt->started_on->timestamp + ($quiz->duration * 60)) - time();
		if($duration < 0) $duration = 0;
	?>
	<script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.plugin.js') }}"></script>
	<script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.countdown.js') }}"></script>
	<script>
	
	$(function () {
		$('#timer').countdown({
			until: +{{ $duration }},
			//onExpiry: autoSubmitQuiz,
			compact: true,
			layout: '<b>Time left: </b>{hnn}{sep}{mnn}{sep}{snn}'
		});
		
		
	});
	// function autoSubmitQuiz() {
	// 	$('#quizForm').submit();
	// }

	</script>

	@endif
<script>
	var share_tk= {{$total_tk}};
	var time = share_tk%60;
	var tt = 0;
	var h = parseInt(share_tk / 3600);
	var m = parseInt(share_tk / 60);
	var s = 0;
	var time_html = '';
	time_html = h+' : '+m+' : '+time;
	$('#ques_time').html(time_html);
	$('#ques_time_taken_id').val(tt);
	$(document).ready(function(){
		
	
   		$('.shiftingpageclass').click(function(){
   			$('#next_page_id').val($(this).data("preq"));
   			$('#quizForm').submit();
		}); 

		setInterval(function(){
			time++;
			tt++;
			if(time >= 60){
				m++;
				time = 0;
			}
			if(m >= 60){
				h++;
				m = 0;
			}
			time_html = h+' : '+m+' : '+time;
			$('#ques_time').html(time_html);
			$('#ques_time_taken_id').val(tt);
		 }, 1000);


		});
</script>
@stop