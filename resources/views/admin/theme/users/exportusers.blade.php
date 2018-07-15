<?php
use App\Model\SiteSetting;

header("Content-type: application/octet-stream");
header("Content-Disposition: attachment; filename=userslist.xls");
header("Pragma: no-cache");
header("Expires: 0")

?>
<table class="table table-advance" id="table1">
    <thead>
        <tr>
            <th><?php echo trans('admin/user.username'); ?></th>
            <th><?php echo trans('admin/user.first_name'); ?> <?php echo trans('admin/user.last_name'); ?></th>
            <th><?php echo trans('admin/user.email_id'); ?></th>
            <th><?php echo trans('admin/user.mobile_number'); ?></th>
            <th><?php echo trans('admin/user.created_on'); ?></th>
            <th><?php echo trans('admin/user.status'); ?></th>
            <th><?php echo trans('admin/user.registration_source'); ?></th>
            @if (SiteSetting::module('UserSetting', 'nda_acceptance') == 'on') 
                <th><?php echo trans('admin/user.nda_status'); ?></th>
                <th><?php echo trans('admin/user.nda_response_time') ?> </th>
            @endif
            @if(is_array($customFields) && !empty($customFields) )
                @foreach($customFields as $key=>$val)
                    <th><?php echo $key ?></th>
                @endforeach
            @endif
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $key=>$user)
            @if (!empty(array_get($user, 'username')))                
                <tr>
                    <td>{{array_get($user, 'username')}}</td>
                    <td>{{array_get($user, 'firstname')}} {{array_get($user, 'lastname')}}</td>
                    <td>{{array_get($user, 'email')}}</td>
                    <td>{{ array_get($user, 'mobile') }}</td>
                    <td><?php echo Timezone::convertFromUTC('@'.
                        array_get($user, 'created_at'),
                        Auth::user()->timezone,
                        Config('app.date_time_format')
                    );?></td>
                    <td>{{array_get($user, 'status')}}</td>
                    <td>
                        @if(isset($user['app_registration']) && $user['app_registration'] == true)
                            APP
                        @else
                            WEB
                        @endif
                    </td>
                    @if(array_key_exists('nda_status', $user))
                        <td>
                            @if ($user['nda_status'] == NDA::DECLINED)
                                {{ trans('admin/user.nda_disagreed') }}
                            @elseif ($user['nda_status'] == NDA::ACCEPTED)
                                {{ trans('admin/user.nda_agreed') }}
                            @elseif ($user['nda_status'] == NDA::NO_RESPONSE)
                                {{ trans('admin/user.nda_no_response') }}
                            @endif
                        </td>
                    @endif
                    @if(array_key_exists('nda_response_time', $user))
                        <td>{{ Timezone::convertFromUTC("@".$user['nda_response_time'],Auth::user()->timezone,Config('app.date_time_format')) }} </td>
                    @else
                        <td></td>
                    @endif
                    @if(is_array($customFields) && !empty($customFields) )
                        @foreach ($customFields as $key=>$fields)
                            @if(array_key_exists($key,$user))
                              <td>{{$user[$key]}}</td>   
                            @endif
                        @endforeach 
                    @endif
                </tr>
            @endif
        @endforeach                          
    </tbody>
</table>