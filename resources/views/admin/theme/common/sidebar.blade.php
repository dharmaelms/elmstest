@section('sidebar')
    <!-- BEGIN Navlist -->
    <div id="sidebar" class="navbar-collapse collapse">
        <ul class="nav nav-list">
            <!-- BEGIN Search Form -->
            <li>
                <form target="#" method="GET" class="search-form">
                    <span class="search-pan">
                        <button type="submit">
                            <i class="fa fa-search"></i>
                        </button>
                        <input type="text" name="search" placeholder="Search ..." autocomplete="off" />
                    </span>
                </form>
            </li>
            <!-- END Search Form -->
            <li class="active" data-slug="dashboard">
                <a href="{{URL::to('/cp/')}}">
                    <i class="fa fa-dashboard"></i>
                    <span>Dashboard</span>
                </a>
            </li>

            @if(config('app.ecommerce') === true)
                @if(has_admin_module_access(ModuleEnum::E_COMMERCE))
                    <li data-slug="order">
                        <a href="#" class="dropdown-toggle">
                            <i class="fa fa-shopping-cart"></i>
                            <span>{{ Lang::get('admin/ecommerce.manage_orders') }}</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <ul class="submenu">
                            @if(has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::LIST_PROMO_CODE))
                                <li data-slug="promocode">
                                    <a href="{{URL::to('/cp/promocode')}}" class="dropdown-toggle">
                                        <span>{{ Lang::get('admin/ecommerce.promocode') }}</span>
                                    </a>
                                </li>
                            @endif
                            @if(has_admin_permission(ModuleEnum::E_COMMERCE, ECommercePermission::LIST_ORDER))
                                <li data-slug="transaction">
                                    <a href="{{URL::to('cp/order/list-order')}}" class="dropdown-toggle">
                                        <span>{{ Lang::get('admin/ecommerce.order_summary') }}</span>
                                    </a>
                                </li>
                            @endif
                        </ul>
                    </li>
                @endif
            @endif
            @if(has_admin_module_access(ModuleEnum::USER) || has_admin_module_access(ModuleEnum::USER_GROUP) ||
                   has_admin_module_access(ModuleEnum::ROLE))
                <li data-slug="users_groups">
                    <a href="#" class="dropdown-toggle">
                        <i class="fa fa-group"></i>
                        <span>{{ Lang::get('admin/user.manage_users') }}</span>
                        <b class="arrow fa fa-angle-right"></b>
                    </a>
                    <ul class="submenu">
                        @if(has_admin_permission(ModuleEnum::USER, UserPermission::LIST_USER))
                            <li data-slug="user"><a href="{{URL::to('/cp/usergroupmanagement')}}">{{ Lang::get('admin/user.manage_user') }}</a></li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::USER_GROUP, UserGroupPermission::LIST_USER_GROUP))
                            <li data-slug="group"><a href="{{URL::to('/cp/usergroupmanagement/user-groups')}}">{{ Lang::get('admin/user.manage_user_group') }}</a></li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::ROLE, RolePermission::LIST_ROLE))
                            <li data-slug="role"><a href="{{URL::to('/cp/rolemanagement/user-roles')}}">{{ Lang::get('admin/user.user_roles') }}</a></li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(has_admin_module_access(ModuleEnum::CHANNEL))
                <li data-slug="program">
                    <a href="#" class="dropdown-toggle">
                        <i class="fa fa fa-rss"></i>
                        <span><?php echo Lang::get('admin/program.program'); ?></span>
                        <b class="arrow fa fa-angle-right"></b>
                    </a>
                    <ul class="submenu">
                        @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::LIST_CHANNEL))
                            <li data-slug="contentfeed">
                                <a href="{{URL::to('/cp/contentfeedmanagement/list-feeds')}}">
                                    {{ Lang::get('admin/program.channel_list') }}
                                </a>
                            </li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_QUESTION))
                            <li data-slug="channelquestions">
                                <a href="{{URL::to('/cp/contentfeedmanagement/channel-questions')}}">
                                    {{ Lang::get('admin/qanda.manage_channel_questions') }}
                                </a>
                            </li>
                        @endif
                        <!-- Since we don't have explicit permission to this certificate so currently using package permission -->
                        @if((has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::LIST_PACKAGES)) && (is_certificate_enable('certificates')) && config('app.list_certificate'))
                            <li data-slug="certificates">
                                <a href="{{URL::to('/cp/contentfeedmanagement/list-certificate-users')}}">
                                    {{ Lang::get('admin/program.certificates') }}
                                </a>
                            </li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::CHANNEL, ChannelPermission::MANAGE_CHANNEL_POST))
                            <li data-slug="postquestions">
                                <a href="{{URL::to('/cp/contentfeedmanagement/all-questions')}}">
                                    {{ Lang::get('admin/qanda.manage_post_questions') }}
                                </a>
                            </li>
                        @endif 
                    </ul>
                </li>
            @endif

            @if(config("app.ecommerce") && has_admin_module_access(ModuleEnum::PACKAGE))
                @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::LIST_PACKAGES))
                    <li data-slug="package">
                        <a href="{{URL::to('/cp/package/list-template')}}">
                            <i class="fa fa-archive"></i>
                            <?php echo Lang::get('admin/package.package_list'); ?>  
                        </a>
                    </li>
              @endif
            @endif
            <li data-slug="lmscourse">
                <a href="{{URL::to('/cp/lmscoursemanagement')}}"> 
                    <i class="fa fa-book"></i> 
                    LMS Courses 
                </a> 
            </li>
            @if(config("app.ecommerce") && has_admin_module_access(ModuleEnum::COURSE))
                @if(has_admin_permission(ModuleEnum::COURSE, CoursePermission::LIST_COURSE))
                    <li data-slug="course">
                        <a href="{{URL::to('/cp/contentfeedmanagement/list-courses')}}">
                            <i class="fa fa-book"></i>
                            {{ Lang::get('admin/program.course_list') }}
                        </a>
                    </li>
                @endif
            @endif

            @if(has_admin_module_access(ModuleEnum::CATEGORY))
                @if(has_admin_permission(ModuleEnum::CATEGORY, CategoryPermission::LIST_CATEGORY))
                    <li data-slug="category">
                        <a  href="{{URL::to('/cp/categorymanagement/categories')}}" >
                            <i class="fa fa-folder"></i>
                            <span>{{ Lang::get('admin/category.categories') }}</span>
                        </a>
                    </li>
                @endif
            @endif
            @if(has_admin_module_access(ModuleEnum::DAMS))
                @if(has_admin_permission(ModuleEnum::DAMS, DAMSPermission::LIST_MEDIA))
                    <li data-slug="dams">
                        <a  href="{{URL::to('/cp/dams/list-media')}}">
                            <i class="fa fa-video-camera"></i>
                            <span>{{ Lang::get('admin/dams.media_library') }}</span>
                        </a>
                    </li>
                @endif
            @endif
            @if(has_admin_module_access(ModuleEnum::ASSESSMENT))
                <li data-slug="assessment">
                    <a href="#" class="dropdown-toggle">
                        <i class="fa fa-pencil-square-o"></i>
                        <span>Assessments</span>
                        <b class="arrow fa fa-angle-right"></b>
                    </a>
                    <ul class="submenu">
                        @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUIZ))
                            <li data-slug="quiz">
                                <a href="{{ URL::to('/cp/assessment/list-quiz') }}">
                                    {{ Lang::get('admin/assessment.manage_quiz') }}
                                </a>
                            </li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK))
                            <li data-slug="questionbank">
                                <a href="{{ URL::to('/cp/assessment/list-questionbank') }}">
                                    {{ Lang::get('admin/assessment.manage_question_banks') }}
                                </a>
                            </li>
                        @endif
                    </ul>
                </li>
            @endif
            @if(has_admin_module_access(ModuleEnum::SURVEY))
                @if(has_admin_permission(ModuleEnum::SURVEY, SurveyPermission::LIST_SURVEY))
                    <li data-slug="survey">
                        <a href="{{ URL::to('/cp/survey')}}">
                            <i class="fa fa-file-text"></i>
                            <span>Survey</span>
                        </a>
                    </li>
                @endif
            @endif
            <!-- assignment starts here -->
            @if(has_admin_module_access(ModuleEnum::ASSIGNMENT))
                @if(has_admin_permission(ModuleEnum::ASSIGNMENT, AssignmentPermission::LIST_ASSIGNMENT))
                <li data-slug="assignment">
                    <a href="{{ URL::Route('get-list')}}">
                        <i class="fa fa-book"></i>
                        {{ Lang::get('admin/assignment.assignments') }}
                    </a>
                </li>
                @endif
            @endif
            <!-- assignment ends -->
            @if(has_admin_module_access(ModuleEnum::EVENT))
                @if(has_admin_permission(ModuleEnum::EVENT, EventPermission::LIST_EVENT))
                    <li data-slug="event">
                        <a href="{{ URL::to('/cp/event/') }}">
                            <i  class="fa fa-calendar" ></i>
                            <span>{{ Lang::get('admin/event.event') }}</span>
                        </a>
                    </li>
                @endif
            @endif
            <!-- flashcards starts here-->
            @if(has_admin_module_access(ModuleEnum::FLASHCARD))
                @if(has_admin_permission(ModuleEnum::FLASHCARD, FlashCardPermission::LIST_FLASHCARD))
                    <li data-slug="flashcard">
                        <a href="{{URL::to('/cp/flashcards/list')}}">
                            <i class="fa fa-th" ></i>
                            <span>{{ Lang::get('admin/flashcards.manage') }}</span>
                        </a>
                    </li>
                @endif
            @endif
            @if(has_admin_module_access(ModuleEnum::ANNOUNCEMENT))
                @if(has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::LIST_ANNOUNCEMENT))
                    <li data-slug="announcement">
                        <a href="{{URL::to('/cp/announce/')}}">
                            <i class="fa fa-flag" ></i>
                            <span>{{ Lang::get('admin/announcement.announcement') }}</span>
                        </a>
                    </li>
                @endif
            @endif

        <!--Start of Import Reports-->
            @if(config('app.ftp_enabled') && has_admin_permission(ModuleEnum::ERP, ERPPermission::MANAGE_BULK_IMPORTS))
                <li data-slug="import-reports">
                    <a href="#" class="dropdown-toggle">
                        <i class="fa fa-bar-chart-o"></i>
                        <span>{{ Lang::get('admin/user.import_users_in_bulk') }} Reports</span>
                        <b class="arrow fa fa-angle-right"></b>
                    </a>
                    <ul class="submenu">
                        <li data-slug="user-import-report"><a href="{{ URL::to('/cp/usergroupmanagement/user-import-report') }}">{{ Lang::get('admin/user.user') }} Report</a></li>
                        <li data-slug="user-usergroup-report"><a href="{{ URL::to('/cp/usergroupmanagement/user-usergroup-report') }}">{{ Lang::get('admin/user.user_usergroup_report') }}</a></li>
                        @if(config("app.ecommerce"))
                            <li data-slug="package-import-report"><a href="{{ URL::to('/cp/bulkimport/package-import-report') }}">{{ Lang::get('admin/program.package') }} Report</a></li>
                            <li data-slug="usergroup-package-report"><a href="{{ URL::to('/cp/bulkimport/usergroup-package-report') }}">{{ Lang::get('admin/program.ug_to_package') }}</a></li>
                        @endif
                        <li data-slug="channel-import-report"><a href="{{ URL::to('/cp/bulkimport/channel-import-report') }}">{{ Lang::get('admin/program.channel') }} Report</a></li>
                        <li data-slug="user-channel-report"><a href="{{ URL::to('/cp/bulkimport/user-channel-report') }}">{{ Lang::get('admin/program.user_to_channel') }}</a></li>
                    </ul>
                </li>
            @endif
            <!--End of Import Reports-->
            <!--  Home Page Modules starts-->

            @if(has_admin_module_access(ModuleEnum::HOME_PAGE))
                <li data-slug="homepage">
                     <a href="#" class="dropdown-toggle">
                        <i class="fa fa-home"></i>
                        <span>Homepage</span>
                        <b class="arrow fa fa-angle-right"></b>
                    </a>
                     <ul class="submenu">
                        @if(has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_BANNERS))
                            <li data-slug="banners"><a href="{{URL::to('/cp/banners')}}">{{ Lang::get('admin/homepage.manage_banners') }} </a></li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_PARTNER))
                            <li data-slug="partnerlogo"><a href="{{ URL::to('/cp/partnerlogo/') }}">{{ Lang::get('admin/homepage.manage_partners') }} </a></li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_UPCOMING_COURSES))
                            <li data-slug="upcomingcourses"><a href="{{ URL::to('/cp/upcomingcourses/') }}">{{ Lang::get('admin/homepage.manage_upcomingcourses') }} </a></li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_POPULAR_COURSES))
                            <li data-slug="popularcourses"><a href="{{ URL::to('/cp/popularcourses/') }}">{{ Lang::get('admin/homepage.manage_popularcourses') }} </a></li>
                        @endif
                        @if(has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::LIST_TESTIMONIALS))
                        <?php $label = SiteSetting::module('Homepage', 'Quotes')['label']; ?>
                            <li data-slug="testimonial"><a href="{{ URL::to('/cp/testimonials/') }}">{{ Lang::get('admin/homepage.manage_testimonial') }} {{ $label }}</a></li>
                        @endif

                    </ul>
                </li>

            @endif
            <!-- Home page modules ends -->

              <!-- Country starts here-->
            @if(config('app.ecommerce') === true)
                @if(has_admin_module_access(ModuleEnum::COUNTRY)) 
                    <li data-slug="country">
                        <a href="{{URL::to('/cp/country')}}">
                            <i class="fa fa-flag" ></i>
                            <span>{{ Lang::get('admin/country.manage_country') }}</span>
                        </a>                   
                    </li> 
                @endif
            @endif
            <!-- Country ends here -->

            @if(has_admin_module_access(ModuleEnum::MANAGE_SITE))
                <li data-slug="web">

                    <a href="#" class="dropdown-toggle">
                        <i class="fa fa-wrench"></i>
                            <span>{{ Lang::get('admin/sitesetting.manage_site') }}</span>
                        <b class="arrow fa fa-angle-right"></b>
                    </a>

                    <ul class="submenu">
                        @if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_FAQ))
                            <li data-slug="faq"><a href="{{URL::to('/cp/manageweb/')}}">{{ Lang::get('admin/sitesetting.faq') }}</a></li>
                        @endif

                        @if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::LIST_STATICPAGE))
                            <li data-slug="staticpages"><a href="{{URL::to('/cp/manageweb/static-pages')}}">{{ Lang::get('admin/sitesetting.static_pages') }}</a></li>
                        @endif

                        @if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::SITE_CONFIGURATION))
                            <li data-slug="siteconfig"><a href="{{URL::to('/cp/sitesetting')}}">{{ Lang::get('admin/sitesetting.site_configuration') }}</a></li>
                        @endif

                        @if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::CUSTOM_FIELDS))
                            <li data-slug="customfields"><a href="{{URL::to('/cp/customfields')}}">{{ Lang::get('admin/customfields.customfields') }}</a></li>
                        @endif

                        {{--@if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::MANAGE_ATTRIBUTE))--}}
                            {{--<li data-slug="attribute"><a href="{{URL::to('/cp/manageattribute')}}">{{ Lang::get('admin/sitesetting.manage_attributes') }}</a></li>--}}
                        {{--@endif--}}

                    </ul>
                </li>
            @endif

            @if(has_admin_module_access(ModuleEnum::REPORT))
                @if(has_admin_permission(ModuleEnum::REPORT, ReportPermission::VIEW_REPORT))
                    <li data-slug="report">
                         <a href="#" class="dropdown-toggle">
                            <i class="fa fa-bar-chart-o"></i>
                                <span>{{trans('admin/reports.reports')}}</span>
                            <b class="arrow fa fa-angle-right"></b>
                        </a>
                        <ul class="submenu">
                            <li data-slug="adminreport">
                                <a href="{{ URL::to('/cp/reports/admin-reports') }}">
                                    {{trans('admin/reports.content_reports')}} 
                                </a>
                            </li>
                            <li data-slug="exportreport">
                                <a href="{{ URL::to('/cp/exportreports') }}">{{trans('admin/reports.export_reports')}}</a>
                            </li>
                            <li data-slug="userreport">
                                <a href="{{ URL::to('/cp/reports/user-reports') }}">{{trans('admin/reports.user_reports')}}</a>
                            </li>
                            <li data-slug="webexreport">
                                <a href="{{ URL::to('/cp/webex/report') }}">{{trans('admin/reports.webex_reports')}}</a>
                            </li>
                            <li data-slug="cronlogsreports">
                                <a href="{{ URL::to('/cp/reports/cron-logs-reports') }}">
                                    {{trans('admin/reports.reports_cron_logs')}}
                                </a>
                            </li>
                        </ul>
                    </li>
                @endif
            @endif
        </ul>
        <!-- END Navlist -->

        <!-- BEGIN Sidebar Collapse Button -->
        <div id="sidebar-collapse" class="visible-lg">
            <i class="fa fa-angle-double-left"></i>
        </div>
        <!-- END Sidebar Collapse Button -->
        <script>
            $(document).ready(function() {
                window.mainmenu = "{{(isset($mainmenu)) ? $mainmenu : ""}}";
                window.submenu = "{{(isset($submenu)) ? $submenu : ""}}";
                if(typeof window.mainmenu != "undefined") {
                    $('#sidebar li').removeClass('active');
                    var $mainmenu = $('[data-slug="'+window.mainmenu+'"]');
                    $mainmenu.addClass('active');
                    if(window.submenu != "undefined") {
                        $mainmenu.find('.submenu li[data-slug="'+window.submenu+'"]').addClass('active');
                        if($mainmenu.find('.arrow').length) {
                            $mainmenu.trigger('click');
                        }
                    }
                }

                // Code to search the subtitle menu
                $('[name="search"]').keyup(function() {
                    var $this = this;
                    if($this.value) {
                        $('#sidebar ul.nav').children('li[data-slug]').each(function(index,element) {
                            if($(element).text().toLowerCase().indexOf($this.value) === -1)
                                $(element).hide();
                            else
                                $(element).show();
                        })
                    }
                    else {
                        $('#sidebar ul.nav').children('li[data-slug]').show();
                    }
                })
            })
        </script>
    </div>
    <!-- END Sidebar -->
@stop
