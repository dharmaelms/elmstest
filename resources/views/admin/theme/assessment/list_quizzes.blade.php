@section('content')
	@if ( Session::get('success'))
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert" id="alert-success">×</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
		<!-- 	<strong>Error!</strong> -->
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
        var $targetarr =  [0, 2, 3, 5, 6, 7, 8];
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
	            		<div class="col-md-6">
	                        <form class="form-horizontal" action="">
	                            <div class="form-group">
	                              <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>{{ trans('admin/assessment.showing') }} :</b></label>
	                              <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
	                                <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">
	                                    <option value="ALL" <?php if ($filter == "ALL") echo 'selected';?>>{{ trans('admin/assessment.quiz_filter.all') }}</option>
	                                    <option value="GENERAL" <?php if ($filter == "GENERAL") echo 'selected';?>>{{ trans('admin/assessment.quiz_filter.general') }}</option>
	                                    <option value="PRACTICE" <?php if ($filter == "PRACTICE") echo 'selected';?>>{{ trans('admin/assessment.quiz_filter.question_generator') }}</option>
	                                    <option value="BETA" <?php if ($filter == "BETA") echo 'selected';?>>{{ trans('admin/assessment.quiz_filter.beta') }}</option>
	                                    <option value="SECTION" <?php if ($filter == "SECTION") echo 'selected';?>>{{ trans('admin/assessment.quiz_filter.section') }}</option>
	                                    <option value="TIMED_SECTION" <?php if ($filter == "TIMED_SECTION") echo 'selected';?>>{{ trans('admin/assessment.quiz_filter.timed_sections') }}</option>
	                                </select>
	                              </div>
	                           </div>
	                        </form>
	                    </div>
                        <div class="pull-right">
	                        <div class="btn-group">
	                        	@if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUIZ))
	                            	<a class="btn btn-primary btn-sm" href="{{ URL::to('cp/assessment/add-quiz') }}">
	                           			<span class="btn btn-circle blue show-tooltip custom-btm">
	                            			<i class="fa fa-plus"></i>
	                            		</span>&nbsp;{{ trans('admin/assessment.add_quiz') }}
	                            	</a>&nbsp;
                				@endif
	                        </div>
                        </div>
                    </div>
                    <br/>
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall"/></th>
				                <th>{{ trans('admin/assessment.quiz') }}</th>
				                <th>{{ trans('admin/assessment.questions') }}</th>
				                <th>{{ trans('admin/assessment.sections') }}</th>
				                <th>{{ trans('admin/assessment.created_on') }}</th>
				                <th>{{trans('admin/assessment.channel')}}</th>
				                <th>{{ trans('admin/assessment.users') }}</th>
				                <th>{{ trans('admin/assessment.user_groups') }}</th>
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
										{{ trans('admin/assessment.quiz_delete') }}
									</h3>                 
								</div>
							</div>
						</div>
					</div>
				</div>
				<div class="modal-body" style="padding: 20px">
					{{ trans('admin/assessment.quiz_delete_confirmation') }}
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
                                    <h3 class="modal-header-title">
                                        <i class="icon-file"></i>
                                            {{ trans('admin/assessment.view_details') }}
                                    </h3>                                                
                                </div>
                            </div>
                            <div class="feed-list" style="display:none;">
                              	<label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b><?php echo trans('admin/program.programs')?> :</b></label>
                              	<div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
	                                <select name="feed" class="chosen">
	                                    @foreach($feeds as $feed)
		                         		<option value="{{ $feed->program_slug }}" data-type="{{ $feed->program_type }}" data-id="{{ $feed->program_id }}">{{ $feed->program_title }}</option>
		                         		@endforeach
	                                </select>
                                </div>
                           </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer" style="padding-right: 38px">
                	<!-- <div style="float: left;" id="selectedcount"> 0 Entrie(s) selected</div> -->
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
        var  order_by_var= "{{Input::get('order_by','4 desc')}}";
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
		            "url": "{{ URL::to('/cp/assessment/list-quiz-ajax/') }}",
		            "data": function ( d ) {
		                d.filter = $('[name="filter"]').val();
		            }
		        },
	            "aaSorting": [[Number(order), _by ]],
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

			/* Code for deleting quiz starts here*/
			$(document).on('click','.deletequiz',function(e){
				e.preventDefault();
				var $this = $(this);
				var $deletemodal = $('#deletemodal');
				$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
				$deletemodal.modal('show');
			});
			/* Code for deleting quiz ends here*/

			/* code to create concept mapping  starts here*/
			$(document).on('click', '.concept-mapping', function(){
				simpleloader.fadeIn();
				var mapping = $.ajax(
								{
									method:'get',
									url:"{{ URL::to('/cp/assessmentmapping/update-quiz-mapping') }}/"+$(this).data('id'),
								}
							);
				mapping.done(function(result){
					$('.page-title').append('<div class="alert alert-success">'+result.message+'</div>');
					$('body').animate({scrollTop:0},1000);
					simpleloader.fadeOut();
					fadeAlert();
				});
				mapping.fail(function(response){
					$('.page-title').append('<div class="alert alert-danger"> Error in concept mapping</div>');
					simpleloader.fadeOut();
					$('body').animate({scrollTop:0},1000);
					fadeAlert();
				});												
				return false;
			});
			/* code to create concept mapping ends here*/

			/* Code for user quiz rel ends here */
			var quiz_id = null;
            $("select[name='feed']").change(function() {
                $('iframe').attr(
                    'src', "{{ URL::to('cp/contentfeedmanagement/packets') }}/"+$(this).find("option[value='"+$(this).val()+"']").data("type")+"/"+this.value+
					"?view=iframe&from=quiz&relid="+quiz_id
                );
            });

			/* Code for user quiz rel starts here */
    		$datatable.on('click','.quizrel',function(e){
    			e.preventDefault();
    			simpleloader.fadeIn();
    			var $this = $(this);
    			quiz_id = $(this).data("key");
    			var $triggermodal = $('#triggermodal');
    			var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
    			
    			$iframeobj.unbind('load').load(function(){
    			//css code for the alignment	    			
	    			var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
	    			if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){	    					
	    					// $triggermodal.find('.modal-body').css({"top":"-27px"});
	    			}	    				
	    			else
	    				$triggermodal.find('.modal-assign').css({"top": "8px"});

	    			if($triggermodal.find('.feed-list').is(':visible')){
	    				$triggermodal.find('.feed-list').css({"padding-top":"12px","padding-bottom":"45px"});	    					
	    				// $triggermodal.find('.modal-body').css({"top":"-30px"});
	    				$triggermodal.find('.modal-assign').css({"top": "-45px"});
	    			}
	    			else{
	    				if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible'))
	    				$triggermodal.find('.modal-assign').css({"top": "30px"});
	    			}
	    			//code ends here
	    			
					// $('#selectedcount').text('0 Entrie(s) selected');

    				if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
    					$triggermodal.modal('show');
    				simpleloader.fadeOut();

    				/* Code to Set Default checkedboxes starts here*/
    				if($this.data('info') == 'feed') {
    					$iframeobj.get(0).contentWindow.checkedBoxes = {};
    					var feed_id = parseInt($("select[name='feed']").find(':selected').data('id'));
    					var json = $this.data('json')[feed_id];
    					if(json === undefined) json = [];
	    				$.each(json, function(index,value){
	    					$iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
	    				})
	    			} else {
	    				$.each($this.data('json'),function(index,value){
	    					$iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
	    				})
	    			}
    				/* Code to Set Default checkedboxes ends here*/

					/* Code to refresh selected count starts here*/
					// $iframeobj.contents().click(function(){
					// 	setTimeout(function(){
					// 		var count = 0;
					// 		$.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
					// 			count++;
					// 		});
					// 		$('#selectedcount').text(count+ ' Entrie(s) selected');
					// 	},10);
					// });
					// $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
					/* Code to refresh selected count ends here*/
    			})
    			$triggermodal.find('.modal-body').html($iframeobj);
    			$triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

            //code for top assign button click starts here
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
            //code for top assign button click ends here

    			if($this.data('info') == 'feed') {
    				$(".feed-list").show();
    				$("select[name='feed']").trigger('change');
    			} else {
    				$(".feed-list").hide();
    			}
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
    					var feed = '';
    					if(action == 'feed')
    						var feed = $("select[name='feed']").val();

    					simpleloader.fadeIn();
    					$.ajax({
							type: "POST",
							url: '{{ URL::to("/cp/assessment/assign-quiz/") }}/'+action+'/'+$this.data('key'),
							data: 'ids='+$postdata+'&empty=true&feed='+feed
						})
						.done(function( response ) {
							if (response.flag == "success") {
								$('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
							}
							else {
								$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
							}
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

    		function fadeAlert() {
		        window.setTimeout(function() {
		            $(".alert").fadeTo(1500, 0).slideUp(500, function() {
		                $(this).remove();
		            });
		        }, 5000);
		    }
		    fadeAlert();
    	})
    </script>
@stop