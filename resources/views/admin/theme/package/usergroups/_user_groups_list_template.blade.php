<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box">
            <div class="box-content">
                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                        <form class="form-horizontal">
                            <div class="form-group">
                                <label for="usergroups-enrollment-status" class="col-xs-2 col-sm-2 col-md-2 col-lg-2 control-label">
                                    Filter
                                </label>
                                <div class=" col-xs-6 col-sm-6 col-md-6 col-lg-6 controls">
                                    <select name="usergroups-enrollment-status" id="usergroups-enrollment-status" class="form-control">
                                        <option value="UNASSIGNED">Non Assigned</option>
                                        <option value="ASSIGNED">Assigned</option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                <div class="row">
                    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
                        <div class="table-responsive">
                            <table class="table table-advance checkbox-custom-listener" id="user-groups-list">
                                <thead>
                                    <tr>
                                        <th style="text-align: left;">
                                            <input type="checkbox">
                                        </th>
                                        <th style="text-align: left;">User Groups</th>
                                        <th style="text-align: left;">User Group Email</th>
                                        <th style="text-align: left;">Created On</th>
                                        <th style="text-align: left;">Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2">
                        <button id="btn-usergroups-unassign" class="btn btn-danger" style="display:none;">
                            <em class="fa fa-times-circle"></em>
                            <span style="font-weight: bold">&nbsp;Unassign</span>
                        </button>
                    </div>        
                    <div class="col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2">
                        <button id="btn-usergroups-assign" class="btn btn-success">
                            <em class="fa fa-check-circle"></em>
                            <span style="font-weight: bold">&nbsp;Assign</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    $(document).ready(function () {
        var user_group_table = $("#user-groups-list").initializeDatatable({
            serverSide : true,
            ordering : true,
            paging : true,
            processing : false,
            searching : true,
            ajax : {
                "url" : "{!! URL::to("cp/package/user-group-list") !!}",
                "type" : "GET",
                "data" : function (data) {
                    data.package_id = "{{ $package_id }}";
                    data.enrollment_status = $("#usergroups-enrollment-status").val();
                }
            },
            order : [[3, "desc"]],
            columnDefs : [{
                targets : [0], orderable : false
            }]
        });

        user_group_table.on("draw.dt", function (event, settings, flag) {
            $("#btn-usergroups-assign, #btn-usergroups-unassign").css({
                display : "none"
            });

            if (settings.aoData.length > 0) {
                if (settings.oAjaxData.enrollment_status === "ASSIGNED") {
                    $("#btn-usergroups-unassign").show({
                        duration : 400
                    });
                } else if (settings.oAjaxData.enrollment_status === "UNASSIGNED") {
                    $("#btn-usergroups-assign").show({
                        duration : 400
                    });
                }
            }
        });

        $("#usergroups-enrollment-status").change(function () {
            user_group_table.customProperties.selectedCheckboxes = [];
            user_group_table.api().draw();
        });

        $("#btn-usergroups-assign, #btn-usergroups-unassign").click(function (event) {
            if (user_group_table.customProperties.selectedCheckboxes.length > 0) {
                var button = $(this);
                var button_action = button.attr("id");
                var buttonHtml = $(this).html();
                button.prop("disabled", true).html("Please wait");
                var selectedCheckboxes = user_group_table.customProperties.selectedCheckboxes;
                var enrollment_action = null;
                var action_url = null;
                
                if ($("#usergroups-enrollment-status").val() === "ASSIGNED") {
                    enrollment_action = "UNASSIGN";
                    action_url = "{{ URL::to("cp/package/un-enroll-user-group/{$slug}") }}";  
                } else {
                    enrollment_action = "ASSIGN";
                    action_url = "{{ URL::to("cp/package/enroll-user-group/{$slug}") }}";
                }

                var xmlHTTPRequest = $.ajax({
                    url : action_url,
                    type : "POST",
                    data : {
                        user_group_ids : selectedCheckboxes
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

                    user_group_table.customProperties.selectedCheckboxes = [];
                    user_group_table.api().draw();
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