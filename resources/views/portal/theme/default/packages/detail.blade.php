@section('content')
<div class="page-bar">
    <ul class="page-breadcrumb">
        <li>
            <a href="{{url('/dashboard')}}">Dashboard</a><i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="{{url('program/my-feeds')}}">My Course</a><i class="fa fa-angle-right"></i>
        </li>
        <li>
            <a href="#">{{ $package->package_title }}</a>
        </li>
    </ul>
</div>
<div class="row col-lg-10">
    <div class="panel-group accordion" id="accordion3">
            <div class="panel panel-default transparent-bg">
                <div class="panel-heading">
                    <h4 class="panel-title m-btm-12">
                        <div class="row">
                            <div class="col-md-9 col-sm-9">
                                <span class="caption gray">{{ $package->package_title }}</span>
                            </div>
                            <div class="col-md-3 col-sm-3">
                                <a class="accordion-toggle accordion-toggle-styled" data-toggle="collapse" data-parent="#accordion3" href="#collapse_1" aria-expanded="true"><!-- <span class="badge badge-roundless badge-success">NEW</span> --></a>
                            </div>
                        </div>
                    </h4>
                </div>
                <div id="collapse_1" class="panel-collapse collapse in" aria-expanded="true">
                    <div class="panel-body">
                        <div class="col-lg-4 col-md-4 col-sm-6 col-xs-12 xs-margin">
                            @if(!empty(array_get($package, 'package_cover_media', '')))
                                <img src="{{ url('media_image/'.$package->package_cover_media) }}" alt="Package" class="img-responsive">
                            @else
                                <img src="{{ url($theme.'/img/default_channel.png') }}" alt="Default Image" class="img-responsive">
                            @endif
                        </div>
                        @if(!empty(array_get($package, 'package_description', '')))
                            <h6>
                                <strong>{{trans('package.description')}}</strong>
                            </h6>
                            <p style="word-wrap: break-word;">{!! $package->package_description !!}</p>
                        @endif
                        <div class="font-12 xs-margin">
                            <span class="start">
                                {{ strtoupper(trans('package.starts')) }}
                            </span>&nbsp; 
                            <strong>{{ Timezone::convertFromUTC('@'.$package->package_startdate, Auth::user()->timezone)}}</strong> <br>
                            <span class="end">{{ strtoupper(trans('package.ends')) }}</span>&nbsp; 
                            <strong>
                                {{ Timezone::convertFromUTC('@'.$package->package_enddate, Auth::user()->timezone) }}
                            </strong><br>
                            @if(!empty(array_get($package, 'categories', '')))
                                <strong>
                                    {{ trans('package.categories') }}
                                </strong>
                                {{ implode(', ', $package->categories) }}
                            @endif
                            @if(!empty(array_get($package, 'channels', '')))
                                <span class="start black">{{ trans('package.channels') }}:</span>
                                <?php foreach ($package->channels as $key => $channel) {?>
                                    <strong>
                                        <a style="color:#297076" href="{{ url('program/packets/' . $channel['slug']) }}">
                                            {{ $channel['title'] }}
                                            @if(count($package->channels) > $key+1)
                                            ,
                                            @endif
                                        </a>
                                    </strong>
                                <?php }?>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
    
@stop