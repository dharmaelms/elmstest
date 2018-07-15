
@section('content')
@if ( Session::get('success') )
        <div class="alert alert-success">
            <button class="close" data-dismiss="alert">×</button>
            
            {{ Session::get('success') }}
        </div>
        <?php Session::forget('success'); ?>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
            <button class="close" data-dismiss="alert">×</button>
            
            {{ Session::get('error') }}
        </div>
        <?php Session::forget('error'); ?>
    @endif
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
   
    <div class="form-wrapper">
    <form action="{{URL::to('cp/manageweb/upload-staticpage')}}" class="form-horizontal form-bordered form-row-stripped" method="post" id="form-addannouncement" style="margin-top:10px;" enctype='multipart/form-data' > 
        <div class="box">
            <div class="box-title">
                <!-- <h3 style="color:black"><i class="fa fa-file"></i> Add Page</h3>                 -->
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.title')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <input type="text" name="title" class="form-control" value="{{Input::old('title')}}" />
                    {!! $errors->first('title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.meta_keywords')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <textarea class="form-control" rows="1" name="meta_key" >{{Input::old('meta_key')}}</textarea>
                    {!! $errors->first('meta_key', '<span   class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.meta_description')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <textarea class="form-control" rows="1" name="meta_description" >{{Input::old('meta_description')}}</textarea>
                    {!! $errors->first('meta_description', '<span   class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
           <!--  <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> Content<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <textarea class="form-control" rows="3" name="static_page_content" >{{Input::old('static_page_content')}}</textarea>
                    {!! $errors->first('static_page_content', '<span   class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div> -->
            <div class="form-group">
                <label for="static_page_content" class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.content')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-6 controls">
                    <textarea name="static_page_content" rows="5" id="addcont" class="form-control ckeditor">{!! Input::old('static_page_content') !!}</textarea>
                    {!! $errors->first('static_page_content', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            @if(!empty(Input::old('editor_images')))
                @foreach(Input::old('editor_images') as $image)
                     <input type="hidden" name="editor_images[]" value={{ $image }}>
                @endforeach
            @endif
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.status')}} </label>
                <div class="col-sm-9 col-lg-10 controls">
                    <label class="radio-inline">
                        <input type="radio" name="status" value="ACTIVE" checked="true"  /> 
                            ACTIVE
                    </label>
                    <label class="radio-inline">
                         <input type="radio" name="status" value="INACTIVE" /> 
                         INACTIVE
                    </label>
                   
                    {!! $errors->first('Status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary">{{trans('admin/manageweb.upload')}}</button>
                    
                    <a href="{{URL::to('/cp/manageweb/static-pages')}}" ><button type="button" class="btn">{{trans('admin/manageweb.cancel')}}</button></a>
                </div>
                <!-- <a href="#modal-1" role="button" class="btn" data-toggle="modal">Basic modal</a> -->
            </div>

        </div>
    </div>
</div>
  
    </form>
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">
        $(function(){
            $('input.tags').tagsInput({
                width: "auto"
            });
        });
        CKEDITOR.replace( 'addcont', {
            filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
        });
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;
    </script>
@stop   