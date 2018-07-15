@section('content')

<style type="text/css">
	td {word-break: break-word; }
</style>
	@if ( Session::get('success') )
		<div class="alert alert-success" id="alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
		<?php Session::forget('success'); ?>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <script src="{{ URL::asset('admin/js/readmore.js')}}"></script>
    <?php
        $sort_by = SiteSetting::module('General', 'sort_by');
    ?>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <div class="box-tool">
	                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
	                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
	                </div>
	            </div>
	            <div class="box-content">
                    @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST) || has_admin_permission(ModuleEnum::COURSE, CoursePermission::MANAGE_COURSE_POST))
                        <div class="btn-toolbar pull-right clearfix">
                            <div class="btn-group">
                                <div class="btn-group">
                                    <a class="btn btn-primary btn-sm"
                                       href="{{url::to("/cp/contentfeedmanagement/add-packets/{$type}/{$slug}")}}">
                                        <span class="btn btn-circle blue custom-btm"> <i class="fa fa-plus"></i> </span>
                                        &nbsp;Add New {{trans('admin/program.packet')}}
                                    </a>&nbsp;&nbsp;
                                </div>
                            </div>
                        </div>
                    @endif
                    <br/><br/>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th><input type="checkbox" id="checkall" /></th>
				                <th style="width: 350px">{{trans('admin/program.packet')}} {{trans('admin/program.title')}}</th>
				                <th>{{trans('admin/program.packet_publish_date')}}</th>
                                <th>{{trans('admin/program.created_at')}}</th>
				                <th>{{trans('admin/program.updated_at')}}</th>
				                <th>{{trans('admin/program.status')}}</th>
				                <th>{{trans('admin/program.element')}}</th>
				                <th>Q and A</th>
				                <th>{{trans('admin/program.actions')}}</th>
				            </tr>
				        </thead>
				    </table>
                </div>
	        </div>
	    </div>

	    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                {{trans('admin/program.packet_delete')}}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px;">
                        Are you sure you want to delete this {{strtolower(trans('admin/program.packet'))}} ?
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/program.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <style type="text/css">
        .custom-table1{
        	padding: 0 20px;
        }
        .custom-table1 table td,
        .custom-table1 table th{
        	padding: 5px !important;
        }
        </style>
        <div class="modal fade" id="postrelations" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div  class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                 {{trans('admin/program.packet_delete')}}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body custom-table1">
                        ...
                    </div>
                    <div class="modal-footer">
                    	<b>{{trans('admin/program.modal_delete_post')}}</b>
                        <a class="btn btn-danger">{{trans('admin/program.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="viewpacketdetails" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row custom-box">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title" >
                                            <i class="icon-file"></i>
                                                {{trans('admin/program.view_packet_details')}}
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>

	    <script>

			var  start_page  = {{Input::get('start',0)}};
			var  length_page = {{Input::get('limit',10)}};
			var  search_var  = "{{Input::get('search','')}}";
            var sort_by = "{{ $sort_by }}";

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
			}
	    	$(document).ready(function(){
	    		$('#alert-success').delay(5000).fadeOut();
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
	    					$(this).readmore({maxHeight: 45,moreLink: '<a href="#" style="padding-left:8px">Read more</a>',lessLink: '<a href="#" style="padding-left:8px">{{trans('admin/program.close')}}</a>'});
	    				}
	    			})
	    		}).dataTable({
	    			"serverSide": true,
					"ajax": {
			            "url": '{{URL::to("/cp/contentfeedmanagement/packet-list-ajax/{$type}/{$slug}/{$input_type}")}}',
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100],
		                [10, 15, 25, 50, 100]
		            ],
		            "iDisplayLength": 10,
		            "aaSorting": (sort_by == 'created_at' ) ? [3, "asc"] : [4, "desc"],
		            "columnDefs": [ { "targets": [0,5,6,7,8], "orderable": false } ],
		            "drawCallback" : updateCheckBoxVals,
		            "iDisplayStart": start_page,
                    "pageLength": length_page,
                    "oSearch": {"sSearch": search_var},
                    "aoColumns": [
					    null,
					    {"sClass": "post_title" },
					    null,
					    null,
					    null,
					    null,
					    null
					]
		        });

		        $('#datatable_filter input').unbind().bind('keyup', function(e) {
					if(e.keyCode == 13) {
						datatableOBJ.fnFilter(this.value);
					}
				});
				/* Code for dataTable ends here */

				$datatable.on('click','.deletepacket',function(e){
					e.preventDefault();
	    			var $this = $(this);
					var $deletemodal = $('#deletemodal');
	    			$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href')).end().modal('show');
				})

				/* Code for view content feed details starts here */

				$datatable.on('click','.viewpacket',function(e){
					e.preventDefault();
					var $this = $(this);
					var $viewpacketdetails = $('#viewpacketdetails');
					simpleloader.fadeIn(200);
	    			$.ajax({
                        type: "GET",
                        url: $(this).attr('href')
                    })
                    .done(function( response ) {
                    	$viewpacketdetails.find('.modal-body').html(response).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
				});

				/* Code for view content feed details ends here */

				/* Code for showing post relations and deleting */
				$datatable.on('click','.postrelations',function(e){
					e.preventDefault();
					var $this = $(this);
					var $postrelations = $('#postrelations');
					simpleloader.fadeIn(200);
	    			$.ajax({
                        type: "GET",
                        url: $(this).attr('href')
                    })
                    .done(function( response ) {
                    	$postrelations.find('.modal-body').html(response.rel_detail).end().modal('show');
                    	$postrelations.find('.modal-footer .btn-danger').prop('href',response.del_url).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
				})
			});




	    </script>
	</div>
@stop