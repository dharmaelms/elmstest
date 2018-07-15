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
	            	<div class="col-md-6" style="display:none">
                        <form class="form-horizontal" action="">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/promocode.showing') }} :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>{{ trans('admin/promocode.program_all') }}</option>
                                    <option value="ACTIVE" <?php if ($filter == 'active') echo 'selected';?>>{{ trans('admin/promocode.active') }}</option>
                                    <option value="IN-ACTIVE" <?php if ($filter == 'in-active') echo 'selected';?>>{{ trans('admin/promocode.inactive') }}</option>
                                </select>
                              </div>
                           </div>
                        </form>
                    </div>
                    <?php if(Input::get('relid')){ ?>
		            	<div class="col-md-6">
	                        <form class="form-horizontal" action="">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/promocode.showing') }} :</b></label>
	                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                              	<?php $relfilter = Input::get('relfilter');
									$relfilter = strtolower($relfilter);
									
									?>
	                                <select class="form-control chosen" name="relfilter" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                    <option value="nonassigned" <?php if ($relfilter == 'nonassigned') echo 'selected';?>>{{ trans('admin/promocode.non_assigned') }}</option>
	                                    <option value="assigned" <?php if ($relfilter == 'assigned') echo 'selected';?>>{{ trans('admin/promocode.assigned') }}</option>
	                                </select>
	                              </div>
	                           </div>
	                        </form>
	                    </div>
	                <?php } ?>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall"/></th>
				                <th style="width:20% !important">
				                @if(isset($program_type) && $program_type=='content_feed')
				                <?php echo trans("admin/program.program");?>
				                @elseif(isset($program_type) && $program_type=='product')
				                	{{ trans('admin/promocode.program_product') }}
				                @elseif(isset($program_type) && $program_type=='course')
				                	{{ trans('admin/promocode.program_course') }}
				                @elseif(isset($program_type) && $program_type=='package')
				                	{{ trans('admin/promocode.program_package') }}
				                @else 
				                	{{ trans('admin/promocode.program_all') }}
				                @endif
				                </th>
				                <th>{{ trans('admin/promocode.short_name') }}</th>
								<th>{{ trans('admin/promocode.start_date') }}</th>
				                <th>{{ trans('admin/promocode.end_date') }}</th>
				                <!-- <th>Category</th> -->
				                <!-- <th>Packets</th> -->
				                <!-- <th>Users</th>
				                <th>User Groups</th> -->
				                <!-- <th>Duration</th> -->
				                <th>{{trans('admin/promocode.status')}}</th>
				                <!-- <th>{{ trans('admin/promocode.actions') }}</th> -->
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
			            "url": "{{URL::to('/cp/promocode/feed-list-ajax')}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			                d.view = "iframe";
			                d.relfilter = $('[name="relfilter"]').val();
			                <?php if(isset($from) && in_array($from,array('user','usergroup'))) echo 'd.from = "'.$from.'"' ?>;
			                <?php if(isset($relid) && preg_match('/^\d+$/',$relid)) echo 'd.relid = "'.$relid.'"' ?>;
							<?php if(isset($subtype)) echo 'd.subtype = "'.$subtype.'"' ?>;
							<?php if(isset($field)) echo 'd.field = "'.$field.'"' ?>;
							<?php if(isset($program_type)) echo 'd.program_type = "'.$program_type.'"' ?>;
							<?php if(isset($filters)) echo 'd.filters = "'.$filters.'"' ?>;
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
