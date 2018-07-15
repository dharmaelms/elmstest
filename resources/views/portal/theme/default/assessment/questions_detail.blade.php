@section('content')
<style type="text/css">
    .table-blue .table-striped > tbody > tr.quiz-headrow { background-color: #c5fdc5 !important; }
    #review_answers .nav > li a {
        color: #ffffff;
        padding: 5.5px 14px;
        background-color: #555555;
        font-weight: 600;
    }
    #review_answers .nav > li.active a{
        color: #ffffff;
        font-weight: 600;
        background-color: #428bca;
    }
    .qg-loading-bar
    {
        position: fixed;
        top: 20%;
        left: 40%;
        z-index: 1100;
        min-width: 175px;
        padding: 2px;
        background-color: rgb(66, 139, 202);
        text-align: center;
    }

    .qg-loading-bar span
    {
        color: white;
        font-weight: bold;
    }
</style>
<link rel="stylesheet" type="text/css" href="{{ URL::to("portal/theme/default/css/responsive-iframe.css") }}">
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/jquery.scrolling-tabs.css') }}" />
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
<!--content starts here-->
<div class="row pane panel-default quiz-name">
    <div class="panel-heading qus-main-panel-head">
        <b>
            {{ $quiz->quiz_name }}  
            @if (!empty($quiz->quiz_description))                                             
                <a href="#info-modal-quiz" class="btn l-gray info-btn" data-toggle="modal" title="Instructions">
                    <i class="fa fa-question"></i>
                </a>
            @endif
        </b>
        <button onclick="window.close();" class="btn btn-danger btn-sm pull-right" style="margin-top:-4px;">
            <strong><i class="fa fa-times"></i> {{ trans('assessment.close') }}</strong>
        </button>  
    </div>
    <br>
    <div class="panel-body padding-0">
        <div class="row">
            <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 quiz-details-tabs">
                <div class="tabbable-line">
                    <ul class="nav nav-tabs ">
                        <li class="active">
                            <a href="#tab_1" data-toggle="tab">{{ trans('assessment.quiz_details') }}</a>
                        </li>
                        <li>
                            <a href="#tab_2" data-toggle="tab">{{ trans('assessment.question_details') }}</a>
                        </li>
                        @if(isset($concepts['data']))
                        <li>
                            <a href="#tab_3" data-toggle="tab">{{ trans('assessment.concepts') }}</a>
                        </li>
                        @endif
                        @if($quiz->type !== QuizType::QUESTION_GENERATOR)
                        <li>
                            <a href="#review_answers" data-toggle="tab">{{ trans('assessment.review_answers') }}</a>
                        </li>
                        @endif
                    </ul>
                    <div class="tab-content">
                        <!-- Quiz Details -->
                        <div class="tab-pane active" id="tab_1">
                            <div class="panel quiz-name">
                                <div class="panel-heading qus-main-panel-head">
                                    <b>
                                        @if((!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR))Attempt No.: {{$attempt_num}} |
                                        @endif Attempt Date: {{ $attempt->started_on->timezone(Auth::user()->timezone)->format('D, d M, h:i A') }}</b></div>
                                        <div class="panel-body">
                                            <div class="row">
                                                <div class="col-md-4 col-sm-4 col-xs-12  xs-margin">
                                                    <h4>
                                                        @if(isset($quiz_details['pass_head']) && !is_null($quiz_details['pass_head']))
                                                        @if($quiz_details['pass_head'])
                                                        <strong class='correct-text'><?php echo Lang::get('assessment.pass'); ?></strong>
                                                        @else
                                                        <strong class='wrong-text'><?php echo Lang::get('assessment.fail'); ?></strong>
                                                        @endif
                                                        @endif
                                                    </h4>
                                                </div>

                                            </div>
                                            <div class="row">
                                                <div class="col-md-12 table-responsive table-blue">
                                                    <table class="table table-striped">
                                                        <thead>
                                                            <tr>
                                                                <th></th>

                                                                @if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR))
                                                                <th><?php echo Lang::get('assessment.marks'); ?></th>
                                                                <th><?php echo Lang::get('assessment.marks'); ?>(%)</th>
                                                                @if(isset($quiz_details['pass_head']) && !is_null($quiz_details['pass_head']))
                                                                <th><?php echo Lang::get('assessment.cut_off'); ?>{{ array_has($quiz, 'cut_off_format') ? ($quiz->cut_off_format == QCFT::PERCENTAGE ? '(%)' : '') : '' }}</th>
                                                                <th><?php echo Lang::get('assessment.cut_off_cleared'); ?></th>
                                                                @endif
                                                                @endif

                                                                <th><?php echo Lang::get('assessment.total_time_(h:m:s)'); ?></th>
                                                                <th><?php echo Lang::get('assessment.speed(m:s)'); ?></th>
                                                                <th><?php echo Lang::get('assessment.accuracy'); ?> (%)</th>
                                                                <th><?php echo Lang::get('assessment.correct'); ?></th>
                                                                <th><?php echo Lang::get('assessment.incorrect'); ?></th>
                                                                @if((!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR))
                                                                <th><?php echo Lang::get('assessment.skipped'); ?></th>
                                                                @endif
                                                            </tr>
                                                        </thead>
                                                        <tbody>
                                                            <?php
                                                            if(isset($quiz_details) && !empty($quiz_details)){
                                                                echo "<tr class='quiz-headrow'>";
                                                                echo "<td>".$quiz->quiz_name ."</td>";

                                                                if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR)){
                                                                    echo "<td>".$quiz_details['marks']."</td>";
                                                                    echo "<td>".
                                                                    number_format((float)$quiz_details['marks_per'], 2)
                                                                    ."</td>";
                                                                    if(isset($quiz_details['pass_head']) && !is_null($quiz_details['pass_head'])){
                                                                        if(isset($quiz_details['cut_off']) && $quiz->type != QuizType::QUESTION_GENERATOR){
                                                                            $temp = isset($quiz_details['cut_off'])?$quiz_details['cut_off']:"<span'>NA</span>";
                                                                            echo $temp;
                                                                            echo  isset($quiz_details['pass']) ? $quiz_details['pass'] : "<td><span>NA</span></td>";
                                                                        }else{
                                                                            echo "<td><span'>NA</span></td>";
                                                                            echo "<td><span'>NA</span></td>";
                                                                        }
                                                                    }
                                                                }

                                                                echo "<td>".$quiz_details['total_time']."</td>";
                                                                echo "<td>".$quiz_details['speed']."</td>";
                                                                echo "<td>".round($quiz_details['accuracy'], 2)."</td>";
                                                                echo "<td>".$quiz_details['correct']."</td>";
                                                                echo "<td>".$quiz_details['incorrect']."</td>";
                                                                if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR)){
                                                                    echo "<td>".$quiz_details['skiped']."</td>";
                                                                }
                                                                echo "</tr>";
                                                            }
                                                            if($quiz->is_sections_enabled && isset($section_details) && !empty($section_details)){
                                                                foreach ($section_details as $section_detail) {
                                                                    echo "<tr>";
                                                                    echo "<td>".$section_detail['title']."</td>";
                                                                    if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR)){
                                                                        echo "<td>".$section_detail['marks']."</td>";
                                                                        echo "<td>".$section_detail['marks_per']."</td>";
                                                                        if(isset($quiz_details['pass_head']) && !is_null($quiz_details['pass_head'])){
                                                                            if(isset($section_detail['cut_off']) && $quiz->type != QuizType::QUESTION_GENERATOR){
                                                                                $temp = '';
                                                                                if (array_get($section_detail, 'percentage', '') != ''){
                                                                                    $temp = $section_detail['percentage'];
                                                                                } else {
                                                                                    $temp = isset($section_detail['cut_off'])?$section_detail['cut_off']:"<span>NA</span>";
                                                                                }
                                                                                echo "<td>".$temp."</td>";
                                                                                echo isset($section_detail['pass'])?$section_detail['pass']:"<td><span>NA</span></td>";

                                                                            }else{
                                                                                echo "<td><span>NA</span></td>";
                                                                                echo "<td><span>NA</span></td>";
                                                                            }
                                                                        }
                                                                    }

                                                                    echo "<td>".$section_detail['total_time']."</td>";
                                                                    echo "<td>".$section_detail['speed']."</td>";
                                                                    echo "<td>".$section_detail['accuracy']."</td>";
                                                                    echo "<td>".$section_detail['correct']."</td>";
                                                                    echo "<td>".$section_detail['incorrect']."</td>";
                                                                    if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR)){
                                                                        echo "<td>".$section_detail['skiped']."</td>";
                                                                    }
                                                                    echo "</tr>";
                                                                }
                                                            }
                                                            ?>
                                                        </tbody>
                                                    </table>
                                                </div>
                                            </div>
                                            <div>
                                                @if(isset($quiz->reference_cut_off) && !empty(($quiz->reference_cut_off)))
                                                {!! $quiz->reference_cut_off !!}
                                                @endif
                                            </div>
                                        </div><!--panel-body-->
                                    </div><!--panel-->
                                </div>
                                <!-- Quiz Details -->

                                <!-- Question Details -->
                                <div class="tab-pane" id="tab_2">
                                    <div class="row">
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 table-responsive table-blue">
                                            <table class="table table-striped">
                                                <thead class="sequential-panel-header panel-heading">
                                                    <tr>
                                                        <th>Question</th>
                                                        @if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR))
                                                        <th><?php echo Lang::get('assessment.marks'); ?></th>
                                                        @endif
                                                        <th><?php echo Lang::get('assessment.time_taken'); ?></th>
                                                        <th><?php echo Lang::get('assessment.answer'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>
                                                    <?php
                                                    $j = 1;
                                                    if($quiz->is_sections_enabled){

                                                        foreach ($attempt->section_details as $section) {
                                                            if(empty($section['page_layout'])){
                                                                continue;
                                                            }
                                                            echo "<tr><td colspan = '4' style='text-align:left;padding-left:15px;'>".$section['title']." </td></tr>";   
                                                            $questions = call_user_func_array('array_merge', $section['page_layout']);
                                                            foreach ($questions as $q) {
                                                                $data = (isset($attemptdata[(int)$q]))? $attemptdata[(int)$q] : false;
                                                                if(!empty($data) && ($data->answer_status == '' || $data->answer_status == 'CORRECT' || $data->answer_status == 'INCORRECT') && $data->status !="NOT_VIEWED") {
                                                                    echo '<tr>';
                                                                    echo "<td>".$j++."</td>";
                                                                    if( (!(isset($quiz->type) )|| $quiz->type != QuizType::QUESTION_GENERATOR)){
                                                                        if($data->obtained_negative_mark > 0) {
                                                                            echo "<td> -".$data->obtained_negative_mark."</td>";
                                                                        } else {
                                                                            echo "<td>".$data->obtained_mark."</td>";
                                                                        }
                                                                    }
                                                                    $tk = isset($data->time_spend) ? array_sum($data->time_spend) : '0';
                                                                    echo "<td>".$tk." Secs</td>";
                                                                    if($data->answer_status == 'CORRECT'){
                                                                        echo "<td><span class='correct-text'><i class='fa fa-check'></i></span></td>";
                                                                    } elseif($data->answer_status == ''){
                                                                        echo "<td>-</td>";
                                                                    } else{
                                                                        echo "<td><span class='wrong-text'><i class='fa fa-close'></i></span></td>";
                                                                    }
                                                                    echo '</tr>';                                       
                                                                } else if(!(isset($quiz->type) )|| $quiz->type != QuizType::QUESTION_GENERATOR){
                                                                    echo '<tr>';
                                                                    echo "<td>".$j++."</td>";
                                                                    echo "<td>0</td>";
                                                                    echo "<td>0</td>";
                                                                    echo "<td>-</td>";
                                                                    echo '</tr>';
                                                                }
                                                            }
                                                        }
                                                    }else{
                                                        foreach ($attempt->questions as $q) {
                                                            $data = (isset($attemptdata[(int)$q]))? $attemptdata[(int)$q] : false;
                                                            if(!empty($data) && ( $data->answer_status == '' || $data->answer_status == 'CORRECT' || $data->answer_status == 'INCORRECT') && $data->status !="NOT_VIEWED") {
                                                                echo '<tr>';
                                                                echo "<td>".$j++."</td>";
                                                                if( (!(isset($quiz->type) )|| $quiz->type != QuizType::QUESTION_GENERATOR)){
                                                                    if($data->obtained_negative_mark > 0) {
                                                                        echo "<td> -".$data->obtained_negative_mark."</td>";
                                                                    } else {
                                                                        echo "<td>".$data->obtained_mark."</td>";
                                                                    }
                                                                }
                                                                $tk = isset($data->time_spend) ? array_sum($data->time_spend) : '0';
                                                                echo "<td>".$tk." Secs</td>";

                                                                if($data->answer_status == 'CORRECT'){
                                                                    echo "<td><span class='correct-text'><i class='fa fa-check'></i></span></td>";
                                                                } else if($data->answer_status == ''){
                                                                    echo "<td>-</td>";
                                                                } else{
                                                                    echo "<td><span class='wrong-text'><i class='fa fa-close'></i></span></td>";
                                                                }   
                                                                echo "</tr>";
                                                            } else if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR)){
                                                                echo '<tr>';
                                                                echo "<td>".$j++."</td>";
                                                                echo "<td>0</td>";
                                                                echo "<td>0</td>";
                                                                echo "<td>-</td>";
                                                                echo '</tr>';
                                                            }
                                                        }
                                                    }
                                                    ?>
                                                </tbody>                   
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                <!-- Question Details -->
                                <!-- Concepts -->
                                @if(isset($concepts['data']))
                                <div class="tab-pane" id="tab_3">
                                    <br>
                                    <div class="row">
                                        <div class="form-group">
                                            <div class="col-md-3">
                                                <select class="form-control" name="concepts" id="concepts-list">
                                                    @foreach($concepts['keywords'] as $keyword)
                                                    <option value="{{ $keyword }}">{{ $keyword }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                        </div>
                                    </div>
                                    <br>
                                    <div class="row">
                                        <div class="col-md-12 table-responsive table-blue">
                                            <table class="table table-striped" id="concept-table">
                                                <thead>
                                                    <tr>
                                                        @if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR))
                                                        <th><?php echo Lang::get('assessment.marks'); ?></th>
                                                        <th>% <?php echo Lang::get('assessment.marks'); ?> </th>
                                                        @endif
                                                        <th><?php echo Lang::get('assessment.total_time'); ?></th>
                                                        <th><?php echo Lang::get('assessment.speed'); ?></th>
                                                        <th><?php echo Lang::get('assessment.accuracy'); ?> (%)</th>
                                                        <th><?php echo Lang::get('assessment.correct'); ?></th>
                                                        <th><?php echo Lang::get('assessment.incorrect'); ?></th>
                                                        <th><?php echo Lang::get('assessment.skipped'); ?></th>
                                                    </tr>
                                                </thead>
                                                <tbody>                                     
                                                    @foreach($concepts['data'] as $key => $data)
                                                    <tr data-concept="{{ $key }}">
                                                        @if( (!(isset($quiz->type)) || $quiz->type != QuizType::QUESTION_GENERATOR))
                                                        <td>{{ $data['obtained_mark']}}/{{ $data['total_mark'] }}</td>
                                                        <td>{{ $data['marks_percentage'] }}</td>
                                                        @endif
                                                        <td>{{ Helpers::secondsToTimeString($data['time_taken']) }}</td>
                                                        <td>{{ Helpers::secondsToTimeString($data['speed']) }}</td>
                                                        <td>{{ $data['accuracy'] }}</td>
                                                        <td>{{ $data['correct'] }}</td>
                                                        <td>{{ $data['incorrect'] }}</td>
                                                        <td>{{ $data['skipped'] }}</td>
                                                    </tr>
                                                    @endforeach
                                                </tbody>
                                            </table>
                                        </div>
                                    </div>
                                </div>
                                @endif
                                @if($quiz->type !== QuizType::QUESTION_GENERATOR)
                                <div class="tab-pane" id="review_answers">
                                    <div id="jquery-script-menu">
                                        @if($quiz->is_sections_enabled)
                                        <ul class="nav nav-tabs" role="tablist">
                                            @foreach($section_details as $key => $section)
                                            @if($key == 0)
                                            <?php $active = "active"?>
                                            @else
                                            <?php $active = '';?>
                                            @endif
                                            <li role="presentation" class="{{ $active }}" data-section="{{ $section['id']}}">
                                                <a href="#section_{{ $section['id']}}" data-toggle="tab" role="tab">{{ $section['title']}}</a>
                                            </li>     
                                            @endforeach
                                        </ul>
                                        @endif
                                        <div class="tab-content">
                                            <div class="active tab-pane" role="tabpanel">
                                                <div class="question-panel">
                                                    <div class="row col-md-12 xs-margin">
                                                        <h4>
                                                            <strong>{{ trans('assessment.questions') }}</strong>
                                                        </h4>
                                                    </div>
                                                    <ul class="question">
                                                        <?php
                                                        $count = 0;
                                                        $statusArray = [];
                                                        ?>
                                                        @if($quiz->is_sections_enabled)
                                                        <?php
                                                        $sections = $attemptdata->groupBy('section_id');
                                                        ?>
                                                            @foreach($sections as $key => $section)
                                                            <?php $attempts = $section->sortBy('_id'); ?>
                                                                @foreach($attempts as $attempt)
                                                                <?php
                                                                $status = ($attempt->answer_status == 'CORRECT' ? 'correct' : ($attempt->answer_status == 'INCORRECT' ? 'wrong' : 'skipped'));
                                                                ?>
                                                                <li style="display: none;" data-section="{{ $quiz->is_sections_enabled ? $attempt->section_id : '' }}">
                                                                    <a class="{{ $status }}" data-question="{{ $attempt->question_id }}">
                                                                        {{ ++$count }}
                                                                        @if($count == 1)
                                                                        <div class="active-line"></div>
                                                                        @endif
                                                                    </a>                                                            
                                                                </li>
                                                                @endforeach
                                                            @endforeach
                                                        @else
                                                            <?php $attempts = $attemptdata->sortBy('_id'); ?>
                                                            @foreach($attempts as $key => $attempt)
                                                            <?php
                                                            $status = ($attempt->answer_status == 'CORRECT' ? 'correct' : ($attempt->answer_status == 'INCORRECT' ? 'wrong' : 'skipped'));
                                                            $statusArray[$attempt->question_id] = $status;
                                                            ?>
                                                            <li data-section="{{ $quiz->is_sections_enabled ? $attempt->section_id : '' }}">
                                                                <a class="{{ $status }}" data-question="{{ $attempt->question_id }}">{{ ++$count }}
                                                                    @if($count == 1)
                                                                    <div class="active-line"></div>
                                                                    @endif
                                                                </a>
                                                            </li>
                                                            @endforeach 
                                                        @endif
                                                    </ul>   
                                                </div>
                                            </div>
                                        </div> 
                                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 lg-margin qus-box">
                                            <div class="panel-body qus-panel question-desc">
                                                <?php
                                                $count = 0;
                                                ?>
                                                @if($quiz->is_sections_enabled) 
                                                <?php 
                                                    $sections = $attemptdata->groupBy('section_id');
                                                    $count = 0;
                                                ?> 
                                                @foreach($sections as $section)
                                                <?php
                                                $attempts = $section->sortBy('_id');
                                                ?>
                                                @foreach($attempts as $attempt)
                                                @include('portal.theme.default.assessment.review.questions._mcq',['attempt' => $attempt, 'quiz' => $quiz, 'count' => $count++])
                                                @endforeach
                                                @endforeach
                                                @else
                                                <?php
                                                $attempts = $attemptdata->sortBy('_id');
                                                $count = 0;
                                                ?>
                                                @foreach($attempts as $attempt)
                                                @include('portal.theme.default.assessment.review.questions._mcq',['attempt' => $attempt, 'quiz' => $quiz, 'count' => $count++])
                                                @endforeach
                                                @endif
                                            </div>
                                        </div>
                                        @endif
                                    </div>
                                </div> 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div id="info-modal-quiz" class="modal fade" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
                        <h4 class="modal-title center"><strong>{{Lang::get('assessment/section.modal_header_text')}}</strong></h4>
                    </div>
                    <div class="modal-body">
                        <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
                            <div class="row">
                                <div class="col-md-12">
                                    <p>
                                        {!! $quiz->quiz_description !!}
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer center">
                        <button type="button" class="btn-success" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong><?php echo Lang::get('assessment.ok'); ?></strong></button>
                    </div>
                </div>
            </div>
        </div>
        <script src="//code.jquery.com/jquery-1.12.0.min.js"></script>
        <script src="{{ asset('portal/theme/default/js/jquery.scrolling-tabs.js') }}"></script>    
        <script type="text/javascript">
            $(document).ready(function(){
                var $table = "table#concept-table";
                $($table+' tbody tr:not(:first-child)').hide();
                $('#concepts-list').on('change', function(){
                // $("#concept-table tr[data-num='3']")
                $($table).find('tbody tr').fadeOut();
                $($table).find('tr[data-concept="'+$(this).val()+'"]').delay(500).fadeIn('slow');
            });
                $('a[data-toggle="tab"]', '#review_answers').on('shown.bs.tab', function (e) {
                    var target = $(e.target).attr("href") // activated tab
                    var $section = $(this).parent().data('section');
                    $('ul.question li').hide();
                    $('ul.question').find("[data-section='"+$section+"']").show();
                    $('ul.question').find("li:visible:first a").delay(500).trigger('click');
                });
                @if($quiz->is_sections_enabled)
                var $firstSection = $('#review_answers ul li:first').data('section');
                $('ul.question').find("[data-section='"+$firstSection+"']").show();
                @endif
                $clone = $('div.answers:first').clone().removeAttr('style').addClass("active-question");
                $('.question-desc').append($clone).show();
                $('ul.question li a').on('click', function(e) {
                    if ($(this).find('div.active-line').length <= 0) {
                        $('ul.question li').find('div.active-line').remove();
                        $(this).append('<div class="active-line"></div>');
                        $('div.answers.active-question').fadeOut('slow').remove();
                        $clone = $('div.qus-panel').find('[data-question="'+$(this).data('question')+'"]').clone().removeAttr('style').addClass("active-question");
                        $('.question-desc').append($clone).delay(500).fadeIn('slow');
                    }                 
                });
            });
        </script>
        <script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
        <script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
        <link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
        @stop

