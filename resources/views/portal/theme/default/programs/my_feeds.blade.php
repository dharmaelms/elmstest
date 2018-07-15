@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}" />
<script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>
<style type="text/css">
    .completed-overlay {
        top:14px !important;
    }
</style>
<div class="tabbable tabbable-tabdrop color-tabs" id="target" style="overflow-x: hidden;overflow-y: scroll; width: 100%; height: -webkit-fill-available;margin-left: 20px;">
    <h2 class="bold gray uppercase"><i class="fa fa-bookmark green font-20"></i> Courses</h2>
    <!--ul class="nav nav-tabs center">
        @if($general->setting['general_category_feeds'] == "on")
        <li ><a href="{{URL::to('program/category-channel')}}"><i class="fa fa-rss-square"></i>&nbsp; {{ trans('program.my_course') }}</a></li>
        @endif
        @if($general->setting['watch_now'] == 'on')
            <li><a href="{{URL::to('program/what-to-watch')}}"><i class="fa fa-video-camera"></i>&nbsp; {{ trans('program.tab_watch_now') }}</a></li>
        @endif
        @if($general->setting['posts'] == 'on')
            <li class="active"><a href="{{URL::to('program/my-feeds')}}"><i class="fa fa-rss-square"></i>&nbsp; {{ trans('program.tab_posts') }}</a></li>
        @endif
        @if($general->setting['favorites'] == 'on')
            <li><a href="{{URL::to('program/favourites')}}"><i class="fa fa-heart"></i>&nbsp; {{ trans('program.tab_favorites') }}</a></li>
        @endif

        @if(!config("app.ecommerce"))
            @if($general->setting['more_feeds'] == "on") 
                <li>
                    <a href="{{URL::to('program/more-feeds')}}">
                        <i class="fa fa-rss"></i>&nbsp; {{ trans('program.tab_other_channels') }}
                    </a>
                </li>
            @endif 
        @endif

    </ul-->
    <div class="tab-content" >
        <div class="tab-pane active">
            <div class="row">
                <div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12" id="end_feeds">
                    <div class="xs-margin"></div><!--space-->
                    @include('portal.theme.default.programs.myfeeds_ajax_load', ['programs' => $programs, 'favorites' => $favorites, 'channelAnalytics' => $channelAnalytics, 'general' => $general])
                </div><!--facets data div-->
            </div>
        </div>
        <!--END My Feeds section-->
    </div>
</div>
<div id="mrova-feedback">
@include($theme_path.'.common.leftsidebar', [ 'other_ids' => $other_ids ])
<div id="mrova-img-control"></div>
</div>
<script type="text/javascript">
    function random(){
        $('.owl-carousel').each(function(pos,value){
            $(value).owlCarousel({
                items:4,
                navigation: true,
                navigationText: [
                "<i class='fa fa-caret-left'></i>",
                "<i class='fa fa-caret-right'></i>"
                ],
                beforeInit : function(elem){

                }
            });
        });
    }
    $(document).ready(function() {
    //Sort random function
        random();
    });

    $( "#end_feeds" ).click(function() {
  $( "#target" ).scroll();
});

    

</script>


<script type="text/javascript">
$(document).ready(function () {
    var url='{{ URL::to('/') }}';
    var pageno=1;
    var count='{{ count($programs) }}';
    var stop = flag = true;
    $(window).scroll(function() {
        if(count > 8 && stop) {
            if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
                if(flag) {
                    flag = false;
                    $.ajax({
                        type: 'GET',
                        url: "{{ url('program/feeds-next-records?pageno=') }}"+pageno
                    }).done(function(e) {
                        if(e.status == true) {
                            $('#end_feeds').append(e.data);
                            count=e.count;
                            stop=true;
                            flag = true;
                            if(count < 9)
                            {
                                $('#end_feeds').append("<div class='col-md-12 center l-gray'><p><strong>{{ trans('pagination.no_more_records') }}</strong></p></div>");
                            }
                        }
                        else {
                            $('#end_feeds').append(e.data);
                            stop = false;
                        }
                        pageno += 1;
                        random();
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
$('#end_feeds').on('click', '.fav-packet', function(e) {
    e.preventDefault();
    var action = $(this).data('action');
    var packet_id = $(this).attr('id');
    if(action == 'favourite') {
        $("#"+packet_id).removeClass("gray").addClass("red");
        $.ajax({
            type: 'GET',
            url: "{{ url('program/packet-favourited/favourite') }}/"+packet_id
        })
        .done(function(response) {
            if(response.status == true) {
                $("#"+response.packet_id).data('action', 'unfavourite');
            } else {
                $("#"+response.packet_id).removeClass("red").addClass("gray");
            }
        })
        .fail(function(response) {
            $("#"+packet_id).removeClass("red").addClass("gray");
            alert( "Error while updating the post. Please try again" );
        });
    }
    if(action == 'unfavourite') {
        $("#"+packet_id).removeClass("red").addClass("gray");
        $.ajax({
            type: 'GET',
            url: "{{ url('program/packet-favourited/unfavourite') }}/"+packet_id
        })
        .done(function(response) {
            if(response.status == true) {
                $('#'+response.packet_id).data('action', 'favourite');
            } else {
                $('#'+response.packet_id).removeClass('gray').addClass('red');
            }
        })
        .fail(function(response) {
            $('#'+response.packet_id).removeClass("gray").addClass("red");
            alert( "Error while updating the post. Please try again" );
        });
    }
});
</script>

<script type="text/javascript">
function SwapDivsWithClick(div1,div2,i)
{
   d1 = document.getElementById(div1);
   d2 = document.getElementById(div2);
   var thisObj = document.getElementById('contentfeed'+i);
   if( d2.style.display == "none")
   {
      d1.style.display = "none";
      d2.style.display = "block";
      thisObj.innerHTML = "View posts";
   }
   else
   {
      d1.style.display = "block";
      d2.style.display = "none";
      thisObj.innerHTML = "More Info";
   }
}
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
			alert("{{ trans('program.plz_fill_mandatory_field') }}");
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
						alert("{{ trans('program.unable_send_msg') }}");
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