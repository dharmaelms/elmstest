@if($category_view_type === 'list')
<div class="col-md-3 col-sm-6 col-xs-12">
    <div class="packet">
      <figure>
        <a href="#" title="Channel">
        @if(!$data['program_cover_media'])
        <img src="{{URL::to('portal/theme/default/img/image-default.png')}}" alt="Channel" class="packet-img img-responsive">
        @else
          <img src="{{URL::to('/media_image/'.$data['program_cover_media'].'/?thumb=178x114')}}" alt="Channel" class="packet-img img-responsive">
        @endif
        </a>
      </figure>
      <div>
        <p class="packet-title"><a href="#"><strong>{{$data['program_title']}}</strong></a></p>
        <p class="packet-data">
        	<?php //dd($data['program_cover_media']);?>
          <span class="left">
            <span class="red">{{ Lang::get('program.start_date') }}</span> <br> {{date(config('app.program_date_format'),Timezone::getTimeStamp($data['program_display_startdate']))}} 
          </span>
          <span class="right">
            <span class="red">{{ Lang::get('program.end_date') }}</span> <br> {{date(config('app.program_date_format'),Timezone::getTimeStamp($data['program_display_enddate']))}} 
          </span>
        </p>
      </div>
      <div class="course-detail"><p class="font-13">{{str_limit($data['program_description'],138)}}</p><p class="center font-13"><a class="btn btn-success" href="{{URL::to('catalog/course/'.$data['program_slug'])}}">{{ Lang::get('program._view_course') }}</a></p></div>
    </div>
</div>
@else
<div class="course-list row">
    <div class="col-md-3 col-sm-3 col-xs-3 no-padding">
      <figure>
       @if(!$data['program_cover_media'])
        <img src="{{URL::to('portal/theme/default/img/image-default.png')}}" alt="Channel" class="packet-img img-responsive">
        @else
          <img src="{{URL::to('/media_image/'.$data['program_cover_media'].'/?thumb=178x114')}}" alt="Channel" class="packet-img img-responsive">
        @endif
      </figure>
    </div>
    <div class="col-md-9 col-sm-9 col-xs-9">
      <h4 class="font-weight-500 black">{{$data['program_title']}}</h4>
      <p>{{$data['program_description']}}</p>
      <div>
        <div class="price">
          <p class="red">{{ Lang::get('program.start_date') }}</p>
          <p class="amt">{{date(config('app.program_date_format'),Timezone::getTimeStamp($data['program_display_startdate']))}}</p> 
        </div>
        <div class="price">
          <p class="red">{{ Lang::get('program.end_date') }}</p>
          <p class="amt">{{date(config('app.program_date_format'),Timezone::getTimeStamp($data['program_display_enddate']))}}</p> 
        </div>                                                
        <div class="btn-div">
            <a href="{{URL::to('catalog/course/'.$data['program_slug'])}}" class="btn btn-primary margin-bottom-5">{{ Lang::get('program.view_course') }}</a>
        </div>
      </div>
    </div>
  </div>
@endif