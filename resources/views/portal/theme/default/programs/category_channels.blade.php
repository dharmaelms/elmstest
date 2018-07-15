@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}" />
<script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>

@if(isset($user_enrollment) && !empty($user_enrollment))
<div class="tabbable tabbable-tabdrop color-tabs">
    <ul class="nav nav-tabs center">
        @if(isset($general))
            @if ($general->setting['general_category_feeds'] == "on")
            <li class="active"><a href="{{URL::to('program/category-channel')}}"><i class="fa fa-rss-square"></i>&nbsp; My <?php echo Lang::get('program.course');?></a></li>
            @endif
            @if($general->setting['watch_now'] == 'on')
                <li><a href="{{URL::to('program/what-to-watch')}}"><i class="fa fa-video-camera"></i>&nbsp; <?php echo Lang::get('program.tab_watch_now');?></a></li>
            @endif
            @if($general->setting['posts'] == 'on')
                <li><a href="{{URL::to('program/my-feeds')}}"><i class="fa fa-rss-square"></i>&nbsp; <?php echo Lang::get('program.tab_posts');?></a></li>
            @endif
            @if($general->setting['favorites'] == 'on')
                <li ><a href="{{URL::to('program/favourites')}}"><i class="fa fa-heart"></i>&nbsp; <?php echo Lang::get('program.tab_favorites'); ?></a></li>
            @endif

            @if(!config("app.ecommerce"))
                @if ($general->setting['more_feeds'] == "on")
                <li>
                    <a href="{{URL::to('program/more-feeds')}}">
                        <i class="fa fa-rss"></i>&nbsp; <?php echo Lang::get('program.tab_other_channels');?>
                    </a>
                </li>
                @endif
            @endif
        @endif
    </ul>

    <div class="tab-content">
        <div class="tab-pane active category-page-css">
            <div class="row">
                <div class="col-md-12" id="category-program">
                    @include('portal.theme.default.programs.category_channel_ajax_load', ['user_enrollment' => $user_enrollment])

                </div>
            </div>
        </div>
        <!--END Favourites section-->
    </div>
</div>

<script type="text/javascript">
    function random(){
        $('.owl-carousel').each(function(pos,value){
            $(value).owlCarousel({
                items:4,
                navigation: true,
                navigationText: [
                "<i class='fa fa-caret-left'></i>",
                "<i class='fa fa-caret-right'></i>"
                ],
                beforeInit : function(elem){

                }
            });
        });
    }
    $(document).ready(function() {
    //Sort random function
        random();
    });

$(document).ready(function () {
    var url='{{ URL::to('/') }}';
    var pageno=1;
    var count='<?php echo count($user_enrollment['catergory_with_program']['data']); ?>';
    var stop = flag = true;
    $(window).scroll(function() {
        if(count > 4 && stop) {
            if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 100)) {
                if(flag) {
                    flag = false;
                    $.ajax({
                        type: 'GET',
                        url: "{{ url('program/user-category-channel?pageno=') }}"+pageno
                    }).done(function(e) {
                        if(e.status == true && e.data != '') {
                            $('#category-program').append(e.data);
                            stop=true;
                            flag = true;                             
                        } else {
                            stop = false;
                            $('#category-program').append(
                                 "<div class='col-md-12 center l-gray'><p><strong>{{ Lang::get('pagination.no_more_records') }}</strong></p></div>"
                            );  
                        }
                        pageno++;
                        random();
                    }).fail(function(e) {
                        alert('Failed to get the data');
                    });
                }
            }
        }
    });
});
</script>
@else
{{ (isset($error)) ? $error : '' }}
@endif
@stop