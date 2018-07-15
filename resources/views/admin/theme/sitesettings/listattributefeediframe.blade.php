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
	            	<!--<div class="col-md-6" style="display:none">
                        <form class="form-horizontal" action="">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>All</option>
                                    <option value="ACTIVE" <?php if ($filter == 'active') echo 'selected';?>>Active</option>
                                    <option value="IN-ACTIVE" <?php if ($filter == 'in-active') echo 'selected';?>>In-Active</option>
                                </select>
                              </div>
                           </div>
                        </form>
                    </div>-->
                   <!-- <?php if(Input::get('relid')){  ?>
		            	<div class="col-md-6">
	                        <form class="form-horizontal" action="">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
	                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                              	<?php $relfilter = Input::get('relfilter'); $relfilter = strtolower($relfilter); ?>
	                                <select class="form-control chosen" name="relfilter" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                    <option value="assigned" <?php if ($relfilter == 'assigned') echo 'selected';?>>Assigned</option>
	                                    <option value="nonassigned" <?php if ($relfilter == 'nonassigned') echo 'selected';?>>Non Assigned</option>
	                                </select>
	                              </div>
	                           </div>
	                        </form>
	                    </div>
	                <?php } ?>-->
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall" /></th>
				                <th style="width:20% !important">{{ trans('admin/sitesetting.product_type') }}</th>
				                
				            </tr>
				        </thead>
				    </table>
                </div>
	        </div>
	    </div>
	    <script>
			
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
                        "url": "{{URL::to('/cp/manageattribute/feed-list-ajax')}}",
			            "data": function ( d ) {
                        
			                d.filter = $('[name="filter"]').val();
			                d.view = "iframe";
			                d.relfilter = $('[name="relfilter"]').val();
			              
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100, -1],
		                [10, 15, 25, 50, 100, "All"]
		            ],
		            "iDisplayLength": 10,
		            "aaSorting": [[ 1, 'desc' ]],
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
				});

				$('#checkall').change(function(e){
					$('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
					$('#datatable td input[type="checkbox"]').trigger('change');
					e.stopImmediatePropagation();
				});
				/* Code to get the selected checkboxes in datatable ends here*/



	    	});
	    </script>
	</div>
@stop
