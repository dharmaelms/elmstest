var attempt = (function(){
	var saveAndNext = '#next',
		clearResponse = '#clear',
		reviewAndNext = '#review_url',
		question = '.page',
		question_list = '.question',
		section_list = '.getsection'
		section_id = section,
		current_page = page,
		timed_section = timed_sections,
		submit_section = '.submit_section',
		submit_quiz = '.submit_quiz',
		switch_section = false;
		next_page = page+1,
		interval = false,
		triggered = false,
		timetaken = 0,
        mark = '#mark',
        loader = '.qg-backdrop',
		reviewed = false;
	function init() { //initialises function call
		reviewAnswer();
		saveAnswer();
		moveToView();
		changePage();
		clearAnswer();
		switchSection();
		submitQuiz();
		timespend();
		$(question_list).find("[data-section-id='"+section_id+"']").removeClass('hide').show();
	}	
	function saveAnswer() { //save next
		$(saveAndNext).on('click', function(e){
			reviewed = switch_section = false;
			next_page = current_page + 1;
			triggered = true;
			submitAnswer();
			nextQuestion();
			e.preventDefault();
			return false;
		});
		return false;
	}
	function reviewAnswer() { //mark review and next
		$(reviewAndNext).on('click', function(e){
			reviewed = true;
			switch_section = false;
			next_page = current_page + 1;
			triggered = true;
			submitAnswer();
			nextQuestion();
			e.preventDefault();
			return false;
		});
		return false;
	}
	function changePage() { //question pagination listing
		$(question).on('click', function(e){
			reviewed = switch_section = false;
			page = $(this).data('preq');
			if (current_page == page) {
				return false;
			}
			else {
				next_page = page;
			}
			triggered = true;
			submitAnswer();
			nextQuestion();
			e.preventDefault();
			return false;
		});
		return false;
	}
	function clearAnswer() {
		$(clearResponse).on('click', function(e){
			triggered = true;
	        $('input:radio:checked').removeAttr('checked').parent().removeClass('checked');
	        review = false;
	        submitAnswer();
	        $(loader).hide();
	        return false;
		});
		return false;
	}
	function getData() { //generate data
		var data = { 
			'reviewed' : reviewed,
			'answer' : selectedAnswer(),
			'current_page' : current_page,
			'next_page' : next_page,
			'section' : section_id,
			'timetaken': timetaken,
		};
		return data;
	}
	function submitAnswer() { //save user response
		$(loader).show();
		if (triggered) {
			$.ajax({
				url:"/attempt/save-answer/"+attempt_id,
				method: 'POST',
				data: getData(),
				dataType:'json',
			}).success(function(response){
				if (response.status) {
					if (section_id == 0) {
						$(question_list).find('.page[data-preq="'+response.attempt.page+'"]').attr('class', 'page').addClass(response.attempt.class);
					} else {
							$(question_list).find('.page[data-section-id="'+response.attempt.section+'"][data-preq="'+response.attempt.page+'"]').attr('class', 'page').addClass(response.attempt.class);
					}
					clearInterval(interval);
					timespend();
				} else {
					return false;
				}
				return true;
			});
			return true;
		}
		return false;		
	}
	function nextQuestion() {
		$.ajax({
			url:"/attempt/next-question/"+attempt_id,
			method: 'POST',
			data: {'page': next_page, 'section': section_id},
			dataType:'json',
		}).success(function(response){
			if (response.status) {
				if(typeof response.question.next != 'undefined') {
					window.location = '/assessment/summary/'+attempt_id;
					return false;
				}
				if(typeof response.question.switch_section != 'undefined') {
					section_id = response.question.section;
					switch_section = false;
					$(section_list+"[data-section='"+section_id+"']").trigger('click').parent().addClass('active');
					return false;
				}
				$(options_class).delegate("div.radio", "click", function(){
				    $('span.checked').removeClass('checked').children().removeAttr('checked');
				    $(this).children().addClass('checked').children().attr('checked', true);
				});
				$('.ques-no', 'form#quizForm').html($(question_list).find('.page[data-preq="'+response.question.page+'"]:visible').html()+'.');
				$('.q-text.table-responsive').html(response.question.question_text);
				var alpha = 65;
				$(options_class).html('');
				$.each(response.question.answers, function(index, value){
					$(options_class).append(
						options.replace(/{a-z}/g, String.fromCharCode(alpha++).toUpperCase()+')')
								.replace(/{value}/g, index)
								.replace(/{text}/g, value)
					);
				});
				reviewed = response.question.review;
				if (response.question.user_response == 0 || response.question.user_response != '') {
					$('input:radio[value="'+response.question.user_response+'"]').attr('checked', true).parent().addClass('checked');
				}	
				if (section_id == 0) {
					$(question_list).find('.page[data-preq="'+next_page+'"]').removeClass().addClass('page '+response.question.class);
				} else {
					$(question_list).find('.page[data-preq="'+next_page+'"]:visible').removeClass().addClass('page '+response.question.class);
				}
				$(mark).html(response.question.mark);
				current_page = parseInt(response.question.page);
				jQuery.getScript('/admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image');
				next_page = current_page+1;
				moveToView();
				clearInterval(interval);
				timespend();
			} else {
				return false;
			}
			$(loader).hide();
		});
		return true;
	}
	function switchSection() {
		if (timed_sections == false) {
			$(section_list).on('click', function(e){
				if(section_id == $(this).data('section') && switch_section == true) {
					return false;
				}
				triggered = switch_section = true;
				if(submitAnswer()) {
					$(section_list).parent().removeClass('active');
					$(this).parent().addClass('active');
					$(question_list).find("[data-section-id='"+section_id+"']").removeClass('hide').show();
					section_id = $(this).data('section');
					next_page = $(this).data('preq');
					$(question).hide();
	                $(question_list).find("[data-section-id='"+section_id+"']").removeClass('hide').show();
					nextQuestion();
					e.preventDefault();
					return false;
				}
			});
		}		
	}	
	function submitQuiz(){
		$(submit_quiz).on('click', function(e){
			triggered = true;
			switch_section = false;
			if (submitAnswer()) {
				if (timed_sections) {
					if (confirm(submit_messge)) {
						toggleSectionLoader();
						closeAttempt();	
					} else {
						$(loader).hide();
					}					
				} else {
					window.location = '/assessment/summary/'+attempt_id;
				}				
			}
			e.preventDefault();
			return false;
		})
	}
	function closeAttempt(){
		$.ajax({
            url : "/assessment/close-attempt/"+attempt_id,
            type : "post",
            success: function(response, status, jqXHR){
	            if (response.status !== undefined && response.status) {               
	                redirectToAnalytics();
	            }
	        }
        });
	}
	function selectedAnswer() {
		$answer = $('input:radio:checked','.ques-list').val();
		if (typeof $answer == 'undefined') {
			$answer = '';
		}
		return $answer;
	}
	function timespend() {
		timetaken = 0;
		interval = setInterval(function(){
            timetaken++;
        }, 1000);
	}
	function moveToView() {
		//Moving element to viewport
		if (section_id == 0) {
			var container = $(question_list),
		    scrollTo = $(question_list).find('.page[data-preq="'+current_page+'"]:visible');
		} else {
			var container = $(question_list),
		    	scrollTo = $(question_list).find('.page[data-preq="'+current_page+'"][data-section-id="'+section_id+'"]');
		}
		$(scrollTo).ensureVisible();
	}
	$.fn.ensureVisible = function () { $(this).each(function () { $(this)[0].scrollIntoView(); }); };
	init();	
	return {
		submitAnswer: submitAnswer,
		closeAttempt: closeAttempt,
		moveToView:moveToView,
	};
})();