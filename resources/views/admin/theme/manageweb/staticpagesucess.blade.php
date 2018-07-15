@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
		<?php Session::forget('success'); ?>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Error!</strong> -->
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
		<div class="row custom-box">
		    <div class="col-md-4">
		        <div class="box box-lightgreen">
		            <div class="box-title">
		                <h3> {{trans('admin/manageweb.static_page_actions')}}</h3>
		            </div>
		            <div class="box-content">
            				<a class="btn btn-blue" href="{{URL::to('cp/manageweb/static-pages')}}" >{{trans('admin/manageweb.list_all_static_pages')}} </a>
            				<a class="btn btn-blue" href="{{URL::to('cp/manageweb/static-pages?opration=add')}}" >{{trans('admin/manageweb.add_another_static_page')}}</a>
            				<a class="btn btn-blue" href="{{URL::to('cp/manageweb/edit-static-page/'.$key)}}" >{{trans('admin/manageweb.edit')}} " {{$title}} "</a>
		            </div>
		   		</div>
		    </div>
		</div>
</script>
</script>
@stop
