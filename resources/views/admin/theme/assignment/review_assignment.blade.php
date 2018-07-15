@section('content')
    @if ( Session::get('success') )
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Success!</strong> -->
            {{ Session::get('success') }}
        </div>
        <?php Session::forget('success'); ?>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            <!-- <strong>Error!</strong> -->
            {{ Session::get('error') }}
        </div>
        <?php Session::forget('error'); ?>
    @endif
    <style type="text/css">
    .cke_contents {
        height: 100px !important;
    }
    label {
        padding-top: 0px !important;
    }
    </style>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <?php
        use App\Enums\Assignment\SubmissionType;
        $assignment_id = $assignment->id;
        $submitted_at = $assignment_attempt->submitted_at;
        $uploaded_text =  html_entity_decode(isset($assignment_attempt->uploaded_text) ? $assignment_attempt->uploaded_text:"");
        $latest_submitted_at = $submitted_at[count($submitted_at)-1];
        $latest_submitted_at = Timezone::convertFromUTC('@' . $latest_submitted_at, Auth::user()->timezone, config('app.date_format'));
        $submission_status_data = $assignment_attempt->submission_status;
        if ($submission_status_data == SubmissionType::YET_TO_REVIEW) {
            $submission_status = trans('admin/assignment.yet_to_review');
        } elseif ($submission_status_data == SubmissionType::SAVE_AS_DRAFT) {
            $submission_status = trans('admin/assignment.draft');
        } else {
            $submission_status = trans('admin/assignment.reviewed');
        }

        $yet_to_grade = trans('admin/assignment.yet_to_grade');
        $graded = trans('admin/assignment.grade').'d';
        $assignment_grade = 100;
        if ((!empty($assignment->grade)) && ($assignment->grade != 0)) {
            $assignment_grade = $assignment->grade;
        }
    ?>
	<div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    <form action="{{ URL::Route('post-review-assignment', ['assignment_id' => $assignment_id, 'user_id' => $assignment_attempt->user_id]) }}" class="form-horizontal form-bordered form-row-stripped" method="post" id="survey_form" onsubmit="formSubmit()">
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.assignment_title')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                {{ $assignment->name }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.submission_status')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                {{ $submission_status }}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.grading_status')}}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                @if (!empty($assignment_attempt))
                                    @if (!empty($assignment_attempt->grade))
                                        {{ $graded }}
                                    @else
                                        {{ $yet_to_grade }}
                                    @endif
                                @else
                                    {{ $yet_to_grade }}
                                @endif
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assignment.modified_date') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                {{ $latest_submitted_at }}
                            </div>
                        </div>
                        @if ($assignment->submission_type == "file_submission")
                            <div class="form-group">
                                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assignment.submitted_files') }}</label>
                                <div class="col-sm-9 col-lg-6 controls">
                                    <a href="{{ URL::Route('uploaded-files', ['assignment_id' => $assignment_id, 'user_id' => $assignment_attempt->user_id]) }}"><i class="fa fa-file"></i>&nbsp;&nbsp;{{ trans('assignment.click_here_to_download') }}</a>
                                </div>
                            </div>
                        @endif    
                        @if (!empty($uploaded_text))
                            <div class="form-group">
                                <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assignment.submitted_data') }} </label>
                                <div class="col-sm-9 col-lg-6 controls" style="max-height: 134px;overflow: auto;border: 1px solid #e7e7e7">
                                    {!! $uploaded_text !!}
                                </div>
                            </div>
                        @endif
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assignment.review_comments') }}</label>
                            <?php
                                if (Input::old('review_comments')) {
                                    $review_comments = Input::old('review_comments');
                                } else {
                                    $review_comments = $assignment_attempt->review_comments;
                                }
                            ?>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="review_comments" rows="5" class="form-control ckeditor">{!! $review_comments !!}</textarea>
                                {!! $errors->first('review_comments', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/assignment.grade_outoff').$assignment_grade}}<span class="red">*</span></label>
                            <?php
                                if (Input::old('grade')) {
                                    $grade = Input::old('grade');
                                } else {
                                    $grade = $assignment_attempt->grade;
                                }
                            ?>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="number" min="0" max="{{ $assignment_grade }}" minlength="1" maxlength="3" name="grade" id="grade" class="form-control" value="{{ $grade }}" style="width: 30%;">
                                {!! $errors->first('grade', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{trans('admin/survey.save')}}</button>
                               <a href="{{ URL::Route('get-grade-assignment', ['assignment_id' => $assignment->id, '$submission_type' => $submission_status]) }}"><button type="button" class="btn">{{trans('admin/survey.cancel')}}</button></a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('#survey_title,.general-input-start-date,.general-input-end-date,.general-input-start-time,.general-input-end-time,.score_display').on('keyup keypress', function(e) {
              var keyCode = e.keyCode || e.which;
              if (keyCode === 13) {
                e.preventDefault();
                return false;
              }
            });
        });
        function formSubmit() {
            $(".btn-success").attr('disabled', true);
        }
    </script>
@stop