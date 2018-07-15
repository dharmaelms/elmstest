@section('content')
    <script type="text/javascript" src="{{URL::to('admin/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>
    <div class="row custom-box">
        <div class="col-md-12">
            <div class="box">           
                <div class="box-content">
                    @include('admin.theme.reports.menu', ['selector' => 'channelcontent' , 'general' => $general])
                    @include('admin.theme.reports.content_report_form')
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info" id = "info_report" style="display:none">
                                <button class="close">×</button>
                                <p>{{trans('admin/reports.channel_compl_disc')}}</p>
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

    <!-- 1st table -->
   </div>
    <div class="row">
        <div class="col-md-6 col-md-offset-3 ">
            <div class="box">
                <div class="box-content">
                    <table class="table fill-head table-striped">
                        <thead>
                            <tr>
                                <th>{{trans('admin/reports.course_name')}}</th>
                                <th>{{trans('admin/reports.completion')}}</th>
                                <th>{{trans('admin/reports.no_of_post')}}</th>
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
    $('#no_record').hide();
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
                    text : '{{trans('admin/reports.course')}}',
                    align : 'middle',
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
                    text : '{{trans('admin/reports.completion')}}',
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
                                      location.href = url+'/cp/reports/individual-channel-completion/'+id_channel_var[this.index]
                                    }
                                    }
                                },  
                            },
                 
                    },
                        
            series : [{
                name : '{{trans('admin/reports.compl')}}',
                data : data,
            }],           
        });
    }
</script>

<script>
    var current=0;
    $("#selected_feed").change(function () {
        var url = '{{URL::to('/')}}';
        var FeedSelected = $('#selected_feed').find('option:selected').val();
          location.href = url+'/cp/reports/individual-channel-completion/'+FeedSelected;
    });

    $('#exp_report').click(function(){
        var url = '{{URL::to('/')}}';
        location.href = url+'/cp/reports/csv-completion-report';
    });

    $('#info_report_btn').click(function(){
        $('#info_report').show();
    });

    $('.close').click(function(){
        $(this).parent().hide()
    });

    $("#sub").click(function(){
        current = 0;
        ajaxCallFunc();
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
        $.ajax({
            type:'GET',
            url : url+'/cp/reports/ajax-channel-completion/'+no_set
        })
        .done(function(response){
            var data = response.data;
            var title = response.title;
            var xaxis = response.xaxis;
            var value = response.avg;
            var id_channel =response.id;
            var html_temp = "";
            var post_row_count = response.post_row_count;
            var url_comp = url+'/cp/reports/individual-channel-completion/';
            if(data.length > 0){
                $('#no_record').hide();
                $('#prev').show();
                $('#last').show();
                if($.isArray(data)){
                    $.each(data, function(key, ele){
                        html_temp+="<tr><td> <a href='"+url_comp+id_channel[key]+"'>"+xaxis[key]+"</a></td><td>"+ele+"</td><td>"+post_row_count[key]+"</td></tr>";
                    });
                }
                if(html_temp != ""){
                    $('#data_tbl_id').html(html_temp);
                }
                visitorData(data,title,xaxis,value, id_channel);    
            }else{
                $('#container').hide();
                $('#data_tbl_id').hide();
                $('#no_record').show();
                $('#prev').hide();
                $('#last').hide();
                current--;
            }
        });
    }
    
    ajaxCallFunc();
</script>
@stop

