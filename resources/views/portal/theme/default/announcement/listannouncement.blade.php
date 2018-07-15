@section('content')
<style type="text/css">
  .publish_on_id{
    border-bottom: 1px solid #555;
    height: 20px;
  }
   .publish_on_name{
    float: left;
  }
  .publish_on_date{
  float: right;
  }
 .resp-tab-content img{
    max-width: 100%;
    max-height: 400px !important;
  }
</style>
<style type="text/css">
  .notifylist {list-style-type: none;padding-left: 0px;}
  .notifylist li .img-div { float: left;}
  .notifylist li .img-div img { height: 46px; }
  .notifylist li .data-div { margin-left: 68px; }
  .notifylist li {
      min-height: 76px;
      padding: 15px;
      border-bottom: 1px solid #eeeeee;
  }
  .notifylist li .data-div .txt{
     font-size: 15px;
  }
</style>
      
        @if(!Auth::check())
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            @if (config('app.ecommerce'))
            <span><?php echo Lang::get('announcement.only_publc_announment'); ?></span>
            @else
            <?php echo Lang::get('announcement.only_publc_announce');?>&nbsp;Please<a style="text-decoration: underline;"href="{{URL::to('/')}}"> sign in</a> to view all announcements
            @endif
        </div>
        @endif
      <div class="tabbable tabbable-tabdrop color-tabs">
        <ul class="nav nav-tabs center margin-btm-0">
          <li class="<?php if (!isset($annonceoranotifi) || $annonceoranotifi != 'notification') {
            echo 'active';} ?>">
          <a href="#announce1" data-toggle="tab" class="announce_div_tab Announcement_btn">
           <i class="fa fa-bullhorn" aria-hidden="true"></i>
 <?php echo Lang::get('announcement.announcement') ?>
                      <span id="list_ann_count"></span> 
          </a>
          </li>
         @if(isset($user_id) && $user_id>0 && $path != 'public')  
           <li class="<?php if (isset($annonceoranotifi) && $annonceoranotifi == 'notification') {
            echo 'active';}?>">
             <a href="#notify" data-toggle="tab" class="Notifications_btn">
              <i class="fa fa-bell-o" aria-hidden="true"></i> <?php echo Lang::get('announcement.notification') ?>
              <span id="list_noti_count"></span>
             </a>
           </li> 
         @endif
        </ul>
        <div class="tab-content">
          <div class="tab-pane <?php if (!isset($annonceoranotifi) || $annonceoranotifi != 'notification') {
            echo 'active';} ?>" id="announce1">
            <div class="row">
              <div class="col-md-12" id="grant_parent">

                <div id="parentVerticalTab" class="announce-tabs1">
                  <ul class="resp-tabs-list hor_1  tabs-ul" id="content_list_announce" style="word-wrap: break-word;"></ul>
                  <div class="resp-tabs-container hor_1 tabs-body1">

                  </div><!-- resp-tabs-container hor_1 -->
                </div><!-- anouncement tabs-->
                <div id='no-records-announce' style='display:none' class='col-md-12 center l-gray'><p><strong>{{Lang::get('pagination.no_more_records')}}</strong></p></div>
              </div>
              <div class="xs-margin"></div>
               <center><div class="alert alert-danger" id='no_announce_published' style='display:none' class='col-md-12 center l-gray'><p><strong>{{Lang::get('pagination.no_announce_published')}}</strong></p></div></center>
            </div><!--row-->
          </div><!--announce tab-->


          <div class="tab-pane <?php if (isset($annonceoranotifi) && $annonceoranotifi == 'notification') {
            echo 'active';}?>" id="notify">
            <div class="row">
              <div class="col-md-offset-1 col-md-10 col-md-offset-1">
                <div class="xs-margin"></div><!--space-->
                <ul class="notifylist" id="content_list_notification"></ul> <!--END list group-->
              </div>
              <div id='no-records' style='display:none' class='col-md-12 center l-gray'><p><strong>{{Lang::get('pagination.no_more_records')}}</strong></p></div>
            </div><!--row-->
          </div><!--notifications tab-->
        </div>
        <!--tab-content-->
      </div><!-- tabdrop tabs-->


<script type="text/javascript">
    $(document).ready(function() {
        //Vertical Tab
        $('#parentVerticalTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text();
                $info.show();
            }
        });
    });
</script>

<script type="text/javascript"> 
 var dummy=1;
 var not_read_notifi="";
 var annonceoranotifi='<?php if (isset($annonceoranotifi)){echo $annonceoranotifi;} else {echo "announce";}?>';
 var for_bind_unbind="bind";
 var total_anno_list ="";
 var total_anno_cont ="";
   $(document).ready(function () {
      <?php
        $url=URL::to("announcements/next-records?announcement=$path");
        $url_notifi=URL::to('notification/notification-records');
        $uid=isset($user_id)?$user_id:0;
        $announcementId = isset($announcementId)?$announcementId:0;
      ?>
      var timer_notifi;
      
      var pageno=1;
      var page_notifi=1;
      var path='<?php echo $url;?>';
      var userid=<?php echo $uid;?>;
      var path_notifi='<?php echo $url_notifi;?>';
      var pop_up_notify = 0;
      var announcementId = '<?php echo $announcementId;?>';
     
      var last_page_var="";
     
      if(annonceoranotifi=="announce"){
       /* $('.Notifications_btn').css('color','black');
        $('.Announcement_btn').css('color','red');*/
         loadData(pageno,path);
         pageno++;
      }else{

        /*$('.Notifications_btn').css('color','red');
        $('.Announcement_btn').css('color','black');*/
        loadData(page_notifi, path_notifi);
        page_notifi++;
      }


    $('.Announcement_btn').click(function(){

        for_bind_unbind="bind";
       /* $('#announce1').show();
        $('#content_list_announce').show();
        $('#content_list_notification').hide();*/
        // $(this).css('color','red');
        // $('.Notifications_btn').css('color','black');
        annonceoranotifi="announce";
        clearTimeout(timer_notifi);
        loadData(pageno,path);
        pageno++;
    });
    $('.Notifications_btn').click(function(){
      for_bind_unbind="bind";
      clearTimeout(timer_notifi);
     /* $('#content_list_announce').hide();
      $('#announce1').hide();
      $('#content_list_notification').show();*/
      // $(this).css('color','red');
      // $('.Announcement_btn').css('color','black');
      annonceoranotifi="notificatio";
      if($('#content_list_notification').html().trim()==""){ 
        if(userid>0){
          loadData(page_notifi, path_notifi);
          page_notifi++;
        }
      }


       
    }); 

   $(window).scroll(function() {   
        if(($(window).scrollTop() + $(window).height()) > ($(document).height() - 10)) {
          if(for_bind_unbind=="bind"){
           if(annonceoranotifi=='announce'){
                loadData(pageno, path);
                pageno++;
              }else{
                clearTimeout(timer_notifi);
                loadData(page_notifi, path_notifi);
                page_notifi++;
              }
          }
      }
  });
  
  function loadData(pageno,path){
    for_bind_unbind="unbind";
    $.ajax({
        method: "GET",
        url:path,
        data: { page_no: pageno}
    })
    .done(function( msg ) {
        if(msg==0)
            {
              for_bind_unbind="unbind";
            }else
            {
              console.log(msg.announcement_view);

              if(annonceoranotifi=='announce'){

                if((msg.announcement_list).length == 0 && (msg.announcement_view).length == 0  && pageno ==1)
                {
                  $('#grant_parent').hide();
                  $('#no_announce_published').show();
                }
                else
                {

                // console.log(msg.announcement_view);
                // $('#content_list_notification').hide();
                // $('#content_list_announce').show();
                if((msg.announcement_list).length > 0 && (msg.announcement_view).length > 0){
                  $.each(msg.announcement_list, function( index, value ) {
                      total_anno_list +=value;
                      // alert(total_anno_list);
                  });
                   $.each(msg.announcement_view, function( index, value1 ) {
                      total_anno_cont +=value1;
                  });
                  $('.resp-tabs-list').html('');                  
                  $('.resp-tabs-container').html('');
                  $('.resp-tabs-list').append(total_anno_list);// appending the announcement data
                  $('.resp-tabs-container').append(total_anno_cont);
                  if(msg.list_ann_count < 1){
                        $('#list_ann_count').hide()
                  }else{
                        $('#list_ann_count').text("("+msg.list_ann_count+")");
                  }
                  reinti();
                  selectTheContent(announcementId); // select the data
                  for_bind_unbind="bind";
                }else{
                  // $('.resp-tabs-list').append("No More Announcements");
                  $('#no-records-announce').show();
                  for_bind_unbind="unbind";
                }
                // alert(msg.announcement_view);
                // $("#click_for_more").show();
                clearTimeout(timer_notifi);

                }

              }else{
                if(msg.notificationlist=="No More Notifications"){ 
                  $('#no-records').show();
                  // $('#list_noti_count').text(0);
                  for_bind_unbind="unbind";
                  if(not_read_notifi!=""){
                      clearTimeout(timer_notifi);
                      timer_notifi=setTimeout(function(){
                      last_page_var=not_read_notifi; 
                      var notifications = not_read_notifi;

                      $.ajax({
                          method: "GET",
                          url: "{{URL::to('/notification/mark-read')}}",
                          data: { notification_ids:notifications}
                      })
                      .done(function( msg ) {
                        if(msg.list_noti_count < 1){
                            $('#list_noti_count').hide()
                        }else{
                            $('#list_noti_count').text("("+msg.list_noti_count+")");
                        }
                        var read_ary = not_read_notifi.split(",");
                        pop_up_notify = parseInt($("#notification_count strong").text());
                        if(pop_up_notify > 1){
                            $("#notification_count strong").text(pop_up_notify-read_ary.length);
                        }else{
                            $('.badge-default').hide();
                        }
                        $.each(read_ary, function( index, value ) {
                            $('#newspan'+value).hide();
                          });
                        clearTimeout(timer_notifi);
                        // window.location.reload();
                      });
                     }, 
                <?php
                if (isset($max_read_delay)){echo $max_read_delay;} else {echo 10000;} ?>); 
                     } else {
                        clearTimeout(timer_notifi);
                     }
                } else { 
                    if (msg.notifi_notread_list!=""){
                      not_read_notifi=msg.notifi_notread_list;
                    }
                    // $('#content_list_announce').hide();
                    $('#content_list_notification').append(msg.notificationlist);
                    if (msg.list_noti_count < 1){
                            $('#list_noti_count').hide()
                        } else {
                            $('#list_noti_count').text("("+msg.list_noti_count+")");
                        }
                    // $('#list_noti_count').text("("+msg.list_noti_count+")");
                    // $('#content_list_notification').show();
                    if (not_read_notifi!=""){
                      clearTimeout(timer_notifi);
                      timer_notifi=setTimeout(function(){
                      last_page_var=not_read_notifi; 
                      var notifications = not_read_notifi;
                      $.ajax({
                          method: "GET",
                          url: "{{URL::to('/notification/mark-read')}}",
                          data: { notification_ids:notifications}
                      })
                      .done(function( msg ) {
                        if (msg.list_noti_count < 1){
                            $('#list_noti_count').hide()
                        } else {
                            $('#list_noti_count').text("("+msg.list_noti_count+")");
                        }
                        var read_ary = not_read_notifi.split(",");
                        pop_up_notify = parseInt($("#notification_count strong").text());
                        if(pop_up_notify > 1){
                            $("#notification_count strong").text(pop_up_notify-read_ary.length);
                        }else{
                            $('.badge-default').hide();
                        }
                        $.each(read_ary, function( index, value ) {
                            $('#newspan'+value).hide();
                          });
                        clearTimeout(timer_notifi);
                        // window.location.reload();
                      });
                     }, 
                    <?php if (isset($max_read_delay)) {echo $max_read_delay;} else {echo 10000;} ?>);
                     } else {
                        clearTimeout(timer_notifi);
                     }
                     for_bind_unbind="bind";
                }
               
              }
              
            }
    });
  }
  function reinti(){
      $('#parentVerticalTab').easyResponsiveTabs({
            type: 'vertical', //Types: default, vertical, accordion
            width: 'auto', //auto or any width like 600px
            fit: true, // 100% fit in a container
            closed: 'accordion', // Start closed if in accordion view
            tabidentify: 'hor_1', // The tab groups identifier
            activate: function(event) { // Callback function if tab is switched
                var $tab = $(this);
                var $info = $('#nested-tabInfo2');
                var $name = $('span', $info);
                $name.text();
                $info.show();
            }
        });
  }
    // pick the id and change its backgroud color
    function selectTheContent(id)
    {
       $("#"+id).trigger('click');
        // console.log( $("#"+id).scroll());
       $( window ).scroll();

    }
});
</script>
<script type='text/javascript' src="/portal/theme/default/js/akamaiRegenerateToken.js" ></script>
@stop