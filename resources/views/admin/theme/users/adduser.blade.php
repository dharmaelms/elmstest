@section('content')
  <?php use App\Model\CustomFields\Entity\CustomFields; ?>
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/usergroupmanagement/add-user')}}" class="form-horizontal form-bordered form-row-stripped" method="post" >         
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="firstname"><?php echo trans('admin/user.first_name'); ?><span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="firstname" value="{{ Input::old('firstname') }}"> 
                {!! $errors->first('firstname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="lastname"><?php echo trans('admin/user.last_name'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="lastname" class="form-control" name="lastname" value="{{ Input::old('lastname') }}"> 
                {!! $errors->first('lastname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="email"><?php echo trans('admin/user.email_id'); ?><span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="email" class="form-control" name="email" value="{{ Input::old('email') }}"> 
                {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>  
            
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="username"><?php echo trans('admin/user.username'); ?> <span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="username" class="form-control" name="username" value="{{ Input::old('username') }}"> 
                {!! $errors->first('username', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="password"><?php echo trans('admin/user.password'); ?><span class="red">*</span></label>
               <div class="col-sm-6 col-lg-5 controls">
                  <input type="password" class="form-control" name="password" data-action="pwindicator" data-indicator="pwindicator-block">
                  {!! $errors->first('password', '<span class="help-inline" style="color:#f00">:message</span>') !!}  
                  <div class="pwindicator" id="pwindicator-block">
                    <div class="bar"></div>
                    <div class="label"></div>
                 </div> 
              </div>
            </div>
            <div class="form-group">
                <label class="col-sm-4 col-lg-3 control-label" for="confirm-password"><?php echo trans('admin/user.confirm_password'); ?><span class="red">*</span></label>
                <div class="col-sm-6 col-lg-5 controls">
                    <input type="password" class="form-control" name="password_confirmation">
                    {!! $errors->first('password_confirmation', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
              <?php 
                if(Input::old('role'))
                {
                  $role_name=Input::old('role');
                }
                else
                {
                  $role_name='';
                }
              ?>
              <label class="col-sm-4 col-lg-3 control-label" for="email"><?php echo trans('admin/user.role'); ?><span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="role" class="chosen gallery-cat form-control" data-placeholder="Select Role">
                  <option value="select">Select role </option>
                  @foreach($context_data["roles"] as $role)
                    <option  value="{{$role['id']}}" <?php if($role_name == $role['id']) echo "selected"?>>{{$role['name']}}</option>
                  @endforeach  
                </select>
                {!! $errors->first('role', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="mobile"><?php echo trans('admin/user.mobile_no'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="mobile" value="{{ Input::old('mobile') }}"> 
                {!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <?php 
                if(Input::old('timezone'))
                {
                  $timezone=Input::old('timezone');
                }
                else
                {
                  $timezone='';
                }
              ?>
              <label class="col-sm-4 col-lg-3 control-label" for="email"><?php echo trans('admin/user.time_zone'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="timezone" class="chosen gallery-cat form-control" data-placeholder="Select Timezone">
                  <optgroup label="Most used">
                    @foreach($frequent_tz as $value)
                    <option  value="{{$value}}" <?php if($timezone == $value) echo "selected"?>>{{$value}}</option>
                    @endforeach  
                  </optgroup>
                  <optgroup label="All">
                    @foreach($timezones as $value)
                    <option  value="{{$value}}" <?php if($timezone == $value) echo "selected"?>>{{$value}}</option>
                    @endforeach  
                  </optgroup> 
                </select>
                {!! $errors->first('timezone', '<span class="help-inline" style="color:#f00">:message</span>') !!}
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
                  <option value="IN-ACTIVE" <?php if($status == 'IN-ACTIVE') echo "selected"?>><?php echo trans('admin/user.inactive'); ?></option>
                </select>
                {!! $errors->first('status', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>

        <!-- custom fields tab -->
        @if(!empty($userCustomField) )
        <div class="form-group">
           <div class="panel-group" id="accordion">
              <div class="panel panel-info">
                  <div class="panel-heading">
                      <h4 class="panel-title">
                          <a class="accordion-toggle" data-toggle="collapse" data-parent="#accordion" href="#collapseOne">{{ trans('admin/user.custom_fields') }}</a>
                      </h4>
                  </div>
                  <div id="collapseOne" class="panel-collapse collapse in">
                      <div class="panel-body">
                      <!-- dispalying text boxes -->
                      <?php $i = 1; ?> 
                         @foreach($userCustomField as $key => $user_field)
                          <?php
                          $record = CustomFields::getCustomField($user_field['fieldname'],'userfields');
                         ?>
                          <div class="form-group">
                            <label class="col-sm-4 col-lg-3 control-label" for="custom_field[{{$i}}]"> <?php  if(isset($user_field['fieldlabel'])) echo $user_field['fieldlabel']; ?>
                            @if(isset($record[0]['mark_as_mandatory']) && $record[0]['mark_as_mandatory']=='yes')
                         <span class="red">*</span>
                         @endif
                            </label>
                              <div class="col-sm-6 col-lg-5 controls">
                                <input type="text" class="form-control" name="{{$user_field['fieldname']}}"  value="<?php Input::old('custom_field[{{$i}}] '); ?>" > 
                                {!! $errors->first($user_field['fieldname'], '<span class="help-inline" style="color:#f00">:message</span>') !!}
                              </div>
                          </div>
                         
                           <?php $i++; ?>
                        @endforeach 
                      <!-- displaying text boxes ends here -->
                      </div>
                  </div>
              </div>
          </div>
        </div>
        @endif
        <!-- ends here -->

            <div class="form-group last">
                <div class="col-sm-offset-3 col-sm-9 col-lg-offset-3 col-lg-9">
                    <input type="submit" class="btn btn-info" value="Add User">
                    <a class="btn" href="{{ URL::to('cp/usergroupmanagement') }}"><?php echo trans('admin/user.cancel'); ?></a>
                </div>
            </div>
          </div>
        </form>

    </div>
  </div><!--Register-->
</div>
@stop