@section('content')

  <link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/calendar.css') }}" />
  <link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/Calendario/css/custom_2.css') }}" />
 <link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/css/new-dashboard.css')}}" />
 <style type="text/css">
   .std table>thead>tr>th {
    background: linear-gradient(#ff9933, #ffbf80)!important;
}
 
.mb-4{
  border: 2px solid #c1bcbc;
 border-radius: 10px!important;
    height: auto;
    width: 48%;
    margin: 10px 0px 0px 13.5px;
    box-shadow: 5px 7px 4px #738190;
    background: linear-gradient(#bdc3c7,#333333e0)
}
.heading {
    margin-left: 45px;
    background: linear-gradient(#b29a22,#f2f2f2);
    padding: 2px;
    width: 40%;
    border: 4px solid #d3c996;
    border-radius: 10px 10px 62px 5px;
}
.md-margin{
  margin-bottom: 10px;
}
.img{
  border-radius: 50%!important;
    width: 40px;
    float: left;
    margin-top: 5px;
}
a {
    text-shadow: none;
    color: #000000bf;
}
.bold {
    font-weight: 600 !important;
}
/*.panel-body .btn:not(.btn-block) { width:120px;margin-bottom:10px; }

.dash{
  box-shadow: 4px 10px 5px #827d7d;
}
.btn-lg {
    padding: 30px 109px !important;}
    .glyphicon {
    position: relative;
    top: 1px;
    display: inline-block;
    font-family: 'Glyphicons Halflings';
    font-style: normal;
    font-weight: 400;
    line-height: 1;
    -webkit-font-smoothing: antialiased;
    left: -25px;
    -moz-osx-font-smoothing: grayscale;
}*/
.navbar-global {
  background-color: indigo;
}

.navbar-global .navbar-brand {
  color: white;
}

.navbar-global .navbar-user > li > a
{
  color: white;
}

.navbar-primary {
  background-color: #252525f5;
  bottom: 0px;
  left: 0px;
  position: fixed;
  top: 72px;
  width: 220px;
  z-index: 8;
  overflow: hidden;
  border-radius: 8px;
    border: 3px solid #e5c674;
  -webkit-transition: all 0.1s ease-in-out;
  -moz-transition: all 0.1s ease-in-out;
  transition: all 0.1s ease-in-out;
}

.navbar-primary.collapsed {
  width: 60px;
}

.navbar-primary.collapsed .glyphicon {
  font-size: 22px;
}

.navbar-primary.collapsed .nav-label {
  display: none;
}
.navbar-primary.collapsed .username{
  display: none;
}
.btn-expand-collapse {
    position: absolute;
    display: block;
    left: 0px;
    bottom:0;
    width: 100%;
    padding: 8px 0;
    border-top:solid 1px #666;
    color: grey;
    font-size: 20px;
    text-align: center;
}

.btn-expand-collapse:hover,
.btn-expand-collapse:focus {
    background-color: #222;
    color: white;
}

.btn-expand-collapse:active {
    background-color: #111;
}

.navbar-primary-menu,
.navbar-primary-menu li {
  margin:0; padding:0;
  list-style: none;
}

.navbar-primary-menu li a {
  display: block;
  padding: 10px 18px;
  text-align: left;
  border-bottom:solid 1px #444;
  color: #ccc;
}

.navbar-primary-menu li a:hover {
  background-color: #000;
  text-decoration: none;
  color: white;
}

.navbar-primary-menu li a .glyphicon {
  margin-right: 6px;
}

.navbar-primary-menu li a:hover .glyphicon {
  color: orchid;
}

.main-content {
  margin-top: 60px;
  margin-left: 196px;
  padding: 20px;
  overflow-y: hidden;
}

.collapsed + .main-content {
  margin-left: 35px;
}
 </style>
  
  
    <nav class="navbar-primary">
<a  class="btn-expand-collapse"><span class="glyphicon glyphicon-menu-left"></span></a>
    <!--div class="col-md-2 col-sm-6">

      <div class="nav-side-menu">
    <div class="brand">Brand Logo</div-->
    <!--i class="fa fa-bars fa-2x toggle-btn" data-toggle="collapse" data-target="#menu-content"></i>
  
        <div class=" menu-list"-->
  
            <ul id="menu-content" class="navbar-primary-menu">
                
<?php if(Auth::check()){ ?>
                <li  data-toggle="collapse" data-target="#products" class="collapsed active">
                  <a> @if(isset(Auth::user()->profile_pic) && !empty(Auth::user()->profile_pic))
                                <img alt="User" class="img-circle " src="{{URL::asset(config('app.user_profile_pic') . Auth::user()->profile_pic)}}" height="20" width="30"/>
                            @else
                                <img alt="Avatar" class="img-circle " src="{{ URL::asset($theme.'/img/avatar.png') }}" height="20"/>
                            @endif
                            <span class="username username-hide-on-mobile margin-left-10 font-14">

               <strong> {{Auth::user()->firstname}} {{Auth::user()->lastname}}</strong></span>
                            
                        </a>
                        
                            @if(has_admin_portal_access())
                                <li>
                                    <a id="user" href="{{URL::to('cp')}}" target="_blank">
                                        <span class="fa fa-user-secret"></span> <span class="nav-label">{{ Lang::get('dashboard.admin_view') }}</span></a>
                                </li>
                            @endif
                            <li>
                                <a id="user" href="{{URL::to('/dashboard')}}" target="_blank">
                                    <span class="fa fa-dashboard"></span> <span class="nav-label">{{ Lang::get('dashboard.my_dashboard') }}</span> </a>
                            </li> </a>
                </li>
                


                <li data-toggle="collapse" data-target="#service" class="collapsed">
                    @if(array_get($lhs_menu_settings->setting, 'my_activity', 'on') == "on")
                                @if(Request::path()=='user')
                            <li class="active">
                                @else
                            <li>
                                @endif
                                <a href="{{URL::to('user')}}" target="_blank">
                                    <span class="fa fa-bar-chart-o"></span> <span class="nav-label">{{ Lang::get('reports.my_activity') }}</span>
                                </a>
                            </li>
                            @endif <!--a></a-->
                </li>  
               
                <!--li data-toggle="collapse" data-target="#new" class="collapsed"->
                  <a-->   <li>
                                <a href="{{URL::to('user/my-profile')}}" target="_blank">
                                    <span class="icon-user"></span> <span class="nav-label">{{ Lang::get('dashboard.my_profile') }} </span></a>
                            </li>

                            @if(config('app.ecommerce'))
                            <li>
                                <a href="{{URL::to('user/my-address')}}" target="_blank">
                                    <span class="fa fa-map-marker"></span> <span class="nav-label">{{ Lang::get('dashboard.my_address') }} </span></a>
                            </li>
                            <li>
                                <a href="{{URL::to('ord/list-order')}}" target="_blank">
                                    <span class="icon-user"></span> <span class="nav-label">{{ Lang::get('dashboard.my_order') }}</span> </a>
                            </li>
                            @endif </a>
                <!--/li>
               

                 <li->
                  <a-->
                     <li>
                                    <a href="{{URL::to('user/change-password')}}" target="_blank">
                                        <span class="icon-key"></span> <span class="nav-label">{{ Lang::get('dashboard.change_password') }}</span></a>
                                </li>
                  <!--/a>
                  </li-->

                 <li>
                  <!--a-->  @if(isset($my_certificate->setting['visibility']) && $my_certificate->setting['visibility'] == 'true')
                                    <li>
                                        <a href="{{URL::to('certificates')}}" target="_blank">
                                            <span class="fa fa-certificate"></span> <span class="nav-label">{{ Lang::get('dashboard.my_certificates') }}</span></a>
                                    </li>
                            @endif
                  <!--/a-->
                </li>
                 <!--li>
                  <a 
                  --> <li>
                                    <a href="{{URL::to('auth/logout')}}">
                                        <span class="fa fa-sign-out"></span> <span class="nav-label">{{ Lang::get('dashboard.sign_out') }}</span></a>
                                </li>
                  <!--/a>
                </li-->
            </ul>
           
     <!--/div>
</div-->


<?php }else{?>


   @if (config('app.ecommerce'))
                    <li>
                        <a href="#signinreg" data-toggle="modal">
                            <span class="icon-lock"></span> <span class="nav-label">
                            {{ Lang::get("dashboard.sign_reg") }}</span>
                        </a>
                    </li>
                    @endif
                <?php }?>

    <!--/div-->
  </nav>
  <div class="main-content">
  <div class="row dashboard1">
    <div class="col-md-12 col-sm-12 col-xs-12" id="db-maincontent">
      @if(array_get($lhs_menu_settings->setting, 'programs', 'on') == "on")
  <div class="container" style="border: 1px solid #dfba49a6;
    border-radius: 15px!important;margin-top: -32px;">
    <div class="row md-margin" >
     <div class="col-lg-6  col-md-6 col-sm-6 text-center mb-4"v id="mycourses">

          <img class="rounded-circle img-fluid d-block mx-auto img" src="{{URL::to('portal/theme/default/img/book.png')}}" alt="">
           <a href="{{ URL::to('/program/my-feeds') }}">
           <h4 align="left" class="bold heading" style="margin-left: 45px">{{trans('dashboard.my')}} {{trans('dashboard.courses')}}</h4>
        </a>
        
              <div class="table-responsive">
           <table class="table table-bordered table-hover" id="programs">
                    <thead>
                    <tr>
                      <th>{{trans('dashboard.course_name')}}</th>
                      <th>{{trans('dashboard.course_starts')}}</th>
                      <th>{{trans('dashboard.course_ends')}}</th>
                     <!-- <th>{{trans('dashboard.course_progress')}}</th> -->
                     <!--  <th class="center">{{trans('dashboard.course_q_a')}}</th> -->
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
              
                </div>
                  <div id="viewfeed">
                <a href="{{ URL::to('/program/my-feeds') }}" class="btn btn-success btn-sm pull-right" style="margin-bottom: 2px;"><i class="fa fa-street-view" aria-hidden="true"></i> {{trans('dashboard.view_all')}}</a>
              </div>
        </div>
        <div class=" col-lg-6  col-md-6 col-sm-6 text-center mb-4" id="latestcourses">

          <img class="rounded-circle img-fluid d-block mx-auto img" src="{{URL::to('portal/theme/default/img/presentation.png')}}" alt="">
          <a href="{{ URL::to('/program/what-to-watch') }}">
          <h4 align="left" class="bold heading" style="margin-left: 45px">{{trans('dashboard.latest')}} {{trans('dashboard.content')}}
            
          </h4>
            </a>
             
              <div class="table-responsive">
           <table class="table table-bordered table-hover" id="posts">
                    <thead>
                    <tr>
                      <th>{{trans('dashboard.post_name')}}</th>
                     <th>{{trans('dashboard.post_course_name')}}</th> 
                      <th>{{trans('dashboard.post_content')}}</th>
                      <th  class="center">{{trans('dashboard.post_q_a')}}</th> 
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                
              </div>
      <div id="viewpost">
                <a href="{{ URL::to('/program/what-to-watch') }}" class="btn btn-success btn-sm pull-right" style="margin-bottom: 2px;"><i class="fa fa-street-view" aria-hidden="true"></i> {{trans('dashboard.view_all')}}</a>
              </div>
        </div>
      </div>
      <div class="row">
         <div class="col-lg-6  col-md-6  col-sm-6 text-center mb-4" >
          <img class="rounded-circle img-fluid d-block mx-auto img" src="{{URL::to('portal/theme/default/img/menu.png')}}" alt="">
          <a href="{{ URL::to('/survey') }}">
          <h4 align="left" class="bold heading" style="margin-left: 45px">{{trans('dashboard.survey')}}
            
          </h4>
         </a>
          
              <div class="table-responsive">
          <table class="table table-bordered table-hover" id="survey">
                    <thead>
                    <tr>
                      <th width="150">{{trans('dashboard.survey_name')}}</th>
                      <th width="150">{{trans('dashboard.survey_starts')}}</th>
                      <th width="110">{{trans('dashboard.survey_ends')}}</th>
                      <th width="100" class="center">{{trans('dashboard.survey_que')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- data inserted here -->
                    </tbody>
                  </table>

</div>
                  <div id="viewsurvey">
                <a href="{{ URL::to('/survey') }}" class="btn btn-success btn-sm pull-right"><i class="fa fa-street-view" aria-hidden="true"></i> {{trans('dashboard.view_all')}}</a>
              </div>
        </div>
         <div class=" col-lg-6 col-md-6  col-sm-6 text-center mb-4" >
          <img class="rounded-circle img-fluid d-block mx-auto img" src="{{URL::to('portal/theme/default/img/marker.png')}}" alt="">
          <a href="{{ URL::to('/assessment') }}">
          <h4 align="left" class="bold heading" style="margin-left: 45px">{{trans('dashboard.assessment')}}
          </h4>
        </a>
         
              <div class="table-responsive">
            <table class="table table-bordered table-hover" id="quiz">
                    <thead>
                    <tr>
                      <th width="200">{{trans('dashboard.assessment_name')}}</th>
                      <th width="110">{{trans('dashboard.assessment_starts')}}</th>
                      <th width="110">{{trans('dashboard.assessment_ends')}}</th>
                      <th width="60" class="center">{{trans('dashboard.assessment_no_of_questions')}}</th>
                      <!--th width="80">{{trans('dashboard.assessment_duration')}}</th-->
                    </tr>
                    </thead>
                    <tbody>
                    <!-- data inserted here -->
                    </tbody>
                  </table>
              
              </div>
              <div id="viewquiz">
                <a href="{{ URL::to('/assessment') }}" class="btn btn-success btn-sm pull-right"><i class="fa fa-street-view" aria-hidden="true"></i> {{trans('dashboard.view_all')}}</a>
              </div>
        </div>
      </div>
      <div class="row">
        <div class=" col-lg-6 col-md-6  col-sm-6 text-center mb-4">
          <img class="rounded-circle img-fluid d-block mx-auto img" src="{{URL::to('portal/theme/default/img/marker.png')}}" alt="">
        <a href="{{ URL::to('/assignment') }}">
          <h4 align="left" class="bold heading" style="margin-left: 45px">{{trans('assignment.assignments')}}
          </h4>
        </a>
       
              <div class="table-responsive">
            <table class="table table-bordered table-hover" id="assignment">
                    <thead>
                    <tr>
                      <th width="150">{{trans('assignment.assignment_title')}}</th>
                      <th width="150">{{trans('assignment.starts')}}</th>
                      <th width="110">{{trans('assignment.ends')}}</th>
                      <th width="110">{{trans('assignment.cutoff_date')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- data inserted here -->
                    </tbody>
                  </table>
              
              </div>
              <div id="viewassignment">
                <a href="{{ URL::to('/assignment') }}" class="btn btn-success btn-sm pull-right"><i class="fa fa-street-view" aria-hidden="true"></i> {{trans('dashboard.view_all')}}</a>
              </div>
        </div>
            <!--div class="panel panel-danger" style="border-radius: 10px!important;">
                <div class="panel-heading" style="border-radius: 10px!important;">
                    <h3 class="panel-title uppercase">
                        <span class="glyphicon glyphicon-bookmark" style="font-size: 20px;left: 0px"></span><b>Quick Shortcuts</b> </h3>
                </div>
                <div class="panel-body">
                    <div class="row" style="margin-left: -10px;">
                        <div class="col-xs-6 col-md-12">
                          <a href="#" class="btn btn-danger btn-lg dash" role="button"><span class="glyphicon glyphicon-list-alt" style=" font-size: 50px;"></span> <br/><b style="margin-left:-53px">Dashboard</b></a>
                          <a href="{{ URL::to('/program/my-feeds') }}" class="btn btn-warning btn-lg dash" role="button" style="background-color: #d8d802;"><span class="glyphicon glyphicon-bookmark" style=" font-size: 50px;"></span> <br/><b style="margin-left:-36px;">Courses</b></a>
                          <a href="#" class="btn btn-primary btn-lg dash" role="button" style="
    background-color: #ff4500e3;
"><span class="glyphicon glyphicon-calendar" style=" font-size: 50px;"></span> <br/><b style="margin-left:-32px">Events</b></a>
                          <a href="#" class="btn btn-primary btn-lg dash" role="button"  style="
    background-color: royalblue;
"><span class="glyphicon glyphicon-comment" style=" font-size: 50px;"></span> <br/><b style="margin-left:-52px">My Activity</b></a>
                        </div>
                        <div class="col-xs-6 col-md-12">
                          <a href="#" class="btn btn-success btn-lg dash" role="button" style="background-color: #5fbd03eb;"><span class="glyphicon glyphicon-edit" style=" font-size: 50px;"></span> <br/><b style="margin-left:-52px">Assessments</b></a>
                          <a href="#" class="btn btn-info btn-lg dash" role="button" style="
    background-color: rebeccapurple;
"><span class="glyphicon glyphicon-file" style=" font-size: 50px;"></span> <br/><b style="margin-left:-38px">Surveys</b></a>
                          <a href="#" class="btn btn-primary btn-lg dash" role="button" style="background-color: #02bfbf;"><span class="glyphicon glyphicon-book" style=" font-size: 50px;"></span> <br/><b style="margin-left:-52px">Assignments</b></a>
                          <a href="#" class="btn btn-primary btn-lg dash" role="button" style="
    background-color: darkred;
"><span class="glyphicon glyphicon-tag" style=" font-size: 50px;"></span> <br/><b style="margin-left:-20px">Tags</b></a>
                        </div>
                    </div>
                    <!--a href="http://www.jquery2dotnet.com/" class="btn btn-success btn-lg btn-block" role="button"><span class="glyphicon glyphicon-globe"></span> Website</a>
                </div>
            </div-->
        </div>
    </div>
     </div>
    </div>


        <!--div class="row md-margin" id="mycourses">
          <div class="col-md-12">
            <!--div class="left-img">
              <a href="{{ URL::to('/program/my-feeds') }}">
                <div class="pink-bl boxlabel">
                  <h3>{{trans('dashboard.my')}} <br>{{trans('dashboard.courses')}}</h3>
                </div>
              </a>
            </div->
            <div class="table std">
              <div class="table-responsive">
                <!--span class="title-box"></span->
                <span class="title-box1">{{trans('dashboard.my_courses')}}</span->
                <div class="nav-buttons pull-right">
                  <!--div class="prev" id="previousPageButtonProgram"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageButtonProgram"><a href=""><i class="fa fa-angle-right"></i></a></div->
                </div>
                <!--hr style="border-color:#E56768;margin-top:27px;"->
                <div class="padding-lft-0">
                  <h2 class="text-align-left  text-uppercase" style="color: #660033;font-weight: 800"> Courses12</h2>
                  <table class="table table-bordered table-hover" id="programs">
                    <thead>
                    <tr>
                      <th>{{trans('dashboard.course_name')}}</th>
                      <th>{{trans('dashboard.course_starts')}}</th>
                      <th>{{trans('dashboard.course_ends')}}</th>
                     <!--  <th>{{trans('dashboard.course_progress')}}</th> -->
                     <!--  <th class="center">{{trans('dashboard.course_q_a')}}</th> ->
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
              <div id="viewfeed">
                <a href="{{ URL::to('/program/my-feeds') }}" class="btn btn-primary btn-sm pull-right">{{trans('dashboard.view_all')}}</a>
              </div>
            </div>
          </div-->
      
      @endif
    <!-- END My courses -->
      @if(array_get($lhs_menu_settings->setting, 'programs', 'on') == "on" && array_get($general->setting, 'watch_now', 'on') == "on")
      <!--div class="row md-margin" id="latestcourses">
        <div class="col-md-12">
            <div class="panel panel-danger" style="border-radius: 10px!important;">
                <div class="panel-heading" style="border-radius: 10px!important;">
                    <h3 class="panel-title uppercase">
                        <span class="glyphicon glyphicon-bookmark" style="font-size: 20px;left: 0px"></span><b>Student Performance</b> </h3>
                </div>
                <div class="panel-body">
                    <div class="row" style="margin-left: 50px;">
                        <div class="col-xs-6 col-md-12">
                          <a href="#" > <img src="{{URL::to('portal/theme/default/img/stdchart.png')}}" alt="College Fest" style="width:31%;"></a>
                          <a href="#"><img src="{{URL::to('portal/theme/default/img/quiz.png')}}" alt="College Fest" style="width:36%;"></a>
                          <a href="#"><img src="{{URL::to('portal/theme/default/img/video.png')}}" alt="College Fest" style="width:22%;"></a>
                         <!--a href="#"><img src="{{URL::to('portal/theme/default/img/attendant.png')}}" alt="College Fest" style="width:30%;"></a->
                        </div>
                        <!--div class="col-xs-6 col-md-12">
                          <a href="#" class="btn btn-success btn-lg" role="button"><span class="glyphicon glyphicon-edit" style=" font-size: 50px;"></span> <br/><b style="margin-left:-52px">Assessments</b></a>
                          <a href="#" class="btn btn-info btn-lg" role="button" style="
    background-color: rebeccapurple;
"><span class="glyphicon glyphicon-file" style=" font-size: 50px;"></span> <br/><b style="margin-left:-38px">Surveys</b></a>
                          <a href="#" class="btn btn-primary btn-lg" role="button"><span class="glyphicon glyphicon-book" style=" font-size: 50px;"></span> <br/><b style="margin-left:-52px">Assignments</b></a>
                          <a href="#" class="btn btn-primary btn-lg" role="button" style="
    background-color: darkred;
"><span class="glyphicon glyphicon-tag" style=" font-size: 50px;"></span> <br/><b style="margin-left:-20px">Tags</b></a>
                        </div->
                    </div>
                    <!--a href="http://www.jquery2dotnet.com/" class="btn btn-success btn-lg btn-block" role="button"><span class="glyphicon glyphicon-globe"></span> Website</a>
                </div>
            </div>
        </div>
    </div>
        <!--div class="row md-margin" id="latestcourses">
          <div class="col-md-12">
            <div class="left-img">
              <a href="{{ URL::to('/program/what-to-watch') }}">
                <div class="gray-bl boxlabel">
                  <h3>{{trans('dashboard.latest')}} <br>{{trans('dashboard.content')}}</h3>
                </div>
              </a>
            </div->
            <div class="table std">
              <div class="table-responsive">
                <!--span class="title-box"></span->
                <span class="title-box1" style="color:#3E4651;">{{Lang::get('dashboard.latest_content')}}</span-->
                <!--div class="nav-buttons pull-right">
                  <!--div class="prev" id="previousPageButtonPost"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageButtonPost"><a href=""><i class="fa fa-angle-right"></i></a></div>
                </div>
                <!--hr style="border-color:#3E4651;margin-top:27px;"->
                <div class="padding-lft-0">
                  <h2 class="text-align-left  text-uppercase" style="color: #660033;font-weight: 800"> latest content</h2>
                  <table class="table table-bordered table-hover" id="posts">
                    <thead>
                    <tr>
                      <th>{{trans('dashboard.post_name')}}</th>
                     <th>{{trans('dashboard.post_course_name')}}</th> 
                      <th>{{trans('dashboard.post_content')}}</th>
                      <th  class="center">{{trans('dashboard.post_q_a')}}</th> 
                    </tr>
                    </thead>
                    <tbody>
                    </tbody>
                  </table>
                </div>
              </div>
              

            </div>
          </div>
        </div-->
      @endif
    <!-- END Latest Content -->
      @if($general->setting['assessments'] == "on")
        <!--div class="row md-margin" id="assess">
          <div class="col-md-12">
            <div class="left-img">
              <a href="{{ URL::to('/assessment') }}">
                <div class="blue-bl boxlabel">
                  <h3>{{trans('dashboard.assessment')}}</h3>
                </div>
              </a>
            </div->
            <div class="table">
              <div class="table-responsive">
                <span class="title-box"></span->
                <span class="title-box1" style="color:#14AAB9;">{{trans('dashboard.assessment')}}</span>
                <!--div class="nav-buttons pull-right">
                  <div class="prev" id="prevPageQuiz"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageQuiz"><a href=""><i class="fa fa-angle-right"></i></a></div>
                </div>
                <hr style="border-color:#14AAB9;margin-top:27px;" ->
                <div class="padding-lft-0">
                  <table class="table table-bordered table-hover" id="quiz">
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
                    <!-- data inserted here ->
                    </tbody>
                  </table>
                </div>
              </div>
              <div id="viewquiz">
                <a href="{{ URL::to('/assessment') }}" class="btn btn-primary btn-sm pull-right">{{trans('dashboard.view_all')}}</a>
              </div>
            </div>
          </div>
        </div-->
    @endif
    <!-- END Assessments -->
    <!-- start survey -->
      @if(!empty($surveys))
        <!--div class="row md-margin" id="assess">
          <div class="col-md-12">
            <div class="left-img">
              <a href="{{ URL::to('/survey') }}">
                <div class="blue-bl boxlabel">
                  <h3>{{trans('survey.surveys2')}}</h3>
                </div>
              </a>
            </div->
            <div class="table">
              <div class="table-responsive">
                <!--span class="title-box"></span->
                <span class="title-box1" style="color:#14AAB9;">{{trans('dashboard.survey')}}</span>
                <!--div class="nav-buttons pull-right">
                  <div class="prev" id="prevPageSurvey"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageSurvey"><a href=""><i class="fa fa-angle-right"></i></a></div>
                </div>
                <hr style="border-color:#14AAB9;margin-top:27px;"->
                <div class="padding-lft-0">
                  <table class="table table-bordered table-hover" id="survey">
                    <thead>
                    <tr>
                      <th width="150">{{trans('dashboard.survey_name')}}</th>
                      <th width="150">{{trans('dashboard.survey_starts')}}</th>
                      <th width="110">{{trans('dashboard.survey_ends')}}</th>
                      <th width="100" class="center">{{trans('dashboard.survey_que')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- data inserted here ->
                    </tbody>
                  </table>
                </div>
              </div>
              <div id="viewsurvey">
                <a href="{{ URL::to('/survey') }}" class="btn btn-primary btn-sm pull-right">{{trans('dashboard.view_all')}}</a>
              </div>
            </div>
          </div>
        </div-->
      @endif
    <!-- end survey -->
    <!-- start assignment -->
      @if(!$assignments->isEmpty())
        <!--div class="row md-margin" id="assess">
          <div class="col-md-12">
            <div class="left-img">
              <a href="{{ URL::to('/assignment') }}">
                <div class="blue-bl boxlabel">
                  <h3>{{trans('assignment.assignments')}}</h3>
                </div>
              </a>
            </div>
            <div class="table">
              <div class="table-responsive">
                <!--div class="nav-buttons pull-right">
                  <div class="prev" id="prevPageAssignment"><a href=""><i class="fa fa-angle-left"></i></a></div>
                  <div class="next" id="nextPageAssignment"><a href=""><i class="fa fa-angle-right"></i></a></div->
                </div>
                <!--hr style="border-color:#14AAB9;margin-top:27px;"->
                <div class="padding-lft-0">
                  <table class="table table-bordered table-hover" id="assignment">
                    <thead>
                    <tr>
                      <th width="150">{{trans('assignment.assignment_title')}}</th>
                      <th width="150">{{trans('assignment.starts')}}</th>
                      <th width="110">{{trans('assignment.ends')}}</th>
                      <th width="110">{{trans('assignment.cutoff_date')}}</th>
                    </tr>
                    </thead>
                    <tbody>
                    <!-- data inserted here ->
                    </tbody>
                  </table>
                </div>
              </div>
              <div id="viewassignment">
                <a href="{{ URL::to('/assignment') }}" class="btn btn-primary btn-sm pull-right">{{trans('dashboard.view_all')}}</a>
              </div>
            </div>
          </div>
        </div-->
      @endif  
    <!-- end assignment -->
    </div>
    <!-- content, courses, assessments -->




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

      /** Program starts here */
      var program = (function(){
          var limit = 4,
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
                  '<td><a href="{url}" title="{title-full}">{title}</a>'+
                  '<a href="{url}" class="label label-primary pull-right"> View </a>'+
                  '</td>'+
                  '<td>{start_date}</td>'+
                  '<td>{end_date}</td>'+
                  // '<td><div class="progress time-bar"><div data-completion-id="{program_id}" style="width: 0%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">0%</div></div></td>'+
                  // '<td align="center" data-program-questions="{program_id}"><span class="badge badge-danger"><a style="color:white;" href="{url}#q-a">00</a></span></td>'+
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
                  url: url+"/"+page+"/"+limit,
                  dataType: "json"
              });
              programsRequest.done(function(response) {
                  if(typeof response.status !== 'undefined') {
                      if(!response.status) {
                          if(page == 1) {
                              $(position).append('<tr><td colspan="5" class="center">'+response.message+'</td></tr>');
                              $('#viewfeed').hide();
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
                                  .replace(/{title}/g, stripText(result.program_title, 45)+" ")
                                  .replace(/{url}/g, redirectUrl+"/"+result.program_slug)
                                  .replace(/{start_date}/g, timestampToDate(result.program_startdate))
                                  .replace(/{end_date}/g, timestampToDate(result.program_enddate))
                          );
                      });
                      if(response.results.length < limit) {
                          next = false;
                          endPage = page;
                          if(page == 1) {
                            $('#viewfeed').hide();
                          }
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
          var limit = 4,
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
                  '<td><a href="{url}" title="{title-full}">{title}</a> <a href="{url}" class="label label-primary pull-right"> View </a></td>'+
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
                              $('#viewpost').hide();
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
                                  .replace(/{feed_slug}/g, result.feed_slug)
                                  .replace(/{elements}/g, result.elements.length)
                                  .replace(/{url}/g, redirectUrl+"/"+result.packet_slug)
                          );
                      });
                      if(response.results.length < limit) {
                          next = false;
                          endPage = page;
                          if(page == 1) {
                            $('#viewpost').hide();
                          }
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
                          $(position).find('[data-program-name="'+program.program_slug+'"]').html(stripText(program.program_title, 30));
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
          var limit = 4,
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
                  '<td><a href="{url}" title="{title-full}">{name}</a> <a href="{url}" class="label label-primary pull-right">View</a></td>'+
                  '<td>{start_date}</td>'+
                  '<td>{end_date}</td>'+
                  '<td class="center"><span class="badge badge-danger">{questions}</span></td>'+
                  /*'<td>{duration}</td>'+*/
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
                              $('#assess').hide();
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
                                  .replace(/{name}/g, stripText(data.quiz_name, 25))
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
                          if(page == 1) {
                            $('#viewquiz').hide();
                          }
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
              '<p class="black margin-0" style="font-size: 14px">{title}</p>'+
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

      /** Survey starts here */
      var survey = (function(){
          var limit = 4,
              page = 1,
              next = true,
              lastPage = 1,
              position = "table#survey tbody",
              endPage = 1,
              $prevPage = $("#prevPageSurvey"),
              $nextPage = $('#nextPageSurvey'),
              url = "{{ URL::to('/dashboard/surveys')}}",
              redirectUrl = "{{ URL::to('/survey/survey-details')}}",
              content = '<tr data-page-id="{page}">'+
                  '<td><a href="{url}" title="{title-full}">{name}</a> <a href="{url}" class="label label-primary pull-right">View</a></td>'+
                  '<td>{start_date}</td>'+
                  '<td>{end_date}</td>'+
                  '<td class="center"><span class="badge badge-danger">{questions}</span></td>'+
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
                              $('#assess').hide();
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
                                  .replace(/{title-full}/g, data.survey_title)
                                  .replace(/{name}/g, stripText(data.survey_title, 25))
                                  .replace(/{questions}/g, data.questions+" ")
                                  .replace(/{url}/g, redirectUrl+"/"+data.id)
                                  .replace(/{start_date}/g, data.start_time)
                                  .replace(/{end_date}/g, data.end_time)
                          );
                      });
                      if(response.result.length < limit) {
                          next = false;
                          endPage = page;
                          if(page == 1) {
                            $('#viewsurvey').show();
                          }
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
      /** Survey ends here */

       /** Assignment starts here */
      var assignment = (function(){
          var limit = 4,
              page = 1,
              next = true,
              lastPage = 1,
              position = "table#assignment tbody",
              endPage = 1,
              $prevPage = $("#prevPageAssignment"),
              $nextPage = $('#nextPageAssignment'),
              url = "{{ URL::to('/dashboard/assignments')}}",
              redirectUrl = "{{ URL::to('assignment/submit-assignment')}}",
              content = '<tr data-page-id="{page}">'+
                  '<td><a href="{url}" title="{title-full}">{name}</a> <a href="{url}" class="label label-primary pull-right">View</a></td>'+
                  '<td>{start_date}</td>'+
                  '<td>{end_date}</td>'+
                  '<td>{cutoff_date}</td>'+
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
                              $('#assess').hide();
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
                                  .replace(/{title-full}/g, data.assignment_name)
                                  .replace(/{name}/g, stripText(data.assignment_name, 25))
                                  .replace(/{cutoff_date}/g, data.cutoff_time)
                                  .replace(/{url}/g, redirectUrl+"/unattempted/"+data.id)
                                  .replace(/{start_date}/g, data.start_time)
                                  .replace(/{end_date}/g, data.end_time)
                          );
                      });
                      if(response.result.length < limit) {
                          next = false;
                          endPage = page;
                          if(page == 1) {
                            $('#viewassignment').hide();
                          }
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
      /** Assignment ends here */

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
      $('.btn-expand-collapse').click(function(e) {
        $('.navbar-primary').toggleClass('collapsed');
});
  </script>
@stop