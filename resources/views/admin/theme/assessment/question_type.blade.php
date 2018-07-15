<div class="modal fade" id="questionType-modal" tabindex="-1" role="dialog" aria-labelledby="myModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="row custom-box">
                <div class="col-md-12">
                    <div class="box">
                        <div class="modal-header box-title">
                        	<a class="close" data-dismiss="modal" href="#" aria-hidden="true"><i class="fa fa-times"></i></a>
                            <h3 style="color:black"><i class="fa fa-file"></i>{{ trans('admin/assessment.select_question') }} Type</h3>                                                
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-body">
                @if ( !empty($qbid) )
                <a class="button" href="{{URL::to('/cp/question/add/mcq/'.$qbid)}}"><i class="fa fa-list-ul"></i>  {{ trans('admin/assessment.mcq') }}</a>
                @else
                <a class="button" href="{{URL::to('/cp/question/add/mcq')}}"><i class="fa fa-list-ul"></i>  {{ trans('admin/assessment.mcq') }}</a>
                @endif
            </div>
            <div class="modal-footer">
                <a class="btn btn-default" data-dismiss="modal">{{ trans('admin/assessment.close') }}</a>
            </div>
        </div>
    </div>
</div>