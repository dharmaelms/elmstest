
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
        if(isset($faq)){
            $question=$faq->question;
            $answer=$faq->answer;
            $status=$faq->status;
            $slug=$faq->faq_id;
        }else{
            $question="";
            $answer="";
            $status="";
            $slug="";
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
    <form action="{{URL::to('cp/manageweb/faq-update/'.$slug)}}" class="form-horizontal form-bordered form-row-stripped" method="post" id="form-addannouncement" style="margin-top:10px;" enctype='multipart/form-data' > 
        
        <div class="box">
            <div class="box-title">
                <!-- <h3 style="color:black"><i class="fa fa-file"></i> {{trans('admin/manageweb.edit_faq')}}</h3>                 -->
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.question')}}<span class="red">*</span></label>
                <div class="col-sm-9 col-lg-10 controls">
                    <input type="text" name="question" class="form-control" value="{{$question}}" />
                    {!! $errors->first('question', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.answer')}}<span class="red">*</span></label>
                 <div class="col-md-10 col-lg-10 controls">
                    <div class="col-md-8 col-lg-8 rich-text-content">
                        <textarea  rows="1" name="answer" id="answer_faq" >{{$answer}}</textarea>
                        {!! $errors->first('answer', '<span   class="help-inline" style="color:#f00">:message</span>') !!}
                    </div>
                </div>
            </div>
            <div class="form-group">
                <label class="col-sm-3 col-lg-2 control-label">{{trans('admin/manageweb.status')}} </label>
                <div class="col-sm-9 col-lg-10 controls">
                    <label class="radio-inline">
                        <input type="radio" name="status" value="ACTIVE" <?php $chk =($status == 'ACTIVE') ? 'checked="true"':'false'; echo $chk;?> /> 
                            ACTIVE
                    </label>
                    <label class="radio-inline">
                         <input type="radio" name="status" value="INACTIVE" <?php $chk =($status == 'INACTIVE') ? 'checked="true"':'false'; echo $chk;?>/> 
                         INACTIVE
                    </label>
                   
                    {!! $errors->first('Status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
                <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                    <button type="submit" class="btn btn-primary">{{trans('admin/manageweb.upload')}}</button>
                    <a href="{{URL::to('/cp/manageweb/')}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}" ><button type="button" class="btn">{{trans('admin/manageweb.cancel')}}</button></a>
                </div>
                <!-- <a href="#modal-1" role="button" class="btn" data-toggle="modal">Basic modal</a> -->
            </div>

        </div>
    </div>
</div>
  
    </form>
    
    <script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
    <script type="text/javascript">

        CKEDITOR.replace( 'answer_faq', {
            filebrowserImageUploadUrl: "{{ URL::to('upload?command=QuickUpload&type=Images') }}"
        });;
        CKEDITOR.config.disallowedContent = 'script; *[on*]';
        CKEDITOR.config.height = 150;
    </script>
@stop   