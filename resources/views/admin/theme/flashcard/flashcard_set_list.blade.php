@extends("admin.theme.layout.master_extended")
@section("content")
<style>
.flashcard-container
{
	padding-bottom : 250px;
}

.flashcard.front
{
	box-sizing: border-box;
	box-shadow: -5px 5px 5px #aaa;
	padding: 10px;
	color: white;
	text-align: center;
	background-color: #aaa;
}
.flashcard.back {
	box-sizing: border-box;
	box-shadow: -5px 5px 5px #aaa;
	padding: 10px;
	color: white;
	text-align: center;
	background-color: #aaa;
}
</style>
<script type="text/javascript" src="{{ URL::asset("admin/js/flip.js") }}"></script>
<div class="row">
	<div class="col-sm-12 col-md-12">
		<div class="flashcard-container">
			<div class="flashcard front">
				<span>{{ trans('admin/flashcards.any_content_can_go_here') }}</span>
			</div>
			<div class="flashcard back">
				<span>{{ trans('admin/flashcards.this_is_nice_for_exposing_more_info') }}</span>
			</div>
		</div>
	</div>
</div>
<script>
	$(document).ready(function(){
		$(".flashcard-container").flip({
			axis : "y",
			trigger : "click"
		});
	});
</script>
@stop