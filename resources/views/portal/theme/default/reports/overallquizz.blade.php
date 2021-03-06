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

	<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{URL::asset('portal/theme/default/js/Chart.js')}}"></script>
    <div class="row">
    		<div class="page-bar">
                <ul class="page-breadcrumb">
                    <li><a href="{{URL::to('/')}}">{{Lang::get('reports.home')}}</a><i class="fa fa-angle-right"></i></li>
                    <li><a href="{{URL::to('reports/quizz-over-all-performance')}}">{{Lang::get('reports.reports')}}</a><i class="fa fa-angle-right"></i></li>
                   	<li>{{Lang::get('reports.overall_perf')}}</li>
                </ul>
                <div class="page-breadcrumb pull-right">
                    <div class="box-tool">
                        <a  style="color:white;background-color:orange" href="{{URL::to('reports/quizz-performance')}}">{{Lang::get('reports.quiz_perf')}}</a>
                    </div>
        		</div>
            </div>
			<div class="col-md-12">
		        <div class="box box-red">
		           <div class="box-title">
		                <h3 style="color:black">{{Lang::get('reports.overall_perf_accross_all_quizes')}}  <span id='oa_per'></span></h3> 
		            </div>
		            <div class="box-content">
		            	<div class="user-profile-info">
          				 	<div>
                				<canvas id="canvas" height="300px" width="200"></canvas>
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
			$lables[]     = $quizz_titles;
			$percentage[] = $quizz_score;
		}
	?>
	var label_js={!!json_encode($lables)!!};//["user"];
	var percentage_js={!!json_encode($percentage)!!};//[60];

	$(document).ready(function(){
        percentage_js

     	var ctx = document.getElementById("canvas").getContext("2d");
		reload_chart(label_js,percentage_js);
		function reload_chart(var1, var2) {
     		barChartData = {
           		labels : var1,
           	 	datasets : [
                {
                    fillColor : "rgba(0,255,0, 0.6)",
                    strokeColor : "rgba(220,220,220,1)",
                    pointColor : "rgba(220,220,220,1)",
                    pointStrokeColor : "#fff",
                    pointHighlightFill : "#fff",
                    pointHighlightStroke : "rgba(220,220,220,1)",
                    data :var2
                }
            	]
	        }
        	window.myBar = new Chart(ctx).Bar(barChartData, {
            	responsive: false,
            	showTooltips : false,
            	barStrokeWidth:3,
            	scaleGridLineWidth:2,
            	scaleLabel:"<%=value%>%",
            	barValueSpacing:5
       		});
            $("#oa_per").text("");
            $("#oa_per").text("("+var2[0]+"  %)");
            if(var2[0] >=50){
                $("#oa_per").css('color','green');
            }else{
                $("#oa_per").css('color','red');
            }
     	}

	});

</script>
@stop
