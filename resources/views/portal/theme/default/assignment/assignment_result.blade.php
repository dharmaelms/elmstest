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
        height: auto !important;
    }
    .modal-footer {
        padding: 5px !important;
    }
    .submitted-data{
        max-height: 165px;
        overflow: auto;
    }
    .dynamic-text p{
        margin: 0 0 0px !important;
    }
    .result-form .result-form-feilds:nth-child(even) {
        background-color: #f9f9f9;
    }
    .result-form .result-form-feilds:nth-child(odd) {
        background-color: #fff;
    }
    .form-label {
        text-align: right;
        padding: 15px;
        font-size: 14px;
    }
    .form-ans {
        border-left: 1px solid #e0e0e0;
        padding: 15px;
        font-size: 14px;
    }
    .form-ans a{
        color: #428bca;
    }
    .result-form-feilds {
        border-bottom: 1px solid #e0e0e0;
    }

    </style>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-timepicker/compiled/timepicker.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-timepicker/js/bootstrap-timepicker.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
    <?php
        use App\Enums\Assignment\SubmissionType;
        $assignment_id = $assignment->id;
        $assignment_name = $assignment->name;
        $assignment_description = $assignment->description;
        $submission_status = $attempt_assignment->submission_status;
        $uploaded_file = $attempt_assignment->uploaded_file;
        $uploaded_text =  html_entity_decode($attempt_assignment->uploaded_text);
        $review_comments = $attempt_assignment->review_comments;
        $grade = $attempt_assignment->grade;
        $status = $attempt_assignment->pass;
        if ($submission_status == SubmissionType::REVIEWED) {
            if ($status) {
                $result = trans('assignment.pass');
            } else {
                $result = trans('assignment.fail');
            }
        }
        $submitted_time = $attempt_assignment->submitted_at;
        $submitted_time = end($submitted_time);
    ?>
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><a href="{{url('dashboard')}}">{{ trans('dashboard.dashboard') }}</a><i class="fa fa-angle-right"></i></li>
            <li><a href="{{ URL::to('/assignment?filter=unattempted') }}">{{ trans('assignment.assignments') }}</a><i class="fa fa-angle-right"></i></li>
            <li><a href="#">{{ $assignment_name }}</a></li>
        </ul>
    </div>

    </p>
	<div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                <div class="row">
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-10">
                        <div class="pull-left">
                            <h2><b>{{ trans('assignment.result') }}</b></h2>
                        </div>
                        <div class="pull-right" style="margin-top:18px">
                            @if ($packet_slug == "unattempted")
                                <a href="{{ URL::to('/assignment?filter=attempted') }}" style="padding: 8px;" class="btn btn-primary"><i class=" fa fa-angle-left"></i>&nbsp;Back</a>
                            @elseif ($packet_slug == "from_reports")
                                <a href="{{ URL::to('/assignment?filter=reports') }}" style="padding: 8px;" class="btn btn-primary"><i class=" fa fa-angle-left"></i>&nbsp;Back</a>
                            @else
                                <a href="{{ URL::to('program/packet/'.$packet_slug. '/element/'.$assignment_id.'/assignment') }}" style="padding: 8px;" class="btn btn-primary pull-right"><i class=" fa fa-angle-left"></i>&nbsp;Back</a>
                            @endif
                        </div>
                        <div class="clearfix"></div>
                    </div>
                </div><br>
                <div class="row">
                    <div class="col-md-1">
                    </div>
                    <div class="col-md-10 result-form" style="border: 1px solid #e7e7e7">
                        <div class="row result-form-feilds">
                            <div class="col-sm-2 col-lg-2 form-label">
                                <span><b>{{ trans('assignment.assignment_title') }}</b></span>
                            </div>
                            <div class="col-sm-10 col-lg-10 form-ans">
                                <span><b>{{ $assignment_name }}</b></span>&nbsp;&nbsp;
                                @if (!empty($assignment_description))
                                    <a data-toggle="modal" data-target="#yourModal"><i class="fa fa-question-circle" style="margin-top: 5px;"></i></a>
                                @endif
                            </div>
                        </div>
                        @if (!empty($assignment->template_file_name))
                        <div class="row result-form-feilds">
                            <div class="col-sm-2 col-lg-2 form-label">
                                <span>{{ trans('assignment.template_file') }} </span>
                            </div>
                            <div class="col-sm-10 col-lg-10 form-ans">
                                <a title="file" href="{{ URL::Route('file-download', ['assignment_id' => $assignment_id, 'packet_slug' => $packet_slug]) }}">
                                {{ $assignment->template_file_name }}
                                </a>
                            </div>
                        </div>
                        @endif
                        @if(!empty($uploaded_file))
                            <div class="row result-form-feilds">
                                <div class="col-sm-2 col-lg-2 form-label">
                                    <span>{{ trans('assignment.submitted_files') }} </span>
                                </div>
                                <div class="col-sm-10 col-lg-10 form-ans">
                                    <a href="{{ URL::Route('uploaded-files', ['assignment_id' => $assignment_id]) }}"><i class="fa fa-file"></i>&nbsp;&nbsp;Click here to download</a>
                                </div>
                            </div>
                        @endif
                        @if(!empty($uploaded_text))
                            <div class="row result-form-feilds">
                                <div class="col-sm-2 col-lg-2 form-label">
                                    <span>{{ trans('assignment.submitted_data')}}</span>
                                </div>
                                <div class="col-sm-10 col-lg-10 form-ans submitted-data">
                                    <span class="dynamic-text">{!!$uploaded_text!!}</span>
                                </div>
                            </div>
                        @endif
                        @if (!empty($submission_status))
                            <div class="row result-form-feilds">
                                <div class="col-sm-2 col-lg-2 form-label">
                                    <span>{{ trans('assignment.status') }} </span>
                                </div>
                                <div class="col-sm-10 col-lg-10 form-ans">
                                    @if ($submission_status == SubmissionType::YET_TO_REVIEW)
                                        <span>{{ trans('admin/assignment.yet_to_review') }}</span>
                                    @elseif ($submission_status == SubmissionType::SAVE_AS_DRAFT)
                                        <span>{{ trans('admin/assignment.draft') }}</span>
                                    @elseif ($submission_status == SubmissionType::LATE_SUBMISSION)
                                        <span>{{ trans('admin/assignment.late_submission') }}</span>
                                    @else
                                        <span>{{ trans('admin/assignment.reviewed') }}</span>
                                    @endif
                                </div>
                            </div>
                        @endif
                        @if (!empty($review_comments))
                            <div class="row result-form-feilds">
                                <div class="col-sm-2 col-lg-2 form-label">
                                    <span>{{ trans('assignment.review_comments') }}  </span>
                                </div>
                                <div class="col-sm-10 col-lg-10 form-ans" style="max-height: 180px;overflow: auto;">
                                    <span class="dynamic-text">{!! $review_comments !!}</span>
                                </div>
                            </div>
                        @endif
                        @if ($grade > 0)
                            <div class="row result-form-feilds">
                                <div class="col-sm-2 col-lg-2 form-label">
                                    <span>{{ trans('assignment.grade') }}</span>
                                </div>
                                <div class="col-sm-10 col-lg-10 form-ans">
                                    <span>{{ $grade.' Out of '.$assignment->grade }}</span>
                                </div>
                            </div>
                        @endif
                        @if ((!empty($assignment->grade_cutoff)) && ($assignment->grade_cutoff != 0) && ($grade > 0))
                            <div class="row result-form-feilds">
                                <div class="col-sm-2 col-lg-2 form-label">
                                    <span>{{ trans('assignment.cutoff') }}  </span>
                                </div>
                                <div class="col-sm-10 col-lg-10 form-ans">
                                    <span>{{ $assignment->grade_cutoff }}</span>
                                </div>
                            </div>
                        @endif
                        @if ($submission_status == SubmissionType::REVIEWED)
                            <div class="row result-form-feilds">
                                <div class="col-sm-2 col-lg-2 form-label">
                                    <span>{{ trans('assignment.result') }} </span>
                                </div>
                                <div class="col-sm-10 col-lg-10 form-ans">
                                    <span>{{ $result }}</span>
                                </div>
                            </div>
                        @endif
                        <div class="row result-form-feilds">
                            <div class="col-sm-2 col-lg-2 form-label">
                                <span>{{ trans('assignment.submitted_at') }}</span>
                            </div>
                            <div class="col-sm-10 col-lg-10 form-ans">
                                <span>{{Timezone::convertFromUTC('@'.$submitted_time, Auth::user()->timezone, 'D, d  M Y, g:i a')}}</span>
                            </div>
                        </div>
                    </div>
                </div>
                </div>
            </div>
            <!-- Modal to display assignment description -->
            <div class="modal fade" id="yourModal" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span class="font-16"><strong>{{$assignment_name}}</strong></span>
                        </div>
                        <div class="modal-body">
                            {!! $assignment_description !!}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">Close</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal to display assignment ends -->
        </div>
    </div>
    <script type="text/javascript">
        $('.alert-danger, .alert-success').delay(5000).fadeOut();
    </script>
@stop