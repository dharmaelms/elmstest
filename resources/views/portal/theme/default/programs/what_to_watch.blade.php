@section('content')
<div class="tabbable tabbable-tabdrop color-tabs">
	<ul class="nav nav-tabs center">
        @if($general->setting['general_category_feeds']=="on")
		<li ><a href="{{URL::to('program/category-channel')}}"><i class="fa fa-rss-square"></i>
		&nbsp; {{trans('program.my_course')}}</a></li>
		@endif
		@if($general->setting['watch_now'] == 'on')
		<li class="active"><a href="{{URL::to('program/what-to-watch')}}"><i class="fa fa-video-camera"></i>
		&nbsp; {{trans('program.tab_watch_now')}}</a></li>
		@endif
		@if($general->setting['posts'] == 'on')
		<li><a href="{{URL::to('program/my-feeds')}}"><i class="fa fa-rss-square"></i>
		&nbsp; {{trans('program.tab_posts')}}</a></li>
		@endif
		@if($general->setting['favorites'] == 'on')
		<li><a href="{{URL::to('program/favourites')}}"><i class="fa fa-heart"></i>
		&nbsp; {{trans('program.tab_favorites')}}</a></li>
		@endif

		@if((!config("app.ecommerce")) && ($general->setting['more_feeds']=="on"))
		<li>
			<a href="{{URL::to('program/more-feeds')}}">
				<i class="fa fa-rss"></i>&nbsp; {{trans('program.tab_other_channels')}}
			</a>
		</li>
		@endif

	</ul>
	<div class="tab-content">
		<div class="tab-pane active">
			<div class="row">
				<div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12" id="end_watchnow">
					<div class="xs-margin"></div><!--space-->
					<div class="myactivity-section">
						<div id="parentHorizontalTab">
							<ul class="resp-tabs-list hor_1">
								<li class="resp-tab-item hor_1 resp-tab-active">{{trans('program.recent_posts')}}</li>
								<li><a href="{{URL::to('program/incomplete-posts')}}">
								{{trans('program.incomplete_post')}}</a></li>
							</ul>
							<div class="resp-tabs-container hor_1">
								<div class="tab-content resp-tab-content hor_1 resp-tab-content-active">
									@if(count($packets) > 0)
									@include('portal.theme.default.programs.whattowatch_ajax_load', ['packets' => $packets, 'favorites' => $favorites, 'program_data' => $program_data, 'package_data' => $package_data])
									@else
									<h4 align="center">{{  trans('program.no_post_to_watch') }}</h4>
									@endif
								</div><!-- Recent Posts -->

							</div>
						</div>
					</div>
				</div><!--facets data div-->
			</div>
		</div>
		<!--END Watch Now section-->
	</div>
</div>
<div id="mrova-feedback">
	@include($theme_path.'.common.leftsidebar')
	<div id="mrova-img-control"></div>
</div>

<script type="text/javascript">
	$(document).ready(function () {
		var url='<?php echo URL::to('/'); ?>';
		var pageno=1;
		var count='<?php echo count($packets); ?>';
		var stop = flag = true;
		$(window).scroll(function() {
			if(count > 8 && stop) {
				if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
					if(flag) {
						flag = false;
						$.ajax({
							type: 'GET',
							url: "{{ url('program/watch-next-records?pageno=') }}"+pageno
						}).done(function(e) {
							if(e.status == true) {
								$('#end_watchnow').append("<div class='myactivity-section'><div class='resp-tabs-container hor_1'><div class='tab-content resp-tab-content hor_1 resp-tab-content-active'>"+e.data+"</div></div></div>");
								count=e.count;
								stop=true;
								flag = true;
								if(count < 9)
								{
									$('#end_watchnow').append("<div	class='col-md-12 center l-gray'><p><strong>{{trans('pagination.no_more_records')}}</strong></p></div>");
								}
							}
							else {
								$('#end_watchnow').append("<div class='myactivity-section'><div class='resp-tabs-container hor_1'><div class='tab-content resp-tab-content hor_1 resp-tab-content-active'>"+e.data+"</div></div></div>");
								stop = false;
							}
							pageno += 1;
						}).fail(function(e) {
							alert('Failed to get the data');
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
@stop