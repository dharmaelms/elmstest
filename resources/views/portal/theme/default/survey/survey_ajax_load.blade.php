<style type="text/css">
.assessment {
    height: 258px;
}
.opacity-40 { opacity: 0.4;}
</style>
@if(!$surveys->isempty() && isset($surveys))
    @foreach($surveys as $s)
    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
        <div class="assessment">
            <?php
                $userNow = Carbon::now(Auth::user()->timezone);
                $id = array_get($s, 'id');
                $completed_list_array = $completed_list->contains($id);
            ?>
            @if (!$completed_list_array)
                @if(isset($s->end_time)
                    && $s->end_time->timestamp != 0
                    && (Timezone::getTimeStamp($s->end_time->timestamp) <= $userNow->timestamp))
                <figure class="opacity-40">
                    <a href="#">
                        <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png')}}" alt="Assessment" class="assessment-img img-responsive" style="position:relative;">
                    </a>
                </figure>
                <p class="assessment-title text-capitalize center font-20">
                    <a href="#"><strong>{{str_limit($s->survey_title, $limit = 40, $end = '...')}}</strong></a>
                </p>
                @else
                <figure>
                    <a href="{{url('survey/survey-details/'.$s['id'])}}" title="{{ $s['survey_title'] }}">
                        <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png')}}" alt="Assessment" class="img-responsive assessment-img" style="position:relative;">
                    </a>
                </figure>
                <p class="assessment-title text-capitalize center font-20">
                    <a href="{{url('survey/survey-details/'.$s['id'])}}" title="{{ $s['survey_title'] }}"><strong>{{str_limit($s->survey_title, $limit = 40, $end = '...')}}</strong></a>
                </p>
                @endif
            @else
            <figure>
                <a href="{{url('survey/view-reports/unattempted/'.$s['id'])}}" title="{{ $s['survey_title'] }}">
                    <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png')}}" alt="Assessment" class="assessment-img img-responsive" style="position:relative;">
                </a>
            </figure>
            <p class="assessment-title text-capitalize center font-20">
                <a href="{{url('survey/view-reports/unattempted/'.$s['id'])}}" title="{{ $s['survey_title'] }}"><strong>{{str_limit($s->survey_title, $limit = 40, $end = '...')}}</strong></a>
            </p>
            @endif
            <div>
                <p class="xs-margin">
                    <strong>
                        @if(!empty($s['start_time']))
                         <span class="start">  Starts:</span> {{Timezone::convertFromUTC('@'.$s->start_time, Auth::user()->timezone, 'D, d  M Y, g:i a')}}
                         <br>
                        @endif
                        @if(!empty($s['end_time']))
                            <span class="red">  Ends:</span> {{Timezone::convertFromUTC('@'.$s->end_time, Auth::user()->timezone, 'D, d  M Y, g:i a')}}
                             <br>
                        @endif
                        <br>
                    </strong>
                </p>
            </div>
        </div>
    </div>
    @endforeach
@else
<div class="col-sm-12 col-xs-12">
{{Lang::get('survey.no_surveys')}}
</div>
@endif

