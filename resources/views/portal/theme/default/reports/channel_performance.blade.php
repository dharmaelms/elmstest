
 <div>
  <div class="row">
    <div class="col-md-6 col-sm-6 col-xs-12">
      <div class="portlet box blue-hoki margin-bottom-20">
        <div class="portlet-title">
          <div class="caption"><i class="fa fa-filter font-20 margin-top-0
          "></i> {{Lang::get('reports.filter')}}</div><div class="tools"><a href="javascript:;" class="expand"></a></div>
        </div>
        <div class="portlet-body black" style="display: none;">
          <form action="">
            <div class="row xs-margin">
              <div class="col-md-12 ">
                <div class="col-md-12" style="padding-left: 0;" id='comp_channel_list_div'>
                  <select class="form-control chosen gallery-cat" id='comp_channel_list' style="width:200px;background-color: #67809f;">
                  <option value="0">{{Lang::get('reports.all_courses')}}</option>
                    @if(count($channnelIdName) >= 1)
                      @foreach($channnelIdName as $cId => $cName)
                        <option value="{{$cId}}">{{$cName}}</option>
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
                    <input type="radio" checked="" id='quiz_type_all' name='quiz_type' value="all"></span></div>  {{Lang::get('reports.all')}}</label>
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios4" style="display:inline-block;"><span class="checked">
                    <input type="radio" id='quiz_type_mock' name='quiz_type' value="mock"></span></div> {{Lang::get('reports.quiz')}}</label>
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios5" style="display:inline-block;"><span><input type="radio" id='quiz_type_practice' name='quiz_type' value="practice"></span></div> {{Lang::get('reports.practice_quiz')}} </label>
                  </div>
                </div>
              </div>
              <div class="col-md-4 col-sm-6 col-xs-12">
                <div class="form-group"  style="margin-bottom: 5px;">
                  <label>{{Lang::get('reports.prformance_criteria')}}</label>
                  <div class="radio-list">
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios6"><span class="checked">
                    <input type="radio"  checked="" name='quiz_criteria' value="score"></span></div> {{Lang::get('reports.score_wp')}}  </label>
                    <label class="radio">
                    <div class="radio" id="uniform-optionsRadios7"><span>
                    <input type="radio" name='quiz_criteria' value="accuracy"></span></div> {{Lang::get('reports.accuracy')}} </label>
                  </div>
                </div>
              </div>
              <div class="col-md-4 col-sm-12 col-xs-12">
                <label class="col-md-12 control-label" style="padding-left:0;">{{Lang::get('reports.source_quiz')}}</label>
                <div class="col-md-12" style="padding-left: 0;">
                  <select class="form-control chosen gallery-cat" id='source_quiz'>
                    <option value="channel" selected> {{Lang::get('reports.courses')}}</option>
                    <option value="direct" > {{Lang::get('reports.direct')}}</option>
                  </select>
                </div>
              </div>
            </div>
             <button type="button" class="btn btn-sm btn-success" id='channel_quiz_btn'><i class="fa fa-arrow-up" aria-hidden="true"></i> {{Lang::get('reports.apply_now')}}</button>
              &nbsp;&nbsp;
              <button type="button" class="btn btn-sm btn-primary" title="Export" id='channe_performance_export'><i class="fa fa-external-link-square"></i></button>
          </form>
        </div>
      </div>
    </div>
  </div>
  <div class="row"  >
     <div class="alert alert-danger" id='no_record_cp' style="display:none">
      <!-- <button class="close" data-dismiss="alert">×</button> -->
        {{Lang::get('reports.no_record_found_in_this_combi')}} 
    </div>
    <div id="performance_graph" style="min-width: 300px; max-width: 800px; min-height: 400px;margin: 0;"></div>  
  </div >

</div>
<script type="text/javascript">
$(document).ready(function() {
  var chartPerf = '{{$chart}}';
  var urlchannelperf = '{{URL::to('/reports/channel-performance-till-date/')}}';
  var urlchannelperfexport = '{{URL::to('/reports/channel-performance-c-s-v/')}}';
  var urldirectperf = '{{URL::to('/reports/direct-quiz-performance-till-date/')}}';
  var urldirectperfexport = '{{URL::to('/reports/direct-quiz-performance-c-s-v/')}}';
  var urlspecchannelperf = '{{URL::to('/user')}}';
  $('#comp_channel_list').select2();
  $("#no_record_cp").hide();
  function chartPerformance(lables, userData, avgData, name, barwidh, ids, xAxisName, myScoreLale, avgScoreLable,ajazCriteriaQuiz ,ajaxduration ,ajaxQuizType, colorLocal , yAxisName) {
      $('#performance_graph').highcharts({
        colors: colorLocal,//["#DF7401", "#58ACFA"],
        chart :{
          type: 'bar'
        },
        title: {
          text : '{{Lang::get('reports.course_perf')}}'
          },
        xAxis : {
          categories : lables,
          title:
          { 
            text : xAxisName,
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
          text : yAxisName, //'{{Lang::get('reports.scores')}}',
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
            pointPadding: 0.2, 
            borderWidth: 0,
            pointWidth: barwidh,
                  cursor: 'pointer',
                      point: {
                          events: {
                              click: function(e) {
                                var url = '<?php echo URL::to('/'); ?>';
                                  id=3;
                                  location.href = url+'/user?chart=performance&channel_id='+ids[this.index]+'&type='+ajaxQuizType+'&criteria='+ajazCriteriaQuiz;
                                }
                        }
                    },  
              },
          },
        legend: {
            verticalAlign: 'top',
            y: 10,
            align: 'right'
        },
        scrollbar: {
            enabled: true
        },
        series : [{
          name : myScoreLale,
          data : avgData,
        },{
          name : avgScoreLable,
          data : userData,
        }],           
      });
  }

  function chartFourPerformance(lables, userData, avgData, userPData, avgPData, name, barwidh, ids, xAxisName,ajazCriteriaQuiz ,ajaxduration ,ajaxQuizType, myScoreL ,avgScoreL ,mypracticeSL ,avgpracticeSL, yAxisName) {
      $('#performance_graph').highcharts({
        colors: ['#FAAC58', '#58FAF4',"#DF7401", "#58ACFA"],
        chart :{
          type: 'bar'
        },
        title: {
            text : '{{Lang::get('reports.course_perf')}}'
          },
        
        xAxis : {
          categories : lables,
          title:
          { 
            text : xAxisName,
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
            pointPadding: 0.2, 
            borderWidth: 0,
            pointWidth: barwidh,
                  cursor: 'pointer',
                      point: {
                          events: {
                              click: function(e) {
                                var url = '<?php echo URL::to('/'); ?>';
                                  id=3;
                                  location.href = url+'/user?chart=performance&channel_id='+ids[this.index]+'&type='+ajaxQuizType+'&criteria='+ajazCriteriaQuiz;
                                }
                                }
                            },  
                      },
                  },
        legend: {
            verticalAlign: 'top',
            y: 10,
            align: 'right'
        },
        scrollbar: {
            enabled: true
        },
        series : [{
          name : avgpracticeSL, //'{{Lang::get('reports.avg_practice_score')}}',
          data : avgPData,
        },{
          name : mypracticeSL,//'{{Lang::get('reports.my_practice_score')}}',
          data : userPData,
        }, {
          name : avgScoreL, //'{{Lang::get('reports.avg_scores_wp')}}',
          data : avgData,
        },{
          name : myScoreL, //'{{Lang::get('reports.my_scores_wp')}}',
          data : userData,
        }],           
      });
  }

  function chartDPerformance(lables, userData, avgData, barwidh, ids,   myScoreL, myAScoreL, colorLocal, yAxisName) {
      $('#performance_graph').highcharts({
        colors:colorLocal,// ["#DF7401", "#58ACFA"],
        chart :{
          type: 'bar'
        },
        title: {
            text : '{{Lang::get('reports.direct_quiz')}} {{Lang::get('reports.performance_tab')}}'
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
            pointPadding: 0.2, 
            borderWidth: 0,
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
        scrollbar: {
            enabled: true
        },
        series : [{
          name : myAScoreL,//'{{Lang::get('reports.avg_scores_wp')}}',
          data : avgData,
        },{
          name : myScoreL ,//'{{Lang::get('reports.my_scores_wp')}}',
          data : userData,
        }],           
      });
  }

  $('#channel_quiz_btn').click(function() {
      var criteriaQuiz = $("input[name='quiz_criteria']:checked").val();
      var sourceQuiz = $('#source_quiz option:selected' ).val();
      var quizType = 'all';
      quizType = $("input[name='quiz_type']:checked").val();
      if(sourceQuiz == 'channel'){
        var channelID = $('#comp_channel_list option:selected' ).val();
        if(channelID > 0){
          window.location.href = urlspecchannelperf+'?chart=performance&channel_id='+channelID+'&type='+quizType+'&criteria='+criteriaQuiz;
        }
        urlperf = urlchannelperf+'/'+channelID+'/'+quizType+'/'+criteriaQuiz;
        ajaxCPerformance(urlperf);
      }else{
        urldperf = urldirectperf+'/'+quizType+'/'+criteriaQuiz;
        ajaxDirectQuizPerformance(urldperf);
      }
  });

  $('#channe_performance_export').click(function() {
      var criteriaQuiz = $("input[name='quiz_criteria']:checked").val();
      var sourceQuiz = $('#source_quiz option:selected' ).val();
      var quizType = 'all';
      quizType = $("input[name='quiz_type']:checked").val();

      if(sourceQuiz == 'channel'){
        var channelID = $('#comp_channel_list option:selected' ).val();
        if(channelID > 0){
          window.location.href = urlspecchannelperf+'?chart=performance&channel_id='+channelID;
        }
        window.location.href = urlchannelperfexport+'/'+channelID+'/'+quizType+'/'+criteriaQuiz;
      }else{
        window.location.href = urldirectperfexport+'/'+quizType+'/'+criteriaQuiz;
      }
      
  });

  var checkSourceQuiz = function(){
    if($('#source_quiz option:selected').val() == 'channel') {
      $('#comp_channel_list_div').show();
    }else{
      $('#comp_channel_list_div').hide();
    }
  }

  $('#source_quiz').change(function() {
    checkSourceQuiz();
  });

  function ajaxDirectQuizPerformance(path) {
    $.ajax({
        type: "GET",
        url : path 
    }).done(function(msg){
        if(msg.length <= 0){
          $("#no_record_cp").show();
          $('#performance_graph').hide();
        }else{
            if(msg['quiz_names'].length > 0){
              var chartWidht = msg['quiz_names'].length*70;
              $('#performance_graph').height(chartWidht);
              $('#performance_graph').width('80%');
              $('#performance_graph').show();
              $("#no_record_cp").hide();
              var myScore = [];
              var avgScore = [];
              var myScoreL = 'My quiz score';
              var myAScoreL = 'Avg. quiz score';
              if(msg['avg_quiz_scores'].length > 0){
                myScore = msg['quiz_score'];
                avgScore = msg['avg_quiz_scores'];
                myScoreL = 'My quiz score';
                myAScoreL = 'Avg. quiz score';
              }else if( msg['avg_practice_scores'].length > 0){
                myScore = msg['practice_score'];
                avgScore = msg['avg_practice_scores'];
                myScoreL = 'My practice score';
                myAScoreL = 'Avg. practice score';
              }
              var qTypeLocal = $("input[name='quiz_type']:checked").val()
              var criteriaSQuizLocal = $("input[name='quiz_criteria']:checked").val();
              if(qTypeLocal == 'practice' && criteriaSQuizLocal == 'score'){
                var colorLocal = ['#FAAC58', '#58FAF4'];
                myScoreL  = '{{Lang::get('reports.my_practice_score')}}';
                myAScoreL  = '{{Lang::get('reports.avg_practice_score')}}';
                yAxisName = '{{Lang::get('reports.scores')}}';
              }else if(qTypeLocal == 'practice'){
                var colorLocal = ['#FAAC58', '#58FAF4'];
                myScoreL  = '{{Lang::get('reports.my_practice_accuracy')}}';
                myAScoreL  = '{{Lang::get('reports.avg_practice_accuracy')}}';
                yAxisName = '{{Lang::get('reports.accuracy_per')}}';
              }else if(qTypeLocal != 'practice' && criteriaSQuizLocal == 'score'){
                var colorLocal = ["#DF7401","#58ACFA"];
                myScoreL  = '{{Lang::get('reports.my_scores_wp')}}';
                myAScoreL  = '{{Lang::get('reports.avg_scores_wp')}}';
                yAxisName = '{{Lang::get('reports.scores')}}';
              }else{
                var colorLocal = ["#DF7401","#58ACFA"];
                myScoreL  = '{{Lang::get('reports.my_accuracy')}}';
                myAScoreL  = '{{Lang::get('reports.avg_accuracy')}}';
                yAxisName = '{{Lang::get('reports.accuracy_per')}}';
              }
              
              
              chartDPerformance(msg['quiz_names'], msg['quiz_scores'], msg['avg_quiz_scores'], 20, msg['ids'],  myScoreL, myAScoreL, colorLocal, yAxisName);
                $("#no_quiz").hide();
            }else{
              $("#no_record_cp").show();
              $('#performance_graph').hide();
            }
        }
        scroll(0,0);
    }).error(function(msg){
        $("#no_record_cp").show();
        $('#performance_graph').hide();
    })
  }

  ajaxCPerformance = function(path){
    $.ajax({
        type: "GET",
        url : path 
    }).done(function(msg){
        if(msg.length <= 0){
          $("#no_record_cp").show();
          $('#performance_graph').hide();
        }else{
            if(msg['channel_name'].length >0){
                ajazCriteriaQuiz = $("input[name='quiz_criteria']:checked").val();
                ajaxduration = $('#quiz_timepriod option:selected' ).val();
                ajaxQuizType = $("input[name='quiz_type']:checked").val();
                chartWidht = msg['channel_name'].length *70;
                $('#performance_graph').height(chartWidht);
                $('#performance_graph').width('80%');
                $('#performance_graph').show();
                $("#no_record_cp").hide();
                if((msg['avg_quiz_scores'].length > 0 
                                      && msg['avg_practice_scores'].length > 0) || ajaxQuizType=='all'){
                  var criteriaSQuizLocal = $("input[name='quiz_criteria']:checked").val();
                  if(criteriaSQuizLocal == 'score'){
                    myScoreL  = '{{Lang::get('reports.my_scores_wp')}}';
                    avgScoreL  = '{{Lang::get('reports.avg_scores_wp')}}';
                    mypracticeSL  = '{{Lang::get('reports.my_practice_score')}}';
                    avgpracticeSL  = '{{Lang::get('reports.avg_practice_score')}}';
                    yAxisName = '{{Lang::get('reports.scores')}}';
                  }else{
                    myScoreL  = '{{Lang::get('reports.my_accuracy')}}';
                    avgScoreL  = '{{Lang::get('reports.avg_accuracy')}}';
                    mypracticeSL  = '{{Lang::get('reports.my_practice_accuracy')}}';
                    avgpracticeSL  = '{{Lang::get('reports.avg_practice_accuracy')}}';
                    yAxisName = '{{Lang::get('reports.accuracy_per')}}';
                  }

                  
                  
                  chartFourPerformance(msg['channel_name'], msg['quiz_score'], msg['avg_quiz_scores'], msg['practice_score'], msg['avg_practice_scores'], 'Course', 10, msg['ids'], '{{Lang::get("reports.c_channels")}}',ajazCriteriaQuiz ,ajaxduration ,ajaxQuizType, myScoreL ,avgScoreL ,mypracticeSL ,avgpracticeSL, yAxisName);
                }else if((msg['avg_quiz_scores'].length > 0 
                                      ^ msg['avg_practice_scores'].length > 0) || ajaxQuizType != 'all'){
                  var myScore = [];
                  var avgScore = [];
                  var myScoreL = 'My quiz score';
                  var myAScoreL = 'Avg. quiz score';
                  if(msg['avg_quiz_scores'].length > 0){
                    myScore = msg['quiz_score'];
                    avgScore = msg['avg_quiz_scores'];
                    myScoreL = 'My quiz score';
                    myAScoreL = 'Avg. quiz score';

                  }else if( msg['avg_practice_scores'].length > 0){
                    myScore = msg['practice_score'];
                    avgScore = msg['avg_practice_scores'];
                    myScoreL = 'My practice score';
                    myAScoreL = 'Avg. practice score';
                  }
                  var qTypeLocal = $("input[name='quiz_type']:checked").val()
                  var criteriaSQuizLocal = $("input[name='quiz_criteria']:checked").val();
                  if(qTypeLocal == 'practice' && criteriaSQuizLocal == 'score'){
                    var colorLocal = ['#FAAC58', '#58FAF4'];
                    myScoreL  = '{{Lang::get('reports.my_practice_score')}}';
                    myAScoreL  = '{{Lang::get('reports.avg_practice_score')}}';
                    yAxisName = '{{Lang::get('reports.scores')}}';
                  }else if(qTypeLocal == 'practice'){
                    var colorLocal = ['#FAAC58', '#58FAF4'];
                    myScoreL  = '{{Lang::get('reports.my_practice_accuracy')}}';
                    myAScoreL  = '{{Lang::get('reports.avg_practice_accuracy')}}';
                    yAxisName = '{{Lang::get('reports.accuracy_per')}}';
                  }else if(qTypeLocal != 'practice' && criteriaSQuizLocal == 'score'){
                    var colorLocal = ["#DF7401","#58ACFA"];
                    myScoreL  = '{{Lang::get('reports.my_scores_wp')}}';
                    myAScoreL  = '{{Lang::get('reports.avg_scores_wp')}}';
                    yAxisName = '{{Lang::get('reports.scores')}}';
                  }else{
                    var colorLocal = ["#DF7401","#58ACFA"];
                    myScoreL  = '{{Lang::get('reports.my_accuracy')}}';
                    myAScoreL  = '{{Lang::get('reports.avg_accuracy')}}';
                    yAxisName = '{{Lang::get('reports.accuracy_per')}}';
                  }
                  chartPerformance(msg['channel_name'], myScore, avgScore, 'Course', 20, msg['ids'], '{{Lang::get("reports.c_channels")}}', myScoreL, myAScoreL,ajazCriteriaQuiz ,ajaxduration ,ajaxQuizType, colorLocal, yAxisName);
                }else{
                  $("#no_record_cp").show();
                  $('#performance_graph').hide();
                }
            }else{
              $("#no_record_cp").show();
              $('#performance_graph').hide();
            }
        }
        scroll(0,0);
    }).error(function(msg){
        $("#no_record_cp").show();
        $('#performance_graph').hide();
    })
  }
  if(chartPerf=='performance'){
    ajaxCPerformance(urlchannelperf);
    checkSourceQuiz();
  }
  
});
</script>