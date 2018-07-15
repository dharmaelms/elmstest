@section('content')
    <?php use App\Model\PacketFaqAnswers; use App\Model\Dam; use App\Model\Quiz; use App\Model\Event; use App\Model\MyActivity; use App\Model\FlashCard; ?>
   <style type="text/css">
.wrapper-video, #akamai_player_wrapper {width:100%;height:100%;margin:0 auto;}
.h_iframe        {position:relative;}
.h_iframe .ratio {display:block;width:100%;height:auto;}
.h_iframe iframe, .h_iframe object {position:absolute;top:0;left:0;width:100%; height:100%;}
.faq_inactive{
    display:none;
}
.faq_active{
    display:block;
}
.accordion .panel .panel-title .accordion-toggle {
    padding: 4px 15px;
}
.bb-bookblock{
    width: 700px;
}
.bb-custom-wrapper{
    margin-left: 0;
    margin-right: 0;
    width: 630px;
}
</style>

    <!-- BEGIN PAGE HEADER-->
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><a href="{{url('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
            <li><a href="{{url('program/my-feeds')}}">My <?php echo Lang::get('program.programs');?></a><i class="fa fa-angle-right"></i></li>
            <li><a href="{{url('program/packets/'.$packet['feed_slug'])}}">{{str_limit(ucwords($channel_name), $limit = 50, $end = '...')}}</a><i class="fa fa-angle-right"></i></li>
            <li><a href="#">{{str_limit(ucwords($packet['packet_title']), $limit = 50, $end = '...')}}</a></li>
        </ul>
    </div>
    <!-- END PAGE HEADER-->
    <!--content starts here-->
    @if(!$inactive)
        <div class="row">
            <div class="col-lg-8 col-md-8 col-sm-8 col-xs-12">
                <!--packet item timeline for mobile-->
                    <div id="panel-mob" class="panel-mob">
                        <!--pocket items starts here-->
                        <div class="pkt-items">
                            <div class="panel panel-default">
                                <div class="center border-btm">
                                    <a type="button" href="{{URL::to('program/packets/'.$packet['feed_slug'])}}" class="btn red-sunglo xs-margin btn-sm">View All {{Lang::get('program.packets')}}</a>
                                </div>
                                @if($packet['sequential_access'] == 'yes')
                                    <!-- <em class="font-13">(Sequential)</em> -->
                                    <?php $sequential="timeline"; ?>
                                @else
                                    <?php $sequential=""; ?>
                                @endif
                                <div class="panel-body">
                                    <div class="{{$sequential}}">
                                    <!-- TIMELINE ITEM -->
                                    <?php 
                                        if(!empty($elements))
                                        {
                                            $le=0;
                                            $last_element_id=$elements[0]['id']; 
                                            $last_element_type=$elements[0]['type'];
                                        }   
                                    ?>
                                    @foreach($elements as $element)
                                    <?php $le=1; ?>
                                        <?php 
                                            switch ($element['type']) {
                                                case 'media':
                                                {
                                                    $element_asset = Dam::getDAMSAssetsUsingAutoID((int)$element['id']);
                                                    if(isset($element_asset[0]))
                                                    {
                                                        $element_asset = $element_asset[0];
                                                        $name=$element_asset['name'];
                                                        $size='Type:'.$element_asset['type'];
                                                        if($element_asset['type'] == 'video')
                                                        {
                                                            $class='fa-play-circle';
                                                        }
                                                        elseif($element_asset['type'] == 'image')
                                                        {
                                                            $class='fa-picture-o';
                                                        }
                                                        elseif($element_asset['type'] == 'document')
                                                        {
                                                            $class='fa-file-text-o';
                                                        }
                                                        elseif($element_asset['type'] == 'scorm')
                                                        {
                                                            $class='fa-film';
                                                        }
                                                        else
                                                        {
                                                            $class='fa-volume-down';
                                                        }
                                                    }
                                                    
                                                    break;
                                                }
                                                case 'assessment':
                                                {
                                                    $element_asset = Quiz::getQuizAssetsUsingAutoID($element['id']);
                                                    if(isset($element_asset[0]))
                                                    {
                                                        $element_asset = $element_asset[0];
                                                        $name=$element_asset['quiz_name'];
                                                        $size='Dur:'.$element_asset['duration'];
                                                        $class='fa-edit';
                                                    }
                                                    break;
                                                }
                                                case 'event':
                                                {
                                                    $element_asset = Event::getEventsAssetsUsingAutoID($element['id']);
                                                    if(isset($element_asset[0]))
                                                    {
                                                        $element_asset = $element_asset[0];
                                                        $name=$element_asset['event_name'];
                                                        $size='Type:'.$element_asset['event_type'];
                                                        $class='fa-calendar';
                                                    }
                                                    break;
                                                }
                                                case 'flashcard':
                                                {
                                                    $element_asset = FlashCard::getFlashcardsAssetsUsingAutoID($element['id']);
                                                    if(isset($element_asset[0]))
                                                    {
                                                        $element_asset = $element_asset[0];
                                                        $name=$element_asset['title'];
                                                        $size='Type: FlashCard';
                                                        $class='fa-star';
                                                    }
                                                    break;
                                                }
                                            }

                                            $activity=MyActivity::pluckElementActivity($packet['packet_id'], $element['id'], $element['type']);
                                            if(($asset['element_type'] == $element['type']) && ($asset['id'] == $element['id']))
                                            {
                                                $seen_class='pkt-item-progress';
                                            }
                                            elseif(!empty($activity))
                                            {
                                                $seen_class='pkt-item-blue';
                                            }
                                            else
                                            {
                                                $seen_class='pkt-item-gray';
                                            }
                                        ?>
                                        <div class="timeline-item">
                                            <div class="timeline-badge">
                                                <div class="{{$seen_class}}"><i class="fa {{$class}}"></i></div>
                                            </div>
                                            <div class="timeline-body transparent-bg">                      
                                                <div class="timeline-body-head">
                                                    <div class="timeline-body-head-caption">
                                                        <div href="javascript:;" class="timeline-body-title font-blue-madison">
                                                            <?php $last_element_activity=MyActivity::pluckElementActivity($packet['packet_id'], $last_element_id, $last_element_type); ?>
                                                            @if($packet['sequential_access'] == 'yes' && empty($last_element_activity))
                                                                <a title="Sequential elements should be accessed with order">{{$name}}</a>
                                                            @else
                                                                <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/element/'.$element['id'].'/'.$element['type'])}}">{{$name}}</a>
                                                            @endif
                                                        </div>
                                                        <span class="timeline-body-time font-grey-cascade"><i>{{$size}}</i></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <?php 
                                            $last_element_id=$element['id']; $last_element_type=$element['type'];
                                        ?>
                                    @endforeach
                                    <!-- END TIMELINE ITEM -->
                                    </div>          
                                </div>
                            </div>
                        </div>
                        <!--pocket items end here-->    
                    </div>
                    <a id="pkt-trigger" class="pkt-trigger">
                        <i class="fa fa-arrow-circle-left"></i>&nbsp;{{count($elements)}}
                                @if(count($elements) > 1) 
                                    Items 
                                @else 
                                    Item 
                                @endif 
                    </a>    
                <!--end here-->


                <div id="accordion1" class="panel-group accordion">
                    <div class="panel panel-default transparent-bg">
                        <div class="panel-heading">
                            <h4 class="panel-title m-btm-12">
                                <div class="row">
                                    <div class="col-md-10">
                                        <span class="caption gray">{{$packet['packet_title']}}&nbsp;&nbsp;</span>
                                        @if(in_array($packet['packet_id'], $favorites))
                                            <?php 
                                                $action="unfavourite";
                                                $class="red"; 
                                            ?>
                                        @else
                                            <?php 
                                                $action="favourite";
                                                $class="gray";
                                            ?>
                                        @endif
                                        <span id="favourite">
                                            <i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}} fav-packet" style="cursor:pointer"></i>
                                        </span>
                                        <span class="font-grey-cascade">&nbsp;&nbsp;|&nbsp;<em class="font-13">{{ Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))}}</em></span>
                                    </div>
                                    @if(!empty($asset))
                                        <?php $collapse=""; $collapse_class="collapsed"; ?>
                                    @else
                                        <?php $collapse="in"; $collapse_class=""; ?>
                                    @endif
                                    <div class="col-md-2">
                                        <a class="accordion-toggle accordion-toggle-styled {{$collapse_class}}" data-toggle="collapse" data-parent="#accordion1" href="#accordion1_1" aria-expanded="false">
                                        <span class="description font-grey-cascade">Description</span>
                                        </a>
                                    </div>
                                </div>
                            </h4>
                        </div>
                        <div id="accordion1_1" class="panel-collapse collapse {{$collapse}}" aria-expanded="false" >
                            <div class="panel-body">{!! $packet['packet_description'] !!}</div>
                        </div>
                    </div>
                </div>



                @if(!empty($asset))
                    <div class="panel panel-default">
                        <div class="panel-heading sequential-panel-header">
                            {{$asset['name']}}&nbsp;&nbsp;
                            @if(isset($asset['users_liked']) && in_array(Auth::user()->uid, $asset['users_liked']))
                                <?php 
                                    $action="unstar";
                                    $class="yellow"; 
                                ?>
                            @else
                                <?php 
                                    $action="star";
                                    $class="gray"; 
                                ?>
                            @endif
                            <span id="element-like">
                                <i id="{{$asset['id']}}" data-action="{{$action}}" data-type="{{$asset['element_type']}}" data-packet="{{$packet['packet_id']}}" class="fa fa-star {{$class}} star-element" style="cursor:pointer"></i>
                            </span>
                            <div class="page pull-right">
                                <?php $element_i=0;$elemnt_count=count($elements); 
                                    $original_elements=$elements;
                                ?>
                                @foreach($elements as $element)
                                    <?php $element_i=$element_i+1; ?>
                                    @if(($asset['element_type'] == $element['type']) && ($asset['id'] == $element['id']))
                                        @if($element_i == 1)
                                            @if($element_i != $elemnt_count)
                                                <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i]['id'].'/'.$original_elements[$element_i]['type'])}}"><button>Next</button></a>  
                                            @endif
                                        @elseif($element_i == $elemnt_count)
                                            <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i-2]['id'].'/'.$original_elements[$element_i-2]['type'])}}"><button>Prev</button></a>
                                        @else
                                            <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i-2]['id'].'/'.$original_elements[$element_i-2]['type'])}}"><button>Prev</button></a>
                                            <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/from/'.$original_elements[$element_i]['id'].'/'.$original_elements[$element_i]['type'])}}"><button>Next</button></a>
                                        @endif
                                    @endif
                                @endforeach
                            </div>
                        </div>
                        <div class="panel-body">
                            @if(!empty($asset) && $asset['element_type'] == "media")
                                <?php switch($asset['type']){ 
                                        case "video" : { 
                                            if($asset['asset_type'] == "file"){
                                                if(isset($kaltura) && isset($asset['kaltura_details']['id'])){
                                                    if(isset($asset['kaltura_details']['rootEntryId']) && $asset['kaltura_details']['id'] == 1) // These two lines are a fix for a bug in which mongo was returning entry_id as 1. Added by cerlin.
                                                        $asset['kaltura_details']['id'] = $asset['kaltura_details']['rootEntryId'];
                                                ?>
                                                <div class="wrapper-video">
                                                    <div class="h_iframe">
                                                        <img class="ratio" src="{{URL::asset($theme.'/img/16x9.png')}}" style="display:none"/>
                                                        <object id="kaltura_player" name="kaltura_player" type="application/x-shockwave-flash" allowFullScreen="true" allowNetworking="all" allowScriptAccess="always" class="xs-margin" rel="media:audio" resource="<?php echo $kaltura ?><?php echo $asset['kaltura_details']['id'] ?>" data="<?php echo $kaltura ?><?php echo $asset['kaltura_details']['id'] ?>">
                                                            <param name="allowFullScreen" value="true" />
                                                            <param name="allowNetworking" value="all" />
                                                            <param name="allowScriptAccess" value="always" />
                                                            <param name="bgcolor" value="#000000" />
                                                            <param name="flashVars" value="" />
                                                            <param name="movie" value="<?php echo $kaltura ?><?php echo $asset['kaltura_details']['id'] ?>"/>
                                                            <span property="dc:description" content=""></span>
                                                            <span property="media:title" content="Kaltura Video"></span>
                                                            <span property="media:type" content="application/x-shockwave-flash"></span> 
                                                        </object>
                                                    </div>
                                                </div>
                                                <?php
                                                }
                                                elseif(isset($asset['akamai_details'])){
                                                    if(isset($asset['akamai_details']['delivery_html5_url'])){ ?>
                                                        <script src="{{URL::to('/portal/theme/default/plugins/jwplayer7/jwplayer.js')}}"></script>
                                                        <script type="text/javascript">
                                                            jwplayer.key = '{{ config('app.jwplayer.key') }}';
                                                            var AKAMAI_MEDIA_ANALYTICS_CONFIG_FILE_PATH = '{{ config('app.akamai_analytics.html5.config_file') }}';
                                                        </script>
                                                        <script src="{{URL::to('/portal/theme/default/plugins/jwplayer7/akamai/AkamaiJWPlayerLoader.js')}}"></script>
                                                        <script src="http://79423.analytics.edgesuite.net/js/csma.js"></script>
                                                        <div class="wrapper-video">
                                                            <div class="h_iframe">
                                                                <img class="ratio" src="{{URL::asset($theme.'/img/16x9.png')}}" style="display:none">
                                                                <div id="akamai_player" class="xs-margin"></div>
                                                            </div>
                                                        </div>
                                                        <script type="text/javascript">
                                                            jwplayer('akamai_player').setup({
                                                                playlist:[{
                                                                    sources:[
                                                                        {
                                                                            file:'{{$asset['akamai_details']['delivery_html5_url']}}',
                                                                            'default': 'true'
                                                                        },
                                                                        {
                                                                            file:'{{$asset['akamai_details']['delivery_flash_url']}}',
                                                                            provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.8/AkamaiAdvancedJWStreamProvider.swf'
                                                                        }
                                                                    ],
                                                                    title: '{{$asset['name']}}'
                                                                    @if(file_exists(config('app.dams_video_thumb_path').$asset['unique_name'].".png"))
                                                                        ,image: "{{URL::to('/media_image/'.$asset['_id']."?compress=1")}}"
                                                                    @endif
                                                                    @if(isset($asset['srt_location']) && file_exists($asset['srt_location']))
                                                                        ,tracks: [{
                                                                            file: "{{URL::to('/cp/dams/video-srt/'.$asset['_id'])}}", 
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
                                                            });
                                                            // var akaJwPlugin = new AkamaiJWPlugin(jwplayer);
                                                            // akaJwPlugin.setData("category", "channel");
                                                            // akaJwPlugin.setData("subCategory", "{{ $asset['id'] }}");
                                                            // akaJwPlugin.setData("viewerId", "{{ Auth::user()->username }}");
                                                            // akaJwPlugin.setData("contentType", "{{ $asset['mimetype'] }}");
                                                            // akaJwPlugin.setData("contentLength", "{{ $asset['file_size'] }}");
                                                            // akaJwPlugin.setData("playerId", "{{ parse_url(config('app.url'), PHP_URL_HOST) }}");
                                                        </script>
                                            <?php   }
                                                    elseif(isset($asset['akamai_details']['stream_success_html5'])){ ?>
                                                        <script src="{{URL::to('/portal/theme/default/plugins/jwplayer7/jwplayer.js')}}"></script>
                                                        <script type="text/javascript">
                                                            jwplayer.key = '{{ config('app.jwplayer.key') }}';
                                                            var AKAMAI_MEDIA_ANALYTICS_CONFIG_FILE_PATH = '{{ config('app.akamai_analytics.html5.config_file') }}';
                                                        </script>
                                                        <script src="{{URL::to('/portal/theme/default/plugins/jwplayer7/akamai/AkamaiJWPlayerLoader.js')}}"></script>
                                                        <script src="http://79423.analytics.edgesuite.net/js/csma.js"></script>
                                                        <div class="wrapper-video">
                                                            <div class="h_iframe">
                                                                <img class="ratio" src="{{URL::asset($theme.'/img/16x9.png')}}" style="display:none"/>
                                                                <div id="akamai_player" class="xs-margin"></div>
                                                            </div>
                                                        </div>
                                                        <script type="text/javascript">
                                                            jwplayer('akamai_player').setup({
                                                                playlist: 
                                                                [{
                                                                    sources:[
                                                                        {
                                                                            file: '{{$asset['akamai_details']['stream_success_html5']}}',
                                                                            'default': 'true'
                                                                        },
                                                                        {
                                                                            file:'{{$asset['akamai_details']['stream_success_flash']}}',
                                                                            provider: 'http://players.edgesuite.net/flash/plugins/jw/v3.8/AkamaiAdvancedJWStreamProvider.swf'
                                                                        }
                                                                    ],
                                                                    title: '{{$asset['name']}}'
                                                                    @if(file_exists(config('app.dams_video_thumb_path').$asset['unique_name'].".png"))
                                                                        ,image: "{{URL::to('/media_image/'.$asset['_id']."?compress=1")}}"
                                                                    @endif
                                                                    @if(isset($asset['srt_location']) && file_exists($asset['srt_location']))
                                                                        ,tracks: [{
                                                                            file: "{{URL::to('/cp/dams/video-srt/'.$asset['_id'])}}", 
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
                                                            });
                                                            // var akaJwPlugin = new AkamaiJWPlugin(jwplayer);
                                                            // akaJwPlugin.setData("category", "channel");
                                                            // akaJwPlugin.setData("subCategory", "{{ $asset['id'] }}");
                                                            // akaJwPlugin.setData("viewerId", "{{ Auth::user()->username }}");
                                                            // akaJwPlugin.setData("contentType", "{{ $asset['mimetype'] }}");
                                                            // akaJwPlugin.setData("contentLength", "{{ $asset['file_size'] }}");
                                                            // akaJwPlugin.setData("playerId", "{{ parse_url(config('app.url'), PHP_URL_HOST) }}");
                                                        </script>
                                                <?php   }
                                                    elseif(!isset($asset['akamai_details']['code']) || $asset['akamai_details']['code'] != 200){ 
                                                        echo "Error in syncing the file. Please contact";
                                                    }
                                                    else{
                                                        echo "File is being proccessed please wait.";   
                                                    }
                                                    //elseif(isset($asset['akamai_details']['code']) && $asset['akamai_details']['code'] == 200 && isset($asset['akamai_details']['stream_success_flash'])){}
                                                }
                                                else{
                                                    echo "File is not synced with Video Server";
                                                }?>
                                                <p><strong>Description</strong></p>
                                                <div id="demo">
                                                    <article>{!! $asset['description'] !!}</article>
                                                </div>
                                            <?php }
                                            else{ ?>
                                                @if(preg_match('%(?:youtube(?:-nocookie)?\.com/(?:[^/]+/.+/|(?:v|e(?:mbed)?)/|.*[?&]v=)|youtu\.be/)([^"&?/ ]{11})%i', $asset['url'], $match))
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
                                                        <iframe src="http://www.youtube.com/embed/{{ $match[1] }}?rel=0&autohide=2&fs=0&iv_load_policy=3&modestbranding=1&theme=light" frameborder="0"/></iframe>   
                                                    </div>
                                                    <br><p><strong>Description</strong></p>
                                                    <div id="demo">
                                                        <article>{!! $asset['description'] !!}</article>
                                                    </div>
                                                @else
                                                 <h4>No Preview Available</h4>
                                                <a href="{{$asset['url']}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                <br><br><p><strong>Description</strong></p>
                                                <div id="demo">
                                                    <article>{!! $asset['description'] !!}</article>
                                                </div>
                                                @endif
                                                <?php 
                                            }
                                            break;
                                        }
                                        case "image" :{
                                            if($asset['asset_type'] == "file"){
                                                ?>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 download-align custom-box center sm-margin">
                                                        <img src="{{URL::to('media_image/'.$asset['_id'])}}" class="img-responsive">
                                                    </div>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                                        <p><strong>Description</strong></p>
                                                        <div id="demo">
                                                            <article>{!! $asset['description'] !!}</article>
                                                        </div>
                                                    </div>
                                                <?php
                                            }
                                            else{ ?>
                                                    <h4>No Preview Available</h4>
                                                    
                                                    <a href="{{$asset['url']}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                    <br><br><p><strong>Description</strong></p>
                                                    <div id="demo">
                                                        <article>{!! $asset['description'] !!}</article>
                                                    </div>
                                                <?php 
                                            }
                                            break;
                                        }
                                        case "scorm" : {
                                            $scorm_file_name = config('app.scorm_file_name');
                                            if($asset['visibility'] == 'public')
                                            {
                                                $file_location = $asset['public_file_location'];
                                            }
                                            else
                                            {
                                                $file_location = $asset['private_file_location'];
                                            }
                                            
                                            if($asset['asset_type'] == "file"){
                                                ?>
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
                                                    <iframe src="{{URL::to($file_location.$scorm_file_name)}}" frameborder="0"/></iframe>   
                                                </div>
                                                <br><p><strong>Description</strong></p>
                                                <div id="demo">
                                                    <article>{!! $asset['description'] !!}</article>
                                                </div>

                                                <?php
                                            }
                                            else{ ?>
                                                    <h4>No Preview Available</h4>
                                                    
                                                    <a href="{{URL::to($file_location.$scorm_file_name)}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                    <br><br><p><strong>Description</strong></p>
                                                    <div id="demo">
                                                        <article>{!! $asset['description'] !!}</article>
                                                    </div>
                                                <?php 
                                            }
                                            break;
                                        }
                                        case "document" :{
                                            if($asset['asset_type'] == "file"){
                                                ?>
                                                    <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 download-align custom-box center xs-margin">
                                                        @if(isset($viewer_session_id) && !empty($viewer_session_id) && $viewer_session_id !='')
                                                        <iframe src="https://view-api.box.com/1/sessions/{{ $viewer_session_id }}/view?theme=dark" style="width:100%; max-width: 100%; height: 500px; border-radius: 5px; border: 1px solid #d9d9d9;"  allowfullscreen="allowfullscreen"></iframe>
                                                        @else
                                                            <a href="{{URL::to('/media_image/'.$asset['_id'])}}"><img src="{{URL::asset($theme.'/img/downloadfile.png')}}" class="img-responsive"></a>
                                                        @endif
                                                    </div>

                                                    <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                                        <p><strong>Description</strong></p>
                                                        <div id="demo">
                                                            <article>{!! $asset['description'] !!}</article>
                                                        </div>
                                                    </div>
                                                <?php
                                            }
                                            else{ ?>
                                                    <h4>No Preview Available</h4>

                                                    <a href="{{$asset['url']}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                    <br><br><p><strong>Description</strong></p>
                                                    <div id="demo">
                                                        <article>{!! $asset['description'] !!}</article>
                                                    </div>
                                                <?php 
                                            }
                                            break;
                                        }
                                        case "audio" :{
                                            if($asset['asset_type'] == "file"){
                                                ?>
                                                @if(in_array(strtolower($asset['file_extension']), ['mp3', 'aac']))
                                                    <script src="{{URL::to('/portal/theme/default/plugins/jwplayer7/jwplayer.js')}}"></script>
                                                    <script type="text/javascript">
                                                        jwplayer.key = '{{ config('app.jwplayer.key') }}';
                                                    </script>
                                                    <div class="wrapper-video">
                                                        <div class="h_iframe">
                                                            <img class="ratio" src="{{URL::asset($theme.'/img/16x9.png')}}" style="display:none"/>
                                                            <div id="audio_player" class="xs-margin"></div>
                                                        </div>
                                                    </div>
                                                    <script type="text/javascript">
                                                        jwplayer('audio_player').setup({
                                                            @if($asset['visibility'] == "public")
                                                                file:'{{URL::to(config('app.public_dams_audio_path').$asset['unique_name_with_extension'])}}',
                                                            @else
                                                                file:'{{URL::to('/cp/dams/show-media/'.$asset['_id'].".mp3")}}',
                                                            @endif
                                                            primary: 'html5',
                                                            androidhls: 'true',
                                                            fallback: 'true',
                                                            width: '100%',
                                                            height: 40
                                                        });
                                                    </script>
                                                @else
                                                <div class="col-lg-7 col-md-7 col-sm-12 col-xs-12 download-align custom-box center xs-margin">
                                                    <a href="{{URL::to('/media_image/'.$asset['_id'])}}"><img src="{{URL::asset($theme.'/img/packetpage_audio.png')}}" class="img-responsive"></a>
                                                </div>
                                                <div class="col-lg-5 col-md-5 col-sm-12 col-xs-12">
                                                    <p><strong>Description</strong></p>
                                                    <div id="demo">
                                                        <article>{!! $asset['description'] !!}</article>
                                                    </div>
                                                </div>
                                                @endif
                                                <?php
                                            }
                                            else{ ?>
                                                     <h4>No Preview Available</h4>
                                                    <a href="{{$asset['url']}}" target="_blank"><button class="btn btn-success">Click Here</button></a>
                                                    <br><br><p><strong>Description</strong></p>
                                                    <div id="demo">
                                                        <article>{!! $asset['description'] !!}</article>
                                                    </div>
                                                <?php 
                                            }
                                            break;
                                        }
                                    }
                                ?>
                            @elseif(!empty($asset) && $asset['element_type'] == "assessment")
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 sm-margin">
                                    <div class="center">
                                        <img src="{{URL::asset($theme.'/img/packetpage_assessment.png')}}" class="img-responsive">
                                        @if(isset($asset["practice_quiz"]) && !empty($asset["practice_quiz"]) && $asset["practice_quiz"])
                                            <div style="position: absolute; top: 3px; right: 15px; border: 1px none ! important; border-radius: 5px ! important;"><span style="font-weight: bold;background-color: rgb(3, 137, 66) !important;" class="label label-success">Practice quiz</span></div>
                                        @endif
                                    </div>
                                </div>
                                @if(Timezone::convertToUTC($asset['start_time'], 'UTC', 'U') < 84600)
                                <?php $asset['start_time'] = ''; ?>
                                @endif
                                @if(Timezone::convertToUTC($asset['end_time'], 'UTC', 'U') < 84600)
                                <?php $asset['end_time'] = ''; ?>
                                @endif
                                <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                    <div class="font-16 xs-margin">
                                        @if(!empty($asset['start_time']))
                                        <span class="start">STARTS:</span> <strong>@if(!empty($asset['start_time'])) {{Timezone::convertFromUTC($asset['start_time'], Auth::user()->timezone, 'D, d M Y h:i A')}} @endif</strong>
                                        @endif 
                                        @if(!empty($asset['end_time']))
                                        <br><span class="end">ENDS:</span> <strong>@if(!empty($asset['end_time'])) {{Timezone::convertFromUTC($asset['end_time'], Auth::user()->timezone, 'D, d M Y h:i A')}} @endif</strong>
                                        @endif
                                    </div>
                                    @if($asset['duration'] != 0)
                                    <p class="font-16 sm-margin">
                                        <i class="fa fa-clock-o font-18"></i>&nbsp;&nbsp;<strong>{{ $asset['duration'].' Mins' }}</strong>
                                    </p>
                                    @endif
                                    <div class="row">
                                        <?php 
                                            $closed = $attempts->where('status','CLOSED');
                                            if($asset['attempts'] == 0)
                                                $attempt_left = 'No attempt limit';
                                            else
                                                $attempt_left = $asset['attempts'] - $attempts->count().' attempts left';
                                        ?>
                                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 xs-margin">
                                            @if(count($asset['questions']) == 0)
                                                <a href="javascript:;" class="btn btn-default1 btn-lg">START QUIZ<br><span class="font-13">{{ $attempt_left }}</span></a>
                                            @elseif((empty($asset['start_time']) || Timezone::convertToUTC($asset['start_time'], 'UTC', 'U') < time()) && (empty($asset['end_time']) || Timezone::convertToUTC($asset['end_time'], 'UTC', 'U') > time()))
                                                @if($asset['attempts'] == 0 || $asset['attempts'] > $attempts->where('status','CLOSED')->count())
                                                    <form action="{{ url('assessment/start-attempt/'.$asset['quiz_id']) }}" method="POST" accept-charset="utf-8">
                                                        <input type="hidden" name="return" value="{{ Request::path() }}">
                                                        @if($attempts->where('status', 'OPENED')->count() != 0)
                                                        <button type="submit" class="btn btn-success btn-lg">RESUME<br><span class="font-13">{{ $attempt_left }}</span></button>
                                                        @else
                                                        <button type="submit" class="btn btn-success btn-lg" @if($asset['duration'] != 0) onclick="return confirm('This assessment has a time limit. Are you sure that you wish to start?')" @endif>START QUIZ<br><span class="font-13">{{ $attempt_left }}</span></button>
                                                        @endif
                                                    </form>
                                                @else
                                                    <a href="javascript:;" class="btn btn-default1 btn-lg">COMPLETED<br><span class="font-13">No attempts left</span></a>
                                                @endif
                                            @else
                                                <a href="javascript:;" class="btn btn-default1 btn-lg">START QUIZ<br><span class="font-13">{{ $attempt_left }}</span></a>
                                            @endif
                                        </div>
                                        <div class="col-lg-6 col-md-12 col-sm-12 col-xs-12 xs-margin">
                                        @if($attempts->count() > 0)
                                            <?php $ii['obtained'] = $ii['total'] = $ii['count'] = 0; ?>
                                            @foreach($attempts as $detail)
                                                <?php
                                                    $ii['obtained'] += $detail->obtained_mark;
                                                    $ii['total'] += $detail->total_mark;
                                                    $ii['count']++;
                                                    $ii['last'] = $detail;
                                                ?>  
                                            @endforeach
                                            @if($attempts->where('status','CLOSED')->count() > 0)
                                            <strong>Last attempt:</strong><br>
                                            <span class="font-44 red"><strong>{{ round(($ii['last']->obtained_mark/$ii['last']->total_mark)*100).'%' }}</strong></span>
                                            @endif
                                        @endif
                                        </div>
                                        <div class="col-md-12">
                                            <a href="{{url('assessment/detail/'.$asset['quiz_id'])}}"><strong>Details ></strong></a>
                                        </div>
                                    </div>
                                </div><!--data-->
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <div id="demo">
                                        @if(!empty($asset['quiz_description']))
                                            <strong>Instructions</strong><br>
                                            <article>{!! $asset['quiz_description'] !!}</article>
                                        @endif
                                    </div>
                                </div>
                            @elseif(!empty($asset) && $asset['element_type'] == "event")
                                
                                @if($asset['event_type'] == 'live')
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 nonsequential-panel sm-margin">
                                        <span class="white label label-primary font-10 general-label border-white"><strong>LIVE</strong></span>
                                        <div class="center">
                                            <img src="{{URL::asset($theme.'/img/packetpage_event.png')}}" class="img-responsive">
                                        </div>
                                    </div>
                                    <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                        <table width="100%" class="xs-margin">
                                            <tr height="60">
                                                <td width="38"><i class="fa fa-calendar-o green font-28"></i></td>
                                                <td class="font-16"><strong>{{Timezone::convertFromUTC($asset['start_time'], Auth::user()->timezone, 'D, d M Y')}}<strong><br><strong>{{Timezone::convertFromUTC($asset['start_time'], Auth::user()->timezone, 'h:i A')}}<strong></td>
                                            </tr>
                                            <tr height="30">
                                                <td width="38"><i class="fa fa-clock-o red font-28"></i></td>
                                                <td class="font-16"><strong>{{ gmdate('H:i', $asset['duration'] * 60) }}<strong></td>
                                            </tr>
                                        </table>

                                        <p>
                                            <strong>Host:</strong>
                                            {{ $asset['event_host_name'] }}
                                        @if(!empty($asset['speakers']))
                                        <br>
                                            <strong>Speakers:</strong>
                                            @foreach($asset['speakers'] as $speaker)
                                                {{ $speaker }},
                                            @endforeach
                                        @endif
                                        </p>
                                    @elseif($asset['event_type'] == 'general')
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12 nonsequential-panel sm-margin">
                                            <span class="white label label-danger font-10 general-label border-white"><strong>GENERAL</strong></span>
                                            <div class="center">
                                                <img src="{{URL::asset($theme.'/img/packetpage_event.png')}}" class="img-responsive">
                                            </div>
                                        </div>
                                        <div class="col-lg-6 col-md-6 col-sm-6 col-xs-12">
                                            <div class="font-16 xs-margin">
                                              <span class="start">STARTS:</span> <strong>{{ Timezone::convertFromUTC($asset['start_time'], Auth::user()->timezone, 'D, d M Y') }}</strong> <br>
                                                <span class="end">ENDS:</span> <strong>{{ Timezone::convertFromUTC($asset['end_time'], Auth::user()->timezone, 'D, d M Y') }}</strong>
                                            </div>
                                            @if(!empty($asset['location']))
                                            <p class="font-16">
                                                <i class="fa fa-map-marker font-18"></i>&nbsp;&nbsp;<strong>{{ $asset['location'] }}</strong>
                                            </p>
                                            @endif
                                        </div>
                                        
                                    @endif
                                    @if($asset['event_type'] == 'live')
                                    <br>
                                        <p>
                                            @if((Timezone::convertToUTC($asset['start_time'], 'UTC', 'U') - ($asset['open_time'] * 60)) < time() && Timezone::convertToUTC($asset['end_time'], 'UTC', 'U') > time())
                                                @if(Auth::user()->uid == $asset['event_host_id'])
                                                    <a href="{{ url('event/live-join/'.$asset['event_id']) }}" class="btn-success btn-lg">Start now</a>
                                                @else
                                                    <a href="{{ url('event/live-join/'.$asset['event_id']) }}" class="btn-success btn-lg">Join now</a>
                                                @endif
                                            @endif
                                        </p>
                                        </div>
                                    @endif
                                <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                                    <p><strong>Description</strong></p>
                                    <div id="demo">
                                        <article>{!! $asset['event_description'] !!}</article>
                                    </div>
                                </div>
                            @elseif(!empty($asset) && $asset['element_type'] == "flashcard")
                                @include('admin.theme.flashcards.preview', [ 'flashcards' => $asset['cards'], 'height' => '400px'])                                
                            @endif
                        </div>
                    </div>
                @endif
                @if($packet['qanda'] == 'yes')
                    <div class="custom-box">
                        <div class="portlet box">
                            <div class="portlet-body">
                                <div class="tabbable-line">
                                    <ul class="nav nav-tabs ">
                                        <li class="active">
                                            <a href="#tab_15_1" data-toggle="tab">
                                            @if($from=='allquestions')
                                                 All Questions
                                            @else
                                                 My Questions
                                            @endif
                                        </a>
                                        </li>
                                        <li>
                                            <a href="#tab_15_2" data-toggle="tab">
                                            FAQ</a>
                                        </li>
                                    </ul>
                                    <div class="tab-content">
                                        <div class="clearfix"></div>
                                        <span class="success_text"></span>
                                        <span class="error_text"></span>

                                        <div class="tab-pane active myquestion_tab" id="tab_15_1">
                                            <div class="form-group">
                                                <ul class="media-list">
                                                    <li class="media">
                                                        <a class="pull-left" href="javascript:;">
                                                            <img class="todo-userpic" src="{{URL::asset($theme.'/img/green.png')}}" width="27px" height="27px">
                                                        </a>
                                                        <div class="media-body">
                                                            <form action="{{URL::to('program/question/'.$packet['packet_id'].'/'.$packet['packet_slug'].'/'.$packet['feed_slug'])}}">
                                                                <div class="col-md-10 col-sm-9 col-xs-12 xs-margin">
                                                                    <textarea class="form-control todo-taskbody-taskdesc" name="question" rows="2" placeholder="Type new question..."></textarea>
                                                                    <span class="help-inline errorspan" style="color:#f00"></span>
                                                                </div>
                                                                <div class="col-md-2 col-sm-3 col-xs-12">
                                                                    <input type="button" id="ques_submit" class="btn red-sunglo" data-action="{{URL::to('program/question/'.$packet['packet_id'].'/'.$packet['packet_slug'].'/'.$packet['feed_slug'])}}" value="Send">
                                                                </div>
                                                            </form>
                                                        </div>
                                                    </li>
                                                    <div>
                                                        <input type="hidden" id="page_no" value="0"> 
                                                    </div>
                                                    <div class="myquestion_div">
                                                        @include('portal.theme.default.programs.myquestions_ajax_load', ['user_ques' => $user_ques])
                                                    </div>
                                                </ul>
                                            </div>
                                        </div>
                                        <div class="tab-pane faq_tab" id="tab_15_2">
                                            @if(count($public_ques) > 0)
                                                <div class="form-group">
                                                    <ul class="media-list">
                                                        <div class="faq_div">
                                                            @include('portal.theme.default.programs.faq_ajax_load', ['public_ques' => $public_ques])                    
                                                        </div>
                                                    </ul>
                                                </div>
                                            @else
                                                <h4 align="center"> There are no faq's to show</h4>
                                            @endif
                                        </div>
                                    </div>
                                </div>  
                            </div>
                        </div>
                    </div>
                @endif
            </div>
            <!--content end here-->
            <!--packet items starts here-->
            <div class="col-lg-4 col-md-4 col-sm-4 col-xs-12 pkt-items pkt-items-desktop">
                <div class="panel panel-default">
                    <div class="center border-btm">
                        <a type="button" href="{{URL::to('program/packets/'.$packet['feed_slug'])}}" class="btn red-sunglo xs-margin btn-sm">View All {{Lang::get('program.packets')}}</a>
                    </div>
                    <div class="panel-heading">
                        {{count($elements)}}
                        @if(count($elements) > 1) 
                            Items 
                        @else 
                            Item 
                        @endif 
                        
                        @if($packet['sequential_access'] == 'yes')
                            <!-- <em class="font-13">(Sequential)</em> -->
                            <?php $sequential="timeline"; ?>
                        @else
                            <?php $sequential=""; ?>
                        @endif
                    </div><!--panel heading-->
                    <div class="panel-body">
                        <div class="{{$sequential}}">
                            <!-- TIMELINE ITEM -->
                            <?php 
                                if(!empty($elements))
                                {
                                    $le=0;
                                    $last_element_id=$elements[0]['id']; 
                                    $last_element_type=$elements[0]['type'];
                                }   
                            ?>
                            @foreach($elements as $element)
                            <?php $le=1; ?>
                                <?php 
                                    switch ($element['type']) {
                                        case 'media':
                                        {
                                            $element_asset = Dam::getDAMSAssetsUsingAutoID((int)$element['id']);
                                            $element_asset = $element_asset[0];
                                            $name=$element_asset['name'];
                                            $size='Type:'.$element_asset['type'];
                                            if($element_asset['type'] == 'video')
                                            {
                                                $class='fa-play-circle';
                                            }
                                            elseif($element_asset['type'] == 'image')
                                            {
                                                $class='fa-picture-o';
                                            }
                                            elseif($element_asset['type'] == 'document')
                                            {
                                                $class='fa-file-text-o';
                                            }
                                            elseif($element_asset['type'] == 'scorm')
                                            {
                                                $class='fa-film';
                                            }
                                            else
                                            {
                                                $class='fa-volume-down';
                                            }
                                            break;
                                        }
                                        case 'assessment':
                                        {
                                            $element_asset = Quiz::getQuizAssetsUsingAutoID($element['id']);
                                            $element_asset = $element_asset[0];
                                            $name=$element_asset['quiz_name'];
                                            $size='Dur:'.$element_asset['duration'];
                                            $class='fa-edit';
                                            break;
                                        }
                                        case 'event':
                                        {
                                            $element_asset = Event::getEventsAssetsUsingAutoID($element['id']);
                                            $element_asset = $element_asset[0];
                                            $name=$element_asset['event_name'];
                                            $size='Type:'.$element_asset['event_type'];
                                            $class='fa-calendar';
                                            break;
                                        }
                                        case 'flashcard':
                                        {
                                            $element_asset = FlashCard::getFlashcardsAssetsUsingAutoID($element['id']);
                                            $element_asset = $element_asset[0];
                                            $size='Type: Flashcard';
                                            $name=$element_asset['title'];
                                            $class='fa-star';
                                            break;
                                        }
                                    }

                                    $activity=MyActivity::pluckElementActivity($packet['packet_id'], $element['id'], $element['type']);
                                    if(($asset['element_type'] == $element['type']) && ($asset['id'] == $element['id']))
                                    {
                                        $seen_class='pkt-item-progress';
                                    }
                                    elseif(!empty($activity))
                                    {
                                        $seen_class='pkt-item-blue';
                                    }
                                    else
                                    {
                                        $seen_class='pkt-item-gray';
                                    }
                                ?>
                                <div class="timeline-item">
                                    <div class="timeline-badge">
                                        <div class="{{$seen_class}}"><i class="fa {{$class}}"></i></div>
                                    </div>
                                    <div class="timeline-body transparent-bg">                      
                                        <div class="timeline-body-head">
                                            <div class="timeline-body-head-caption">
                                                <div href="javascript:;" class="timeline-body-title font-blue-madison">
                                                    <?php $last_element_activity=MyActivity::pluckElementActivity($packet['packet_id'], $last_element_id, $last_element_type); ?>
                                                    @if($packet['sequential_access'] == 'yes' && empty($last_element_activity))
                                                        <a title="Sequential item should be accessed with order">{{$name}}</a>
                                                    @else
                                                        <a href="{{URL::to('program/packet/'.$packet['packet_slug'].'/element/'.$element['id'].'/'.$element['type'])}}">{{$name}}</a>
                                                    @endif
                                                </div>
                                                <span class="timeline-body-time font-grey-cascade"><i>{{$size}}</i></span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <?php 
                                    $last_element_id=$element['id']; $last_element_type=$element['type'];
                                ?>
                            @endforeach
                            <!-- END TIMELINE ITEM -->
                        </div>
                    </div><!--panel-body-->
                </div>
            </div>
            <!--packet items end here-->
        </div>
    @else
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                <div id="accordion1" class="panel-group accordion">
                    <div class="panel transparent-bg">
                        <div class="panel-heading">
                            <h4 class="panel-title m-btm-12">
                                <div class="row">
                                    <div class="col-md-10">
                                        <span class="caption gray">{{$packet['packet_title']}}&nbsp;&nbsp;</span>
                                        @if(in_array($packet['packet_id'], $favorites))
                                            <?php 
                                                $action="unfavourite";
                                                $class="red"; 
                                            ?>
                                        @else
                                            <?php 
                                                $action="favourite";
                                                $class="gray";
                                            ?>
                                        @endif
                                        <span>
                                            <i id="{{$packet['packet_id']}}" data-action="{{$action}}" class="fa fa-heart {{$class}}"></i>
                                        </span>
                                        <span class="font-grey-cascade">&nbsp;&nbsp;|&nbsp;<em class="font-13">{{ Timezone::convertFromUTC('@'.$packet['packet_publish_date'], Auth::user()->timezone, Config('app.date_format'))}}</em>
                                            &nbsp;&nbsp;|&nbsp;<label class="pkt-inactive-label">Inactive</label>
                                        </span>

                                    </div>
                                </div>
                            </h4>
                        </div>
                        <div>
                            <div class="panel-body border-0" >{!! $packet['packet_description'] !!}fhduwwqjwqkjlwqwjiqwji</div>
                        </div>
                    </div>
                </div>  
            </div>
        </div>
    @endif



<!-- delete window -->
<div class="modal fade deletemodal">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3><i class="icon-file"></i>Delete Question</h3>                                                 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body" style="padding: 20px">               
                Are you sure you want to delete this question?
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-danger">Yes</a>
              <a class="btn btn-success" data-dismiss="modal">Close</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->
<script src="{{ URL::asset('admin/assets/flashcards/js/jquery.bookblock.min.js')}}"></script>
<script>
            var Page = (function() {
                
                var config = {
                        $bookBlock : $( '#bb-bookblock' ),
                        $navNext : $( '#bb-nav-next' ),
                        $navPrev : $( '#bb-nav-prev' ),
                        $navFirst : $( '#bb-nav-first' ),
                        $navLast : $( '#bb-nav-last' )
                    },
                    init = function() {
                        config.$bookBlock.bookblock( {
                            orientation : 'horizontal',
                            speed : 800,
                            shadowSides : 0.8,
                            shadowFlip : 0.7
                        } );
                        initEvents();
                    },
                    initEvents = function() {
                        
                        var $slides = config.$bookBlock.children();
                        // add navigation events
                        config.$navNext.on( 'click touchstart', function() {
                            config.$bookBlock.bookblock( 'next' );
                            return false;
                        } );
                        config.$navPrev.on( 'click touchstart', function() {
                            config.$bookBlock.bookblock( 'prev' );
                            return false;
                        } );
                        config.$navFirst.on( 'click touchstart', function() {
                            config.$bookBlock.bookblock( 'first' );
                            return false;
                        } );
                        config.$navLast.on( 'click touchstart', function() {
                            config.$bookBlock.bookblock( 'last' );
                            return false;
                        } );
                        
                        // add swipe events
                        $slides.on( {
                            'swipeleft' : function( event ) {
                                config.$bookBlock.bookblock( 'next' );
                                return false;
                            },
                            'swiperight' : function( event ) {
                                config.$bookBlock.bookblock( 'prev' );
                                return false;
                            }
                        } );
                        // add keyboard events
                        $( document ).keydown( function(e) {
                            var keyCode = e.keyCode || e.which,
                                arrow = {
                                    left : 37,
                                    up : 38,
                                    right : 39,
                                    down : 40
                                };
                            switch (keyCode) {
                                case arrow.left:
                                    config.$bookBlock.bookblock( 'prev' );
                                    break;
                                case arrow.right:
                                    config.$bookBlock.bookblock( 'next' );
                                    break;
                            }
                        } );
                    };
                    return { init : init };
            })();
</script>
<script>
                Page.init();
</script>

<script type="text/javascript">

    $(document).on('click','#ques_submit',function(e){
        e.preventDefault();
        var page_no = $('#page_no').val();
        var $this = $(this);
        var action=$this.data('action');
        var ques=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                ques:ques,
                page_no:page_no
            }
        }).done(function(response) {
            if(response.status == true) {
                $('.myquestion_div').html(response.data);
                $this.parent().prev().find('textarea').val('');
                $this.parent().prev().find('span.errorspan').text('');
                $('.success_text').html('<div class="alert alert-success">'+response.message+'</div>');
            } else {
                $('.success_text').html('');
                $this.parent().prev().find('textarea').val(ques);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }   
        }).fail(function(response) {
            alert( "Error while deleting the question. Please try again" );
        });
    });

    $(document).on('click','.ans_submit',function(e){
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('value');
        var action=$this.data('action');
        var ans=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                ans:ans
            }
        }).done(function(response) {
            if(response.status == true) {
                $('#answers_div'+id).html(response.data);
                $this.parent().prev().find('textarea').val('');
                $this.parent().prev().find('span.errorspan').text('');
            } else {
                $this.parent().prev().find('textarea').val(ans);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }   
        }).fail(function(response) {
            alert( "Error while deleting the question. Please try again" );
        });
    });

    $(document).on('click','.faq_delete',function(e){
        e.preventDefault();
        var page_no = $('#page_no').val();
        var $this = $(this);
        var $deletemodal = $('.deletemodal');
        var action = $this.data('action');
        $deletemodal.modal('show');
        $deletemodal.find('.modal-footer .btn-danger').unbind('click').click(function(){
            $deletemodal.modal('hide');
            $.ajax({
                type: 'GET',
                url: action+"?page_no="+page_no
            })
            .done(function(response) {
                if(response.status == true) {
                    $('.myquestion_div').html(response.data);
                } else {
                    alert("Error while deleting the question. Please try again");
                }
            })
            .fail(function(response) {
                alert( "Error while deleting the question. Please try again" );
            });
        });   
    });

    $(document).on('click','.faq_edit',function(e){
      e.preventDefault();
      var id = $(this).data('value');
      $("#edit_faq"+id).removeClass("faq_inactive").addClass("faq_active");
      $("#faq_sec"+id).removeClass("faq_active").addClass("faq_inactive");
    });

    $(document).on('click','.edit_cancel',function(e){
      e.preventDefault();
      var $this = $(this);
      var id = $this.data('value');
      var ques = $('#question_value'+id).val();
      $this.parent().prev().find('textarea').val(ques);
      $("#faq_sec"+id).removeClass("faq_inactive").addClass("faq_active");
      $("#edit_faq"+id).removeClass("faq_active").addClass("faq_inactive");
      $this.parent().prev().find('span.errorspan').text('');
    });

    $(document).on('click','.edit_submit',function(e){
        e.preventDefault();
        var $this = $(this);
        var id = $this.data('value');
        var action=$this.data('action');
        var edit=$this.parent().prev().find('textarea').val();
        $.ajax({
            type: 'GET',
            url: action,
            data :{
                edit:edit
            }
        }).done(function(response) {
            if(response.status == true) {
                $('#update_ques'+id).html(response.data);
                $("#question_value"+id).val(response.data);
                $("#faq_sec"+id).removeClass("faq_inactive").addClass("faq_active");
                $("#edit_faq"+id).removeClass("faq_active").addClass("faq_inactive");
                $this.parent().prev().find('textarea').val(response.data);
                $this.parent().prev().find('span.errorspan').text('');
            } else {
                $("#edit_faq"+id).removeClass("faq_inactive").addClass("faq_active");
                $("#faq_sec"+id).removeClass("faq_active").addClass("faq_inactive");
                $this.parent().prev().find('textarea').val(edit);
                $this.parent().prev().find('span.errorspan').text(response.message);
            }   
        }).fail(function(response) {
            alert( "Error while deleting the question. Please try again" );
        });
    });


    $('#favourite').on('click', '.fav-packet', function() {
        var action = $(this).data('action');
        var packet_id = $(this).attr('id');
        if(action == 'favourite') {
            $("#"+packet_id).removeClass("l-gray").addClass("red");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/packet-favourited/favourite') }}/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $("#"+response.packet_id).data('action', 'unfavourite');
                } else {
                    $("#"+response.packet_id).removeClass("red").addClass("gray");
                }
            })
            .fail(function(response) {
                $("#"+packet_id).removeClass("red").addClass("gray");
                alert( "Error while updating the post. Please try again" );
            });
        }
        if(action == 'unfavourite') {
            $("#"+packet_id).removeClass("red").addClass("gray");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/packet-favourited/unfavourite') }}/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $('#'+response.packet_id).data('action', 'favourite');
                } else {
                    $('#'+response.packet_id).removeClass('gray').addClass('red');
                }
            })
            .fail(function(response) {
                $('#'+response.packet_id).removeClass("gray").addClass("red");
                alert( "Error while updating the post. Please try again" );
            });
        }
    });

    $('#element-like').on('click', '.star-element', function() {
        var action = $(this).data('action');
        var element_id = $(this).attr('id');
        var element_type = $(this).data('type');
        var packet_id = $(this).data('packet');
        if(action == 'star') {
            $("#"+element_id).removeClass("gray").addClass("yellow");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/element-liked/star') }}/"+element_id+"/"+element_type+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $("#"+response.element_id).data('action', 'unstar');
                } else {
                    $("#"+response.element_id).removeClass("yellow").addClass("gray");
                }
            })
            .fail(function(response) {
                $("#"+element_id).removeClass("yellow").addClass("gray");
                alert( "Error while updating the element. Please try again" );
            });
        }
        if(action == 'unstar') {
            $("#"+element_id).removeClass("yellow").addClass("gray");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/element-liked/unstar') }}/"+element_id+"/"+element_type+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $('#'+response.element_id).data('action', 'star');
                } else {
                    $('#'+response.element_id).removeClass('gray').addClass('yellow');
                }
            })
            .fail(function(response) {
                $('#'+response.element_id).removeClass("gray").addClass("yellow");
                alert( "Error while updating the item. Please try again" );
            });
        }
    });

</script>
<script type="text/javascript">
    $(document).ready(function () {

    var settings = {
        objSlideTrigger: '#pkt-trigger', // link button id
        objSlidePanel: '.panel-mob' // slide div class or id
    }

    $(settings.objSlideTrigger).bind('click' , function() {
        //If the panel isn't out
        if(!$(settings.objSlidePanel).hasClass('out')) {
            slidePanelOut();
        } else if($(settings.objSlidePanel).hasClass('out')) {
            slidePanelIn();
        } 
    });

    function slidePanelOut() {
        //Animate it to left 
        $(settings.objSlidePanel).animate({
            'left' : '-2%'
        });
        //Add the out class
        $(settings.objSlidePanel).addClass('out');
    }
    function slidePanelIn() {
        //Otherwise, animate it back in
        $(settings.objSlidePanel).animate({
            'left' : '-123%'
        });
        //Remove the out class
        $(settings.objSlidePanel).removeClass('out');
    }

    /*$("a").each(function() {
     var a = new RegExp("/" + window.location.host + "/");
     if(!a.test(this.href) && this.href != "javascript:;" && this.href != "#" && this.id != "pkt-trigger") {
        $(this).click(function(event) {
            event.preventDefault();
            event.stopPropagation();
            window.open(this.href, "_blank");
        });
     }
    });*/

});
</script>

<script type="text/javascript">
    $(document).ready(function () {
        var m_pageno=1;
        var f_pageno=1;
        var myquestion_count='<?php echo count($user_ques); ?>';
        var faq_count='<?php echo count($public_ques); ?>';
        var packet_id='<?php echo $packet['packet_id']; ?>';
        var stop = flag = true;
        $(window).scroll(function() {
            var period_val = $(".tab-pane.active").attr('id');
            if(period_val == 'tab_15_1')
            {
                if(myquestion_count > 8 && stop) {
                    if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
                        if(flag) {
                            flag = false;
                            $.ajax({
                                type: 'GET',
                                url: "{{ url('program/next-questions?pageno=') }}"+m_pageno+"&packet_id="+packet_id
                            }).done(function(e) {
                                if(e.status == true) {
                                    $('.myquestion_div').append(e.data);
                                    myquestion_count=e.count;
                                    $("#page_no").val(m_pageno);
                                    stop=true;
                                    flag = true;
                                    if(myquestion_count < 9)
                                    {
                                        $('.myquestion_div').append("<div class='col-md-12 center l-gray'><p><strong><?php echo Lang::get('pagination.no_more_records'); ?></strong></p></div>");
                                    }
                                }
                                else {
                                    $('.myquestion_div').append(e.data);
                                    stop = false;
                                }
                                m_pageno += 1;
                            }).fail(function(e) {
                                alert('Failed to get the data');
                            });
                                
                        }
                    }
                }
            }
            else if(faq_count > 8 && stop) {
                if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
                    if(flag) {
                        flag = false;
                        $.ajax({
                            type: 'GET',
                            url: "{{ url('program/next-faqs?pageno=') }}"+f_pageno+"&packet_id="+packet_id
                        }).done(function(e) {
                            if(e.status == true) {
                                $('.faq_div').append(e.data);
                                faq_count=e.count;
                                stop=true;
                                flag = true;
                                if(faq_count < 9)
                                {
                                    $('.faq_div').append("<div  class='col-md-12 center l-gray'><p><strong><?php echo Lang::get('pagination.no_more_records'); ?></strong></p></div>");
                                }
                            }
                            else {
                                $('.faq_div').append(e.data);
                                stop = false;
                            }
                            f_pageno += 1;
                        }).fail(function(e) {
                            alert('Failed to get the data');
                        });     
                    }
                }
            }
        });
    });
</script>
<script type="text/javascript">
    $('.faq_div').on('click', '.like-faq', function(e) {
        e.preventDefault();
        var action = $(this).data('action');
        var qid = $(this).attr('id');
        var packet_id = $(this).data('packet');
        if(action == 'like') {
            $("#"+qid).removeClass("gray").addClass("blue");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/question-liked/like') }}/"+qid+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $("#"+response.qid).data('action', 'unlike');
                    $("#like_count"+response.qid).html(response.like_count);
                } else {
                    $("#"+response.qid).removeClass("blue").addClass("gray");
                }
            })
            .fail(function(response) {
                $("#"+qid).removeClass("blue").addClass("gray");
                alert( "Error while updating the item. Please try again" );
            });
        }
        if(action == 'unlike') {
            $("#"+qid).removeClass("blue").addClass("gray");
            $.ajax({
                type: 'GET',
                url: "{{ url('program/question-liked/unlike') }}/"+qid+"/"+packet_id
            })
            .done(function(response) {
                if(response.status == true) {
                    $('#'+response.qid).data('action', 'like');
                    $("#like_count"+response.qid).html(response.like_count);
                } else {
                    $('#'+response.qid).removeClass('gray').addClass('blue');
                }
            })
            .fail(function(response) {
                $('#'+response.qid).removeClass("gray").addClass("blue");
                alert( "Error while updating the item. Please try again" );
            });
        }
    });




</script>
@stop
