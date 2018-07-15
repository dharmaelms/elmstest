<?php
use App\Model\SiteSetting;
use App\Model\ManageLmsProgram;
use App\Model\User;
$wstoken=SiteSetting::module('Lmsprogram', 'wstoken');
$user=User::getActiveUserUsingID($moodle_userid);
$general=SiteSetting::module('General');
$lms_menu_settings = SiteSetting::module('LHSMenuSettings');
?>
<style type="text/css">
	.page-sidebar {

    margin:0px;

    /*r scroll hide**/
    -webkit-transition: -webkit-transform 1s cubic-bezier(0.86, 0, 0.07, 1);
  -moz-transition: -moz-transform 1s cubic-bezier(0.86, 0, 0.07, 1);
  transition: transform 1s cubic-bezier(0.86, 0, 0.07, 1);
  }


  /** to hide navbar*/
  .page-sidebar.hidden {
  -webkit-transform: translateY(100%);
  -moz-transform: translateY(100%);
  -ms-transform: translateY(100%);
  -o-transform: translateY(100%);
  transform: translateY(100%);
}
</style>
<!--div class="page-sidebar navbar-collapse collapse"navbar navbar-inverse-->
	<div class="page-sidebar  navbar-fixed-bottom navbar-collapse collapse  center font-14 bold" style="width:74%!important;margin-left: 14%;">
		 <a href="javascript:;" class="menu-toggler responsive-toggler hide" data-toggle="collapse" data-target=".navbar-collapse"><i class="fa fa-bars"></i>
            </a>
	<!-- BEGIN SIDEBAR MENU1 -->
	<!--ul class="page-sidebar-menu hidden-sm hidden-xs" data-auto-scroll="true" data-slide-speed="200"-->
	<ul class="page-sidebar-menu  nav navbar-nav hidden-sm hidden-xs" data-auto-scroll="true" data-slide-speed="200">
		@if(Request::path()=='/')
			<li class="active">
		@else
			<li>
				@endif
				<a href="{{URL::to('/dashboard')}}">
					<i class="fa fa-home"></i><br>
					<span class="title"><?php echo Lang::get('dashboard.dashboard'); ?></span>
				</a>
			</li>
			@if(stristr(Request::path(), 'program') && Request::path()!='program/my-packages')
			<li class="active">
			@else
			<li>
				@endif
				<?php $general_category_feeds=SiteSetting::module('General', 'general_category_feeds');
				if($lms_menu_settings->setting['programs'] == "on") {
				if($general_category_feeds=="on"){ ?>
				<a href="{{URL::to('program/category-channel')}}">
					<?php }else{ ?>
					<a href="{{URL::to('program/my-feeds')}}">
						<?php } ?>

						<i class="fa fa-rss-square"></i><br>
						<span class="title"><?php echo Lang::get('program.course');?></span>
					</a>
				<?php } ?>
			</li>

			@if(Request::path()=='event')
			<li class="active">
			@else
			<li>
			@endif
				<?php if($general->setting['events'] == "on") { ?>
				<a href="{{URL::to('event')}}">
					<i class="fa fa-calendar"></i><br>
					<span class="title"><?php echo Lang::get('event.events'); ?></span>
				</a>
				<?php } ?>
			</li>

			@if(Request::path()=='user')
			<li class="active">
			@else
			<li>
			@endif
				<?php if($lms_menu_settings->setting['my_activity'] == "on") { ?>
				<a href="{{URL::to('user')}}">
					<i class="fa fa-bar-chart-o"></i><br>
					<span class="title"><?php echo Lang::get('reports.my_activity'); ?></span>
				</a>
				<?php } ?>
			</li>
						@if(stristr(Request::path(), 'assessment') && !stristr(Request::path(), 'program'))
							<li class="active">
						@else
						<li>
							@endif
							<?php if($general->setting['assessments'] == "on") { ?>
							<a href="{{URL::to('assessment')}}">
								<i class="fa fa-edit"></i><br>
								<span class="title"><?php echo Lang::get('assessment.assessments'); ?></span>
							</a>
							<?php } ?>
						</li>

						@if(Request::path()=='lmscourse/my-courses' || Request::path()=='lmscourse/more-courses')
							<li class="active">
						@else
						<li>
							@endif
							<?php if(($general->setting['moodle_courses'] == "on") && (is_admin_role(Auth::user()->role))) { ?>
							<a href="{{URL::to('lmscourse/more-courses')}}">
								<i class="fa fa-rss-square"></i><br>
								<span class="title"><?php echo Lang::get('lmscourse.courses'); ?></span>
							</a>
							<?php } ?>
						</li>

						@if(isset($user[0]['relations']['lms_course_rel']) && !empty($user[0]['relations']['lms_course_rel']))
							@if(Request::path()=='lmscourse/my-courses' || Request::path()=='lmscourse/more-courses')
								<li class="active">
							@else
								<li>
									@endif
									<?php if(($general->setting['moodle_courses'] == "on") && (!is_admin_role(Auth::user()->role))) { ?>
									<a href="{{URL::to('lmscourse/my-courses')}}">
										<i class="fa fa-rss-square"></i><br>
										<span class="title"><?php echo Lang::get('lmscourse.courses'); ?></span>
									</a>
									<?php } ?>
								</li>
							@endif
						<!--lms end-->
							<!--start of package-->
							@if($general->setting['package']=='on' && config('app.ecommerce') === true)
								@if(Request::path()=='program/my-packages')
									<li class="active">
								@else
									<li>
										@endif
										<a href="{{URL::to('program/my-packages')}}">
											<i class="fa fa-rss-square"></i><br>
											<span class="title"><?php echo Lang::get('program.package'); ?></span>
										</a>
									</li>
							@endif
							<!--end of package-->

							<!-- Start of survey -->
							@if((stristr(Request::path(), 'survey/start-survey')) || (stristr(Request::path(), 'survey/view-reports')) || (Request::path() == 'survey'))
								<li class="active">
							@else
							<li>
							@endif
								<a href="{{URL::to('survey')}}">
									<i class="fa fa-file-text-o"></i><br>
									<span class="title">{{Lang::get('survey.surveys2')}}</span>
								</a>
							</li>
							<!-- End of Survey -->

							<!-- start of assignment -->
							@if((stristr(Request::path(), 'assignment/submit-assignment')) || (stristr(Request::path(), 'assignment/assignment-result')) || (Request::path() == 'assignment'))
								<li class="active">
							@else
							<li>
							@endif
								<a href="{{URL::to('assignment')}}">
									<i class="fa fa-book"></i><br>
									<span class="title">{{Lang::get('assignment.assignments')}}</span>
								</a>
							</li>
							<!-- end of assignment -->
	</ul>
	<!-- END SIDEBAR MENU1 -->
	<!-- BEGIN RESPONSIVE MENU FOR HORIZONTAL & SIDEBAR MENU -->
	<ul class="page-sidebar-menu visible-sm visible-xs" data-slide-speed="200" data-auto-scroll="true">
		<!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
		<!-- DOC: This is mobile version of the horizontal menu. The desktop version is defined(duplicated) in the header above -->
		@if(Request::path()=='/')
			<li class="active">
		@else
			<li>
				@endif
				<a href="{{URL::to('/dashboard')}}">
					<i class="fa fa-home"></i><br>
					<span class="title"><?php echo Lang::get('dashboard.dashboard'); ?></span>
				</a>
			</li>

			@if(stristr(Request::path(), 'program') && Request::path()!='program/my-packages')
				<li class="active">
			@else
			<li>
				@endif
				<?php $general_category_feeds=SiteSetting::module('General', 'general_category_feeds');
				if($lms_menu_settings->setting['programs'] == "on") {
				if($general_category_feeds=="on"){ ?>
				<a href="{{URL::to('program/category-channel')}}">
					<?php }else{ ?>
					<a href="{{URL::to('program/my-feeds')}}">
						<?php } ?>

						<i class="fa fa-rss-square"></i><br>
						<span class="title"><?php echo Lang::get('program.programs');?></span>
					</a>
				<?php } ?>
			</li>

			@if(Request::path()=='event')
			<li class="active">
			@else
			<li>
				@endif
				<?php if($general->setting['events'] == "on") { ?>
				<a href="{{URL::to('event')}}">
					<i class="fa fa-calendar"></i><br>
					<span class="title"><?php echo Lang::get('event.events'); ?></span>
				</a>
				<?php } ?>
			</li>

			@if(Request::path()=='user')
			<li class="active">
			@else
			<li>
				@endif
				<?php if($lms_menu_settings->setting['my_activity'] == "on") { ?>
				<a href="{{URL::to('user')}}">
					<i class="fa fa-bar-chart-o"></i><br>
					<span class="title"><?php echo Lang::get('reports.my_activity'); ?></span>
				</a>
				<?php } ?>
			</li>

					<!--
		@if(Request::path()=='#')
						<li class="active">
                    @else
						<li>
                    @endif
							<a href="#">
                            <i class="fa fa-folder"></i><br>
                            <span class="title">Library</span>
                            </a>
                        </li>
                        -->
						@if(Request::path()=='assessment')
						<li class="active">
						@else
						<li>
							@endif
							<?php if($general->setting['assessments'] == "on") { ?>
							<a href="{{URL::to('assessment')}}">
								<i class="fa fa-edit"></i><br>
								<span class="title"><?php echo Lang::get('assessment.assessments'); ?></span>
							</a>
							<?php } ?>
						</li>

						@if(Request::path()=='lmscourse/my-courses' || Request::path()=='lmscourse/more-courses')
						<li class="active">
						@else
						<li>
							@endif
							<?php if(($general->setting['moodle_courses'] == "on") && (is_admin_role(Auth::user()->role))) { ?>
							<a href="{{URL::to('lmscourse/more-courses')}}">
								<i class="fa fa-rss-square"></i><br>
								<span class="title"><?php echo Lang::get('lmscourse.courses'); ?></span>
							</a>
							<?php } ?>
						</li>

						@if(isset($user[0]['relations']['lms_course_rel']) && !empty($user[0]['relations']['lms_course_rel']))
						@if(Request::path()=='lmscourse/my-courses' || Request::path()=='lmscourse/more-courses')
							<li class="active">
						@else
							<li>
								@endif
								<?php if(($general->setting['moodle_courses'] == "on") && (!is_admin_role(Auth::user()->role))) { ?>
								<a href="{{URL::to('lmscourse/my-courses')}}">
									<i class="fa fa-rss-square"></i><br>
									<span class="title"><?php echo Lang::get('lmscourse.courses'); ?></span>
								</a>
								<?php } ?>
							</li>
						@endif

						@if($general->setting['package']=='on')
						@if(Request::path()=='program/my-packages')
							<li class="active">
						@else
							<li>
								@endif
								<a href="{{URL::to('program/my-packages')}}">
									<i class="fa fa-rss-square"></i><br>
									<span class="title"><?php echo Lang::get('program.package'); ?></span>
								</a>
							</li>
						@endif

	</ul>
	<!-- END RESPONSIVE MENU FOR HORIZONTAL & SIDEBAR MENU -->
</div>
