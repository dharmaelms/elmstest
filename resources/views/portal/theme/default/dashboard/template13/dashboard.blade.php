@section('content') 
<style type="text/css">
  .packet{min-height: auto;}
  .packet-img-cont-dashboard7, .packet-img-cont-dashboard10{height: 112px;}
  @media screen and (-webkit-min-device-pixel-ratio:0) {
      .packet-img-cont-dashboard10 {
        height: 110px !important;
      }
      .packet-container-dashboard10{height: 185px !important;max-height: 185px !important;}
      }
      @-moz-document url-prefix() {
      .dashboard10 .packet-container-dashboard10 {
      margin: 0px;
      width: 175px !important;
      }
      .dashboard10 .packet-title-dashboard10{margin: 0px !important;margin-bottom: 3px !important;}
      }
  .dashboard10 .packet-title-dashboard10{height: 52px;overflow: hidden;}
  .dashboard-catagory-layer{background: <?php echo config('app.color.dashboard_category'); ?>;text-align: center;font-size: 11px;color: #fff;padding: 2px;font-weight: bold;height: 20px;overflow: hidden;}
  .packet-img-cont-dashboard10 img{height: 112px !important;max-height: 112px !important;}
</style>
<div class="row dashboard1"> 
<!--   <link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/new-dashboard.css')}}" />-->  
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
  <link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />
  <?php $programs['results'] = array_get($programs, 'results'); ?>
    <div class="col-md-9 col-sm-12 col-xs-12 dashboard10 db-main-content" id="db-maincontent">
        @if(array_get($lhs_menu_settings->setting, 'programs', 'on') == "on")
            <div class="nav-space sm-margin border-btm">
                @if(count($programs['results']) > 0) 
                    <div id="collapse13" class="panel-collapse collapse in border-btm programs" aria-expanded="true" style="">
                        <div class="panel-title">
                            <div class="col-md-12"> 
                                <h4 class="margin-top-10">
                                    <strong>{{trans('dashboard.my_courses')}}</strong>
                                    @if (count($programs['results']) > 8)
                                      <div class="pull-right font-14">
                                          <a href="{{url('program/my-feeds')}}">{{trans('dashboard.view_all')}}</a>
                                      </div>
                                    @endif
                                </h4> 
                            </div>
                        </div> 
                        <div class="panel-body panel-body1">
                            @include('portal.theme.default.dashboard.template13.programs', ['programs' => $programs])
                        </div> 
                    </div>
                @else
                  {{ trans("dashboard.$site.no_channels") }} 
                @endif
                @if(!empty($posts['results']))
                    <div id="collapse13" class="panel-collapse collapse in" aria-expanded="true" style="">
                        <div class="panel-title">
                            <div class="col-md-12"> 
                                <h4 class="margin-top-10">
                                    <strong>{{trans('dashboard.latest_content')}}</strong>
                                    @if (count($posts['results']) >= 4)
                                      <div class="pull-right font-14">
                                          <a href="{{url('program/what-to-watch')}}">{{trans('dashboard.view_all')}}</a>
                                      </div>
                                    @endif
                                </h4> 
                            </div>
                        </div> 
                        <div class="panel-body panel-body1">
                            @foreach($posts['results'] as $post)
                                <div class="item white-bg content-row-dashboard10"> 
                                    <a href="{{url('/program/packet/'.$post->packet_slug)}}" title="{{$post->packet_title}}"> 
                                        <div class="packet packet-container-dashboard10"> 
                                            <div class="packet-img-cont-dashboard10">
                                                @if(!empty($post->packet_cover_media)) 
                                                    <img src="{{url('media_image/'.$post->packet_cover_media.'?thumb=180x180')}}" alt="{{$post->packet_title}}" class="packet-img img-responsive packet-img-dashboard7">
                                                @else
                                                    <img src="{{url($theme.'/img/default_packet.jpg')}}" alt={{$post->packet_title}} class="packet-img img-responsive packet-img-dashboard7">
                                                @endif
                                            </div>
                                            <?php
                                            $channel_name = $posts['programs']->where('program_slug', $post['feed_slug'])->first()->program_title;
                                            ?>
                                            <div class="dashboard-catagory-layer" title="{{$channel_name}}">{{str_limit($channel_name, 25)}}
                                            </div>
                                            <div> 
                                                <p class="packet-title packet-title-dashboard10">{{$post->packet_title}}</p> 
                                            </div> 
                                        </div><!--packet--> 
                                    </a> 
                                </div><!--packet div-->
                            @endforeach
                        </div> 
                    </div>   
                @endif
                <!-- </div> --> 
            </div><!--ENd Packets div--> 
        @endif
        @if($general->setting['assessments'] == "on")
            @if(!empty($quizzes['result']))
                <div class="nav-space sm-margin border-btm"> 
                    <div id="collapse13" class="panel-collapse collapse in" aria-expanded="true" style="">
                        <div class="panel-title">
                            <div class="col-md-12"> 
                                <h4 class="margin-top-10">
                                    <strong>{{trans('dashboard.assessements')}}</strong>
                                    @if (count($quizzes['result']) >= 4)
                                      <div class="pull-right font-14">
                                          <a href="{{url('assessment')}}">{{trans('dashboard.view_all')}}</a>
                                      </div>
                                    @endif
                                </h4> 
                            </div>
                        </div> 
                        <div class="panel-body panel-body1">
                            @foreach($quizzes['result'] as $quiz)
                                <div class="item white-bg content-row-dashboard10"> 
                                    <a href="{{url('/assessment/detail/'.$quiz->quiz_id)}}" title="{{$quiz->quiz_name}}"> 
                                        <div class="packet packet-container-dashboard10"> 
                                            <div class="packet-img-cont-dashboard10">
                                                <img src="{{asset($theme.'/img/assessment-default.png')}}" alt="{{$quiz->quiz_name}}" class="packet-img img-responsive packet-img-dashboard7">
                                            </div> 
                                            <div> 
                                                <p class="packet-title packet-title-dashboard10">{{$quiz->quiz_name}}</p> 
                                            </div> 
                                        </div><!--packet--> 
                                    </a> 
                                </div><!--packet div-->
                            @endforeach
                        </div> 
                    </div>
                        <!-- </div> --> 
                </div><!--ENd Packets div--> 
            @endif
        @endif
    </div>
    <!-- content, courses, assessments -->

    <div class="col-md-3 col-sm-12 col-xs-12" id="db-sidebar">
      <div class="annoucements-div1 sm-margin">
        <div class="title-border"><h4 class="black font-weight-500">{{trans("dashboard.$site.announcements")}}</h4>
          <div class="nav-buttons pull-right">
            <div class="prev" id="prevPageAnnouncement"><a href=""><i class="fa fa-angle-left"></i></a></div>
            <div class="next" id="nextPageAnnouncement"><a href=""><i class="fa fa-angle-right"></i></a></div>
          </div>
        </div>
        <ul class="accordion" id="announcement">
        </ul>
        <p class="center"><a href="{{ URL::to('/announcements') }}" class="black font-12"><img src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More" title="More" style="display:inline-block;"> {{trans('dashboard.more') }}</a></p>
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
          <p class="center"><a href="{{ URL::to('/event') }}" class="black font-12"><img src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More" title="More"> {{ trans('dashboard.more') }}</a></p>
        </div><!-- events -->

        <div class="calendar1 sm-margin">
          <div class="title-border"><h4 class="black font-weight-500">{{trans("dashboard.calendar")}}</h4>
          </div>

          <div id="custom-inner" class="custom-inner">
            <div class="custom-header clearfix">
              <nav>
                <span id="custom-prev" class="custom-prev"></span>
                <span id="custom-next" class="custom-next"></span>
              </nav>
              <h2 class="margin-0 black"><span id="custom-month" class="custom-month"></span> <span id="custom-year" class="custom-year"></span></h2>
            </div>
            <div id="calendar" class="fc-calendar-container"></div>
          </div>
        </div>
      @endif

    </div>
    <!-- announcements, evenst, calendar -->
  </div>


  <script type="text/javascript" src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/js/jquery.calendario.js') }}"></script>
  <script>

      $(document).ready(function(){
              <?php if ($general->setting['events'] == "on") { ?>
          var day = new Date().getDate();
          var calendar = $("#calendar").calendario({
              displayWeekAbbr : true,
              onDayClick: function($el, $content, dateProperties){
                  day = dateProperties.day;
                  // if ($content.length > 0) {
                  window.location = "{{ URL::to('/event?show=custom') }}"+"&day="+dateProperties.day+"&month="+dateProperties.month+"&year="+dateProperties.year;
                  // showEvents(dateProperties.day, dateProperties.month, dateProperties.year);
                  // } else {
                  //   $('#event-count').html('0');
                  //   $("#events-list").html('<li>No events found</li>');
                  // }
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
          eventsList = [];
          eventPage = 1;
          eventLimit = 2;



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
                  '<div class="font-10"><a href="{url}&day={day}&month={month}&year={year}"  class="pull-right">View More</a></div>'+
                  '</li>';

          var eventView = function(page) {
              $(eventPosition).find('li').remove();
              start = (page * eventLimit) - eventLimit;
              end = start + eventLimit;
              if(start >= eventsList.length) {
                  return false;
              }
              for(i=start; i<end; i++) {
                  if(typeof eventsList[i] != 'undefined') {
                      var data = eventsList[i];
                      var str = data.start_time;
                      str = str.replace(/-/g,'/');  // replaces all occurances of "-" with "/"
                      var dateObject = new Date(str);
                      $(eventPosition).append(
                          eventContent.replace(/{page}/g, page)
                              .replace(/{title}/g, stripText(data.event_name,25))
                              .replace(/{description}/g, stripText(data.event_description, 200))
                              .replace(/{url}/g, url)
                              .replace(/{date}/g, monthNames[dateObject.getMonth()]+" "+dateObject.getDate())
                              .replace(/{time}/g, convertTime24to12(data.start_time_label))
                              .replace(/{day}/g, day)
                              .replace(/{month}/g, calendar.getMonth())
                              .replace(/{year}/g, calendar.getYear())
                      );
                  } else {
                      next = false;
                  }
              }
              return true;
          }

          $('#prevPageEvent').on('click', function(){
              if(eventPage-1 == 0) {
                  return false;
              }
              if(eventView(eventPage-1)) {
                  eventPage--;
              }
              return false;
          });

          $('#nextPageEvent').on('click', function(){
              if((eventPage >= Math.round(eventsList.length/eventLimit))) {
                  return false;
              }

              if(eventView(eventPage+1)) {
                  eventPage++;
              }
              return false;
          });
          <?php } ?>
      });

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
                  '<p class="black margin-0" style="word-wrap: break-word;">{title}</p>'+
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
                  $(position).find('li').hide();
                  $(position).find('[data-page-id="'+(page)+'"]').show();
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
                  $(position).find('[data-page-id="'+(currentPage)+'"]').hide();
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
              $(position).find('[data-page-id="'+(currentPage-1)+'"]').hide();
              $(position).find('[data-page-id="'+(previousPage-1)+'"]').show();
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

      var event = (function(){
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

              uid  = {{Auth::user()->uid}};

          content =
              '<li data-page-id="{page}" data-event-id="{id}" data-time="{start_time}" data-type="{type}">'+
              '<div class="date">'+
              '<div class="font-16">{date}</div>'+
              '<div class="font-weight-600 black font-12">{time}</div>'+
              ' </div>'+
              '<div class="event-name"> '+
              '<p class="black margin-0">{title}</p>'+
              '<p class="font-10 gray margin-0">{description}</p>'+
              '<p><a class="join-now" href={join-now} style="display:none">{link}</a></p>'+
              '<p class="font-10"><a href="{url}" class="pull-right">View More</a></p>'+
              '</div>'
          '</li>';
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
                  if(isLoading) {
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
              if(page < lastPage) {
                  $(position).find('tr').hide();
                  $(position).find('[data-page-id="'+(page)+'"]').show();
                  page++;
                  return false;
              }
              isLoading = true;


              var postRequest = $.ajax({
                  method: "GET",
                  url: url+"/"+page+"/"+limit,
                  dataType: "json"
              });
              postRequest.done(function(response) {
                  if(typeof response.status !== 'undefined') {
                      if(!response.status) {
                          if(page == 1) {
                              $(position).append('<li>'+response.message+'</li>');
                          }
                          next = false;
                          return false;
                      }
                  }
                  currentPage = page - 1;


                  $(position).find('[data-page-id="'+(currentPage)+'"]').hide();
                  if (typeof response.results !== 'undefined') {
                      $(eventsContainer).show();
                      $.each(response.results, function(index,result) {
                          $(position).append(
                              content.replace(/{page}/g, page)
                                  .replace(/{id}/g, result.id)
                                  .replace(/{date}/g, result.date)
                                  .replace(/{time}/g, result.time)
                                  .replace(/{title}/g, stripText(result.name, 25))
                                  .replace(/{description}/g, stripText(result.description, 200))
                                  .replace(/{url}/g, redirectUrl+"&day="+result.day+"&month="+result.month+"&year="+result.year)
                                  .replace(/{link}/g, uid==result.event_host_id?"{{ Lang::get('event.start_now')}}":"{{ Lang::get('event.join_now') }}")
                                  .replace(/{start_time}/g, result.start_time)
                                  .replace(/{type}/g, result.type)
                                  .replace(/{join-now}/g, joinNowLink+"/"+result.id)
                          );
                      });
                      if(response.results.length < limit) {
                          next = false;
                          endPage = page;
                      }

                      showJoinNow();
                  }

                  if (typeof response.faqs !== 'undefined') {
                      $.each(response.faqs, function(index, faq) {
                          $(position).find('[data-post-questions= "'+index+'"]').children().html(faq);
                      });
                  }

                  if (typeof response.statuses !== 'undefined') {
                      $.each(response.statuses, function(index, status) {
                          if(status == 'completed') {
                              $class = "success";
                          } else if(status == 'pending'){
                              $class = 'watched';
                          } else {
                              $class = 'new';
                          }
                          $(position).find('[data-post-status="'+index+'"]').html('<span class="badge badge-'+$class+'">'+status+"</span>");
                      });
                  }

                  if(typeof response.programs !== 'undefined') {
                      $.each(response.programs, function(index, program) {
                          $(position).find('[data-program-name="'+program.program_slug+'"]').html(stripText(program.program_title, 20));
                      });
                  }
                  page++;
                  lastPage++;
              });
              isLoading = false;

          }

          function prevPage() {
              if(isLoading) {
                  return false;
              }
              if(page <= 2) {
                  return false;
              }
              isLoading = true;
              currentPage = page;
              previousPage = page - 1;
              $(position).find('[data-page-id="'+(currentPage-1)+'"]').hide();
              $(position).find('[data-page-id="'+(previousPage-1)+'"]').show();
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

      var showJoinNow = function(){
          if($('ul#events-list').is(':visible')){
              $('ul#events-list').find('li').each(function(){
                  if($(this).data('type') == 'live') { //if it is live event
                      var eventTime = new Date($(this).data('time')*1000).getTime();
                      var currentTime = new Date().getTime();
                      if(eventTime < currentTime || eventTime <= currentTime+(15*60*1000)){
                          $(this).find('.join-now').show();
                      }
                  }
              });
          }
      }

      setInterval(function(){showJoinNow()}, 60000); //run every minutes to check for event start time

      var monthNames = ["Jan", "Feb", "Mar", "Apr", "May", "Jun",
          "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"
      ];

      var dateToTimestamp = function(time) {
          var date = new Date(time);
          return date.getDate()+" "+monthNames[date.getMonth()]+" "+date.getFullYear();
      }

      var quizDurationToTime = function(time) {
          if(typeof time == 'undefined' || time == 0) {
              return 'N/A';
          }
          return time;
      }

      var quizDateToTimestamp = function(data) {
          if(data == 0 || typeof data == undefined) {
              return 'N/A';
          }
          var d = new Date(data * 1000);
          return d.getDate()+" "+monthNames[d.getMonth()]+" "+d.getFullYear();
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
    @if(count($programs['results']) >= 12)
        <script type="text/javascript">
            jQuery(
                function($)
                {
                    var page = 1, status = true;
                    $('.programs').bind('scroll', function(){
                        if($(this).scrollTop() + $(this).innerHeight()>=$(this)[0].scrollHeight)
                        {
                            if (status) {
                                $.ajax({
                                    url : "{{url('dashboard/more-programs')}}"+ "/"+(++page),
                                    dataType : 'json',
                                    success : function(response){
                                        if (response.status) {
                                            $('.programs .panel-body').append(response.data);
                                        } else {
                                            status = false;
                                        }
                                    }
                                });
                            } 
                        }
                    })
                }
            );
        </script>
    @endif
  @stop