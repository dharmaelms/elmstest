
<!-- modal pop up -->
<div class="col-md-offset-3 modal fade" id="action-quizzes" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                    <i class="icon-file"></i>
                                         {{ trans('admin/dashboard.quizzes_with_no_question') }}
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div id="action-quizzes-body" class="modal-body" style="height: 250px; overflow-y: auto;">
                <div class='col-md-6 col-md-offset-1' id="action-quizzes-table-content">
                </div>
            </div>
            <div class="modal-footer">
                <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/user.close') }}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $('.action-quizzes').click(function() {
        $.ajax({
            type: "GET",
            url: "{{URL::to('/cp/dashboard/quizzes-with-no-questions/')}}",
        })
        .done(function(response) {
            $('#action-quizzes').modal('show');
            var html_temp = "";
            html_temp+= "<table class='table table-advance' id='datatable'><thead><tr><th>{{trans('admin/dashboard.quizzes')}}</th></tr></thead>";

            html_temp+= "<tr><td>{{trans('admin/dashboard.name_of_module')}}</td></tr>";
            
            html_temp+= "</table>";
            $('#action-quizzes-table-content').html(html_temp);
        })
        .fail(function(response) {
            alert( "Error while updating the data. Please try again" );
        });        
    });
</script>