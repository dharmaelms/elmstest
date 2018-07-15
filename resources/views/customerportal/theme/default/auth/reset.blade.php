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
</style>

<div class="row">
    <div class="col-md-offset-3 col-md-6 col-sm-12 col-xs-12 white-bg">
        <div class="portlet box register-panel" style="min-height:510px !important">
            <div class="col-md-12 reg-title">Reset Password</div>
            <div class="clearfix"></div>
            @if (count($errors) > 0)
                <div class="alert alert-danger">
                    
                    <ul>
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif
            <div class="portlet-body">
                <form class="form-horizontal" method="POST" action="{{URL::to('password/reset')}}">
                    <input type="hidden" name="_token" value="{{ csrf_token() }}">
                    <input type="hidden" name="token" value="{{ $token }}">
                    <div class="sm-margin"></div>
                        <div class="form-group">
                            <div class="col-md-12">
                                <input type="email" class="form-control" name="email" value="{{ old('email') }}" placeholder="Email">
                            </div>
                        </div>
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
        </div>
    </div>
</div>
@endsection