
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
		<div class="row">
		    <div class="col-md-6">
		        <div class="box box-red">
		            <div class="box-title">
		                <h3 style="color:black"><i class="fa fa-gear"></i> Forum Management Actions</h3>
		                <div class="box-tool">
		                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
		                </div>
		            </div>
		            <div class="box-content">
		            	<table class="table table-bordered">
		            		<tbody>
		            			<tr>
		            				<td>1. </td>
		            				<td><a href="" >Assign this Forum to a Program</a></td>
		            			</tr>
		            			<tr>
		            				<td>2. </td>
		            				<td><a href="" >Assign this Forum to a {{Lang::get('admin/program.program')}}</a></td>
		            			</tr>
		            			<tr>
		            				<td>3. </td>
		            				<td><a href="" >Assign this Forum to Users</a></td>
		            			</tr>
		            		</tbody>
		            	</table>
		            </div>
		   		</div>
		    </div>
		    <div class="col-md-6">
		        <div class="box box-red">
		            <div class="box-title">
		                <h3 style="color:black"><i class="fa fa-gears"></i> Additional Actions</h3>
		                <div class="box-tool">
		                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
		                </div>
		            </div>
		            <div class="box-content">
		            	<table class="table table-bordered">
		            		<tbody>
		            			<tr>
		            				<td>1. </td>
		            				<td><a href="" >Assign this Forum to User Groups</a></td>
		            			</tr>
		            			<tr>
		            				<td>2. </td>
		            				<td><a href="" >Assign this Forum to a Category</a></td>
		            			</tr>
		            			<tr>
		            				<td>3. </td>
		            				<td><a href="" >Add another Forum</a></td>
		            			</tr>
		            		</tbody>
		            	</table>
		            </div>
		   		</div>
		    </div>
		</div>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <h3 style="color:black"><i class="fa fa-file"></i> List Forum</h3>
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div>
	            </div>
	            <div class="box-content">
	            	<div class="btn-toolbar pull-right clearfix">
                        <div class="btn-group">
                            <a class="btn btn-circle show-tooltip" title="Add New Forum" href="{{url::to('/cp/forum')}}"><i class="fa fa-plus"></i></a>
                           <!--  <a class="btn btn-circle show-tooltip disabled" title="Edit selected" href="#"><i class="fa fa-edit"></i></a> -->
                            <a class="btn btn-circle show-tooltip " title="Delete selected" href="#"><i class="fa fa-trash-o"></i></a>
                        </div>
                        <div class="btn-group">
                            <a class="btn btn-circle show-tooltip " title="Refresh" href="#"><i class="fa fa-repeat"></i></a>
                        </div>
                    </div>
                    <br/><br/>
	             <div class="box-content">
                    <div class="clearfix"></div>
	            	@if(count($forums)>0 && !empty($forums) )
	                    <table class="table table-advance" id="datatable">
					        <thead>
					            <tr>
					                <th style="width:18px"><input type="checkbox" /></th>
					                <th>Forum Title</th>
					                <th>Status</th>
					                <th>Created By</th>
					                <th>Created At</th>
					                <th>Actions</th>
					            </tr>
					        </thead>
					        <tbody>					        	
					        	@foreach($forums as $forum) 
					        		<tr>
					        			<td style="width:18px"><input type="checkbox" value="{{$forum->forum_id}}" /></td>
					        			<td>{{$forum->forum_title}}</td>
					        			<td>{{$forum->forum_status}}</td>
					        			<td>{{$forum->created_by}}</td>
					        			<td>{{$forum->created_at}}</td>
					           			<td>
					        				<a class="btn btn-circle show-tooltip " title="View Forum " href="{{URL::to('cp/forum?opration=view')}}" ><i class="fa fa-list-alt"></i></a>
					        				<a class="btn btn-circle show-tooltip " title="Edit Forum" href="{{URL::to('cp/forum/edit/'.$forum->forum_slug)}}" ><i class="fa fa-edit"></i></a>
					        				<!-- <a class="btn btn-circle show-tooltip alert-danger" title="Delete This Forum" href="#"><i class="fa fa-trash-o"></i></a> -->
					        				<a class="btn btn-circle show-tooltip " sytle="background-color:red" title="Delete This Forum" href="{{URL::to('cp/forum?delete='.$forum->forum_slug)}}"><i class="fa fa-trash-o"></i></a>
					        			</td>
					        		</tr>
					        	@endforeach
					        
					        </tbody>
					     </table>					  
					    @else
					 		<div class="text-center">There are no {{Lang::get('admin/program.programs')}}</div> 
					  @endif
                </div>                  
	        </div>
	    </div>	    
	</div>
</div>
</script>
</script>

@stop
