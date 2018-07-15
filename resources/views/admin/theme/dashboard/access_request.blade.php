<!-- modal pop up -->
<div class="col-md-offset-3 modal fade access-requests" id="access-requests" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-width dashWidth600">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                    <i class="icon-file"></i>
                                        {{ trans('admin/dashboard.access_request') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="access-request-body" class="modal-body dashHeight300">
                <div class='col-md-6 col-md-offset-1 modal-table-data' id="access-request-table-content">
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn btn-primary" id="access-request-export">{{ trans('admin/dashboard.export') }}</div> 
                <a class="btn btn-success" href="{{ URL::to('/cp/contentfeedmanagement/requested-feeds?filter=PENDING') }}">{{ trans('admin/dashboard.more_actions') }}</a>
                <a class="btn btn-danger" data-dismiss="modal">{{ trans('admin/dashboard.close') }}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#access-request-export').click(function() {
        window.location.href = '/cp/dashboard/access-request-export/'+start_date+'/'+end_date;
    });
    $('.access-request').click(function() {
        $.ajax({
            type: "GET",
            url: "{{URL::to('/cp/dashboard/access-request/')}}"+'/'+start_date+'/'+end_date,
        })
        .done(function(response) {
            var html_temp = "";
            if(response.message != "" ) {
                $('#access-requests').modal('show');
                html_temp+= "<table class='table table-advance' id='datatable'><thead><tr><th>{{trans('admin/dashboard.channel_name')}}</th><th>{{trans('admin/dashboard.list_of_users')}}</th></tr></thead>";
                $.each(response.message, function( index, value ) {
                    html_temp+= "<tr><td>"+value[0]+"</td> <td>"+value[1]+"</td></tr>";
                });
                html_temp+= "</table>";
            }
            $('#access-request-table-content').html(html_temp);
        })
        .fail(function(response) {
            alert( "Error while updating the data. Please try again" );
        });        
    });
</script>