<style type="text/css">
.assessment {
    height: 258px;
}
.opacity-40 { opacity: 0.4;}
</style>
@if(!$assignments->isempty() && isset($assignments))
    @foreach($assignments as $s)
    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
        <?php
            $userNow = Carbon::now(Auth::user()->timezone);
            $id = array_get($s, 'id');
            $completed_list_array = $completed_list->contains($id);
            $drafted_assignment = in_array($id, $drafted_list);
        ?>
        <div class="assessment" @if($drafted_assignment) style="background-color: #E4F1FE" title='{{trans('assignment.saved_as_draft')}}' @endif;>
            @if (!$completed_list_array)
                @if(isset($s->cutoff_time)
                    && $s->cutoff_time->timestamp != 0
                    && (Timezone::getTimeStamp($s->cutoff_time->timestamp) <= $userNow->timestamp))
                <figure class="opacity-40">
                    <a href="#" style="cursor: default">
                        <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png')}}" alt="Assessment" class="assessment-img img-responsive" style="position:relative;">
                    </a>
                </figure>
                <p class="assessment-title text-capitalize font-18 center">
                    <a href="#"><strong>{{str_limit($s->name, $limit = 40, $end = '...')}}</strong></a>
                </p>
                @elseif(isset($s->start_time)
                    && $s->start_time->timestamp != 0
                    && (Timezone::getTimeStamp($s->start_time->timestamp) >= $userNow->timestamp))
                <figure title="{{trans('assignment.not_yet_started')}}">
                    <a href="#" style="cursor: default">
                        <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png')}}" alt="Assessment" class="assessment-img img-responsive" style="position:relative;">
                    </a>
                </figure>
                <p class="assessment-title" title="{{trans('assignment.not_yet_started')}}">
                    <a href="#"><strong>{{str_limit($s->name, $limit = 40, $end = '...')}}</strong></a>
                </p>
                @else
                <div>
                    <figure >
                        <a href="{{ URL::Route('submit-assignment',['packet_slug' => 'unattempted', 'assignment_id' => $s['id']]) }}">
                            <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png')}}" alt="Assessment" class="img-responsive assessment-img" style="position:relative;">
                        </a>
                    </figure>
                    <p class="assessment-title text-capitalize center font-18">
                        <a href="{{ URL::Route('submit-assignment',['packet_slug' => 'unattempted', 'assignment_id' => $s['id']]) }}"><strong>{{str_limit($s->name, $limit = 40, $end = '...')}}</strong></a>
                    </p>
                </div>
                @endif
            @else
            <figure>
                <a href="{{ URL::Route('assignment-result',['packet_slug' => 'unattempted', 'assignment_id' => $s['id']]) }}" title="{{ $s['name'] }}">
                    <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/survey-default.png')}}" alt="Assessment" class="assessment-img img-responsive" style="position:relative;">
                </a>
            </figure>
            <p class="assessment-title center text-capitalize font-18">
                <a href="{{ URL::Route('assignment-result',['packet_slug' => 'unattempted', 'assignment_id' => $s['id']]) }}" title="{{ $s['survey_title'] }}"><strong>{{str_limit($s->name, $limit = 40, $end = '...')}}</strong></a>
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
                             <span class="end">  Ends:</span> {{Timezone::convertFromUTC('@'.$s->end_time, Auth::user()->timezone, 'D, d  M Y, g:i a')}}
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
{{Lang::get('assignment.no_assignments')}}
</div>
@endif

