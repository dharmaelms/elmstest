@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success" id="alert-success">
			<button class="close" data-dismiss="alert">×</button>
		<!-- 	<strong>Success!</strong> -->
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
	<div class="row custom-box">
		<div class="col-md-12">
	        <div class="box">
	        </div>
	    </div>
    </div>
	<div class="row custom-box">
	    <div class="col-md-4">
	        <div class="box box-lightgreen">
	            <div class="box-title">
	                <h3>{{trans('admin/program.element_management_actions')}}</h3>
	            </div>
	            <div class="box-content">
				            <?php if($type=='content_feed') { ?>
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/elements/'.$packet['packet_slug'])}}" >{{trans('admin/program.manage_elements')}}
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/packets/'.$packet['feed_slug'])}}" >{{trans('admin/program.view_all_packets')}} </a>
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/add-packets/'.$packet['feed_slug'])}}" >{{trans('admin/program.add_more_packet_to_channel')}}</a>
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/add-feeds/'.$packet['packet_slug'])}}" >{{trans('admin/program.add_more_channels')}}</a>
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement')}}" >{{trans('admin/program.view_all_channels')}}</a>
							<?php } ?>

							<?php if($type=='course') { ?>
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/elements/'.$packet['packet_slug'].'/'.$type)}}" >{{trans('admin/program.manage_elements')}}
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/packets/'.$packet['feed_slug'].'/'.$type)}}" >{{trans('admin/program.view_all_packets')}}</a>
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/add-packets/'.$packet['feed_slug'].'/'.$type)}}" >{{trans('admin/program.add_more_packet_to_course')}}</a>
								<a class="btn btn-blue" href="{{URL::to('/cp/contentfeedmanagement/list-courses')}}" >{{trans('admin/program.view_all_courses')}}</a>
							<?php } ?>
	            </div>
	   		</div>
	    </div>
	</div>
<script type="text/javascript">
	$(document).ready(function(){
        $('#alert-success').delay(5000).fadeOut();
    })
</script>
@stop
