@section('content')
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
			<!-- <strong>Error!</strong> -->
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<?php
	use App\Model\CustomFields\Entity\CustomFields;
	$pgmCustomField = CustomFields::getUserActiveCustomField($program_type = 'course', $program_sub_type = 'single',$status = 'ACTIVE');
    ?>
	<script>
		/* Function to remove specific value from array */
		if (!Array.prototype.remove) {
			Array.prototype.remove = function(val) {
				var i = this.indexOf(val);
				return i>-1 ? this.splice(i, 1) : [];
			};
		}
		var $targetarr = [0,4,5,6,7,9];
			
	</script>
<script src="{{ URL::asset('admin/assets/jquery/jquery-2.1.1.min.js')}}"></script>
<script src="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.css')}}">
	
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
<script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
<script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
<script src='//cdn.datatables.net/1.10.5/js/jquery.dataTables.min.js'></script>
<!--<script type="text/javascript" src="//code.jquery.com/jquery-1.11.3.min.js"></script>-->
<script type="text/javascript" src="https://cdn.datatables.net/1.10.10/js/jquery.dataTables.min.js"></script>
<link href="https://cdn.datatables.net/1.10.10/css/jquery.dataTables.min.css" rel="stylesheet">

<link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
<script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    
<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
<script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
<style>
table.dataTable thead th, table.dataTable thead td {
border-bottom: none;
padding: 10px 10px;
}
table.dataTable {
border-collapse: separate;
border-spacing: 0;
border:none !important;
}
@media (min-width: 1200px){
   .wdthChange {
			margin-left: 3.667%;
			width: 100%;
    	}
    }
    .font-14{
    		font-size: 14px;
    }
    .form-group .control-label{width: 40%;} 
    .form-group .controls{width: 60%;} 
    .top-field{width: 30%;} 	
@media screen and (-webkit-min-device-pixel-ratio:0) { 
	.form-group .control-label{width: 47%;} 
	.form-group .controls{width: 53%;} 
	.top-field{width: 34.5% !important;} 
}	
</style>
	
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	           <!--<div class="box-title">
	              
	            </div>-->
	            <div class="box-content">
				
				<!--start of add button-->
				<div class="col-md-12 margin-bottom-20">
	            	<div class="btn-toolbar pull-right clearfix">
                        
                        <div class="btn-group">
						<a href="#" class="btn btn-primary btn-sm" id="button" onclick="showhide()" style="margin-right:10px;">
                        		{{trans('admin/program.advance_search')}}
                			</a>
                        	@if(has_admin_permission(ModuleEnum::COURSE, CoursePermission::ADD_COURSE))
                            	<div class="btn-group">
	                            	<a class="btn btn-primary btn-sm" href="{{url::to('/cp/contentfeedmanagement/add-course')}}">
	                            		<span class="btn btn-circle blue show-tooltip custom-btm">
	                            			<i class="fa fa-plus"></i>
	                        			</span>&nbsp;<?php echo trans('admin/program.add_course'); ?>
	                    			</a>&nbsp;&nbsp;
                    			</div>
                            @endif
                            
                        <!--course - User mapping -->
		               	<?php 
		               		$program_type = 'course';
		               		$program_sub_type = 'single'; 
		               	?>
		                
		                @if(has_admin_permission(ModuleEnum::COURSE, CoursePermission::EXPORT_COURSE))
		               		<input type="hidden" id="export_link_user" name="export_link_user" value="{{URL::to('/cp/contentfeedmanagement/channel-user-export/'.$program_type.'/'.$program_sub_type )}}">
		               		<a class="btn btn-circle show-tooltip" id= "export_link" title="<?php echo trans('admin/program.course_export_with_user'); ?>" href="{{URL::to('/cp/contentfeedmanagement/channel-user-export/'.$program_type.'/'.$program_sub_type)}}"><i class="fa fa-user"></i></a>
		               	@endif
						
		               	<!-- 	<input type="hidden" id="export_link_ug" name="export_link_ug" value="{{URL::to('/cp/contentfeedmanagement/channel-usergroup-export/'.$program_type.'/'.$program_sub_type)}}">
		               		<a class="btn btn-circle show-tooltip" id= "export_link_group" title="<?php echo trans('admin/program.course_export_with_usergroups'); ?>" href="{{URL::to('/cp/contentfeedmanagement/channel-usergroup-export/'.$program_type.'/'.$program_sub_type)}}"><i class="fa fa-users"></i></a>
		                -->
		                <a class="btn btn-circle show-tooltip" title="Refresh" href="#" onclick="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);var a = $(this).addClass('anim-turn180');setTimeout(function(){a.removeClass('anim-turn180');},500);return false;"><i class="fa fa-refresh"></i></a>
						</div>
					</div>
                 </div>
                    <!--<br/><br/>-->
                    <!--end of add button-->
                <!--start of filter-->
	            	<!--<div class="col-md-6">-->
                    <form class="form-horizontal" action="" name="filterform">
					<div id="advancesearch" style="display:none;">
	            		<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
                        	<!--start of status filter-->
							<div class="form-group">
                              <label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.status')}} :</b></label>
                              <div class="col-sm-6 col-lg-6 controls" style="padding-left:0;">
                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
                                <select id= "test" class="form-control chosen" name="filter" id="filter" data-placeholder="ALL" >
                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>{{trans('admin/program.all')}}</option>
                                    <option value="ACTIVE" <?php if ($filter == 'active') echo 'selected';?>>{{trans('admin/program.active')}}</option>
                                    <option value="IN-ACTIVE" <?php if ($filter == 'in-active') echo 'selected';?>>{{trans('admin/program.in_active')}}</option>
                                </select>
                              </div>
                           </div>
							<!--end of status filter-->
							<!--start of visibility filter-->
							<div class="form-group">
							    <label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.visibility')}} :</b></label>
							    <div class="col-sm-6 col-lg-6 controls" style="padding-left:0;">
							    <?php $visibility = Input::get('visibility'); $visibility = strtolower($visibility); ?>
							    <select class="form-control chosen" name="visibility" id="visibility" data-placeholder="all">
							    <option value="all" <?php if ($visibility == 'all') echo 'selected';?>>{{trans('admin/program.all')}}</option>
							    <option value="yes" <?php if ($visibility == 'yes') echo 'selected';?>>{{trans('admin/program.yes')}}</option>
							    <option value="no" <?php if ($visibility == 'no') echo 'selected';?>>{{trans('admin/program.no')}}</option>
							    </select>
							    </div>
						    </div>
							<!--end of visibility filter-->
							
							<!--start of sellability filter-->
							<div class="form-group">
							    <label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.sellability')}} :</b></label>
							    <div class="col-sm-6 col-lg-6 controls" style="padding-left:0;">
							    <?php $sellability = Input::get('sellability'); $sellability = strtolower($sellability); ?>
							    <select class="form-control chosen" name="sellability" id="sellability" data-placeholder="all">
							    <option value="all" <?php if ($sellability == 'all') echo 'selected';?>>{{trans('admin/program.all')}}</option>
							    <option value="yes" <?php if ($sellability == 'yes') echo 'selected';?>>{{trans('admin/program.yes')}}</option>
							    <option value="no" <?php if ($sellability == 'no') echo 'selected';?>>{{trans('admin/program.no')}}</option>
							    </select>
							    </div>
						    </div>
							<!--end of sellability filter-->
							
							<!--start of category filter-->
							<div class="form-group">
							<label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.category')}} :</b></label>
							<div class="col-sm-6 col-lg-6 controls"  style="padding-left:0;">
							<input type="text" id="category" class="form-control" value="{{Input::old('category')}}" name="category" />
														
							</div>
							</div>
							<!--end of category filter-->
							<!--start of batch filter-->
							<div class="form-group">
							<label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.batch')}} :</b></label>
							<div class="col-sm-6 col-lg-6 controls"  style="padding-left:0;">
							<input type="text" id="batch_name" class="form-control" value="{{Input::old('batch_name')}}" name="batch_name" />
							</div>
							</div>
							<!--end of batch filter-->
	            		</div>
		            	<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
		            		<!--start of fullname filter-->
								<div class="form-group">
								    <label class="col-sm-4 col-md-4 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.course_name')}} :</b></label>
								    <div class="col-sm-6 col-md-8 col-lg-7 controls" style="padding-left:0;">
									<input type="text" name="feed_title" id="feed_title" class="form-control" value="{{ Input::get('feed_title') }}" placeholder="{{trans('admin/program.course_name')}}">
									</div>
							    </div>
								<!--end of fullname filter-->
		            		<!--start of shortname filter-->
								<div class="form-group">
								    <label class="col-sm-4 col-md-4 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.short_name')}} :</b></label>
								    <div class="col-sm-6 col-md-8 col-lg-7 controls" style="padding-left:0;">
									<input type="text" name="shortname" id="shortname" class="form-control" value="{{ Input::get('shortname') }}">
									</div>
							    </div>
								<!--end of shortname filter-->
								<!--start of description filter-->
								<div class="form-group">
								    <label class="col-sm-4 col-md-4 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.description')}} :</b></label>
								    <div class="col-sm-6 col-md-8 col-lg-7 controls" style="padding-left:0;">
									<input type="text" name="descriptions" id="descriptions" class="form-control" value="{{ Input::get('description') }}">
									</div>
							    </div>
								<!--end of description filter-->
								
								
								
								<!--start of created date filter-->
								<div class="form-group">
								    <label class="col-sm-4 col-md-4 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.created_at')}} :</b></label>
								    <div class="col-sm-6 col-md-8 col-lg-7 controls" style="padding-left:0;">
								    <div class="input-group date" style="padding-right:68px">
								    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								    <input type="text" readonly name="created_date" id="created_date" class="form-control datepicker"  style="cursor: pointer" value="">
								    </div>
									<select class="form-control cs-chosen" name="get_created_date" id="get_created_date">
									<option value="=" >=</option>
									<option value="<" ><</option>
									<option value=">" >></option>
									<option value="<=" ><=</option>
									<option value=">=" >>=</option>
									</select>
								    </div>
							    </div>
								<!--end of created date filter-->
								
								<!--start of updated date filter-->
								<div class="form-group">
								    <label class="col-sm-4 col-md-4 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.updated_at')}} :</b></label>
								    <div class="col-sm-6 col-md-8 col-lg-7 controls" style="padding-left:0;">
								    <div class="input-group date" style="padding-right:68px">
								    <span class="input-group-addon"><i class="fa fa-calendar"></i></span>
								    <input type="text" readonly name="updated_date" id="updated_date" class="form-control datepicker"  style="cursor: pointer" value="">
								    </div>
									<select class="form-control cs-chosen" name="get_updated_date" id="get_updated_date">
									<option value="=" >=</option>
									<option value="<" ><</option>
									<option value=">" >></option>
									<option value="<=" ><=</option>
									<option value=">=" >>=</option>
									</select>
								    </div>
							    </div>
								<!--end of updated date filter-->
								<!--start of key tags filter-->
								 <div class="form-group">
								<label class="col-sm-4 col-md-4 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.keywords')}} :</b></label>
								<div class="col-sm-6 col-md-8 col-lg-7 controls"  style="padding-left:0;">
									<input type="text" class="form-control tags medium" value="{{Input::get('feed_tags')}}" name="feed_tags" />
								
								</div>
							</div>
								<!--end of key tags filter-->
								
								<!--start of search button-->
								<div class="form-group last">
									<div class="col-sm-9 col-sm-offset-4 col-lg-7 col-lg-offset-5 wdthChange">
									<button class="btn btn-success" type="button" onclick="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">Search</button>
									<a href="{{URL::to('/cp/contentfeedmanagement/list-courses')}}"><button type="button" class="btn">{{trans('admin/program.cancel')}}</button></a>
									<input type="hidden" id="export_hidden_channel_link" name="export_hidden_channel_link" value="{{URL::to('/cp/contentfeedmanagement/channel-export/'.$program_type.'/'.$program_sub_type )}}">
									<a class="btn btn-primary btn-sm font-14" id="export_channel_link" href="{{URL::to('cp/contentfeedmanagement/channel-export/'.$program_type.'/'.$program_sub_type)}}" title="<?php echo trans('admin/program.course_export_report') ?>">
									<i class="fa fa-download">&nbsp;</i><?php echo trans('admin/user.export_course') ?></a>
									</div>
								</div>
								<!--end of search button-->
		            	</div>
		            	<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
		            		<!--custom fields starts here-->
							@if($pgmCustomField)
								
									<!--select box starts here-->
									<div class="form-group">
								    <label class="col-sm-4 col-lg-3 control-label top-field" style="padding-right:0;text-align:left"><b>Select Field :</b></label>
								    <div class="col-sm-6 col-lg-6 controls" style="padding-left: 6px; padding-right: 0px;  margin-bottom: 16px;">
								    <select class="form-control chosen" name="customfieldlist" id="customfieldlist" data-placeholder="all">
								    @foreach($pgmCustomField as $key => $pgm_field)
									<option value="{{$pgm_field['fieldname']}}-{{$pgm_field['fieldlabel']}}" name="customfield">{{$pgm_field['fieldlabel']}}</option>
								    @endforeach
								    </select>
								    </div>
								    <div class="input_fields_wrap">
			                            <button  class="add_field_button btn-success" style="margin-left:15px"><i class="fa fa-plus"></i></button><div></div>
			                         </div>
							        </div>
									<!--select box ends here-->
								@endif	
							<!--custom fields ends here-->
		            	</div>
					</div>
	                </form>
                    <!--</div>-->
                    <!-- end of filter-->
                     
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable" class="display">
				        <thead>
				            <tr>
							    <th></th>
				               <!--<th style="width:18px"><input type="checkbox" id="checkall" /></th>-->
				                <th style="width:20% !important">{{trans("admin/program.course")}}</th>
								<th>{{trans('admin/program.short_name')}}</th>
				                <th>{{trans('admin/program.start_date')}}</th>
				                <th>{{trans('admin/program.end_date')}}</th>
			                	<th>{{trans('admin/category.category')}}</th>
			                	<th>{{trans('admin/program.packets')}}</th>
			                	<th>{{trans('admin/program.batch')}}es</th>
			                	<th>{{trans('admin/program.status')}}</th>
				                <th style="min-width:113px">{{trans('admin/program.actions')}}</th>
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
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                                {{trans('admin/program.course')}} Delete
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        Are you sure you want to delete {{strtolower(trans('admin/program.course'))}} ?
                    </div>
                    <div class="modal-footer">
                        <a class="btn btn-danger">{{trans('admin/program.yes')}}</a>
                        <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal fade" id="viewfeeddetails" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                View {{trans('admin/program.course')}} details
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
	                                            {{trans('admin/program.view_program_details')}}
	                                    </h3>                                                
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
	                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/program.assign')}}</a>
	                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/program.close')}}</a>
	                </div>
	            </div>
	        </div>
	    </div>
	    
	<!--</div>-->
	<script>
			
			var  start_page  = {{Input::get('start',0)}};
			var  length_page = {{Input::get('limit',10)}};
			var  search_var  = "{{Input::get('search','')}}";
			var  order_by_var= "{{Input::get('order_by','2 desc')}}";
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
			(function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
            simpleloader.init();
	    	$(document).ready(function(){
			//start of dynamic fields
    var max_fields      = <?php echo count($pgmCustomField) ;?>; //maximum input boxes allowed
    var wrapper         = $(".input_fields_wrap"); //Fields wrapper
    var add_button      = $(".add_field_button"); //Add button ID
   
    var x = 1; //initlal text box count
    $(add_button).click(function(e){ //on add input button click
        e.preventDefault();
		var c_select = document.getElementById("customfieldlist"); //dynamic
		var field_name = document.getElementById("customfieldlist").value;
		var i = field_name.indexOf('-');
		var l = field_name.length;
		var fname = field_name.substr(0,i);
		var lname = field_name.substr(i+1,l);
		
		if (e.timeStamp==0) {
			return false;
		}
		if (document.filterform[fname]) {
			alert(lname+' field already exist');
			return false;
		}
        
        if(x <= max_fields){ //max input box allowed
		
		    x++; //text box increment
			//c_select.remove(c_select.field_name); //dynamic
			$(wrapper).append('<div class="col-md-12"><input type="hidden" name="add_fied_name" value='+fname+'><input type="hidden" name="add_label_name" value='+lname+'><div class="form-group col-md-10 col-lg-10" style="padding: 0px;"><label class="col-sm-4 col-lg-4 control-label" style="text-align:left"><b>'+lname+' :</b></label><div class="col-sm-4 col-lg-8 controls" style="padding-left:0;"><input type="text" name='+fname+' class="form-control"/></div></div><a href="#" class="remove_field col-md-2 col-lg-2" style="color: red; font-size: 16px; text-align: left;"><i class="glyphicon glyphicon-trash"></i></a></div>'); //add input box
        }
    });
   
    $(wrapper).on("click",".remove_field", function(e){ //user click on remove text
	    e.preventDefault(); $(this).parent('div').remove(); x--;
		//var add_fied_name = $(this).parent("div").find("input[name='add_fied_name']").val(); //dynamic
		//var add_label_name = $(this).parent("div").find("input[name='add_label_name']").val(); //dynamic
		//var c_select = document.getElementById("customfieldlist"); //dynamic
		//var option = document.createElement("option"); //dynamic
        //option.text = add_label_name; //dynamic
        //option.value = add_fied_name.concat('-').concat(add_label_name); //dynamic
        //c_select.add(option); //dynamic
		
	})
    //end  of dynamic fields
	//start
   /*$('[name="sellability"]').on("change",function(){
   
					if($(this).val()== "yes"){
						$('#access_filter').prop('disabled',false);
						$('#access_filter').show();
					}
					else {
						$('#access_filter').prop('disabled', 'disabled');
						$('#access_filter').hide();
					}
				});*/
   //end
			$('.datepicker').datepicker({
                format : "dd-mm-yyyy",
                //startDate: '+0d'
            }).on('changeDate',function(){
                    $(this).datepicker('hide')
                });
				$('input.tags').tagsInput({
		            width: "auto"
		        });
        		
	    		var $datatable = $('#datatable');
	    		
					var table = $('#datatable').DataTable( {
					"language": {
                    "emptyTable": "<?php echo trans('admin/program.no_courses');?>"
                     },
					"serverSide": true,
					"ajax": {
					
			            "url": "{{URL::to('/cp/contentfeedmanagement/course-list-ajax')}}",
			            "data": function ( d ) {
			                d.filter = $('[name="filter"]').val();
							d.visibility = $('[name="visibility"]').val();
                            d.sellability = $('[name="sellability"]').val();
                            d.feed_title = $('[name="feed_title"]').val();
                            d.shortname = $('[name="shortname"]').val();
							d.created_date = $('[name="created_date"]').val();
							d.updated_date = $('[name="updated_date"]').val();
							d.descriptions= $('[name="descriptions"]').val();
							d.feed_tags= $('[name="feed_tags"]').val();
							d.batch_name= $('[name="batch_name"]').val();
							d.category= $('[name="category"]').val();
							d.get_created_date= $('[name="get_created_date"]').val();
							d.get_updated_date= $('[name="get_updated_date"]').val();
							<?php
							if(!empty($pgmCustomField)) {
							foreach($pgmCustomField as $key => $pgm_field) {
							?>
							if (document.filterform['<?php echo $pgm_field['fieldname']; ?>']) {
							d.<?php echo $pgm_field['fieldname']; ?> =$('[name="<?php echo $pgm_field["fieldname"];?>"]').val();
							}
							<?php
							}
							}
							?>
							},
			            },
					"aLengthMenu": [
		                [10, 15, 25, 50, 100],
		                [10, 15, 25, 50, 100]
		            ],
					"iDisplayLength": 10,
		            "aaSorting": [[ Number(order), _by]],
		            "columns": [
                      {
                        "defaultContent": ''
                      },
			 
                     //{ "data": "checkbox" },
                     { "data": "coursename" },
			         { "data": "shortname" },
                     { "data": "startdate" },
                     { "data": "enddate" },
                     { "data": "category" },
			         { "data": "posts" },
                     { "data": "batches" },
                     { "data": "status" },
                     { "data": "actions" }
                     ],
		            "columnDefs": [ { "targets": $targetarr, "orderable": false } ],
                    "drawCallback" : updateCheckBoxVals,
                    "iDisplayStart": start_page,
                    "pageLength": length_page
		            });

				 $('#datatable_filter').unbind().bind('keyup', function(e) {
				    if(e.keyCode == 13) {
					var a = document.getElementById("test").value;
					oTable = $('#datatable').dataTable();
                    oTable.fnFilter( a,8 );
                    }
                });
		       
				

				/* Code for dataTable ends here */

				/* Hide the Export with user and Export with usergroup icons if Data table is empty */
				$datatable.on('xhr.dt',function(e, settings, json, xhr){
					if(json.recordsTotal <=0)
					{
						$("#export_link").removeAttr("href").css('cursor', 'default');
					}

				});

				$datatable.on('click','.deletefeed',function(e){
					e.preventDefault();
	    			var $this = $(this);
					var $deletemodal = $('#deletemodal');
	    			$deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href')).end().modal('show');
				})

				/* Code to get the selected checkboxes in datatable starts here*/
				if(typeof window.checkedBoxes == 'undefined')
					window.checkedBoxes = {};
				$datatable.on('change','td input[type="checkbox"]',function(){
					var $this = $(this);
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

				/* Code for view content feed details starts here */

				$datatable.on('click','.viewfeed',function(e){
					e.preventDefault();
					var $this = $(this);
					var $viewfeeddetails = $('#viewfeeddetails');
					simpleloader.fadeIn(200);
	    			$.ajax({
                        type: "GET",
                        url: $(this).attr('href')
                    })
                    .done(function( response ) {
                    	$viewfeeddetails.find('.modal-body').html(response).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
				});

				/* Code for view content feed details ends here */

	    		/* Code for user feed rel starts here */
	    		$datatable.on('click','.feedrel',function(e){
	    			e.preventDefault();
	    			simpleloader.fadeIn();
	    			var $this = $(this);
	    			var $triggermodal = $('#triggermodal');
	    			var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
	    			
	    			$iframeobj.unbind('load').load(function(){

	    			//css code for the alignment 
	    			// var isFF = !!navigator.userAgent.match(/firefox/i);  		
	    			var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
		            if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){		                
		                // $triggermodal.find('.modal-body').css({"top":"-40px"});
		                // $triggermodal.find('.modal-assign').css({"top": "15px"});
		               	//$triggermodal.find('.modal-assign').css({"top": "54px"});
		            }             
		            else{
		              $triggermodal.find('.modal-assign').css({"top": "3px"});		            	  
		              $triggermodal.find('.modal-body').css({"top":"0px"});		              
		            }
		            //code ends here 			

						// $('#selectedcount').text('0 Entrie(s) selected');

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
								// $('#selectedcount').text(count+ ' Entrie(s) selected');
							},10);
						});
						$iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
						/* Code to refresh selected count ends here*/
	    			})
	    			$triggermodal.find('.modal-body').html($iframeobj);
	    			$triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

	    			//code for top assign button click starts here
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
	    			//code for top assign button click ends here


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
							url: '{{URL::to('/cp/contentfeedmanagement/course-feed/')}}/'+action+'/'+$this.data('key'),
							data: 'ids='+$postdata+"&empty=true"
						})
						.done(function( response ) {
							if(response.flag == "success")
							$('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
							// if(response.flag == "success" && response.action=="user")
							// $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/program.course_user_assigned_success');?></div>').insertAfter($('.page-title'));
							if(response.message!=undefined && response.flag == "error")
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
							if(response.message==undefined && response.flag == "error")
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/program.server_error');?></div>').insertAfter($('.page-title'));
							$triggermodal.modal('hide');
							var $alerts = $('.alert');
							setTimeout(function(){
								$alerts.alert('close');
							},5000);
							//window.datatableOBJ.fnDraw(true);
							$('#datatable').DataTable().ajax.reload();
							simpleloader.fadeOut(200);
						})
						.fail(function() {
							$('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong> <?php echo trans('admin/program.server_error');?></div>').insertAfter($('.page-title'));
							//window.datatableOBJ.fnDraw(true);
							simpleloader.fadeOut(200);
						})
	    			})
	    		});
	    		/* Code for user feed rel ends here */
//start
$('#datatable tbody').on('click', 'td > a.details-control', function () {
				
        var tr = $(this).closest('tr');
        var row = table.row(tr);

        if ( row.child.isShown() ) {
            // This row is already open - close it
            row.child.hide();
            tr.removeClass('shown');
        }
        else {
            row.child( format(row.data()) ).show();
            tr.addClass('shown');
        }
    } );
	function format ( d ) {
if (d.count > 0) {
var result = '<table class="table table-bordered table-striped batch-table" cellpadding="5" cellspacing="0" border="0"><tr><th>Batch Name</th><th>{{trans('admin/program.start_date')}}</th><th>{{trans('admin/program.end_date')}}</th><th>Posts</th><th>Users</th>';

	if(d.program_sellability == 'no')
	{
		
		result += '<th>User Group</th>';
		
	}

	result += '<th>Actions</th></tr>';


for (var i=0;i<d.count;i++) {
result +='<tr><td>'+d.batch[i]['batch_name']+'</td>';
result +='<td>'+d.batch[i]['batch_startdate']+'</td>';
result +='<td>'+d.batch[i]['batch_enddate']+'</td>';
result +='<td>'+d.batch[i]['batch_posts']+'</td>';
result +='<td>'+d.batch[i]['batch_users']+'</td>';
	
	if(d.program_sellability == 'no')
	{
		if(d.batch[i]['user_group_enable'] == false)
		{

			result +='<td>N/A</td>';

		}
		else
		{

			result +='<td>'+d.batch[i]['batch_usergroups']+'</td>';
			
		}
	
	}
	
	result +='<td>'+d.batch[i]['batch_actions']+'</td></tr>';
}
result += '</table>';
}
else{
  result ="Batches not avaliable in this course";
}

return result;
}

});


//filter selection for the export channel
	$("#filter").bind("click", function() {
		var selectedValue = $('#filter').find('option:selected').val();
		var exportlink = $('#export_link_user').val();
		$('#export_link').attr('href',(exportlink+"/"+selectedValue));
		
		var exportlink = $('#export_link_ug').val();
		$('#export_link_group').attr('href',(exportlink+"/"+selectedValue));

	});

	//start of channel name filling
			$( "#feed_title" ).keyup(function() {
            var $this = $(this);
            feed_title = $this.val();
            if(feed_title != '')
            {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('/cp/contentfeedmanagement/course-data') }}",
                    data :{
                        feed_title:feed_title,
						program_sub_type:'single'
                    }
                }).done(function(e) {
                    if(e.status == true) {
                        $this.autocomplete({
                            source: e.data
                        });
                    }
                })
            }
        });
			//end of channel name filling
			//start of category name filling
			$( "#category" ).keyup(function() {
			
            var $this = $(this);
            category = $this.val();
            if(category != '')
            {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('/cp/categorymanagement/category-data') }}",
                    data :{
                        category:category
                    }
                }).done(function(e) {
                    if(e.status == true) {
                        $this.autocomplete({
                            source: e.data
                        });
                    }
                })
            }
        });
		
			//end of category name filling
			//start of batch name filling
			$( "#batch_name" ).keyup(function() {
            var $this = $(this);
            feed_title = $this.val();
            if(feed_title != '')
            {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('/cp/contentfeedmanagement/batch-data') }}",
                    data :{
                        feed_title:feed_title
                    }
                }).done(function(e) {
                    if(e.status == true) {
                        $this.autocomplete({
                            source: e.data
                        });
                    }
                })
            }
        });
//end of batch name filling
			function showhide()
     {
           var div = document.getElementById("advancesearch");
    if (div.style.display !== "none") {
        div.style.display = "none";
    }
    else {
        div.style.display = "block";
    }
     }


     //Export course reports
     $("#export_channel_link").click(function(){
     	var export_hidden_channel_link = $("#export_hidden_channel_link").val();
     	var filter = $('[name=filter] :selected').val();
     	var visibility = $('#visibility option:selected').val();
     	var sellability = $('#sellability option:selected').val();
     	var access = "all";
     	var category = $('#category').val();
		var feed_title = $('#feed_title').val();
     	var shortname = $('#shortname').val();
     	var descriptions = $('#descriptions').val();
     	var created_date = $('#created_date').val();
     	var updated_date = $('#updated_date').val();
     	var get_created_date = $('#get_created_date option:selected').val();
     	var get_updated_date = $('#get_updated_date option:selected').val();
     	var feed_tags = $('[name="feed_tags"]').val();
     	var fieldname = "";
     	<?php
		$pgmCustomField = CustomFields::getUserActiveCustomField($program_type='course',$program_sub_type='single',$status='ACTIVE'); ?>
		<?php
		if(!empty($pgmCustomField)) {
			foreach($pgmCustomField as $key => $pgm_field) {
		?>
			if (document.filterform['<?php echo $pgm_field['fieldname']; ?>']) {
				<?php echo $pgm_field['fieldname']; ?> = $('[name="<?php echo $pgm_field["fieldname"];?>"]').val();
				fieldname +="&{{$pgm_field['fieldname']}}="+{{$pgm_field['fieldname']}};
			}
		<?php
			}
		}
		?>
     	var channel_name = "";
     	var batch_name = $("#batch_name").val();
     	var url = export_hidden_channel_link +"/"+filter+"/"+visibility+"/"+sellability+"?access="+access+"&category="+escape(category)+"&feed_title="+escape(feed_title)+"&shortname="+escape(shortname)+"&descriptions="+escape(descriptions)+"&created_date="+created_date+"&updated_date="+updated_date+"&get_created_date="+get_created_date+"&get_updated_date="+get_updated_date+"&feed_tags="+escape(feed_tags)+"&channel_name="+escape(channel_name)+"&batch_name="+escape(batch_name)+fieldname;
		$(this).attr('href',url);
     });


</script>
@include('admin/theme/programs/__copy_course_content')
</div>
@stop
