<?php if(isset($socialite_info)) { ?>

                <form action="{{URL::to('cp/sitesetting/save-socialite')}}" method="post" accept-charset="utf-8" class="form-horizontal form-bordered form-row-stripped" id="filltersoptions">
                    <?php $ischecked = $socialite_info['setting']['enabled'];?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.socialite_enabled')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">                               
                                 <input type="checkbox" value="on" id="soc_enabled" name="soc_enabled" <?php echo $ischecked=="on"?"checked":"";?> >
                            </div>
                    </div>
                    <?php $ischecked = $socialite_info['setting']['mobile_app'];?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.socialite_mob_app')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                                <input type="checkbox" value="on" id="soc_app" name="soc_app" <?php echo $ischecked=="on"?"checked":"";?> >
                            </div>
                    </div>
                    <?php $ischecked = $socialite_info['setting']['register'];?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.socialite_register')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                              <input type="checkbox" value="on" id="soc_register" name="soc_register" <?php echo $ischecked=="on"?"checked":"";?> >
                              </div>
                    </div>
                    <?php $ischecked = $socialite_info['setting']['login'];?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.socialite_login')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                               <input type="checkbox" value="on" id="soc_login" name="soc_login" <?php echo $ischecked=="on"?"checked":"";?> >

                            </div>
                    </div>
                    <?php $ischecked = $socialite_info['setting']['facebook'];?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.socialite_facebook')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                               <input type="checkbox" value="on" id="soc_fb" name="soc_fb" <?php echo $ischecked=="on"?"checked":"";?> >                               
                            </div>
                    </div>
                    <?php $ischecked = $socialite_info['setting']['google'];?>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.socialite_google')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                               <input type="checkbox" value="on" id="soc_google" name="soc_google" <?php echo $ischecked=="on"?"checked":"";?> >
                            </div>
                    </div>
                    <div class="form-group">
                            <label class="col-sm-6 col-lg-6 control-label">{{trans('admin/sitesetting.social_landing_page')}}</label>
                            <div class="col-sm-6 col-lg-4 controls">
                               <textarea id="landing_page" name="landing_page" class="form-control">{!! $socialite_info['setting']['landing_page'] !!}</textarea>                               
                            </div>
                    </div>
                    <?php
                        if(has_admin_permission(ModuleEnum::MANAGE_SITE, ManageSitePermission::EDIT_SITESETTING) == true)
                        {
                    ?>
                     <div class="form-group">
                        <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-6">
                           <button type="submit" class="btn btn-info text-right" id="genarelsettingadmin">{{ trans('admin/sitesetting.update') }} </button>
                            <a href="{{URL::to('/cp/sitesetting/')}}" >
                                <button type="button" class="btn">{{ trans('admin/sitesetting.cancel') }}</button>
                            </a> 
                        </div>
                    </div>
                    <?php
                         }
                    ?>
                </form> 
            <?php } else {?>
                <p>{{ trans('admin/sitesetting.please_update_your_seeder') }}</p>
            <?php } ?>
  <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
  <script type="text/javascript">
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 100;
    </script>
  </script>