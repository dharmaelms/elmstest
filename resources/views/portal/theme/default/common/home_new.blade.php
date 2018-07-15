@section('content')
    <?php
    use App\Model\Common;
    use App\Model\SiteSetting;
    use App\Model\Banners;
    use App\Model\PartnerLogo;
    ?>

<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/slider-revolution-slider/rs-plugin/css/settings.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/front-end/style-revolution-slider.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/front-end/style-responsive.css')}}" />

<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}" />
 <link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/home_default.css')}}" />
<script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>

<div class="home1">
    <div class="container container-head-top">
        <div class="row">
        <!-- BEGIN SLIDER -->
            <div class="page-slider col-xs-12 col-sm-7 col-md-7 col-lg-8 pull-left">
                <div class="fullwidthbanner-container revolution-slider margin-btm-0">
                    <div class="fullwidthbanner2">
                    <ul id="revolutionul">
                        <?php
                        $banners=Banners::getAllBanners("ACTIVE");
                        $show_default_banner = true;
                        ?>
                        @if(isset($banners) && ($banners != '') && (!empty($banners)))
                        @foreach($banners as $banner)
                        <?php
                        $banner_file_name=$banner['file_client_name'];
                        $banner_file_path=config('app.site_banners_path').$banner_file_name;
                        $show_default_banner = false;
                        ?>
                        @if (file_exists($banner_file_path))
                            <li data-transition="fade" @if(isset($banner['banner_url']) && ($banner['banner_url'] != '') && (!empty($banner['banner_url']))) data-link="{{$banner['banner_url']}}" data-target="_blank" @endif data-slotamount="8" data-masterspeed="700" data-delay="9400">
                                <img src="{{URL::to($banner_file_path)}}" alt="Banner">
                            @if(!empty($banner['description']))
                                <div class="black-layer">
                                    <div class="container">
                                        <div class="row">
                                            <div class="col-md-offset-1 col-md-12 col-xs-12 col-sm-12">{{str_limit($banner['description'], $limit = 150, $end = '...')}}</div>
                                        </div>
                                    </div>
                                </div>
                            @endif
                        </li>
                        @endif
                        @endforeach
                        @endif
                        @if ($show_default_banner)
                        <li data-transition="fade" data-slotamount="8" data-masterspeed="700" data-delay="9400">
                            <img src="{{config('app.default_banner_path')}}" alt="Banner">
                            <div class="black-layer">
                                <div class="container">
                                    <div class="row">
                                        <div class="col-md-offset-1 col-md-12 col-xs-12 col-sm-12">{{str_limit(trans('banner.banner_description'), $limit = 150)}}</div>
                                    </div>
                                </div>
                            </div>
                        </li>
                        @endif
                        </ul>
                    </div>
                </div>
            </div>
        <!-- END SLIDER -->
        <!--Form starts-->
        <div class="col-xs-12 col-sm-5 col-md-5 col-lg-4 pull-right">
            <div class="right-cont pull-right">
                <?php
                $site_logo=SiteSetting::module('Contact Us', 'site_logo');
                if (isset($site_logo) && !empty($site_logo)) {
                    $logo=config('app.site_logo_path').$site_logo;
                } else {
                    $logo=config('app.default_logo_path');
                }

                ?>
                <div class="logo-container center">
                    <a href="#">
                        <img src="{{ URL::to($logo) }}" width="206">
                    </a>
                </div>
                <div class="login">
                    <div class="form-group">
                        <span id="error_text_popup" name="error_text_popup" class="text-danger" style="margin-top: -20px;"></span>
                    </div>
                    <form action="#" class="default-form xs-margin" role="form" id="signin_popup" name="signin_popup" method="post">
                        <input type="hidden" name="login_url" id="login_url" value="{{URL::to('auth/login')}}">
                        <input type="hidden" name="email_verification" id="email_verification" value="<?php echo config('app.email_verification'); ?>">
                        <input type="hidden" name="baseurl" id="baseurl" value="{{URL::to('/')}}">
                        <input type="hidden" name="dashboard_url" id="dashboard_url" value="{{URL::to('/dashboard/')}}">
                        <input id="email" class="name input-txt" name="email" placeholder="Email/Username" type="text">
                        <input id="password" class="psswd input-txt" name="password" placeholder="Password" type="password">
                        <button class="btn-sbmit" type="submit">{{ trans('user.sign_in') }}</button>
                        <div class="text_cont center">
                            <a href="{{URL::to('password/forgot')}}">{{ trans('user.forgot_your_password') }}</a>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!--Form ends-->
        <div class="clearfix"></div>
        </div>
    </div>
    <div class="container">
    <div class="lg-margin"></div>
    @if($SiteSetting['setting']['UpcomingCourses']['enabled'] == 'on' && !empty($upcoming_courses))
        <div class="row lg-margin upcoming-courses">
            <div class="col-md-12 center">
                <h1 class="font-weight-500 black myellow-border margin-bottom-30 font-28">{{$SiteSetting['setting']['UpcomingCourses']['display_name']}}</h1>
            </div>
            <div class="col-md-12 nav-space">
                <div class="owl-carousel owl-carousel5 md-margin">
                    @foreach($upcoming_courses as $upcoming)
                        @if(!isset($upcoming['parent_id']) || $upcoming['parent_id'] == 0)
                            <div>
                                <div class="packet">
                                    <figure>
                                        <a href="{{ URL::to('catalog/course/'.$upcoming['program_slug']) }}" title="{{$upcoming['program_title']}}">
                                            @if(isset($upcoming['program_cover_media']) && !empty($upcoming['program_cover_media']))
                                                <img src="{{URL::to('media_image/'.$upcoming['program_cover_media'])}}" alt="{{$upcoming['program_title']}}" class="packet-img img-responsive">
                                            @else
                                                <img src="{{URL::to($theme.'/img/default_channel.png')}}" alt="{{$upcoming['program_title']}}" class="packet-img img-responsive">
                                            @endif
                                        </a>
                                    </figure>
                                    <div>
                                        <p class="packet-title"><a href="#"><strong>{{str_limit($upcoming['program_title'], $limit = 30, $end = '...')}}</strong></a></p>
                                        <p class="packet-data">
                                            <span class="left">
                                                <span class="red">{{ Lang::get('announcement.start_date') }}</span> <br>
                                                <?php
                                                if (Auth::check()) {
                                                    $timezone = Auth::user()->timezone;
                                                } else {
                                                    $timezone = config('app.default_timezone');
                                                }
                                                ?>
                                                {{Timezone::convertFromUTC('@'.$upcoming['program_display_startdate'], $timezone, 'd M\, Y')}}
                                            </span>
                                            <span class="right">
                                                <span class="red">{{ Lang::get('announcement.end_date') }}</span> <br>
                                                            {{Timezone::convertFromUTC('@'.$upcoming['program_display_enddate'], $timezone, 'd M\, Y')}}
                                            </span>
                                        </p>
                                    </div>
                                    <div class="course-detail">
                                        <p class="font-13">{{str_limit($upcoming['program_description'], $limit = 180, $end = '...')}}</p>
                                        <p class="center font-13"><a class="btn btn-success" href="{{ URL::to('catalog/course/'.$upcoming['program_slug']) }}">{{Lang::get('announcement.view_course')}}</a></p>
                                    </div>
                                </div>
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        <!-- <div class="col-md-12 col-sm-12 col-xs-12">
        <p class="center md-margin"><a href="{{URL::to('/catalog')}}" class="btn btn-primary">View More</a></p>
        </div> -->
        </div>
    @endif
    <!-- upcoming products -->
    @if($SiteSetting['setting']['PopularCourses']['enabled'] == 'on' && !empty($popular_courses))
        <div class="row lg-margin upcoming-courses">
            <div class="col-md-12 center">
                <h1 class="font-weight-500 black myellow-border margin-bottom-30 font-28">{{$SiteSetting['setting']['PopularCourses']['display_name']}}</h1>
            </div>
            <div class="col-md-12 nav-space">
                <div class="owl-carousel owl-carousel5 md-margin">
                    @foreach($popular_courses as $popular)
                        <div>
                            <div class="packet">
                                <figure>
                                    <a href="{{ URL::to('catalog/course/'.$popular['program_slug']) }}" title="{{$popular['program_title']}}">
                                        @if(isset($popular['program_cover_media']) && !empty($popular['program_cover_media']))
                                            <img src="{{URL::to('media_image/'.$popular['program_cover_media'])}}" alt="{{$popular['program_title']}}" class="packet-img img-responsive">
                                        @else
                                            <img src="{{URL::to($theme.'/img/default_channel.png')}}" alt="{{$popular['program_title']}}" class="packet-img img-responsive">
                                        @endif
                                    </a>
                                </figure>
                                <div>
                                    <p class="packet-title"><a href="#"><strong>{{str_limit($popular['program_title'], $limit = 30, $end = '...')}}</strong></a></p>
                                    <p class="packet-data">
                                        <span class="left">
                                            <span class="red">{{ Lang::get('announcement.start_date') }}</span> <br>
                                            <?php
                                            if (Auth::check()) {
                                                $timezone = Auth::user()->timezone;
                                            } else {
                                                $timezone = config('app.default_timezone');
                                            }
                                            ?>
                                            {{Timezone::convertFromUTC('@'.$popular['program_display_startdate'], $timezone, 'd M\, Y')}}
                                        </span>
                                        <span class="right">
                                            <span class="red">{{ Lang::get('announcement.end_date') }}</span> <br>
                                                        {{Timezone::convertFromUTC('@'.$popular['program_display_enddate'], $timezone, 'd M\, Y')}}
                                        </span>
                                    </p>
                                </div>
                                <div class="course-detail"><p class="font-13">{{str_limit($popular['program_description'], $limit = 180, $end = '...')}}</p><p class="center font-13"><a class="btn btn-success" href="{{ URL::to('catalog/course/'.$popular['program_slug']) }}">{{ Lang::get('announcement.view_course') }}</a></p></div>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    @endif
        <!-- popular products -->
    @if(is_array($testimonials) && count($testimonials) > 0)
        @include('portal/theme/default/common/testimonial',['testimonials'=>$testimonials])
    @endif

    <?php
    $partners = PartnerLogo::getAllPartners("ACTIVE");
    ?>
    @if(isset($partners)  && (count($partners) != 0)  )
        <div class="row margin-bottom-30" id="clients">
            <div class="col-md-12 col-xs-12 col-sm-12 center">
                <h1 class="font-weight-500 black myellow-border margin-bottom-30 font-28">{{ Lang::get('partnerlogo.partners')  }}</h1>
            </div>

            <!-- partner logo code -->
            <div class="col-md-12 xs-margin">
                <div id="owl-demo" class="owl-carousel owl-theme nav-space1">
                    @foreach($partners as $partner)
                        <div class="item">
                            <figure>
                                <?php
                                $logo_name = $partner['partner_diamension'];
                                $logo =  Config::get('app.partner_logo_path').basename($logo_name);
                                $partner_description = (isset($partner['partner_description']) && !empty($partner['partner_description'])) ? str_limit($partner['partner_description'], $limit = 150, $end = '...') : 'No Description';
                                ?>
                                <div><a><img src="{{ URL::to($logo) }}" alt="Image" class="img-responsive xs-margin" height="50px" weight="50px" title="{{ $partner_description }}"></a></div>
                            </figure>
                            <p class="packet-title"><a data-toggle="tooltip" href="javascript:void(0)" title="{{ $partner_description }}" ><strong>{{str_limit($partner['partner_name'], $limit = 30, $end = '...')}}</strong></a></p>
                        </div><!--item-->

                    @endforeach
                </div>
            </div>

            <!-- view all partners code -->
            <div class="col-md-12 center xs-margin">
                <a href="{{ URL::to('/partners/partner-logo') }}" class="btn btn-primary">{{Lang::get('announcement.view_all')}}</a>
            </div>
        </div><!-- our partners -->
    @endif
    </div>

    <div class="darkgrey-bg">
        <div class="container home-page">
            <div class="row lg-margin">
                @if(Auth::check())
                    @if(isset($announcements) && !empty($announcements))
                        <div class="col-md-offset-1 col-md-5 col-sm-6 col-xs-12">
                            <div class="md-margin"></div>
                            <div class="announce-tabs">
                                <h3><span class="black font-weight-500">{{Lang::get('announcement.announcements')}}&nbsp;&nbsp;|&nbsp;&nbsp;</span><a href="{{ URL::to('announcements?announcement=public') }}" class="font-16">{{Lang::get('announcement.view_all')}}</a></h3>
                                <ul>
                                    @foreach ($announcements as $key => $value)
                                        <li>
                                            <div class="col-md-12 padding-0">
                                                <div class="img-div"><img src="{{ URL::to('portal/theme/default/img/announce/announcementDefault.png') }}" alt="Announcement"></div>
                                                <div class="data-div">
                                                    <a href="{{ URL::to('announcements/index/'.$value['announcement_id'].'?announcement=public') }}">{{ $value["announcement_title"] }}</a>
                                                    <p class="font-11">{{Common::getPublishOnDisplay($value['schedule'])}}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div><!--announcements-->
                    @else
                        <div class="col-md-offset-1 col-md-5 col-sm-6 col-xs-12">
                            <div class="md-margin"></div>
                            <div class="announce-tabs">
                                <h3><span class="black font-weight-500">{{Lang::get('announcement.announcements')}}</span></h3>
                                <p>{{Lang::get('pagination.no_announce_published')}}</p>

                            </div>
                        </div>
                    @endif
                @else
                    @if(isset($announcements) && !empty($announcements))
                        <div class="col-md-offset-1 col-md-5 col-sm-6 col-xs-12">
                            <div class="md-margin"></div>
                            <div class="announce-tabs">
                                <h3><span class="black font-weight-500">{{Lang::get('announcement.announcements')}}&nbsp;&nbsp;|&nbsp;&nbsp;</span><a href="{{ URL::to('announcements?announcement=public') }}" class="font-16">{{Lang::get('announcement.view_all')}}</a></h3>
                                <ul>
                                    @foreach ($announcements as $key => $value)
                                        <li>
                                            <div class="col-md-12 padding-0">
                                                <div class="img-div"><img src="{{ URL::to('portal/theme/default/img/announce/announcementDefault.png') }}" alt="Announcement"></div>
                                                <div class="data-div">
                                                    <a href="{{ URL::to('announcements/index/'.$value['announcement_id'].'?announcement=public') }}">{{ $value["announcement_title"] }}</a>
                                                    <p class="font-11">{{Common::getPublishOnDisplay($value['schedule'])}}</p>
                                                </div>
                                            </div>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div><!--announcements-->
                    @else
                        <div class="col-md-offset-1 col-md-5 col-sm-6 col-xs-12">
                            <div class="md-margin"></div>
                            <div class="announce-tabs">
                                <h3><span class="black font-weight-500">{{Lang::get('announcement.announcements')}}</span></h3>
                                <p>{{Lang::get('pagination.no_announce_published')}}</p>

                            </div>
                        </div>
                    @endif
                @endif

                <?php $contact_us=SiteSetting::module('Contact Us')->setting;
                ?>
                <div class="col-md-5 col-md-offset-1  col-sm-6 col-xs-12">
                    <div class="md-margin"></div>
                    <h3><span class="black font-weight-500">{{Lang::get('announcement.contact_us')}}</h3>
                        @if(isset($contact_us['homepage']) && !empty($contact_us['homepage']))
                            <p>Homepage: <a href="//{{$contact_us['homepage']}}" target="_blank" >{{$contact_us['homepage']}}</a></p>
                        @endif
                        @if(isset($contact_us['company_name']) && !empty($contact_us['company_name']))
                            <p> <a href="#">{{$contact_us['company_name']}}</a></p>
                        @endif
                        @if(isset($contact_us['email']) && !empty($contact_us['email']))
                            <p>Email: <a href="#">{{$contact_us['email']}}</a></p>
                        @endif
                        @if(isset($contact_us['address']) && !empty($contact_us['address']))
                            <p>Address: <a href="#">{{$contact_us['address']}}</a></p>
                        @endif
                        @if(isset($contact_us['phone']) && !empty($contact_us['phone']))
                            <p>Phone No: <a href="#">{{$contact_us['phone']}}</a></p>
                        @endif
                        @if(isset($contact_us['mobile_no']) && !empty($contact_us['mobile_no']))
                            <p>Mobile No: <a href="#">{{$contact_us['mobile_no']}}</a></p>
                        @endif
                        @if(isset($contact_us['social_media']) && !empty($contact_us['social_media']))
                            <p>Social:
                                <?php $i=0;?>
                                @foreach($contact_us['social_media'] as $key => $value)
                                    <?php $i=$i+1;?>
                                    @if($i != 1)
                                        &nbsp;|&nbsp;
                                    @endif
                                    <a target="_blank" href="{{$value}}">{{$key}}</a>
                                @endforeach
                            </p>
                        @endif
                        <!-- <p>Support: <a href="#">Support@AcmeIndustries.com</a></p> -->
                        <!--   @if(isset($contact_us['phone']) && !empty($contact_us['phone']))
                            <p>Phone: <strong>{{$contact_us['phone']}}</strong></p>
                        @endif -->
                            <!-- <p>Download App:  &nbsp;<a href="#" class="icon-bg"><i class="fa fa-apple white"></i></a>&nbsp;|&nbsp;<a href="#" class="icon-bg"><i class="fa fa-android white"></i></a>&nbsp;|&nbsp;<a href="#" class="icon-bg"><i class="fa fa-windows white"></i></a></p> -->
                </div>
            </div>
        </div>
    </div>
</div>

<script src="{{ URL::asset($theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.revolution.min.js')}}"></script>
<script src="{{ URL::asset($theme.'/plugins/slider-revolution-slider/rs-plugin/js/jquery.themepunch.tools.min.js')}}"></script>
<script src="{{ URL::asset($theme.'/js/front-end/revo-slider-init.js')}}"></script>

<script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.min.js') }}" type="text/javascript"></script><!-- slider for products -->

<script>
    jQuery(document).ready(function() {
        RevosliderInit.initRevoSlider();
    });
</script>
<!--Plug-in Initialisation-->
<script type="text/javascript">
    $(document).ready(function() {
        //Vertical Tab
        $('#announceTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text($tab.text());
                $info.show();
            }
        });
    });
</script>

<!--Plug-in Initialisation-->
<script type="text/javascript">
    function random(){
        $('.owl-carousel').each(function(pos,value){
            $(value).owlCarousel({
                items:5,
                navigation: true,
                navigationText: [
                    "<img src='{{URL::to('portal/theme/default/img/icons/prev.png')}}' alt='Prev'>",
                    "<img src='{{URL::to('portal/theme/default/img/icons/next.png')}}' alt='next'>"
                ],
                beforeInit : function(elem){
                }
            });
        });
        $('.owl-carousel5').each(function(pos,value){
            $(value).owlCarousel({
                items:4,
                navigation: true,
                navigationText: [
                    "<img src='{{URL::to('portal/theme/default/img/icons/prev.png')}}' alt='Prev'>",
                    "<img src='{{URL::to('portal/theme/default/img/icons/next.png')}}' alt='next'>"
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
</script>
@stop