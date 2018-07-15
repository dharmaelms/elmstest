<form  name="category" id="cat_feed_filter" method="get" >
	<div class="facets-sidebar">
		<div class="form-group form-md-line-input">
			<label for="form_control_1">{{ Lang::get('category.sort_by') }}</label>
			<?php if(!isset($sort_by)) $sort_by='new_to_old';?>
			<div class="sort-by">
				<select id="form_control_1" class="form-control" name="sort_by">
					<option value="new_to_old" <?php if($sort_by == 'new_to_old') echo "selected"?>>{{ Lang::get('category.new_to_old') }}</option>
					<option value="old_to_new" <?php if($sort_by == 'old_to_new') echo "selected"?>>{{ Lang::get('category.old_to_new') }}</option>
					<option value="a_z" <?php if($sort_by == 'a_z') echo "selected"?>>{{ Lang::get('category.A_to_Z') }}</option>
					<option value="z_a" <?php if($sort_by == 'z_a') echo "selected"?>>{{ Lang::get('category.Z_to_A') }}</option>
				</select>
				<div class="form-control-focus"> </div>
			</div>
		</div>

		@if(isset($cat_filter) && $cat_filter==1)
			<div class="portlet box grey-cascade">
				<div class="portlet-title">
					<div class="caption">{{ Lang::get('category.categories') }}</div>
					<div class="tools"><a href="javascript:;" class="collapse"></a></div>
				</div>
				<div class="portlet-body min-hg-200">
					<div class="dd icheck-list" id="nestable_list_1">
						<ol class="dd-list">
						@if(empty($categories)) {{ "There are no categories to display" }} @else
							@foreach($categories as $each)
							@if($each['parents']==null)
								<li class="dd-item">
									<div class="dd-handle">
										<label><input type="checkbox" class="filter" name="category[]" value="{{ $each['category_id']}}" <?php if( isset($cat_ids) && in_array($each['category_id'],$cat_ids)){?> checked="checked" <?php } ?> ><span>{{ html_entity_decode(ucwords(strtolower($each['category_name'])))}}</span></label>
									</div>
								</li>
							@endif
								@if(isset($each['children']))
				            	  @foreach($each['children'] as $key=> $sub_cat)
					                @if(isset($sub_cat['category_id']))
										<ol class="dd-list">
											<li class="dd-item">
												<div class="dd-handle">
													<label><input type="checkbox" class="filter" name="sub_category[]"  value="{{ $sub_cat['category_id']}}" <?php if( isset($cat_ids) && in_array($sub_cat['category_id'],$cat_ids)){?> checked="checked" <?php } ?> ><span>{{ html_entity_decode(ucwords(strtolower($sub_cat['category_name']))) }}</span></label>
												</div>
											</li>
										</ol>
									@endif
				           		 @endforeach
				        	@endif
						@endforeach
						@endif
					<div class="dd-handle">
					@if(isset($other_ids) && !empty($other_ids))
					<?php 	$selected 	= (isset($others_ids_checked) && $others_ids_checked===true) ?"checked=checked" : '';
					?>
			        	<label><input type="checkbox" class="filter" name="other_ids"  value="{{ $other_ids }}" {{$selected}} ><span>{{ Lang::get('category.others') }}</span></label>
			        @endif
						</div>
						
						</ol>
					</div>

				</div>

			</div><!-- END Category-->
		@endif
	
	<!--product type filter  -->
<!--  <?php 
	$Filter = config('app.showTypeFilterInChannelContent');
	$get_program_type = Input::get('program_type'); 
	$program_type = array(
				// Lang::get('catalog/template_two.product') => 'product',
				Lang::get('program.course') => 'course',
				Lang::get('program.channel') => 'channel',
				Lang::get('program.package') => 'package',
			);
?> -->
<!-- @if (($Filter == 'on') && isset($typeFilter) && $typeFilter==1)
	<div class="portlet box grey-cascade">
			<div class="portlet-title">
				<div class="caption"><?php echo Lang::get('program.type');?></div>
				<div class="tools"><a href="javascript:;" class="collapse"></a></div>
			</div>
			<div class="portlet-body min-hg-100">
				<div class="icheck-list" id="type_list_1">
				@foreach($program_type as $key=>$val)
                    <input type="checkbox" name="program_type[]" id="program_type[]" class="filter" value="{{$val}}" @if(is_array($get_program_type) && in_array($val, $get_program_type)) checked @endif > {{ $key}} <br/>
				@endforeach	
				</div>
			</div>
		</div>
@endif -->
<!--product type filter ends here -->
	
		@if(isset($feeds) && $feeds==1)
			<div class="portlet box grey-cascade">
				<div class="portlet-title">
					<div class="caption"><?php echo Lang::get('program.course');?></div>
					<div class="tools"><a href="javascript:;" class="collapse"></a></div>
				</div>
				<div class="portlet-body min-hg-200">
					<div class="icheck-list">
						@foreach($content_feeds as $feed)
							<?php if(isset($feed['program_id'])) {?>
							<label><input type="checkbox" class="icheck filter" name='feed[]' value="{{$feed['program_id']}}"  <?php if( isset($feed_ids) && in_array($feed['program_id'],$feed_ids)){?> checked="checked" <?php } ?> > <span>{{$feed['program_title']}}</span></label>
							<?php } ?>
						@endforeach
					</div>
				</div>
			</div><!-- END Content feeds-->
		@endif

  </div><!--facets sidebar div-->
</form>

	@if(isset($feeds_selected) && !empty($feeds_selected))
	<form>
		<div class="facets-sidebar">
			<div class="form-group form-md-line-input">
				<label for="form_control_1">Quiz Type</label>
				<div class="sort-by">
					<select name="sort_by" class="form-control" id="form_control_1">
						<option selected="" value="All">All</option>
						<option value="Quiz">Quiz</option>
						<option value="Practice Quiz">Practice Quiz</option>
						<option value="Question Generator">Question Generator</option>
					</select>
					<div class="form-control-focus"> </div>
				</div>
			</div> <!-- Quiz type -->
			<div class="form-group form-md-line-input">
				<label for="form_control_1">Quiz Source</label>
				<div class="sort-by">
					<select name="sort_by" class="form-control" id="form_control_1">
						<option selected="" value="Course">Course Quizzes</option>
						<option selected="" value="Direct">Direct Quizzes</option>
					</select>
					<div class="form-control-focus"> </div>
				</div>
			</div> <!-- Quiz source -->
		</div><!--facets sidebar div-->
	</form>
	<form id="filter" method="POST">
		<div class="portlet box grey-cascade">
			<div class="portlet-title">
				<div class="caption"><?php echo Lang::get('program.programs');?> @if(count($feeds_selected)) ({{ count($feeds_selected) }})@endif</div>
				<div class="tools"><a href="javascript:;" class="collapse"></a></div>
			</div>
			<div class="portlet-body min-hg-200">
				@if(!empty($feeds))
				<div class="icheck-list">
					@foreach($feeds as $f)
					<label>
						<input class="cf-list" type="checkbox" name="cf-slug[]" value="{{ $f['program_slug'] }}" @if(in_array($f['program_slug'], $feeds_selected)) checked @endif> <span>{{ $f['program_title'] }}</span>
					</label>
					@endforeach
				</div>
				@else
				<center>No <?php echo Lang::get('program.programs');?> to display</center>
				@endif
			</div>
		</div><!--  END Content feeds -->
	</form> 
	@endif

<style>
	.icheck-list > label, .dd-list label{ display: flex !important; line-height: 1.4; margin-bottom: 15px;}
  .icheck-list > label span,  .dd-list label span { margin-top: -5px;}
</style>
<script type="text/javascript">

	$('.filter').click(function(){
		
	$("#cat_feed_filter").removeAttr("action");
		
		$('#cat_feed_filter').submit();
	}); 
	$(function() { 
		$('#form_control_1').change(function() 
		{ 
			$("#cat_feed_filter").removeAttr("action");
			$('#cat_feed_filter').submit(); 
		});
	});
</script>

