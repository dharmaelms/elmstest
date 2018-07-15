@section('content')
<style type="text/css">
    .page-content-wrapper{
        width: 100%;
        margin:0px;
    }
    .page-content-wrapper .page-content{
        margin-left: 0px !important;
        background-color: transparent;
        padding-bottom: 0px !important;
    }
    ul#error_id {
        list-style-type: none;
    }
</style>
<div class="row">
    <div class="col-md-offset-3 col-md-6 col-sm-12 col-xs-12 white-bg">
        <div class="portlet box register-panel" style="min-height:510px !important">
            <div class="col-md-12 reg-title">Reset Password</div>
            <div class="clearfix"></div>
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul id='error_id'>
                        {{ $errors->first('password') }}
                    </ul>
                </div>
            @endif
            @if(Session::has('status'))
                </br>
                    <div class="alert alert-success">
                    <!-- <strong>Success!</strong><br> -->
                    @if (config('app.ecommerce'))
                    {{ Session::get('status') }}&nbsp;&nbsp;<a href="#signinreg" data-toggle="modal" style="text-decoration: underline;"><?php echo Lang::get('passwords.reset_login'); ?></a>
                    @else
                    {{ Session::get('status') }}&nbsp;&nbsp;<a href="{{URL::to('/')}}" data-toggle="modal" style="text-decoration: underline;"><?php echo Lang::get('passwords.reset_login'); ?></a>
                    @endif
                    </div>
                {{ Session::forget('status') }}
            @else
                <div class="portlet-body">
                    <form class="form-horizontal" method="POST" action="{{ url('password/reset') }}">
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        <input type="hidden" name="token" value="{{ $token }}">
                        <input type="hidden" name="email" value="{{ $email }}">
                        <div class="sm-margin"></div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="password" class="form-control" name="password" placeholder="Password">
                            </div>
                        </div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="password" class="form-control" name="password_confirmation" placeholder="Confirm Password">
                            </div>
                        </div>
                        <button type="submit" class="btn red-sunglo pull-right">Reset Password</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection