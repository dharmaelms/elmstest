@if(isset($data["data_flag"]) && ($data["data_flag"]))
<style>
.qg-question-review .portlet-title .caption
{
	font-size : 13px;
	font-weight : bold;
}
.portlet .portlet-body  .well{overflow: auto;}
/*.alert .q-review-icon-container
{
	position : relative;
}

.alert .q-review
{
	position: absolute !important;
	left: 130px !important;
	top: 40px !important;
}*/
</style>
<div class="row qg-question-review">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<input type="hidden" name="question_review_data_flag" value="TRUE">
		<input type="hidden" name="total_attempted_questions" value="{{ $data["total_attempted_questions"] }}">
		@if($data["is_answer_selected"])
			@if($data["is_answer_correct"])
				<div class="alert alert-success">
					<table>
						<tr>
							<td class="q-review-icon-container" width="70px"><img src="{{ URL::to("portal/theme/default/img/icons/correct-icon.jpg") }}" alt="Correct answer"></td>
							<td class="q-review"><b><?php echo Lang::get('assessment.your_ans_correct'); ?></b></td>
						</tr>
					</table>											
				</div>
				@if(!empty($data["correct_answer_rationale"]))
					<div class="portlet">
						<div class="portlet-title">
							<div class="caption">
								<span><?php echo Lang::get('assessment.your_ans_correct_because'); ?></span>
							</div>
							<div class="tools">
								<a title="" data-original-title="" class="collapse" href="javascript:void;"></a>
							</div>
						</div>
						<div style="display: block;" class="portlet-body">
							<div class="well">
								{!! $data["correct_answer_rationale"] !!}
							</div>
						</div>
					</div>
				@endif
			@else
				<div class="alert alert-warning">
					<table>
						<tr>
							<td class="q-review-icon-container" width="70px"><img alt="Wrong answer" src="{{ URL::to("portal/theme/default/img/icons/wrong-icon.png") }}" class="img-responsive"></td>
							<td class="q-review"><b><?php echo Lang::get('assessment.your_ans_incorrect'); ?></b></td>
						</tr>
					</table>
				</div>
				@if(!empty($data["incorrect_answer_rationale"]))
					<div class="portlet">
						<div class="portlet-title">
							<div class="caption">
								<span><?php echo Lang::get('assessment.your_answer_incorrect_beacuse'); ?></span>
							</div>
							<div class="tools">
								<a title="" data-original-title="" class="collapse" href="javascript:void;"></a>
							</div>
						</div>
						<div style="display: block;" class="portlet-body">
							<div class="well">
								{!! $data["incorrect_answer_rationale"] !!}
							</div>
						</div>
					</div>
				@endif
				<div class="alert alert-success">
					<div class="row">
						<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
							<span><strong><?php echo Lang::get('assessment.correct_answer'); ?></strong></span>
							{!! $data["correct_answer"] !!}
						</div>
					</div>													
				</div>
				@if(!empty($data["correct_answer_rationale"]))
					<div class="portlet">
						<div class="portlet-title">
							<div class="caption">
								<span><?php echo Lang::get('assessment.correct_answer_because'); ?></span>
							</div>
							<div class="tools">
								<a title="" data-original-title="" class="collapse" href="javascript:void;"></a>
							</div>
						</div>
						<div style="display: block;" class="portlet-body">
							<div class="well">
								{!! $data["correct_answer_rationale"] !!}
							</div>
						</div>
					</div>
				@endif
			@endif
		@else
			<div class="alert alert-success">
				<div class="row">
					<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
						<span><strong><?php echo Lang::get('assessment.correct_answer'); ?></strong></span>
						{!! $data["correct_answer"] !!}
					</div>
				</div>													
			</div>
			@if(!empty($data["correct_answer_rationale"]))
				<div class="portlet">
					<div class="portlet-title">
						<div class="caption">
							<span><?php echo Lang::get('assessment.last_ques_in_Qgenerator'); ?></span>
						</div>
						<div class="tools">
							<a title="" data-original-title="" class="collapse" href="javascript:void;"></a>
						</div>
					</div>
					<div style="display: block;" class="portlet-body">
						<div class="well">
							{!! $data["correct_answer_rationale"] !!}
						</div>
					</div>
				</div>
			@endif
		@endif
	</div>
</div>
@else
	<div class="note note-warning">
		<input type="hidden" name="question_review_data_flag" value="FALSE">
		<span><strong><?php echo Lang::get('assessment.something_went_worng_try_again'); ?></strong></span>
	</div>
@endif