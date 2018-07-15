<style type="text/css">
    .opacity-40 { opacity: 0.4; }
</style>
@if(isset($order_qids))
    @foreach($order_qids as $qid)
        <?php
            $quiz_ary = $quizzes->where('quiz_id', $qid);
            if($quiz_ary->count() <= 0)
                continue;
        ?>
        @foreach ($quiz_ary as $q)
        <div class="col-lg-3 col-md-4 col-sm-6 col-xs-12 sm-margin">
            <div class="assessment">
                <figure 
                <?php 

                $userNow = Carbon::now(Auth::user()->timezone);
                if(isset($q['end_time']) 
                    && $q['end_time'] != 0 
                    && (Timezone::getTimeStamp($q['end_time']) <= $userNow->timestamp)) {
                    echo 'class="opacity-40"';
                }
                ?>
                >
                    <a href="{{url('assessment/detail/'.$q['quiz_id'])}}" title="{{ $q['quiz_name'] }}">
                    <img src="{{asset('portal/theme/'.config('app.portal_theme_name').'/img/assessment-default.png')}}" alt="Assessment" class="assessment-img img-responsive" style="position:relative;width: 150px;border-radius: 50%!important;">
                    @if(isset($q['practice_quiz']) 
                        && !empty($q['practice_quiz']) 
                        && $q['practice_quiz'])
                        <div class="asssesment-type">
                            <span class="label label-success">
                                {{ $q['beta']?Lang::get('assessment.practice_quiz').' - '.Lang::get('assessment.beta'):Lang::get('assessment.practice_quiz') }}
                            </span>
                        </div>
                    @elseif(isset($q['type']) 
                        && !empty($q['type']) 
                        && ($q['type'] == 'QUESTION_GENERATOR'))
                        <div class="asssesment-type">
                        <span class="label label-warning">
                            {{ $q['beta']?Lang::get('assessment.question_generator').' - '.Lang::get('assessment.beta'):Lang::get('assessment.question_generator') }}
                            </span>
                        </div>
                    @elseif(isset($q['beta']) 
                        && ($q['beta']))
                        <div class="asssesment-type"><span class="label label-info"><?php echo Lang::get('assessment.beta');?></span></div>
                    @endif
                    </a>
                </figure>
                <div>
                    <p class="assessment-title center text-capitalize font-16">
                        <a href="{{url('assessment/detail/'.$q['quiz_id'])}}">
                            <strong>
                                {{str_limit($q['quiz_name'], $limit = 40, $end = '...')}}
                            </strong>
                        </a>
                    </p>
                    @if($filter == 'unattempted')
                    <p class="assessment-data gray font-12">
                    <p class="xs-margin">
                    <strong>
                        @if(!empty($q['start_time']))
                        <span class="start"> Starts: </span> {{Timezone::convertFromUTC('@'.$q['start_time'], Auth::user()->timezone, 'D, d  M Y, g:i a')}}
                         <br>
                        @endif
                        @if(!empty($q['end_time']))
                         <span class="end"> Ends: </span> {{Timezone::convertFromUTC('@'.$q['end_time'], Auth::user()->timezone, 'D, d  M Y, g:i a')}}
                         <br>
                        @endif
                    </strong>
                    </p>
                    <p><i class="fa fa-clock-o font-20 blue"></i> 
                        @if(empty($q['duration']))
                        N/A 
                        @else
                        @if($q['duration'] != 0)
                        {{ floor($q['duration']/60).'hr' }}
                        @if($q['duration']%60 != 0)
                        {{ ($q['duration']%60).'min' }} 
                        @endif
                        @endif
                        @endif
                        @if(isset($q['attempts']))
                        {{ ($q['attempts'] != 0)?($q['attempts'] == 1?'&nbsp;|&nbsp;'.$q['attempts'].' attempt left':'&nbsp;|&nbsp;'.$q['attempts'].' attempts left'): '' }} <br>
                        @endif
                    </p>
                    </p>
                    @endif
                    @if($filter == 'attempted')
                    <?php $i['obtained'] = $i['total'] = $i['count'] = 0; ?>
                    @foreach($attempt_detail[$q['quiz_id']] as $detail)
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
                        @if(!empty($q['start_time']))
                            <span class="start">Starts: </span>{{Timezone::convertFromUTC('@'.$q['start_time'], Auth::user()->timezone, 'D, d  M Y, g:i a')}}
                             <br>

                        @endif
                            @if(!empty($q['end_time']))
                            <span class="end">Ends: </span>{{Timezone::convertFromUTC('@'.$q['end_time'], Auth::user()->timezone, 'D, d  M Y, g:i a')}}
                            <br>
                        @endif
                    </strong>
                    </p>
                    <p><i class="fa fa-clock-o font-20 blue"></i> 
                        @if(empty($q['duration']))
                        N/A
                        @else
                        @if($q['duration'] != 0)
                        {{ floor($q['duration']/60).'hr' }}
                        @if($q['duration']%60 != 0)
                        {{ ($q['duration']%60).'min' }}
                        @endif
                        @endif
                        @endif        

                        @if(isset($q['attempts']) &&$q['attempts'] != 0 
                            && $q['attempts'] != $i['count'])
                        <?php
                            $attempt_left = $q['attempts'] -  $i['count'];
                        ?>
                            @if($attempt_left > 0)
                            {{ '&nbsp;|&nbsp;' }}
                            {{ $attempt_left == 1?$attempt_left.' attempt left':$attempt_left.' attempts left' }}
                            @endif                           
                        @endif
                        <br>
                    </p>
                    @endif
                </div>
            </div><!--assessment-->
            <div class="analytic-div">
            <?php
            if((config('app.channelAnalytic') == 'on') && (array_get($q, 'is_score_display', true) == 'yes')){
                if(!empty($quizAnalytics)){
                    $sepecificQuizAnalticAry = $quizAnalytics->get((int)$q['quiz_id']);
                }else{
                    $sepecificQuizAnalticAry = [];
                }
            ?>
            @if(!empty($quizAnalytics) && isset($quizMatrics->setting['quiz_marics']) 
                && ($quizMatrics->setting['quiz_marics']['quiz_speed'] == 'on' || 
                    $quizMatrics->setting['quiz_marics']['quiz_accuracy'] == 'on' ||
                    $quizMatrics->setting['quiz_marics']['quiz_score'] == 'on'
                    )
                && !isset($sepecificQuizAnalticAry[0]['type'])
            )
            
               @if(!is_null($sepecificQuizAnalticAry)) 
                <?php
                    $sepecificQuizAnaltic = $sepecificQuizAnalticAry[0];
                    $score = isset($sepecificQuizAnaltic['score']) ?  $sepecificQuizAnaltic['score'] : 0;
                    $accuracy = isset($sepecificQuizAnaltic['accuracy']) ?  
                                                    $sepecificQuizAnaltic['accuracy'] : 0;
                    $speed = isset($sepecificQuizAnaltic['speed']) ?  $sepecificQuizAnaltic['speed'] : 0;
                ?>      
                    @if(isset($quizMatrics->setting['quiz_marics']['quiz_score']) && $quizMatrics->setting['quiz_marics']['quiz_score'] == 'on')
                        <div>
                          <div class="left">
                            <img src="{{URL::asset($theme.'/img/icons/icons-05.png')}}" alt="score" title="Score" width="22">
                          </div>
                          <div class="right">
                            <div class="progress score-bar">
                              <div style="width: {{$score}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">
                                {{number_format((float)$score, 2 )}}% 
                              </div>
                            </div>
                          </div>
                        </div><!-- score-bar -->
                    @endif
                  
                    @if(isset($quizMatrics->setting['quiz_marics']['quiz_accuracy']) && $quizMatrics->setting['quiz_marics']['quiz_accuracy'] == 'on')
                        <div>
                          <div class="left">
                            <img src="{{URL::asset($theme.'/img/icons/icons-07.png')}}" alt="accuracy" title="Accuracy" width="22">
                          </div>
                          <div class="right">
                            <div class="progress accuracy-bar">
                              <div style="width: {{$accuracy}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar">
                                {{number_format((float)$accuracy, 2)}}% 
                              </div>
                            </div>
                          </div>
                        </div><!--  accuracy-bar -->
                    @if(isset($quizMatrics->setting['quiz_marics']['quiz_speed']) && $quizMatrics->setting['quiz_marics']['quiz_speed'] == 'on')
                        <div>
                          <div class="left">
                            <img src="{{URL::asset($theme.'/img/icons/icons-03.png')}}" alt="time" title="Time" width="22">
                          </div>
                          <div class="right">
                           <span>{{$speed}}&nbsp;</span><span>&nbsp;(H:M:S)&nbsp;
                            </span>
                          </div>
                        </div><!-- time-bar -->
                    @endif
                    @endif 
                @endif
            
            @elseif(!empty($quizAnalytics))
            <?php
                $sepecificQuizAnalticAry = $quizAnalytics->get((int)$q['quiz_id']);
            ?>
               @if(!is_null($sepecificQuizAnalticAry) 
                    && isset($sepecificQuizAnalticAry[0]['type']) 
                    && ($sepecificQuizAnalticAry[0]['type'] == "QUESTION_GENERATOR"))
                         <div>
                            <div class="left">
                                <img src="{{URL::asset($theme.'/img/icons/correct-icon.jpg')}}" alt="Completion" title="Completion" width="18px">
                            </div>
                            <div class="right">
                                <div class="progress score-bar">
                                  <div style="width:{{$sepecificQuizAnalticAry[0]['completion_percentage']}}%" aria-valuemax="100" aria-valuemin="0" aria-valuenow="70" role="progressbar" class="progress-bar progress-bar-striped progress-bar-animated">{{$sepecificQuizAnalticAry[0]['completion_percentage']}}% </div>
                                </div>
                            </div>
                        </div><!-- score-bar -->
               @endif
            @endif
            <?php  }?>
            </div>
        </div><!--assessment div-->
        @endforeach
    @endforeach
@else
    {{ Lang::get('assessment.no_more_assessment_to_show') }}
@endif