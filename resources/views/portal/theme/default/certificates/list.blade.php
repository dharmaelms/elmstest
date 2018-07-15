@section('content')
<style>
	td { border-bottom: 1px solid #dddddd;border-top: 0 !important; }
	select {
	 	background-color: #ffffff;
    border-color: -moz-use-text-color -moz-use-text-color #aaaaaa;
    border-style: none none solid;
    border-width: 0 0 1px;
    color: #737373;
    float: right;
    font-size: 15px;
    padding: 1px 10px;
	}
</style>
<div class="page-bar">
	<ul class="page-breadcrumb">
		<li><a href="{{ URL::to('dashboard') }}">{{Lang::get('dashboard.dashboard')}}<i class="fa fa-angle-right"></i></a></li>
		<li><span>{{ Lang::get('certificate.certificates') }}</span></li>
	</ul>
</div>

<div class="container">
	<div class="row">
		<div class="col-md-12">
			<div class="sm-margin"></div><!--space-->
			<p class="row">
				<h3 class="col-md-3 margin-top-0">{{ Lang::get('certificate.certificates') }} 
				</h3>
				<form id="search-form" class="col-md-3">
					<input type="search" id="search-input" class="form-control pull-right" autofocus="" placeholder="{{ Lang::get('certificate.search_placeholder') }}">
				</form>
				<span class="col-md-2">
					<select name="filter-dropdown" id="filter" class="form-control pull-right">
						<option value="">{{ Lang::get('certificate.sort_by') }}</option>
						<option value="name-asc">{{ Lang::get('certificate.a_z') }}</option>
						<option value="name-desc">{{ Lang::get('certificate.z_a') }}</option>
						<option value="date-asc">{{ Lang::get('certificate.by_date') }}</option>
					</select>
				</span>				
			</p>
			<p class="col-md-3 cron-message pull-right">
				<span class="col-md-1">*</span>
				<span class="col-md-11 certificate-message">{{ trans('certificate.cron_timing_message') }}</span>				
			</p>						
			<span class="page-title-small row"></span>
		</div>
	</div>

	<div class="row">
		<div class="col-md-8 col-sm-12 col-xs-12 certificates">
			<table width="100%" class="table" id="certificates-list">
			 <!-- row comes here -->
			</table>
			<a href="#" class="center" id="load-more" style="display:none;">{{ Lang::get('certificate.more_records') }}</a>
			<span id="no-more" style="display:none;">{{ Lang::get('certificate.no_more_records') }}</span>
			<span id="no-record" style="display:none;">{{ Lang::get('certificate.not_found') }}</span>
		</div>
	</div>
<script type="text/javascript">
	$(document).ready(function(){

		//Javascript IIFE(Immeidate invocation of function expression) implementation
		var certificates = (function(){

			var $downloadUrl = "{{URL::to('certificates/pdf/1')}}",
				$viewUrl = "{{URL::to('certificates/pdf/0')}}",
				$page = 1,
				$limit = 10,
				$search = $('#search-form'),
				$searchInput = $('#search-form #search-input'),
				$loadMore = $('#load-more'),
				$noMore = $('#no-more'),
				$noRecord = $('#no-record'),
				$orderBy = $('#filter'),
				$position = $('#certificates-list'),
				$url = "{{URL::to('certificates/details')}}",
				$content = '<tr>'+
					'<td width="50">'+
						'<img alt="Certificate" src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/img/icons/certificate-icon1.png') }}" width="40px">'+
					'</td>'+
					'<td>'+
						'<h4 class="margin-0 font-weight-500">'+
							'<a href="#">{name}</a>'+
						'</h4>'+
						'<p class="font-11">{date}</p>'+
					'</td>'+
					'<td width="30">'+
						'<a target="_blank" href="{view_url}" title="View" class="btn btn-primary btn-sm">'+
							'<i class="fa fa-eye"></i>'+
						'</a>'+
					'</td>'+
					'<td width="30">'+
						'<a href="{download_url}" title="Print" class="btn btn-primary btn-sm">'+
							'<i class="fa fa-download"></i>'+
						'</a>'+
					'</td>'+
				'</tr>';

			function init() {

				search();
				//filter dropdown
				$orderBy.on('change', function(e){ 
					if($orderBy.val() == '') {
						return false;
					}
					search();
				});
				//search button and form
				$search.on('submit', function(e){ 
					search();
					e.preventDefault(); //prevent event bubbling
				});

				//load more button event
				$loadMore.on('click', function(e){
					loadData();
					e.preventDefault(); //prevent event bubbling
				});
			}

			function search() {
				$page = 1;
				$position.html(''); //clearing existing rows
				loadData();
			}

			function loadData(){

				var certificates = $.ajax({
										url: $url+'/'+$page+'/'+$limit+'?condition='+$orderBy.val()+'&search='+$searchInput.val(),
										method: 'GET',
										dataType: 'json',
									});

				certificates.done(function(response) {

					if(response.status) {
						$.each(response.data, function(index, data) {
							$($position).append(
								$content.replace(/{name}/g, data.name)
									.replace(/{date}/g, data.date)
									.replace(/{view_url}/g, $viewUrl+'/'+data.id)
									.replace(/{download_url}/g, $downloadUrl+'/'+data.id)
							);
						});
					} else {
						if($page == 1) { //show no records found 
							$noRecord.show();
						}						
					}

					if(response.data.length < $limit) {
						$loadMore.hide();
						if($page > 1) {
							$noMore.show();
						}						
					} else {
						$page = $page + 1;
						$loadMore.show();
						$noMore.hide();
					}

				});

				certificates.fail(function(response){
					console.log(response);
				});

				return false;
			}		
			init(); //initializing ajax requests and events
		})();
	});
</script>
@stop
