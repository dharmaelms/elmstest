@section('content')@section('content')
@if ( Session::get('success') )
	<div class="alert alert-success">
	<button class="close" data-dismiss="alert">×</button>

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
	<style>
		#main-content{
			background: white !important;
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
	            	
                    <div class="clearfix"></div>
                   
                        <table class="table table-advance" id="datatable">
                            <thead>
                                <tr>
                                    <th style="width:18px"><input type="checkbox" id="checkall"/></th>
                                    <th>{{ trans('admin/category.category_name') }}</th>
                                    <th>{{ trans('admin/category.created_on') }}</th>
                                    <th>{{ trans('admin/category.status') }}</th>
                                </tr>
                            </thead>
                        </table> 
                  
                </div>
	        </div>
	    </div>

	    <script>
	    var flag_ck = 0;
	   		/*function updateCheckBoxVals(){
				if(typeof window.checkedBoxes != 'undefined'){
					$('#datatable td input[type="checkbox"]').each(function(index,value){
						var $value = $(value);
						if(typeof checkedBoxes[$value.val()] != "undefined")
							$('[value="'+$value.val()+'"]').prop('checked',true);
					})
				}
				updateHeight();
			}*/
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
                        "url": "{{URL::to('/cp/categorymanagement/category-list-ajax')}}",
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
				$('#datatable').on('change','td input[type="checkbox"]',function(e){
					var $this = $(this);
					if($this.prop('checked'))
					{
						checkedBoxes[$this.val()] = $($this).parent().next().text();

						/*  Use if condition to check parent category, when any one of its sub category is unchecked
						tick parent category on click of sub category
						*/
						if('sub_category' == e.currentTarget.name)
						{
							//parentCategoryId = e.currentTarget.id
						var parentCategoryId = e.currentTarget.getAttribute('data-parentid');
							if(parentCategoryId!='undefined' && parentCategoryId!='')
							{
								if($("#"+parentCategoryId).is(':not(:checked)'))
								$("#"+parentCategoryId).prop( "checked", true );
							}
							
						}

					}
					else
					{

					/*  Use if condition to uncheck parent category, when all sub category
					is unchecked
					*/
						/*
						if('sub_category' == e.currentTarget.name)
						{

							var parentCategoryId = '';
							var pid              = '';
							var checkLength      = '';
							var empty            = new Array();
							parentCategoryId = e.currentTarget.getAttribute('data-parentid');
							pid = '"'+parentCategoryId+'"';

							console.log($('[data-parentid='+pid+']') );
							checkLength = document.querySelectorAll('input[data-parentid='+pid+']' );
								
							empty = [].filter.call( checkLength, function( el ) {
							   return !el.checked
							});

							console.log(checkLength.length);
							console.log(empty.length);
							if (checkLength.length == empty.length) {
							    //alert("None checked");
							    $("#"+parentCategoryId).prop( "checked", true );
							}
						} */

						delete checkedBoxes[$this.val()];
					}
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