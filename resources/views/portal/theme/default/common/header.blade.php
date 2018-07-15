@section('header')
    <?php
    use App\Model\SiteSetting;

    $my_certificate = SiteSetting::module('certificates');
    $lhs_menu_settings = SiteSetting::module('LHSMenuSettings');
?>
<div class="page-header navbar navbar-fixed-top">
<div class="menu-toggler sidebar-toggler hide">
                        <i class="fa fa-bars"></i>
                    </div>
        <!-- BEGIN HEADER INNER -->
        <div class="page-header-inner">
            <!-- BEGIN LOGO -->
            <div class="page-logo">
                @if(Auth::check() && !Session::has('menubar'))
                    <!--div class="menu-toggler sidebar-toggler">
                        <i class="fa fa-bars"></i>
                    </div-->
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
                    <img src="{{ URL::to($logo) }}" alt="logo" class="logo-default" />
                </a>
            </div>
            
            <!-- END LOGO -->

        @if(Auth::check())
            @if(!Request::is('catalog') && !Request::is('catalog/*') && !Request::is('checkout/*') && !Request::is('/') && !Request::is('catalog-search'))
                <!-- BEGIN HEADER SEARCH BOX -->
                    <!-- DOC: Apply "search-form-expanded" right after the "search-form" class to have half expanded search box -->

                    <form class="search-form search-form-expanded"  action="{{{ URL::to('search') }}}"  onsubmit="return submitSearchBarForm();">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="{{Lang::get('search.simple_search_placeholder')}}" name="query" id="search" >
                            <span class="input-group-btn submit">
                    <a href="javascript:;" class="btn submit"><i class="fa fa-search gray"></i></a>
                    </span>
                        </div>
                        <input name="type" value="simple" type="hidden">
                    </form>
                    <!-- END HEADER SEARCH BOX -->
            @endif
        @endif

        @if(config('app.ecommerce'))
            <!-- Catalog search starts -->
                @if(Request::is('catalog') || Request::is('catalog/*') || Request::is('/') || Request::is('catalog-search'))
                    <form class="search-form search-form-expanded"  action="{{{ URL::to('catalog-search') }}}" onsubmit="return submitCatSearch();">
                        <div class="input-group">
                            <input type="text" class="form-control" placeholder="Catalog Search" name="cat_search" id="cat_search">
                            <span class="input-group-btn submit">
                    <a href="javascript:;" class="btn submit"><i class="fa fa-search gray"></i></a>
                </span>
                        </div>
                    </form>
                @endif
            <!-- Catalog search ends -->
            @endif

        <!-- BEGIN RESPONSIVE MENU TOGGLER -->
            @if(!Session::has('menubar'))
                <a href="javascript:;" class="menu-toggler responsive-toggler" data-toggle="collapse" data-target=".navbar-collapse"><i class="fa fa-bars"></i>
                </a>
        @endif
        <!-- END RESPONSIVE MENU TOGGLER -->
            <!-- BEGIN TOP NAVIGATION MENU -->
            
        <!-- END HEADER INNER -->
         <a href="{{URL::to('auth/logout')}}" class="pull-right  margin-top-15" style="margin-right: 20px;">
                                        <span class="fa fa-sign-out font-20 red" data-toggle="tooltip" data-placement="top" title="Sign Out"></span> <span class="nav-label black font-14">{{ Lang::get('dashboard.sign_out') }}</span></a>
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
                                    $('#view_all').html('{{ Lang::get('announcement.view_all') }}');
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