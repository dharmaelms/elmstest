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
<style type="text/css">
        .user-profile-info{
          clear: both;
          /*border-style:;*/
          /*border-width: 5px;*/
        }
        #report_chart{
            float: left;
        }
        #report_tbl{
            /*padding-left: 200px;*/
            /*padding-top: 100px;*/
            float: left;
        }
        td {
            /*padding: 25px;*/
            text-align: left;
        }
        th{
        /*padding: 20px;*/
        }
        table{
            border: 1px;
            /*border-color:red; */
        }
    </style>
	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{URL::asset('portal/theme/default/js/Chart.js')}}"></script>
    <div class="row">
    		<div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><a href="{{URL::to('/')}}">{{Lang::get('reports.home')}}</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{URL::to('reports/quizz-over-all-performance')}}">{{Lang::get('reports.reports')}}</a><i class="fa fa-angle-right"></i></li>
                   	<li>{{Lang::get('reports.quiz_perf')}} </li>
                </ul>
                <div class="page-breadcrumb pull-right">
                    <div class="box-tool">
                        <a  style="color:white;background-color:orange" href="{{URL::to('/reports/area-improve')}}">{{Lang::get('reports.area_of_improvement')}}</a>
                    </div>
        		</div>
            </div>
            
    	
			<div class="col-md-12">
		        <div class="box box-red">
		           <div class="box-title">
                   <h3 style="color:black"> {{Lang::get('reports.overall_quiz_perf')}}<?php if(isset($overallquizz) && !empty($overallquizz)){echo $overallquizz."%";}else{echo "0%";}?></h3> 
		                <!-- <h3 style="color:black"> Quiz performance </h3>  -->
		                <span id="no_quiz" style="display:none;color:orange"> {{Lang::get('reports.no_quiz_records_found_in_this_combination')}} </span>
		                <div class="col-md-6  pull-right">
                            <form class="form-horizontal" action="">
                                <div class="form-group" >
                                  <label class="col-sm-4 col-lg-4 control-label" style="padding-right:10px;text-align:right"><b>{{Lang::get('reports.show_on')}}</b></label>
                                  <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                    <select class="form-control chosen" name="show" data-placeholder="Active" id="chart_show_by_id" tabindex="1">
                           <!--              <option value="all" > All </option>
                                        <option value="fail" > Bellow  50% scored </option>
                                        <option value="pass" > Above  50% scored</option> -->
                                        <option value="all" <?php if(isset($show) && !empty($show) && $show=="all"){ echo "selected";}?> >{{Lang::get('reports.all')}}</option>
                                        <option value="fail" <?php if(isset($show) && !empty($show) && $show=="fail"){ echo "selected";}?>>{{Lang::get('reports.below_50%_scored')}}</option>
                                        <option value="pass" <?php if(isset($show) && !empty($show) && $show=="pass"){ echo "selected";}?>>{{Lang::get('reports.above_50%_scored')}}</option>
                                       </select>
                                  </div>
                               </div>
                               <div class="form-group" >
                                  <label class="col-sm-4 col-lg-4 control-label" style="padding-right:10px;text-align:right"><b>{{Lang::get('reports.filter_by')}}</b></label>
                                  <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                    <select class="form-control chosen" name="filter" data-placeholder="Active" id="chart_filter_by_id" tabindex="1">
                                        <!-- <option value="day" selected>Last 24 Hours</option>
                                        <option value="week" >Last 7 Days</option>
                                        <option value="month" >Last 30 Days</option>
                                        <option value="year" >Last 12 Months</option>
                                        <option value="all" >All</option> -->
                                        <option value="day" <?php if(isset($time_int) && !empty($time_int) && $time_int=="day"){ echo "selected";}?> >{{Lang::get('reports.last_24_hours')}}</option>
                                        <option value="week" <?php if(isset($time_int) && !empty($time_int) && $time_int=="week"){ echo "selected";}?>>{{Lang::get('reports.last_7_days')}}</option>
                                        <option value="month" <?php if(isset($time_int) && !empty($time_int) && $time_int=="month"){ echo "selected";}?>>{{Lang::get('reports.last_30_days')}}</option>
                                        <option value="year" <?php if(isset($time_int) && !empty($time_int) && $time_int=="year"){ echo "selected";}?>>{{Lang::get('reports.last_12_months')}}</option>
                                        <option value="all" <?php if(isset($time_int) && !empty($time_int) && $time_int=="all"){ echo "selected";}?>>{{Lang::get('reports.all')}}All</option>
                                 
                                    </select>
                                  </div>
                               </div>
                            </form>
                        </div>		
                    </div>
		            </div>
		            <div class="box-content">
		            	<div class="user-profile-info">
          				 	<div id='report_chart'>
                				<canvas id="myChart" height="300px" width="600"></canvas>
            				</div>
                    <?php 
                            if(isset($quizz_titles) && !empty($quizz_titles) && isset($quizz_score) && isset($ids) && !empty($ids)){
                          ?>
                            <div id = "report_tbl">
                                <table>
                                    <tr>
                                        <th> {{Lang::get('reports.quiz')}}</th>
                                        <th>{{Lang::get('reports.avg_score_wp')}}</th>
                                        <th> {{Lang::get('reports.link')}}</th>                                    
                                    </tr>

                                    <?php
                                        foreach ($quizz_titles as $key => $value) {
                                            ?>
                                                <tr>
                                                   <td>{{$value}}</td> 
                                                   <td>{{$quizz_score[$key]}}</td> 
                                                   <td><a href="{{URL::to('/assessment/detail/'.$ids[$key])}}"> {{Lang::get('reports.read_more')}}</a></td> 
                                                </tr>
                                            <?php
                                        }
                                    ?>
                                </table>
                            </div>
                            <?php  }
                            ?>
            			</div>
            		</div>
            	</div>
			</div>
		</div>
<script type="text/javascript">
	<?php
		$lables     = array();
		$percentage = array();
		if(isset($quizz_titles) && !empty($quizz_titles) && isset($quizz_score)){
			foreach ($quizz_titles as $key => $value) {
				array_push($lables, $value);
				array_push($percentage, $quizz_score[$key]);
			}
		}
	?>
	var label_js={!!json_encode($lables)!!};//["user"];
	var percentage_js={!!json_encode($percentage)!!};//[60];
    var ids_js        ={!!json_encode($ids)!!};
    var wd = 100;
	$(document).ready(function(){
        // console.log(ids_js);
        var canvas = document.getElementById("myChart");
     	var ctx = canvas.getContext("2d");
        
     	if(label_js.length < 1){
     		$('#no_quiz').show();
     	}else{
          wd = label_js.length*8; 
          if(label_js.length >20){
              wd = label_js.length*4;
          }else if(label_js.length <20 && label_js.length > 10) {
              wd = label_js.length*4.75; 
          }else if(label_js.length <10) {
              wd = label_js.length*7;
          }
          if(wd > 80){
              $("#myChart").css('width','80%');
          }else{
              $("#myChart").css('width',wd+'%');
          }
          $("#myChart").css('height','400px');
          reload_chart(label_js, percentage_js, ids_js);

        }
		function reload_chart(var1, var2, var3) {
     		lineChartData = {
           		labels : var1,
           	 	datasets : [
                {
                    fillColor : "rgba(0,0,255, 0.9)",
                    strokeColor : "rgba(220,220,220,1)",
                    pointColor : "rgba(220,220,220,1)",
                    pointStrokeColor : "#fff",
                    pointHighlightFill : "#fff",
                    pointHighlightStroke : "rgba(220,220,220,1)",
                    ids :var3,
                    data :var2
                }
            	]
	        }
            var myBarChart = new Chart(ctx).Bar(lineChartData,{ 
                responsive: false,
                showTooltips : false,
                barStrokeWidth:3,
                scaleGridLineWidth:1,
                scaleLabel:"<%=value%>%",
                // barValueSpacing:20,
                scaleLineWidth:1
            });
            canvas.onclick = function(evt){
                // var activePoints[0]['id'] = 0;
                var activePoints = myBarChart.getBarsAtEvent(evt);
                console.log(activePoints[0]['id']);
                if(activePoints.length > 0){
                    window.location.href = '{{URL::to('/assessment/detail')}}/'+activePoints[0]['id'];
                }
             };
     	}

     	function forajaxcall() {
         window.location.href ='{{URL::to('/reports/quizz-performance/')}}/'+$("#chart_filter_by_id").val()+"/"+$("#chart_show_by_id").val()+'/'+'redirect';
     		/*$.ajax({
            	type: "GET",
            	 url: '{{URL::to('/reports/quizz-performance/')}}/'+$("#chart_filter_by_id").val()+"/"+$("#chart_show_by_id").val()
            }).done(function(msg){
            	label_js=[];
            	percentage_js=[];
            	group_per=[];
                if(msg.length <= 0){
                   alert("No records there in this combination");
                }else{
                	if(msg['quizz_titles'].length >0){
                        wd = msg['quizz_titles'].length*100; 
                        $("#myChart").css('width',wd+'px');
                        $("#myChart").css('height','400px');
                   		reload_chart(msg['quizz_titles'],msg['quizz_score'],msg['ids']);
                   		$("#no_quiz").hide();
                	}else{
                		$("#no_quiz").show();
                        wd = label_js.length*100; 
                        $("#myChart").css('width',wd+'px');
                        $("#myChart").css('height','400px');
                		reload_chart(msg['quizz_titles'],msg['quizz_score'],msg['ids']);
                	}
                	
                }
            }).error(function(msg){
            	$("#no_quiz").show();
            	alert('error while fetch');
            })*/
     	}
     	$("#chart_filter_by_id").change(function(){
     		forajaxcall();
     	});
        $("#chart_show_by_id").change(function(){
            forajaxcall();
        });	
            // var myNewChart = new Chart(ctx).StackedBar(data,options); // ,{barValueSpacing : 30}
            // $(myNewChart.generateLegend()).insertAfter(canvas);
        
	});
</script>
@stop
