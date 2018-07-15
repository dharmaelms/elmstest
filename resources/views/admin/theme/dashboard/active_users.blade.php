
<!-- modal pop up -->
<div class="modal fade" id="active-users" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content dashWidth900">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                    <i class="icon-file"></i>
                                         {{ trans('admin/dashboard.active_user') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="active-users-body" class="modal-body dashHeight450">
                <div class='col-md-8 col-md-offset-1 modal-table-data' id="active-users-table-content">
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn btn-primary" id="active-users-export">{{ trans('admin/dashboard.export') }}</div>
                <a class="btn btn-success" href="{{ URL::to('/cp/usergroupmanagement/') }}">{{ trans('admin/dashboard.more_actions') }}</a>
                <a class="btn btn-danger" data-dismiss="modal">{{ trans('admin/dashboard.close') }}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#active-users-export').click(function() {
        window.location.href = '/cp/dashboard/active-users-export/'+start_date+'/'+end_date;
    });

    $('.active-users').click(function() {
        $.ajax({
            type: "GET",
            url: "{{URL::to('/cp/dashboard/active-users/')}}"+'/'+start_date+'/'+end_date,
        })
        .done(function(response) {
            var html_temp = "";
            if(response.message != "" ) {
                $('#active-users').modal('show');
                html_temp+= "<table class='table table-advance' id='datatable'><thead><tr><th>{{trans('admin/dashboard.user_fullname')}}</th><th>{{trans('admin/dashboard.username')}}</th><th>{{trans('admin/dashboard.user_email')}}</th></tr></thead>";
                $.each(response.message, function( index, value ) {
                    html_temp+= "<tr><td>"+value[0]+"</td> <td>"+value[1]+"</td><td>"+value[2]+"</td></tr>";
                });
                html_temp+= "</table>";
            }
            $('#active-users-table-content').html(html_temp);
        })
        .fail(function(response) {
            alert( "Error while updating the data. Please try again" );
        });        
    });
</script>