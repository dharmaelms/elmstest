@section('content')
<!--[if IE 8]> <html lang="en" class="ie8 no-js"> <![endif]-->
<!--[if IE 9]> <html lang="en" class="ie9 no-js"> <![endif]-->
<!--[if !IE]><!-->

<!--<![endif]-->
			<div class="page-bar">
				<ul class="page-breadcrumb">
					<li><a href="{{URL::to('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
					<li><a href="#">Search</a></li>
				</ul>
			</div>

		<div class="row">
				<div class="col-md-3 col-sm-3 search-facets facets-sidebar">
				  <form  name="category" action="search" id="cat_feed_facet_filter" method="get" >
						@if(isset($facets) && count($facets['category']) > 0)
							<div class="portlet box grey-cascade">
								<div class="portlet-title">
									<div class="caption">Categories</div>
									<div class="tools"><a href="javascript:;" class="collapse"></a></div>
								</div>
								<div class="portlet-body min-hg-200">
									<div class="dd icheck-list" id="nestable_list_1">
										<ol class="dd-list">
											<li class="dd-item" data-id="1">
												<div class="dd-handle">
													<!-- <label><input type="checkbox" class="icheck"> <strong>Select All</strong> </label> -->
												</div>
											</li>
										<?php  foreach($facets['category'] as $key => $value)
                							{ ?>
											<li class="dd-item facet" data-id="5">
												<div class="dd-handle">
													<label><input type="checkbox" class="icheck" value='{{$key}}' name="category[]" <?php if( isset($facets['category_facet']) && in_array($key,$facets['category_facet'])){?> checked="checked" <?php } ?>><strong> {{ucwords(strtolower($key))}}</strong></label>
												</div>
											</li>
											<?php
               							 }
                					 ?>
										</ol>
									</div>
								</div>
							</div><!-- END Category-->
						@endif
							<!-- START Content feeds-->
							<!-- <div class="portlet box grey-cascade">
								<div class="portlet-title">
									<div class="caption">Content Feeds</div>
									<div class="tools"><a href="javascript:;" class="collapse"></a></div>
								</div>
								<div class="portlet-body min-hg-200">
									<div class="icheck-list">
										<label><input type="checkbox" class="icheck"> <strong>Select All</strong> </label>
										<label><input type="checkbox" class="icheck"> Favorites </label>
										<label><input type="checkbox" class="icheck"> Content Feed 1 </label>
										<label><input type="checkbox" class="icheck"> Content Feed 2 </label>
										<label><input type="checkbox" class="icheck"> Content Feed 3 </label>
										<label><input type="checkbox" class="icheck"> Content Feed 4 </label>
										<label><input type="checkbox" class="icheck"> Content Feed 5 </label>
										<label><input type="checkbox" class="icheck"> Content Feed 6 </label>
										<label><input type="checkbox" class="icheck"> Content Feed 7 </label>
									</div>
								</div>
							</div> -->
						<!-- END Content feeds-->
						@if(isset($facets) && count($facets['format']) > 0)
							<div class="portlet box grey-cascade">
								<div class="portlet-title">
									<div class="caption">Formats</div>
									<div class="tools"><a href="javascript:;" class="collapse"></a></div>
								</div>
								<div class="portlet-body min-hg-200">
									<div class="icheck-list">
										<!-- <label><input type="checkbox" class="icheck"> <strong>Select All</strong> </label> -->
									<?php	foreach($facets['format'] as $key => $value)
                					{ ?>
										<label class="facet"><input type="checkbox" class="icheck" value='{{$key}}' name="format[]" <?php if( isset($facets['format_facet']) && in_array($key,$facets['format_facet'])){?> checked="checked" <?php } ?>><strong> {{$key}}</strong></label>
									<?php } ?>	
									</div>
								</div>
							</div><!-- END Content feeds-->
						@endif
					
					<input type="hidden" name="type" value="facet_search">
					<input type="hidden" name="key" value="{{Input::get('key')}}">
				</form>
			</div>
			
<div class="col-md-9 col-sm-9 search-data">
	<div class="row lg-margin">
		<div class="col-md-6 col-sm-8 col-xs-12">
			<?php if(Input::get('type')=='simple'){ ?>
			<h3 class="margin-top-0">Search results for: <strong>{{$term}}</strong></h3>
			<?php }elseif(Input::get('type')=='advanced'){
			 ?>
			 <?php $category=$format='';?>
			<h3 class="margin-top-0">Searched for: </h3>
			<h4>
				@if(Input::get('title')!=='') Title: <strong>{{ Input::get('title')}}</strong>@endif
				@if(Input::get('description')!=='') Description: <strong>{{ Input::get('description')}}</strong>@endif 
 				@if(Input::get('keywords')!=='') Keywords: <strong>{{ Input::get('keywords')}}</strong>@endif 
				@if(Input::get('category')) Category: @foreach(Input::get('category') as $each)<?php $category.=ucwords(strtolower($each)).','; ?>@endforeach<strong>{{trim($category,',')}}</strong>@endif 
				@if(Input::get('format')) Format: @foreach(Input::get('format') as $each)<?php $format.=$each.','; ?>@endforeach<strong>{{trim($format,',')}}</strong>@endif 
				Specify Dates: @if(Input::get('date')=='All') All @elseif(Input::get('date')=='days') last {{Input::get('days')}} days @else start: {{Input::get('start')}} end: {{Input::get('end')}} @endif
			</h4>
			<?php } ?>
			<p class="font-13 gray">showing: @if(isset($count))@if($count>$per_page){{$per_page}} out @endif {{$count}}  @else 0 @endif results </p>
		</div>
		<?php $advanced_search=Common::checkPermission('portal', 'search', 'advanced-search');
		if($advanced_search==true){?>
		<div class="col-md-3 col-sm-4 col-xs-12 border-left">
			<a href="{{URL::to('advanced-search/')}}" class="btn red-sunglo">Advanced Search</a>
		</div>
		<?php } ?>
	</div>
<?php  ?>
	<div class="row">
		<div class="col-md-9 col-sm-12 col-xs-12">
			<ul class="search-ul" id="dynamic_pager_content2">
				@if(!empty($programs))
				@foreach($programs as $program)
        		<li>
					<div class="img-div">
						<?php if($program['doc_type']=='post'){
						if(isset($program['packet_cover_media']) && $program['packet_cover_media']!=''){ ?>
							<img src="{{URL::to('media_image/'.$program['packet_cover_media'])}}">&nbsp;
						<?php }else{ ?>
							<img src="{{URL::asset($theme.'/img/default_packet.jpg')}}" alt="Packet">&nbsp;
						<?php } 
					} ?>

						<?php if($program['doc_type']=='channel'){ 
						if(isset($program['program_cover_media']) && $program['program_cover_media']!=''){?>
							<img src="{{URL::to('media_image/'.$program['program_cover_media'])}}">&nbsp;
						<?php }else{ ?>
							<img src="{{URL::asset($theme.'/img/default_channel.png')}}" alt="Program">&nbsp;
						<?php } 
					}?>

						<?php if($program['doc_type']=='quiz'){
						 ?>
							<img src="{{URL::asset($theme.'/img/assessment-default.png')}}" alt="Quiz">&nbsp;
						<?php } ?>
						<?php if($program['doc_type']=='event'){
						 ?>
							<img src="{{URL::asset($theme.'/img/packetpage_event.png')}}" alt="Event">&nbsp;
						<?php } ?>

						<?php if($program['doc_type']=='image'){
						if(isset($program['public_file_location']) && $program['public_file_location']!='') {?>
							<img src="{{URL::to($program['public_file_location'])}}">&nbsp;
						<?php }else{ ?>
							<img src="{{URL::asset($theme.'/img/default_link_search.png')}}" alt="Image">&nbsp;
						<?php } 
						}?>
						<?php if($program['doc_type']=='video'){
						if(isset($program['unique_name']) && $program['unique_name']!='') {?>
							<img src="{{URL::to('media_image/'.$program['unique_name'][0])}}">&nbsp;
						<?php }else{ ?>
							<img src="{{URL::asset($theme.'/img/default_link_search.png')}}" alt="Video">&nbsp;
						<?php } 
					}?>

					<?php if($program['doc_type']=='document'){
						if($program['asset_type'][0]=='file'){
						 ?>
						<img src="{{URL::asset($theme.'/img/downloadfile.png')}}" alt="Document">&nbsp;
						<?php }else{ 
					?><img src="{{URL::asset($theme.'/img/default_link_search.png')}}" alt="Document">&nbsp;
					<?php } 
					}?>

					<?php if($program['doc_type']=='audio'){;
						 if($program['asset_type'][0]=='file'){
						 ?>
						<img src="{{URL::asset($theme.'/img/packetpage_audio.png')}}" alt="Audio">&nbsp;
						<?php }else{ 
					?><img src="{{URL::asset($theme.'/img/default_link_search.png')}}" alt="Audio">&nbsp;
						<?php  
					}
				}?>

					<?php if($program['doc_type']=='announcement'){
						 ?>
						<img src="{{ URL::to($theme.'/img/announce/announcementDefault.png') }}" alt="Announcement">&nbsp;
						<?php  
					}?>

					</div>
					<div class="data-div">
						<?php  $now=str_replace('+00:00', 'Z', gmdate('c', time()));
						 // if(isset($program['program_startdate'])) echo $program['program_startdate'][0]; if(isset($program['program_enddate'])) echo $program['program_enddate'][0];   
						 ?>
						@if( isset($program['program_startdate']) && isset($program['program_enddate']) && (Timezone::getTimeStamp($program['program_startdate'][0]) <= $now) && (Timezone::getTimeStamp($program['program_enddate'][0]) >= $now))
							@if(isset($program['program_title']))
								<a href="{{URL::to('program/packets/'.$program['program_slug'])}}"><strong>{{$program['program_title']}}</strong></a><br>
							@endif 
						@else
							@if(isset($program['program_title']))
								<span class="pkt-opacity"><strong>{{$program['program_title']}}</strong></span><br>
							@endif
						@endif
						@if(isset($program['quiz_name']))<a href="{{url('assessment/detail/'.$program['quiz_id'][0])}}"><strong>{{$program['quiz_name'][0]}}</strong></a><br>@endif 
						@if(isset($program['event_name']))<a href="{{url('/event')}}"><strong>{{$program['event_name'][0]}}</strong></a><br>@endif 
						@if(isset($program['packet_title']) && isset($program['packet_slug']))<a href="{{URL::to('program/packet/'.$program['packet_slug'][0])}}"><strong>{{$program['packet_title'][0]}}</strong></a><br>@endif
						@if(isset($program['name']))<strong>{{$program['name'][0]}}</strong><br>@endif  
						@if(isset($program['announcement_title']))<a href="{{url('/announcements')}}"><strong>{{$program['announcement_title']}}</strong></a><br>@endif 
						<i>@if(isset($program['doc_type'])){{ucwords($program['doc_type'])}}</br>@endif</i>
						@if(isset($program['category']))
					  	 <?php $category=''; ?>
							@foreach($program['category'] as $info)
								<?php $category.=$info.',';?>
							@endforeach
									<i> {{ucwords(strtolower(trim($category,',')))}}</i>
							@endif 
						<p class="font-13">
							@if(isset($program['program_description'])){{trim($program['program_description'])}}@endif 
							@if(isset($program['packet_description'])){{trim($program['packet_description'][0])}}@endif
					  	 </p>
					  	 
					</div>
				</li>
				@endforeach
				@else
					<b>{{'No Results found'}}</b>
				@endif
     		 </ul>
      
			<p id="dynamic_pager_demo2" class="search-pagiation pull-right">
			</p>
		</div>
	</div>
		  <div id="end" ></div>	

	</div><!--search data-->
</div><!--main row-->
	<!-- END CONTENT -->
	<!-- BEGIN QUICK SIDEBAR -->
	<a href="javascript:;" class="page-quick-sidebar-toggler"><i class="icon-close"></i></a>
	<div class="page-quick-sidebar-wrapper">
		<div class="page-quick-sidebar">
			<div class="nav-justified">
				<ul class="nav nav-tabs nav-justified">
					<li class="active">
						<a href="#quick_sidebar_tab_1" data-toggle="tab">
						Users <span class="badge badge-danger">2</span>
						</a>
					</li>
					<li>
						<a href="#quick_sidebar_tab_2" data-toggle="tab">
						Alerts <span class="badge badge-success">7</span>
						</a>
					</li>
					<li class="dropdown">
						<a href="javascript:;" class="dropdown-toggle" data-toggle="dropdown">
						More<i class="fa fa-angle-down"></i>
						</a>
						<ul class="dropdown-menu pull-right" role="menu">
							<li>
								<a href="#quick_sidebar_tab_3" data-toggle="tab">
								<i class="fa fa-bell"></i> Alerts </a>
							</li>
							<li>
								<a href="#quick_sidebar_tab_3" data-toggle="tab">
								<i class="icon-info"></i> Notifications </a>
							</li>
						</ul>
					</li>
				</ul>
				<div class="tab-content">
					<div class="tab-pane active page-quick-sidebar-chat" id="quick_sidebar_tab_1">
						<div class="page-quick-sidebar-chat-users" data-rail-color="#ddd" data-wrapper-class="page-quick-sidebar-list">
							<h3 class="list-heading">Staff</h3>
							<ul class="media-list list-items">
								<li class="media">
									<div class="media-status">
										<span class="badge badge-success">8</span>
									</div>
									<img class="media-object" src="../../assets/admin/layout/img/avatar3.jpg" alt="...">
									<div class="media-body">
										<h4 class="media-heading">Bob Nilson</h4>
										<div class="media-heading-sub">
											 Project Manager
										</div>
									</div>
								</li>
								<li class="media">
									<img class="media-object" src="../../assets/admin/layout/img/avatar1.jpg" alt="...">
									<div class="media-body">
										<h4 class="media-heading">Nick Larson</h4>
										<div class="media-heading-sub">
											 Art Director
										</div>
									</div>
								</li>
							</ul>
						</div>
						<div class="page-quick-sidebar-item">
							<div class="page-quick-sidebar-chat-user">
								<div class="page-quick-sidebar-nav">
									<a href="javascript:;" class="page-quick-sidebar-back-to-list"><i class="icon-arrow-left"></i>Back</a>
								</div>
								<div class="page-quick-sidebar-chat-user-messages">
									<div class="post out">
										<img class="avatar" alt="" src="../../assets/admin/layout/img/avatar3.jpg"/>
										<div class="message">
											<span class="arrow"></span>
											<a href="javascript:;" class="name">Bob Nilson</a>
											<span class="datetime">20:15</span>
											<span class="body">
											When could you send me the report ? </span>
										</div>
									</div>
									<div class="post in">
										<img class="avatar" alt="" src="../../assets/admin/layout/img/avatar2.jpg"/>
										<div class="message">
											<span class="arrow"></span>
											<a href="javascript:;" class="name">Ella Wong</a>
											<span class="datetime">20:15</span>
											<span class="body">
											Its almost done. I will be sending it shortly </span>
										</div>
									</div>
								</div>
								<div class="page-quick-sidebar-chat-user-form">
									<div class="input-group">
										<input type="text" class="form-control" placeholder="Type a message here...">
										<div class="input-group-btn">
											<button type="button" class="btn blue"><i class="icon-paper-clip"></i></button>
										</div>
									</div>
								</div>
							</div>
						</div>
					</div>
					<div class="tab-pane page-quick-sidebar-alerts" id="quick_sidebar_tab_2">
						<div class="page-quick-sidebar-alerts-list">
							<h3 class="list-heading">General</h3>
							<ul class="feeds list-items">
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-info">
													<i class="fa fa-check"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 You have 4 pending tasks. <span class="label label-sm label-warning ">
													Take action <i class="fa fa-share"></i>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 Just now
										</div>
									</div>
								</li>
								<li>
									<a href="javascript:;">
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-success">
													<i class="fa fa-bar-chart-o"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 Finance Report for year 2013 has been released.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 20 mins
										</div>
									</div>
									</a>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-danger">
													<i class="fa fa-user"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 You have 5 pending membership that requires a quick review.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 24 mins
										</div>
									</div>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-info">
													<i class="fa fa-shopping-cart"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 New order received with <span class="label label-sm label-success">
													Reference Number: DR23923 </span>
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 30 mins
										</div>
									</div>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-success">
													<i class="fa fa-user"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 You have 5 pending membership that requires a quick review.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 24 mins
										</div>
									</div>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-info">
													<i class="fa fa-bell-o"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 Web server hardware needs to be upgraded. <span class="label label-sm label-warning">
													Overdue </span>
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 2 hours
										</div>
									</div>
								</li>
								<li>
									<a href="javascript:;">
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-default">
													<i class="fa fa-briefcase"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 IPO Report for year 2013 has been released.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 20 mins
										</div>
									</div>
									</a>
								</li>
							</ul>
							<h3 class="list-heading">System</h3>
							<ul class="feeds list-items">
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-info">
													<i class="fa fa-check"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 You have 4 pending tasks. <span class="label label-sm label-warning ">
													Take action <i class="fa fa-share"></i>
													</span>
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 Just now
										</div>
									</div>
								</li>
								<li>
									<a href="javascript:;">
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-danger">
													<i class="fa fa-bar-chart-o"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 Finance Report for year 2013 has been released.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 20 mins
										</div>
									</div>
									</a>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-default">
													<i class="fa fa-user"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 You have 5 pending membership that requires a quick review.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 24 mins
										</div>
									</div>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-info">
													<i class="fa fa-shopping-cart"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 New order received with <span class="label label-sm label-success">
													Reference Number: DR23923 </span>
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 30 mins
										</div>
									</div>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-success">
													<i class="fa fa-user"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 You have 5 pending membership that requires a quick review.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 24 mins
										</div>
									</div>
								</li>
								<li>
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-warning">
													<i class="fa fa-bell-o"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 Web server hardware needs to be upgraded. <span class="label label-sm label-default ">
													Overdue </span>
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 2 hours
										</div>
									</div>
								</li>
								<li>
									<a href="javascript:;">
									<div class="col1">
										<div class="cont">
											<div class="cont-col1">
												<div class="label label-sm label-info">
													<i class="fa fa-briefcase"></i>
												</div>
											</div>
											<div class="cont-col2">
												<div class="desc">
													 IPO Report for year 2013 has been released.
												</div>
											</div>
										</div>
									</div>
									<div class="col2">
										<div class="date">
											 20 mins
										</div>
									</div>
									</a>
								</li>
							</ul>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
	<!-- END QUICK SIDEBAR -->

<script>
 //  jQuery(document).ready(function() {    
 //    Metronic.init(); // init metronic core components
	// 	Layout.init(); // init current layout
	// 	QuickSidebar.init(); // init quick sidebar
	// 	Demo.init(); // init demo features

	// 	UIGeneral.init();
	// 	UINestable.init();
	// 	FormiCheck.init(); // init page demo
	// });

   $('.facet').click(function(){

    $('#cat_feed_facet_filter').submit();
  }); 

   $(document).ready(function () {
   		var url='<?php echo URL::to('/'); ?>';

	    $('#end').hide();
	    var searchpageno = 1;    
	    $(window).scroll(function () {   

	        if ($(window).scrollTop() == ($(document).height() - $(window).height())) {
	             
	            loadData(searchpageno);
	            searchpageno = searchpageno + 1;
	
	        }
	    });

	    function loadData(searchpageno) 
	    {
	        $.ajax(
	        { 

	          type: 'GET',
	          url: url+'/search/getnextprograms?searchpageno=' + searchpageno,
	          
	          success: function(html) 
	          { 
	            if(html==0)
	            {
	                $(window).unbind("scroll");
	                $('#loadingimg').hide();
	                $('#end').show();
	                $('#end').append( "<h6>No More Records</h6>");
	            
	            }else
	            {
	            	$('#end').show();
	               $('#end').append(html);
	            }
	                   
	          }
	        });
	         
	    }
	    });
  </script>
<!-- END JAVASCRIPTS -->

 

@stop
