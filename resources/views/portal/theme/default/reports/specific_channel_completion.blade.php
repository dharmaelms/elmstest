 <div>
  <div class="row myactivity-portlet">
    <div class="col-md-6 col-sm-12 col-xs-12">
      <div class="portlet box blue margin-bottom-20">
        <div class="portlet-title">
          <div class="caption">{{trans('reports.filter')}}</div><div class="tools"><a href="javascript:;" class="expand"></a></div>
        </div>
        <div class="portlet-body black" style="display: none;">
            <form action="">
              <div class="row xs-margin">
                <div class="col-md-12 ">
                  <div class="col-md-12" style="padding-left: 0;">
                    <select class="form-control chosen gallery-cat" id='spc_comp_channel_list' style="width:200px">
                    <option value="0"> {{trans('reports.all_courses')}}</option>
                     @if(count($channnelIdName) >= 1 )
                      @foreach($channnelIdName as $cId => $cName)
                        <option value="{{$cId}}" {{($cId == $channelId) ? 'selected' :'' }}>{{$cName}}</option>
                      @endforeach
                    @endif
                    </select>
                  </div>
                </div>
              </div>  
               <button type="button" class="btn btn-sm btn-success" id='spec_channel_comp_btn'>{{trans('reports.apply_now')}}</button>
              &nbsp;&nbsp;
              <button type="button" class="btn btn-sm btn-primary" title="Export" id='spec_channel_comp_export'><i class="fa fa-share-square-o"></i></button>
            </form>
          </div>
      </div>
    </div>
  </div>
  <div class="row">
    <div class="alert alert-danger" id='no_record' style="display:none">
        {{trans('reports.no_record_found_in_this_combi')}} 
    </div>
    <div id="spec_completion_graph" style="min-width: 310px; max-width: 80%;  min-height: 400px; margin: 0 ;"></div>  
  </div>

</div>
<script type="text/javascript">
var channelId = '{{$channelId}}';

  $("input[name='daterange_radio']").change(function(){
    if($(this).val() == 'all'){
      $('#daterang_div').hide();
      $('#daterang_checkbox').show();
    }else{
      $('#daterang_checkbox').hide();
      $('#daterang_div').show();
    }
  });

  if($("input[name='daterange_radio']:checked").val() == 'all'){
    $('#daterang_div').hide();
    $('#daterang_checkbox').show();
  }else{
    $('#daterang_checkbox').hide();
    $('#daterang_div').show();
  }
$(document).ready(function() {
  var urlchannelcompl = '{{URL::to('/user')}}';
  var urlspecchannelcompl = '{{URL::to('/reports/specific-channel-completion/')}}';
  var urlspecchannelcomplexport = '{{URL::to('/reports/specific-channel-completion-c-s-v/')}}';
  $("#no_record").hide();
  $('#spc_comp_channel_list').select2();
  function specChartCompletion(lables, userData, avgData, barwidh, title) {
      $('#spec_completion_graph').highcharts({
        colors: [ "#DF7401","#58ACFA"],
        chart :{
          type: 'bar'
        },
        title: {
            text : title
          },
        
        xAxis : {
          categories : lables,
          title:
          { 
            text : '{{trans('reports.posts')}}',
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
          text : '{{trans('reports.completion_per')}}',
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
        },],           
      });
  }

  $('#spec_channel_comp_btn').click(function(){
      var channelID = $('#spc_comp_channel_list option:selected' ).val();
      if(channelID <= 0){
        window.location.href = urlchannelcompl+'?chart=completion&channel_id=0#parentHorizontalTab12';
        return false;
      }
      var urlsplcomp  = urlspecchannelcompl+'/'+ channelID;
      ajaxSpecificChannelCompletion(urlsplcomp);
  });

  $('#spec_channel_comp_export').click(function(){
      var channelID = $('#spc_comp_channel_list option:selected' ).val();
      window.location.href = urlspecchannelcomplexport+'/'+ channelID;
  });

  function ajaxSpecificChannelCompletion(path){
    $.ajax({
        type: "GET",
        url : path 
    }).done(function(msg){
      console.log(msg);
        if(msg.length == 0){
          $('#no_record').show();
          $('#spec_completion_graph').hide();
        }else{
            if(msg['post_names'].length >0){
                chartWidht = msg['post_names'].length*70;
                $('#spec_completion_graph').height(chartWidht);
                $('#spec_completion_graph').width('80%');
                $("#no_record").hide();
                specChartCompletion(msg['post_names'], msg['post_completion'], msg['avg_post_completion'], 20, msg['title']);
                $('#spec_completion_graph').show();
            }else{
              $('#no_record').show();
              $('#spec_completion_graph').hide();
            }
        }
        scroll(0,0);
    }).error(function(msg){
        $("#no_record").show();
        $('#spec_completion_graph').hide();
    })
  }
  ajaxSpecificChannelCompletion(urlspecchannelcompl+'/'+channelId);
});
</script>
