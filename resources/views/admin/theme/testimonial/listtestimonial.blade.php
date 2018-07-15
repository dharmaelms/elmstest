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
                      <form class="form-horizontal" action="{{URL::to('cp/testimonials')}}">
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
                        <a class="btn btn-primary btn-sm" href="{{ URL::to('cp/testimonials/create-testimonial') }}">
                          <span class="btn btn-circle blue show-tooltip custom-btm">
                            <i class="fa fa-plus"></i>
                          </span>&nbsp;{{ trans('admin/testimonial.list_add_label').' '.$label }}
                        </a>&nbsp;&nbsp;
                    </div>
                </div> 
                <?php 
                    $edit_testimonial = has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::EDIT_TESTIMONIALS);
                    $delete_testimonial = has_admin_permission(ModuleEnum::HOME_PAGE, HomePagePermission::DELETE_TESTIMONIALS);
                ?>
                @if(count($testimonials) > 0 )
                    <div class="clearfix"></div>
                    <div class="table-responsive">
                        <table class="table table-advance" id="datatable1">
                            <thead>
                                <tr>
                                    <th>{{ trans('admin/testimonial.list_page_picture_label') }}</th>
                                    <th>{{ trans('admin/testimonial.list_page_name') }}</th>
                                    <th>{{ trans('admin/testimonial.list_page_sort_order') }}</th>
                                    <th>{{ trans('admin/testimonial.list_page_status') }}</th>
                                    @if($edit_testimonial || $delete_testimonial)
                                        <th>{{ trans('admin/testimonial.actions') }}</th>
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
                                    $i = 0;
                                ?>
                            @foreach($testimonials as $testimonial)
                            <?php
                                $sn=$sn+1;
                                $logo_name = $testimonial['logoname'];
                                $logo = Config::get('app.testimonials_path').$logo_name;
                                $nxtval=$testimonial['sort_order']+1;
                                $preval=$testimonial['sort_order']-1;
                                $i++;
                            ?>
                            <tr>
                                <!-- <td>{{$sn}}</td> -->
                                <td><img src="{{URL::to($logo)}}" height="50px" width="50px" ></td>
                                <td>{{$testimonial['name'] }}</td>
                                
                           
                                <!-- sort option -->
                                <td>{{$testimonial['sort_order']}} &nbsp;&nbsp;&nbsp;
                                    @if(count($testimonials) > 1)
                                        @if($testimonial['sort_order'] == 1)
                                        <a class="btn btn-circle show-tooltip order" id="orderdownonly" title="{{ trans('admin/testimonial.click_to_change_display_order') }}"  data-toggle="modal" href="{{ URL::to('cp/testimonials/sort-order/'.$testimonial['id'].'/'.$testimonial['sort_order'].'/'.$nxtval) }}" value="{{$testimonial['sort_order']}}" ><i class="fa fa-caret-down"></i></a>
                                        @elseif($testimonial['sort_order'] == $last_order)
                                        <a class="btn btn-circle show-tooltip order" id="orderuponly" title="{{ trans('admin/testimonial.click_to_change_display_order') }}"  data-toggle="modal" href="{{ URL::to('cp/testimonials/sort-order/'.$testimonial['id'].'/'.$testimonial['sort_order'].'/'.$preval) }}"  value="{{$testimonial['sort_order']}}" ><i class="fa fa-caret-up"></i></a>
                                        @elseif($testimonial['sort_order'] > 1)
                                        <a class="btn btn-circle show-tooltip order" id="orderuponly" title="{{ trans('admin/testimonial.click_to_change_display_order') }}"  data-toggle="modal" href="{{ URL::to('cp/testimonials/sort-order/'.$testimonial['id'].'/'.$testimonial['sort_order'].'/'.$preval) }}"  value="{{$testimonial['sort_order']}}" ><i class="fa fa-caret-up"></i></a>
                                        <a class="btn btn-circle show-tooltip order" id="orderdownonly" title="{{ trans('admin/testimonial.click_to_change_display_order') }}"  data-toggle="modal" href="{{ URL::to('cp/testimonials/sort-order/'.$testimonial['id'].'/'.$testimonial['sort_order'].'/'.$nxtval) }}" value="{{$testimonial['sort_order']}}" ><i class="fa fa-caret-down"></i></a>
                                        @endif
                                    @endif
                                </td>
                                <td>{{$testimonial['status'] }}</td>
                                <td>
                                @if($edit_testimonial)
                                    <a class="btn btn-circle show-tooltip" title="{{ trans('admin/testimonial.list_edit_testimonial').' '.$label }}" href="{{ URL::to('/cp/testimonials/edit-testimonial/'. $testimonial['id']) }}"><i class="fa fa-edit"></i></a>
                                @endif
                                @if($delete_testimonial)
                                    <a class="btn btn-circle show-tooltip deletelogo" title="{{ trans('admin/testimonial.list_delete_testimonial').' '.$label }}" href="{{URL::to('/cp/testimonials/delete-testimonial/'. $testimonial['id'].'/'.$testimonial['logoname'].'/'.$testimonial['sort_order'])}}"><i class="fa fa-trash-o"></i></a>
                                @endif
                                    <a class="btn btn-circle show-tooltip viewlogo" title="{{ trans('admin/testimonial.view') }} {{ $label }}" data-toggle="modal" data-target="#viewModal{{ $i }}"><i class="fa fa-eye"></i></a>
                                </td>
                            </tr>
                            <!-- View window -->
                            <div id="viewModal{{ $i }}" class="modal fade">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <!--header-->
                                        <div class="modal-header">
                                            <div class="row custom-box">
                                                <div class="col-md-12">
                                                    <div class="box">
                                                        <div class="box-title">
                                                            <button type="button" class="close" data-dismiss="modal" aria-hidden="true">×</button>
                                                            <h3><i class="icon-file"></i>{{ trans('admin/testimonial.view') }} {{ $label }}</h3>                                                 
                                                        </div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                        <!--content-->
                                        <div class="modal-body" style="padding: 20px">               
                                            <span>{{ trans('admin/testimonial.add_page_name_label') }}: {{ $testimonial['name'] }}</span><br>
                                            <span>{{ trans('admin/testimonial.short_description') }}: <p>{{ $testimonial['short_description'] }}</p></span>
                                            <span>{{ trans('admin/testimonial.add_page_description_label') }}: <p>{{ $testimonial['description'] }}</p></span>
                                            <span>Status: {{ $testimonial['status'] }}</span><br>
                                            <span>{{ trans('admin/testimonial.add_page_sort_order_label') }}: {{ $testimonial['sort_order'] }}</span><br>
                                            <span>{{ trans('admin/testimonial.display_in_home_page') }}: {{ $testimonial['home_page_display_status'] }}</span>
                                        </div>
                                        <!--footer-->
                                        <div class="modal-footer">
                                          <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/testimonial.close') }}</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <!-- View window ends -->
                            @endforeach     
                            </tbody>
                        </table>
                    <div class="row">
                            <div class="col-md-12 ">
                                <div class="pull-right">
                                    <?php echo $testimonials->render(); ?>
                                </div>
                            </div>
                        </div>    
                  
            </div>
             @else
                    <div class="text-center">{{ trans('admin/testimonial.there_are_no_testimonial') }} {{ $label }}</div>
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
                                <h3><i class="icon-file"></i>{{ trans('admin/testimonial.delete') }} {{ $label }}</h3>                                                 
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <!--content-->
            <div class="modal-body" style="padding: 20px">               
                {{ trans('admin/testimonial.modal_delete_testimonial') }} {{ $label }}?
            </div>
            <!--footer-->
            <div class="modal-footer">
              <a class="btn btn-danger"> {{ trans('admin/testimonial.yes') }}</a>
              <a class="btn btn-success" data-dismiss="modal">{{ trans('admin/testimonial.close') }}</a>
            </div>
        </div>
    </div>
</div>
<!-- delete window ends -->


<script type="text/javascript">
    //individual user delete
    $(document).on('click','.deletelogo',function(e){
      e.preventDefault();
      var $this = $(this);
      var $deletemodal = $('#deletemodal');
        $deletemodal.find('.modal-footer .btn-danger').prop('href',$this.prop('href'))
      $deletemodal.modal('show');
    });

    $(document).ready(function(){
        $('#alert-success').delay(5000).fadeOut();
        $('#datatable1').DataTable({
            "paging":   false,
            "info":     false
        });
    })

</script>
@stop