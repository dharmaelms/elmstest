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
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Import Users In Bulk</h3>
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div> -->
	            </div>
	            <div class="box-content">
	            	<?php $errorflag = session('errorflag'); ?>
	            	<div class="btn-toolbar clearfix">                       
						<div class="pull-right">
						<a class="btn btn-circle show-tooltip" title="{{ trans('admin/user.view_user_import_history') }}" href="{{ URL::to('cp/usergroupmanagement/userimport-history') }}"><i class="fa fa-eye"></i></a>
						</div>
						<div class="col-md-offset-4 col-md-8" style="margin-bottom:20px">
						@if(Input::old() || isset($errorflag))
							<a class="btn disabled show-tooltip" title="{{ trans('admin/user.upload_template') }}"><i class="fa fa-upload"></i><?php echo trans('admin/user.upload'); ?></a>
						@else
							<a class="btn btn-info show-tooltip upload_button" title="{{ trans('admin/user.upload_template') }}"><i class="fa fa-upload"></i><?php echo trans('admin/user.upload'); ?></a>
						@endif
						
						
						<a class="btn btn-gray show-tooltip" title="<?php echo trans('admin/user.add_user_template'); ?>" href="{{ URL::to('cp/usergroupmanagement/import-add-users-excel') }}"  style="margin-left: 6px;"><i class="fa fa-download">&nbsp;<?php echo trans('admin/user.add_user_template'); ?></i></a>
						<a class="btn btn-gray show-tooltip" title="<?php echo trans('admin/user.update_user_template'); ?>" href="{{ URL::to('cp/usergroupmanagement/import-update-users') }}" style="margin-left: 6px;"><i class="fa fa-download">&nbsp;<?php echo trans('admin/user.update_user_template'); ?></i></a>
						<a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/user.list_user_group'); ?>" data-toggle="modal" href="#list_user" style="margin-left: 6px;"><i class="fa fa-user"></i></a>
						<a class="btn btn-circle show-tooltip" title="{{ trans('admin/user.file_upload_help') }}" data-toggle="modal" href="#help" style="margin-left: 6px;"><i class="fa fa-question"></i></a>
						</div>
	                </div><br>
	                
					<div @if(!Input::old() && !(isset($errorflag))) style="display:none;" @endif id="show_form">
		            	<form action="{{URL::to('cp/usergroupmanagement/import-users')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype='multipart/form-data'>
							<div class="form-group">
								<label class="col-sm-4 col-lg-3 control-label"><?php echo trans('admin/user.select_file'); ?><span class="red">*</span></label>
								<div class="col-sm-6 col-lg-5 controls">
									<div class="fileupload fileupload-new" data-provides="fileupload" style="margin-bottom:0;">
										<div class="input-group">
											<div class="form-control uneditable-input">
												<i class="fa fa-file fileupload-exists"></i> 
												<span class="fileupload-preview"></span>
											</div>
											<div class="input-group-btn">
												<a class="btn bun-default btn-file">
													<span class="fileupload-new"><?php echo trans('admin/user.browse'); ?></span>
													<span class="fileupload-exists"><?php echo trans('admin/user.change'); ?></span>
													<input type="file" class="file-input" name="xlsfile"/>
												</a>
												<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload"><?php echo trans('admin/user.remove'); ?></a>
											</div>
										</div>
									</div>
									<?php if((isset($errorflag)) && ($errorflag == 1)){ ?>  
										<span class="help-inline" style="color:#f00">
											<?php echo trans('admin/user.format_error_excel'); ?>
											<a class="btn btn-circle btn-danger show-tooltip" title="{{ trans('admin/user.download_error_report') }}" href="{{url::to('/cp/usergroupmanagement/bulk-import-error-report')}}">
												<i class="fa fa-download"></i>
											</a>
										</span><br/>
									<?php } ?>
									{!! $errors->first('xlsfile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								</div>
							</div>
							<div class="form-group last">
								<div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
									<button type="submit" class="btn btn-info"><?php echo trans('admin/user.submit'); ?></button>
									<a href="{{URL::to('/cp/usergroupmanagement/')}}" ><button type="button" class="btn"><?php echo trans('admin/user.cancel'); ?></button></a>
								</div>
							</div>
						</form>
					</div>
	            </div>
            </div>
        </div>
    </div>
	<div id="help" class="modal fade">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <!--header-->
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3><i class="icon-file"></i><?php echo trans('admin/user.user_import_information'); ?></h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
            <!--content-->
	      		<div class="modal-body">
	                <br>
	                <ul>
	                	<?php echo trans('admin/user.upload_help_note'); ?>
	                </ul>
	                <br>
			  	</div>
			  	<!--footer-->
			  	<div class="modal-footer">
		      		<a class="btn btn-success" data-dismiss="modal" >
	                    <i class="icon-file"></i><?php echo trans('admin/user.ok'); ?></a>
		  		</div>
			</div>
		</div>
	</div>

	<div id="list_user" class="modal fade">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <!--header-->
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3><i class="icon-file"></i><?php echo trans('admin/user.list_user_group'); ?></h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
            <!--content-->
	      		<div class="modal-body" style="height: 250px; overflow-y: auto;">
	                <br>
	                <ul>
	                <!-- show the list here -->
					@if(count($groupListWithUserCount) > 0)
						@foreach($groupListWithUserCount as $name => $count)
						<li> Group -&nbsp;{{ $name }} &nbsp; : &nbsp; {{ ($count >= 1) ? $count.' users' : "No user" }} </li>
						@endforeach
					@else
						<center>{{trans('admin/user.no_usergroup')}}</center> 
					@endif
	                </ul>
	                <br>
					
			  	</div>
			  	<!--footer-->
			  	<div class="modal-footer">
		      		<a class="btn btn-success" data-dismiss="modal" ><?php echo trans('admin/user.ok'); ?></a>
		  		</div>
			</div>
		</div>
	</div>


<script type="text/javascript">
	$(document).on('click','.upload_button',function(e){
      e.preventDefault();
      $(".upload_button").removeClass("btn-info").addClass("disabled");
      document.getElementById('show_form').style.display="block";
    });
</script>
@stop