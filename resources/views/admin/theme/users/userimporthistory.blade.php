@section('content')
<!-- BEGIN Main Content -->
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <!-- <h3><i class="icon-file"></i> Users Import History</h3> -->
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content">
                @if(count($import_history) > 0)              
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-advance" class="sorting_asc_disabled sorting_desc_disabled">
                            <thead>
                                <tr>
                                    <th><?php echo trans('admin/user.file_name'); ?></th>
                                    <th><?php echo trans('admin/user.no_of_records'); ?></th>
                                    <th><?php echo trans('admin/user.success_count'); ?></th> 
                                    <th><?php echo trans('admin/user.failed_count'); ?></th>
                                    <th><?php echo trans('admin/user.status'); ?></th>
                                    <th><?php echo trans('admin/user.created_by'); ?></th>
                                    <th><?php echo trans('admin/user.created_on'); ?></th>
                                </tr>
                            </thead>
                            <tbody>  
                                @foreach($import_history as $history)               
                                <tr>
                                    <td>{{$history['filename']}}</td>
                                    <td>{{$history['no_of_records']}}</td>
                                    <td>{{$history['success_count']}}</td>
                                    <td>{{$history['failed_count']}}</td>
                                    <td>{{ucwords(strtolower($history['status']))}}</td>
                                    <td>{{$history['created_by']}}</td>
                                    <td>{{ Timezone::convertFromUTC('@'.$history['created_at'], Auth::user()->timezone, config('app.date_format'))}}</td>
                                </tr>
                                @endforeach                          
                            </tbody>
                        </table>
                    </div>
                @else
                    <div class="text-center"><?php echo trans('admin/user.no_import_history'); ?></div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->
@stop
 
