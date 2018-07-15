@if(count(array_get($programs, 'results', [])) > 0)  
   @foreach($programs['results'] as $program)
        <div class="item white-bg content-row-dashboard10"> 
            <a href="{{url('/program/packets/'.$program['program_slug'])}}" title="{{$program['program_title']}}"> 
                <div class="packet packet-container-dashboard10"> 
                    <div class="packet-img-cont-dashboard10">
                        @if(!empty($program['program_cover_media'])) 
                            <img src="{{url('media_image/'.$program['program_cover_media'].'?thumb=180x180')}}" alt="Channel" class="packet-img img-responsive packet-img-dashboard7">
                        @else
                            <img src="{{url($theme.'/img/default_channel.png')}}" alt="{{trans('search.channel')}}" alt="Channel" class="packet-img img-responsive packet-img-dashboard7">
                        @endif
                    </div> 
                    <div> 
                        <p class="packet-title packet-title-dashboard10">{{$program['program_title']}}</p> 
                    </div> 
                </div><!--packet--> 
            </a> 
        </div><!--packet div-->
    @endforeach
@endif
