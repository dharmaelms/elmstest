@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success" id="alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Error!</strong> -->
			{{ Session::get('error') }}
		</div>
	@endif
	<div class="alert alert-warning alert-dismissible copy-alert" role="alert" style="display:none">
	  {{ trans('admin/assessment.copy_question_select')}}
	</div>
	<div class="alert alert-success alert-dismissible copy-success" role="alert" style="display:none">
		{{ trans('admin/assessment.copy_question_success') }}
	</div>
	<div class="alert alert-danger alert-dismissible copy-failure" role="alert" style="display:none">
	{{ trans('admin/assessment.copy_question_failure') }}
	</div>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i>List questions</h3> -->
	            </div>
	            <div class="box-content">
	            	<div class="btn-toolbar clearfix">
                        <div class="btn-group pull-right">
                        	@if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION))
                        	<!--
                            <a class="btn btn-circle show-tooltip" title="{{ trans('admin/assessment.add_new_question') }}" href="#" data-target="#questionType-modal" data-toggle="modal"><i class="fa fa-plus"></i></a>
                        	-->
                        	<?php $return = urlencode("cp/assessment/questionbank-questions/{$qbid}"); ?>
                        	<div class="btn-group" style="margin-right:5px;">
								<button style="margin-right:5px;"  type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
									{{ trans("admin/assessment.add_new_question") }}
									<span class="caret"></span>
								</button>
								{{-- <a class="btn btn-sm btn-primary" id="copy-question">{{ trans('admin/assessment.copy_question') }}</a> --}}
								<ul class="dropdown-menu dropdown-primary">
								<?php $questionTypes = trans('admin/assessment.question_types'); ?>
								@foreach($questionTypes as $type => $label)
									<li>
										<a href="{{  $type === "mcq"? URL::to("cp/assessment/add-question/{$type}?qb={$qbid}&return={$return}") : URL::to("cp/question/add?qb={$qbid}&return={$return}") }}" style="{{ ($type === "mcq")? "display : block": "display : none" }}">{{ $label }}</a>
									</li>
								@endforeach
								</ul>
							</div>
                        	@endif
                        </div>
                        <div class="col-md-6">
	                        <form class="form-horizontal" action="">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/assessment.showing') }} :</b></label>
	                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
	                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                    <option value="ALL" <?php if ($filter == "ALL") echo 'selected';?>>
	                                    {{ trans('admin/assessment.question_filter.all') }}
	                                    </option>
	                                    <option value="ORIGINAL" <?php if ($filter == "ORIGINAL") echo 'selected';?>>
	                                    {{ trans('admin/assessment.question_filter.original') }}
	                                    </option>
	                                    <option value="COPIED" <?php if ($filter == "COPIED") echo 'selected';?>>
	                                    {{ trans('admin/assessment.question_filter.copied') }}
	                                    </option>
	                                </select>
	                              </div>
	                           </div>
	                        </form>
	                    </div>
                    </div>
                    <br/><br/>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall"/></th>
				                <th>{{ trans('admin/assessment.question_name') }}</th>
				                <th>{{ trans('admin/assessment.question_text') }}</th>
				                <th>Type</th>
				                <th>{{ trans('admin/assessment.default_mark') }}</th>
				                <th>{{ trans('admin/assessment.difficulty_level') }}</th>
				                <th>{{ trans('admin/assessment.created_on') }}</th>
				                <th>{{ trans('admin/assessment.actions') }}</th>
				            </tr>
				        </thead>
				    </table>
                </div>
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
									<h3 class="modal-header-title">
										<i class="icon-file"></i>
										{{ trans('admin/assessment.question_delete') }}
									</h3>                 
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-body" style="padding-left: 20px;">
					{{ trans('admin/assessment.question_delete_confirmation') }}
				</div>
				<div class="modal-footer">
					<a class="btn btn-danger">{{ trans('admin/assessment.yes') }}</a>
					<a class="btn btn-success" data-dismiss="modal">{{ trans('admin/assessment.close') }}</a>
				</div>
			</div>
		</div>
	</div>
	<script>
		var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";
        var  order_by_var= "{{Input::get('order_by','1 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];
        var selectedQuestions = [];

    	function updateCheckBoxVals() {
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
    	/* Simple Loader */
  		(function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
    	simpleloader.init();
    	$(document).ready(function() {
    		$.ajaxSetup({ cache: false });
    		$('#alert-success').delay(5000).fadeOut();
    		/* code for DataTable begins here */
    		var $datatable = $('#datatable');
    		window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag) {
    			if(flag == true)
    				simpleloader.fadeIn();
    			else
    				simpleloader.fadeOut();
    		}).on('draw.dt',function(event,settings,flag) {
    			$('.show-tooltip').tooltip({container: 'body', delay: {show:500}});
    		}).dataTable({
    			"autoWidth": false,
				"serverSide": true,
				"ajax": {
		            "url": "{{URL::to('/cp/assessment/questionbank-questions-ajax/'.$qbid)}}",
		            "data": function ( d ) {
		                d.filter = $('[name="filter"]').val();
		            },
		            "cache": false,
		        },
	            "aaSorting": [[Number(order), _by  ]],
	            "columnDefs": [ { "targets": [7,0], "orderable": false }],
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

			/* Code to get the selected checkboxes in datatable starts here*/
			if(typeof window.checkedBoxes == 'undefined')
				window.checkedBoxes = {};
			$datatable.on('change','td input[type="checkbox"]',function(){
				var $this = $(this);
				if($this.prop('checked')) {
					checkedBoxes[$this.val()] = $($this).parent().next().text();
					selectedQuestions[selectedQuestions.length] = $this.val();
				}
				else {
					delete checkedBoxes[$this.val()];
					var removeItem = $this.val();
					selectedQuestions = jQuery.grep(selectedQuestions, function(value) {
					  return value != removeItem;
					});
				}
			});

			$('#checkall').change(function(e){
				$('#datatable td input[type="checkbox"]').prop('checked',$(this).prop('checked'));
				$('#datatable td input[type="checkbox"]').trigger('change');
				e.stopImmediatePropagation();
			});
			/* Code to get the selected checkboxes in datatable ends here*/

			/* Code for deleting question starts here*/
			$(document).on('click','.deletequestion',function(e){
				e.preventDefault();
				var $this = $(this);
				var $deletemodal = $('#deletemodal');
				$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
				$deletemodal.modal('show');
			});
			/* Code for deleting question ends here*/
	    	$(document).on('click', '.copy-single', function(){
	    		selectedQuestions.length = 0;
	    		selectedQuestions[selectedQuestions.length] = $(this).data('question-id');
	    		copy();	    		
	    	});
	    	var returnUrl = "{{ URL::to('cp/assessment/edit-question/{question_id}')}}?return=/cp/assessment/questionbank-questions/{{ $qbid }}&qbid={{ $qbid }}";
	    	var copy = function() {
	    		simpleloader.fadeIn();
				var process = $.ajax({
					url: "{{ URL::to('cp/assessment/copy-questions') }}",
					method: 'POST',
					data: {'questions': selectedQuestions}
				});
				process.done(function(response){
					if(response.status) {
						showHideAlert('.copy-success',1000, 1000)
						console.log(returnUrl.replace(/{question_id}/g, response.question_id));
						window.open(returnUrl.replace(/{question_id}/g, response.question_id), '_blank');
					} else if(!response.status) {
						showHideAlert('.copy-failure', 1000, 1000)
					} else {
						console.log(response);
					}
				});
				simpleloader.fadeOut();
				selectedQuestions.length = 0;
				checkedBoxes = {};
				$datatable.fnDraw();
				console.log('refreshed');				
	    	}
	    	showHideAlert = function(element, showDuration, hideDuration){
	    		$(element).slideDown(showDuration).fadeTo(hideDuration, 500).slideUp(hideDuration, function(){
				    $(element).hide();
				});	
	    	}
    	});
    </script>

@stop