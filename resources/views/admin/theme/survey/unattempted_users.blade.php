<style type="text/css">
    .modal-body {
        padding: 20px !important;
    }
</style>
<!-- modal pop up -->
<div class="modal fade" id="unattempted-users" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true" data-backdrop="static">
    <div class="modal-dialog modal-lg">
        <div class="modal-content modal-width">
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3 class="modal-header-title">
                                    <span id="title_model">

                                    </span>
                                </h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body" id="user-table-content">
            </div>
            <div class="modal-footer">
                <a id="read-more" class="btn btn-success">{{trans('admin/survey.read_more')}}</a>
                <a class="btn btn-danger" data-dismiss="modal">{{ trans('admin/survey.close') }}</a>
            </div>
        </div>
    </div>
</div>
