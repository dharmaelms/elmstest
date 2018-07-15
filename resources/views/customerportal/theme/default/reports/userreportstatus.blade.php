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
                    <li><a href="{{URL::to('/')}}">Home</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{URL::to('reports')}}">Reports</a><i class="fa fa-angle-right"></i></li>
                    <li>Content feed completion status report</li>
                </ul>
            </div>
			<div class="col-md-12">
		        <div class="box box-red">
		           <div class="box-title">
                    <h3 style="color:black"> Over all {{Lang::get('program.programs')}} ...<?php if(isset($overall_perf) && !empty($overall_perf)){echo $overall_perf."%";}else{echo "0%";}?></h3> 
		                <!-- <h3 style="color:black"> Completion status report % wise</h3>  -->
		                <span id="for_intimate_no"></span>
		                <div class="btn-toolbar clearfix">
                        <div class="col-md-6  pull-right">
                            <form class="form-horizontal" action="">
                                <!-- <div class="form-group" >
                                  <label class="col-sm-4 col-lg-4 control-label" style="padding-right:0;text-align:left"><b>Show on:</b></label>
                                  <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                    <select class="form-control chosen" name="show" data-placeholder="Active" id="chart_show_by_id" tabindex="1">
                                        <option value="all" >All</option>
                                    </select>
                                  </div>
                               </div> -->
                               <div class="form-group" >
                                  <label class="col-sm-4 col-lg-4 control-label" style="padding-right:10px;text-align:right"><b>Filter By:</b></label>
                                  <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                                    <select class="form-control chosen" name="filter" data-placeholder="Active" id="chart_filter_by_id" tabindex="1">
                                        <option value="day" <?php if(isset($time_int) && !empty($time_int) && $time_int=="day"){ echo "selected";}?> >Last 24 Hours</option>
                                        <option value="week" <?php if(isset($time_int) && !empty($time_int) && $time_int=="week"){ echo "selected";}?>>Last 7 Days</option>
                                        <option value="month" <?php if(isset($time_int) && !empty($time_int) && $time_int=="month"){ echo "selected";}?>>Last 30 Days</option>
                                        <option value="year" <?php if(isset($time_int) && !empty($time_int) && $time_int=="year"){ echo "selected";}?>>Last 12 Months</option>
                                        <option value="all" <?php if(isset($time_int) && !empty($time_int) && $time_int=="all"){ echo "selected";}?>>All</option>
                                    </select>
                                  </div>
                               </div>
                            </form>
                        </div>		
                    </div>
		            </div>
		            <div class="box-content">
		            	<div class="user-profile-info">
          				 	<div id = "report_chart">  
                				<canvas id="canvas" height="400px" width="400px"></canvas>
            				</div>
                    <?php 
                            if(isset($tbl) && !empty($tbl)){
                          ?>
                            <div id = "report_tbl">
                                <table>
                                    <tr>
                                        <th> Feed Name</th>
                                        <th> In-Complete Pack</th>
                                        <th> Completed Pack</th>                                    
                                    </tr>

                                    <?php
                                        foreach ($tbl as $key => $value) {
                                            ?>
                                                <tr>
                                                   <td>{{$key}}</td> 
                                                   <td>{{$value['incomplete']}}</td> 
                                                   <td>{{$value['complete']}}</td> 
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
		$lables = array();
    $valuebar = array();
		$userpercentage=array();
		$grouppercentage=array();

		if(isset($performace_graf) && !empty($performace_graf)){
			foreach ($performace_graf as $key => $value) {
				array_push($lables, $key);
                array_push($valuebar,$value);
				/*array_push($userpercentage, $value['by me']); 
				array_push($grouppercentage, $value['cohort']);*/
			}
		}

	?>
	var label_js   = {!!json_encode($lables)!!};//["user"];
    var value_js   = {!!json_encode($valuebar)!!};//["user"];
    var cf_list_js = {!!json_encode($cf_list)!!};
	/*var percentage_js = {!!json_encode($userpercentage)!!};//[60];
	var group_per = {!!json_encode($grouppercentage)!!}*/

	$(document).ready(function(){
     	var ctx = document.getElementById("canvas").getContext("2d");
     	var le =  label_js.length;
        /*if(cf_list_js.length > 0 ){
            $.each(cf_list_js, function(i,l){
                $('#chart_show_by_id').append('<option value="'+l+'" >'+l+'</option>');
            });
        }
*/
     	if(le>0){
        var wd = le*100; 
        $("#canvas").css('width',wd+'px');
        $("#canvas").css('height','400px');
			  reload_chart_byme(label_js,value_js);

     	}else{
     		$('#for_intimate_no').text(" No Content Feed Assgined");
     		$('#for_intimate_no').css('color','red');
     	}
		function reload_chart(var1, var2, var3) {
     		lineChartData = {
           		labels : var1,
           	 	datasets : [
                {
                    fillColor : "rgba(0,0,255, 1)",
                    strokeColor : "rgba(220,220,220,1)",
                    pointColor : "rgba(220,220,220,1)",
                    pointStrokeColor : "#fff",
                    pointHighlightFill : "#fff",
                    pointHighlightStroke : "rgba(220,220,220,1)",
                    data :var2
                },
                {
                    fillColor : "rgba(255,0,0, 1)",
                    strokeColor : "rgba(220,220,220,1)",
                    pointColor : "rgba(220,220,220,1)",
                    pointStrokeColor : "#fff",
                    pointHighlightFill : "#fff",
                    pointHighlightStroke : "rgba(220,220,220,1)",
                    data :var3
                }
            	]
	        }
        	window.myBar = new Chart(ctx).Bar(lineChartData, {
            	responsive: false,
            	showTooltips : false,
                scaleGridLineWidth:2,
                scaleLabel:"<%=value%>%",
                barValueSpacing:5
       		});
     	}

     	function forajaxcall() {
        window.location.href ='{{URL::to('/reports/c-f-completion-status-report/')}}/'+$("#chart_filter_by_id").val()+"/"+'all'+"/"+'false';
     	/*	$.ajax({
            	type: "GET",
            	 url: '{{URL::to('/reports/c-f-completion-status-report/')}}/'+$("#chart_filter_by_id").val()+"/"+$("#chart_show_by_id").val()
            }).done(function(msg){
            	label_js=[];
                value_js=[];
            	percentage_js=[];
            	group_per=[];
                if(msg.length <= 0){
                   alert("No content feed there in this combination");
                }else{
                   $.each( msg, function( i, l ){
                    if($("#chart_filter_by_id").val() == "least" || $("#chart_filter_by_id").val()=="most"){
                        label_js.push(i);
                        percentage_js.push(l);
                        reload_chart_byme(label_js, percentage_js);
                    }else{
                        label_js.push(i);
                        value_js.push(l);
                        $('#canvas').prop("width","200px");
                        reload_chart_byme(label_js,value_js);
                        // reload_chart(label_js,percentage_js, group_per);
                    }
                }); 
                }
            }).error(function(msg){
            	alert('error while fetch');
            })*/
     	}

     	$("#chart_filter_by_id").change(function(){
     		forajaxcall();
     	});
        $("#chart_show_by_id").change(function(){
            forajaxcall();
        });
     	function reload_chart_byme(var1, var2) {
     		lineChartData = {
           		labels : var1,
           	 	datasets : [
                {
                    fillColor : "rgba(0,0,255, 1)",
                    strokeColor : "rgba(220,220,220,1)",
                    pointColor : "rgba(220,220,220,1)",
                    pointStrokeColor : "#fff",
                    pointHighlightFill : "#fff",
                    pointHighlightStroke : "rgba(220,220,220,1)",
                    data :var2
                }
                ]
	        }
        	window.myBar = new Chart(ctx).Bar(lineChartData, {
            	responsive: false,
            	showTooltips : false,
                scaleGridLineWidth:2,
                scaleLabel:"<%=value%>%",
                barValueSpacing:5
       		});
     	}
	});
</script>
@stop
