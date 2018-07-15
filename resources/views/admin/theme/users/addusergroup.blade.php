@section('content')
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
        <!-- <h3><i class="icon-file"></i> Add User group</h3> -->
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/usergroupmanagement/adduser-group')}}" class="form-horizontal form-bordered form-row-stripped" method="post" >
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="usergroup_name"><?php echo trans('admin/user.user_group_name'); ?><span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="usergroup_name" value="{{ Input::old('usergroup_name') }}"> 
                {!! $errors->first('usergroup_name', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="usergroup_email"><?php echo trans('admin/user.user_group_email'); ?> </label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="usergroup_email" class="form-control" name="usergroup_email" value="{{ Input::old('usergroup_email') }}"> 
                {!! $errors->first('usergroup_email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="description"><?php echo trans('admin/user.user_group_description'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                  <textarea id="textarea" class="form-control" rows="5" name="description">{{Input::old('description')}}</textarea>
                  <div>{{ trans('admin/user.max_characters_for_ug') }}</div>
                  {!! $errors->first('description', '<span class="help-inline" style="color:#f00">:message</span>') !!}                            
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
                $status='ACTIVE';
              }
            ?>
              <label class="col-sm-4 col-lg-3 control-label" for="status"><?php echo trans('admin/user.status'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="status" class="chosen gallery-cat form-control" data-placeholder="None">
                  <option value="ACTIVE" <?php if($status == 'ACTIVE') echo "selected"?>><?php echo trans('admin/user.active'); ?></option>
                  <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>><?php echo trans('admin/user.in_active'); ?></option>
                </select>
                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Add User Group">
                    <a class="btn" href="{{ URL::to('cp/usergroupmanagement/user-groups') }}"><?php echo trans('admin/user.cancel'); ?></a>
                </div>
            </div>
          </div>
        </form>
    </div>
  </div><!--Register-->
</div>
@stop