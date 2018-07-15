<?php
if ($media->asset_type == "file") {
    ?>
    @if(in_array(strtolower($media->file_extension), ['mp3', 'aac']))
        @include('media._audio', ['media' => $media])
    @else
    <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12 download-align custom-box center xs-margin">
        <a href="{{URL::to('/media_image/'.$media->_id)}}"><img src="{{URL::asset($theme.'/img/packetpage_audio.png')}}" class="img-responsive" alt="Audio image"></a>
    </div>
    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
        @if(!empty($media->description))
            <p><strong>{{ Lang::get('program.description') }}</strong></p>
            <div id="demo">
                <article>{!! $media->description !!}</article>
            </div>
        @endif
    </div>
    @endif
    <?php
} else { ?>
        <h4>{{ Lang::get('program.to_listen_audio') }}</h4>
        <!--  <h4>{{ Lang::get('program.no_preview_available') }}</h4> -->
        <a href="{{$media->url}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
        <br><br>
        @if(!empty($media->description))
            <p><strong>{{ Lang::get('program.description') }}</strong></p>
            <div id="demo">
                <article>{!! $media->description !!}</article>
            </div>
        @endif
    <?php
}