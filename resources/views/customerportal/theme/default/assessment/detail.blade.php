@section('content')
	<div class="page-bar">
		<ul class="page-breadcrumb">
			<li><a href="{{url('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
			@if($attempts->count() > 0)
			<li><a href="{{url('/assessment?filter=attempted')}}">Assessment</a><i class="fa fa-angle-right"></i></li>
			@else
			<li><a href="{{url('assessment')}}">Assessment</a><i class="fa fa-angle-right"></i></li>
			@endif
			<li><a href="#">{{$quiz->quiz_name}}</a></li>
		</ul>
	</div>
	<div class="row md-margin">
		<div class="col-md-12 col-sm-12 col-xs-12">
			<div class="custom-box">
				<h3 class="page-title-small margin-top-0">
					{{ $quiz->quiz_name }} 
					&nbsp;
					@if(isset($quiz->users_liked))
						@if(in_array(Auth::user()->uid, $quiz->users_liked))
							<i id="{{$quiz->quiz_id}}" data-action="unstar" class="fa fa-star font-20 yellow star-quiz" style="cursor:pointer"></i>
						@else
							<i id="{{$quiz->quiz_id}}" data-action="star" class="fa fa-star font-20 l-gray star-quiz" style="cursor:pointer"></i>
						@endif
					@else
						<i id="{{$quiz->quiz_id}}" data-action="star" class="fa fa-star font-20 l-gray star-quiz" style="cursor:pointer"></i>
					@endif
				</h3>
				<div class="row">
					<div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 sm-margin">
						<img src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/packetpage_assessment.png') }}" class="img-responsive center center-align" alt="{{ $quiz->quiz_name }}">
					</div>
					<div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
						<div class="font-16 xs-margin">
						  	<span class="start">STARTS:</span>
						  	@if(!empty($quiz->start_time))
								<strong>{{ $quiz->start_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</strong>
							@else
								<strong>N/A</strong>
							@endif
							<br>
							<span class="end">ENDS:</span>
							@if(!empty($quiz->end_time))
								<strong>{{ $quiz->end_time->timezone(Auth::user()->timezone)->format('D, d M Y h:i A') }}</strong>
							@else
								<strong>N/A</strong>
							@endif
						</div>
						<p class="font-16 sm-margin">
							@if($quiz->duration != 0)
							<i class="fa fa-clock-o font-18"></i>&nbsp;&nbsp;<strong>{{ $quiz->duration.' mins' }}</strong>
							@endif
							@if(!$program->isEmpty())
								&nbsp;&nbsp;|&nbsp;&nbsp;
								<strong>Source:</strong>
								@foreach($program as $p)
									{{ $p->program_title.'('.Lang::get('program.program').') ' }}
								@endforeach
							@endif
						</p>
						<div class="row">
							<?php 
								$closed = $attempts->where('status','CLOSED');
								if($quiz->attempts == 0)
									$attempt_left = 'No attempt limit';
								else
									$attempt_left = $quiz->attempts - $attempts->count().' attempts left';
							?>
							<div class="col-lg-3 col-md-4 col-sm-4 col-xs-12 xs-margin">
								@if(count($quiz->questions) == 0)
									<a href="javascript:;" class="btn btn-default1 btn-lg">START QUIZ<br><span class="font-13">{{ $attempt_left }}</span></a>
								@elseif((empty($quiz->start_time) || $quiz->start_time->timestamp < time()) && (empty($quiz->end_time) || $quiz->end_time->timestamp > time()))
									@if($quiz->attempts == 0 || $quiz->attempts > $attempts->where('status','CLOSED')->count())
										<form action="{{ url('assessment/start-attempt/'.$quiz->quiz_id) }}" method="POST" accept-charset="utf-8">
											<input type="hidden" name="return" value="{{ Request::path() }}">
											@if($attempts->where('status', 'OPENED')->count() != 0)
											<button type="submit" class="btn btn-success btn-lg">RESUME<br><span class="font-13">{{ $attempt_left }}</span></button>
											@else
											<button type="submit" class="btn btn-success btn-lg" @if($quiz->duration != 0) onclick="return confirm('This assessment has a time limit. Are you sure that you wish to start?')" @endif>START QUIZ<br><span class="font-13">{{ $attempt_left }}</span></button>
											@endif
										</form>
									@else
										<a href="javascript:;" class="btn btn-default1 btn-lg">COMPLETED<br><span class="font-13">No attempts left</span></a>
									@endif
								@else
									<a href="javascript:;" class="btn btn-default1 btn-lg">START QUIZ<br><span class="font-13">{{ $attempt_left }}</span></a>
								@endif
							</div>
							@if($closed->count() > 0)
							<div class="col-lg-9 col-md-8 col-sm-8 col-xs-12 xs-margin">
								<strong>Last Attempt:</strong><br>
								<span class="font-44 red"><strong>{{ round(($closed->last()->obtained_mark/$closed->last()->total_mark)*100).'%' }} </strong></span>
							</div>
							@endif
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	@if($attempts->count() > 0)
	<div class="row">
		<div class="col-md-12">
			<div class="panel panel-default">
				<div class="panel-heading sequential-panel-header center">
					 Summary of Attempts
				</div>
				<div class="panel-body">
					<div class="table-responsive">
						<table class="table">
							<thead>
								<th>#</th>
								<th>Score</th>
								<th>Time Taken</th>
								<th>Status</th>
								<th>Started On</th>
								<th>Completed On</th>
								@if($quiz->review_options['the_attempt'])
								<th>Review</th>
								@endif
							</thead>
							<tbody>
								<?php $i = $attempts->count() ?>
								@foreach($attempts->reverse() as $attempt)
								<tr>
									<td>{{$i--}}</td>
									<td>
										@if($attempt->status == 'CLOSED' || !empty($attempt->obtained_mark)) 
			
										{{ round(($attempt->obtained_mark/$attempt->total_mark)*100).'%' }} 
											
										@endif
									</td>
									<td>
										@if(!empty($attempt->started_on) && !empty($attempt->completed_on))
											{{ $attempt->completed_on->diffForHumans($attempt->started_on, true) }}
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
									@if($quiz->review_options['the_attempt'])
									<td>
										@if($attempt->status == 'CLOSED')
										<a href="{{ url('assessment/report/'.$attempt->attempt_id) }}">view</a>
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
@stop