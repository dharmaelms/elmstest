<?php
header("Content-type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet");
header("Content-Disposition: attachment; filename='updatebulkuserlist.xls'");
header("Pragma: no-cache");
header("Expires: 0");
?>
<table class="table table-advance" id="table1">
    <thead>
        <tr>
           <th><?php echo trans('admin/user.serial_no'); ?></th>
            <th><?php echo trans('admin/user.first_name'); ?> *</th>
            <th><?php echo trans('admin/user.last_name'); ?></th>
            <th><?php echo trans('admin/user.email'); ?>*</th> 
            <th><?php echo trans('admin/user.mobile'); ?></th>
            <th><?php echo trans('admin/user.username'); ?>*</th>
            <th><?php echo trans('admin/user.usergroup'); ?></th>
            @if(is_array($customFieldArr) && !empty($customFieldArr) )
            @foreach($customFieldArr as $key=>$val)
            <th><?php echo $val; ?></th>
            @endforeach
            @endif

            <th><?php echo trans('admin/user.option'); ?>*</th>
        </tr>
    </thead>
    <tbody>
    <?php
        $i = 1;
    ?>
        
        @foreach ($users as $user)
            <?php $user['usergroupnames'] =  (isset($user['usergroupnames'])) ? $user['usergroupnames'] : "" ?>
        <tr>
            <td>
                {{$i++}}
            </td>
            <td>{{$user['firstname']}}</td>
            <td>{{$user['lastname']}}</td>
            <td>{{$user['email']}}</td>
            <td>{{ array_get($user, 'mobile', '') }}</td>
            <td>{{$user['username']}}</td>
            <td>{{ $user['usergroupnames'] }}</td>
            <!-- Custom feilds data-->
            @if(is_array($customFieldInUsersData) && !empty($customFieldInUsersData) )
                @foreach ($customFieldInUsersData as $key=>$fields)
                    <?php 
                    if(array_key_exists($key,$user))
                     echo '<td>'.$user[$key].'</td>';
                    else
                    echo "<td></td>";
                    ?>
                @endforeach 
            @endif



            <td>update</td>
        </tr>
        @endforeach
                        
    </tbody>
</table>