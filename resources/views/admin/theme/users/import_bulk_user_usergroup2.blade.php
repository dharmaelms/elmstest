<?php
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename='".trans('admin/user.users_usergroup')."'");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table class="table table-advance" id="table1">
    <thead>
        <tr>
            <th>{{trans('admin/user.username')}}*</th>
            <th>{{trans('admin/user.usergroup')}}*</th>
        </tr>
    </thead>
    <tbody>
        @foreach ($users as $user)
        <tr>
            <td>{{ array_get($user, 'username', '') }}</td>
            <td></td>
        </tr>
        @endforeach
                        
    </tbody>
</table>