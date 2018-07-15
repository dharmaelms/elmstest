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
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> {{ trans('admin/assessment.import_question') }} Banks In Bulk</h3>
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div> -->
	            </div>
	            <div class="box-content">
	            	<div class="btn-toolbar clearfix">
	            		<div class="pull-right">
							<a class="btn btn-circle show-tooltip" title="View Questionbank Import History" href="{{ URL::to('cp/assessment/questionbank-import-history') }}"><i class="fa fa-eye"></i></a>
						</div>
	            		<div class="col-md-offset-4 col-md-8" style="margin-bottom:20px">
							<a class="btn btn-gray show-tooltip" title="Download sample template" href="{{ URL::to('cp/assessment/question-bank-bulkimport-template') }}"  style="margin-left: 6px;"><i class="fa fa-download"> {{ trans('admin/assessment.download') }}</i></a>
							<a class="btn btn-circle show-tooltip" title="File upload help" data-toggle="modal" href="#help" style="margin-left: 6px;"><i class="fa fa-question"></i></a>
						</div>
					</div>
	            	<form action="{{URL::to('cp/assessment/import-questionbank/'.$quiz_id)}}" class="form-horizontal form-bordered form-row-stripped" method="post" enctype='multipart/form-data'>
						<div class="form-group">
							<label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/assessment.select_file') }} <span class="red">*</span></label>
							<div class="col-sm-6 col-lg-5 controls">
								<div class="fileupload fileupload-new" data-provides="fileupload" style="margin-bottom:0;">
									<div class="input-group">
										<div class="form-control uneditable-input">
											<i class="fa fa-file fileupload-exists"></i> 
											<span class="fileupload-preview"></span>
										</div>
										<div class="input-group-btn">
											<a class="btn bun-default btn-file">
												<span class="fileupload-new">{{ trans('admin/assessment.browse') }}</span>
												<span class="fileupload-exists">{{ trans('admin/assessment.change') }}</span>
												<input type="file" class="file-input" accept=".csv" name="csvfile"/>
											</a>
											</a>
											<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/assessment.remove') }}</a>
										</div>
									</div>
								</div>
								<?php if(Session::get('errorflag')){?>  
									<span class="help-inline" style="color:#f00">
										{{ trans('admin/assessment.format_error_csv') }}
										<a class="btn btn-circle btn-danger show-tooltip" title="Download error report" href="{{url::to('/cp/assessment/export-error-report')}}">
											<i class="fa fa-download"></i>
										</a>
									</span><br/>
								<?php Session::forget('errorflag'); }?>
								{!! $errors->first('csvfile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
								{!! $errors->first('extension', '<span class="help-inline" style="color:#f00">:message</span>') !!}
							</div>
						</div>
						<div class="form-group last">
							<div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
								<button type="submit" class="btn btn-info">{{ trans('admin/assessment.submit') }}</button>
								<a href="{{URL::to('/cp/assessment/list-questionbank')}}" ><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
							</div>
						</div>
					</form>
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
	                                <h3><i class="icon-file"></i>{{ trans('admin/assessment.question_bank_import_info') }}</h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
            <!--content-->
	      		<div class="modal-body">
	                <br>
	                <ul>
	                	<?php echo trans('admin/assessment.mcq_help_tip'); ?>
	                </ul>
	                <br>
			  	</div>
			  	<!--footer-->
			  	<div class="modal-footer">
		      		<a class="btn btn-success" data-dismiss="modal" >{{ trans('admin/assessment.question_bank_import_info') }}</a>
		  		</div>
			</div>
		</div>
	</div>
@stop
