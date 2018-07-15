@if(isset($user_enrollment['catergory_with_program']))
    @foreach($user_enrollment['catergory_with_program']['data'] as $key => $eachcategory) 
    <!--start content feed 1-->
    <div>
        <h3 class="page-title-small">{{ html_entity_decode($eachcategory['title']) }}</h3>
    </div>
    <div class="row xs-margin border-btm dashboard">
        <div class="col-md-12 nav-space">
            <div id="owl-demo" class="owl-carousel owl-theme">
                @foreach($eachcategory['data'] as $key => $channel)
                    <div class="item">
                        <div class="packet">
                            <figure>
                                <?php $program_slug = htmlentities($channel['program_slug']); ?>
                                <a href="{{ URL::to('program/packets/'.$program_slug)}}" title="{{ $channel['program_title'] }}">
                                    <?php $url = !empty($channel['program_cover_media']) ? URL::to('media_image/'.$channel['program_cover_media']) : URL::asset($theme.'/img/default_channel.png'); ?>
                                    <img src="{{$url}}" alt="Channel" class="packet-img img-responsive">
                                </a>
                                <div class="channel-label channel-label1" title="{{ $channel['program_title'] }}"><span>{{ $channel['program_title'] }}</span></div>
                                <!--package tooltip starts here-->
                                <?php $package_name = array_get($user_enrollment, 'catergory_with_program.package_name', []) ?>
                                    @if(array_key_exists($channel->program_id, $package_name))
                                        <a class="pckg-name test-pack-icon tooltip tooltip-effect-1" href="#">
                                            <i class="fa fa-info"></i>
                                            <span class="tooltip-content">{{ str_limit($package_name[$channel->program_id]['package_title'], 20) }}</span>
                                        </a>
                                     @endif
                                <!--package tooltip starts here-->
                            </figure>
                        </div><!--packet-->
                    </div><!--item-->
                @endforeach
            </div>
        </div>
    </div><!--ENd Packets div-->
    <!--end content feed 1-->
    @endforeach
@endif