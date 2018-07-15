<div class="row">
    <div class="col-xs-12 col-sm-12 col-md-12 col-lg-12">
        <div class="box">
            <div class="box-content">
                <div class="row">
                    <div class="col-xs-4 col-sm-4 col-md-4 col-lg-4">
                        <form class="form-horizontal">
                            <div class="form-group">
                                <label for="filter-enrollment-status" class="col-xs-2 col-sm-2 col-md-2 col-lg-2 control-label">
                                    Filter
                                </label>
                                <div class=" col-xs-6 col-sm-6 col-md-6 col-lg-6 controls">
                                    <select name="filter-enrollment-status" id="filter-enrollment-status" class="form-control">
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
                            <table class="table table-advance checkbox-custom-listener" id="user-list">
                                <thead>
                                    <tr>
                                        <th>
                                            <input type="checkbox">
                                        </th>
                                        <th>Username</th>
                                        <th>Full Name</th>
                                        <th>Email Id</th>
                                        <th>Role</th>
                                        <th>Created On</th>
                                        <th>Status</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="row" style="margin-top:10px;">
                    <div class="{{ ($module === "program")? "col-xs-offset-8 col-sm-offset-8 col-md-offset-8 col-lg-offset-8 col-xs-4 col-sm-4 col-md-4 col-lg-4" : "col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2" }}">
                    @if($module === 'program')
                        <button id="btn-updaterole" class="btn btn-success" style="display:none;">
                            <em class="fa fa-check-circle"></em>
                            <span style="font-weight: bold">&nbsp;Update Role</span>
                        </button>
                    @endif
                        <button id="btn-unassign" class="btn btn-danger" style="display:none;">
                            <em class="fa fa-times-circle"></em>
                            <span style="font-weight: bold">&nbsp;Unassign</span>
                        </button>
                    </div>        
                    <div class="col-xs-offset-10 col-sm-offset-10 col-md-offset-10 col-lg-offset-10 col-xs-2 col-sm-2 col-md-2 col-lg-2">
                        <button id="btn-assign" class="btn btn-success">
                            <em class="fa fa-check-circle"></em>
                            <span style="font-weight: bold">&nbsp;Assign</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<script src="{{ URL::asset('admin/js/datatable_list.js')}}"></script>
<script src="{{ URL::asset('admin/js/auto_hide.js')}}"></script>
<script>
    $(document).ready(function () {
        var user_table = $("#user-list").initializeDatatable({
            serverSide : true,
            ordering : true,
            paging : true,
            processing : false,
            searching : true,
            ajax : {
                "url" : "{!! URL::to("cp/usergroupmanagement/user-list") !!}",
                "type" : "GET",
                "data" : function (data) {
                    data.module = "{{ $module }}";
                    data.instance_id = "{{ $instance_id }}";
                    data.enrollment_status = $("#filter-enrollment-status").val();
                }
            },
            order : [[5, "desc"]],
            columnDefs : [{
                targets : [0, 4, 6], orderable : false
            }],
            drawCallback : updateCheckBoxVals
        });

        user_table.on("draw.dt", function (event, settings, flag) {
            $("#btn-assign, #btn-unassign, #btn-updaterole").css({
                display : "none"
            });

            if (settings.aoData.length > 0) {
                if (settings.oAjaxData.enrollment_status === "ASSIGNED") {
                    $("#btn-unassign, #btn-updaterole").show({
                        duration : 400
                    });
                } else if (settings.oAjaxData.enrollment_status === "UNASSIGNED") {
                    $("#btn-assign").show({
                        duration : 400
                    });
                }
            }

            user_table.find("select[name='role']").change(function () {
                $(this).parent().parent().find("input[type='checkbox']").prop({
                    checked : true
                }).trigger("change");
            });
        });

        $("#filter-enrollment-status").change(function () {
            user_table.customProperties.selectedCheckboxes = [];
            user_table.api().draw();
        });

        $("#btn-assign, #btn-unassign, #btn-updaterole").click(function (event) {
            if (Object.keys(checkedBoxes).length > 0) {
                user_table.customProperties.selectedCheckboxes = Object.keys(checkedBoxes);
            }
            if (user_table.customProperties.selectedCheckboxes.length > 0) {
                var button = $(this);
                var button_action = button.attr("id");
                var buttonHtml = $(this).html();
                button.prop("disabled", true).html("Please wait");
                var selectedCheckboxes = user_table.customProperties.selectedCheckboxes;

                var enrollment_action = null;
                var user_role_mapping = {};
                if ($("#filter-enrollment-status").val() === "ASSIGNED") {
                    if(button_action === "btn-unassign") {
                        enrollment_action = "UNASSIGN";
                        var action_url = "{{ URL::to("cp/contentfeedmanagement/assign-feed/user/{$slug}") }}";    
                    } else {
                        enrollment_action = "UPADTE-ROLE";
                        var action_url = "{{ URL::to("cp/contentfeedmanagement/update-role/updaterole/{$slug}") }}";
                        for (var i = 0; i < selectedCheckboxes.length; i++) {
                            user_role_mapping[selectedCheckboxes[i]] = $("#user_"+selectedCheckboxes[i]).val();
                        }
                    }
                } else {
                    enrollment_action = "ASSIGN";
                    var action_url = "{{ URL::to("cp/contentfeedmanagement/assign-feed/user/{$slug}") }}";
                    for (var i = 0; i < selectedCheckboxes.length; i++) {
                        user_role_mapping[selectedCheckboxes[i]] = $("#user_"+selectedCheckboxes[i]).val();
                    }
                }

                var xmlHTTPRequest = $.ajax({
                    url : action_url,
                    type : "POST",
                    data : {
                        enrollment_action : enrollment_action,
                        ids : selectedCheckboxes.join(","),
                        empty : false,
                        user_role_mapping : user_role_mapping
                    }
                });

                // Callback handler that will be called on success
                xmlHTTPRequest.done(function (response, textStatus, jqXHR){
                    // Log a message to the console
                    $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'
                        +response.message+'</div>').insertAfter($('.page-title')).autoHide();
                    user_table.customProperties.selectedCheckboxes = [];
                    checkedBoxes = [];
                    user_table.api().draw();
                });

                // Callback handler that will be called on failure
                xmlHTTPRequest.fail(function (jqXHR, textStatus, errorThrown){
                    // Log the error to the console
                    $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>'
                        +textStatus+'</div>').insertAfter($('.page-title')).autoHide();
                });

                // Callback handler that will be called regardless
                // if the request failed or succeeded
                xmlHTTPRequest.always(function () {
                    button.prop("disabled", false).html(buttonHtml);
                });
            } else {
                alert("Please select at least one user");
            }
        });
    });

    var flag_ck =0;
    function updateCheckBoxVals() {
        $allcheckBoxes = $('#user-list td input[type="checkbox"]');
        if(typeof window.checkedBoxes != 'undefined'){
          $allcheckBoxes.each(function(index,value){
          var $value = $(value);
          if(typeof checkedBoxes[$value.val()] != "undefined")
          $('[value="'+$value.val()+'"]').prop('checked',true);
          })
        }

        if($allcheckBoxes.length > 0)
          if($allcheckBoxes.not(':checked').length > 0)
            $('#user-list thead tr th:first input[type="checkbox"]').prop('checked',false);
          else
            $('#user-list thead tr th:first input[type="checkbox"]').prop('checked',true);
          updateHeight();
    }

        if(typeof window.checkedBoxes == 'undefined')
            window.checkedBoxes = {};
        $('#user-list').on('change','td input[type="checkbox"]',function(){
            var $this = $(this);
            if($this.prop('checked'))
                checkedBoxes[$this.val()] = $($this).parent().next().text();
            else
                delete checkedBoxes[$this.val()];
      
            if(flag_ck == 0){
                updateCheckBoxVals();
            }
      
        });

</script>