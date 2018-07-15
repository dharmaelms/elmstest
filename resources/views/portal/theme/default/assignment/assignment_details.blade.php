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
    .fileupload a{
        color: #555555 !important;
    }
    .pip {
        text-align: left;
        padding-top: 5px;
    }
    .modal-footer {
        padding: 5px !important;
    }
    .details-form .result-form-feilds:nth-child(even) {
        background-color: #f9f9f9;
    }
    .details-form .result-form-feilds:nth-child(odd) {
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
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">

    <?php
        use App\Enums\Assignment\SubmissionType;
        $assignment_id = $assignment->id;
        $assignment_title = $assignment->name;
        $submission_type = $assignment->submission_type;
        $assignment_description = $assignment->description;
        $start_time = Timezone::convertFromUTC('@' . $assignment->start_time, Auth::user()->timezone, 'D, d  M Y, g:i a');
        $end_time = Timezone::convertFromUTC('@' . $assignment->end_time, Auth::user()->timezone, 'D, d  M Y, g:i a');
        $cutoff_time = Timezone::convertFromUTC('@' . $assignment->cutoff_time, Auth::user()->timezone, 'D, d  M Y, g:i a');
    ?>
    <div class="page-bar">
        <ul class="page-breadcrumb">
            <li><a href="{{url('dashboard')}}">{{ trans('dashboard.dashboard') }}</a><i class="fa fa-angle-right"></i></li>
            <li><a href="{{ URL::to('/assignment?filter=unattempted') }}">{{ trans('assignment.assignments') }}</a></li>
            <li><a href="#"></a></li>
        </ul>
    </div>
    <div class="row">
        <div class="col-md-12">
            <div class="box">
                <div class="box-content">
                    <div class="row">
                        <div class="col-md-1">
                        </div>
                        <div class="col-md-10 details-form" style="border: 1px solid #e7e7e7">
                            <form action="{{ URL::Route('submit-assignment',['packet_slug' => $packet_slug, 'assignment_id' => $assignment_id]) }}" method="POST" id="survey_form" onsubmit="formSubmit()" enctype="multipart/form-data">
                                <div class="row result-form-feilds">
                                    <div class="col-sm-2 col-lg-2 form-label">
                                        <span><b>{{ trans('assignment.assignment_title') }} </b></span>
                                    </div>
                                    <div class="col-sm-10 col-lg-10 form-ans">
                                        <span><b>{{ $assignment_title }}</b></span>&nbsp;&nbsp;
                                        @if (!empty($assignment_description))
                                            <a data-toggle="modal" data-target="#yourModal"><i class="fa fa-question-circle" style="margin-top: 5px;"></i></a>
                                        @endif
                                    </div>
                                </div>
                                <div class="row result-form-feilds">
                                    <div class="col-sm-2 col-lg-2 form-label">
                                        <span>{{ trans('assignment.starts') }} </span>
                                    </div>
                                    <div class="col-sm-10 col-lg-10 form-ans">
                                        <span>{{ $start_time }}</span>&nbsp;&nbsp;
                                    </div>
                                </div>
                                <div class="row result-form-feilds">
                                    <div class="col-sm-2 col-lg-2 form-label">
                                        <span>{{ trans('assignment.ends') }} </span>
                                    </div>
                                    <div class="col-sm-10 col-lg-10 form-ans">
                                        <span>{{ $end_time }}</span>&nbsp;&nbsp;
                                    </div>
                                </div>
                                <div class="row result-form-feilds">
                                    <div class="col-sm-2 col-lg-2 form-label">
                                        <span>{{ trans('assignment.cutoff') }} </span>
                                    </div>
                                    <div class="col-sm-10 col-lg-10 form-ans">
                                        <span>{{ $cutoff_time }}</span>&nbsp;&nbsp;
                                    </div>
                                </div>

                                @if (!empty($assignment->template_file_name))
                                <div class="row result-form-feilds">
                                    <div class="col-sm-2 col-lg-2 form-label">
                                        <span>{{ trans('assignment.template_file')}}</span>
                                    </div>
                                    <div class="col-sm-10 col-lg-10 form-ans">
                                        <a class="show-tooltip" title="file" href="{{ URL::Route('file-download', ['assignment_id' => $assignment_id, 'packet_slug' => $packet_slug]) }}">
                                        {{ $assignment->template_file_name }}&nbsp;&nbsp;
                                        </a>
                                    </div>
                                </div>
                                @endif

                                @if(!empty($assignment_attempt))
                                    @if(($assignment_attempt->submission_status == SubmissionType::SAVE_AS_DRAFT) && ($submission_type == "file_submission"))
                                    <div class="row result-form-feilds">
                                        <div class="col-sm-2 col-lg-2 form-label">
                                            <span>{{ trans('assignment.drafted_files') }} </span>
                                        </div>
                                        <div class="col-sm-10 col-lg-10 form-ans">
                                            <a href="{{ URL::Route('uploaded-files', ['assignment_id' => $assignment_id]) }}"><i class="fa fa-file"></i>&nbsp;&nbsp;{{ trans('assignment.click_here_to_download') }}</a>
                                        </div>
                                    </div>
                                    @endif
                                @endif

                                @if($submission_type == "file_submission")
                                <div class="row result-form-feilds">
                                    <div class="col-sm-2 col-lg-2 form-label" style="padding-top: 20px;">
                                        <span>{{ trans('assignment.upload_file') }}<span class="red">*</span></span>
                                    </div>
                                    <div class="col-sm-10 col-lg-10 form-ans fileupload fileupload-new" data-provides="fileupload" style="margin-bottom: unset;">
                                        <div class="col-sm-12 col-lg-12" style="padding-left: 0px;">
                                            <div class="pull-left">
                                                <a class="btn btn-default btn-sm btn-file" style="background-color: #e0e0e0;margin-right: 5px">
                                                    <span class="fileupload-new">{{ trans('assignment.select_files') }}</span>
                                                    <span class="fileupload-exists">{{ trans('assignment.change_files') }}</span>
                                                    <input type="file" id="multiple_files" name="multiple_files[]"  class="file-input" multiple="multiple"/>
                                                </a>
                                            </div>
                                            <div class="pull-left">
                                                <a href="#" class="btn btn-default btn-sm fileupload-exists" data-dismiss="fileupload" style="background-color: #e0e0e0;">{{ trans('assignment.reset_files') }}</a>
                                            </div>
                                            @if ($assignment->max_no_file_allowed == 1)
                                            <div class="pull-left" style="padding: 5px;">
                                                <span style="color:blue">&nbsp;&nbsp;Max {{ $assignment->max_no_file_allowed }} file is allowed for this assignment.</span>
                                            </div>
                                            @else
                                            <div class="pull-left" style="padding: 5px;">
                                                <span style="color:blue">&nbsp;&nbsp;Max {{ $assignment->max_no_file_allowed }} files are allowed for this assignment.</span>
                                            </div>
                                            @endif
                                        </div>
                                        <br><ul id="file-preview"></ul>
                                        {!! $errors->first('multiple_files', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                @endif
                                <?php
                                    if (Input::old('uploaded_text')) {
                                        $uploaded_text = Input::old('uploaded_text');
                                    } else {
                                        if (!empty($assignment_attempt)) {
                                            $uploaded_text = $assignment_attempt->uploaded_text;
                                        } else {
                                            $uploaded_text = "";
                                        }
                                    }
                                ?>
                                <div class="row result-form-feilds">
                                    <div class="col-sm-2 col-lg-2 form-label">
                                        <span>{{ trans('assignment.edit_assignment') }}<span class="red">*</span></span>
                                    </div>
                                    <div class="col-sm-10 col-lg-10 form-ans">
                                        <textarea name="uploaded_text" rows="5" class="form-control ckeditor">{!! $uploaded_text !!}</textarea>
                                        {!! $errors->first('uploaded_text', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                                    </div>
                                </div>
                                <div class="row">
                                    <div class="col-sm-12 col-lg-12 form-ans" style="text-align: center">
                                        <button type="submit" value="submit" class="btn btn-success" onclick="return confirm('{{ trans('assignment.confirm_submit') }}')"><i class="fa fa-check"></i>{{ trans('assignment.save') }}</button>
                                       <button type="submit" value="draft" class="btn btn-success"><i class="fa fa-download"></i> {{ trans('assignment.save_as_draft') }}</button>
                                        @if ($packet_slug == "unattempted")
                                            <a href="{{ URL::to('assignment') }}"><button type="button" class="btn btn-danger"> <i class="fa fa-times"></i> {{ trans('assignment.cancel') }}</button></a>
                                        @elseif ($packet_slug == "from_reports")
                                            <a href="{{ URL::to('/assignment?filter=reports') }}"><button type="button" class="btn btn-danger"><i class="fa fa-times"></i> {{ trans('assignment.cancel') }}</button></a>
                                        @else
                                            <a href="{{ URL::to('program/packet/'.$packet_slug. '/element/'.$assignment_id.'/assignment') }}"><button type="button" class="btn btn-danger"><i class="fa fa-times"></i> {{ trans('assignment.cancel') }}</button></a>
                                        @endif
                                    </div>
                                </div>
                                <input type="hidden" name="form_action" id="form_action" value="">
                            </form>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal to display assignment description -->
            <div class="modal fade" id="yourModal" tabindex="-1" role="dialog">
                <div class="modal-dialog">
                    <div class="modal-content">
                        <div class="modal-header">
                            <span class="font-16"><strong>{{$assignment_title}}</strong></span>
                        </div>
                        <div class="modal-body">
                            {!! $assignment_description !!}
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-primary" data-dismiss="modal">{{ trans('assignment.close') }}</button>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal to display assignment ends -->
        </div>
    </div>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">
        $(document).ready(function() {
            $("input#multiple_files").change(function() {
                $(".pip").remove();
                var ele = document.getElementById($(this).attr('id'));
                var result = ele.files;
                for(var x = 0;x< result.length;x++){
                    var fle = result[x];
                    $("<li class=\"pip\"><a>" +
                        fle.name+" "+
                        "<br/>" +
                        "</a></li>").insertAfter("#file-preview");
                }
            });
            $('button').click(function() {
                $("#form_action").val($(this).val());
            });
        });
        function formSubmit() {
            $(".btn-success").attr('disabled', true);
        }
        $('.alert-danger, .alert-success').delay(5000).fadeOut();
    </script>
@stop