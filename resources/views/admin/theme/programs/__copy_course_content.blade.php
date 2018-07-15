<div class="modal fade" id="copy_content_list" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
	        <div class="modal-dialog modal-lg">
	            <div class="modal-content">
	                <div class="modal-header">
	                    <div class="row custom-box">
	                        <div class="col-md-12">
	                            <div class="box">
	                                <div class="box-title">
	                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
	                                    <h3 class="modal-header-title">
	                                        <i class="icon-file"></i>
	                                        {{trans('admin/batch/copy_course_content.course_copy_modal_header')}}
	                                    </h3>                                                
	                                </div>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	       
	                <div class="modal-body">

	                	<div class="alert alert-success hide" id="copy_content_list_success" name="copy_content_list_success">
	                	{{trans('admin/batch/copy_course_content.course_copy_success_label')}}
	                	</div>
	                <span id="loading_icon" class="hide pull-right" style="margin-right: 10px;"><i class="fa fa-spinner fa-pulse fa-3x fa-fw margin-bottom"></i></span>
	                	<div class="row">
						    <div class="col-md-12">
						        <div class="box">
						            <div class="box-content">
						                    <div id="copy_list">
	                    					</div>
	                    			</div>
	                    		</div>
	                    	</div>
	                    </div>
	                    <input type="hidden" id="copy_to" name="copy_to" value="">
	                </div>
	                <div class="modal-footer" style="margin-right: 50px;margin-bottom: 10px;">
	                    
	                    <a class="btn btn-success copy"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/batch/copy_course_content.course_copy_submit_label')}}</a>
	                    <a href="" class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/batch/copy_course_content.course_copy_cancel_label')}}</a>
	                </div>
	            </div>
	        </div>
	    </div>
	    <script type="text/javascript">

	    	 $(document).on("click", ".open-course-copy-list", function () {
	    	 	var slug = $(this).data('tocopy');   
	    	 	$('#copy_to').val(slug);
	    	 	var parentcourse = $(this).data('parentcourse'); 
	    	 	 $.ajax({
                      method: "POST",
                      url: "{{URL::to('cp/contentfeedmanagement/course-list-for-copy-content')}}",
                      data:{ 
                        slug: slug,
                        parentcourse:parentcourse
                      }
                    })
                      .done(function( msg ) {
                        $('#copy_list').html(msg);
                });
                $("#copy_content_list_success").addClass('hide');
	    	 	$("#copy_content_list").modal('show');
	    	 	$('.copy').attr('disabled',false);
	    	 	$('#loading_icon').addClass('hide');
	    	 });

	    	 $(document).on("click", ".copy", function () {
	    	 	if(!confirm("{{trans('admin/batch/copy_course_content.copy_content_conform_message')}}")) return false;
	    	 	var copy_to = $('#copy_to').val();   
	    	 	var from_copy = $('#from_copy:checked').val();
	    	 	$('.copy').attr('disabled',true);
	    	 	$('#loading_icon').removeClass('hide');
	    	 	 $.ajax({
                      method: "POST",
                      url: "{{URL::to('cp/contentfeedmanagement/copy-course-content')}}/"+from_copy+'/'+copy_to,
                      data:{ 
                       
                      }
                    })
                      .done(function( msg ) {
	    	 			$("#copy_content_list_success").removeClass('hide');
	    	 			$('input:radio').attr('disabled',true);
	    	 			$('#loading_icon').addClass('hide');
                });
	    	 });

	    	 $('#copy_content_list').on('hidden.bs.modal', function () {
				var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);var a = $(this).addClass('anim-turn180');setTimeout(function(){a.removeClass('anim-turn180');},500);
			});
	    </script>