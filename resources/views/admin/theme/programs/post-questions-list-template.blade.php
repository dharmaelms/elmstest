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
	            	<div class="col-md-6">
                        <form class="form-horizontal" action="">
                            <div class="form-group">
                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.showing')}} :</b></label>
                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>{{trans('admin/program.all')}}</option>
                                    <option value="ANSWERED" <?php if ($filter == 'answered') echo 'selected';?>>{{trans('admin/program.answered')}}</option>
                                    <option value="UNANSWERED" <?php if ($filter == 'unanswered') echo 'selected';?>>{{trans('admin/program.unanswered')}}</option>
                                </select>
                              </div>
                           </div>
                        </form>
                    </div>
	            	<div class="btn-toolbar pull-right clearfix">
                            </div>
                    <br/><br/>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <!-- <th style="width:18px"><input type="checkbox" id="checkall" /></th> -->
				                <th>{{trans('admin/program.asked_on')}}</th>
				                <th style="width:200px">Question</th>
				                <th>{{trans('admin/program.packet')}}</th>
				                <th>{{ trans('admin/assessment.channel') }}</th>
				                <th>{{trans('admin/program.asked_by')}}</th>
				                <th>{{trans('admin/program.status')}}</th>
				                <th>{{trans('admin/program.actions')}}</th>
				            </tr>
				        </thead>
				    </table>
                </div>
	        </div>
	    </div>

	    <div class="modal fade" id="actionmodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{trans('admin/program.mark_this_ques_as_faq')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding-left: 20px;">
                        
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger" id="actionbtn">{{trans('admin/program.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
	    <script>
	    
	    var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";
        var  order_by_var= "{{Input::get('order_by','0 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];


                    
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
	    		/* code for DataTable begins here */
	    		var $datatable = $('#datatable');
	    		window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag){
	    			if(flag == true)
	    				simpleloader.fadeIn();
	    			else
	    				simpleloader.fadeOut();
	    		}).on('draw.dt',function(event,settings,flag){
	    			$('.show-tooltip').tooltip({container: 'body'});
                    $('td:nth-child(2) div, td:nth-child(3) div, td:nth-child(4) div', '#datatable tr').each(function(){
                        if($(this).height() > 75){
                            $(this).readmore({maxHeight: 55,moreLink: '<a href="#" style="padding-left:8px">Read more</a>',lessLink: '<a href="#" style="padding-left:8px">Close</a>'});
                        }
                    })
	    		}).dataTable({
	    			"serverSide": true,
					"ajax": {
			            "url": "{{URL::to("/cp/contentfeedmanagement/post-questions-list-data/{$program_type}/{$program_slug}/{$packet_slug}")}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100],
		                [10, 15, 25, 50, 100]
		            ],
		            "iDisplayLength": 10,
		            "aaSorting": [Number(order), _by ],
		            "columnDefs": [ { "targets": [2,3,5,6], "orderable": false } ],
		            "drawCallback" : updateCheckBoxVals,
		            "iDisplayStart": start_page,
                    "pageLength": length_page,
                    "oSearch": {"sSearch": search_var}
		        });

		        $('#datatable_filter input').unbind().bind('keyup', function(e) {
					if(e.keyCode == 13) {
						datatableOBJ.fnFilter(this.value);
					}
				});
				/* Code for dataTable ends here */

				$datatable.on('click','.faq',function(e){
	    			e.preventDefault();
	    			var $modal = $('#actionmodal');
	    			var $this = $(this);
	    			$modal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('original-title'));
	    			$modal.find('.modal-body').html($this.data('text'));
	    			$modal.find('.modal-footer a#actionbtn').attr('href',$this.attr('href'));
	    			$modal.modal('show');
	    		});
			});
	    </script>
	</div>
@stop