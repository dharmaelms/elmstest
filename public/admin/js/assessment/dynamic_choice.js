$(function(){
	var choice = "<div class=\"form-group\" style=\"display:none\"><label for=\"answer[{CHOICE_NUM}]\" class=\"col-md-2 col-lg-2 control-label\">{CHOICE_LABEL}</label><div class=\"col-md-10 col-lg-10 controls\"><div class=\"col-md-8 col-lg-8 rich-text-content\"><textarea name=\"answer[{CHOICE_NUM}]\" id=\"answer-{CHOICE_NUM}\" contenteditable=\"true\"placeholder=\"Please type the answer\" class=\"form-control\"></textarea></div><div class=\"col-md-4 col-lg-4\"><div class=\"editor-media\"><button type=\"button\" class=\"btn btn-primary btn-sm media-list-btn\" data-bind-to=\"answer-{CHOICE_NUM}\"><i class=\"fa fa-video-camera\"></i><span>&nbsp;Add media from library</span></button></div><input type=\"radio\" name=\"correct_answer\" value=\"{CHOICE_NUM}\" required/> Mark as Correct Answer</div><div class=\"col-md-8 col-lg-8\"><a href=\"javascript:;\" class=\"rationale\" data-id=\"rationale_box_{CHOICE_NUM}\"><button class=\"btn btn-circle btn-success btn-xs\"><i class=\"fa fa-plus\"></i></button> Add Rationale</a></div></div></div>";

    var rationale = "<div id=\"rationale_box_{CHOICE_NUM}\" class=\"form-group\" style=\"display:none\"> <label for=\"rationale[{CHOICE_NUM}]\" class=\"col-md-2 col-lg-2 control-label\">{RATIONALE_LABEL}</label> <div class=\"col-md-10 col-lg-10 controls\"> <div class=\"col-md-8 col-lg-8 rich-text-content\"> <textarea name=\"rationale[{CHOICE_NUM}]\" id=\"rationale-{CHOICE_NUM}\" contenteditable=\"true\" placeholder=\"Please type the rationale\" class=\"form-control\"></textarea> </div> <div class=\"col-md-4 col-lg-4\"> <div class=\"editor-media\"> <button type=\"button\" class=\"btn btn-primary btn-sm media-list-btn\" data-bind-to=\"rationale-{CHOICE_NUM}\"> <i class=\"fa fa-video-camera\"></i> <span>Add media from library</span> </button> </div> </div> </div> </div>";

	$("#add-choice").click(function(){
		var choiceNum = parseInt($("#choice_count").val());
		var choiceLabelNum = choiceNum+1;
		$(choice.replace(/{CHOICE_NUM}/g, choiceNum).replace(/{CHOICE_LABEL}/g, "Choice "+choiceLabelNum)).insertBefore("#choice_div").slideDown({ duration : 400 });
		$("#choice_div").before(rationale.replace(/{CHOICE_NUM}/g, choiceNum).replace(/{RATIONALE_LABEL}/g, "Choice "+choiceLabelNum+" Rationale"));

		$("#choice_count").prop({value : (choiceNum + 1)});

		CKEDITOR.inline("answer-"+choiceNum,{customConfig: $configPath});
		CKEDITOR.inline("rationale-"+choiceNum,{customConfig: $configPath});

	});

});