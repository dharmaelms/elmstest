<div class="row margin-0">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12 lg-margin qus-box">
		<div class="panel-body qus-panel">
			<div class="qus-number"><b>Q&nbsp;{{ $data["total_attempted_questions"]+1 }}&#46;</b></div>
			<div class="qus-heading">
				<span>{!! $data["question"]->question_text !!}</span>
			 	<div class="radio-list" style="margin-top:30px;">
<?php 
			 	$answerChunks = collect($data["question"]->answers)->chunk(2);
			 	$answerCount = 0; 
?>

				@foreach($answerChunks as $chunk)
				<div class="row">
					@foreach($chunk as $answerData)
					<div class="col-sm-12 col-xs-12 col-md-6 col-lg-6" style="max-height:300px;overflow-y:auto;">
						<label class="lft-div">
							<input type="radio" name="{{ $data["question"]->_id }}" value="{{ $answerCount }}">
						</label>
						<div class="right-div">
						{!! $answerData["answer"] !!}
						</div>
					</div>
<?php
						++$answerCount;
?>
					@endforeach
				</div>
				@endforeach	
				</div>
			</div>
		</div>
	</div>
</div>