@section('content')
	<div class="page-bar">
		<ul class="page-breadcrumb">
			<li><a href="{{url('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
			<li><a href="{{url('/assessment')}}">Assessment</a><i class="fa fa-angle-right"></i></li>
			<li><a href="#">{{$quiz->quiz_name}}</a></li>
		</ul>
	</div>
	<!--content starts here-->
	<div class="row">
		<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
			<div class="row">
				<div class="panel panel-default submit-panel">
					<div class="panel-heading qus-main-panel-head">
						<b>{{ $quiz->quiz_name }}</b>
						<div class="pull-right">
							<div id="timer"></div>
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
									$answered = Session::get('assessment.'.$attempt->attempt_id.'.question_answered');
									foreach($layout as $q) {
										$blur = (in_array($q, $answered))? 'answered' : 'not-answered';
										echo '<li><a class="'.$blur.'" href="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno).'" >'.$j++.'</a></li>';
									}
								}
								?>
								</ul>							
							</div>
						</div>
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="panel panel-default">
								<div class="panel-body">
									<h4 class="sm-margin"><strong>Submit Assessment?</strong></h4>
									@if(!empty($message))
									<div class="alert alert-warning">
										<center><b>{{ $message }}</b></center>
									</div>
									@endif
									<ul class="submit-quiz-list">
										<li class="submit-quiz-item">
											No. of questions attemped <span class="pull-right"> {{ count($attemptdata) }} </span>
										</li>
										<li class="submit-quiz-item">
											No. of questions skipped <span class="pull-right"> {{ count($attempt->questions) - count($attemptdata) }} </span>
										</li>
									</ul>
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 quiz-retry quiz-submit top-lg-margin">
										<form action="{{ url('assessment/close-attempt/'.$attempt->attempt_id) }}" method="POST" accept-charset="utf-8">
											<a href="{{ url('assessment/attempt/'.$attempt->attempt_id) }}" class="btn green"> Back to attempt</a>
											<button type="submit" class="btn red" onclick="return confirm('Are you sure you want to submit this assessment?')">Submit</button>
										</form>
									</div>
								</div>
							</div>
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
			compact: true,
			layout: '<b>Time Left: </b>{hnn}{sep}{mnn}{sep}{snn}'
		});
	});
	</script>
	@endif
@stop