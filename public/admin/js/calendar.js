$(document).ready(function(){
	$('.calender-icon').on('click',function(e){
	    $(this).next().focus();
	    e.preventDefault();
	});
});