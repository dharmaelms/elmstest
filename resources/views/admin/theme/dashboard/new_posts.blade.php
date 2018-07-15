
<!-- modal pop up -->
<div class="col-md-offset-3 modal fade" id="new-posts" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                         {{ trans('admin/dashboard.new_post') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="new-post-body" class="modal-body dashHeight300">
                 <div class='col-md-6 col-md-offset-1 modal-table-data' id="new-post-table-content">
                </div>
            </div>
            <div class="modal-footer">
                <div class="btn btn-primary" id="post-export">{{ trans('admin/dashboard.export') }}</div>              
                <a class="btn btn-success" href="{{ URL::to('/cp/contentfeedmanagement/list-feeds/') }}">{{ trans('admin/dashboard.more_actions') }}</a>
                <a class="btn btn-danger" data-dismiss="modal">{{ trans('admin/dashboard.close') }}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('#post-export').click(function() {
        window.location.href = '/cp/dashboard/new-post-export/'+start_date+'/'+end_date;
    });

    $('.new-posts').click(function() {
        $.ajax({
            type: "GET",
            url: "{{URL::to('/cp/dashboard/new-posts/')}}"+'/'+start_date+'/'+end_date,
        })
        .done(function(response) {
                var html_temp = "";
                if(response.message != "" ) {
                    $('#new-posts').modal('show');
                    html_temp+= "<table class='table table-advance' id='datatable'><thead><tr><th>{{trans('admin/dashboard.program_name')}}</th><th>{{trans('admin/dashboard.posts')}}</th></tr></thead>";
                    $.each(response.message, function( index, value ) {
                        html_temp+= "<tr><td>"+value[0]+"</td><td>"+value[1]+"</td></tr>";
                    });
                    html_temp+= "</table>";
                }
                $('#new-post-table-content').html(html_temp);
        })
        .fail(function(response) {
            alert( "Error while updating the data. Please try again" );
        });        
    });
</script>