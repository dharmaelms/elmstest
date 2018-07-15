<?php use App\Model\SiteSetting; ?>
<div class="page-sidebar navbar-collapse collapse">
	<!-- BEGIN SIDEBAR MENU1 -->
	<ul class="page-sidebar-menu hidden-sm hidden-xs" data-auto-scroll="true" data-slide-speed="200">
		@if(Request::path()=='/')
			<li class="active">
		@else
			<li>
		@endif
			<a href="{{URL::to('/')}}">
			<i class="fa fa-home"></i><br>
			<span class="title">Home</span>
			</a>
		</li>
		@if(stristr(Request::path(), 'program'))
			<li class="active">
		@else
			<li>
		@endif
			<?php $general_category_feeds=SiteSetting::module('General','general_category_feeds');
            if($general_category_feeds=="on"){ ?>
			<a href="{{URL::to('program/category-channel')}}">
			<?php }else{ ?>
			<a href="{{URL::to('program/my-feeds')}}">
			<?php } ?>
				
				<i class="fa fa-rss-square"></i><br>
				<span class="title"><?php echo Lang::get('program.programs');?></span>
				</a>
			</li>
			@if(Request::path()=='event')
				<li class="active">
			@else
				<li>
			@endif
				<a href="{{URL::to('event')}}">
				<i class="fa fa-calendar"></i><br>
				<span class="title">Events</span>
				</a>
			</li>
			@if(Request::path()=='user')
				<li class="active">
			@else
				<li>
			@endif
				<a href="{{URL::to('user')}}">
				<i class="fa fa-bar-chart-o"></i><br>
				<span class="title">My Activity</span>
				</a>
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
			<!--lms start-->
			@if(Request::path()=='lmscourse/my-courses' || Request::path()=='lmscourse/more-courses')
				<li class="active">
			@else
				<li>
			@endif
				<a href="{{URL::to('lmscourse/my-courses')}}">
				<i class="fa fa-rss-square"></i><br>
				<span class="title">Courses</span>
				</a>
			</li>
	<!--lms end-->
	</ul>
	<!-- END SIDEBAR MENU1 -->
	<!-- BEGIN RESPONSIVE MENU FOR HORIZONTAL & SIDEBAR MENU -->
	<ul class="page-sidebar-menu visible-sm visible-xs" data-slide-speed="200" data-auto-scroll="true">
		<!-- DOC: To remove the search box from the sidebar you just need to completely remove the below "sidebar-search-wrapper" LI element -->
		<!-- DOC: This is mobile version of the horizontal menu. The desktop version is defined(duplicated) in the header above -->
		<li class="active">
			<a href="{{URL::to('/')}}">
			<i class="fa fa-home"></i>
			<span class="title">Home</span>
			</a>
		</li>
		<li>
			<a href="{{URL::to('program/what-to-watch')}}">
			<i class="fa fa-rss-square"></i>
			<span class="title"><?php echo Lang::get('program.packets');?></span>
			</a>
		</li>
		<li>
			<a href="{{URL::to('event')}}">
			<i class="fa fa-calendar"></i>
			<span class="title">Events</span>
			</a>
		</li>
		<li>
			<a href="{{URL::to('user')}}">
			<i class="fa fa-bar-chart-o"></i>
			<span class="title">My Activity</span>
			</a>
		</li>
		<li class="last">
			<a href="{{URL::to('assessment')}}">
			<i class="fa fa-file"></i>
			<span class="title">Assessments</span>
			</a>
		</li>
	</ul>
	<!-- END RESPONSIVE MENU FOR HORIZONTAL & SIDEBAR MENU -->
</div>