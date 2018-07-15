@section('content')
<style type="text/css">
    .editor{height: 200px; overflow: auto;}   
    .windowLabel > span{margin: 2px 0 0 2px; font-size: 10px; font-weight: bold;}
</style>
<script type="text/javascript" src="{{ URL::asset('admin/assets/ckeditor/ckeditor.js')}}"></script>
<script src="{{ URL::asset('admin/assets/ckeditor/plugins/ckeditor_wiris/integration/WIRISplugins.js?viewer=image') }}"></script>
<div class="box">
    <div class="box-content">
        <div class="form-wrapper">            
            <form id="add-flashcards" class="form-horizontal form-row-stripped" enctype="multipart/form-data" method="post" >
                <div class="box">
                    <div class="box-title"></div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/flashcards.name') }}<span class="red">*</span></label>
                        <div class="col-sm-9 col-lg-10 controls">
                            <input type="text" class="form-control" name="name">
                            <span class="help-block error" id ="name_error"></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/flashcards.description') }}</label>
                        <div class="col-sm-9 col-lg-10 controls">
                            <textarea rows="6" cols="50" type="text" class="form-control" name="description" ></textarea>
                            <span class="help-block error" id ="description_error"></span>
                        </div>
                    </div>
                </div>
                <div class="form-group">
                    <div class="col-md-6">
                        <label class="col-sm-3 col-lg-2 control-label">{{ trans('admin/flashcards.status') }} <span class="red">*</span></label>
                        <div class="col-sm-3 col-lg-5 controls">
                            <select name="status" id="" class="form-control">
                                <option value="ACTIVE">ACTIVE</option>
                                <option value="INACTIVE">INACTIVE</option>
                            </select>
                            <span class="help-block error" id ="title_error"></span>
                        </div>
                    </div>
                    <a class="btn-primary btn" href="#" id="select-questions">{{ trans('admin/flashcards.select_questions') }}</a>
                </div>                
                @include('admin.theme.flashcards.card', [ 'count' => 0 ])
                <div class="col-md-12 form-group">
                    <a href="#" class="btn btn-primary" type="button" id="add-new-card"><i class="fa fa-plus"></i> {{ trans('admin/flashcards.new_card') }}</a>
                </div>
                <div class="form-group">
                    <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                        <button class="btn btn-primary" type="submit"><i class="fa fa-check"></i> {{ trans('admin/flashcards.save') }}</button>
                        <a class="btn" href="{{ url::to('/cp/flashcards/list') }}">{{ trans('admin/flashcards.cancel') }}</a>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>
</div>
<div class="modal fade" id="questions" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
<div class="modal-dialog modal-lg">
    <div class="modal-content">
        <div class="modal-header">
            <div class="row custom-box">
                <div class="col-md-12">
                    <div class="box">
                        <div class="box-title">
                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                            <h3 class="modal-header-title">
                            <i class="icon-file"></i>
                            {{ trans('admin/flashcards.select_questions') }}
                            </h3>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="modal-body">
            <div id="questionbanks-dropdown"></div>
        </div>
        <div class="modal-footer">
            <div class="pull-left" id="selected"><span>0</span> Selected</div>
            {{-- <label class="radio-inline">
                <input type="radio" value="first" name="position" class="position" checked="checked"> {{ trans('admin/flashcards.add_to_top') }}
            </label>
            <label class="radio-inline">
                <input type="radio" value="last" name="position" class="position"> {{ trans('admin/flashcards.add_at_bottom') }}
            </label> --}}
            <a class="btn brn-info" id="question-create-cards" href="#">{{ trans('admin/flashcards.created_cards') }}</a>
            <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/flashcards.close') }}</a>
        </div>
    </div>
</div>
</div>
<script type="text/javascript">
    var chost = "{{ URL::to('') }}",
        count = 1;    
</script>
<?php
$mathml_editor = \App\Model\SiteSetting::module('MathML', 'mathml_editor');
?>
@if($mathml_editor && $mathml_editor == 'on')
<script type="text/javascript">
    CKEDITOR.config.extraPlugins += (CKEDITOR.config.extraPlugins.length == 0 ? '' : ',') + 'ckeditor_wiris';
    CKEDITOR.config.allowedContent = true;
</script>
@endif
<script type="text/javascript" src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<script type="text/javascript" src="{{ URL::asset('admin/assets/flashcards/js/flashcards.js')}}"></script>
<script type="text/javascript" src="{{ URL::asset('admin/assets/flashcards/js/questions.js') }}"></script>
@stop