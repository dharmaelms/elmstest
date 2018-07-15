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
            @if (session('status'))
                <div class="alert alert-success">
                    {{ session('status') }}
                </div>
            @endif
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    <ul id="error_id">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="portlet-body">
                <form class="form-horizontal" method="POST" action="{{ url('password/forgot') }}">
                    <div class="sm-margin"></div>
                    <div class="form-group">
                        <div class="col-md-12">
                            <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email address">
                        </div>
                    </div>
                    <button type="submit" class="btn red-sunglo pull-right">SEND PASSWORD RESET LINK</button>
                    @if ((config('app.ecommerce') != true) || (isset($_SERVER['HTTP_REFERER']) && $_SERVER['HTTP_REFERER'] == url('/login')))
                        <a href="{{ url('/') }}"><button type="button" class="btn"> <i class="fa fa-chevron-left"></i> <span> &nbsp;&nbsp;  </span>Back</button></a>
                    @else
                        <a href="{{ url('/') }}#signinreg"><button type="button" class="btn"> <i class="fa fa-chevron-left"></i> <span> &nbsp;&nbsp;  </span>Back</button></a>
                    @endif
                </form>
            </div>
        </div>
    </div>
</div>
@endsection
