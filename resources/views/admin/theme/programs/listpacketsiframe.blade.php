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
	.post-table tr th{
		text-align: left !important;
	}
	</style>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-content">
	            	<div class="col-md-6" style="display:none">
                        <form class="form-horizontal" action="">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.showing')}} :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>{{trans('admin/program.all')}}</option>
                                    <option value="ACTIVE" <?php if ($filter == 'active') echo 'selected';?>>{{trans('admin/program.active')}}</option>
                                    <option value="IN-ACTIVE" <?php if ($filter == 'in-active') echo 'selected';?>>{{trans('admin/program.in_active')}}</option>
                                </select>
                              </div>
                           </div>
                        </form>
                    </div>
                    <div class="clearfix"></div>
                    <table class="table table-advance post-table" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px">
                                @if ($input_type == "checkbox")    
				                	<input type="checkbox" id="checkall" />
				                @endif
				                </th>
				                <th style="width:35% !important">{{trans('admin/program.packet')}} {{trans('admin/program.title')}}</th>
				                <th>{{trans('admin/program.packet_publish_date')}}</th>
				                <th>{{trans('admin/program.updated_at')}}</th>
				                <th>{{trans('admin/program.status')}}</th>
				            </tr>
				        </thead>
				    </table>
                </div>
	        </div>
	    </div>
	    <script src="{{URL::asset('admin/js/readmore.js')}}"></script>
	    <script>
	    	var flag_ck = 0;   
	    	function updateCheckBoxVals(){
				$allcheckBoxes = $('#datatable td input[type="checkbox"]');
				if(typeof window.checkedBoxes != 'undefined'){
					$allcheckBoxes.each(function(index,value){
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
	    		window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag){
	    			if(flag == true)
	    				simpleloader.fadeIn();
	    			else
	    				simpleloader.fadeOut();
	    		}).on('draw.dt',function(event,settings,flag){
	    			$('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
                    $('td:nth-child(2) div, td:nth-child(3) div', '#datatable tr').each(function(){
                        if($(this).height() > 75){
                            $(this).readmore({maxHeight: 55,moreLink: '<a href="#" style="padding-left:8px">Read more</a>',lessLink: '<a href="#" style="padding-left:8px">Close</a>'});
                        }
                    })
	    		}).dataTable({
	    			"serverSide": true,
					"ajax": {
			            "url": "{{URL::to("/cp/contentfeedmanagement/packet-list-ajax/{$program->program_type}/{$program->program_slug}/{$input_type}")}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			                d.view = "iframe";
			                d.from_event = "event";
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100],
		                [10, 15, 25, 50, 100]
		            ],
		            "iDisplayLength": 10,
		            "aaSorting": [[ 3, 'desc' ]],
		            "columnDefs": [ { "targets": [0,2], "orderable": false } ],
		            "drawCallback" : updateCheckBoxVals
		        });

		        $('#datatable_filter input').unbind().bind('keyup', function(e) {
					if(e.keyCode == 13) {
						datatableOBJ.fnFilter(this.value);
					}
				});

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
				/* Code for dataTable ends here */

				$datatable.on('click','.deletepacket',function(e){
					e.preventDefault();
	    			var $this = $(this);
					var $deletemodal = $('#deletemodal');
	    			$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href')).end().modal('show');
				})
			});
	    </script>
	</div>
@stop