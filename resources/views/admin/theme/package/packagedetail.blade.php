<style>
	.feedinfo tr td{
		word-break: break-all;
	}
	#akamai_player_wrapper{
		width:100% !important;
	}
</style>

<div class="row">
	<div class="col-md-12">
		<div class="col-md-6">
			<table class="table table-bordered feedinfo" style="table-layout: fixed;csword-wrap: break-word;">	
				<tr>
					<th>{{trans('admin/program.title')}}</th>
					<td>{!! !empty($package['package_title']) ? $package['package_title'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.short_name')}}</th>
					<td>{!! !empty($package['package_shortname']) ? $package['package_shortname'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.description')}}</th>
					<td>{!! !empty($package['package_description']) ? $package['package_description'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.start_date')}}</th>
					<td>{!! !empty($package['package_startdate']) ? $package['package_startdate'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.end_date')}}</th>
					<td>{!! !empty($package['package_enddate']) ? $package['package_enddate'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.review')}}</th>
					<td>{!! !empty($package['package_review']) ? $package['package_review'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.rating')}}</th>
					<td>{!! !empty($package['package_rating']) ? $package['package_rating'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.visibility')}}</th>
					<td>{!! !empty($package['package_visibility']) ? $package['package_visibility'] : 'NA' !!}</td>
				</tr>

				@if (config('app.ecommerce'))
					<tr>
						<th>{{trans('admin/program.sellability')}}</th>
						<td>{!! !empty($package['package_sellability']) ? $package['package_sellability'] : 'NA' !!}</td>
					</tr>
				@endif

				<tr>
					<th>{{trans('admin/program.status')}}</th>
					<td>{!! !empty($package['status']) ? $package['status'] : 'NA' !!}</td>
				</tr>

				<tr>
					<th>{{trans('admin/program.program')}}</th>
					@if (!empty($program_rel))
						<td>
						<?php
							$i=1;
							foreach($program_rel as $field) {
								echo  $i.') '.$field['program_title'].'<br/>';
								$i++;
							}
						?> 
						</td>
					@else
						<td> NA </td>
					@endif
				</tr>

			</table>
		</div>

		<div class="col-md-6">
			@if (isset($package['package_cover_media']) && $package['package_cover_media'])
				@if (!empty($media) && $media['type'] == "image")
					<img src="{{URL::to('/cp/dams/show-media/'.$package['package_cover_media'])}}" width="100%">
				@endif
			@endif
		</div>

	</div>
</div>
