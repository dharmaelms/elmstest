@section('content')
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.css')}}" />
<link rel="stylesheet" type="text/css" href="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.theme.css')}}" />
<script src="{{ URL::asset($theme.'/plugins/carousel-owl-carousel/owl-carousel/owl.carousel.js')}}"></script>

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
	<li class="active"><a href="{{URL::to('lmscourse/my-courses')}}"><i class="fa fa-rss-square"></i>&nbsp; My Courses</a></li>
	@if(!empty($site_url))
	<li><a href="{{URL::to('lmscourse/more-courses')}}"><i class="fa fa-rss"></i>&nbsp; Other Courses</a></li>
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
	<!--start--->
        <div class="tab-content">
		<div class="tab-pane active category-page-css">
			<div class="row">
				<div class="facets-data col-lg-12 col-md-12 col-sm-12 col-xs-12">
					<div class="col-md-12">
						
							<div>
								<h3 class="page-title-small">{{$batch['coursename']}}-{{$batch['batchname']}}</h3>
							</div>
							<div class="row xs-margin border-btm category-list">
								<div class="col-md-12 nav-space">
							    <div id="owl-demo" class="owl-carousel owl-theme">
							   		
											<div class="item">
										    	<div class="packet">
							              			<figure>
													<?php
								              		echo '<a href="'.$site['site_url'].'/login/index.php?id='.$lmscourse.'&username='.$username.'" target="_blank" title="">';
                                                    ?>
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