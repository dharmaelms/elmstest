$(document).ready(function(){
	$("form.form-question").submit(function(event){
		for(instanceName in CKEDITOR.instances)
			CKEDITOR.instances[instanceName].updateElement();
		var formInstance = $(this);
		$("textarea").each(function(){
			var textareaInstance = $(this);
			var textareaName = $(this).attr("name");
			var mediaCount = 0;
			$($(this).val()).find(".question-media").each(function(){
				formInstance.append($("<input type=\"hidden\" name=\"question_dam_media_"+textareaName+"["+mediaCount+"]\">").val($(this).data("media-id")));
				++mediaCount;
			});
		});
	});
});