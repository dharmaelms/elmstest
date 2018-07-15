@section('content')
<?php 
      $start    =  Input::get('start', 0);
      $limit    =  Input::get('limit', 10);
      $filter   =  Input::get('filter','all');
      $search   =  Input::get('search','');
      $order_by =  Input::get('order_by','4 desc');
      use App\Model\CustomFields\Entity\CustomFields;

?>
<div class="row">
  <div class="col-md-12">
    <div class="box">
      <div class="box-title">
        <!-- <h3><i class="icon-file"></i> Edit User</h3> -->
      </div>
      <div class="box-content">
        <form action="{{URL::to('cp/usergroupmanagement/edit-user/'.$user['uid'])}}" class="form-horizontal form-bordered form-row-stripped" method="post" >         
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="firstname"><?php echo trans('admin/user.first_name'); ?><span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="firstname" <?php if(Input::old('firstname')) {?>value="{{Input::old('firstname')}}"<?php } elseif($errors->first('firstname')) {?> value="{{Input::old('firstname')}}"<?php } elseif(isset($user['firstname'])) {?> value="{{$user['firstname']}}"<?php } ?>> 
                {!! $errors->first('firstname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group"> 
              <label class="col-sm-4 col-lg-3 control-label" for="lastname"><?php echo trans('admin/user.last_name'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="lastname" class="form-control" name="lastname" <?php if(Input::old('lastname')) {?>value="{{Input::old('lastname')}}"<?php } elseif($errors->first('lastname')) {?> value="{{Input::old('lastname')}}"<?php } elseif(isset($user['lastname'])) {?> value="{{$user['lastname']}}"<?php } ?>> 
                {!! $errors->first('lastname', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="email"><?php echo trans('admin/user.email_id'); ?><span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="email" class="form-control" name="email" <?php if(Input::old('email')) {?>value="{{Input::old('email')}}"<?php } elseif($errors->first('email')) {?> value="{{Input::old('email')}}"<?php } elseif(isset($user['email'])) {?> value="{{$user['email']}}"<?php } ?>> 
                {!! $errors->first('email', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                @if ( Session::get('email_exist') )
                  <span class="help-inline" style="color:#f00">{{ Session::get('email_exist') }}</span>
                  <?php Session::forget('email_exist'); ?>
                @endif
              </div>
            </div>  
            
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="username"><?php echo trans('admin/user.username'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="username" class="form-control" name="username" readonly <?php if(Input::old('username')) {?>value="{{Input::old('username')}}"<?php } elseif($errors->first('username')) {?> value="{{Input::old('username')}}"<?php } elseif(isset($user['username'])) {?> value="{{$user['username']}}"<?php } ?>> 
                {!! $errors->first('username', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                @if ( Session::get('name_exist') )
                  <span class="help-inline" style="color:#f00">{{ Session::get('name_exist') }}</span>
                  <?php Session::forget('name_exist'); ?>
                @endif
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="password"><?php echo trans('admin/user.password'); ?></label>
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
                <label class="col-sm-4 col-lg-3 control-label" for="confirm-password"><?php echo trans('admin/user.confirm_password'); ?></label>
                <div class="col-sm-6 col-lg-5 controls">
                    <input type="password" class="form-control" name="password_confirmation">
                    {!! $errors->first('password_confirmation', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                </div>
            </div>
            <div class="form-group">
              <?php 
                if(Input::old('role'))
                {
                  $role_id = Input::old('role');
                }
                elseif(isset($user['role']))
                {
                  $role_id = $user['role'];
                }
              ?>
              <label class="col-sm-4 col-lg-3 control-label" for="email"><?php echo trans('admin/user.role'); ?> <span class="red">*</span></label>
              <div class="col-sm-6 col-lg-5 controls">
                <select name="role" class="chosen gallery-cat form-control" data-placeholder="Select Role">
                  <option value="select"><?php echo trans('admin/user.select_role'); ?> </option>
                  @foreach($roles as $role)
                    <option  value="{{$role['id']}}" {{ ($role_id == $role['id'])? "selected" : "" }}>
                        {{$role['name']}}
                    </option>
                  @endforeach  
                </select>
                {!! $errors->first('role', '<span class="help-inline" style="color:#f00">:message</span>') !!}
                <span class="help-inline">
                    Updating role in system context will override all the context level role assignments.
                </span>
              </div>
            </div>
            <div class="form-group">
              <label class="col-sm-4 col-lg-3 control-label" for="mobile"><?php echo trans('admin/user.mobile_no'); ?></label>
              <div class="col-sm-6 col-lg-5 controls">
                <input type="text" class="form-control" name="mobile" <?php if(Input::old('mobile')) {?>value="{{Input::old('mobile')}}"<?php } elseif($errors->first('mobile')) {?> value="{{Input::old('mobile')}}"<?php } elseif(isset($user['mobile'])) {?> value="{{$user['mobile']}}"<?php } ?>> 
                {!! $errors->first('mobile', '<span class="help-inline" style="color:#f00">:message</span>') !!}
              </div>
            </div>
            <div class="form-group">
              <?php 
                if(Input::old('timezone'))
                {
                  $timezone=Input::old('timezone');
                }
                elseif(isset($user['timezone']))
                {
                  $timezone=$user['timezone'];
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
              elseif(isset($user['status']))
              {
                $status=$user['status'];
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
        @if(!empty($customFieldList) || !empty($newcustomfield))
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
                      
                         @foreach($customFieldList as $key => $val)
                           <?php
                           $record = CustomFields::getCustomField($key,'userfields');
                            if(in_array($key,$session_arr))
                            {
                                $val = "";
                            }
                           ?>
                          <div class="form-group">
                            <label class="col-sm-4 col-lg-3 control-label" > <?php if(isset($record[0]['fieldlabel'])) echo $record[0]['fieldlabel']; ?>
                              @if(isset($record[0]['mark_as_mandatory']) && $record[0]['mark_as_mandatory']=='yes')
                                <span class="red">*</span>
                              @endif
                            </label>
                            <div class="col-sm-6 col-lg-5 controls">
                              <input type="text" class="form-control"  name="{{ $key }}" value="{{ $val }}"> 
                              {!! $errors->first($key, '<span class="help-inline" style="color:#f00">:message</span>') !!}
                           </div>
                          </div>
                         @endforeach
                          <?php session::forget('session_arr'); ?>
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
                    <input type="submit" class="btn btn-info" value="Save Changes and Update">
                    <a class="btn" href="{{ URL::to('cp/usergroupmanagement') }}?start={{$start}}&limit={{$limit}}&filter={{$filter}}&search={{$search}}&order_by={{$order_by}}"><?php echo trans('admin/user.cancel'); ?></a>
                </div>
            </div>
          </div>
        </form>

    </div>
  </div><!--Register-->
</div>
@stop