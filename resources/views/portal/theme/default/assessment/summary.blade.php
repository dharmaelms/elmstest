@section('content')
<?php
    $is_score_display = 'yes';
    $is_review_options = 'yes';
    if (!isset($quiz->is_score_display) || !$quiz->is_score_display || $quiz->is_score_display == '') {
        $is_score_display = 'no';
    } 
    if($quiz->practice_quiz == '')
    {
        $mock_quiz = "yes";
    }
    if($quiz->review_options['the_attempt'] == '')
    {
        $is_review_options = "no";
    }
    else
    {
        $mock_quiz = "no";
    }
?>
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
<script type="text/javascript">
    var is_score_display = "{{$is_score_display}}";
    var is_review_options = "{{$is_review_options}}";
</script>
<style type="text/css">
    #overlay {
        position: fixed !important;
        width: 100% !important;
        height: 100% !important;
        left: 0 !important;
        top: 0 !important;
        bottom: 0 !important;
        right: 0 !important;
        display: none;
    }​
</style>
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/jquery.scrolling-tabs.css') }}" />
<link rel="stylesheet" type="text/css" href="{{ asset('portal/theme/default/css/loader.css') }}" />
    <!--content starts here-->
    <div class="row" id="allow_click">
        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
            <div id="overlay" style="display:none;">
                <div class="sk-fading-circle">
                    <div class="sk-circle1 sk-circle"></div>
                    <div class="sk-circle2 sk-circle"></div>
                    <div class="sk-circle3 sk-circle"></div>
                    <div class="sk-circle4 sk-circle"></div>
                    <div class="sk-circle5 sk-circle"></div>
                    <div class="sk-circle6 sk-circle"></div>
                    <div class="sk-circle7 sk-circle"></div>
                    <div class="sk-circle8 sk-circle"></div>
                    <div class="sk-circle9 sk-circle"></div>
                    <div class="sk-circle10 sk-circle"></div>
                    <div class="sk-circle11 sk-circle"></div>
                    <div class="sk-circle12 sk-circle"></div>
                </div>
                @if($is_score_display ==  'yes')
                <p id="section-message">{{ trans('assessment.quiz_submit_message') }}</p>
                @endif
            </div>
            <div class="row">
                <div class="panel panel-default submit-panel quiz-name">
                    <div class="panel-heading qus-main-panel-head">
                        <b>{{ $quiz->quiz_name }} @if(isset($quiz->quiz_description) && !empty($quiz->quiz_description))
                                                    <a href="#info-modal" class="btn l-gray info-btn" data-toggle="modal" title="{{ trans('assessment/summary.i_title') }}"><i class="fa fa-question"></i></a></strong>
                                                @endif</b>
                        <div class="pull-right">
                            <div id="timer"></div>
                        </div>
                    </div>
                    <?php
                     $newTemplate = config('app.assessment_template');
                     if($newTemplate != "DEFAULT")
                        $newTemplate = true;
                    else
                        $newTemplate = false;
                    if(!$newTemplate)
                     {
                    ?>
                    <div class="panel-body">
                        <div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 sm-margin">
                        <!-- @if(!empty($section_ids))
                            <div class="btn-group">
                            @foreach($section_ids as $index => $section_id)
                                <button type="button" class="btn btn-primary getsection" value="{{$section_id}}"><?php echo (strlen($section_names[$index]) > 10) ? str_split($section_names[$index], 10)[0] : $section_names[$index]?></button>
                            @endforeach
                            </div>
                        @endif -->
                        @if(!empty($section_ids))
                            <!-- <div class="btn-group"> -->
                        <div id="jquery-script-menu">
                            <div class="row">
                                <div class="col-md-12">
                                    <!-- Nav tabs -->
                                    <ul class="nav nav-tabs" role="tablist">
                                    <?php $flag = true; $i = 0;?>
                            @foreach($section_ids as $index => $section_id)
                                    <?php 
                                        $i++;
                                      if($flag){
                                        $class_css ='active';
                                        $flag = false;
                                      }else{
                                        $class_css = '';
                                      } 
                                     ?>
                                      <li role="presentation" class="{{$class_css}}"><a class = "getsection" href="#tab{{$i}}" role="tab" data-toggle="tab" data-section="{{$section_id}}">{{$section_names[$index]}}</a></li>
                            @endforeach
                                    </ul>
                                </div>
                            </div>  
                        </div>
                            <!-- </div> -->
                        @endif
                            
                        <!-- </div> -->
                                
                                <?php 
                                if($quiz->is_sections_enabled){
                                    ?>
                                    <div class="tab-content">
                                        <?php
                                        $i = 0;
                                        $j = 1;                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                 
                                        $flag = true;
                                        foreach ($section_ids as $sec_id) {
                                            $i++;
                                            if($flag){
                                                $class_css ='active';
                                                $flag = false;
                                            }else{
                                                $class_css = '';
                                            } 
                                            ?>
                                            <div role="tabpanel" class="tab-pane {{$class_css}}" id="tab{{$i}}">
                                                <div class="panel-body question-panel sm-margin">
                                                    <h4 style="margin-left:6px;">
                                                        <strong>Questions &nbsp;
                                                        @if(isset($section_details) && !empty($section_details))            
                                                            @foreach ($section_details as $key => $eachSection) 
                                                                @if(isset($eachSection['title']) && !empty($eachSection['title']))
                                                                    @if(isset($eachSection['section_id']) && $eachSection['section_id'] === $sec_id && !empty($eachSection['description']))                                                                                     
                                                                                <a href="#{{$sec_id}}" class="btn l-gray info-btn" data-toggle="modal" title="{{ trans('assessment/summary.i_title') }}"><i class="fa fa-question"></i></a>
                                                                    @endif
                                                                @endif  
                                                            @endforeach
                                                        @endif
                                                        </strong>   
                                                    </h4>
                                                    <!-- Description Modal -->
                                                    <div id="{{$sec_id}}" class="modal fade" tabindex="-1" aria-hidden="true">
                                                        <div class="modal-dialog">
                                                            <div class="modal-content">
                                                                <div class="modal-header">
                                                                    <button type="button" class="close red" data-dismiss="modal" aria-hidden="true"></button>
                                                                    <h4 class="modal-title center"><strong>{{ trans('assessment.info') }}</strong></h4>
                                                                </div>
                                                                <div class="modal-body">
                                                                    <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
                                                                        <div class="row">
                                                                            <div class="col-md-12">
                                                                                <p>

																					@if(isset($section_details) && !empty($section_details))			
																				  		@foreach ($section_details as $key => $eachSection) 
																				  			@if(isset($eachSection['title']) && !empty($eachSection['title']))
																				  				@if(isset($eachSection['section_id']) && $eachSection['section_id'] === $sec_id)
																				  					<?php echo $eachSection['description'];?>
																				  				@endif
																				  			@endif	
																				  		@endforeach
																				  	@endif
																				</p>
																			</div>
																		</div>
																	</div>
																</div>
																<div class="modal-footer center">
																	<button type="button" class="btn-success" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong>OK</strong></button>
																</div>
															</div>
														</div>
													</div>
												<!-- Description Modal Ends -->
									  		<div class="question-panel">
														<ul class="question" id="list_question">  	
															<?php
															
															foreach($attempt->section_details[$sec_id]['page_layout'] as $pno => $layout) {
																foreach($layout as $q) {
                                                                    $blur = AttemptHelper::getQuestionStatus($attempt->details, $q);
																	echo '<li><a class="'.$blur.'" href="'.url('assessment/attempt/'.$attempt->attempt_id.'?section='.$sec_id.'&page='.$pno.'&'.$requestUrl).'" >'.$j++.'</a></li>';
																}
															}
															?>
														</ul>
													</div>
												</div>	
											</div>
											<?php
										}
										?>
									</div>
									<?php

                                }else{
                                    $j = 1;
                                    ?>
                                    <div class="panel-body question-panel sm-margin">
                                            <h4 style="margin-left:6px;">
                                                <strong>{{ trans('assessment.questions') }}  
                                            </h4>

								  			<div class="question-panel">
												<ul class="question" id="list_question">
									<?php
									foreach($attempt->page_layout as $pno => $layout) {
										foreach($layout as $q) {
											$blur = AttemptHelper::getQuestionStatus($attempt->details, $q);
											echo '<li><a class="'.$blur.'" href="'.url('assessment/attempt/'.$attempt->attempt_id.'?page='.$pno.'&'.$requestUrl).'" >'.$j++.'</a></li>';
										}
									}
								}
								?>
								</ul>							
							</div>
						</div>
					<?php
               		 }
					?>
						<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
							<div class="panel panel-default">
								<div class="panel-body">
									<h4 class="sm-margin"><strong>{{ trans('assessment.submit_assessment?')}}</strong></h4>
									@if(!empty($message))
									<div class="alert alert-warning">
										<center><b>{{ $message }}</b></center>
									</div>
									@endif
									<ul class="submit-quiz-list">
										<li class="submit-quiz-item">
										{{  trans('assessment.no_of_question_attempted') }}
											<span class="pull-right"> {{ count($attempt->details['answered']) }} </span>
										</li>
										<li class="submit-quiz-item">
										{{ trans('assessment.no_of_question_skipped') }}
											<span class="pull-right"> {{ count($attempt->questions) - count($attempt->details['answered']) }} </span>
										</li>
									</ul>
									<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12 quiz-retry quiz-submit top-lg-margin">
										<form id="submit-quiz" action="{{ url('assessment/close-attempt/'.$attempt->attempt_id) }}?
										{{$requestUrl}}" method="POST" accept-charset="utf-8">
											<a href="{{ url('assessment/attempt/'.$attempt->attempt_id) }}?{{$requestUrl}}" class="btn green"><i class="fa fa-arrow-left"></i> {{ trans('assessment.back_to_attempt') }}</a>
											<button type="submit" class="btn btn-success" onclick="return confirm('Are you sure you want to submit this assessment?')"><i class="fa fa-check"></i> {{ trans('assessment.submit') }}</button>
										</form>
									</div>
								</div>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>
	</div>
    <script type="text/javascript">
        var redirectToAnalytics = function(){
        if(is_score_display == 'no') {
            closeWindow();
        }
        var analyticUrl = "{{ url('assessment/question-detail/'.$attempt->attempt_id.'/'.$no_of_attempts) }}";
        $.ajax({
            url: analyticUrl,
            method: 'GET',
            dataType: 'json',
            success: function(response){
                if(response.attempt){
                    window.location = analyticUrl;
                } else {
                    $('#success').modal('show');
                    setTimeout(function(){
                        closeWindow();
                    }, 5000);
                }
            },
        });
    };
    </script>
	@if(!empty($quiz->duration))
	<?php
		$duration = ($attempt->started_on->timestamp + ($quiz->duration * 60)) - time();
		if($duration < 0) $duration = 0;
	?>
	<script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.plugin.js') }}"></script>
	<script src="{{ asset('portal/theme/'.config('app.portal_theme_name').'/plugins/countdown/jquery.countdown.js') }}"></script>
	<script>
	$(function () {
		$('#timer').countdown({
			until: +{{ $duration }},
			compact: true,
			layout: '<b>Time Left: </b>{hnn}{sep}{mnn}{sep}{snn}'
		});
	});	
	setTimeout(function(){
            var xmlHTTPRequest = $.ajax({
                url : "{{ config('app.url') }}/assessment/close-attempt/{{ $attempt->attempt_id.'?'.$requestUrl }}",
                type : "post"
            });

            xmlHTTPRequest.done(function(response, status, jqXHR){
                if(response.status !== null && response.status !== undefined)
                {
                    alert("You have exceeded the maximum assessment duration. Assessment has been submitted automatically.");
                    if (is_score_display == 'yes') {
                        redirectToAnalytics();
                    } else {
                        closeWindow();
                    }
                }
            });
        }, {{ $duration }} * 1000);
    </script>
    
    @endif
    @if(isset($remaining_seconds) && $remaining_seconds)
    <script type="text/javascript">
        setTimeout(function(){
                var xmlHTTPRequest = $.ajax({
                    url : "{{ config('app.url') }}/assessment/close-attempt/{{ $attempt->attempt_id }}",
                    type : "post"
                });

                xmlHTTPRequest.done(function(response, status, jqXHR){
                    if(response.status !== null && response.status !== undefined)
                    {
                        alert("You have exceeded the assessment end time. Assessment has been submitted automatically.");
                        if (is_score_display == 'yes') {
                            redirectToAnalytics();
                        } else {
                            closeWindow();
                        }                      
                    }
                });
            }, {{ $remaining_seconds}}*1000);
    </script>
    @endif
    <script src="{{ asset('portal/theme/default/js/jquery.scrolling-tabs.js') }}"></script>
    <script>
        $(document).ready(function(){
            $('#submit-quiz').on('submit', function(e){
                var $this = $(this);
                $.ajax({
                    url: $this.attr('action'),
                    method: $this.attr('method'),
                    success: function(response){
                        if (response.status != 'undefined'){
                            if(response.status) {
                                if(is_review_options == 'yes'){
                                    $('#overlay').toggle();
                                }
                                setTimeout(function(){
                                    reloadWindow();
                                    if(is_score_display == 'yes') {
                                        redirectToAnalytics();  
                                    } else{
                                        $('#success').modal('show');
                                        setTimeout(function(){
                                            closeWindow();
                                        }, 5000);
                                    }
                                },1000);
                            }
                        } else {
                            closeWindow();
                        }
                    },
                })
                return false;
            });
            
        });
        $('.nav-tabs').scrollingTabs();
    </script>
@include('portal.theme.default.assessment.success')
<div id="info-modal" class="modal fade" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close red" id="unclick_event2" data-dismiss="modal" aria-hidden="true"></button>
                <h4 class="modal-title center"><strong>{{Lang::get('assessment/summary.modal_header_text')}}</strong></h4>
            </div>
            <div class="modal-body">
                <div class="scroller" style="height:200px" data-always-visible="1" data-rail-visible1="1">
                    <div class="row">
                        <div class="col-md-12">
                            <p>{!!$quiz->quiz_description!!}</p>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer center">
                <button type="button" class="btn-success" id="unclick_event" data-dismiss="modal" aria-hidden="true" style="padding:5px 24px;"><strong>OK</strong></button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/keyboard_code_enum.js')}}"></script>
<script type="text/javascript" src="{{URL::asset('portal/theme/default/js/disable_copy.js')}}"></script>
<link rel="stylesheet" href="{{URL::asset('portal/theme/default/css/disable-copy.css')}}"/>
@stop