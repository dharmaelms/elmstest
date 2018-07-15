@section('content')

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker-channel.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>
    <script src="{{ URL::asset("admin/js/simpleloader/sl.min.js")}}"></script>
    <script src="{{ URL::asset('admin/js/datatable_list.js')}}"></script>
    <script src="{{ URL::asset('admin/js/auto_hide.js')}}"></script>
    <script src="{{ URL::asset("admin/js/common.js")}}"></script>
    <script src="{{ URL::asset('admin/js/calendar.js')}}"></script>

    <script>
        /* Function to remove specific value from array */
        if (!Array.prototype.remove) {
            Array.prototype.remove = function(val) {
                var i = this.indexOf(val);
                return i>-1 ? this.splice(i, 1) : [];
            };
        }
        var $targetarr = [0,6];
    </script>

    @if ( Session::get('ap_success') )
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert">×</button>
            <!--    <strong>Success!</strong> -->
            {{ Session::get('ap_success') }}
        </div>
        <?php Session::forget('ap_success'); ?>
    @endif

    <div class="tabbable">
        <?php

            $enble = array('pricing', 'edit', 'tab', 'pack');
            $enabled = 'edit';

            if (Session::get('pricing')) {
                $enabled = "pricing";
            }

            if (Session::get('tab')) {
                $enabled = "tab";
            }

            if (Session::get('pack')) {
                $enabled = "pack";
            }

            if (Session::get('feedcustomfield')) {
                $enabled = "feedcustomfield";
            }

            if (Session::get('packagecustomfield')) {
                $enabled = "packagecustomfield";
            }

        ?>

        <ul id="myTab1" class="nav nav-tabs">
            @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EDIT_PACKAGE))
                <li class="<?php if($enabled === 'edit'){ echo "active"; }?>">
                    <a href="#content-feed" data-toggle="tab">
                        <i class="fa fa-home"></i>&nbsp;{{trans('admin/package.general_info')}}
                    </a>
                </li>
            @endif
            @if (has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_CHANNELS))
                <li>
                    <a href="#package-programs-tab-content" id="package-programs-tab" data-toggle="tab">
                        <i class="fa fa fa-rss"></i>&nbsp;{{trans("admin/package.package_programs")}}
                    </a>
                </li>
            @endif

            @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USERS))
                <li>
                    <a href="#package-users" data-toggle="tab" id="package-users-tab">
                        <i class="fa fa-user"></i>&nbsp;{{trans('admin/package.package_users')}}
                    </a>
                </li>
            @endif

            @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USER_GROUPS))
                <li>
                    <a href="#package-user-groups" data-toggle="tab" id="package-user-groups-tab">
                        <i class="fa fa-users"></i>&nbsp;{{trans('admin/package.package_usergroups')}}
                    </a>
                </li>
            @endif

            @if(config('app.ecommerce'))
                @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS))
                    @if (($pri_ser_info['pri_service'] === 'enabled') && ($package['package_sellability'] == "yes"))
                        <li class="<?php if($enabled === 'pricing'){ echo " active";}?>">
                            <a href="#pricing" data-toggle="tab">
                                <i class="fa fa-shopping-cart"></i> Pricing
                            </a>
                        </li>
                    @endif
                @endif

                @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_TABS))
                    <li class="<?php if($enabled === 'tab'){ echo " active";}?>">
                        <a href="#tab-content" data-toggle="tab">
                            {{trans('admin/package.other_details')}}
                        </a>
                    </li>
                @endif
            @endif

            @if(!empty($packageCF))
                <li class="<?php if($enabled === 'packagecustomfield'){ echo " active";}?>">
                    <a  class ="packagecustomfield" href="#packagecustomfield" data-toggle="tab">
                        {{trans('admin/customfields.customfields')}}
                    </a>
                </li>
            @endif
        </ul>
    </div>

    <div id="myTabContent1" class="tab-content">
        <div class="tab-pane fade <?php if(Session::get('packagecustomfield') && Session::get('packagecustomfield')==='packagecustomfield'){ echo " active in";}?>" id="packagecustomfield">
            <div class="row">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-content">
                            <form action="{{URL::to('cp/package/save-customfield/'.$package['package_slug'].'?filter=packagefields')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped">
                                @foreach($packageCF as $packagefield)
                                    <div class="form-group">
                                        <?php
                                            if (Input::old($packagefield['fieldname'])) {
                                                $field = Input::old($packagefield['fieldname']);
                                            } elseif ($errors->first($packagefield['fieldname'])) {
                                                $field = Input::old($packagefield['fieldname']);
                                            } elseif (isset($package[$packagefield['fieldname']]) && !empty($package[$packagefield['fieldname']])) {
                                                $field = $package[$packagefield['fieldname']];
                                            } else {
                                                $field = "";
                                            }
                                        ?>
                                        <label class="col-sm-3 col-lg-2 control-label">{{$packagefield['fieldlabel']}}@if($packagefield['mark_as_mandatory'] == 'yes') <span class="red">*</span> @endif</label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <input type="text" value="{{$field}}" name="{{$packagefield['fieldname']}}"> <br>
                                            {!! $errors->first($packagefield['fieldname'], '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                @endforeach
                                <div class="form-group">
                                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                                        <button type="submit" class="btn btn-info text-right">{{trans('admin/package.save')}} </button>
                                        <a href="{{URL::to('/cp/package/list-template')}}" >
                                            <button type="button" class="btn">{{trans('admin/package.cancel')}}</button>
                                        </a>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- Custom fields tab ends here -->

        <!-- Package programs tab content starts here -->
        @if (has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_CHANNELS))
            <div class="tab-pane fade" id="package-programs-tab-content">
                @include("admin.theme.package.programs._programs_list_template")
            </div>
        @endif
        <!-- Package programs tab content ends here -->

        @if(config('app.ecommerce'))
            @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_SUBSCRIPTIONS))
                <div class="tab-pane fade <?php if(Session::get('pricing') && Session::get('pricing')==='enabled'){ echo " active in";}?>" id="pricing">
                    <div class="box">
                        <div class="box-title">
                            <div class="box-content">
                                @if ( Session::get('success_price') )
                                    <div class="alert alert-success">
                                        <button class="close" data-dismiss="alert">×</button>
                                        {{ Session::get('success_price') }}
                                    </div>
                                    <?php Session::forget('success_price'); ?>
                                @endif
                                <div class="row">
                                    <div class="col-md-12" style="padding-bottom: 10px;">
                                        <button onclick="addSubscription();" id="add_subscription" name="add_subscription" class="btn btn-primary pull-right" ><span class="btn btn-circle blue show-tooltip custom-btm" data-original-title="" title="">
                                <i class="fa fa-plus"></i>
                                </span>&nbsp;{{trans('admin/package.add_new_subscription')}}
                                        </button>
                                        <button onclick="listSubscription();" id="list_subscription" name="list_subscription" class="btn btn-primary pull-right hide"><span class="btn blue show-tooltip custom-btm" data-original-title="" title="">

                                </span>&nbsp;{{trans('admin/package.back')}}
                                        </button>
                                    </div>
                                </div>
                                <div class="" id="subscription_list" name="subscription_list">
                                    @if($pri_ser_info['pri_service'] === 'enabled')
                                        @include('admin/theme/package/pricing/list_subscription', ['pri_ser_info' => $pri_ser_info])
                                    @endif
                                </div>
                                <div class="hide" id="subscription_add" name="subscription_add">
                                    @if($pri_ser_info['pri_service'] === 'enabled')
                                        @include('admin/theme/package/pricing/add_subscription', ['pri_ser_info' => $pri_ser_info])
                                    @endif
                                </div>
                                <div class="" id="subscription_edit" name="subscription_edit">
                                    <div id="edisubscriptioncontent" name="edisubscriptioncontent">

                                    </div>
                                </div>
                                <script type="text/javascript">
                                    function listSubscription()
                                    {
                                        $('#subscription_add').addClass('hide');
                                        $('#subscription_list').removeClass('hide');
                                        $('#list_subscription').addClass('hide');
                                        $('#add_subscription').removeClass('hide');
                                        $('#subscription_edit').addClass('hide');
                                        $("#subscription-tab input[type=text],input[type=number], textarea").val("");
                                        $(".help-inline").text('');

                                    }
                                    function addSubscription()
                                    {
                                        $('#subscription_list').addClass('hide');
                                        $('#subscription_add').removeClass('hide');
                                        $('#add_subscription').addClass('hide');
                                        $('#list_subscription').removeClass('hide');
                                    }
                                </script>
                                @if(Session::get('pricing_action') === 'add')
                                    <script type="text/javascript">
                                        addSubscription();
                                    </script>
                                @endif
                                <script type="text/javascript">
                                    $(document).on("click", ".open-AddBookDialog", function () {
                                        var slug = $(this).data('slug');
                                        $('#subscription_list').addClass('hide');
                                        $('#add_subscription').addClass('hide');
                                        $('#list_subscription').removeClass('hide');
                                        $('#subscription_edit').removeClass('hide');
                                        $.ajax({
                                            method: "POST",
                                            url: "<?php echo URL::to('cp/package/edit-subscription');?>",
                                            data:{
                                                slug: slug,
                                                sellable_id:$('#sellable_id').val(),
                                                sellable_type:$('#sellable_type').val(),
                                                package_slug:$('#slug').val()
                                            }
                                        })
                                            .done(function( msg ) {
                                                $('#edisubscriptioncontent').html(msg);
                                            });
                                    });
                                </script>
                            </div>
                        </div>
                    </div>
                </div>

                <input type="hidden" id="sellable_id" name="sellable_id" value="{{$package['package_id']}}">
                <input type="hidden" id="sellable_type" name="sellable_type" value="package">
                <input type='hidden' id="package_slug" name="package_slug" value="{{$package['package_slug']}}">
            @endif
            @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_TABS))
                <div class="tab-pane fade <?php if($enabled === 'tab'){ echo " active in";}?>" id="tab-content">

                    <div class="box">
                        <div class="box-title">
                            <div class="box-content">
                                @if ( Session::get('success_tab') )
                                    <div class="alert alert-success">
                                        <button class="close" data-dismiss="alert">×</button>
                                        {{ Session::get('success_tab') }}
                                    </div>
                                    <?php Session::forget('success_tab'); ?>
                                @endif
                                <div class="row">
                                    <div class="col-md-12" style="padding-bottom: 10px;">
                                        <button type="button" class="btn btn-primary pull-right" data-toggle="modal" data-target="#myModal"><i class="fa fa-plus"></i>
                                            </span>&nbsp;{{trans('admin/package.add_new_tab')}}
                                        </button>

                                    </div>
                                </div>
                                <div class="">
                                    <div class="table-responsive">
                                        <table class="table table-advance" id="sample">
                                            <thead>
                                            <tr>
                                                <th>{{trans('admin/package.tab_title')}}</th>
                                                <th>{{trans('admin/package.created_on')}}</th>
                                                <th>{{trans('admin/package.action')}}</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php
                                            if(!empty($tabs['tabs'])){
                                            foreach ($tabs['tabs'] as $key => $value)
                                            {

                                            ?>
                                            <tr>
                                                <td>
                                                    {{$value['title']}}
                                                </td>
                                                <td>
                                                    <?php
                                                    if(isset($value['created_at'])){
                                                    ?>
                                                    {{date('Y-m-d',$value['created_at'])}}
                                                    <?php
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <a class="btn btn-circle show-tooltip openedittabb" title="" data-toggle="modal" data-target="#edittab" data-pid="{{$tabs['p_id']}}" data-tslug="{{$value['slug']}}">
                                                        <i class="fa fa-edit"></i>
                                                    </a>
                                                    <a class="btn btn-circle show-tooltip deletefeed" title="" href="{{URL::to('cp/package/delete-package-tab/'.$tabs['p_id'].'/'.$value['slug'])}}" data-original-title="{{ trans('admin/package.delete') }}"><i class="fa fa-trash-o"></i></a>
                                                </td>
                                            </tr>
                                            <?php
                                            }
                                            }
                                            else
                                            {
                                            ?>
                                            <tr>
                                                <td colspan="3" class="text-center">{{trans('admin/package.on_this_points_are_no_tabs')}}</td>
                                            </tr>
                                            <?php
                                            }
                                            ?>
                                            </tbody>
                                        </table>
                                        <!-- Edit tab -->
                                        <div id="edittab" class="modal fade" role="dialog">
                                            <div class="modal-dialog modal-lg">
                                                <div class="modal-content">
                                                    <div class="modal-header">
                                                        <div class="row custom-box">
                                                            <div class="col-md-12">
                                                                <div class="box">
                                                                    <div class="box-title">
                                                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                                        <h3 class="modal-header-title">{{trans('admin/package.edit_tab')}}</h3>
                                                                    </div>
                                                                </div>
                                                            </div>
                                                        </div>
                                                    </div>
                                                    <div class="modal-body">
                                                        <div id="edittabBody">
                                                        </div>
                                                    </div>

                                                </div>
                                            </div>
                                        </div>
                                        <script type="text/javascript">
                                            $(document).on("click", ".openedittabb", function () {

                                                $.ajax({
                                                    method: "POST",
                                                    url: "<?php echo URL::to('cp/package/edit-tab');?>/"+$(this).data('pid')+"/"+$(this).data('tslug'),
                                                    data:{
                                                    }
                                                })
                                                    .done(function( msg ) {
                                                        $('#edittabBody').html(msg);
                                                    });
                                            });
                                        </script>
                                        <!-- Edit tab -->
                                    </div>
                                </div>
                                <div class="" id="tab_list" name="tab_list">
                                    @include('admin/theme/package/tabs/add', ['pri_ser_info' => $pri_ser_info, 'from' => 'edit_package', 'package_slug' => $pri_ser_info['package_slug'] ])
                                </div>
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript">
                        $(document).ready(function () {

                            window.setTimeout(function() {
                                $(".alert").fadeTo(1500, 0).slideUp(500, function(){
                                    $(this).remove();
                                });
                            }, 1000);

                        });
                    </script>
                </div>
            @endif
        @endif

        <!-- Content Feed -->
        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::EDIT_PACKAGE))
            <div class="tab-pane fade <?php if($enabled === 'edit'){ echo " active in";}?>" id="content-feed">
                @if ( Session::get('success') )
                    <div class="alert alert-success">
                        <button class="close" data-dismiss="alert">×</button>
                        <!--    <strong>Success!</strong> -->
                        {{ Session::get('success') }}
                    </div>
                    <?php Session::forget('success'); ?>
                @endif
                @if ( Session::get('error'))
                    <div class="alert alert-danger">
                        <button class="close" data-dismiss="alert">×</button>
                        <!-- <strong>Error!</strong> -->
                        {{ Session::get('error') }}
                    </div>
                    <?php Session::forget('error'); ?>
                @endif
                <?php
                $start    =  Input::get('start', 0);
                $limit    =  Input::get('limit', 10);
                $filter   =  Input::get('filter','all');
                $search   =  Input::get('search','');
                $order_by =  Input::get('order_by','2 desc');
                ?>
                <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
                <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
                <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>


                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                            <!-- <h3 style="color:black"><i class="fa fa-file"></i> Edit {{trans('admin/package.program')}}</h3> -->
                                <div class="box-tool">
                                    <a data-action="collapse" href="#"><i class="fa fa-chevron-up"></i></a>
                                    <a data-action="close" href="#"><i class="fa fa-times"></i></a>
                                </div>
                            </div>
                            <div class="box-content">
                                <form action="" class="form-horizontal form-bordered form-row-stripped" method="post">
                                    <div class="form-group">
                                        <?php
                                        if(Input::old('package_title'))
                                        {
                                            $package_title = Input::old('package_title');
                                        } elseif($errors->first('package_title')) {
                                            $package_title = Input::old('package_title');
                                        } else {
                                            $package_title = $package['package_title'];
                                        }
                                        ?>
                                        <label for="package_title" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.title')}} <span class="red">*</span></label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <input type="text" name="package_title" id="package_title" placeholder="{{trans('admin/package.program')}} {{trans('admin/package.title')}}" class="form-control" value="{{ $package_title }}">
                                            <input type="hidden" name="package_slug" id="package_slug" placeholder="Feed Slug" class="form-control" value="{{ $package['package_slug'] }}">
                                            <input type="hidden"  name="old_package_slug"  class="form-control" value="{{ $package['package_slug']}}">
                                            <?php $msg = $errors->first('package_title', '<span class="help-inline" style="color:#f00">:message</span>'); ?>
                                            <?php if($msg == "") echo $errors->first('package_slug', '<span class="help-inline" style="color:#f00">:message</span>'); else echo $msg; ?>

                                        </div>
                                    </div>
                                    <!--short name ends here-->
                                    <div class="form-group">
                                        <?php
                                        if(Input::old('package_shortname'))
                                        {
                                            $package_shortname = Input::old('package_shortname');
                                        } elseif($errors->first('package_slug')) {
                                            $package_shortname = Input::old('package_shortname');
                                        } else {
                                            $package_shortname = $package['package_shortname'];
                                        }
                                        ?>
                                        <label class="col-sm-3 col-lg-2 control-label" for="">{{trans('admin/package.short_name')}}</label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <input type="hidden" name="old_shortname" class="form-control" value="{{$package['package_shortname']}}">
                                            <input type="hidden" name="package_shortname_slug" id="package_shortname_slug" placeholder="Feed Slug" class="form-control" value="{{ Input::old('package_shortname_slug') }}">
                                            <input type="text" class="form-control" name="package_shortname" value="{{$package_shortname}}"  placeholder="{{trans('admin/package.short_name_nt_disp')}}">
                                            {!! $errors->first('package_shortname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <!--short name ends here-->
                                    <div class="form-group">
                                        <label for="package_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.start_date')}} <span class="red">*</span></label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <div class="input-group date">
                                                <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" readonly name="package_start_date" id="package_start_date" placeholder="{{trans('admin/package.start_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$package['package_startdate'],Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
                                            </div>
                                            {!! $errors->first('package_start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="package_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.end_date')}} <span class="red">*</span></label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <div class="input-group date">
                                                <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" readonly name="package_end_date" id="package_end_date" placeholder="{{trans('admin/package.end_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$package['package_enddate'],Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
                                            </div>
                                            {!! $errors->first('package_end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="package_display_start_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.display_start_date')}} <span class="red">*</span></label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <div class="input-group date">
                                                <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" readonly name="package_display_start_date" id="package_display_start_date" placeholder="{{trans('admin/package.display_start_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$package['package_display_startdate'],Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
                                            </div>
                                            {!! $errors->first('package_display_start_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="package_display_end_date" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.display_end_date')}} <span class="red">*</span></label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <div class="input-group date">
                                                <span class="input-group-addon calender-icon"><i class="fa fa-calendar"></i></span>
                                                <input type="text" readonly name="package_display_end_date" id="package_display_end_date" placeholder="{{trans('admin/package.display_end_date')}}" class="form-control datepicker" value="{{ Timezone::convertFromUTC("@".$package['package_display_enddate'],Auth::user()->timezone,'d-m-Y') }}"  style="cursor: pointer">
                                            </div>
                                            {!! $errors->first('package_display_end_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    @if (config('app.ecommerce') == true)
                                        <div class="form-group">
                                            <label for="sellability" class="col-sm-2 col-lg-2 control-label">{{trans('admin/package.sellability')}} <span class="red">*</span></label>
                                            <div class="col-sm-4 col-lg-4 controls">
                                                <select class="form-control" name="sellability" id="sellability" data-rule-required="true">
                                                    <option <?php if($package['package_sellability'] == "yes") echo "selected"?> value="yes">{{trans('admin/package.yes')}}</option>
                                                    <option <?php if($package['package_sellability'] == "no") echo "selected"?> value="no">{{trans('admin/package.no')}}</option>
                                                </select>
                                                {!! $errors->first('sellability', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-group">
                                        <label for="visibility" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.visibility')}} <span class="red">*</span></label>
                                        <div class="col-sm-5 col-lg-5 controls">
                                            <select class="form-control" name="visibility" id="visibility" data-rule-required="true">
                                                <option <?php if($package['package_visibility'] == "yes") echo "selected"?> value="yes">{{trans('admin/package.yes')}}</option>
                                                <option <?php if($package['package_visibility'] == "no") echo "selected"?> value="no">{{trans('admin/package.no')}}</option>
                                            </select>
                                            {!! $errors->first('visibility', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label for="select" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.status')}} <span class="red">*</span></label>
                                        <div class="col-sm-5 col-lg-5 controls">
                                            <select class="form-control" name="status" id="status" data-rule-required="true">
                                                <option <?php if($package['status'] == "ACTIVE") echo "selected"?> value="active">{{trans('admin/package.active')}}</option>
                                                <option <?php if($package['status'] == "IN-ACTIVE") echo "selected"?> value="inactive">{{trans('admin/package.in_active')}}</option>
                                            </select>
                                            {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <input type="hidden" name="hiddensellability" value="{{$package['package_sellability']}}">
                                    <!--start-->
                                    @if (config('app.ecommerce') == false)
                                        <div class="form-group" id="access">
                                            <label for="select" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.access')}}<span class="red">*</span></label>
                                            <div class="col-sm-5 col-lg-5 controls">
                                                <select class="form-control" name="package_access" id="package_access" data-rule-required="true">
                                                <option <?php if($package['package_access'] == "restricted_access") echo "selected"?> value="restricted_access">{{trans('admin/package.restricted')}}</option>
                                                <option <?php if($package['package_access'] == "general_access") echo "selected"?> value="general_access">{{trans('admin/package.general')}}</option>
                                                </select>
                                                {!! $errors->first('access', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                            </div>
                                        </div>
                                @endif
                                <!--end-->
                                    <div class="form-group">
                                        <label for="package_description" class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.description')}} </label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <textarea name="package_description" id="package_description" rows="5" class="form-control" placeholder="{{trans('admin/package.content_feed')}} Description">{{ $package['package_description'] }}</textarea>
                                            {!! $errors->first('package_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.keyword_tags')}}</label>
                                        <div class="col-sm-9 col-lg-10 controls">
                                            <input type="text" class="form-control tags medium" value="<?php echo (isset($package['package_keywords']) && is_array($package['package_keywords'])) ? implode(',',$package['package_keywords']) : ""; ?>" name="package_tags" />
                                            {!! $errors->first('package_tags', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                        </div>
                                    </div>
                                    <div class="form-group">
                                        <div class="col-sm-12 col-lg-6">
                                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/package.cover_image')}} </label>
                                            <div class="col-sm-9 col-lg-10 controls">
                                                <div class="fileupload fileupload-new">
                                                    <div class="fileupload-new img-thumbnail" style="width: 200px;padding:0">
                                                        <?php if($package['package_cover_media']){ ?>
                                                        <img src="{{URL::to('/cp/dams/show-media/'.$package['package_cover_media'])}}" width="100%" alt="" id="bannerplaceholder"/>
                                                        <?php } else{ ?>
                                                        <img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" id="bannerplaceholder"/>
                                                        <?php } ?>
                                                    </div>
                                                    <div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                                                    <div>
                                                        <button class="btn" type="button" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&filter=image&from=edit_package&select=radio')}}">{{trans('admin/program.select')}}</button>
                                                        @if (has_admin_permission(ModuleEnum::DAMS, DAMSPermission::ADD_MEDIA))
                                                        <button class="btn" type="button" id="upload" data-url="{{URL::to("cp/dams/add-media?view=iframe&from=edit_package&filter=image")}}">{{trans('admin/program.upload_new')}}</button>
                                                        @endif
                                                        <?php
                                                        if($package['package_cover_media']){ ?>
                                                        <button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/package.remove')}} </button>
                                                        <?php   }
                                                        ?>

                                                        <input type="hidden" name="banner" value="{{(isset($package['package_cover_media'])) ? $package['package_cover_media'] : ""}}">
                                                    </div>
                                                </div>
                                                {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                            </div>
                                        </div>
                                    </div>
                                    <div class="form-group last">
                                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                                            <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{trans('admin/package.save')}}</button>
                                            <a href="{{URL::to('/cp/package/'.$url)}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}"><button type="button" class="btn">{{trans('admin/package.cancel')}}</button></a>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                    <div class="modal fade" id="trigger_modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
                        <div class="modal-dialog modal-lg">
                            <div class="modal-content">
                                <div class="modal-header">
                                    <div class="row custom-box">
                                        <div class="col-md-12">
                                            <div class="box">
                                                <div class="box-title">
                                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                    <h3 class="modal-header-title" >
                                                        <i class="icon-file"></i>
                                                        {{trans('admin/package.view_media_details')}}
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
                                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{trans('admin/package.assign')}}</a>
                                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{trans('admin/package.close')}}</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <script>
                        $(document).ready(function(){
                            $('.datepicker').datepicker({
                                format : "dd-mm-yyyy",
                                startDate: '+0d'
                            }).on('changeDate',function(){
                                $(this).datepicker('hide')
                            });

                            $('[name="package_title"]').on("blur",function(){
                                if($(this).val().trim() != ""){
                                    var slug=$('[name="package_slug"]').val($(this).val().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'))

                                    //If name contains special characters,generated slug will be empty.Sending slug as special character to get proper validation.
                                    if(!slug.val())
                                    {
                                        $('[name="package_slug"]').val('$*&');
                                    }
                                }
                            });

                            $('[name="package_shortname"]').on("blur",function(){
                                if($(this).val().trim() != ""){
                                    var sort_slug=$('[name="package_shortname_slug"]').val($(this).val().toLowerCase().replace(/[^\w ]+/g,'').replace(/ +/g,'-'))

                                    //If name contains special characters,generated slug will be empty.Sending slug as special character to get proper validation.
                                    if(!sort_slug.val())
                                    {
                                        $('[name="package_shortname_slug"]').val('$*&');
                                    }
                                    $('[name="package_title"]').trigger('blur');

                                }
                            });

                            $('#selectfromdams, #upload').click(function(e){

                                e.preventDefault();
                                simpleloader.fadeIn();
                                var $this = $(this);
                                var $triggermodal = $('#trigger_modal');
                                var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="500px" style="max-height:500px !important" frameBorder="0"></iframe>');
                                $iframeobj.unbind('load').load(function(){
                                    if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                                        $triggermodal.modal('show');
                                    simpleloader.fadeOut();
                                });
                                $triggermodal.find('.modal-body').html($iframeobj);
                                $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());
                                $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                                    var $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked');
                                    if($selectedRadio.length){
                                        $('#bannerplaceholder').attr('src','{{URL::to('/cp/dams/show-media/')}}/'+$selectedRadio.val()).width("100%");
                                        $('#removethumbnail').remove();
                                        $('<button class="btn btn-danger" type="button" id="removethumbnail"> {{trans('admin/package.remove')}} </button>').insertBefore($('input[name="banner"]').val($selectedRadio.val()));
                                        $triggermodal.modal('hide');
                                    }
                                    else{
                                        alert('Please select atleast one entry');
                                    }
                                });
                            });
                            $('input.tags').tagsInput({
                                width: "auto"
                            });
                            $(document).on('click','#removethumbnail',function(){
                                $('#bannerplaceholder').attr('src', '{{URL::asset("admin/img/demo/200x150.png")}}');
                                $('input[name="banner"]').val('');
                                $(this).remove();
                            });
                            $('.input-time').timepicker({
                                minuteStep: 1,
                                secondsStep: 5,
                                showSeconds: true,
                                showHours: false,
                                showMeridian: false,
                                defaultTime: false
                            });
                            $('[name="package_shortname"]').trigger('blur');
                        })
                    </script>
                </div>
            </div>
        @endif
        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USERS))
            <div class="tab-pane fade" id="package-users">
                @include("admin.theme.package.users._users_list_template", [
                "module" => "package",
                "package_id" => $package["package_id"],
                "slug" => $package["package_slug"],
                "from" => "package",
                ])
            </div>
        @endif

        @if(has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_USER_GROUPS))
            <div class="tab-pane fade" id="package-user-groups">
                @include("admin.theme.package.usergroups._user_groups_list_template", [
                "module" => "package",
                "package_id" => $package["package_id"],
                "slug" => $package["package_slug"],
                "from" => "package",
                ])
            </div>
        @endif
    </div>

    <script>
        var active_tab = window.location.hash;
        $(document).ready(function () {
            if (active_tab !== undefined) {
                $(active_tab).tab("show");
            }

            @if (has_admin_permission(ModuleEnum::PACKAGE, PackagePermission::MANAGE_PACKAGE_CHANNELS))
                $("#package-programs-tab").on("show.bs.tab", function () {
                if ($("#package-programs-enrollment-status").val() === "ASSIGNED") {
                    var xmlHttpRequest = $.ajax({
                        url : "{{ URL::to("cp/packages/{$package["package_id"]}/users-count") }}",
                        method : "GET",
                        dataType : "json"
                    });

                    xmlHttpRequest.done(function (data) {
                        package_users_count = data.users_count;
                        if (package_users_count > 0) {
                            $("#btn-pkg-programs-un-assign").hide({
                                duration : 400
                            });
                        } else {
                            $("#btn-pkg-programs-un-assign").show({
                                duration : 400
                            });
                        }
                    });
                }
            })
            @endif
        });
    </script>
    <!-- Content Feed Ends -->
@stop
