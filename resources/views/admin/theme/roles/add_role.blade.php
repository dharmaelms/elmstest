@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
 <!--  <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
<script>
    var role_contexts = {};
</script>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <!-- <h3><i class="icon-file"></i>Add Role</h3> -->
            </div>
            <div class="box-content">
                <form action="{{URL::to('cp/rolemanagement/add-role')}}" class="form-horizontal form-bordered form-row-stripped" method="post">
                    <div class="form-group">
                      <?php 
                        if(Input::old('parent_role'))
                        {
                          $parent_role=Input::old('parent_role');
                        }
                        else
                        {
                          $parent_role='';
                        }
                      ?>
                        <label class="col-sm-4 col-lg-3 control-label" for="parent_role">{{ trans('admin/role.select_role_permissions') }}<span class="red">*</span></label>
                        <div class="col-sm-6 col-lg-5 controls">
                          <select name="parent_role" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/role.select_parent_role') }}">
        
                            @foreach($roles as $role)
                                  <option value="{{$role['slug']}}" <?php if($parent_role == $role['slug']) echo "selected"?>>{{ucfirst(strtolower($role['name']))}}</option>
                                  <script>
                                     //role_contexts.push({"{{$role["slug"]}}" : "{{json_encode($role["contexts"])}}"});
                                      role_contexts["{{$role["slug"]}}"] = {!! json_encode($role["contexts"]) !!};
                                  </script>
                            @endforeach
                          </select>
                          {!!$errors->first('parent_role', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>
                    <div class="form-group">
                        <label for="contexts" class="col-sm-4 col-md-3 col-lg-3 control-label">
                            Context where role is allowed to assign
                            <span class="red">*</span>
                        </label>
                        <div class="col-sm-2 col-md-2 col-lg-2 controls">
                            @foreach($contexts as $slug => $context)
                                @if(!in_array($slug, [ContextsEnum::COURSE, ContextsEnum::BATCH]))
                                <label class="checkbox">
                                    <input type="checkbox" name="contexts[]" value="{{$slug}}" disabled>
                                    <span>{{ucfirst(strtolower($context["name"]))}}</span>
                                </label>
                                @endif
                            @endforeach
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="role">{{ trans('admin/role.role_name') }}<span class="red">*</span></label>
                        <div class="col-sm-6 col-lg-5 controls">
                            <input type="text" class="form-control" name="role_name" <?php if(Input::old('role_name')) {?>value="{{Input::old('role_name')}}"<?php } ?>>
                            {!! $errors->first('role_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                             @if ( Session::get('role_exist') )
                              <span class="help-inline" style="color:#f00">{{ Session::get('role_exist') }}</span>
                              <?php Session::forget('role_exist'); ?>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="col-sm-4 col-lg-3 control-label" for="role">{{ trans('admin/role.description') }}</label>
                        <div class="col-sm-6 col-lg-5 controls">
                            <textarea id="textarea" class="form-control" rows="5" name="description">{{Input::old('description')}}</textarea>
                            {!!$errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <?php 
                            if(Input::old('status'))
                            {
                              $status=Input::old('status');
                            }
                            else
                            {
                              $status='';
                            }
                        ?>
                        <label class="col-sm-4 col-lg-3 control-label" for="item_type">{{ trans('admin/role.status') }}<span class="red">*</span></label>
                        <div class="col-sm-6 col-lg-5 controls">
                          <select name="status" class="chosen gallery-cat form-control" data-placeholder="Select Status">
                            <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>>Active</option>
                            <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>>In-active</option>
                          </select>
                          {!!$errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>
                    <div class="form-group last">
                        <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                          <input type="submit" class="btn btn-info" value="Add Permissions">
                          <a class="btn" href="{{ URL::to('cp/rolemanagement/user-roles') }}">{{ trans('admin/role.cancel') }}</a>
                        </div>
                    </div>
                </form>
            </div> 
        </div>
    </div>
</div>
<script>
    $(document).ready(function (event) {
        var checkContexts = function (role_slug) {
            var role_context_data = role_contexts[role_slug];

            $("input[type=checkbox]").prop({
                checked : false
            });

            for (var context_slug in role_context_data) {
                $("input[type=checkbox][value="+context_slug+"]").prop({checked : true});
            }
        };

        checkContexts($("select[name=parent_role]").val());

        $("select[name=parent_role]").change(function () {
            checkContexts($(this).val());
        });
    });
</script>
@stop
    