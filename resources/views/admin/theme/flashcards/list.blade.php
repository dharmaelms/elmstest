@section('content')
<script src="{{ URL::asset('admin/js/simpleloader/sl.min.js')}}"></script>
<link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}"/>
<div class="row">
    <div class="col-xs-12 col-sm-12">
        <div id="alert-success" class="alert alert-success created" style="display: none;">
            <button data-dismiss="alert" class="close">
                ×
            </button>           
            {{ trans('admin/flashcards.flashcard_added_successfully') }}
        </div>
        <div id="alert-success" class="alert alert-success updated" style="display: none;">
            <button data-dismiss="alert" class="close">
                ×
            </button>            
            {{ trans('admin/flashcards.flashcard_updated_successfully') }}
        </div>
        <div class="box">
            <div class="box-title">
                <div class="box-tool">
                </div>
            </div>
            <div class="box-content">
                <div class="btn-toolbar clearfix">
                    <div class="col-md-6">
                        <form action="" class="form-horizontal">
                            <div class="form-group">
                                <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left">
                                    <b>
                                        {{ trans('admin/flashcards.showing') }} :
                                    </b>
                                </label>
                                <div class="col-sm-4 col-lg-4 controls">
                                    <select class="form-control" name="type" id="status">
                                        <option value="ALL">
                                            {{ trans('admin/flashcards.all') }}
                                        </option>
                                        <option value="ACTIVE" selected="selected">
                                            {{ trans('admin/flashcards.active') }}
                                        </option>
                                        <option value="INACTIVE">
                                            {{ trans('admin/flashcards.inactive') }}
                                        </option>
                                    </select>
                                </div>
                            </div>
                        </form>
                    </div>
                    <div class="btn-group pull-right">
                        <div class="btn-group">
                            @if(has_admin_permission(ModuleEnum::FLASHCARD, FlashCardPermission::ADD_FLASHCARD))
                                <a href="{{url::to('/cp/flashcards/add')}}" class="btn btn-primary btn-sm">
                                    <span class="btn btn-circle blue custom-btm">
                                        <i class="fa fa-plus">
                                        </i>
                                    </span>
                                    {{ trans('admin/flashcards.add_new_flashcards') }}
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="row dataTables_wrapper form-inline no-footer" id="datatable_wrapper" >
                    <div class="col-xs-12 col-sm-6">
                        <div class="dataTables_length" id="datatable_length">
                            <label>
                                <select name="datatable_length form-control" aria-controls="datatable" class="" id="count">
                                    <option value="10">
                                        10
                                    </option>
                                    <option value="25">
                                        25
                                    </option>
                                    <option value="50">
                                        50
                                    </option>
                                    <option value="100">
                                        100
                                    </option>
                                </select>
                                Records per Page
                            </label>
                        </div>
                    </div>
                    <div class="col-xs-12 col-sm-3 pull-right dataTables_filter">
                        <form id="search-all">
                            <label>
                                {{ trans('admin/flashcards.search') }}:
                                <input type="text" class="form-control" id="search"/>
                            </label>
                        </form>
                    </div>
                </div>
                <div id="table">
                </div>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var chost = "{{ URL::to('') }}", pageUrl = '/cp/flashcards/list-ajax';
</script>
<script type="text/javascript" src="{{ URL::asset('admin/assets/flashcards/js/lists.js')}}">
</script>
@stop
