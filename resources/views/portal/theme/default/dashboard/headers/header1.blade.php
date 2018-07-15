@section('header')
    <?php
    use App\Model\SiteSetting;
    $my_certificate = SiteSetting::where('module', 'certificates')->first(['setting']);
    ?>
    <style>
        .page-header-fixed .page-container { margin-top: 73px;}
    </style>

    <div class="page-header navbar navbar-fixed-top" id="header1">
        <!-- BEGIN HEADER INNER -->
        <div class="page-header-inner">
            <!-- BEGIN LOGO -->
            <div class="page-logo">
                @if(Auth::check() && !Session::has('menubar'))
                    <div class="menu-toggler sidebar-toggler hide">
                        <i class="fa fa-bars"></i>
                    </div>
                @endif
                <?php
                $site_logo=SiteSetting::module('Contact Us', 'site_logo');
                if(isset($site_logo) && !empty($site_logo))
                {
                    $logo=config('app.site_logo_path').$site_logo;
                }
                else
                {
                    $logo=config('app.default_logo_path');
                }

                ?>
                <a href="{{ url('/') }}">
                    <img src="{{ URL::to($logo) }}" alt="logo" class="logo-default" style="width: 200px;max-height: 70px;margin-top: -4px"/>
                </a>
            </div>
            <!-- END LOGO -->


            <!-- BEGIN RESPONSIVE MENU TOGGLER -->
            <a href="javascript:;" class="menu-toggler responsive-toggler hide" data-toggle="collapse" data-target=".navbar-collapse"><i class="fa fa-bars"></i>
            </a>
            <!-- END RESPONSIVE MENU TOGGLER -->
            <!-- BEGIN TOP NAVIGATION MENU -->
      <div class="top-menu">
          <ul class="nav navbar-nav pull-right">
          @if(config('app.ecommerce') === true)
            <li>
                <a href="{{URL::to('catalog')}}" class="btn">{{ Lang::get('dashboard.catalog') }}</a>
            </li>
          @endif
              <!-- BEGIN NOTIFICATION DROPDOWN -->
              <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
          <?php if(Auth::check()){
            if(isset($continuewrleftforhome) && !empty($continuewrleftforhome) && array_get($lhs_menu_settings->setting, 'programs', 'on') == "on"){
          ?>
              <li>
                <a class="btn" href="{{URL::to($continuewrleftforhome)}}">{{ Lang::get('dashboard.conti_from_where_u_left') }}</a>
              </li>
          <?php
              }
           ?>
              <li>
                @if(Auth::check())
                    @if(!Request::is('catalog') && !Request::is('catalog/*') && !Request::is('checkout/*') && !Request::is('/') && !Request::is('catalog-search'))
                      <form class="search-form search-form-expanded"  action="{{{ URL::to('search') }}}"  onsubmit="return submitSearchBarForm();">
                          <div class="input-group">
                              <input type="text" class="form-control" placeholder="{{Lang::get('search.simple_search_placeholder')}}" name="query" id="search" >
                              <span class="input-group-btn submit">
                            <a href="javascript:;" class="btn submit"><i class="fa fa-search gray"></i></a>
                            </span>
                          </div>
                          <input name="type" value="simple" type="hidden">
                      </form>
                    @endif
                    <!-- Catalog search ends data-toggle="dropdown" data-close-others="true"-->
                    </li>
                    <li class="dropdown dropdown-extended dropdown-notification" id="header_notification_bar" >
                        <a href="{{URL::to('announcements')}}" class="dropdown-toggle launch-modal" >
                            <i class="fa fa-bell-o"></i>
                            <span class="badge badge-default" <?php if(isset($noti_count)){$display=($noti_count>=1)?'display':'none';}else{$display='none';}?> style="display:{{$display}}">
                          <label id="notification_count" <?php if(isset($noti_count)){$display=($noti_count>=1)?'display':'none';}else{$display='none';}?> style="display:{{$display}}">
                              <strong><?php if(isset($noti_count)){echo $noti_count;}?></strong>
                          </label>
                      </span>
                        </a>
                        <ul class="dropdown-menu">
                            <?php
                            if(isset($specific_user_announce_titles) && !empty($specific_user_announce_titles)){
                            ?>
                            <li class="external">
                                <h3><span class="bold">{{ Lang::get('dashboard.announcements') }}</span></h3>
                                <a href="{{URL::to('announcements')}}">View All</a>
                            </li>
                            <li>
                                <ul class="dropdown-menu-list scroller hg-150" data-handle-color="#637283">
                                    <?php
                                    if(isset($specific_user_announce_titles)  && !empty($specific_user_announce_titles)){
                                    foreach ($specific_user_announce_titles as $key => $value) {
                                    $slug=$specific_user_announce_id[$key];
                                    ?>
                                    <li>
                                        <a href="{{URL::to('announcements')}}">
                                            <span class="time">{{Common::getPublishOnDisplay((int)$announce_date_header[$key])}}</span>
                                            <span class="details">{{$value}}</span>
                                        </a>
                                    </li>
                                    <?php
                                    }
                                    ?>
                                </ul>
                                <?php
                                }
                                ?>
                            </li>
                            <?php
                            }
                            ?>
                            @endif
                            <li class="external" >
                                <h3><span class="bold">{{ Lang::get('dashboard.notification') }}</span></h3>
                                <a id ="view_all" href="{{URL::to('notification/list-notifications')}}">{{ Lang::get('program.view_all') }}</a>
                            </li>
                            <li>
                                <ul id="appand_notififcation" class="dropdown-menu-list scroller hg-150" data-handle-color="#637283">

                                </ul>
                            </li>
                        </ul>
                    </li>
                    <?php }?>
                <!-- END NOTIFICATION DROPDOWN -->

                    <!-- BEGIN USER LOGIN DROPDOWN -->
                    <!-- DOC: Apply "dropdown-dark" class after below "dropdown-extended" to change the dropdown styte -->
              
                </ul>
            </div>

            <!-- END TOP NAVIGATION MENU -->
        </div>
        <!-- END HEADER INNER -->
    </div>

    <script type="text/javascript">
        var noti_announce_count = <?php if(isset($noti_count) && $noti_count>0){echo $noti_count;}else{ echo 0;} ?>;
        $(document).ready(function(){
            var timer_notifi;
            $('.launch-modal').click(function(){
                $.ajax({
                    type: "GET",
                    url: "{{URL::to('/notification/notification-list')}}"
                })
                        .done(function( response ) {
                            var list_notifi="";
                            var hid_in="";
                            if(response.flag == "success"){
                                if($.isArray(response.messages)){
                                    $.each( response.messages, function( key, value ) {
                                        list_notifi+='<li class="make_read "> <a href="javascript:;"><span class="time">'+response.notify_date[key]+'</span><span class="details make_read">'+value+'</span></a></li>';
                                        noti_announce_count--;
                                    });
                                    hid_in="<input type='hidden' class='notification_ids' name='notifi_list' value='"+response.notification_ids.toString()+"'>";
                                    $('#view_all').html('{{ Lang::get('announcement.view_all')}}');
                                }
                            }else
                            {
                                $('#view_all').html('{{ Lang::get('announcement.view_all') }}');
                            }
                            if(list_notifi == ""){
                                $("#appand_notififcation").parent().css("height","10px");
                            }
                            $("#appand_notififcation").html(list_notifi);
                            $("#appand_notififcation").append(hid_in);
                            $("#see_all").show();
                        })
                        .fail(function() {
                            $("#appand_notififcation").html("");
                            var no_notifi = '<li class="make_read ">    <a href="javascript:;"><span class="details"> No New Notification</span></a></li>';
                            $("#appand_notififcation").append(no_notifi);
                            // $("#no_hide_notification").hide();
                            // $("#appand_notififcation").hide();
                        });
                timer_notifi=setTimeout(function(){
                    if($('.notification_ids').val()!=""){
                        var notifications = $('.notification_ids').val();
                        $.ajax({
                            method: "GET",
                            url: "{{URL::to('/notification/mark-read')}}",
                            data: { notification_ids:$('.notification_ids').val()}
                        })
                                .done(function( msg ) {
                                    clearTimeout(timer_notifi);
                                    $('.notification_ids').val('');
                                    $('.make_read').css('overline','yellow');
                                    $('.make_read').css('color','red');
                                    $('#notification_count').children('strong').text();
                                    if(noti_announce_count<=0){
                                        $("#notification_count").hide();
                                        $("#appand_notififcation").parent().css("height","10px");
                                        $('.badge-default').hide();
                                    }else{
                                        $('#notification_count').children('strong').text(noti_announce_count);
                                    }
                                });
                    }
                }, <?php if(isset($max_read_delay)){echo $max_read_delay;}else{echo 3000;}?>);
            });
            $(".launch-modal").focusout(function(){
                clearTimeout(timer_notifi);
            });

            //catalog search keyup
            $( "#cat_search" ).keyup(function() {
                var $this = $(this);
                cat_search = $this.val();
                if(cat_search != '')
                {
                    $.ajax({
                        type: 'GET',
                        url: "{{ url('catalog-search/suggest-data') }}",
                        data :{
                            cat_search:cat_search
                        }
                    }).done(function(e) {
                        if(e.status == true) {
                            $this.autocomplete({
                                source: e.data
                            });
                        }
                    })
                }
            });
            //catalog search keyup ends
        });
        function submitSearchBarForm(){
            var sb = $("#search"),
                    val = sb.val();

            val = val.replace(/^[ ]+/g, "").replace(/[ ]+$/g, "");
            if(val === "" || val.toLowerCase() == "enter keyword, title, isbn, author name"){
                sb.focus();
                return false;
            }
            return true;
        };

        function submitCatSearch(){
            var sb = $("#cat_search"),
                    val = sb.val();

            val = val.replace(/^[ ]+/g, "").replace(/[ ]+$/g, "");
            if(val === ""){
                sb.focus();
                return false;
            }
            return true;
        };
    </script>

    <script type="text/javascript">
        $("#user").click(function() {
            $("#user").attr('target', '_blank');
            return true;
        });
    </script>
@stop
