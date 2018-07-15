
function updateHeight(){
	setTimeout(function(){
		/* Code to reset the iframe height starts here*/
		var frame = $("iframe:not(.question-media)", window.parent.document);
		var heightToSet = $('#datatable').height() > 400 ? $(window.parent.window).height() / 1.5 : ($('#datatable').height() + 140);
		frame.animate({height:heightToSet+"px"});
		frame.css({"max-height": ($(window.parent).height() / 100 ) * 58 + "px"});
		/* Code to reset the iframe height ends here*/
	},250);
}

$(document).ready(function(){
	
});