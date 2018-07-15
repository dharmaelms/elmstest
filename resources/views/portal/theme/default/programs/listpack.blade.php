@section('content')
<?php
use App\Model\Package\Entity\Package;
use App\Model\Program;
use App\Model\User;
use App\Model\Category;
$more_feeds = User::getAllUserDetailsByID($userid);
?>

<div class="tabbable tabbable-tabdrop color-tabs">

<div class="tab-content">
    <div class="tab-pane active">
        <div class="row">

          <div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div class="col-md-12">
              <div class="xs-margin"></div><!--space-->
              <!--start content feed -->

              @if(isset($more_feeds[0]['relations']['user_parent_feed_rel']) && !empty($more_feeds[0]['relations']['user_parent_feed_rel']))
              @foreach($more_feeds[0]['relations']['user_parent_feed_rel'] as $package_id)
                <div>
                <?php
                $feed = Package::getPackageDetailsByID($package_id);
                ?>
                  <h3 class="page-title-small"><a href="{{ URL::to('package/detail/'.$feed['package_slug'])}}">{{$feed['package_title']}}</a></h3>
                </div>
                <div class="md-margin cf-info">

                  <div class="row">

                    <div class="col-md-3 col-sm-4 col-xs-11 xs-margin">
                      @if(empty($feed['package_cover_media']))
                        <img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" title="{{$feed['package_title']}}" class="packet-img img-responsive center-align" alt="{{$feed['package_title']}}">
                     @else
                        <img src="{{URL::to('media_image/'.$feed['package_cover_media'])}}" title="{{$feed['package_title']}}" class="packet-img img-responsive center-align" alt="{{$feed['package_title']}}">
                     @endif

                    </div>

                    <?php
                    $category='';$cat_feed_info=Category::getFeedRelatedCategory($feed['package_id']);
                    ?>
                    <div class="cl-lg-8 col-md-9 col-sm-8 col-xs-11">
                      <p>{!! $feed['package_description'] !!}</p>
                      @foreach($cat_feed_info as $info)
                        <?php $category.= html_entity_decode($info['category_name']).',';?>
                     @endforeach
                      <table class="sm-margin">
                        @if($category!='')
                          <tr>
                            <td width="140px"><strong><?php if(count($cat_feed_info)>1){ echo trans('category.categories');}else{ echo trans('category.category'); } ?></strong></td>
                            <td>{{ ucwords(strtolower(trim($category,',')))}}</td>
                          </tr>
                        @endif
                        @if(isset($feed['package_startdate']))
                          <tr>
                            <td width="140px"><strong>{{ Lang::get('program.start_date') }}</strong></td>
                            <td>{{Timezone::convertFromUTC('@'.$feed['package_startdate'], Auth::user()->timezone)}}</td>
                          </tr>
                        @endif
                        @if(isset($feed['package_enddate']))
                          <tr>
                            <td width="140px"><strong>{{ Lang::get('program.end_date') }}</strong></td>
                            <td>{{Timezone::convertFromUTC('@'.$feed['package_enddate'], Auth::user()->timezone)}}</td>
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
                             $child_program = Package::getPackageDetailsByID($child_id);
                 //$child_program_list.= $child_program['program_title'].',';
                             $child_program_list.= '<a href="'.URL::to('program/packets/'.$child_program['package_slug']).'">'.$child_program['package_title'].'<a>, ';
                }
                        ?>
                        <tr>
                          <td width="140px"><strong>{{ Lang::get('program.child_channels') }}</strong></td>
                          <td><?php echo trim($child_program_list,', '); ?></td>
                        </tr>
                        @endif

                      </table>
                      <!--<p>
                        <a href="{{url::to('program/feed-detail/'.$feed['program_slug'])}}" class="btn red-sunglo btn-sm xs-margin">{{ Lang::get('program.more_info')}}</a>&nbsp;&nbsp;&nbsp;

                         @if(isset($feed['relations']['access_request_pending']) && in_array(Auth::user()->uid,$feed['relations']['access_request_pending']))
                                  <b class="alert alert-success">Your request for this <?php echo strtolower(Lang::get('program.program'));?> is in process.</b>
                         @else
                        <a href="{{url::to('program/feed-access-request/'.$feed['program_id'])}}" onClick="button_clicked(<?php echo $feed['program_id']?>)" id="{{$feed['program_id']}}" class="btn red-sunglo btn-sm xs-margin">{{ Lang::get('program.request_access') }}</a>                         @endif
                      </p>-->
                    </div>
                  </div>
                </div><!-- END CF Info-->
              @endforeach
              @else
                <h4 align="center"> No {{Lang::get('program.package')}}s assigned to you.</h4>
              @endif
              <!--end content feed-->
            </div>
            <!--facets data div-->
        </div>
      </div>
  </div>

</div>

@stop