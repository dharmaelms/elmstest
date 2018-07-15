<?php const TWELVE = 12; ?>
@section('content')
    <style>
        .dashboard1 {
            padding-top: 0;
        }
        .height33{
            height: 33px;
        }
        .btn-view{color: #fff !important;margin-right: 10px;}
        .btn-view:hover{color: #fff !important;}
        .panel-body1{padding: 30px 0px;}
        .up-down-arrow{padding: 15px !important;}
    </style>
    <link rel="stylesheet" type="text/css"
          href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}"/>
    <link rel="stylesheet" type="text/css"
          href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}"/>

    <link rel="stylesheet" type="text/css"
          href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}"/>
    <link rel="stylesheet" type="text/css"
          href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}"/>
    <script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>
    <div class="row dashboard1">
        @if(array_get($lhs_menu_settings->setting, 'programs', 'on') == "on")
            <div class="col-lg-9 col-md-12 col-sm-12 col-xs-12 dashboard7 db-main-content" id="db-maincontent">
                <div class="row">
                    <div class="col-md-12">
                        <h3 class="black font-weight-500 margin-0 border-btm padding-btm-4 height33">
                            @if(array_get($general->setting, 'watch_now', 'on') == 'on')
                                <span class="font-13 pull-right blue margin-top-10">
                                    <a href="{{ URL::to('program/what-to-watch') }}">{{ Lang::get('dashboard.watch_now') }}</a>
                                </span>
                            @endif
                        </h3>
                    </div>
                </div>
                <br>
                @if($data['status'])
                    @foreach($data['results']['data'] as $key_category => $category)

                        <div class="row margin-0">
                            <div class="col-md-12">
                                <h4 class="margin-top-10"><a>{{ $category['title'] }}</a></h4>
                            </div>
                            <div class="col-md-12 nav-space sm-margin border-btm nav-space-dashboard7">
                                    @foreach($category['data'] as $key=>$channel)
                                        @if($key < TWELVE)
                                        <div class="item white-bg content-row-dashboard7">
                                            <a href="{{ URL::to('program/packets/'.$channel->program_slug)}}"
                                               title="{{ $channel->program_title }}">
                                                <div class="packet packet-container-dashboard7">
                                                    <div class="packet-img-cont-dashboard7">
                                                        @if( isset($channel->program_cover_media) && !empty($channel->program_cover_media) )
                                                            <img src="{{URL::to('media_image/'.$channel->program_cover_media)}}" alt="Channel" class="packet-img img-responsive packet-img-dashboard7">
                                                        @else
                                                        <img src="{{URL::asset($theme.'/img/default_channel.png')}}"
                                                                 alt="Channel" class="packet-img img-responsive packet-img-dashboard7">
                                                        @endif
                                                    </div>
                                                    <div>
                                                        <p class="packet-title packet-title-dashboard7">
                                                        {{ str_limit($channel->program_title, $limit = 32, $end = '...') }}</p>
                                                    </div>
                                                </div><!--packet-->
                                            </a>
                                        </div><!--packet div-->
                                        @endif
                                    @endforeach
                                    @if(count($category['data']) > TWELVE)
                                    <div id="accordion33" class="panel-group accordion">
                                        <div class="panel panel-default transparent-bg">
                                            <div class="panel-heading">
                                                <h4 class="panel-title m-btm-12">
                                                    <div class="row">
                                                        <div class="col-md-9 col-sm-9"></div>
                                                            <div class="col-md-3 col-sm-3">
                                                                <a class="accordion-toggle accordion-toggle-styled up-down-arrow" href="#collapse{{$key_category}}" data-parent="#accordion33" data-toggle="collapse" aria-expanded="true"></a>
                                                                <span class="badge pull-right"></span>
                                                            </div>
                                                    </div>
                                                </h4>
                                            </div>
                                            <div id="collapse{{$key_category}}" class="panel-collapse collapse in" aria-expanded="true" style="">
                                                <div class="panel-body panel-body1">
                                                    @foreach($category['data'] as $key=>$channel)
                                                        @if($key >= TWELVE)
                                                            <div class="item white-bg content-row-dashboard7">
                                                                <a href="{{ URL::to('program/packets/'.$channel->program_slug)}}" title="{{ $channel->program_title }}">
                                                                    <div class="packet packet-container-dashboard7">
                                                                        <div class="packet-img-cont-dashboard7">
                                                                           @if( isset($channel->program_cover_media) && !empty($channel->program_cover_media) )
                                                                                <img src="{{URL::to('media_image/'.$channel->program_cover_media)}}" alt="Channel" class="packet-img img-responsive packet-img-dashboard7">
                                                                            @else
                                                                                <img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Channel" class="packet-img img-responsive packet-img-dashboard7">
                                                                            @endif
                                                                        </div>
                                                                        <div>
                                                                            <p class="packet-title packet-title-dashboard7">{{ str_limit($channel->program_title, $limit = 32, $end = '...') }}</p>
                                                                        </div>
                                                                    </div><!--packet-->
                                                                </a>
                                                           </div><!--packet div-->
                                                        @endif
                                                    @endforeach
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                     @endif

                                <!-- </div> -->
                            </div>
                        </div><!--ENd Packets div-->
                    @endforeach
                @else
                    <p>{{ Lang::get("dashboard.$site.no_channels") }} </p>
                @endif
            </div>
        @endif
        <!-- content, courses, assessments -->
        <div class="col-md-3 col-sm-12 col-xs-12 db-side-bar" id="db-sidebar">
            <div class="annoucements-div1 sm-margin">
                <div class="title-border"><h4
                            class="black font-weight-500">{{trans("dashboard.$site.announcements")}}</h4>
                    <div class="nav-buttons pull-right">
                        <div class="prev" id="prevPageAnnouncement"><a href=""><i class="fa fa-angle-left"></i></a>
                        </div>
                        <div class="next" id="nextPageAnnouncement"><a href=""><i class="fa fa-angle-right"></i></a>
                        </div>
                    </div>
                </div>
                <ul class="accordion" id="announcement">
                </ul>
                <p class="center"><a href="{{ URL::to('/announcements') }}" class="black font-12"><img
                                src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More" title="More"
                                style="display:inline-block;"> {{ trans('dashboard.more') }}</a></p>
            </div><!-- announcements -->
        @if($general->setting['events'] == "on")
                <div class="event-container1 sm-margin" style="display:none;">
                    <div class="title-border"><h4 class="black font-weight-500">{{trans("dashboard.$site.events")}}</h4>
                        <div class="nav-buttons pull-right">
                            <div class="prev" id="prevPageEvent"><a href=""><i class="fa fa-angle-left"></i></a></div>
                            <div class="next" id="nextPageEvent"><a href=""><i class="fa fa-angle-right"></i></a></div>
                        </div>
                    </div>
                    <!-- <p class="blue center"><b>UPCOMING EVENTS</b></p> -->
                    <ul id="events-list">
                    </ul>
                <p class="center"><a href="{{ URL::to('/event') }}" class="black font-12"><img
                                    src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More"
                                    title="More"> {{ trans('dashboard.more') }}</a></p>
                </div><!-- events -->
                <div class="calendar1 sm-margin">
                    <div class="title-border"><h4 class="black font-weight-500">{{trans("dashboard.calendar")}}</h4>
                    </div>

                    <div id="custom-inner" class="custom-inner">
                        {{-- <div class="custom-header clearfix">
                          <nav>
                            <span id="custom-prev" class="custom-prev"></span>
                            <span id="custom-next" class="custom-next"></span>
                          </nav>
                          <h2 class="margin-0 black"><span id="custom-month" class="custom-month"></span> <span id="custom-year" class="custom-year"></span></h2>
                        </div> --}}
                        <div id="calendar" class="fc-calendar-container"></div>
                    </div>
                </div>
            @endif
        </div>
        <!-- announcements, evenst, calendar -->
    </div>

    <script type="text/javascript"
            src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/js/jquery.calendario.js') }}"></script>
    <script>

        $(document).ready(function () {
            <?php if ($general->setting['events'] == "on") { ?>
            var day = new Date().getDate();
            var calendar = $("#calendar").calendario({
                displayWeekAbbr: true,
                onDayClick: function ($el, $content, dateProperties) {
                    day = dateProperties.day;
                    window.location = "{{ URL::to('/event?show=custom') }}" + "&day=" + dateProperties.day + "&month=" + dateProperties.month + "&year=" + dateProperties.year;
                    $("#calendar").find('.date-active').removeClass('date-active').css("border", "");
                    $el.closest("div").css('border', '1px solid #ef6c6c').addClass("date-active");//date-active is a dummy class
                }
            });

            function updateMonthYear() {
                $.ajax({
                    type: "GET",
                    url: "{{ url('event/cal-dates') }}?month=" + calendar.getMonth() + "&year=" + calendar.getYear()
                })
                        .done(function (response) {
                            calendar.setData(response);
                        })
                        .fail(function (response) {
                            alert("Error while updating the calendar. Please try again");
                        });
            }

            updateMonthYear();
            <?php } ?>
        });

        /** announcement starts here */
        var announcement = (function () {
            var limit = 2,
                    page = 1,
                    next = true,
                    lastPage = 1,
                    position = "ul#announcement",
                    endPage = 1,
                    $prevPage = $("#prevPageAnnouncement"),
                    $nextPage = $('#nextPageAnnouncement'),
                    url = "{{ URL::to('/dashboard/announcements')}}",
                    redirectUrl = "{{ URL::to('/announcements/index')}}",
                    content = '<li class="xs-margin" data-page-id="{page}">' +
                            '<p class="black margin-0" style="word-wrap: break-word;">{title}</p>' +
                            '<p class="font-10 gray margin-0" data-announcement-id="{id}">{description}</p>' +
                            '<p class="font-10"><a href="{url}"  class="pull-right">View More</a></p>' +
                            '</li>';

            function init() {
                $nextPage.on('click', function () {
                    nextPage();
                    return false;
                });
                $prevPage.on('click', function () {
                    prevPage();
                    return false;
                })
                nextPage();
            }

            function nextPage() {
                if (page < lastPage) {
                    $(position).find('li').fadeOut('slow');
                    $(position).find('[data-page-id="' + (page) + '"]').fadeIn('slow');
                    page++;
                    return false;
                }
                if (!next) {
                    return false;
                }
                var quizRequest = $.ajax({
                    method: "GET",
                    url: url + "/" + page + "/" + limit,
                    dataType: "json"
                });
                quizRequest.done(function (response) {
                    if (typeof response.status !== 'undefined') {
                        if (!response.status) {
                            if (page == 1) {
                                $(position).append('<tr><td colspan="5" class="center">' + response.message + '</td></tr>');
                            }
                            next = false;
                            return false;
                        }
                    }
                    currentPage = page - 1;
                    $(position).find('[data-page-id="' + (currentPage) + '"]').fadeOut('slow');
                    if (typeof response.results !== 'undefined') {
                        $.each(response.results, function (index, result) {
                            $(position).append(
                                    content.replace(/{page}/g, page)
                                            .replace(/{id}/g, result.id)
                                            .replace(/{title}/g, stripText(result.title, 60))
                                            .replace(/{description}/g, result.description)
                                            .replace(/{url}/g, redirectUrl + "/" + result.id)
                                            .replace(/{id}/g, redirectUrl + "/" + result.id)
                            );
                        });
                        if (response.results.length < limit) {
                            next = false;
                            endPage = page;
                        }
                    }
                    page++;
                    lastPage++;
                });
            }

            function prevPage() {
                if (page <= 2) {
                    return false;
                }
                currentPage = page;
                previousPage = page - 1;
                $(position).find('[data-page-id="' + (currentPage - 1) + '"]').fadeOut('slow');
                $(position).find('[data-page-id="' + (previousPage - 1) + '"]').fadeIn('slow');
                page--;
                next = true;
            }

            init();
            return {
                init: init,
                nextPage: nextPage,
                prevPage: prevPage,
            };
        })();
        /** announcement ends here */

        /** event starts here */

        var event = (function () {
            var limit = 2,
                    page = 1,
                    next = true,
                    lastPage = 1,
                    eventsContainer = '.event-container1',
                    position = "ul#events-list",
                    endPage = 1,
                    isLoading = false,
                    $prevPage = $("#prevPageEvent"),
                    $nextPage = $('#nextPageEvent'),
                    url = "{{ URL::to('/dashboard/events')}}",
                    redirectUrl = "{{ URL::to('event')}}?show=custom",
                    joinNowLink = "{{ URL::to('/event/live-join')}}",

                    uid = {{Auth::user()->uid}};

            content =
                    '<li data-page-id="{page}" data-event-id="{id}" data-time="{start_time}" data-type="{type}">' +
                    '<div class="date">' +
                    '<div class="font-16">{date}</div>' +
                    '<div class="font-weight-600 black font-12">{time}</div>' +
                    ' </div>' +
                    '<div class="event-name"> ' +
                    '<p class="black margin-0">{title}</p>' +
                    '<p class="font-10 gray margin-0">{description}</p>' +
                    '<p><a class="join-now" href={join-now} style="display:none">{link}</a></p>' +
                    '<p class="font-10"><a href="{url}" class="pull-right">View More</a></p>' +
                    '</div>'
            '</li>';
            function init() {
                $nextPage.on('click', function (e) {
                    e.preventDefault();

                    if (isLoading) {
                        return false;
                    }
                    nextPage();
                });

                $prevPage.on('click', function (e) {
                    e.preventDefault();
                    if (isLoading) {
                        return false;
                    }
                    prevPage();
                });

                nextPage();
            }

            function nextPage() {
                if (isLoading || !next) {
                    return false;
                }
                if (page < lastPage) {
                    $(position).find('tr').fadeOut('slow');
                    $(position).find('[data-page-id="' + (page) + '"]').fadeIn('slow');
                    page++;
                    return false;
                }
                isLoading = true;


                var postRequest = $.ajax({
                    method: "GET",
                    url: url + "/" + page + "/" + limit,
                    dataType: "json"
                });
                postRequest.done(function (response) {
                    if (typeof response.status !== 'undefined') {
                        if (!response.status) {
                            if (page == 1) {
                                $(position).append('<li>' + response.message + '</li>');
                            }
                            next = false;
                            return false;
                        }
                    }
                    currentPage = page - 1;


                    $(position).find('[data-page-id="' + (currentPage) + '"]').fadeOut('slow');
                    if (typeof response.results !== 'undefined') {
                        $(eventsContainer).show();
                        $.each(response.results, function (index, result) {
                            $(position).append(
                                    content.replace(/{page}/g, page)
                                            .replace(/{id}/g, result.id)
                                            .replace(/{date}/g, result.date)
                                            .replace(/{time}/g, result.time)
                                            .replace(/{title}/g, stripText(result.name, 25))
                                            .replace(/{description}/g, stripText(result.description, 200))
                                            .replace(/{url}/g, redirectUrl + "&day=" + result.day + "&month=" + result.month + "&year=" + result.year)
                                            .replace(/{link}/g, uid == result.event_host_id ? "{{ Lang::get('event.start_now')}}" : "{{ Lang::get('event.join_now') }}")
                                            .replace(/{start_time}/g, result.start_time)
                                            .replace(/{type}/g, result.type)
                                            .replace(/{join-now}/g, joinNowLink + "/" + result.id)
                            );
                        });
                        if (response.results.length < limit) {
                            next = false;
                            endPage = page;
                        }

                        showJoinNow();
                    }

                    if (typeof response.faqs !== 'undefined') {
                        $.each(response.faqs, function (index, faq) {
                            $(position).find('[data-post-questions= "' + index + '"]').children().html(faq);
                        });
                    }

                    if (typeof response.statuses !== 'undefined') {
                        $.each(response.statuses, function (index, status) {
                            if (status == 'completed') {
                                $class = "success";
                            } else if (status == 'pending') {
                                $class = 'watched';
                            } else {
                                $class = 'new';
                            }
                            $(position).find('[data-post-status="' + index + '"]').html('<span class="badge badge-' + $class + '">' + status + "</span>");
                        });
                    }

                    if (typeof response.programs !== 'undefined') {
                        $.each(response.programs, function (index, program) {
                            $(position).find('[data-program-name="' + program.program_slug + '"]').html(stripText(program.program_title, 20));
                        });
                    }
                    page++;
                    lastPage++;
                });
                isLoading = false;

            }

            function prevPage() {
                if (isLoading) {
                    return false;
                }
                if (page <= 2) {
                    return false;
                }
                isLoading = true;
                currentPage = page;
                previousPage = page - 1;
                $(position).find('[data-page-id="' + (currentPage - 1) + '"]').fadeOut('slow');
                $(position).find('[data-page-id="' + (previousPage - 1) + '"]').fadeIn('slow');
                page--;
                next = true;
                isLoading = false;

            }

            init();
            return {
                init: init,
                page: page,
                nextPage: nextPage,
                prevPage: prevPage,
            };
        })();
        /** events ends here */

        var showJoinNow = function () {
            if ($('ul#events-list').is(':visible')) {
                $('ul#events-list').find('li').each(function () {
                    if ($(this).data('type') == 'live') { //if it is live event
                        var eventTime = new Date($(this).data('time') * 1000).getTime();
                        var currentTime = new Date().getTime();
                        if (eventTime < currentTime || eventTime <= currentTime + (15 * 60 * 1000)) {
                            $(this).find('.join-now').show();
                        }
                    }
                });
            }
        }

        setInterval(function () {
            showJoinNow()
        }, 60000); //run every minutes to check for event start time

        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
            "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];

        var dateToTimestamp = function (time) {
            var date = new Date(time);
            return date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        }

        var quizDurationToTime = function (time) {
            if (typeof time == 'undefined' || time == 0) {
                return 'N/A';
            }
            return time;
        }

        var quizDateToTimestamp = function (data) {
            if (data == 0 || typeof data == undefined) {
                return '--';
            }
            var d = new Date(data * 1000);
            return d.getDate() + " " + monthNames[d.getMonth()] + " " + d.getFullYear();
        }

        var timestampToDateMonth = function (time) {
            if (time == 0 || typeof time == undefined) {
                return '--';
            }
            var date = new Date(time);
            return date.getDate() + " " + monthNames[date.getMonth()];
        }

        var timestampToTime = function (time) {
            if (time == 0 || typeof time == undefined) {
                return '--';
            }
            var date = new Date(time);
            return date.toLocaleTimeString().replace(/:\d+ /, ' ');
        }

        var timestampToDate = function (timestamp) {
            if (timestamp == 0) {
                return "No time limit";
            }
            var date = new Date(timestamp * 1000);
            return date.getDate() + " " + monthNames[date.getMonth()] + " " + date.getFullYear();
        };

        var stripText = function (text, limit) {
            return text.length > limit ? text.substr(0, limit) + '..' : text;
        }

    </script>

    <script type="text/javascript">
        function random() {
            $('.owl-carousel').each(function (pos, value) {
                $(value).owlCarousel({
                    items: 4,
                    itemsDesktop: [1199, 3],
                    itemsDesktopSmall: [980, 3],
                    itemsTablet: [768, 2],
                    itemsMobile: [479, 1],
                    singleItem: false,
                    itemsScaleUp: false,
                    navigation: true,
                    navigationText: [
                        "<i class='fa fa-caret-left'></i>",
                        "<i class='fa fa-caret-right'></i>"
                    ],
                    beforeInit: function (elem) {

                    }
                });
            });
        }
        $(document).ready(function () {
            //Sort random function
            random();
        });
    </script>
@stop
