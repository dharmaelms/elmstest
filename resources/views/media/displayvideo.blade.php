<?php
if ($media->asset_type == "file") {
    if (isset($media->akamai_details)) {
?>
    @include('media._video', ['aspectratio' => isset($aspectratio) ? $aspectratio : '16:9' ])
        <?php
        //elseif(isset($media->akamai_details['code']) && $media->akamai_details['code'] == 200 && isset($media->akamai_details['stream_success_flash'])){}
    } else {
            echo "File is not synced with Video Server";
    }?>
        @if(!empty($media->description))
            <p><strong>{{ Lang::get('program.description') }}</strong></p>
                <div id="demo">
                    <article>{!! $media->description !!}</article>
                </div>
        @endif
        <?php  } else { ?>
        @if(isset($media->is_youtube[1]))
            <style type="text/css">
                .youtube-video-container {
                    position:relative;
                    padding-bottom:56.25%;
                    padding-top:30px;
                    height:0;
                    overflow:hidden;
                }
                .youtube-video-container iframe {
                    position:absolute;
                    top:0;
                    left:0;
                    width:100%;
                    height:100%;
                }
            </style>
                <div class="youtube-video-container">
                    {!! $media->youtube_embed_code !!}
                </div>
                @if(!empty($media->description))
                    <br><p><strong>{{ Lang::get('program.description') }}</strong></p>
                        <div id="demo">
                            <article>{!! $media->description !!}</article>
                        </div>
                @endif
        @elseif ($media->is_ted !== false)
            <style type="text/css">
                .ted-video-container {
                    position:relative;
                    padding-bottom:56.25%;
                    height:0;
                    overflow:hidden;
                }
                .ted-video-container iframe {
                    height: 360px;
                    width: 100%;
                }
            </style>
            <div class="ted-video-container">
                {!! $media->ted_embed_code !!}
            </div>
            @if(!empty($media->description))
                <br>
                <p><strong>{{ Lang::get('program.description') }}</strong></p>
                <div id="demo">
                    <article>{!! $media->description !!}</article>
                </div>
            @endif
        @elseif ($media->is_vimeo) 
            <style type="text/css">
                .vimeo-video-container {
                    position:relative;
                    padding-bottom:56.25%;
                    height:0;
                    overflow:hidden;
                }
                .vimeo-video-container iframe {
                    height: 360px;
                    width: 100%;
                }
            </style>
            <div class="vimeo-video-container">
                {!! $media->vimeo_embed_code !!}
            </div>
            @if(!empty($media->description))
                <br>
                <p><strong>{{ Lang::get('program.description') }}</strong></p>
                <div id="demo">
                    <article>{!! $media->description !!}</article>
                </div>
            @endif
        @else
            <h4>{{ Lang::get('program.to_view_video') }}</h4>
                <a href="{{$media->url}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
            <br><br>
            @if(!empty($media->description))
                <p><strong>{{ Lang::get('program.description') }}</strong></p>
                <div id="demo">
                    <article>{!! $media->description !!}</article>
                </div>
            @endif
        @endif
    <?php } ?>