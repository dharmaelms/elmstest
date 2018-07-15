
@if($status == 'SUCCESS')
	<h4>Successfully imported users</h4>
	<table border="1" width="100%" cellpadding="2" cellspacing="1" style="border-collapse:collapse">
	    <thead>
			<tr>
				<th>{{trans('admin/user.serial_no')}}</th>
				<th>{{trans('admin/user.username')}}</th>
				<th>{{trans('admin/user.email')}}</th>
				<th>{{trans('admin/user.status')}}</th>

				 @if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	 @foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php
                    echo "<th> $key </th>";                    
                    ?>
                @endforeach 
            @endif

				<th><?php echo trans('admin/user.record_status'); ?></th>
			</tr>
	    </thead>
	    <tbody>
	    	<?php $i = 0; ?>
			@foreach($users as $user)
				<tr>
					<?php $i = $i+1; ?>
					<td>{{$i}}</td>
					<td>{{$users[$i]['username']}}</td>
					<td>{{$users[$i]['email']}}</td>
					<td>ACTIVE</td>

					@if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	 @foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php 
                    if(array_key_exists($key,$user))
                     echo '<td>'.$user[$key].'</td>';
                    
                    ?>
                @endforeach 
            @endif


					<td>{{$users[$i]['record_status']}}</td>
				</tr>
			@endforeach
	    </tbody>
	</table>
@elseif($status == 'FAILED')
	<h4>Users failed to create</h4>
	<table border="1" width="100%" cellpadding="2" cellspacing="1" style="border-collapse:collapse">
	    <thead>
			<tr>
				<th>{{trans('admin/user.serial_no')}}</th>
				<th>{{trans('admin/user.username')}}</th>
				<th>{{trans('admin/user.email')}}</th>
				<th>{{trans('admin/user.user_status')}}</th>
				<th>{{trans('admin/user.record_status')}}</th>

				 @if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	 @foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php
                    echo "<th> $key </th>";
                    ?>
                @endforeach 
            @endif
				<th><?php echo trans('admin/user.errors'); ?></th>
			</tr>
	    </thead>
	    <tbody>
	    	<?php $i = 0; ?>
			@foreach($users as $user)
				<tr>
					<?php $i = $i+1; ?>
					<td>{{$i}}</td>
					<td>{{$users[$i]['username']}}</td>
					<td>{{$users[$i]['email']}}</td>
					<td>ACTIVE</td>
					<td>{{$users[$i]['record_status']}}</td>

				 @if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	 @foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php 
                    if(array_key_exists($key,$user))
                     echo '<td>'.$user[$key].'</td>';
                   
                    ?>
                @endforeach 
            @endif

					<td>{{$users[$i]['errors']}}</td>
				</tr>
			@endforeach

	    </tbody>
	</table>
@else
<?php 
	$success_array = array(); 
	$failed_array = array();
	foreach($users as $user){
		if($user['record_status'] == 'Success'){
			$success_array [] = $user;
		}else{
			$failed_array[] = $user;
		}
	}
?>
	<h4>Successfully imported users</h4>
	<table border="1" width="100%" cellpadding="2" cellspacing="1" style="border-collapse:collapse">
	    <thead>
			<tr>
				<th>{{trans('admin/user.serial_no')}}</th>
				<th>{{trans('admin/user.username')}}</th>
				<th>{{trans('admin/user.email')}}</th>
				<th>{{trans('admin/user.user_status')}}</th>
				 @if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	 @foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php
                    echo "<th> $key </th>";                    
                    ?>
                @endforeach 
            @endif


				<th><?php echo trans('admin/user.record_status'); ?></th>
			</tr>
	    </thead>
	    <tbody>
	    	<?php $i = 0; ?>
			@foreach($success_array as $user)
				<tr>
					<?php $i = $i+1; ?>
					<td>{{$i}}</td>
					<td>{{$users[$i]['username']}}</td>
					<td>{{$users[$i]['email']}}</td>
					<td>ACTIVE</td>

					@if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	 @foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php 
                    	if(array_key_exists($key,$user))
                    	 echo '<td>'.$user[$key].'</td>';
                   	 	
                   	 ?>
                	@endforeach 
            	@endif
					<td>{{$users[$i]['record_status']}}</td>
				</tr>
			@endforeach
	    </tbody>
	</table>

	<h4>Users failed to create</h4>
	<table border="1" width="100%" cellpadding="2" cellspacing="1" style="border-collapse:collapse">
	    <thead>
			<tr>
				<th>{{trans('admin/user.serial_no')}}</th>
				<th>{{trans('admin/user.username')}}</th>
				<th>{{trans('admin/user.email')}}</th>
				<th>{{trans('admin/user.user_status')}}</th>
				<th>{{trans('admin/user.record_status')}}</th>

				 @if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	 @foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php
                    echo "<th> $key </th>";                    
                    ?>
                @endforeach 
            @endif

				<th><?php echo trans('admin/user.errors'); ?></th>
			</tr>
	    </thead>
	    <tbody>
	    	<?php $i = 0; ?>
			@foreach($failed_array as $user)
				<tr>
					<?php $i = $i+1; ?>
					<td>{{$i}}</td>
					<td>{{$users[$i]['username']}}</td>
					<td>{{$users[$i]['email']}}</td>
					<td>ACTIVE</td>
					<td>{{$users[$i]['record_status']}}</td>

					@if(is_array($customFieldDataFromExcel) && !empty($customFieldDataFromExcel) )
               	@foreach ($customFieldDataFromExcel as $key=>$fields)
                    <?php 
                    	if(array_key_exists($key,$user))
                    	 echo '<td>'.$user[$key].'</td>';
                   	 	
                   	 ?>
                	@endforeach 
            	@endif

					<td>{{$users[$i]['errors']}}</td>
				</tr>
			@endforeach
	    </tbody>
	</table>
@endif



