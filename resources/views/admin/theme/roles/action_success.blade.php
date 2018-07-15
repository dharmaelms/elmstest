@section('content')
<div class="alert alert-success" id="alert-success">
<button class="close" data-dismiss="alert">×</button>
<!-- <strong>Success!</strong><br> -->
{!! Session::get('success') !!}
</div>
<!-- BEGIN Main Content -->
<div class="row custom-box">
  <div class="col-md-4">
    <div class="box box-lightgreen">
      <!-- <div class="box"> -->
        <div class="box-title">
            <h3>{{ trans('admin/role.more_actions') }}</h3>
        </div> 
          <div class="box-content">
              @if(has_admin_permission(ModuleEnum::ROLE, RolePermission::ADD_ROLE))
                  <a class="btn btn-blue" href="{{url::to('cp/rolemanagement/add-role')}}">
                      {{ trans('admin/role.add_another_role') }}
                  </a>
              @endif

              @if(has_admin_permission(ModuleEnum::ROLE, RolePermission::EDIT_ROLE))
                  <a class="btn btn-blue" href="{{url::to('cp/rolemanagement/edit-role/'.$role_id)}}">
                      {{ trans('admin/role.edit') }} <b>{{$role_info[0]['name']}}</b> {{ strtolower(trans('admin/role.role')) }}
                  </a>
              @endif

              @if(has_admin_permission(ModuleEnum::ROLE, RolePermission::DELETE_ROLE))
                  <a class="btn btn-blue" href="{{url::to('cp/rolemanagement/user-roles')}}">
                      {{ trans('admin/role.view_all_roles') }}
                  </a>
              @endif
          </div>
      <!-- </div> -->
    </div>
  </div>
</div>
<!-- END Main Content -->
<script type="text/javascript">
   $(document).ready(function(){
        $('#alert-success').delay(5000).fadeOut();
    })
</script>
@stop