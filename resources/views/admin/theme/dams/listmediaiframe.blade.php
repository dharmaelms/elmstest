@section('content')
@if ( Session::get('success') )
	<div class="alert alert-success">
	<button class="close" data-dismiss="alert">x</button>
	<!-- <strong>Success!</strong> -->
	{{ Session::get('success') }}
	</div>
	<?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
	<div class="alert alert-danger">
	<button class="close" data-dismiss="alert">x</button>
	<!-- <strong>Error!</strong> -->
	{{ Session::get('error') }}
	</div>
	<?php Session::forget('error'); ?>
@endif
	<style>
		#main-content{
			background: white !important;
			padding-top: 0px;
		}
        <?php
            if((is_array(Input::get('filter')) && count(Input::get('filter')) == 1 && strtolower(Input::get('filter')[0]) == "image") || Input::get('filter') == "image") {
        ?>
            .chosen-drop{
                display:none;
            }
            .chosen-container-single .chosen-single div b {
                display: none !important;
            }
        <?php
            } else if(is_array(Input::get('filter')) && count(Input::get('filter')) == 1 && Input::get('filter')[0] == "video"){
        ?>
            .chosen-drop{
                display:none;
            }
            .chosen-container-single .chosen-single div b {
                display: none !important;
            }
        <?php
            } else if(Input::get('filter') && count(Input::get('filter')) == 1 && Input::get('filter') == "document"){
        ?>
            .chosen-drop{
                display:none;
            }
            .chosen-container-single .chosen-single div b {
                display: none !important;
            }
        <?php
            }
        ?>
	</style>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
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
	            		<div class="col-md-6">
	                        <form class="form-horizontal" action="">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
	                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                              	<?php
	                              		$filter = Input::get('filter', ['all']); 
	                              		$filter = is_array($filter) ? $filter : [$filter];
	                              	?>
	                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                	@if(in_array('all', $filter) || in_array('all', $filter))
		                                    <option value="ALL" <?php if (in_array('all', $filter)) echo 'selected';?>>{{ trans('admin/dams.all')}}</option>
		                                @endif
	                                	@if(in_array('all', $filter) || in_array('image', $filter))
		                                    <option value="IMAGE" <?php if (in_array('image', $filter)) echo 'selected';?>>{{ trans('admin/dams.image')}}</option>
		                                @endif
	                                	@if(in_array('all', $filter) || in_array('document', $filter))
		                                    <option value="DOCUMENT" <?php if (in_array('document', $filter)) echo 'selected';?>>{{ trans('admin/dams.document')}}</option>
		                                @endif
	                                	@if(in_array('all', $filter) || in_array('audio', $filter))
		                                    <option value="AUDIO" <?php if (in_array('audio', $filter)) echo 'selected';?> >{{ trans('admin/dams.audio')}}</option>
		                                @endif
	                                	@if(in_array('all', $filter) || in_array('video', $filter))
		                                    <option value="VIDEO" <?php if (in_array('video', $filter)) echo 'selected';?>>{{ trans('admin/dams.video')}}</option>
		                                @endif
	                                	@if(in_array('all', $filter) || in_array('scorm', $filter))
		                                    <option value="SCORM" <?php if (in_array('scorm', $filter)) echo 'selected';?> >{{ trans('admin/dams.scorm')}}</option>
		                                @endif	
	                                </select>
	                              </div>
	                           </div>
	                        </form>

	                    @if(Request::input("add_media", "true") === "true")
	                    <div class="pull-right" style="margin-bottom: 10px;">
	                    	<div class="btn-group">
	                        	<?php if(isset($permissions) && is_array($permissions) && array_key_exists('add-media', $permissions)) {?>
	                            	<a class="btn btn-primary btn-sm " href="{{URL::to('/cp/dams/add-media?view=iframe')}}{{(Input::get('select') == "radio") ? "&select=radio" : ""}}&{{http_build_query(["filter" => $filter])}}{{((Input::get('id') == "id") ? "&id=id" : "")}}" title="Add media" data-placement="left">
	                            		<span class="" data-original-title="" title="">
	                            			<i class="fa fa-plus"></i>

	                        			</span>&nbsp;Add new media

	                    			</a>&nbsp;&nbsp;
	                            <?php } ?>
                			</div>
                        </div>
                        @endif
                    </div>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px">
				                	<?php if(isset($select) && $select == "radio") ; else{  ?>
				                		<input type="checkbox" id="checkall" /></th>
				                	<?php } ?>
				                <th style="width:30%">{{ trans('admin/dams.media_resource')}}</th>
				                <th>Type</th>
				                <th>{{ trans('admin/dams.created_on')}}</th>
				                <th>{{ trans('admin/dams.added_by')}}</th>
				                <th>{{ trans('admin/dams.preview')}}</th>
				                <th>{{ trans('admin/dams.keywords_tags')}}</th>
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
					$('#datatable td input[type="checkbox"], #datatable td input[type="radio"]').each(function(index,value){
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

				<?php $mediaid = Input::get('mediaid');
					if($mediaid) {?>
						var checkbox = $("#datatable").find("input[value=<?php echo $mediaid?>]");
						if(checkbox.prop('checked') == false)
							checkbox.trigger('click');
					<?php }
				?>
			}

			var filter;
			
<?php
	 		$filter = Input::get("filter");
	 		if(is_array($filter))
	 		{
?>
				filter = new Array();
<?php
				foreach($filter as $filterVal)
				{
?>
					filter.push("<?=$filterVal; ?>");
<?php
				}
	 		}
	 		else
	 		{
?>
				filter = "<?=$filter; ?>";
<?php
	 		}
?>
			
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
			            "url": "{{URL::to('/cp/dams/media-list-ajax')}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
			                d.view = "iframe";
			                <?php if(isset($select) && $select == "radio") echo 'd.select = "radio";' ?>
			                <?php if(isset($idtype) && $idtype == "id") echo 'd.id = "id";' ?>
								d.from = "{{Input::get("from", null)}}";
							@if($from === "program" || $from === "post" || $from = "add-post")
								d.program_type = "{{Input::get("program_type", null)}}";
								d.program_slug = "{{Input::get("program_slug", null)}}";
								@if($from === "post")
                                    d.post_slug = "{{Input::get("post_slug", null)}}";
								@endif
							@endif
			            }
			        },
		            "aaSorting": [[ 3, 'desc' ]],
		            "columnDefs": [ { "targets": [0,4,5,6], "orderable": false } ],
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
				if(typeof window.media_types == 'undefined')
					window.media_types = {};

				$datatable.on('change','td input[type="checkbox"]',function(){
					var $this = $(this);
					if($this.prop('checked')){
						checkedBoxes[$this.val()] = $($this).parent().next().text();
						media_types[$this.val()] = $($this).parent().next().next().find('.show-tooltip').data('originalTitle');
					}
					else{
						delete checkedBoxes[$this.val()];
						delete media_types[$this.val()];
					}
					// updateCheckBoxVals();
					if(flag_ck == 0){
                    	updateCheckBoxVals();
                	}
				});

				$('#checkall').change(function(e){
					$('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
					flag_ck = 1;
					$('#datatable td input[type="checkbox"]').trigger('change');
					flag_ck = 0
					e.stopImmediatePropagation();
				});
				/* Code to get the selected checkboxes in datatable ends here*/
	    	});
	    </script>
	</div>
@stop