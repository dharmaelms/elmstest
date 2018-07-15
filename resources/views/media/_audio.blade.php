<script src="{{URL::to('/portal/theme/default/plugins/jwplayer-7.7.4/jwplayer.js')}}"></script>
<script type="text/javascript">jwplayer.key="{{config('app.jwplayer.key')}}";</script>
<?php $for_id = rand(1000, 1000000);
$for_id  = 'akamai_player_'.$for_id;?>
<div id="{{$for_id}}"></div>
<style>
    #akamai_player{{$for_id}}_wrapper{
        width:100% !important;
    }
</style>
<script type="text/javascript">
    jwplayer("{{$for_id}}").setup({
        @if($media->visibility === "public")
    		file:"{{URL::to(config("app.public_dams_audio_path").$media->unique_name_with_extension)}}",
        @else
    		file:"{{URL::to("/cp/dams/show-media/{$media->_id}.mp3")}}",
    	@endif
        primary: "html5",
        androidhls: "true",
        fallback: "true",
        width: '100%',
        height: 40
    });
</script>