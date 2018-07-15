<?php $max_addmedia_items = config('app.max_addmedia_items');?>
<div class="alert alert-success hide" id="alert-success">
	<button class="close" data-dismiss="alert">×</button>
	<span id="success_message"></span>
</div>
<div class="pull-right">
	<a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/dams.allowed_type_help') ?>" ><i class="fa fa-question"></i></a>
</div>
<form name="upload" id="add-media" enctype="multipart/form-data" class="form-horizontal form-bordered form-row-stripped" method="post">

    <div class="form-group" id="success_file0">
		<div class="col-sm-7 col-lg-8 controls">
			<div class="fileupload fileupload-new" data-provides="fileupload">
				<div class="input-group">
					<div class="input-group-btn">
						<a class="btn bun-default btn-file">
							<span class="fileupload-new">{{trans('admin/program.select_file')}}</span>
							<span class="fileupload-exists">{{trans('admin/program.change')}}</span>
							<input type="file" class="file-input" name="file[0]" />
						</a>
						<a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{trans('admin/program.remove')}}</a>
					</div>
					<div class="form-control uneditable-input">
						<i class="fa fa-file fileupload-exists"></i> 
						<span class="fileupload-preview"></span>
					</div>
				</div>
			</div>
			<span class="help-inline" id="error_file0" style="color:#f00"></span>
		</div>
	</div>
    <div class="form-group hide" id="choice_div">
        <div class="col-sm-5 col-lg-6 controls">
            <input type="hidden" id="choice_count" name="choice_count" value="{{ Input::old('choice_count', 1) }}">
            <a href="#" id="add-choice">
                <button class="btn btn-circle btn-success btn-xs"><i class="fa fa-plus"></i></button> Add more media items{{Input::old('choice_count')}}
            </a>
        </div>
    </div>

    <div class="form-group">
        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
            <button type="submit" class="btn btn-info text-right">Update </button>
			<button type="button" class="btn btn-cancel" data-btn-belongs-to="items_upload">
				{{trans('admin/program.cancel')}}
			</button>
        </div>
    </div>
</form>

<script type="text/javascript">
	

    $(function(){

    	$('input[type=file]').change(function (){
			$('#choice_div').removeClass('hide');
		});

        $('#add-choice').click( function(){
            var count = parseInt($("input[name='choice_count']").val());
            var html = '';
            var max_addmedia_items = '<?php echo $max_addmedia_items; ?>';
            if(count < max_addmedia_items)
            {
	            for(i=count; i<count+1; i++)
	            {
	            	html += '<div class="form-group" id="success_file'+i+'"><div class="col-sm-7 col-lg-8 controls"><div class="fileupload fileupload-new" data-provides="fileupload"><div class="input-group"><div class="input-group-btn"><a class="btn bun-default btn-file"><span class="fileupload-new">Select file</span><span class="fileupload-exists">Change</span><input type="file" class="file-input" name="file['+i+']" /></a><a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">{{trans('admin/program.remove')}}</a></div><div class="form-control uneditable-input"><i class="fa fa-file fileupload-exists"></i><span class="fileupload-preview"></span></div></div></div><span class="help-inline" id="error_file'+i+'" style="color:#f00"></span></div></div>';
	            }
	            $(html).insertBefore('#choice_div');
	            $("input[name='choice_count']").val(count+1);
	            $('#choice_div').addClass('hide');
	        }
	        else
	        {
	        	html += '<span class="help-inline">You cannot add more than '+max_addmedia_items+' items at a time.</span>'
	        	$(html).insertBefore('#choice_div');
	        	$('#choice_div').hide();
	        }

	        $('input[type=file]').change(function (){
	        	$('#choice_div').removeClass('hide');	
			});
        });
    });

	$('#add-media').on('submit', function(e){
		e.preventDefault();
		var formData = new FormData($('form#add-media')[0]);
		simpleloader.fadeIn();
		$.ajax({
			url:"/cp/contentfeedmanagement/upload-media/{{$program_type}}/{{$program_slug}}/{{$packet_slug}}",
			method:'POST',
			data:formData,
			contentType:false,
			processData:false,
		}).done(function(response) {
			simpleloader.fadeOut();
			$.each(response.success_array, function(key, value) {
                $('#success_file'+value).remove();
            });
            $.each(response.error_array, function(key, value) {
                $('#error_file'+key).text(value);
            });
            if(response.error_count == 0 || response.error_count == '')
            {
            	window.location.reload(true);
            }
            else if(response.success_count > 0 || response.success_count != '')
            {
            	$("#alert-success").removeClass('hide');
            	$("#success_message").html(response.success_count+' file(s) uploaded successfully');
            	$('#alert-success').delay(5000).fadeOut();
            }
            else
            {
            	//nothing to do
            }
        }).fail(function(response) {
            alert( "Error while uploading. Please try again" );
        });
    });

</script>