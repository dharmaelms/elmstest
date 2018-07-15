@section('content')
  <style>
    .page-content-wrapper .page-content { margin-left: 0 !important; }
    .page-sidebar.navbar-collapse { display: none !important; max-height: none !important; }
    .page-header.navbar .menu-toggler.sidebar-toggler , .page-bar{ display: none; }
  </style>
  <link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
  <link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />

  <div class="row dashboard1">
    <div class="col-md-9 col-sm-12 col-xs-12" id="db-maincontent">
      @if(array_get($lhs_menu_settings->setting, 'programs', 'on') == "on")
        <div class="row md-margin" id="mycourses">
          <div class="col-md-12">
            <div class="left-img">
              <a href="{{ URL::to('/program/my-feeds') }}">
                <div class="pink-bl boxlabel">
                  <h3>{{trans('dashboard.my')}} <br>{{trans('dashboard.courses')}}</h3>
                </div>
              </a>
            </div>
            <div class="right-table">
              <div class="table-responsive">
                <span class="title-box"></span>
                <span class="title-box1">{{trans('dashboard.my_courses')}}</span>
                <div class="nav-buttons pull-right">
                  <div class="prev" id="previousPageButtonProgram"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageButtonProgram"><a href=""><i class="fa fa-angle-right"></i></a></div>
                </div>
                <hr style="border-color:#E56768;margin-top:27px;">
                <div class="padding-lft-30">
                  <table class="table" id="programs">
                    <thead>
                    <tr>
                      <th width="200px">{{trans('dashboard.course_name')}}</th>
                      <th width="100" class="center">{{trans('dashboard.course_posts')}}</th>
                      <th width="120" class="center">{{trans('dashboard.course_progress')}}</th>
                      <th width="80px" class="center">{{trans('dashboard.course_q_a')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
              <div>
                <a href="{{ URL::to('/program/my-feeds') }}" class="btn btn-primary btn-sm pull-right">{{trans('dashboard.view_all')}}</a>
              </div>
            </div>
          </div>
        </div>
      @endif
    <!-- END My courses -->
      @if(array_get($lhs_menu_settings->setting, 'programs', 'on') == "on" && array_get($general->setting, 'watch_now', 'on') == "on")
        <div class="row md-margin" id="latestcourses">
          <div class="col-md-12">
            <div class="left-img">
              <a href="{{ URL::to('/program/what-to-watch') }}">
                <div class="gray-bl boxlabel">
                  <h3>{{trans('dashboard.latest')}} <br>{{trans('dashboard.content')}}</h3>
                </div>
              </a>
            </div>
            <div class="right-table">
              <div class="table-responsive">
                <span class="title-box"></span>
                <span class="title-box1" style="color:#3E4651;">{{trans('dashboard.latest_content')}}</span>
                <div class="nav-buttons pull-right">
                  <div class="prev" id="previousPageButtonPost"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageButtonPost"><a href=""><i class="fa fa-angle-right"></i></a></div>
                </div>
                <hr style="border-color:#3E4651;margin-top:27px;">
                <div class="padding-lft-30">
                  <table class="table" id="posts">
                    <thead>
                    <tr>
                      <th width="200">{{trans('dashboard.post_name')}}</th>
                      <th width="140">{{trans('dashboard.post_course_name')}}</th>
                      <th width="60">{{trans('dashboard.post_content')}}</th>
                      <!-- due to mongo load hidden status feature -->
                      <th width="80" class="center">{{trans('dashboard.post_q_a')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
              <div>
                <a href="{{ URL::to('/program/what-to-watch') }}" class="btn btn-primary btn-sm pull-right">{{trans('dashboard.view_all')}}</a>
              </div>
            </div>
          </div>
        </div>
      @endif
    <!-- END Latest Content -->
      @if($general->setting['assessments'] == "on")
        <div class="row md-margin" id="assess">
          <div class="col-md-12">
            <div class="left-img">
              <a href="#">
                <div class="blue-bl boxlabel">
                  <h3>{{trans('dashboard.assessment')}}</h3>
                </div>
              </a>
            </div>
            <div class="right-table">
              <div class="table-responsive">
                <span class="title-box"></span>
                <span class="title-box1" style="color:#14AAB9;">{{trans('dashboard.assessment')}}</span>
                <div class="nav-buttons pull-right">
                  <div class="prev" id="prevPageQuiz"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageQuiz"><a href=""><i class="fa fa-angle-right"></i></a></div>
                </div>
                <hr style="border-color:#14AAB9;margin-top:27px;">
                <div class="padding-lft-30">
                  <table class="table" id="quiz">
                    <thead>
                    <tr>
                      <th width="200">{{trans('dashboard.assessment_name')}}</th>
                      <th width="110">{{trans('dashboard.assessment_starts')}}</th>
                      <th width="110">{{trans('dashboard.assessment_ends')}}</th>
                      <th width="100" class="center">{{trans('dashboard.assessment_no_of_questions')}}</th>
                      <th width="80">{{trans('dashboard.assessment_duration')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- data inserted here -->
                    </tbody>
                  </table>
                </div>
              </div>
              <div>
                <a href="{{ URL::to('/assessment') }}" class="btn btn-primary btn-sm pull-right">{{trans('dashboard.view_all')}}</a>
              </div>
            </div>
          </div>
        </div>
    @endif
    <!-- END Assessments -->
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
        <p class="center"><a href="{{ URL::to('/announcements') }}" class="black font-12"><img src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More" title="More" style="display:inline-block;"> {{ trans('dashboard.more') }}</a></p>
      </div><!-- announcements -->
      @if($general->setting['events'] == "on")
        <div class="event-container1 sm-margin" style="display:none;">
          <div class="title-border"><h4 class="black font-weight-500">{{trans("dashboard.$site.events")}}</h4>
            <div class="nav-buttons pull-right">
              <div class="prev" id="prevPageEvent"><a href=""><i class="fa fa-angle-left"></i></a></div>
              <div class="next" id="nextPageEvent"><a href=""><i class="fa fa-angle-right"></i></a></div>
            </div>
          </div>
          <ul id="events-list">
          </ul>
          <p class="center"><a href="{{ URL::to('/event') }}" class="black font-12"><img src="{{URL::asset($theme.'/img/icons/more-icon.png')}}" alt="More" title="More"> {{trans('dashboard.more') }}</a></p>
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
                  window.location = "{{ URL::to('/event?show=custom') }}"+"&day="+dateProperties.day+"&month="+dateProperties.month+"&year="+dateProperties.year;
                  
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
        <?php
}
        ?>
      });

      /** Program starts here */
      var program = (function(){
          var limit = 8,
              page = 1,
              next = true,
              lastPage = 1,
              position = "table#programs tbody",
              endPage = 1,
              $prevPage = $("#previousPageButtonProgram"),
              $nextPage = $('#nextPageButtonProgram'),
              url = "{{ URL::to('/dashboard/programs')}}",
              redirectUrl = "{{ URL::to('/program/packets/')}}",
              content = '<tr data-id="{program_id}" data-page-id="{page}">'+
                  '<td><a href="{url}" title="{title-full}" class="font-14">{title}</a>'+
                  '<a href="{url}" class="label label-primary pull-right"> View </a>'+
                  '</td>'+
                  '<td class="center"><span class="badge badge-danger"><a href="{url}" style="color:white;">{post}<a></span></td>'+
                  '<td><div class="progress time-bar"><div data-completion-id="{program_id}" style="width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">0%</div></div></td>'+
                  '<td align="center" data-program-questions="{program_id}"><span class="badge badge-danger"><a style="color:white;" href="{url}#q-a">00</a></span></td>'+
                  '</tr>';

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
                  $(position).find('tr').hide();
                  $(position).find('[data-page-id="'+(page)+'"]').show();
                  page++;
                  return false;
              }
              if(!next) {
                  return false;
              }
              var programsRequest = $.ajax({
                  method: "GET",
                  url: url+"/"+page+"/"+limit+"/"+true,
                  dataType: "json"
              });
              programsRequest.done(function(response) {
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
                                  .replace(/{title-full}/g, result.program_title)
                                  .replace(/{program_id}/g, result.program_id)
                                  .replace(/{title}/g, stripText(result.program_title, 60)+" ")
                                  .replace(/{url}/g, redirectUrl+"/"+result.program_slug)
                                  .replace(/{post}/g, result.posts)
                          );
                      });
                      if(response.results.length < limit) {
                          next = false;
                          endPage = page;
                      }
                  }

                  if (typeof response.faqs !== 'undefined') {
                      $.each(response.faqs, function(index, faq) {
                          $(position).find('[data-program-questions= "'+index+'"]').children().children().html(faq);
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
      /** Program ends here */

      /** Post starts here */
      var post = (function(){
          var limit = 3,
              page = 1,
              next = true,
              lastPage = 1,
              position = "table#posts tbody",
              endPage = 1,
              isLoading = false,
              $prevPage = $("#previousPageButtonPost"),
              $nextPage = $('#nextPageButtonPost'),
              url = "{{ URL::to('/dashboard/posts')}}",
              redirectUrl = "{{ URL::to('/program/packet/')}}",
              content = '<tr data-id="{packet_id}" data-page-id="{page}">'+
                  '<td><a href="{url}" title="{title-full}" class="font-14">{title}</a> <a href="{url}" class="label label-primary pull-right"> View </a></td>'+
                  '<td data-program-name={feed_slug}></td>'+
                  '<td>{elements} items</td>'+
                  //due to mongo load hidden this feature
                      /*'<td class="status-badge" data-post-status="{packet_id}" align="center"></td>'+*/
                  '<td align="center" data-post-questions="{packet_id}"><span class="badge badge-danger"><a style="color:white;" href="{url}#q-a">0</a></span></td>'+
                  '</tr>';
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
                              $(position).append('<tr><td colspan="5" class="center">'+response.message+'</td></tr>');
                          }
                          next = false;
                          return false;
                      }
                  }
                  currentPage = page - 1;
                  $(position).find('[data-page-id="'+(currentPage)+'"]').hide();
                  if (typeof response.results !== 'undefined') {
                      $.each(response.results, function(index,result) {
                          $(position).append(
                              content.replace(/{page}/g, page)
                                  .replace(/{title-full}/g, result.packet_title)
                                  .replace(/{packet_id}/g, result.packet_id)
                                  .replace(/{title}/g, stripText(result.packet_title, 30))
                                  .replace(/{feed_slug}/g, result.feed_slug+" ")
                                  .replace(/{elements}/g, result.elements.length)
                                  .replace(/{url}/g, redirectUrl+"/"+result.packet_slug)
                          );
                      });
                      if(response.results.length < limit) {
                          next = false;
                          endPage = page;
                      }
                  }

                  if (typeof response.faqs !== 'undefined') {
                      $.each(response.faqs, function(index, faq) {
                          $(position).find('[data-post-questions= "'+index+'"]').children().children().html(faq);
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
      /** Posts ends here */

      /** quiz starts here */
      var quiz = (function(){
          var limit = 3,
              page = 1,
              next = true,
              lastPage = 1,
              position = "table#quiz tbody",
              endPage = 1,
              $prevPage = $("#prevPageQuiz"),
              $nextPage = $('#nextPageQuiz'),
              url = "{{ URL::to('/dashboard/assessments')}}",
              redirectUrl = "{{ URL::to('/assessment/detail')}}",
              content = '<tr data-page-id="{page}">'+
                  '<td><a href="{url}" title="{title-full}" class="font-14">{name}</a> <a href="{url}" class="label label-primary pull-right">View</a></td>'+
                  '<td>{start_date}</td>'+
                  '<td>{end_date}</td>'+
                  '<td class="center"><span class="badge badge-danger">{questions}</span></td>'+
                  '<td>{duration}</td>'+
                  '</tr>';
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
                  $(position).find('tr').hide();
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
                  if (typeof response.result !== 'undefined') {
                      $.each(response.result, function(index,data) {
                          $(position).append(
                              content.replace(/{page}/g, page)
                                  .replace(/{title-full}/g, data.quiz_name)
                                  .replace(/{name}/g, stripText(data.quiz_name, 30))
                                  .replace(/{questions}/g, data.questions+" ")
                                  .replace(/{duration}/g, quizDurationToTime(data.duration)+" ")
                                  .replace(/{url}/g, redirectUrl+"/"+data.quiz_id)
                                  .replace(/{start_date}/g, quizDateToTimestamp(data.start_time))
                                  .replace(/{end_date}/g, quizDateToTimestamp(data.end_time))
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
              $(position).find('tr').hide();
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
                  '<p class="black margin-0"><h5 style="word-wrap: break-word;"><strong>{title}</strong></h5></p>'+
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
                                  .replace(/{title}/g, stripText(result.title, 200))
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
              '<p class="black margin-0"><h5><strong>{title}</strong></h5></p>'+
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

@stop