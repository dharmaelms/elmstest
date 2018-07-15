@section('content')
    @if ( Session::get('success') )
        <div class="alert alert-success" id="alert-success">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Success!</strong> -->
            {{ Session::get('success') }}
        </div>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Error!</strong> -->
            {{ Session::get('error') }}
        </div>
    @endif
    @if(!empty($questions))
    <div>
        Total of marks: {{ $section->total_marks }} | Questions: {{ count($questions) }}
    </div>
    <br>
    @endif
    @if($attempt > 0)
    <div style="color:red;">
        You cannot add or remove questions because this quiz has been attempted. (Attempts: {{ $attempt }}) 
        <a href="{{url('cp/assessment/report-quiz/'.$quiz->quiz_id)}}">Click here to view the attempts</a>
    </div>
    <br>
    @endif
    <!-- This div is to append the message after bulk delete -->
    <div id="delete-msg"></div>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.css')}}">
    <?php $sort = []; 
    ?>
    @if((Input::has('qbank') || empty($questions)) && ($attempt == 0) )
    <div class="row custom-box">
        <div class="col-md-7">
            <div class="box">
                <div class="box-title">
                    <h3 style="color:black"><i class="fa fa-file"></i> Section Questions</h3>
                    @if(!empty($questions))
                        <div class="pull-right">
                            <a  class="btn btn-circle show-tooltip bulkdelete" title="<?php echo trans('admin/user.bulk_user_delete'); ?>" href="#"><i class="fa fa-trash-o"></i></a>
                        </div>
                    @endif
                    <div class="box-tool" id="apply" style="display:none;padding-right:35px;">
                        <button name="reset" id="reset" class="btn btn-default btn-sm">{{ trans('admin/assessment.reset') }}</button>
                        <button name="apply" id="apply" class="btn btn-primary btn-sm">{{ trans('admin/assessment.apply_changes') }}</button>
                    </div>
                </div>
                <div class="box-content">
                    @if(!empty($questions))
                        <input type="checkbox" id="select-all-questions" name="delete_all_questions[]" value="" style="margin-left: 10px;">&nbsp;&nbsp;{{ trans('admin/assessment.select_all') }}
                        @foreach($qids as $key => $q)
                        @if(!empty($q))
                        <fieldset id="question-list" style="margin-bottom:5px;">
                            <!-- <legend style="font-size:20px;margin-bottom:5px;">Page {{ $key + 1 }}</legend> -->
                            <?php $sort[] = '#sortable'.$key; ?>
                            <ul id="sortable{{$key}}" class="connectedSortable" style="list-style-type: none;padding:0; margin:0; min-height: 20px;" data-page-id="{{$key}}">
                                <li class="well well-sm" style="margin:5px 0 5px 0;" data-question-id="{{$questions[$q]['question_id']}}">
                                    <input type="checkbox" class="bulk-delete-questions" name="bulk_delete_questions[]" value="{{ $questions[$q]['question_id'] }}">&nbsp;
                                    <b>Q{{ $questions[$q]['question_id'] }}</b>
                                    {{ str_limit(strip_tags($questions[$q]['question_text']), 40) }}
                                    @if(Input::has('qbank'))
                                        <?php $qbank = '&qbank='.Input::get('qbank'); ?>
                                    @else
                                        <?php $qbank = ''; ?>
                                    @endif
                                    <div class="pull-right">
                                        (<span><b>Mark</b>: {{ $questions[$q]['default_mark'] }}, <b>Type</b>: {{ $questions[$q]['difficulty_level'] }}</span>)
                                        @if($attempt == 0)
                                        <?php
                                            $filter = "&randmize=".Input::get('randmize')
                                                     ."&qlimit=".Input::get('qlimit')
                                                     ."&qtags=".Input::get('qtags')
                                                     ."&qdifficulty=".Input::get('qdifficulty')
                                                     ."&qtype=".Input::get('qtype');
                                        ?>
                                        <button data-link="{{ URL::to('/cp/section/remove-section-question/'.$quiz->quiz_id.'/'.$sec_id.'?question='.$questions[$q]['question_id'].$qbank).$filter }}" class="btn btn-circle btn-sm btn-danger show-tooltip remove" title="Remove" >
                                            <i class="fa fa-trash-o"></i>
                                        </button>
                                        @endif
                                    </div>
                                </li>
                            </ul>
                        </fieldset>
                        @endif
                        @endforeach
                        <br>
                        <!-- <a id="add-page" class="btn btn-info btn-sm">Add One More Page</a> -->
                    @else
                        <center>{{ trans('admin/assessment.no_of_ques_to_display') }}</center>
                    @endif
                </div>
            </div>
        </div>
        <div class="col-md-5">
            <div class="box custom-box">
                <div class="box-title">
                    @if($from == 'add' || $from == 'edit' || $from == 'list')
                        @if($from == 'add')
                            <div class="box-tool">
                                <a style="margin-top: -87px;" class="btn btn-primary btn-sm " href="{{ URL::to('cp/section/add-section/'.$quiz->quiz_id.'/'.$sec_id) }}"><i class="fa fa-chevron-left"></i> Back</a>
                            </div>
                        @endif
                        @if($from == 'list')
                            <div class="box-tool">
                                <a style="margin-top: -87px;" class="btn btn-primary btn-sm " href="{{ URL::to('cp/section/list-section/'.$quiz->quiz_id) }}"><i class="fa fa-chevron-left"></i> Back</a>
                            </div>
                        @endif
                    @endif
                    <h3 style="color:black"><i class="fa fa-file"></i>  {{ trans('admin/assessment.assign_ques') }}</h3>
                    <div class="box-tool">
                        <a href="{{ URL::to('cp/section/add-questions/'.$quiz->quiz_id.'/'.$section->section_id) }}"><i class="fa fa-times"></i></a>
                    </div>
                </div>

                <div class="box-content">
                    <script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
                    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
                    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
                <form class="form-horizontal" action="{{ URL::to('cp/section/add-questions/'.$quiz->quiz_id.'/'.$section->section_id.'/'.$from) }}">
                        <div class="form-group">
                          <!--  <label class="col-sm-3 col-lg-2 control-label"></label> -->
                           <div class="col-sm-12 col-lg-12 controls">
                              <select class="form-control input-sm" name="qbank">
                                <option ="">{{ trans('admin/assessment.select_question_bank') }}</option>
                                @if(!empty($questionbank))
                                    @foreach ($questionbank as $qb)
                                    <option value="{{ $qb->question_bank_id }}" @if(Input::get('qbank') == $qb->question_bank_id) selected @endif>{{ $qb->question_bank_name }} ({{ count($qb->questions) - count($qb->draft_questions) }})</option>
                                    @endforeach
                                @endif
                              </select>
                              <?php
                                //dd($qb_error);
                              ?>
                              <span class="help-inline" style="color:#f00">{{$qb_error}}</span>
                           </div>
                        </div>
                        <div class="form-group">
                           <!-- <label class="col-sm-3 col-lg-2 control-label"></label> -->
                           <div class="col-sm-12 col-lg-12 controls">
                             <select class="form-control input-sm chosen" name="qtype" disabled="disabled">
                                <option value="0">{{ trans('admin/assessment.select_question_type') }}</option>            
                                <option value="MCQ" selected @if(Input::get('qtype') == "MCQ") selected @endif>{{ trans('admin/assessment.mcq') }}</option>                                                               
                            </select>
                           </div>
                        </div>
                        <div class="form-group">
                           <!-- <label class="col-sm-3 col-lg-2 control-label"></label> -->
                           <div class="col-sm-12 col-lg-12 controls">
                              <select class="form-control input-sm chosen" name="qdifficulty" >
                                <option value="0">Select the question difficulty</option>            
                                <option value="EASY" @if(Input::get('qdifficulty') == "EASY") selected @endif>{{ trans('admin/assessment.easy') }}</option>
                                <option value="MEDIUM" @if(Input::get('qdifficulty') == "MEDIUM") selected @endif>{{ trans('admin/assessment.medium') }}</option>
                                <option value="DIFFICULT" @if(Input::get('qdifficulty') == "DIFFICULT") selected @endif>{{ trans('admin/assessment.difficult') }}</option>                                    
                            </select>
                           </div>
                        </div>
                         <div class="form-group">
                           <!-- <label for="textfield1" class="col-xs-3 col-lg-2 control-label"></label> -->
                           <div class="col-sm-12 col-lg-12 controls">
                             <input type="text" class="form-control tags medium" value="{{Input::get('qtags')}}" name="qtags" /> 
                           </div>
                        </div>
                         <div class="form-group">
                           <!-- <label for="textfield1" class="col-xs-3 col-lg-2 control-label"></label> -->
                           <div class="col-sm-6 col-lg-6 controls">
                             <input type="number" name="qlimit" id="qlimit" placeholder="No of Questions" class="form-control" value="{{Input::get('qlimit')}}">
                           </div>
                           <div class="col-sm-6 col-lg-6 controls">
                                <input type="checkbox" name="randmize" @if(Input::get('randmize') == "on") checked @endif>Randomize
                           </div>
                        </div>
                         <div class="form-group">
                           <div class="col-sm-3 col-lg-2 controls">
                              <input class="btn btn-info btn-sm" type="submit" name="fsubmit" value="Search">  
                           </div>
                            <div class="col-sm-9 col-lg-10 controls">
                              <span style="margin-top: 5px;" id="no_question_desc" name="no_question_desc"></span>
                            </div>
                        </div>                       
                    </form>
                    <form class="form-horizontal" action="{{ URL::to('cp/section/add-questions/'.$quiz->quiz_id.'/'.$section->section_id.'/'.$from) }}" method="post">
                        <input type="hidden" name="_qb" value="{{ Input::get('qbank') }}">
                        <!-- Hide value reatin state -->
                        <input type="checkbox" class="hide" name="randmize" @if(Input::get('randmize') == "on") checked @endif>
                        <!-- <input type="number" class="hide" name="qlimit" id="qlimit" placeholder="No of Questions" class="form-control" value="{{Input::get('qlimit')}}"> -->
                        <input type="text" class="hide" value="{{Input::get('qtags')}}" name="qtags" />
                        <select class="hide" name="qdifficulty" >
                            <option value="0">Select the question difficulty</option>            
                            <option value="EASY" @if(Input::get('qdifficulty') == "EASY") selected @endif>{{ trans('admin/assessment.easy') }}</option>
                            <option value="MEDIUM" @if(Input::get('qdifficulty') == "MEDIUM") selected @endif>{{ trans('admin/assessment.medium') }}</option>
                            <option value="DIFFICULT" @if(Input::get('qdifficulty') == "DIFFICULT") selected @endif>Difficult</option>                                    
                        </select>
                         <select class="hide" name="qtype">
                                <option value="0">{{ trans('admin/assessment.select_question_type') }}</option>            
                                <option value="MCQ" @if(Input::get('qtype') == "MCQ") selected @endif>{{ trans('admin/assessment.mcq') }}</option>                                                               
                        </select>
                        <!-- Hide Value retain state ends -->
                        @if(!empty($qbank_questions))
                            <?php 
                                $flag = 0;
                                $q_count = 0;
                            ?>
                            @foreach($qbank_questions as $qbq)
                            @if($flag === 0)
                             <?php $flag = 1; ?>
                             <input name="selectall" id="selectall" class="" type="checkbox" value="">&nbsp;&nbsp;{{ trans('admin/assessment.select_all') }}
                             <input type="submit" name="submit" id="addqu" class="btn btn-info btn-sm pull-right" value="Add to section" disabled>
                                <br/><br/>
                            @endif
                            <p class="@if(!is_null($quiz->questions) && in_array($qbq->question_id, $quiz->questions)) hide @endif">
                                <input name="qb_questions[]" class="qbquestion case" type="checkbox" value="{{ $qbq->question_id }}" @if(!is_null($quiz->questions) && in_array($qbq->question_id, $quiz->questions)) disabled @endif> 
                                Q{{ $qbq->question_id }}
                                @if(!is_null($quiz->questions) && in_array($qbq->question_id, $quiz->questions)) 
                                @else 
                                <?php $q_count++;?>
                                @endif
                                @if($qbq['difficulty_level'] == 'EASY') <span class="label label-info">{{ trans('admin/assessment.easy') }}</span> @endif
                                @if($qbq['difficulty_level'] == 'MEDIUM') <span class="label label-info">{{ trans('admin/assessment.medium') }}</span> @endif
                                @if($qbq['difficulty_level'] == 'DIFFICULT') <span class="label label-info">{{ trans('admin/assessment.difficult') }}</span> @endif
                                <b>{{ str_limit(strip_tags($qbq->question_text), 40) }}<span style='border:1px solid #999999; border-radius:50px;padding:4px;'>{{$qbq->default_mark}}M</span></b>
                            </p>
                            @endforeach
                            <input type="submit" name="submit" id="addq" class="btn btn-info btn-sm pull-right" value="Add to section" disabled>
                            <br>
                             <script type="text/javascript">
                                 $('#no_question_desc').html('');
                                $('#no_question_desc').html('<p>{{$q_count}} question(s) found </p>');
                            </script>
                        @endif
                    </form>
                </div>
            </div>
        </div>
    </div>
<script type="text/javascript">
 $("#selectall").click(function () {
          $('.case').prop('checked', this.checked);
            var checkedAtLeastOne = false;
            $('.qbquestion').each(function() {
                if($(this).is(":checked")) {
                    checkedAtLeastOne = true;
                }
            });
            if(checkedAtLeastOne){
                $('#addq').removeAttr('disabled');
                $('#addqu').removeAttr('disabled');
            }
            else{
                $('#addq').attr("disabled", true);
                $('#addqu').attr("disabled", true);
            }
    });
</script>
<!-- delete model -->
    <div class="modal fade" id="deletemodal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <div class="row custom-box">
                        <div class="col-md-12">
                            <div class="box">
                                <div class="box-title">
                                    <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                    <h3 class="modal-header-title" >
                                        <i class="icon-file"></i>
                                        {{ trans('admin/assessment.question_delete') }}
                                    </h3>                 
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-body" style="padding-left: 20px;">
                <p>{{ trans('admin/assessment.question_delete_confirmation') }}</p>
                </div>
                <div class="modal-footer">
                <span id="question_delete_id">
                 </span>
                    <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/assessment.close') }}</a>
                </div>
            </div>
        </div>
    </div>
    <!-- delete model code ends here -->

    <script src="{{ URL::asset('admin/assets/jquery-ui/jquery-ui.min.js')}}"></script>
    <script type="text/javascript">
        $(document).on('change', '.qbquestion', function(){
            var checkedAtLeastOne = false;
            $('.qbquestion').each(function() {
                if($(this).is(":checked")) {
                    checkedAtLeastOne = true;
                }
            });
           if(checkedAtLeastOne){
                $('#addq').removeAttr('disabled');
                $('#addqu').removeAttr('disabled');
            }
            else{
                $('#addq').attr("disabled", true);
                $('#addqu').attr("disabled", true);
            }
        });
        $(function() {
            $('#alert-success').delay(5000).fadeOut();
            $("{{ implode(',', $sort) }}").sortable({
                connectWith: ".connectedSortable",
                change: function(event, ui) {
                    $('#apply').show();
                }
            }).disableSelection();
            $('#reset').click(function(e){
                if(confirm("Are you sure you don't want to save the changes?")) {
                    location.reload();
                }
                e.stopImmediatePropagation();                
            });
            $('#apply').click(function(e){
                e.preventDefault();
                var question = new Array();
                var i = 0;
                $('#question-list ul').each(function() {
                    var page = $(this).data('page-id');
                    var data = new Array();
                    $(this).children('li').each(function(index, value) {
                        data.push($(value).data('question-id'));
                    });
                    question[i++] = data;
                });
                $.ajax({
                    type: "POST",
                    url: "{{ url('cp/section/add-question-ajax/'.$quiz->quiz_id.'/'.$section->section_id) }}",
                    data: 'action=sort&ids='+JSON.stringify(question)
                })
                .done(function(response) {
                    location.reload();
                })
                .fail(function(response) {
                    alert( "Error while updating the quiz. Please try again" );
                });
            });
       
            // loading a modal for delete quize question
            $(".remove").bind("click", function(){
                $("#deletemodal").modal("show");
                var current = $(this).attr('data-link');
                 $("#question_delete_id").html('<a style="display: inline-block;padding: 6px 18px;margin-bottom: 0;margin-left=20px;font-size: 14px;" class="btn btn-danger" href="'+current+'">YES</a>');
            });
            // loading the modal ends here

            $('#add-page').click(function(e){
                e.preventDefault();
                $.ajax({
                    type: "POST",
                    url: "{{ url('cp/section/add-question-ajax/'.$quiz->quiz_id) }}",
                    data: 'action=add-page'
                })
                .done(function(response) {
                    if(response.status == 'success')
                        location.reload();
                    else
                        alert(response.message);
                })
                .fail(function(response) {
                    alert( "Error while updating the quiz. Please try again" );
                });
            });
        });
         $('input.tags').tagsInput({
                    width: "auto"
                });
    </script>
    @else
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-title">
                    <h3 style="color:black"><i class="fa fa-file"></i>List of Questions</h3>
                    <div class="box-tool">
                        @if($attempt == 0)
                            <a style="margin-top: -87px;" class="btn btn-primary btn-sm " href="{{ URL::to('cp/section/add-questions/'.$quiz->quiz_id.'/'.$sec_id.'/'.$from.'?qbank=0') }}">{{ trans('admin/assessment.manage_question') }}</a>
                        @endif
                        <a href="{{ URL::to('cp/assessment/list-quiz') }}" style="margin-right: 1%;margin-top:-68px;" class="btn btn-primary btn-sm pull-right">
                            <span class="btn btn-circle blue custom-btm"> 
                                <i class="fa fa-chevron-left"></i> 
                            </span>
                             {{ trans('admin/assessment.back_to_quiz') }}
                        </a>
                    </div>                    
                </div>
                <div class="box-content">
                    @if(!empty($questions))
                        @foreach($qids as $key => $q)
                        <fieldset id="question-list" style="margin-bottom:5px;">
                            <!-- <legend style="font-size:20px;margin-bottom:5px;">Page {{ $key + 1 }}</legend> -->
                            <ul style="list-style-type: none;padding:0; margin:0; min-height: 20px;">
                                <li class="well well-sm" style="margin:5px 0 5px 0;">
                                    <b>{{ $questions[$q]['question_name'] }}</b>
                                    {{ str_limit(strip_tags($questions[$q]['question_text']), 100) }}
                                    @if(Input::has('qbank'))
                                        <?php $qbank = '&qbank='.Input::get('qbank'); ?>
                                    @else
                                        <?php $qbank = ''; ?>
                                    @endif
                                    <div class="pull-right">
                                        (<span><b>Mark</b>: {{ $questions[$q]['default_mark'] }}, <b>Type</b>: {{ $questions[$q]['difficulty_level'] }}</span>)
                                    </div>
                                </li>
                            </ul>
                        </fieldset>
                        @endforeach
                    @else
                        <center>{{ trans('admin/assessment.no_of_ques_to_display') }}</center>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
    <script type="text/javascript">
        /* #select-all-questions is a id for "select all" option */
        $('#select-all-questions').change(function(e) {
            if( this.checked ) {
                $(".bulk-delete-questions").prop('checked', true);     
            } else{
                $(".bulk-delete-questions").prop('checked', false); 
            }
        });
        $('.bulkdelete').click(function(e){ /* bulkdelete class is for bulk icon */
            var values = ""; 
            if($(".bulk-delete-questions:checked").length <= 0) {
                alert("{{ trans('admin/assessment.select_questions_for_delete') }}");
            } else {
                var bulk_delete_checkbox = $('.bulk-delete-questions:checked').each(function(){ 
                    values += $(this).val() + ",";
                });
                if(confirm("{{ trans('admin/assessment.questions_delete_confirmation') }}")) {
                    $.ajax({
                        type: "POST",
                        url: "{{ url('cp/section/ajax-bulk-delete-quiz-questions/'.$quiz->quiz_id.'/'.$sec_id) }}",
                        data: 'delete-ids='+values
                    })
                    .done(function(response) {
                        if(response.status == "success") {
                            $('#delete-msg').html('<div class= "alert alert-success">'+response.message+'<button class="close" data-dismiss="alert">×</button></div>').delay(5000).fadeOut();
                        }
                        location.reload();
                    })
                    .fail(function(response) {
                        alert( "error" );
                    });   
                } 
            }
        });
    </script>
@stop