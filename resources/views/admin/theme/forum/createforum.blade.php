
@section('content')
<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
<form action="{{URL::to('cp/forum/upload-forum')}}" class="form-horizontal form-bordered form-row-stripped" method="post" id="uploadform" style="margin-top:10px;" enctype='multipart/form-data' > 
			<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label"> Forum Title</label>
							<div class="col-sm-9 col-lg-10 controls">
								<input type="text" name="title" class="form-control" value="" />
								<span class="help-inline">Some hint here for help</span>
								{!! $errors->first('title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>

			</div>
			<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">Forum Type</label>
							<div class="col-sm-9 col-lg-10 controls">
								<label class="radio-inline">
									<input type="radio" name="forum_type" value="Open"  checked="true" /> Open
								</label>
								<label class="radio-inline">
									<input type="radio" name="forum_type" value="Closed"  /> Closed
								</label>
								{!! $errors->first('media_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								<span class="help-inline" ><i title='Select here make Open or Closed This forum' class="fa fa-tint"></i>
</span>
							</div>

						</div>
			<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">Status</label>
							<div class="col-sm-9 col-lg-10 controls">
								<label class="radio-inline">
									<input type="radio" name="status" value="ACTIVE" checked="true" /> Active
								</label>
								<label class="radio-inline">
									<input type="radio" name="status" value="INACTIVE"  /> In-Active
								</label>
								<label class="radio-inline">
									<input type="radio" name="status" value="CLOSED"  /> CLOSED
								</label>
								<span class="help-inline" >Select here make Active or InActive of the forumm</span>
								{!! $errors->first('media_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
			</div>
			<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">Visibility</label>
							<div class="col-sm-9 col-lg-10 controls">
								<label class="radio-inline">
									<input type="radio" name="visiblity" value="Public" checked="true" /> Public
								</label>
								<label class="radio-inline">
									<input type="radio" name="visiblity" value="Private"  /> Private
								</label>
								
								<span class="help-inline"><i ></i></span>
								{!! $errors->first('media_type', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
			</div>
		
			<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label"> Description</label>
							<div class="col-sm-9 col-lg-10 controls">
								<textarea class="form-control" rows="3" name="forum_description"></textarea>
								{!! $errors->first('forum_description', '<span 	class="help-inline" style="color:#f00">:message</span>') !!}
								<span class="help-inline">Write Something about your forumm</span>
							</div>
			</div>
			<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">Admin Comments</label>
							<div class="col-sm-9 col-lg-10 controls">
								<textarea class="form-control" rows="6	" name="admin_description"></textarea>
								{!! $errors->first('admin_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								<span class="help-inline">Commetnts of Your forum </span>
							</div>
						</div> 
		
			
			<div class="form-group">
							<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
								<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> Create forum</button>
								<span class="help-inline">	Create New Forum press here</span>
							</div>
			</div>
	<div> 
			

	
			</div>
		</form>
	</script>
	</script>

@stop