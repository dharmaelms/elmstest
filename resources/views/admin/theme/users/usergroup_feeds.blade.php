@if(count($groupfeeds) > 0)            
    <div class="clearfix"></div>
    <div class="table-responsive">
        <table class="table table-advance" class="sorting_asc_disabled sorting_desc_disabled">
            <thead>
                <tr>
                    <th>{{ trans('admin/program.channel') }}</th>
                    <th>{{ trans('admin/program.start_date') }}</th>
                    <th>{{ trans('admin/program.end_date') }}</th>
                    <th>{{ trans('admin/user.user_groups') }}</th>
                    <th>{{ trans('admin/user.status') }}</th>
                </tr>
            </thead>
            <tbody>
                @foreach($groupfeeds as $groupfeed)
                    <tr>
                        <td>{{$groupfeed['program_title']}}</td>
                        <td>{{ Timezone::convertFromUTC('@'.$groupfeed['program_startdate'], Auth::user()->timezone, Config('app.date_format'))}}</td>
                        <td>{{ Timezone::convertFromUTC('@'.$groupfeed['program_enddate'], Auth::user()->timezone, Config('app.date_format'))}}</td>
                        <td>{{$groupfeed['group_names']}}</td>
                        <td>{{$groupfeed['status']}}</td>
                    </tr>
                @endforeach                          
            </tbody>
        </table>
    </div>
@else
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-content">
                <div class="row">
                    <div class="col-md-12">
                        <div class="text-center">{{ trans('admin/user.no_channel_assigned_through_ug_for_user') }}</div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endif
