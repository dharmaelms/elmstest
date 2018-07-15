<div class="box">
    <div class="box-title">
        <div class="box-content">
            <div class="clearfix"></div>
            <div class="row">
                <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                    <form class="form-horizontal">
                        <div class="form-group">
                            <label for="package-programs-enrollment-status" class="col-xs-2 col-sm-2 col-md-2 col-lg-2 control-label">
                                Filter
                            </label>
                            <div class=" col-xs-8 col-sm-8 col-md-8 col-lg-8 controls">
                                <select name="package-programs-enrollment-status" id="package-programs-enrollment-status" class="form-control">
                                    <option value="NON_ASSIGNED">Non Assigned</option>
                                    <option value="ASSIGNED">Assigned</option>
                                </select>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
            <table class="table table-advance" id="package-programs-list">
                <thead>
                    <tr>
                        <th style="text-align: left;">
                            <input type="checkbox">
                        </th>
                        <th style="text-align: left;">{{trans("admin/program.name")}}</th>
                        <th style="text-align: left;">{{trans("admin/program.packets")}}</th>
                        <th style="text-align: left;">{{trans("admin/program.created_at")}}</th>
                        <th style="text-align: left;">{{trans("admin/program.created_by")}}</th>
                        <th style="text-align: left;">{{trans("admin/program.status")}}</th>
                    </tr>
                </thead>
            </table>
            <div class="row" style="margin-top:10px;">
                <div class="col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <button id="btn-pkg-programs-un-assign" class="btn btn-danger" style="display:none;">
                        <em class="fa fa-times-circle"></em>
                        <span style="font-weight: bold">&nbsp;{{trans('admin/package.unassign')}}</span>
                    </button>
                </div>
                <div class="col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2">
                    <button id="btn-pkg-programs-assign" class="btn btn-success">
                        <em class="fa fa-check-circle"></em>
                        <span style="font-weight: bold">&nbsp;{{trans('admin/package.assign')}}</span>
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>
<script>
    var package_users_count = {{ $package_users_count }};
    $(document).ready(function () {
        var package_programs_table = $("#package-programs-list").initializeDatatable({
            serverSide : true,
            ordering : true,
            paging : true,
            processing : false,
            searching : true,
            order: [[3, "desc"]],
            columnDefs: [{
                targets: [0, 2, 5], orderable: false
            }],
            ajax : {
                "url": "{!! URL::to("cp/packages/{$package["package_id"]}/programs") !!}",
                "type": "GET",
                "data" : function (data) {
                    data.enrollment_status = $("#package-programs-enrollment-status").val();
                },
                "dataSrc": function (json) {
                    var packagePrograms = json.data;
                    var dataTableData = [];
                    for (i = 0; i < packagePrograms.length; ++i) {
                        dataTableData[i] = [];

                        if ((($("#package-programs-enrollment-status").val() === "ASSIGNED")
                            || (packagePrograms[i].posts > 0 && packagePrograms[i].elements > 0))) {
                            dataTableData[i][0] =
                                "<td><input type=\"checkbox\" name=\"program_ids[]\" value=\"" + packagePrograms[i].id + "\"></td>";
                        } else {
                            dataTableData[i][0] = "";
                        }

                        dataTableData[i][1] = "<td>" + packagePrograms[i].title + "</td>";

                        dataTableData[i][2] = "<td>" +
                            "<a href=\"javascript:void;\" class=\"" + getBadgeClass(packagePrograms[i].posts) + "\">" +
                            packagePrograms[i].posts
                            + "</a>" +
                            "</td>";

                        dataTableData[i][3] = "<td>" + convertTimestampToDate(packagePrograms[i].created_at) + "</td>";

                        dataTableData[i][4] = "<td>" + packagePrograms[i].created_by + "</td>";

                        dataTableData[i][5] = "<td>" + packagePrograms[i].status + "</td>";
                    }

                    return dataTableData;
                }
            }
        });

        package_programs_table.on("draw.dt", function (event, settings, flag) {
            $("#btn-pkg-programs-un-assign, #btn-pkg-programs-assign").css({
                display : "none"
            });

            if (settings.aoData.length > 0) {
                if (settings.oAjaxData.enrollment_status === "ASSIGNED" && (package_users_count === 0)) {
                    $("#btn-pkg-programs-un-assign").show({
                        duration : 400
                    });
                } else if (settings.oAjaxData.enrollment_status === "NON_ASSIGNED") {
                    $("#btn-pkg-programs-assign").show({
                        duration : 400
                    });
                }
            }
        });

        $("#package-programs-enrollment-status").change(function () {
            package_programs_table.customProperties.selectedCheckboxes = [];
            package_programs_table.api().draw();
        });

        $("#btn-pkg-programs-un-assign, #btn-pkg-programs-assign").click(function () {
            if (package_programs_table.customProperties.selectedCheckboxes.length > 0) {
                var button = $(this);
                var buttonHtml = $(this).html();
                button.prop("disabled", true).html("Please wait");
                var selectedCheckboxes = package_programs_table.customProperties.selectedCheckboxes;

                var programs_enrollment_status = $("#package-programs-enrollment-status").val();
                var action_url = null;

                if (programs_enrollment_status === "ASSIGNED") {
                    action_url = "{{ URL::to("cp/packages/{$package["package_id"]}/un-assign-programs") }}";
                } else if (programs_enrollment_status === "NON_ASSIGNED") {
                    action_url = "{{ URL::to("cp/packages/{$package["package_id"]}/assign-programs") }}";
                }

                var xmlHTTPRequest = $.ajax({
                    url : action_url,
                    type : "POST",
                    data : {
                        program_ids : selectedCheckboxes
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

                    package_programs_table.customProperties.selectedCheckboxes = [];
                    package_programs_table.api().draw();
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
                alert("{{trans('admin/package.no_programs_selected_alert')}}");
            }
        });
    });
</script>