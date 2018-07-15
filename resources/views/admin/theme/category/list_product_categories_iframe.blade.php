@section('content')@section('content')
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
			background: white !important;
		}
	</style>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-content">
	            	<div class="btn-toolbar clearfix">
	            		<div class="col-md-6" style="display:none;">
	                        <form class="form-horizontal" action="">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
	                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
	                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                    <option value="ACTIVE" <?php if (Input::get('filter')== 'ACTIVE') echo 'selected';?>>trans('admin/category.active')}}</option>
                                        <option value="IN-ACTIVE" <?php if (Input::get('filter')== 'IN-ACTIVE') echo 'selected';?>>trans('admin/category.in_active')}}</option>
                                        <option value="EMPTY" <?php if (Input::get('filter')== 'EMPTY') echo 'selected';?>>trans('admin/category.unassigned')}}</option>
	                                </select>
	                              </div>
	                           </div>
	                        </form>
	                    </div>
                    </div>
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-advance" id="datatable">
                            <thead>
                                <tr>
                                    <th style="width:18px"><input type="checkbox" id="checkall" /></th>
                                    <th>{{ trans('admin/category.category_name') }}</th>
                                    <th>{{ trans('admin/category.created_on') }}</th>
                                    <th>{{ trans('admin/category.status') }}</th>
                                </tr>
                            </thead>
                        </table> 
                    </div>	
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
	    	/* Simple Loader */
	  		(function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
	    	simpleloader.init();
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
	    		}).dataTable({
	    			"autoWidth": false,
					"serverSide": true,
		            "iDisplayLength": 10,
					"ajax": {
			            "url": "{{URL::to('/cp/categorymanagement/category-list-ajax')}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			                d.view = "iframe";
			            }
			        },
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
				$datatable.on('change','td input[type="checkbox"]',function(){
					var $this = $(this);
					if($this.prop('checked'))
						checkedBoxes[$this.val()] = $($this).parent().next().text();
					else
						delete checkedBoxes[$this.val()];
					// updateCheckBoxVals();
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