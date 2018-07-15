@extends("admin.theme.layout.master_extended")
@section("content")
@parent
<link rel="stylesheet" href="{{ URL::asset('admin/css/assessment/question.css') }}">
<link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
<script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
<script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
<script type="text/javascript" src="{{ URL::asset("admin/assets/ckeditor/ckeditor.js")}}"></script>
<script type="text/javascript" src="{{ URL::asset("admin/assets/ckeditor/config.js") }}"></script>
<script type="text/javascript" src="{{ URL::asset("admin/js/assessment/editor_media.js") }}"></script>
<div class="row">
	<div class="col-md-12">
    	<div class="box">
	        <div class="box-title">
	        </div>
        	<div class="box-content">
        		@yield("descriptive_attributes")
            </div>
        </div>
    </div>
</div>
<script>
	$(document).ready(function(){
		$('input.tags').tagsInput({
            width: "auto"
        });
	});
</script>
@include("admin/theme/assessment/media_embed", ["from" => "question"])
@stop