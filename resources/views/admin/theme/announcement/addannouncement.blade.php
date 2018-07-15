@section('content')
<?php use App\Model\Common; ?>
<?php use App\Model\Announcement; ?>
@if ( Session::get('success') )
    <div class="alert alert-success" id="alert-success">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('success') }}
    </div>
    <?php Session::forget('success'); ?>
@endif
@if ( Session::get('error'))
    <div class="alert alert-danger">
        <button class="close" data-dismiss="alert">×</button>
        {{ Session::get('error') }}
    </div>
    <?php Session::forget('error'); ?>
@endif
    <?php
    if (!is_null(Input::old('announce_id_holder')) &&  Input::old('announce_id_holder')>0) {
        $announcement=Announcement::getAnnouncement(Input::old('announce_id_holder'));
        // print_r($announcement[0]['relations']);
        $slug=$announcement[0]['announcement_id'];
        if (isset($announcement[0]['relations'])) {
             $rel=$announcement[0]['relations'];
            if (isset($rel['active_user_announcement_rel']) && !empty($rel['active_user_announcement_rel'])) {
                $userCount = "<a href='".URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel badge btn btn-primary' data-key='".$slug."' data-info='user' data-text='Assign this Announcement to User' data-json='".json_encode($rel['active_user_announcement_rel'])."'>".count($rel['active_user_announcement_rel']).trans('admin/announcement.selected_users'). "</a><input type='hidden' name='user' value='".implode(',', $rel['active_user_announcement_rel'])."'>";
            } else {
                $userCount = "<a href='".URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='user' data-text='Assign this announcement to user' data-json=''>". 0 .' '.trans('admin/announcement.selected_users'). "</a><input type='hidden' name='user' value=''>";
            }

            if (isset($rel['active_usergroup_announcement_rel']) && !empty($rel['active_usergroup_announcement_rel'])) {
                $userGroupCount = "<a href='".URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug ."' data-info='usergroup' data-text='Assign this Announcement to user group' data-json='".json_encode($rel['active_usergroup_announcement_rel'])."'>".count($rel['active_usergroup_announcement_rel']).trans('admin/announcement.selected_user_groups'). "</a><input type='hidden' name='usergroup' value='".implode(',', $rel['active_usergroup_announcement_rel'])."'>";
            } else {
                $userGroupCount = "<a href='".URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug."'data-info='usergroup' data-text='Assign this announcement to user group' data-json=''>". 0 .' '.trans('admin/announcement.selected_user_groups'). "</a><input type='hidden' name='usergroup' value=''>";
            }
                /*if(isset($rel['active_media_announcement_rel']) && !empty($rel['active_media_announcement_rel']))
                    $media = "<a href='".URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio')."' class='damsrel btn btn-primary' data-key='".$slug ."' data-info='media' data-text='Assign this announcement to media' data-json='".json_encode($rel['active_media_announcement_rel'])."'>".count($rel['active_media_announcement_rel'])."Select</a>";
                else
                    $media = "<a href='".URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='media' data-text='Assign this announcement to media' data-json=''>Select</a>";
                */
            if (isset($rel['active_contentfeed_announcement_rel']) && !empty($rel['active_contentfeed_announcement_rel'])) {
                $contentfeed = "<a href='".URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&from=announcement')."' class='damsrel btn btn-primary' data-key='".$slug ."' data-info='contentfeed' data-text='Assign this Announcement to ".trans('admin/program.program')."' data-json='".json_encode($rel['active_contentfeed_announcement_rel'])."'>".count($rel['active_contentfeed_announcement_rel']).trans('admin/announcement.selected_channel'). "</a><input type='hidden' name='contentfeed' value='".implode(',', $rel['active_contentfeed_announcement_rel'])."'>";
            } else {
                $contentfeed = "<a href='".URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&from=announcement')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='contentfeed' data-text='Assign this announcement to ".trans('admin/program.program')."' data-json=''>". 0 .' '.trans('admin/announcement.selected_channel'). "</a><input type='hidden' name='contentfeed' value=''>";
            }
        } else {
            $userCount = "<a href='".URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='user' data-text='Assign this announcement to user' data-json=''>". 0 .' '.trans('admin/announcement.selected_users'). "</a>";
            $userGroupCount = "<a href='".URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug."'data-info='usergroup' data-text='Assign this Announcement to User Group' data-json=''>". 0 .' '.trans('admin/announcement.selected_user_groups'). "</a>";
            // $media = "<a href='".URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='media' data-text='Assign this announcement to media' data-json=''>Select</a><input type='hidden' name='select' value=''>";
            $contentfeed = "<a href='".URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&from=announcement')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='contentfeed' data-text='Assign this Announcement to ".trans('admin/program.program')."' data-json=''>". 0 .' '.trans('admin/announcement.selected_channel'). "</a><input type='hidden' name='contentfeed' value=''>";
        }
    } else {
        $userCount = "<a href='".URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='' data-info='user' data-text='Assign this Announcement to User' data-json=''>". 0 .' '.trans('admin/announcement.selected_users'). "</a><input type='hidden' name='user' value=''>";
        $userGroupCount = "<a href='".URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key=''data-info='usergroup' data-text='Assign this Announcement to User Group' data-json=''>". 0 .' '.trans('admin/announcement.selected_user_groups'). "</a><input type='hidden' name='usergroup' value=''>";
        // $media = "<a href='".URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio')."' class='damsrel btn btn-primary' data-key='' data-info='media' data-text='Assign this Announcement to Media' data-json=''>Select</a><input type='hidden' name='select' value=''>";
        $contentfeed = "<a href='".URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&from=announcement')."' class='damsrel btn btn-primary' data-key='' data-info='contentfeed' data-text='Assign this Announcement to ".trans('admin/program.program')."' data-json=''>". 0 .' '.trans('admin/announcement.selected_channel'). "</a><input type='hidden' name='contentfeed' value=''>";
    }
    ?>
    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>

    <div class="form-wrapper">
        <form action="<?php if (!is_null(Input::old('announce_id_holder')) && Input::old('announce_id_holder')>0) {
?>{{URL::to('cp/announce/upload-announcement/'.Input::old('announce_id_holder'))}}<?php
}?>" class="form-horizontal form-bordered form-row-stripped" method="post" id="form-addannouncement" style="margin-top:10px;" enctype='multipart/form-data' >
        <div class="box">
            <div class="box-title">
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.title') }}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-8 controls">
                    <input id='announce_title_id' type="text" name="announcement_title" class="form-control" value="{{Input::old('announcement_title')}}" />
                    {!! $errors->first('announcement_title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group">
                <label for="announcement_content" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.content') }}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-8 controls">
                    <textarea name="announcement_content" rows="5" id="addcont" class="form-control ckeditor">{!! Input::old('announcement_content') !!}</textarea>
                    {!! $errors->first('announcement_content', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            @if(!empty(Input::old('editor_images')))
                @foreach(Input::old('editor_images') as $image)
                     <input type="hidden" name="editor_images[]" value={{ $image }}>
                @endforeach
            @endif
            <div class="form-group" id="pick_date">
                <label for="start_time" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.publish_on') }}<span class="red">*</span></label>
                <div class="col-sm-5 col-lg-3 controls">
                     <div class="input-group date">
                        <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="schedule_date" class="form-control datepicker" readonly='readonly' value="{{ Input::old('schedule_date', Timezone::convertFromUTC('@'.time(), Auth::user()->timezone, 'd-m-Y')) }}" style="cursor: pointer">
                    </div>
                    {!! $errors->first('schedule', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
                <label for="expire_date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.expiry_on') }}</label>
                <div class="col-sm-5 col-lg-3 controls">
                     <div class="input-group date">
                        <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="expire_date" class="form-control datepicker" readonly='readonly' value="{{Input::old('expire_date',Timezone::convertFromUTC('@'.(time()+(15*24*60*60)), Auth::user()->timezone, 'd-m-Y'))}}" style="cursor: pointer">
                    </div>
                    {!! $errors->first('expire_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group"  id= "for_add_ann_id" style="display:none">

                <label class="col-sm-3 col-lg-2 control-label">

                    {{ trans('admin/announcement.send_to') }}

                    <span class="red">*</span>

                </label>

                <div class="col-sm-9 col-lg-10 controls" id="tar_for_id">

                    @if(is_admin_role(Auth::user()->role))

                        <div>

                            <label calss="checkbox" id="public_chk_lbl">
                                <input  type="checkbox" id="public_chk" name="checkbox[]" value="public" @if(Input::old('public_chk') == 'public') checked @endif>
                                &nbsp;{{ trans('admin/announcement.public') }}

                            </label>

                        </div>

                        <div>

                            <label calss="checkbox" id="register_user_chk_lbl">

                                <input  type="checkbox" id="register_user_chk" name="checkbox[]" value="registerusers" @if(Input::old('register_user_chk') == 'registerusers') checked @endif>

                                &nbsp;{{ trans('admin/announcement.all_registered_users') }}

                            </label>

                        </div>

                    @endif

                @if(has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USER))

                    <div>

                        <label calss="checkbox" id="speci_user_chk_lbl">

                            <input  type="checkbox" id="speci_user_chk" name = "checkbox[]" value="users"> &nbsp;{{ trans('admin/announcement.specific_users') }}

                           
                                <span id='add_user_div' style="display:none" >

                                    @if(isset($userCount))

                                        {!! $userCount !!}

                                    @endif

                                </span>
                            <input type="hidden" name="user" value="">
                        </label>

                    </div>
                @endif

                @if(has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_USERGROUP))
                    <div>

                        <label calss="checkbox" id="speci_ug_chk_lbl">

                            <input  type="checkbox" id="speci_ug_chk" name = "checkbox[]" value="usergroup">

                            &nbsp;{{ trans('admin/announcement.specific_user_groups') }}

                                <span  id='add_usergroup_div' style="display:none">

                                    @if(isset($userGroupCount))

                                        {!! $userGroupCount !!}

                                    @endif

                                </span>
                            <input type="hidden" name="usergroup" value="">
                       </label>

                    </div>
                @endif

                @if(has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_CHANNEL))
                    <div>

                       <label calss="checkbox" id="cf_user_chk_lbl">

                            <input  type="checkbox" id="cf_user_chk" name = "checkbox[]" value="cfusers">

                            &nbsp;<?php echo trans('admin/program.program');?>s

                          

                                <span  id='add_cf_div' style="display:none">

                                    @if(isset($contentfeed))

                                        {!! $contentfeed !!}

                                    @endif

                                </span>

                            

                           <input type="hidden" name="contentfeed" value="">

                       </label>

                    </div>

                    {!! $errors->first('checkbox', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                @endif
                </div>

            </div>

            <div class="form-group" id="add_media" style="display:none">
                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.add_media') }} </label>
                <div class="col-sm-9 col-lg-10 controls">
                    <div class="fileupload fileupload-new">
                        <div class="fileupload-new img-thumbnail" style="width: 200px;padding:0">
                            <?php if (Input::old('banner')) { ?>
                                <img src="{{URL::to('/cp/dams/show-media/'.Input::old('banner'))}}" width="100%" alt="" id="bannerplaceholder"/>
                            <?php } else { ?>
                                <img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" id="bannerplaceholder"/>
                            <?php } ?>
                        </div>
                        <div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                        <div>
                            <button class="btn" type="button" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&filter=all&select=radio&from=announcement')}}">{{ trans('admin/announcement.select_from_media_library') }}</button>
                            <?php
                            if (Input::old('banner')) { ?>
                                <button class="btn btn-danger" type="button" id="removethumbnail"> {{ trans('admin/announcement.remove') }} </button>
                            <?php  }
                            ?>
                            <input type="hidden" name="banner" value="{{(Input::old('banner')) ? Input::old('banner') : ""}}" >
                        <!-- <span class="fileupload-exists">Change</span>
                        <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a> -->
                        </div>
                    </div>
                    {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <input type="hidden" name="announce_id_holder" value="{{Input::old('announce_id_holder')}}">

            <input id="statusmode" type="hidden" name="status_mode" value="ACTIVE">
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.notify_by_mail') }}</label>
                <div class="col-sm-9 col-lg-10 controls">
                    <input id="mail_notify_id" type="checkbox" class="checkbox" value="on" name="mail_notify" >
                </div>
            </div>
            <div class="form-group last" id="publish_btn" style="display:none">
                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary">{{ trans('admin/announcement.publish') }}</button>
                    <button type="button" class="btn btn-primary" id="draft">{{ trans('admin/announcement.save_as_draft') }}</button>
                    <a href="{{URL::to('/cp/announce/')}}" ><button type="button" class="btn">{{ trans('admin/announcement.cancel') }}</button></a>
                </div>
                <!-- <a href="#modal-1" role="button" class="btn" data-toggle="modal">Basic modal</a> -->
            </div>
        </div>
    </div>

<!-- BEGIN MODALS -->
    <div id="add-user" class="modal fade">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="myModalLabel">{{ trans('admin/announcement.add_user') }}</h3>
                </div>
                <div class="modal-body">
                    <p>{{ trans('admin/announcement.please_enter_list') }}</p>
                    <input type="text" name="user_t" class="form-control" id='get_userlist' value="" />
                    <br><br>
                    <div class="box">
                        <div class="box-title">
                             <h3 style="color:black"><i class="fa fa-file"></i> {{ trans('admin/announcement.added_user_list') }} </h3>
                        </div>
                        <div class="box-content">
                            <ul id="need_to_selet">
                                <!-- <li onclick="calljsfun(this)">list 1</li>
                                <li onclick="calljsfun(this)">list 2</li>
                                <li onclick="calljsfun(this)">list 3</li> -->
                            </ul>
                        </div>
                    </div>
                    <div class="box">
                        <div class="box-title">
                             <h3 style="color:black"><i class="fa fa-file"></i> {{ trans('admin/announcement.added_user_list') }} </h3>
                        </div>
                        <div class="box-content">
                          <ul  id="need_to_seleted">

                          </ul>
                        </div>
                    </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">{{ trans('admin/announcement.close') }}</button>
                    <button class="btn btn-primary" data-dismiss="modal">{{ trans('admin/announcement.save_close') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <div class="row">
                            <div class="col-md-12">
                                <div class="box">
                                    <div class="box-title">
                                        <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                        <h3 class="modal-header-title">
                                            <i class="icon-file"></i>
                                        </h3>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-assign" style="position: relative; z-index: 9; right: 46px; top: 8px; margin-left: 600px">
                    </div>
                    <div class="modal-body">
                        ...
                    </div>
                    <div class="modal-footer">
                        <div style="float: left;" id="selectedcount"> 0 selected</div>
                        <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/announcement.assign') }}</a>
                        <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/announcement.close') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">

        CKEDITOR.replace( 'addcont', {
            filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
        });
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;

</script>
<script type="text/javascript">
    var permision_chk = "no";
    var announce_id = 0;
    var announce_create_status = "no";
    var announce_type_js = "General";
    var cf_on = "no";
    var tar_people_js = "";
    var cf_user = "";
    var flag = false;

    $("#mail_notify_id").attr('disabled',true);

    <?php
    if (!is_null(Input::old('announce_id_holder')) && Input::old('announce_id_holder')>0) {
        ?>
        permision_chk='yes';
        announce_create_status='yes';
        announce_id={{Input::old('announce_id_holder')}};
        $("#add_media").show();
        $("#publish_btn").show();
        $("#for_add_ann_id").show();
        flag = true;
        <?php
    } else {
        ?>
        permision_chk="no";
        announce_create_status="no";
        announce_id=0;
        <?php
    }
    if (!is_null(Input::old('announcement_type')) && !empty(Input::old('announcement_type'))) {
        ?>
       announce_type_js = "{{Input::old('announcement_type')}}";
        <?php
    } ?>
        <?php
            $tar_people = "";
        if (!is_null(Input::old('checkbox')) && !empty(Input::old('checkbox'))) {
            foreach (Input::old('checkbox') as $key => $value) {
                if ($value == "cfusers") {
                    ?>
                    $("#register_user_chk").prop('checked', false);
                    $("#public_chk").prop('checked', false);
                    $("#cf_user_chk").prop('checked',true);
                    $('#add_cf_div').show();
                    <?php
                } elseif ($value == "public") {
                    ?>
                    $("#public_chk").prop('checked', true);
                    $("#register_user_chk").prop('checked', false);
                    $("#speci_user_chk").prop('checked', false);
                    $("#speci_ug_chk").prop('checked', false);
                    $("#cf_user_chk").prop('checked',false);
                    $('#add_user_div').hide();
                    $("#add_usergroup_div").hide();
                    $("#add_cf_div").hide();
                    $("#mail_notify_id").prop('checked',false);
                    $("#mail_notify_id").attr('disabled',true);
                    <?php
                    // $tar_people = "public";
                } elseif ($value == "registerusers") {
                    ?>
                    $("#register_user_chk").prop('checked', true);
                    $("#public_chk").prop('checked', false);
                    $("#speci_user_chk").prop('checked', false);
                    $("#speci_ug_chk").prop('checked', false);
                    $("#cf_user_chk").prop('checked',false);
                    $("#add_user_div").hide();
                    $("#add_usergroup_div").hide();
                    $("#add_cf_div").hide();
                    <?php
                 // $tar_people = "register users";
                } elseif ($value == "users") {
                    ?>
                    $("#speci_user_chk").prop('checked', true);
                    $("#register_user_chk").prop('checked', false);
                    $("#public_chk").prop('checked', false);
                    $('#add_user_div').show();
                    <?php
                    // $tar_people .= "users";
                } elseif ($value == "usergroup") {
                    ?>
                    $("#speci_ug_chk").prop('checked', true);
                    $("#register_user_chk").prop('checked', false);
                    $("#public_chk").prop('checked', false);
                    $('#add_usergroup_div').show();
                    <?php
                    // $tar_people .= "usergroup";
                }
            }
        }
        if ($tar_people != "") {
            ?>
            tar_people_js ="{{$tar_people}}";
            // console.log(tar_people_js);
            <?php
        }
        ?>
        $(document).ready(function() {
            $('#alert-success').delay(5000).fadeOut();
            $(window).keydown(function(event){
                if(event.keyCode == 13) {
                  event.preventDefault();
                  return false;
                }
            });
               $('.datepicker').datepicker({
                    format : "dd-mm-yyyy",
                    startDate: '+0d'
                })

            $("#announce_title_id").focusout(function(){
                if(announce_create_status=='yes'){
                    return false;
                }
                // console.log("on the way");
                var announce_title=$(this).val();
                announce_title = $.trim(announce_title);
                var inputVal = $(this).val();
                // var characterReg = /^([a-zA-Z0-9 \-_&%\.\'\"(),]{5,200})$/;
                if(inputVal.length < 5) {
                   $('#validate_client').html('');
                   $(this).after('<span id="validate_client" class="error error-keyup-3 alert-danger">Minimum 5 characters.</span>');
                }else{
                    $("#validate_client").hide();
                  if(announce_title !="" && (announce_title.length)>4 && announce_create_status=='no')
                  {
                    $.ajax({
                                type: "GET",
                                url: '{{URL::to('/cp/announce/create-announcement')}}',
                                data: 'announcement_title='+announce_title
                            })
                            .done(function( response ) {
                                if(response.flag == "success"){
                                    announce_create_status="yes";
                                    $('<div class="alert alert-success" id="alert-success"><button class="close" data-dismiss="alert">×</button> Announcement successfully created with status pending</div>').insertAfter($('.page-title'));
                                    announce_id=response.announcement_id;
                                    $('input[name=announce_id_holder]').val(announce_id);
                                    permision_chk='yes';
                                    $('#publish_btn').show();
                                    $("#add_media").show();
                                    $("#for_add_ann_id").show();
                                    $("#form-addannouncement").prop('action',"{{URL::to('cp/announce/upload-announcement/')}}"+"/"+response.announcement_id);
                                }
                                else{
                                    $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button>  <?php echo trans('admin/manageweb.server_error');?>/div>').insertAfter($('.page-title'));
                                }
                            })
                            .fail(function() {
                                alert("error");
                                 permision_chk='no';
                            })
                  }else{
                    permision_chk='no';
                  }
                }//console.log(permision_chk);
            });
            //Send to slect process
            $('#public_chk').change(function(){
                $("#register_user_chk").prop('checked', false);
                $("#speci_user_chk").prop('checked', false);
                $("#speci_ug_chk").prop('checked', false);
                $("#cf_user_chk").prop('checked',false);
                $('#add_user_div').hide();
                $("#add_usergroup_div").hide();
                $("#add_cf_div").hide();
                $("#mail_notify_id").prop('checked',false);
                $("#mail_notify_id").attr('disabled',true);
            });
            $('#register_user_chk').change(function(){
                $("#public_chk").prop('checked', false);
                $("#speci_user_chk").prop('checked', false);
                $("#speci_ug_chk").prop('checked', false);
                $("#cf_user_chk").prop('checked',false)
                $('#add_user_div').hide();
                $("#add_usergroup_div").hide();
                $("#add_cf_div").hide();
                $("#mail_notify_id").attr('disabled',false);
            });
            $('#speci_user_chk').change(function(){
                $("#mail_notify_id").attr('disabled',false);
                if($(this).is(':checked')){
                    // console.log("inside the use");
                    $("#register_user_chk").prop('checked', false);
                    $("#public_chk").prop('checked', false);
                    $('#add_user_div').show();
                }else{
                    // console.log("outside the use");
                    $('#add_user_div').hide();
                }
            });
             $('#speci_ug_chk').change(function(){
                $("#mail_notify_id").attr('disabled',false);
                if($(this).is(':checked')){
                    $("#register_user_chk").prop('checked', false);
                    $("#public_chk").prop('checked', false);
                    $('#add_usergroup_div').show();
                }else{
                    $("#add_usergroup_div").hide();
                }

            });
            $('#cf_user_chk').change(function(){
                $("#mail_notify_id").attr('disabled',false);
                if($(this).is(':checked')){
                    $("#register_user_chk").prop('checked', false);
                    $("#public_chk").prop('checked', false);
                    $('#add_cf_div').show();
                }else{
                    $("#add_cf_div").hide();
                }
            });

            $("#selectuser").click(function(){
                if(permision_chk=='yes'){
                    $("#add_user_div").show();
                }
            });
            $("#draft").click(function(){
                $("#statusmode").val("DRAFT");
                $("#form-addannouncement").submit();
            });
            $.fn.alreadyexist= function(chk_user_exist) {
                var childs = $("#need_to_selet").children();
                var is_exist=false;
                for(var j=0;childs.length > j;j++){
                   if( childs[j].textContent == chk_user_exist){
                        is_exist=true;
                    }
                }
                if(is_exist==false){
                    $("#need_to_selet").append(' <li onclick="calljsfun(this)">'+chk_user_exist+'</li>')
                }
                return;
            }
            $('.damsrel').click(function(e){
                // console.log(announce_id);
                $('#selectedcount').show();
                    e.preventDefault();
                    simpleloader.fadeIn();
                    var $this = $(this);
                    var $triggermodal = $('#triggermodal');
                    var $iframeobj;
                    if($this.data('info') == "user" || $this.data('info') == "usergroup"  ){
                         $iframeobj = $('<iframe src="'+$this.prop('href')+announce_id+'" width="100%" height="" frameBorder="0"></iframe>');

                    }else{
                        $iframeobj = $('<iframe src="'+$this.prop('href')+'" width="100%" height="" frameBorder="0"></iframe>');

                    }

                    $iframeobj.unbind('load').load(function(){

                        //css code for the alignment
                        var a = $('#triggermodal .modal-content .modal-body iframe').get(0).contentDocument;
                        if($(a).find('.box-content select.form-control').parent().parent().find('label').is(':visible')){
                            // $('#triggermodal').find('.modal-body').css({"top":"-32px"});
                            $('#triggermodal .modal-assign').css({"top": "17px"});
                        }
                        else{
                          $('#triggermodal .modal-assign').css({"top": "10px"});
                          $('#triggermodal').find('.modal-body').css({"top":"10px"});
                        }
                        //code ends here


                        $('#selectedcount').text('0 selected');
                        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                            $triggermodal.modal('show');
                            simpleloader.fadeOut();

                        /* Code to Set Default checkedboxes starts here*/
                        $.each($this.data('json'),function(index,value){
                            $iframeobj.get(0).contentWindow.checkedBoxes[value] = "";
                        })
                        /* Code to Set Default checkedboxes ends here*/

                        /* Code to refresh selected count starts here*/
                        $iframeobj.contents().click(function(){
                            setTimeout(function(){
                                var count = 0;
                                $.each($iframeobj.get(0).contentWindow.checkedBoxes,function(){
                                    count++;
                                });
                                $('#selectedcount').text(count+ ' selected');
                            },10);
                        });
                        $iframeobj.contents().trigger('click'); // Trigger the assigned event once on load
                        /* Code to refresh selected count ends here*/
                    })
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.data('text'));

                      //code for top assign button click
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
                    //code for top assign button ends here

                    $('.modal-footer .btn-success,.modal-footer .btn-primary',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes;
                        var $postdata = "";
                        var $radiobtnval="";
                        var $forjson=[];
                        if($this.data('info') =="media"){
                            $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked').val();
                            if(!$.isEmptyObject($selectedRadio)){
                                if($selectedRadio.length>0){
                                 $postdata = $selectedRadio;
                                 $forjson.push($postdata);
                                }
                            }else{
                                $postdata = "";
                            }

                        }else{
                            $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                            $postdata = "";

                            if(!$.isEmptyObject($checkedboxes)){
                                $.each($checkedboxes,function(index,value){
                                    $forjson.push(index);
                                    if(!$postdata)
                                        $postdata += index;
                                    else
                                        $postdata += "," + index;
                                });
                                // console.log($postdata);
                            }
                        }
                            // Post to server
                            var action = $this.data('info');
                            // console.log("forjson"+$forjson);
                            $this.data('json',$forjson);
                            if(action  == "user"){
                                $( "input[name='user']" ).val($forjson);
                                if($forjson.length >1){
                                    $this.text($forjson.length+" Selected Users ");
                                }else{
                                   $this.text($forjson.length+" Selected User ");
                                }
                            }else if(action  == "usergroup"){
                                $( "input[name='usergroup']" ).val($forjson);
                                if($forjson.length >1){
                                    $this.text($forjson.length+" Selected User Groups ");
                                }else{
                                   $this.text($forjson.length+" Selected User Group ");
                                }
                            }else if(action  == "contentfeed"){
                                $( "input[name='contentfeed']" ).val($forjson);
                                if($forjson.length >1){
                                    $this.text($forjson.length+" Selected Channels ");
                                }else{
                                   $this.text($forjson.length+"  Selected Channel ");
                                }
                            }
                            simpleloader.fadeIn();
                            $triggermodal.modal('hide');
                            simpleloader.fadeOut(200);
                           /* $.ajax({
                                type: "POST",
                                url: '{{URL::to('/cp/announce/assign-announcement/')}}/'+action+'/'+announce_id+'/'+false,
                                data: 'ids='+$postdata+"&empty=true"
                            })
                            .done(function( response ) {
                                if(response.flag == "success")
                                    $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><strong>{{ trans('admin/announcement.success') }}</strong> Announcement successfully assigned</div>').insertAfter($('.page-title'));
                                else
                                    $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>{{ trans('admin/announcement.error') }}</strong> <?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                                $triggermodal.modal('hide');
                                setTimeout(function(){
                                    $('.alert').alert('close');
                                },5000);
                                // window.datatableOBJ.fnDraw(true);
                                simpleloader.fadeOut(200);
                            })
                            .fail(function() {
                                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>{{ trans('admin/announcement.error') }}</strong> <?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                                // window.datatableOBJ.fnDraw(true);
                                simpleloader.fadeOut(200);
                            })*/
                    })
                });
                $('#selectfromdams').click(function(e){
                    $('#selectedcount').hide();
                    e.preventDefault();
                    simpleloader.fadeIn();
                    var $this = $(this);
                    var $triggermodal = $('#triggermodal');
                    var $iframeobj = $('<iframe src="'+$this.data('url')+'" width="100%" height="" style="max-height:500px !important" frameBorder="0"></iframe>');
                    $iframeobj.unbind('load').load(function(){
                        if(!$triggermodal.data('bs.modal') || !$triggermodal.data('bs.modal').isShown)
                            $triggermodal.modal('show');
                        simpleloader.fadeOut();
                    });
                     //code for top assign button click
                    $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                        $(this).parents().find('.modal-footer .btn-success').click();
                    });
                    //code for top assign button ends here
                                       //code for top assign button click
                   $('.modal-assign .btn-success',$triggermodal).unbind('click').click(function(){
                       $(this).closest('#triggermodal').find('.modal-footer .btn-success').trigger('click');
                   });
                   //code for top assign button ends here


                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());
                    $('.modal-footer .btn-success,.modal-footer .btn-primary',$triggermodal).unbind('click').click(function(){
                        var $selectedRadio = $iframeobj.contents().find('#datatable input[type="radio"]:checked');
                        if($selectedRadio.length){
                            $('#bannerplaceholder').attr('src','{{URL::to('/cp/dams/show-media/')}}/'+$selectedRadio.val()).width("100%");
                            $('#removethumbnail').remove();
                            $('<button class="btn btn-danger" type="button" id="removethumbnail"> Remove </button>').insertBefore($('input[name="banner"]').val($selectedRadio.val()));
                            $triggermodal.modal('hide');
                        }
                        else{
                            alert('Please select atleast one entry');
                        }
                    });
                });
            $(document).on('click','#removethumbnail',function(){
                $('#bannerplaceholder').attr('src','');
                $('#bannerplaceholder').attr('src', '{{URL::asset("admin/img/demo/200x150.png")}}');
                $('input[name="banner"]').val('');
                $(this).remove();
            });
        });
        function calljsfun(ele){
            var newli = document.createElement('li');
            newli.setpropibute("onclick","calljsremovefun(this)");
            newli.innerHTML = ele.innerHTML;
            var ni = document.getElementById('need_to_seleted');
            ni.appendChild(newli);
            ele.remove();
        }
        function calljsremovefun(ele){
            var newli = document.createElement('li');
            newli.innerHTML = ele.innerHTML;
            var ni = document.getElementById('need_to_selet');
            ni.appendChild(newli);
            ele.remove();
        }

    </script>
@stop