@foreach($quizzes as $q)
    <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
        <div class="assessment">
            <figure>
                <a href="{{url('assessment/detail/'.$q->quiz_id)}}" title="{{ $q->quiz_name }}">
                <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/assessment-default.png')}}" alt="Assessment" class="assessment-img img-responsive" style="position:relative;">
                @if(isset($q->practice_quiz) && !empty($q->practice_quiz) && $q->practice_quiz)
                    <div style="position: absolute; top: 14px; right: 21px; border: 1px none ! important; border-radius: 5px ! important;"><span style="font-weight: bold;background-color: rgb(3, 137, 66) !important;" class="label label-success">Practice quiz</span></div>
                @endif
                </a>
            </figure>
            <div>
                <p class="assessment-title">
                    <a href="{{url('assessment/detail/'.$q->quiz_id)}}"><strong>{{ $q->quiz_name }}</strong></a>
                </p>
                @if($filter == 'unattempted')
                <p class="assessment-data gray font-12">
                <p class="xs-margin">
                <strong>
                    @if(!empty($q->start_time))
                    Starts: {{ $q->start_time->timezone(Auth::user()->timezone)->format("D, d  M Y, g:i a ") }} <br>
                    @endif
                    @if(!empty($q->end_time))
                    Ends: {{ $q->end_time->timezone(Auth::user()->timezone)->format("D, d  M Y, g:i a") }} <br>
                    @endif
                </strong>
                </p>
                <p><i class="fa fa-clock-o"></i> 
                    @if(empty($q->duration))
                    N/A 
                    @else
                    @if($q->duration != 0)
                    {{ floor($q->duration/60).'hr' }}
                    @if($q->duration%60 != 0)
                    {{ ($q->duration%60).'min' }} 
                    @endif
                    @endif
                    @endif
                    &nbsp;|&nbsp;{{ ($q->attempts != 0)? $q->attempts.' Attempts left' : 'No Attempts Limit' }} <br>
                </p>
                </p>
                @endif
                @if($filter == 'attempted')
                <?php $i['obtained'] = $i['total'] = $i['count'] = 0; ?>
                @foreach($attempt_detail[$q->quiz_id] as $detail)
                <?php
                    $i['obtained'] += $detail->obtained_mark;
                    $i['total'] += $detail->total_mark;
                    $i['count']++;
                    $i['last'] = $detail;
                ?>  
                @endforeach
                <p class="assessment-data">
                <p class="assessment-data gray font-12">
                <p class="xs-margin">
                <strong>
                    @if(!empty($q->start_time))
                    Starts: {{ $q->start_time->timezone(Auth::user()->timezone)->format("D, d  M Y, g:i a") }} <br>
                    @endif
                    @if(!empty($q->end_time))
                    Ends: {{ $q->end_time->timezone(Auth::user()->timezone)->format("D, d  M Y, g:i a") }} <br>
                    @endif
                </strong>
                </p>
                <p><i class="fa fa-clock-o"></i> 
                    @if(empty($q->duration))
                    N/A
                    @else
                    @if($q->duration != 0)
                    {{ floor($q->duration/60).'hr' }}
                    @if($q->duration%60 != 0)
                    {{ ($q->duration%60).'min' }}
                    @endif
                    @endif
                    @endif
                    &nbsp;|&nbsp;
                    @if($q->attempts == 0)
                    No Attempt Limit
                    @else
                    @if($q->attempts != 0 && $q->attempts != $i['count'])
                    {{ $attempt_left = $q->attempts -  $i['count']}} attempts left
                    @else
                    No Attempts Left
                    @endif
                    @endif
                    <br>
                </p>
                </p>
                   <!--  Last attempt score: {{ $i['last']->obtained_mark.'/'.$i['last']->total_mark }} <br>
                    Last attempt: {{ $i['last']->started_on->timezone(Auth::user()->timezone)->format('d M Y h:i A') }} <br>
                    Average score: {{ round(($i['obtained']/$i['total'])*100, 1).'%' }}  <br>
                    No. of attempts: {{ $i['count'] }} /{{ ($q->attempts != 0)? $q->attempts : 'Unlimited' }}<br> -->
                </p>
                @endif
            </div>
        </div><!--assessment-->
    </div><!--assessment div-->
@endforeach