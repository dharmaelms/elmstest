<form  name="category" id="cat_feed_filter" method="get" >
<div class="facets-sidebar">
	<div class="form-group form-md-line-input">
		<label for="form_control_1">Sort By</label>
		<?php if(!isset($sort_by)) $sort_by='new_to_old';?>
		<div class="sort-by">
			<select id="form_control_1" class="form-control" name="sort_by">
				<option value="new_to_old" <?php if($sort_by == 'new_to_old') echo "selected"?>>Newest to Oldest</option>
				<option value="old_to_new" <?php if($sort_by == 'old_to_new') echo "selected"?>>Oldest to Newest</option>
				<option value="a_z" <?php if($sort_by == 'a_z') echo "selected"?>>A to Z</option>
				<option value="z_a" <?php if($sort_by == 'z_a') echo "selected"?>>Z to A</option>
			</select>
			<div class="form-control-focus"> </div>
		</div>
	</div>
	
		<div class="portlet box grey-cascade">
			<div class="portlet-title">
				<div class="caption">Categories</div>
				<div class="tools"><a href="javascript:;" class="collapse"></a></div>
			</div>
			<div class="portlet-body min-hg-200">
				<div class="dd icheck-list" id="nestable_list_1">
					<ol class="dd-list">
						@foreach($categories as $each)
						@if($each['parents']==null)
							<li class="dd-item">
								<div class="dd-handle">
									<label><input type="checkbox" class="filter" name="category[]" value="{{ $each['category_id']}}" <?php if( isset($cat_ids) && in_array($each['category_id'],$cat_ids)){?> checked="checked" <?php } ?> >{{ ucwords(strtolower($each['category_name']))}} </label>
								</div>
							</li>
						@endif
							@if(isset($each['children']))
			            	  @foreach($each['children'] as $sub_cat)
				                @if(isset($cat_id_name[$sub_cat['category_id']]))
									<ol class="dd-list">
										<li class="dd-item">
											<div class="dd-handle">
												<label><input type="checkbox" class="filter" name="sub_category[]"  value="{{ $sub_cat['category_id']}}" <?php if( isset($cat_ids) && in_array($sub_cat['category_id'],$cat_ids)){?> checked="checked" <?php } ?>>{{ucwords(strtolower($cat_id_name[$sub_cat['category_id']]))}} </label>
											</div>
										</li>
									</ol>
								@endif
			           		 @endforeach
			        	@endif
					@endforeach
					</ol>
				</div>
			</div>
		</div><!-- END Category-->
	

	@if(isset($feeds) && $feeds==1)
		<div class="portlet box grey-cascade">
			<div class="portlet-title">
				<div class="caption"><?php echo Lang::get('program.programs');?></div>
				<div class="tools"><a href="javascript:;" class="collapse"></a></div>
			</div>
			<div class="portlet-body min-hg-200">
				<div class="icheck-list">
					@foreach($content_feeds as $feed)
						<label><input type="checkbox" class="icheck filter" name='feed[]' value="{{$feed['program_id']}}"  <?php if( isset($feed_ids) && in_array($feed['program_id'],$feed_ids)){?> checked="checked" <?php } ?> > {{ucwords(strtolower($feed['program_title']))}} </label>
					@endforeach
				</div>
			</div>
		</div><!-- END Content feeds-->
	@endif
</div><!--facets sidebar div-->
</form>

	@if(isset($feeds_selected))
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
						<input class="cf-list" type="checkbox" name="cf-slug[]" value="{{ $f['program_slug'] }}" @if(in_array($f['program_slug'], $feeds_selected)) checked @endif> {{ $f['program_title'] }}
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

