@section('content')
<?php use App\Model\Common; use App\Model\Dam;?>
    @if ( Session::get('success') )
        <div class="alert alert-success">
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
    $cf_user="";
    $tar_people="";
        if(!empty(Input::old('slug')) && isset($announcement)){
            /*echo "on the way";
            die;*/
            $slug = Input::old('slug');
            if(!empty(Input::old('announcement_title')))
            {
                $title=Input::old('announcement_title');
            }
            else
            {
                $title=$announcement['announcement_title'];
            }
            if(!empty(Input::old('announcement_content')))
            {
                $announcement_content=Input::old('announcement_content');
            }else{
                $announcement_content=$announcement['announcement_content'];
            }
            if(!empty(Input::old('checkbox'))){
                echo  trans('admin/announcement.on_the_way');
                foreach (Input::old('checkbox') as $key => $value) {
                    if($value == "cfusers"){
                        $cf_user = "cf users";
                    }elseif ($value == "public") {
                        $tar_people = "public";
                    }elseif ($value == "registerusers") {
                         $tar_people = "register users";
                    }elseif ($value == "users") {
                        $tar_people .= "users";
                    }elseif ($value == "usergroup") {
                        $tar_people .= "usergroup";
                    }else{
                        $tar_people = "";
                    }
                }
            }else{
                if($announcement["announcement_for"] == "cfusers"){
                $cf_user = "cf users";
                }elseif ($announcement["announcement_for"] == "users & cfusers") {
                    $cf_user = "cf users";
                    $tar_people = "users";
                }elseif ($announcement["announcement_for"] == "public") {
                    $tar_people = "public";
                }elseif ($announcement["announcement_for"] == "registerusers") {
                     $tar_people = "register users";
                }elseif ($announcement["announcement_for"] == "users & usergroup") {
                     $tar_people .= "usersusergroup";
                }elseif ($announcement["announcement_for"] == "users") {
                    $tar_people .= "users";
                }elseif ($announcement["announcement_for"] == "usergroup") {
                    $tar_people .= "usergroup";
                }else{
                    $tar_people = "";
                }
            }

            /*if(!empty(Input::old('target_device')))
            {
                $target_device=Input::old('target_device');
            }else{
                $target_device=$announcement['announcement_device'];
            }*/

            if(!empty(Input::old('schedule_date')))
            {
                $schedule=Input::old('schedule_date');
            }else{
                $schedule=$announcement['schedule'];
            }
            if(!empty(Input::old('expire_date')))
            {
                $expire_date=Input::old('expire_date');
            }else{
                $expire_date=Timezone::convertFromUTC('@'.$announcement['expire_date'], Auth::user()->timezone,'d-m-Y');
            }
            if(!empty(Input::old('banner'))){
                $media_assigned = Input::old('banner','');
            }else{
                if(isset($announcement['media_assigned']) && !empty(trim($announcement['media_assigned'])) && $announcement['media_assigned']!= "yet to fix"){
                    $media_assigned = $announcement['media_assigned'];
                }elseif(isset($announcement['relations']['active_media_announcement_rel']) && !empty($announcement['relations']['active_media_announcement_rel']) )
                {
                    $asset = Dam::getDAMSMediaUsingID($announcement['relations']['active_media_announcement_rel'][0]);
                    $media_assigned = $asset[0]['_id'];
                }else{
                    $media_assigned = '';
                }
            }
            if(!empty(Input::old('announcement_type')))
            {
                $announcement_type=Input::old('announcement_type');
                if($announcement_type == "Content Feed"){
                   $cf_user = "cf users";
                }

            }else{
                $announcement_type=$announcement['announcement_type'];
                if($announcement_type == "Content Feed"){
                   $cf_user = "cf users";
                }
            }
            if(!empty(Input::old('mail_notify')))
            {
                $mail_notify=Input::old('mail_notify');
            }else{
                if(isset($announcement['notify_mail']) && !empty($announcement['notify_mail'])){
                    $mail_notify=$announcement['notify_mail'];
                }else{
                    $mail_notify="off";
                }
            }
        }
        elseif(isset($announcement)){
            // $mail_notify = $announcement['notify_mail'];
            if(isset($announcement['notify_mail']) && !empty($announcement['notify_mail'])){
                $mail_notify=$announcement['notify_mail'];
            }else{
                $mail_notify="off";
            }
            $title=$announcement['announcement_title'];
            $announcement_content=$announcement['announcement_content'];
            $schedule=$announcement['schedule'];
            $announcement_type=$announcement['announcement_type'];
            $slug =$announcement['announcement_id'];
            $for=$announcement['announcement_for'];
            // $target_device=$announcement['announcement_device'];
            $expire_date=Timezone::convertFromUTC('@'.$announcement['expire_date'], Auth::user()->timezone,'d-m-Y');

            if(isset($announcement['media_assigned']) && !empty(trim($announcement['media_assigned'])) && $announcement['media_assigned']!= "yet to fix"){
                $media_assigned = $announcement['media_assigned'];

            }elseif(isset($announcement['relations']['active_media_announcement_rel']) && !empty($announcement['relations']['active_media_announcement_rel']) )
            {
                // $media_assigned = $announcement['media_assigned'];
                $asset = Dam::getDAMSMediaUsingID($announcement['relations']['active_media_announcement_rel'][0]);
                $media_assigned = $asset[0]['_id'];
            }else{
                $media_assigned = '';
            }

            if($announcement["announcement_for"] == "cfusers"){
                $cf_user = "cf users";
            }elseif ($announcement["announcement_for"] == "users & cfusers") {
                $cf_user = "cf users";
                $tar_people = "users";
            }elseif ($announcement["announcement_for"] == "public") {
                $tar_people = "public";
            }elseif ($announcement["announcement_for"] == "registerusers") {
                $tar_people = "register users";
            }elseif ($announcement["announcement_for"] == "users & usergroup") {
                $tar_people .= "usersusergroup";
            }elseif ($announcement["announcement_for"] == "users") {
                $tar_people .= "users";
            }elseif ($announcement["announcement_for"] == "usergroup") {
                $tar_people .= "usergroup";
            }else{
                $tar_people = "";
            }

            if($announcement_type == "Content Feed"){
                   $cf_user = "cf users";
                }
        }
        if(isset($rel)){
         // $rel=$announcement[0]['relations'];
            if(isset($rel['active_user_announcement_rel']) && !empty($rel['active_user_announcement_rel']))
                $userCount = "<a href='".URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel  btn btn-primary' data-key='".$slug."' data-info='user' data-text='Assign this Announcement to User' data-json='".json_encode($rel['active_user_announcement_rel'])."'>".count($rel['active_user_announcement_rel']).' '.trans('admin/announcement.selected_users'). "</a><input type='hidden' name='user' value='".implode(',',$rel['active_user_announcement_rel'])."'>";
            else
                $userCount = "<a href='".URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel  btn btn-primary' data-key='".$slug."' data-info='user' data-text='Assign this Announcement to User' data-json=''>". 0 .' '.trans('admin/announcement.selected_users')."</a><input type='hidden' name='user' value=''>";

            if(isset($rel['active_usergroup_announcement_rel']) && !empty($rel['active_usergroup_announcement_rel']))
                $userGroupCount = "<a href='".URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug ."' data-info='usergroup' data-text='Assign this Announcement to User Group' data-json='".json_encode($rel['active_usergroup_announcement_rel'])."'>".count($rel['active_usergroup_announcement_rel']).' '.trans('admin/announcement.selected_user_groups')."</a><input type='hidden' name='usergroup' value='".implode(',',$rel['active_usergroup_announcement_rel'])."'>";
            else
                $userGroupCount = "<a href='".URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug."'data-info='usergroup' data-text='Assign this Announcement to User Group' data-json=''>". 0 .' '.trans('admin/announcement.selected_user_groups'). "</a><input type='hidden' name='usergroup' value=''>";
            if(isset($rel['active_media_announcement_rel']) && !empty($rel['active_media_announcement_rel']))
                $media = "<a href='".URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio')."' class='damsrel badge btn btn-primary' data-key='".$slug ."' data-info='media' data-text='Assign this Announcement to Media' data-json='".json_encode($rel['active_media_announcement_rel'])."'>".count($rel['active_media_announcement_rel'])." Selected media</a>";
            else
                $media = "<a href='".URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='media' data-text='Assign this Announcement to Media' data-json=''>". 0 ." Selected media</a>";
            if(isset($rel['active_contentfeed_announcement_rel']) && !empty($rel['active_contentfeed_announcement_rel']))
                $contentfeed = "<a href='".URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&from=announcement')."' class='damsrel btn btn-primary' data-key='".$slug ."' data-info='contentfeed' data-text='Assign this Announcement to ".trans('admin/program.program')."' data-json='".json_encode($rel['active_contentfeed_announcement_rel'])."'>".count($rel['active_contentfeed_announcement_rel']).' '.trans('admin/announcement.selected_channel'). "</a><input type='hidden' name='contentfeed' value='".implode(',',$rel['active_contentfeed_announcement_rel'])."'>";
            else
                $contentfeed = "<a href='".URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&from=announcement')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='contentfeed' data-text='Assign this Announcement to ".trans('admin/program.program')."' data-json=''>". 0 .' '.trans('admin/announcement.selected_channel'). "</a><input type='hidden' name='contentfeed' value=''>";
            /*if(isset($rel['active_event_announcement_rel']) && !empty($rel['active_event_announcement_rel']))
                $event = "<a href='".URL::to('/cp/event?filter=ACTIVE&view=iframe')."' class='damsrel btn btn-primary' data-key='".$slug ."' data-info='event' data-text='Assign this Announcement to Event' data-json='".json_encode($rel['active_event_announcement_rel'])."'>".count($rel['active_event_announcement_rel'])."</a><input type='hidden' name='event' value=".implode(',',$rel['active_event_announcement_rel']).">Add Even</a>";
            else
                $event = "<a href='".URL::to('/cp/event?filter=ACTIVE&view=iframe')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='event' data-text='Assign this Announcement to Event' data-json=''>". 0 ."</a><input type='hidden' name='event' value=''>Add Event</a>";
            */
        }else{
            $userCount = "<a href='".URL::to('/cp/usergroupmanagement?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='user' data-text='Assign this Announcement to User' data-json=''>". 0 .' '.trans('admin/announcement.selected_users'). "</a><input type='hidden' name='user' value=''>";
            $userGroupCount = "<a href='".URL::to('/cp/usergroupmanagement/user-groups?filter=ACTIVE&view=iframe&from=announcement&relid=')."' class='damsrel btn btn-primary' data-key='".$slug."'data-info='usergroup' data-text='Assign this Announcement to User Group' data-json=''>". 0 .' '.trans('admin/announcement.selected_user_groups'). "</a><input type='hidden' name='usergroup' value=''>";
            // $media = "<a href='".URL::to('/cp/dams?filter=ACTIVE&view=iframe&id=id&select=radio')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='media' data-text='Assign this Announcement to Media' data-json=''>". 0 ."Add Media</a>";
            $contentfeed = "<a href='".URL::to('/cp/contentfeedmanagement/list-feeds?filter=ACTIVE&view=iframe&from=announcement')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='contentfeed' data-text='Assign this Announcement to ".trans('admin/program.program')."' data-json=''>". 0 .' '.trans('admin/announcement.selected_channel')."</a><input type='hidden' name='contentfeed' value=''>";
            // $event = "<a href='".URL::to('/cp/event?filter=ACTIVE&view=iframe')."' class='damsrel btn btn-primary' data-key='".$slug."' data-info='event' data-text='Assign this Announcement to Event' data-json=''>". 0 ."Add Event</a><input type='hidden' name='event' value=''>";
        }
?>
    <?php
        $start    =  Input::get('start', 0);
        $limit    =  Input::get('limit', 10);
        $search   =  Input::get('search','');
        $order_by =  Input::get('order_by','3 desc');
        $status   =  Input::get('status','ACTIVE');
    ?>
   <div class="form-wrapper">
        <form action="{{URL::to('cp/announce/edit-load/'.$slug)}}" class="form-horizontal form-bordered form-row-stripped" method="post" id="form-addannouncement" style="margin-top:10px;" enctype='multipart/form-data' >
        <input type="hidden" name="slug" value="{{$slug}}">
        <div class="box">
            <div class="box-title">
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.title') }}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-8 controls">
                    <input id='announce_title_id' type="text" name="announcement_title" class="form-control" value="{{$title}}" />
                    {!! $errors->first('announcement_title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group">
                <label for="announcement_content" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.content') }}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-8 controls">
                    <textarea name="announcement_content" rows="5" id="addcont" class="form-control ckeditor">{!! $announcement_content!!}</textarea>
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
                        <input type="text" name="schedule_date" class="form-control datepicker" readonly='readonly' value="{{$schedule}}" style="cursor: pointer">
                    </div>
                    {!! $errors->first('schedule', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
                <label for="expire_date" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.expiry_on') }}</label>
                <div class="col-sm-5 col-lg-3 controls">
                     <div class="input-group date">
                        <span class="input-group-addon" onclick="$(this).next().focus()"><i class="fa fa-calendar"></i></span>
                        <input type="text" name="expire_date" class="form-control datepicker" readonly='readonly' value="{{$expire_date}}" style="cursor: pointer">
                    </div>
                    {!! $errors->first('expire_date', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>

            <div class="form-group"  id= "for_add_ann_id" >

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

                            <input  type="checkbox" id="speci_user_chk" name = "checkbox[]" value="users">

                            &nbsp;{{ trans('admin/announcement.specific_users') }}

                                <span id='add_user_div' style="display:none" >

                                    @if(isset($userCount))

                                        {!! $userCount !!}

                                    @endif

                                </span>

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

                        </label>

                    </div>
                @endif

                @if(has_admin_permission(ModuleEnum::ANNOUNCEMENT, AnnouncementPermission::ASSIGN_CHANNEL))
                    <div>

                       <label calss="checkbox" id="cf_user_chk_lbl">

                            <input  type="checkbox" id="cf_user_chk" name = "checkbox[]" value="cfusers">&nbsp;<?php echo trans('admin/program.program');?>s

                            <span  id='add_cf_div' style="display:none">

                                @if(isset($contentfeed))

                                    {!! $contentfeed !!}

                                @endif

                            </span>

                       </label>

                    </div>

                    {!! $errors->first('checkbox', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                @endif
                </div>
            </div>
        
            <div class="form-group" id="add_media" >
                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.add_media') }}</label>
                <div class="col-sm-9 col-lg-10 controls">
                    <div class="fileupload fileupload-new">
                        <div class="fileupload-new img-thumbnail" style="width: 200px;padding:0">
                            <?php if(isset($media_assigned) && !empty($media_assigned) && $media_assigned != "yet to fix"){ ?>
                                <img src="{{URL::to('/cp/dams/show-media/'.$media_assigned)}}" width="100%" alt="" id="bannerplaceholder"/>
                            <?php } else{ ?>
                                <img src="{{URL::asset('admin/img/demo/200x150.png')}}" alt="" id="bannerplaceholder"/>
                            <?php } ?>
                        </div>
                        <div class="fileupload-preview fileupload-exists img-thumbnail" style="max-width: 200px; max-height: 150px; line-height: 20px;"></div>
                        <div>
                            <button class="btn" type="button" id="selectfromdams" data-url="{{URL::to('/cp/dams?view=iframe&filter=all&select=radio&from=announcement')}}">{{ trans('admin/announcement.select_from_media_library') }}</button>
                            <?php
                            if(isset($media_assigned) && !empty($media_assigned) && $media_assigned != "yet to fix"){ ?>
                                <button class="btn btn-danger" type="button" id="removethumbnail"> {{ trans('admin/announcement.remove') }} </button>
                            <?php   }
                            ?>
                            <input type="hidden" name="banner" value="{{$media_assigned}}" >
                        <!-- <span class="fileupload-exists">Change</span>
                        <a href="#" class="btn btn-default fileupload-exists" data-dismiss="fileupload">Remove</a> -->
                        </div>
                    </div>
                    {!! $errors->first('banner', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/announcement.notify_by_mail') }}</label>
                <div class="col-sm-9 col-lg-10 controls">
                    <input id="mail_notify_id" type="checkbox" class="checkbox" value="on" name="mail_notify" <?php echo $mail_notify=="on"?"checked":"";?>>
                </div>
            </div>
            <input type="hidden" name="announce_id_holder" value="{{Input::old('announce_id_holder')}}">
       
            <input id="statusmode" type="hidden" name="status_mode" value="ACTIVE">
            <div class="form-group last" id="publish_btn">
                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary">{{ trans('admin/announcement.publish') }}</button>
                    <button type="button" class="btn btn-primary" id="unpublish">{{ trans('admin/announcement.update_and_un_publish') }}</button>
                    <a href="{{URL::to('/cp/announce/')}}?start={{$start}}&limit={{$limit}}&status={{$status}}&search={{$search}}&order_by={{$order_by}}" ><button type="button" class="btn">{{ trans('admin/announcement.cancel') }}</button></a>
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
                             <h3 style="color:black"><i class="fa fa-file"></i>{{ trans('admin/announcement.avilable_users_list') }} </h3>
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
                             <h3 style="color:black"><i class="fa fa-file"></i>{{ trans('admin/announcement.added_users_list') }}</h3>
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
                    <button class="btn btn-primary" data-dismiss="modal">{{ trans('admin/announcement.save_changes') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-2" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="myModalLabel1">{{ trans('admin/announcement.modal_header') }}</h3>
                </div>
                <div class="modal-body">
                    <p>{{ trans('admin/announcement.modal_body') }}</p>

                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">{{ trans('admin/announcement.close') }}</button>
                    <button class="btn btn-primary" data-dismiss="modal">{{ trans('admin/announcement.save_changes') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-3" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="myModalLabel2">{{ trans('admin/announcement.modal_header') }}</h3>
                </div>
                <div class="modal-body">
                    <p>{{ trans('admin/announcement.modal_body') }}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn" data-dismiss="modal" aria-hidden="true">{{ trans('admin/announcement.no') }}</button>
                    <button class="btn btn-primary" data-dismiss="modal">{{ trans('admin/announcement.yes') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div id="modal-4" class="modal fade">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                    <h3 id="myModalLabel3">{{ trans('admin/announcement.modal_header') }}</h3>
                </div>
                <div class="modal-body">
                    <p>{{ trans('admin/announcement.modal_body') }}</p>
                </div>
                <div class="modal-footer">
                    <button class="btn btn-primary" data-dismiss="modal">{{ trans('admin/announcement.ok') }}</button>
                </div>
            </div>
        </div>
    </div>
    <div class="modal fade" id="triggermodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
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
                                                {{ trans('admin/announcement.view_announcement_detail') }}
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
                        <div style="float: left;" id="selectedcount"> 0  selected</div>
                    <a class="btn btn-success"><em class="fa fa-check-circle"></em>&nbsp;{{ trans('admin/announcement.assign') }}</a>
                    <a class="btn btn-danger" data-dismiss="modal"><em class="fa fa-times-circle"></em>&nbsp;{{ trans('admin/announcement.close') }}</a>
                    </div>
                </div>
            </div>
        </div>
    </form>
    </div>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>

    <script type="text/javascript">

        CKEDITOR.replace( 'addcont', {
            filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
        });
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;

</script>
    <script type="text/javascript">
    var announce_type_js = "{{$announcement_type}}";
    var cf_on = "no";
    var tar_people_js = "";
    var cf_user = "";
    var flag = false;
    var announce_id = 0;

    <?php
    if($slug >0){
        ?>
        announce_id = {{$slug}};
        <?php
    }
     if($tar_people != ""){
            ?>
                tar_people_js ="{{$tar_people}}";
                // console.log(tar_people_js+"==>tar_people_js");
            <?php
            }
            if($cf_user != ""){
            ?>
                cf_on = 'yes';
                cf_user ="{{$cf_user}}";
                // console.log(cf_user+"::cf_user");
            <?php
            }
            ?>

       </script>
        <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
        <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
        <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
        <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
        <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
        <script type="text/javascript">
            $(window).load(function(){
            <?php
                $for_send_to = explode("&", $announcement["announcement_for"]);
                foreach ($for_send_to as $key => $value) {
                    if(trim($value) == "cfusers"){
                        ?>
                        $("#register_user_chk").prop('checked', false);
                        $("#public_chk").prop('checked', false);
                        $("#cf_user_chk").prop('checked',true);
                        $('#add_cf_div').show();
                        <?php
                    }elseif (trim($value) == "public") {
                        ?>
                        $("#public_chk").prop('checked', true);
                        $("#register_user_chk").prop('checked', false);
                        $("#speci_user_chk").prop('checked', false);
                        $("#speci_ug_chk").prop('checked', false);
                        $("#cf_user_chk").prop('checked',false);
                        $('#add_user_div').hide();
                        $("#add_usergroup_div").hide();
                        $("#add_cf_div").hide();
                        $("#mail_notify_id").attr('disabled',true);
                        $("#mail_notify_id").prop('checked',false);
                        <?php
                        // $tar_people = "public";
                    }elseif (trim($value) == "registerusers") {
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
                    }elseif (trim($value) == "users") {
                        ?>
                        $("#speci_user_chk").prop('checked', true);
                        $("#register_user_chk").prop('checked', false);
                        $("#public_chk").prop('checked', false);
                        $('#add_user_div').show();
                        <?php
                        // $tar_people .= "users";
                    }elseif (trim($value) == "usergroup") {
                        ?>
                        $("#speci_ug_chk").prop('checked', true);
                        $("#register_user_chk").prop('checked', false);
                        $("#public_chk").prop('checked', false);
                        $('#add_usergroup_div').show();
                        <?php
                        // $tar_people .= "usergroup";
                    }
                }
            ?>
            });
        $(document).ready(function() {
            $('.datepicker').datepicker({
                    format : "dd-mm-yyyy",
                    startDate: '+0d'
                })
             $("#unpublish").click(function(){
                $("#statusmode").val("INACTIVE");
                $("#form-addannouncement").submit();

            });

             $('#public_chk').change(function(){
                // console.log("on the way");
                $("#register_user_chk").prop('checked', false);
                $("#speci_user_chk").prop('checked', false);
                $("#speci_ug_chk").prop('checked', false);
                $("#cf_user_chk").prop('checked',false);
                $('#add_user_div').hide();
                $("#add_usergroup_div").hide();
                $("#add_cf_div").hide();
                $("#mail_notify_id").attr('disabled',true);
                $("#mail_notify_id").prop('checked',false);
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
                    // console.log('announce_id'+announce_id);
                    $('#selectedcount').show();
                    e.preventDefault();
                    simpleloader.fadeIn();
                    var $this = $(this);
                    var $triggermodal = $('#triggermodal');
                     if($this.data('info') == "user" || $this.data('info') == "usergroup"  ){
                         $iframeobj = $('<iframe src="'+$this.prop('href')+announce_id+'" width="100%" height="" frameBorder="0"></iframe>');

                    }else{
                        $iframeobj = $('<iframe src="'+$this.prop('href')+'" width="100%" height="" frameBorder="0"></iframe>');

                    }
                    // var $iframeobj = $('<iframe src="'+$this.attr('href')+announce_id+'" width="100%" height="" frameBorder="0"></iframe>');
                    $iframeobj.unbind('load').load(function(){
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
                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
                        var $checkedboxes = $iframeobj.get(0).contentWindow.checkedBoxes;
                        var $checkedboxes;
                        var $postdata = "";
                        var $radiobtnval="";
                        var $forjson=[];
                        if($this.data('info') =="media"){
                            $('#selectedcount').hide();
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
                            $('#selectedcount').show();
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
                            }
                        }
                            // Post to server
                            var action = $this.data('info');
                            // console.log("forjson"+$forjson);
                            $this.data('json',$forjson);
                            if(action  == "user"){
                                $( "input[name='user']" ).val($forjson);
                                if($forjson.length >1){
                                    $this.text($forjson.length+" Selected  Users ");
                                }else{
                                   $this.text($forjson.length+" Selected  User ");
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
                                   $this.text($forjson.length+" Selected Channel ");
                                }
                            }

                            simpleloader.fadeIn();
                            $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button> Announcement successfully assigned</div>').insertAfter($('.page-title'));
                            $triggermodal.modal('hide');
                            simpleloader.fadeOut(200);


                           /* $.ajax({
                                type: "POST",
                                url: '{{URL::to('/cp/announce/assign-announcement/')}}/'+action+'/'+$this.data('key')+'/'+false,
                                data: 'ids='+$postdata+"&empty=true"
                            })
                            .done(function( response ) {
                                if(response.flag == "success")
                                    $('<div class="alert alert-success"><button class="close" data-dismiss="alert">×</button><strong>{{ trans('admin/announcement.success') }}</strong> Announcement successfully assigned</div>').insertAfter($('.page-title'));
                                else
                                    $('<div class="alreadyexistert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>{{ trans('admin/announcement.error') }}</strong>  <?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                                $triggermodal.modal('hide');
                                setTimeout(function(){
                                    $('.alert').alert('close');
                                },5000);
                                simpleloader.fadeOut(200);
                            })
                            .fail(function() {
                                console.log("wronge side");
                                $('<div class="alert alert-danger"><button class="close" data-dismiss="alert">×</button><strong>{{ trans('admin/announcement.error') }}</strong>  <?php echo trans('admin/manageweb.server_error');?></div>').insertAfter($('.page-title'));
                                simpleloader.fadeOut(200);
                            });*/
                    });
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
                    $triggermodal.find('.modal-body').html($iframeobj);
                    $triggermodal.find('.modal-header-title').html('<i class="icon-file"></i>'+$this.text());
                    $('.modal-footer .btn-success',$triggermodal).unbind('click').click(function(){
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
            newli.setAttribute("onclick","calljsremovefun(this)");
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
