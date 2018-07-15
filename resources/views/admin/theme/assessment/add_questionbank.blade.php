@section('content')
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
	<div class="row">
	    <div class="col-md-12">
	        <div class="box">
	            <div class="box-title">
	                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Add question bank</h3> -->
	            </div>
	            <div class="box-content">
                    <form action="#" class="form-horizontal form-bordered form-row-stripped" method="post" accept-Charset="UTF-8">
                        <div class="form-group">
                            <label for="question_bank_name" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.question_bank_name') }} <span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="question_bank_name" class="form-control" value="{{ Input::old('question_bank_name') }}" >
                                {!! $errors->first('question_bank_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="question_bank_description" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.question_bank_description') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="question_bank_description" rows="5" id="addqb" class="form-control ckeditor">{!! Input::old('question_bank_description') !!}</textarea>
                                {!! $errors->first('question_bank_description', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="keywords" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/assessment.keywords_tags') }}</label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <input type="text" name="keywords" class="form-control tags" value="{{ Input::old('keywords') }}">
                                {!! $errors->first('keywords', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            </div>
                        </div>
                        @if(!empty(Input::old('editor_images')))
                        @foreach(Input::old('editor_images') as $image)
                            <input type="hidden" name="editor_images[]" value={{ $image }}>
                        @endforeach
                        @endif
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success" ><i class="fa fa-check"></i> {{ trans('admin/assessment.save') }}</button>
                               <a href="{{URL::to('/cp/assessment/list-questionbank')}}"><button type="button" class="btn">{{ trans('admin/assessment.cancel') }}</button></a>
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
            $('input.tags').tagsInput({
                width: "auto"
            });
        });
        CKEDITOR.replace( 'addqb', {
            filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
        });
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;
    </script>
@stop