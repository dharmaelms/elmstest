<div class="row">
  <div class="col-md-12">
      <div class="box">
          <div class="box-content">
              <div class="row">
                  <div class="col-md-12 user-profile-info">
                    <p><span>{{ trans('admin/user.user_group_id') }}:</span> {{$usergroup['ugid']}}</p>
                    <p><span>{{ trans('admin/user.user_group_name') }}:</span> {{$usergroup['usergroup_name']}}</p>
                    <p><span>{{ trans('admin/user.user_group_email') }}:</span> <a href="#">{{$usergroup['usergroup_email']}}</a></p>
                    <p><span>{{ trans('admin/user.description') }}:</span> {{html_entity_decode($usergroup['description'])}}</p>
                    <p><span>{{ trans('admin/user.created_on') }}:</span> {{ Timezone::convertFromUTC($usergroup['created_at'], Auth::user()->timezone, Config('app.date_format'))}}</p>
                    <p><span>{{ trans('admin/user.status') }}:</span> {{$usergroup['status']}}</p>  
                  </div>
              </div>
          </div>
      </div>
  </div>
</div>