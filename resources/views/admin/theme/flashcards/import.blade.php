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
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Import Question Banks In Bulk</h3>
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div> -->
	            </div>
	            <div class="box-content">
	            	<div class="btn-toolbar clearfix">
	            		<div class="pull-right">
							{{-- <a class="btn btn-circle show-tooltip" title="View Questionbank Import History" href="{{ URL::to('cp/assessment/questionbank-import-history') }}"><i class="fa fa-eye"></i></a> --}}
						</div>
	            		<div class="col-md-offset-4 col-md-8" style="margin-bottom:20px">
							<a class="btn btn-gray show-tooltip" title="{{ trans('admin/flashcards.download_sample_template') }}" href="{{ URL::to('/exceltemplate/flashcard_bulk_import.csv') }}"  style="margin-left: 6px;"><i class="fa fa-download"> {{ trans('admin/flashcards.download') }}</i></a>
							{{-- <a class="btn btn-circle show-tooltip" title="File upload help" data-toggle="modal" href="#help" style="margin-left: 6px;"><i class="fa fa-question"></i></a> --}}
						</div>
					</div>
	            	<form action="{{URL::to('cp/flashcards/import')}}" class="form-horizontal form-bordered form-row-stripped" method="post" id="import-flashcard" enctype='multipart/form-data'>
						<div class="form-group">
							<label class="col-sm-4 col-lg-3 control-label">{{ trans('admin/flashcards.select_file') }} <span class="red">*</span></label>
							<div class="col-sm-6 col-lg-5 controls">
								<div class="fileupload fileupload-new" data-provides="fileupload" style="margin-bottom:0;">
									<div class="input-group">
										<div class="form-control uneditable-input">
											<i class="fa fa-file fileupload-exists"></i> 
											<span class="fileupload-preview"></span>
										</div>
										<div class="input-group-btn">
											<a class="btn bun-default btn-file">
												<span class="fileupload-new">{{ trans('admin/flashcards.browse') }}</span>
												<span class="fileupload-exists">{{ trans('admin/flashcards.change') }}</span>
												<input type="file" id="file" class="file-input" accept=".csv" name="file"/>												
											</a>
											<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{ trans('admin/flashcards.remove') }}</a>
										</div>
									</div>
									<span id="file_error" class="help-block error required" style="display:none;">{{ trans('admin/flashcards.name_field_required') }}.</span>
								</div>								
							</div>
						</div>
						<div class="form-group last">
							<div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
								<button type="submit" class="btn btn-info">{{ trans('admin/flashcards.submit') }}</button>
								<a href="{{URL::to('/cp/assessment/list-questionbank')}}" ><button type="button" class="btn">{{ trans('admin/flashcards.cancel') }}</button></a>
							</div>
						</div>
					</form>
	            </div>
            </div>
        </div>
    </div>

	{{-- <div id="help" class="modal fade">
	    <div class="modal-dialog">
	        <div class="modal-content">
	            <!--header-->
	            <div class="modal-header">
	                <div class="row custom-box">
	                    <div class="col-md-12">
	                        <div class="box">
	                            <div class="box-title">
	                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                <h3><i class="icon-file"></i>Question Bank Import Information</h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
            <!--content-->
	      		<div class="modal-body">
	                <br>
	                <ul>
	                	<li>The fields marked with * are mandatory.</li>
	                	<li>Keywords are "","" seperated values and it can contain any characters.</li>
	                	<li>Default Mark should be number.</li>
	                	<li>Difficulty Level should be EASY or MEDIUM or DIFFICULT.</li>
	                	<li>Shuffle answers can either be 1 or blank. If you require to shuffle options, you need to set it as 1.</li>
	                	<li>There should be minimum of 2 answers &amp; maximum of 4 answers.</li>
	                	<li>Put the value as 1 for any one of the correct answers.</li>
	                	<li>Rationale is a feedback to each answers and it is optional.</li>
	                	<li>Only CSV file is supported.</li>
	                	<li>If no question bank is specified, by default it will add into question bank named "General".</li>
	                </ul>
	                <br>
			  	</div>
			  	<!--footer-->
			  	<div class="modal-footer">
		      		<a class="btn btn-success" data-dismiss="modal" >OK</a>
		  		</div>
			</div>
		</div>
	</div> --}}
<script type="text/javascript">
	$(document).ready(function(){

		$(document).on('submit', '#import-flashcard', function(e){
			e.preventDefault();
			var file_data = $('#file').prop('files')[0];
			var form_data = new FormData();                  
    		form_data.append('file', file_data);
			var upload = $.ajax({
					url: "{{ URL::to('cp/flashcards/import')}}",
					method: "POST",
					cache: false,
	                contentType: false,
	                processData: false,
	                data: form_data,
					dataType: 'json'
				});
			upload.done(function(data){
				$('.error').hide().parent().removeClass('has-error');
				if(data.failure == true){
					$.each(data.message, function(index, value){
						console.log(value);
						$('<span class="help-block error required">'+value+'</span>').insertBefore('#file_error').parent().addClass('has_error');
					});
				}
			});
			upload.fail(function(data){
				console.log(data);
			});
		});
		// return false;
	});
</script>
@stop
