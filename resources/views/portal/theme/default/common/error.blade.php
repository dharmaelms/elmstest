@section('content')
	<div class="row">
	<div class="col-md-12 page-404">
		<div class="number">401</div>
		<div class="details">
			<h3>Unauthorized: Access is denied</h3>
			<p>
				{{ $message }}
				<br>
				<a href="{{ $callback }}" class="font-16"><strong>Return Back</strong></a>
			</p>
		</div>
	</div>
</div>
@stop