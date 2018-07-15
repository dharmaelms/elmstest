@section('content')
    <div class="row custom-box">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    @include('admin.theme.reports.menu', ['selector' => 'scormreports', 'general' => $general ])
                </div>
            </div>
        </div>
   </div>

    <div class="alert alert-danger" id='no_record'>
        <button class="close" data-dismiss="alert">×</button>{{trans('admin/reports.no_more_records_found')}} 
    </div>

    <div class="row" id="scorm-table-data">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content admin-reports-tbl">
                    <div class="col-sm-2 col-lg-1 scorm-reports-export">
                        <div class="input-group pull-right">
                            <button class="show-tooltip btn btn-sucess" title="Export Report" type="button" id="exp_report"><i class="fa fa-download"></i></button>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-1 arrow-nav-16">
                            <div class="cs-nav-btn" style="top: 10px !important;">
                                <button type="button" id="prev" class="fa fa-angle-left datasrc btn btn-circle"></button>
                            </div>
                        </div>

                        <div class="col-md-10">
                            <table class="table fill-head table-striped">
                                <thead>
                                    <tr>
                                        <th>{{trans('admin/reports.name')}}</th>
                                        <th colspan="3" align="center">
                                           <center>{{trans('admin/reports.status')}}</center> 
                                        </th>
                                        <th width="15%">{{trans('admin/reports.time_spent')}}</th>
                                        <th>{{trans('admin/reports.avg_scores')}}</th>
                                        <th>{{trans('admin/reports.number_of_users')}}</th>
                                        <tr class="status-cont">
                                            <th> </th>
                                            <th class="green-txt-col">{{trans('admin/reports.completed_in_percentage')}}</td>
                                            <th class="green-txt-col">{{trans('admin/reports.inprogress_in_percentage')}}</td>
                                            <th class="green-txt-col">{{trans('admin/reports.not_started')}}</td>
                                            <th> </th>
                                            <th> </th>
                                            <th> </th>
                                        </tr>
                                    </tr>
                                </thead>
                                <tbody id="data_tbl_id">
                                </tbody>
                            </table>
                        </div>

                        <div class="col-md-1 arrow-nav-16">
                            <div class="cs-nav-btn" style="top: 10px !important;">
                                <button type="button" id="last" class="fa fa-angle-right datasrc btn btn-circle"></button>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
    </div>
<script>
    $('#no_record').hide();
    var error=0;
    var current=0;
    if(current == 0)
    { 
        $('#prev').hide(); 
    }
    else
    { 
        $('#prev').show();
    }

    $('.close').click(function(){
        $(this).parent().hide()
    });
    
    $('#exp_report').click(function(){
        var url = '{{URL::to('/')}}';
        location.href = url+'/cp/reports/export-scorm-reports/';
    });
    
    $('#prev').click(function()
    {
        if(current >= 1)
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

    $('#info_report_btn').click(function(){
        $('#info_report').show();
    });

    function ajaxCallFunc(){
        var url='{{URL::to('/')}}';
        var no_set = current;//0;
        var limit = 10;
            
        $.ajax({
            type:'POST',
            url : url+'/cp/reports/ajax-scorm-reports/'+no_set+'/'+limit
        })
        .done(function(response){
            var html_temp = "";
            if(response.length > 0){

                if(current == 0)
                { 
                    $('#prev').hide(); 
                }
                else
                { 
                    $('#prev').show();
                }
                if(response.length < limit)
                {
                    $('#last').hide();
                } else {
                    $('#last').show();
                }

                $('#no_record').hide();
                $.each(response, function(key, ele){
                    html_temp+="<tr><td>"+ele.scorm_names+"</td><td class='green-col'>"+ele.completed+"</td><td class='yellow-col'>"+ele.inprogress+"</td><td class='pink-col'>"+ele.not_started+"</td><td>"+ele.avg_time_spent+"</td><td>"+ele.avg_score+"</td><td>"+ele.number_of_users+"</td></tr>";
                });
                if (html_temp != "") {
                    $('#data_tbl_id').html(html_temp);
                }
            } else {
                 $('#last').hide();
                $('#no_record').show();
                current--;
            }
            
        });
    }

    ajaxCallFunc();

</script>
@stop