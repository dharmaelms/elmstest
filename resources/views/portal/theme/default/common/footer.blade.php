@section('footer')
    <?php

    use App\Model\Faq;
    use App\Model\SiteSetting;
    ?>

    <!-- BEGIN FOOTER -->
    <div class="page-footer-inner">
        <div class="row">
            <div class="col-md-2 col-sm-8 col-xs-12">
            </div>
            <div class="col-md-6 col-sm-8 col-xs-12">
            <span>
            <?php
                if(isset($pages)) {
                    foreach ($pages as $key => $value) {
                      //  echo '<a class="black" href="'.URL::to("/".$value["slug"]).'" title="'.$value["title"].'">'.$value["title"].'</a>&nbsp;|&nbsp;';
                    }
                }
                if(SiteSetting::module('General','faq') == "on") {
                    //echo '<a title="Faq" class="black" href="'.URL::to('/faq').'">FAQ</a>&nbsp;';
                }
                if(!Auth::check()) {
                    //echo '|&nbsp;<a class="black" href="'.URL::to('/contactus').'" >Contact Us</a>';
                }
                ?>
            </span>
            </div>
            <?php
            $links = SiteSetting::module('Socialite','landing_page');
            ?>
            @if(!empty($links) && $links != "off")
                <div class="col-md-4 col-sm-4 col-xs-12">
                    <ul class="social-icons pull-right">

                    </ul>
                </div>
            @endif
        </div>
        <!--div class="row">
             <div class="col-md-2 col-sm-8 col-xs-12">
            </div>
            <div class="col-md-6 col-sm-8 col-xs-12">
                <p>Copyright &copy; {{ config('app.site_name', 'Openlink IT') }} &nbsp;|&nbsp; {{ Lang::get('user.all_rights_reserved') }}</p>
            </div>
            <div class="col-md-4 col-sm-4 col-xs-12">
                <div class="pull-right">
                    Powered by <a href="http://openlink.in/" title="Openlink IT" class="black" target="_blank">{{ Lang::get('user.openlinkit') }}</a>
                </div>
            </div>
        </div-->
    </div>

    <div class="scroll-to-top">
        <i class="icon-arrow-up"></i>
    </div>
    <!-- login modal -->
    <div id="signinreg" class="modal fade" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-body">
                    <ul class="nav nav-tabs">
                        <li class="active">
                            <a href="#signin" data-toggle="tab">{{ trans('user.sign_in') }}</a>
                        </li>
                        <li>
                            <a href="#reg" data-toggle="tab">{{ trans('user.register') }}</a>
                        </li>
                    </ul>
                    <div class="tab-content">
                        <div class="tab-pane fade active in" id="signin">
                            <div class="form-group">
                                <span id="error_text_popup" name="error_text_popup" class="text-danger" style="margin-top: -20px;"></span>
                            </div>
                            <!-- BEGIN FORM-->
                            <form action="#" class="default-form xs-margin" role="form" id="signin_popup" name="signin_popup" method="post">
                                <input type="hidden" name="login_url" id="login_url" value="{{URL::to('auth/login')}}">
                                <input type="hidden" name="email_verification" id="email_verification" value="<?php echo config('app.email_verification'); ?>">
                                <input type="hidden" name="baseurl" id="baseurl" value="{{URL::to('/')}}">
                                <input type="hidden" name="dashboard_url" id="dashboard_url" value="{{URL::to('/dashboard/')}}">
                                <div class="form-group">
                                    <input type="text" class="form-control" id="email" name="email" placeholder="Email/Username">
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="password" name="password" placeholder="Password">
                                </div>

                                <div class="margin-bottom-5">
                                    <button type="submit" class="btn btn-danger btn-block">{{ trans('user.sign_in') }}</button>
                                </div>
                                <p class="margin-bottom-15"><a href="{{URL::to('password/forgot')}}">{{ trans('user.forgot_your_password') }}</a></p>

                                <div class="row">
                                    <?php $sitessets = SiteSetting::module('Socialite')->setting; ?>
                                    @if($sitessets['google'] == "on")
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <a class="btn btn-danger btn-block btn-gplus socialite" href="{{ route('social.login', ['google']) }}"><i class="fa fa-google-plus"></i>{{ trans('user.google') }}</a>
                                        </div>
                                    @endif

                                    @if($sitessets['facebook'] == "on")
                                        <div class="col-md-6 col-sm-6 col-xs-6">
                                            <a class="btn btn-danger btn-block btn-fb socialite" href="{{ route('social.login', ['facebook']) }}"><i class="fa fa-facebook"></i>{{ trans('user.facebook') }}</a>
                                        </div>
                                    @endif
                                </div>
                            </form>
                            <!-- END FORM-->
                        </div>

                        <div class="tab-pane fade" id="reg">
                            <div id="loading-image" style="display: none;">
                                <div class="loading-div">
                                    <img src="{{ URL::to('portal/theme/default/img/loader1.gif') }}" alt="Loader">
                                </div>
                            </div>
                            <!-- BEGIN FORM-->
                            <form action="#" class="default-form" role="form">
                                <input type="hidden" id="register_url" name="register_url" value="{{URL::to('auth/register')}}">

                                <div class="form-group">
                                    <input type="text" class="form-control" id="reg_firstname" name="reg_firstname" placeholder="First Name *">
                                    <span class="text-danger" id="err_reg_firstname" name="err_reg_firstname"></span>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="reg_lastname" nmae="reg_lastname" placeholder="Last Name">
                                    <span class="text-danger" id="err_reg_lastname" name="err_reg_lastname"></span>
                                </div>
                                <div class="form-group">
                                    <input type="text" class="form-control" id="reg_username" name="reg_username" placeholder="Username *">
                                    <span class="text-danger" id="err_reg_username" name="err_reg_username"></span>
                                </div>
                                <div class="form-group">
                                    <input type="email" class="form-control" id="reg_email" name="reg_email" placeholder="Email *">
                                    <span class="text-danger" id="err_reg_email" name="err_reg_email"></span>
                                </div>
                                <div class="form-group">
                                    <input type="tel" class="form-control" id="reg_phone" name="reg_phone" placeholder="Contact Number *">
                                    <span class="text-danger" id="err_reg_phone" name="err_reg_phone"></span>
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="reg_password" name="reg_password" placeholder="Password *" maxlength="24" autocomplete="off"><br>
                                    <div id="passwordDescription">{{ Lang::get('user.password_strength') }}</div>
                                    <div class="passwordStrength-bg"><div id="passwordStrength" class="strength0"></div></div>
                                    <span class="text-danger" id="err_reg_password" name="err_reg_password"></span>
                                </div>
                                <div class="form-group">
                                    <input type="password" class="form-control" id="reg_confpassword" name="reg_confpassword" placeholder="Confirm Password *" maxlength="24" autocomplete="off">
                                    <span class="text-danger" id="err_reg_confpassword" name="err_reg_confpassword"></span>
                                </div>

                                <div class="form-group">
                                    <input type="checkbox" id="terms_and_condition" name="terms_and_condition" checked><a href="{{URL::to('terms-and-conditions')}}" target="_blank">{{ Lang::get('user.terms_condition') }} </a>
                                </div>
                                <div>
                                    <button type="submit" class="btn btn-danger btn-block">{{ Lang::get('user.create_account') }}</button>
                                </div>
                            </form>
                            <!-- END FORM-->
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="{{ asset($theme.'/js/custom-front-end/header_frontend.js')}}"></script>
    <script src="{{ asset($theme.'/js/custom-front-end/pwstrength.js')}}"></script>
    @if(config("app.leadsquared.enabled"))
        <!--LeadSquared Tracking Code Start-->
        <script type="text/javascript" src="//web.mxradon.com/t/Tracker.js"></script>
        <script type="text/javascript">
            pidTracker({{ config("app.leadsquared.pid_tracker") }});
        </script>

        <!--LeadSquared Tracking Code End-->
    @endif

    <!-- END FOOTER -->
@stop
