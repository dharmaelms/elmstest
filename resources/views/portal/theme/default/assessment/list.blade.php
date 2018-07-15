@section('content')
	<div class="tabbable tabbable-tabdrop color-tabs">
		<ul class="nav nav-tabs center margin-btm-0">
			<li @if($filter == 'unattempted') class="active" @endif><a href="{{ URL::to('/assessment?filter=unattempted') }}"><i class="fa fa-times" aria-hidden="true"></i> Unattempted ({{$count['unattempted']}})</a></li>
			<li @if($filter == 'attempted') class="active" @endif><a href="{{ URL::to('/assessment?filter=attempted') }}"><i class="fa fa-check-square-o" aria-hidden="true"></i> Attempted ({{$count['attempted']}})</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active">
				<div class="row">
					<div class="facets-sidebar col-lg-3 col-md-3 col-sm-3 col-xs-12">
						<div class="xs-margin"></div><!--space-->
					
					</div><!--facets sidebar div-->

					<div class="col-md-12">
					<div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12 assessment-div">
						<div class="xs-margin"></div><!--space-->
						@include('portal.theme.default.assessment.quiz_ajax_load', ['quizzes' => $quizzes, 'attempt_detail' => $attempt_detail, 'filter' => $filter, 'order_qids' => $order_qids, 'quizAnalytics' => $quizAnalytics, 'quizMatrics' => $quizMatrics])
					</div><!--facets data div-->
					<div id='no-records' style='display:none' class='col-md-12 center l-gray'>
						<p><strong>{{Lang::get('pagination.no_more_records')}}</strong></p>
					</div>
					</div>
				</div>
			</div><!--announce tab-->
		</div>
		<!--tab-content-->
	</div><!-- tabdrop tabs-->

<div id="mrova-feedback">
@include($theme_path.'.common.leftsidebar')
<div id="mrova-img-control"></div>
</div>

	<script type="text/javascript">
	$(function() {
		$('.cf-list').on('change', function(){
			$('#filter').submit();
		});

		var assessment_display_count = {{ $count[$filter] }};
		var start = 9;
		var stop = flag = true;
		$(window).scroll(function() {
			if(assessment_display_count > 8 && stop) {
	        	if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
	        		if(flag) {
	        			flag = false;
		        		$.ajax({
		        			type: 'GET',
		        			url: "{{ url('assessment?filter='.$filter) }}&start="+start
		        		}).done(function(e) {
		        			if(e.status == true) {
		        				$('.assessment-div').append(e.data);
		        				flag = true;
		        			}
		        			else {
		        				$('#no-records').show();
		        				stop = false;
		        			}
		        			start += 9;
		        		}).fail(function(e) {
		        			alert('Failed to get assessment data');
		        		});
		        	}
	        	}
	        }
	    });
	});
	</script>
	<script type="text/javascript">
	(function ($) {
	$.fn.vAlign = function() {
	  return this.each(function(i){
	  var h = $(this).height();
	  var oh = $(this).outerHeight();
	  var mt = (h + (oh - h)) / 1.3;  
	  $(this).css("margin-top", "-" + mt + "px"); 
	  $(this).css("top", "13%");
	  }); 
	};
	$.fn.toggleClick = function(){
	    var functions = arguments ;
	    return this.click(function(){
	            var iteration = $(this).data('iteration') || 0;
	            functions[iteration].apply(this, arguments);
	            iteration = (iteration + 1) % functions.length ;
	            $(this).data('iteration', iteration);
	    });
	};
	})(jQuery);
	$(window).load(function() {
	  //cache
	  $img_control = $("#mrova-img-control");
	  $mrova_feedback = $('#mrova-feedback');
	  $mrova_contactform = $('#mrova-contactform');

	  //setback to block state and vertical align to center
	  $mrova_feedback.vAlign()
	  .css({'display':'block','height':$mrova_feedback.outerHeight()});
	  //Aligning feedback button to center with the parent div 

	  $img_control.vAlign()
	  //animate the form
	  .toggleClick(function(){
	    $mrova_feedback.animate({'right':'-2px'},1000);
	  }, function(){
	    $mrova_feedback.animate({'right':'-'+$mrova_feedback.outerWidth()},1000);
	  });

	  //Form handling
	  $('#mrova-sendbutton').click( function() {
	        var url = 'send.php';
	        var error = 0;
	        $('.required', $mrova_contactform).each(function(i) {
	          if($(this).val() === '') {
	            error++;
	          }
	        });
	        // each
	        if(error > 0) {
	          alert('Please fill in all the mandatory fields. Mandatory fields are marked with an asterisk *.');
	        } else {
	          $str = $mrova_contactform.serialize();

	          //submit the form
	          $.ajax({
	            type : "GET",
	            url : url,
	            data : $str,
	            success : function(data) {

	              if(data == 'success') {
	                // show thank you
	                $('#mrova-contact-thankyou').show();
	                $mrova_contactform.hide();
	              } else {
	                alert('Unable to send your message. Please try again.');
	              }
	            }
	          });
	          //$.ajax

	        }
	        return false;
	      });

	});
	</script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
@stop