@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>{{ trans('admin/announcement.success') }}</strong> -->
			{{ Session::get('success') }}
		</div>
		<?php Session::forget('success'); ?>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>{{ trans('admin/announcement.error') }}</strong> -->
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
		<div class="row custom-box">
		    <div class="col-md-4">
		        <div class="box box-blue">
		            <div class="box-title">
		                <h3 style="color:black">{{ trans('admin/announcement.announcement_actions') }}</h3>
		            </div>
		            <div class="box-content">
        					<a  class="btn btn-lightblue" href="{{URL::to('cp/announce/add')}}" >{{ trans('admin/announcement.add_another_Announce') }} </a>
        					<a class="btn btn-lightblue" href="{{URL::to('cp/announce/')}}" >{{ trans('admin/announcement.list_all_announce') }}</a>
        					<a class="btn btn-lightblue" href="{{URL::to('cp/announce/edit/'.$key)}}" >{{ trans('admin/announcement.edit') }} " {{$title}} "</a>
		            </div>
		   		</div>
		    </div>
		</div>
</script>
</script>
@stop
