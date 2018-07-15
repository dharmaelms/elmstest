@section('content')
<?php
use App\Model\Program;
use App\Model\Category;
use App\Model\SiteSetting;
?>

<div class="tabbable tabbable-tabdrop color-tabs">
  <ul class="nav nav-tabs center">
    <?php
    $general = SiteSetting::module('General');
    $general_category_feeds=SiteSetting::module('General', 'general_category_feeds');
            if($general_category_feeds=="on"){ ?>
    <li ><a href="{{URL::to('program/category-channel')}}"><i class="fa fa-rss-square"></i>&nbsp; <?php echo Lang::get('program.my_course');?></a></li>
    <?php } ?>
    @if($general->setting['watch_now'] == 'on')
      <li><a href="{{URL::to('program/what-to-watch')}}"><i class="fa fa-video-camera"></i>&nbsp; <?php echo Lang::get('program.tab_watch_now');?></a></li>
    @endif
    @if($general->setting['posts'] == 'on')
      <li><a href="{{URL::to('program/my-feeds')}}"><i class="fa fa-rss-square"></i>&nbsp; <?php echo Lang::get('program.tab_posts');?></a></li>
    @endif
    @if($general->setting['favorites'] == 'on')
      <li><a href="{{URL::to('program/favourites')}}"><i class="fa fa-heart"></i>&nbsp; <?php echo Lang::get('program.tab_favorites');?></a></li>
    @endif
    <?php $more_feeds_display=SiteSetting::module('General', 'more_feeds');
            if($more_feeds_display=="on"){ ?>
        <li class="active"><a href="{{URL::to('program/more-feeds')}}"><i class="fa fa-rss"></i>&nbsp; <?php echo Lang::get('program.tab_other_channels');?></a></li>
      <?php } ?>

  </ul>
<div class="tab-content">
    <div class="tab-pane active">
        <div class="row">

          <div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="col-md-12">
              <div class="xs-margin"></div><!--space-->
              <!--start content feed -->

              @if(!empty($more_feeds))
              @foreach($more_feeds as $feed)
                <div>
                  <h3 class="page-title-small"><a href="#">{{$feed['program_title']}}</a></h3>
                </div>
                <div class="md-margin cf-info">
                  <div class="row">
                    <div class="col-md-3 col-sm-4 col-xs-11 xs-margin">
                      @if(empty($feed['program_cover_media']))
                        <img src="{{URL::asset($theme.'/img/book.jpg')}}" title="{{$feed['program_title']}}" class="packet-img img-responsive center-align" alt="{{$feed['program_title']}}">
                     @else
                        <img src="{{URL::to('media_image/'.$feed['program_cover_media'])}}" title="{{$feed['program_title']}}" class="packet-img img-responsive center-align" alt="{{$feed['program_title']}}">
                     @endif
                    </div>
                    <?php
                    $packet_count = Program::getPacketsCount($feed['program_slug']);
                    $category='';$cat_feed_info=Category::getFeedRelatedCategory($feed['program_id']);
                    ?>
                    <div class="cl-lg-8 col-md-9 col-sm-8 col-xs-11">
                      <p style="text-align:justify;width: 900px;word-wrap: break-word;">{!! $feed['program_description'] !!}</p>
                      @foreach($cat_feed_info as $info)
                        <?php $category.= htmlspecialchars_decode($info['category_name']).',';?>
                     @endforeach
                      <table class="sm-margin">
                        @if($category!='')
                          <tr>
                            <td width="140px"><strong><?php if(count($cat_feed_info)>1){ echo trans('category.categories');}else{ echo trans('category.category'); } ?></strong></td>
                            <td>{{ ucwords(strtolower(trim($category,',')))}}</td>
                          </tr>
                        @endif
                        @if(isset($feed['program_startdate']))
                          <tr>
                            <td width="140px"><strong>{{ Lang::get('program.start_date') }}</strong></td>
                            <td>{{Timezone::convertFromUTC('@'.$feed['program_startdate'], Auth::user()->timezone)}}</td>
                          </tr>
                        @endif
                        @if(isset($feed['program_enddate']))
                          <tr>
                            <td width="140px"><strong>{{ Lang::get('program.end_date') }}</strong></td>
                            <td>{{Timezone::convertFromUTC('@'.$feed['program_enddate'], Auth::user()->timezone)}}</td>
                          </tr>
                        @endif
                        @if(isset($packet_count) && isset($feed['program_sub_type']) && $feed['program_sub_type']=='single')
                          <tr>
                            <td width="140px"><strong>No. Of {{ Lang::get('program.packets') }}</strong></td>
                            <td>{{$packet_count}}</td>
                          </tr>
                        @endif
                        <tr>
                          <td width="140px"><strong>{{ Lang::get('program.status') }}</strong></td>
                          <td>Available</td>
                        </tr>
                        @if(isset($feed['child_relations']['active_channel_rel']) && !empty($feed['child_relations']['active_channel_rel']))
                        <?php
                        $child_program_list='';
					    foreach($feed['child_relations']['active_channel_rel'] as $child_id)
					      {
                             $child_program = Program::getProgramDetailsByID($child_id);
						     $child_program_list.= $child_program['program_title'].',';
					      }
                        ?>
                        <tr>
                          <td width="140px"><strong>{{ Lang::get('program.child_course') }}</strong></td>
                          <td><?php echo trim($child_program_list,','); ?></td>
                        </tr>
                        @endif
                        @if(isset($feed['parent_relations']['active_parent_rel']) && !empty($feed['parent_relations']['active_parent_rel']))
                        <?php
                        $parent_program_list='';
					    foreach($feed['parent_relations']['active_parent_rel'] as $parent_id)
					     {
                             $parent_program = Program::getProgramDetailsByID($parent_id);
						     $parent_program_list.= $parent_program['program_title'].',';
					     }
                        ?>
                        <tr>
                          <td width="140px"><strong>{{ Lang::get('program.parent_channels') }}</strong></td>
                          <td><?php echo trim($parent_program_list,','); ?></td>
                        </tr>
                        @endif
                      </table>
                      <p>
                        <a href="{{url::to('program/feed-detail/'.$feed['program_slug'])}}" class="btn red-sunglo btn-sm xs-margin">{{ Lang::get('program.more_info')}}</a>&nbsp;&nbsp;&nbsp;

                         @if(isset($feed['relations']['access_request_pending']) && in_array(Auth::user()->uid,$feed['relations']['access_request_pending']))
                                  <b class="alert alert-success">Your request for this <?php echo strtolower(Lang::get('program.course'));?> is in process.</b>
                         @else
                        <a href="{{url::to('program/feed-access-request/'.$feed['program_id'])}}" onClick="button_clicked(<?php echo $feed['program_id']?>)" id="{{$feed['program_id']}}" class="btn red-sunglo btn-sm xs-margin">{{ Lang::get('program.request_access') }}</a>                         @endif
                      </p>
                    </div>
                  </div>
                </div><!-- END CF Info-->
              @endforeach
              @else
                <h4 align="center"> No {{Lang::get('program.programs')}} to Subscribe.</h4>
              @endif
              <!--end content feed-->
            </div>
            <div id="end" class="no-results" >
          </div><!--facets data div-->
        </div>
      </div>
  </div>
  <div id='no-records' style='display:none' class='col-md-12 center l-gray'><p><strong>{{Lang::get('pagination.no_more_records')}}</strong></p></div>
  </div>


<div id="mrova-feedback">
@include($theme_path.'.common.leftsidebar')
<div id="mrova-img-control"></div>
</div>


<script type="text/javascript">
 var url='<?php echo URL::to('/'); ?>';
    var pageno=0;
    var count='<?php echo count($more_feeds); ?>';
    var stop = flag = true;
    $(window).scroll(function() {
      if( count && stop)
      {
          if($(window).scrollTop() + $(window).height() > $(document).height() - 100) {
              pageno = pageno + 1;
            if(flag){
                flag = false;
                 $.ajax({
                        type: 'GET',
                         url: url+'/program/postnextrecords?pageno='+pageno,
                      }).done(function(html) {
                      if(html.status == true) {
                        $('#end').append(html.data);
                        flag = true;
                      }
                      else {
                        $('#no-records').show();
                        stop = false;
                      }
                    }).fail(function(html) {
                         alert('Failed to get more channel data');
                  });
              }
          }
      }
    });
function button_clicked(id)
{
   document.getElementById(id).setAttribute("disabled","disabled");
}


(function ($) {
$.fn.vAlign = function() {
  return this.each(function(i){
  var h = $(this).height();
  var oh = $(this).outerHeight();
  var mt = (h + (oh - h)) / 1.3;
  $(this).css("margin-top", "-" + mt + "px");
  $(this).css("top", "13%");
  });
};
$.fn.toggleClick = function(){
    var functions = arguments ;
    return this.click(function(){
            var iteration = $(this).data('iteration') || 0;
            functions[iteration].apply(this, arguments);
            iteration = (iteration + 1) % functions.length ;
            $(this).data('iteration', iteration);
    });
};
})(jQuery);
$(window).load(function() {
  //cache
  $img_control = $("#mrova-img-control");
  $mrova_feedback = $('#mrova-feedback');
  $mrova_contactform = $('#mrova-contactform');

  //setback to block state and vertical align to center
  $mrova_feedback.vAlign()
  .css({'display':'block','height':$mrova_feedback.outerHeight()});
  //Aligning feedback button to center with the parent div

  $img_control.vAlign()
  //animate the form
  .toggleClick(function(){
    $mrova_feedback.animate({'right':'-2px'},1000);
  }, function(){
    $mrova_feedback.animate({'right':'-'+$mrova_feedback.outerWidth()},1000);
  });

  //Form handling
  $('#mrova-sendbutton').click( function() {
        var url = 'send.php';
        var error = 0;
        $('.required', $mrova_contactform).each(function(i) {
          if($(this).val() === '') {
            error++;
          }
        });
        // each
        if(error > 0) {
          alert('Please fill in all the mandatory fields. Mandatory fields are marked with an asterisk *.');
        } else {
          $str = $mrova_contactform.serialize();

          //submit the form
          $.ajax({
            type : "GET",
            url : url,
            data : $str,
            success : function(data) {

              if(data == 'success') {
                // show thank you
                $('#mrova-contact-thankyou').show();
                $mrova_contactform.hide();
              } else {
                alert('Unable to send your message. Please try again.');
              }
            }
          });
          //$.ajax

        }
        return false;
      });

});
</script>

@stop