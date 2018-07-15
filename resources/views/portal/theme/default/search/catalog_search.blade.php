@section('content')
<style type="text/css">
	.page-content-wrapper .page-content { margin-left: 0 !important; }
	.page-sidebar.navbar-collapse { display: none !important; max-height: none !important; }
	.page-header.navbar .menu-toggler.sidebar-toggler { display: none; }
</style>
<?php $search = Input::old('cat_search'); if(Input::old('p_type')) { $p_type = Input::old('p_type'); } else { $p_type = ''; } ?>

<div class="search-data1">
	<div class="row md-margin">
		<div class="col-md-9 col-sm-8 col-xs-12">
			<h3 class="margin-top-0">{{ Lang::get('search.search_result_for') }} <strong>{{Input::old('cat_search')}}</strong></h3>
			<p class="font-13 gray">Showing: {{$s_count + $p_count}} results</p>
			<hr>
		</div>
		@if($s_count != 0)
			<div class="col-md-3 col-sm-4 col-xs-12">
				<div class="btn-group">
					<a aria-expanded="false" data-toggle="dropdown"><img width="20px" src="{{URL::asset($theme.'/img/icons/filter-icon.png')}}" alt="Filter icon"></a>
					<ul role="menu" class="dropdown-menu pull-right">
						<li class="filter-title border-btm"><h4>&nbsp;&nbsp;{{ Lang::get('search.program_type') }}</h4></li>
						@if($search_count == $type_count || $type_count == 0)
							<li class="active"><a href="#">@if($s_data[0]['program_type'] == 'content_feed') {{ Lang::get('search.channel') }} @elseif($s_data[0]['program_type'] == 'course') {{ Lang::get('search.course') }} @else {{ Lang::get('search.products') }} @endif</a></li>
						@else
							<li <?php if($p_type == ''){ ?> class="active" <?php } ?>><a href="{{ URL::to('catalog-search?cat_search='.$search) }}">{{ Lang::get('search.all') }} </a></li>
							<li <?php if($p_type == 'content_feed'){ ?> class="active" <?php } ?>><a href="{{ URL::to('catalog-search?cat_search='.$search.'&p_type=content_feed') }}">{{ Lang::get('search.channel') }} </a></li>
							<li <?php if($p_type == 'product'){ ?> class="active" <?php } ?>><a href="{{ URL::to('catalog-search?cat_search='.$search.'&p_type=product') }}">{{ Lang::get('search.products') }} </a></li>
							<li <?php if($p_type == 'course'){ ?> class="active" <?php } ?>><a href="{{ URL::to('catalog-search?cat_search='.$search.'&p_type=course') }}">{{ Lang::get('search.course') }} </a></li>
						@endif
					</ul>
			  	</div>
				<!-- filter -->
			</div>
		@endif
	</div>

	<div class="row">
		<div class="col-md-9 col-sm-12 col-xs-12">
			@if(!empty($s_data) || !empty($p_data))
				<ul class="search-ul" id="end_search">
					@include('portal.theme.default.search.catalogsearch_ajax_load', ['s_data' => $s_data, 'p_data' => $p_data])
				</ul>
			@else
				<strong>{{ Lang::get('search.no_results_found') }}</strong>
			@endif
		</div>
	</div>
</div>

<script type="text/javascript"> 
	$(document).ready(function () {
		var pageno=1;
		var count='<?php echo count($s_data); ?>';
		var records_per_page='<?php echo $records_per_page; ?>';
		var p_type='<?php echo $p_type; ?>';
		var cat_search='<?php echo $search; ?>';
		var stop = flag = true;
		$(window).scroll(function() {
			if(count >= records_per_page && stop) {
				if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
					if(flag) {
						flag = false;
						$.ajax({
							type: 'GET',
							url: "{{ url('catalog-search/next-data') }}",
							data :{
				                pageno:pageno,
				                cat_search:cat_search,
				                p_type:p_type,
				            }
						}).done(function(e) {
							if(e.status == true) {
								$('#end_search').append(e.data);
								count=e.count;
								stop=true;
								flag = true;
								if(count < records_per_page)
								{
									$('#end_search').append("<div	class='col-md-12 center l-gray'><p><strong><?php echo Lang::get('pagination.no_more_records'); ?></strong></p></div>");
								}
							}
							else {
								$('#end_search').append(e.data);
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
@stop