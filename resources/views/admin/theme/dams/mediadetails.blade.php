@if (isset($asset) && !empty($asset))
<div class="row">
        <div class="col-md-12">
            <div class="col-md-6">
                <table class="table table-bordered" style="table-layout: fixed;word-wrap: break-word;">
                    <tr>
                        <th>{{ trans('admin/dams.media_type')}}</th>
                        <td>{!! ucfirst($asset->type) !!}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('admin/dams.title')}}</th>
                        <td>{!! ucfirst($asset->name) !!}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('admin/dams.description')}}</th>
                        <td>{!! ucfirst($asset->description) !!}</td>
                    </tr>
                    <tr>
                        <th>Keywords / Tags</th>
                        <td>{!! implode(',', $asset->tags) !!}</td>
                    </tr>
                    <tr>
                        <th>{{ trans('admin/dams.visibility')}} </th>
                        <td>{!! ucfirst($asset->visibility) !!}</td>
                    </tr>
                    <tr>
                        <th>Status</th>
                        <td>{!! $asset->status !!}</td>
                    </tr>
                    <?php
                    switch ($asset->type) {
                        case "video" : { ?>
                            <?php if (isset($asset->video_status)) { ?>
                                <tr>
                                    <th>{{ trans('admin/dams.video_status')}}</th>
                                    <td>
                                        @if (in_array($asset->video_status, ['INTEMP']))
                                            <span class="label">{{ trans("admin/dams.pending") }}</span>
                                        @elseif (in_array($asset->video_status, ['UPLOADING', 'UPLOADED']))
                                            <span class="label label-info">{{ trans("admin/dams.processing") }}</span>
                                        @elseif (in_array($asset->video_status, ['READY']))
                                            <span class="label label-success">{{ trans("admin/dams.ready") }} </span>
                                        @endif
                                    </td>
                                </tr>
                            <?php } ?>
                            <?php if (isset($asset->srt_status)) { ?>
                                <tr>
                                    <th>{{ trans('admin/dams.srt_status')}}</th>
                                    <td>{!! '<span class="label label-inverse">'.$asset->srt_status.'</span>' !!}</td>
                                </tr>
                            <?php } ?>
                            <?php
                            break;
                        }
                    }
                    ?>
                </table>
            </div>
            <div class="col-md-6">
                <?php
                
                switch ($asset->type) {
                    case "video" : {
                        if ($asset->asset_type == "file") {
                            if (isset($asset->akamai_details)) {
                                if (isset($asset->akamai_details['delivery_html5_url'])) {
                                    ?>
                                        <script src="{{URL::to('admin/js/jwplayer7/jwplayer.js')}}"></script>
                                        <script type="text/javascript">
                                        jwplayer.key = '{{ config('app.jwplayer.key') }}';
                                        </script>
                                        <div class="wrapper-video">
                                        <div class="h_iframe">
                                        <script type='text/javascript'>     
                                        var isFlashInstalled = (function(){
                                        var b=new function(){var n=this;n.c=!1;var a="ShockwaveFlash.ShockwaveFlash",r=[{name:a+".7",version:function(n){return e(n)}},{name:a+".6",version:function(n){var a="6,0,21";try{n.AllowScriptAccess="always",a=e(n)}catch(r){}return a}},{name:a,version:function(n){return e(n)}}],e=function(n){var a=-1;try{a=n.GetVariable("$version")}catch(r){}return a},i=function(n){var a=-1;try{a=new ActiveXObject(n)}catch(r){a={activeXError:!0}}return a};n.b=function(){if(navigator.plugins&&navigator.plugins.length>0){var a="application/x-shockwave-flash",e=navigator.mimeTypes;e&&e[a]&&e[a].enabledPlugin&&e[a].enabledPlugin.description&&(n.c=!0)}else if(-1==navigator.appVersion.indexOf("Mac")&&window.execScript)for(var t=-1,c=0;c<r.length&&-1==t;c++){var o=i(r[c].name);o.activeXError||(n.c=!0)}}()};  
                                        return b.c;
                                        })();
                                        if(isFlashInstalled){
                                         $('.wrapper-video').append("<div id='akamai_player_{{$asset->id}}'></div>");
                                         jwplayer("akamai_player_{{$asset->id}}").setup({
                                        playlist:[{
                                            sources:[
                                                {
                                                    file:'{{$asset->akamai_details['delivery_html5_url']}}?{{$token}}',
                                                    'default': 'true',
                                                },
                                                {
                                                    file:'{{$asset->akamai_details['delivery_flash_url']}}?{{$token}}',
                                                    provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.8/AkamaiAdvancedJWStreamProvider.swf'
                                                }
                                            ]
                                            @if(file_exists(config('app.dams_video_thumb_path').$asset->unique_name.".png"))
                                                ,image: "{{URL::to('/media_image/'.$asset->_id."?compress=1")}}"
                                            @endif
                                            @if(isset($asset->srt_location) && file_exists($asset->srt_location))
                                                ,tracks: [{
                                                    file: "{{URL::to('/cp/dams/video-srt/'.$asset->_id)}}", 
                                                    label: "English",
                                                    kind: "captions",
                                                    'default': 'true'
                                                }]
                                            @endif
                                        }],
                                        primary: 'html5',
                                        androidhls: 'true',
                                        fallback: 'true',
                                        width: '100%',
                                        aspectratio: '16:9'
                                        });
                                        jwplayer().onError(function(evt) {
                                        console.log(evt.message);
                                        // call an ajax call to check if we can create a new token after error
                                        callAjaxToCreateNewToken("{{$asset->_id}}","akamai_player_{{$asset->id}}");
                                        });
                                             
                                           
                                        }else{
                                            $('.wrapper-video').append("<span>{{ trans('admin/program.errormsg_for_installing_flash_player') }} <a id='link' href='{{ URL::to(config('app.download_flash_player')) }}' >{{ trans('admin/program.click_here') }}</a></span>");
                                                $("#link").click(function() {
                                                $("#link").attr('target', '_blank');
                                                return true;
                                                });
                                        }
                                        </script>

                                        </div>
                                        </div>
                                        <a href="#orignalvideo" id="showoriginal" > {{ trans('admin/dams.show_original')}}</a>
                                        <script>
                                        $('#showoriginal').click(function(e){
                                            e.preventDefault();
                                            $($(this).attr('href')).slideToggle();
                                        })
                                        </script>
                                    <?php
                                    if (isset($asset->akamai_details['stream_success_html5'])) { ?>
                                                <style>
                                                    #akamai_player_original_wrapper{
                                                        width:100% !important;
                                                    }
                                                </style>
                                                <div id="orignalvideo" style="display:none;">
                                                    <script src="{{URL::to('admin/js/jwplayer7/jwplayer.js')}}"></script>
                                                    <script type="text/javascript">
                                                        jwplayer.key="{{config('app.jwplayer.key')}}";
                                                    </script>

                                    <div class="wrapper-video1">
                                        <div class="h_iframe1">
	                                        <script type='text/javascript'>     
	                                        var isFlashInstalled = (function(){
	                                        var b=new function(){var n=this;n.c=!1;var a="ShockwaveFlash.ShockwaveFlash",r=[{name:a+".7",version:function(n){return e(n)}},{name:a+".6",version:function(n){var a="6,0,21";try{n.AllowScriptAccess="always",a=e(n)}catch(r){}return a}},{name:a,version:function(n){return e(n)}}],e=function(n){var a=-1;try{a=n.GetVariable("$version")}catch(r){}return a},i=function(n){var a=-1;try{a=new ActiveXObject(n)}catch(r){a={activeXError:!0}}return a};n.b=function(){if(navigator.plugins&&navigator.plugins.length>0){var a="application/x-shockwave-flash",e=navigator.mimeTypes;e&&e[a]&&e[a].enabledPlugin&&e[a].enabledPlugin.description&&(n.c=!0)}else if(-1==navigator.appVersion.indexOf("Mac")&&window.execScript)for(var t=-1,c=0;c<r.length&&-1==t;c++){var o=i(r[c].name);o.activeXError||(n.c=!0)}}()};  
	                                        return b.c;
	                                            })();
	                                        if(isFlashInstalled){
	                                             $('.wrapper-video1').append("<div id='akamai_player_original'></div>");
	                                                                                                       
	                                                        jwplayer("akamai_player_original").setup({
	                                                            playlist:[{
	                                                                sources:[
	                                                                    {
	                                                                        file:'{{$asset->akamai_details['stream_success_html5']}}?{{$token}}',
	                                                                        'default': 'true',
	                                                                        
	                                                                    },
	                                                                    {
	                                                                        file:'{{$asset->akamai_details['stream_success_flash']}}?{{$token}}',
	                                                                        provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.8/AkamaiAdvancedJWStreamProvider.swf',
	                                                                        
	                                                                    }
	                                                                ]
	                                                                @if(file_exists(config('app.dams_video_thumb_path').$asset->unique_name.".png"))
	                                                                    ,image: "{{URL::to('/media_image/'.$asset->_id."?raw=yes")}}"
	                                                                @endif
	                                                                @if(isset($asset->srt_location) && file_exists($asset->srt_location))
	                                                                    ,tracks: [{
	                                                                        file: "{{URL::to('/cp/dams/video-srt/'.$asset->_id)}}", 
	                                                                        label: "English",
	                                                                        kind: "captions",
	                                                                        'default': 'true'
	                                                                    }]
	                                                                @endif
	                                                            }],
	                                                            primary: 'html5',
	                                                            hlshtml: 'true',
	                                                            androidhls: 'true',
	                                                            fallback: 'true',
	                                                            width: '100%',
	                                                            aspectratio: '16:9'
	                                                        });
	                                                        jwplayer().onError(function(evt) {
	                                                        // after token expiry, on reload call the ajax                                                      
	                                                        callAjaxToCreateNewToken("{{$asset->_id}}",'akamai_player_original');
	                                                            
	                                                        });
	                                        }else{
	                                                $('.wrapper-video1').append("<span>{{ trans('admin/program.errormsg_for_installing_flash_player') }} <a id='link1' href='{{ URL::to(config('app.download_flash_player')) }}' >{{ trans('admin/program.click_here') }}</a></span>");
                                                        $("#link1").click(function() {
                                                        $("#link1").attr('target', '_blank');
                                                    return true;
                                                    });

	                                            }
                                    </script>
                                </div>
                                </div>
                                    <?php                                                                         }                         
                                } elseif (isset($asset->akamai_details['stream_success_html5'])) { ?>
                                	<script src="{{URL::to('admin/js/jwplayer7/jwplayer.js')}}"></script>
                                    <script type="text/javascript">
                                        jwplayer.key="{{config('app.jwplayer.key')}}";
                                	</script>
                                    <div class="wrapper-video1">
                                        <div class="h_iframe1">
                                            <script type='text/javascript'>     
                                            var isFlashInstalled = (function(){
                                            var b=new function(){var n=this;n.c=!1;var a="ShockwaveFlash.ShockwaveFlash",r=[{name:a+".7",version:function(n){return e(n)}},{name:a+".6",version:function(n){var a="6,0,21";try{n.AllowScriptAccess="always",a=e(n)}catch(r){}return a}},{name:a,version:function(n){return e(n)}}],e=function(n){var a=-1;try{a=n.GetVariable("$version")}catch(r){}return a},i=function(n){var a=-1;try{a=new ActiveXObject(n)}catch(r){a={activeXError:!0}}return a};n.b=function(){if(navigator.plugins&&navigator.plugins.length>0){var a="application/x-shockwave-flash",e=navigator.mimeTypes;e&&e[a]&&e[a].enabledPlugin&&e[a].enabledPlugin.description&&(n.c=!0)}else if(-1==navigator.appVersion.indexOf("Mac")&&window.execScript)for(var t=-1,c=0;c<r.length&&-1==t;c++){var o=i(r[c].name);o.activeXError||(n.c=!0)}}()};  
                                            return b.c;
                                                })();
                                            if(isFlashInstalled){
                                                 $('.wrapper-video1').append("<div id='akamai_player_{{$asset->id}}'></div>");
                                                 jwplayer("akamai_player_{{$asset->id}}").setup({
                                                playlist:[{
                                                    sources:[
                                                        {
                                                            file:'{{$asset->akamai_details['stream_success_html5']}}?{{$token}}',
                                                            'default': 'true'
                                                        },
                                                        {
                                                            file:'{{$asset->akamai_details['stream_success_flash']}}?{{$token}}',
                                                            provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.8/AkamaiAdvancedJWStreamProvider.swf'
                                                        }
                                                    ]
                                                    @if(file_exists(config('app.dams_video_thumb_path').$asset->unique_name.".png"))
                                                        ,image: "{{URL::to('/media_image/'.$asset->_id."?raw=yes")}}"
                                                    @endif
                                                    @if(isset($asset->srt_location) && file_exists($asset->srt_location))
                                                        ,tracks: [{
                                                            file: "{{URL::to('/cp/dams/video-srt/'.$asset->_id)}}", 
                                                            label: "English",
                                                            kind: "captions",
                                                            'default': 'true'
                                                        }]
                                                    @endif
                                                }],
                                                primary: 'html5',
                                                androidhls: 'true',
                                                fallback: 'true',
                                                width: '100%',
                                                aspectratio: '16:9'
                                            });
                                            jwplayer().onError(function(evt) {
                                               // after token expiry, on reload call the ajax
                                                 callAjaxToCreateNewToken("{{$asset->_id}}","akamai_player_{{$asset->id}}");
                                            });
                                                    
                                            }else{
                                                    $('.wrapper-video1').append("<span>{{ trans('admin/program.errormsg_for_installing_flash_player') }} <a id='link' href='{{ URL::to(config('app.download_flash_player')) }}' >{{ trans('admin/program.click_here') }}</a></span>");
                                                        $("#link").click(function() {
                                                        $("#link").attr('target', '_blank');
                                                    return true;
                                                    });

                                                }
                                        </script>
                                        </div>
                                    </div>
                            <?php	                                } elseif (!isset($asset->akamai_details['code']) || $asset->akamai_details['code'] != 200) {
                                    echo "Error in syncing the file. Please contact";
} else {
    echo "File is being proccessed.... Please wait.....";
}
                            } else {
                                echo "File is not synced with video server";
                            }
                        } else { ?>
                                @if($asset->is_youtube[1])
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
                                        {!! $asset->youtube_embed_code !!}   
                                    </div>
                                @elseif ($asset->is_ted !== false)
                                    <style type="text/css">
                                        .ted-video-container {
                                            position:relative;
                                            padding-bottom:56.25%;
                                            height:0;
                                            overflow:hidden;
                                        }
                                        .ted-video-container iframe {
                                            position:absolute;
                                            top:0;
                                            left:0;
                                            width:100%;
                                            min-height:230px;
                                            height: 100%;
                                        }
                                    </style>
                                    <div class="ted-video-container">
                                        {!! $asset->ted_embed_code !!}
                                    </div>
                                @elseif ($asset->is_vimeo)
                                    <style type="text/css">
                                        .vimeo-video-container {
                                            position:relative;
                                            padding-bottom:56.25%;
                                            height:0;
                                            overflow:hidden;
                                        }
                                        .vimeo-video-container iframe {
                                            position:absolute;
                                            top:0;
                                            left:0;
                                            width:100%;
                                            min-height:230px;
                                            height: 100%;
                                        }
                                    </style>
                                    <div class="vimeo-video-container">
                                        {!! $asset->vimeo_embed_code !!}
                                    </div>
                                @else
                                <h3>{{ trans('admin/dams.preview_error')}}</h3>
                                <h5>{{ trans('admin/dams.visit_another_link_note')}}</h5><br />
                                <a href="{{$asset->youtube_embed_code}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/dams.go_to')}}</button></a>
                                @endif
                                <?php
                        }

                            break;
                    }
                    case "image" :{
                        if ($asset->asset_type == "file") {
                            ?>
                                <img style="margin-top: 6px !important;" src="{!! URL::to('/cp/dams/show-media/'.$asset->_id) !!}" width="400" /> 
                            <?php
                        } else { ?>
                                    <h3>{{ trans('admin/dams.preview_error')}}</h3>
                                    <h5>{{ trans('admin/dams.visit_another_link_note')}}</h5><br />
                                    <a href="{{$asset->url}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/dams.go_to')}}</button></a>
                                <?php
                        }
                            break;
                    }
                    case "document" :{
                        if ($asset->asset_type == "file") {
                            ?>
                                <h3>{{ trans('admin/dams.preview_error')}}</h3>
                                <h5>{{ trans('admin/dams.download_doc_note')}}</h5><br />
                                <a href="{{URL::to('/cp/dams/show-media/'.$asset->_id)}}"><button class="btn btn-primary">{{ trans('admin/dams.download')}}</button></a>
                            <?php
                        } else { ?>
                                    <h3>{{ trans('admin/dams.preview_error')}}</h3>
                                    <h5>{{ trans('admin/dams.visit_another_link_note')}}</h5><br />
                                    <a href="{{$asset->url}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/dams.go_to')}}</button></a>
                                <?php
                        }
                            break;
                    }
                    case "scorm" :{
                    ?>
                        <script src="{{ asset("portal/theme/default/js/scorm_api.js") }}"></script>
                        <script>
                            window.API = getScormAPI({}, {scorm_runtime_activity_data : {}});
                        </script>
                    <?php
                        if (!empty($asset->launch_file)) {
                            $scorm_file_name = "/".$asset->launch_file;
                        } else {
                            $scorm_file_name = config("app.scorm_file_name");
                        }

                        if ($asset->visibility == 'public') {
                            $file_location = $asset->public_file_location;
                        } else {
                            $file_location = $asset->private_file_location;
                        }

                        if ($asset->asset_type == "file") {
                            ?>
                            <a target="_blank" href="{{URL::to($file_location.$scorm_file_name)}}"><button class="btn btn-primary center-block" style="margin-top:25%;">{{ trans('admin/dams.scorm_view_text')}}</button></a>
                            <?php
                        } else { ?>
                                    <h3>{{ trans('admin/dams.preview_error')}}</h3>
                                    <h5>{{ trans('admin/dams.visit_another_link_note')}}</h5><br />
                                    <a href="{{URL::to($file_location.$scorm_file_name)}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/dams.go_to')}}</button></a>
                                <?php
                        }
                            break;
                    }
                    case "audio" :{
                        if ($asset->asset_type == "file") {
                            ?>
                                <script src="{{URL::to('admin/js/jwplayer7/jwplayer.js')}}"></script>
                                <script type="text/javascript">jwplayer.key="{{config('app.jwplayer.key')}}";</script>
                                <div id="akamai_player" ></div>
                                <style>
                                    #akamai_player{
                                        margin-top: 8px;
                                        width:100% !important;
                                    }
                                    /*#akamai_player_controlbar{
                                        display: inline-block;
                                    }*/
                                </style>
                                <script type="text/javascript">
                                    jwplayer("akamai_player").setup({
                                        @if($asset->visibility == "public")
                                            file:'{{URL::to(config('app.public_dams_audio_path').$asset->unique_name_with_extension)}}',
                                        @else
                                            file:'{{URL::to('/cp/dams/show-media/'.$asset->_id.".mp3")}}',
                                        @endif
                                        primary: "html5",
                                        androidhls: "true",
                                        fallback: "true",
                                        height: 30
                                    });
                                </script>
                                <?php
                        } else { ?>
                                    <h3>{{ trans('admin/dams.preview_error')}}</h3>
                                    <h5>{{ trans('admin/dams.visit_another_link_note')}}</h5><br />
                                    <a href="{{$asset->url}}" target="_blank"><button class="btn btn-primary">{{ trans('admin/dams.go_to')}}</button></a>
                                <?php
                        }
                            break;
                    }
                }
                ?>
            </div>
        </div>
</div>
<script type='text/javascript' src="/admin/js/akamaiRegenerateToken.js" ></script>
@else
<div class="alert alert-danger">
    <button class="close" data-dismiss="alert">×</button>
    {{ $message }}
    <script>
        var reloadMediaList = function () {
            var parentWindow = window.parent;
            if(parentWindow !== undefined){
                if (parentWindow.location.href === "{{ URL::to("/cp/dams/list-media") }}") {
                    parentWindow.location.reload();
                }
            }
        };

        window.setTimeout(reloadMediaList, 3000);
    </script>
</div>
@endif
