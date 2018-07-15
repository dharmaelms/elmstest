@section('content')
	@if ( Session::get('success'))
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
	@if ( Session::get('warning'))
        <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('warning') }}
        </div>
        <?php Session::forget('warning'); ?>
    @endif
	<script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [0,2,4];
    </script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	            </div>
	            <div class="box-content">
	            	<div class="btn-toolbar clearfix">
                        <div class="pull-right">
                  
		              		@if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::IMPORT_QUESTION_BANK))
		                	<div class="btn-group">
		                		<a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/assessment.import_questionbank');?>" href="{{ URL::to('cp/assessment/import-questionbank') }}"><i class="fa fa-sign-in"></i></a>
		                	</div>
		                	@endif
			              
			            	<div class="btn-group">
								<a class="btn btn-circle show-tooltip" title="<?php echo trans('admin/assessment.view_import_history');?>" href="{{ URL::to('cp/assessment/questionbank-import-history') }}"><i class="fa fa-eye"></i></a>
							</div>
			                @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION))
								<div class="btn-group" style="margin-right:5px;">
									<button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
										{{ trans("admin/assessment.add_new_question") }}
										<span class="caret"></span>
									</button>
									<ul class="dropdown-menu dropdown-primary">
									<?php $questionTypes = trans('admin/assessment.question_types'); ?>
									@foreach($questionTypes as $type => $label)
										<li>
											<a href="{{  $type === "mcq"? URL::to("cp/assessment/add-question/{$type}") : URL::to("cp/question/add") }}" style="{{ ($type === "mcq")? "display : block": "display : none" }}">{{ $label }}</a>
										</li>
									@endforeach
									</ul>
								</div>
				            @endif
				            @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION_BANK))
			                    <div class="btn-group">
			                        <a class="btn btn-primary btn-sm" href="{{ URL::to('/cp/assessment/add-questionbank') }}">
			                          <span class="btn btn-circle blue show-tooltip custom-btm">
			                          	<i class="fa fa-plus"></i>
			                          </span>&nbsp;<?php echo trans('admin/assessment.add_question_bank');?>
			                        </a>
			                    </div>
			    			@endif
                    	</div>
                    </div>
                    <br/>
                    <div class="clearfix"></div>
                    <?php 
                    $edit_questionbank = has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::EDIT_QUESTION_BANK);
                    $delete_questionbank =  has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::DELETE_QUESTION_BANK);
                    ?>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall"/></th>
				                <th>{{ trans('admin/assessment.question_bank') }}</th>
				                <th>{{ trans('admin/assessment.questions') }}</th>
				                <th>{{ trans('admin/assessment.created_on') }}</th>
				                <!--<th>{{ trans('admin/assessment.users') }}</th>
				                <th>{{ trans('admin/assessment.user_groups') }}</th>-->
				                <?php
				                if($edit_questionbank == true || $delete_questionbank == true)
				                 { ?>
				                <th>{{ trans('admin/assessment.actions') }}</th>
				                <?php } else { ?> <script>$targetarr.pop()</script>  <?php } ?>
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
									<h3 class="modal-header-title" >
										<i class="icon-file"></i>
										{{ trans('admin/assessment.question_bank_delete') }}
									</h3>                 
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-body" style="padding-left: 20px;">
					{{ trans('admin/assessment.questionbank_delete_confirmation') }}
				</div>
				<div class="modal-footer">
					<a class="btn btn-danger">{{ trans('admin/assessment.yes') }}</a>
					<a class="btn btn-success" data-dismiss="modal">{{ trans('admin/assessment.close') }}</a>
				</div>
			</div>
		</div>
	</div>
	<div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                           {{ trans('admin/assessment.view_ques_bank_detail') }}
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
                	<div style="float: left;" id="selectedcount">0</div>
                     <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/assessment.assign') }}</a>
	                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/assessment.close') }}</a>
                </div>
            </div>
        </div>
    </div>
	<script> 
		var  start_page  = {{Input::get('start',0)}};
        var  length_page = {{Input::get('limit',10)}};
        var  search_var  = "{{Input::get('search','')}}";
        var  order_by_var= "{{Input::get('order_by','3 desc')}}";
        var  order = order_by_var.split(' ')[0];
        var  _by   = order_by_var.split(' ')[1];

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
		            "url": "{{ URL::to('/cp/assessment/list-questionbank-ajax/') }}",
		            "data": function ( d ) {
		                d.filter = $('[name="filter"]').val();
		            }
		        },
	            "aaSorting": [[Number(order), _by ]],
	            //"columnDefs": [ { "targets": [0,2,4,5,6], "orderable": false } ],
	            "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
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
				console.log($this);
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

			/* Code for deleting question starts here*/
			$(document).on('click','.deleteqbank',function(e){
				e.preventDefault();
				var $this = $(this);
				var $deletemodal = $('#deletemodal');
				$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
				$deletemodal.modal('show');
			});
			/* Code for deleting question ends here*/

			/* Code for user dams rel starts here */
    		$datatable.on('click','.qbrel',function(e){
    			e.preventDefault();
    			simpleloader.fadeIn();
    			var $this = $(this);
    			var $triggermodal = $('#triggermodal');
    			var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
    			$iframeobj.unbind('load').load(function(){
					$('#selectedcount').text('0 selected');

    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
    					$triggermodal.modal('show');
    				simpleloader.fadeOut();

    				/* Code to Set Default checkedboxes starts here*/
    				$.each($this.data('json'),function(index,value){
    					$iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
    				})
    				/* Code to Set Default checkedboxes ends here*/

					/* Code to refresh selected count starts here*/
					$iframeobj.contents().click(function(){
						setTimeout(function(){
							var count = 0;
							$.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
								count++;
							});
							$('#selectedcount').text(count+ ' selected');
						},10);
					});
					$iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
					/* Code to refresh selected count ends here*/
    			})
    			$triggermodal.find('.modal-body').html($iframeobj);
    			$triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));
    			$('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
    				var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
    					var $postdata = "";
	    				if(!$.isEmptyObject($checkedboxes)){
	    					$.each($checkedboxes,function(index,value){
	    						if(!$postdata)
	    							$postdata += index;
	    						else
	    							$postdata += "," + index;
	    					});
	    				}
    					// Post to server
    					var action = $this.data('info');

    					simpleloader.fadeIn();
    					$.ajax({
							type: "POST",
							url: '{{ URL::to('/cp/assessment/assign-questionbank/') }}/'+action+'/'+$this.data('key'),
							data: 'ids='+$postdata+"&empty=true"
						})
						.done(function( response ) {
							if(response.flag == "success")
								$('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button> <?php echo trans('admin/assessment.questionbank_successfully_assigned');?></div>').insertAfter($('.page-title'));
							else
								$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
							$triggermodal.modal('hide');
							setTimeout(function(){
								$('.alert').alert('close');
							},5000);
							window.datatableOBJ.fnDraw(true);
							simpleloader.fadeOut(200);
						})
						.fail(function() {
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
							window.datatableOBJ.fnDraw(true);
							simpleloader.fadeOut(200);
						})
    			})
    		});
    		/* Code for user dams rel ends here */
    	})
    </script>
@stop