<!-- BEGIN Main Content -->
<?php use App\Model\Role;
use App\Model\CustomFields\Entity\CustomFields;
$custom_fields = CustomFields::getUserActiveCustomField('user','', $status = 'ACTIVE');
?>
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12 user-profile-info">
                    <?php 
                      $role=Role::pluckRoleName($user['role']);
                    ?>
                      <p><span>{{ trans('admin/user.user_id') }}:</span> {{$user['uid']}}</p>
                      <p><span>{{ trans('admin/user.username') }}:</span> {{$user['username']}}</p>
                      <p><span>{{ trans('admin/user.full_name') }}:</span> {{$user['firstname']}} {{$user['lastname']}}</p>
                      <p><span>{{ trans('admin/user.role_name') }}:</span> {{$role}}</p>
                      <p><span>{{ trans('admin/user.user_email') }}:</span> <a href="#">{{$user['email']}}</a></p>
                      <p><span>{{ trans('admin/user.created_on') }}:</span> {{ Timezone::convertFromUTC('@'.$user['created_at'], Auth::user()->timezone, Config('app.date_format'))}}</p>
                      <p><span>{{ trans('admin/user.status') }}:</span> {{$user['status']}}</p>
                      @if(!empty($custom_fields))
                      @foreach($custom_fields as $key => $value)
                      @if(array_key_exists($value['fieldname'],$user))
                      <?php
                      $user[$value['fieldname']] = (empty($user[$value['fieldname']])) ? 'N/A': $user[$value['fieldname']];
                      ?>
                      <p><span>{{$value['fieldname']}}:</span> {{$user[$value['fieldname']]}}</p>
                      @endif
                      @endforeach
                      @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>