@section('content')
<div class="container">
 <div class="margin-bottom-60"></div><!--space-->
	<div class="row margin-bottom-60">
		<div class="col-md-12 page-404 center margin-bottom-60">
			<div class="number">
			404
			</div>
			<div class="details">
				<h3>Oops! You're lost.</h3>
				<p>
					We can not find the page you're looking for.<br>
					<a href="{{URL::to('/')}}" class="font-16"><strong>Return home</strong></a>
				</p>
			</div>
		</div>
	</div>
</div>
@stop
