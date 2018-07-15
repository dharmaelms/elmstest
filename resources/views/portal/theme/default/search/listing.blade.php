@foreach($results as $result)
    <li>
        <div class="img-div">
            @if ($result['_type']== ElasticType::PROGRAM)
                @if(!empty($result['_source']['cover_image']))
                    <img src="{{url('media_image/'.$result['_source']['cover_image'].'?thumb=180x180')}}" alt="{{trans('search.channel')}}">&nbsp;  
                @else
                    <img src="{{url($theme.'/img/default_channel.png')}}" alt="{{trans('search.channel')}}">&nbsp;
                @endif
            @endif
            @if ($result['_type']== ElasticType::PACKAGE)
                @if(!empty($result['_source']['cover_image']))
                    <img src="{{url('media_image/'.$result['_source']['cover_image'].'?thumb=180x180')}}" alt="{{trans('search.channel')}}">&nbsp;  
                @else
                    <img src="{{url($theme.'/img/default_channel.png')}}" alt="{{trans('search.channel')}}">&nbsp;
                @endif
            @endif
            @if ($result['_type']== ElasticType::POST)
                @if(!empty($result['_source']['cover_image']))
                    <img src="{{url('media_image/'.$result['_source']['cover_image'].'?thumb=180x180')}}" alt="Post">&nbsp;  
                @else
                    <img src="{{asset($theme.'/img/default_packet.jpg')}}" alt="{{trans('search.post')}}">&nbsp;
                @endif
            @endif
            @if ($result['_type']== ElasticType::ITEM)
                <?php $type = $result['_source']['type']; ?>
                @if($type == 'media')
                    <img src="{{asset($theme.'/img/image-default.png')}}" alt="{{trans('search.'.$result['_source']['type'])}}">&nbsp;
                @elseif($type == 'assessment')
                    <img src="{{asset($theme.'/img/assessment-default.png')}}" alt="{{trans('search.'.$result['_source']['type'])}}">&nbsp;
                @elseif($type == 'event')
                    <img src="{{asset($theme.'/img/packetpage_event.png')}}" alt="{{trans('search.'.$result['_source']['type'])}}">&nbsp;
                @elseif($type == 'flashcard')
                    <img src="{{asset($theme.'/img/assessment-default.png')}}" alt="{{trans('search.'.$result['_source']['type'])}}">&nbsp;
                @endif
            @endif
            @if ($result['_type']== ElasticType::EVENT)
                <img src="{{asset($theme.'/img/packetpage_event.png')}}" alt="{{trans('search.event')}}">&nbsp;  
            @endif
            @if ($result['_type']== ElasticType::ASSESSMENT)
                <img src="{{asset($theme.'/img/assessment-default.png')}}" alt="{{trans('search.assessment')}}">&nbsp;  
            @endif
        </div>
        <div class="data-div">
            @if ($result['_type']== ElasticType::PROGRAM)
                <a href="{{url('/program/packets/'.$result['_source']['slug'])}}" title="{{$result['_source']['title']}}">
                    <strong>{{Helpers::stripString($result['_source']['title'], 0, 200)}}</strong>
                </a>
                @if ($result['_source']['type'] == 'content_feed')
                    <span class="label label-primary label-sm">{{trans('search.channel')}}</span>
                @elseif ($result['_source']['type'] == 'product')
                    <span class="label label-primary label-sm">{{trans('search.product')}}</span>
                @elseif ($result['_source']['type'] == 'course')
                    <span class="label label-primary label-sm">{{trans('search.course')}}</span>
                @endif
                <p class="font-13">
                    @if(array_get($result['_source'], 'short_title', '') != '')
                        <strong>{{trans('search.short_name')}} : </strong>{{ $result['_source']['short_title']}}
                    @endif
                </p>
            @endif
            @if ($result['_type']== ElasticType::PACKAGE)
                <a href="{{url('/package/detail/'.$result['_source']['slug'])}}" title="{{$result['_source']['title']}}">
                    <strong>{{Helpers::stripString($result['_source']['title'], 0, 200)}}</strong>
                </a>
                <span class="label label-primary label-sm">{{trans('search.package')}}</span>
                <p class="font-13">
                    @if(array_get($result['_source'], 'short_title', '') != '')
                        <strong>{{trans('search.short_name')}} : </strong>{{ $result['_source']['short_title']}}
                    @endif
                </p>
            @endif
            @if ($result['_type']== ElasticType::POST)
                <a href="{{url('/program/packet/'.$result['_source']['slug'])}}" title="{{$result['_source']['title']}}">
                    <strong>{{Helpers::stripString($result['_source']['title'], 0, 200)}}</strong>
                </a>
                <span class="label label-primary label-sm">{{trans('search.post')}}</span>
            @endif
            @if ($result['_type']== ElasticType::ITEM)
                <a href="{{url('/program/packet/'.$result['_source']['slug']. '/element/' . $result['_source']['id'] . '/' . $result['_source']['type'])}}">
                    <strong>
                        @if(!empty($result['_source']['description']))
                            {!! Helpers::stripString($result['_source']['description'], 0, 200) !!}
                        @else
                            {!! Helpers::stripString($result['_source']['title'], 0, 200) !!}
                        @endif
                        <span class="label label-primary label-sm">{{trans('search.'.$result['_source']['type'])}}</span>
                    </strong>
                </a><br>
            @endif
            @if ($result['_type']== ElasticType::EVENT)
                <?php
                    $path = explode('-', date('d-m-Y', $result['_source']['start_time']));
                ?>
                <a href="{{url('/event?show=custom&day='.$path[0].'&month='.$path[1].'&year='.$path[2])}}" title="{{$result['_source']['title']}}">
                    <strong>{{Helpers::stripString($result['_source']['title'], 0, 200)}}</strong>
                </a>
                <span class="label label-primary label-sm">{{trans('search.event')}}</span>
            @endif 
            @if ($result['_type']== ElasticType::ASSESSMENT)
                <a href="{{url('/assessment/detail/'.$result['_source']['id'])}}" title="{{$result['_source']['title']}}">
                    <strong>{{Helpers::stripString($result['_source']['title'], 0, 200)}}</strong>
                </a>
                <span class="label label-primary label-sm">{{trans('search.assessment')}}</span>
            @endif          
            @if($result['_type'] != ElasticType::ITEM)
                <p class="font-13">
                    @if ($result['_source']['description'] != '')
                        <strong>{{trans('search.description')}}</strong> : {!! Helpers::truncate(trim($result['_source']['description']), 500) !!}
                    @endif
                </p>
            @endif
            @if(!empty(array_get($result, '_source.keywords', [])))
                @if(!empty(implode(',', $result['_source']['keywords'])))
                    <p class="font-13">
                        <strong>{{trans('search.keywords') }} : </strong>{{trim(implode(', ', $result['_source']['keywords']))}}
                    </p>
                @endif
            @endif
        </div>
    </li>
@endforeach
