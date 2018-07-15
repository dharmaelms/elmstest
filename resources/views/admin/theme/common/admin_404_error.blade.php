@section('content')
    <div class="content" style="margin: 0; padding: 20px; text-align:center; font-family:Arial, Helvetica, sans-serif; font-size:14px; color:#666666;">
        <img src="{{URL::asset('admin/img/404.gif')}}">
        <h1>Resource Not Found</h1>
        <p>The requested resource not found</p>
        <p>
            <a href="{{ url('/') }}" style="color: #9caa6d;" >Return Back</a>
        </p>
    </div>
@stop