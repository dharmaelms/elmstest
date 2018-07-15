<script src="{{URL::asset("portal/theme/default/plugins/jwplayer-7.7.4/jwplayer.js")}}"></script>
<script type='text/javascript' src="/portal/theme/default/js/akamaiRegenerateToken.js" ></script>

<script type="text/javascript">
    jwplayer.key = '{{ config('app.jwplayer.key') }}';
    var AKAMAI_MEDIA_ANALYTICS_CONFIG_FILE_PATH = '{{ config('app.akamai_analytics.html5.config_file') }}';
</script>
<script type='text/javascript' src="{{URL::to('/portal/theme/default/plugins/jwplayer-7.7.4/checkflashplayer.js')}}"></script>

<?php $for_id = rand(1000, 1000000);
$for_id  = 'akamai_player_'.$for_id;?>
<style>
    #{{$for_id}}_wrapper{
        width:100% !important;
    }
</style>

@if(isset($media->akamai_details["delivery_html5_url"]))
<div class="wrapper-video{{$for_id }}">
    <div class="h_iframe{{$for_id }}">
        <script type='text/javascript'> 
            if(isMobile || isFlashInstalled){
                $('.wrapper-video{{$for_id }}').append("<div id='{{$for_id }}'></div>");
             
                jwplayer("{{$for_id }}").setup({
                    playlist:[{
                        sources:[

                            {
                                file:"{{$media->akamai_details["delivery_html5_url"]}}?{{$token}}",
                                "default": true
                            },
                            {
                                file:"{{$media->akamai_details["delivery_flash_url"]}}?{{$token}}",
                                provider: "{{URL::to('portal/theme/default/plugins/jwplayer-7.7.4/akamai/AkamaiAdvancedJWStreamProvider.swf')}}"
                            }
                        ]
                        @if(file_exists(config("app.dams_video_thumb_path").$media->unique_name.".png"))
                            ,image: "{{URL::to("/media_image/".$media->_id."?compress=1")}}"
                        @endif
                        @if(isset($media->srt_location) && file_exists($media->srt_location))
                            ,tracks: [{
                                file: "{{URL::to('/cp/dams/video-srt/'.$media->_id)}}", 
                                label: "English",
                                kind: "captions",
                                'default': 'true'
                            }]
                        @endif
                    }],
                    primary: "html5",
                    androidhls: "true",
                    fallback: "true",
                    aspectratio: "{{ $aspectratio }}",
                    width: "100%",
                });
                jwplayer().onReady(function(){ //play button fix
                    //$(document).on("mousedown", '.jw-icon-display',function(){
                    $('.jw-icon-display').mousedown(function() {
                        if(/Google Inc/.test(navigator.vendor)) {
                            jwplayer().play();
                        }
                    });
                });
                jwplayer().onError(function(evt) {  
                    callAjaxToCreateNewToken("{{$media->_id}}","{{$for_id }}");
                });

            }else{
                $('.wrapper-video{{$for_id }}').append("<span>{{ trans('admin/program.errormsg_for_installing_flash_player') }} <a id='link' href='{{ URL::to(config('app.download_flash_player')) }}' >{{ trans('admin/program.click_here') }}</a></span>");
                $("#link").click(function() {
                $("#link").attr('target', '_blank');
                return true;
                });
            }
        </script>
    </div>
</div>
@elseif (isset($media->akamai_details['stream_success_html5']))

<div class="wrapper-video{{$for_id }}">
    <div class="h_iframe{{$for_id }}">
        <script type="text/javascript">
            if(isMobile || isFlashInstalled){
                $('.wrapper-video{{$for_id }}').append("<div id='{{$for_id }}'></div>");

                jwplayer("{{$for_id }}").setup({
                    playlist:[{
                        sources:[
                            {
                                file:"{{$media->akamai_details["stream_success_html5"]}}?{{$token}}",
                                "default": true
                            },
                            {
                                file:"{{$media->akamai_details["stream_success_flash"]}}?{{$token}}",
                                provider: "{{URL::to('portal/theme/default/plugins/jwplayer-7.7.4/akamai/AkamaiAdvancedJWStreamProvider.swf')}}"
                            }
                        ]
                        @if(file_exists(config("app.dams_video_thumb_path").$media->unique_name.".png"))
                            ,image: "{{URL::to("/media_image/".$media->_id."?compress=1")}}"
                        @endif
                        @if(isset($media->srt_location) && file_exists($media->srt_location))
                            ,tracks: [{
                                file: "{{URL::to('/cp/dams/video-srt/'.$media->_id)}}", 
                                label: "English",
                                kind: "captions",
                                'default': 'true'
                            }]
                        @endif
                    }],
                    primary: "html5",
                    androidhls: "true",
                    fallback: "true",
                    aspectratio: "{{ $aspectratio }}",
                    width: "100%"        
                });
                jwplayer().onReady(function(){ //play button fix
                    //$(document).on("mousedown", '.jw-icon-display',function(){
                    $('.jw-icon-display').mousedown(function() {
                        if(/Google Inc/.test(navigator.vendor)) {
                            jwplayer().play();
                        }
                    });
                });
                jwplayer().onError(function(evt) {
                    callAjaxToCreateNewToken("{{$media->_id}}","{{$for_id }}");
                });

            } else {
                $('.wrapper-video{{$for_id }}').append("<span>{{ trans('admin/program.errormsg_for_installing_flash_player') }} <a id='link' href='{{ URL::to(config('app.download_flash_player')) }}' >{{ trans('admin/program.click_here') }}</a></span>");
                $("#link").click(function() {
                    $("#link").attr('target', '_blank');
                    return true;
                });
            }
        </script>
    </div>
</div>
   @elseif (!isset($media->akamai_details['code']) || $media->akamai_details['code'] != 200) 

            <?php echo "Error in syncing the file. Please contact"; ?>
    @else
            <?php echo "File is being proccessed please wait."; ?>
@endif