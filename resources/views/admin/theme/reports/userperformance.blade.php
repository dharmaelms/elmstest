@section('content')

    <script type="text/javascript" src="{{URL::to('admin/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>
    <div class="row custom-box">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                    <h3 style="color:white">{{$title}}</h3>

                <div  class="btn-toolbar pull-right clearfix"> 
                    <a href="{{URL::to('cp/reports/user-reports')}}" title="{{trans('admin/reports.list_user')}}" class="show-tooltip btn btn-primary fa fa-angle-left">{{trans('admin/reports.back_user_list')}}</a>
                </div>

                </div>
                <div class="box-content">
                    <form class="form-horizontal" name="myForm" action="" >
                        <div class="form-group">
                            <label class="col-sm-2 col-lg-1 control-label" style="padding-right:0;text-align:right"><b>{{trans('admin/reports.find')}} : &nbsp;</b></label>
                            <div class="col-sm-2 col-lg-2 " style="padding-left:0;">
                                <select id="selected_feed" class="form-control chosen">
                                <option>{{trans('admin/reports.search_here')}}</option>
                                    @foreach ($contentfeeds as $feed)
                                        <option value="{{ $feed['channel_id'] }}">{{str_limit(trim($feed['channel_name']), 75)}}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-sm-2 col-lg-1 pull-right" style="padding-left:0;">
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
                                <button class="close" >×</button>
                                <p>{{trans('admin/reports.user_channel_perf_disc')}}</p>
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
                                <button class="close" >×</button>
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
        <div class="col-md-6 col-md-offset-3">
            <div class="box">
                <div class="box-content">
                    <table class="table fill-head table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('admin/reports.course_name')}}</th>
                                <th align="center">{{$user_name}}'s {{trans('admin/reports.scores')}}</th>
                                <th>{{trans('admin/reports.avg_scores')}}</th>
                            </tr>
                        </thead>
                        <tbody id="data_tbl_id">
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

<script>
    var user_id_js = {{$user_id}};
    var user_name = "{{$user_name}}";
    var error=0;
    var current=0;
    $('#no_record').hide();
    function renderChart (data, xaxis, id_channel_var, avg_data, user_name) {
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
                    text : '{{trans('admin/reports.course')}}',
                    align : 'middle',
                },
                labels: {
                    formatter: function () {
                        var text = this.value,
                            formatted = text.length > 15 ? text.substring(0, 15) + '...' : text;

                        return '<div class="js-ellipse" style="width:150px; overflow:hidden" title="' + text + '">' + formatted + '</div>';
                    },
                    style: {
                        width: '150px'
                    },
                    useHTML: true
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
                                      location.href = url+'/cp/reports/individual-channel-user-performance/'+id_channel_var[this.index]+'/'+user_id_js
                            }
                        }
                    },  
                },
            },
            series : [{
                    name : user_name +"'s{{trans('admin/reports.perf')}}",
                    data : data,
                }, {
                    name : '{{trans('admin/reports.avg_perf')}}',
                    data : avg_data,
                }
            ],           
        });
    }
    $("#selected_feed").change(function () {
        var url = '{{URL::to('/')}}';
        var FeedSelected = $('#selected_feed').find('option:selected').val();
        location.href = url+'/cp/reports/individual-channel-user-performance/'+FeedSelected+'/'+user_id_js;
    });
    $('#exp_report').click(function(){
        var url = '{{URL::to('/')}}';
        location.href = url+'/cp/reports/csv-user-performance-report/'+user_id_js;
    });
    $('#info_report_btn').click(function(){
        $('#info_report').show();
    });
    $('.close').click(function(){
        $(this).parent().hide()
    });
    $('#prev').click(function()
    {
        if(current <= 0)
        {
            current = 0;
        }else
        {
            current--;
        }
        ajaxCallFunc();
    });
    
    $('#last').click(function()
    {
        current++;
        ajaxCallFunc();
    });

    function ajaxCallFunc(){
        var url='{{URL::to('/')}}';
        var id = $(this).attr('id'); 
        var no_set = current;
        var limit = 10;
        $.ajax({
            type:'GET',
            url : url+'/cp/reports/ajax-user-channel-performance/'+no_set+'/'+user_id_js
        })
        .done(function(response){
            var data = response.data;
            var avg_data = response.avg_data;
            var xaxis = response.xaxis;
            var id_channel =response.id;
            var html_temp = "";
            var url_quiz = url+'/cp/reports/individual-channel-user-performance/';
            if(data.length > 0){
                $('#no_record').hide();
                if($.isArray(data)){
                    $.each(data, function(key, ele){
                        html_temp+="<tr><td><a href='"+url_quiz+id_channel[key]+"/"+user_id_js+"/"+xaxis[key]+"'>";
                        html_temp+=xaxis[key]+"</a></td><td>"+ele+"</td><td>"+avg_data[key]+"</td>></tr>";
                    });
                }
                if(html_temp != ""){
                    $('#data_tbl_id').html(html_temp);
                }
                renderChart(data, xaxis, id_channel, avg_data, user_name);
            }else{
                $('#no_record').show();
                current--;
            }
        });
    }
    ajaxCallFunc();
</script>
@stop

