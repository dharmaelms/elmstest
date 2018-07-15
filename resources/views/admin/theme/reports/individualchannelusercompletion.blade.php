@section('content')
    <script type="text/javascript" src="{{URL::to('admin/js/Highcharts-4.1.8/js/highcharts.js')}}"></script>
    <div class="row custom-box">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                    <h3 style="color:white">{{$title}}</h3>
                    <div class="btn-toolbar pull-right clearfix"> 
                        <a href="{{URL::to('cp/reports/user-reports')}}"
                            title="{{trans('admin/reports.list_user')}}" 
                            class="show-tooltip btn btn-primary fa fa-angle-left">
                                {{trans('admin/reports.back_user_list')}}
                        </a>
                    </div>
                </div>
                <div class="box-content">
                    <form class="form-horizontal" name="myForm" action="" onclick="">
                        <div class="form-group">
                            <label class="col-sm-2 col-lg-1 control-label" style="padding-right:0;text-align:right">
                                <b>{{trans('admin/reports.find')}} : &nbsp;</b>
                            </label>
                            <div class="col-sm-2 col-lg-2 " style="padding-left:0;">
                                <select id="selected_feed" class="form-control chosen">
                                <option value="0">{{trans('admin/reports.search_here')}}</option>
                                    @foreach ($contentfeeds as $feed)
                                        <option value="{{ $feed['channel_id'] }}" <?php echo $feed['channel_id']==$channel_id?"selected":"";?>>{{str_limit(trim($feed['channel_name']), 75)}}
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
                                <button class="close">×</button>
                                <p>{{trans('admin/reports.user_ind_channel_compl_disc')}}</p>
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
                                <th>{{trans('admin/reports.avg_compl')}}</th>
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
    var error=0;
    var channal_id_var = "{{$channel_id}}";
    var user_id_js = "{{$user_id}}";
    var name = "{{$name}}";
    var user_name = "{{$user_name}}";
    $('#no_record').hide();
    $("#selected_feed").change(function () {
        if($('#selected_feed').find('option:selected').val() == 0){
            var url = '{{URL::to('/')}}';
            window.location.replace(url+'/cp/reports/user-completion-report/'+user_id_js);
        }else{
            channal_id_var = $('#selected_feed').find('option:selected').val();
            ajaxCallFunc();
        }
    });
    $('#exp_report').click(function(){
        var url = '{{URL::to('/')}}';
        location.href = url+'/cp/reports/csv-user-single-completion-report/'+channal_id_var+'/'+user_id_js;
    });
    $('#info_report_btn').click(function(){
        $('#info_report').show();
    });
    $('.close').click(function(){
        $(this).parent().hide()
    });
    var current = 0;
    function renderChart (data, xaxis, avg_data, user_name) {
        $('#container').highcharts({
            colors: ["#DF7401", "#58ACFA"],
            chart :{
                type: 'column'
            },
            title: {
                    text : ""
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
                name : user_name +"'s {{trans('admin/reports.post_comp')}}",
                data : data,
            },
            {
                name : '{{trans('admin/reports.avg_compl')}}',
                data : avg_data,
            }],           
        });
    }
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
        var limit = 3;
        $.ajax({
            type:'GET',
            url : url+'/cp/reports/ajax-individual-channel-user-completion/'+no_set+'/'+channal_id_var+'/'+user_id_js
        })
        .done(function(response){
            console.log(response);
            $('#range').html("");
            $('#range1').html("");
            var data_flter = new Array();
            var title_flter = new Array();
            var avg_data_filter = new Array();
            var xaxis = response.xaxis;
            var data_rest = response.avg_data;
            var html_temp = "";
            if(response.data.length > 0){
                $('#no_record').hide();
                if($.isArray(response.data)){
                    $.each(response.data, function(key, ele){
                        data_flter.push(ele);
                        title_flter.push(xaxis[key]);
                        avg_data_filter.push(data_rest[key]);
                        html_temp+="<tr><td>"+xaxis[key]+"</td><td>"+ele+"</td><td>"+data_rest[key]+"</td></tr>";
                    });
                }
                if(html_temp != ""){
                    $('#data_tbl_id').html(html_temp);
                }
                renderChart(data_flter, title_flter, avg_data_filter, user_name);    
            }else{
                $('#no_record').show();
                current--;
            }
        });
    }
ajaxCallFunc();
</script>
@stop
