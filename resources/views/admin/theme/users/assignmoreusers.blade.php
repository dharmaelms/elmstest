@section('content')
@if ( Session::get('success') )
  <div class="alert alert-success">
  <button class="close" data-dismiss="alert">×</button>
  <!-- <strong>Success!</strong><br> -->
  {{ Session::get('success') }}
  </div>
  <?php Session::forget('success'); ?>
@endif
<!-- BEGIN Main Content -->
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <h3><i class="icon-file"></i> {{ trans('admin/user.assign_more_users') }}</h3>
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content">               
                <div class="btn-toolbar clearfix">                       
                    <div class="pull-right">
                        <div style="display:inline-block" class="show-tooltip" title="{{ trans('admin/user.add_select_users_to_group') }}">
                            <a class="btn btn-circle show-tooltip" title="{{ trans('admin/user.add_select_users_to_group') }}" id="delete-selected-btn" data-toggle="modal" href="#bulk"><i class="fa fa-plus"></i></a>
                        </div>
                    </div>
                </div><br>
                @if(count($users) > 0)              
                    <div class="clearfix"></div>
                    <form method="post" action="{{ URL::to('cp/usergroupmanagement/assign-moreusers/'.$ugid) }}" name="userform">
                    <div class="table-responsive">
                        <table class="table table-advance" class="sorting_asc_disabled sorting_desc_disabled">
                            <thead>
                                <tr>
                                    <th style="width:18px"><input type="checkbox" id="allselect" /></th>
                                    <th>{{ trans('admin/user.username') }}</th>
                                    <th>{{ trans('admin/user.first_name') }} {{ trans('admin/user.last_name') }}</th>
                                    <th>{{ trans('admin/user.email_id') }}</th>
                                    <th>{{ trans('admin/user.created_on') }}</th>
                                    <th>{{ trans('admin/user.user_groups') }}</th>
                                    <th>{{ trans('admin/program.programs') }}</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php $i=0;?>  
                                @foreach ($users as $user)
                                <?php $i++;?>                
                                <tr>
                                    <td>
                                       <input type="checkbox" name="user[]" value="{{$user['uid']}}" id="bulkchk"/>
                                    </td>
                                    <td>{{$user['username']}}</td>
                                    <td>{{$user['firstname']}} {{$user['lastname']}}</td>
                                    <td>{{$user['email']}}</td>
                                    <td>{{$user['created_at']}}</td>
                                    <td>
                                    @if(isset($user['ugids']))
                                      {{count($user['ugids'])}}
                                    @else
                                      0
                                    @endif
                                    </td>
                                    <td></td>
                                </tr>
                                @endforeach                          
                            </tbody>
                        </table>
                    </div>
                    </form>
                    <div id="bulk" class="modal fade">
                      <div class="modal-dialog">
                        <div class="modal-content">
                          <!--header-->
                          <div class="modal-header">
                              <div class="row">
                                  <div class="col-md-12">
                                      <div class="box">
                                          <div class="box-title">
                                              <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                              <h3><i class="icon-file"></i>{{ trans('admin/user.add_users') }}</h3>                                                 
                                          </div>
                                      </div>
                                  </div>
                              </div>
                          </div>
                          <!--content-->
                          <div class="modal-body">               
                              {{ trans('admin/user.modal_add_selected_user_to_ug') }}
                          </div>
                          <!--footer-->
                          <div class="modal-footer">
                              <a class="btn btn-danger" onclick="userform.submit();">{{ trans('admin/user.label_add') }}</a>
                              <a class="btn" data-dismiss="modal" aria-hidden="true" >{{ trans('admin/user.cancel') }}</a>
                          </div>
                        </div>
                      </div>
                    </div>
                @else
                    <div class="text-center">{{ trans('admin/user.no_assigned_user_to_this_ug') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->
@stop
 