@section('content')
    <script type="text/javascript" src="{{URL::to('admin/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>    <div class="row custom-box">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    @include('admin.theme.reports.menu', ['selector' => 'channelperformance', 'general' => $general ])
                    <div class="" style="text-align:left; padding-bottom:10px;padding-left:260px;">
                        <a href="{{URL::to('cp/reports/admin-reports')}}" title="{{ trans('admin/reports.channel_quiz_perf') }}" class="show-tooltip btn btn-primary channelperformance">{{trans('admin/reports.channel_quiz')}}</a>
                        <a href="{{URL::to('cp/reports/direct-quiz-user')}}" title="{{trans('admin/reports.direct_quiz')}}" class="show-tooltip btn btn-gray directuserquiz" >{{trans('admin/reports.direct_quiz')}}</a>
                    </div>
                     @include('admin.theme.reports.content_report_form', ['channel_name' => $name])
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info" id = "info_report" style="display:none">
                                <button class="close">×</button>
                                <p>{{trans('admin/reports.ind_channel_perf_disc')}}</p>
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
    var channal_id_var = "{{$channel_id}}";
    var name = "{{$name}}";
    $('#no_record').hide();
    $("#selected_feed").change(function () {
        if($('#selected_feed').find('option:selected').val() == 0){
            var url = '{{URL::to('/')}}';
            window.location.replace(url+'/cp/reports');
        }else{
            current = 0;
            channal_id_var = $('#selected_feed').find('option:selected').val();
            ajaxCallFunc();
        }
    });
    $('#exp_report').click(function(){
        var url = '{{URL::to('/')}}';
        var from = $('#from').val();
        var to = $('#to').val();
        var date_range = $('#cus_range').val();
        location.href = url+'/cp/reports/csv-single-performance-report/'+channal_id_var+'/'+name;
    });
    $('#info_report_btn').click(function(){
        $('#info_report').show();
    });
    $('.close').click(function(){
        $(this).parent().hide()
    });
    var current = 0;
    function visitorData (data,title,xaxis,value, id_channel_var){
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
                                        id=3;
                                      location.href = url+'/cp/reports/quiz-performance-by-question/'+id_channel_var[this.index]+'/'+channal_id_var 
                                    }
                                    }
                                },  
                            },
                 
                    },
                        
            series : [{
                name : '{{trans('admin/reports.quiz_perf')}}',
                data : data,
            }],           
        });
    }
    </script>

<script>
    $("#sub").click(function(){
        current = 0;
        ajaxCallFunc();
    });

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
        var prev = current+1;
        current++;
        // alert(prev); 
        ajaxCallFunc();
    });

    function ajaxCallFunc(){
        var url='{{URL::to('/')}}';
        var id = $(this).attr('id'); 
        var no_set = current;
        $.ajax({
            type:'GET',
            url : url+'/cp/reports/ajax-individual-channel-performance/'+no_set+'/'+channal_id_var
        })
        .done(function(response){
            $('#range').html("");
            $('#range1').html("");
            var data_flter = new Array();
            var title_flter = new Array();
            var data = response.data;
            var title = response.title;
            var xaxis = response.xaxis;
            var value = response.avg;
            var id_channel =response.id;
            var html_temp = "";
            if(data.length > 0){
                if($.isArray(data)){
                    $('#no_record').hide();
                    $('#prev').show();
                    $('#last').show();
                    $.each(data, function(key, ele){
                        var url_quiz = url+'/cp/reports/quiz-performance-by-question';
                        data_flter.push(ele);
                        title_flter.push(xaxis[key]);
                        url_quiz += '/'+id_channel[key]+'/'+channal_id_var;
                        html_temp+="<tr><td><a href='"+url_quiz+"'>"+xaxis[key]+"</a></td><td>"+ele+"</td></tr>";  
                    });
                }
                if(html_temp != ""){
                    $('#data_tbl_id').html(html_temp);
                }
                $('#container').show();
                $('#data_tbl_id').show();
                visitorData(data_flter, title, title_flter, value, id_channel);   
            }else{
                $('#no_record').show();
                current--;
                $('#container').hide();
                $('#data_tbl_id').hide();
                $('#prev').hide();
                $('#last').hide();
            }
            
        
        });
    }
ajaxCallFunc();

</script>
@stop
