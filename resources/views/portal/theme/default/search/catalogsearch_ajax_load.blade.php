<?php use App\Model\Category; ?>

@if(isset($s_data) && !empty($s_data))
	@foreach($s_data as $program)
		<li>
			<div class="img-div">
				@if(isset($program['program_cover_media']) && !empty($program['program_cover_media']))
					<img src="{{URL::to('media_image/'.$program['program_cover_media'])}}" alt="Program">
				@else
					<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Channel">
				@endif
			</div>
			<div class="data-div">
				<a href="{{URL::to('catalog/course/'.$program['program_slug'])}}"><strong>{!! Helpers::highlight($search, $program['program_title']) !!}</strong></a><br>
				<p class="font-13"><?php $description = str_limit($program['program_description'], $limit = 250, $end = '...'); ?>{!! Helpers::highlight($search, $description) !!}</p>
				<?php 
	                if(Auth::check()) 
	                {
	                    $timezone = Auth::user()->timezone;
	                }
	                else
	                {
	                    $timezone = config('app.default_timezone');
	                }
	            ?>
				<table class="xs-margin">
					<tr>
						<td width="120px"><strong>{{ Lang::get('search.program_type') }}</strong></td>
						<td>
							@if($program['program_type'] == 'content_feed')
								Channel
							@elseif($program['program_type'] == 'course')
								Course
							@elseif($program['program_type'] == 'product')
								Product
							@endif
						</td>
					</tr>
					<tr>
						<td width="120px"><strong>Start Date</strong></td><td>{{ Timezone::convertFromUTC('@'.$program['program_startdate'], $timezone, Config('app.date_format')) }}</td>
					</tr>
					<tr>
						<td width="120px"><strong>End Date</strong></td><td>{{ Timezone::convertFromUTC('@'.$program['program_enddate'], $timezone, Config('app.date_format')) }}</td>
					</tr>
					<?php 
						if($program['program_type'] == 'content_feed')
						{
							$categories=Category::getFeedRelatedCategory($program['program_id']);
						} else {
							$categories=Category::getProductRelatedCategory($program['program_id']);
						}

						if(empty($categories))
								$categories=array();

						$category='';
						foreach($categories as $info)
						{
                            $category.= html_entity_decode($info['category_name']).', ';
						}
					?>
					@if(!empty($categories))
						<tr>
							<td width="120px"><strong>Category</strong></td><td>{{trim($category,', ')}}</td>
						</tr>
					@endif
					@if(!empty(array_filter($program['program_keywords'])))
						<?php $keywords = ''; ?>
						@foreach($program['program_keywords'] as $key)
							<?php 
								$keywords.= $key.', ';
							?>
						@endforeach
						<tr>
							<td width="120px"><strong>{{ Lang::get('search.keywords') }}</strong></td><td>{!! Helpers::highlight($search, trim($keywords,', ')) !!}</td>
						</tr>
					@endif
				</table>
			</div>
		</li>
	@endforeach
@endif

@if(isset($p_data) && !empty($p_data))
	@foreach($p_data as $program)
		<li>
			<div class="img-div">
				@if(isset($program['package_cover_media']) && !empty($program['package_cover_media']))
					<img src="{{URL::to('media_image/'.$program['package_cover_media'])}}" alt="Program">
				@else
					<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Channel">
				@endif
			</div>
			<div class="data-div">
				<a href="{{URL::to('catalog/course/'.$program['package_slug'].'/package')}}"><strong>{!! Helpers::highlight($search, $program['package_title']) !!}</strong></a><br>
				<p class="font-13"><?php $description = str_limit($program['package_description'], $limit = 250, $end = '...'); ?>{!! Helpers::highlight($search, $description) !!}</p>
				<?php 
	                if(Auth::check()) 
	                {
	                    $timezone = Auth::user()->timezone;
	                }
	                else
	                {
	                    $timezone = config('app.default_timezone');
	                }
	            ?>
				<table class="xs-margin">
					<tr>
						<td width="120px"><strong>{{ Lang::get('search.program_type') }}</strong></td>
						<td>
							Package
						</td>
					</tr>
					<tr>
						<td width="120px"><strong>Start Date</strong></td><td>{{ Timezone::convertFromUTC('@'.$program['package_startdate'], $timezone, Config('app.date_format')) }}</td>
					</tr>
					<tr>
						<td width="120px"><strong>End Date</strong></td><td>{{ Timezone::convertFromUTC('@'.$program['package_enddate'], $timezone, Config('app.date_format')) }}</td>
					</tr>
					<?php 
						$categories=Category::getPackageRelatedCategory($program['package_id']);
						if(empty($categories))
								$categories=array();

						$category='';
						foreach($categories as $info)
						{
                            $category.= html_entity_decode($info['category_name']).', ';
						}
					?>
					@if(!empty($categories))
						<tr>
							<td width="120px"><strong>Category</strong></td><td>{{trim($category,', ')}}</td>
						</tr>
					@endif
					<?php 
						$package_keywords = array_get($program, 'package_keywords', []); 
					?>
					@if(!empty($package_keywords))
						<?php $keywords = ''; ?>
						@foreach($program['package_keywords'] as $key)
							<?php 
								$keywords.= $key.', ';
							?>
						@endforeach
						<tr>
							<td width="120px"><strong>{{ Lang::get('search.keywords') }}</strong></td><td>{!! Helpers::highlight($search, trim($keywords,', ')) !!}</td>
						</tr>
					@endif
				</table>
			</div>
		</li>
	@endforeach
@endif
