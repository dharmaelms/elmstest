@section('content')
<div class="row">
	<div class="col-md-12 page-404">
		<div class="number">
			500
		</div>
		<div class="details">
			<h3> Internal Server Error.</h3>
			<p>
				Currently unable to handle this request.<br>
				<a href="{{URL::to('/')}}" class="font-16"><strong>Return home</strong></a>
			</p>
		</div>
	</div>
</div>
@stop
