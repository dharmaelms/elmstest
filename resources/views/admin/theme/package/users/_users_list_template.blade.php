<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box">
            <div class="box-content">
                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                        <form class="form-horizontal">
                            <div class="form-group">
                                <label for="filter-enrollment-status" class="col-xs-2 col-sm-2 col-md-2 col-lg-2 control-label">
                                    {{trans('admin/package.filter')}}
                                </label>
                                <div class=" col-xs-6 col-sm-6 col-md-6 col-lg-6 controls">
                                    <select name="filter-enrollment-status" id="filter-enrollment-status" class="form-control">
                                        <option value="UNASSIGNED">{{trans('admin/package.non_assigned')}}</option>
                                        <option value="ASSIGNED">{{trans('admin/package.assigned')}}</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-advance checkbox-custom-listener" id="user-list">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">
                                            <input type="checkbox">
                                        </th>
                                        <th style="text-align: left;">{{trans('admin/package.username')}}</th>
                                        <th style="text-align: left;">{{trans('admin/package.full_name')}}</th>
                                        <th style="text-align: left;">{{trans('admin/package.email_id')}}</th>
                                        <th style="text-align: left;">{{trans('admin/package.created_on')}}</th>
                                        <th style="text-align: left;">{{trans('admin/package.status')}}</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2">
                        <button id="btn-unassign" class="btn btn-danger" style="display:none;">
                            <em class="fa fa-times-circle"></em>
                            <span style="font-weight: bold">&nbsp;{{trans('admin/package.unassign')}}</span>
                        </button>
                    </div>        
                    <div class="col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2">
                        <button id="btn-assign" class="btn btn-success">
                            <em class="fa fa-check-circle"></em>
                            <span style="font-weight: bold">&nbsp;{{trans('admin/package.assign')}}</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        var user_table = $("#user-list").initializeDatatable({
            serverSide : true,
            ordering : true,
            paging : true,
            processing : false,
            searching : true,
            ajax : {
                "url" : "{!! URL::to("cp/package/user-list") !!}",
                "type" : "GET",
                "data" : function (data) {
                    data.module = "{{ $module }}";
                    data.package_id = "{{ $package_id }}";
                    data.enrollment_status = $("#filter-enrollment-status").val();
                }
            },
            order : [[4, "desc"]],
            columnDefs : [{
                targets : [0,5], orderable : false
            }]
        });

        user_table.on("draw.dt", function (event, settings, flag) {
            $("#btn-assign, #btn-unassign").css({
                display : "none"
            });

            if (settings.aoData.length > 0) {
                if (settings.oAjaxData.enrollment_status === "ASSIGNED") {
                    $("#btn-unassign").show({
                        duration : 400
                    });
                } else if (settings.oAjaxData.enrollment_status === "UNASSIGNED") {
                    $("#btn-assign").show({
                        duration : 400
                    });
                }
            }
        });

        $("#filter-enrollment-status").change(function () {
            user_table.customProperties.selectedCheckboxes = [];
            user_table.api().draw();
        });

        $("#btn-assign, #btn-unassign").click(function (event) {
            if (user_table.customProperties.selectedCheckboxes.length > 0) {
                var button = $(this);
                var button_action = button.attr("id");
                var buttonHtml = $(this).html();
                button.prop("disabled", true).html("Please wait");
                var selectedCheckboxes = user_table.customProperties.selectedCheckboxes;
                var action_url = null;
            
                if ($("#filter-enrollment-status").val() === "ASSIGNED") {
                    action_url = "{{ URL::to("cp/package/un-enroll-user/{$slug}") }}";  
                } else {
                    action_url = "{{ URL::to("cp/package/enroll-user/{$slug}") }}";    
                }

                var xmlHTTPRequest = $.ajax({
                    url : action_url,
                    type : "POST",
                    data : {
                        user_ids : selectedCheckboxes
                    }
                });

                // Callback handler that will be called on success
                xmlHTTPRequest.done(function (response, textStatus, jqXHR){
                    // Log a message to the console
                    if (response.flag) {
                        $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>')
                                .insertAfter($('.page-title')).autoHide();
                        
                    } else {
                        
                        $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>')
                                .insertAfter($('.page-title')).autoHide();
                    }

                    user_table.customProperties.selectedCheckboxes = [];
                    user_table.api().draw();
                });

                // Callback handler that will be called on failure
                xmlHTTPRequest.fail(function (jqXHR, textStatus, errorThrown){
                    // Log the error to the console
                    $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+textStatus+'</div>')
                            .insertAfter($('.page-title')).autoHide();
                });

                // Callback handler that will be called regardless
                // if the request failed or succeeded
                xmlHTTPRequest.always(function () {
                    button.prop("disabled", false).html(buttonHtml);
                });
            } else {
                alert("{{trans('admin/package.user_assign_error')}}");
            }
        });
    });
</script>