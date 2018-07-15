@section('content')
<?php
$program_type = 'content_feed';
$program_sub_type = 'single';
?>
	<script>
		if (!Array.prototype.remove) {
			Array.prototype.remove = function(val) {
				var i = this.indexOf(val);
				return i>-1 ? this.splice(i, 1) : [];
			};
		}
		var $targetarr = [0,7,8];
	</script>
	<script src="{{ URL::asset('admin/assets/jquery/jquery-2.1.1.min.js')}}"></script>
	<script src="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.css')}}">
	
	<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	          
	            <div class="box-content">
				<div class="col-md-12 margin-bottom-20">
					<div class="btn-toolbar pull-right clearfix">
            		    <div class="btn-group">
							<input type="hidden" id="export_link_pack" name="export_link_pack" value="{{URL::to('/cp/bulkimport/pack-export/'.$program_type.'/'.$program_sub_type )}}">
							<a class="btn btn-primary btn-sm" id= "export_link"  href="{{URL::to('/cp/bulkimport/pack-export/'.$program_type.'/'.$program_sub_type)}}"><?php echo trans('admin/user.export_log') ?></a>
							<a class="btn btn-primary btn-sm" style= "margin-left:5px;"
							   title="<?php echo trans('admin/program.import_add_channel_template'); ?>"
							   href="{{URL::to('/cp/bulkimport/import-add-program-template/'.$program_type.'/'.$program_sub_type.'/'.$action='ADD')}}">
								<i class="fa fa-download">&nbsp;</i>
                                <?php echo trans('admin/program.import_add_channel_template'); ?>
							</a>
							<a class="btn btn-primary btn-sm" style= "margin-left:5px;"
							   title="<?php echo trans('admin/program.import_update_channel_template'); ?>"
							   href="{{URL::to('/cp/bulkimport/import-update-program-template/'.$program_type.'/'.$program_sub_type.'/'.$action='UPDATE')}}">
								<i class="fa fa-download">&nbsp;</i>
                                <?php echo trans('admin/program.import_update_channel_template'); ?>
							</a>
							<a class="btn btn-circle show-tooltip" title="{{ trans('admin/program.channel_import_help') }}"
								data-toggle="modal" href="#help" style="margin-left: 6px;"><i class="fa fa-question"></i></a>
						</div>
                    </div>
				</div>
                    <br/><br/>
	            	
                    <form class="form-horizontal" action="" name="filterform">
					
			            	<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
		                            <!--start of status filter-->
									<div class="form-group">
		                              <label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
		                              <div class="col-sm-6 col-lg-6 controls" style="padding-left:0;">
		                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
		                                <select class="form-control chosen" name="filter" id="filter" data-placeholder="ALL" >
		                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>All</option>
		                                    <option value="SUCCESS" <?php if ($filter == 'SUCCESS') echo 'selected';?>>Success</option>
		                                    <option value="FAILURE" <?php if ($filter == 'FAILURE') echo 'selected';?>>Failure</option>
		                                </select>
		                              </div>
		                           </div>
									<!--end of status filter-->
									<!--start of action filter-->
									<div class="form-group">
		                              <label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
		                              <div class="col-sm-6 col-lg-6 controls" style="padding-left:0;">
		                              	<?php $filters = Input::get('filters'); $filters = strtoupper($filters); ?>
		                                <select class="form-control chosen" name="filters" id="filters" data-placeholder="ALL" >
		                                    <option value="ALL" <?php if ($filters == 'ALL') echo 'selected';?>>All</option>
		                                    <option value="ADD" <?php if ($filters == 'ADD') echo 'selected';?>>Add</option>
		                                    <option value="UPDATE" <?php if ($filters == 'UPDATE') echo 'selected';?>>Update</option>
		                                </select>
		                              </div>
		                           </div>
									<!--end of action filter-->
									<!--start of created date filter-->
									<div class="form-group">
									    <label class="col-sm-4  col-lg-3 control-label" style="text-align:left"><b>Created Date :</b></label>
									    <div class="col-sm-6  col-lg-6 controls" style="padding-left:0;">
									    <div class="input-group date" >
									    <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
									    <input type="text" readonly name="created_date" id="created_date" class="form-control datepicker"  style="cursor: pointer" value="">
									    </div>
										
									    </div>
								    </div>
									<!--end of created date filter-->
									<!--start of search button-->
									<div class="form-group last">
										<div class="col-sm-9 col-sm-offset-4 col-lg-7 col-lg-offset-5">
										<button class="btn btn-success" type="button" onclick="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1"><?php echo trans('admin/user.search') ?></button>
										<a href="{{URL::to('/cp/bulkimport/channel-import-report')}}"><button type="button" class="btn"><?php echo trans('admin/user.clear') ?></button></a>
										</div>
									</div>
									<!--end of search button-->
							</div>
			              </form>

	            	
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
							    <th>{{Lang::get("admin/program.program")}}</th>
								<th>Short Name</th>
				                <th>Start Date</th>
				                <th>End Date</th>
								<th>Display Start Date</th>
				                <th>Display End Date</th>
								<th>Error Messages</th>
				                <th>Created At</th>
								<th>Status</th>
								<th>{{Lang::get("admin/program.action")}}</th>
	                        	<script>$targetarr.pop()</script>  
				            </tr>
				        </thead>
				    </table>
                </div>
	        </div>
	    </div>
	    <!--start of help-->
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
	                                <h3><i class="icon-file"></i><?php echo trans('admin/program.channel_import_information'); ?></h3>
	                            </div>
	                        </div>
	                    </div>
	                </div>
	            </div>
            <!--content-->
	      		<div class="modal-body">
	                <br>
	                <ul>
	                	<?php echo trans('admin/program.channel_import_help_note'); ?>
	                </ul>
	                <br>
			  	</div>
			  	<!--footer-->
			  	<div class="modal-footer">
		      		<a class="btn btn-success" data-dismiss="modal" >
	                    <i class="icon-file"></i><?php echo trans('admin/program.ok'); ?></a>
		  		</div>
			</div>
		</div>
	</div>
<!--end of help-->
	    <script>

			var  start_page  = {{Input::get('start',0)}};
			var  length_page = {{Input::get('limit',10)}};
			var  search_var  = "{{Input::get('search','')}}";
			var  order_by_var= "{{Input::get('order_by','2 desc')}}";
			var  order = order_by_var.split(' ')[0];
			var  _by   = order_by_var.split(' ')[1];

	    	$(document).ready(function(){
				$('.datepicker').datepicker({
                format : "dd-mm-yyyy",
                //startDate: '+0d'
            }).on('changeDate',function(){
                    $(this).datepicker('hide')
                });
				$('input.tags').tagsInput({
		            width: "auto"
		        });
        		$('#alert-success').delay(5000).fadeOut();
	    		/* code for DataTable begins here */
	    		var $datatable = $('#datatable');
	    		window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag){
				    $('#datatable_processing').hide();
	    			if(flag == true)
	    				simpleloader.fadeIn();
	    			else
	    				simpleloader.fadeOut();
	    		}).on('draw.dt',function(event,settings,flag){
	    			$('.show-tooltip').tooltip({container: 'body'});
	    		}).dataTable({
	    			"serverSide": true,
					"ajax": {
			            "url": "{{URL::to('/cp/bulkimport/channel-import-list')}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
							d.filters = $('[name="filters"]').val();
							d.created_date = $('[name="created_date"]').val();
							
							},
			            "error" : function(){
			            	alert('Please check if you have an active session.');
			            	window.location.replace("{{URL::to('/')}}");
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100],
		                [10, 15, 25, 50, 100]
		            ],
		            "iDisplayLength": 10,
		            "aaSorting": [[ Number(order), _by]],
		            "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
		            //"drawCallback" : updateCheckBoxVals,
		            "iDisplayStart": start_page,
                    "pageLength": length_page,
					"oSearch": {"sSearch": search_var}
		        });

		        $('#datatable_filter input').unbind().bind('keyup', function(e) {
					if(e.keyCode == 13) {
						datatableOBJ.fnFilter(this.value);
					}
				});
				/* Hide the Export with user and Export with usergroup icons if Data table is empty */
				$datatable.on('xhr.dt',function(e, settings, json, xhr){
					if(json.recordsTotal <=0)
					{
						$("#export_link").removeAttr("href").css('cursor', 'default');
					}

				});

	    	});
			
		/*$("#filter").bind("click", function() {
		var selectedValue = $('#filter').find('option:selected').val();
		var created_date = $('#created_date').val();
		
		var exportlink = $('#export_link_pack').val();
		$('#export_link').attr('href',(exportlink+"/"+selectedValue+"/"+created_date));
		});
		
		//$("#created_date").live("click", function() {
		$("#created_date").bind("click", function() {
		var selectedValue = $('#filter').find('option:selected').val();
		var created_date = $('#created_date').val();
		
		var exportlink = $('#export_link_pack').val();
		$('#export_link').attr('href',(exportlink+"/"+selectedValue+"/"+created_date));
		});*/
		
		$("#export_link").click(function() {
		var selectedValue = $('#filter').find('option:selected').val();
		var selectedValues = $('#filters').find('option:selected').val();
		var created_date = $('#created_date').val();
		
		var exportlink = $('#export_link_pack').val();
		$('#export_link').attr('href',(exportlink+"/"+selectedValue+"/"+selectedValues+"/"+created_date));
		});
			
	    </script>
	</div>
	
@stop
