<style>
.modal-table-data {
    padding: 10px;
    border: 1px solid #e0e0e0;
}
.textans-body {
    padding: 20px;
    min-height:350px;
    max-height: 500px;
    overflow: auto;
}
.textans-content {
    width:700px;
}
#title_question_model {
    font-size: 16px !important;
}
</style>
<!-- modal pop up -->
<div class="col-md-offset-2 modal fade" id="text-ans" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content textans-content">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" id="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                    <span id="title_question_model">

                                    </span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body textans-body" id="others">
            </div>
            <div class="modal-footer">
                <a id="read-more-text" class="btn btn-success">{{trans('admin/survey.read_more')}}</a>
                <a class="btn btn-danger" data-dismiss="modal">{{ trans('admin/survey.cancel') }}</a>
            </div>
        </div>
    </div>
</div>

<script type="text/javascript">
     $('#close').click(function() {
        $("#others").html("");
     });
</script>
