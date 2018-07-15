@section('content')
<?php
use App\Model\ManageLmsProgram;
use App\Model\User;
use App\Model\SiteSetting;
$site_url=SiteSetting::module('Lmsprogram', 'site_url');
$user=User::getActiveUserUsingID($userid);
$username=Auth::user()->username;
?>
<div class="tabbable tabbable-tabdrop color-tabs">
	<ul class="nav nav-tabs center">
	<li class="active"><a href="{{URL::to('lmscourse/my-courses')}}"><i class="fa fa-rss-square"></i>&nbsp;<?php echo Lang::get('lmscourse.my_course'); ?></a></li>
	@if(!empty($site_url))
	<!--<li><a href="{{URL::to('lmscourse/more-courses')}}"><i class="fa fa-rss"></i>&nbsp; Other Courses</a></li>-->
    @endif
	</ul>

	
	@if(isset($user[0]['relations']['lms_course_rel']) && !empty($user[0]['relations']['lms_course_rel']))
    <!--<div class="well well-sm">
        <strong>Display</strong>
        <div class="btn-group">
            <a href="#" id="list" class="btn btn-default btn-sm"><span class="glyphicon glyphicon-th-list">
            </span>List</a>
				<a href="#" id="grid" class="btn btn-default btn-sm"><span
                class="glyphicon glyphicon-th"></span>Grid</a>
        </div>
    </div>-->
		
 
	<?php
	$lmscourses=$user[0]['relations']['lms_course_rel'];
	foreach($lmscourses as $lmscourse) {
    $batch=ManageLmsProgram::getBatchDetails($lmscourse);
	?>
	<!--start-->
  <div class="tab-content">
		<div class="tab-pane active category-page-css">
			<div class="row">
				<div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12 mycourse-list">
					<div class="course-list">
						<h3 class="page-title-small">{{$batch['coursename']}}-{{$batch['batchname']}}</h3>
							
						<div class="row xs-margin margin-top-10">
							<div class="col-md-4 col-sm-4 col-xs-3">
            			<figure>
					              		@if(isset($batch['media']) && !empty($batch['media']))
											<img src="{{URL::to('media_image/'.$batch['media'])}}" alt="{{$batch['coursename']}}-{{$batch['batchname']}}" title="{{$batch['coursename']}}-{{$batch['batchname']}}" class="packet-img img-responsive">
										@else
											<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="{{$batch['coursename']}}-{{$batch['batchname']}}" title="{{$batch['coursename']}}-{{$batch['batchname']}}" class="packet-img img-responsive">
										@endif
              		</figure>
							</div>
							<div class="col-md-8 col-sm-8 col-xs-9">
								<div>
					        <div class="price">
					          <p class="red">{{ Lang::get('lmscourse._start_date') }}</p>
					          <p class="amt">{{$batch['startdate']}}</p>
					        </div>
					        <div class="price">
					          <p class="red">{{ Lang::get('lmscourse._end_date') }}</p>
					          <p class="amt">{{$batch['enddate']}}</p>
					        </div>                                                
					        <div class="btn-div">
					            <?php
					              		echo '<a href="'.$site_url.'/login/index.php?id='.$lmscourse.'&username='.$username.'" class="btn btn-primary margin-bottom-5">' ?>
                                                                <?php echo Lang::get("lmscourse.view_course") ?>
                                                                </a>
                                              
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
        
			<?php } ?>
    </div>
<!-- </div> -->
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