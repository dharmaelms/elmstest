<style type="text/css">
/*#report_chart{
    float: left;
} */ 
#report_tbl{
  border:1px solid #eeeeee;
  width: 100%;
  /*padding: 5px 15px !important;*/
}

#report_tbl th, #report_tbl td, #tbl_quiz th , #tbl_quiz td{
  padding:15px;
}
#report_tbl th, #tbl_quiz th {
  border-bottom: 2px solid #dddddd;
}
#report_tbl td, #tbl_quiz td {
  border-bottom: 1px solid #eeeeee;
}
</style>
<div class="row">
    <div class="col-md-12">
        <div class="box box-red">
            <div class="box-title btm-shadow sm-margin">
                <h3 class="padding-btm-10">  Areas of Improvement </h3> 
                <span id="no_quiz" style="display:none;color:orange">No {{Lang::get('program.program')}} for improve </span>
            </div>
            <div class="row box-content">
                <div class="user-profile-info">
                    <div class="row">
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div class="col-md-12" id="report_chart" style="overflow-x:scroll">
                                <canvas id="canvas" height="300px" ></canvas>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        @if(isset($quizz_titles) && !empty($quizz_titles) && isset($quizz_score))
                        <div class="col-md-12 col-sm-12 col-xs-12">
                            <div id = "report_tbl">
                                <table width="100%">
                                    <tr>
                                        <th> {{Lang::get('program.program')}} Name</th>
                                        <th> Avg Score</th>
                                    </tr>
                                    @foreach ($quizz_titles as $key => $value)
                                    <tr>
                                        <td>{{$value}}</td> 
                                        <td>{{$quizz_score[$key]}}%</td> 
                                    </tr>
                                    @endforeach
                                </table>
                            </div>
                        </div>
                        @endif 
                    </div> 
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
	<?php
		$lables=array();
		$percentage=array();
		if(isset($quizz_titles) && !empty($quizz_titles) && isset($quizz_score)){
			foreach ($quizz_titles as $key => $value) {
				array_push($lables, $value);
				array_push($percentage, $quizz_score[$key]);
			}
		}
	?>
	var label_js={!!json_encode($lables)!!};//["user"];
	var percentage_js={!!json_encode($percentage)!!};//[60];
	var wd =100;
	$(document).ready(function(){

     	var ctx = document.getElementById("canvas").getContext("2d");
     	if(label_js.length < 1){
     		$('#no_quiz').show();
        $("#canvas").css('width',wd+'px');
     	}else{
        wd = label_js.length*100; 
        $("#canvas").css('width',wd+'px');
     	}
      $("#canvas").css('height','400px');
      reload_chart(label_js,percentage_js);
		function reload_chart(var1, var2) {
     		lineChartData = {
           		labels : var1,
           	 	datasets : [
                {
                    fillColor : "rgba(255,100,0, 0.9)",
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
            	scaleLabel:"<%=value%>%",
       		});
     	}

	});

</script>

