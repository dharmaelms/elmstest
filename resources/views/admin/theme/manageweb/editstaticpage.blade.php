
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
    <?php   
        if(isset($staticpage)){
            $title=$staticpage->title;
            $metakey=$staticpage->metakey;
            $meta_description=$staticpage->meta_description;
            $content=$staticpage->content;
            $slug =$staticpage->staticpagge_id;
            $status = $staticpage->status;
        }

        $start    =  Input::get('start', 0);
        $limit    =  Input::get('limit', 10);
        $filter   =  Input::get('filter','ACTIVE');
        $search   =  Input::get('search','');
        $order_by =  Input::get('order_by','1 desc');
?>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.css')}}">
    <script src="{{ URL::asset('admin/assets/jquery-tags-input/jquery.tagsinput.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.css')}}">
    <script src="{{ URL::asset('admin/assets/bootstrap-fileupload/bootstrap-fileupload.js')}}"></script>
   
    <div class="form-wrapper">
    <form action="{{URL::to('cp/manageweb/edit-static-page-update/'.$slug)}}" class="form-horizontal form-bordered form-row-stripped" method="post" id="form-addannouncement" style="margin-top:10px;" enctype='multipart/form-data' > 

        <div class="box">
            <div class="box-title">
                <!-- <h3 style="color:black"><i class="fa fa-file"></i> {{trans('admin/manageweb.edit_page')}}</h3>                 -->
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.title')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <?php if(!in_array($slug,$default_static_page_ids)){ ?>
                    <input type="text" name="title" class="form-control" value="{{$title}}" />
                    <?php }else{ ?>
                    <input type="text" name="title" class="form-control" readonly value="{{$title}}" />
                    <?php } ?>
                    {!! $errors->first('title', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.meta_keywords')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <textarea class="form-control" rows="1" name="meta_key" >{{$metakey}}</textarea>
                    {!! $errors->first('meta_key', '<span   class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label"> {{trans('admin/manageweb.meta_description')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <textarea class="form-control" rows="1" name="meta_description" >{{$meta_description}}</textarea>
                    {!! $errors->first('meta_description', '<span   class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
           <div class="form-group">
                            <label for="content" class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.content')}}<span class="red">*</span></label>
                            <div class="col-sm-9 col-lg-6 controls">
                                <textarea name="static_page_content" rows="5" id="addcont" class="form-control ckeditor">{!! $content !!}</textarea>
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
                        <input type="radio" name="status" value="ACTIVE" <?php $chk =($status == 'ACTIVE') ? 'checked="true"':'false'; echo $chk; if(in_array($slug,$default_static_page_ids)){?> readonly <?php } ?>/> 
                            ACTIVE
                    </label>
                    <label class="radio-inline">
                         <input type="radio" name="status" value="INACTIVE" <?php $chk =($status == 'INACTIVE')  ? 'checked="true"':'false'; echo $chk;if(in_array($slug,$default_static_page_ids)){?> readonly <?php } ?>/> 
                         INACTIVE
                    </label>
                   
                    {!! $errors->first('Status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary">{{trans('admin/manageweb.upload')}}</button>
                    
                    <a href="{{URL::to('/cp/manageweb/static-pages')}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}" ><button type="button" class="btn">{{trans('admin/manageweb.cancel')}}</button></a>
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