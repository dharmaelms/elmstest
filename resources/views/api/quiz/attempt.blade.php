<!DOCTYPE html>
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->
<html lang="en">
<!--<![endif]-->
<!-- BEGIN HEAD -->
<head>
	<meta charset="utf-8"/>
	<title>Assessment - {{ $quiz->quiz_name }}</title>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta content="width=device-width, initial-scale=1.0" name="viewport"/>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<!-- BEGIN GLOBAL MANDATORY STYLES -->
	<link href="//fonts.googleapis.com/css?family=Open+Sans:400,300,600,700&subset=all" rel="stylesheet" type="text/css"/>
	<link href="{{ asset('api/quiz/css/bootstrap.min.css') }}" rel="stylesheet" type="text/css"/>
	<link href="{{ asset('api/quiz/css/components.css') }}" id="style_components" rel="stylesheet" type="text/css"/>
	<link href="{{ asset('api/quiz/css/layout.css') }}" rel="stylesheet" type="text/css"/>
	<link href="{{ asset('api/quiz/css/darkblue.css') }}" id="style_color" rel="stylesheet" type="text/css"/>
	<link href="{{ asset('api/quiz/css/custom.css') }}" rel="stylesheet" type="text/css"/>

</head>
<!-- BEGIN BODY -->
<body class="page-full-width">
	<!-- BEGIN CONTAINER -->
	<div class="page-container">
		<!-- BEGIN CONTENT -->
		<div class="page-content-wrapper">
			<div class="page-content">	
				<?php 
				$i = $qno = 1; 
				foreach($attempt->page_layout as $pno => $layout) {
					if($page == $pno) {
						$qno = $i;
					}
					$i += count($layout);
				}
				?>
				<div class="row">
					<div class="col-xs-12 col-md-12 custom-title">
						<h4><strong>{{ $quiz->quiz_name }}</strong><a class="pull-right" href="{{url('api/quiz/backurl')}}"><b><</b> Back</a></h4>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-lft-0 padding-rgt-0 mobile-view">
						<form id="quizForm" action="{{ url('api/quiz/attempt/'.$attempt->attempt_id) }}" method="POST" accept-charset="UTF-8">
							@if(!empty($quiz->duration))
							<link href="{{ asset('api/quiz/assets/countdown/jquery.countdown.css') }}" rel="stylesheet" type="text/css"/>
							<div class="panel panel-default margin-btm-0">
								<div class="panel-heading sequential-panel-header quiz-name-head center">
							 		<div class="timer-holder">
							 			<h3><span id="timer"></span></h3>
							 		</div>
								</div>
							</div>
							@endif
							<style type="text/css">
								.qus-box .q-left{ float: left; }
								.qus-box .q-right { margin-left: 40px; display: block; }
							</style>
							<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 padding-lft-0 padding-rgt-0">
								<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin qus-box">
									<?php $p = $page; $count_hr = 1;?>
									@foreach($attemptdata as $q)
									<div class="panel-body qus-panel @if($count_hr++ < count($attemptdata)) question-desc @endif">
										<div class="qus-number">Q{{ $qno++ }}</div>
										<div class="qus-heading">
											@if($q->question_type == 'MCQ')
											<input type="hidden" name="q[]" value="{{$q->question_id}}">
											<div>{!! $q->question_text !!}</div>
											<div class="radio-list">
											<?php
												$answer_data = [];
												foreach ($q->answers as $key => $answer) {
													$checked = '';
													$acount = array_search($key, $q->answer_order);
													if($q->user_response == $answer['answer'])
														$checked = ' checked = "checked"';
													$answer_data[$acount]['a'] = '<div class="row"><div class="col-md-12"><label class="q-left"><input type="radio" name="q:'.$q->question_id.'" value="'.$acount.'"'.$checked.' > </label>';
													$answer_data[$acount]['b'] = '<div class="q-right" style="font-weight:normal;">'.$answer['answer'].'</div></div></div>';
												}
												$a = range('a', 'z');
												array_unshift($a, 0);
												ksort($answer_data);
												$final = [];
												foreach ($answer_data as $key => $value) {
													$final[$key] = $value['a'].'<div style="float:left;font-weight:normal;">&nbsp;'.next($a).')</div> '.$value['b'];
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
								<input type="hidden" name="prev_page" value="{{ $p - 1 }}">
								<input type="hidden" name="next_page" value="{{ $p + 1 }}">
								<div class="page center ques-btn">
							      	<input type="submit" class="btn green-meadow btn-sm" id="cleartoasts" name="prev" @if($p == 0) disabled @endif value="<< Prev">
							      	<button type="button" class="btn green-meadow btn-sm" id="cleartoasts" data-toggle="modal" href="#responsive">List</button>
							      	<input type="submit" class="btn green-meadow btn-sm" id="cleartoasts" name="next" value="Next >>">
						  		</div>
					  		</div>
				  		</form>
				  		<p>&nbsp;</p>
				  		<p>&nbsp;</p>
					</div>
				</div>
			</div><!--page content-->			
		</div>			
	</div>
	<!-- responsive -->
	<div id="responsive" class="modal fade cust-respons" tabindex="-1" aria-hidden="true">
		<div class="modal-dialog modal-full">
			<div class="modal-content">
				<div class="modal-header">
					<button type="button" class="close" data-dismiss="modal" aria-hidden="true"></button>
					<h4 class="modal-title">Questions</h4>
				</div>
				<div class="modal-body">
					<div class="center">
						<ul class="question">
							<?php 
							$j = 1;
							foreach($attempt->page_layout as $pno => $layout) {
								if($page == $pno) {
									foreach($layout as $q) {
										echo '<li><a href="#" onclick="return false"><div class="quiz-no quiz-active">'.$j++.'</div></a></li>';
									}
								} else {
									$answered = Session::get('assessment.'.$attempt->attempt_id.'.question_answered');
									foreach($layout as $q) {
										$blur = (in_array($q, $answered))? '' : 'quiz-gray';
										echo '<li><a href="'.url('api/quiz/attempt/'.$attempt->attempt_id.'?page='.$pno).'" ><div class="quiz-no '.$blur.'">'.$j++.'</div></a></li>';
									}
								}
							}
							?>
						</ul>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!--modal window-->
	<script src="{{ asset('api/quiz/js/jquery.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('api/quiz/js/jquery-ui.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('api/quiz/js/bootstrap.min.js') }}" type="text/javascript"></script>
	<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>

	@if(!empty($quiz->duration))
	<?php
		$duration = ($attempt->started_on->timestamp + ($quiz->duration * 60)) - time();
		if($duration < 0) $duration = 0;
	?>
	<script src="{{ asset('api/quiz/assets/countdown/jquery.plugin.js') }}"></script>
	<script src="{{ asset('api/quiz/assets/countdown//jquery.countdown.js') }}"></script>
	<script>
	$(function () {
		$('#timer').countdown({
			until: +{{ $duration }},
			onExpiry: autoSubmitQuiz,
			compact: true,
			layout: '<strong>Time left: </strong>{hnn}{sep}{mnn}{sep}{snn}'
		});
	});
	function autoSubmitQuiz() {
		$('#quizForm').submit();
	}
	</script>
	@endif
</body>
</html>