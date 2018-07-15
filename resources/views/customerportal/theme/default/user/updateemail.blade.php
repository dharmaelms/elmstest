@section('content')
    @if(Session::get('success'))
        <span class="help-inline" style="color:green">
            <!-- <strong>Success!</strong><br> -->
            {{ Session::get('success') }}
        </span>
        <?php Session::forget('success'); ?>
    @endif
    <div class="panel panel-default">
        <div class="panel-heading"><h1>Update Email</h1></div>
        <div class="panel-body">
            <form  action="{{URL::to('user/update-email/'.$uid)}}" method="POST">
                <div class="form-group">
                    <label class="col-md-4 col-sm-4 control-label">Old Email</label>
                    <div class="col-md-6 col-sm-6">
                        <input class="form-control" value="{{$email}}" disabled/>
                    </div>
                </div>
                <div class="form-group">
                    <label class="col-md-4 col-sm-4 control-label">New Email</label>
                    <div class="col-md-6 col-sm-6">
                        <input type="email" class="form-control" name="email" value="{{ Input::old('email') }}"> 
                        {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        @if ( Session::get('email_exist') )
                          <span class="help-inline" style="color:#f00">{{ Session::get('email_exist') }}</span>
                          <?php Session::forget('email_exist'); ?>
                        @endif
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6 col-md-offset-4 col-sm-6 col-sm-offset-4">
                        <button type="submit" class="btn btn-primary">
                            Update
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>    
@stop