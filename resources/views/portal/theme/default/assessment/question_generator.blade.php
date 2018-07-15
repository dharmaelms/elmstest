@section("content")
<style>
.qg-backdrop
{
	position : fixed;
	top : 0%;
	right : 0%;
	bottom : 0%;
	left : 0%;
	z-index : 1000;
	background-color : rgba(102, 102, 102, 0.32);
}

.qg-loading-bar
{
	position: fixed;
	top: 20%;
	left: 40%;
	z-index: 1100;
	min-width: 175px;
	padding: 2px;
	background-color: rgb(66, 139, 202);
	text-align: center;
}

.qg-loading-bar span
{
	color: white;
	font-weight: bold;
}

.select2-container .select2-selection--single
{
	height : 32px !important;
}

</style>
<link rel="stylesheet" type="text/css" href="{{ URL::to("portal/theme/default/css/responsive-iframe.css") }}">
<link rel="stylesheet" type="text/css" href="{{ URL::to("portal/theme/default/css/select2.min.css") }}">
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
<div class="page-bar">
	<ul class="page-breadcrumb">
		<li><a href="{{url("/")}}">Home</a><i class="fa fa-angle-right"></i></li>
		<li><a href="{{url("/assessment?filter=attempted".'&'.$requestUrl)}}"><?php echo Lang::get('assessment.assessment'); ?></a><i class="fa fa-angle-right"></i></li>
		<li><a href="javascript:void;">{{$data["question_generator"]["name"]}}</a></li>
	</ul>
</div>
<div class="row">
	<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
		<div class="panel panel-default quiz-name">
			<div class="panel-heading qus-main-panel-head">
				<b>{{ $data["question_generator"]["name"] }}</b>
			@if(isset($data["question_generator"]["instructions"]) && !empty($data["question_generator"]["instructions"]))
				<a href="javascript:void;" data-toggle="modal" data-target="#qg_instructions"><i class="fa fa-question-circle"></i></a>
			@endif
				<input type="hidden" name="total_no_of_questions" value={{ $data["total_no_of_questions"] }}>
				<input type="hidden" name="total_question_limit" value="{{ $data["total_question_limit"] }}">
				<div class="pull-right center">
					<span class="margin-right-10"><?php echo Lang::get('assessment.total_question_completed'); ?></span>
					<span style="font-size: 22px !important;line-height: 1;">
						<span id="total_attempted_questions_count">{{ $data["total_attempted_questions"] }}</span> / {{ ($data["total_no_of_questions"] >= $data["total_question_limit"])? $data["total_question_limit"] : $data["total_no_of_questions"] }}</span>
				</div>
			</div>
		</div>
	</div>
</div>
<div class="row">
	<div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
		<div class="panel-body">
			<div class="row">
			@if($data["is_sections_enabled"])
				<div class="col-xs-12 col-sm-12 col-md-3 col-lg-3 xs-margin">
					<div class="form-group">
						<label class="col-xs-12 col-sm-12 col-md-4 col-lg-4 control-label">
							<span><?php echo Lang::get('assessment.section'); ?>&nbsp;:</span>
						</label>
						<div class="col-xs-12 col-sm-12 col-md-8 col-lg-8">
							<select name="q_g_sections" id="q_g_sections" class="form-control">
							@foreach($data["sections"] as $sectionData)
								<option value="{{ $sectionData["id"] }}" {{ (isset($sectionData["is_active_section"]) && ($sectionData["is_active_section"] === true))? "selected" : "" }}  data-enable_keyword_search={{ (isset($sectionData["enable_keyword_search"]) && ($sectionData["enable_keyword_search"]))? "true" : "false" }}>{{ $sectionData["title"] }}</option>
							@endforeach
							</select>
						</div>
					</div>
				</div>
			@endif
				<div id="keyword_search_container" class="col-md-3 col-sm-3 col-xs-12 xs-margin" style="display:none;">
					<div class="form-group">
						<select name="question_keyword" id="question_keyword" value="{{ isset($data["active_keyword"])? $data["active_keyword"] : "" }}" class="form-control" style="width:100%;">
						@if(isset($data["active_keyword"]))
							<option value="{{ $data["active_keyword"] }}">{{ $data["active_keyword"] }}</option>
						@endif
						</select>
					</div>
				</div>
				<div class="col-md-4 col-sm-4 col-xs-12 xs-margin pull-right qa-btns">
					@if(!isset($data['sections']))
						<button type="button" id="get-question-btn" class="btn btn-primary btn-circle"><?php echo Lang::get('assessment.get_question'); ?> | <i class="fa fa-angle-double-right"></i></button>&nbsp;&nbsp;
					@else
						<button type="button" id="get-question-btn" class="btn btn-primary btn-circle" style="display:none;"><?php echo Lang::get('assessment.get_question'); ?> | <i class="fa fa-angle-double-right"></i></button>&nbsp;&nbsp;
					@endif
					<a href="{{url('/assessment/detail/'.$data['quiz_id'].'?'.$requestUrl)}}" id="qg-review-btn" class="btn btn-default btn-circle" style="{{ ($data["total_attempted_questions"] > 0)? "display : inline-block" : "display : none" }}"><i class="fa fa-angle-double-left blue"></i> | <?php echo Lang::get('assessment.view_summary'); ?> </a>
				</div>
			</div>
			<div class="note note-success" id="question-generator-completion-note" style="display:none;">
				<span><strong><?php echo Lang::get('assessment.u_have_attemented_All_the_question_inQgenerator'); ?></strong></span>
			</div>
			<div id="question_container" style="display:none;">
			</div>
			<div class="page pull-left" id="check-answer-btn-container" style="display:none;">
				<input type="button" id="check-answer-btn" class="btn btn-success" value="Check answer">
				<span id="no-selection-message" class="red"></span>
			</div>
			<input type="hidden" name="quiz_uid" value="{{ $data["question_generator"]["_id"] }}">
			<input type="hidden" name="attempt_uid" value="{{ $data["quiz_attempt_id"] }}">
			<input type="hidden" name="is_sections_enabled" value="{{ $data["is_sections_enabled"] }}">
		</div>
	</div>
</div>
<div class="qg-backdrop" style="display:none;">
</div>
<div class="qg-loading-bar" style="display:none;">
	<span><?php echo Lang::get('assessment.loading..'); ?></span>
</div>
@if(isset($data["question_generator"]["instructions"]) && !empty($data["question_generator"]["instructions"]))
<div id="qg_instructions" class="modal fade" tabindex="-1" role="dailog" aria-labelledby="qg_instructions_title">
	<div class="modal-dialog" role="document">
		<div class="modal-content">
			<div class="modal-header">
				<button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
				<h4 class="modal-title center" id="qg_instructions_title"><strong>{{Lang::get('assessment/detail.instructions_header_title')}}</strong></h4>
			</div>
			<div class="modal-body">
				<div class="row">
					<div class="col-md-12">
						{!! $data["question_generator"]["instructions"] !!}
					</div>
				</div>
			</div>
			<div class="modal-footer center">
				<button type="button" class="btn-success" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong>OK</strong></button>
			</div>
		</div>
	</div>
</div>
@endif
<script type="text/javascript" src="{{ URL::to("portal/theme/default/plugins/select2.min.js") }}"></script>
<script type="text/javascript" src="{{ URL::to("portal/theme/default/plugins/jquery.ba-bbq.min.js") }}"></script>
<script>
	$(document).ready(function(){
		var getQuestionURL = "{{ URL::to("assessment/question") }}";
		var checkAnswerURL = "{{ URL::to("assessment/attempt-question") }}";
		var getKeywordsURL = "{{ URL::to("assessment/question-keywords") }}";

		var params = {};

		var getQuestionBtn = $("#get-question-btn");
		var keywordSearchContainer = $("#keyword_search_container");

		params["setup_layout"] = "FALSE";
		params["quiz_uid"] = $("input[name=quiz_uid]").val();
		params["attempt_uid"] = $("input[name=attempt_uid]").val();

		var getQueryString = function(optionalParams){
			if(parseInt($("input[name=is_sections_enabled]").val()))
				params["section_uid"] = $("select[name=q_g_sections]").val();
			if(optionalParams !== null && (typeof optionalParams === "object"))
			{
				var mergedParams = {};
				for(var tmpProp1 in params)
					mergedParams[tmpProp1] = params[tmpProp1];
				for(var tmpProp2 in optionalParams)
					mergedParams[tmpProp2] = optionalParams[tmpProp2];
				return $.param(mergedParams);
			}
			else
				return $.param(params); 
		};

		var getQuestion = function(queryString){
			$("#check-answer-btn-container, #question-generator-completion-note").css({
				display : "none"
			});

			getQuestionBtn.css({ display : "none" });

			$(".qg-backdrop, .qg-loading-bar").css({
				display : "block"
			});

			var xmlHttpRequest = $.ajax({
				type : "POST",
				url : getQuestionURL,
				data : queryString,
				contentType : "application/x-www-form-urlencoded; charset=UTF-8",
				dataType : "html"
			});

			xmlHttpRequest.done(function(response, textStatus, jqXHR){
				$("#question_container").html(response).slideDown({
					duration : 600
				});

				if($("input[name=question_data_flag]").val() === "TRUE")
				{
					$("#q_g_sections, #question_keyword").prop({ disabled : true });
					$("#total_attempted_questions").html($("input[name=total_attempted_questions]").val());
					$("#check-answer-btn-container").slideDown({
						duration : 600
					});
				}
			});

			xmlHttpRequest.fail(function(jqXHR, textStatus, errorThrown){
				alert("Something went wrong. Please refresh the page.");
			});

			xmlHttpRequest.always(function(){
				$(".qg-backdrop, .qg-loading-bar").css({
					display : "none"
				});
			});
		};


    <?php $enableKeywordSearchByDefault = false; ?>
	@if(isset($data["is_sections_enabled"]) && ($data["is_sections_enabled"]))
		if($("#q_g_sections option:selected").data("enable_keyword_search") === true)
	<?php $enableKeywordSearchByDefault = true; ?>
	@elseif(isset($data["enable_keyword_search"]) && ($data["enable_keyword_search"]))
	<?php $enableKeywordSearchByDefault = true; ?>
	@endif

	@if($enableKeywordSearchByDefault)
		keywordSearchContainer.show({
			duration : 400
		});

		getQuestionBtn.show({
			duration : 400
		});
	@endif
	
		getQuestionBtn.click(function(){
			getQuestion(getQueryString({ keyword : $("select[name=question_keyword]").val() }));
		});

		var checkAnswer = function(queryString){

			$("#check-answer-btn-container").css({
				display : "none"
			});

			$(".qg-backdrop, .qg-loading-bar").css({
				display : "block"
			});

			var xmlHttpRequest = $.ajax({
				type : "POST",
				url : checkAnswerURL,
				data : queryString,
				contentType : "application/x-www-form-urlencoded; charset=UTF-8",
				dataType : "html"
			});

			xmlHttpRequest.done(function(response, textStatus, jqXHR){
				$("#question_container").append(response);
				if($("input[name=question_review_data_flag]").val() === "TRUE")
				{
					if(parseInt($("input[name=total_attempted_questions]").val()) >= 1)
						$("#qg-review-btn").show({ duration : 400 });
					$("#total_attempted_questions_count").html($("input[name=total_attempted_questions]").val());

					var totalNoOfQuestions = parseInt($("input[name=total_no_of_questions]").val());
					var totalQuestionLimit = parseInt($("input[name=total_question_limit]").val());
					var totalAttemptedQuestions = parseInt($("input[name=total_attempted_questions]").val());

					if((totalNoOfQuestions > totalAttemptedQuestions) && (totalQuestionLimit > totalAttemptedQuestions))
					{
						if($("input[name=is_last_question]").val() === "FALSE")
							getQuestionBtn.show({ duration : 400 });

						$("#q_g_sections, #question_keyword").prop({ disabled : false });
					}
					else
					{
						$("#last-question-note").css({ display : "none" });
						$("#question-generator-completion-note").slideDown({
							duration : 600
						});
					}
				}
			});

			xmlHttpRequest.fail(function(jqXHR, textStatus, errorThrown){
				alert("Something went wrong. Please refresh the page.");
			});

			xmlHttpRequest.always(function(){
				$(".qg-backdrop, .qg-loading-bar").css({
					display : "none"
				});
			});
		};

		$("#check-answer-btn").click(function(){
			var checkAnswerParams = { question_uid : $("input[name=question_uid]").val() };
			var selectedAnswerIndex = $("input[name="+checkAnswerParams.question_uid+"]:checked").prop("value");
			if(selectedAnswerIndex !== undefined){
				checkAnswerParams["selected_answer_index"] = selectedAnswerIndex;
				checkAnswer(getQueryString(checkAnswerParams));
				$(".qus-heading :input").attr("disabled", true);
				$('#no-selection-message').html('');
			} else {
				$('#no-selection-message').html('{{ Lang::get("assessment.select_answer")}}');
				return false;
			}			
		});

		$("#question_keyword").select2({
			placeholder : "Search a concept",
			allowClear : true,
			ajax : {
				delay : 500,
				type : "POST",
				url : getKeywordsURL,
				data : function(params){
					return $.deparam(getQueryString({ keyword : params.term }));
				},
				contentType : "application/x-www-form-urlencoded; charset=UTF-8",
				dataType : "json",
				processResults : function(data, params){
					return { results : data };
				},
				cache : true
			}
		}).on("change", function(event){
			getQuestionBtn.show({
				duration : 400
			});
			// getQuestion(getQueryString({ keyword : $(this).val() }));
		});

		$("select[name=q_g_sections]").change(function(){
			$("#question_keyword").select2('val', ''); //resetting concept drop-down 
			if($(this).val() !== "")
			{
				var keywordSearchContainer = $("#keyword_search_container");
				if($(this).find("option:selected").data("enable_keyword_search") === true)
				{
					keywordSearchContainer.show({
						duration : 400
					});

					getQuestionBtn.show({
						duration : 400
					});

					$("#question_container").empty();
				}
				else
				{
					keywordSearchContainer.hide({
						duration : 400
					});
					// getQuestion(getQueryString());
				}
			}
			else
			{
				keywordSearchContainer.hide({
					duration : 400
				});

				getQuestionBtn.hide({
					duration : 400
				});
			}
		});
	});		
</script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
@stop