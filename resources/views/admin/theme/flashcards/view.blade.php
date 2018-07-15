@section('content')
<div class="row">
	<div class="col-md-12">
		<div class="box">
			<div class="box-content">
				<div class="col-md-12"><a class="btn btn-primary pull-right" href="{{ URL::to('/cp/flashcards/list') }}">Back</a></div>
				<div class="col-md-12"><label>Name - </label> {{ $flashcards->title }}</div>
				<div class="col-md-12"><label>Description - </label> {{ $flashcards->description }}</div>
				<div class="col-md-12"><label>Status - </label> {{ $flashcards->status }}</div>
				<br>
				@include('admin.theme.flashcards.preview', ['flashcards' => $flashcards->cards, 'height' => '400px'])
			</div>
		</div>
	</div>	
</div>
@stop