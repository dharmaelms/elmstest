<script type="text/javascript">
    var $configPath = "{{ URL::asset('admin/assets/ckeditor/config.js')}}", chost = "{{ config('app.url') }}";
</script>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <div class="box-content">
                    <form action="" class="form-horizontal form-bordered" id="edit-tab" name="edit-tab" method="post">
                        <input type="hidden" id="p_id" name="p_id" value="{{$tabs['pid']}}">
                        <div class="form-group">
                            <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/catalog.title') }} <span class="red">*</span></label>
                            <div class="col-sm-6 col-lg-4 controls">
                                <input type="text" name="tab_title" id="tab_title" placeholder="Title" class="form-control" value="{{$tabs['title']}}">
                                <input type="hidden" name="c_tab_title" id="c_tab_title" placeholder="Title" class="form-control" value="{{$tabs['title']}}">
                                <span class="help-inline" style="color:#f00" id="e_tab_title" name="e_tab_title"></span>
                            </div>
                        </div>
                        <div class="form-group">
                            <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/catalog.description') }} <span class="red">*</span></label>
                            <div class="col-md-9 col-lg-10 controls" >
                                <div class="row" style="padding-left: 2%">
                                    <div class="col-md-8 col-lg-8 rich-text-content panel panel-default" style="overflow-y:scroll !important; max-height:400px !important;">
                                        @include("admin/theme/assessment/question/partials/_rich_text_area", [
                                        "name" => "tab_description",
                                        "id" => "tab_description",
                                        "content" => array_get($tabs, 'description', [])
                                        ])
                                    </div>
                                    <div class="col-md-2 col-lg-2 editor-cover-media" >
                                        <button type="button" class="btn btn-primary btn-sm media-list-btn" data-bind-to="tab_description">
                                            <i class="fa fa-video-camera"></i>
                                            <span>{{ trans('admin/assessment.add_media_lib') }}</span>
                                        </button>
                                    </div>
                                </div>
                                <div class="row" style="padding-left: 2%">
                                    <span class="help-inline" style="color:#f00" id="e_tab_description" name="e_tab_description"></span>
                                </div>
                            </div>
                        </div>
                        <div class="form-group last">
                            <div class="col-sm-9 col-sm-offset-3 col-lg-10 col-lg-offset-2">
                               <button type="submit" class="btn btn-success"><i class="fa fa-check"></i> {{ trans('admin/catalog.save') }}</button>                                   
                               <button type="button" class="btn btn-danger" data-dismiss="modal" aria-hidden="true"> {{ trans('admin/catalog.cancel') }}</button>
                            </div>
                        </div>
                      <!-- END Left Side -->
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript" src="{{ URL::asset("admin/assets/ckeditor/ckeditor.js")}}"></script>
<script type="text/javascript" src="{{ URL::asset("admin/js/assessment/dynamic_choice.js") }}"></script>
<script type="text/javascript">
    var $configPath = "{{ URL::asset('admin/assets/ckeditor/config.js')}}",  
    chost = "{{ config('app.url') }}";
     
    $('#edit-tab').on('submit', function(e){
        e.preventDefault();
        var media = new Array();
        $("textarea").each(function(){
            var textareaInstance = $(this);
            var textareaName = $(this).attr("name");
            var mediaCount = 0;        
            $($(this).val()).find(".question-media").each(function(){
                media.push($(this).data("media-id"));
            });
        });
        $.ajax({
            method: "POST",
            url: "{{URL::to('cp/package/edit-save')}}",
            data: {
                title : $('#tab_title').val(),
                ctitle : $('#c_tab_title').val(),
                description : $('#tab_description').val(),
                package_slug: $('#slug').val(),
                p_id : $('#p_id').val(),
                p_type:$('#sellable_type').val(),
                p_media_ids: media
            }
        })
        .done(function( msg ) {
            $('#e_tab_title').html(msg.title);
            $('#e_tab_description').html(msg.description);
            if(msg.success === "error") {
                //some 
            } else {
                window.location = msg.success;
            }
        });
    });
</script>
