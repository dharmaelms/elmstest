@if(isset($data["data_flag"]) && ($data["data_flag"]))
	<input type="hidden" name="question_uid" value="{{ $data["question"]->_id }}">
	<input type="hidden" name="question_data_flag" value="TRUE">
	@if(isset($data["is_last_question_in_keyword"]) && ($data["is_last_question_in_keyword"]))
	<div class="note note-info" id="last-question-note">
		<span><strong><?php echo Lang::get('assessment.last_ques_in_concept'); ?></strong></span>
		<input type="hidden" name="is_last_question" value="TRUE">
	</div>
	@elseif(isset($data["is_last_question_in_section"]) && ($data["is_last_question_in_section"]))
	<div class="note note-info" id="last-question-note">
		<span><strong><?php echo Lang::get('assessment.last_ques_in_section'); ?></strong></span>
		<input type="hidden" name="is_last_question" value="TRUE">
	</div>	
	@elseif(isset($data["is_last_question_in_quiz"]) && ($data["is_last_question_in_quiz"]))
	<div class="note note-info" id="last-question-note">
		<span><strong><?php echo Lang::get('assessment.last_ques_in_Qgenerator'); ?></strong></span>
		<input type="hidden" name="is_last_question" value="TRUE">
	</div>
	@else
		<input type="hidden" name="is_last_question" value="FALSE">
	@endif
	@include("portal.theme.default.assessment._mcq_question")
@else
<div class="note note-warning">
	<span><strong>{{ $data["message"] }}</strong></span>
</div>
@endif