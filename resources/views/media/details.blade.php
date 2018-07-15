<script type='text/javascript' src="{{URL::asset('admin/assets/jquery/jquery-2.1.1.min.js')}}" ></script>
@if($media->type === "image")
<img style="margin-top: 6px !important;" src="{!! URL::to("media_image/{$media->_id}") !!}" width="400"/>
@elseif($media->type === "audio")
	@include("media._audio")
@elseif($media->type === "video")
	@include('media.displayvideo', ['media' => $media, 'aspectratio' => '12:5'])
@endif