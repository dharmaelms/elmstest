@foreach($data as $package)
	<div class="row">
		<div class="col-md-12">
			<div>
				<h3 class="page-title margin-top-10">
					<a href="#">{{ $package->title }}</a>
				</h3>
			</div>
			<div class="md-margin cf-info white-bg">
				<div class="row">
					<div class="col-md-4 col-sm-6 col-xs-12">
						@if(!empty($package->cover_image))

						@endif
					</div>
					<div class="col-md-5 col-sm-5 col-xs-12">
						@if(isset($package->categories))
							<span class="col-xs-3">
								<strong>{{ trans('package.categories') }}:</strong>
							</span>
							<span class="col-xs-9">{{ implode(',', $package->categories) }}</span>
						@endif
						<span class="col-xs-3">
							<strong>{{ trans('package.start_date') }}:</strong>
						</span>
						<span class="col-xs-9">{{ implode(',', $package->start_date) }}</span>
						<span class="col-xs-3">
							<strong>{{ trans('package.end_date') }}:</strong>
						</span>
						<span class="col-xs-9">{{ implode(',', $package->end_date) }}</span>
					</div>
				</div>
			</div>
		</div>
	</div>
@endforeach