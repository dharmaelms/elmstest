@extends("admin.theme.layout.master_extended")
@section("content")
<!--datatable related css files-->
<link rel="stylesheet" href="{{ URL::asset("admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css")}}">

@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('success') }}
    </div>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('error') }}
    </div>
@endif

<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
            </div>
            <div class="box-content">
                    <div class="col-md-12 margin-bottom-20">
                        <div class="btn-toolbar pull-right clearfix">
                            <div class="btn-group">
                                @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::ADD_PACKAGE))
                                    <div class="btn-group">
                                        <a class="btn btn-primary btn-sm" href="{{URL::to("/cp/package/add-package")}}">
                                        <span class="btn btn-circle blue show-tooltip custom-btm">
                                            <i class="fa fa-plus"></i>
                                        </span>&nbsp;{{ trans('admin/package.add_package') }}
                                        </a>&nbsp;&nbsp;
                                    </div>
                                @endif
                                
                                @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EXPORT_PACKAGE_WITH_USERS))
                                    <a class="btn btn-circle show-tooltip" id= "export_link"
                                       title="<?php echo trans('admin/program.package_export_with_user'); ?>"
                                       href="{{URL::to('/cp/package/package-user-export')}}">
                                        <i class="fa fa-user"></i>
                                    </a>
                                @endif

                                @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EXPORT_PACKAGE_WITH_USER_GROUPS))
                                    <a class="btn btn-circle show-tooltip" id= "export_link_group"
                                       title="<?php echo trans('admin/program.package_export_with_usergroups'); ?>"
                                       href="{{URL::to('/cp/package/package-usergroup-export')}}">
                                        <i class="fa fa-users"></i>
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                <div class="clearfix"></div>
                <table class="table table-advance" id="package-list">
                    <thead>
                        <tr>
                            <th><input type="checkbox" id="check-all" /></th>
                            <th>{{trans('admin/package.name')}}</th>
                            <th>{{trans('admin/package.short_name')}}</th>
                            <th>{{trans('admin/package.start_date')}}</th>
                            <th>{{trans('admin/package.end_date')}}</th>
                            <th>{{trans('admin/package.content_feed')}}</th>
                            <th>{{trans('admin/package.category')}}</th>
                            <th>{{trans('admin/package.users')}}</th>
                            <th>{{trans("admin/package.user_groups")}}</th>
                            <th>{{trans('admin/package.status')}}</th>
                            <th>{{trans('admin/package.action')}}</th>
                        </tr>
                    </thead>
                </table>
            </div>
        </div>
    </div>
</div>

<!--datatable related js files-->
<script src="{{ URL::asset("admin/js/simpleloader/sl.min.js")}}"></script>
<script src="{{ URL::asset("admin/assets/data-tables/jquery.dataTables.js")}}"></script>
<script src="{{ URL::asset("admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js")}}"></script>
<script src="{{ URL::asset("admin/js/simpleloader/sl.min.js")}}"></script>
<script src="{{ URL::asset("admin/js/datatable_list.js")}}"></script>
<script src="{{ URL::asset("admin/js/auto_hide.js")}}"></script>
<script src="{{ URL::asset("admin/js/common.js")}}"></script>

<script>
    $(document).ready(function () {
        $(".alert").autoHide();

        dataTableObject = $("#package-list").initializeDatatable({
            serverSide : true,
            ordering : true,
            paging : true,
            processing : false,
            searching : true,
            order: [[0, "desc"]],
            columnDefs: [{
                targets: [5, 6, 7, 8, 9, 10], orderable: false
            }],
            ajax : {
                "url": "{!! URL::to("cp/package/list-data") !!}",
                "type": "GET",
                "dataSrc": function (json) {
                    var jsonPackageData = json.data;
                    var dataTableData = [];
                    for (i = 0; i < jsonPackageData.length; ++i) {
                        dataTableData[i] = [];
                        dataTableData[i][0] =
                            "<td><input type=\"checkbox\" name=\"package_ids[]\" value=\"" + jsonPackageData[i].package_id + "\"></td>";
                        dataTableData[i][1] = "<td>" + jsonPackageData[i].package_title + "</td>";

                        if (jsonPackageData[i].package_shortname !== "") {
                            dataTableData[i][2] = "<td>" + jsonPackageData[i].package_shortname + "</td>";
                        } else {
                            dataTableData[i][2] = "<td>NA</td>";
                        }
                        
                        dataTableData[i][3] = "<td>" + convertTimestampToDate(jsonPackageData[i].package_startdate) + "</td>";
                        dataTableData[i][4] = "<td>" + convertTimestampToDate(jsonPackageData[i].package_enddate) + "</td>";

                        var edit_package_url = "{{ URL::to("cp/package/edit-package") }}"+"/"+jsonPackageData[i].package_slug;

                        var assign_category = "{{ URL::to("cp/package/category-template") }}"+"/"+jsonPackageData[i].package_slug;;

                        var programs_count =
                            (jsonPackageData[i].program_ids !== undefined ? jsonPackageData[i].program_ids.length : 0);

                        var category_count =
                            (jsonPackageData[i].category_ids !== undefined ? jsonPackageData[i].category_ids.length : 0);

                        @if (has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_CHANNELS))
                            dataTableData[i][5] = "<td>" +
                                "<a href=\""+edit_package_url+"#package-programs-tab\" class=\"" + getBadgeClass(programs_count) + "\">" +
                                programs_count
                                + "</a>" +
                                "</td>";
                        @else
                            dataTableData[i][5] = "<td>" +
                                "<a class=\"" + getBadgeClass(programs_count) +
                                    "\" data-toggle=\"tooltip\" title=\"{{ trans("admin/package.no_permission_to_manage_programs") }}\">" +
                                programs_count
                                + "</a>" +
                                "</td>";
                        @endif
                        
                        dataTableData[i][6] = "<td>" +
                            "<a href=\""+assign_category+"\"  data-key=\""+jsonPackageData[i].package_slug+"\" title=\"{{ trans("admin/program.assign_cat") }}\" class=\"feedrel " + getBadgeClass(category_count) + "\" data-info=\"'category'\" data-json="+JSON.stringify(jsonPackageData[i].category_ids)+" data-text=\"Assign Category to <b>"+htmlEntities(jsonPackageData[i].package_title)+"<b>\" >" +
                            category_count
                            + "</a>" +
                            "</td>";


                        var users_count = (jsonPackageData[i].user_ids !== undefined ? jsonPackageData[i].user_ids.length : 0);
                        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USERS))
                            dataTableData[i][7] = "<td>" +
                            "<a href=\""+edit_package_url+"#package-users-tab\" class=\"" + getBadgeClass(users_count) + "\">" +
                            users_count
                            + "</a>" +
                            "</td>";
                        @else
                            dataTableData[i][7] = "<td>" +
                            "<a data-toggle=\"tooltip\" title=\"{{ trans("admin/package.no_permission_to_manage_users") }}\" class=\"" + getBadgeClass(users_count) + "\">" +
                            users_count
                            + "</a>" +
                            "</td>";
                        @endif

                        var user_groups_count =
                            (jsonPackageData[i].user_group_ids !== undefined ? jsonPackageData[i].user_group_ids.length : 0);
                        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USER_GROUPS))
                            dataTableData[i][8] = "<td>" +
                            "<a href=\""+edit_package_url+"#package-user-groups-tab\" class=\"" + getBadgeClass(user_groups_count) + "\">" +
                            user_groups_count
                            + "</a>" +
                            "</td>";
                        @else
                            dataTableData[i][8] = "<td>" +
                            "<a data-toggle=\"tooltip\" title=\"{{ trans("admin/package.no_permission_to_manage_user_groups") }}\" class=\"" + getBadgeClass(user_groups_count) + "\">" +
                            user_groups_count
                            + "</a>" +
                            "</td>";
                        @endif

                        dataTableData[i][9] = "<td>" + jsonPackageData[i].status + "</td>";

                        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::VIEW_PACKAGE_DETAILS))
                            dataTableData[i][10] = "<td>" +
                            "<a class=\"btn btn-circle show-tooltip viewpackage\" data-href=\"{{URL::to("cp/package/package-details")}}"+'/'+jsonPackageData[i].package_slug+"\" data-original-title=\"{{trans("admin/package.view")}}\">" +
                            "<i class=\"fa fa-eye\"></i>"
                            + "</a>"
                            + "</td>";
                        @else
                            dataTableData[i][10] = "<td>" +
                            "<a class=\"btn btn-circle show-tooltip\" data-toggle=\"tooltip\" title=\"{{ trans("admin/package.no_permission_to_view_package_details") }}\">" +
                            "<i class=\"fa fa-eye\"></i>"
                            + "</a>"
                            + "</td>";
                        @endif

                        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EDIT_PACKAGE))
                            dataTableData[i][10] += "<td>" +
                            "<a class=\"btn btn-circle show-tooltip\" href=\"{{URL::to("cp/package/edit-package")}}"+'/'+jsonPackageData[i].package_slug+"\" data-original-title=\"{{trans("admin/package.edit_package")}}\">" +
                            "<i class=\"fa fa-edit\"></i>"
                            + "</a>"
                            + "</td>";
                        @else
                            dataTableData[i][10] += "<td>" +
                            "<a class=\"btn btn-circle\" data-toggle=\"tooltip\" title=\"{{ trans("admin/package.no_permission_to_edit_package_details") }}\">" +
                            "<i class=\"fa fa-edit\"></i>"
                            + "</a>"
                            + "</td>";
                        @endif

                        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::DELETE_PACKAGE))
                            dataTableData[i][10] += "<td>" +
                            "<a class=\"btn btn-circle show-tooltip deletepackage\" href=\"{{URL::to("cp/package/delete-package")}}"+'/'+jsonPackageData[i].package_slug+"\" data-original-title=\"{{trans("admin/package.delete")}}\">" +
                            "<i class=\"fa fa-trash-o\"></i>"
                            + "</a>"
                            + "</td>";
                        @else
                            dataTableData[i][10] += "<td>" +
                            "<a class=\"btn btn-circle show-tooltip\" data-toggle=\"tooltip\" title=\"{{ trans("admin/package.no_permission_to_delete_package") }}\">" +
                            "<i class=\"fa fa-trash-o\"></i>"
                            + "</a>"
                            + "</td>";
                        @endif
                    }
                    return dataTableData;
                }
            }
        });
    });
</script>

@if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::VIEW_PACKAGE_DETAILS))
    <div class="modal fade" id="viewpackagedetails" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                        {{trans('admin/package.view')}}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body">
                    ...
                </div>
                <div class="modal-footer">
                    <a class="btn btn-success" data-dismiss="modal">{{trans('admin/program.close')}}</a>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            /* Code for view content feed details starts here */
            $("#package-list").on('click','.viewpackage',function(e){
                e.preventDefault();
                var $this = $(this);
                var $viewpackagedetails = $('#viewpackagedetails');
                simpleloader.fadeIn(200);
                $.ajax({
                    type: "GET",
                    url: $(this).attr('data-href')
                })
                    .done(function( response ) {
                        $viewpackagedetails.find('.modal-body').html(response).end().modal('show');
                        simpleloader.fadeOut(200);
                    })
                    .fail(function() {
                        alert( "Error while fetching data from server. Please try again later" );
                        simpleloader.fadeOut(200);
                    })
            });

            /* Code for view content feed details ends here */
        });
    </script>
@endif

@if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::DELETE_PACKAGE))
    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row custom-box">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3 class="modal-header-title">
                                    <i class="icon-file"></i>
                                        {{trans('admin/package.package_delete')}}
                                    </h3>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <div class="modal-body" style="padding: 20px">
                {{trans('admin/package.delete_confirmation')}}
            </div>
            <div class="modal-footer">
                <a class="btn btn-danger">{{trans('admin/package.yes')}}</a>
                <a class="btn btn-success" data-dismiss="modal">{{trans('admin/package.close')}}</a>
            </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function () {
            $("#package-list").on('click','.deletepackage',function(e){
                e.preventDefault();
                var $this = $(this);
                var $deletemodal = $('#deletemodal');
                $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href')).end().modal('show');
            })
        });
    </script>
@endif

<div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                    {{trans('admin/program.view_program_details')}}
                                </h3>                                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
   
            <div class="modal-body">
                ...
            </div>
            <div class="modal-footer" style="padding-right: 38px">
                <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/package.assign')}}</a>
                <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/package.close')}}</a>
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        $('#package-list').on('click','.feedrel',function(e){
            e.preventDefault();
            simpleloader.fadeIn();
            var $this = $(this);
            var $triggermodal = $('#triggermodal');
            var $iframeobj = $('<iframe src="'+$this.attr('href')+'" width="100%" height="" frameBorder="0"></iframe>');
            $iframeobj.unbind('load').load(function(){     
                
                var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
                
                if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){                       
                }             
                else{
                  $triggermodal.find('.modal-assign').css({"top": "3px"});                        
                  $triggermodal.find('.modal-body').css({"top":"0px"});                   
                }

                if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                    $triggermodal.modal('show');
                simpleloader.fadeOut();
                /* Code to Set Default checkedboxes starts here*/
                $.each($this.data('json'),function(index,value){
                    $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                })
                   
                $iframeobj.contents().click(function(){
                    setTimeout(function(){
                        var count = 0;
                        $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                            count++;
                        });
                    },10);
                });
                $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                   
        })

        $triggermodal.find('.modal-body').html($iframeobj);
        $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

        //code for top assign button click starts here
        $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
            $(this).parents().find('.modal-footer .btn-success').click();
        });

        $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
            var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
            console.log($checkedboxes);
            var $postdata = "";
            if(!$.isEmptyObject($checkedboxes)){
                $.each($checkedboxes,function(index,value){
                    if(!$postdata)
                        $postdata += index;
                    else
                        $postdata += "," + index;
                });
            }
            simpleloader.fadeIn();
            $.ajax({
                type: "POST",
                url: '{{URL::to('/cp/package/assign-category/')}}/'+$this.data('key'),
                data: 'ids='+$postdata+"&empty=true"
            })
            .done(function( response ) {
                if(response.flag == "success")
                $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                if(response.message!=undefined && response.flag == "error")
                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>'+response.message+'</div>').insertAfter($('.page-title'));
                if(response.message==undefined && response.flag == "error")
                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><?php echo trans('admin/program.server_error');?></div>').insertAfter($('.page-title'));
                $triggermodal.modal('hide');
                var $alerts = $('.alert');
                setTimeout(function(){
                    $alerts.alert('close');
                },5000);
                window.dataTableObject.fnDraw(true);
                simpleloader.fadeOut(1);
            })
            .fail(function() {
                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>Error!</strong> <?php echo trans('admin/program.server_error');?></div>').insertAfter($('.page-title'));
                window.dataTableObject.fnDraw(true);
                simpleloader.fadeOut(1);
            })
        })
    });
});
</script>

@stop
