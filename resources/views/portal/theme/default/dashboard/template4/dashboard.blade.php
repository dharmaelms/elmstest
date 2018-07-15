@section('content')
    <style>
        .page-content-wrapper .page-content { margin-left: 0 !important; }
        .page-sidebar.navbar-collapse { display: none !important; max-height: none !important; }
        .page-header.navbar .menu-toggler.sidebar-toggler , .page-bar{ display: none; }
        .page-content { margin-top: 0px; padding: 0 20px 0px; background-color: #fff;}
    </style>
    <link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
    <link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />
    <link rel="stylesheet" href="//cdnjs.cloudflare.com/ajax/libs/font-awesome/4.4.0/css/font-awesome.min.css">
    <div class="dashboard3">


        <div class="row margin-bottom-20">
            @if($lhs_menu_settings->setting['programs'] == "on")
                <div class="col-md-5 col-sm-6 col-xs-12" id="db-sidebar">
                    <div class="row">
                        <div class="col-md-12 margin-bottom-10">
                        </div>
                        <div class="col-md-12 table-responsive blue-table margin-bottom-30">
                            <table id="programs">
                                <thead>
                                </thead>
                                <tbody> </tbody>
                            </table>
                            <div class="center cs-expand">
                                <a href="" class="btn btn-primary btn-sm" id="nextPageProgram"><i class="fa fa-angle-down"></i></a>
                            </div>
                        </div>
                    </div>
                </div>
        @endif
        <!-- content, courses, assessments -->

            <div class="col-md-7 col-sm-6 col-xs-12" id="db-maincontent">
                <div id="latestcourses">
                    @if($lhs_menu_settings->setting['programs'] == "on")
                        <div class="col-md-12">
                            <div class="xs-margin"></div>
                            <div class="table-responsive">
                                <div class="title-border1"><h4 class="black font-weight-500">{{Lang::get("dashboard.$site.posts")}}</h4>
                                    <div class="pull-right">
                                        <table><tr><td>
                                                    <div > <a href="{{ URL::to('program/what-to-watch') }}" class="btn pull-left">{{Lang::get("dashboard.view_all")}}</a> </div></td>
                                                <td>
                                                    <div class="nav-buttons pull-right">

                                                        <div class="prev"><a href="" id="prevPagePost"><i class="fa fa-angle-left"></i></a></div>
                                                        <div class="next"><a href="" id="nextPagePost"><i class="fa fa-angle-right"></i></a></div>
                                                    </div>
                                                </td></tr></table>
                                    </div>
                                </div>
                                <div  class="green-cs-border"><hr></div>
                                <div class="padding-lft-15" id="posts">
                                    <!-- Harish -->



                                </div>
                                <!-- Harish -->
                            </div>
                        </div>
                    @endif
                <!-- END Latest Content -->
                    @if($general->setting['assessments'] == "on")
                        <div id="assess">
                            <div class="col-md-12">
                                <div class="table-responsive">
                                    <div class="title-border2"><h4 class="black font-weight-500">{{Lang::get("dashboard.$site.assessments")}}</h4>
                                        <div class="pull-right">
                                            <table><tr><td>
                                                        <div > <a href="{{ URL::to('assessment') }}" class="btn pull-left">{{Lang::get("dashboard.view_all")}}</a> </div></td>
                                                    <td>
                                                        <div class="nav-buttons pull-right">

                                                            <div class="prev"><a href="" id="prevPageQuiz"><i class="fa fa-angle-left"></i></a></div>
                                                            <div class="next"><a href="" id="nextPageQuiz"><i class="fa fa-angle-right"></i></a></div>
                                                        </div>
                                                    </td></tr></table>
                                        </div>
                                    </div>
                                    <div  class="green-cs-border"><hr></div>

                                    <div class="padding-lft-15" id="assessments">
                                        <!-- assessments displayed here -->
                                    </div>
                                </div>
                            </div>
                        </div>
                @endif
                <!-- END Assessments -->
                </div>
            </div>
            <!-- announcements, evenst, calendar -->
        </div>
        <div class="row" id="db-sidebar">
            <div class="col-md-4 col-sm-4 col-xs-12 boxheight">
                <div class="annoucements-div1 sm-margin">
                    <div class="title-border">
                        <h4 class="black font-weight-500">{{Lang::get("dashboard.$site.announcements")}}</h4>
                        <!-- <span class="badge badge-danger">13</span></h4> -->
                            <div class="nav-buttons pull-right">
                                <div class="prev"><a href="" id="prevPageAnnouncement"><i class="fa fa-angle-left"></i></a></div>
                                <div class="next"><a href="" id="nextPageAnnouncement"><i class="fa fa-angle-right"></i></a></div>
                            </div>
                    </div>
                    <div class="myellow-cs-border"><hr></div>
                    <ul class="padding-lft-30" id="announcement">

                    </ul>
                    <p class="center"><a href="{{ URL::to('/announcements') }}" class="black font-12"><img src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More" title="More"> {{Lang::get("dashboard.more")}}</a></p>
                </div><!-- announcements -->
            </div>
            @if($general->setting['events'] == "on")
                <div class="col-md-4 col-sm-4 col-xs-12 boxheight">
                    <div class="calendar1 sm-margin">
                        <div class="title-border"><h4 class="black font-weight-500" style="line-height: 1.15;">{{Lang::get("dashboard.event_calendar")}}</h4>
                        </div>
                        <div class="red-cs-border"><hr></div>
                        <div id="custom-inner" class="custom-inner">
                            <div class="custom-header clearfix">
                                <h2 class="margin-0 black"><span id="custom-month" class="custom-month"></span> <span id="custom-year" class="custom-year"></span></h2>
                            </div>
                            <div id="calendar" class="fc-calendar-container"></div>
                        </div>
                    </div>
                </div>
                <div class="col-md-4 col-sm-4 col-xs-12 boxheight">
                    <div class="event-container1 sm-margin">
                        <div class="title-border">
                            <h4 class="black font-weight-500">Upcoming Events <span class="badge badge-danger" id="event-count">0</span></h4>
                            <div class="nav-buttons pull-right">
                                <div class="prev"><a href=""><i class="fa fa-angle-left"></i></a></div>
                                <div class="next"><a href=""><i class="fa fa-angle-right"></i></a></div>
                            </div>
                        </div>
                        <div class="blue-cs-border"><hr></div>
                        <ul class="padding-lft-30" id="events-list">
                        </ul>
                        <p class="center"><a href="{{ URL::to('event') }}" class="black font-12"><img src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More" title="More"> {{Lang::get("dashboard.more")}}</a></p>
                    </div><!-- events -->
                </div>
            @endif
        </div>
    </div>
    <script type="text/javascript" src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/js/jquery.calendario.js') }}"></script>
    <script>


        $(document).ready(function(){
                <?php if($general->setting['events'] == "on") { ?>
            var day = new Date().getDate();
            var calendar = $("#calendar").calendario({
                displayWeekAbbr : true,
                onDayClick: function($el, $content, dateProperties){
                    day = dateProperties.day;
                    if ($content.length > 0) {
                        showEvents(dateProperties.day, dateProperties.month, dateProperties.year);
                    } else {
                        $('#event-count').html('0');
                        $("#events-list").html('<li>No events found</li>');
                    }
                    $("#calendar").find('.date-active').removeClass('date-active').css("border","");
                    $el.closest("div").css('border','1px solid #ef6c6c').addClass("date-active");//date-active is a dummy class
                }
            });
            $month = $('#custom-month').html(calendar.getMonthName()),
                $year = $('#custom-year').html(calendar.getYear());
            $("#custom-month").html(calendar.getMonthName());
            $("#custom-year").html(calendar.getYear());
            $('#custom-next').on('click', function() {
                calendar.gotoNextMonth(updateMonthYear);
            });
            $('#custom-prev').on('click', function() {
                calendar.gotoPreviousMonth(updateMonthYear);
            });
            function updateMonthYear() {
                $.ajax({
                    type: "GET",
                    url: "{{ url('event/cal-dates') }}?month="+calendar.getMonth()+"&year="+calendar.getYear()
                })
                    .done(function(response) {
                        calendar.setData(response);
                    })
                    .fail(function(response) {
                        alert( "Error while updating the calendar. Please try again" );
                    });
                $month.html(calendar.getMonthName());
                $year.html(calendar.getYear());
            }
            updateMonthYear();
            xmlHTTPRequestObj = $.ajax({
                type : "GET",
                url : "{!! URL::to("event?show=today&context=user-dashboard") !!}",
                dataType : "json",
                contentType : "application/x-www-form-urlencoded; charset=UTF-8"
            });

            xmlHTTPRequestObj.done(function(response, textStatus, jqXHR){
                eventsContainer = $("#today-events");
                eventsContainer.after(response.data);
                calendar.setData(response);
            });
            function convertTime24to12(time24){
                var tmpArr = time24.split(':'), time12;
                if(+tmpArr[0] == 12) {
                    time12 = tmpArr[0] + ':' + tmpArr[1] + ' pm';
                } else {
                    if(+tmpArr[0] == 00) {
                        time12 = '12:' + tmpArr[1] + ' am';
                    } else {
                        if(+tmpArr[0] > 12) {
                            time12 = (+tmpArr[0]-12) + ':' + tmpArr[1] + ' pm';
                        } else {
                            time12 = (+tmpArr[0]) + ':' + tmpArr[1] + ' am';
                        }
                    }
                }
                return time12;
            }

            var eventsList = [];
            eventPage = 1;
            eventLimit = 2;

            showEvents = function(date, month, year) {
                var events = $.ajax({url:"{{ URL::to('event')}}?show=today&context=popover&day="+date+"&month="+ month+"&year="+year, dataType: 'json'});
                events.done(function(response){
                    var titles = '';
                    if(response.status) {
                        $('#event-count').html(response.data.events.length);
                        var a = [],
                            size = 2,
                            count = 0;
                        eventsList.length = 0;
                        $.each(response.data.events, function(index, data){
                            eventsList[count] = data;
                            count++;
                        });
                        eventPage = 1;
                        eventView(eventPage);
                    }
                    else {
                        titles = response.message;
                    }
                });
                events.fail(function(response){
                });
            };

            showEvents(calendar.today.getDate(), calendar.today.getMonth()+1, calendar.today.getFullYear());

            var eventPosition = '#events-list',
                url = "{{ URL::to('event?show=custom')}}",
                eventContent = '<li data-page-id="{page}">'+
                    '<div class="date">'+
                    '<div class="font-16">{date}</div>'+
                    '<div class="font-weight-600 black font-12">{time}</div>'+
                    '</div>'+
                    '<div class="event-name">'+
                    '<p class="black margin-0">{title}</p>'+
                    '<p class="font-10 gray margin-0">{description}</p>'+
                    '</div>'+
                    '<p class="font-10"><a href="{url}&day={day}&month={month}&year={year}"  class="pull-right">View More</a></p>'+
                    '</li>'+
                    '</ul>';

            var eventView = function(page) {
                $(eventPosition).find('li').remove();
                start = (page * eventLimit) - eventLimit;
                end = start + eventLimit;
                for(i=start; i<end; i++) {
                    if(typeof eventsList[i] != 'undefined') {
                        var data = eventsList[i];
                        $(eventPosition).append(
                            eventContent.replace(/{page}/g, page)
                                .replace(/{title}/g, data.event_name)
                                .replace(/{description}/g, data.event_description)
                                .replace(/{url}/g, url)
                                .replace(/{date}/g, timestampToDateMonth(data.start_time))
                                .replace(/{time}/g, convertTime24to12(data.start_time_label))
                                .replace(/{day}/g, day)
                                .replace(/{month}/g, calendar.getMonth())
                                .replace(/{year}/g, calendar.getYear())
                        );
                    }
                }
            }

            $('#prevPageEvent').on('click', function(){
                if(eventPage == 0) {
                    return false;
                }
                eventView(eventPage-1);
                eventPage--;
                return false;
            });

            $('#nextPageEvent').on('click', function(){
                eventView(eventPage+1);
                eventPage++;
                return false;
            });
            <?php } ?>
        });

        /** Program starts here */
        var program = (function(){
            var limit = 6,
                page = 1,
                next = true,
                lastPage = 1,
                position = "table#programs tbody",
                endPage = 1,
                view = $('.view'),
                $prevPage = $("#previousPageProgram"),
                $nextPage = $('#nextPageProgram'),
                url = "{{ URL::to('/dashboard/active-programs-count')}}",
                totalUrl = "{{ URL::to('/dashboard/active-programs-total-count')}}",
                redirectUrl = "{{ URL::to('/program/packets/')}}",
                totalRow = '<tr class="active">'+
                    '<td class="title" data-slug="all">{{trans('program.all_courses')}}</td>'+
                    '<td width="166"><span class="label label-blue black">{total_course}</span> {{trans('dashboard.courses')}}</td>'+
                    '<td width="152"><span class="label label-yellow black">{total_assessment}</span> {{trans('dashboard.assessements')}}</td>'+
                    '<td width="40"><a class="view-details" href="" data-slug="all"><i class="fa fa-caret-right"></i></a></td>'+
                    '</tr>',
                content = '<tr>'+
                    '<td class="title"  data-slug="{slug}">{title}</td>'+
                    '<td data-content={slug} width="166"><span class="label label-blue black">{total_course}</span> UNREAD CONTENT</td>'+
                    '<td data-assessment={slug} width="152"><span class="label label-yellow black">{total_assessment}</span> {{trans('dashboard.assessements')}}</td>'+
                    '<td width="40"><a class="view-details" href="" data-slug={slug}><i class="fa fa-caret-right"></i></a></td>'+
                    '</tr>';

            function init(){
                $nextPage.on('click', function(){
                    nextPage(); return false;
                });
                $prevPage.on('click', function(){
                    prevPage(); return false;
                })
                loadTotalCourses();
            }

            function loadTotalCourses() {
                var total = $.ajax({
                    url:totalUrl,
                    method: 'GET',
                });
                total.done(function(response){
                    if(typeof response.status !== 'undefined') {
                        if(!response.status) {
                            if(page == 1) {
                                $(position).append('<tr><td colspan="5" class="center">'+response.message+'</td></tr>');
                            }
                            next = false;
                            return false;
                        }
                    }
                    $('table#programs thead').append(
                        totalRow.replace(/{total_course}/g, response.program_count)
                            .replace(/{total_assessment}/g, response.quiz_count)
                    );
                    nextPage();
                });
            }

            function nextPage() {
                if(page < lastPage) {
                    $(position).find('tr').fadeOut('slow');
                    $(position).find('[data-page-id="'+(page)+'"]').fadeIn('slow');
                    page++;
                    return false;
                }
                if(!next) {
                    return false;
                }
                var programsRequest = $.ajax({
                    method: "GET",
                    url: url+"/"+page+"/"+limit,
                    dataType: "json"
                });
                programsRequest.done(function(response) {
                    if(typeof response.status !== 'undefined') {
                        if(!response.status) {
                            if(page == 1) {
                                $(position).html('<tr><td colspan="5" class="center">'+response.message+'</td></tr>');
                            }
                            next = false;
                            return false;
                        }
                    }
                    currentPage = page-1;
                    $(position).find('[data-page-id="'+(currentPage)+'"]').fadeOut('slow');
                    if (typeof response.programs !== 'undefined') {
                        $.each(response.programs, function(index,result) {
                            $(position).append(
                                content.replace(/{title}/g, result.program_title)
                                    .replace(/{slug}/g, result.program_slug)
                            );
                        });
                        if(response.programs.length < limit) {
                            next = false;
                            endPage = page;
                        }
                    }

                    if (typeof response.faqs !== 'undefined') {
                        $.each(response.faqs, function(index, faq) {
                            $(position).find('[data-program-questions= "'+index+'"]').children().html(faq);
                        });
                    }
                    if (typeof response.count !== 'undefined') {
                        $.each(response.count, function(index, data) {
                            $(position).find('[data-content="'+index+'"]').children().html(data.posts);
                            $(position).find('[data-assessment="'+index+'"]').children().html(data.assessments);
                        });
                    }
                    page++;
                    lastPage++;
                });
            }
            function prevPage() {
                if(page <= 2) {
                    return false;
                }
                currentPage = page;
                previousPage = page - 1;
                $(position).find('[data-page-id="'+(currentPage-1)+'"]').fadeOut('slow');
                $(position).find('[data-page-id="'+(previousPage-1)+'"]').fadeIn('slow');
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
        /** Program ends here */

        var currentProgram = 'all', selectedProgram = 'all';
        $('table#programs').on('click', '.view-details', function(e){
            selectedProgram = $(this).data('slug');
            if(currentProgram == selectedProgram) {
                return false;
            } else {
                currentProgram = selectedProgram;
                post.refresh();
                quiz.refresh();
                $('table#programs').find('tr.active').removeClass('active');
                $(this).parent().parent().addClass('active');
            }
            return false;
        });
        /** Post starts here */
        var post = (function(){
            var limit = 3,
                page = 1,
                next = true,
                lastPage = 1,
                position = "#posts",
                endPage = 1,
                isLoading = false,
                $prevPage = $("#prevPagePost"),
                $nextPage = $('#nextPagePost'),
                url = "{{ URL::to('/dashboard/post-details')}}",
                redirectUrl = "{{ URL::to('/program/packet/')}}",
                content = '<div class="col-md-4" id="db-maincontent" data-page-id="{page}">'+
                    '<div class="community color">'+
                    '<div class="boxImage">'+
                    '<img src="{{URL::asset($theme.'/img/icons/Red_topcurve.png')}}">'+
                    '</div>'+
                    '<div class="community-logo"><img src="{{URL::asset($theme.'/img/icons/notes_icon.png')}}"></div>'+
                    '<div class="info-block">'+
                    '<div class="row">'+
                    '<div class="boxformate">'+
                    '<div><strong>{title}</strong></div>'+
                    '<p>'+
                    '<div class="col-md-6 text-left " ><span class="mainsubtext1"># of Items</span><br/><span>{elements}</span></div>'+
                    '<div class="col-md-6 text-right" ><span class="mainsubtext1"># of Assessments</span><br/><span> <span data-assessment="{slug}">{assessments}</span> Assessments</span></div><br/><br/>'+
                    '</p>'+
                    '</div>'+
                    '<div class="col-md-10 col-md-offset-1">'+
                    '<div class="progress score-bar">'+
                    '<div data-completion={slug} style="width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="80" role="progressbar" class="progress-bar">0% </div>'+
                    '</div>'+
                    '</div>'+
                    '</div>' +
                    '</div>'+
                    '</div>'+
                    '</div>';
            function init(){
                $nextPage.on('click', function(e){
                    e.preventDefault();
                    if(isLoading) {
                        return false;
                    }
                    nextPage();
                });

                $prevPage.on('click', function(e){
                    e.preventDefault();

                    prevPage();
                });

                nextPage();
            }

            function refresh() {
                $(position).find('div#db-maincontent').fadeOut('slow');
                page = 1;
                lastPage = 1;
                next = true;
                nextPage();
            }

            function nextPage() {

                if(page < lastPage) {
                    $(position).find('div#db-maincontent').fadeOut('slow');
                    $(position).find('[data-page-id="'+(page)+'"]').fadeIn('slow');
                    page++;
                    return false;
                }
                if(!next) {
                    return false;
                }
                isLoading = true;
                var postRequest = $.ajax({
                    method: "GET",
                    url: url+"/"+page+"/"+limit+"/"+selectedProgram,
                    dataType: "json"
                });
                postRequest.done(function(response) {
                    if(typeof response.status !== 'undefined') {
                        if(!response.status) {
                            if(page == 1) {
                                $(position).append('<div id="db-maincontent">'+response.message+'</div>');
                            }
                            next = false;
                            return false;
                        }
                    }
                    currentPage = page - 1;
                    $(position).find('[data-page-id="'+(currentPage)+'"]').fadeOut('slow');
                    if (typeof response.results !== 'undefined') {
                        $.each(response.results, function(index,result) {
                            $(position).append(
                                content.replace(/{page}/g, page)
                                    .replace(/{packet_id}/g, result.packet_id)
                                    .replace(/{title}/g, result.packet_title)
                                    .replace(/{feed_slug}/g, result.feed_slug+" ")
                                    .replace(/{elements}/g, result.elements.length)
                                    .replace(/{slug}/g, result.packet_slug)
                            );
                        });
                        if(response.results.length < limit) {
                            next = false;
                            endPage = page;
                        }
                    }

                    if (typeof response.count !== 'undefined') {
                        $.each(response.count, function(index, count) {
                            $(position).find('[data-assessment= "'+index+'"]').html(count);
                        });
                    }

                    if (typeof response.completion !== 'undefined') {
                        $.each(response.completion, function(index, analytic) {
                            $(position).find('[data-completion="'+index+'"]').css("width", Math.round(analytic)+"%").html(Math.round(analytic)+"%");
                        });
                    }

                    if(typeof response.programs !== 'undefined') {
                        $.each(response.programs, function(index, program) {
                            $(position).find('[data-program-name="'+program.program_slug+'"]').html(program.program_title);
                        });
                    }
                    page++;
                    lastPage++;
                });
                isLoading = false;
            }

            function refresh() {
                $(position).find('div#db-maincontent').fadeOut('slow').delay(2000).remove();
                page = 1;
                lastPage = 1;
                next = true;
                nextPage();
            }

            function prevPage() {
                if(page <= 2) {
                    return false;
                }
                currentPage = page;
                previousPage = page - 1;
                $(position).find('div#db-maincontent').fadeOut('slow');
                $(position).find('div[data-page-id="'+(previousPage-1)+'"]').fadeIn('slow');
                page--;
                next = true;
            }
            init();
            return { //properties accessible from outside
                init: init,
                nextPage: nextPage,
                prevPage: prevPage,
                refresh: refresh,
            };
        })();
        /** Posts ends here */

        /** quiz starts here */
        var quiz = (function(){
            var limit = 3,
                page = 1,
                next = true,
                lastPage = 1,
                position = "div#assessments",
                endPage = 1,
                $prevPage = $("#prevPageQuiz"),
                $nextPage = $('#nextPageQuiz'),
                url = "{{ URL::to('/dashboard/assessments')}}",
                redirectUrl = "{{ URL::to('/assessment/detail')}}",
                content = '<div class="col-md-4" id="db-maincontent" data-page-id="{page}">'+

                    '<div class="communitygreen color">'+
                    '<div class="boxImage">'+
                    '<img src="{{URL::asset($theme.'/img/icons/green_topcurve.png')}}">'+
                    '</div>'+

                    '<div class="communitygreen-logo"><img src="{{URL::asset($theme.'/img/icons/notes_icon.png')}}"></div>'+
                    '<div class="info-block">'+
                    '<div class="row">'+
                    '<div class="boxformate">'+
                    '<div><strong>{name}</strong></div>'+

                    '<p>'+
                    '</p><div class="col-md-6 text-left "><span class="mainsubtext">Duration</span><br><span>{duration}</span></div>'+
                    '<div class="col-md-6 text-right"><span class="mainsubtext"># of Questions</span><br><span>{questions} Questions</span></div>'+
                    '<p></p><br><br>'+
                    '<p>'+
                    '</p><div class="col-md-6 text-left"><span class="mainsubtext">Start Date</span><br><span>{start_date}</span></div>'+
                    '<div class="col-md-6 text-right"><span class="mainsubtext">End Date</span><br><span>{end_date}</span></div>'+
                    '<p></p>'+
                    '</div>'+

                    '</div> '+

                    '</div>'+
                    '</div>'+
                    '</div>';

            function init(){
                $nextPage.on('click', function(){
                    nextPage(); return false;
                });
                $prevPage.on('click', function(){
                    prevPage(); return false;
                })
                nextPage();
            }

            function refresh() {
                $(position).find('div#db-maincontent').fadeOut('slow').delay(2000).remove();
                page = 1;
                lastPage = 1;
                next = true;
                nextPage();
            }

            function nextPage() {
                if(page < lastPage) {
                    $(position).find('div#db-maincontent').fadeOut('slow');
                    $(position).find('[data-page-id="'+(page)+'"]').fadeIn('slow');
                    page++;
                    return false;
                }
                if(!next) {
                    return false;
                }
                var quizRequest = $.ajax({
                    method: "GET",
                    url: url+"/"+page+"/"+limit+"/"+selectedProgram,
                    dataType: "json"
                });
                quizRequest.done(function(response) {
                    if(typeof response.status !== 'undefined') {
                        if(!response.status) {
                            if(page == 1) {
                                $(position).append('<div id="db-maincontent">'+response.message+'</div>');
                            }
                            next = false;
                            return false;
                        }
                    }
                    currentPage = page-1;
                    $(position).find('[data-page-id="'+(currentPage)+'"]').fadeOut('slow');
                    if (typeof response.result !== 'undefined') {
                        $.each(response.result, function(index,result) {
                            $(position).append(
                                content.replace(/{page}/g, page)
                                    .replace(/{name}/g, result.quiz_name)
                                    .replace(/{questions}/g, result.questions+" ")
                                    .replace(/{duration}/g, quizDurationToTime(result.duration))
                                    .replace(/{url}/g, redirectUrl+"/"+result.quiz_id)
                                    .replace(/{start_date}/g, quizDateToTimestamp(result.start_time))
                                    .replace(/{end_date}/g, quizDateToTimestamp(result.end_time))
                            );
                        });
                        if(response.result.length < limit) {
                            next = false;
                            endPage = page;
                        }
                    }

                    if (typeof response.faqs !== 'undefined') {
                        $.each(response.faqs, function(index, faq) {
                            $(position).find('[data-program-questions= "'+index+'"]').children().html(faq);
                        });
                    }
                    if (typeof response.analytics !== 'undefined') {
                        $.each(response.analytics, function(index, analytic) {
                            $(position).find('[data-completion-id="'+analytic.channel_id+'"]').css("width", Math.round(analytic.completion)+"%").html(Math.round(analytic.completion)+"%");
                        });
                    }
                    page++;
                    lastPage++;
                });
            }
            function prevPage() {
                if(page <= 2) {
                    return false;
                }
                currentPage = page;
                previousPage = page - 1;
                $(position).find('[data-page-id="'+(currentPage-1)+'"]').fadeOut('slow');
                $(position).find('[data-page-id="'+(previousPage-1)+'"]').fadeIn('slow');
                page--;
                next = true;
            }
            init();
            return {
                init: init,
                nextPage: nextPage,
                prevPage: prevPage,
                refresh: refresh,
            };
        })();
        /** quiz ends here */

        /** announcement starts here */
        var announcement = (function(){
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
                content = '<li class="xs-margin" data-page-id="{page}">'+
                    '<p class="black margin-0">{title}</p>'+
                    '<p class="font-10 gray margin-0" data-announcement-id="{id}">{description}</p>'+
                    '<p class="font-10"><a href="{url}"  class="pull-right">View More</a></p>'+
                    '</li>';
            function init(){
                $nextPage.on('click', function(){
                    nextPage(); return false;
                });
                $prevPage.on('click', function(){
                    prevPage(); return false;
                })
                nextPage();
            }

            function nextPage() {
                if(page < lastPage) {
                    $(position).find('li').fadeOut('slow');
                    $(position).find('[data-page-id="'+(page)+'"]').fadeIn('slow');
                    page++;
                    return false;
                }
                if(!next) {
                    return false;
                }
                var quizRequest = $.ajax({
                    method: "GET",
                    url: url+"/"+page+"/"+limit,
                    dataType: "json"
                });
                quizRequest.done(function(response) {
                    if(typeof response.status !== 'undefined') {
                        if(!response.status) {
                            if(page == 1) {
                                $(position).append('<tr><td colspan="5" class="center">'+response.message+'</td></tr>');
                            }
                            next = false;
                            return false;
                        }
                    }
                    currentPage = page-1;
                    $(position).find('[data-page-id="'+(currentPage)+'"]').fadeOut('slow');
                    if (typeof response.results !== 'undefined') {
                        $.each(response.results, function(index,result) {
                            $(position).append(
                                content.replace(/{page}/g, page)
                                    .replace(/{id}/g, result.id)
                                    .replace(/{title}/g, stripText(result.title, 60))
                                    .replace(/{description}/g, result.description)
                                    .replace(/{url}/g, redirectUrl+"/"+result.id)
                                    .replace(/{id}/g, redirectUrl+"/"+result.id)
                            );
                        });
                        if(response.results.length < limit) {
                            next = false;
                            endPage = page;
                        }
                    }
                    page++;
                    lastPage++;
                });
            }

            function prevPage() {
                if(page <= 2) {
                    return false;
                }
                currentPage = page;
                previousPage = page - 1;
                $(position).find('[data-page-id="'+(currentPage-1)+'"]').fadeOut('slow');
                $(position).find('[data-page-id="'+(previousPage-1)+'"]').fadeIn('slow');
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
        var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "June",
            "July", "Aug", "Sep", "Oct", "Nov", "Dec"
        ];

        var dateToTimestamp = function(time) {
            var date = new Date(time);
            return date.getDate()+" "+monthNames[date.getMonth()]+" "+date.getFullYear();
        }

        var quizDurationToTime = function(time) {
            if(typeof time == 'undefined') {
                return 'N/A';
            }
            if(time == 0) {
                return 'N/A';
            }

            return time;
        }

        var quizDateToTimestamp = function(time) {
            if(time == 0 || typeof time == undefined) {
                return '--';
            }
            var date = new Date(time.date);
            return date.getDate()+" "+monthNames[date.getMonth()]+" "+date.getFullYear();
        }

        var timestampToDateMonth = function(time) {
            if(time == 0 || typeof time == undefined) {
                return '--';
            }
            var date = new Date(time);
            return date.getDate()+" "+monthNames[date.getMonth()];
        }

        var timestampToTime = function(time) {
            if(time == 0 || typeof time == undefined) {
                return '--';
            }
            var date = new Date(time);
            return date.toLocaleTimeString().replace(/:\d+ /, ' ');
        }

        var timestampToDate = function(timestamp) {
            if(timestamp == 0) {
                return "No time limit";
            }
            var date = new Date(timestamp * 1000);
            return date.getDate()+" "+monthNames[date.getMonth()]+" "+date.getFullYear();
        };

        var stripText = function(text, limit) {
            return text.length > limit ?text.substr(0,limit)+'..':text;
        }

    </script>
@stop