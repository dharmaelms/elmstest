@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
<!--   <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
<?php use App\Model\User; ?>
<?php 
  $start    =  Input::get('start', 0);
  $limit    =  Input::get('limit', 10);
  $filter   =  Input::get('filter','all');
  $search   =  Input::get('search','');
  $order_by =  Input::get('order_by','3 desc');
  
?>
<script>
    var role_contexts = {};
</script>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <!-- <h3><i class="icon-file"></i>Edit Portal Role</h3> -->
            </div>
            <div class="box-content">
                <form action="{{URL::to('cp/rolemanagement/edit-role/'.$id)}}" class="form-horizontal form-bordered form-row-stripped" method="post">

                  @if($role['system_role']==true)
                    <input type="hidden" name="parent_role" value="{{ $role['parent'] }}">
                    <input type="hidden" name="current_parent" value="{{ $role['parent'] }}">

                  @else
                    <div class="form-group">
                      <?php 
                        if(Input::old('parent'))
                        {
                          $parent_role=Input::old('parent');
                        }
                        elseif(isset($role['parent']))
                        {
                          $parent_role=$role['parent'];
                        }
                        else
                        {
                          $parent_role='';
                        }
                      ?>
                        <input type="hidden" name="current_parent" value="{{ $parent_role }}">
                        <label class="col-sm-4 col-lg-3 control-label" for="parent_role">{{ trans('admin/role.select_parent_role') }}<span class="red">*</span></label>
                        <div class="col-sm-6 col-lg-5 controls">
                          <select name="parent_role" class="chosen gallery-cat form-control" data-placeholder="{{ trans('admin/role.select_parent_role') }}">
                            @foreach($parent as $parents)
                               @if(!empty($parents))
                                 <option value="{{$parents['slug']}}" <?php if($parent_role == $parents['slug']) echo "selected"?>>{{ucfirst(strtolower($parents['name']))}}</option>
                                  <script>
                                      role_contexts["{{$parents["slug"]}}"] = {!! json_encode($parents["contexts"]) !!};
                                  </script>
                              @endif
                            @endforeach
                          </select>
                          {!! $errors->first('parent', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                        </div>
                    </div>
                  @endif
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
                            <input type="text" class="form-control" name="role_name" <?php if(Input::old('name')) {?>value="{{Input::old('name')}}"<?php } elseif($errors->first('name')) {?> value="{{Input::old('name')}}"<?php } elseif(isset($role['name'])) { ?> value="{{$role['name']}}"<?php } ?>>
                            {!! $errors->first('role_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                            @if ( Session::get('role_exist') )
                              <span class="help-inline" style="color:#f00">{{ Session::get('role_exist') }}</span>
                              <?php Session::forget('role_exist'); ?>
                            @endif
                        </div>
                    </div>
                    <div class="form-group">
                      <?php 
                        if(Input::old('description')) 
                        {
                          $description=Input::old('description');
                        }
                        elseif($errors->first('description')) 
                        { 
                          $description=Input::old('description');
                        } 
                        elseif(isset($role['description'])) 
                        { 
                          $description=$role['description']; 
                        }
                      ?>
                      <label class="col-sm-4 col-lg-3 control-label" for="role">{{ trans('admin/role.description') }}</label>
                      <div class="col-sm-6 col-lg-5 controls">
                          <textarea id="textarea" class="form-control" rows="5" name="description">{{$description}}</textarea>
                          {!! $errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
                      </div>
                    </div>
                    <div class="form-group">
                      <?php 
                        if(Input::old('status')) 
                        {
                          $status=Input::old('status');
                        }
                        elseif($errors->first('status')) 
                        { 
                          $status=Input::old('status');
                        } 
                        elseif(isset($role['status'])) 
                        {
                          $status=$role['status'];
                        }
                        $assigned=User::getIsAssigned($role['rid']);

                      ?>
                      <label class="col-sm-4 col-lg-3 control-label" for="item_type">{{ trans('admin/role.status') }}<span class="red">*</span></label>
                      <div class="col-sm-6 col-lg-5 controls">
                        <select name="status" class="chosen gallery-cat form-control" data-placeholder="Select Status" <?php if(!empty($assigned)) { echo "disabled";} ?>>
                          <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>>Active</option>
                          <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>>In-active</option>
                        </select>
                        {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                      </div>
                      <?php if(!empty($assigned)) {?>  
                        <a class="btn btn-circle show-tooltip" title="{{trans('admin/role.no_status_change')}}" href="#"><i class="fa fa-question"></i></a>
                        <?php } ?>
                    </div>
                    <div class="form-group last">
                        <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                          <input type="submit" class="btn btn-info" value="Edit Permissions">
                          <a class="btn" href="{{ URL::to('cp/rolemanagement/user-roles')}}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}">{{ trans('admin/role.cancel') }}</a>
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
    