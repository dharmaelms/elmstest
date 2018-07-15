
<!-- modal pop up -->
<div class="col-md-offset-3 modal fade" id="new-announcements" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                         {{ trans('admin/dashboard.new_announcement') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="new-announcement-body" class="modal-body dashHeight300">
                <div class='col-md-6 col-md-offset-1 modal-table-data' id="new-announcement-table-content">
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn btn-primary" id="new-announcements-export">{{ trans('admin/dashboard.export') }}</div>
                <a class="btn btn-success" href="{{ URL::to('/cp/announce') }}">{{ trans('admin/dashboard.more_actions') }}</a>
                <a class="btn btn-danger" data-dismiss="modal">{{ trans('admin/dashboard.close') }}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#new-announcements-export').click(function() {
        window.location.href = '/cp/dashboard/new-announcements-export/'+start_date+'/'+end_date;
    });

    $('.new-announcements').click(function() {
        $.ajax({
            type: "GET",
            url: "{{URL::to('/cp/dashboard/new-announcements/')}}"+'/'+start_date+'/'+end_date,
        })
        .done(function(response) {
                var html_temp = "";
                if(response.message != "" ) {
                    $('#new-announcements').modal('show');
                    html_temp+= "<table class='table table-advance' id='datatable'><thead><tr><th>{{trans('admin/dashboard.announcement_name')}}</th><th>{{trans('admin/dashboard.send_to')}}</th></tr></thead>";
                    $.each(response.message, function( index, value ) {
                        html_temp+= "<tr><td>"+value[0]+"</td> <td>"+value[1]+"</td></tr>";
                    });
                    html_temp+= "</table>";
                }
                $('#new-announcement-table-content').html(html_temp);
        })
        .fail(function(response) {
            alert( "Error while updating the data. Please try again" );
        });        
    });
</script>