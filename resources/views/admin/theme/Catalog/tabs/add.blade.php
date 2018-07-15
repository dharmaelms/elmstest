
<div id="myModal" class="modal fade" role="dialog">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">{{trans('admin/catalog.add_tab')}}</h3>                                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="modal-body">
                <div class="row">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <div class="box-content">
                                    <form action="" class="form-horizontal form-bordered" id="add-tab" name="add-tab" method="post">
                                     <input type="hidden" id="p_id" name="p_id" value="{{$pri_ser_info['sellable_id']}}">
                                        <div class="form-group">
                                            <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/catalog.title') }} <span class="red">*</span></label>
                                            <div class="col-sm-6 col-lg-4 controls">
                                                <input type="text" name="tab_title" id="tab_title" placeholder="Title" class="form-control" value="{{Input::old('title')}}">
                                                <span class="help-inline" style="color:#f00" id="e_tab_title" name="e_tab_title"></span>
                                            </div>
                                        </div>
                                        <div class="form-group">
                                            <label for="textfield1" class="col-sm-3 col-lg-2 control-label">{{ trans('admin/catalog.description') }} <span class="red">*</span></label>
                                            <div class="col-sm-9 col-lg-10 controls">
                                                <div class="row" style="padding-left: 2%">    
                                                    <div class="col-md-8 col-lg-8 rich-text-content panel panel-default" style="overflow-y:scroll !important; max-height:400px !important;">
                                                        <textarea name="tab_description" rows="10" id="tab_description" class="form-control ckeditor"></textarea>

                                                    </div>
                                                    <div class="col-md-2 col-lg-2 editor-cover-media">
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
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $( document ).ready(function() {
        $('#add-tab').on('submit', function(e){
            e.preventDefault();
            var media = new Array();
            CKEDITOR.instances['tab_description'].updateElement();
            $($("#tab_description").val()).find(".question-media").each(function(){
                media.push($(this).data("media-id"));
            });
            $.ajax({
                method: "POST",
                url: "{{URL::to('cp/tab/save-tab')}}",
                data: {
                    title : $('#tab_title').val(),
                    description : $('#tab_description').val(),
                    program_slug: $('#program_slug').val(),
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
                }
                else {
                    window.location = msg.success;
                }
            });
        });
    });
</script>
<script type="text/javascript" src="{{URL::to('admin/assets/ckeditor/ckeditor.js')}}"></script>
<script type="text/javascript">
var $configPath = "{{ URL::asset('admin/assets/ckeditor/config.js')}}", chost = "{{ config('app.url') }}";
CKEDITOR.inline("tab_description",{customConfig: $configPath});
</script>
<!-- Delete Tab -->
<div class="modal fade" id="tabDel" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                    <i class="icon-file"></i>
                                       {{trans('admin/catalog.del_tab')}} 
                                </h3>                                                
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body" style="padding: 20px">
                {{trans('admin/catalog.del_tab_confirmation')}} 
            </div>
            <div class="modal-footer">
                <a href="" class="btn btn-danger" id="tabDelBtn">{{ trans('admin/catalog.yes') }}</a>
                <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/catalog.close') }}</a>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    $(document).on("click", ".deletefeed", function (event) 
    {
        event.preventDefault();
        $('#tabDelBtn').attr('href',$(this).attr('href'));
        $('#tabDel').modal();       
    });
    $('#tabDel').on('hidden.bs.modal', function () {
        $('.modal-backdrop').removeClass('modal-backdrop');
        $('#subscriptionDel').modal('hide');
    });
</script>
<!-- Delete Tab Ends -->
@include(
    "admin/theme/assessment/media_embed",
     [
        "from" => $from,
        "program_type" => $program_type,
        "program_slug" => $program_slug,
        "media_types" => ["video"]
    ]
)
 