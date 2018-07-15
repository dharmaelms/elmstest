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
	<style>
		.center {
		    text-align: center !important;
		}
	</style>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Add Media</h3> -->
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div>
	            </div>
	            <div class="box-content">
	            	<form action="{{URL::to('cp/dams/bulk-import')}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype='multipart/form-data'>
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.import_file')}}<span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<div class="fileupload fileupload-new" data-provides="fileupload">
									<div class="input-group">
										<div class="input-group-btn">
											<a class="btn bun-default btn-file">
												<span class="fileupload-new">{{ trans('admin/dams.select_file')}}</span>
												<span class="fileupload-exists">{{ trans('admin/dams.change')}}</span>
												<input type="file" class="file-input" name="xlsfile"/>
											</a>
											<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/dams.remove')}}</a>
										</div>
										<div class="form-control uneditable-input">
											<i class="fa fa-file fileupload-exists"></i> 
											<span class="fileupload-preview"></span>
										</div>
									</div>
								</div>
								<span class="help-inline">
									{{ trans('admin/dams.file_support_note')}}
									<a class="btn btn-circle btn-success show-tooltip" title="Download Sample template" href="{{url::to('/exceltemplate/Media {{ trans('admin/dams.bulk_import')}}.xlsx')}}">
										<i class="fa fa-download"></i>
									</a>
								</span><br/>
								<?php $errorflag = session('errorflag'); ?>
								<?php if(isset($errorflag)){ ?>  
									<span class="help-inline">
										{{ trans('admin/dams.format_error')}}
										<a class="btn btn-circle btn-danger show-tooltip" title="Download Error Report" href="{{url::to('/cp/dams/bulk-import-error-report')}}">
											<i class="fa fa-download"></i>
										</a>
									</span><br/>
								<?php } ?>
								{!! $errors->first('xlsfile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/dams.zip_file')}} <span class="red">*</span></label>
							<div class="col-sm-9 col-lg-10 controls">
								<div class="fileupload fileupload-new" data-provides="fileupload">
									<div class="input-group">
										<div class="input-group-btn">
											<a class="btn bun-default btn-file">
												<span class="fileupload-new">{{ trans('admin/dams.select_file')}}</span>
												<span class="fileupload-exists">{{ trans('admin/dams.change')}}</span>
												<input type="file" class="file-input" name="zipfile"/>
											</a>
											<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/dams.remove')}}</a>
										</div>
										<div class="form-control uneditable-input">
											<i class="fa fa-file fileupload-exists"></i> 
											<span class="fileupload-preview"></span>
										</div>
									</div>
								</div>
								<span class="help-inline">{{ trans('admin/dams.note_bulk_import')}}</span><br />
								{!! $errors->first('zipfile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group">
							<div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
								<button type="submit" class="btn btn-primary"><i class="fa fa-upload"></i> {{ trans('admin/dams.upload')}}</button>
								<a href="{{URL::to('/cp/dams/')}}" ><button type="button" class="btn">{{ trans('admin/dams.cancel')}}</button></a>
							</div>
						</div>
					</form>
	            </div>
            </div>
        </div>
    </div>
@stop