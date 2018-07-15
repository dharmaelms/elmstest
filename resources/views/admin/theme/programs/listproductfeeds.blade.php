@section('content')
	 @if ( Session::get('success') )
		<div class="alert alert-success" id="alert-success">
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
<?php
use App\Model\CustomFields\Entity\CustomFields;
$pgmCustomField = CustomFields::getUserActiveCustomField($program_type = 'product', $program_sub_type = '',$status ='ACTIVE');
?>
	<script>
		
		if (!Array.prototype.remove) {
			Array.prototype.remove = function(val) {
				var i = this.indexOf(val);
				return i>-1 ? this.splice(i, 1) : [];
			};
		}
		var $targetarr = [0,4,5,7];
	</script>
	<script src="{{ URL::asset('admin/assets/jquery/jquery-2.1.1.min.js')}}"></script>
	<script src="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.css')}}">
	
	<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
	
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	           <!--<div class="box-title">
	              
	            </div>-->
	            <div class="box-content">
				<div class="col-md-12 margin-bottom-20">
					<div class="btn-toolbar pull-right clearfix">
                
            		<div class="btn-group">
						<a href="#" class="btn btn-primary btn-sm" id="button" onclick="showhide()" style="margin-right:10px;">{{trans('admin/program.advance_search')}}</a>
						<?php if(isset($permissions) && is_array($permissions) && array_key_exists('add-products', $permissions)) {?>
                        		<div class="btn-group">
                            	<a class="btn btn-primary btn-sm" href="{{url::to('/cp/contentfeedmanagement/add-product')}}">
                            		<span class="btn btn-circle blue show-tooltip custom-btm">
                            			<i class="fa fa-plus"></i>

                        			</span>&nbsp;{{trans('admin/program.add_new_product')}}

                    			</a>&nbsp;&nbsp;
                    			</div>
                         <?php } ?>
						<a class="btn btn-primary btn-sm" href="{{URL::to('/cp/contentfeedmanagement/add-packets?type=product')}}">
	                          <span class="btn btn-circle blue show-tooltip custom-btm">
	                            <i class="fa fa-plus"></i>
	                          </span>&nbsp;<?php echo trans('admin/program.create_post'); ?>
						</a>&nbsp;&nbsp;
                        <a class="btn btn-circle show-tooltip" title="Refresh" href="#" onclick="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);var a = $(this).addClass('anim-turn180');setTimeout(function(){a.removeClass('anim-turn180');},500);return false;"><i class="fa fa-refresh"></i></a>
                        </div>
                    </div>
				</div>
                    <br/><br/>
	            	<!--<div class="col-md-6">-->
                    <form class="form-horizontal" action="" name="filterform">
					<div id="advancesearch" style="display:none;">
			            	<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
		                            <div class="form-group">
		                              <label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.showing')}} :</b></label>
		                              <div class="col-sm-6 col-lg-6 controls" style="padding-left:0;">
		                              	<?php $filter = Input::get('filter'); $filter = strtolower($filter); ?>
		                                <select class="form-control chosen" name="filter" data-placeholder="ALL" >
		                                    <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>{{trans('admin/program.all')}}</option>
		                                    <option value="ACTIVE" <?php if ($filter == 'active') echo 'selected';?>>{{trans('admin/program.active')}}</option>
		                                    <option value="IN-ACTIVE" <?php if ($filter == 'in-active') echo 'selected';?>>{{trans('admin/program.in_active')}}</option>
		                                </select>
		                              </div>
		                           </div>
									
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
									<!--start of access filter-->
									<div class="form-group" id="access_filter" style="display:none;">
									<label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.access')}} :</b></label>
									<div class="col-sm-6 col-lg-6 controls" style="padding-left:0;">
									<?php $access= Input::get('access'); $access = strtolower($access); ?>
									<select class="form-control chosen" name="access" id="access" data-placeholder="all">
									<option value="all" <?php if ($access == 'all') echo 'selected';?>>{{trans('admin/program.all')}}</option>
									<option value="restricted_access" <?php if ($access == 'yes') echo 'selected';?>>{{trans('admin/program.restricted')}}</option>
									<option value="general_access" <?php if ($access == 'no') echo 'selected';?>>{{trans('admin/program.general')}}</option>
									</select>
									</div>
									</div>
									<!--end of access filter-->
									<!--start of category filter-->
									<div class="form-group">
									<label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.category')}} :</b></label>
									<div class="col-sm-6 col-lg-6 controls"  style="padding-left:0;">
									<input type="text" id="category" class="form-control" value="{{Input::old('category')}}" name="category" />
																
									</div>
									</div>
									<!--end of category filter-->
			            	</div>
			            	<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
			            		<!--start of fullname filter-->
									<div class="form-group">
									    <label class="col-sm-4 col-md-4 col-lg-5 control-label" style="padding-right:0;text-align:left"><b>{{trans('admin/program.product_name')}} :</b></label>
									    <div class="col-sm-6 col-md-8 col-lg-7 controls" style="padding-left:0;">
										<input type="text" name="feed_title" id="feed_title" class="form-control" value="{{ Input::get('feed_title') }}" placeholder="{{trans('admin/program.product')}} Name">
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
										<select class="form-control chosen cs-chosen" name="get_created_date" id="get_created_date">
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
										<select class="form-control chosen cs-chosen" name="get_updated_date" id="get_updated_date">
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
										<input type="text" class="form-control tags medium" value="{{Input::old('feed_tags')}}" name="feed_tags" />
									
									</div>
								</div>
									<!--end of key tags filter-->
									
									<!--start of search button-->
									<div class="form-group last">
										<div class="col-sm-9 col-sm-offset-4 col-lg-7 col-lg-offset-5">
										<button class="btn btn-success" type="button" onclick="var e=$.Event('keyup');e.keyCode=13;$('#datatable_filter [type=search]').trigger(e);" tabindex="1">Search</button>
										<a href="{{URL::to('/cp/contentfeedmanagement/list-products')}}"><button type="button" class="btn">{{trans('admin/program.cancel')}}</button></a>
										</div>
									</div>
									<!--end of search button-->
			            	</div>
			            	<div class="col-lg-4 col-md-6 col-sm-12 col-xs-12 margin-bottom-20">
			            		<!--custom fields starts here-->
								@if($pgmCustomField)
									
										<!--select box starts here-->
										<div class="form-group">
									    <label class="col-sm-4 col-lg-3 control-label" style="padding-right:0;text-align:left"><b>Select Field :</b></label>
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
	            	
                    <div class="clearfix"></div>
                    <table class="table table-advance" id="datatable">
				        <thead>
				            <tr>
				                <th style="width:18px"><input type="checkbox" id="checkall" /></th>
				                <th style="width:20% !important">{{trans("admin/program.product")}}</th>
				                <th>{{trans('admin/program.start_date')}}</th>
				                <th>{{trans('admin/program.end_date')}}</th>
			                	<th>{{trans('admin/category.category')}}</th>
			                	<th>{{trans('admin/program.packets')}}</th>
			                
				                <th>{{trans('admin/program.status')}}</th>
	                        	<th style="min-width:98px">{{trans('admin/program.actions')}}</th>
				                <script>$targetarr.pop()</script>  
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
                                                {{trans('admin/program.product_delete')}}
                                        </h3>                                                
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-body" style="padding: 20px">
                        {{trans('admin/program.modal_delete_product')}}
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
                                                View {{trans('admin/program.product')}} details
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
			  //(function(){var e={defaults:{id:"simpleloader",init:false,opacity:.5,autoopen:false,selector:false},init:function(e){if(typeof jQuery=="undefined"){this.log("jQuery is not found. Make sure you have jquery included");return false}else if(typeof $=="undefined"){$=jQuery}$.fn.extend({exists:function(){return this.length!==0}});if(!this.isEmpty(e)){this.updatedefaults(e)}if(!$("#"+this.defaults.id).exists()){var e=this.defaults.selector?this.defaults.selector:$("body");e.prepend('<div class="'+this.defaults.id+'" style="display:none;position:absolute;width:100%;top:0;background-color:black;height:100%;left:0;z-index:99999;opacity:'+this.defaults.opacity+';" ></div>');e.prepend('<div class="'+this.defaults.id+' progress progress-striped active" style="display:none; position: fixed !important; width: 16%; z-index: 100000; height: 20px;margin: auto; left: 0; top: 0; right: 0; bottom: 0; "> <div style="width: 100%;" class="progress-bar progress-bar-success">Loading...</div> </div>');this.defaults.init=true;if(this.autoopen){this.fadeIn()}}},show:function(e){if(this.defaults.init)$("."+this.defaults.id).show(e);else this.log("Please initialize the loader using simpleloader.init()")},hide:function(e){if(this.defaults.init)$("."+this.defaults.id).hide(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeIn:function(e){this.fadein(e)},fadeOut:function(e){this.fadeout(e)},fadein:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeIn(e);else this.log("Please initialize the loader using simpleloader.init()")},fadeout:function(e){if(this.defaults.init)$("."+this.defaults.id).fadeOut(e);else this.log("Please initialize the loader using simpleloader.init()")},remove:function(){$("."+this.defaults.id).remove();this.defaults.init=false},log:function(e){if(window.console)console.log(e)},updatedefaults:function(e){for(var t in e){this.defaults[t]=e[t]}},isEmpty:function(e){for(var t in e){if(e.hasOwnProperty(t))return false}return true}};window.simpleloader=e})();
    //simpleloader.init();
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
   $('[name="sellability"]').on("change",function(){
   
					if($(this).val()== "yes"){
						$('#access_filter').prop('disabled',false);
						$('#access_filter').show();
					}
					else {
						$('#access_filter').prop('disabled', 'disabled');
						$('#access_filter').hide();
					}
				});
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
        		$('#alert-success').delay(5000).fadeOut();
	    		/* code for DataTable begins here */
	    		var $datatable = $('#datatable');
	    		window.datatableOBJ = $datatable.on('processing.dt',function(event,settings,flag){
				    $('#datatable_processing').hide();
	    			if(flag == true)
	    				simpleloader.fadeIn();
	    			else
	    				simpleloader.fadeOut();
	    		}).on('draw.dt',function(event,settings,flag){
	    			$('.show-tooltip').tooltip({container: 'body'});
	    		}).dataTable({
	    			"serverSide": true,
					"ajax": {
			            "url": "{{URL::to('/cp/contentfeedmanagement/product-list-ajax')}}",
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
							d.access= $('[name="access"]').val();
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
			            "error" : function(){
			            	alert('Please check if you have an active session.');
			            	window.location.replace("{{URL::to('/')}}");
			            }
			        },
		            "aLengthMenu": [
		                [10, 15, 25, 50, 100],
		                [10, 15, 25, 50, 100]
		            ],
		            "iDisplayLength": 10,
		            "aaSorting": [[ Number(order), _by]],
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

				
	    		
				$datatable.on('click','.feedrel',function(e){
                    e.preventDefault();
                    simpleloader.fadeIn();
                    var $this = $(this);
                    var $triggermodal = $('#triggermodal');
                    var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
                    
                    $iframeobj.unbind('load').load(function(){
                        $('#selectedcount').text('0 Entrie(s) selected');

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
                            var count = 0;
                            $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                count++;
                            });
                            $('#selectedcount').text(count+ ' selected');
                        });
                        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                        /* Code to refresh selected count ends here*/
                    })
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        alert("Btn Clicked");
                    });

                    //code for top assign button click
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
                    //code for top assign button ends here

                    

                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                       
                            var $postdata = "";
                            $.each($checkedboxes,function(index,value){
                                if(!$postdata)
                                    $postdata += index;
                                else
                                    $postdata += "," + index;
                            });

                            // Post to server
                            var action = $this.data('info');

                            simpleloader.fadeIn();
                            $.ajax({
                                type: "POST",
                                url: '{{URL::to('/cp/contentfeedmanagement/product-feed/')}}/'+action+'/'+$this.data('key'),
                                data: 'ids='+$postdata+"&empty=true"
								
                            })
							
                            .done(function( response ) {
							
                                if(response.flag == "success")
                                    $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                                else
                                    $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button> <?php echo trans('admin/category.server_error');?></div>').insertAfter($('.page-title'));
                                $triggermodal.modal('hide');
                                setTimeout(function(){
                                    $('.alert').alert('close');
                                },5000);
                                window.datatableOBJ.fnDraw(true);
                                simpleloader.fadeOut(200);
                            })
                            .fail(function() {
                                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/category.server_error');?></div>').insertAfter($('.page-title'));
                                window.datatableOBJ.fnDraw(true);
                                simpleloader.fadeOut(200);
                            })
                        
                    })
                });
			

	    	});
			//start of channel name filling
			$( "#feed_title" ).keyup(function() {
            var $this = $(this);
            feed_title = $this.val();
            if(feed_title != '')
            {
                $.ajax({
                    type: 'GET',
                    url: "{{ url('/cp/contentfeedmanagement/product-data') }}",
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
	    </script>
	</div>
	
@stop
