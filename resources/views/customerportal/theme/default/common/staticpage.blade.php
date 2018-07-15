@section('content')
<style type="text/css">
	td, th { padding: 10px !important;}
</style>
<div class="container">
	<div class="row">
		<div class="col-md-offset-1 col-md-10 col-sm-12 col-xs-12 margin-bottom-60">
			<div class="center margin-bottom-30">
				<h2 class="font-weight-500 black myellow-border margin-bottom-20">{{$staticpage['title']}}</h2>
			</div>
			<div>
				{!! $staticpage['content'] !!}
			</div>
		</div>
	</div>
</div>
@stop