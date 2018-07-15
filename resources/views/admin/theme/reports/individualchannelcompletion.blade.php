@section('content')
    <script type="text/javascript" src="{{URL::to('admin/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>
    <div class="row custom-box">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    @include('admin.theme.reports.menu', ['selector' => 'channelcontent', 'general' => $general ])
                    @include('admin.theme.reports.content_report_form', ['channel_name' => $name])
                    <div class="row">
                        <div class="col-md-12">
                            <div class="alert alert-info" id = "info_report" style="display:none">
                                <button class="close" >×</button>
                                <p>{{trans('admin/reports.ind_channel_compl_disc')}}</p>
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
                                <th>{{trans('admin/reports.post_name')}}</th>
                                <th>{{trans('admin/reports.completion')}}</th>
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
    $('#no_record').hide();
    var channal_id_var = "{{$channel_id}}";
    var name = "{{$name}}"; 
    $("#selected_feed").change(function () {
        if($('#selected_feed').find('option:selected').val() == 0){
            var url = '{{URL::to('/')}}';
            window.location.replace(url+'/cp/reports/channel-completion');
        }else{
            channal_id_var = $('#selected_feed').find('option:selected').val();
            ajaxCallFunc()
        }
    });
    $('#exp_report').click(function(){
        var url = '{{URL::to('/')}}';
        var from = $('#from').val();
        var to = $('#to').val();
        var date_range = $('#cus_range').val();
        location.href = url+'/cp/reports/csv-single-completion-report/'+channal_id_var;
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
                    text : '{{trans('admin/reports.posts')}}',
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
                },
            },
            series : [{
                name : '{{trans('admin/reports.post_comp')}}',
                data : data,
            }],           
        });
    }
</script>

<script>
    var error=0;

    $("#sub").click(function(){
        current = 0;
        ajaxCallFunc();
    });

    $('#prev').click(function()
    {
        if(current <= 0)
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
    // }
    function ajaxCallFunc(){
        var url='{{URL::to('/')}}';
        var id = $(this).attr('id'); 
        var no_set = current;
        $.ajax({
            type:'GET',
            url : url+'/cp/reports/ajax-individual-channel-completion/'+no_set+'/'+channal_id_var
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
                $('#no_record').hide();
                $('#prev').show();
                $('#last').show();
                if($.isArray(data)){
                    $.each(data, function(key, ele){
                        data_flter.push(ele);
                        title_flter.push(xaxis[key]);
                        html_temp+="<tr><td>"+xaxis[key]+"</td><td>"+ele+"</td></tr>";  
                    });
                }
                if(html_temp != ""){
                    $('#data_tbl_id').html(html_temp);
                }
                $('#container').show();
                $('#data_tbl_id').show();
                visitorData(data_flter,title,title_flter,value, id_channel);    
            }else{
                $('#no_record').show();
                $('#container').hide();
                $('#data_tbl_id').hide();
                $('#prev').hide();
                $('#last').hide();
                current--;
            }
        });
    }

    ajaxCallFunc();
</script>
@stop
