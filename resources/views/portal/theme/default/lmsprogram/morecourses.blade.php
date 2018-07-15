@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}" />
<script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>
<?php
use App\Model\ManageLmsProgram;
use App\Model\User;
$lmsprograms=ManageLmsProgram::getLmsProgramDetails();
$user=User::getActiveUserUsingID($userid);
?>
<div class="tabbable tabbable-tabdrop color-tabs">
    <ul class="nav nav-tabs center">
    <!--<li ><a href="{{URL::to('lmscourse/my-courses')}}"><i class="fa fa-rss-square"></i>&nbsp; My Courses</a></li>-->
    <li class="active"><a href="{{URL::to('lmscourse/more-courses')}}"><i class="fa fa-rss"></i>&nbsp;<?php echo Lang::get('lmscourse.course'); ?></a></li>
    </ul>
    @if(!empty($lmsprograms))
    <?php
    $lmscourses=ManageLmsProgram::getCourseList();
    if(isset($user[0]['relations']['lms_course_rel'])) {
    $user_courses=$user[0]['relations']['lms_course_rel'];
    }
    else {
    $user_courses=array();
    }
    if(is_array($lmscourses)) {
    foreach($lmscourses as $lmscourse) {
    if($lmscourse['id'] > 1 && !in_array($lmscourse['id'], $user_courses)) {
    $batch=ManageLmsProgram::getBatchDetails($lmscourse['id']);
    if (is_array($batch)) {
    ?>
    <!--start--->
       <div class="tab-content">
        <div class="tab-pane active category-page-css">
            <div class="row">
                <div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12">
                    <div class="col-md-12">
                            <div>
                                <!--<h3 class="page-title-small">{{$batch['coursename']}}-{{$batch['batchname']}}</h3>-->
                            </div>
                            <div class="row xs-margin border-btm category-list">
                                <div class="col-md-12 nav-space">
                                <div id="owl-demo" class="owl-carousel owl-theme">

                                            <div class="item">
                                                <div class="packet">
                                                      <figure>
                                                      <a href="{{ array_get($setting, 'site_url') . '/login/index.php?id=' . array_get($lmscourse, 'id') .  '&username=' . $username  }}">
                                                      @if(isset($batch['media']) && !empty($batch['media']))
                                                        <img src="{{URL::to('media_image/'.$batch['media'])}}" alt="Channel" class="packet-img img-responsive">
                                                    @else
                                                        <img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Channel" class="packet-img img-responsive">
                                                    @endif
                                                      </a>
                                                      <div class="channel-label"><span>{{$batch['coursename']}}-{{$batch['batchname']}}</span></div>
                                                      </figure>
                                                </div>

                                            </div>
                                               <div>
                                            <h3>{{$batch['coursename']}}-{{$batch['batchname']}}</h3>
                                            <h5><?php echo Lang::get('lmscourse.start_date'); ?> {{$batch['startdate']}}</h5>
                                                <h5><?php echo Lang::get('lmscourse.end_date'); ?> {{$batch['enddate']}}</h5>
                                            </div>
                                  </div>
                                </div>
                            </div>
                            </div>
                                </div>
                            </div>
                            </div>
                                </div>

        <!--end-->
        <?php }}}} ?>

    </div>
</div>
@endif

<script>
    $(document).ready(function() {
    $('#products .item').addClass('grid-group-item');
    $('#list').click(function(event){event.preventDefault();$('#products .item').addClass('list-group-item');});
    $('#grid').click(function(event){event.preventDefault();$('#products .item').removeClass('list-group-item');
    $('#products .item').addClass('grid-group-item');});
});
function random(){
        $('.owl-carousel').each(function(pos,value){
            $(value).owlCarousel({
                items:4,
                navigation: true,
                navigationText: [
                "<i class='fa fa-caret-left'></i>",
                "<i class='fa fa-caret-right'></i>"
                ],
                beforeInit : function(elem){

                }
            });
        });
    }
    $(document).ready(function() {
    //Sort random function
        random();
    });
</script>
@stop
