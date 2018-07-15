@section('content')
	<script type="text/javascript" src="{{URL::to('admin/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>
	<div class="row custom-box">
		<div class="col-md-12">
	        <div class="box">
	            <div class="box-content">
	            	@include('admin.theme.reports.menu', ['selector' => 'channelperformance', 'general' => $general ])
	            	<div class="" style="text-align:left; padding-bottom:10px;padding-left:260px;">
	            		<a href="{{URL::to('cp/reports/admin-reports')}}" 
	            			title="{{ trans('admin/reports.channel_quiz_perf') }}" 
	            			class="show-tooltip btn btn-gray  channelperformance">
	            				{{trans('admin/reports.channel_quiz')}}
	            		</a>
	            		<a href="{{URL::to('cp/reports/direct-quiz-user')}}" 
	            			title="{{trans('admin/reports.direct_quiz')}}"
	            			class="show-tooltip btn btn-primary directuserquiz" >
	            				{{trans('admin/reports.direct_quiz')}}
	            		</a>
	            	</div>
	                <form class="form-horizontal" name="myForm" action="" onclick="">
                        <div class="form-group">
                            <div class="col-sm-24 col-lg-3 pull-right" style="padding-right: 50px;">
                            	<div class="input-group pull-right" style="padding-left: 2px;">
	                                <button class="show-tooltip btn btn-sucess" 
	                                	title="{{ trans('admin/reports.info') }}" 
	                                	type="button" id="info_report_btn">
	                                		<i class="fa fa-info"></i>
	                                </button>
	                            </div>
	                          	<div class="input-group pull-right">
	                                <button class="show-tooltip btn btn-sucess" 
	                                	title="{{trans('admin/reports.report_export')}}"
	                                	type="button"
	                                	id="exp_report">
	                                		<i class="fa fa-download"></i>
	                                </button>
	                            </div>
                            </div>
                        </div>
                    </form>
					<div class="row">
						<div class="col-md-12">
							<div class="alert alert-info" id = "info_report" style="display:none">
								<button class="close">×</button>
								<p>{{trans('admin/reports.direct_quiz_user_disc')}}</p>
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
								<button class="close">×</button>
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
  	</div>
	<div class="row">
		<div class="col-md-6 col-md-offset-3">
			<div class="box">
				<div class="box-content">
		            <table class="table fill-head table-striped">
		            	<thead>
			            	<tr>
			            		<th>{{trans('admin/reports.quiz_name')}}</th>
			            		<th>{{trans('admin/reports.scores')}}</th>
			            	</tr>
			            </thead>
			            <tbody id="data_tbl_id">
			            </tbody>
			            </tr>
		            </table>
		    	</div>
	    	</div>
	    </div>
	</div>
<script>
	var current = 0;
	$('#no_record').hide();
    $('#prev').click(function()
    {
        if(current <= 0)
        {
            current = 0;
        } else {
        	current--;
        }
        ajaxCallFunc();
    });

    $('#last').click(function()
    {
        current++;
        ajaxCallFunc();
    });

	$('#exp_report').click(function(){
		var url = '{{URL::to('/')}}';
		var from = $('#from').val();
		var to = $('#to').val();
		var date_range = $('#cus_range').val();
       	location.href = url+'/cp/reports/c-s-v-direct-quiz-user';
	});
	$('#info_report_btn').click(function(){
		$('#info_report').show();
	});
	$('.close').click(function(){
		$(this).parent().hide()
	});
	function drawChart(title, xaxis, id_quiz, data, avg){
		$('#container').highcharts({
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
					text : '{{trans('admin/reports.quizzes')}}',
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
					text : '{{trans('admin/reports.scores')}}',
					align : 'middle'
				},
			 	labels: {
			    	overflow: 'justify'
			    }
			},
			tooltip :
			{
				valueSuffix : '%'
			},
			plotOptions: {
				series: {
					pointWidth: 30,
			      	cursor: 'pointer',
		            point: {
		                events: {
		                    click: function(e) {
		                    	var url = '{{URL::to('/')}}';
		                    	  location.href = url+'/cp/reports/direct-quiz-performance-by-question/'+id_quiz[this.index];
		                    },
		                },
		            },
		        },
		    },
			series : [{
				name : "{{trans('admin/reports.quiz_perf')}}",
				data : data,
			}],           
		});
	}

	function ajaxCallFunc(){
		var url='{{URL::to('/')}}';
    	var id = $(this).attr('id'); 
		
		$.ajax({
			type:'GET',
			url : url+'/cp/reports/ajax-direct-quiz-user/'+current
		})
		.done(function(response){
			$('#range').html("");
			$('#range1').html("");
			var data_flter = new Array();
			var title_flter = new Array();
			var data = response.data;
		 	var title = response.title;
		 	var xaxis = response.xaxis;
		 	var avg = response.avg;
		 	var id_quiz =response.id;
		 	var html_temp = "";
		 	if(data.length > 0){
		 		if($.isArray(data)){
					$('#no_record').hide();
					$('#prev').show();
					$('#last').show();
			 		$.each(data, function(key, ele){
		 				var url_quiz = url+'/cp/reports/direct-quiz-performance-by-question';
		 				data_flter.push(ele);
		 				title_flter.push(xaxis[key]);
		 				url_quiz += '/'+id_quiz[key];
		 				html_temp+="<tr><td><a href='"+url_quiz+"'>"+xaxis[key]+"</a></td><td>"+ele+"</td></tr>";	
		 			});
		 		}
		 		if(html_temp != ""){
		 			$('#data_tbl_id').html(html_temp);
		 		}
		 		drawChart(title, title_flter, id_quiz, data_flter, avg);
		 	}else{
				$('#no_record').show();
				$('#container').hide();
				$('#prev').hide();
				$('#last').hide();
				current--;
		 	}
		});
	}
	ajaxCallFunc();
</script>
@stop
