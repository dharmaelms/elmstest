@section('content')
<div class="row custom-box">
    <div class="col-md-4">
        <div class="box box-lightgreen">
            <div class="box-title">
                <h3>{{ trans('admin/assessment.question_management_actions') }}</h3>
            </div>
            <div class="box-content">
            @if(Input::has('qb'))
                @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION))
                    <a class="btn btn-blue" href="{{ URL::to('/cp/assessment/add-question?qb='.Input::get('qb')) }}" >{{ trans('admin/assessment.add_another_question') }}</a>
                @endif
                @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK))
                    <a class="btn btn-blue" href="{{ URL::to('/cp/assessment/questionbank-questions/'.Input::get('qb')) }}" >{{ trans('admin/assessment.view_selected_question') }}</a>
                @endif
            @else
                @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::ADD_QUESTION))
                    <a class="btn btn-blue" href="{{ URL::to('/cp/assessment/add-question') }}" >{{ trans('admin/assessment.add_another_question') }}</a>
                @endif
            @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <div class="box box-lightgray">
            <div class="box-title">
                <h3>{{ trans('admin/assessment.additional_actions') }}</h3>
            </div>
            <div class="box-content">
                @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUESTION_BANK))
                    <a class="btn btn-lightblue" href="{{ URL::to('/cp/assessment/list-questionbank') }}" >{{ trans('admin/assessment.view_all_question_banks') }}</a>
                @endif
                @if(has_admin_permission(ModuleEnum::ASSESSMENT, AssessmentPermission::LIST_QUIZ))
                    <a class="btn btn-lightblue" href="{{ URL::to('/cp/assessment/list-quiz') }}" >{{ trans('admin/assessment.view_all_assessments') }}</a>
                @endif
            </div>
        </div>
    </div>
</div>
@stop