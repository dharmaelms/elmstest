 <div>
  <div class="row myactivity-portlet">
    <div class="col-md-6 col-sm-12 col-xs-12">
      <div class="portlet box blue-hoki margin-bottom-20">
        <div class="portlet-title">
          <div class="caption"><i class="fa fa-filter font-20 margin-top-0
          "></i> {{trans('reports.filter')}}</div><div class="tools"><a href="javascript:;" class="expand"></a></div>
        </div>
        <div class="portlet-body black" style="display: none;">
            <form action="">
              <div class="row xs-margin">
                <div class="col-md-12 ">
                  <div class="col-md-12" style="padding-left: 0;">
                    <select class="form-control chosen gallery-cat " id='comp_channel_list_t' style="width:200px">
                    <option value="0"> {{trans('reports.all_courses')}} </option>
                    @if(count($channnelIdName) >= 1)
                      @foreach($channnelIdName as $cId => $cName)
                        <option value="{{$cId}}">{{$cName}}</option>
                      @endforeach
                    @endif
                    </select>
                  </div>
                </div>
              </div>
               <button type="button" class="btn btn-sm btn-success" id='channel_completion_btn'> <i class="fa fa-arrow-up" aria-hidden="true"></i> {{trans('reports.apply_now')}}</button>
              &nbsp;&nbsp;
              <button type="button" class="btn btn-sm btn-primary" title="Export" id='channel_completion_export'><i class="fa fa-external-link-square"></i></button>
            </form>
          </div>
      </div>
    </div>
  </div>
  <div class="row">
     <div class="alert alert-danger" id='no_record_cc' style="display:none">
      <!-- <button class="close" data-dismiss="alert">×</button> -->
        {{trans('reports.no_record_found_in_this_combi')}} 
    </div>
    <div id="completion_graph" style="width: 80%; min-height: 400px; margin: 0 ;"></div>  
  </div>

</div>

<script type="text/javascript">
  var chartComp = '{{$chart}}';
  var urlchannelcompl = '{{URL::to('/reports/channel-completion/')}}';
  var urlchannelcomplexport = '{{URL::to('/reports/channel-completion-c-s-v/')}}';
  var urlspecchannelcomp = '{{URL::to('/user')}}';
  var urlspecchannelcomplexport = '{{URL::to('/reports/specific-channel-completion-c-s-v/')}}';
  $("#no_record_cc").hide();
  function chartCompletion(lables, userData, avgData, barwidh, ids) {
      $('#completion_graph').highcharts({
        colors: [ "#DF7401","#58ACFA"],
        chart :{
          type: 'bar'
        },
        title: {
            text : ' {{trans('reports.course')}} {{trans('reports.compl')}}'
          },
        xAxis : {
          categories : lables,
          title:
          { 
            text : '{{trans("reports.c_channels")}}',
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
          text : '{{trans("reports.completion_per")}}',
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
                                var url = '{{URL::to('/')}}';
                                  id=3;
                                  location.href = url+'/user?chart=completion&channel_id='+ids[this.index]+'#parentHorizontalTab12';
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
        series : [{
          name : '{{trans('reports.avg_compl_wp')}}',
          data : avgData,
        },{
          name : '{{trans('reports.my_compl_wp')}}',
          data : userData,
        }],           
      });
  }
  ajaxSpecificChannelCompletion = function(path){
    $.ajax({
        type: "GET",
        url : path 
    }).done(function(msg){
        if(msg.length <= 0){
          $('#no_record_cc').show();
          $('#completion_graph').hide();
        }else{
            if(msg['channel_name'].length >0){
                chartWidht = msg['channel_name'].length *70;
                $('#completion_graph').height(chartWidht);
                $('#completion_graph').width('80%');
                $("#no_record_cc").hide();
                $('#completion_graph').show();
                chartCompletion(msg['channel_name'], msg['channel_completion_compl'], msg['avg_channel_completion_compl'], 20, msg['id']);
            }else{
              $('#no_record_cc').show();
              $('#completion_graph').hide();
            }
        }
    }).error(function(msg){
        $('#no_record_cc').show();
        $('#completion_graph').hide();
    })
  }
$(document).ready(function() {
  $('#comp_channel_list_t').select2();
  $('#channel_completion_btn').click(function(){
      var channelID = $('#comp_channel_list_t option:selected' ).val();
      if(channelID > 0){
        window.location.href = urlspecchannelcomp+'?chart=completion&channel_id='+channelID+'#parentHorizontalTab12';
      }
      var urlcomp  = urlchannelcompl+'/'+ channelID;
      ajaxSpecificChannelCompletion(urlcomp);
  });

  $('#channel_completion_export').click(function(){
    var channelID = $('#comp_channel_list_t option:selected' ).val();
    if(channelID > 0){
      window.location.href = urlspecchannelcomplexport+'/'+channelID;
      return false;
    }
    window.location.href = urlchannelcomplexport+'/'+ channelID;
  });

  if(chartComp=='completion'){
    var channelID = $('#comp_channel_list_t option:selected' ).val();
    var timeCPeriod = $("input[name='daterange_radio']:checked").val();
    var cusDateRange = $('#cus_range').val();
    var urlcomp  = urlchannelcompl+'/'+ channelID;
    ajaxSpecificChannelCompletion(urlcomp);
  }
  
});

</script>