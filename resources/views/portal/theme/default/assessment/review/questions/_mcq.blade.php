<div class="answers" data-question="{{ $attempt->question_id}}" style="display: none;">
    <div class="qus-number">
        <b>Q{{ ++$count }}</b>  
    </div>
    <div class="qus-heading">
        <div class="table-responsive">
            {!! $attempt->question_text !!}
        </div>
        <div class="radio-list">
            <?php
                $answers = [];
            ?>
            @foreach ($attempt->answers as $key => $answer)
                <?php $answers[array_search($key, $attempt->answer_order)] = $answer; ?>
            @endforeach
            <?php
                ksort($answers);
                $answerChunks = collect($answers)->chunk(2);
                $flag_align_checkmark = 0;
                $asciiCharVal = 97;
            ?>
            @foreach($answerChunks as $answers)
                <div class="row">
                    @foreach($answers as $answer)
                        <div class="col-lg-6 col-md-6 col-sm-12 col-xs-12 margin-bottom-10" style="max-height:300px;overflow-y:auto;">
                            <div class="lft-div">
                                @if($quiz->review_options["whether_correct"])
                                    @if($attempt->user_response === $answer["answer"])
                                        <?php $flag_align_checkmark = 1;?>
                                        @if(($attempt->answer_status == "CORRECT") || ($attempt->correct_answer == $attempt->user_response))
                                          <i class="fa fa-check green"></i>
                                        @else
                                            <?php $wrongChoiceRationale = $answer["rationale"]; ?>
                                            <i class="fa fa-times red"></i>
                                        @endif
                                    @endif
                                @endif
                            </div>
                            <?php
                            $class_align_checkmark = "margin-left-14";
                            if ($flag_align_checkmark === 1) {
                                $class_align_checkmark = '';
                                $flag_align_checkmark = 0;
                            }
                            ?>
                            <label class="lft-div {{$class_align_checkmark}}">
                                <input type="radio" {{ ($attempt->user_response === $answer["answer"])? "checked " : "" }}disabled>
                            </label>
                            <div class="right-div">
                                {{ chr($asciiCharVal++) }}&#41;&#32;
                            </div>
                            <div class="right-div1">
                                {!! html_entity_decode($answer["answer"]) !!}
                            </div>
                        </div>
                    @endforeach
                </div>
            @endforeach      
        </div>
        @if($quiz->review_options['whether_correct'])
            @if($quiz->review_options['correct_answer'] || $quiz->review_options['marks'] || $quiz->review_options['rationale'])
                @if($attempt->answer_status == 'CORRECT' || $attempt->correct_answer == $attempt->user_response)
                    <div class="alert alert-success">
                        <table>
                            <tbody>
                                <tr>
                                    @if($quiz->review_options['correct_answer'])
                                        <td width="70px">
                                            <img src="{{ asset('portal/theme/default/img/icons/correct-icon.jpg') }}" alt="Correct Answer">
                                        </td>
                                    @endif
                                    <td>
                                        @if($quiz->review_options['correct_answer'])
                                            <p class="margin-0">
                                                {{ trans('assessment.your_ans_correct') }}
                                            </p>
                                        @endif
                                        @if($quiz->review_options['marks'])
                                            <p class="margin-0">
                                                <b>Marks: </b>
                                                @if($attempt->obtained_negative_mark > 0)
                                                {!! "<span class='red'>".'-'.$attempt->obtained_negative_mark."</span>/".$attempt->question_mark !!}
                                                @else
                                                {{ $attempt->obtained_mark.'/'.$attempt->question_mark }}
                                                @endif
                                            </p>
                                        @endif
                                        @if(isset($attempt->time_spend))
                                            <p class="margin-0"><b> <?php echo Lang::get('assessment.time_taken'); ?>: </b> {{ Helpers::secondsToTimeString(array_sum($attempt->time_spend))}}</p>
                                        @endif
                                        @if(isset($quiz->review_options['rationale']) && $quiz->review_options['rationale'])
                                                @if(isset($attempt->rationale) && !empty($attempt->rationale))
                                                <p>
                                                    <b>{{Lang::get('assessment/review.solution')}}</b>
                                                </p>
                                                <p>
                                                    {!! html_entity_decode($attempt->rationale) !!}
                                                </p>
                                            @endif
                                        @endif
                                    </td>   
                                </tr>
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="alert alert-warning">
                        <table>
                            <tbody>
                                <tr>
                                    @if($quiz->review_options['correct_answer'])
                                        <td width="70px">
                                            <img src="{{ asset('portal/theme/default/img/icons/wrong-icon.png') }}" alt="wrong Answer">
                                        </td>
                                    @endif
                                    <td>
                                        @if($quiz->review_options['correct_answer'])
                                            <p class="margin-0">
                                                @if($attempt->answer_status === "INCORRECT")
                                                {{ trans('assessment.your_ans_incorrect') }}
                                                @else
                                                {{ trans('assessment.not_attempted') }}
                                                @endif
                                            </p>
                                        @endif
                                        @if($quiz->review_options['marks'])
                                            <p class="margin-0"><b>Marks: </b>
                                            @if($attempt->obtained_negative_mark>0)
                                                {!! "<span class='red'>".'-'.$attempt->obtained_negative_mark."</span>/".$attempt->question_mark !!} @else {{ $attempt->obtained_mark.'/'.$attempt->question_mark }}
                                            </p>
                                            @endif
                                        @endif
                                        @if(isset($attempt->time_spend))
                                            <p class="margin-0"><b> {{ trans('assessment.time_taken') }}: </b> {{ Helpers::secondsToTimeString(array_sum($attempt->time_spend))}} </p>
                                        @endif
                                        @if(isset($quiz->review_options['rationale']) && $quiz->review_options['rationale'])
                                            @if(isset($wrongChoiceRationale) && !empty($wrongChoiceRationale))
                                                <p><b>{{ trans('assessment.your_answer_incorrect_beacuse') }} :</b></p>
                                                <p>{!! html_entity_decode( $wrongChoiceRationale ) !!}</p>
                                            @endif
                                        @endif
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    <div class="alert alert-success">
                        @if($quiz->review_options['correct_answer'])
                            <p class="margin-0">
                                <b>{{ trans('assessment.correct_answer') }}</b>
                                {!! html_entity_decode($attempt->correct_answer) !!}
                            </p>
                        @endif
                        @if(isset($quiz->review_options['rationale']) && $quiz->review_options['rationale'])
                            @if(isset($attempt->rationale) && !empty($attempt->rationale))
                            <p><b>{{ trans('assessment/review.solution') }}</b> </p>
                            <p>
                                {!! html_entity_decode($attempt->rationale) !!}
                            </p>
                            @endif
                        @endif
                    </div>
                @endif
            @endif
        @endif
    </div>
</div> 
