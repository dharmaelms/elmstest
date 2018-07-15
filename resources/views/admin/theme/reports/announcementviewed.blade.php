@section('content')
	@if ( Session::get('success') )
		<div class="alert alert-success">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Success!</strong> -->
			{{ Session::get('success') }}
		</div>
		<?php Session::forget('success'); ?>
	@endif
	@if ( Session::get('error'))
		<div class="alert alert-danger">
			<button class="close" data-dismiss="alert">×</button>
			<!-- <strong>Error!</strong> -->
			{{ Session::get('error') }}
		</div>
		<?php Session::forget('error'); ?>
	@endif
	<script type="text/javascript" src="{{URL::to('admin/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>
	
	<!-- Date Range Picker -->
	<script type="text/javascript" src="{{URL::to('admin/js/bootstrap-daterangepicker/moment.min.js')}}"></script>
	<script type="text/javascript" src="{{URL::to('admin/js/bootstrap-daterangepicker/daterangepicker.js')}}"></script>
	<link rel="stylesheet" type="text/css" href="{{URL::to('admin/js/bootstrap-daterangepicker/daterangepicker-bs3.css')}}" />

	<div class="row custom-box">
		<div class="col-md-12">
	        <div class="box">
	            <div class="box-content">
	            	@include('admin.theme.reports.menu', ['selector' => 'announcementview', 'general' => $general ])
	                <form class="form-horizontal" name="myForm" action="">
                        <div class="form-group">
                            <div class="col-sm-2 col-lg-2 " style="padding-left:0;">
                            </div>
                      		<label class="col-sm-1 col-lg-1 control-label" style="padding-right:0;text-align:right"><b> <?php echo trans('admin/reports.range'); ?> : &nbsp;</b></label>
	                        <div class="col-sm-3 col-lg-3 controls" style="padding-left:0;">
	                            <div class="input-group">
	                                <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
	                                <input type="text" class="form-control daterange" name="range" id="cus_range" value="{{Timezone::convertFromUTC('@'.$start_date, Auth::user()->timezone, 'd-m-Y') . " to " . Timezone::convertFromUTC('@'.$end_date, Auth::user()->timezone, 'd-m-Y')}}"/>
	                            </div>
	                        </div>
                            <div class="col-sm-3 col-lg-1" style="padding-left:0;">
                           
	                          	<div class="input-group" style="">
	                                <button type="button" id="sub" class="form-control btn btn-success datasrc"><?php echo trans('admin/reports.generate'); ?></button>
	                            </div>
                            </div>
                            <div class="col-sm-2 col-lg-1" style="padding-left:0;">
                            	<div class="input-group pull-right" style="padding-left: 2px;">
	                                <button class="show-tooltip btn btn-sucess" title="Info" type="button" id="info_report_btn"><i class="fa fa-info"></i></button>
	                            </div>
	                          	<div class="input-group pull-right">
	                                <button class="show-tooltip btn btn-sucess" title="Report Export" type="button" id="exp_report"><i class="fa fa-download"></i></button>
	                            </div>
                            </div>
                        </div>
                    </form>
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-info" id = "info_report" style="display:none">
								<button class="close">×</button>
								<p>{{trans('admin/reports.announcement_disc')}}</p>
							</div>
						</div>
					</div>
                    <div class="row">
                    	<div class="col-md-1">
                    		<div class="cs-nav-btn">
								<button type="button" id="prev" class="fa fa-angle-left datasrc btn btn-circle"></button>
                    		</div>
                    	</div>
						<div class="col-md-10" style="overflow-x:scroll">
							<div class="alert alert-danger" id='no_record'>
								<button class="close" data-dismiss="alert">×</button>
									{{trans('admin/reports.no_record_found_in_this_combi')}} 
							</div>
	            			<div id="container" class='report-container'></div>
			            </div>
			            <div class="col-md-1">
							<div class="cs-nav-btn">
								<button type="button" id="last" class="fa fa-angle-right datasrc btn btn-circle"></button>
							</div>
			            </div>
	            </div>
	        </div>
	    </div>
    </div>

    <!-- 1st table -->
   </div>
	<div class="row">
		<div class="col-md-9">
			<div class="box">
				<div class="box-content">
		            <table class="table fill-head table-striped">
		            	<thead>
			            	<tr>
			            		<th>{{trans('admin/reports.announcment_name')}}</th>
			            		<th>{{trans('admin/reports.viewed')}}</th>
			            		<th>{{trans('admin/reports.not_viewed')}}</th>
			            	</tr>
			            </thead>
			            <tbody id="data_tbl_id">
			            
			            </tbody>
			            	
		            </table>
		    	</div>
	    	</div>
		</div>
		<div class="col-md-3" id='need_hide'>
			<div class="box">
				<div class="box-content">
					<div id="announcement"></div>
				</div>
			</div>
		</div>
</div>


<script>
$('#need_hide').hide();
$('#no_record').hide();
$('.daterange').daterangepicker({
        format: 'DD-MM-YYYY',
	    maxDate: moment(),
	    dateLimit: { days: 90 },
	    showDropdowns: true,
	    showWeekNumbers: true,
	    timePicker: false,
	    timePickerIncrement: 1,
	    timePicker12Hour: true,
        ranges: {
           'Yesterday': [moment().subtract(1, 'days'), moment()],
           'Last 15 Days': [moment().subtract(15, 'days'), moment()],
           'Last 30 Days': [moment().subtract(30, 'days'), moment()],
        },
        opens: 'right',
        drops: 'down',
        buttonClasses: ['btn', 'btn-sm'],
        applyClass: 'btn-primary',
        cancelClass: 'btn-default',
        separator: ' to ',
        locale: {
            applyLabel: 'Apply',
            cancelLabel: 'Cancel',
            fromLabel: 'From',
            toLabel: 'To',
            customRangeLabel: 'Custom',
            daysOfWeek: ['Su', 'Mo', 'Tu', 'We', 'Th', 'Fr','Sa'],
            monthNames: ['January', 'February', 'March', 'April', 'May', 'June', 'July', 'August', 'September', 'October', 'November', 'December'],
            firstDay: 1
        }
    });		

function visitorData(title,xaxis,viewed,not_viewed){
$('#container').highcharts({

   colors: ["#DF7401", "#58ACFA"],
    chart :{
        type: 'column'
    },
    title: {
            text : ''
        },
    
    xAxis : {
        categories : xaxis,
        title:
        { 
            text : 'Announcements',
            align : 'middle',
         }
    },
    yAxis : {
            min: 0,
            max: 100,
            tickInterval: 10,
            lineColor: '#D8D8D8',
            lineWidth: 1,

    title :
     {
        text : '<?php echo trans('admin/reports.per_user'); ?>',
        align : 'middle'
    },
     labels: {
                overflow: 'justify'
            }
    },
      legend: {
            align: 'right',
            verticalAlign: 'center',
            y: 100,
            layout: 'vertical',
            floating: true,
            backgroundColor: '#FFFFFF',
            borderWidth: 1,
            shadow: false
        }, 
    tooltip :
    {
        valueSuffix : ''
    },
     plotOptions: {
     		series: {
				pointWidth: 30,
			},
            column: {
                stacking: 'normal'
            }
        },
    
    series : [{
            name : '<?php echo trans('admin/reports.viewed'); ?>',
        	data : viewed,
        }
    ],             
});

}
	</script>

<script>
	var error=0;
	var current=0;
	var user_ids_pass = new Array();
	var xaxis = new Array();
	var viewed_list = new Array();
	var not_viewed_list = new Array();

	$('.close').click(function(){
		$(this).parent().hide()
	});
	
	$('#exp_report').click(function(){
		var url = '{{URL::to('/')}}';
		var date_range = $('#cus_range').val();
		var no_set = current;//0;
		var limit = 10;
       	location.href = url+'/cp/reports/csv-announcement-report/'+no_set+'/'+limit+'?range='+date_range;
	});
	$("#sub").click(function(){
		current = 0;
		ajaxCallFunc();
	});

	$('#prev').click(function()
	{
		if(current == 0)
		{
			current = 0;
		}else
		{
			var prev = current-1;
			current--;
		}
		ajaxCallFunc();
	});


	$('#last').click(function()
	{
		var prev = current+1;
		current++;
		ajaxCallFunc();
	});
	$('#info_report_btn').click(function(){
		$('#info_report').show();
	});
	function ajaxCallFunc(){
		var url='{{URL::to('/')}}';
    	var id = $(this).attr('id'); 
		var no_set = current;//0;
		var limit = 7;
		var from = $('#from').val(); 
		var to = $('#to').val(); 
		var date_range = $('#cus_range').val();		
		$.ajax({
			type:'GET',
			url : url+'/cp/reports/ajax-announcement-viewed/'+no_set+'/'+limit+'?range='+date_range
		})
		.done(function(response){
			var data = response.viewed_list;
		 	var title = response.title;
		 		xaxis = response.xaxis;
		 	var viewed = new Array();
		 	var not_viewed = new Array();
		 	var viewed_per = new Array();
		 	var not_viewed_per = new Array();
		 		viewed_list = response.viewed_list;
		 		not_viewed_list = response.not_viewed_list;
		 	var html_temp = "";
		 	var temp_to = 0;
		 
		 	if(viewed_list.length > 0){
		 		$('#no_record').hide();
		 		$.each(viewed_list, function(key1, ele_ary){
		 			temp_to = (ele_ary.length/(ele_ary.length+not_viewed_list[key1].length))*100;
		 			viewed_per.push(Math.round((ele_ary.length/(ele_ary.length+not_viewed_list[key1].length))*100));
		 			not_viewed_per.push(Math.round((not_viewed_list[key1].length/(ele_ary.length+not_viewed_list[key1].length))*100));
		 			viewed.push( ele_ary.length );
					not_viewed.push(not_viewed_list[key1].length);
		 		});
		 	}

		 	if(viewed.length > 0){
		 		$('#no_record').hide();
		 		if($.isArray(viewed)){
	 				var view = 'view';
		 			var not_view = 'not view';
			 		$.each(viewed, function(key, ele){
			 			html_temp+="<tr><td>"+xaxis[key]+"</td><td><a href='#'  onclick='getUsersList("+key+","+1+");'>"+ele+"</a></td><td><a href='#'  onclick='getUsersList("+key+","+0+");'>"+not_viewed[key]+"</a></td></tr>";
		 			});
		 		}
		 		if(html_temp != ""){
		 			$('#data_tbl_id').html(html_temp);
		 		}
		 		visitorData(title,xaxis,viewed_per,not_viewed_per);	
		 	}else{
		 		$('#no_record').show();
		 		current--;
		 	}
	 		
		});
	}

	ajaxCallFunc();

	function getUsersList (id, view_or) {
	    var announce_title = '"'+xaxis[id]+'"';
	   	if(view_or == 1){
	   		var user_ids = viewed_list[id];
	   		announce_title += " Viewed";
	   	}else{
	   		var user_ids = not_viewed_list[id];
	   		announce_title += " Not Viewed";
	   	}
	   	var url='{{URL::to('/')}}';
		if(user_ids.length > 0){
			$.ajax({
				type:'POST',
				url : url+'/cp/reports/ajax-announcement-viewed-count',
				data:{user_id:user_ids}
			})
			.done(function(response){
		  		var html_head = "<table class='table table-advance'><thead><tr><strong><th>"+announce_title+"</th></strong></tr></thead>";
		  		var html_body = '';
		  		if(response.length > 0){
		  			$.each(response, function(index, ele){
		  				html_body+='<tr><td align="left">'+ele+'<td><tr>';
		  			});
		  		}else{
		  			html_body = '<tr><td align="left"> Super admin<td><tr>';
		  		}
		  		$('#need_hide').show();
		  		 $("#announcement").html(html_head+html_body+'</table>'); 
		  		 scroll(0,900);
			});
		}else{
			$('#need_hide').show();
			var html_cont = "<table class='table table-advance'><thead><tr><strong><th>"+announce_title+"</th></strong></tr></thead><tr><td>{{ trans('admin/reports.no_user') }}</td></tr></table> ";
	        $("#announcement").html(html_cont);
	        scroll(0,900);
		}

	}
</script>
@stop