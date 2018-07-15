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
	#main-content{
		background:white !important;
	}
	</style>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-content">
	            	<div class="col-md-6">
                        <form class="form-horizontal" action="">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/courses.type')}} :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                              	<?php $type = Input::get('course_type'); $type = strtolower($type); ?>
                                <select class="form-control chosen" name="course_type" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                    <!-- <option value="all" <?php //if ($type == 'all') echo 'selected';?>>All</option> -->
                                    <option value="channel" <?php if ($type == 'channel') echo 'selected';?>>{{trans('admin/courses.channel')}}</option>
                                    <!-- <option value="product" <?php if ($type == 'product') echo 'selected';?>>{{trans('admin/courses.product')}}</option> -->
                                    @if(config('app.ecommerce') === true)
                                    <option value="package" <?php if ($type == 'package') echo 'selected';?>>{{trans('admin/courses.package')}}</option>
                                    <option value="course" <?php if ($type == 'course') echo 'selected';?>>{{trans('admin/courses.course')}}</option>
                                    @endif
                                </select>
                              </div>
                           </div>
                        </form>
                    </div>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall"/></th>
				                <th style="width:20% !important">{{trans('admin/courses.program_name')}}</th>
				                <th>{{trans('admin/courses.short_name')}}</th>
								<th>{{trans('admin/courses.start_date')}}</th>
				                <th>{{trans('admin/courses.end_date')}}</th>
				                <th>{{trans('admin/courses.status')}}</th>
				            </tr>
				        </thead>
				    </table>
                </div>
	        </div>
	    </div>
	    <script>
			var flag_ck = 0;
			function updateCheckBoxVals(){
				$allcheckBoxes = $('#datatable td input[type="checkbox"]');
				if(typeof window.checkedBoxes != 'undefined'){
					$('#datatable td input[type="checkbox"]').each(function(index,value){
						var $value = $(value);
						if(typeof checkedBoxes[$value.val()] != "undefined")
							$('[value="'+$value.val()+'"]').prop('checked',true);
					})
				}
				if($allcheckBoxes.length > 0)
					if($allcheckBoxes.not(':checked').length > 0)
						$('#datatable thead tr th:first input[type="checkbox"]').prop('checked',false);
					else
						$('#datatable thead tr th:first input[type="checkbox"]').prop('checked',true);

				updateHeight();
			}

	    	$(document).ready(function(){
	    		/* code for DataTable begins here */
	    		var $datatable = $('#datatable');
	    		window.datatableOBJ = $('#datatable').on('processing.dt',function(event,settings,flag){
	    			if(flag == true)
	    				simpleloader.fadeIn();
	    			else
	    				simpleloader.fadeOut();
	    		}).on('draw.dt',function(event,settings,flag){
	    			$('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
	    		}).on('xhr.dt',function(a,b,c) {console.dir(a);console.dir(b);console.dir(c);}).dataTable({
	    			"autoWidth": false,
	    			"serverSide": true,
					"ajax": {
			            "url": "{{URL::to('/cp/upcomingcourses/course-iframe-ajax')}}",
			            "data": function ( d ) {
			                //d.filter = $('[name="filter"]').val();
			                d.view = "iframe";
			                d.course_type = $('[name="course_type"]').val();
			                <?php if(isset($from) && in_array($from, array('upcoming','popular'))) echo 'd.from = "'.$from.'"' ?>;
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100, -1],
		                [10, 15, 25, 50, 100, "All"]
		            ],
		            "iDisplayLength": 10,
		            "aaSorting": [[ 2, 'desc' ]],
		            "columnDefs": [ { "targets": [0], "orderable": false } ],
		            "drawCallback" : updateCheckBoxVals
		        });

		        $('#datatable_filter input').unbind().bind('keyup', function(e) {
					if(e.keyCode == 13) {
						datatableOBJ.fnFilter(this.value);
					}
				});
				/* Code for dataTable ends here */

				/* Code to get the selected checkboxes in datatable starts here*/
				if(typeof window.checkedBoxes == 'undefined')
					window.checkedBoxes = {};
				$('#datatable').on('change','td input[type="checkbox"]',function(){
					var $this = $(this);
					if($this.prop('checked'))
						checkedBoxes[$this.val()] = $($this).parent().next().text();
					else
						delete checkedBoxes[$this.val()];
					if(flag_ck == 0){
				        updateCheckBoxVals();
				    }
				});

				$('#checkall').change(function(e){
					$('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
					flag_ck = 1;
					$('#datatable td input[type="checkbox"]').trigger('change');
					flag_ck = 0;
					e.stopImmediatePropagation();
				});
				/* Code to get the selected checkboxes in datatable ends here*/

	    	});
	    </script>
	</div>
@stop
