@section('content')
    @if ( Session::get('success') )
        <div class="alert alert-success" id="alert-success">
          <button class="close" data-dismiss="alert">×</button>
          <strong>Success!</strong>
          {{ Session::get('success') }}
        </div>
    <?php Session::forget('success'); ?>
    @endif
    @if ( Session::get('error'))
        <div class="alert alert-danger">
          <button class="close" data-dismiss="alert">×</button>
          <strong>Error!</strong>
          {{ Session::get('error') }}
        </div>
    <?php Session::forget('error'); ?>
    @endif

    @if ( Session::get('warning'))
        <div class="alert alert-warning">
        <button class="close" data-dismiss="alert">×</button>
        <strong>Warning!</strong>
        {{ Session::get('warning') }}
        </div>
        <?php Session::forget('warning'); ?>
    @endif

    <link rel="stylesheet" href="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.min.css')}}">
    <script src="{{ URL::asset('admin/assets/chosen-bootstrap/chosen.jquery.min.js')}}"></script>
    <link rel="stylesheet" href="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.css')}}">
    <script src="{{ URL::asset('admin/assets/data-tables/jquery.dataTables.js')}}"></script>
    <script src="{{ URL::asset('admin/assets/data-tables/bootstrap3/dataTables.bootstrap.js')}}"></script>

<!-- BEGIN Main Content -->
<div class="row">
    <div class="col-md-12">
        <div class="box">
            <div class="box-title">
                <div class="box-tool">
                    <a data-action="collapse" href="#"><i class="icon-chevron-up"></i></a>
                </div>
            </div>                    
            <div class="box-content"> 
                <div class="btn-toolbar clearfix">
                    <div class="col-md-6">
                      <form class="form-horizontal" action="{{URL::to('cp/banners')}}">
                          <div class="form-group">
                            <label class="col-sm-2 col-lg-2 control-label" style="padding-right:0;text-align:left"><b>Showing :</b></label>
                            <div class="col-sm-4 col-lg-6 controls" style="padding-left:0;">
                            <?php $filter = Input::get('filter');?>
                              <select class="form-control chosen" name="filter" data-placeholder="ALL" onchange="this.form.submit();" tabindex="1">
                                  <option value="ALL" <?php if ($filter == 'ALL') echo 'selected';?>>All</option>
                                  <option value="ACTIVE" <?php if ($filter == 'ACTIVE') echo 'selected';?>>Active</option>
                                  <option value="IN-ACTIVE" <?php if ($filter == 'IN-ACTIVE') echo 'selected';?>>In-active</option>
                              </select>
                            </div>
                         </div>
                      </form>
                    </div>                       
                    <div class="pull-right">
                        <a class="btn btn-primary btn-sm" href="{{ URL::to('cp/banners/add-banner') }}">
                          <span class="btn btn-circle blue show-tooltip custom-btm">
                            <i class="fa fa-plus"></i>
                          </span>&nbsp;<?php echo trans('admin/banner.add_banner'); ?>
                        </a>&nbsp;&nbsp;
                    </div>
                </div> 
                @if(count($banners) > 0)              
                    <div class="clearfix"></div>
                    <?php
                        $edit_banner =  has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_BANNERS);
                        $delete_banner = has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_BANNERS);
                    ?>
                    <div class="table-responsive">
                        <table class="table table-advance" id="datatable1">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin/banner.serial_no') }}</th>
                                    <th>{{ trans('admin/banner.banner_name') }}</th>
                                    <th>{{ trans('admin/banner.web_banner') }} </th>
                                    <th>{{ trans('admin/banner.mobile_banner_portrait') }}</th>
                                    <th>{{ trans('admin/banner.mobile_banner_landscape') }}</th>
                                    <th>{{ trans('admin/banner.display_order') }}</th>
                                    <th>{{ trans('admin/banner.status') }}</th>
                                    <th>{{ trans('admin/banner.create_date') }}</th>
                                    @if($edit_banner || $delete_banner)
                                        <th>{{ trans('admin/banner.actions') }}</th>
                                    @endif
                                </tr>
                            </thead>
                            <tbody>
                                <?php 
                                    $page = Input::get('page', 0);
                                    if($page != 0)
                                    {
                                        $page = $page - 1;
                                    }
                                    $sn=$page * 10; 
                                ?>

                                @foreach($banners as $banner)
                                <?php
                                    
                                    $sn=$sn+1;
                                    $banner_file_name = $banner['file_client_name'];
                                    $mobile_banner_file_name = $banner['mobile_portrait'];
                                    $mobile_banner_file_name2 = $banner['mobile_landscape'];
                                     // $banner_type = $banner['banner_type'];
                                    $banner_exist = config('app.site_banners_path').$banner_file_name;
                                    $banner_exist1 = config('app.site_banners_path').$mobile_banner_file_name;
                                     $banner_exist2 = config('app.site_banners_path').$mobile_banner_file_name2;
                                    
                                    // web image
                                    if(!empty($banner_file_name) && (file_exists($banner_exist)) )
                                    {
                                        $banner_file_path=config('app.site_banners_path').$banner_file_name;
                                    }
                                    else{
                                        $banner_file_path=config('app.no_image'); 
                                    }

                                    // mobile portrait image
                                    if(!empty($mobile_banner_file_name) && (file_exists($banner_exist1)) ){    
                                        $mobile_banner_file_path = config('app.site_banners_path').$mobile_banner_file_name;
                                    }
                                    else{
                                         $mobile_banner_file_path=config('app.no_image'); 
                                    }

                                    // mobile landscape image 
                                    if(!empty($mobile_banner_file_name2) && (file_exists($banner_exist2)) ){
                                        $mobile_banner_file_path2 =  config('app.site_banners_path').$mobile_banner_file_name2;
                                    }else{
                                        $mobile_banner_file_path2=config('app.no_image');
                                    }

                                    $nxtval=$banner['sort_order']+1;
                                    $preval=$banner['sort_order']-1;
                                ?>
                                <tr>
                                    <td>{{$sn}}</td>
                                    <td>{{$banner['name']}}</td>
                                    <td><img src="{{URL::to($banner_file_path)}}" height="50px" style="max-width:130px;"></td>
                                    <td><img src="{{URL::to($mobile_banner_file_path)}}" height="50px" style="max-width:130px;"></td>
                                     <td><img src="{{URL::to($mobile_banner_file_path2)}}" height="50px" style="max-width:130px;"></td>
                                    <td>{{$banner['sort_order']}} &nbsp;&nbsp;&nbsp;
                                        @if($banner['sort_order'] == 1)
                                        <a class="btn btn-circle show-tooltip order" id="orderdownonly" title="Click to change the display order"  data-toggle="modal" href="{{ URL::to('cp/banners/sort-order/'.$banner['id'].'/'.$banner['sort_order'].'/'.$nxtval) }}" value="{{$banner['sort_order']}}" ><i class="fa fa-caret-down"></i></a>
                                        @elseif($banner['sort_order'] == $last_order)
                                        <a class="btn btn-circle show-tooltip order" id="orderuponly" title="Click to change the display order"  data-toggle="modal" href="{{ URL::to('cp/banners/sort-order/'.$banner['id'].'/'.$banner['sort_order'].'/'.$preval) }}"  value="{{$banner['sort_order']}}" ><i class="fa fa-caret-up"></i></a>
                                        @elseif($banner['sort_order'] != 1)
                                        <a class="btn btn-circle show-tooltip order" id="orderuponly" title="Click to change the display order"  data-toggle="modal" href="{{ URL::to('cp/banners/sort-order/'.$banner['id'].'/'.$banner['sort_order'].'/'.$preval) }}"  value="{{$banner['sort_order']}}" ><i class="fa fa-caret-up"></i></a>
                                        <a class="btn btn-circle show-tooltip order" id="orderdownonly" title="Click to change the display order"  data-toggle="modal" href="{{ URL::to('cp/banners/sort-order/'.$banner['id'].'/'.$banner['sort_order'].'/'.$nxtval) }}" value="{{$banner['sort_order']}}" ><i class="fa fa-caret-down"></i></a>
                                        @endif
                                    </td>
                                    <td>{{$banner['status']}}</td>
                                    <td>{{ Timezone::convertFromUTC($banner['created_at'], Auth::user()->timezone, Config('app.date_format'))}}</td>
                                    <td>
                                        @if($edit_banner)
                                            <a class="btn btn-circle show-tooltip" title="Edit banner" href="{{ URL::to('cp/banners/edit-banner/'.$banner['id']) }}"><i class="fa fa-edit"></i></a>
                                        @endif
                                        @if($delete_banner)
                                            <a class="btn btn-circle show-tooltip deletebanner" title="Delete banner" href="{{ URL::to('cp/banners/delete-banner/'.$banner['id'].'/'.$banner['sort_order']) }}"><i class="fa fa-trash-o"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach                          
                            </tbody>
                        </table>
                        <div class="row">
                            <div class="col-md-12 ">
                                <div class="pull-right">
                                    <?php echo $banners->render(); ?>
                                </div>
                            </div>
                        </div>
                    </div>
                @else
                    <div class="text-center">{{ trans('admin/banner.there_are_no_banners') }}</div>
                @endif
            </div>
        </div>
    </div>
</div>
<!-- END Main Content -->

<!-- delete window -->
<div id="deletemodal" class="modal fade">
    <div class="modal-dialog">
        <div class="modal-content">
            <!--header-->
            <div class="modal-header">
                <div class="row custom-box">
                    <div class="col-md-12">
                        <div class="box">
                            <div class="box-title">
                                <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                <h3><i class="icon-file"></i>{{ trans('admin/banner.delete_banner') }}</h3>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body" style="padding: 20px">               
                {{ trans('admin/banner.modal_delete_banner') }}
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-danger">{{ trans('admin/banner.yes') }}</a>
              <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/banner.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->

<script type="text/javascript">
    //individual user delete
    $(document).on('click','.deletebanner',function(e){
      e.preventDefault();
      var $this = $(this);
      var $deletemodal = $('#deletemodal');
        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
      $deletemodal.modal('show');
    });

    $(document).ready(function(){
        $('#alert-success').delay(5000).fadeOut();
        $('#datatable1').DataTable({
            "paging":   true,
            "info":     true
        });
    })

</script>
@stop