<div class="modal fade" id="media-list-modal" tabindex="-1" role="dialog" data-backdrop="static" aria-labelledby="mediaListModalLabel">
    <div class="modal-dialog modal-lg" role="media-list">
        <div class="modal-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">{{ trans('admin/assessment.select_media') }}</h3>               
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-success">
                    <em class="fa fa-check-circle"></em>
                    <span>{{ trans('admin/assessment.add') }}</span>
                </button>
                <button type="button" class="btn btn-danger" data-dismiss="modal">
                    <em class="fa fa-times-circle"></em>
                    <span>{{ trans('admin/assessment.close') }}</span>
                </button>
            </div>
        </div>
    </div>
</div>

<script>
    <?php
        if(!isset($from)) {
            $from = '';
        };
    ?>
    var filterQuery = "{{ http_build_query(["filter" => $media_types]) }}";
    var mediaListIFrameURL = "{{ URL::to("cp/dams?view=iframe&from={$from}&add_media=true") }}";
    @if($from === "program")
        mediaListIFrameURL += "&program_type={{$program_type}}&program_slug={{$program_slug}}";
    @elseif($from === "package")
        mediaListIFrameURL += "&package_slug={{$package_slug}}";
    @endif
    $(document).ready(function(){
        var mediaContext = null;
        var mediaListIframe = "<iframe src=\""+mediaListIFrameURL+"&"+filterQuery+"\" id=\"media-list-iframe\" width=\"100%\" frameborder=\"0\" height=\"\"></iframe>";
        $(document).on("click", ".media-list-btn", function(){
            $("#media-list-modal .modal-body").append(mediaListIframe);
            mediaContext = $(this).data("bind-to");
            $("#media-list-iframe").load(function(){
                $("#media-list-modal").modal("show");
            });
        });

        $("#media-list-modal .modal-footer .btn-success").click(function(){
            var selectedMedia = new Array();
            var selected_checkboxes = $('#media-list-iframe').get(0).contentWindow.checkedBoxes;
            $.each(selected_checkboxes, function(key, value){
                selectedMedia.push(key);
            });
            if(selectedMedia.length > 0)
            {
                xmlHTTPRequest = $.ajax({
                    url : "{{ URL::to("cp/dams/embed-code") }}?"+$.param({"media": selectedMedia}),
                    type : "GET",
                    dataType : "json"
                });

                xmlHTTPRequest.done(function(response, status, jqXHR){
                    editorName = $(".media-list-btn").data("bind-to");
                    editorInstance = CKEDITOR.instances[mediaContext];
                    for(i=0; i<response.length; ++i)
                    {
                        tmpEditorDom = CKEDITOR.dom.element.createFromHtml(response[i]);
                        editorInstance.insertElement(tmpEditorDom);
                    }
                });
            }
            else{
                alert('Please select atleast one media');
                return false;
            }
            $("#media-list-modal").modal("hide");
        });

        $("#media-list-modal").on("hidden.bs.modal", function(e){
            $(this).find(".modal-body").empty();
        });
    });
</script>