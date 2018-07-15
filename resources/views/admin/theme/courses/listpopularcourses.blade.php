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
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <script src="{{ URL::asset('admin/js/readmore.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	            </div>
	            <div class="box-content">
	            	@if(has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::ADD_POPULAR_COURSES))
	                    <div class="row">
			            	<div class="col-md-12">
				            	<div class="btn-toolbar pull-right clearfix">
				            		<a href="{{URL::to('/cp/upcomingcourses/course-iframe?from=popular')}}" class='productrel btn btn-primary btn-sm' data-info="popular" data-text="Add to list"><i class="fa fa-plus"></i> {{trans('admin/courses.add_to_list')}}</a>
				            	</div>
			            	</div>
		            	</div>
		            @endif

                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width: 600px">{{trans('admin/courses.name')}}</th>
				                <th>{{trans('admin/courses.type')}}</th>
				                <th>{{trans('admin/courses.start_date')}}</th>
				                <th>{{trans('admin/courses.end_date')}}</th>
				                <th>{{trans('admin/courses.actions')}}</th>
				            </tr>
				        </thead>
				    </table>
                </div>
	        </div>
	    </div>

	    <!-- Assigning relation to users window -->
		<div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
		                                        {{trans('admin/courses.view_media_details')}}
		                                </h3>
		                            </div>
		                        </div>
		                    </div>
		                </div>
		            </div>
		            <div class="modal-body">
		                ...
		            </div>
		            <div class="modal-footer" style="padding-right: 47px">
		              <!-- <div style="float: left;" id="selectedcount"> 0 selected</div> -->
		                <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/courses.save')}}</a>
		                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/courses.cancel')}}</a>
		            </div>
		        </div>
		    </div>
		</div>

		<!-- delete window -->
		<div id="deletemodal" class="modal fade">
		    <div class="modal-dialog">
		        <div class="modal-content">
		            <!--header-->
		            <div class="modal-header">
		                <div class="row custom-box">
		                    <div class="col-md-12">
		                        <div class="box">
		                            <div class="box-title">
		                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
		                                <h3><i class="icon-file"></i>{{trans('admin/courses.delete_course')}}</h3>
		                            </div>
		                        </div>
		                    </div>
		                </div>
		            </div>
		            <!--content-->
		            <div class="modal-body" style="padding: 20px">
		                {{trans('admin/courses.modal_delete_course')}}
		            </div>
		            <!--footer-->
		            <div class="modal-footer">
		              <a class="btn btn-danger">{{trans('admin/courses.yes')}}</a>
		              <a class="btn btn-success" data-dismiss="modal">{{trans('admin/courses.close')}}</a>
		            </div>
		        </div>
		    </div>
		</div>
		<!-- delete window ends -->

		<script>

	    	var  start_page  = {{Input::get('start',0)}};
        	var  length_page = {{Input::get('limit',10)}};

	    	$(document).ready(function(){
	    		$('.alert-success').delay(5000).fadeOut();
	    		/* code for DataTable begins here */
	    		var $datatable = $('#datatable');
	    		window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag){
	    			if(flag == true)
	    				simpleloader.fadeIn();
	    			else
	    				simpleloader.fadeOut();
	    		}).on('draw.dt',function(event,settings,flag){
	    			$('.show-tooltip').tooltip({container: 'body'});
	    			$('#datatable tr td:nth-child(1) div').each(function(){
	    				console.log($(this).height());
	    				if($(this).height() > 75){
	    					$(this).readmore({maxHeight: 45,moreLink: '<a href="#" style="padding-left:8px">Read more</a>',lessLink: '<a href="#" style="padding-left:8px">{{trans('admin/courses.close')}}</a>'});
	    				}
	    			})
	    		}).dataTable({
	    			"serverSide": true,
					"ajax": {
			            "url": "{{URL::to('/cp/popularcourses/popular-courses-ajax/')}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100],
		                [10, 15, 25, 50, 100]
		            ],
		            "columnDefs": [ { "targets": [0, 1, 2, 3, 4], "orderable": false } ],
		            "order": [],
		            "iDisplayStart": start_page,
                    "pageLength": length_page,
                    "bFilter": false,
                    "bLengthChange": false,
		        });
				/* Code for dataTable ends here */

				//individual user delete
			    $(document).on('click','.deletecourse',function(e){
			      e.preventDefault();
			      var $this = $(this);
			      var $deletemodal = $('#deletemodal');
			        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
			      $deletemodal.modal('show');
			    });

				/* Code for user rel starts here */
			    $(document).on('click','.productrel',function(e){
				    e.preventDefault();
				    simpleloader.fadeIn();
				    var $this = $(this);
				    var $triggermodal = $('#triggermodal');
				    var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');

				    $iframeobj.unbind('load').load(function(){
				        $('#selectedcount').text('0 Entrie(s) selected');

				        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
				            $triggermodal.modal('show');
				        simpleloader.fadeOut();

				        /* Code to Set Default checkedboxes starts here*/
				        $.each($this.data('json'),function(index,value){
				            $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
				        })
				        /* Code to Set Default checkedboxes ends here*/

				        /* Code to refresh selected count starts here*/
				        $iframeobj.contents().click(function(){
				        	alert('true');
				            var count = 0;
				            $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
				                count++;
				            });
				            $('#selectedcount').text(count+ ' selected');
				        });
				        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
				        /* Code to refresh selected count ends here*/
				    })

				    $triggermodal.find('.modal-body').html($iframeobj);
				    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

				    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
				        alert("Btn Clicked");
				    });

				    //code for top assign button click
				    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
				        $(this).parents().find('.modal-footer .btn-success').click();
				    });
				    //code for top assign button ends here

				    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
				    	//alert('true');
				        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
				        var $postdata = "";
				        $.each($checkedboxes,function(index,value){
				            if(!$postdata)
				                $postdata += index;
				            else
				                $postdata += "," + index;
				        });

				        // Post to server
				        var action = $this.data('info');

				        simpleloader.fadeIn();
				        $.ajax({
				            type: "GET",
				           	url: '{{URL::to('/cp/upcomingcourses/add-program/')}}/'+action,
				           	data: 'ids='+$postdata
				        })
				        .done(function( response ) {
				            if(response.flag == "success")
				                $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button> {{ trans('admin/category.list_success') }} </div>').insertAfter($('.page-title'));
				            else if(response.flag == 'overflow')
				            	alert(response.message);
				            else
				                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button> <?php echo trans('admin/category.server_error');?></div>').insertAfter($('.page-title'));

							if(response.flag != 'overflow')
							{
								$triggermodal.modal('hide');
					            setTimeout(function(){
					                $('.alert').alert('close');
					            },5000);
					            window.datatableOBJ.fnDraw(true);
							}

				            simpleloader.fadeOut(200);

				        })
				        .fail(function() {
				            $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/category.server_error');?></div>').insertAfter($('.page-title'));
				            window.datatableOBJ.fnDraw(true);
				            simpleloader.fadeOut(200);
				        })
				    })
				});
			    /* Code for user dams rel ends here */

			    /*Code to hide success message after 5seconds*/
			    $('#alert-success').delay(5000).fadeOut();
			});
	    </script>
	</div>
@stop