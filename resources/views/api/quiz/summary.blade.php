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
				<div class="row">
					<div class="col-xs-12 col-md-12 custom-title">
						<h4><strong>{{ $quiz->quiz_name }}</strong></h4>
					</div>
				</div>
				<div class="row">
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
					<div class="col-xs-12 col-md-12 center">
						<div class="sm-margin"></div><!--space-->
						<h4><strong>Submit assessment?</strong></h4>
					</div>
					@if(!empty($message))
					<div class="alert alert-warning">
						<center><b>{{ $message }}</b></center>
					</div>
					@endif
					<div class="col-xs-12 col-md-12 sm-margin">
						<table class="center-align">
							<tr>
								<td width="220px"><strong>No. of questions attempted</strong></td>
								<td>{{ count($attemptdata) }}</td>
							</tr>
							<tr>
								<td width="220px"><strong>No. of questions skipped</strong></td>
								<td>{{ count($attempt->questions) - count($attemptdata) }}</td>
							</tr>
						</table>
					</div>
				</div>
				<div class="md-margin">
					<div class="row lgray-row">
						<div class="col-xs-12 col-md-12">
							<div class="center">
								<p><strong>Question List</strong></p>
								<p class="font-13">Click on any question below to revise or re-attempted a question</p>
								<ul class="question">
									<?php 
									$j = 1;
									foreach($attempt->page_layout as $pno => $layout) {
										$answered = Session::get('assessment.'.$attempt->attempt_id.'.question_answered');
										foreach($layout as $q) {
											$blur = (in_array($q, $answered))? '' : 'quiz-gray';
											echo '<li><a href="'.url('api/quiz/attempt/'.$attempt->attempt_id.'?page='.$pno).'" ><div class="quiz-no '.$blur.'">'.$j++.'</div></a></li>';
										}
									}
									?>
								</ul>
							</div>
						</div>
					</div><!--gray-->
				</div><!--1st detail row-->
				<div class="row md-margin">
					<div class="col-md-12 col-xs-12 center">
						<form action="{{ url('api/quiz/close-attempt/'.$attempt->attempt_id) }}" method="POST" accept-charset="utf-8">
							<button type="submit" class="btn green-meadow btn-sm" onclick="return confirm('Are you sure you want to submit this assessment?')">Submit</button>
						</form>
					</div>
				</div><!--btn-->

			</div><!--page content-->			
		</div>			
	</div>

	<script src="{{ asset('api/quiz/js/jquery.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('api/quiz/js/jquery-ui.min.js') }}" type="text/javascript"></script>
	<script src="{{ asset('api/quiz/js/bootstrap.min.js') }}" type="text/javascript"></script>
	@if(!empty($quiz->duration))
	<?php
		$duration = ($attempt->started_on->timestamp + ($quiz->duration * 60)) - time();
		if($duration < 0) $duration = 0;
	?>
	<script src="{{ asset('api/quiz/assets/countdown/jquery.plugin.js') }}"></script>
	<script src="{{ asset('api/quiz/assets/countdown//jquery.countdown.js') }}"></script>
	<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>

	<script>
	$(function () {
		$('#timer').countdown({
			until: +{{ $duration }},
			compact: true,
			layout: '<strong>Time left: </strong>{hnn}{sep}{mnn}{sep}{snn}'
		});
	});
	</script>
	@endif
</body>
<!-- END BODY -->
</html>