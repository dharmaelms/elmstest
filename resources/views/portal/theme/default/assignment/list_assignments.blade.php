@section('content')
	<div class="tabbable tabbable-tabdrop color-tabs">
		<ul class="nav nav-tabs center margin-btm-0">
		    <li @if($filter == 'unattempted') class="active" @endif><a href="{{ URL::to('/assignment?filter=unattempted') }}"><i class="fa fa-times" aria-hidden="true"></i> {{Lang::get('assignment.unattempted')}} ({{$count['unattempted']}})</a></li>

		    <li @if($filter == 'attempted') class="active" @endif><a href="{{ URL::to('/assignment?filter=attempted') }}"><i class="fa fa-check-square-o" aria-hidden="true"></i> {{Lang::get('assignment.attempted')}} ({{$count['attempted']}})</a></li>
		    <li @if($filter == 'reports') class="active" @endif><a href="{{ URL::to('/assignment?filter=reports') }}"><i class="fa fa-line-chart" aria-hidden="true"></i> {{Lang::get('assignment.reports')}} ({{$count['reports']}})</a></li>
		</ul>
		<div class="tab-content">
			<div class="tab-pane active">
				<div class="row">
					<div class="xs-margin"></div>
					<div class="col-md-12">
						<div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12 assessment-div">
							<div class="xs-margin"></div>
							@if($filter != "reports")
								@include('portal.theme.default.assignment.assignment_ajax_load', ['assignments' => $assignments, 'completed_list' => $completed_list, 'drafted_list' => $drafted_list, 'filter' => $filter])
							@else
								@include('portal.theme.default.assignment.assignment_report', ['filter' => $filter, 'drafted_list' => $drafted_list, 'assignments' => $assignments, 'attempted_data' => $attempted_data])
							@endif
						</div>
						<div class='col-md-12 center l-gray'>
							<p><input type="button" id="load_more" value="Load more"> </p>
						</div>
						<div id='no-records' style='display:none' class='col-md-12 center l-gray'>
							<p><strong>{{Lang::get('pagination.no_more_records')}}</strong></p>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<div id="mrova-feedback">
		@include($theme_path.'.common.leftsidebar')
		<div id="mrova-img-control"></div>
	</div>
	<script type="text/javascript">
		var filter_type = "{{$filter}}";
		console.log(filter_type);
        var assignment_display_count = {{ $count[$filter] }};
        var start = 9;
        var stop = flag = true;

	$(document).ready(function(){
		$('#load_more').hide();
        if(assignment_display_count > 8 && stop && filter_type != "reports") {
            $('#load_more').show();
        }
	});
	$(function() {
		$('.cf-list').on('change', function(){
			$('#filter').submit();
		});
		$(window).scroll(function() {
			if(assignment_display_count > 1 && stop && filter_type != "reports") {
	        	if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
                    ajax_call(start);
                    start += 9;
	        	}
	        }
	    });

		$('#load_more').click(function ($this) {
            ajax_call(start);
            start += 9;
        });
	});
	</script>
	<script type="text/javascript">
	(function ($) {
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
	function ajax_call(start) {
        if(flag) {
            flag = false;
            $.ajax({
                type: 'GET',
                url: "{{ url('assignment/list?filter='.$filter) }}&start="+start
            }).done(function(e) {
                if(e.status == true) {
                    $('.assessment-div').append(e.data);
                    flag = true;
                    $('#load_more').show();
                }
                else {
                    $('#no-records').show();
                    stop = false;
                    $('#load_more').hide();
                }

            }).fail(function(e) {
                alert('Failed to get assessment data');
            });
        }
    }
	</script>
	<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
	<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
	<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
@stop