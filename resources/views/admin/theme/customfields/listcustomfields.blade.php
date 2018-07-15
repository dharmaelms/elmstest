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

	<style type="text/css">
		.custom-fields-ul li.active .btn-default { background-color: #ffffff; color: #333333; }
		.custom-fields-ul li { border:1px solid #dddddd; }
	</style>
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
                    <div class="row">
		            	<div class="col-md-12">
		            		<ul class="nav nav-tabs custom-fields-ul">
		            			<?php
                                if (Session::get('filter')) {
                                    $filter = Session::get('filter');
                                } else {
                                    $filter = 'userfields';
                                }
                                ?>
					            <li class="@if($filter == 'userfields') active @endif tabfields"><button id="userfields" class="btn btn-default" onclick="switchTab(this);">{{trans('admin/customfields.userfields')}}</button> </li>
					            <li class="@if($filter == 'channelfields') active @endif tabfields"><button id="channelfields" class="btn btn-default" onclick="switchTab(this);">{{trans('admin/customfields.channelfields')}}</button> </li>
					            @if (config('app.ecommerce'))
					            	<li class="@if($filter == 'packagefields') active @endif tabfields"><button id="packagefields" class="btn btn-default" onclick="switchTab(this);">{{trans('admin/customfields.packagefields')}}</button> </li>
					            @endif
					            <li class="@if($filter == 'coursefields') active @endif tabfields"><button id="coursefields"  class="btn btn-default" onclick="switchTab(this);">{{trans('admin/customfields.coursefields')}}</button> </li>
					        </ul><br>
		            		
		            		<div class="btn-toolbar pull-right clearfix">
			            		<a class="btn btn-primary btn-sm addfield" href="{{ URL::to('cp/customfields/add-field') }}">
		                          <span class="btn btn-circle blue show-tooltip custom-btm">
		                            <i class="fa fa-plus"></i>
		                          </span>&nbsp;{{trans('admin/customfields.newfield')}}
		                        </a>&nbsp;&nbsp;<br><br>
			            	</div>
		            	</div>
	            	</div>

                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th>{{ trans('admin/customfields.fieldname')}}</th>
				                <th>{{ trans('admin/customfields.mandatory')}}</th>
				                <th>{{ trans('admin/customfields.created_on')}}</th>
				                <th>{{ trans('admin/customfields.status')}}</th>
				                <th>{{ trans('admin/customfields.actions')}}</th>
				            </tr>
				        </thead>
				    </table>
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
		                                <h3><i class="icon-file"></i>{{ trans('admin/customfields.del_custom_field')}}</h3>                                                 
		                            </div>
		                        </div>
		                    </div>
		                </div>
		            </div>
		            <!--content-->
		            <div class="modal-body" style="padding: 20px">               
		                Are you sure you want to delete this custom field?
		            </div>
		            <!--footer-->
		            <div class="modal-footer">
		              <a class="btn btn-danger"> {{ trans('admin/customfields.yes')}}</a>
		              <a class="btn btn-success" data-dismiss="modal"> {{ trans('admin/customfields.close')}}</a>
		            </div>
		        </div>
		    </div>
		</div>
		<!-- delete window ends -->

		<script>
	    
	    	var  start_page  = {{Input::get('start',0)}};
        	var  length_page = {{Input::get('limit',10)}};
        	var  search_var  = "{{Input::get('search','')}}";

        	var filter = "<?php echo $filter; ?>";

        	function switchTab(thisObj)
        	{
        		var id = thisObj.id;

        		filter = id ;

        		$(".tabfields").removeClass("active");
        		$("#"+filter).parent().addClass("active");

        		datatableOBJ.fnDraw();
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
	    			$('#datatable tr td:nth-child(1) div').each(function(){
	    				if($(this).height() > 75){
	    					$(this).readmore({maxHeight: 45,moreLink: '<a href="#" style="padding-left:8px">Read more</a>',lessLink: '<a href="#" style="padding-left:8px">Close</a>'});
	    				}
	    			})
	    		}).dataTable({
	    			"serverSide": true,
					"ajax": {
			            "url": "{{URL::to('/cp/customfields/customfields-ajax/')}}",
			            "data": function ( d ) {
			                d.filter = filter;
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
                    "oSearch": {"sSearch": search_var},
                    "bLengthChange": false,
		        });
				/* Code for dataTable ends here */

				//individual user delete
			    $(document).on('click','.deletefield',function(e){
			      e.preventDefault();
			      var $this = $(this);
			      var $deletemodal = $('#deletemodal');
			        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
			      $deletemodal.modal('show');
			    });

			    $(document).on('click','.addfield',function(e){
			      e.preventDefault();
			      var $this = $(this);
			      window.location.href = $this.prop('href') + "?filter=" + filter;
			    });

			    /*Code to hide success message after 5seconds*/
			    $('#alert-success').delay(5000).fadeOut();
			});
	    </script>
	</div>
@stop