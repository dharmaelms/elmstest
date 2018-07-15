<?php
  $typQuiz = Input::get('type', 'all');
  $cirteriaQuiz = Input::get('criteria', 'score');
  $durationQuiz = Input::get('duration', '15');
?>
 <div>
  <div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
      <div class="portlet box blue margin-bottom-20">
        <div class="portlet-title">
          <div class="caption">{{Lang::get('reports.filter')}}</div><div class="tools"><a href="javascript:;" class="expand"></a></div>
        </div>
        <div class="portlet-body black" style="display: none;">
          <form action="">
            <div class="row xs-margin">
              <div class="col-md-12 ">
                <!-- <label class="col-md-3 control-label" style="margin-top: 6px;padding-left:0;">All Channels</label> -->
                <div class="col-md-12" style="padding-left: 0;">
                  <select class="form-control chosen gallery-cat" id="spc_perf_channel_list" style="width:200px">
                  <option value="0"> {{Lang::get('reports.all_courses')}}</option>
                   @if(count($channnelIdName) >= 1)
                      @foreach($channnelIdName as $cId => $cName)
                        <option value="{{$cId}}" {{($cId == $channelId) ? 'selected' :'' }}>{{$cName}}</option>
                      @endforeach
                    @endif
                  </select>
                </div>
              </div>
            </div>
            <div class="row xs-margin">
              <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="form-group" style="margin-bottom: 5px;">
                  <label>{{Lang::get('reports.quiz_type')}}</label>
                  <div class="radio-list">
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios4" style="display:inline-block;"><span class="checked">
                    <input type="radio" {{($typQuiz == 'all') ? 'checked':''}} id='spec_type_mock' value="all" name='quiz_type'>
                    </span></div> All</label>
                     <label class="radio">
                    <div class="radio" id="uniform-optionsRadios4" style="display:inline-block;"><span class="checked">
                    <input type="radio" {{($typQuiz == 'mock') ? 'checked':''}} id='spec_type_mock' value="mock" name='quiz_type'>
                    </span></div> {{Lang::get('reports.quiz')}}</label>
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios5" style="display:inline-block;"><span>
                    <input type="radio" id='spec_type_practice' {{($typQuiz == 'practice') ? 'checked':''}} value='practice' name='quiz_type'>
                    </span></div>{{Lang::get('reports.practice_quiz')}}  </label> 
                  </div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="form-group"  style="margin-bottom: 5px;">
                  <label>{{Lang::get('reports.prformance_criteria')}}</label>
                  <div class="radio-list">
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios6"><span class="checked"><input type="radio"  {{($cirteriaQuiz == 'score') ? 'checked':''}} name="spec_criteria" value="score"></span></div> {{Lang::get('reports.score_wp')}} </label>
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios7"><span>
                    <input type="radio" {{($cirteriaQuiz == 'accuracy') ? 'checked':''}} name="spec_criteria" value="accuracy"></span></div> {{Lang::get('reports.accuracy')}} </label>
                  </div>
                </div>
              </div>
            </div>
             <button type="button" class="btn btn-sm btn-success" id='spec_channel_quiz_btn'>{{Lang::get('reports.apply_now')}}</button>
              &nbsp;&nbsp;
              <button type="button" class="btn btn-sm btn-primary" title="Export" id='spec_channel_quiz_export'><i class="fa fa-share-square-o"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="row">
   <div class="alert alert-danger" id='no_record' style="display:none">
      <!-- <button class="close" data-dismiss="alert">×</button> -->
        {{Lang::get('reports.no_record_found_in_this_combi')}} 
    </div>
    <div id="spec_performance_graph" style="min-width: 310px; max-width: 95%; min-height: 400px; margin: 0 ;"></div>  
  </div>

</div>
<script type="text/javascript">
$(document).ready(function() {
  var urlspecchannelperf = '{{URL::to('/reports/specific-channel-performance-till-date/')}}';
  var urlspecchannelperfexport = '{{URL::to('/reports/specific-channel-performance-c-s-v/')}}';
  var urlchannelperf = '{{URL::to('/user')}}';
  var channelId = '{{$channelId}}';
  $('#spc_perf_channel_list').select2();
  function SpecChartPerformance(lables, userData, avgData, barwidh, ids, colors, myLocalLable , avgLocalLable, yAxisName, title) {
      $('#spec_performance_graph').highcharts({
        colors: colors,
        chart :{
          type: 'bar'
        },
        title: {
            text : title,//'{{Lang::get('reports.indiv_course_perf')}}'
          },
        
        xAxis : {
          categories : lables,
          title:
          { 
            text : '{{Lang::get('reports.quizzes')}}',
            align : 'middle',
           }
        },
        yAxis : {
              max: 100,
              tickInterval: 20,
              lineColor: '#D8D8D8',
              lineWidth: 1,
        title :
         {
          text : yAxisName,//'{{Lang::get('reports.scores')}}',
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
            pointWidth: barwidh,
            cursor: 'pointer',
            point: {
                events: {
                    click: function(e) {
                      var url = '<?php echo URL::to('/'); ?>';
                        id=3;
                        location.href = url+'/assessment/detail/'+ids[this.index];
                      }
              },
            },
          },
        },  
        legend: {
            verticalAlign: 'top',
            y: 10,
            align: 'right'
        },          
        series : [{
          name : avgLocalLable,
          data : avgData,
        },{
          name : myLocalLable,
          data : userData,
        }],           
      });
  }

  $('#spec_channel_quiz_btn').click(function(){
      var channelID = $('#spc_perf_channel_list option:selected' ).val();
      var criteriaSQuiz = $("input[name='spec_criteria']:checked").val();
      var quizType = 'all';
      quizType = $("input[name='quiz_type']:checked").val();
      if(channelID <= 0){
        window.location.href = urlchannelperf+'?chart=performance&channel_id='+channelID;
        return false;
      }
      var urlsperf = urlspecchannelperf+'/'+channelID+'/'+quizType+'/'+criteriaSQuiz;
      ajaxSpecificChannelPerformance(urlsperf);
  });

  $('#spec_channel_quiz_export').click(function(){
      var channelID = $('#spc_perf_channel_list option:selected' ).val();
      var criteriaSQuiz = $("input[name='spec_criteria']:checked").val();
      var quizType = 'all';
      quizType = $("input[name='quiz_type']:checked").val();
      window.location.href = urlspecchannelperfexport+'/'+channelID+'/'+quizType+'/'+criteriaSQuiz;
  });

  function ajaxSpecificChannelPerformance(path){
    $.ajax({
        type: "GET",
        url : path 
    }).done(function(msg){
        if(msg.length <= 0){
          $("#no_record").show();
          $('#spec_performance_graph').hide();
        }else{
            if(msg['quiz_names'].length >0){
                chartWidht = msg['quiz_names'].length *70;
                $('#spec_performance_graph').height(chartWidht);
                $("#no_record").hide();
                $('#spec_performance_graph').show();
                $('#spec_performance_graph').width('80%');
                var qTypeLocal = $("input[name='quiz_type']:checked").val()
                var criteriaSQuizLocal = $("input[name='spec_criteria']:checked").val();
                if(qTypeLocal == 'practice' && criteriaSQuizLocal == 'score'){
                  var colorLocal = ['#FAAC58', '#58FAF4'];
                  var myLocalLable  = '{{Lang::get('reports.my_practice_score')}}';
                  var avgLocalLable  = '{{Lang::get('reports.avg_practice_score')}}';
                  yAxisName = '{{Lang::get('reports.scores')}}';
                }else if(qTypeLocal == 'practice'){
                  var colorLocal = ['#FAAC58', '#58FAF4'];
                  var myLocalLable  = '{{Lang::get('reports.my_practice_accuracy')}}';
                  var avgLocalLable  = '{{Lang::get('reports.avg_practice_accuracy')}}';
                  yAxisName = '{{Lang::get('reports.accuracy_per')}}';
                }else if(qTypeLocal != 'practice' && criteriaSQuizLocal == 'score'){
                  var colorLocal = ["#DF7401","#58ACFA"];
                  var myLocalLable  = '{{Lang::get('reports.my_scores_wp')}}';
                  var avgLocalLable  = '{{Lang::get('reports.avg_scores_wp')}}';
                  yAxisName = '{{Lang::get('reports.scores')}}';
                }else{
                  var colorLocal = ["#DF7401","#58ACFA"];
                  var myLocalLable  = '{{Lang::get('reports.my_accuracy')}}';
                  var avgLocalLable  = '{{Lang::get('reports.avg_accuracy')}}';
                  yAxisName = '{{Lang::get('reports.accuracy_per')}}';
                }
                

                SpecChartPerformance(msg['quiz_names'], msg['quiz_scores'], msg['avg_quiz_scores'], 20, msg['ids'], colorLocal,myLocalLable , avgLocalLable, yAxisName, msg['title']);
                $("#no_quiz").hide();
            }else{
              $("#no_record").show();
              $('#spec_performance_graph').hide();
            }
        }
        scroll(0,0);
    }).error(function(msg){
        $("#no_record").show();
        $('#spec_performance_graph').hide();
    })
  }
  ajaxSpecificChannelPerformance(urlspecchannelperf+'/'+channelId+'/'+'{{$typQuiz}}'+'/'+'{{$cirteriaQuiz}}');
});
</script>